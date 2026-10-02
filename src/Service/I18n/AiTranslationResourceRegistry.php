<?php

namespace App\Service\I18n;

final class AiTranslationResourceRegistry
{
    public const EXECUTABLE_ENTITY = 'SitePage';

    public const SUPPORTED_ENTITIES = [
        'SitePage',
        'Practice',
        'Service',
        'Projet',
        'Team',
        'LegalPage',
        'HomeSection',
        'SiteGlobalContent',
        'ErpQuestionnaireJson',
        'AiConsultantJson',
    ];

    public const STRUCTURED_FIELDS = [
        'SitePage.structuredData.homePage',
        'SitePage.structuredData.corePage',
        'SitePage.structuredData.landingNarrative',
        'TeamTranslation.public_profile',
        'ServiceTranslation.public_narrative',
        'SiteGlobalContentTranslation.bodyHtml',
        'ErpQuestionnaireJson.questions',
        'AiConsultantJson.prompts',
    ];
}
