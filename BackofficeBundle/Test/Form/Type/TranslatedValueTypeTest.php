<?php

namespace PHPOrchestra\BackofficeBundle\Test\Form\Type;

use Phake;
use PHPOrchestra\BackofficeBundle\Form\Type\TranslatedValueType;

/**
 * Class TranslatedValueTypeTest
 */
class TranslatedValueTypeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var TranslatedValueType
     */
    protected $form;

    protected $builder;

    /**
     * Set up the test
     */
    public function setUp()
    {
        $this->builder = Phake::mock('Symfony\Component\Form\FormBuilder');

        $this->form = new TranslatedValueType();
    }

    /**
     * Test instance and name
     */
    public function testNameAndInstance()
    {
        $this->assertInstanceOf('Symfony\Component\Form\AbstractType', $this->form);
        $this->assertSame('translated_value', $this->form->getName());
    }

    /**
     * Option resolver
     */
    public function testDefaultOption()
    {
        $resolver = Phake::mock('Symfony\Component\OptionsResolver\OptionsResolverInterface');

        $this->form->setDefaultOptions($resolver);

        Phake::verify($resolver)->setDefaults(array(
            'data_class' => 'PHPOrchestra\ModelBundle\Document\TranslatedValue'
        ));
    }

    /**
     * Test buildForm
     */
    public function testBuildForm()
    {
        $this->form->buildForm($this->builder, array());

        Phake::verify($this->builder)->add(Phake::anyParameters());
        Phake::verify($this->builder)->addEventSubscriber(Phake::anyParameters());
    }
}
