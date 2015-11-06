<?php

namespace OpenOrchestra\ApiBundle\Controller;

use OpenOrchestra\ApiBundle\Controller\ControllerTrait\HandleRequestDataTable;
use OpenOrchestra\BaseApi\Facade\FacadeInterface;
use OpenOrchestra\UserBundle\Event\GroupEvent;
use OpenOrchestra\UserBundle\GroupEvents;
use OpenOrchestra\BaseApiBundle\Controller\Annotation as Api;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as Config;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use OpenOrchestra\BaseApiBundle\Controller\BaseController;

/**
 * Class GroupController
 *
 * @Config\Route("group")
 *
 * @Api\Serialize()
 */
class GroupController extends BaseController
{
    use HandleRequestDataTable;

    /**
     * @param string $groupId
     *
     * @Config\Route("/{groupId}", name="open_orchestra_api_group_show")
     * @Config\Method({"GET"})
     *
     * @Config\Security("is_granted('ROLE_ACCESS_GROUP')")
     *
     * @return FacadeInterface
     */
    public function showAction($groupId)
    {
        $group = $this->get('open_orchestra_user.repository.group')->find($groupId);

        return $this->get('open_orchestra_api.transformer_manager')->get('group')->transform($group);
    }

    /**
     * @param Request $request
     * @param string  $groupId
     *
     * @Config\Route("/{groupId}", name="open_orchestra_api_group_edit")
     * @Config\Method({"POST"})
     *
     * @Config\Security("has_role('ROLE_ACCESS_GROUP')")
     *
     * @return array
     */
    public function editAction(Request $request, $groupId)
    {
        $facade = $this->get('jms_serializer')->deserialize($request->getContent(), 'OpenOrchestra\ApiBundle\Facade\GroupFacade', $request->get('_format', 'json'));
        $group = $this->get('open_orchestra_user.repository.group')->find($groupId);

        $this->get('open_orchestra_api.transformer_manager')->get('group')->reverseTransform($facade, $group);

        $this->get('object_manager')->flush();

        return array();
    }

    /**
     * @param Request $request
     *
     * @Config\Route("", name="open_orchestra_api_group_list")
     * @Config\Method({"GET"})
     *
     * @Config\Security("is_granted('ROLE_ACCESS_GROUP')")
     *
     * @return FacadeInterface
     */
    public function listAction(Request $request)
    {
        $mapping = $this
            ->get('open_orchestra.annotation_search_reader')
            ->extractMapping($this->container->getParameter('open_orchestra_user.document.group.class'));
        $repository = $this->get('open_orchestra_user.repository.group');
        $collectionTransformer = $this->get('open_orchestra_api.transformer_manager')->get('group_collection');

        return $this->handleRequestDataTable($request, $repository, $mapping, $collectionTransformer);
    }

    /**
     * @param int $groupId
     *
     * @Config\Route("/{groupId}/delete", name="open_orchestra_api_group_delete")
     * @Config\Method({"DELETE"})
     *
     * @Config\Security("is_granted('ROLE_ACCESS_DELETE_GROUP')")
     *
     * @return Response
     */
    public function deleteAction($groupId)
    {
        $group = $this->get('open_orchestra_user.repository.group')->find($groupId);
        $dm = $this->get('object_manager');
        $this->dispatchEvent(GroupEvents::GROUP_DELETE, new GroupEvent($group));
        $dm->remove($group);
        $dm->flush();

        return array();
    }
}
