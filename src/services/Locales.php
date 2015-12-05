<?php

namespace NerdsAndCompany\Schematic\Services;

use Craft\Craft;

/**
 * Schematic Locales Service.
 *
 * Sync Craft Setups.
 *
 * @author    Nerds & Company
 * @copyright Copyright (c) 2015, Nerds & Company
 * @license   MIT
 *
 * @link      http://www.nerds.company
 */
class Locales extends Base
{
    /**
     * @return LocalizationService
     */
    protected function getLocalizationService()
    {
        return craft()->i18n;
    }

    /**
     * @param array $localeDefinitions
     * @param bool  $force
     *
     * @return Schematic_ResultModel
     */
    public function import(array $localeDefinitions, $force = false)
    {
        Craft::log(Craft::t('Importing Locales'));

        // Get existing locales
        $locales = $this->getLocalizationService()->getSiteLocaleIds();

        foreach ($localeDefinitions as $locale) {
            if (!in_array($locale, $locales)) {
                if (!$this->getLocalizationService()->addSiteLocale($locale)) {
                    $this->addError(Craft::t('Locale {locale} could not be installed', array('locale' => $locale)));
                }
            }
        }

        // Re-order locales by definition
        $this->getLocalizationService()->reorderSiteLocales($localeDefinitions);

        return $this->getResultModel();
    }

    /**
     * @param array $data
     *
     * @return array
     */
    public function export(array $data = array())
    {
        Craft::log(Craft::t('Exporting Locales'));

        $locales = $this->getLocalizationService()->getSiteLocales();
        $localeDefinitions = array();

        foreach ($locales as $locale) {
            $localeDefinitions[] = $locale->getId();
        }

        return $localeDefinitions;
    }
}
