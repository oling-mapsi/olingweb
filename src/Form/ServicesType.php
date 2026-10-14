<?php

namespace App\Form;

use App\Entity\Practice;
use App\Entity\Services;
use App\Entity\Team;
use App\Service\SeoStructuredContentAdminMapper;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Validator\Constraints\File;

class ServicesType extends AbstractType
{
    public function __construct(private readonly SeoStructuredContentAdminMapper $seoMapper)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $publicNarrative = is_object($options['data'] ?? null) && is_callable([$options['data'], 'getPublicNarrative'])
            ? $options['data']->getPublicNarrative()
            : null;
        $seoAdvanced = $this->seoMapper->serviceNarrativeToFormData(is_array($publicNarrative) ? $publicNarrative : null);

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
            ->add('seoAdvancedMetaTitle', TextType::class, [
                'label' => 'SEO avance - Title',
                'mapped' => false,
                'required' => false,
                'data' => $seoAdvanced['metaTitle'] ?? '',
                'attr' => ['class' => 'form-control'],
            ])
            ->add('seoAdvancedMetaDescription', TextareaType::class, [
                'label' => 'SEO avance - Meta description',
                'mapped' => false,
                'required' => false,
                'data' => $seoAdvanced['metaDescription'] ?? '',
                'attr' => ['rows' => 2],
            ])
            ->add('seoAdvancedEyebrow', TextType::class, [
                'label' => 'SEO avance - Eyebrow',
                'mapped' => false,
                'required' => false,
                'data' => $seoAdvanced['eyebrow'] ?? '',
                'attr' => ['class' => 'form-control'],
            ])
            ->add('seoAdvancedHeadline', TextType::class, [
                'label' => 'SEO avance - H1 / headline',
                'mapped' => false,
                'required' => false,
                'data' => $seoAdvanced['headline'] ?? '',
                'attr' => ['class' => 'form-control'],
            ])
            ->add('seoAdvancedIntro', TextareaType::class, [
                'label' => 'SEO avance - Introduction',
                'mapped' => false,
                'required' => false,
                'data' => $seoAdvanced['intro'] ?? '',
                'attr' => ['rows' => 3],
            ])
            ->add('seoAdvancedPromise', TextareaType::class, [
                'label' => 'SEO avance - Promesse',
                'mapped' => false,
                'required' => false,
                'data' => $seoAdvanced['promise'] ?? '',
                'attr' => ['rows' => 3],
            ])
            ->add('seoAdvancedSourceHash', HiddenType::class, [
                'mapped' => false,
                'required' => false,
                'data' => hash('sha256', json_encode($seoAdvanced, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)),
            ])
        ;

        $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) use ($publicNarrative, $seoAdvanced): void {
            $form = $event->getForm();
            $data = $event->getData();
            if (!is_object($data) || !is_callable([$data, 'setPublicNarrative'])) {
                return;
            }

            $currentSourceHash = hash('sha256', json_encode($seoAdvanced, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR));
            if ($form->get('seoAdvancedSourceHash')->getData() !== $currentSourceHash) {
                $form->get('seoAdvancedMetaTitle')->addError(new \Symfony\Component\Form\FormError('Le contenu SEO avance a ete modifie depuis l’ouverture du formulaire. Rechargez la page avant de sauvegarder.'));

                return;
            }

            $mergedNarrative = $this->seoMapper->mergeServiceNarrative(is_array($publicNarrative) ? $publicNarrative : null, [
                'metaTitle' => $form->get('seoAdvancedMetaTitle')->getData(),
                'metaDescription' => $form->get('seoAdvancedMetaDescription')->getData(),
                'eyebrow' => $form->get('seoAdvancedEyebrow')->getData(),
                'headline' => $form->get('seoAdvancedHeadline')->getData(),
                'intro' => $form->get('seoAdvancedIntro')->getData(),
                'promise' => $form->get('seoAdvancedPromise')->getData(),
            ]);
            $data->setPublicNarrative($mergedNarrative === [] && !is_array($publicNarrative) ? null : $mergedNarrative);
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Services::class,
        ]);
    }
}
