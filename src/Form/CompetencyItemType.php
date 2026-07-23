<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\CompetencyItem;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CompetencyItemType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => false,
                'attr' => ['placeholder' => 'np. Achiever'],
            ])
            ->add('description', TextType::class, [
                'label' => false,
                'required' => false,
                'attr' => ['placeholder' => 'Krótki opis (opcjonalnie)'],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => CompetencyItem::class,
        ]);
    }
}
