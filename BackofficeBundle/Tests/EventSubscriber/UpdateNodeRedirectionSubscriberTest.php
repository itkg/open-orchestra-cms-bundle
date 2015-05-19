<?php

namespace OpenOrchestra\BackofficeBundle\Tests\EventSubscriber;

use Phake;
use OpenOrchestra\BackofficeBundle\EventSubscriber\UpdateNodeRedirectionSubscriber;
use OpenOrchestra\ModelInterface\NodeEvents;

/**
 * Test UpdateNodeRedirectionSubscriberTest
 */
class UpdateNodeRedirectionSubscriberTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var UpdateNodeRedirectionSubscriber
     */
    protected $subscriber;

    protected $node;
    protected $status;
    protected $id = 'id';
    protected $nodeEvent;
    protected $nodeRepository;
    protected $language = 'fr';
    protected $nodeId = 'nodeId';
    protected $redirectionManager;
    protected $routePattern = 'route_pattern';
    protected $currentSiteManager;

    /**
     * Set up the test
     */
    public function setUp()
    {
        $this->status = Phake::mock('OpenOrchestra\ModelInterface\Model\StatusInterface');
        Phake::when($this->status)->isPublished()->thenReturn(false);
        $this->node = Phake::mock('OpenOrchestra\ModelInterface\Model\NodeInterface');
        Phake::when($this->node)->getId()->thenReturn($this->id);
        Phake::when($this->node)->getStatus()->thenReturn($this->status);
        Phake::when($this->node)->getNodeId()->thenReturn($this->nodeId);
        Phake::when($this->node)->getParentId()->thenReturn($this->nodeId);
        Phake::when($this->node)->getLanguage()->thenReturn($this->language);
        Phake::when($this->node)->getRoutePattern()->thenReturn($this->routePattern);
        $this->nodeEvent = Phake::mock('OpenOrchestra\ModelInterface\Event\NodeEvent');
        Phake::when($this->nodeEvent)->getNode()->thenReturn($this->node);
        $this->nodeRepository = Phake::mock('OpenOrchestra\ModelInterface\Repository\NodeRepositoryInterface');
        $this->redirectionManager = Phake::mock('OpenOrchestra\BackofficeBundle\Manager\RedirectionManager');
        $this->currentSiteManager = Phake::mock('OpenOrchestra\BaseBundle\Context\CurrentSiteIdInterface');

        $this->subscriber = new UpdateNodeRedirectionSubscriber($this->nodeRepository, $this->redirectionManager, $this->currentSiteManager);
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
        $this->assertArrayHasKey(NodeEvents::NODE_CHANGE_STATUS, $this->subscriber->getSubscribedEvents());
    }

    /**
     * Test with no previous node
     */
    public function testUpdateRedirectionWithNoPerviousNode()
    {
        Phake::when($this->nodeRepository)->findByNodeIdAndLanguageAndSiteIdAndPublishedOrderedByVersion(Phake::anyParameters())
            ->thenReturn(array($this->node));

        $this->subscriber->updateRedirection($this->nodeEvent);

        Phake::verify($this->redirectionManager, Phake::never())->createRedirection(Phake::anyParameters());
    }

    /**
     * Test with no previous node with same pattern
     */
    public function testUpdateRedirectionWithPerviousNodeAndSamePattern()
    {
        $node = Phake::mock('OpenOrchestra\ModelInterface\Model\NodeInterface');
        Phake::when($node)->getId()->thenReturn('other');
        Phake::when($node)->getRoutePattern()->thenReturn($this->routePattern);

        Phake::when($this->nodeRepository)->findByNodeIdAndLanguageAndSiteIdAndPublishedOrderedByVersion(Phake::anyParameters())
            ->thenReturn(array($this->node, $node));

        $this->subscriber->updateRedirection($this->nodeEvent);

        Phake::verify($this->redirectionManager, Phake::never())->createRedirection(Phake::anyParameters());
    }

    /**
     * Test with no previous node with different pattern
     */
    public function testUpdateRedirectionWithPerviousNodeAndDifferentPattern()
    {
        Phake::when($this->status)->isPublished()->thenReturn(true);
        $oldPattern = 'oldPattern';
        $node = Phake::mock('OpenOrchestra\ModelInterface\Model\NodeInterface');
        Phake::when($node)->getId()->thenReturn('other');
        Phake::when($node)->getRoutePattern()->thenReturn($oldPattern);
        $parent = Phake::mock('OpenOrchestra\ModelInterface\Model\NodeInterface');
        Phake::when($parent)->getRoutePattern()->thenReturn($oldPattern);

        Phake::when($this->nodeRepository)->findOneByNodeIdAndLanguageWithPublishedAndLastVersionAndSiteId(Phake::anyParameters())
            ->thenReturn($parent);
        Phake::when($this->nodeRepository)->findByNodeIdAndLanguageAndSiteIdAndPublishedOrderedByVersion(Phake::anyParameters())
            ->thenReturn(array($this->node, $node));

        $this->subscriber->updateRedirection($this->nodeEvent);

        Phake::verify($this->redirectionManager)->createRedirection($oldPattern . '/' . $oldPattern, $this->nodeId, $this->language);
    }
}
