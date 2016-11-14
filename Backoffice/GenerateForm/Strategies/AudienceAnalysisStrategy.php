<?php

namespace OpenOrchestra\Backoffice\GenerateForm\Strategies;

use OpenOrchestra\DisplayBundle\DisplayBlock\Strategies\AudienceAnalysisStrategy as BaseAudienceAnalysisStrategy;
use OpenOrchestra\ModelInterface\Model\BlockInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Class AudienceAnalysisStrategy
 */
class AudienceAnalysisStrategy extends AbstractBlockStrategy
{
    /**
     * @param BlockInterface $block
     *
     * @return bool
     */
    public function support(BlockInterface $block)
    {
        return BaseAudienceAnalysisStrategy::NAME === $block->getComponent();
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('tag_type', 'choice', array(
                'choices' => $this->getChoices(),
                'label' => 'open_orchestra_backoffice.block.audience_analysis.tag_type',
                'constraints' => new NotBlank(),
                'group_id' => 'data',
                'sub_group_id' => 'content',
            ))
            ->add('site_id', 'text', array(
                'label' => 'open_orchestra_backoffice.block.audience_analysis.site_id',
                'constraints' => new NotBlank(),
                'group_id' => 'data',
                'sub_group_id' => 'content',
            ))
        ;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'audience_analysis';
    }

    /**
     * @return array
     */
    protected function getChoices()
    {
        return array(
            'google_analytics' => 'open_orchestra_backoffice.block.audience_analysis.google_analytics',
            'xiti_free' => 'open_orchestra_backoffice.block.audience_analysis.xiti_free'
        );
    }
}
