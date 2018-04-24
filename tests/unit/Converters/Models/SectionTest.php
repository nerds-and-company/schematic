<?php

namespace NerdsAndCompany\Schematic\Converters\Models;

use Craft;
use craft\models\Section as SectionModel;
use craft\models\Section_SiteSettings;
use craft\models\Site;
use Codeception\Test\Unit;

/**
 * Class SectionTest.
 *
 * @author    Nerds & Company
 * @copyright Copyright (c) 2015-2017, Nerds & Company
 * @license   MIT
 *
 * @see      http://www.nerds.company
 */
class SectionTest extends Unit
{
    /**
     * @var Section
     */
    private $converter;

    /**
     * Set the converter.
     *
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     * phpcs:disable PSR2.Methods.MethodDeclaration.Underscore
     */
    protected function _before()
    {
        $this->converter = new Section();
    }

    //==============================================================================================================
    //=================================================  TESTS  ====================================================
    //==============================================================================================================

    /**
     * @dataProvider provideSections
     *
     * @param SectionModel $section
     * @param array        $definition
     */
    public function testGetRecordDefinition(SectionModel $section, array $definition)
    {
        Craft::$app->controller->module->modelMapper->expects($this->exactly(1))
                           ->method('export')
                           ->with($section->getEntryTypes())
                           ->willReturn($definition['entryTypes']);

        $result = $this->converter->getRecordDefinition($section);

        $this->assertSame($definition, $result);
    }

    /**
     * @dataProvider provideSections
     *
     * @param SectionModel $section
     * @param array        $definition
     * @param bool         $valid
     */
    public function testSaveRecord(SectionModel $section, array $definition, bool $valid)
    {
        Craft::$app->sections->expects($this->exactly(1))
                             ->method('saveSection')
                             ->with($section)
                             ->willReturn($valid);

        Craft::$app->controller->module->modelMapper->expects($this->exactly($valid ? 1 : 0))
                                     ->method('import')
                                     ->with($definition['entryTypes'], $section->getEntryTypes(), [
                                         'sectionId' => $section->id,
                                     ])
                                     ->willReturn($section->getEntryTypes());

        $result = $this->converter->saveRecord($section, $definition);

        $this->assertSame($valid, $result);
    }

    /**
     * @dataProvider provideSections
     *
     * @param SectionModel $section
     */
    public function testDeleteRecord(SectionModel $section)
    {
        Craft::$app->sections->expects($this->exactly(1))
                             ->method('deleteSection')
                             ->with($section);

        $this->converter->deleteRecord($section);
    }

    //==============================================================================================================
    //==============================================  PROVIDERS  ===================================================
    //==============================================================================================================

    /**
     * @return array
     */
    public function provideSections()
    {
        $mockSection1 = $this->getMockSection(1);
        $mockSection2 = $this->getMockSection(2);

        return [
            'valid section' => [
                'section' => $mockSection1,
                'definition' => $this->getMockSectionDefinition($mockSection1),
                'validSave' => true,
            ],
            'invalid section' => [
                'section' => $mockSection2,
                'definition' => $this->getMockSectionDefinition($mockSection2),
                'validSave' => false,
            ],
        ];
    }

    //==============================================================================================================
    //================================================  HELPERS  ===================================================
    //==============================================================================================================

    /**
     * @param SectionModel $mockSection
     *
     * @return array
     */
    private function getMockSectionDefinition(SectionModel $mockSection)
    {
        $siteSettingsDef = [];
        foreach ($mockSection->getSiteSettings() as $siteSetting) {
            $siteSettingsDef[$siteSetting->site->handle] = [
                'class' => get_class($siteSetting),
                'attributes' => [
                    'enabledByDefault' => true,
                    'hasUrls' => null,
                    'uriFormat' => null,
                    'template' => null,
                ],
            ];
        }

        return [
            'class' => get_class($mockSection),
            'attributes' => [
                'name' => 'sectionName'.$mockSection->id,
                'handle' => 'sectionHandle'.$mockSection->id,
                'type' => null,
                'maxLevels' => null,
                'enableVersioning' => true,
                'propagateEntries' => true,
            ],
            'siteSettings' => $siteSettingsDef,
            'entryTypes' => [
                'entryTypeDefinition1',
                'entryTypeDefinition2',
            ],
        ];
    }

    /**
     * @param int $sectionId
     *
     * @return Mock|SectionModel
     */
    private function getMockSection(int $sectionId)
    {
        $mockSection = $this->getMockBuilder(SectionModel::class)
                           ->setMethods(['getGroup', 'getEntryTypes', 'getSiteSettings'])
                           ->disableOriginalConstructor()
                           ->getMock();

        $mockSection->id = $sectionId;
        $mockSection->handle = 'sectionHandle'.$sectionId;
        $mockSection->name = 'sectionName'.$sectionId;

        $mockSection->expects($this->any())
                   ->method('getEntryTypes')
                   ->willReturn([
                       $this->getMockEntryType(1),
                       $this->getMockEntryType(2),
                   ]);

        $mockSection->expects($this->any())
                    ->method('getSiteSettings')
                    ->willReturn([$this->getMockSiteSettings()]);

        return $mockSection;
    }

    /**
     * Get a mock entry block type.
     *
     * @param int $blockId
     *
     * @return Mock|EntryType
     */
    private function getMockEntryType($blockId)
    {
        $mockBlockType = $this->getMockBuilder(EntryType::class)
                              ->disableOriginalConstructor()
                              ->getmock();

        $mockBlockType->id = $blockId;
        $mockBlockType->handle = 'blockHandle'.$blockId;

        return $mockBlockType;
    }

    /**
     * Get mock siteSettings.
     *
     * @param string $class
     *
     * @return Mock|Section_SiteSettings
     */
    private function getMockSiteSettings()
    {
        $mockSiteSettings = $this->getMockBuilder(Section_SiteSettings::class)
                                 ->setMethods(['getSite'])
                                 ->getMock();

        $mockSiteSettings->expects($this->any())
          ->method('getSite')
          ->willReturn($this->getMockSite());

        return $mockSiteSettings;
    }

    /**
     * Get a mock site.
     *
     * @return Mock|Site
     */
    private function getMockSite()
    {
        $mockSite = $this->getMockBuilder(Site::class)->getMock();
        $mockSite->handle = 'default';

        return $mockSite;
    }
}
