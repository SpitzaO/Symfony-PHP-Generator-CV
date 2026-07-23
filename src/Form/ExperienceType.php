<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Experience;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ExperienceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('company', null, [
                'label' => 'Firma',
            ])
            ->add('position', null, [
                'label' => 'Stanowisko',
            ])
            ->add('location', null, [
                'label' => 'Lokalizacja',
                'required' => false,
                'attr' => ['placeholder' => 'np. Warszawa, Polska (lub zdalnie)'],
            ])
            ->add('description', null, [
                'label' => 'Opis',
            ])
            ->add('startDate', DateType::class, [
                'label' => 'Data rozpoczęcia',
                'widget' => 'single_text',
                'input' => 'datetime_immutable',
                'required' => false,
            ])
            ->add('endDate', DateType::class, [
                'label' => 'Data zakończenia',
                'widget' => 'single_text',
                'input' => 'datetime_immutable',
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Experience::class,
        ]);
    }
}
