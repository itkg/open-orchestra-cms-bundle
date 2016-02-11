<?php

namespace OpenOrchestra\UserAdminBundle\Tests\Form\Type;

use OpenOrchestra\UserAdminBundle\Form\Type\UserType;
use Phake;

/**
 * Class UserTypeTest
 */
class UserTypeTest extends AbstractUserTypeTest
{
    /**
     * @var UserType
     */
    protected $form;

    protected $class = 'OpenOrchestra\UserBundle\Document\User';
    protected $twig;

    /**
     * Set up the test
     */
    public function setUp()
    {
        parent::setUp();
        $this->twig = Phake::mock('Twig_Environment');
        $parameters = array(0 => 'en', 1 => 'fr');

        $this->form = new UserType($this->class, $parameters);
    }

    /**
     * Test name
     */
    public function testName()
    {
        $this->assertSame('oo_user', $this->form->getName());
    }

    /**
     * Test builder
     */
    public function testBuilder()
    {
        $this->form->buildForm($this->builder, array());

        Phake::verify($this->builder, Phake::times(6))->add(Phake::anyParameters());
    }

    /**
     * Test configureOptions
     */
    public function testResolver()
    {
        $this->form->configureOptions($this->resolver);
        Phake::verify($this->resolver)->setDefaults(array(
            'data_class' => $this->class
        ));
        Phake::verify($this->resolver)->setDefaults(Phake::anyParameters());
    }
}
