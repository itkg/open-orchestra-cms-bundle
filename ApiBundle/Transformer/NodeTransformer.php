<?php

namespace OpenOrchestra\ApiBundle\Transformer;

use OpenOrchestra\ApiBundle\Exceptions\HttpException\StatusChangeNotGrantedHttpException;
use OpenOrchestra\BaseApi\Exceptions\TransformerParameterTypeException;
use OpenOrchestra\Backoffice\Exception\StatusChangeNotGrantedException;
use OpenOrchestra\Backoffice\NavigationPanel\Strategies\TransverseNodePanelStrategy;
use OpenOrchestra\Backoffice\NavigationPanel\Strategies\TreeNodesPanelStrategy;
use OpenOrchestra\BaseApi\Facade\FacadeInterface;
use OpenOrchestra\BaseApi\Transformer\AbstractSecurityCheckerAwareTransformer;
use OpenOrchestra\ModelInterface\Event\StatusableEvent;
use OpenOrchestra\ModelInterface\Model\SchemeableInterface;
use OpenOrchestra\ModelInterface\Model\SiteAliasInterface;
use OpenOrchestra\ModelInterface\StatusEvents;
use OpenOrchestra\BaseBundle\Manager\EncryptionManager;
use OpenOrchestra\ModelInterface\Model\NodeInterface;
use OpenOrchestra\ModelInterface\Repository\SiteRepositoryInterface;
use OpenOrchestra\ModelInterface\Repository\StatusRepositoryInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use OpenOrchestra\ApiBundle\Context\CMSGroupContext;

/**
 * Class NodeTransformer
 */
class NodeTransformer extends AbstractSecurityCheckerAwareTransformer
{
    protected $encrypter;
    protected $siteRepository;
    protected $eventDispatcher;
    protected $statusRepository;
    protected $facadeClass;

    /**
     * @param string                        $facadeClass
     * @param EncryptionManager             $encrypter
     * @param SiteRepositoryInterface       $siteRepository
     * @param StatusRepositoryInterface     $statusRepository
     * @param EventDispatcherInterface      $eventDispatcher
     * @param AuthorizationCheckerInterface $authorizationChecker
     */
    public function __construct(
        $facadeClass,
        EncryptionManager $encrypter,
        SiteRepositoryInterface $siteRepository,
        StatusRepositoryInterface $statusRepository,
        EventDispatcherInterface $eventDispatcher,
        AuthorizationCheckerInterface $authorizationChecker
    ) {
        parent::__construct($facadeClass, $authorizationChecker);
        $this->encrypter = $encrypter;
        $this->siteRepository = $siteRepository;
        $this->eventDispatcher = $eventDispatcher;
        $this->statusRepository = $statusRepository;
    }

    /**
     * @param NodeInterface $node
     *
     * @return FacadeInterface
     *
     * @throws TransformerParameterTypeException
     */
    public function transform($node)
    {
        if (!$node instanceof NodeInterface) {
            throw new TransformerParameterTypeException();
        }

        $facade = $this->newFacade();

        $facade = $this->addMainAttributes($facade, $node);
        $facade = $this->addAreas($facade, $node);
        $facade = $this->addStatus($facade, $node);
        $facade = $this->addLinks($facade, $node);
        $facade = $this->addGeneralNodeLinks($facade, $node);

        return $facade;
    }

    /**
     * @param FacadeInterface $facade
     * @param NodeInterface   $node
     *
     * @return FacadeInterface
     */
    protected function addMainAttributes(FacadeInterface $facade, NodeInterface $node)
    {
        $facade->id = $node->getId();
        $facade->nodeId = $node->getNodeId();
        $facade->name = $node->getName();
        $facade->siteId = $node->getSiteId();
        $facade->deleted = $node->isDeleted();
        $facade->templateId = $node->getTemplateId();
        $facade->nodeType = $node->getNodeType();
        $facade->parentId = $node->getParentId();
        $facade->path = $node->getPath();
        $facade->routePattern = $node->getRoutePattern();
        $facade->language = $node->getLanguage();
        $facade->metaKeywords = $node->getMetaKeywords();
        $facade->metaDescription = $node->getMetaDescription();
        $facade->metaIndex = $node->getMetaIndex();
        $facade->metaFollow = $node->getMetaFollow();
        $facade->theme = $node->getTheme();
        $facade->themeSiteDefault = $node->hasDefaultSiteTheme();
        $facade->version = $node->getVersion();
        $facade->createdBy = $node->getCreatedBy();
        $facade->updatedBy = $node->getUpdatedBy();
        $facade->createdAt = $node->getCreatedAt();
        $facade->updatedAt = $node->getUpdatedAt();
        $facade->boDirection = $node->getBoDirection();
        $facade->editable = $this->authorizationChecker->isGranted($this->getEditionRole($node), $node);

        return $facade;
    }

    /**
     * @param FacadeInterface $facade
     * @param NodeInterface   $node
     *
     * @return FacadeInterface
     */
    protected function addAreas(FacadeInterface $facade, NodeInterface $node)
    {
        if ($this->hasGroup(CMSGroupContext::AREAS)) {
            foreach ($node->getAreas() as $area) {
                $facade->addArea($this->getTransformer('area')->transform($area, $node));
            }
        }

        return $facade;
    }

    /**
     * @param FacadeInterface $facade
     * @param NodeInterface   $node
     *
     * @return FacadeInterface
     */
    protected function addStatus(FacadeInterface $facade, NodeInterface $node)
    {
        $facade->status = $this->getTransformer('status')->transform($node->getStatus());

        return $facade;
    }

    /**
     * @param FacadeInterface $facade
     * @param NodeInterface   $node
     *
     * @return FacadeInterface
     */
    protected function addLinks(FacadeInterface $facade, NodeInterface $node)
    {
        $facade->addLink('_self_without_language', $this->generateRoute('open_orchestra_api_node_show_or_create', array(
            'nodeId' => $node->getNodeId()
        )));

        $facade->addLink('_self', $this->generateRoute('open_orchestra_api_node_show_or_create', array(
            'nodeId' => $node->getNodeId(),
            'version' => $node->getVersion(),
            'language' => $node->getLanguage(),
        )));

        $facade->addLink('_language_list', $this->generateRoute('open_orchestra_api_parameter_languages_show'));

        $routeName = 'open_orchestra_api_block_list_without_transverse';
        if (NodeInterface::TYPE_TRANSVERSE !== $node->getNodeType()) {
            $routeName = 'open_orchestra_api_block_list_with_transverse';
        }
        $facade->addLink('_block_list', $this->generateRoute($routeName, array('language' => $node->getLanguage())));

        return $facade;
    }

    /**
     * @param FacadeInterface $facade
     * @param NodeInterface   $node
     *
     * @return FacadeInterface
     */
    protected function addGeneralNodeLinks(FacadeInterface $facade, NodeInterface $node)
    {
        if (NodeInterface::TYPE_TRANSVERSE !== $node->getNodeType()) {

            $facade = $this->addPreviewLinks($facade, $node);

            $facade->addLink('_self_form', $this->generateRoute('open_orchestra_backoffice_node_form', array(
                'id' => $node->getId(),
            )));

            $facade->addLink('_status_list', $this->generateRoute('open_orchestra_api_node_list_status', array(
                'nodeMongoId' => $node->getId()
            )));

            $facade->addLink('_self_status_change', $this->generateRoute('open_orchestra_api_node_update', array(
                'nodeMongoId' => $node->getId()
            )));

            if ($this->authorizationChecker->isGranted($this->getEditionRole($node))) {
                $facade->addLink('_self_duplicate', $this->generateRoute('open_orchestra_api_node_duplicate', array(
                    'nodeId' => $node->getNodeId(),
                    'language' => $node->getLanguage(),
                    'version' => $node->getVersion(),
                )));
            }

            $facade->addLink('_self_version', $this->generateRoute('open_orchestra_api_node_list_version', array(
                'nodeId' => $node->getNodeId(),
                'language' => $node->getLanguage(),
            )));

            if (NodeInterface::TYPE_ERROR !== $node->getNodeType() &&
                $this->authorizationChecker->isGranted(TreeNodesPanelStrategy::ROLE_ACCESS_DELETE_NODE, $node)
            ) {
                $facade->addLink('_self_delete', $this->generateRoute('open_orchestra_api_node_delete', array(
                    'nodeId' => $node->getNodeId()
                )));
            }
        }

        return $facade;
    }

    /**
     * @param FacadeInterface $facade
     * @param NodeInterface   $node
     *
     * @return FacadeInterface
     */
    protected function addPreviewLinks(FacadeInterface $facade, NodeInterface $node)
    {
        if ($this->hasGroup(CMSGroupContext::PREVIEW) && $site = $this->siteRepository->findOneBySiteId($node->getSiteId())) {
            /** @var SiteAliasInterface $alias */
            $encryptedId = $this->encrypter->encrypt($node->getId());

            foreach ($site->getAliases() as $aliasId => $alias) {
                if ($alias->getLanguage() == $node->getLanguage()) {
                    $facade->addPreviewLink(
                        $this->getPreviewLink($node->getScheme(), $alias, $encryptedId, $aliasId, $nodeId)
                    );
                }
            }
        }

        return $facade;
    }

    /**
     * Get a preview link
     *
     * @param string             $scheme
     * @param SiteAliasInterface $alias
     * @param string             $encryptedId
     * @param int                $aliasId
     * @param string             $nodeId
     *
     * @return FacadeInterface
     */
    protected function getPreviewLink($scheme, $alias, $encryptedId, $aliasId, $nodeId)
    {
        $previewLink = array(
            'name' => $alias->getDomain(),
            'link' => ''
        );

        if (is_null($scheme) || SchemeableInterface::SCHEME_DEFAULT == $scheme) {
            $scheme = $alias->getScheme();
        }
        $domain = $scheme . '://' . $alias->getDomain();
        $routeName = 'open_orchestra_base_node_preview';
        $parameters = array(
            'token' => $encryptedId,
            'aliasId' => $aliasId,
            'nodeId' => $nodeId
        );

        $previewLink['link'] = $domain . $this->generateRoute($routeName, $parameters, UrlGeneratorInterface::ABSOLUTE_PATH);

        return $this->getTransformer('link')->transform($previewLink);
    }

    /**
     * @param NodeInterface $node
     *
     * @return FacadeInterface
     */
    public function transformVersion($node)
    {
        $facade = $this->newFacade();

        $facade->id = $node->getId();
        $facade->nodeId = $node->getNodeId();
        $facade->name = $node->getName();
        $facade->version = $node->getVersion();
        $facade->createdBy = $node->getCreatedBy();
        $facade->updatedBy = $node->getUpdatedBy();
        $facade->createdAt = $node->getCreatedAt();
        $facade->updatedAt = $node->getUpdatedAt();
        $facade->status = $this->getTransformer('status')->transform($node->getStatus());

        $facade->addLink('_self', $this->generateRoute('open_orchestra_api_node_show_or_create', array(
            'nodeId' => $node->getNodeId(),
            'version' => $node->getVersion(),
            'language' => $node->getLanguage(),
        )));

        return $facade;
    }

    /**
     * @param FacadeInterface    $facade
     * @param NodeInterface|null $source
     *
     * @return mixed
     * @throws StatusChangeNotGrantedHttpException
     */
    public function reverseTransform(FacadeInterface $facade, $source = null)
    {
        if ($source) {
            if ($facade->statusId) {
                $toStatus = $this->statusRepository->find($facade->statusId);
                if ($toStatus) {
                    $event = new StatusableEvent($source, $toStatus);
                    try {
                        $this->eventDispatcher->dispatch(StatusEvents::STATUS_CHANGE, $event);
                    } catch (StatusChangeNotGrantedException $e) {
                        throw new StatusChangeNotGrantedHttpException();
                    }
                }
            }
        }

        return $source;
    }

    /**
     * @param NodeInterface $node
     *
     * @return string
     */
    protected function getEditionRole(NodeInterface $node)
    {
        if (NodeInterface::TYPE_TRANSVERSE === $node->getNodeType()) {
            return TransverseNodePanelStrategy::ROLE_ACCESS_UPDATE_GENERAL_NODE;
        } elseif (NodeInterface::TYPE_ERROR === $node->getNodeType()) {
            return TreeNodesPanelStrategy::ROLE_ACCESS_UPDATE_ERROR_NODE;
        }

        return TreeNodesPanelStrategy::ROLE_ACCESS_UPDATE_NODE;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'node';
    }

}
