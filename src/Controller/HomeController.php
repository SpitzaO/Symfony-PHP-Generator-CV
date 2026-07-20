<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\ProfileRepository;
use Dompdf\Dompdf;
use Dompdf\Options;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class HomeController extends AbstractController
{
    public function __construct(
        #[Autowire('%kernel.project_dir%/public/uploads/profiles')] private readonly string $photoDir,
    ) {
    }

    #[Route('/', name: 'app_home', methods: ['GET'])]
    public function index(ProfileRepository $profileRepository): Response
    {
        return $this->render('home/index.html.twig', [
            'profile' => $profileRepository->findMain(),
        ]);
    }

    #[Route('/cv.pdf', name: 'app_cv_pdf', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    public function pdf(ProfileRepository $profileRepository): Response
    {
        $profile = $profileRepository->findMain();
        if (!$profile) {
            $this->addFlash('error', 'Create your profile first.');

            return $this->redirectToRoute('app_home');
        }

        $html = $this->renderView('home/pdf.html.twig', [
            'profile' => $profile,
            'photo' => $this->photoDataUri($profile->getPhotoFilename()),
        ]);

        $options = new Options();
        $options->set('defaultFont', 'DejaVu Sans');

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return new Response(
            $dompdf->output(),
            Response::HTTP_OK,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="cv.pdf"',
            ],
        );
    }

    /**
     * Inline the profile photo as a base64 data URI so Dompdf needs no file/remote access.
     * Returns null for missing files or formats Dompdf can't render reliably (e.g. WebP).
     */
    private function photoDataUri(?string $filename): ?string
    {
        if (!$filename) {
            return null;
        }

        $mimes = [
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
        ];
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        if (!isset($mimes[$extension])) {
            return null;
        }

        $path = $this->photoDir.'/'.$filename;
        if (!is_file($path)) {
            return null;
        }

        return 'data:'.$mimes[$extension].';base64,'.base64_encode((string) file_get_contents($path));
    }
}
