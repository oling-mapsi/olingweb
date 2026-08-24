<?php

namespace App\Form;

use App\Entity\SitePage;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class SitePageType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $editorMode = (string) ($options['editor_mode'] ?? 'default');
        $isHomePage = $editorMode === 'home';
        $isEditorialPage = $editorMode === 'editorial';
        $isSeoPage = $editorMode === 'seo';
        $isStructuredPage = $editorMode === 'structured';
        $bodyLabel = $isHomePage
            ? 'Configuration home (JSON)'
            : ($isEditorialPage ? 'Configuration editoriale (JSON)' : ($isSeoPage ? 'FAQ (JSON)' : ($isStructuredPage ? 'Configuration structuree (JSON)' : 'Contenu principal (HTML)')));
        $bodyClass = ($isHomePage || $isEditorialPage || $isSeoPage || $isStructuredPage) ? 'form-control' : 'form-control js-wysiwyg';
        $heroSideLabel = $isSeoPage ? 'Contenu principal (HTML)' : 'Bloc hero droit (HTML)';
        $heroSideClass = 'form-control js-wysiwyg';

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
                'label' => $heroSideLabel,
                'required' => false,
                'attr' => ['rows' => 12, 'class' => $heroSideClass],
            ])
            ->add('bodyHtml', TextareaType::class, [
                'label' => $bodyLabel,
                'required' => false,
                'attr' => ['rows' => 12, 'class' => $bodyClass],
            ])
            ->add('heroImage', TextType::class, [
                'label' => 'Image hero (chemin ou URL)',
                'required' => false,
                'attr' => ['class' => 'form-control'],
            ])
            ->add('heroImageFile', FileType::class, [
                'label' => 'Téléverser l\'image hero',
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new File([
                        'mimeTypes' => ['image/*'],
                        'mimeTypesMessage' => 'Veuillez envoyer une image valide.',
                    ]),
                ],
                'attr' => ['class' => 'form-control'],
            ])
        ;

        if ($isHomePage || $isEditorialPage || $isSeoPage || $isStructuredPage) {
            $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event): void {
                $form = $event->getForm();
                $bodyHtml = (string) $form->get('bodyHtml')->getData();

                if (trim($bodyHtml) === '') {
                    return;
                }

                try {
                    $decoded = json_decode($bodyHtml, true, 512, JSON_THROW_ON_ERROR);
                } catch (\JsonException $exception) {
                    $form->get('bodyHtml')->addError(new FormError(sprintf('JSON invalide : %s', $exception->getMessage())));

                    return;
                }

                if (!is_array($decoded)) {
                    $form->get('bodyHtml')->addError(new FormError('Le JSON doit contenir un objet ou un tableau.'));
                }
            });
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => SitePage::class,
            'editor_mode' => 'default',
        ]);
    }
}
