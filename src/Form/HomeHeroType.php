<?php

namespace App\Form;

use App\Entity\Metier;
use App\Entity\SitePage;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class HomeHeroType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $ctaDefaults = $options['cta_defaults'];
        $selectedMetier = $options['selected_metier'];

        $builder
            ->add('eyebrow', TextType::class, [
                'label' => 'Libellé',
                'property_path' => 'heroBadge',
                'required' => false,
                'attr' => ['class' => 'form-control'],
            ])
            ->add('title', TextType::class, [
                'label' => 'Titre H1',
                'property_path' => 'heroTitle',
                'required' => false,
                'attr' => ['class' => 'form-control'],
            ])
            ->add('intro', TextareaType::class, [
                'label' => 'Sous-titre',
                'property_path' => 'heroIntro',
                'required' => false,
                'attr' => ['rows' => 3, 'class' => 'form-control js-wysiwyg'],
            ])
            ->add('tagsText', TextareaType::class, [
                'label' => 'Tags hero',
                'mapped' => false,
                'required' => false,
                'data' => $options['tags_default'] ?? null,
                'attr' => ['rows' => 3, 'class' => 'form-control'],
            ])
            ->add('ctaLabelSecondary', TextType::class, [
                'label' => 'Bouton hero - libellé',
                'mapped' => false,
                'data' => $ctaDefaults['ctaLabelSecondary'] ?? null,
                'required' => false,
                'attr' => ['class' => 'form-control'],
            ])
            ->add('ctaUrlSecondary', TextType::class, [
                'label' => 'Bouton hero - URL',
                'mapped' => false,
                'data' => $ctaDefaults['ctaUrlSecondary'] ?? null,
                'required' => false,
                'attr' => ['class' => 'form-control'],
            ])
            ->add('metier', EntityType::class, [
                'label' => 'Métier mis en avant',
                'class' => Metier::class,
                'choice_label' => 'designation',
                'mapped' => false,
                'data' => $selectedMetier,
                'required' => false,
                'placeholder' => 'Aucun métier',
                'attr' => ['class' => 'form-select'],
            ])
            ->add('metierIntro', TextareaType::class, [
                'label' => 'Intro métier',
                'mapped' => false,
                'required' => false,
                'data' => $options['metier_intro_default'] ?? null,
                'attr' => ['rows' => 3, 'class' => 'form-control js-wysiwyg'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => SitePage::class,
            'cta_defaults' => [],
            'tags_default' => null,
            'selected_metier' => null,
            'metier_intro_default' => null,
        ]);
    }
}
