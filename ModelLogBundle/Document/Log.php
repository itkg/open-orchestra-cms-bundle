<?php

namespace OpenOrchestra\ModelLogBundle\Document;

use OpenOrchestra\LogBundle\Model\LogInterface;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

/**
 * Class Log
 *
 * @ODM\Document(
 *   collection="log",
 *   repositoryClass="OpenOrchestra\ModelLogBundle\Repository\LogRepository"
 * )
 */
class Log implements LogInterface
{
    /**
     * @var string $id
     *
     * @ODM\Id
     */
    protected $id;

    /**
     * @var string $message
     *
     * @ODM\Field(type="string")
     */
    protected $message;

    /**
     * @var array $context
     *
     * @ODM\Field(type="collection")
     */
    protected $context = array();

    /**
     * @var int $level
     *
     * @ODM\Field(type="int")
     */
    protected $level;

    /**
     * @var string $levelName
     *
     * @ODM\Field(type="string")
     */
    protected $levelName;

    /**
     * @var string $channel
     *
     * @ODM\Field(type="string")
     */
    protected $channel;

    /**
     * @var string $datetime
     *
     * @ODM\Field(type="string")
     */
    protected $datetime;

    /**
     * @var array $extra
     *
     * @ODM\Field(type="collection")
     */
    protected $extra;

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @return array
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * @return int
     */
    public function getLevel()
    {
        return $this->level;
    }

    /**
     * @return string
     */
    public function getLevelName()
    {
        return $this->levelName;
    }

    /**
     * @return string
     */
    public function getChannel()
    {
        return $this->channel;
    }

    /**
     * @return string
     */
    public function getDateTime()
    {
        return $this->datetime;
    }

    /**
     * @return array
     */
    public function getExtra()
    {
        return $this->extra;
    }
}
