<?php

namespace App\Form;

use App\Entity\Services;
use App\Entity\Team;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class TeamType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('noncomplet', TextType::class, [
                'label' => 'Nom complet',
            ])
            ->add('titre', TextType::class, [
                'label' => 'Titre',
                'required' => false,
            ])
            ->add('shortcv', TextareaType::class, [
                'label' => 'Bio courte',
                'required' => false,
                'attr' => ['rows' => 5],
            ])
            ->add('linkedin', TextType::class, [
                'label' => 'LinkedIn',
                'required' => false,
            ])
            ->add('photo', TextType::class, [
                'label' => 'Photo (chemin ou URL)',
                'required' => false,
            ])
            ->add('photoFile', FileType::class, [
                'label' => 'Téléverser une photo',
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
            ->add('services', EntityType::class, [
                'label' => 'Services associés',
                'class' => Services::class,
                'choice_label' => 'designation',
                'multiple' => true,
                'required' => false,
                'by_reference' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Team::class,
        ]);
    }
}
