<?php

namespace NerdsAndCompany\Schematic\Models;

use Symfony\Component\Yaml\Yaml;
use Codeception\Test\Unit;

/**
 * Class DataTest.
 *
 * @author    Nerds & Company
 * @copyright Copyright (c) 2015-2018, Nerds & Company
 * @license   MIT
 *
 * @see      http://www.nerds.company
 */
class DataTest extends Unit
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
     *
     * @param bool $useOverride Whether to use the override file or not
     */
    private function generateDataModel($useOverride = false)
    {
        putenv('SCHEMATIC_S3_SECRET_ACCESS_KEY=secret');

        $schema = $this->getSchemaTestFile();
        $override = $useOverride ? $this->getOverrideTestFile() : [];

        return Data::fromYaml($schema, $override);
    }

    public function testEnvironment()
    {
        putenv('S3_BUCKET=bucket_name');
        $result = $this->generateDataModel();
        $this->assertEquals('bucket_name', $result['volumes']['uploads']['attributes']['bucket']);
    }

    public function testEnvironmentFallback()
    {
        putenv('S3_BUCKET'); // unset
        putenv('SCHEMATIC_S3_BUCKET=bucket_name');
        $result = $this->generateDataModel();
        $this->assertEquals('bucket_name', $result['volumes']['uploads']['attributes']['bucket']);
        $this->assertEquals('secret', $result['volumes']['uploads']['attributes']['secret']);
    }

    public function testRegularOverride()
    {
        putenv('S3_BUCKET=bucket_name');
        $result = $this->generateDataModel(true);
        $this->assertEquals('override_key', $result['volumes']['uploads']['attributes']['keyId']);
    }

    public function testEnvironmentOverride()
    {
        putenv('S3_BUCKET=override_bucket_name');
        $result = $this->generateDataModel(true);
        $this->assertEquals('override_bucket_name', $result['volumes']['uploads']['attributes']['bucket']);
    }

    public function testEnvironmentOverrideFallback()
    {
        putenv('S3_BUCKET'); // unset
        putenv('SCHEMATIC_S3_BUCKET=override_bucket_name');
        $result = $this->generateDataModel(true);
        $this->assertEquals('override_bucket_name', $result['volumes']['uploads']['attributes']['bucket']);
        $this->assertEquals('secret', $result['volumes']['uploads']['attributes']['secret']);
    }

    public function testErrorWhenEnvironmentVariableNotSet()
    {
        // unset environment variables
        putenv('S3_BUCKET');
        putenv('SCHEMATIC_S3_BUCKET');
        $this->expectException('Exception');
        $schema = $this->getSchemaTestFile();
        $override = $this->getOverrideTestFile();
        Data::fromYaml($schema, $override);
    }

    public function testToYamlIsValidYaml()
    {
        putenv('S3_BUCKET=bucket_name');
        $dataModel = $this->generateDataModel();
        $yaml = Data::toYaml($dataModel);
        $this->assertInternalType('array', Yaml::parse($yaml));
    }

    public function testToYamlContainsCorrectText()
    {
        putenv('S3_BUCKET=bucket_name');
        $dataModel = $this->generateDataModel();
        $yaml = Data::toYaml($dataModel);
        $this->assertContains('bucket_name', $yaml);
    }

    public function testToYamlOverride()
    {
        putenv('S3_BUCKET=bucket_name');
        $dataModel = $this->generateDataModel();
        $override = $this->getOverrideTestFile();
        $yaml = Data::toYaml($dataModel, $override);
        $this->assertContains("'%S3_BUCKET%'", $yaml);
    }
}
