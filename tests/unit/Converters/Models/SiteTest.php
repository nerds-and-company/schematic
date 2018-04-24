<?php

namespace NerdsAndCompany\Schematic\Converters\Models;

use Craft;
use craft\models\Site as SiteModel;
use craft\models\SiteGroup;
use Codeception\Test\Unit;

/**
 * Class SiteTest.
 *
 * @author    Nerds & Company
 * @copyright Copyright (c) 2015-2017, Nerds & Company
 * @license   MIT
 *
 * @see      http://www.nerds.company
 */
class SiteTest extends Unit
{
    /**
     * @var Site
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
        $this->converter = new Site();
    }

    //==============================================================================================================
    //=================================================  TESTS  ====================================================
    //==============================================================================================================

    /**
     * @dataProvider provideSites
     *
     * @param SiteModel $site
     * @param array     $definition
     */
    public function testGetRecordDefinition(SiteModel $site, array $definition)
    {
        $result = $this->converter->getRecordDefinition($site);

        $this->assertSame($definition, $result);
    }

    /**
     * @dataProvider provideSites
     *
     * @param SiteModel $site
     * @param array     $definition
     * @param string    $groupStatus existing|new|invalid
     */
    public function testSaveRecord(SiteModel $site, array $definition, string $groupStatus)
    {
        Craft::$app->sites->expects($this->exactly(1))
                          ->method('getAllGroups')
                          ->willReturn([$this->getMockSiteGroup(1)]);

        Craft::$app->sites->expects($this->exactly('existing' == $groupStatus ? 0 : 1))
                          ->method('saveGroup')
                          ->willReturn('invalid' !== $groupStatus);

        Craft::$app->sites->expects($this->exactly(1))
                          ->method('saveSite')
                          ->with($site)
                          ->willReturn(true);

        $result = $this->converter->saveRecord($site, $definition);

        $this->assertTrue($result);
    }

    /**
     * @dataProvider provideSites
     *
     * @param SiteModel $site
     */
    public function testDeleteRecord(SiteModel $site)
    {
        Craft::$app->sites->expects($this->exactly(1))
                          ->method('deleteSiteById')
                          ->with($site->id);

        $this->converter->deleteRecord($site);
    }

    //==============================================================================================================
    //==============================================  PROVIDERS  ===================================================
    //==============================================================================================================

    /**
     * @return array
     */
    public function provideSites()
    {
        $mockSite1 = $this->getMockSite(1, 1);
        $mockSite2 = $this->getMockSite(1, 2);

        return [
            'valid site existing group' => [
                'site' => $mockSite1,
                'definition' => $this->getMockSiteDefinition($mockSite1),
                'groupStatus' => 'existing',
            ],
            'valid site new group' => [
                'site' => $mockSite2,
                'definition' => $this->getMockSiteDefinition($mockSite2),
                'groupStatus' => 'new',
            ],
            'valid site invalid group' => [
                'site' => $mockSite2,
                'definition' => $this->getMockSiteDefinition($mockSite2),
                'groupStatus' => 'invalid',
            ],
        ];
    }

    //==============================================================================================================
    //================================================  HELPERS  ===================================================
    //==============================================================================================================

    /**
     * @param SiteModel $mockSite
     *
     * @return array
     */
    private function getMockSiteDefinition(SiteModel $mockSite)
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
     * @return Mock|SiteModel
     */
    private function getMockSite(int $siteId, int $groupId)
    {
        $mockSite = $this->getMockBuilder(SiteModel::class)
                                    ->setMethods(['getGroup'])
                                    ->disableOriginalConstructor()
                                    ->getMock();

        $mockSite->id = $siteId;
        $mockSite->groupId = $groupId;
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
}
