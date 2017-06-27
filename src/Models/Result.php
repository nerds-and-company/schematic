<?php

namespace NerdsAndCompany\Schematic\Models;

use Craft\BaseModel as Base;

/**
 * Schematic Result Model.
 *
 * Encapsulates the result of an action, including error messages.
 *
 * @author    Nerds & Company
 * @copyright Copyright (c) 2015-2017, Nerds & Company
 * @license   MIT
 *
 * @see      http://www.nerds.company
 */
class Result extends Base
{
    /**
     * Consumes the errors listed in an existing result and appends them to this result.
     *
     * @param Result $result The result to consume
     */
    public function consume(Result $result)
    {
        if ($result->hasErrors('errors')) {
            $this->addErrors(['errors' => $result->getErrors('errors')]);
        }
    }
}
