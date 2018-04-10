<?php

namespace NerdsAndCompany\Schematic\Services;

use Craft;
use craft\models\Site;
use craft\models\SiteGroup;
use Codeception\Test\Unit;
use NerdsAndCompany\Schematic\Schematic;

/**
 * Class SitesTest.
 *
 * @author    Nerds & Company
 * @copyright Copyright (c) 2015-2017, Nerds & Company
 * @license   MIT
 *
 * @see      http://www.nerds.company
 */
class SitesTest extends Unit
{
    /**
     * @var Sites
     */
    private $service;

    /**
     * Set the service.
     *
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     */
    protected function _before()
    {
        Craft::$app->sites->expects($this->any())
                          ->method('getAllGroups')
                          ->willReturn([$this->getMockSiteGroup(1)]);

        Craft::$app->sites->expects($this->any())
                          ->method('saveGroup')
                          ->willReturn(true);

        $this->service = new ModelProcessor();
    }

    //==============================================================================================================
    //=================================================  TESTS  ====================================================
    //==============================================================================================================

    /**
     * @dataProvider provideValidSites
     *
     * @param SiteModel[] $sites
     * @param array       $expectedResult
     */
    public function testSuccessfulExport(array $sites, array $expectedResult = [])
    {
        $actualResult = $this->service->export($sites);

        $this->assertSame($expectedResult, $actualResult);
    }

    /**
     * @dataProvider provideValidSiteDefinitions
     *
     * @param array $siteDefinitions
     */
    public function testSuccessfulImport(array $siteDefinitions, array $existingsites, int $saveCount)
    {
        $this->expectSaves($saveCount);
        $this->expectDeletes(0);

        $this->service->import($siteDefinitions, $existingsites);
    }

    /**
     * @dataProvider provideValidSiteDefinitions
     *
     * @param array $siteDefinitions
     */
    public function testImportWithForceOption(array $siteDefinitions, array $existingsites, int $saveCount, int $deleteCount)
    {
        Schematic::$force = true;
        $this->expectSaves($saveCount);
        $this->expectDeletes($deleteCount);

        $this->service->import($siteDefinitions, $existingsites);
    }

    //==============================================================================================================
    //==============================================  PROVIDERS  ===================================================
    //==============================================================================================================

    /**
     * @return array
     */
    public function provideValidSites()
    {
        $mockSite1 = $this->getMockSite(1, 1);
        $mockSite2 = $this->getMockSite(2, 1);

        return [
            'emptyArray' => [
                'sites' => [],
                'expectedResult' => [],
            ],
            'single site' => [
                'sites' => [
                    'site1' => $mockSite1,
                ],
                'expectedResult' => [
                    'siteHandle1' => $this->getMockSiteDefinition($mockSite1),
                ],
            ],
            'multiple sites' => [
                'sites' => [
                    'site1' => $mockSite1,
                    'site2' => $mockSite2,
                ],
                'expectedResult' => [
                    'siteHandle1' => $this->getMockSiteDefinition($mockSite1),
                    'siteHandle2' => $this->getMockSiteDefinition($mockSite2),
                ],
            ],
        ];
    }

    /**
     * @return array
     */
    public function provideValidSiteDefinitions()
    {
        $mockSite1 = $this->getMockSite(1, 1);
        $mockSite2 = $this->getMockSite(2, 2);

        return [
            'emptyArray' => [
                'siteDefinitions' => [],
                'existingsites' => [
                    $mockSite1,
                ],
                'saveCount' => 0,
                'deleteCount' => 1,
            ],
            'single new site' => [
                'siteDefinitions' => [
                    'siteHandle1' => $this->getMockSiteDefinition($mockSite1),
                    'siteHandle2' => $this->getMockSiteDefinition($mockSite2),
                ],
                'existingsites' => [
                    $mockSite1,
                ],
                'saveCount' => 1,
                'deleteCount' => 0,
            ],
        ];
    }

    //==============================================================================================================
    //================================================  HELPERS  ===================================================
    //==============================================================================================================

    /**
     * @param Site $mockSite
     *
     * @return array
     */
    private function getMockSiteDefinition(Site $mockSite)
    {
        return [
            'class' => get_class($mockSite),
            'attributes' => [
                'name' => $mockSite->name,
                'handle' => $mockSite->handle,
                'language' => 'nl',
                'primary' => true,
                'hasUrls' => true,
                'originalName' => null,
                'originalBaseUrl' => null,
                'baseUrl' => '@web/',
                'sortOrder' => 1,
            ],
            'group' => $mockSite->group->name,
        ];
    }

    /**
     * @param int $siteId
     *
     * @return Mock|Site
     */
    private function getMockSite(int $siteId, int $groupId)
    {
        $mockSite = $this->getMockBuilder(Site::class)
                                    ->setMethods(['getGroup'])
                                    ->disableOriginalConstructor()
                                    ->getMock();

        $mockSite->id = $siteId;
        $mockSite->groupId = 1;
        $mockSite->handle = 'siteHandle'.$siteId;
        $mockSite->language = 'nl';
        $mockSite->primary = true;
        $mockSite->hasUrls = true;
        $mockSite->originalName = null;
        $mockSite->originalBaseUrl = null;
        $mockSite->baseUrl = '@web/';
        $mockSite->sortOrder = 1;

        $mockSite->expects($this->any())
                 ->method('getGroup')
                 ->willReturn($this->getMockSiteGroup($groupId));

        return $mockSite;
    }

    /**
     * Get a mock site group.
     *
     * @param int $groupId
     *
     * @return Mock|SiteGroup
     */
    private function getMockSiteGroup(int $groupId)
    {
        $mockGroup = $this->getMockBuilder(SiteGroup::class)
                        ->disableOriginalConstructor()
                        ->getmock();

        $mockGroup->id = $groupId;
        $mockGroup->name = 'siteGroup'.$groupId;

        return $mockGroup;
    }

    /**
     * Expect a number of site saves.
     *
     * @param int $saveCount
     */
    private function expectSaves(int $saveCount)
    {
        Craft::$app->sites
                   ->expects($this->exactly($saveCount))
                   ->method('saveSite')
                   ->willReturn(true);
    }

    /**
     * Expect a number of site deletes.
     *
     * @param int $deleteCount
     */
    private function expectDeletes(int $deleteCount)
    {
        Craft::$app->sites
                    ->expects($this->exactly($deleteCount))
                    ->method('deleteSiteById');
    }
}
