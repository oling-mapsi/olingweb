<?php

namespace App\Form;

use App\Entity\Metier;
use App\Entity\Projet;
use App\Entity\Services;
use App\Entity\Team;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class ProjetType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('designation', TextType::class, [
                'label' => 'Désignation',
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'required' => false,
                'attr' => ['rows' => 6],
            ])
            ->add('class', TextType::class, [
                'label' => 'Classe CSS',
                'required' => false,
            ])
            ->add('metier', EntityType::class, [
                'label' => 'Métier',
                'class' => Metier::class,
                'choice_label' => 'designation',
                'required' => false,
                'placeholder' => 'Choisir un métier',
            ])
            ->add('services', EntityType::class, [
                'label' => 'Services associés',
                'class' => Services::class,
                'choice_label' => 'designation',
                'multiple' => true,
                'required' => false,
                'by_reference' => false,
            ])
            ->add('teams', EntityType::class, [
                'label' => 'Collaborateurs associés',
                'class' => Team::class,
                'choice_label' => 'noncomplet',
                'multiple' => true,
                'required' => false,
                'by_reference' => false,
            ])
            ->add('featuredProjects', CheckboxType::class, [
                'label' => 'Mettre en avant sur la page projets (6 max)',
                'required' => false,
            ])
            ->add('image', TextType::class, [
                'label' => 'Image (chemin ou URL)',
                'required' => false,
            ])
            ->add('imageFile', FileType::class, [
                'label' => 'Téléverser une image',
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '5M',
                        'mimeTypes' => ['image/*'],
                        'mimeTypesMessage' => 'Veuillez envoyer une image valide.',
                    ]),
                ],
            ])
            ->add('imageHero', TextType::class, [
                'label' => 'Image recadrée pour les cartes hero (chemin ou URL)',
                'required' => false,
            ])
            ->add('imageHeroFile', FileType::class, [
                'label' => 'Téléverser l\'image recadrée (cartes hero)',
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '5M',
                        'mimeTypes' => ['image/*'],
                        'mimeTypesMessage' => 'Veuillez envoyer une image valide.',
                    ]),
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Projet::class,
        ]);
    }
}
