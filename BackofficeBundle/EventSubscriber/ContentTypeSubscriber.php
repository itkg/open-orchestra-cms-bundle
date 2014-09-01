<?php

namespace PHPOrchestra\BackofficeBundle\EventSubscriber;

use PHPOrchestra\ModelBundle\Model\FieldTypeInterface;
use PHPOrchestra\ModelBundle\Repository\ContentTypeRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

/**
 * Class ContentTypeSubscriber
 */
class ContentTypeSubscriber implements EventSubscriberInterface
{
    protected $contentTypeRepository;
    protected $contentAttributClass;

    /**
     * @param ContentTypeRepository $contentTypeRepository
     * @param string                $contentAttributClass
     */
    public function __construct(ContentTypeRepository $contentTypeRepository, $contentAttributClass)
    {
        $this->contentTypeRepository = $contentTypeRepository;
        $this->contentAttributClass = $contentAttributClass;
    }

    /**
     * @param FormEvent $event
     */
    public function preSetData(FormEvent $event)
    {
        $form = $event->getForm();
        $data = $event->getData();
        $contentType = $this->contentTypeRepository->findOneByContentTypeId($data->getContentType());

        if (is_object($contentType)) {
            /** @var FieldTypeInterface $field */
            foreach ($contentType->getFields() as $field) {
                $attribute = $data->getAttributeByName($field->getFieldId());
                if ($attribute) {
                    $defaultValue = $attribute->getValue();
                } else {
                    $defaultValue = $field->getDefaultValue();
                }
                $form->add($field->getFieldId(), $field->getType(), array_merge(
                    array(
                        'data' => $defaultValue,
                        'label' => $field->getLabel(),
                        'mapped' => false,
                    ),
                    $field->getFormOptions()
                ));
            }
        }
    }

    /**
     * @param FormEvent $event
     */
    public function preSubmit(FormEvent $event)
    {
        $form = $event->getForm();
        $content = $form->getData();
        $data = $event->getData();
        $contentType = $this->contentTypeRepository->find($data['contentType']);

        foreach ($contentType->getFields() as $field) {
            if ($attribute = $content->getAttributeByName($field->getFieldId())) {
                $attribute->setValue($data[$field->getFieldId()]);
            } else {
                $contentAttributClass = $this->contentAttributClass;
                $attribute = new $contentAttributClass;
                $attribute->setName($field->getFieldId());
                $attribute->setValue($data[$field->getFieldId()]);
                $content->addAttribute($attribute);
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
}
