<?php

namespace OpenOrchestra\Backoffice\EventSubscriber;

use OpenOrchestra\ModelInterface\ContentEvents;
use OpenOrchestra\ModelInterface\Event\ContentEvent;
use OpenOrchestra\ModelInterface\Event\TrashcanEvent;
use OpenOrchestra\ModelInterface\TrashcanEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use OpenOrchestra\ModelInterface\NodeEvents;
use OpenOrchestra\ModelInterface\Event\NodeEvent;
use Doctrine\Common\Persistence\ObjectManager;
use OpenOrchestra\Backoffice\Reference\ReferenceManager;

/**
 * Class UpdateReferenceSubscriber
 */
class UpdateReferenceSubscriber implements EventSubscriberInterface
{
    protected $referenceManager;
    protected $objectManager;

    /**
     * @param referenceManager $referenceManager
     * @param ObjectManager    $objectManager
     */
    public function __construct(ReferenceManager $referenceManager, ObjectManager $objectManager)
    {
        $this->referenceManager = $referenceManager;
        $this->objectManager = $objectManager;
    }

    /**
     * @param NodeEvent $event
     */
    public function updateReferencesToNode(NodeEvent $event)
    {
        $node = $event->getNode();
        $this->referenceManager->updateReferencesToEntity($node);
    }

    /**
     * @param ContentEvent $event
     */
    public function updateReferencesToContent(ContentEvent $event)
    {
        $content = $event->getContent();
        $this->referenceManager->updateReferencesToEntity($content);
    }

    /**
     * @param TrashcanEvent $event
     */
    public function removeReferencesToEntity(TrashcanEvent $event)
    {
        $deletedElement = $event->getDeletedEntity();
        $this->referenceManager->removeReferencesToEntity($deletedElement);
    }

    /**
     * @return array The event names to listen to
     */
    public static function getSubscribedEvents()
    {
        return array(
            NodeEvents::NODE_UPDATE_BLOCK => 'updateReferencesToNode',
            NodeEvents::NODE_DELETE_BLOCK => 'updateReferencesToNode',
            NodeEvents::NODE_UPDATE_BLOCK_POSITION => 'updateReferencesToNode',
            ContentEvents::CONTENT_UPDATE => 'updateReferencesToContent',
            ContentEvents::CONTENT_CREATION => 'updateReferencesToContent',
            TrashcanEvents::TRASHCAN_REMOVE_ENTITY => 'removeReferencesToEntity',
        );
    }
}
