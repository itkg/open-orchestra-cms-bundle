<?php

namespace OpenOrchestra\Backoffice\Tests\Form\Type\Component;

use OpenOrchestra\Backoffice\Form\Type\Component\ChoicesOptionType;
use OpenOrchestra\BaseBundle\Tests\AbstractTest\AbstractBaseTestCase;
use Phake;

/**
 * Class ChoicesOptionTypeTest
 */
class ChoicesOptionTest extends AbstractBaseTestCase
{
    protected $form;
    protected $transformer;
    protected $builder;

    /**
     * Set up the test
     */
    public function setUp()
    {
        $this->builder = Phake::mock('Symfony\Component\Form\FormBuilder');
        $this->transformer = Phake::mock('OpenOrchestra\Backoffice\Form\DataTransformer\ChoicesOptionToArrayTransformer');
        $this->form = new ChoicesOptionType($this->transformer);
    }

    /**
     * Test model transformer
     */
    public function testBuildForm()
    {
        $this->form->buildForm($this->builder, array('embedded' => true));

        Phake::verify($this->builder)->addModelTransformer($this->transformer);
    }

    /**
     * Test parent
     */
    public function testGetParent()
    {
        $this->assertEquals('text', $this->form->getParent());
    }

    /**
     * test Name
     */
    public function testGetName()
    {
        $this->assertEquals('oo_choices_option', $this->form->getName());
    }
}
