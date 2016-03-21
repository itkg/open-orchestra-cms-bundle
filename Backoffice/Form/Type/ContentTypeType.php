<?php

namespace OpenOrchestra\Backoffice\Form\Type;

use OpenOrchestra\Backoffice\EventListener\TranslateValueInitializerListener;
use OpenOrchestra\Backoffice\EventSubscriber\ContentTypeTypeSubscriber;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Class ContentTypeType
 */
class ContentTypeType extends AbstractType
{
    protected $translateValueInitializer;
    protected $contentTypeClass;
    protected $translator;

    /**
     * @param string                            $contentTypeClass
     * @param TranslatorInterface               $translator
     * @param TranslateValueInitializerListener $translateValueInitializer
     */
    public function __construct(
        $contentTypeClass,
        TranslatorInterface $translator,
        TranslateValueInitializerListener $translateValueInitializer
    )
    {
        $this->translateValueInitializer = $translateValueInitializer;
        $this->contentTypeClass = $contentTypeClass;
        $this->translator = $translator;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $contentTypeIdName = "contentTypeId";

        $builder->addEventListener(FormEvents::PRE_SET_DATA, array($this->translateValueInitializer, 'preSetData'));
        $builder
            ->add($contentTypeIdName, 'text', array(
                'label' => 'open_orchestra_backoffice.form.content_type.content_type_id',
                'attr' => array(
                    'class' => 'generate-id-dest',
                )
            ))
            ->add('names', 'oo_translated_value_collection', array(
                'label' => 'open_orchestra_backoffice.form.content_type.names'
            ))
            ->add('template', 'text', array(
                'label' => 'open_orchestra_backoffice.form.content_type.template.label',
                'required' => false,
                'attr' => array('help_text' => 'open_orchestra_backoffice.form.content_type.template.helper'),
            ))
            ->add('linkedToSite', 'checkbox', array(
                'label' => 'open_orchestra_backoffice.form.content_type.linked_to_site',
                'required' => false,
            ))
            ->add('defaultListable', 'collection', array(
                'required' => false,
                'type' => 'oo_default_listable_checkbox',
                'label' => 'open_orchestra_backoffice.form.content_type.default_display',
            ))
            ->add('fields', 'collection', array(
                'type' => 'oo_field_type',
                'allow_add' => true,
                'allow_delete' => true,
                'label' => 'open_orchestra_backoffice.form.content_type.fields',
                'attr' => array(
                    'data-prototype-label-add' => $this->translator->trans('open_orchestra_backoffice.form.field_type.add'),
                    'data-prototype-label-new' => $this->translator->trans('open_orchestra_backoffice.form.field_type.new'),
                    'data-prototype-label-remove' => $this->translator->trans('open_orchestra_backoffice.form.field_type.delete'),
                ),
                'options' => array( 'label' => false ),
            ));

        $builder->addEventSubscriber(new ContentTypeTypeSubscriber());
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
                'data_class' => $this->contentTypeClass,
            )
        );
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'oo_content_type';
    }
}
