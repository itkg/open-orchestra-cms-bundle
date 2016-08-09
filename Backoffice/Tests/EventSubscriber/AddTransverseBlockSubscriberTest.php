<?php

namespace OpenOrchestra\Backoffice\Tests\EventSubscriber;

use Doctrine\Common\Collections\ArrayCollection;
use OpenOrchestra\Backoffice\EventSubscriber\AddTransverseBlockSubscriber;
use OpenOrchestra\BaseBundle\Tests\AbstractTest\AbstractBaseTestCase;
use OpenOrchestra\ModelInterface\BlockNodeEvents;
use OpenOrchestra\ModelInterface\Model\NodeInterface;
use Phake;

/**
 * Class AddTransverseBlockSubscriberTest
 */
class AddTransverseBlockSubscriberTest extends AbstractBaseTestCase
{
    /**
     * @var AddTransverseBlockSubscriber
     */
    protected $subscriber;

    protected $nodeRepository;

    /**
     * Set up the test
     */
    public function setUp()
    {
        $this->nodeRepository = Phake::mock('OpenOrchestra\ModelInterface\Repository\NodeRepositoryInterface');

        $this->subscriber = new AddTransverseBlockSubscriber($this->nodeRepository);
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
    public function testSubscribedEvent()
    {
        $this->assertArrayHasKey(BlockNodeEvents::ADD_BLOCK_TO_NODE, $this->subscriber->getSubscribedEvents());
    }

    /**
     * @param string $type
     *
     * @dataProvider provideNonTransverseType
     */
    public function testAddTransverseBlockWithGeneralNode($type)
    {
        $block = Phake::mock('OpenOrchestra\ModelInterface\Model\BlockInterface');
        $node = Phake::mock('OpenOrchestra\ModelInterface\Model\NodeInterface');
        Phake::when($node)->getNodeType()->thenReturn($type);

        $event = Phake::mock('OpenOrchestra\ModelInterface\Event\BlockNodeEvent');
        Phake::when($event)->getBlock()->thenReturn($block);
        Phake::when($event)->getNode()->thenReturn($node);

        $this->subscriber->addTransverseBlock($event);

        Phake::verifyNoInteraction($this->nodeRepository);
    }

    /**
     * @return array
     */
    public function provideNonTransverseType()
    {
        return array(
            array(NodeInterface::TYPE_DEFAULT),
            array(NodeInterface::TYPE_ERROR),
        );
    }

    /**
     * Test with a transverse block
     */
    public function testAddTransverseBlockWithTransverseNode()
    {
        $siteId = 'siteId';

        $block = Phake::mock('OpenOrchestra\ModelInterface\Model\BlockInterface');
        $node = Phake::mock('OpenOrchestra\ModelInterface\Model\NodeInterface');
        Phake::when($node)->getNodeType()->thenReturn(NodeInterface::TYPE_TRANSVERSE);
        Phake::when($node)->getSiteId()->thenReturn($siteId);
        Phake::when($node)->getId()->thenReturn('mongoId');
        Phake::when($node)->getNodeId()->thenReturn('nodeId');

        $event = Phake::mock('OpenOrchestra\ModelInterface\Event\BlockNodeEvent');
        Phake::when($event)->getBlock()->thenReturn($block);
        Phake::when($event)->getNode()->thenReturn($node);

        $blockIndex = 8;

        $rootArea = Phake::mock('OpenOrchestra\ModelInterface\Model\AreaInterface');
        $area = Phake::mock('OpenOrchestra\ModelInterface\Model\AreaInterface');
        $areas = new ArrayCollection(array($area));
        Phake::when($rootArea)->getAreas()->thenReturn($areas);

        $otherNode = Phake::mock('OpenOrchestra\ModelInterface\Model\NodeInterface');
        Phake::when($otherNode)->getRootArea()->thenReturn($rootArea);
        Phake::when($otherNode)->getBlockIndex(Phake::anyParameters())->thenReturn($blockIndex);

        $otherNodes = array($otherNode, $node, $otherNode);
        Phake::when($this->nodeRepository)->findByNodeIdAndNodeTypeAndSite(Phake::anyParameters())->thenReturn($otherNodes);

        $this->subscriber->addTransverseBlock($event);

        Phake::verify($this->nodeRepository)->findByNodeIdAndNodeTypeAndSite('nodeId', NodeInterface::TYPE_TRANSVERSE, $siteId);
        Phake::verify($node, Phake::never())->addBlock(Phake::anyParameters());
        Phake::verify($otherNode, Phake::times(2))->addBlock(Phake::anyParameters());
        Phake::verify($area, Phake::times(2))->addBlock(array('nodeId' => 0, 'blockId' => $blockIndex));
    }
}
