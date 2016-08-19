<?php

namespace OpenOrchestra\Backoffice\EventSubscriber;

use OpenOrchestra\ModelInterface\Model\NodeInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use OpenOrchestra\ModelInterface\NodeEvents;
use OpenOrchestra\ModelInterface\Event\NodeEvent;
use OpenOrchestra\DisplayBundle\Manager\CacheableManager;
use OpenOrchestra\BaseBundle\Manager\TagManager;

/**
 * Class TransverseBlockCacheSubscriber
 */
class TransverseBlockCacheSubscriber implements EventSubscriberInterface
{
    protected $cacheableManager;
    protected $tagManager;

    /**
     * @param CacheableManager $cacheableManager
     * @param TagManager       $tagManager
     */
    public function __construct(CacheableManager $cacheableManager, TagManager $tagManager)
    {
        $this->cacheableManager = $cacheableManager;
        $this->tagManager = $tagManager;
    }

    /**
     * Triggered when a node status changes
     *
     * @param NodeEvent $event
     */
    public function invalidateNodeWithTransverseBlockTag(NodeEvent $event)
    {
        $node = $event->getNode();
        if (NodeInterface::TYPE_TRANSVERSE === $node->getNodeType()) {
            $this->cacheableManager->invalidateTags(
                array(
                    $this->tagManager->formatNodeIdTag($node->getNodeId())
                )
            );
        }
    }

    /**
     * @return array The event names to listen to
     */
    public static function getSubscribedEvents()
    {
        return array(
            NodeEvents::NODE_UPDATE_BLOCK => 'invalidateNodeWithTransverseBlockTag',
        );
    }
}
