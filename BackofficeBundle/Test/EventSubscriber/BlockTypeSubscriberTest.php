<?php

namespace PHPOrchestra\BackofficeBundle\Test\EventSubscriber;

use Phake;
use PHPOrchestra\BackofficeBundle\EventSubscriber\BlockTypeSubscriber;
use Symfony\Component\Form\FormEvents;

/**
 * Class BlockTypeSubscriberTest
 */
class BlockTypeSubscriberTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var BlockTypeSubscriber
     */
    protected $subscriber;

    protected $form;
    protected $event;
    protected $block;
    protected $fixedParams;
    protected $generateFormManager;

    /**
     * Set up the test
     */
    public function setUp()
    {
        $this->block = Phake::mock('PHPOrchestra\ModelInterface\Model\BlockInterface');

        $this->form = Phake::mock('Symfony\Component\Form\Form');
        Phake::when($this->form)->add(Phake::anyParameters())->thenReturn($this->form);

        $this->event = Phake::mock('Symfony\Component\Form\FormEvent');
        Phake::when($this->event)->getForm()->thenReturn($this->form);

        $this->generateFormManager = Phake::mock('PHPOrchestra\BackofficeBundle\StrategyManager\GenerateFormManager');

        $this->fixedParams = array('component', 'submit', 'label', 'class', 'id');

        $this->subscriber = new BlockTypeSubscriber($this->generateFormManager, $this->fixedParams);
    }

    /**
     * Test instance
     */
    public function testInstance()
    {
        $this->assertInstanceOf('Symfony\Component\EventDispatcher\EventSubscriberInterface', $this->subscriber);
    }

    /**
     * Test subscribed events
     */
    public function testEventSubscribed()
    {
        $this->assertArrayHasKey(FormEvents::PRE_SET_DATA, $this->subscriber->getSubscribedEvents());
        $this->assertArrayHasKey(FormEvents::PRE_SUBMIT, $this->subscriber->getSubscribedEvents());
    }

    /**
     * Test with no data
     */
    public function testPreSetDataWithNoAttributes()
    {
        Phake::when($this->event)->getForm()->thenReturn($this->form);
        Phake::when($this->event)->getData()->thenReturn($this->block);

        $this->subscriber->preSetData($this->event);

        Phake::verify($this->form, Phake::times(3))->add(Phake::anyParameters());
        Phake::verify($this->generateFormManager)->buildForm($this->form, $this->block);
    }

    /**
     * @return array
     */
    public function provideKeyAndData()
    {
        return array(
            array('test', array('tentative' => 'tentative', 'test')),
            array('key', array('value' => 'tentative', 'test', 'other' => 4)),
        );
    }

    /**
     * @param array $sentData
     *
     * @dataProvider provideStringData
     */
    public function testPreSubmitWithNoPreviousDataAndStringDatas($sentData)
    {
        Phake::when($this->form)->getData()->thenReturn($this->block);
        Phake::when($this->block)->getAttributes()->thenReturn(array());

        $sentDataFull = array_merge(array('submit' => 'submit', 'component' => 'sample', 'class' => 'class', 'id' => 'htmlId'), $sentData);
        Phake::when($this->event)->getData()->thenReturn($sentDataFull);

        $this->subscriber->preSubmit($this->event);

        Phake::verify($this->block)->setAttributes($sentData);
    }

    /**
     * @return array
     */
    public function provideStringData()
    {
        return array(
            array(array('test' => 'test')),
            array(array('test' => 'test', 'tentative' => 'tentative')),
            array(array('test' => 'test', 'tentative' => 'tentative', 'number' => 5)),
        );
    }

    /**
     * @param array $sentData
     *
     * @dataProvider provideArrayData
     */
    public function testPreSubmitWithJsonEncodedData($sentData)
    {
        Phake::when($this->form)->getData()->thenReturn($this->block);
        Phake::when($this->block)->getAttributes()->thenReturn(array());

        Phake::when($this->event)->getData()->thenReturn(array('test' => json_encode($sentData)));

        $this->subscriber->preSubmit($this->event);

        Phake::verify($this->block)->setAttributes(array('test' => $sentData));
    }

    /**
     * @return array
     */
    public function provideArrayData()
    {
        return array(
            array(array()),
            array(array('tentative' => 'tentative')),
            array(array('tentative', 'test', 'autre' => 5)),
            array(array('tentative', 'test', 'autre' => array('test' => 'test'))),
        );
    }

    /**
     * @param $sentData
     *
     * @dataProvider provideStringDataWithCheckBox
     */
    public function testPreSubmitWithPreviousDataAndKeyNotInDataAndString($sentData)
    {
        $startData = array('test' => 'oldTest', 'checkbox2' => true);
        Phake::when($this->form)->getData()->thenReturn($this->block);
        Phake::when($this->form)->has('checkbox2')->thenReturn(true);
        Phake::when($this->block)->getAttributes()->thenReturn($startData);

        $sentDataFull = array_merge(array('submit' => 'submit', 'component' => 'sample', 'class' => 'class', 'id' => 'htmlId'), $sentData);
        Phake::when($this->event)->getData()->thenReturn($sentDataFull);

        $this->subscriber->preSubmit($this->event);

        Phake::verify($this->block)->setAttributes(array_merge(array('checkbox2' => false), $sentData));
    }

    /**
     * @return array
     */
    public function provideStringDataWithCheckBox()
    {
        return array(
            array(array('test' => 'test')),
            array(array('test' => 'test', 'tentative' => 'tentative')),
            array(array('test' => 'test', 'tentative' => 'tentative', 'number' => 5)),
            array(array('test' => 'test', 'tentative' => 'tentative', 'number' => 5, 'checkbox' => true)),
            array(array('test' => 'test', 'tentative' => 'tentative', 'number' => 5, 'checkbox2' => true)),
        );
    }
}
