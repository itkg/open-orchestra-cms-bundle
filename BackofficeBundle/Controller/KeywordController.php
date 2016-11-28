<?php

namespace OpenOrchestra\BackofficeBundle\Controller;

use OpenOrchestra\ModelInterface\Event\KeywordEvent;
use OpenOrchestra\ModelInterface\KeywordEvents;
use OpenOrchestra\ModelInterface\Model\KeywordInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as Config;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use OpenOrchestra\Backoffice\Security\ContributionActionInterface;

/**
 * Class KeywordController
 */
class KeywordController extends AbstractAdminController
{
    /**
     * @param Request $request
     * @param int     $keywordId
     *
     * @Config\Route("/keyword/form/{keywordId}", name="open_orchestra_backoffice_keyword_form")
     * @Config\Method({"GET", "POST"})
     *
     * @return Response
     */
    public function formAction(Request $request, $keywordId)
    {
        $keyword = $this->get('open_orchestra_model.repository.keyword')->find($keywordId);
        $this->denyAccessUnlessGranted(ContributionActionInterface::EDIT, $keyword);

        $form = $this->createForm(
            'oo_keyword',
            $keyword,
            array(
                'action' => $this->generateUrl('open_orchestra_backoffice_keyword_form', array('keywordId' => $keywordId)),
            )
        );

        $form->handleRequest($request);
        $this->handleForm($form, $this->get('translator')->trans('open_orchestra_backoffice.form.keyword.success'));

        return $this->renderAdminForm($form);
    }

    /**
     * @param Request $request
     *
     * @Config\Route("/keyword/new", name="open_orchestra_backoffice_keyword_new")
     * @Config\Method({"GET", "POST"})
     *
     * @return Response
     */
    public function newAction(Request $request)
    {
        $keywordClass = $this->container->getParameter('open_orchestra_model.document.keyword.class');
        /** @var KeywordInterface $keyword */
        $keyword = new $keywordClass();
        $this->denyAccessUnlessGranted(ContributionActionInterface::CREATE, $keyword);

        $form = $this->createForm('oo_keyword', $keyword, array(
            'action' => $this->generateUrl('open_orchestra_backoffice_keyword_new'),
            'method' => 'POST',
        ));

        $form->handleRequest($request);
        $message = $this->get('translator')->trans('open_orchestra_backoffice.form.keyword.creation');

        if ($this->handleForm($form, $message, $keyword)) {
            $this->dispatchEvent(KeywordEvents::KEYWORD_CREATE, new KeywordEvent($keyword));
            $response = new Response('', Response::HTTP_CREATED, array('Content-type' => 'text/html; charset=utf-8'));

            return $this->render('BraincraftedBootstrapBundle::flash.html.twig', array(), $response);
        }

        return $this->renderAdminForm($form);
    }
}
