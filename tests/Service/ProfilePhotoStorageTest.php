<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Service\ProfilePhotoStorage;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\AsciiSlugger;

final class ProfilePhotoStorageTest extends TestCase
{
    /** A valid 1x1 PNG. */
    private const PNG = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==';

    private string $photoDir;
    private string $workDir;
    private Filesystem $filesystem;
    private ProfilePhotoStorage $storage;

    protected function setUp(): void
    {
        $this->filesystem = new Filesystem();
        $base = sys_get_temp_dir().'/cv-photo-test-'.bin2hex(random_bytes(6));
        $this->photoDir = $base.'/uploads';
        $this->workDir = $base.'/work';
        $this->filesystem->mkdir([$this->photoDir, $this->workDir]);

        $this->storage = new ProfilePhotoStorage(
            $this->photoDir,
            new AsciiSlugger(),
            $this->filesystem,
            new NullLogger(),
        );
    }

    protected function tearDown(): void
    {
        $this->filesystem->remove(\dirname($this->photoDir));
    }

    public function testStoreMovesTheFileAndReturnsAUniqueName(): void
    {
        $first = $this->storage->store($this->upload('My Photo.png'));
        $second = $this->storage->store($this->upload('My Photo.png'));

        self::assertNotSame($first, $second, 'Two uploads of the same name must not collide.');
        self::assertStringStartsWith('my-photo-', $first);
        self::assertStringEndsWith('.png', $first);
        self::assertFileExists($this->photoDir.'/'.$first);
        self::assertFileExists($this->photoDir.'/'.$second);
    }

    public function testDataUriInlinesTheImage(): void
    {
        $filename = $this->storage->store($this->upload('photo.png'));

        $dataUri = $this->storage->dataUri($filename);

        self::assertNotNull($dataUri);
        self::assertStringStartsWith('data:image/png;base64,', $dataUri);
        self::assertSame(
            self::PNG,
            substr($dataUri, \strlen('data:image/png;base64,')),
        );
    }

    public function testDataUriReturnsNullWhenThereIsNoUsableFile(): void
    {
        self::assertNull($this->storage->dataUri(null));
        self::assertNull($this->storage->dataUri(''));
        self::assertNull($this->storage->dataUri('never-uploaded.png'));
    }

    public function testDataUriRefusesToLeaveTheUploadDirectory(): void
    {
        $outside = \dirname($this->photoDir).'/secret.png';
        file_put_contents($outside, base64_decode(self::PNG, true));

        self::assertNull($this->storage->dataUri('../secret.png'));
    }

    public function testRemoveDeletesTheFileAndToleratesMissingOnes(): void
    {
        $filename = $this->storage->store($this->upload('photo.png'));

        $this->storage->remove($filename);

        self::assertFileDoesNotExist($this->photoDir.'/'.$filename);

        // Removing again, or removing nothing at all, must not blow up.
        $this->storage->remove($filename);
        $this->storage->remove(null);
        self::assertDirectoryExists($this->photoDir);
    }

    private function upload(string $originalName): UploadedFile
    {
        $path = $this->workDir.'/'.bin2hex(random_bytes(6)).'.png';
        file_put_contents($path, base64_decode(self::PNG, true));

        return new UploadedFile($path, $originalName, 'image/png', null, true);
    }
}
