<?php
namespace NerdsAndCompany\Schematic\Behaviors;

use Craft;
use yii\base\Behavior;
use craft\models\FieldLayout;

/**
 * Schematic FieldLayout Behavior.
 *
 * Sync Craft Setups.
 *
 * @author    Nerds & Company
 * @copyright Copyright (c) 2015-2018, Nerds & Company
 * @license   MIT
 *
 * @see      http://www.nerds.company
 */
class FieldLayoutBehavior extends Behavior
{
    /**
     * Get field layout definition.
     *
     * @param FieldLayout $fieldLayout
     *
     * @return array
     */
    public function getFieldLayoutDefinition(FieldLayout $fieldLayout)
    {
        if ($fieldLayout->getTabs()) {
            $tabDefinitions = [];

            foreach ($fieldLayout->getTabs() as $tab) {
                $tabDefinitions[$tab->name] = $this->getFieldLayoutFieldsDefinition($tab->getFields());
            }

            return ['tabs' => $tabDefinitions];
        }

        return ['fields' => $this->getFieldLayoutFieldsDefinition($fieldLayout->getFields())];
    }

    /**
     * Get field layout fields definition.
     *
     * @param FieldLayoutFieldModel[] $fields
     *
     * @return array
     */
    private function getFieldLayoutFieldsDefinition(array $fields)
    {
        $fieldDefinitions = [];

        foreach ($fields as $field) {
            $fieldDefinitions[$field->handle] = $field->required;
        }

        return $fieldDefinitions;
    }

    /**
     * Attempt to import a field layout.
     *
     * @param array $fieldLayoutDef
     *
     * @return FieldLayout
     */
    public function getFieldLayout(array $fieldLayoutDef)
    {
        $layoutFields = [];
        $requiredFields = [];

        if (array_key_exists('tabs', $fieldLayoutDef)) {
            foreach ($fieldLayoutDef['tabs'] as $tabName => $tabDef) {
                $layoutTabFields = $this->getPrepareFieldLayout($tabDef);
                $requiredFields = array_merge($requiredFields, $layoutTabFields['required']);
                $layoutFields[$tabName] = $layoutTabFields['fields'];
            }
        } elseif (array_key_exists('fields', $fieldLayoutDef)) {
            $layoutTabFields = $this->getPrepareFieldLayout($fieldLayoutDef);
            $requiredFields = $layoutTabFields['required'];
            $layoutFields = $layoutTabFields['fields'];
        }

        $fieldLayout = Craft::$app->fields->assembleLayout($layoutFields, $requiredFields);
        $fieldLayout->type = ElementType::Entry;

        return $fieldLayout;
    }

    /**
     * Get a prepared fieldLayout for the craft assembleLayout function.
     *
     * @param array $fieldLayoutDef
     *
     * @return array
     */
    private function getPrepareFieldLayout(array $fieldLayoutDef)
    {
        $layoutFields = [];
        $requiredFields = [];

        foreach ($fieldLayoutDef as $fieldHandle => $required) {
            $field = Craft::$app->fields->getFieldByHandle($fieldHandle);
            if ($field instanceof FieldModel) {
                $layoutFields[] = $field->id;

                if ($required) {
                    $requiredFields[] = $field->id;
                }
            }
        }

        return [
          'fields' => $layoutFields,
          'required' => $requiredFields,
        ];
    }
}
