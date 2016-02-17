<?php

namespace OpenOrchestra\Backoffice\Form\Type;

use OpenOrchestra\Backoffice\EventSubscriber\AreaCollectionSubscriber;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Class TemplateType
 */
class TemplateType extends AbstractType
{
    protected $templateClass;
    protected $areaClass;
    protected $translator;

    /**
     * @param string              $templateClass
     * @param string              $areaClass
     * @param TranslatorInterface $translator
     */
    public function __construct($templateClass, $areaClass, TranslatorInterface $translator)
    {
        $this->templateClass = $templateClass;
        $this->areaClass = $areaClass;
        $this->translator = $translator;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', 'text', array(
                'label' => 'open_orchestra_backoffice.form.template.name',
            ))
            ->add('language', 'orchestra_language', array(
                'label' => 'open_orchestra_backoffice.form.template.language',
            ))
            ->add('boDirection', 'orchestra_direction', array(
                'label' => 'open_orchestra_backoffice.form.template.boDirection',
            ))
            ->add('templateId', 'hidden', array(
                'disabled' => true
            ));

        $builder->addEventSubscriber(new AreaCollectionSubscriber($this->areaClass, $this->translator));
        if (array_key_exists('disabled', $options)) {
            $builder->setAttribute('disabled', $options['disabled']);
        }
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => $this->templateClass,
            )
        );
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'oo_template';
    }
}
