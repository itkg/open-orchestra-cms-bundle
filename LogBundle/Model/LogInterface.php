<?php

namespace OpenOrchestra\LogBundle\Model;

/**
 * Class LogInterface
 */
interface LogInterface
{
    const ENTITY_TYPE = 'log';

    /**
     * @return string
     */
    public function getId();

    /**
     * @return string
     */
    public function getMessage();

    /**
     * @return array
     */
    public function getContext();

    /**
     * @return int
     */
    public function getLevel();

    /**
     * @return string
     */
    public function getLevelName();

    /**
     * @return string
     */
    public function getChannel();

    /**
     * @return string
     */
    public function getDateTime();

    /**
     * @return array
     */
    public function getExtra();
}
