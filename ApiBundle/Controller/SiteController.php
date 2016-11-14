<?php

namespace OpenOrchestra\ApiBundle\Controller;

use OpenOrchestra\ApiBundle\Controller\ControllerTrait\HandleRequestDataTable;
use OpenOrchestra\BaseApi\Facade\FacadeInterface;
use OpenOrchestra\ModelInterface\Event\SiteEvent;
use OpenOrchestra\ModelInterface\SiteEvents;
use OpenOrchestra\BaseApiBundle\Controller\Annotation as Api;
use OpenOrchestra\Pagination\Configuration\PaginateFinderConfiguration;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as Config;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use OpenOrchestra\BaseApiBundle\Controller\BaseController;

/**
 * Class SiteController
 *
 * @Config\Route("site")
 *
 * @Api\Serialize()
 */
class SiteController extends BaseController
{
    use HandleRequestDataTable;

    /**
     * @param string $siteId
     *
     * @return FacadeInterface
     *
     * @Config\Route("/{siteId}", name="open_orchestra_api_site_show")
     * @Config\Method({"GET"})
     * @Config\Security("is_granted('ROLE_ACCESS_SITE')")
     *
     * @Api\Groups({
     *     OpenOrchestra\ApiBundle\Context\CMSGroupContext::THEME,
     *     OpenOrchestra\ApiBundle\Context\CMSGroupContext::BLOCKS
     * })
     */
    public function showAction($siteId)
    {
        $site = $this->get('open_orchestra_model.repository.site')->findOneBySiteId($siteId);

        return $this->get('open_orchestra_api.transformer_manager')->get('site')->transform($site);
    }

    /**
     * @param Request $request
     *
     * @Config\Route("", name="open_orchestra_api_site_list")
     * @Config\Method({"GET"})
     *
     * @Config\Security("is_granted('ROLE_ACCESS_SITE')")
     *
     * @return FacadeInterface
     */
    public function listAction(Request $request)
    {
        $repository =  $this->get('open_orchestra_model.repository.site');
        $transformer = $this->get('open_orchestra_api.transformer_manager')->get('site_collection');

        if ($entityId = $request->get('entityId')) {
            $element = $repository->find($entityId);
            return $transformer->transform(array($element));
        }

        $configuration = PaginateFinderConfiguration::generateFromRequest($request);
        $configuration->addColumnSearch('deleted', false);
        $mapping = $this
            ->get('open_orchestra.annotation_search_reader')
            ->extractMapping($this->container->getParameter('open_orchestra_model.document.site.class'));
        $configuration->setDescriptionEntity($mapping);
        $siteCollection = $repository->findByDeletedForPaginate(false, $configuration);
        $recordsTotal = $repository->countByDeleted(false);
        $recordsFiltered = $repository->countWithSearchFilterByDeleted(false, $configuration);

        return $this->generateFacadeDataTable($transformer, $siteCollection, $recordsTotal, $recordsFiltered);
    }

    /**
     * @Config\Route("/list/available", name="open_orchestra_api_available_site_list")
     * @Config\Method({"GET"})
     * @Api\Groups({
     *     OpenOrchestra\ApiBundle\Context\CMSGroupContext::SITE_MAIN_ALIAS
     * })
     *
     * @Config\Security("is_granted('IS_AUTHENTICATED_FULLY')")
     *
     * @return FacadeInterface
     */
    public function listAvailableSiteAction()
    {
        $availableSite = $this->get('open_orchestra_backoffice.context_manager')->getAvailableSites();

        return $this->get('open_orchestra_api.transformer_manager')->get('site_collection')->transform($availableSite);
    }

    /**
     * @param string $siteId
     *
     * @Config\Route("/{siteId}/delete", name="open_orchestra_api_site_delete")
     * @Config\Method({"DELETE"})
     *
     * @Config\Security("is_granted('ROLE_ACCESS_DELETE_SITE')")
     *
     * @return Response
     */
    public function deleteAction($siteId)
    {
        $site = $this->get('open_orchestra_model.repository.site')->findOneBySiteId($siteId);
        $site->setDeleted(true);
        $this->dispatchEvent(SiteEvents::SITE_DELETE, new SiteEvent($site));
        $this->get('object_manager')->flush();

        return array();
    }

    /**
     * @Config\Route("/{siteId}/languages", name="open_orchestra_api_site_languages_show")
     * @Config\Method({"GET"})
     *
     * @return Response
     */
    public function getAllLanguagesAction($siteId)
    {
        $site = $this->get('open_orchestra_model.repository.site')->findOneBySiteId($siteId);

        return array('languages' => $site->getLanguages());
    }
}
