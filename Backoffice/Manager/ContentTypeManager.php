<?php

namespace OpenOrchestra\Backoffice\Manager;

use OpenOrchestra\ModelInterface\Model\ContentTypeInterface;

/**
 * Class ContentTypeManager
 */
class ContentTypeManager
{
    protected $contentTypeClass;

    /**
     * @param string $contentTypeClass
     */
    public function __construct($contentTypeClass)
    {
        $this->contentTypeClass = $contentTypeClass;
    }

    /**
     * @return ContentTypeInterface
     */
    public function initializeNewContentType()
    {
        $contentTypeClass = $this->contentTypeClass;
        /** @var ContentTypeInterface $contentType */
        $contentType = new $contentTypeClass();
        $contentType->setDefaultListable($this->getDefaultListableColumns());

        return $contentType;
    }

    /**
     * @param ContentTypeInterface $contentType
     *
     * @return ContentTypeInterface
     */
    public function duplicate(ContentTypeInterface $contentType)
    {
        $newContentType = clone $contentType;

        foreach ($contentType->getFields() as $field) {
            $newField = clone $field;
            foreach ($field->getOptions() as $option) {
                $newOption = clone $option;
                $newField->addOption($newOption);
            }

            $newContentType->addFieldType($newField);
        }

        return $newContentType;
    }

    /**
     * @param array $contentTypes
     */
    public function delete($contentTypes)
    {
        if (!empty($contentTypes)) {
            foreach ($contentTypes as $contentType)
            {
                $contentType->setDeleted(true);
            }
        }
    }

    /**
     * @return array
     */
    protected function getDefaultListableColumns()
    {
        return array(
            'name'           => true,
            'status_label'   => false,
            'version'        => false,
            'language'       => false,
            'linked_to_site' => true,
            'created_at'     => true,
            'created_by'     => true,
            'updated_at'     => false,
            'updated_by'     => false,
        );
    }
}
