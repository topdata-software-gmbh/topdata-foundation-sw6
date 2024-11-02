<?php

namespace Topdata\TopdataFoundationSW6\Service;
use Doctrine\DBAL\Connection;
use Shopware\Core\Defaults;

/**
 * 10/2024 created
 * 11/2024 moved from TopdataWebserviceConnector to TopdataFoundation
 */
class LocaleHelperService
{
    /**
     * eg "de-DE"
     */
    private ?string $systemDefaultLocaleCode = null;

    public function __construct(
        private readonly Connection $connection
    )
    {
    }


    /**
     * 10/2024 extracted from multiple services [duplicate code]
     * 11/2024 added caching
     *
     * Returns the locale code of the system language
     *
     * The locale code consists of:
     * - Language code (e.g., "de" for German)
     * - Country/region code (e.g., "DE" for Germany)
     *
     * @return string The locale code, eg "de-DE"
     */
    public function getLocaleCodeOfSystemLanguage(): string
    {
        if(empty($this->systemDefaultLocaleCode))
        {
            return $this->systemDefaultLocaleCode =  $this->connection->fetchOne('
                    SELECT lo.code 
                        FROM language as la 
                        JOIN locale as lo on lo.id = la.locale_id  
                        WHERE la.id = UNHEX(:systemLanguageId)
                ', ['systemLanguageId' => Defaults::LANGUAGE_SYSTEM]);
        }

        return $this->systemDefaultLocaleCode;
    }

}