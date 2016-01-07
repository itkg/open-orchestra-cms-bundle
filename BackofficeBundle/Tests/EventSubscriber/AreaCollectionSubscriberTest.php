<?php

namespace OpenOrchestra\BackofficeBundle\Tests\EventSubscriber;

use OpenOrchestra\BaseBundle\Tests\AbstractTest\AbstractBaseTestCase;
use Phake;
use OpenOrchestra\BackofficeBundle\EventSubscriber\AreaCollectionSubscriber;
use Symfony\Component\Form\FormEvents;

/**
 * Class AreaCollectionSubscriberTest
 */
class AreaCollectionSubscriberTest extends AbstractBaseTestCase
{
    /**
     * @var AreaCollectionSubscriber
     */
    protected $subscriber;

    protected $form;
    protected $event;
    protected $areaClass;
    protected $areaContainer;
    protected $translator;

    /**
     * Set up the test
     */
    public function setUp()
    {
        $this->areaClass = 'OpenOrchestra\ModelBundle\Document\Area';

        $this->areaContainer = Phake::mock('OpenOrchestra\ModelInterface\Model\AreaContainerInterface');
        $this->translator = Phake::mock('Symfony\Component\Translation\TranslatorInterface');
        $this->form = Phake::mock('Symfony\Component\Form\FormInterface');

        Phake::when($this->form)->add(Phake::anyParameters())->thenReturn($this->form);
        Phake::when($this->form)->getData()->thenReturn($this->areaContainer);

        $this->event = Phake::mock('Symfony\Component\Form\FormEvent');
        Phake::when($this->event)->getForm()->thenReturn($this->form);

        $this->subscriber = new AreaCollectionSubscriber($this->areaClass, $this->translator);
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
        $this->assertArrayHasKey(FormEvents::PRE_SUBMIT, $this->subscriber->getSubscribedEvents());
        $this->assertArrayHasKey(FormEvents::PRE_SET_DATA, $this->subscriber->getSubscribedEvents());
    }

    /**
     * Test with no addition
     */
    public function testWithNoNewArea()
    {
        $newBlocks = array();
        Phake::when($this->event)->getData()->thenReturn($newBlocks);

        $this->subscriber->preSubmit($this->event);

        Phake::verify($this->areaContainer, Phake::never())->addBlock(Phake::anyParameters());
    }

    /**
     * @param array $newAreas
     *
     * @dataProvider provideNewAreas
     */
    public function testWithMultipleAreaAddition($newAreas)
    {
        Phake::when($this->event)->getData()->thenReturn(array('newAreas' => $newAreas));

        $this->subscriber->preSubmit($this->event);

        Phake::verify($this->areaContainer, Phake::never())->addBlock(Phake::anyParameters());
        Phake::verify($this->areaContainer, Phake::times(count($newAreas)))->addArea(Phake::anyParameters());
    }

    /**
     * @return array
     */
    public function provideNewAreas()
    {
        return array(
            array(array('sample')),
            array(array('sample', 'Test')),
            array(array('sample', 'new_area')),
        );
    }

    /**
     * @param array $blockArray
     *
     * @dataProvider provideAreaOrBlockNumber
     */
    public function testPreSetData($blockArray)
    {
        Phake::when($this->event)->getData()->thenReturn($this->areaContainer);

        Phake::when($this->areaContainer)->getBlocks()->thenReturn($blockArray);
        Phake::when($this->translator)->trans('open_orchestra_backoffice.form.area.add_sub')->thenReturn('Add');
        Phake::when($this->translator)->trans('open_orchestra_backoffice.form.area.id_sub')->thenReturn('Area id');
        Phake::when($this->translator)->trans('open_orchestra_backoffice.form.area.remove_sub')->thenReturn('Remove');


        $this->subscriber->preSetData($this->event);

        Phake::verify($this->form, Phake::times(1 - count($blockArray)))->add('newAreas', 'collection', array(
            'type' => 'text',
            'allow_add' => true,
            'mapped' => false,
            'required' => false,
            'error_bubbling' => false,
            'label' => 'open_orchestra_backoffice.form.area.new_areas',
            'attr' => array(
                'data-prototype-label-add' => 'Add',
                'data-prototype-label-new' => 'Area id',
                'data-prototype-label-remove' => 'Remove',
            )
        ));
    }

    /**
     * @return array
     */
    public function provideAreaOrBlockNumber()
    {
        return array(
            array(array()),
            array(array('blocks')),
        );
    }

    /**
     * test with new node
     */
    public function testPreSetDataWithNewNode()
    {
        $nodeInterface = Phake::mock('OpenOrchestra\ModelInterface\Model\NodeInterface');
        Phake::when($nodeInterface)->getId()->thenReturn(null);
        Phake::when($this->event)->getData()->thenReturn($nodeInterface);

        $this->subscriber->preSetData($this->event);

        Phake::verify($this->form, Phake::never())->add(Phake::anyParameters());
    }
}
