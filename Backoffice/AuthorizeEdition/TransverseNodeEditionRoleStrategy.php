<?php

namespace OpenOrchestra\Backoffice\AuthorizeEdition;

use OpenOrchestra\Backoffice\NavigationPanel\Strategies\GeneralNodesPanelStrategy;
use OpenOrchestra\ModelInterface\Model\NodeInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Class TransverseNodeEditionRoleStrategy
 */
class TransverseNodeEditionRoleStrategy implements AuthorizeEditionInterface
{
    protected $authorizationChecker;

    /**
     * @param AuthorizationCheckerInterface $authorizationChecker
     */
    public function __construct(AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->authorizationChecker = $authorizationChecker;
    }

    /**
     * @param mixed $document
     *
     * @return bool
     */
    public function support($document)
    {
        return $document instanceof NodeInterface && $document->getNodeType() === NodeInterface::TYPE_TRANSVERSE;
    }

    /**
     * @param mixed $document
     *
     * @return bool
     */
    public function isEditable($document)
    {
        return $this->authorizationChecker->isGranted(GeneralNodesPanelStrategy::ROLE_ACCESS_UPDATE_GENERAL_NODE, $document);
    }
}
