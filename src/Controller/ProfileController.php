<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Profile;
use App\Form\ProfileType;
use App\Repository\ProfileRepository;
use App\Service\ProfilePhotoStorage;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsCsrfTokenValid;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/profile')]
#[IsGranted('ROLE_ADMIN')]
final class ProfileController extends AbstractController
{
    public function __construct(
        private readonly ProfilePhotoStorage $photoStorage,
    ) {
    }

    #[Route('/new', name: 'app_profile_new', methods: ['GET', 'POST'])]
    public function new(Request $request, ProfileRepository $profileRepository): Response
    {
        // The app is single-profile by design: a second one would be saved but never
        // shown, because findMain() always resolves to the lowest id.
        if ($existing = $profileRepository->findMain()) {
            return $this->redirectToRoute('app_profile_edit', ['id' => $existing->getId()]);
        }

        $profile = new Profile();
        $form = $this->createForm(ProfileType::class, $profile);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->handlePhotoUpload($form, $profile);
            $profileRepository->save($profile, true);

            return $this->redirectToRoute('app_home', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('profile/new.html.twig', [
            'profile' => $profile,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_profile_edit', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function edit(Request $request, Profile $profile, ProfileRepository $profileRepository): Response
    {
        $form = $this->createForm(ProfileType::class, $profile);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->handlePhotoUpload($form, $profile);
            $profileRepository->save($profile, true);

            return $this->redirectToRoute('app_home', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('profile/edit.html.twig', [
            'profile' => $profile,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_profile_delete', requirements: ['id' => '\d+'], methods: ['POST'])]
    #[IsCsrfTokenValid('delete')]
    public function delete(Profile $profile, ProfileRepository $profileRepository): Response
    {
        $this->photoStorage->remove($profile->getPhotoFilename());
        $profileRepository->remove($profile, true);

        return $this->redirectToRoute('app_home', [], Response::HTTP_SEE_OTHER);
    }

    private function handlePhotoUpload(FormInterface $form, Profile $profile): void
    {
        $file = $form->get('photo')->getData();
        if (!$file instanceof UploadedFile) {
            return;
        }

        $newFilename = $this->photoStorage->store($file);
        $this->photoStorage->remove($profile->getPhotoFilename());
        $profile->setPhotoFilename($newFilename);
    }
}
