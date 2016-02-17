<?php

namespace OpenOrchestra\Backoffice\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class OrchestraChoiceType
 */
class OrchestraChoiceType extends AbstractType
{
    protected $choices;
    protected $name;

    /**
     * @param array  $choices
     * @param string $name
     */
    public function __construct(array $choices, $name)
    {
        $this->choices = $choices;
        $this->name = $name;
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            array(
                'choices' => $this->choices,
            )
        );
    }

    /**
     * @return string
     */
    public function getParent()
    {
        return 'choice';
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }
}
