<?php

namespace OpenOrchestra\Workflow\Tests\Form\Type\Component;

use OpenOrchestra\BaseBundle\Tests\AbstractTest\AbstractBaseTestCase;
use Phake;
use OpenOrchestra\Workflow\Form\Type\Component\WorkflowProfileTransitionsType;

/**
 * Class WorkflowProfileTransitionsTypeTest
 */
class WorkflowProfileTransitionsTypeTest extends AbstractBaseTestCase
{
    protected $form;
    protected $dataClass = 'data-class';

    /**
     * Set up the test
     */
    public function setUp()
    {
        $this->form = new WorkflowProfileTransitionsType($this->dataClass);
    }

    /**
     * Test instance
     */
    public function testInstance()
    {
        $this->assertInstanceOf('Symfony\Component\Form\AbstractType', $this->form);
    }

    /**
     * Test getName
     */
    public function testGetName()
    {
        $this->assertSame('oo_workflow_profile_transitions', $this->form->getName());
    }

    public function testBuildForm()
    {
        $builder = Phake::mock('Symfony\Component\Form\FormBuilderInterface');
        $options = array(
            'statuses' => array($this->generateStatus('1'), $this->generateStatus('2')),
            'locale'   => 'fakeLocale'
        );
        $expectedChoices = array(
            '1-1' => '1-1',
            '1-2' => '1-2',
            '2-1' => '2-1',
            '2-2' => '2-2'
        );

        $this->form->buildForm($builder, $options);

        Phake::verify($builder)->add('transitions', 'oo_workflow_transitions_collection', array(
            'required' => false,
            'choices'  => $expectedChoices,
            'statuses' => $options['statuses'],
            'locale'   => $options['locale']
        ));
    }

    /**
     * Test configureOptions
     */
    public function testConfigureOptions()
    {
        $resolver = Phake::mock('Symfony\Component\OptionsResolver\OptionsResolver');

        $this->form->configureOptions($resolver);

        Phake::verify($resolver)->setDefaults(array(
            'data_class' => $this->dataClass,
            'statuses'   => array(),
            'locale'     => 'en'
        ));
    }

    /**
     * Generate a Phake status
     *
     * @param string $statusId
     *
     * @return Phake_IMock
     */
    protected function generateStatus($statusId)
    {
        $status = Phake::mock('OpenOrchestra\ModelInterface\Model\StatusInterface');
        Phake::when($status)->getId()->thenReturn($statusId);
        Phake::when($status)->getLabel(Phake::anyParameters())->thenReturn('label-' . $statusId);

        return $status;
    }
}
