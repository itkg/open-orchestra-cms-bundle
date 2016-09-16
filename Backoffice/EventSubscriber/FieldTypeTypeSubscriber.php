<?php

namespace OpenOrchestra\Backoffice\EventSubscriber;

use OpenOrchestra\ModelInterface\Model\FieldOptionInterface;
use OpenOrchestra\ModelInterface\Model\FieldTypeInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;

/**
 * Class FieldTypeTypeSubscriber
 */
class FieldTypeTypeSubscriber implements EventSubscriberInterface
{
    protected $options = array();
    protected $fieldOptionClass;
    protected $fieldTypeClass;
    protected $fieldTypeParameters;

    /**
     * @param array  $options
     * @param string $fieldOptionClass
     * @param string $fieldTypeClass
     * @param array  $fieldTypeParameters
     */
    public function __construct(array $options, $fieldOptionClass, $fieldTypeClass, array $fieldTypeParameters)
    {
        $this->options = $options;
        $this->fieldOptionClass = $fieldOptionClass;
        $this->fieldTypeClass = $fieldTypeClass;
        $this->fieldTypeParameters = $fieldTypeParameters;
   }

    /**
     * @param FormEvent $event
     */
    public function preSetData(FormEvent $event)
    {
        /** @var FieldTypeInterface $data */
        $form = $event->getForm();
        $data = $event->getData();
        if ($data instanceof FieldTypeInterface) {
            $type = $data->getType();

            $this->checkFieldType($data, $type, $form);
            $this->addDefaultValueField($data, $type, $form);
        }
    }

    /**
     * @param FormEvent $event
     */
    public function preSubmit(FormEvent $event)
    {
        /** @var FieldTypeInterface $data */
        $form = $event->getForm();
        $data = $form->getData();

        if (is_null($data)) {
            $data = new $this->fieldTypeClass();
            $event->getForm()->setData($data);
        }

        $dataSend = $event->getData();
        $type = $dataSend['type'];
        $this->checkFieldType($data, $type, $form);
        $this->addDefaultValueField($data, $type, $form);

        foreach ($this->fieldTypeParameters as $fieldTypeParameters) {
            if (array_key_exists('type', $this->options[$type]) &&
                array_key_exists('search', $this->options[$type]) &&
                $fieldTypeParameters['type'] == $data->getType()) {
                $data->setFieldTypeSearchable($fieldTypeParameters['search']);
                break;
            }
        }
    }

    /**
     * @return array The event names to listen to
     */
    public static function getSubscribedEvents()
    {
        return array(
            FormEvents::PRE_SET_DATA => 'preSetData',
            FormEvents::PRE_SUBMIT => 'preSubmit'
        );
    }

    /**
     * @param FieldTypeInterface        $data
     * @param string                    $type
     * @param FormInterface             $form
     */
    protected function addDefaultValueField(FieldTypeInterface $data, $type, FormInterface $form)
    {
        if ($form->has('default_value')) {
            $form->remove('default_value');
        }

        if (is_null($type) || !array_key_exists($type, $this->options)) {
            return;
        }

        if ($data->getType() !== $type) {
            $data->setDefaultValue(null);
        }

        if (isset($this->options[$type]['default_value'])) {
            $defaultValueField = $this->options[$type]['default_value'];
            $defaultOption = (isset($defaultValueField['options'])) ? $defaultValueField['options'] : array();
            $form->add('default_value', $defaultValueField['type'], $defaultOption);
        }
    }

    /**
     * @param FieldTypeInterface $data
     * @param string             $type
     * @param FormInterface      $form
     */
    protected function checkFieldType(FieldTypeInterface $data, $type, FormInterface $form)
    {
        if (is_null($type) || !array_key_exists($type, $this->options)) {
            return;
        }

        if (array_key_exists('options', $this->options[$type])) {
            $keys = array();
            foreach ($this->options[$type]['options'] as $key => $option) {
                if (!$data->hasOption($key)) {
                    $fieldOptionClass = $this->fieldOptionClass;
                    /** @var FieldOptionInterface $fieldOption */
                    $fieldOption = new $fieldOptionClass();
                    $fieldOption->setKey($key);
                    $fieldOption->setValue($option['default_value']);

                    $data->addOption($fieldOption);
                }
                $keys[] = $key;
            }

            foreach ($data->getOptions() as $option) {
                if (!in_array($option->getKey(), $keys)) {
                    $data->removeOption($option);
                }
            }
        } else {
            $data->clearOptions();
        }

        $form->add('options', 'collection', array(
            'type' => 'oo_field_option',
            'allow_add' => false,
            'allow_delete' => false,
            'label' => false,
            'options' => array( 'label' => false ),
        ));
    }
}
