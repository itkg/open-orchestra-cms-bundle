<?php

namespace OpenOrchestra\LogBundle\Tests\EventSubscriber;

use Phake;
use OpenOrchestra\ModelInterface\NodeEvents;
use OpenOrchestra\LogBundle\EventSubscriber\LogNodeSubscriber;

/**
 * Test LogNodeSubscriberTest
 */
class LogNodeSubscriberTest extends LogAbstractSubscriberTest
{
    protected $nodeEvent;
    protected $context;
    protected $node;

    /**
     * Set up the test
     */
    public function setUp()
    {
        parent::setUp();
        $this->node = Phake::mock('OpenOrchestra\ModelBundle\Document\Node');
        $this->nodeEvent = Phake::mock('OpenOrchestra\ModelInterface\Event\NodeEvent');
        Phake::when($this->nodeEvent)->getNode()->thenReturn($this->node);

        $this->context =  array(
            'node_id' => $this->node->getNodeId(),
            'node_version' => $this->node->getVersion(),
            'node_language' => $this->node->getLanguage(),
            'node_name' => $this->node->getName(),
        );

        $this->subscriber = new LogNodeSubscriber($this->logger);
    }

    /**
     * @return array
     */
    public function provideSubscribedEvent()
    {
        return array(
            array(NodeEvents::NODE_CREATION),
            array(NodeEvents::NODE_ADD_LANGUAGE),
            array(NodeEvents::NODE_DELETE),
            array(NodeEvents::NODE_RESTORE),
            array(NodeEvents::NODE_DUPLICATE),
            array(NodeEvents::NODE_UPDATE),
            array(NodeEvents::NODE_UPDATE_BLOCK),
            array(NodeEvents::NODE_UPDATE_BLOCK_POSITION),
        );
    }

    /**
     * Test nodeCreation
     */
    public function testNodeCreation()
    {
        $this->subscriber->nodeCreation($this->nodeEvent);

        $this->assertEventLogged('open_orchestra_log.node.create', $this->context);
    }

    /**
     * Test nodeDelete
     */
    public function testNodeDelete()
    {
        $nodeDeleteEvent = Phake::mock('OpenOrchestra\ModelInterface\Event\NodeDeleteEvent');
        Phake::when($nodeDeleteEvent)->getNodeId()->thenReturn($this->node->getNodeId());

        $this->subscriber->nodeDelete($nodeDeleteEvent);

        $this->assertEventLogged('open_orchestra_log.node.delete', array(
            'node_id' => $this->node->getNodeId(),
        ));
    }

    /**
     * Test nodeRestore
     */
    public function testNodeRestore()
    {
        $this->subscriber->nodeRestore($this->nodeEvent);

        $this->assertEventLogged('open_orchestra_log.node.restore', $this->context);
    }

    /**
     * Test nodeUpdate
     */
    public function testNodeUpdate()
    {
        $this->subscriber->nodeUpdate($this->nodeEvent);
        $this->assertEventLogged('open_orchestra_log.node.update', $this->context);
    }

    /**
     * Test nodeDuplicate
     */
    public function testNodeDuplicate()
    {
        $this->subscriber->nodeDuplicate($this->nodeEvent);
        $this->assertEventLogged('open_orchestra_log.node.duplicate', $this->context);
    }

    /**
     * Test nodeAddLanguage
     */
    public function testNodeAddLanguage()
    {
        $this->subscriber->nodeAddLanguage($this->nodeEvent);
        $this->assertEventLogged('open_orchestra_log.node.add_language', $this->context);
    }

    /**
     * Test nodeUpdateBlock
     */
    public function testNodeUpdateBlock()
    {
        $this->subscriber->nodeUpdateBlock($this->nodeEvent);
        $this->assertEventLogged('open_orchestra_log.node.block.update', $this->context);
    }

    /**
     * Test nodeUpdateBlockPosition
     */
    public function testNodeUpdateBlockPosition()
    {
        $this->subscriber->nodeUpdateBlockPosition($this->nodeEvent);
        $this->assertEventLogged('open_orchestra_log.node.block.update_position', $this->context);
    }

    /**
     * Test nodeChangeStatus
     */
    public function testNodeChangeStatus()
    {
        $this->subscriber->nodeChangeStatus($this->nodeEvent);
        $this->assertEventLogged('open_orchestra_log.node.status', $this->context);
    }
}
