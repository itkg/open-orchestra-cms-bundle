<?php

namespace PHPOrchestra\BackofficeBundle\Test\Form\Type;

use Phake;
use PHPOrchestra\BackofficeBundle\Form\Type\NodeType;

/**
 * Description of NodeTypeTest
 */
class NodeTypeTest extends \PHPUnit_Framework_TestCase
{
    protected $nodeType;
    protected $nodeClass = 'nodeClass';

    /**
     * Set up the test
     */
    public function setUp()
    {
        $this->nodeType = new NodeType($this->nodeClass);
    }

    /**
     * test the build form
     */
    public function testBuildForm()
    {
        $formBuilderMock = Phake::mock('Symfony\Component\Form\FormBuilder');
        Phake::when($formBuilderMock)->add(Phake::anyParameters())->thenReturn($formBuilderMock);

        $this->nodeType->buildForm($formBuilderMock, array());

        Phake::verify($formBuilderMock, Phake::times(8))->add(Phake::anyParameters());
        Phake::verify($formBuilderMock, Phake::never())->addModelTransformer(Phake::anyParameters());
    }

    /**
     * Test the default options
     */
    public function testSetDefaultOptions()
    {
        $resolverMock = Phake::mock('Symfony\Component\OptionsResolver\OptionsResolverInterface');

        $this->nodeType->setDefaultOptions($resolverMock);

        Phake::verify($resolverMock)->setDefaults(array(
            'data_class' => $this->nodeClass,
        ));
    }

    /**
     * Test the form name
     */
    public function testGetName()
    {
        $this->assertEquals('node', $this->nodeType->getName());
    }
}
