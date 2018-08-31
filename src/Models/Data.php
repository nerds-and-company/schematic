<?php

namespace NerdsAndCompany\Schematic\Models;

use Exception;
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
 */
class Data extends Model
{
    /**
     * Parse a yaml file.
     *
     * @param string $yaml
     * @param string $overrideYaml
     *
     * @return array
     */
    public static function fromYaml($yaml, $overrideYaml): array
    {
        $yaml = static::replaceEnvVariables($yaml);
        $data = Yaml::parse($yaml);

        if (!empty($overrideYaml)) {
            $overrideYaml = static::replaceEnvVariables($overrideYaml);
            $overrideData = Yaml::parse($overrideYaml);

            if ($overrideData != null) {
                $data = array_replace_recursive($data, $overrideData);
            }
        }

        return $data;
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
    public static function replaceEnvVariables($yaml): string
    {
        $matches = null;
        preg_match_all('/%\w+%/', $yaml, $matches);
        $originalValues = $matches[0];
        $replaceValues = [];
        foreach ($originalValues as $match) {
            $envVariable = substr($match, 1, -1);
            $envValue = getenv($envVariable);
            if (!$envValue) {
                $envVariable = strtoupper($envVariable);
                $envVariable = 'SCHEMATIC_'.$envVariable;
                $envValue = getenv($envVariable);
                if (!$envValue) {
                    throw new Exception("Schematic environment variable not set: {$envVariable}");
                }
            }
            $replaceValues[] = $envValue;
        }

        return str_replace($originalValues, $replaceValues, $yaml);
    }

    /**
     * Convert array to yaml.
     *
     * @param array  $data
     * @param string $overrideYaml
     *
     * @return string
     */
    public static function toYaml(array $data, $overrideYaml = ''): string
    {
        if (!empty($overrideYaml)) {
            $overrideData = Yaml::parse($overrideYaml);

            if ($overrideData != null) {
                $data = array_replace_recursive($data, $overrideData);
            }
        }

        return Yaml::dump($data, 12, 2);
    }
}
