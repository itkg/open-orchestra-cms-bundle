<?php

namespace OpenOrchestra\Backoffice\Manager;

use OpenOrchestra\ModelInterface\Event\NodeEvent;
use OpenOrchestra\ModelInterface\Model\TemplateInterface;
use OpenOrchestra\ModelInterface\Saver\VersionableSaverInterface;
use OpenOrchestra\ModelInterface\Model\AreaContainerInterface;
use OpenOrchestra\ModelInterface\Model\StatusInterface;
use OpenOrchestra\ModelInterface\NodeEvents;
use OpenOrchestra\ModelInterface\Model\NodeInterface;
use OpenOrchestra\ModelInterface\Model\ReadNodeInterface;
use OpenOrchestra\Backoffice\Context\ContextManager;
use OpenOrchestra\ModelInterface\Repository\NodeRepositoryInterface;
use OpenOrchestra\ModelInterface\Repository\SiteRepositoryInterface;
use OpenOrchestra\ModelInterface\Repository\StatusRepositoryInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use OpenOrchestra\ModelInterface\Model\AreaInterface;

/**
 * Class NodeManager
 */
class NodeManager
{
    protected $versionableSaver;
    protected $statusRepository;
    protected $eventDispatcher;
    protected $nodeRepository;
    protected $siteRepository;
    protected $contextManager;
    protected $blockManager;
    protected $areaManager;
    protected $nodeClass;
    protected $areaClass;

    /**
     * Constructor
     *
     * @param VersionableSaverInterface  $versionableSaver
     * @param NodeRepositoryInterface    $nodeRepository
     * @param SiteRepositoryInterface    $siteRepository
     * @param StatusRepositoryInterface  $statusRepository
     * @param AreaManager                $areaManager
     * @param BlockManager               $blockManager
     * @param ContextManager             $contextManager
     * @param string                     $nodeClass
     * @param string                     $areaClass
     * @param EventDispatcherInterface   $eventDispatcher
     */
    public function __construct(
        VersionableSaverInterface  $versionableSaver,
        NodeRepositoryInterface $nodeRepository,
        SiteRepositoryInterface $siteRepository,
        StatusRepositoryInterface $statusRepository,
        AreaManager $areaManager,
        BlockManager $blockManager,
        ContextManager $contextManager,
        $nodeClass,
        $areaClass,
        $eventDispatcher
    ){
        $this->versionableSaver =  $versionableSaver;
        $this->nodeRepository = $nodeRepository;
        $this->siteRepository = $siteRepository;
        $this->statusRepository = $statusRepository;
        $this->areaManager = $areaManager;
        $this->blockManager = $blockManager;
        $this->contextManager = $contextManager;
        $this->nodeClass = $nodeClass;
        $this->areaClass = $areaClass;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * Duplicate a node
     *
     * @param string    $nodeId
     * @param string    $siteId
     * @param string    $language
     * @param int|null  $version
     * @param bool|true $save
     *
     * @return NodeInterface
     */
    public function duplicateNode($nodeId, $siteId, $language, $version = null, $save = true)
    {
        $node = $this->nodeRepository->findVersion($nodeId, $language, $siteId, $version);
        $lastNode = $this->nodeRepository->findInLastVersion($nodeId, $language, $siteId);
        $lastNodeVersion = $lastNode->getVersion();
        $status = $this->getEditableStatus($node);
        if ($status === null) {
            $status = $this->statusRepository->findOneByInitial();
        }
        /** @var NodeInterface $newNode */
        $newNode = clone $node;
        $newNode->setStatus($status);
        $newNode->setCurrentlyPublished($status->isPublished());
        $newNode->setVersion($lastNodeVersion + 1);
        $this->duplicateBlockAndArea($node, $newNode);
        $this->updateBlockReferences($node, $newNode);

        if (true === $save) {
            $this->saveDuplicatedNode($newNode);
        }

        return $newNode;
    }

    /**
     * @param NodeInterface $newNode
     */
    public function saveDuplicatedNode(NodeInterface $newNode)
    {
        $this->versionableSaver->saveDuplicated($newNode);
    }

    /**
     * @param string $nodeId
     * @param string $siteId
     * @param string $language
     *
     * @return NodeInterface
     */
    public function createNewErrorNode($nodeId, $siteId, $language)
    {
        $node = $this->initializeNode(NodeInterface::ROOT_NODE_ID, $language, $siteId);
        $node->setNodeId($nodeId);
        $node->setNodeType(ReadNodeInterface::TYPE_ERROR);
        $node->setRoutePattern($nodeId);
        $node->setName($nodeId);
        $node->setInFooter(false);
        $node->setInMenu(false);
        $node->setVersion(1);

        $area = new $this->areaClass();
        $area->setLabel('main');
        $area->setAreaId('main');
        $node->addArea($area);

        $this->eventDispatcher->dispatch(NodeEvents::NODE_CREATION, new NodeEvent($node));

        return $node;
    }

    /**
     * @param NodeInterface $node
     * @param string        $language
     *
     * @return NodeInterface
     */
    public function createNewLanguageNode(NodeInterface $node, $language)
    {
        $newNode = clone $node;
        $newNode->setVersion(1);
        $newNode->setStatus($this->getEditableStatus($node));
        $newNode->setLanguage($language);
        $newNode = $this->duplicateBlockAndArea($node, $newNode);

        $this->eventDispatcher->dispatch(NodeEvents::NODE_ADD_LANGUAGE, new NodeEvent($node));

        return $newNode;
    }

    /**
     * @param NodeInterface|null $node
     *
     * @return StatusInterface
     */
    protected function getEditableStatus(NodeInterface $node = null)
    {
        if (is_null($node) || $node->getNodeId() == NodeInterface::TRANSVERSE_NODE_ID) {
            return $this->statusRepository->findOneByEditable();
        }

        return null;
    }

    /**
     * @param mixed $nodes
     */
    public function deleteTree($nodes)
    {
        $siteId = $this->contextManager->getCurrentSiteId();
        foreach ($nodes as $node) {
            if (!$node->isDeleted()) {
                $node->setDeleted(true);
                $node->setOrder(NodeInterface::DELETED_ORDER);
                $nodePath = $node->getPath();
                $this->eventDispatcher->dispatch(NodeEvents::NODE_DELETE, new NodeEvent($node));
                $subNodes = $this->nodeRepository->findByIncludedPathAndSiteId($nodePath, $siteId);
                foreach ($subNodes as $subNode) {
                    if (!$subNode->isDeleted()) {
                        $subNode->setDeleted(true);
                        $subNode->setOrder(NodeInterface::DELETED_ORDER);
                        $this->eventDispatcher->dispatch(NodeEvents::NODE_DELETE, new NodeEvent($subNode));
                    }
                }
            }
        }
    }

    /**
     * @param NodeInterface $node
     * @param string        $nodeId
     *
     * @return NodeInterface
     */
    public function hydrateNodeFromNodeId(NodeInterface $node, $nodeId)
    {
        $siteId = $this->contextManager->getCurrentSiteId();
        $oldNode = $this->nodeRepository->findInLastVersion($nodeId, $node->getLanguage(), $siteId);

        if ($oldNode) {
            $this->duplicateBlockAndArea($oldNode, $node);
        }

        return $node;
    }

    /**
     * @param AreaContainerInterface $areaContainer
     * @param Collection             $sourceAreas
     */
    public function hydrateAreaFromTemplate(AreaContainerInterface $areaContainer, $sourceAreas)
    {
        foreach($sourceAreas as $area) {
            $newArea = clone $area;
            if (!empty($area->getAreas())) {
                $this->hydrateAreaFromTemplate($newArea, $area->getAreas());
            }
            $areaContainer->addArea($newArea);
        }
    }

    /**
     * @param NodeInterface $node
     * @param NodeInterface $newNode
     *
     * @return NodeInterface
     */
    protected function duplicateBlockAndArea(NodeInterface $node, NodeInterface $newNode)
    {
        $newNode->setBoDirection($node->getBoDirection());
        $this->duplicateArea($node, $newNode);
        foreach ($node->getBlocks() as $block) {
            $newBlock = clone $block;
            $newNode->addBlock($newBlock);
        }

        return $newNode;
    }

    /**
     * @param AreaContainerInterface $areaContainer
     * @param AreaContainerInterface $newAreaContainer
     */
    protected function duplicateArea(AreaContainerInterface $areaContainer, AreaContainerInterface $newAreaContainer)
    {
        foreach ($areaContainer->getAreas() as $area) {
            $newArea = clone $area;
            $newAreaContainer->addArea($newArea);
            $this->duplicateArea($area, $newArea);
        }
    }

    /**
     * @param array $nodes
     *
     * @return bool
     */
    public function nodeConsistency($nodes)
    {
        if (is_array($nodes)) {
            foreach ($nodes as $node) {
                if (!$this->areaManager->areaConsistency($node) || !$this->blockManager->blockConsistency($node)) {
                    return false;
                }
            }

            return true;
        }

        return false;
    }

    /**
     * @param string            $siteId
     * @param string            $language
     * @param string            $name
     * @param string            $routePattern
     * @param TemplateInterface $template
     *
     * @return NodeInterface
     */
    public function createRootNode($siteId, $language, $name, $routePattern, TemplateInterface $template)
    {
        $node = $this->initializeNode(NodeInterface::ROOT_PARENT_ID, $siteId, $language);
        $node->setSiteId($siteId);
        $node->setTemplateId($template->getTemplateId());
        $node->setRoutePattern($routePattern);
        $node->setName($name);
        $node->setVersion(1);
        $node->setInMenu(true);
        $node->setInFooter(true);
        $node->setLanguage($language);

        $this->hydrateAreaFromTemplate($node, $template->getAreas());

        return $node;
    }

    /**
     * @param string $parentId
     *
     * @return NodeInterface
     *
     * @depraceted use initializeNode, will be removed in 1.2.0
     */
    public function initializeNewNode($parentId)
    {
        @trigger_error('The '.__METHOD__.' method is deprecated since version 1.1.0 and will be removed in 1.2.0. Use the '.__CLASS__.'::initializeNode method instead.', E_USER_DEPRECATED);

        $language = $this->contextManager->getCurrentSiteDefaultLanguage();
        $siteId = $this->contextManager->getCurrentSiteId();

        /** @var NodeInterface $node */
        $node = new $this->nodeClass();
        $node->setSiteId($siteId);
        $node->setLanguage($language);
        $node->setMaxAge(NodeInterface::MAX_AGE);
        $node->setParentId($parentId);
        $node->setOrder($this->getNewNodeOrder($parentId, $siteId));
        $node->setTheme(NodeInterface::THEME_DEFAULT);
        $node->setDefaultSiteTheme(true);

        $parentNode = $this->nodeRepository->findVersion($parentId, $language, $siteId);
        $node->setStatus($this->getEditableStatus($parentNode));
        $nodeType = NodeInterface::TYPE_DEFAULT;
        if ($parentNode instanceof NodeInterface) {
            $nodeType = $parentNode->getNodeType();
        } else {
            $node->setNodeId(NodeInterface::ROOT_NODE_ID);
        }
        $node->setNodeType($nodeType);

        $site = $this->siteRepository->findOneBySiteId($siteId);
        if ($site) {
            $node->setMetaKeywords($site->getMetaKeywords());
            $node->setMetaDescription($site->getMetaDescription());
            $node->setMetaIndex($site->getMetaIndex());
            $node->setMetaFollow($site->getMetaFollow());
        }

        return $node;
    }

    /**
     * @param string $parentId
     * @param string $language
     * @param string $siteId
     *
     * @return NodeInterface
     */
    public function initializeNode($parentId, $language, $siteId)
    {
        /** @var NodeInterface $node */
        $node = new $this->nodeClass();
        $node->setSiteId($siteId);
        $node->setLanguage($language);
        $node->setMaxAge(NodeInterface::MAX_AGE);
        $node->setParentId($parentId);
        $node->setOrder($this->getNewNodeOrder($parentId, $siteId));
        $node->setTheme(NodeInterface::THEME_DEFAULT);
        $node->setDefaultSiteTheme(true);

        $parentNode = $this->nodeRepository->findVersion($parentId, $language, $siteId);
        $node->setStatus($this->getEditableStatus($parentNode));
        $nodeType = NodeInterface::TYPE_DEFAULT;
        if ($parentNode instanceof NodeInterface) {
            $nodeType = $parentNode->getNodeType();
        } else {
            $node->setNodeId(NodeInterface::ROOT_NODE_ID);
        }
        $node->setNodeType($nodeType);

        $site = $this->siteRepository->findOneBySiteId($siteId);
        if ($site) {
            $node->setMetaKeywords($site->getMetaKeywords());
            $node->setMetaDescription($site->getMetaDescription());
            $node->setMetaIndex($site->getMetaIndex());
            $node->setMetaFollow($site->getMetaFollow());
        }

        return $node;
    }

    /**
     * @param NodeInterface $oldNode
     * @param NodeInterface $node
     */
    public function updateBlockReferences(NodeInterface $oldNode, NodeInterface $node)
    {
        $nodeTransverse = $this->nodeRepository
            ->findInLastVersion(NodeInterface::TRANSVERSE_NODE_ID, $node->getLanguage(), $node->getSiteId());

        foreach($node->getAreas() as $area) {
            foreach ($area->getBlocks() as $areaBlock) {
                if (NodeInterface::TRANSVERSE_NODE_ID === $areaBlock['nodeId']) {
                    $block = $nodeTransverse->getBlock($areaBlock['blockId']);
                    $block->addArea(array('nodeId' => $node->getId(), 'areaId' => $area->getAreaId()));
                    continue;
                }
                $block = $node->getBlock($areaBlock['blockId']);
                foreach ($block->getAreas() as $blockArea) {
                    if ($blockArea['nodeId'] === $oldNode->getId()) {
                        $blockArea['nodeId'] = $node->getId();
                    }
                }
            }
        }
    }

    /**
     * @param array         $orderedNode
     * @param NodeInterface $node
     */
    public function orderNodeChildren($orderedNode, NodeInterface $node)
    {
        $nodeId = $node->getNodeId();
        foreach ($orderedNode as $position => $childNodeId) {
            $siteId = $this->contextManager->getCurrentSiteId();
            $children = $this->nodeRepository->findByNodeAndSite($childNodeId, $siteId);
            $path = $node->getPath() . '/' . $childNodeId;
            /** @var NodeInterface $child */
            foreach ($children as $child) {
                $child->setOrder($position);
                $child->setParentId($nodeId);
                $child->setPath($path);
            }
            $event = new NodeEvent($child);
            $this->eventDispatcher->dispatch(NodeEvents::PATH_UPDATED, $event);
        }
    }

    /**
     * Create transverse node
     *
     * @param string $language
     * @param string $siteId
     *
     * @return NodeInterface
     */
    public function createTransverseNode($language, $siteId)
    {
        $area = new $this->areaClass();
        $area->setLabel('main');
        $area->setAreaId('main');

        /** @var NodeInterface $node */
        $node = new $this->nodeClass();
        $node->setLanguage($language);
        $node->setNodeId(NodeInterface::TRANSVERSE_NODE_ID);
        $node->setName(NodeInterface::TRANSVERSE_NODE_ID);
        $node->setNodeType(NodeInterface::TYPE_TRANSVERSE);
        $node->setSiteId($siteId);
        $node->addArea($area);

        return $node;
    }

    /**
     * @param string $parentId
     * @param string $siteId
     *
     * @return int
     */
    protected function getNewNodeOrder($parentId, $siteId)
    {
        $greatestOrderNode = $this->nodeRepository->findOneByParentWithGreatestOrder($parentId, $siteId);
        if (null === $greatestOrderNode) {
            return 0;
        }

        return $greatestOrderNode->getOrder() + 1;
    }

    /**
     * Remove unused blocks present in $node
     *
     * @param NodeInterface $node
     */
    public function removeUnusedBlocks(NodeInterface $node)
    {
        $blocks = $node->getBlocks();

        foreach ($blocks as $index => $block) {
            if (count($block->getAreas()) == 0) {
                $node->removeBlockWithKey($index);
            }
        }
    }
}
