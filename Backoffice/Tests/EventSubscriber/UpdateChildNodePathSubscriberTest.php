<?php

namespace OpenOrchestra\Backoffice\Tests\EventSubscriber;

use Doctrine\Common\Collections\ArrayCollection;
use OpenOrchestra\BaseBundle\Tests\AbstractTest\AbstractBaseTestCase;
use Phake;
use OpenOrchestra\Backoffice\EventSubscriber\UpdateChildNodePathSubscriber;
use OpenOrchestra\ModelInterface\NodeEvents;

/**
 * Class UpdateChildNodePathSubscriberTest
 */
class UpdateChildNodePathSubscriberTest extends AbstractBaseTestCase
{
    /**
     * @var UpdateChildNodePathSubscriber
     */
    protected $subscriber;

    protected $nodeRepository;
    protected $eventDispatcher;
    protected $currentSiteManager;
    protected $siteId = 'fakeId';

    /**
     * Set up the test
     */
    public function setUp()
    {
        $this->nodeRepository = Phake::mock('OpenOrchestra\ModelInterface\Repository\NodeRepositoryInterface');

        $this->eventDispatcher = Phake::mock('Symfony\Component\EventDispatcher\EventDispatcher');

        $this->currentSiteManager = Phake::mock('OpenOrchestra\Backoffice\Context\ContextBackOfficeInterface');
        Phake::when($this->currentSiteManager)->getSiteId()->thenReturn($this->siteId);

        $this->subscriber = new UpdateChildNodePathSubscriber($this->nodeRepository, $this->eventDispatcher, $this->currentSiteManager);
    }

    /**
     * Test instance
     */
    public function testInstance()
    {
        $this->assertInstanceOf('Symfony\Component\EventDispatcher\EventSubscriberInterface', $this->subscriber);
    }

    /**
     * Test event subscribed
     */
    public function testEventSubscribed()
    {
        $this->assertArrayHasKey(NodeEvents::PATH_UPDATED, $this->subscriber->getSubscribedEvents());
    }

    /**
     * Test update path
     */
    public function testUpdatePath()
    {
        $parentNodeId = 'parent';
        $parentPath = 'parentPath';
        $son1NodeId = 'son1NodeId';
        $son2NodeId = 'son2NodeId';

        $parent = Phake::mock('OpenOrchestra\ModelInterface\Model\NodeInterface');
        Phake::when($parent)->getNodeId()->thenReturn($parentNodeId);
        Phake::when($parent)->getPath()->thenReturn($parentPath);
        $son1 = Phake::mock('OpenOrchestra\ModelInterface\Model\NodeInterface');
        Phake::when($son1)->getNodeId()->thenReturn($son1NodeId);
        $son2 = Phake::mock('OpenOrchestra\ModelInterface\Model\NodeInterface');
        Phake::when($son2)->getNodeId()->thenReturn($son2NodeId);
        $son3 = Phake::mock('OpenOrchestra\ModelInterface\Model\NodeInterface');
        Phake::when($son3)->getNodeId()->thenReturn($son2NodeId);
        $sons = new ArrayCollection();
        $sons->add($son1);
        $sons->add($son2);
        $sons->add($son3);
        Phake::when($this->nodeRepository)->findByParent(Phake::anyParameters())->thenReturn($sons);

        $event = Phake::mock('OpenOrchestra\ModelInterface\Event\NodeEvent');
        Phake::when($event)->getNode()->thenReturn($parent);

        $this->subscriber->updatePath($event);

        Phake::verify($son1)->setPath($parentPath . '/' . $son1NodeId);
        Phake::verify($son2)->setPath($parentPath . '/' . $son2NodeId);
        Phake::verify($son3)->setPath($parentPath . '/' . $son2NodeId);

        Phake::verify($this->eventDispatcher, Phake::times(2))->dispatch(Phake::anyParameters());
    }
}
