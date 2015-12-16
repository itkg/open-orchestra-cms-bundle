<?php

namespace OpenOrchestra\ApiBundle\Transformer;

use OpenOrchestra\ApiBundle\Exceptions\HttpException\StatusChangeNotGrantedHttpException;
use OpenOrchestra\ApiBundle\Exceptions\TransformerParameterTypeException;
use OpenOrchestra\Backoffice\Exception\StatusChangeNotGrantedException;
use OpenOrchestra\Backoffice\NavigationPanel\Strategies\ContentTypeForContentPanelStrategy;
use OpenOrchestra\BaseApi\Facade\FacadeInterface;
use OpenOrchestra\BaseApi\Transformer\AbstractSecurityCheckerAwareTransformer;
use OpenOrchestra\ModelInterface\Event\StatusableEvent;
use OpenOrchestra\ModelInterface\StatusEvents;
use OpenOrchestra\ModelInterface\Model\ContentInterface;
use OpenOrchestra\ModelInterface\Repository\StatusRepositoryInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Class ContentTransformer
 */
class ContentTransformer extends AbstractSecurityCheckerAwareTransformer
{
    protected $statusRepository;
    protected $eventDispatcher;

    /**
     * @param string                        $facadeClass
     * @param StatusRepositoryInterface     $statusRepository
     * @param EventDispatcherInterface      $eventDispatcher
     * @param AuthorizationCheckerInterface $authorizationChecker
     */
    public function __construct(
        $facadeClass,
        StatusRepositoryInterface $statusRepository,
        EventDispatcherInterface $eventDispatcher,
        AuthorizationCheckerInterface $authorizationChecker
    )
    {
        $this->statusRepository = $statusRepository;
        $this->eventDispatcher = $eventDispatcher;
        parent::__construct($facadeClass, $authorizationChecker);
    }

    /**
     * @param ContentInterface $content
     *
     * @return FacadeInterface
     *
     * @throws TransformerParameterTypeException
     */
    public function transform($content)
    {
        if (!$content instanceof ContentInterface) {
            throw new TransformerParameterTypeException();
        }

        $facade = $this->newFacade();

        $facade->id = $content->getContentId();
        $facade->contentType = $content->getContentType();
        $facade->name = $content->getName();
        $facade->version = $content->getVersion();
        $facade->contentTypeVersion = $content->getContentTypeVersion();
        $facade->language = $content->getLanguage();
        $facade->status = $this->getTransformer('status')->transform($content->getStatus());
        $facade->statusLabel = $facade->status->label;
        $facade->createdAt = $content->getCreatedAt();
        $facade->updatedAt = $content->getUpdatedAt();
        $facade->createdBy = $content->getCreatedBy();
        $facade->updatedBy = $content->getUpdatedBy();
        $facade->deleted = $content->isDeleted();
        $facade->linkedToSite = $content->isLinkedToSite();

        foreach ($content->getAttributes() as $attribute) {
            $contentAttribute = $this->getTransformer('content_attribute')->transform($attribute);
            $facade->addAttribute($contentAttribute);
        }

        if ($this->authorizationChecker->isGranted(ContentTypeForContentPanelStrategy::ROLE_ACCESS_UPDATE_CONTENT_TYPE_FOR_CONTENT)) {
            $facade->addLink('_self_form', $this->generateRoute('open_orchestra_backoffice_content_form', array(
                'contentId' => $content->getContentId(),
                'language' => $content->getLanguage(),
                'version' => $content->getVersion(),
            )));
        }

        if ($this->authorizationChecker->isGranted(ContentTypeForContentPanelStrategy::ROLE_ACCESS_CREATE_CONTENT_TYPE_FOR_CONTENT)) {
            $facade->addLink('_self_duplicate', $this->generateRoute('open_orchestra_api_content_duplicate', array(
                'contentId' => $content->getContentId(),
                'language' => $content->getLanguage(),
                'version' => $content->getVersion(),
            )));
        }

        $facade->addLink('_self_version', $this->generateRoute('open_orchestra_api_content_list_version', array(
            'contentId' => $content->getContentId(),
            'language' => $content->getLanguage(),
        )));

        if ($this->authorizationChecker->isGranted(ContentTypeForContentPanelStrategy::ROLE_ACCESS_DELETE_CONTENT_TYPE_FOR_CONTENT)) {
            $facade->addLink('_self_delete', $this->generateRoute('open_orchestra_api_content_delete', array(
                'contentId' => $content->getId()
            )));
        }

        $facade->addLink('_self', $this->generateRoute('open_orchestra_api_content_show_or_create', array(
            'contentId' => $content->getContentId(),
            'version' => $content->getVersion(),
            'language' => $content->getLanguage(),
        )));

        $facade->addLink('_self_without_parameters', $this->generateRoute('open_orchestra_api_content_show_or_create', array(
            'contentId' => $content->getContentId(),
        )));

        $facade->addLink('_language_list', $this->generateRoute('open_orchestra_api_parameter_languages_show'));

        $facade->addLink('_status_list', $this->generateRoute('open_orchestra_api_content_list_status', array(
            'contentMongoId' => $content->getId()
        )));

        $facade->addLink('_self_status_change', $this->generateRoute('open_orchestra_api_content_update', array(
            'contentMongoId' => $content->getId()
        )));

        return $facade;
    }

    /**
     * @param FacadeInterface $facade
     * @param ContentInterface|null         $source
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
     * @return string
     */
    public function getName()
    {
        return 'content';
    }
}
