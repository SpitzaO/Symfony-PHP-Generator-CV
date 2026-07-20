<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Competency;
use App\Form\CompetencyType;
use App\Repository\CompetencyRepository;
use App\Repository\ProfileRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsCsrfTokenValid;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/competency')]
#[IsGranted('ROLE_ADMIN')]
final class CompetencyController extends AbstractController
{
    #[Route(name: 'app_competency_index', methods: ['GET'])]
    public function index(CompetencyRepository $competencyRepository): Response
    {
        return $this->render('competency/index.html.twig', [
            'competencies' => $competencyRepository->findBy([], ['id' => 'ASC']),
        ]);
    }

    #[Route('/new', name: 'app_competency_new', methods: ['GET', 'POST'])]
    public function new(Request $request, CompetencyRepository $competencyRepository, ProfileRepository $profileRepository): Response
    {
        $profile = $profileRepository->findMain();
        if (!$profile) {
            $this->addFlash('error', 'Create your profile first.');

            return $this->redirectToRoute('app_home');
        }

        $competency = new Competency();
        $competency->setProfile($profile);
        $form = $this->createForm(CompetencyType::class, $competency);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $competencyRepository->save($competency, true);

            return $this->redirectToRoute('app_competency_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('competency/new.html.twig', [
            'competency' => $competency,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_competency_show', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function show(Competency $competency): Response
    {
        return $this->render('competency/show.html.twig', [
            'competency' => $competency,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_competency_edit', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function edit(Request $request, Competency $competency, CompetencyRepository $competencyRepository): Response
    {
        $form = $this->createForm(CompetencyType::class, $competency);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $competencyRepository->save($competency, true);

            return $this->redirectToRoute('app_competency_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('competency/edit.html.twig', [
            'competency' => $competency,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_competency_delete', requirements: ['id' => '\d+'], methods: ['POST'])]
    #[IsCsrfTokenValid('delete')]
    public function delete(Competency $competency, CompetencyRepository $competencyRepository): Response
    {
        $competencyRepository->remove($competency, true);

        return $this->redirectToRoute('app_competency_index', [], Response::HTTP_SEE_OTHER);
    }
}
