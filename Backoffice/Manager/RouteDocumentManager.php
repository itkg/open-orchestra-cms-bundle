<?php

namespace OpenOrchestra\Backoffice\Manager;

use Doctrine\Common\Collections\Collection;
use OpenOrchestra\ModelInterface\Model\NodeInterface;
use OpenOrchestra\ModelInterface\Model\ReadSiteInterface;
use OpenOrchestra\ModelInterface\Model\RedirectionInterface;
use OpenOrchestra\ModelInterface\Model\RouteDocumentInterface;
use OpenOrchestra\ModelInterface\Model\SchemeableInterface;
use OpenOrchestra\ModelInterface\Model\SiteAliasInterface;
use OpenOrchestra\ModelInterface\Model\SiteInterface;
use OpenOrchestra\ModelInterface\Repository\NodeRepositoryInterface;
use OpenOrchestra\ModelInterface\Repository\RouteDocumentRepositoryInterface;
use OpenOrchestra\ModelInterface\Repository\SiteRepositoryInterface;

/**
 * Class RouteDocumentManager
 */
class RouteDocumentManager
{
    protected $routeDocumentRepository;
    protected $routeDocumentClass;
    protected $siteRepository;
    protected $nodeRepository;

    /**
     * @param string                           $routeDocumentClass
     * @param SiteRepositoryInterface          $siteRepository
     * @param NodeRepositoryInterface          $nodeRepository
     * @param RouteDocumentRepositoryInterface $routeDocumentRepository
     */
    public function __construct(
        $routeDocumentClass,
        SiteRepositoryInterface $siteRepository,
        NodeRepositoryInterface $nodeRepository,
        RouteDocumentRepositoryInterface $routeDocumentRepository
    )
    {
        $this->routeDocumentRepository = $routeDocumentRepository;
        $this->routeDocumentClass = $routeDocumentClass;
        $this->siteRepository = $siteRepository;
        $this->nodeRepository = $nodeRepository;
    }

    /**
     * @param SiteInterface $site
     *
     * @return array
     */
    public function createForSite(SiteInterface $site)
    {
        $nodes = $this->nodeRepository->findLastVersionByTypeCurrentlyPublished($site->getSiteId());

        $routes = array();
        foreach ($nodes as $node) {
            $routes = array_merge($this->generateRoutesForNode($node, $site), $routes);
        }

        return $routes;
    }

    /**
     * @param SiteInterface $site
     *
     * @return array
     */
    public function clearForSite(SiteInterface $site)
    {
        return $this->routeDocumentRepository->findBySite($site->getSiteId());
    }

    /**
     * @param NodeInterface $givenNode
     *
     * @return array
     */
    public function createForNode(NodeInterface $node)
    {
        $site = $this->siteRepository->findOneBySiteId($node->getSiteId());
        $nodes = $this->getTreeNode($node, $site);

        $routes = array();

        foreach ($nodes as $node) {
            $routes = array_merge($this->generateRoutesForNode($node, $site), $routes);
        }

        return $routes;
    }

    /**
     * @param RedirectionInterface $redirection
     *
     * @return array
     */
    public function createOrUpdateForRedirection(RedirectionInterface $redirection)
    {
        $routes = array();
        $site = $this->siteRepository->findOneBySiteId($redirection->getSiteId());
        $node = $this->getNodeForRedirection($redirection);
        $controller = 'FrameworkBundle:Redirect:urlRedirect';
        $paramKey = 'path';
        if ($node instanceof NodeInterface) {
            $controller = 'FrameworkBundle:Redirect:redirect';
            $paramKey = 'route';
        }

        /** @var SiteAliasInterface $alias */
        foreach ($site->getAliases() as $key => $alias) {
            if ($alias->getLanguage() == $redirection->getLocale()) {
                /** @var RouteDocumentInterface $route */
                $route = $this->getOrCreateRouteDocument($redirection, $key);
                $route->setHost($alias->getDomain());
                if ($node instanceof NodeInterface) {
                    $paramValue = $key . '_' . $node->getId();
                } else {
                    $paramValue = $redirection->getUrl();
                }
                $route->setDefaults(array(
                    '_controller' => $controller,
                    $paramKey => $paramValue,
                    'permanent' => $redirection->isPermanent()
                ));
                $route->setPattern($redirection->getRoutePattern());
                $routes[] = $route;
            }
        }

        return $routes;
    }

    /**
     * @param RedirectionInterface $redirection
     *
     * @return array
     */
    public function deleteForRedirection(RedirectionInterface $redirection)
    {
        return $this->routeDocumentRepository->findByRedirection($redirection->getId());
    }

    /**
     * @param string|null $parentId
     * @param string|null $suffix
     * @param string      $language
     * @param string      $siteId
     *
     * @return string|null
     */
    protected function completeRoutePattern($parentId = null, $suffix = null, $language, $siteId)
    {
        if (is_null($parentId) || '-' == $parentId || '' == $parentId || NodeInterface::ROOT_NODE_ID == $parentId || 0 === strpos($suffix, '/')) {
            return $suffix;
        }

        $parent = $this->nodeRepository->findOneCurrentlyPublished($parentId, $language, $siteId);

        if ($parent instanceof NodeInterface) {
            return $this->suppressDoubleSlashes($this->completeRoutePattern($parent->getParentId(), $parent->getRoutePattern() . '/' . $suffix, $language, $siteId));
        }

        return $suffix;
    }

    /**
     * @param NodeInterface $node
     *
     * @return Collection
     */
    public function clearForNode(NodeInterface $node)
    {
        $nodes = $this->getTreeNode($node, null, true);

        $routes = array();

        foreach ($nodes as $node) {
            $routes = array_merge($this->routeDocumentRepository->findByNodeIdSiteIdAndLanguage($node->getNodeId(), $node->getSiteId(), $node->getLanguage()), $routes);
        }

        return $routes;
    }

    /**
     * @param NodeInterface      $node
     * @param SiteInterface|null $site
     * @param boolean            $all
     *
     * @return Collection
     */
    protected function getTreeNode(NodeInterface $node, $site = null, $all = false)
    {
        if (null === $site) {
            $site = $this->siteRepository->findOneBySiteId($node->getSiteId());
        }

        return $all ? $this->nodeRepository->findByIncludedPathSiteIdAndLanguage($node->getPath(), $site->getSiteId(), $node->getLanguage()) : $this->nodeRepository->findByPathCurrentlyPublishedAndLanguage($node->getPath(), $site->getSiteId(), $node->getLanguage());

    }


    /**
     * @param RedirectionInterface $redirection
     *
     * @return NodeInterface|null
     */
    protected function getNodeForRedirection(RedirectionInterface $redirection)
    {
        if (is_null($redirection->getNodeId())) {
            return null;
        }

        $node = $this->nodeRepository->findOneCurrentlyPublished(
            $redirection->getNodeId(),
            $redirection->getLocale(),
            $redirection->getSiteId()
        );

        return $node;
    }

    /**
     * @param RedirectionInterface $redirection
     * @param int                  $key
     *
     * @return RouteDocumentInterface
     */
    protected function getOrCreateRouteDocument(RedirectionInterface $redirection, $key)
    {
        $routeDocumentClass = $this->routeDocumentClass;
        $routeName = $key . '_' . $redirection->getId();

        if (!($route = $this->routeDocumentRepository->findOneByName($routeName))) {
            $route = new $routeDocumentClass();
            $route->setName($routeName);
        }

        return $route;
    }

    /**
     * @param string $route
     *
     * @return string
     */
    protected function suppressDoubleSlashes($route)
    {
        return str_replace('//', '/', $route);
    }

    /**
     * @param NodeInterface     $node
     * @param ReadSiteInterface $site
     *
     * @return array
     */
    protected function generateRoutesForNode(NodeInterface $node, ReadSiteInterface $site)
    {
        $routes = array();

        if (!$node instanceof NodeInterface) {
            return $routes;
        }

        $routeDocumentClass = $this->routeDocumentClass;

        /** @var SiteAliasInterface $alias */
        foreach ($site->getAliases() as $key => $alias) {
            if ($alias->getLanguage() == $node->getLanguage()) {
                /** @var RouteDocumentInterface $route */
                $route = new $routeDocumentClass();
                $route->setName($key . '_' . $node->getId());
                $route->setHost($alias->getDomain());
                $scheme = $node->getScheme();
                if (is_null($scheme) || SchemeableInterface::SCHEME_DEFAULT == $scheme) {
                    $scheme = $alias->getScheme();
                }
                $route->setSchemes($scheme);
                $route->setLanguage($node->getLanguage());
                $route->setNodeId($node->getNodeId());
                $route->setSiteId($site->getSiteId());
                $route->setAliasId($key);
                $pattern = $this->completeRoutePattern($node->getParentId(), $node->getRoutePattern(), $node->getLanguage(), $site->getSiteId());
                if ($alias->getPrefix()) {
                    $pattern = $this->suppressDoubleSlashes('/' . $alias->getPrefix() . '/' . $pattern);
                }
                $route->setPattern($pattern);
                $routes[] = $route;
            }
        }

        return $routes;
    }
}
