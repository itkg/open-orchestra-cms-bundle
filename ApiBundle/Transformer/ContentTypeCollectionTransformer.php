<?php

namespace OpenOrchestra\ApiBundle\Transformer;

use Doctrine\Common\Collections\Collection;
use OpenOrchestra\ApiBundle\Facade\ContentTypeCollectionFacade;
use OpenOrchestra\BaseApi\Facade\FacadeInterface;
use OpenOrchestra\BaseApi\Transformer\AbstractTransformer;

/**
 * Class ContentTypeCollectionTransformer
 */
class ContentTypeCollectionTransformer extends AbstractTransformer
{
    /**
     * @param Collection $contentTypeCollection
     *
     * @return FacadeInterface
     */
    public function transform($contentTypeCollection)
    {
        $facade = new ContentTypeCollectionFacade();

        foreach ($contentTypeCollection as $contentType) {
            $facade->addContentType($this->getTransformer('content_type')->transform($contentType));
        }

        $facade->addLink('_self_add', $this->generateRoute(
            'open_orchestra_backoffice_content_type_new',
            array()
        ));

        return $facade;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'content_type_collection';
    }
}
