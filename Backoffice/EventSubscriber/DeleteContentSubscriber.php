<?php

namespace OpenOrchestra\Backoffice\EventSubscriber;

use OpenOrchestra\ModelInterface\ContentEvents;
use OpenOrchestra\ModelInterface\Event\ContentEvent;
use OpenOrchestra\ModelInterface\Model\TrashItemInterface;

/**
 * Class DeleteContentSubscriber
 */
class DeleteContentSubscriber extends  AbstractDeleteSubscriber
{
    /**
     * @param ContentEvent $event
     */
    public function addContentTrashCan(ContentEvent $event)
    {
        $content = $event->getContent();
        $name = $content->getName();
        $type = TrashItemInterface::TYPE_CONTENT;
        $this->createTrashItem($content, $name, $type);
    }

    /**
     * @return array The event names to listen to
     */
    public static function getSubscribedEvents()
    {
        return array(
            ContentEvents::CONTENT_DELETE => 'addContentTrashCan',
        );
    }
}
