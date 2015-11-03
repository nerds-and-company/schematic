<?php

namespace Craft;

use Symfony\Component\Yaml\Yaml;

/**
 * Schematic Data Model.
 *
 * Encapsulates data that has been exported via schematic.
 *
 * @author    Nerds & Company
 * @copyright Copyright (c) 2015, Nerds & Company
 * @license   MIT
 *
 * @link      http://www.nerds.company
 */
class Schematic_DataModel extends BaseModel
{
    /**
     * Define attributes.
     *
     * @inheritdoc
     */
    protected function defineAttributes()
    {
        return array(
            'assets'            => array(AttributeType::Mixed, 'default' => array()),
            'fields'            => array(AttributeType::Mixed, 'default' => array()),
            'globals'           => array(AttributeType::Mixed, 'default' => array()),
            'plugins'           => array(AttributeType::Mixed, 'default' => array()),
            'sections'          => array(AttributeType::Mixed, 'default' => array()),
            'userGroups'        => array(AttributeType::Mixed, 'default' => array()),
            'users'             => array(AttributeType::Mixed, 'default' => array()),
            'pluginData'        => array(AttributeType::Mixed, 'default' => array()),
        );
    }

    /**
     * Populate data model from yaml.
     *
     * @param string $yaml
     * @param string $overrideYaml
     *
     * @return Schematic_DataModel
     */
    public static function fromYaml($yaml, $overrideYaml)
    {
        $data = Yaml::parse($yaml);
        $overrideData = Yaml::parse($overrideYaml);
        if ($overrideData != null) {
            $mergedData = array_replace_recursive($data, $overrideData);
        } else {
            $mergedData = $data;
        }

        return $mergedData === null ? null : new static($mergedData);
    }

    /**
     * Populate yaml from data model.
     *
     * @param array $data
     *
     * @return Schematic_DataModel
     */
    public static function toYaml(array $data)
    {
        $data = $data === null ? null : new static($data);

        return Yaml::dump($data->attributes, 12, 2);
    }

    /**
     * @param string     $attribute
     * @param bool|false $flattenValue
     * @param array      $default
     *
     * @return array
     */
    public function getAttribute($attribute, $flattenValue = false, $default = array())
    {
        $attribute = parent::getAttribute($attribute, $flattenValue);

        return (!is_null($attribute) ? $attribute : $default);
    }
}
