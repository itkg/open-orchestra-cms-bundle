<?php

namespace PHPOrchestra\ApiBundle\Transformer;

use PHPOrchestra\ApiBundle\Facade\AreaFacade;
use PHPOrchestra\ApiBundle\Facade\FacadeInterface;
use PHPOrchestra\ModelBundle\Document\Area;
use PHPOrchestra\ModelBundle\Document\Node;
use PHPOrchestra\ModelBundle\Model\AreaInterface;
use PHPOrchestra\ModelBundle\Model\NodeInterface;
use PHPOrchestra\ModelBundle\Model\TemplateInterface;
use PHPOrchestra\ModelBundle\Repository\NodeRepository;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Class AreaTransformer
 */
class AreaTransformer extends AbstractTransformer
{
    protected $nodeRepository;

    /**
     * @param NodeRepository $nodeRepository
     */
    public function __construct(NodeRepository $nodeRepository)
    {
        $this->nodeRepository = $nodeRepository;
    }

    /**
     * @param AreaInterface $mixed
     * @param NodeInterface $node
     * @param string        $parentAreaId
     *
     * @return FacadeInterface
     */
    public function transform($mixed, NodeInterface $node = null, $parentAreaId = null)
    {
        $facade = new AreaFacade();

        if ($node instanceof NodeInterface) {
            $nodeId = $node->getNodeId();
        } else {
            $nodeId = null;
        }

        $facade->areaId = $mixed->getAreaId();
        $facade->classes = implode(',', $mixed->getClasses());
        foreach ($mixed->getAreas() as $subArea) {
            $facade->addArea($this->getTransformer('area')->transform($subArea, $node, $mixed->getAreaId()));
        }
        foreach ($mixed->getBlocks() as $blockPosition => $block) {
            if (0 === $block['nodeId'] || $node->getNodeId() == $block['nodeId']) {
                $facade->addBlock($this->getTransformer('block')->transform(
                    $node->getBlocks()->get($block['blockId']),
                    true,
                    $nodeId,
                    $block['blockId'],
                    $mixed->getAreaId(),
                    $blockPosition
                ));
            } else {
                $otherNode = $this->nodeRepository->findOneByNodeId($block['nodeId']);
                $facade->addBlock($this->getTransformer('block')->transform(
                    $otherNode->getBlocks()->get($block['blockId']),
                    false,
                    $otherNode->getNodeId(),
                    $block['blockId'],
                    $mixed->getAreaId(),
                    $blockPosition
                ));
            }
        }
        $facade->boDirection = $mixed->getBoDirection();

        $facade->uiModel = $this->getTransformer('ui_model')->transform(
            array(
                'label' => $mixed->getAreaId(),
                'class' => $mixed->getHtmlClass()
            )
        );
        $facade->addLink('_self_form', $this->getRouter()->generate('php_orchestra_backoffice_area_form',
            array(
                'nodeId' => $nodeId,
                'areaId' => $mixed->getAreaId(),
            ),
            UrlGeneratorInterface::ABSOLUTE_URL
        ));
        $facade->addLink('_self_block', $this->getRouter()->generate('php_orchestra_api_area_update_block',
            array(
                'nodeId' => $node->getNodeId(),
                'areaId' => $mixed->getAreaId()
            ),
            UrlGeneratorInterface::ABSOLUTE_URL
        ));

        if ($parentAreaId) {
            $facade->addLink('_self_delete', $this->getRouter()->generate('php_orchestra_api_area_delete_in_node_area',
                array(
                    'nodeId' => $nodeId,
                    'parentAreaId' => $parentAreaId,
                    'areaId' => $mixed->getAreaId()
                ),
                UrlGeneratorInterface::ABSOLUTE_URL
            ));

        } else {
            $facade->addLink('_self_delete', $this->getRouter()->generate('php_orchestra_api_area_delete_in_node',
                array(
                    'nodeId' => $nodeId,
                    'areaId' => $mixed->getAreaId(),
                ),
                UrlGeneratorInterface::ABSOLUTE_URL
            ));
        }

        return $facade;
    }

    /**
     * @param AreaInterface     $mixed
     * @param TemplateInterface $template
     *
     * @return FacadeInterface
     */
    public function transformFromTemplate($mixed, TemplateInterface $template = null, $parentAreaId = null)
    {
        $facade = new AreaFacade();

        if ($template instanceof TemplateInterface) {
            $templateId = $template->getTemplateId();
        } else {
            $templateId = null;
        }

        $facade->areaId = $mixed->getAreaId();
        $facade->classes = implode(',', $mixed->getClasses());
        foreach ($mixed->getAreas() as $subArea) {
            $facade->addArea($this->getTransformer('area')->transformFromTemplate($subArea, $template, $mixed->getAreaId()));
        }
        $facade->boDirection = $mixed->getBoDirection();

        $facade->uiModel = $this->getTransformer('ui_model')->transform(
            array(
                'label' => $mixed->getAreaId(),
                'class' => $mixed->getHtmlClass()
            )
        );
        $facade->addLink('_self_form', $this->getRouter()->generate('php_orchestra_backoffice_template_area_form',
            array(
                'templateId' => $templateId,
                'areaId' => $mixed->getAreaId(),
            ),
            UrlGeneratorInterface::ABSOLUTE_URL
        ));

        if ($parentAreaId) {
            $facade->addLink('_self_delete', $this->getRouter()->generate('php_orchestra_api_area_delete_in_template_area',
                array(
                    'templateId' => $templateId,
                    'parentAreaId' => $parentAreaId,
                    'areaId' => $mixed->getAreaId()
                ),
                UrlGeneratorInterface::ABSOLUTE_URL
            ));

        } else {
            $facade->addLink('_self_delete', $this->getRouter()->generate('php_orchestra_api_area_delete_in_template',
                array(
                    'templateId' => $templateId,
                    'areaId' => $mixed->getAreaId(),
                ),
                UrlGeneratorInterface::ABSOLUTE_URL
            ));
        }

        return $facade;
    }

    /**
     * @param AreaFacade|FacadeInterface $facade
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
        }

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
