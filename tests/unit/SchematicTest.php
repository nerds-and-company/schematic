<?php

namespace NerdsAndCompany\Schematic;

use craft\base\Element;
use craft\fields\PlainText;
use craft\models\Section;
use Codeception\Test\Unit;
use NerdsAndCompany\Schematic\Converters\Base\Field as FieldConverter;
use NerdsAndCompany\Schematic\Converters\Models\Section as SectionConverter;
use NerdsAndCompany\Schematic\Interfaces\DataTypeInterface;

/**
 * Class SchematicTest.
 *
 * @author    Nerds & Company
 * @copyright Copyright (c) 2015-2018, Nerds & Company
 * @license   MIT
 *
 * @see      http://www.nerds.company
 */
class SchematicTest extends Unit
{
    /**
     * @var Schematic
     */
    private $module;

    /**
     * Set the mapper.
     *
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     * phpcs:disable PSR2.Methods.MethodDeclaration.Underscore
     */
    protected function _before()
    {
        $this->module = new Schematic('schematic');
    }

    //==============================================================================================================
    //=================================================  TESTS  ====================================================
    //==============================================================================================================

    /**
     * @dataProvider provideDataTypes
     *
     * @param string $dataTypeHandle
     * @param bool   $valid
     * @param string $dataTypeClass
     */
    public function testGetDataType(string $dataTypeHandle, bool $valid, string $dataTypeClass)
    {
        if ($dataTypeClass) {
            $this->module->dataTypes[$dataTypeHandle] = $dataTypeClass;
        }

        $result = $this->module->getDataType($dataTypeHandle);

        if ($valid) {
            $this->assertInstanceOf(DataTypeInterface::class, $result);
        } else {
            $this->assertNull($result);
        }
    }

    /**
     * @dataProvider provideMappers
     *
     * @param string $mapperHandle
     * @param bool   $valid
     * @param string $mapperClass
     */
    public function testCheckMapper(string $mapperHandle, bool $valid, string $mapperClass)
    {
        if ($mapperClass) {
            $this->module->setComponents([
                $mapperHandle => [
                    'class' => $mapperClass,
                ],
            ]);
        }

        $result = $this->module->checkMapper($mapperHandle);

        $this->assertSame($valid, $result);
    }

    /**
     * @dataProvider provideConverters
     *
     * @param string $modelClass
     * @param bool   $valid
     * @param string $converterClass
     */
    public function testGetConverter(string $modelClass, bool $valid, string $converterClass)
    {
        $result = $this->module->getConverter($modelClass);

        if ($valid) {
            $this->assertInstanceOf($converterClass, $result);
        } else {
            $this->assertNull($result);
        }
    }

    //==============================================================================================================
    //==============================================  PROVIDERS  ===================================================
    //==============================================================================================================

    /**
     * @return array
     */
    public function provideDataTypes()
    {
        return [
            'existing dataType' => [
                'dataTypeHandle' => 'sections',
                'valid' => true,
                'dataTypeClass' => '',
            ],
            'dataType not registerd' => [
                'dataTypeHandle' => 'unregistered',
                'valid' => false,
                'dataTypeClass' => '',
            ],
            'dataTypeClass does not exist' => [
                'dataTypeHandle' => 'notExists',
                'valid' => false,
                'dataTypeClass' => 'NotExists',
            ],
            'dataTypeClass does not implement interface' => [
                'dataTypeHandle' => 'implements',
                'valid' => false,
                'dataTypeClass' => \stdClass::class,
            ],
        ];
    }

    /**
     * @return array
     */
    public function provideMappers()
    {
        return [
            'existing mapper' => [
                'mapperHandle' => 'modelMapper',
                'valid' => true,
                'mapper' => '',
            ],
            'mapper not registerd' => [
                'mapperHandle' => 'unregistered',
                'valid' => false,
                'mapper' => '',
            ],
            'mapper does not implement interface' => [
                'mapperHandle' => 'fakeMapper',
                'valid' => false,
                'mapper' => \stdClass::class,
            ],
        ];
    }

    /**
     * @return array
     */
    public function provideConverters()
    {
        return [
            'direct match' => [
                'modelClass' => Section::class,
                'valid' => true,
                'converterClass' => SectionConverter::class,
            ],
            'parent match' => [
                'modelClass' => PlainText::class,
                'valid' => true,
                'converterClass' => FieldConverter::class,
            ],
            'no match' => [
                'modelClass' => Element::class,
                'valid' => false,
                'converterClass' => '',
            ],
        ];
    }
}
