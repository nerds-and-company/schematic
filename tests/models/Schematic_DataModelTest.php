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

    private function getOverrideTestFile()
    {
        return __DIR__ . '/../data/test_override.yml';
    }

    /**
     * @covers ::fromYaml
     */
    public function testRegularOverride()
    {
        putenv('SCHEMATIC_S3_BUCKET=override_bucket_name');
        $schema = $this->getSchemaTestFile();
        $override = $this->getOverrideTestFile();
        $result = Schematic_DataModel::fromYaml($schema, $override);
        $this->assertEquals('override_key', $result->assets['uploads']['settings']['keyId']);
    }

    /**
     * @covers ::fromYaml
     */
    public function testEnvironmentOverride()
    {
        putenv('SCHEMATIC_S3_BUCKET=override_bucket_name');
        $schema = $this->getSchemaTestFile();
        $override = $this->getOverrideTestFile();
        $result = Schematic_DataModel::fromYaml($schema, $override);
        $this->assertEquals('override_bucket_name', $result->assets['uploads']['settings']['bucket']);
    }

    /**
     * @covers ::fromYaml
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
        putenv('SCHEMATIC_S3_BUCKET=override_bucket_name');
        $schema = $this->getSchemaTestFile();
        $override = $this->getOverrideTestFile();
        $data_model = Schematic_DataModel::fromYaml($schema, $override);
        $yaml = Schematic_DataModel::toYaml($data_model->attributes);
        $this->assertInternalType('array', Yaml::parse($yaml));
    }

    /**
     * @covers ::toYaml
     */
    public function testToYamlContainsCorrectText()
    {
        putenv('SCHEMATIC_S3_BUCKET=override_bucket_name');
        $schema = $this->getSchemaTestFile();
        $override = $this->getOverrideTestFile();
        $data_model = Schematic_DataModel::fromYaml($schema, $override);
        $yaml = Schematic_DataModel::toYaml($data_model->attributes);
        $this->assertContains('override_bucket_name', $yaml);
    }

    /**
     * @covers ::getAttribute
     */
    public function testGetAttribute()
    {
        putenv('SCHEMATIC_S3_BUCKET=override_bucket_name');
        $schema = $this->getSchemaTestFile();
        $override = $this->getOverrideTestFile();
        $data_model = Schematic_DataModel::fromYaml($schema, $override);
        $this->assertEquals(array('test_user'), $data_model->getAttribute('users'));
    }
}
