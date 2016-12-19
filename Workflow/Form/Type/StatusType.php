<?php

namespace OpenOrchestra\Workflow\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class StatusType
 */
class StatusType extends AbstractType
{
    protected $statusClass;
    protected $backOfficeLanguages;

    /**
     * @param string $statusClass
     * @param array  $backOfficeLanguages
     */
    public function __construct($statusClass, array $backOfficeLanguages)
    {
        $this->backOfficeLanguages = $backOfficeLanguages;
        $this->statusClass = $statusClass;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', 'text', array(
                'label' => 'open_orchestra_workflow_admin.form.status.name',
                'group_id' => 'properties',
            ))
            ->add('labels', 'oo_multi_languages', array(
                'label' => 'open_orchestra_workflow_admin.form.status.labels',
                'languages' => $this->backOfficeLanguages,
                'group_id' => 'properties',
            ))
            ->add('displayColor', 'orchestra_color_choice', array(
                'label' => 'open_orchestra_workflow_admin.form.status.display_color',
                'group_id' => 'properties',
            ))
            ->add('blockedEdition', 'checkbox', array(
                'label' => 'open_orchestra_workflow_admin.form.status.blocked_edition.label',
                'required' => false,
                'attr' => array('help_text' => 'open_orchestra_workflow_admin.form.status.blocked_edition.helper'),
                'group_id' => 'properties',
            ))
            ->add('outOfWorkflow', 'checkbox', array(
                'label' => 'open_orchestra_workflow_admin.form.status.out_of_workflow.label',
                'required' => false,
                'attr' => array('help_text' => 'open_orchestra_workflow_admin.form.status.out_of_workflow.helper'),
                'group_id' => 'properties',
            ));

        if (array_key_exists('disabled', $options)) {
            $builder->setAttribute('disabled', $options['disabled']);
        }
    }

    /**
     * Returns the name of this type.
     *
     * @return string The name of this type
     */
    public function getName()
    {
        return 'oo_status';
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => $this->statusClass,
            'group_enabled' => true,
            'group_render' => array(
                'properties' => array(
                    'rank' => 0,
                    'label' => 'open_orchestra_workflow_admin.form.status.group.properties',
                ),
            ),
        ));
    }

}
