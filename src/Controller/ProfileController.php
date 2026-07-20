<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Profile;
use App\Form\ProfileType;
use App\Repository\ProfileRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsCsrfTokenValid;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/profile')]
final class ProfileController extends AbstractController
{
    public function __construct(
        #[Autowire('%kernel.project_dir%/public/uploads/profiles')] private readonly string $photoDir,
        private readonly SluggerInterface $slugger,
    ) {
    }

    #[Route('/new', name: 'app_profile_new', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function new(Request $request, ProfileRepository $profileRepository): Response
    {
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
    #[IsGranted('ROLE_ADMIN')]
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
    #[IsGranted('ROLE_ADMIN')]
    #[IsCsrfTokenValid('delete')]
    public function delete(Profile $profile, ProfileRepository $profileRepository): Response
    {
        $this->removePhoto($profile);
        $profileRepository->remove($profile, true);

        return $this->redirectToRoute('app_home', [], Response::HTTP_SEE_OTHER);
    }

    private function handlePhotoUpload(FormInterface $form, Profile $profile): void
    {
        $file = $form->get('photo')->getData();
        if (!$file instanceof UploadedFile) {
            return;
        }

        $original = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $newFilename = $this->slugger->slug($original)->lower().'-'.uniqid().'.'.$file->guessExtension();

        $file->move($this->photoDir, $newFilename);

        // remove the previous photo, if any
        $this->removePhoto($profile);

        $profile->setPhotoFilename($newFilename);
    }

    private function removePhoto(Profile $profile): void
    {
        $filename = $profile->getPhotoFilename();
        if ($filename && is_file($path = $this->photoDir.'/'.$filename)) {
            @unlink($path);
        }
    }
}
