<?php

namespace OpenOrchestra\BackofficeBundle\Tests\Manager;

use OpenOrchestra\BaseBundle\Tests\AbstractTest\AbstractBaseTestCase;
use OpenOrchestra\ModelInterface\Model\AreaContainerInterface;
use Doctrine\Common\Collections\ArrayCollection;
use OpenOrchestra\BackofficeBundle\Manager\AreaManager;
use OpenOrchestra\ModelBundle\Document\Area;
use Phake;
use OpenOrchestra\ModelInterface\Model\NodeInterface;

/**
 * Class AreaManagerTest
 */
class AreaManagerTest extends AbstractBaseTestCase
{
    /**
     * @var AreaManager
     */
    protected $manager;

    protected $blockParameterManager;
    protected $language = 'fr';
    protected $nodeRepository;
    protected $block;
    protected $node;

    /**
     * Set up the test
     */
    public function setUp()
    {
        $this->blockParameterManager = Phake::mock('OpenOrchestra\BackofficeBundle\StrategyManager\BlockParameterManager');
        $this->nodeRepository = Phake::mock('OpenOrchestra\ModelInterface\Repository\NodeRepositoryInterface');

        $this->node = Phake::mock('OpenOrchestra\ModelInterface\Model\NodeInterface');
        Phake::when($this->node)->getLanguage()->thenReturn($this->language);

        $this->block = Phake::mock('OpenOrchestra\ModelInterface\Model\BlockInterface');

        $this->manager = new AreaManager($this->nodeRepository, $this->blockParameterManager, 'OpenOrchestra\ModelBundle\Document\Area');
    }

    /**
     * @param AreaContainerInterface $areaContainer
     * @param string                 $areaId
     * @param AreaContainerInterface $expectedArea
     *
     * @dataProvider provideAreaAndAreaId
     */
    public function testDeleteAreaFromAreas(AreaContainerInterface $areaContainer, $areaId, AreaContainerInterface $expectedArea)
    {
        $this->manager->deleteAreaFromContainer($areaContainer, $areaId);

        $this->assertTrue(
            $this->arrayContains($expectedArea->getAreas(), $areaContainer->getAreas())
            && $this->arrayContains($areaContainer->getAreas(), $expectedArea->getAreas())
        );
    }

    /**
     * @param ArrayCollection $refArray
     * @param ArrayCollection $includedArray
     *
     * @return bool
     */
    protected function arrayContains(ArrayCollection $refArray, ArrayCollection $includedArray)
    {
        if (count($includedArray) > 0) {
            foreach($includedArray as $element) {
                if (!$refArray->contains($element)) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * @return array
     */
    public function provideAreaAndAreaId()
    {
        $area1 = Phake::mock('OpenOrchestra\ModelInterface\Model\AreaInterface');
        Phake::when($area1)->getAreaId()->thenReturn('area1');

        $area2 = Phake::mock('OpenOrchestra\ModelInterface\Model\AreaInterface');
        Phake::when($area2)->getAreaId()->thenReturn('area2');

        $area3 = Phake::mock('OpenOrchestra\ModelInterface\Model\AreaInterface');
        Phake::when($area3)->getAreaId()->thenReturn('area3');

        $emptyArea = new Area();

        $area = new Area();
        $area->addArea($area1); $area->addArea($area2); $area->addArea($area3);

        $filteredArea = new Area();
        $filteredArea->addArea($area1); $filteredArea->addArea($area3);

        return array(
            array($emptyArea, 'miscId', $emptyArea),
            array($area, 'miscId', $area),
            array($area, 'area2', $filteredArea)
        );
    }

    /**
     * @param array  $oldBlocks
     * @param array  $newBlocks
     * @param string $areaId
     * @param string $nodeId
     *
     * @dataProvider provideBlocks
     */
    public function testDeleteAreaFromBlock($oldBlocks, $newBlocks, $areaId, $nodeId)
    {
        Phake::when($this->node)->getBlock(Phake::anyParameters())->thenReturn($this->block);
        Phake::when($this->node)->getId()->thenReturn($nodeId);

        $this->manager->deleteAreaFromBlock($oldBlocks, $newBlocks, $areaId, $this->node);

        Phake::verify($this->node, Phake::times(1))->getBlock(Phake::anyParameters());
        Phake::verify($this->block, Phake::times(1))->removeAreaRef($areaId, $nodeId);
        Phake::verify($this->node, Phake::times(1))->getId();
    }

    /**
     * @return array
     */
    public function provideBlocks()
    {
        return array(
            array(
                array(
                    array('nodeId' => 0, 'blockId' => 0),
                    array('nodeId' => 0, 'blockId' => 1),
                    array('nodeId' => 0, 'blockId' => 2),
                    array('nodeId' => 0, 'blockId' => 3),
                ),
                array(
                    array('nodeId' => 0, 'blockId' => 0),
                    array('nodeId' => 0, 'blockId' => 1),
                    array('nodeId' => 0, 'blockId' => 3),
                ),
                'test2',
                'node-test'
            ),
        );
    }

    /**
     * @param array  $oldBlocks
     * @param array  $newBlocks
     * @param string $areaId
     * @param string $nodeId
     * @param string $nodeTransverseId
     *
     * @dataProvider provideBlocksWithNodeId
     */
    public function testDeleteAreaFromBlockWithNodeId($oldBlocks, $newBlocks, $areaId, $nodeId, $nodeTransverseId)
    {
        $siteId = 2;
        Phake::when($this->nodeRepository)
            ->findInLastVersion(Phake::anyParameters())->thenReturn($this->node);
        Phake::when($this->node)->getBlock(Phake::anyParameters())->thenReturn($this->block);
        Phake::when($this->node)->getId()->thenReturn($nodeId);
        Phake::when($this->node)->getSiteId()->thenReturn($siteId);

        $this->manager->deleteAreaFromBlock($oldBlocks, $newBlocks, $areaId, $this->node);

        Phake::verify($this->nodeRepository, Phake::times(1))
            ->findInLastVersion($nodeTransverseId, $this->language, $siteId);
        Phake::verify($this->node, Phake::times(2))->getBlock(Phake::anyParameters());
        Phake::verify($this->block, Phake::times(1))->removeAreaRef($areaId, $nodeId);
        Phake::verify($this->node, Phake::times(1))->getId();
    }

    /**
     * @return array
     */
    public function provideBlocksWithNodeId()
    {
        return array(
            array(
                array(
                    array('nodeId' => 'transverse', 'blockId' => 0),
                    array('nodeId' => 'transverse', 'blockId' => 1),
                    array('nodeId' => 'transverse', 'blockId' => 2),
                    array('nodeId' => 'transverse', 'blockId' => 3),
                ),
                array(
                    array('nodeId' => 'transverse', 'blockId' => 0),
                    array('nodeId' => 'transverse', 'blockId' => 1),
                    array('nodeId' => 'transverse', 'blockId' => 3),
                ),
                'test2',
                'node-test',
                'transverse'
            ),
        );
    }

    /**
     * @param NodeInterface $node
     * @param NodeInterface $nodeTransverse
     *
     * @dataProvider provideNodeWithAreaAndBlock
     */
    public function testAreaConsistency($node, $nodeTransverse)
    {
        Phake::when($this->nodeRepository)->findInLastVersion(Phake::anyParameters())->thenReturn($nodeTransverse);
        Phake::when($this->blockParameterManager)->getBlockParameter(Phake::anyParameters())->thenReturn(array());

        $this->assertTrue($this->manager->areaConsistency($node));
    }

    /**
     * @return array
     */
    public function provideNodeWithAreaAndBlock()
    {
        $block1 = Phake::mock('OpenOrchestra\ModelInterface\Model\BlockInterface');
        Phake::when($block1)->getAreas()->thenReturn(array(
            array('nodeId' => NodeInterface::ROOT_NODE_ID, 'areaId' => 'menu'),
            array('nodeId' => 0, 'areaId' => 'main')
        ));

        $block2 = Phake::mock('OpenOrchestra\ModelInterface\Model\BlockInterface');
        Phake::when($block2)->getAreas()->thenReturn(array(
            array('nodeId' => NodeInterface::ROOT_NODE_ID, 'areaId' => 'footer'),
            array('nodeId' => 0, 'areaId' => 'main')
        ));

        $block3 = Phake::mock('OpenOrchestra\ModelInterface\Model\BlockInterface');
        Phake::when($block3)->getAreas()->thenReturn(array(
            array('nodeId' => 0, 'areaId' => 'main')
        ));

        $areaMenu = Phake::mock('OpenOrchestra\ModelInterface\Model\AreaInterface');
        Phake::when($areaMenu)->getAreaId()->thenReturn('menu');
        Phake::when($areaMenu)->getAreas()->thenReturn(array());
        Phake::when($areaMenu)->getBlocks()->thenReturn(array(array('nodeId' => 0, 'blockId' => 0, 'blockParameter' => array())));

        $areaFooter = Phake::mock('OpenOrchestra\ModelInterface\Model\AreaInterface');
        Phake::when($areaFooter)->getAreaId()->thenReturn('footer');
        Phake::when($areaFooter)->getAreas()->thenReturn(array());
        Phake::when($areaFooter)->getBlocks()->thenReturn(array(
            array('nodeId' => 0, 'blockId' => 1, 'blockParameter' => array()),
        ));

        $areaMain = Phake::mock('OpenOrchestra\ModelInterface\Model\AreaInterface');
        Phake::when($areaMain)->getAreaId()->thenReturn('main');
        Phake::when($areaMain)->getBlocks()->thenReturn(array());
        Phake::when($areaMain)->getAreas()->thenReturn(array($areaMenu, $areaFooter));

        $areaMain2 = Phake::mock('OpenOrchestra\ModelInterface\Model\AreaInterface');
        Phake::when($areaMain2)->getAreaId()->thenReturn('main');
        Phake::when($areaMain2)->getAreas()->thenReturn(array());
        Phake::when($areaMain2)->getBlocks()->thenReturn(array(
            array('nodeId' => 0, 'blockId' => 0, 'blockParameter' => array()),
            array('nodeId' => 'home', 'blockId' => 1, 'blockParameter' => array()),
            array('nodeId' => 0, 'blockId' => 2, 'blockParameter' => array()),
        ));

        $node = Phake::mock('OpenOrchestra\ModelInterface\Model\NodeInterface');
        Phake::when($node)->getNodeId()->thenReturn(NodeInterface::ROOT_NODE_ID);
        Phake::when($node)->getAreas()->thenReturn(array($areaMain));
        Phake::when($node)->getBlocks()->thenReturn(array($block1, $block2));
        Phake::when($node)->getBlock(0)->thenReturn($block1);
        Phake::when($node)->getBlock(1)->thenReturn($block2);

        $node2 = Phake::mock('OpenOrchestra\ModelInterface\Model\NodeInterface');
        Phake::when($node2)->getNodeId()->thenReturn('home');
        Phake::when($node2)->getAreas()->thenReturn(array($areaMain2));
        Phake::when($node2)->getBlocks()->thenReturn(array($block1, $block2, $block3));
        Phake::when($node2)->getBlock(0)->thenReturn($block1);
        Phake::when($node2)->getBlock(1)->thenReturn($block2);
        Phake::when($node2)->getBlock(2)->thenReturn($block3);

        $node3 = Phake::mock('OpenOrchestra\ModelInterface\Model\NodeInterface');
        Phake::when($node3)->getNodeId()->thenReturn('home');
        Phake::when($node3)->getAreas()->thenReturn(array($areaMain2));
        Phake::when($node3)->getBlocks()->thenReturn(array($block1, $block2, $block3));
        Phake::when($node3)->getBlock(0)->thenReturn($block1);
        Phake::when($node3)->getBlock(1)->thenReturn($block2);
        Phake::when($node3)->getBlock(2)->thenReturn($block3);

        return array(
            array($node, $node2),
            array($node, $node3),
            array($node2, $node3),
        );
    }

    /**
     * @param NodeInterface $node
     * @param NodeInterface $nodeTransverse
     *
     * @dataProvider provideNodeWithAreaAndBlock
     */
    public function testFailingAreaConsistencyOnBlockParameter($node, $nodeTransverse)
    {
        Phake::when($this->nodeRepository)->findInLastVersion(Phake::anyParameters())->thenReturn($nodeTransverse);
        Phake::when($this->blockParameterManager)->getBlockParameter(Phake::anyParameters())->thenReturn(array('newsId'));

        $this->assertFalse($this->manager->areaConsistency($node));
    }

    /**
     * @param NodeInterface $node
     * @param NodeInterface $nodeTransverse
     *
     * @dataProvider provideFailingNodeWithAreaAndBlock
     */
    public function testFailingAreaConsistencyOnBlockReference($node, $nodeTransverse)
    {
        Phake::when($this->nodeRepository)->findInLastVersion(Phake::anyParameters())->thenReturn($nodeTransverse);

        $this->assertFalse($this->manager->areaConsistency($node));
    }

    /**
     * @return array
     */
    public function provideFailingNodeWithAreaAndBlock()
    {
        $block1 = Phake::mock('OpenOrchestra\ModelInterface\Model\BlockInterface');
        Phake::when($block1)->getAreas()->thenReturn(array(
            array('nodeId' => 0, 'areaId' => 'main')
        ));

        $block2 = Phake::mock('OpenOrchestra\ModelInterface\Model\BlockInterface');
        Phake::when($block2)->getAreas()->thenReturn(array(
            array('nodeId' => 0, 'areaId' => 'main'),
        ));

        $block3 = Phake::mock('OpenOrchestra\ModelInterface\Model\BlockInterface');
        Phake::when($block3)->getAreas()->thenReturn(array(
            array('nodeId' => 0, 'areaId' => 'main')
        ));

        $block3 = Phake::mock('OpenOrchestra\ModelInterface\Model\BlockInterface');
        Phake::when($block3)->getAreas()->thenReturn(array(
            array('nodeId' => 0, 'areaId' => 'main')
        ));

        $areaMenu = Phake::mock('OpenOrchestra\ModelInterface\Model\AreaInterface');
        Phake::when($areaMenu)->getAreaId()->thenReturn('menu');
        Phake::when($areaMenu)->getBlocks()->thenReturn(array(array('nodeId' => 0, 'blockId' => 0)));

        $areaFooter = Phake::mock('OpenOrchestra\ModelInterface\Model\AreaInterface');
        Phake::when($areaFooter)->getAreaId()->thenReturn('footer');
        Phake::when($areaFooter)->getBlocks()->thenReturn(array(
            array('nodeId' => 0, 'blockId' => 1),
            array('nodeId' => NodeInterface::TRANSVERSE_NODE_ID, 'blockId' => 0),
        ));

        $areaFooter2 = Phake::mock('OpenOrchestra\ModelInterface\Model\AreaInterface');
        Phake::when($areaFooter2)->getAreaId()->thenReturn('footer');
        Phake::when($areaFooter2)->getBlocks()->thenReturn(array(
            array('nodeId' => 0, 'blockId' => 1),
            array('nodeId' => NodeInterface::TRANSVERSE_NODE_ID, 'blockId' => 0),
        ));

        $areaMain = Phake::mock('OpenOrchestra\ModelInterface\Model\AreaInterface');
        Phake::when($areaMain)->getAreaId()->thenReturn('main');
        Phake::when($areaMain)->getBlocks()->thenReturn(array());
        Phake::when($areaMain)->getAreas()->thenReturn(array($areaMenu, $areaFooter));

        $areaMain2 = Phake::mock('OpenOrchestra\ModelInterface\Model\AreaInterface');
        Phake::when($areaMain2)->getAreaId()->thenReturn('main');
        Phake::when($areaMain2)->getAreas()->thenReturn(array());
        Phake::when($areaMain2)->getBlocks()->thenReturn(array(
            array('nodeId' => 0, 'blockId' => 0),
            array('nodeId' => 0, 'blockId' => 1),
            array('nodeId' => 0, 'blockId' => 2),
        ));

        $areaMain3 = Phake::mock('OpenOrchestra\ModelInterface\Model\AreaInterface');
        Phake::when($areaMain3)->getAreaId()->thenReturn('main');
        Phake::when($areaMain3)->getBlocks()->thenReturn(array());
        Phake::when($areaMain3)->getAreas()->thenReturn(array($areaMenu, $areaFooter2));

        $node = Phake::mock('OpenOrchestra\ModelInterface\Model\NodeInterface');
        Phake::when($node)->getNodeId()->thenReturn(NodeInterface::ROOT_NODE_ID);
        Phake::when($node)->getAreas()->thenReturn(array($areaMain));
        Phake::when($node)->getBlocks()->thenReturn(array($block1, $block2));
        Phake::when($node)->getBlock(0)->thenReturn($block1);
        Phake::when($node)->getBlock(1)->thenReturn($block2);

        $node2 = Phake::mock('OpenOrchestra\ModelInterface\Model\NodeInterface');
        Phake::when($node2)->getNodeId()->thenReturn(NodeInterface::TRANSVERSE_NODE_ID);
        Phake::when($node2)->getAreas()->thenReturn(array($areaMain2));
        Phake::when($node2)->getBlocks()->thenReturn(array($block1, $block2, $block3));
        Phake::when($node2)->getBlock(0)->thenReturn($block1);
        Phake::when($node2)->getBlock(1)->thenReturn($block2);
        Phake::when($node2)->getBlock(2)->thenReturn($block3);

        $node3 = Phake::mock('OpenOrchestra\ModelInterface\Model\NodeInterface');
        Phake::when($node3)->getNodeId()->thenReturn(NodeInterface::ROOT_NODE_ID);
        Phake::when($node3)->getAreas()->thenReturn(array($areaMain3));
        Phake::when($node3)->getBlocks()->thenReturn(array($block1, $block2));
        Phake::when($node3)->getBlock(0)->thenReturn($block1);
        Phake::when($node3)->getBlock(1)->thenReturn($block2);

        return array(
            array($node, $node2),
            array($node3, $node2),
        );
    }
}
