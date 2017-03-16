<?php

namespace OpenOrchestra\LogBundle\Tests\EventSubscriber;

use Phake;
use OpenOrchestra\LogBundle\EventSubscriber\LogContentSubscriber;
use OpenOrchestra\ModelInterface\ContentEvents;

/**
 * Class LogContentSubscriberTest
 */
class LogContentSubscriberTest extends LogAbstractSubscriberTest
{
    protected $content;
    protected $contentEvent;

    /**
     * Set up the test
     */
    public function setUp()
    {
        parent::setUp();
        $this->content = Phake::mock('OpenOrchestra\ModelBundle\Document\Content');
        $this->contentEvent = Phake::mock('OpenOrchestra\ModelInterface\Event\ContentEvent');
        Phake::when($this->contentEvent)->getContent()->thenReturn($this->content);

        $this->subscriber = new LogContentSubscriber($this->logger);
    }

    /**
     * @return array
     */
    public function provideSubscribedEvent()
    {
        return array(
            array(ContentEvents::CONTENT_CREATION),
            array(ContentEvents::CONTENT_DELETE),
            array(ContentEvents::CONTENT_DUPLICATE),
            array(ContentEvents::CONTENT_UPDATE),
            array(ContentEvents::CONTENT_RESTORE),
        );
    }

    /**
     * Test contentCreation
     */
    public function testContentCreation()
    {
        $this->subscriber->contentCreation($this->contentEvent);
        $this->assertEventLogged('open_orchestra_log.content.create', array(
            'content_id' => $this->content->getContentId(),
        ));
    }

    /**
     * Test contentDelete
     */
    public function testContentDelete()
    {
        $contentDeleteEvent = Phake::mock('OpenOrchestra\ModelInterface\Event\ContentDeleteEvent');
        Phake::when($contentDeleteEvent)->getContentId()->thenReturn($this->content->getContentId());

        $this->subscriber->contentDelete($contentDeleteEvent);
        $this->assertEventLogged('open_orchestra_log.content.delete', array(
            'content_id' => $this->content->getContentId(),
        ));
    }

    /**
     * Test contentRestore
     */
    public function testContentRestore()
    {
        $this->subscriber->contentRestore($this->contentEvent);
        $this->assertEventLogged('open_orchestra_log.content.restore', array(
            'content_id' => $this->content->getContentId(),
            'content_name' => $this->content->getName(),
        ));
    }

    /**
     * Test contentUpdate
     */
    public function testContentUpdate()
    {
        $this->subscriber->contentUpdate($this->contentEvent);
        $this->assertEventLogged('open_orchestra_log.content.update', array(
            'content_id' => $this->content->getContentId(),
            'content_version' => $this->content->getVersion(),
            'content_language' => $this->content->getLanguage()
        ));
    }

    /**
     * Test contentDuplicate
     */
    public function testContentDuplicate()
    {
        $this->subscriber->contentDuplicate($this->contentEvent);
        $this->assertEventLogged('open_orchestra_log.content.duplicate', array(
            'content_id' => $this->content->getContentId(),
            'content_version' => $this->content->getVersion(),
            'content_language' => $this->content->getLanguage()
        ));
    }

    /**
     * Test contentChangeStatus
     */
    public function testContentChangeStatus()
    {
        $this->subscriber->contentChangeStatus($this->contentEvent);
        $this->assertEventLogged('open_orchestra_log.content.status', array(
            'content_id' => $this->content->getContentId(),
            'content_version' => $this->content->getVersion(),
            'content_language' => $this->content->getLanguage()
        ));
    }
}
