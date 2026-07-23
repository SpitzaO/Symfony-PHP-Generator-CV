<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Interest;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class InterestType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            // No explicit label: standalone forms get the default one, while the
            // collection inside ProfileType passes 'label' => false via entry_options.
            ->add('name', TextType::class, [
                'label' => 'Nazwa',
                'attr' => ['placeholder' => 'np. Fotografia'],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Interest::class,
        ]);
    }
}
