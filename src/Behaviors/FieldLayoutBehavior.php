<?php

namespace NerdsAndCompany\Schematic\Behaviors;

use Craft;
use yii\base\Behavior;
use craft\base\Field;
use craft\base\FieldInterface;
use craft\models\FieldLayout;
use craft\elements\Entry;

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
    public function getFieldLayoutDefinition(FieldLayout $fieldLayout): array
    {
        if ($fieldLayout->getTabs()) {
            $tabDefinitions = [];

            foreach ($fieldLayout->getTabs() as $tab) {
                $tabDefinitions[$tab->name] = $this->getFieldLayoutFieldsDefinition($tab->getFields());
            }

            return [
                'type' => $fieldLayout->type,
                'tabs' => $tabDefinitions
            ];
        }

        return [
            'type' => $fieldLayout->type,
            'fields' => $this->getFieldLayoutFieldsDefinition($fieldLayout->getFields()),
        ];
    }

    /**
     * Get field layout fields definition.
     *
     * @param FieldInterface[] $fields
     *
     * @return array
     */
    private function getFieldLayoutFieldsDefinition(array $fields): array
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
    public function getFieldLayout(array $fieldLayoutDef): FieldLayout
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

        if (array_key_exists('type', $fieldLayoutDef)) {
            $fieldLayout->type = $fieldLayoutDef['type'];
        } else {
            $fieldLayout->type = Entry::class;
        }

        return $fieldLayout;
    }

    /**
     * Get a prepared fieldLayout for the craft assembleLayout function.
     *
     * @param array $fieldLayoutDef
     *
     * @return array
     */
    private function getPrepareFieldLayout(array $fieldLayoutDef): array
    {
        $layoutFields = [];
        $requiredFields = [];

        foreach ($fieldLayoutDef as $fieldHandle => $required) {
            $field = Craft::$app->fields->getFieldByHandle($fieldHandle);
            if ($field instanceof Field) {
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
