<?php

namespace NerdsAndCompany\Schematic\Models;

use Craft\BaseTest;
use Craft\IOHelper;
use Symfony\Component\Yaml\Yaml;

/**
 * Class DataTest.
 *
 * @author    Nerds & Company
 * @copyright Copyright (c) 2015-2017, Nerds & Company
 * @license   MIT
 *
 * @link      http://www.nerds.company
 *
 * @coversDefaultClass NerdsAndCompany\Schematic\Models\Data
 * @covers ::<!public>
 */
class DataTest extends BaseTest
{
    /**
     * @return string
     */
    private function getSchemaTestFile()
    {
        return IOHelper::getFileContents(__DIR__.'/../data/test_schema.yml');
    }

    /**
     * @return string
     */
    private function getOverrideTestFile()
    {
        return IOHelper::getFileContents(__DIR__.'/../data/test_override.yml');
    }

    /**
     * @return Data
     */
    private function generateDataModel()
    {
        putenv('SCHEMATIC_S3_BUCKET=override_bucket_name');
        $schema = $this->getSchemaTestFile();
        $override = $this->getOverrideTestFile();

        return Data::fromYaml($schema, $override);
    }

    /**
     * @covers ::fromYaml
     */
    public function testRegularOverride()
    {
        $result = $this->generateDataModel();
        $this->assertEquals('override_key', $result->assetSources['uploads']['settings']['keyId']);
    }

    /**
     * @covers ::fromYaml
     * @covers ::replaceEnvVariables
     */
    public function testEnvironmentOverride()
    {
        $result = $this->generateDataModel();
        $this->assertEquals('override_bucket_name', $result->assetSources['uploads']['settings']['bucket']);
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
        Data::fromYaml($schema, $override);
    }

    /**
     * @covers ::toYaml
     */
    public function testToYamlIsValidYaml()
    {
        $dataModel = $this->generateDataModel();
        $yaml = Data::toYaml($dataModel->attributes);
        $this->assertInternalType('array', Yaml::parse($yaml));
    }

    /**
     * @covers ::toYaml
     */
    public function testToYamlContainsCorrectText()
    {
        $dataModel = $this->generateDataModel();
        $yaml = Data::toYaml($dataModel->attributes);
        $this->assertContains('override_bucket_name', $yaml);
    }

    /**
     * @covers ::getAttribute
     */
    public function testGetAttribute()
    {
        $dataModel = $this->generateDataModel();
        $this->assertEquals(['test_user'], $dataModel->getAttribute('users'));
    }
}
