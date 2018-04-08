<?php

namespace Helper;

use Codeception\Module;
use Codeception\TestCase;

class Unit extends Module
{
    /**
     * {@inheritdoc}
     *
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     */
    public function _cleanup(TestCase $test)
    {
        Test::clean();
    }
}
