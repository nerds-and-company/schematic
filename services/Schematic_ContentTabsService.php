<?php

namespace Craft;

class Schematic_ContentTabsService extends BaseApplicationComponent
{
    public function export($section, $entryType, $tabName)
    {
        $entryTypeDefs = array();

        $fieldnames = array();

        foreach ($this->_exportFieldLayout($entryType->getFieldLayout()) as $contenttype) {
            foreach ($contenttype as $key => $value) {
                if ($key == $tabName) {
                    $entryTypeDefs[$key] = $value;
                    foreach ($value as $fieldkey => $fieldvalue) {
                        $fieldnames[] = $fieldkey;
                    }
                }
            }
        }

        return $entryTypeDefs;
    }

    private function _exportFieldLayout(FieldLayoutModel $fieldLayout)
    {
        if ($fieldLayout->getTabs()) {
            $tabDefs = array();

            foreach ($fieldLayout->getTabs() as $tab) {
                $tabDefs[$tab->name] = array();

                foreach ($tab->getFields() as $field) {
                    $tabDefs[$tab->name][$field->getField()->handle] = $field->required;
                }
            }

            return array(
                'tabs' => $tabDefs,
            );
        } else {
            $fieldDefs = array();

            foreach ($fieldLayout->getFields() as $field) {
                $fieldDefs[$field->getField()->handle] = $field->required;
            }

            return array(
                'fields' => $fieldDefs,
            );
        }
    }

    /**
     * Attempt to import Content Tabs.
     *
     * @param array $sectionDefs
     *
     * @return Schematic_ResultModel
     */
    public function import($tabDefs, $applyToTabs)
    {
        $result = new Schematic_ResultModel();

        if (empty($tabDefs) || empty($applyToTabs)) {
            // Ignore importing sections.
            return $result;
        }

        foreach ($tabDefs as $tabname => $tabDef) {
            $entryTypeIds = array();
            $sections = array();

            print $tabname;

            foreach ($applyToTabs->applyTo as $sec) {
                $secet = explode('||', $sec);
                $entryTypeHandle = $secet[1];
                $sectionHandle = $secet[0];

                $section = craft()->sections->getSectionByHandle($sectionHandle);

                $entryTypes = $section->getEntryTypes('handle');

                $entryType = $entryTypes[$entryTypeHandle];
                $entryTypeIds[] = $entryType->id;
                $sections[] = $entryType->getSection();
            }

            $sectionDefs = craft()->schematic_sections->export($sections, $entryTypeIds);

            foreach ($sectionDefs as $sectionName => $sectionDef) {
                print('here');
                foreach ($sectionDef['entryTypes'] as $entryHandle => $entryType) {
                    print 'ADD TAB '.$tabname;
                    $sectionDefs[$sectionName]['entryTypes'][$entryHandle]['fieldLayout']['tabs'][$tabname] = $tabDef;
                }
            }

            craft()->schematic_sections->import($sectionDefs);
        }

        return true;
    }

    /**
     * Attempt to import a field layout.
     *
     * @param array $fieldLayoutDef
     *
     * @return FieldLayoutModel
     */
    private function _importFieldLayout(Array $fieldLayoutDef)
    {
        $layoutTabs   = array();
        $layoutFields = array();

        if (array_key_exists('tabs', $fieldLayoutDef)) {
            $tabSortOrder = 0;

            foreach ($fieldLayoutDef['tabs'] as $tabName => $tabDef) {
                $layoutTabFields = array();

                foreach ($tabDef as $fieldHandle => $required) {
                    $fieldSortOrder = 0;

                    $field = craft()->fields->getFieldByHandle($fieldHandle);

                    if ($field) {
                        $layoutField = new FieldLayoutFieldModel();

                        $layoutField->setAttributes(array(
                            'fieldId'   => $field->id,
                            'required'  => $required,
                            'sortOrder' => ++$fieldSortOrder,
                        ));

                        $layoutTabFields[] = $layoutField;
                        $layoutFields[] = $layoutField;
                    }
                }

                $layoutTab = new FieldLayoutTabModel();

                $layoutTab->setAttributes(array(
                    'name' => $tabName,
                    'sortOrder' => ++$tabSortOrder,
                ));

                $layoutTab->setFields($layoutTabFields);

                $layoutTabs[] = $layoutTab;
            }
        } elseif (array_key_exists('fields', $fieldLayoutDef)) {
            $fieldSortOrder = 0;

            foreach ($fieldLayoutDef['fields'] as $fieldHandle => $required) {
                $field = craft()->fields->getFieldByHandle($fieldHandle);

                if ($field) {
                    $layoutField = new FieldLayoutFieldModel();

                    $layoutField->setAttributes(array(
                        'fieldId'   => $field->id,
                        'required'  => $required,
                        'sortOrder' => ++$fieldSortOrder,
                    ));

                    $layoutFields[] = $layoutField;
                }
            }
        }

        $fieldLayout = new FieldLayoutModel();
        $fieldLayout->type = ElementType::Entry;
        $fieldLayout->setTabs($layoutTabs);
        $fieldLayout->setFields($layoutFields);

        return $fieldLayout;
    }
}
