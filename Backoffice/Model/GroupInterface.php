<?php

namespace OpenOrchestra\Backoffice\Model;

use Doctrine\Common\Collections\Collection;
use FOS\UserBundle\Model\GroupInterface as BaseGroupInterface;
use OpenOrchestra\Media\Model\MediaFolderGroupRoleInterface;
use OpenOrchestra\ModelInterface\Model\ReadSiteInterface;
use OpenOrchestra\ModelInterface\Model\TranslatedValueContainerInterface;
use OpenOrchestra\ModelInterface\Model\TranslatedValueInterface;

/**
 * Interface GroupInterface
 */
interface GroupInterface extends BaseGroupInterface, TranslatedValueContainerInterface
{
    /**
     * @param ReadSiteInterface|null $site
     */
    public function setSite(ReadSiteInterface $site = null);

    /**
     * @return ReadSiteInterface|null
     */
    public function getSite();

    /**
     * @param TranslatedValueInterface $label
     */
    public function addLabel(TranslatedValueInterface $label);

    /**
     * @param TranslatedValueInterface $label
     */
    public function removeLabel(TranslatedValueInterface $label);

    /**
     * @param string $language
     *
     * @return string
     */
    public function getLabel($language = 'en');

    /**
     * @return Collection
     */
    public function getLabels();

    /**
     * @return array
     */
    public function getNodeRoles();

    /**
     * @param NodeGroupRoleInterface $nodeGroupRole
     */
    public function addNodeRole(NodeGroupRoleInterface $nodeGroupRole);

    /**
     * @param string $nodeId
     * @param string $role
     *
     * @return NodeGroupRoleInterface|null
     */
    public function getNodeRoleByNodeAndRole($nodeId, $role);

    /**
     * @param string $nodeId
     * @param string $role
     *
     * @return boolean
     */
    public function hasNodeRoleByNodeAndRole($nodeId, $role);

    /**
     * @return array
     */
    public function getMediaFolderRoles();

    /**
     * @param MediaFolderGroupRoleInterface $mediaFolderGroupRole
     */
    public function addMediaFolderRole(MediaFolderGroupRoleInterface $mediaFolderGroupRole);

    /**
     * @param string $folderId
     * @param string $role
     *
     * @return MediaFolderGroupRoleInterface|null
     */
    public function getMediaFolderRoleByMediaFolderAndRole($folderId, $role);
}
