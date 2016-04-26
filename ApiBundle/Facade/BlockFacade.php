<?php

namespace OpenOrchestra\ApiBundle\Facade;

use JMS\Serializer\Annotation as Serializer;
use OpenOrchestra\BaseApi\Facade\AbstractFacade;

/**
 * Class BlockFacade
 */
class BlockFacade extends AbstractFacade
{
    const GENERATE = 'generate';
    const LOAD = 'load';

    /**
     * @Serializer\Type("string")
     */
    public $method;

    /**
     * @Serializer\Type("string")
     */
    public $component;

    /**
     * @Serializer\Type("string")
     */
    public $label;

    /**
     * @Serializer\Type("string")
     */
    public $class;

    /**
     * @Serializer\Type("string")
     */
    public $id;

    /**
     * @Serializer\Type("OpenOrchestra\ApiBundle\Facade\UiModelFacade")
     */
    public $uiModel;

    /**
     * @Serializer\Type("string")
     */
    public $nodeId;

    /**
     * @Serializer\Type("integer")
     */
    public $blockId;

    /**
     * @Serializer\Type("boolean")
     */
    public $isDeletable = true;

    /**
     * @Serializer\Type("array<string,string>")
     */
    protected $attributes = array();

    /**
     * @param string $key
     * @param string $value
     */
    public function addAttribute($key, $value)
    {
        $this->attributes[$key] = $value;
    }

    /**
     * @return array
     */
    public function getAttributes()
    {
        return $this->attributes;
    }
}
