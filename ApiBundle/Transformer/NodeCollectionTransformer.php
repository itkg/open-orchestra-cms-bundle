<?php

namespace OpenOrchestra\ApiBundle\Transformer;

use Doctrine\Common\Collections\ArrayCollection;
use OpenOrchestra\BaseApi\Facade\FacadeInterface;
use OpenOrchestra\BaseApi\Transformer\AbstractTransformer;
use OpenOrchestra\ApiBundle\Facade\NodeCollectionFacade;
use OpenOrchestra\ApiBundle\Facade\NodeFacade;

/**
 * Class NodeCollectionTransformer
 */
class NodeCollectionTransformer extends AbstractTransformer
{
    /**
     * @param ArrayCollection $nodeCollection
     *
     * @return FacadeInterface
     */
    public function transformVersions($nodeCollection)
    {
        $facade = new NodeCollectionFacade();

        foreach ($nodeCollection as $node) {
            $facade->addNode($this->getTransformer('node')->transformVersion($node));
        }

        return $facade;
    }

    /**
     * @param NodeCollectionFacade $nodeCollection
     *
     * @return array
     */
    public function reverseTransformOrder($nodeCollection)
    {
        $orderedNode = array();
        /** @var NodeFacade $node */
        foreach ($nodeCollection->getNodes() as $node) {
            $orderedNode[] = $node->nodeId;
        }

        return $orderedNode;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'node_collection';
    }
}
