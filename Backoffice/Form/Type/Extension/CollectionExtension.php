<?php

namespace OpenOrchestra\Backoffice\Form\Type\Extension;

use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use OpenOrchestra\Backoffice\EventSubscriber\CollectionSubscriber;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;

/**
 * Class CollectionExtension
 */
class CollectionExtension extends AbstractTypeExtension
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if ($options['sortable']) {
            $builder->addEventSubscriber(new CollectionSubscriber());
        }
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'sortable' => false,
        ));
    }
    /**
     * @param FormView      $view
     * @param FormInterface $form
     * @param array         $options
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        if ($options['sortable']) {
            $view->vars['attr'] = array_merge_recursive($view->vars['attr'], array('class' => 'collection-sortable'));
        }
    }

    /**
     * Returns the name of extended type.
     *
     * @return string The name of extended type
     */
    public function getExtendedType()
    {
        return 'collection';
    }
}
