<?php

namespace OpenOrchestra\BackofficeBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as Config;
use OpenOrchestra\ModelInterface\ContentEvents;
use OpenOrchestra\ModelInterface\Event\ContentEvent;
use OpenOrchestra\ModelInterface\Model\ContentInterface;
use OpenOrchestra\ModelInterface\Model\ContentTypeInterface;
use OpenOrchestra\Backoffice\Security\ContributionActionInterface;

/**
 * Class ContentController
 */
class ContentController extends AbstractAdminController
{
    /**
     * @param Request $request
     * @param string  $contentId
     * @param string  $language
     * @param string  $version
     *
     * @Config\Route(
     *     "/content/form/{contentId}/{language}/{version}",
     *      name="open_orchestra_backoffice_content_form",
     *      defaults={"version": null},
     * )
     * @Config\Method({"GET", "POST"})
     *
     * @return Response
     */
    public function formAction(Request $request, $contentId, $language, $version)
    {
        $content = $this->get('open_orchestra_model.repository.content')->findOneByLanguageAndVersion($contentId, $language, $version);
        if (!$content instanceof ContentInterface) {
            throw new \UnexpectedValueException();
        }
        $contentType = $this->get('open_orchestra_model.repository.content_type')->findOneByContentTypeIdInLastVersion($content->getContentType());
        if (!$contentType instanceof ContentTypeInterface) {
            throw new \UnexpectedValueException();
        }
        $this->denyAccessUnlessGranted(ContributionActionInterface::EDIT, $content);

        $publishedContents = $this->get('open_orchestra_model.repository.content')->findAllPublishedByContentId($contentId);
        $isUsed = false;
        foreach ($publishedContents as $publishedContent) {
            $isUsed = $isUsed || $publishedContent->isUsed();
        }
        $options = array(
            'action' => $this->generateUrl('open_orchestra_backoffice_content_form', array(
                'contentId' => $content->getContentId(),
                'language' => $content->getLanguage(),
                'version' => $content->getVersion(),
            )),
            'delete_button' => $this->canDeleteContent($content),
            'need_link_to_site_defintion' => false,
            'is_blocked_edition' => $content->getStatus() ? $content->getStatus()->isBlockedEdition() : false,
        );
        $form = $this->createForm('oo_content', $content, $options);

        $form->handleRequest($request);

        if ($form->isValid()) {
            $this->get('object_manager')->flush();
            $this->dispatchEvent(ContentEvents::CONTENT_UPDATE, new ContentEvent($content));

            $message =  $this->get('translator')->trans('open_orchestra_backoffice.form.content.success');
            $this->get('session')->getFlashBag()->add('success', $message);

        }

        $code = Response::HTTP_OK;
        if ($form->isSubmitted() && !$form->isValid()) {
            $code = Response::HTTP_UNPROCESSABLE_ENTITY;
        }
        $response = new Response('', $code, array('Content-type' => 'text/html; charset=utf-8', 'version' => $content->getVersion()));

        return $this->renderAdminForm(
            $form,
            array(),
            $response,
            $this->getFormTemplate($content->getContentType()
        ));
    }

    /**
     * @param Request $request
     * @param string  $contentTypeId
     *
     * @Config\Route("/content/new/{contentTypeId}/{language}", name="open_orchestra_backoffice_content_new")
     * @Config\Method({"GET", "POST"})
     *
     * @return Response
     */
    public function newAction(Request $request, $contentTypeId, $language)
    {
        $contentManager = $this->get('open_orchestra_backoffice.manager.content');
        $contentType = $this->get('open_orchestra_model.repository.content_type')->findOneByContentTypeIdInLastVersion($contentTypeId);
        if (!$contentType instanceof ContentTypeInterface) {
            throw new \UnexpectedValueException();
        }
        $content = $contentManager->initializeNewContent($contentTypeId, $language, $contentType->isLinkedToSite() && $contentType->isAlwaysShared());
        if (!$content instanceof ContentInterface) {
            throw new \UnexpectedValueException();
        }
        $this->denyAccessUnlessGranted(ContributionActionInterface::CREATE, $content);

        $form = $this->createForm('oo_content', $content, array(
            'action' => $this->generateUrl('open_orchestra_backoffice_content_new', array(
                'contentTypeId' => $contentTypeId,
                'language' => $language,
            )),
            'method' => 'POST',
            'new_button' => true,
            'need_link_to_site_defintion' => $contentType->isLinkedToSite() && !$contentType->isAlwaysShared(),
            'is_blocked_edition' => $content->getStatus() ? $content->getStatus()->isBlockedEdition() : false,
        ));

        $status = $content->getStatus();
        $form->handleRequest($request);

        if ($form->isValid()) {
            $content = $contentManager->setVersionName($content);
            $documentManager = $this->get('object_manager');
            $documentManager->persist($content);
            $this->dispatchEvent(ContentEvents::CONTENT_CREATION, new ContentEvent($content));

            $languages = $this->get('open_orchestra_backoffice.context_manager')->getCurrentSiteLanguages();
            foreach ($languages as $siteLanguage) {
                if ($language !== $siteLanguage) {
                    $translatedContent = $contentManager->createNewLanguageContent($content, $siteLanguage);
                    $documentManager->persist($translatedContent);
                    $this->dispatchEvent(ContentEvents::CONTENT_CREATION, new ContentEvent($translatedContent));
                }
            }
            $documentManager->flush();
            if ($status->getId() !== $content->getStatus()->getId()) {
                $this->dispatchEvent(ContentEvents::CONTENT_CHANGE_STATUS, new ContentEvent($content, $status));
            }

            $message = $this->get('translator')->trans('open_orchestra_backoffice.form.content.creation');
            $response = new Response(
                $message,
                Response::HTTP_CREATED,
                array('Content-type' => 'text/plain; charset=utf-8', 'contentId' => $content->getContentId(), 'version' => $content->getVersion())
            );

            return $response;
        }

        return $this->renderAdminForm($form);
    }

    /**
     * @param ContentInterface $content
     *
     * @return bool
     */
    protected function canDeleteContent(ContentInterface $content) {
        $contentRepository = $this->get('open_orchestra_model.repository.content');

        return false === $contentRepository->hasContentIdWithoutAutoUnpublishToState($content->getContentId()) &&
               $this->isGranted(ContributionActionInterface::DELETE, $content);
    }

    /**
     * Get Form Template related to content of $contentTypeId
     *
     * @param string $contentTypeId
     *fof
     * @return string
     */
    protected function getFormTemplate($contentTypeId)
    {
        $template = AbstractAdminController::TEMPLATE;

        $contentType = $this->get('open_orchestra_model.repository.content_type')->findOneByContentTypeIdInLastVersion($contentTypeId);

        if ($contentType instanceof ContentTypeInterface) {
            $customTemplate = $contentType->getTemplate();

            if ($customTemplate != '' && $this->get('templating')->exists($customTemplate)) {
                $template = $customTemplate;
            }
        }

        return $template;
    }
}
