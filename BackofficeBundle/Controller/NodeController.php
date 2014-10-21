<?php

namespace PHPOrchestra\BackofficeBundle\Controller;

use PHPOrchestra\ModelBundle\Model\NodeInterface;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as Config;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class NodeController
 */
class NodeController extends AbstractAdminController
{
    /**
     * @param Request $request
     * @param int     $nodeId
     *
     * @Config\Route("/node/form/{nodeId}", name="php_orchestra_backoffice_node_form")
     * @Config\Method({"GET", "POST"})
     *
     * @return Response
     */
    public function formAction(Request $request, $nodeId)
    {
        $nodeRepository = $this->container->get('php_orchestra_model.repository.node');
        $node = $nodeRepository->findOneByNodeIdAndSiteIdAndLastVersion($nodeId);

        $url = $this->generateUrl('php_orchestra_backoffice_node_form', array('nodeId' => $nodeId));
        $message = $this->get('translator')->trans('php_orchestra_backoffice.form.node.success');

        $form = $this->generateForm($node, $url);

        $form->handleRequest($request);

        $this->handleForm($form, $message, $node);

        return $this->renderAdminForm($form, array('path' => $this->generateUrl('php_orchestra_api_node_show', array('nodeId' => $node->getNodeId()))));
    }

    /**
     * @param Request $request
     * @param string  $parentId
     *
     * @Config\Route("/node/new/{parentId}", name="php_orchestra_backoffice_node_new")
     * @Config\Method({"GET", "POST"})
     *
     * @return Response
     */
    public function newAction(Request $request, $parentId)
    {
        $nodeClass = $this->container->getParameter('php_orchestra_model.document.node.class');
        $node = new $nodeClass();

        $contextManager = $this->get('php_orchestra_backoffice.context_manager');

        $node->setSiteId($contextManager->getCurrentSiteId());
        $node->setLanguage('fr');
        $node->setParentId($parentId);

        $url = $this->generateUrl('php_orchestra_backoffice_node_new', array('parentId' => $parentId));
        $message = $this->get('translator')->trans('php_orchestra_backoffice.form.node.success');

        $form = $this->generateForm($node, $url);

        $form->handleRequest($request);

        $this->handleForm($form, $message, $node);

        if ($form->getErrors()->count() > 0) {
            $statusCode = 400;
        } elseif (!is_null($node->getNodeId())) {
                $url = $this->generateUrl('php_orchestra_backoffice_node_form', array('nodeId' => $node->getNodeId()));

                return $this->redirect($url);
        } else {
            $statusCode = 200;
        };

        $response = new Response('', $statusCode, array('Content-type' => 'text/html; charset=utf-8'));

        return $this->render(
            'PHPOrchestraBackofficeBundle:Editorial:template.html.twig',
            array('form' => $form->createView()),
            $response
        );
    }

    /**
     * @param NodeInterface $node
     * @param string        $url
     *
     * @return Form
     */
    protected function generateForm($node, $url)
    {
        $form = $this->createForm(
            'node',
            $node,
            array(
                'action' => $url
            )
        );

        return $form;
    }
}
