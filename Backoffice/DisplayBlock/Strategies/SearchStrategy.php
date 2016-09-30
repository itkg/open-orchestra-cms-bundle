<?php

namespace OpenOrchestra\Backoffice\DisplayBlock\Strategies;

use OpenOrchestra\DisplayBundle\DisplayBlock\Strategies\AbstractStrategy;
use OpenOrchestra\ModelInterface\Model\ReadBlockInterface;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class SearchStrategy
 *
 * @deprecated will be removed in 2.0
 */
class SearchStrategy extends AbstractStrategy
{
    const SEARCH = 'search';

    /**
     * Check if the strategy support this block
     *
     * @param ReadBlockInterface $block
     *
     * @return boolean
     */
    public function support(ReadBlockInterface $block)
    {
        return self::SEARCH == $block->getComponent();
    }

    /**
     * Perform the show action for a block
     *
     * @param ReadBlockInterface $block
     *
     * @return Response
     */
    public function show(ReadBlockInterface $block)
    {
        $value = $block->getAttribute('value');
        $limit = $block->getAttribute('limit');

        return $this->render(
            'OpenOrchestraBackofficeBundle:Block/Search:show.html.twig',
            array(
                'value' => $value,
                'limit' => $limit
            )
        );
    }

    /**
     * @param ReadBlockInterface $block
     *
     * @return array
     */
    public function getCacheTags(ReadBlockInterface $block)
    {
        return array();
    }

    /**
     * Get the name of the strategy
     *
     * @return string
     */
    public function getName()
    {
        return 'search';
    }
}
