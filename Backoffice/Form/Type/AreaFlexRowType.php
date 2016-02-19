<?php

namespace OpenOrchestra\Backoffice\Form\Type;

use OpenOrchestra\Backoffice\EventSubscriber\AreaFlexRowSubscriber;
use OpenOrchestra\Backoffice\Manager\AreaFlexManager;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Class AreaFlexType
 */
class AreaFlexRowType extends AbstractAreaFlexType
{
    protected $areaFlexManager;

    /**
     * @param string              $areaClass
     * @param TranslatorInterface $translator
     * @param AreaFlexManager     $areaFlexManager
     */
    public function __construct($areaClass, TranslatorInterface $translator, AreaFlexManager $areaFlexManager)
    {
        parent::__construct($areaClass, $translator);
        $this->areaFlexManager = $areaFlexManager;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder->addEventSubscriber(new AreaFlexRowSubscriber($this->areaFlexManager));
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);
        $resolver->setDefault(
            'attr',
            array('data-title' => $this->translator->trans('open_orchestra_backoffice.form.area_flex.new_row_title'))
        );
    }
}
