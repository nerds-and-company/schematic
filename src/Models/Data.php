<?php

namespace NerdsAndCompany\Schematic\Models;

use craft\base\Model;
use Symfony\Component\Yaml\Yaml;

/**
 * Schematic Data Model.
 *
 * Encapsulates data that has been exported via schematic.
 *
 * @author    Nerds & Company
 * @copyright Copyright (c) 2015-2018, Nerds & Company
 * @license   MIT
 *
 * @see      http://www.nerds.company
 *
 * @property array $volumes
 * @property array $assetTransforms
 * @property array $fields
 * @property array $globalSets
 * @property array $plugins
 * @property array $sections
 * @property array $userGroups
 * @property array $users
 * @property array $elementIndexSettings
 * @property array $categoryGroups
 * @property array $tagGroups
 */
class Data extends Model
{
    public $sites;
    public $volumes;
    public $assetTransforms;
    public $fields;
    public $globalSets;
    public $plugins;
    public $sections;
    public $userGroups;
    public $users;
    public $elementIndexSettings;
    public $categoryGroups;
    public $tagGroups;

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
            if (null != $overrideData) {
                $data = array_replace_recursive($data, $overrideData);
            }
        }

        return null === $data ? null : new static($data);
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
        $originalValues = $matches[0];
        $replaceValues = [];
        foreach ($originalValues as $match) {
            $envVariable = strtoupper(substr($match, 1, -1));
            $envVariable = 'SCHEMATIC_'.$envVariable;
            $envValue = getenv($envVariable);
            if (!$envValue) {
                throw new Exception("Schematic environment variable not set: {$envVariable}");
            }
            $replaceValues[] = $envValue;
        }

        return str_replace($originalValues, $replaceValues, $yaml);
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
        $data = null === $data ? null : new static($data);

        return Yaml::dump(array_filter($data->attributes), 12, 2);
    }
}
