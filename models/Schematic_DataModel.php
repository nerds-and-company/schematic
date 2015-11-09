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
            $data = array_replace_recursive($data, $overrideData);
        }
        $data = Schematic_DataModel::replaceEnvVariables($data);

        return $data === null ? null : new static($data);
    }

    /**
     * Replace placeholders with enviroment variables in array.
     *
     * Placeholders start with % and end with %. This will be replaced by the
     * environment variable with the name SCHEMATIC_{PLACEHOLDER}. If the
     * environment variable is not set an exception will be thrown.
     *
     * @param array $yaml
     *
     * @return array
     */
    public static function replaceEnvVariables($yaml)
    {
        $replacer = function($value) {
            if (substr($value, 0, 1) == '%' && substr($value, -1, 1) == '%') {
                $env_variable = strtoupper(substr($value, 1, -1));
                $env_variable = 'SCHEMATIC_' . $env_variable;
                $env_value = getenv($env_variable);
                if (!$env_value) {
                    throw new Exception("Schematic environment variable not set: {$env_variable}");
                }
                return getenv($env_variable);
            }
            return $value;
        };

        array_walk_recursive($yaml, function(&$v) use ($replacer) {
            $v = $replacer($v);
        });

        return $yaml;
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
