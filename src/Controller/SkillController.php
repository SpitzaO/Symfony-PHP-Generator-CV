<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Skill;
use App\Form\SkillType;
use App\Repository\ProfileRepository;
use App\Repository\SkillRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsCsrfTokenValid;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/skill')]
#[IsGranted('ROLE_ADMIN')]
final class SkillController extends AbstractController
{
    #[Route(name: 'app_skill_index', methods: ['GET'])]
    public function index(SkillRepository $skillRepository, ProfileRepository $profileRepository): Response
    {
        return $this->render('skill/index.html.twig', [
            'skills' => $skillRepository->findBy(
                ['profile' => $profileRepository->findMain()],
                ['id' => 'ASC'],
            ),
        ]);
    }

    #[Route('/new', name: 'app_skill_new', methods: ['GET', 'POST'])]
    public function new(Request $request, SkillRepository $skillRepository, ProfileRepository $profileRepository): Response
    {
        $profile = $profileRepository->findMain();
        if (!$profile) {
            $this->addFlash('error', 'Najpierw utwórz swój profil.');

            return $this->redirectToRoute('app_home');
        }

        $skill = new Skill();
        $skill->setProfile($profile);
        $form = $this->createForm(SkillType::class, $skill);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $skillRepository->save($skill, true);

            return $this->redirectToRoute('app_skill_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('skill/new.html.twig', [
            'skill' => $skill,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_skill_show', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function show(Skill $skill): Response
    {
        return $this->render('skill/show.html.twig', [
            'skill' => $skill,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_skill_edit', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function edit(Request $request, Skill $skill, SkillRepository $skillRepository): Response
    {
        $form = $this->createForm(SkillType::class, $skill);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $skillRepository->save($skill, true);

            return $this->redirectToRoute('app_skill_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('skill/edit.html.twig', [
            'skill' => $skill,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_skill_delete', requirements: ['id' => '\d+'], methods: ['POST'])]
    #[IsCsrfTokenValid('delete')]
    public function delete(Skill $skill, SkillRepository $skillRepository): Response
    {
        $skillRepository->remove($skill, true);

        return $this->redirectToRoute('app_skill_index', [], Response::HTTP_SEE_OTHER);
    }
}
