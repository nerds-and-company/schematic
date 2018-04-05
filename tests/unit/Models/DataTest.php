<?php

namespace NerdsAndCompany\Schematic\Models;

use Symfony\Component\Yaml\Yaml;

/**
 * Class DataTest.
 *
 * @author    Nerds & Company
 * @copyright Copyright (c) 2015-2018, Nerds & Company
 * @license   MIT
 *
 * @link      http://www.nerds.company
 */
class DataTest extends \Codeception\Test\Unit
{
    /**
     * @return string
     */
    private function getSchemaTestFile()
    {
        return file_get_contents(__DIR__.'/../../_data/test_schema.yml');
    }

    /**
     * @return string
     */
    private function getOverrideTestFile()
    {
        return file_get_contents(__DIR__.'/../../_data/test_override.yml');
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

    public function testRegularOverride()
    {
        $result = $this->generateDataModel();
        $this->assertEquals('override_key', $result->volumes['uploads']['attributes']['keyId']);
    }

    public function testEnvironmentOverride()
    {
        $result = $this->generateDataModel();
        $this->assertEquals('override_bucket_name', $result->volumes['uploads']['attributes']['bucket']);
    }

    public function testErrorWhenEnvironmentVariableNotSet()
    {
        // unset environment variable
        putenv('SCHEMATIC_S3_BUCKET');
        $this->setExpectedException('Error');
        $schema = $this->getSchemaTestFile();
        $override = $this->getOverrideTestFile();
        Data::fromYaml($schema, $override);
    }

    public function testToYamlIsValidYaml()
    {
        $dataModel = $this->generateDataModel();
        $yaml = Data::toYaml($dataModel->attributes);
        $this->assertInternalType('array', Yaml::parse($yaml));
    }

    public function testToYamlContainsCorrectText()
    {
        $dataModel = $this->generateDataModel();
        $yaml = Data::toYaml($dataModel->attributes);
        $this->assertContains('override_bucket_name', $yaml);
    }
}
