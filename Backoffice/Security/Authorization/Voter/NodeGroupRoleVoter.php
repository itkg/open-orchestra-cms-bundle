<?php

namespace OpenOrchestra\Backoffice\Security\Authorization\Voter;

use FOS\UserBundle\Model\UserInterface;
use OpenOrchestra\Backoffice\Model\ModelGroupRoleInterface;
use OpenOrchestra\Backoffice\Model\GroupInterface;
use OpenOrchestra\ModelInterface\Model\NodeInterface;
use OpenOrchestra\ModelInterface\Model\ReadSiteInterface;
use OpenOrchestra\ModelInterface\Repository\NodeRepositoryInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

/**
 * Class NodeGroupRoleVoter
 */
class NodeGroupRoleVoter implements VoterInterface
{
    /**
     * @var NodeRepositoryInterface
     */
    protected $nodeRepository;

    /**
     * @param NodeRepositoryInterface $nodeRepository
     */
    public function __construct(NodeRepositoryInterface $nodeRepository)
    {
        $this->nodeRepository = $nodeRepository;
    }

    /**
     * Checks if the voter supports the given attribute.
     *
     * @param string $attribute An attribute
     *
     * @return bool true if this Voter supports the attribute, false otherwise
     */
    public function supportsAttribute($attribute)
    {
        return (bool) preg_match('/^ROLE_ACCESS_[^_]+_NODE$/', $attribute);
    }

    /**
     * Checks if the voter supports the given class.
     *
     * @param string $class A class name
     *
     * @return bool true if this Voter can process the class
     */
    public function supportsClass($class)
    {
        return is_subclass_of($class, 'OpenOrchestra\ModelInterface\Model\NodeInterface');
    }

    /**
     * Returns the vote for the given parameters.
     *
     * This method must return one of the following constants:
     * ACCESS_GRANTED, ACCESS_DENIED, or ACCESS_ABSTAIN.
     *
     * @param TokenInterface $token A TokenInterface instance
     * @param NodeInterface|null $object The object to secure
     * @param array $attributes An array of attributes associated with the method being invoked
     *
     * @return int either ACCESS_GRANTED, ACCESS_ABSTAIN, or ACCESS_DENIED
     */
    public function vote(TokenInterface $token, $object, array $attributes)
    {
        if (!$this->supportsClass($object)) {
            return self::ACCESS_ABSTAIN;
        }
        if (($user = $token->getUser()) instanceof UserInterface && $user->isSuperAdmin()) {
            return VoterInterface::ACCESS_GRANTED;
        }
        foreach ($attributes as $attribute) {
            if (!$this->supportsAttribute($attribute)) {
                return self::ACCESS_ABSTAIN;
            }
        }

        /** @var GroupInterface $group */
        foreach ($user->getGroups() as $group) {
            if (!$group->getSite() instanceof ReadSiteInterface) {
                continue;
            }
            if ($group->getSite()->getSiteId() != $object->getSiteId()) {
                continue;
            }
            foreach ($attributes as $attribute) {
                if (!$this->supportsAttribute($attribute)) {
                    continue;
                }
                if (true === $this->isGrantedNodeGroupRole($object, $group, $attribute)) {
                    return self::ACCESS_GRANTED;
                }
            }

            return self::ACCESS_DENIED;
        }

        return self::ACCESS_ABSTAIN;
    }

    /**
     * @param NodeInterface  $node
     * @param GroupInterface $group
     * @param string         $attribute
     *
     * @return boolean
     */
    protected function isGrantedNodeGroupRole(NodeInterface $node, GroupInterface $group, $attribute)
    {
        if ($node->getNodeType() === NodeInterface::TYPE_TRANSVERSE || $node->getNodeType() === NodeInterface::TYPE_ERROR) {
            return true;
        }
        $nodeGroupRole = $group->getModelRoleByTypeAndIdAndRole(ModelGroupRoleInterface::TYPE_NODE, $node->getNodeId(), $attribute);
        if ($nodeGroupRole instanceof ModelGroupRoleInterface) {
            if (ModelGroupRoleInterface::ACCESS_INHERIT === $nodeGroupRole->getAccessType()) {
                $nodeParent = $this->nodeRepository->findInLastVersion($node->getParentId(), $node->getLanguage(), $node->getSiteId());
                if (null !== $nodeParent) {
                    return $this->isGrantedNodeGroupRole($nodeParent, $group, $attribute);
                }
            } elseif (ModelGroupRoleInterface::ACCESS_GRANTED === $nodeGroupRole->getAccessType()) {
                return true;
            }
        }

        return false;
    }
}
