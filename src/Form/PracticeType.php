<?php

namespace App\Form;

use App\Entity\Practice;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class PracticeType extends AbstractType
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
            ->add('h1Title', TextType::class, [
                'label' => 'Titre H1 (page practice)',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Titre H1 dédié (sinon Désignation)',
                ],
            ])
            ->add('introduction', TextareaType::class, [
                'label' => 'Introduction',
                'required' => false,
                'attr' => ['rows' => 4],
            ])
            ->add('introductionShort', TextareaType::class, [
                'label' => 'Introduction courte',
                'required' => false,
                'attr' => ['rows' => 4],
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'required' => false,
                'attr' => ['rows' => 6],
            ])
            ->add('descriptionShort', TextareaType::class, [
                'label' => 'Description courte',
                'required' => false,
                'attr' => ['rows' => 4],
            ])
            ->add('tags', TextType::class, [
                'label' => 'Tags / Hashtags',
                'required' => false,
                'attr' => [
                    'placeholder' => '#AMOA #SI #ERP',
                    'class' => 'js-tags-input',
                ],
                'help' => 'Sépare par des espaces ou des virgules. Exemple : #AMOA #SI #ERP',
            ])
            ->add('featuredHome', CheckboxType::class, [
                'label' => 'Afficher sur la home (cards sous le hero)',
                'required' => false,
            ])
            ->add('class1', TextType::class, [
                'label' => 'Classe CSS',
                'required' => false,
            ])
            ->add('color', TextType::class, [
                'label' => 'Couleur',
                'required' => false,
            ])
            ->add('ico', TextType::class, [
                'label' => 'Icône (classe CSS, ex: bi-diagram-3)',
                'required' => false,
            ])
            ->add('image1', TextType::class, [
                'label' => 'Image 1 (chemin ou URL)',
                'required' => false,
            ])
            ->add('image1File', FileType::class, [
                'label' => 'Téléverser l\'image 1',
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
                'label' => 'Image 2 (chemin ou URL)',
                'required' => false,
            ])
            ->add('image2File', FileType::class, [
                'label' => 'Téléverser l\'image 2',
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

        $builder->get('tags')->addModelTransformer(new CallbackTransformer(
            function ($tagsArray): string {
                if (!$tagsArray) {
                    return '';
                }
                $tags = [];
                foreach ($tagsArray as $tag) {
                    $tag = trim((string) $tag);
                    if ($tag === '') {
                        continue;
                    }
                    $tags[] = str_starts_with($tag, '#') ? $tag : '#' . $tag;
                }
                return implode(' ', array_values(array_unique($tags)));
            },
            function ($tagsString): array {
                if (!$tagsString) {
                    return [];
                }
                $parts = preg_split('/[\\s,;]+/', trim((string) $tagsString));
                $parts = array_filter($parts, fn ($tag) => $tag !== '');
                $clean = [];
                foreach ($parts as $tag) {
                    $tag = trim((string) $tag);
                    if ($tag === '') {
                        continue;
                    }
                    $clean[] = str_starts_with($tag, '#') ? $tag : '#' . $tag;
                }
                return array_values(array_unique($clean));
            }
        ));
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Practice::class,
        ]);
    }
}
