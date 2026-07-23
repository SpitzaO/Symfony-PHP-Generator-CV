<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Experience;
use App\Form\ExperienceType;
use App\Repository\ExperienceRepository;
use App\Repository\ProfileRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsCsrfTokenValid;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/experience')]
#[IsGranted('ROLE_ADMIN')]
final class ExperienceController extends AbstractController
{
    #[Route(name: 'app_experience_index', methods: ['GET'])]
    public function index(ExperienceRepository $experienceRepository, ProfileRepository $profileRepository): Response
    {
        return $this->render('experience/index.html.twig', [
            'experiences' => $experienceRepository->findBy(
                ['profile' => $profileRepository->findMain()],
                ['startDate' => 'DESC'],
            ),
        ]);
    }

    #[Route('/new', name: 'app_experience_new', methods: ['GET', 'POST'])]
    public function new(Request $request, ExperienceRepository $experienceRepository, ProfileRepository $profileRepository): Response
    {
        $profile = $profileRepository->findMain();
        if (!$profile) {
            $this->addFlash('error', 'Najpierw utwórz swój profil.');

            return $this->redirectToRoute('app_home');
        }

        $experience = new Experience();
        $experience->setProfile($profile);
        $form = $this->createForm(ExperienceType::class, $experience);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $experienceRepository->save($experience, true);

            return $this->redirectToRoute('app_experience_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('experience/new.html.twig', [
            'experience' => $experience,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_experience_show', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function show(Experience $experience): Response
    {
        return $this->render('experience/show.html.twig', [
            'experience' => $experience,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_experience_edit', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function edit(Request $request, Experience $experience, ExperienceRepository $experienceRepository): Response
    {
        $form = $this->createForm(ExperienceType::class, $experience);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $experienceRepository->save($experience, true);

            return $this->redirectToRoute('app_experience_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('experience/edit.html.twig', [
            'experience' => $experience,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_experience_delete', requirements: ['id' => '\d+'], methods: ['POST'])]
    #[IsCsrfTokenValid('delete')]
    public function delete(Experience $experience, ExperienceRepository $experienceRepository): Response
    {
        $experienceRepository->remove($experience, true);

        return $this->redirectToRoute('app_experience_index', [], Response::HTTP_SEE_OTHER);
    }
}
