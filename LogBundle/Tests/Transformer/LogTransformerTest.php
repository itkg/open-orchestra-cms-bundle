<?php

namespace OpenOrchestra\LogBundle\Tests\Transformer;

use OpenOrchestra\BaseBundle\Tests\AbstractTest\AbstractBaseTestCase;
use Phake;
use OpenOrchestra\LogBundle\Transformer\LogTransformer;

/**
 * Class LogTransformerTest
 */
class LogTransformerTest extends AbstractBaseTestCase
{
    /**
     * @var LogTransformer
     */
    protected $transformer;

    protected $log;
    protected $translator;
    protected $facadeClass = 'OpenOrchestra\LogBundle\Facade\LogFacade';

    /**
     * Set up the test
     */
    public function setUp()
    {
        $this->translator = Phake::mock('Symfony\Component\Translation\TranslatorInterface');
        $this->log = Phake::mock('OpenOrchestra\LogBundle\Model\LogInterface');
        $this->transformer = new LogTransformer($this->facadeClass, $this->translator);
    }

    /**
     * Test getName
     */
    public function testGetName()
    {
        $this->assertSame($this->transformer->getName(), 'log');
    }

    /**
     * Test transform
     */
    public function testTransform()
    {
        Phake::when($this->log)->getContext()->thenReturn(array('node_name' => 'root'));
        Phake::when($this->log)->getExtra()->thenReturn(array());
        $facade = $this->transformer->transform($this->log);

        Phake::verify($this->translator)->trans(Phake::anyParameters());
        $this->assertInstanceOf('OpenOrchestra\LogBundle\Facade\LogFacade', $facade);
    }

    /**
     * Test transform
     */
    public function testTransformEmptyContext()
    {
        Phake::when($this->log)->getContext()->thenReturn(array());
        Phake::when($this->log)->getExtra()->thenReturn(array());
        $facade = $this->transformer->transform($this->log);

        Phake::verify($this->translator, Phake::never())->trans(Phake::anyParameters());
        $this->assertInstanceOf('OpenOrchestra\LogBundle\Facade\LogFacade', $facade);
    }
}
