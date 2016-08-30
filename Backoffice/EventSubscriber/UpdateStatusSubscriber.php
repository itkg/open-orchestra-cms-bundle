<?php

namespace OpenOrchestra\Backoffice\EventSubscriber;

use OpenOrchestra\Backoffice\Exception\StatusChangeNotGrantedException;
use OpenOrchestra\ModelInterface\Event\StatusableEvent;
use OpenOrchestra\ModelInterface\StatusEvents;
use OpenOrchestra\BackofficeBundle\StrategyManager\AuthorizeStatusChangeManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use OpenOrchestra\ModelInterface\Model\IsStatusableInterface;

/**
 * Class UpdateStatusSubscriber
 */
class UpdateStatusSubscriber implements EventSubscriberInterface
{
    protected $authorizeStatusChangeManager;

    /**
     * @param AuthorizeStatusChangeManager $authorizeStatusChangeManager
     */
    public function __construct(AuthorizeStatusChangeManager $authorizeStatusChangeManager)
    {
        $this->authorizeStatusChangeManager = $authorizeStatusChangeManager;
    }

    /**
     * @param StatusableEvent $event
     *
     * @throws StatusChangeNotGrantedException
     */
    public function updateStatus(StatusableEvent $event)
    {
        $document = $event->getStatusableElement();
        $toStatus = $event->getToStatus();
        if ($this->authorizeStatusChangeManager->isGranted($document, $toStatus)) {
            if (!$document instanceof IsStatusableInterface || $document->isStatusable()) {
                $document->setStatus($toStatus);
            }
        } else {
            throw new StatusChangeNotGrantedException();
        }
    }

    /**
     * @return array The event names to listen to
     */
    public static function getSubscribedEvents()
    {
        return array(
            StatusEvents::STATUS_CHANGE => 'updateStatus',
        );
    }
}
