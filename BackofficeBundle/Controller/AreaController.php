<?php

namespace OpenOrchestra\BackofficeBundle\Controller;

use OpenOrchestra\Backoffice\NavigationPanel\Strategies\TransverseNodePanelStrategy;
use OpenOrchestra\Backoffice\NavigationPanel\Strategies\TreeNodesPanelStrategy;
use OpenOrchestra\Backoffice\NavigationPanel\Strategies\TreeTemplatePanelStrategy;
use OpenOrchestra\ModelInterface\Event\NodeEvent;
use OpenOrchestra\ModelInterface\Event\TemplateEvent;
use OpenOrchestra\ModelInterface\Model\AreaInterface;
use OpenOrchestra\ModelInterface\Model\AreaContainerInterface;
use OpenOrchestra\ModelInterface\Model\NodeInterface;
use OpenOrchestra\ModelInterface\NodeEvents;
use OpenOrchestra\ModelInterface\TemplateEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as Config;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class AreaController
 */
class AreaController extends AbstractEditionRoleController
{
    /**
     * @param Request $request
     * @param string  $nodeId
     * @param string  $areaId
     *
     * @Config\Route("/area/form/{nodeId}/{areaId}", name="open_orchestra_backoffice_area_form")
     * @Config\Method({"GET", "POST"})
     *
     * @return Response
     */
    public function formAction(Request $request, $nodeId, $areaId)
    {
        $node = $this->get('open_orchestra_model.repository.node')->find($nodeId);
        $area = $this->get('open_orchestra_model.repository.node')->findAreaByAreaId($node, $areaId);

        $actionUrl = $this->generateUrl('open_orchestra_backoffice_area_form', array(
            'nodeId' => $nodeId,
            'areaId' => $areaId
        ));

        $editionRole = $this->getEditionRole($node);
        $form = $this->generateForm($request, $actionUrl, $area, $node, $editionRole);
        $message = $this->get('translator')->trans('open_orchestra_backoffice.form.area.success');
        if ($this->handleForm($form, $message)) {
            $this->dispatchEvent(NodeEvents::NODE_UPDATE_AREA, new NodeEvent($node));
        }

        return $this->renderAdminForm($form);
    }

    /**
     * @param Request $request
     * @param string  $templateId
     * @param string  $areaId
     *
     * @config\Route("/template/area/form/{templateId}/{areaId}", name="open_orchestra_backoffice_template_area_form")
     * @Config\Method({"GET", "POST"})
     *
     * @return Response
     */
    public function templateFormAction(Request $request, $templateId, $areaId)
    {
        $template = $this->get('open_orchestra_model.repository.template')->findOneByTemplateId($templateId);
        $area = $this->get('open_orchestra_model.repository.template')->findAreaByAreaId($template, $areaId);
        $actionUrl = $this->generateUrl('open_orchestra_backoffice_template_area_form', array(
            'templateId' => $templateId,
            'areaId' => $areaId
        ));

        $form = $this->generateForm($request, $actionUrl, $area, $template, TreeTemplatePanelStrategy::ROLE_ACCESS_UPDATE_TEMPLATE);
        $message = $this->get('translator')->trans('open_orchestra_backoffice.form.area.success');
        if ($this->handleForm($form, $message)) {
            $this->dispatchEvent(TemplateEvents::TEMPLATE_AREA_UPDATE, new TemplateEvent($template));
        }

        return $this->renderAdminForm($form);
    }

    /**
     * @param Request                $request
     * @param string                 $actionUrl
     * @param AreaInterface          $area
     * @param AreaContainerInterface $areaContainer
     * @param string                 $role
     *
     * @return FormInterface
     */
    protected function generateForm(Request $request, $actionUrl, $area, AreaContainerInterface $areaContainer, $role)
    {
        $options = array('action' => $actionUrl);

        $options['disabled'] = !$this->get('security.authorization_checker')->isGranted($role, $areaContainer);
        $form = parent::createForm('oo_area', $area, $options);

        $form->handleRequest($request);

        return $form;
    }
}
