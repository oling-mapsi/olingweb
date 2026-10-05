<?php

namespace App\Form;

use App\Entity\GrowthContent;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class GrowthContentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, ['label' => 'Titre'])
            ->add('slug', TextType::class, ['label' => 'Slug public'])
            ->add('excerpt', TextareaType::class, ['label' => 'Extrait', 'attr' => ['rows' => 3]])
            ->add('contentHtml', TextareaType::class, ['label' => 'Contenu HTML', 'attr' => ['rows' => 12, 'class' => 'js-wysiwyg']])
            ->add('metaTitle', TextType::class, ['label' => 'Meta title'])
            ->add('metaDescription', TextareaType::class, ['label' => 'Meta description', 'attr' => ['rows' => 3]])
            ->add('featuredImage', TextType::class, ['label' => 'Image', 'required' => false])
            ->add('authorDisplayName', TextType::class, ['label' => 'Auteur'])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => GrowthContent::class]);
    }
}
