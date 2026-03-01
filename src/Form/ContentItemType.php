<?php

namespace App\Form;

use App\Entity\ContentItem;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class ContentItemType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Titre',
            ])
            ->add('body', TextareaType::class, [
                'label' => 'Texte',
                'required' => false,
                'attr' => [
                    'rows' => 8,
                ],
            ])
            ->add('imageFile', FileType::class, [
                'label' => 'Image',
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '5M',
                        'mimeTypes' => [
                            'image/*',
                        ],
                        'mimeTypesMessage' => 'Veuillez envoyer une image valide.',
                    ]),
                ],
            ])
            ->add('imageHero', TextType::class, [
                'label' => 'Image recadrée (chemin ou URL)',
                'required' => false,
            ])
            ->add('imageHeroFile', FileType::class, [
                'label' => 'Téléverser l\'image recadrée',
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '5M',
                        'mimeTypes' => [
                            'image/*',
                        ],
                        'mimeTypesMessage' => 'Veuillez envoyer une image valide.',
                    ]),
                ],
            ])
            ->add('iconFile', FileType::class, [
                'label' => 'Icône',
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '2M',
                        'mimeTypes' => [
                            'image/*',
                            'image/svg+xml',
                        ],
                        'mimeTypesMessage' => 'Veuillez envoyer une icône valide (PNG, SVG, etc.).',
                    ]),
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ContentItem::class,
        ]);
    }
}
