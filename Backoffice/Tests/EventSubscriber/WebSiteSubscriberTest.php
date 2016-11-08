<?php

namespace OpenOrchestra\Backoffice\Tests\EventSubscriber;

use OpenOrchestra\BaseBundle\Tests\AbstractTest\AbstractBaseTestCase;
use Phake;
use OpenOrchestra\Backoffice\EventSubscriber\WebSiteSubscriber;
use Symfony\Component\Form\FormEvents;

/**
 * Class WebSiteSubscriberTest
 */
class WebSiteSubscriberTest extends AbstractBaseTestCase
{
    /**
     * @var WebSiteSubscriber
     */
    protected $subscriber;

    protected $event;
    protected $form;
    protected $data;

    /**
     * Set up the test
     */
    public function setUp()
    {
        $this->form = Phake::mock('Symfony\Component\Form\Form');
        $this->data = Phake::mock('OpenOrchestra\ModelInterface\Model\SiteInterface');
        $this->event = Phake::mock('Symfony\Component\Form\FormEvent');
        Phake::when($this->event)->getForm()->thenReturn($this->form);
        Phake::when($this->event)->getData()->thenReturn($this->data);

        $this->subscriber = new WebSiteSubscriber();
    }

    /**
     * Test subscribed events
     */
    public function testEventSubscribed()
    {
        $this->assertArrayHasKey(FormEvents::PRE_SET_DATA, $this->subscriber->getSubscribedEvents());
    }

    /**
     * @param string $siteId
     * @param array $options
     *
     * @dataProvider generateOptions
     */
    public function testOnPreSetData($siteId, $options)
    {
        Phake::when($this->data)->getSiteId()->thenReturn($siteId);

        $this->subscriber->onPreSetData($this->event);

        Phake::verify($this->form)->add('siteId', 'text', $options);
    }

    /**
     * @return array
     */
    public function generateOptions()
    {
        return array(
            array(null, array(
                'label' => 'open_orchestra_backoffice.form.website.site_id',
                'attr' => array('class' => 'generate-id-dest'),
                'group_id' => 'information',
                'sub_group_id' => 'property',
            )),
            array('siteId', array(
                'label' => 'open_orchestra_backoffice.form.website.site_id',
                'attr' => array('class' => 'generate-id-dest'),
                'group_id' => 'information',
                'sub_group_id' => 'property',
                'disabled' => true
            ))
        );
    }
}
