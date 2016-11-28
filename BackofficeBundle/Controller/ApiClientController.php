<?php

namespace OpenOrchestra\BackofficeBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration as Config;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use OpenOrchestra\Backoffice\Security\ContributionActionInterface;

/**
 * Class ApiClientController
 */
class ApiClientController extends AbstractAdminController
{
    /**
     * @param Request $request
     *
     * @Config\Route("/new", name="open_orchestra_backoffice_api_client_new")
     * @Config\Method({"GET","POST"})
     *
     * @return Response
     */
    public function newAction(Request $request)
    {
        $apiClientClass = $this->container->getParameter('open_orchestra_api.document.api_client.class');
        $apiClient = new $apiClientClass();

        $this->denyAccessUnlessGranted(ContributionActionInterface::CREATE, $apiClient);

        $form = $this->createForm('oo_api_client', $apiClient, array(
            'action' => $this->generateUrl('open_orchestra_backoffice_api_client_new'),
            'method' => 'POST',
        ));

        $form->handleRequest($request);
        $message = $this->get('translator')->trans('open_orchestra_backoffice.form.api_client.new.success');

        if ($this->handleForm($form, $message, $apiClient)) {
            $response = new Response('', Response::HTTP_CREATED, array('Content-type' => 'text/html; charset=utf-8'));

            return $this->render('BraincraftedBootstrapBundle::flash.html.twig', array(), $response);
        }

        return $this->renderAdminForm($form);
    }

    /**
     * @param Request $request
     * @param string  $apiClientId
     *
     * @Config\Route("/form/{apiClientId}", name="open_orchestra_backoffice_api_client_form")
     * @Config\Method({"GET","POST"})
     *
     * @return Response
     */
    public function formAction(Request $request, $apiClientId)
    {
        $apiClient = $this->get('open_orchestra_api.repository.api_client')->find($apiClientId);

        $this->denyAccessUnlessGranted(ContributionActionInterface::EDIT, $apiClient);

        $form = $this->createForm('oo_api_client', $apiClient, array(
            'action' => $this->generateUrl('open_orchestra_backoffice_api_client_form', array(
                'apiClientId' => $apiClientId,
            )))
        );

        $form->handleRequest($request);
        $this->handleForm($form, $this->get('translator')->trans('open_orchestra_backoffice.form.api_client.edit.success'));

        return $this->renderAdminForm($form);
    }
}
