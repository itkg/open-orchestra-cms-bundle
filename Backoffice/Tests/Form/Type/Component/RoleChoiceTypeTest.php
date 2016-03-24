<?php

namespace OpenOrchestra\Backoffice\Tests\Form\Type\Component;

use OpenOrchestra\BaseBundle\Tests\AbstractTest\AbstractBaseTestCase;
use Phake;
use OpenOrchestra\Backoffice\Form\Type\Component\RoleChoiceType;

/**
 * Class RoleChoiceTypeTest
 */
class RoleChoiceTypeTest extends AbstractBaseTestCase
{
    /**
     * @var RoleChoiceType
     */
    protected $form;

    protected $roleCollector;

    /**
     * Set up the test
     */
    public function setUp()
    {
        $this->roleCollector = Phake::mock('OpenOrchestra\Backoffice\Collector\RoleCollectorInterface');

        $this->form = new RoleChoiceType($this->roleCollector, 'oo_role_choice', '');
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
        $this->assertSame('oo_role_choice', $this->form->getName());
    }

    /**
     * Test parent
     */
    public function testParent()
    {
        $this->assertSame('choice', $this->form->getParent());
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

        Phake::verify($builder, Phake::never())->add(Phake::anyParameters());
        Phake::verify($builder, Phake::never())->addEventSubscriber(Phake::anyParameters());
        Phake::verify($builder, Phake::never())->addEventListener(Phake::anyParameters());
    }

    /**
     * @param array  $roles
     *
     * @dataProvider provideRoleAndTranslation
     */
    public function testConfigureOptions(array $roles)
    {
        Phake::when($this->roleCollector)->getRoles()->thenReturn($roles);

        $resolver = Phake::mock('Symfony\Component\OptionsResolver\OptionsResolver');

        $this->form->configureOptions($resolver);

        Phake::verify($resolver)->setDefaults(array(
            'choices' => $roles
        ));

    }

    /**
     * @return array
     */
    public function provideRoleAndTranslation()
    {
        return array(
            array(array('foo' => 'bar')),
            array(array('foo' => 'bar', 'bar' => 'bar')),
            array(array('FOO' => 'bar', 'BAR' => 'bar')),
        );
    }
}
