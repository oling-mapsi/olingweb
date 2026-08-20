<?php

namespace App\Form;

use App\Entity\HomeSection;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class HomeSectionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('eyebrow', TextType::class, [
                'label' => 'Libellé',
                'required' => false,
                'attr' => ['class' => 'form-control'],
            ])
            ->add('title', TextType::class, [
                'label' => 'Titre de section',
                'required' => false,
                'attr' => ['class' => 'form-control'],
            ])
            ->add('intro', TextareaType::class, [
                'label' => 'Texte d’intro',
                'required' => false,
                'attr' => ['rows' => 4, 'class' => 'form-control js-wysiwyg'],
            ])
            ->add('ctaLabel', TextType::class, [
                'label' => 'Bouton - libellé',
                'required' => false,
                'attr' => ['class' => 'form-control'],
            ])
            ->add('ctaUrl', TextType::class, [
                'label' => 'Bouton - URL',
                'required' => false,
                'attr' => ['class' => 'form-control'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => HomeSection::class,
        ]);
    }
}
