<?php

namespace OpenOrchestra\BackofficeBundle\Form\Type;

use OpenOrchestra\BaseBundle\Context\CurrentSiteIdInterface;
use OpenOrchestra\ModelInterface\Repository\NodeRepositoryInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use OpenOrchestra\DisplayBundle\Manager\TreeManager;

/**
 * Class OrchestraNodeType
 */
class OrchestraNodeChoiceType extends AbstractType
{
    protected $nodeRepository;
    protected $treeManager;
    protected $currentSiteManager;

    /**
     * @param NodeRepositoryInterface $nodeRepository
     * @param TreeManager $treeManager
     * @param CurrentSiteIdInterface $currentSiteManager
     */
    public function __construct(NodeRepositoryInterface $nodeRepository, TreeManager $treeManager, CurrentSiteIdInterface $currentSiteManager)
    {
        $this->nodeRepository = $nodeRepository;
        $this->treeManager = $treeManager;
        $this->currentSiteManager = $currentSiteManager;
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'choices' => $this->getChoices(),
                'attr' => array(
                    'class' => 'orchestra-node-choice'
                )
            )
        );
    }

    /**
     * @return array
     */
    protected function getChoices()
    {
        $siteId = $this->currentSiteManager->getCurrentSiteId();
        $nodes = $this->nodeRepository->findLastVersionBySiteId($siteId);
        $orderedNodes = $this->treeManager->generateTree($nodes);

        return $this->getHierarchicalChoices($orderedNodes);
    }

    /**
     * @return array
     */
    protected function getHierarchicalChoices($nodes, $depth = 0)
    {
        $choices = array();
        foreach ($nodes as $node) {
            $pre = '';
            if ($depth > 0) {
                $pre = str_repeat('&#x2502;', $depth - 1).'&#x251C;';
            }
            $choices[$node['node']->getNodeId()] = $pre.$node['node']->getName();

            if (array_key_exists('child', $node)) {
                $choices = array_merge($choices, $this->getHierarchicalChoices($node['child'], $depth + 1));
            }
        }
        if (isset($node)) {
            $pre = '';
            if ($depth > 0) {
                $pre = str_repeat('&#x2502;', $depth - 1).'&#x2514;';
            }
            $choices[$node['node']->getNodeId()] = $pre.$node['node']->getName();
        }

        return $choices;

    }
    /**
     * Returns the name of this type.
     *
     * @return string The name of this type
     */
    public function getName()
    {
        return 'orchestra_node_choice';
    }

    /**
     * @return null|string|\Symfony\Component\Form\FormTypeInterface
     */
    public function getParent()
    {
        return 'choice';
    }
}
