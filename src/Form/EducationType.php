<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Education;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EducationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('schoolName', null, [
                'label' => 'Nazwa szkoły',
            ])
            ->add('degree', null, [
                'label' => 'Kierunek',
            ])
            ->add('location', null, [
                'label' => 'Lokalizacja',
                'required' => false,
                'attr' => ['placeholder' => 'np. Kraków, Polska'],
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
            'data_class' => Education::class,
        ]);
    }
}
