<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Education;
use App\Form\EducationType;
use App\Repository\EducationRepository;
use App\Repository\ProfileRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsCsrfTokenValid;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/education')]
#[IsGranted('ROLE_ADMIN')]
final class EducationController extends AbstractController
{
    #[Route(name: 'app_education_index', methods: ['GET'])]
    public function index(EducationRepository $educationRepository, ProfileRepository $profileRepository): Response
    {
        return $this->render('education/index.html.twig', [
            'education' => $educationRepository->findBy(
                ['profile' => $profileRepository->findMain()],
                ['startDate' => 'DESC'],
            ),
        ]);
    }

    #[Route('/new', name: 'app_education_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EducationRepository $educationRepository, ProfileRepository $profileRepository): Response
    {
        $profile = $profileRepository->findMain();
        if (!$profile) {
            $this->addFlash('error', 'Najpierw utwórz swój profil.');

            return $this->redirectToRoute('app_home');
        }

        $education = new Education();
        $education->setProfile($profile);
        $form = $this->createForm(EducationType::class, $education);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $educationRepository->save($education, true);

            return $this->redirectToRoute('app_education_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('education/new.html.twig', [
            'education' => $education,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_education_show', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function show(Education $education): Response
    {
        return $this->render('education/show.html.twig', [
            'education' => $education,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_education_edit', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function edit(Request $request, Education $education, EducationRepository $educationRepository): Response
    {
        $form = $this->createForm(EducationType::class, $education);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $educationRepository->save($education, true);

            return $this->redirectToRoute('app_education_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('education/edit.html.twig', [
            'education' => $education,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_education_delete', requirements: ['id' => '\d+'], methods: ['POST'])]
    #[IsCsrfTokenValid('delete')]
    public function delete(Education $education, EducationRepository $educationRepository): Response
    {
        $educationRepository->remove($education, true);

        return $this->redirectToRoute('app_education_index', [], Response::HTTP_SEE_OTHER);
    }
}
