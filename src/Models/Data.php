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
 * @copyright Copyright (c) 2015-2017, Nerds & Company
 * @license   MIT
 *
 * @see      http://www.nerds.company
 *
 * @property array $Locales
 * @property array $assetSources
 * @property array $assetTransforms
 * @property array $fields
 * @property array $globalSets
 * @property array $plugins
 * @property array $sections
 * @property array $userGroups
 * @property array $users
 * @property array $elementIndexSettings
 * @property array $pluginData
 * @property array $categoryGroups
 * @property array $tagGroups
 */
class Data extends Base
{
    /**
     * Define attributes.
     *
     * {@inheritdoc}
     */
    protected function defineAttributes()
    {
        return [
            'locales' => [AttributeType::Mixed, 'default' => []],
            'assetSources' => [AttributeType::Mixed, 'default' => []],
            'assetTransforms' => [AttributeType::Mixed, 'default' => []],
            'fields' => [AttributeType::Mixed, 'default' => []],
            'globalSets' => [AttributeType::Mixed, 'default' => []],
            'plugins' => [AttributeType::Mixed, 'default' => []],
            'sections' => [AttributeType::Mixed, 'default' => []],
            'userGroups' => [AttributeType::Mixed, 'default' => []],
            'users' => [AttributeType::Mixed, 'default' => []],
            'elementIndexSettings' => [AttributeType::Mixed, 'default' => []],
            'pluginData' => [AttributeType::Mixed, 'default' => []],
            'categoryGroups' => [AttributeType::Mixed, 'default' => []],
            'tagGroups' => [AttributeType::Mixed, 'default' => []],
        ];
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
        $replace_values = [];
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
    public function getAttribute($attribute, $flattenValue = false, $default = [])
    {
        $attribute = parent::getAttribute($attribute, $flattenValue);

        return !is_null($attribute) ? $attribute : $default;
    }
}
