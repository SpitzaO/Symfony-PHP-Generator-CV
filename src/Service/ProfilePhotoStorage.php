<?php

declare(strict_types=1);

namespace App\Service;

use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;

/**
 * Owns everything about profile photos on disk: where they live, what formats are
 * accepted, and how they are handed to Dompdf.
 */
final class ProfilePhotoStorage
{
    /**
     * The only formats the app accepts. Anything outside this list would render on the
     * website but silently vanish from the PDF export, so the upload constraint in
     * ProfileType reads this same list instead of keeping its own.
     */
    public const ALLOWED_EXTENSIONS = ['jpg', 'jpeg', 'png', 'gif'];

    /**
     * @var array<string, string> extension => MIME type used in the data URI
     */
    private const MIME_TYPES = [
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png' => 'image/png',
        'gif' => 'image/gif',
    ];

    public function __construct(
        #[Autowire('%kernel.project_dir%/public/uploads/profiles')] private readonly string $photoDir,
        private readonly SluggerInterface $slugger,
        private readonly Filesystem $filesystem,
        private readonly LoggerInterface $logger,
    ) {
    }

    /**
     * Moves the upload into place and returns the stored filename.
     */
    public function store(UploadedFile $file): string
    {
        $original = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $filename = $this->slugger->slug($original)->lower()
            .'-'.bin2hex(random_bytes(8))
            .'.'.$file->guessExtension();

        $file->move($this->photoDir, $filename);

        return $filename;
    }

    public function remove(?string $filename): void
    {
        if (null === $path = $this->path($filename)) {
            return;
        }

        try {
            $this->filesystem->remove($path);
        } catch (IOException $e) {
            // A leftover file is not worth failing the request over, but it must not
            // disappear silently either — the upload directory would grow unexplained.
            $this->logger->warning('Could not delete profile photo "{file}": {reason}', [
                'file' => $filename,
                'reason' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Inlines the photo as a base64 data URI so Dompdf needs no file or network access.
     * Returns null when there is no usable photo.
     */
    public function dataUri(?string $filename): ?string
    {
        if (null === $path = $this->path($filename)) {
            return null;
        }

        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        if (!isset(self::MIME_TYPES[$extension])) {
            return null;
        }

        return 'data:'.self::MIME_TYPES[$extension].';base64,'.base64_encode((string) file_get_contents($path));
    }

    /**
     * Resolves a stored filename to an existing path, or null if it is missing or
     * tries to point outside the upload directory.
     */
    private function path(?string $filename): ?string
    {
        if (null === $filename || '' === $filename || $filename !== basename($filename)) {
            return null;
        }

        $path = $this->photoDir.'/'.$filename;

        return is_file($path) ? $path : null;
    }
}
