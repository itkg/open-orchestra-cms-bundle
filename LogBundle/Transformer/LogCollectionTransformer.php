<?php

namespace OpenOrchestra\LogBundle\Transformer;

use Doctrine\Common\Collections\ArrayCollection;
use OpenOrchestra\BaseApi\Facade\FacadeInterface;
use OpenOrchestra\BaseApi\Transformer\AbstractTransformer;

/**
 * Class LogCollectionTransformer
 */
class LogCollectionTransformer extends AbstractTransformer
{
    /**
     * @param ArrayCollection $mixed
     *
     * @return FacadeInterface
     */
    public function transform($mixed)
    {
        $facade = $this->newFacade();

        foreach ($mixed as $log) {
            $facade->addLog($this->getContext()->transform('log', $log));
        }

        return $facade;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'log_collection';
    }
}
