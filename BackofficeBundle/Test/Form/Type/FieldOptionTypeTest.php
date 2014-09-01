<?php

namespace PHPOrchestra\BackofficeBundle\Test\Form\Type;

use Phake;
use PHPOrchestra\BackofficeBundle\Form\Type\FieldOptionType;

/**
 * Class FieldOptionTypeTest
 */
class FieldOptionTypeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var FieldOptionType
     */
    protected $form;

    protected $builder;
    protected $resolver;
    protected $translator;
    protected $translatedLabel = 'existing option';

    /**
     * Set up the test
     */
    public function setUp()
    {
        $this->builder = Phake::mock('Symfony\Component\Form\FormBuilder');
        Phake::when($this->builder)->add(Phake::anyParameters())->thenReturn($this->builder);

        $this->resolver = Phake::mock('Symfony\Component\OptionsResolver\OptionsResolverInterface');

        $this->translator = Phake::mock('Symfony\Component\Translation\TranslatorInterface');
        Phake::when($this->translator)->trans(Phake::anyParameters())->thenReturn($this->translatedLabel);

        $this->form = new FieldOptionType($this->translator);
    }

    /**
     * Test name
     */
    public function testName()
    {
        $this->assertSame('field_option', $this->form->getName());
    }

    /**
     * Test resolver
     */
    public function testResolver()
    {
        $this->form->setDefaultOptions($this->resolver);

        Phake::verify($this->resolver)->setDefaults(array(
            'data_class' => 'PHPOrchestra\ModelBundle\Document\FieldOption',
            'label' => $this->translatedLabel,
        ));
        Phake::verify($this->translator)->trans(Phake::anyParameters());
    }

    /**
     * Test form builder
     */
    public function testFormBuilder()
    {
        $this->form->buildForm($this->builder, array());

        Phake::verify($this->builder, Phake::times(2))->add(Phake::anyParameters());
    }
}
