<?php

namespace App\Form;

use App\Entity\SitePage;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SitePageType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('slug', TextType::class, [
                'label' => 'Slug',
                'disabled' => true,
                'attr' => ['class' => 'form-control'],
            ])
            ->add('title', TextType::class, [
                'label' => 'Titre (balise <title>)',
                'attr' => ['class' => 'form-control'],
            ])
            ->add('metaDescription', TextareaType::class, [
                'label' => 'Meta description',
                'required' => false,
                'attr' => ['rows' => 2, 'class' => 'form-control'],
            ])
            ->add('heroBadge', TextType::class, [
                'label' => 'Badge (hero)',
                'required' => false,
                'attr' => ['class' => 'form-control'],
            ])
            ->add('heroTitle', TextType::class, [
                'label' => 'Titre hero',
                'required' => false,
                'attr' => ['class' => 'form-control'],
            ])
            ->add('heroIntro', TextareaType::class, [
                'label' => 'Intro hero',
                'required' => false,
                'attr' => ['rows' => 3, 'class' => 'form-control js-wysiwyg'],
            ])
            ->add('heroSideHtml', TextareaType::class, [
                'label' => 'Bloc hero droit (HTML)',
                'required' => false,
                'attr' => ['rows' => 8, 'class' => 'form-control js-wysiwyg'],
            ])
            ->add('bodyHtml', TextareaType::class, [
                'label' => 'Contenu principal (HTML)',
                'required' => false,
                'attr' => ['rows' => 12, 'class' => 'form-control js-wysiwyg'],
            ])
            ->add('heroImage', TextType::class, [
                'label' => 'Image hero (chemin ou URL)',
                'required' => false,
                'attr' => ['class' => 'form-control'],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => SitePage::class,
        ]);
    }
}
