<?php

namespace OpenOrchestra\Backoffice\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use OpenOrchestra\ModelInterface\Model\SchemeableInterface;

/**
 * Class SiteAliasType
 */
class SiteAliasType extends AbstractType
{
    protected $siteAliasClass;
    protected $schemeChoices;

    /**
     * @param string $siteAliasClass
     */
    public function __construct($siteAliasClass)
    {
        $this->siteAliasClass = $siteAliasClass;
        $this->schemeChoices = array(
            SchemeableInterface::SCHEME_HTTP => 'open_orchestra_backoffice.scheme.http',
            SchemeableInterface::SCHEME_HTTPS => 'open_orchestra_backoffice.scheme.https'
        );
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('scheme', 'choice', array(
                'choices' => $this->schemeChoices,
                'label' => 'open_orchestra_backoffice.form.website.scheme'
            ))
            ->add('domain', 'text', array(
                'label' => 'open_orchestra_backoffice.form.website.domain'
            ))
            ->add('language', 'orchestra_language', array(
                'label' => 'open_orchestra_backoffice.form.website.language'
            ))
            ->add('prefix', 'text', array(
                'label' => 'open_orchestra_backoffice.form.website.prefix',
                'required' => false,
            ))
            ->add('main', 'checkbox', array(
                'label' => 'open_orchestra_backoffice.form.website.main',
                'required' => false
            ));
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => $this->siteAliasClass,
            )
        );
    }

    /**
     * Returns the name of this type.
     *
     * @return string The name of this type
     */
    public function getName()
    {
        return 'oo_site_alias';
    }

}
