<?php

namespace App\Form;

use App\Entity\HomeSection;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class HomeHeroType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Titre H1',
                'required' => false,
            ])
            ->add('intro', TextareaType::class, [
                'label' => 'Sous-titre',
                'required' => false,
                'attr' => ['rows' => 3, 'class' => 'form-control js-wysiwyg'],
            ])
            ->add('ctaLabel', TextType::class, [
                'label' => 'Bouton principal - libellé',
                'required' => false,
            ])
            ->add('ctaUrl', TextType::class, [
                'label' => 'Bouton principal - URL',
                'required' => false,
            ])
            ->add('ctaLabelSecondary', TextType::class, [
                'label' => 'Bouton secondaire - libellé',
                'required' => false,
            ])
            ->add('ctaUrlSecondary', TextType::class, [
                'label' => 'Bouton secondaire - URL',
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => HomeSection::class,
        ]);
    }
}
