<?php

namespace NerdsAndCompany\Schematic\Converters\Elements;

use Craft;
use craft\elements\GlobalSet as GlobalSetElement;
use craft\base\Field;
use craft\models\FieldLayout;
use craft\models\Site;
use craft\services\Fields;
use Codeception\Test\Unit;

/**
 * Class GlobalSetsTest.
 *
 * @author    Nerds & Company
 * @copyright Copyright (c) 2015-2017, Nerds & Company
 * @license   MIT
 *
 * @see      http://www.nerds.company
 */
class GlobalSetsTest extends Unit
{
    /**
     * @var GlobalSets
     */
    private $converter;

    /**
     * Set the converter.
     *
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     */
    protected function _before()
    {
        $this->converter = new GlobalSet();
    }

    //==============================================================================================================
    //=================================================  TESTS  ====================================================
    //==============================================================================================================

    /**
     * @dataProvider provideGlobalSets
     *
     * @param GlobalSetModel $set
     * @param array          $definition
     */
    public function testGetRecordDefinition(GlobalSetElement $set, array $definition)
    {
        $result = $this->converter->getRecordDefinition($set);

        $this->assertSame($definition, $result);
    }

    /**
     * @dataProvider provideGlobalSets
     *
     * @param GlobalSetModel $set
     * @param array          $definition
     * @param bool           $valid
     */
    public function testSaveRecord(GlobalSetElement $set, array $definition, bool $valid)
    {
        Craft::$app->sites->expects($this->any())
                ->method('getSiteByHandle')
                ->willReturnMap([
                  ['default', $this->getMockSite()],
                  ['invalid', null],
                ]);

        Craft::$app->globals->expects($this->exactly($valid ? 1 : 0))
                            ->method('saveSet')
                            ->with($set)
                            ->willReturn(true);

        $result = $this->converter->saveRecord($set, $definition);

        $this->assertSame($valid, $result);
    }

    /**
     * @dataProvider provideGlobalSets
     *
     * @param GlobalSetModel $set
     */
    public function testDeleteRecord(GlobalSetElement $set)
    {
        Craft::$app->elements->expects($this->exactly(1))->method('deleteElementById')->with($set->id);
        $this->converter->deleteRecord($set);
    }

    //==============================================================================================================
    //==============================================  PROVIDERS  ===================================================
    //==============================================================================================================

    /**
     * @return array
     */
    public function provideGlobalSets()
    {
        $mockGlobalSet1 = $this->getMockGlobalSet(1);
        $mockGlobalSet2 = $this->getMockGlobalSet(1, 'invalid');

        return [
            'Valid set' => [
                'set' => $mockGlobalSet1,
                'definition' => $this->getMockGlobalSetDefinition($mockGlobalSet1),
                'validSave' => true,
            ],
            'Invalid set' => [
                'set' => $mockGlobalSet2,
                'definition' => $this->getMockGlobalSetDefinition($mockGlobalSet2),
                'validSave' => false,
            ],
        ];
    }

    //==============================================================================================================
    //================================================  HELPERS  ===================================================
    //==============================================================================================================

    /**
     * @param GlobalSet $mockGlobalSet
     *
     * @return array
     */
    private function getMockGlobalSetDefinition(GlobalSetElement $mockGlobalSet)
    {
        $fieldDefs = [];
        foreach ($mockGlobalSet->getFieldLayout()->getFields() as $field) {
            $fieldDefs[$field->handle] = $field->required;
        }

        return [
            'class' => get_class($mockGlobalSet),
            'attributes' => [
                'name' => $mockGlobalSet->name,
                'handle' => $mockGlobalSet->handle,
                'enabled' => true,
                'archived' => false,
                'enabledForSite' => true,
                'title' => null,
                'slug' => null,
                'uri' => null,
            ],
            'fieldLayout' => [
                'fields' => $fieldDefs,
            ],
            'site' => $mockGlobalSet->getSite()->handle,
        ];
    }

    /**
     * @param int    $setId
     * @param string $siteHandle
     *
     * @return Mock|GlobalSet
     */
    private function getMockGlobalSet(int $setId, string $siteHandle = 'default')
    {
        $mockSet = $this->getMockBuilder(GlobalSetElement::class)
                                    ->setMethods(['__isset', 'getSite', 'getFieldLayout', 'fieldByHandle'])
                                    ->disableOriginalConstructor()
                                    ->getMock();

        $mockSet->id = $setId;
        $mockSet->fieldLayoutId = $setId;
        $mockSet->handle = 'setHandle'.$setId;
        $mockSet->name = 'setName'.$setId;

        $mockSet->expects($this->any())
                ->method('getSite')
                ->willReturn($this->getMockSite($siteHandle));

        $mockField = $this->getMockbuilder(Field::class)->getMock();
        $mockField->id = $setId;
        $mockField->handle = 'field'.$setId;
        $mockField->required = false;

        $mockFieldLayout = $this->getMockBuilder(FieldLayout::class)->getMock();

        $mockFieldLayout->expects($this->any())
                        ->method('getFields')
                        ->willReturn([$mockField]);

        $mockSet->expects($this->any())
                  ->method('getFieldLayout')
                  ->willReturn($mockFieldLayout);

        return $mockSet;
    }

    /**
     * Get a mock site.
     *
     * @param string $handle
     *
     * @return Mock|Site
     */
    private function getMockSite(string $handle = 'default')
    {
        $mockSite = new Site([
            'id' => 99,
            'handle' => $handle,
        ]);

        return $mockSite;
    }
}
