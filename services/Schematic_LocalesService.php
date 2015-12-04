<?php

namespace Craft;

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
class Schematic_LocalesService extends Schematic_AbstractService
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

        foreach ($localeDefinitions as $locale) {
            $localeExists = $this->localeExists($locale);

            if (!$localeExists) {
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
     * Check if a locale exists.
     *
     * @param string $locale
     *
     * @return bool
     */
    private function localeExists($locale)
    {
        return craft()->db->createCommand()
            ->select('locale')
            ->from('locales')
            ->where('locale = :locale', array('locale' => $locale))
            ->queryScalar();
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
