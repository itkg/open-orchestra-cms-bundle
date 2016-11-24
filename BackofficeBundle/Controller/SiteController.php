<?php

namespace OpenOrchestra\BackofficeBundle\Controller;

use OpenOrchestra\ModelInterface\Event\SiteEvent;
use OpenOrchestra\ModelInterface\SiteEvents;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as Config;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use OpenOrchestra\ModelInterface\Model\SiteInterface;

/**
 * Class SiteController
 */
class SiteController extends AbstractAdminController
{
    /**
     * @param Request $request
     * @param string  $siteId
     *
     * @Config\Route("/site/form/{siteId}", name="open_orchestra_backoffice_site_form")
     * @Config\Method({"GET", "POST"})
     *
     * @return Response
     */
    public function formAction(Request $request, $siteId)
    {
        $site = $this->get('open_orchestra_model.repository.site')->findOneBySiteId($siteId);

        if ($site instanceof SiteInterface) {
            $oldAliases = $site->getAliases();
            $form = $this->createForm(
                'oo_site',
                $site,
                array(
                    'action' => $this->generateUrl('open_orchestra_backoffice_site_form', array(
                        'siteId' => $siteId,
                    ))
                )
            );

            $form->handleRequest($request);
            $message =  $this->get('translator')->trans('open_orchestra_backoffice.form.website.success');
            if ($this->handleForm($form, $message)) {
                $this->dispatchEvent(SiteEvents::SITE_UPDATE, new SiteEvent($site, $oldAliases));
            }

            return $this->renderAdminForm($form);
        }
    }

    /**
     * @param Request $request
     *
     * @Config\Route("/site/new", name="open_orchestra_backoffice_site_new")
     * @Config\Method({"GET", "POST"})
     *
     * @return Response
     */
    public function newAction(Request $request)
    {
        $site = $this->get('open_orchestra_backoffice.manager.site')->initializeNewSite();
        $form = $this->createForm('oo_site', $site, array(
            'action' => $this->generateUrl('open_orchestra_backoffice_site_new'),
            'method' => 'POST',
        ));

        $form->handleRequest($request);
        $message = $this->get('translator')->trans('open_orchestra_backoffice.form.website.creation');

        if ($this->handleForm($form, $message, $site)) {
            $this->dispatchEvent(SiteEvents::SITE_CREATE, new SiteEvent($site, null));
            $response = new Response('', Response::HTTP_CREATED, array('Content-type' => 'text/html; charset=utf-8'));

            return $this->render('BraincraftedBootstrapBundle::flash.html.twig', array(), $response);
        }

        return $this->renderAdminForm($form);
    }
}
