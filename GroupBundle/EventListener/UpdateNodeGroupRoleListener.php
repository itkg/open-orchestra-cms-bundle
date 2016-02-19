<?php

namespace OpenOrchestra\GroupBundle\EventListener;

use Doctrine\ODM\MongoDB\Event\PreUpdateEventArgs;
use OpenOrchestra\Backoffice\Model\DocumentGroupRoleInterface;
use OpenOrchestra\Backoffice\Model\GroupInterface;
use OpenOrchestra\GroupBundle\Exception\NodeGroupRoleNotFoundException;
use OpenOrchestra\ModelInterface\Model\NodeInterface;
use OpenOrchestra\ModelInterface\Repository\NodeRepositoryInterface;

/**
 * Class UpdateNodeGroupRoleListener
 */
class UpdateNodeGroupRoleListener
{
    protected $nodeClass;

    /**
     * @param string $nodeClass
     */
    public function __construct($nodeClass)
    {
        $this->nodeClass = $nodeClass;
    }

    /**
     * @param PreUpdateEventArgs $event
     *
     * @throws NodeGroupRoleNotFoundException
     */
    public function preUpdate(PreUpdateEventArgs $event)
    {
        $document = $event->getDocument();
        $uow = $event->getDocumentManager()->getUnitOfWork();
        if (
            $document instanceof DocumentGroupRoleInterface &&
            DocumentGroupRoleInterface::TYPE_NODE === $document->getType() &&
            $event->hasChangedField("accessType")
        ) {
            $parentAssociation = $uow->getParentAssociation($document);
            /** @var $group GroupInterface */
            if (isset($parentAssociation[1]) && ($group = $parentAssociation[1]) instanceof GroupInterface) {
                $site = $group->getSite();
                $uow->initializeObject($site);
                /** @var NodeRepositoryInterface $nodeRepository */
                $nodeRepository = $event->getDocumentManager()->getRepository($this->nodeClass);
                $nodes = $nodeRepository->findByParent($document->getId(), $site->getSiteId());
                /** @var $node NodeInterface */
                foreach ($nodes as $node) {
                    $role = $document->getRole();
                    $nodeGroupRole = $group->getNodeRoleByIdAndRole($node->getNodeId(), $role);
                    if ($nodeGroupRole === null) {
                        throw new NodeGroupRoleNotFoundException($role, $node->getNodeId(), $group->getName());
                    } else if (DocumentGroupRoleInterface::ACCESS_INHERIT === $nodeGroupRole->getAccessType()) {
                        $nodeGroupRole->setGranted($document->isGranted());
                    }
                }
                $meta = $event->getDocumentManager()->getClassMetadata(get_class($group));
                $uow->recomputeSingleDocumentChangeSet($meta, $group);
            }
        }
    }
}
