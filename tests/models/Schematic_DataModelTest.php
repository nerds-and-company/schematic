<?php

namespace Craft;

use Symfony\Component\Yaml\Yaml;

/**
 * Class Schematic_DataModelTest
 *
 * @author    Itmundi
 * @copyright Copyright (c) 2015, Itmundi
 * @license   MIT
 *
 * @link      http://www.itmundi.nl
 *
 * @coversDefaultClass Craft\Schematic_DataModel
 * @covers ::<!public>
 */
class Schematic_DataModelTest extends BaseTest
{

    /**
     * {@inheritdoc}
     */
    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        require_once __DIR__.'/../../models/Schematic_DataModel.php';
    }

    /**
     * @return string
     */
    private function getSchemaTestFile()
    {
        return __DIR__ . '/../data/test_schema.yml';
    }

    /**
     * @return string
     */
    private function getOverrideTestFile()
    {
        return __DIR__ . '/../data/test_override.yml';
    }

    /**
     * @return Schematic_DataModel
     */
    private function generateDataModel()
    {
        putenv('SCHEMATIC_S3_BUCKET=override_bucket_name');
        $schema = $this->getSchemaTestFile();
        $override = $this->getOverrideTestFile();
        return Schematic_DataModel::fromYaml($schema, $override);
    }

    /**
     * @covers ::fromYaml
     */
    public function testRegularOverride()
    {
        $result = $this->generateDataModel();
        $this->assertEquals('override_key', $result->assets['uploads']['settings']['keyId']);
    }

    /**
     * @covers ::fromYaml
     * @covers ::replaceEnvVariables
     */
    public function testEnvironmentOverride()
    {
        $result = $this->generateDataModel();
        $this->assertEquals('override_bucket_name', $result->assets['uploads']['settings']['bucket']);
    }

    /**
     * @covers ::fromYaml
     * @covers ::replaceEnvVariables
     */
    public function testErrorWhenEnvironmentVariableNotSet()
    {
        // unset environment variable
        putenv('SCHEMATIC_S3_BUCKET');
        $this->setExpectedException('\Craft\Exception');
        $schema = $this->getSchemaTestFile();
        $override = $this->getOverrideTestFile();
        Schematic_DataModel::fromYaml($schema, $override);
    }

    /**
     * @covers ::toYaml
     */
    public function testToYamlIsValidYaml()
    {
        $dataModel = $this->generateDataModel();
        $yaml = Schematic_DataModel::toYaml($dataModel->attributes);
        $this->assertInternalType('array', Yaml::parse($yaml));
    }

    /**
     * @covers ::toYaml
     */
    public function testToYamlContainsCorrectText()
    {
        $dataModel = $this->generateDataModel();
        $yaml = Schematic_DataModel::toYaml($dataModel->attributes);
        $this->assertContains('override_bucket_name', $yaml);
    }

    /**
     * @covers ::getAttribute
     */
    public function testGetAttribute()
    {
        $dataModel = $this->generateDataModel();
        $this->assertEquals(array('test_user'), $dataModel->getAttribute('users'));
    }
}
