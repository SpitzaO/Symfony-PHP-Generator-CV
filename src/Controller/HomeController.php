<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\ProfileRepository;
use App\Service\CvPdfRenderer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home', methods: ['GET'])]
    public function index(ProfileRepository $profileRepository): Response
    {
        return $this->render('home/index.html.twig', [
            'profile' => $profileRepository->findMainWithRelations(),
        ]);
    }

    #[Route('/cv.pdf', name: 'app_cv_pdf', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    public function pdf(ProfileRepository $profileRepository, CvPdfRenderer $pdfRenderer): Response
    {
        $profile = $profileRepository->findMainWithRelations();
        if (!$profile) {
            $this->addFlash('error', 'Najpierw utwórz swój profil.');

            return $this->redirectToRoute('app_home');
        }

        return new Response(
            $pdfRenderer->render($profile),
            Response::HTTP_OK,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="cv.pdf"',
            ],
        );
    }
}
