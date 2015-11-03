<?php

namespace Craft;

/**
 * Schematic Result Model.
 *
 * Encapsulates the result of an action, including error messages.
 *
 * @author    Nerds & Company
 * @copyright Copyright (c) 2015, Nerds & Company
 * @license   MIT
 *
 * @link      http://www.nerds.company
 */
class Schematic_ResultModel extends BaseModel
{
    /**
     * Consumes the errors listed in an existing result and appends them to this result.
     *
     * @param Schematic_ResultModel $result The result to consume.
     */
    public function consume(Schematic_ResultModel $result)
    {
        if ($result->hasErrors('errors')) {
            $this->addErrors(array('errors' => $result->getErrors('errors')));
        }
    }
}
