<?php

namespace OpenOrchestra\UserBundle\Tests\Form\Type;

use Phake;
use OpenOrchestra\BackofficeBundle\Form\Type\RedirectionType;

/**
 * Class RedirectionTypeTest
 */
class RedirectionTypeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var RedirectionType
     */
    protected $form;

    protected $redirectionClass = 'redirectionClass';

    /**
     * Set up the test
     */
    public function setUp()
    {
        $this->form = new RedirectionType($this->redirectionClass);
    }

    /**
     * Test instance
     */
    public function testInstance()
    {
        $this->assertInstanceOf('Symfony\Component\Form\AbstractType', $this->form);
    }

    /**
     * Test name
     */
    public function testName()
    {
        $this->assertSame('oo_redirection', $this->form->getName());
    }

    /**
     * Test builder
     */
    public function testBuilder()
    {
        $builder = Phake::mock('Symfony\Component\Form\FormBuilder');
        Phake::when($builder)->add(Phake::anyParameters())->thenReturn($builder);
        Phake::when($builder)->addEventSubscriber(Phake::anyParameters())->thenReturn($builder);

        $this->form->buildForm($builder, array());

        Phake::verify($builder, Phake::times(6))->add(Phake::anyParameters());
        Phake::verify($builder, Phake::times(1))->addEventSubscriber(Phake::anyParameters());
    }

    /**
     * Test resolver
     */
    public function testConfigureOptions()
    {
        $resolver = Phake::mock('Symfony\Component\OptionsResolver\OptionsResolver');

        $this->form->configureOptions($resolver);

        Phake::verify($resolver)->setDefaults(array(
            'data_class' => $this->redirectionClass
        ));
    }
}
