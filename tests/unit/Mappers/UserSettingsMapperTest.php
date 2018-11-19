<?php

namespace NerdsAndCompany\Schematic\Mappers;

use Craft;
use craft\base\Field;
use craft\base\Volume;
use craft\elements\User;
use craft\models\FieldLayout;
use craft\models\FieldLayoutTab;
use Codeception\Test\Unit;

/**
 * Class UserSettingsMapperTest.
 *
 * @author    Nerds & Company
 * @copyright Copyright (c) 2015-2018, Nerds & Company
 * @license   MIT
 *
 * @see      http://www.nerds.company
 */
class UserSettingsMapperTest extends Unit
{
    /**
     * @var UserSettingsMapper
     */
    private $mapper;

    /**
     * Set the mapper.
     *
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     * phpcs:disable PSR2.Methods.MethodDeclaration.Underscore
     */
    protected function _before()
    {
        $this->mapper = new UserSettingsMapper();
    }

    //==============================================================================================================
    //=================================================  TESTS  ====================================================
    //==============================================================================================================

    /**
     * Test UserSettings service export.
     */
    public function testUserSettingsServiceExport()
    {
        $this->setMockServicesForExport();

        $definition = $this->getUserSettingsDefinition();
        $result = $this->mapper->export();

        $this->assertSame($definition, $result);
    }

    /**
     * Test UserSettings service import without fieldlayout.
     */
    public function testUserSettingsServiceImportWithoutFieldLayout()
    {
        $this->setMockServicesForImport(false, false);

        $definition = $this->getUserSettingsDefinition();
        unset($definition['fieldLayout']);
        $import = $this->mapper->import($definition);

        $this->assertSame([], $import);
    }

    /**
     * Test UserSettings service import with import error.
     */
    public function testUserSettingsServiceImportWithImportError()
    {
        $this->setMockServicesForImport(true, true, true);

        $definition = $this->getUserSettingsDefinition();
        $import = $this->mapper->import($definition);

        $this->assertSame([], $import);
    }

    //==============================================================================================================
    //================================================  HELPERS  ===================================================
    //==============================================================================================================

    /**
     * Get user settings definition.
     *
     * @return array
     */
    private function getUserSettingsDefinition()
    {
        return [
            'settings' => [
                'requireEmailVerification' => true,
                'allowPublicRegistration' => false,
                'defaultGroup' => null,
                'photoSubpath' => 'profile',
                'photoVolume' => 'volumeHandle',
            ],
            'fieldLayout' => [
                'type' => User::class,
                'tabs' => [
                    'Content' => [
                        'fieldHandle1' => true,
                        'fieldHandle2' => false,
                    ],
                ],
            ],
        ];
    }

    /**
     * Set mock services for export.
     */
    private function setMockServicesForExport()
    {
        $mockFieldLayout = $this->getMockFieldLayout();
        Craft::$app->fields->expects($this->exactly(1))
                           ->method('getLayoutByType')
                           ->with(User::class)
                           ->willReturn($mockFieldLayout);

        $settings = [
            'requireEmailVerification' => true,
            'allowPublicRegistration' => false,
            'defaultGroup' => null,
            'photoSubpath' => 'profile',
            'photoVolumeId' => 1,
        ];

        Craft::$app->systemSettings->expects($this->exactly(1))
                                   ->method('getSettings')
                                   ->with('users')
                                   ->willReturn($settings);

        Craft::$app->volumes->expects($this->exactly(1))
                            ->method('getVolumeById')
                            ->with(1)
                            ->willReturn($this->getMockVolume());
    }

    /**
     * @param bool $saveLayout
     * @param bool $deleteLayoutsByType
     */
    private function setMockServicesForImport($saveLayout = true, $deleteLayoutsByType = true, $errors = false)
    {
        Craft::$app->fields->expects($this->exactly($saveLayout ? 1 : 0))->method('saveLayout')->willReturn(!$errors);
        Craft::$app->fields->expects($this->exactly($deleteLayoutsByType ? 1 : 0))
                           ->method('deleteLayoutsByType')
                           ->willReturn(true);
        $mockFieldLayout = $this->getMockFieldLayout();
        if ($errors) {
            $mockFieldLayout->expects($this->exactly(1))->method('getErrors')->willReturn([
                'errors' => ['error 1', 'error 2', 'error 3'],
            ]);
        }

        Craft::$app->fields->expects($this->exactly($saveLayout ? 1 : 0))
                           ->method('assembleLayout')
                           ->willReturn($mockFieldLayout);
    }

    /**
     * @return Mock|FieldLayout
     */
    private function getMockFieldLayout()
    {
        $mockFieldLayout = $this->getMockBuilder(FieldLayout::class)->getMock();
        $mockFieldLayout->type = User::class;
        $mockFieldLayoutTab = $this->getMockBuilder(FieldLayoutTab::class)->getMock();
        $mockFieldLayoutTab->name = 'Content';

        $mockFieldLayout->expects($this->any())
                        ->method('getTabs')
                        ->willReturn([$mockFieldLayoutTab]);

        $mockFieldLayoutTab->expects($this->any())
                           ->method('getFields')
                           ->willReturn([
                               $this->getMockField(1, true),
                               $this->getMockField(2, false),
                           ]);

        return $mockFieldLayout;
    }

    /**
     * Get a mock field.
     *
     * @param int  $fieldId
     * @param bool $required
     *
     * @return Mock|Field
     */
    private function getMockField(int $fieldId, bool $required)
    {
        $mockField = $this->getMockbuilder(Field::class)
                         ->setMethods([])
                         ->getMock();

        $mockField->id = $fieldId;
        $mockField->handle = 'fieldHandle'.$fieldId;
        $mockField->required = $required;

        return $mockField;
    }

    /**
     * Get mock volume.
     *
     * @return Mock|Volume
     */
    private function getMockVolume()
    {
        $mockVolume = $this->getMockBuilder(Volume::class)->getMock();
        $mockVolume->handle = 'volumeHandle';

        return $mockVolume;
    }
}
