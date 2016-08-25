<?php

namespace OpenOrchestra\Backoffice\Reference\Strategies;

use OpenOrchestra\ModelInterface\Model\ContentTypeInterface;

/**
 * Class KeywordInContentTypeReferenceStrategy
 */
class KeywordInContentTypeReferenceStrategy extends AbstractKeywordReferenceStrategy implements ReferenceStrategyInterface
{
    /**
     * @param mixed $entity
     *
     * @return boolean
     */
    public function support($entity)
    {
        return $entity instanceof ContentTypeInterface;
    }

    /**
     * @param mixed $entity
     */
    public function addReferencesToEntity($entity)
    {
        $keywordIds = $this->extractKeywordsFromContentType($entity);

        foreach ($keywordIds as $keywordId) {
            /** @var KeywordInterface $keyword */
            $keyword = $this->keywordRepository->find($keywordId);
            $keyword->addUseInContentType($entity->getId());
        }
    }

    /**
     * @param mixed $entity
     */
    public function removeReferencesToEntity($entity)
    {
        $contentTypeId = $entity->getId();

        $keywordsUsedInContentType = $this->keywordRepository->findUsedInContentType($contentTypeId);

        foreach ($keywordsUsedInContentType as $keyword) {
            $keyword->removeUseInContentType($contentId);
        }
    }

    /**
     * @param ContentInterface $content
     *
     * @return array
     */
    protected function extractKeywordsFromContentType(ContentTypeInterface $contentType)
    {
        $keywordIds = array();
        $fields = $contentType->getFields();

        foreach ($fields as $field) {
            $fieldOptions = $field->getOptions();
            foreach ($fieldOptions as $option) {
                if ($this->isKeywordsConditionAttribute($option->getValue())) {
                    $keywordIds = array_merge(
                        $keywordIds,
                        $this->extractKeywordIdsFromConditionAttribute($option->getValue())
                    );
                }
            }
        }

        return $keywordIds;
    }
}
