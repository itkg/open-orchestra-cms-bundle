<?php

namespace OpenOrchestra\ApiBundle\Controller;

use OpenOrchestra\BaseApi\Facade\FacadeInterface;
use OpenOrchestra\ModelInterface\Event\NodeEvent;
use OpenOrchestra\ModelInterface\NodeEvents;
use OpenOrchestra\ModelInterface\Model\NodeInterface;
use OpenOrchestra\BaseApiBundle\Controller\Annotation as Api;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as Config;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use OpenOrchestra\BaseApiBundle\Controller\BaseController;

/**
 * Class NodeController
 *
 * @Config\Route("node")
 */
class NodeController extends BaseController
{
    /**
     * @param Request $request
     * @param string $nodeId
     *
     * @Config\Route("/{nodeId}", name="open_orchestra_api_node_show")
     * @Config\Method({"GET"})
     * @Api\Serialize()
     *
     * @Config\Security("has_role('ROLE_ACCESS_TREE_NODE')")
     *
     * @return FacadeInterface
     */
    public function showAction(Request $request, $nodeId)
    {
        $currentSiteManager = $this->get('open_orchestra.manager.current_site');
        $language = $request->get('language', $currentSiteManager->getCurrentSiteDefaultLanguage());
        $version = $request->get('version');
        $siteId = $currentSiteManager->getCurrentSiteId();
        $node = $this->get('open_orchestra_model.repository.node')
            ->findOneByNodeIdAndLanguageAndVersionAndSiteId($nodeId, $language, $siteId, $version);

        if (!$node) {
            $defaultCurrentSiteLanguage = $currentSiteManager->getCurrentSiteDefaultLanguage();
            $oldNode = $this->get('open_orchestra_model.repository.node')->findOneByNodeIdAndLanguageAndVersionAndSiteId($nodeId, $defaultCurrentSiteLanguage, $siteId);
            $node = $this->get('open_orchestra_backoffice.manager.node')->createNewLanguageNode($oldNode, $language);
            $dm = $this->get('doctrine.odm.mongodb.document_manager');
            $dm->persist($node);

            $this->get('open_orchestra_backoffice.manager.node')->updateBlockReferences($oldNode, $node);

            $dm->flush();
        }

        return $this->get('open_orchestra_api.transformer_manager')->get('node')->transform($node);
    }

    /**
     * @param string $nodeId
     *
     * @Config\Route("/{nodeId}/delete", name="open_orchestra_api_node_delete")
     * @Config\Method({"DELETE"})
     *
     * @Config\Security("has_role('ROLE_ACCESS_TREE_NODE')")
     *
     * @return Response
     */
    public function deleteAction($nodeId)
    {
        $siteId = $this->get('open_orchestra.manager.current_site')->getCurrentSiteId();
        $nodes = $this->get('open_orchestra_model.repository.node')->findByNodeIdAndSiteId($nodeId, $siteId);
        $node = $nodes->getNext();
        $this->get('open_orchestra_backoffice.manager.node')->deleteTree($nodes);
        $this->get('doctrine.odm.mongodb.document_manager')->flush();
        $this->dispatchEvent(NodeEvents::NODE_DELETE, new NodeEvent($node));

        return new Response('', 200);
    }

    /**
     * @param Request $request
     * @param string  $nodeId
     *
     * @Config\Route("/{nodeId}/duplicate", name="open_orchestra_api_node_duplicate")
     * @Config\Method({"POST"})
     *
     * @Config\Security("has_role('ROLE_ACCESS_TREE_NODE')")
     *
     * @return Response
     */
    public function duplicateAction(Request $request, $nodeId)
    {
        $language = $request->get('language');
        $siteId = $this->get('open_orchestra.manager.current_site')->getCurrentSiteId();
        /** @var NodeInterface $node */
        $node = $this->get('open_orchestra_model.repository.node')
            ->findOneByNodeIdAndLanguageAndVersionAndSiteId($nodeId, $language, $siteId);
        $newNode = $this->get('open_orchestra_backoffice.manager.node')->duplicateNode($node);

        $this->dispatchEvent(NodeEvents::NODE_DUPLICATE, new NodeEvent($newNode));

        $em = $this->get('doctrine.odm.mongodb.document_manager');
        $em->persist($newNode);

        $this->get('open_orchestra_backoffice.manager.node')->updateBlockReferences($node, $newNode);

        $em->flush();

        return new Response('', 200);
    }

    /**
     * @param Request $request
     * @param string  $nodeId
     *
     * @Config\Route("/{nodeId}/list-version", name="open_orchestra_api_node_list_version")
     * @Config\Method({"GET"})
     * @Api\Serialize()
     *
     * @Config\Security("has_role('ROLE_ACCESS_TREE_NODE')")
     *
     * @return Response
     */
    public function listVersionAction(Request $request, $nodeId)
    {
        $language = $request->get('language');
        $siteId = $this->get('open_orchestra.manager.current_site')->getCurrentSiteId();
        $node = $this->get('open_orchestra_model.repository.node')->findByNodeIdAndLanguageAndSiteId($nodeId, $language, $siteId);

        return $this->get('open_orchestra_api.transformer_manager')->get('node_collection')->transformVersions($node);
    }

    /**
     * @param Request $request
     * @param string $nodeMongoId
     *
     * @Config\Route("/update/{nodeMongoId}", name="open_orchestra_api_node_update")
     * @Config\Method({"POST"})
     * @Api\Serialize()
     *
     * @Config\Security("has_role('ROLE_ACCESS_TREE_NODE')")
     *
     * @return Response
     */
    public function changeStatusAction(Request $request, $nodeMongoId)
    {
        return $this->reverseTransform(
            $request, $nodeMongoId,
            'node',
            NodeEvents::NODE_CHANGE_STATUS,
            'OpenOrchestra\ModelInterface\Event\NodeEvent'
        );
    }

    /**
     * @param Request $request
     * @param string  $nodeId
     *
     * @Config\Route("/update/children/order/{nodeId}", name="open_orchestra_api_node_update_children_order")
     * @Config\Method({"POST"})
     * @Api\Serialize()
     *
     * @Config\Security("has_role('ROLE_ACCESS_TREE_NODE')")
     *
     * @return Response
     */
    public function updateChildrenOrderAction(Request $request, $nodeId)
    {
        $facade = $this->get('jms_serializer')->deserialize(
            $request->getContent(),
            'OpenOrchestra\ApiBundle\Facade\NodeCollectionFacade',
            $request->get('_format', 'json')
        );

        $node = $this->get('open_orchestra_model.repository.node')->findOneByNodeId($nodeId);

        $orderedNode = $this->get('open_orchestra_api.transformer_manager')->get('node_collection')->reverseTransformOrder($facade);

        $this->get('open_orchestra_backoffice.manager.node')->orderNodeChildren($orderedNode, $node);

        $this->get('doctrine.odm.mongodb.document_manager')->flush();

        return new Response('', 200);
    }
}
