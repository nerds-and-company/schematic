<?php

namespace NerdsAndCompany\Schematic\Services;

use Craft;
use craft\elements\User;
use yii\base\Component as BaseComponent;
use NerdsAndCompany\Schematic\Behaviors\FieldLayoutBehavior;
use NerdsAndCompany\Schematic\Interfaces\MappingInterface;

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
     * @param array $user_settings
     * @param bool  $force         If set to true user settings not included in the import will be deleted
     *
     * @return Result
     */
    public function import($force = true, array $user_settings = null)
    {
        Craft::info('Importing Users', 'schematic');

        // always delete existing fieldlayout first
        Craft::$app->fields->deleteLayoutsByType(ElementType::User);

        if (isset($user_settings['fieldLayout'])) {
            $fieldLayoutDefinition = (array) $user_settings['fieldLayout'];
        } else {
            $fieldLayoutDefinition = [];
        }

        $fieldLayout = Craft::$app->schematic_fields->getFieldLayout($fieldLayoutDefinition);
        $fieldLayout->type = ElementType::User;

        if (!Craft::$app->fields->saveLayout($fieldLayout)) {  // Save fieldlayout via craft
            $this->addErrors($fieldLayout->getAllErrors());
        }

        return $this->getResultModel();
    }
}
