<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Profile;
use App\Service\ProfilePhotoStorage;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Image;

class ProfileType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('firstName', null, [
                'label' => 'Imię',
            ])
            ->add('lastName', null, [
                'label' => 'Nazwisko',
            ])
            ->add('email', null, [
                'label' => 'E-mail',
            ])
            ->add('phone', null, [
                'label' => 'Telefon',
            ])
            ->add('birthDate', DateType::class, [
                'label' => 'Data urodzenia',
                'widget' => 'single_text',
                'input' => 'datetime_immutable',
            ])
            ->add('about', null, [
                'label' => 'O mnie',
            ])
            ->add('photo', FileType::class, [
                'label' => 'Zdjęcie',
                'mapped' => false,
                'required' => false,
                'help' => 'JPG, PNG lub GIF, maksymalnie 2 MB.',
                'constraints' => [
                    // Constrained to the formats the PDF export can embed. Without this
                    // an Image constraint accepts anything matching "image/*", so a WebP
                    // would show on the site and then quietly vanish from the PDF.
                    new Image(
                        maxSize: '2M',
                        extensions: ProfilePhotoStorage::ALLOWED_EXTENSIONS,
                        detectCorrupted: true,
                    ),
                ],
            ])
            ->add('interests', CollectionType::class, [
                'entry_type' => InterestType::class,
                'entry_options' => ['label' => false],
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'label' => false,
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Profile::class,
        ]);
    }
}
