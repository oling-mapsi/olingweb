<?php

namespace App\Form;

use App\Service\SeoStructuredContentAdminMapper;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

final class SeoNarrativeAdminFields
{
    public function __construct(private readonly SeoStructuredContentAdminMapper $mapper) {}

    public function add(FormBuilderInterface $builder, ?array $narrative, string $kind = 'service'): void
    {
        $values = $kind === 'project' ? $this->mapper->projectNarrativeToFormData($narrative) : $this->mapper->serviceNarrativeToFormData($narrative);
        $help = ['missionPhases' => 'Une étape par ligne.', 'deliverables' => 'Un livrable par ligne.', 'projectContexts' => 'Un contexte par ligne.', 'clientTypes' => 'Un type d’interlocuteur par ligne.', 'focus' => 'Un point par ligne.', 'citablePassages' => 'Titre | texte, un passage par ligne.', 'supportLinks' => 'Libellé | URL | description, un lien par ligne.'];
        foreach ($values as $key => $value) {
            $builder->add('seoAdvanced' . ucfirst($key), TextareaType::class, [
                'mapped' => false, 'required' => false, 'label' => $key,
                'data' => $value, 'help' => $help[$key] ?? null,
                'attr' => ['class' => 'form-control', 'rows' => isset($help[$key]) ? 5 : 2],
            ]);
        }
        $hash = hash('sha256', json_encode($values, JSON_THROW_ON_ERROR));
        $builder->add('seoAdvancedSourceHash', HiddenType::class, ['mapped' => false, 'data' => $hash]);
        $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) use ($narrative, $values, $hash, $kind): void {
            $form = $event->getForm();
            if (!hash_equals($hash, (string) $form->get('seoAdvancedSourceHash')->getData())) {
                $form->addError(new FormError('Le contenu a changé. Rechargez le formulaire.'));
                return;
            }
            $data = $event->getData();
            if (!is_object($data) || !is_callable([$data, 'setPublicNarrative'])) {
                return;
            }
            $submitted = [];
            foreach ($values as $key => $_) {
                $submitted[$key] = $form->get('seoAdvanced' . ucfirst($key))->getData();
            }
            try {
                $merged = $kind === 'project' ? $this->mapper->mergeProjectNarrative($narrative, $submitted) : $this->mapper->mergeServiceNarrative($narrative, $submitted);
                $data->setPublicNarrative($merged === [] && $narrative === null ? null : $merged);
            } catch (\InvalidArgumentException $e) {
                $form->addError(new FormError($e->getMessage()));
            }
        });
    }
}
