<?php

namespace App\Form;

use App\Entity\SitePageTranslation;
use App\Repository\SitePageTranslationRepository;
use App\Service\SeoStructuredContentAdminMapper;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class SitePageTranslationType extends AbstractType
{
    public function __construct(
        private readonly SitePageTranslationRepository $translationRepository,
        private readonly SeoStructuredContentAdminMapper $seoMapper,
    )
    {
    }

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
        $translation = $options['data'] instanceof SitePageTranslation ? $options['data'] : null;
        // Migrated editorial pages keep their historical HTML alongside corePage JSON.
        $editorialLegacyHtml = $isEditorialPage
            && is_array($translation?->getStructuredData()['corePage'] ?? null)
            && str_starts_with(ltrim((string) $translation?->getBodyHtml()), '<');
        if ($editorialLegacyHtml) { $bodyLabel = 'Contenu historique (HTML)'; }

        $seoAdvanced = $this->seoMapper->landingNarrativeToFormData($translation?->getStructuredData());
        $originalTitle = $translation?->getTitle();
        $originalBasics = ['metaTitle' => $translation?->getTitle(), 'metaDescription' => $translation?->getSeoDescription(), 'heroTitle' => $translation?->getHeroTitle(), 'heroIntro' => $translation?->getHeroIntro()];

        $builder
            ->add('translationStatus', ChoiceType::class, [
                'label' => 'Statut FR',
                'choices' => [
                    'Brouillon' => SitePageTranslation::STATUS_DRAFT,
                    'Traduit IA' => SitePageTranslation::STATUS_AI_TRANSLATED,
                    'A relire' => SitePageTranslation::STATUS_TO_REVIEW,
                    'Relu' => SitePageTranslation::STATUS_REVIEWED,
                    'Publie' => SitePageTranslation::STATUS_PUBLISHED,
                ],
                'attr' => ['class' => 'form-select'],
            ])
            ->add('slug', TextType::class, [
                'label' => 'Slug',
                'attr' => ['class' => 'form-control'],
            ])
            ->add('title', TextType::class, [
                'label' => 'Titre (balise <title>)',
                'attr' => ['class' => 'form-control'],
            ])
            ->add('seoDescription', TextareaType::class, [
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
                'attr' => ['rows' => 12, 'class' => 'form-control js-wysiwyg'],
            ])
            ->add('bodyHtml', TextareaType::class, [
                'label' => $bodyLabel,
                'required' => false,
                'attr' => ['rows' => 12, 'class' => $bodyClass],
            ])
            ->add('publishedAt', DateTimeType::class, [
                'label' => 'Publie le',
                'required' => false,
                'widget' => 'single_text',
                'input' => 'datetime_immutable',
                'attr' => ['class' => 'form-control'],
            ])
            ->add('unpublishedAt', DateTimeType::class, [
                'label' => 'Depublie le',
                'required' => false,
                'widget' => 'single_text',
                'input' => 'datetime_immutable',
                'attr' => ['class' => 'form-control'],
            ])
            ->add('heroImage', TextType::class, [
                'label' => 'Image hero (chemin ou URL)',
                'mapped' => false,
                'required' => false,
                'data' => $options['hero_image'],
                'attr' => ['class' => 'form-control'],
            ])
            ->add('heroImageFile', FileType::class, [
                'label' => 'Televerser l\'image hero',
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new File([
                        'mimeTypes' => ['image/*'],
                        'mimeTypesMessage' => 'Veuillez envoyer une image valide.',
                    ]),
                ],
                'attr' => ['class' => 'form-control'],
            ]);

        if (!$isHomePage) { $this->addSeoAdvancedFields($builder, $seoAdvanced); }

        $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) use ($editorMode, $isHomePage, $isEditorialPage, $isSeoPage, $isStructuredPage, $editorialLegacyHtml, $seoAdvanced, $originalTitle, $originalBasics): void {
            $form = $event->getForm();
            $translation = $event->getData();
            if (!$translation instanceof SitePageTranslation) {
                return;
            }

            if ($translation->getTitle() !== $originalTitle) {
                $translation->setSeoTitle($translation->getTitle());
            }

            $existing = $this->translationRepository->findOneBy([
                'locale' => $translation->getLocale(),
                'slug' => $translation->getSlug(),
            ]);

            if ($existing instanceof SitePageTranslation && $existing->getId() !== $translation->getId()) {
                $form->get('slug')->addError(new FormError('Ce slug existe deja pour cette langue.'));
            }

            if ($form->has('seoAdvancedMetaTitle')) {
                $currentSourceHash = hash('sha256', json_encode($seoAdvanced, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR));
                if ($form->get('seoAdvancedSourceHash')->getData() !== $currentSourceHash) {
                    $form->get('seoAdvancedMetaTitle')->addError(new FormError('Le contenu SEO avance a ete modifie depuis l’ouverture du formulaire. Rechargez la page avant de sauvegarder.'));

                    return;
                }

                try {
                    $mergedStructured = $this->seoMapper->mergeLandingNarrative($translation->getStructuredData(), [
                        'metaTitle' => $form->get('seoAdvancedMetaTitle')->getData(),
                        'metaDescription' => $form->get('seoAdvancedMetaDescription')->getData(),
                        'heroTitle' => $form->get('seoAdvancedHeroTitle')->getData(),
                        'heroIntro' => $form->get('seoAdvancedHeroIntro')->getData(),
                        'promise' => $form->get('seoAdvancedPromise')->getData(),
                        'heroCtaLabel' => $form->get('seoAdvancedHeroCtaLabel')->getData(),
                        'missionPhases' => $form->get('seoAdvancedMissionPhases')->getData(),
                        'deliverables' => $form->get('seoAdvancedDeliverables')->getData(),
                        'projectContexts' => $form->get('seoAdvancedProjectContexts')->getData(),
                        'clientTypes' => $form->get('seoAdvancedClientTypes')->getData(),

                        'focus' => $form->get('seoAdvancedFocus')->getData(),
                        'citablePassages' => $form->get('seoAdvancedCitablePassages')->getData(),
                        'supportLinks' => $form->get('seoAdvancedSupportLinks')->getData(),
                    ]);
                    foreach (['metaTitle' => 'title', 'metaDescription' => 'seoDescription', 'heroTitle' => 'heroTitle', 'heroIntro' => 'heroIntro'] as $key => $baseField) {
                        $value = $form->get($baseField)->getData();
                        $advancedField = $form->get('seoAdvanced' . ucfirst($key));
                        $advancedValue = $advancedField->getData();
                        // Resources render the native fields: mirror only an explicit advanced edit.
                        if ($editorMode === 'resource' && $advancedField->isSubmitted()
                            && $advancedValue !== null && $advancedValue !== ''
                            && $advancedValue !== $seoAdvanced[$key] && $value === $originalBasics[$key]) {
                            $setter = 'set' . ucfirst($baseField);
                            $translation->{$setter}($advancedValue);
                            if ($key === 'metaTitle') { $translation->setSeoTitle($advancedValue); }
                        }
                        if ($seoAdvanced[$key] !== '' && $value !== $originalBasics[$key] && $form->get('seoAdvanced' . ucfirst($key))->getData() === $seoAdvanced[$key]) {
                            $mergedStructured['landingNarrative'][$key] = $value;
                        }
                    }
                    $translation->setStructuredData($mergedStructured);
                } catch (\InvalidArgumentException $exception) {
                    $form->get('seoAdvancedCitablePassages')->addError(new FormError($exception->getMessage()));
                }
            }

            if (!($isHomePage || $isEditorialPage || $isSeoPage || $isStructuredPage)) {
                return;
            }

            if ($editorialLegacyHtml) { return; }

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

    /**
     * @param array<string, mixed> $data
     */
    private function addSeoAdvancedFields(FormBuilderInterface $builder, array $data): void
    {
        foreach (['heroCtaLabel' => 'CTA principal', 'missionPhases' => 'Méthode — une étape par ligne', 'deliverables' => 'Livrables — un par ligne', 'projectContexts' => 'Contextes — un par ligne', 'clientTypes' => 'Interlocuteurs — un par ligne'] as $key => $label) {
            $builder->add('seoAdvanced'.ucfirst($key), TextareaType::class, ['mapped'=>false, 'required'=>false, 'label'=>$label, 'data'=>$data[$key] ?? '', 'attr'=>['class'=>'form-control','rows'=>$key==='heroCtaLabel' ? 1 : 4]]);
        }
        $builder
            ->add('seoAdvancedMetaTitle', TextType::class, [
                'label' => 'SEO avance - Title',
                'mapped' => false,
                'required' => false,
                'data' => $data['metaTitle'] ?? '',
                'attr' => ['class' => 'form-control'],
            ])
            ->add('seoAdvancedMetaDescription', TextareaType::class, [
                'label' => 'SEO avance - Meta description',
                'mapped' => false,
                'required' => false,
                'data' => $data['metaDescription'] ?? '',
                'attr' => ['rows' => 2, 'class' => 'form-control'],
            ])
            ->add('seoAdvancedHeroTitle', TextType::class, [
                'label' => 'SEO avance - H1 / hero title',
                'mapped' => false,
                'required' => false,
                'data' => $data['heroTitle'] ?? '',
                'attr' => ['class' => 'form-control'],
            ])
            ->add('seoAdvancedHeroIntro', TextareaType::class, [
                'label' => 'SEO avance - Introduction hero',
                'mapped' => false,
                'required' => false,
                'data' => $data['heroIntro'] ?? '',
                'attr' => ['rows' => 3, 'class' => 'form-control'],
            ])
            ->add('seoAdvancedPromise', TextareaType::class, [
                'label' => 'SEO avance - Promesse',
                'mapped' => false,
                'required' => false,
                'data' => $data['promise'] ?? '',
                'attr' => ['rows' => 3, 'class' => 'form-control'],
            ])
            ->add('seoAdvancedFocus', TextareaType::class, [
                'label' => 'SEO avance - Focus (un point par ligne)',
                'mapped' => false,
                'required' => false,
                'data' => $data['focus'] ?? '',
                'attr' => ['rows' => 5, 'class' => 'form-control'],
            ])
            ->add('seoAdvancedCitablePassages', TextareaType::class, [
                'label' => 'SEO avance - Passages citables (titre | texte)',
                'mapped' => false,
                'required' => false,
                'data' => $data['citablePassages'] ?? '',
                'attr' => ['rows' => 5, 'class' => 'form-control'],
            ])
            ->add('seoAdvancedSupportLinks', TextareaType::class, [
                'label' => 'SEO avance - Liens utiles (libelle | URL | description)',
                'mapped' => false,
                'required' => false,
                'data' => $data['supportLinks'] ?? '',
                'attr' => ['rows' => 5, 'class' => 'form-control'],
            ])
            ->add('seoAdvancedSourceHash', HiddenType::class, [
                'mapped' => false,
                'required' => false,
                'data' => hash('sha256', json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)),
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => SitePageTranslation::class,
            'editor_mode' => 'default',
            'hero_image' => null,
        ]);
    }
}
