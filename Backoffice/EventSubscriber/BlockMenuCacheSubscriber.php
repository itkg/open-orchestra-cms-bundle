<?php

namespace OpenOrchestra\Backoffice\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use OpenOrchestra\ModelInterface\NodeEvents;
use OpenOrchestra\ModelInterface\Event\NodeEvent;
use OpenOrchestra\DisplayBundle\Manager\CacheableManager;
use OpenOrchestra\BaseBundle\Manager\TagManager;

/**
 * Class BlockMenuCacheSubscriber
 *
 */
class BlockMenuCacheSubscriber implements EventSubscriberInterface
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
    public function invalidateNodeTag(NodeEvent $event)
    {
        $node = $event->getNode();
        $siteId = $node->getSiteId();
        $this->cacheableManager->invalidateTags(
            array(
                $this->tagManager->formatMenuTag($siteId)
            )
        );
    }

    /**
     * @return array The event names to listen to
     */
    public static function getSubscribedEvents()
    {
        return array(
            NodeEvents::PATH_UPDATED => 'invalidateNodeTag',
            NodeEvents::NODE_CHANGE_STATUS => 'invalidateNodeTag',
        );
    }
}
