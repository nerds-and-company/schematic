<?php

namespace NerdsAndCompany\Schematic\Models;

use Craft\Craft;
use Craft\BaseModel as Base;
use Craft\AttributeType;
use Craft\Exception;
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
class Data extends Base
{
    /**
     * Define attributes.
     *
     * @inheritdoc
     */
    protected function defineAttributes()
    {
        return array(
            'locales'           => array(AttributeType::Mixed, 'default' => array()),
            'assetSources'      => array(AttributeType::Mixed, 'default' => array()),
            'fields'            => array(AttributeType::Mixed, 'default' => array()),
            'globalSets'        => array(AttributeType::Mixed, 'default' => array()),
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
     * @return Data
     */
    public static function fromYaml($yaml, $overrideYaml)
    {
        $data = Yaml::parse($yaml);
        if (!empty($overrideYaml)) {
            $overrideYaml = static::replaceEnvVariables($overrideYaml);
            $overrideData = Yaml::parse($overrideYaml);
            if ($overrideData != null) {
                $data = array_replace_recursive($data, $overrideData);
            }
        }

        return $data === null ? null : new static($data);
    }

    /**
     * Replace placeholders with enviroment variables.
     *
     * Placeholders start with % and end with %. This will be replaced by the
     * environment variable with the name SCHEMATIC_{PLACEHOLDER}. If the
     * environment variable is not set an exception will be thrown.
     *
     * @param string $yaml
     *
     * @return string
     *
     * @throws Exception
     */
    public static function replaceEnvVariables($yaml)
    {
        $matches = null;
        preg_match_all('/%\w+%/', $yaml, $matches);
        $original_values = $matches[0];
        $replace_values = array();
        foreach ($original_values as $match) {
            $env_variable = strtoupper(substr($match, 1, -1));
            $env_variable = 'SCHEMATIC_'.$env_variable;
            $env_value = getenv($env_variable);
            if (!$env_value) {
                throw new Exception(Craft::t("Schematic environment variable not set: {$env_variable}"));
            }
            $replace_values[] = $env_value;
        }

        return str_replace($original_values, $replace_values, $yaml);
    }

    /**
     * Populate yaml from data model.
     *
     * @param array $data
     *
     * @return Data
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
