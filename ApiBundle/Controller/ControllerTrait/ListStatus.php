<?php

namespace OpenOrchestra\ApiBundle\Controller\ControllerTrait;

use OpenOrchestra\ModelInterface\Model\StatusInterface;

/**
 * Trait ListStatus
 */
trait ListStatus
{
    /**
     * @param StatusInterface $status
     *
     * @return \OpenOrchestra\ApiBundle\Facade\StatusCollectionFacade
     */
    protected function listStatuses(StatusInterface $status)
    {
        $transitions = $status->getFromRoles();

        $possibleStatuses = array();

        foreach ($transitions as $transition) {
            $possibleStatuses[] = $transition->getToStatus();
        }

        return $this->get('open_orchestra_api.transformer_manager')->get('status_collection')->transform($possibleStatuses, $status);
    }
}
