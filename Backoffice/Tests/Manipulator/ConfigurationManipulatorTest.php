<?php

namespace OpenOrchestra\Backoffice\Tests\Manipulator;

use OpenOrchestra\Backoffice\Manipulator\BackofficeDisplayConfigurationManipulator;
use OpenOrchestra\BaseBundle\Tests\AbstractTest\AbstractBaseTestCase;

/**
 * Class ConfigurationManipulatorTest
 */
class ConfigurationManipulatorTest extends AbstractBaseTestCase
{
    /**
     * @var BackofficeDisplayConfigurationManipulator
     */
    protected $manipulator;

    protected $file;
    protected $baseDir;
    protected $blockName = 'test';
    protected $blockNamespace = 'OpenOrchestra\Backoffice';

    /**
     * Set up the test
     */
    public function setUp()
    {
        $this->baseDir = __DIR__ . '/files/generated';
        $this->file = $this->baseDir . '/backoffice.yml';

        $this->manipulator = new BackofficeDisplayConfigurationManipulator($this->file);
    }

    /**
     * test Instance
     */
    public function testInstance()
    {
        $this->assertInstanceOf('Sensio\Bundle\GeneratorBundle\Manipulator\Manipulator', $this->manipulator);
    }

    /**
     * Test add resources
     */
    public function testAddResourceForBackoffice()
    {
        $this->assertFileDoesNotExist($this->file);

        $this->manipulator->addResource($this->blockName, $this->blockNamespace);

        $this->assertFileEquals(__DIR__ . '/files/references/backoffice.yml', $this->file);
    }

    /**
     * @param string $file
     */
    protected function assertFileDoesNotExist($file)
    {
        if (file_exists($file)) {
            unlink($file);
        }

        $this->assertFileNotExists($file);
    }

    /**
     * @param string $class
     * @param string $namespace
     * @param string $file
     *
     * @dataProvider provideOtherType
     */
    public function testAddResourcesForTheOther($class, $namespace, $file)
    {
        $this->assertFileDoesNotExist($this->baseDir . '/' . $file);

        $manipulator = new $class($this->baseDir . '/' . $file);

        $manipulator->addResource($this->blockName, $namespace);

        $this->assertFileEquals(__DIR__ . '/files/references/' . $file, $this->baseDir . '/' . $file);
    }

    /**
     * @return array
     */
    public function provideOtherType()
    {
        return array(
            array('OpenOrchestra\Backoffice\Manipulator\FrontDisplayConfigurationManipulator', 'OpenOrchestra\DisplayBundle', 'front.yml'),
            array('OpenOrchestra\Backoffice\Manipulator\BackofficeIconConfigurationManipulator', 'OpenOrchestra\Backoffice', 'icon.yml'),
            array('OpenOrchestra\Backoffice\Manipulator\GenerateFormConfigurationManipulator', 'OpenOrchestra\Backoffice', 'generator.yml'),
        );
    }

    /**
     * Test add resources
     *
     * @expectedException OpenOrchestra\Backoffice\Exception\StrategyAlreadyCreatedException
     */
    public function testDoNotAddIfExisting()
    {
        $this->manipulator->addResource($this->blockName, $this->blockNamespace);

        $this->assertFileEquals(__DIR__ . '/files/references/backoffice.yml', $this->file);
    }
}
