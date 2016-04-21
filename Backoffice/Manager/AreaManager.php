<?php

namespace OpenOrchestra\Backoffice\Manager;

use OpenOrchestra\BackofficeBundle\StrategyManager\BlockParameterManager;
use OpenOrchestra\ModelInterface\Model\AreaContainerInterface;
use OpenOrchestra\ModelInterface\Model\AreaInterface;
use OpenOrchestra\ModelInterface\Model\BlockInterface;
use OpenOrchestra\ModelInterface\Model\NodeInterface;
use OpenOrchestra\ModelInterface\Repository\NodeRepositoryInterface;

/**
 * Class AreaManager
 */
class AreaManager
{
    protected $nodeRepository;
    protected $blockParameterManager;
    protected $areaClass;

    /**
     * @param NodeRepositoryInterface $nodeRepository
     * @param BlockParameterManager   $blockParameterManager
     */
    public function __construct(NodeRepositoryInterface $nodeRepository, BlockParameterManager $blockParameterManager, $areaClass)
    {
        $this->nodeRepository = $nodeRepository;
        $this->blockParameterManager = $blockParameterManager;
        $this->areaClass = $areaClass;
    }

    /**
     * Remove an area from an AreaCollections
     *
     * @param AreaContainerInterface $areaContainer
     * @param string                 $areaId
     *
     * @return AreaContainerInterface
     */
    public function deleteAreaFromContainer(AreaContainerInterface $areaContainer, $areaId)
    {
        $areaContainer->removeAreaByAreaId($areaId);

        return $areaContainer;
    }

    /**
     * @param array         $oldBlocks
     * @param array         $newBlocks
     * @param string        $areaId
     * @param NodeInterface $node
     */
    public function deleteAreaFromBlock($oldBlocks, $newBlocks, $areaId, NodeInterface $node)
    {
        foreach ($oldBlocks as $blockReference) {
            if (!$this->blockIsInArray($blockReference, $newBlocks)) {
                $block = $node->getBlock($blockReference['blockId']);
                if ($blockReference['nodeId'] !== 0) {
                    $blockNode = $this->nodeRepository
                        ->findInLastVersion($blockReference['nodeId'], $node->getLanguage(), $node->getSiteId());
                    $block = $blockNode->getBlock($blockReference['blockId']);
                }
                $block->removeAreaRef($areaId, $node->getId());
            }
        }
    }

    /**
     * @param AreaContainerInterface $container
     * @param NodeInterface          $node
     *
     * @return bool
     */
    public function areaConsistency(AreaContainerInterface $container,NodeInterface $node = null)
    {
        if (is_null($node)) {
            $node = $container;
        }

        foreach ($container->getAreas() as $area) {
            if (is_array($area->getBlocks()) && count($area->getBlocks()) > 0) {
                if (!$this->checkBlockRef($area->getBlocks(), $node, $area)) {
                    return false;
                }
            }
            foreach ($container->getAreas() as $areaIncluded) {
                $consistency = $this->areaConsistency($areaIncluded, $node);
                if (false === $consistency) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * @param BlockInterface $block
     * @param array          $arrayBlock
     *
     * @return bool
     */
    protected function blockIsInArray($block, array $arrayBlock)
    {
        foreach ($arrayBlock as $blockTest) {
            if ($blockTest['blockId'] === $block['blockId'] && $blockTest['nodeId'] === $block['nodeId']) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param array         $blocks
     * @param NodeInterface $node
     * @param AreaInterface $area
     *
     * @return bool
     */
    protected function checkBlockRef($blocks, NodeInterface $node, AreaInterface $area)
    {
        foreach ($blocks as $block) {
            $otherNode = $node;
            if (!($block['nodeId'] === $node->getNodeId() || $block['nodeId'] === 0)) {
                $otherNode = $this->nodeRepository->findInLastVersion($block['nodeId'], $node->getLanguage(), $node->getSiteId());
            }
            $consideredBlock = $otherNode->getBlock($block['blockId']);
            if (!$this->areaIdExistInBlock($consideredBlock, $area->getAreaId())) {
                return false;
            }
            if (!$this->blockParamExistInArea($consideredBlock, $block)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param BlockInterface $block
     * @param string         $areaId
     *
     * @return bool
     */
    protected function areaIdExistInBlock(BlockInterface $block, $areaId)
    {
        $areas = $block->getAreas();

        foreach ($areas as $area) {
            if ($area['areaId'] === $areaId) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param BlockInterface $blockElement
     * @param array          $block
     *
     * @return bool
     */
    protected function blockParamExistInArea(BlockInterface $blockElement, array $block)
    {
        return $this->blockParameterManager->getBlockParameter($blockElement) == $block['blockParameter'];
    }
}
