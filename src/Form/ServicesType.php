<?php

namespace App\Form;

use App\Entity\Practice;
use App\Entity\Services;
use App\Entity\Team;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Validator\Constraints\File;

class ServicesType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('designation', TextType::class, [
                'label' => 'Désignation',
            ])
            ->add('designationShort', TextType::class, [
                'label' => 'Désignation courte',
                'required' => false,
            ])
            ->add('introductionShort', TextareaType::class, [
                'label' => 'Introduction courte',
                'required' => false,
                'attr' => ['rows' => 4],
            ])
            ->add('descriptionShort', TextareaType::class, [
                'label' => 'Description courte',
                'required' => false,
                'attr' => ['rows' => 4],
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'required' => false,
                'attr' => ['rows' => 6],
            ])
            ->add('practice', EntityType::class, [
                'label' => 'Practice',
                'class' => Practice::class,
                'choice_label' => 'designation',
                'required' => false,
                'placeholder' => 'Choisir une practice',
            ])
            ->add('ico', TextType::class, [
                'label' => 'Icône (classe CSS, ex: bi-diagram-3)',
                'required' => false,
            ])
            ->add('image1', TextType::class, [
                'label' => 'Image principale (chemin ou URL)',
                'required' => false,
            ])
            ->add('image1File', FileType::class, [
                'label' => 'Téléverser l\'image principale',
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
            ->add('image2', TextType::class, [
                'label' => 'Image secondaire (chemin ou URL)',
                'required' => false,
            ])
            ->add('image2File', FileType::class, [
                'label' => 'Téléverser l\'image secondaire',
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
            ->add('teams', EntityType::class, [
                'label' => 'Équipe associée',
                'class' => Team::class,
                'choice_label' => 'noncomplet',
                'multiple' => true,
                'required' => false,
                'by_reference' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Services::class,
        ]);
    }
}
