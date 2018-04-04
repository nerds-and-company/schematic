<?php

namespace NerdsAndCompany\Schematic\Services;

use Craft;
use craft\elements\User;
use yii\base\Component as BaseComponent;
use NerdsAndCompany\Schematic\Behaviors\FieldLayoutBehavior;
use NerdsAndCompany\Schematic\Interfaces\MappingInterface;
use NerdsAndCompany\Schematic\Schematic;

/**
 * Schematic Users Service.
 *
 * Sync Craft Setups.
 *
 * @author    Nerds & Company
 * @copyright Copyright (c) 2015-2018, Nerds & Company
 * @license   MIT
 *
 * @see      http://www.nerds.company
 */
class Users extends BaseComponent implements MappingInterface
{
    /**
     * Load fieldlayout behavior
     *
     * @return array
     */
    public function behaviors()
    {
        return [
          FieldLayoutBehavior::className(),
        ];
    }

    //==============================================================================================================
    //================================================  EXPORT  ====================================================
    //==============================================================================================================

    /**
     * Export user settings
     *
     * @return array
     */
    public function export()
    {
        $settings = Craft::$app->getSystemSettings()->getSettings('users');
        $fieldLayout = Craft::$app->getFields()->getLayoutByType(User::class);
        return [
            'settings' => $settings,
            'fieldLayout' => $this->getFieldLayoutDefinition($fieldLayout),
        ];
    }

    //==============================================================================================================
    //================================================  IMPORT  ====================================================
    //==============================================================================================================

    /**
     * Attempt to import user settings.
     *
     * @param array $userSettings
     * @param bool  $force         If set to true user settings not included in the import will be deleted
     */
    public function import(array $userSettings, $force = true)
    {
        // always delete existing fieldlayout first
        Craft::$app->fields->deleteLayoutsByType(User::class);

        if (isset($userSettings['fieldLayout'])) {
            $fieldLayoutDefinition = (array) $userSettings['fieldLayout'];
        } else {
            $fieldLayoutDefinition = [];
        }

        $fieldLayout = Craft::$app->schematic_fields->getFieldLayout($fieldLayoutDefinition);
        $fieldLayout->type = User::class;

        if (!Craft::$app->fields->saveLayout($fieldLayout)) {  // Save fieldlayout via craft
            $this->addErrors($fieldLayout->getAllErrors());
        }
    }
}
