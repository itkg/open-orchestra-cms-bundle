<?php

namespace OpenOrchestra\UserAdminBundle\Tests\Form\Type;

use OpenOrchestra\UserAdminBundle\Form\Type\RegistrationUserType;
use Phake;

/**
 * Class RegistrationUserTypeTest
 */
class RegistrationUserTypeTest extends AbstractUserTypeTest
{
    /**
     * @var RegistrationUserType
     */
    protected $form;

    protected $class = 'OpenOrchestra\UserBundle\Document\User';

    /**
     * Set up the test
     */
    public function setUp()
    {
        parent::setUp();
        $this->form = new RegistrationUserType($this->class);
    }

    /**
     * Test name
     */
    public function testName()
    {
        $this->assertSame('oo_registration_user', $this->form->getName());
    }

    /**
     * Test builder
     */
    public function testBuilder()
    {
        $this->form->buildForm($this->builder, array());

        Phake::verify($this->builder, Phake::times(5))->add(Phake::anyParameters());
    }

    /**
     * Test configureOptions
     */
    public function testResolver()
    {
        $this->form->configureOptions($this->resolver);

        Phake::verify($this->resolver)->setDefaults(Phake::anyParameters());
    }
}
