<?php

namespace App\Form;

use App\Entity\HomeCard;
use App\Entity\Metier;
use App\Entity\Projet;
use App\Entity\Services;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class HomeCardType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('service', EntityType::class, [
                'class' => Services::class,
                'choice_label' => 'designation',
                'required' => false,
                'placeholder' => '— Sélectionner un service —',
            ])
            ->add('projet', EntityType::class, [
                'class' => Projet::class,
                'choice_label' => 'designation',
                'required' => false,
                'placeholder' => '— Sélectionner un projet —',
            ])
            ->add('metier', EntityType::class, [
                'class' => Metier::class,
                'choice_label' => 'designation',
                'required' => false,
                'placeholder' => '— Sélectionner un métier —',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => HomeCard::class,
        ]);
    }
}
