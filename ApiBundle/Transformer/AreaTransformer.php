<?php

namespace OpenOrchestra\ApiBundle\Transformer;

use OpenOrchestra\ApiBundle\Exceptions\HttpException\AreaTransformerHttpException;
use OpenOrchestra\ApiBundle\Exceptions\TransformerParameterTypeException;
use OpenOrchestra\BaseApi\Facade\FacadeInterface;
use OpenOrchestra\BaseApi\Transformer\AbstractSecurityCheckerAwareTransformer;
use OpenOrchestra\BackofficeBundle\Manager\AreaManager;
use OpenOrchestra\BaseBundle\Context\CurrentSiteIdInterface;
use OpenOrchestra\ModelInterface\Model\AreaInterface;
use OpenOrchestra\ModelInterface\Model\NodeInterface;
use OpenOrchestra\ModelInterface\Model\TemplateInterface;
use OpenOrchestra\ModelInterface\Repository\NodeRepositoryInterface;
use OpenOrchestra\Backoffice\NavigationPanel\Strategies\TreeNodesPanelStrategy;
use OpenOrchestra\Backoffice\NavigationPanel\Strategies\TreeTemplatePanelStrategy;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Class AreaTransformer
 */
class AreaTransformer extends AbstractSecurityCheckerAwareTransformer
{
    protected $nodeRepository;
    protected $areaManager;
    protected $currentSiteManager;

    /**
     * @param string                        $facadeClass
     * @param NodeRepositoryInterface       $nodeRepository
     * @param AreaManager                   $areaManager
     * @param CurrentSiteIdInterface        $currentSiteManager
     * @param AuthorizationCheckerInterface $authorizationChecker
     */
    public function __construct(
        $facadeClass,
        NodeRepositoryInterface $nodeRepository,
        AreaManager $areaManager,
        CurrentSiteIdInterface $currentSiteManager,
        AuthorizationCheckerInterface $authorizationChecker
    )
    {
        parent::__construct($facadeClass, $authorizationChecker);
        $this->nodeRepository = $nodeRepository;
        $this->areaManager = $areaManager;
        $this->currentSiteManager = $currentSiteManager;
    }

    /**
     * @param AreaInterface      $area
     * @param NodeInterface|null $node
     * @param string|null        $parentAreaId
     *
     * @return FacadeInterface
     *
     * @throws TransformerParameterTypeException
     * @throws AreaTransformerHttpException
     */
    public function transform($area, NodeInterface $node = null, $parentAreaId = null)
    {
        $facade = $this->newFacade();

        if (!$area instanceof AreaInterface) {
            throw new TransformerParameterTypeException();
        }

        if (!$node instanceof NodeInterface) {
            throw new AreaTransformerHttpException();
        }

        $facade->label = $area->getLabel();
        $facade->areaId = $area->getAreaId();
        $facade->classes = $area->getHtmlClass();
        foreach ($area->getAreas() as $subArea) {
            $facade->addArea($this->getTransformer('area')->transform($subArea, $node, $area->getAreaId()));
        }
        foreach ($area->getBlocks() as $blockPosition => $block) {
            $otherNode = $node;
            $isInside = true;
            if (0 !== $block['nodeId'] && $node->getNodeId() != $block['nodeId']) {
                $siteId = $this->currentSiteManager->getCurrentSiteId();
                $otherNode = $this->nodeRepository->findInLastVersion($block['nodeId'], $node->getLanguage(), $siteId);
                $isInside = false;
            }
            $facade->addBlock($this->getTransformer('block')->transform(
                $otherNode->getBlock($block['blockId']),
                $isInside,
                $otherNode->getNodeId(),
                $block['blockId'],
                $area->getAreaId(),
                $blockPosition,
                $otherNode->getId()
            ));
        }
        $facade->boDirection = $area->getBoDirection();

        $facade->uiModel = $this->getTransformer('ui_model')->transform(
            array(
                'label' => $area->getLabel(),
                'class' => $area->getHtmlClass(),
                'id' => $area->getAreaId()
            )
        );

        $facade->addLink('_self_form', $this->generateRoute('open_orchestra_backoffice_area_form', array(
            'nodeId' => $node->getId(),
            'areaId' => $area->getAreaId(),
        )));

        if ($this->authorizationChecker->isGranted(TreeNodesPanelStrategy::ROLE_ACCESS_UPDATE_NODE, $node)) {
            $facade->addLink('_self_block', $this->generateRoute('open_orchestra_api_area_update_block', array(
                'nodeId' => $node->getId(),
                'areaId' => $area->getAreaId()
            )));
        }

        $facade->addLink('_self', $this->generateRoute('open_orchestra_api_area_show_in_node', array(
            'nodeId' => $node->getId(),
            'areaId' => $area->getAreaId()
        )));

        if ($this->authorizationChecker->isGranted(TreeNodesPanelStrategy::ROLE_ACCESS_UPDATE_NODE, $node)) {
            if ($parentAreaId) {
                $facade->addLink('_self_delete', $this->generateRoute('open_orchestra_api_area_delete_in_node_area',
                    array(
                        'nodeId' => $node->getId(),
                        'parentAreaId' => $parentAreaId,
                        'areaId' => $area->getAreaId()
                    )
                ));

            } else {
                $facade->addLink('_self_delete', $this->generateRoute('open_orchestra_api_area_delete_in_node',
                    array(
                        'nodeId' => $node->getId(),
                        'areaId' => $area->getAreaId(),
                    )
                ));
            }
        }

        return $facade;
    }

    /**
     * @param AreaInterface          $area
     * @param TemplateInterface|null $template
     * @param string|null            $parentAreaId
     *
     * @return FacadeInterface
     */
    public function transformFromTemplate($area, TemplateInterface $template = null, $parentAreaId = null)
    {
        $facade = $this->newFacade();

        $templateId = null;
        if ($template instanceof TemplateInterface) {
            $templateId = $template->getTemplateId();
        }

        $facade->label = $area->getLabel();
        $facade->areaId = $area->getAreaId();
        $facade->classes = $area->getHtmlClass();
        foreach ($area->getAreas() as $subArea) {
            $facade->addArea($this->getTransformer('area')->transformFromTemplate($subArea, $template, $area->getAreaId()));
        }

        $facade->boDirection = $area->getBoDirection();
        $facade->gridX = intval($area->getGridX());
        $facade->gridY = intval($area->getGridY());
        $facade->gridWidth = intval($area->getGridWidth());
        $facade->gridHeight = intval($area->getGridHeight());

        $facade->uiModel = $this->getTransformer('ui_model')->transform(
            array(
                'label' => $area->getLabel(),
                'class' => $area->getHtmlClass(),
                'id' => $area->getAreaId()
            )
        );
        $facade->addLink('_self_form', $this->generateRoute('open_orchestra_backoffice_template_area_form',
            array(
                'templateId' => $templateId,
                'areaId' => $area->getAreaId(),
            )
        ));

        $facade->addLink('_self', $this->generateRoute('open_orchestra_api_area_show_in_template', array(
            'templateId' => $templateId,
            'areaId' => $area->getAreaId()
        )));

        if ($this->authorizationChecker->isGranted(TreeTemplatePanelStrategy::ROLE_ACCESS_UPDATE_TEMPLATE, $template)) {
            if ($parentAreaId) {
                $facade->addLink('_self_delete', $this->generateRoute('open_orchestra_api_area_delete_in_template_area',
                    array(
                        'templateId' => $templateId,
                        'parentAreaId' => $parentAreaId,
                        'areaId' => $area->getAreaId()
                    )
                ));

            } else {
                $facade->addLink('_self_delete', $this->generateRoute('open_orchestra_api_area_delete_in_template',
                    array(
                        'templateId' => $templateId,
                        'areaId' => $area->getAreaId(),
                    )
                ));
            }
        }

        return $facade;
    }

    /**
     * @param FacadeInterface            $facade
     * @param AreaInterface|null         $source
     * @param NodeInterface|null         $node
     *
     * @return mixed|AreaInterface
     */
    public function reverseTransform(FacadeInterface $facade, $source = null, NodeInterface $node = null)
    {
        $blocks = $facade->getBlocks();
        $blockDocument = array();

        foreach ($blocks as $position => $blockFacade) {
            $blockArray = $this->getTransformer('block')->reverseTransformToArray($blockFacade, $node);
            $blockDocument[$position] = $blockArray;
            $block = $node->getBlock($blockArray['blockId']);
            if ($blockArray['nodeId'] !== 0) {
                $siteId = $this->currentSiteManager->getCurrentSiteId();
                $blockNode = $this->nodeRepository->findInLastVersion($blockArray['nodeId'], $node->getLanguage(), $siteId);
                $block = $blockNode->getBlock($blockArray['blockId']);
            }
            $block->addArea(array('nodeId' => $node->getId(), 'areaId' => $source->getAreaId()));
        }

        $this->areaManager->deleteAreaFromBlock($source->getBlocks(), $blockDocument, $source->getAreaId(), $node);
        $source->setBlocks($blockDocument);

        return $source;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'area';
    }
}
