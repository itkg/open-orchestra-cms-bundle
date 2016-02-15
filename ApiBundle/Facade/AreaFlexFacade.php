<?php

namespace OpenOrchestra\ApiBundle\Facade;

use JMS\Serializer\Annotation as Serializer;
use OpenOrchestra\BaseApi\Facade\AbstractFacade;
use OpenOrchestra\BaseApi\Facade\FacadeInterface;

/**
 * Class AreaFlexFacade
 */
class AreaFlexFacade extends AbstractFacade
{
    /**
     * @Serializer\Type("string")
     */
    public $label;

    /**
     * @Serializer\Type("string")
     */
    public $areaId;

    /**
     * @Serializer\Type("string")
     */
    public $areaType;

    /**
     * @Serializer\Type("string")
     */
    public $width;

    /**
     * @Serializer\Type("array<OpenOrchestra\ApiBundle\Facade\AreaFlexFacade>")
     */
    protected $areas = array();

    /**
     * @param FacadeInterface $facade
     */
    public function addArea(FacadeInterface $facade)
    {
        $this->areas[] = $facade;
    }
}
