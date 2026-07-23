<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Interest;
use App\Form\InterestType;
use App\Repository\InterestRepository;
use App\Repository\ProfileRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsCsrfTokenValid;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/interest')]
#[IsGranted('ROLE_ADMIN')]
final class InterestController extends AbstractController
{
    #[Route(name: 'app_interest_index', methods: ['GET'])]
    public function index(InterestRepository $interestRepository, ProfileRepository $profileRepository): Response
    {
        return $this->render('interest/index.html.twig', [
            'interests' => $interestRepository->findBy(
                ['profile' => $profileRepository->findMain()],
                ['name' => 'ASC'],
            ),
        ]);
    }

    #[Route('/new', name: 'app_interest_new', methods: ['GET', 'POST'])]
    public function new(Request $request, InterestRepository $interestRepository, ProfileRepository $profileRepository): Response
    {
        $profile = $profileRepository->findMain();
        if (!$profile) {
            $this->addFlash('error', 'Najpierw utwórz swój profil.');

            return $this->redirectToRoute('app_home');
        }

        $interest = new Interest();
        $interest->setProfile($profile);
        $form = $this->createForm(InterestType::class, $interest);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $interestRepository->save($interest, true);

            return $this->redirectToRoute('app_interest_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('interest/new.html.twig', [
            'interest' => $interest,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_interest_show', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function show(Interest $interest): Response
    {
        return $this->render('interest/show.html.twig', [
            'interest' => $interest,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_interest_edit', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function edit(Request $request, Interest $interest, InterestRepository $interestRepository): Response
    {
        $form = $this->createForm(InterestType::class, $interest);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $interestRepository->save($interest, true);

            return $this->redirectToRoute('app_interest_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('interest/edit.html.twig', [
            'interest' => $interest,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_interest_delete', requirements: ['id' => '\d+'], methods: ['POST'])]
    #[IsCsrfTokenValid('delete')]
    public function delete(Interest $interest, InterestRepository $interestRepository): Response
    {
        $interestRepository->remove($interest, true);

        return $this->redirectToRoute('app_interest_index', [], Response::HTTP_SEE_OTHER);
    }
}
