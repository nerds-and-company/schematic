<?php

namespace Craft;

/**
 * Schematic Exported Data Model.
 *
 * Encapsulates data that has been exported via schematic.
 *
 * @author    Itmundi
 * @copyright Copyright (c) 2015, Itmundi
 * @license   MIT
 *
 * @link      http://www.itmundi.nl
 */
class Schematic_ExportedDataModel extends BaseModel
{
    /**
     * Creates an Schematic_ExportedDataModel from JSON input.
     *
     * @param string $json The input JSON.
     *
     * @return Schematic_ExportedDataModel|null The new Schematic_ExportedDataMode on success, null on invalid JSON.
     */
    public static function fromJson($json)
    {
        $data = json_decode($json, true);

        return $data === null ? null : new static($data);
    }

    /**
     * Define attributes.
     *
     * @inheritdoc
     */
    protected function defineAttributes()
    {
        return array(
            'assets'            => AttributeType::Mixed,
            'categories'        => AttributeType::Mixed,
            'fields'            => AttributeType::Mixed,
            'globals'           => AttributeType::Mixed,
            'plugins'           => AttributeType::Mixed,
            'sections'          => AttributeType::Mixed,
            'contenttabs'       => AttributeType::Mixed,
            'tags'              => AttributeType::Mixed,
            'userGroups'        => AttributeType::Mixed,
            'pluginData'        => AttributeType::Mixed,
        );
    }

    /**
     * Returns a JSON representation of this model.
     *
     * @return string
     */
    public function toJson()
    {
        return json_encode($this->getAttributes(), JSON_PRETTY_PRINT | JSON_NUMERIC_CHECK);
    }
}
