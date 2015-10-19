<?php

namespace Craft;

use Symfony\Component\Yaml\Yaml;

/**
 * Schematic Data Model.
 *
 * Encapsulates data that has been exported via schematic.
 *
 * @author    Itmundi
 * @copyright Copyright (c) 2015, Itmundi
 * @license   MIT
 *
 * @link      http://www.itmundi.nl
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
            'assets'            => AttributeType::Mixed,
            'fields'            => AttributeType::Mixed,
            'globals'           => AttributeType::Mixed,
            'plugins'           => AttributeType::Mixed,
            'sections'          => AttributeType::Mixed,
            'userGroups'        => AttributeType::Mixed,
            'pluginData'        => AttributeType::Mixed,
        );
    }

    /**
     * Populate data model from yaml.
     *
     * @param string $yaml
     *
     * @return Schematic_DataModel
     */
    public static function fromYaml($yaml)
    {
        $data = Yaml::parse($yaml);

        return $data === null ? null : new static($data);
    }

    /**
     * Populate yaml from data model.
     *
     * @param string $yaml
     *
     * @return Schematic_DataModel
     */
    public static function toYaml($data)
    {
        $data = $data === null ? null : new static($data);

        return Yaml::dump($data->attributes, 12);
    }
}
