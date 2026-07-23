<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Profile;
use Dompdf\Dompdf;
use Dompdf\Options;
use Twig\Environment;

/**
 * Renders a profile into a PDF document.
 */
final class CvPdfRenderer
{
    public function __construct(
        private readonly Environment $twig,
        private readonly ProfilePhotoStorage $photoStorage,
    ) {
    }

    /**
     * @return string raw PDF bytes
     */
    public function render(Profile $profile): string
    {
        $html = $this->twig->render('home/pdf.html.twig', [
            'profile' => $profile,
            'photo' => $this->photoStorage->dataUri($profile->getPhotoFilename()),
        ]);

        $options = new Options();
        $options->set('defaultFont', 'DejaVu Sans');
        // Remote access stays off (Dompdf's default): the photo arrives as a data URI,
        // so the renderer never needs the filesystem or the network.

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return (string) $dompdf->output();
    }
}
