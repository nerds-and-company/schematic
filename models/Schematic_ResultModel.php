<?php

namespace Craft;

/**
 * Schematic Result Model.
 *
 * Encapsulates the result of an action, including error messages.
 *
 * @author    Itmundi
 * @copyright Copyright (c) 2015, Itmundi
 * @license   MIT
 *
 * @link      http://www.itmundi.nl
 */
class Schematic_ResultModel extends BaseModel
{
    /**
     * Define attributes.
     *
     * @return array
     */
    protected function defineAttributes()
    {
        return array(
            'ok'     => AttributeType::Bool,
            'errors' => AttributeType::Mixed,
        );
    }

    /**
     * Initialize.
     *
     * @param null|array $errors
     */
    public function __construct($errors = null)
    {
        parent::__construct(array(
            'ok'     => $errors === null || count($errors) === 0,
            'errors' => $errors === null ? array() : $errors,
        ));
    }

    /**
     * Appends an error message to this result.
     *
     * @param array|string $message The error message, or array of error messages.
     *
     * @return Schematic_ResultModel Self, for chaining.
     */
    public function error($data)
    {
        $this->ok = false;
        $this->errors = array_merge($this->errors, is_array($data) ? $data : array($data));

        return $this;
    }

    /**
     * Consumes the errors listed in an existing result and appends them to this result.
     *
     * @param Schematic_ResultModel $result The result to consume.
     */
    public function consume(Schematic_ResultModel $result)
    {
        if (count($result->errors) > 0) {
            $this->error($result->errors);
        }
    }
}
