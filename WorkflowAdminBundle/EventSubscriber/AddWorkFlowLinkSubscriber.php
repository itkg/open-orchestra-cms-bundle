<?php

namespace OpenOrchestra\WorkflowAdminBundle\EventSubscriber;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use OpenOrchestra\UserAdminBundle\Event\UserFacadeEvent;
use OpenOrchestra\UserAdminBundle\UserFacadeEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class AddWorkFlowLinkSubscriber
 */
class AddWorkFlowLinkSubscriber implements EventSubscriberInterface
{
    protected $router;

    /**
     * @param UrlGeneratorInterface $router
     */
    public function __construct(UrlGeneratorInterface $router)
    {
        $this->router = $router;
    }

    /**
     * @param UserFacadeEvent $event
     */
    public function postUserTransformation(UserFacadeEvent $event)
    {
        $facade = $event->getUserFacade();
        $user = $event->getUser();
        if (false === $user->isSuperAdmin()) {
            $facade->addLink('_self_panel_workflow_right',
                $this->router->generate('open_orchestra_backoffice_workflow_right_form',
                    array('userId' => $facade->id),
                    UrlGeneratorInterface::ABSOLUTE_URL));
        }
    }

    /**
     * @return array The event names to listen to
     */
    public static function getSubscribedEvents()
    {
        return array(
            UserFacadeEvents::POST_USER_TRANSFORMATION => 'postUserTransformation',
        );
    }
}
