<?php

namespace OpenOrchestra\BackofficeBundle\Controller;

use OpenOrchestra\ModelInterface\Event\RedirectionEvent;
use OpenOrchestra\ModelInterface\Model\RedirectionInterface;
use OpenOrchestra\ModelInterface\RedirectionEvents;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as Config;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use OpenOrchestra\Backoffice\Security\ContributionActionInterface;

/**
 * Class RedirectionController
 *
 * @Config\Route("redirection")
 */
class RedirectionController extends AbstractAdminController
{
    /**
     * @param Request $request
     *
     * @Config\Route("/new", name="open_orchestra_backoffice_redirection_new")
     * @Config\Method({"GET", "POST"})
     *
     * @return Response
     */
    public function newAction(Request $request)
    {
        $redirectionClass = $this->container->getParameter('open_orchestra_model.document.redirection.class');
        /** @var RedirectionInterface $redirection */
        $redirection = new $redirectionClass();
        $this->denyAccessUnlessGranted(ContributionActionInterface::CREATE, $redirection);

        $form = $this->createForm('oo_redirection', $redirection, array(
            'action' => $this->generateUrl('open_orchestra_backoffice_redirection_new'),
            'method' => 'POST',
            'csrf_protection'   => false,
        ));

        $form->handleRequest($request);
        $message = $this->get('translator')->trans('open_orchestra_backoffice.form.redirection.new.success');

        if ($this->handleForm($form, $message, $redirection)) {
            $this->dispatchEvent(RedirectionEvents::REDIRECTION_CREATE, new RedirectionEvent($redirection));
            $response = new Response('', Response::HTTP_CREATED, array('Content-type' => 'text/html; charset=utf-8'));

            return $this->render('BraincraftedBootstrapBundle::flash.html.twig', array(), $response);
        }

        return $this->renderAdminForm($form);
    }

    /**
     * @param Request $request
     * @param string  $redirectionId
     *
     * @Config\Route("/form/{redirectionId}", name="open_orchestra_backoffice_redirection_form")
     * @Config\Method({"GET", "POST"})
     *
     * @return Response
     */
    public function formAction(Request $request, $redirectionId)
    {
        $redirection = $this->get('open_orchestra_model.repository.redirection')->find($redirectionId);
        $this->denyAccessUnlessGranted(ContributionActionInterface::EDIT, $redirection);

        $form = $this->createForm('oo_redirection', $redirection, array(
            'action' => $this->generateUrl('open_orchestra_backoffice_redirection_form', array(
                'redirectionId' => $redirectionId,
            )))
        );

        $form->handleRequest($request);
        $message =  $this->get('translator')->trans('open_orchestra_backoffice.form.redirection.edit.success');
        if ($this->handleForm($form, $message)) {
            $this->dispatchEvent(RedirectionEvents::REDIRECTION_UPDATE, new RedirectionEvent($redirection));
        }

        return $this->renderAdminForm($form);
    }
}
