<?php

namespace OpenOrchestra\ApiBundle\Exceptions\HttpException;

use OpenOrchestra\BaseApi\Exceptions\HttpException\ApiException;

/**
 * Class KeywordNotDeletableException
 */
class KeywordNotDeletableException extends ApiException
{
    const DEVELOPER_MESSAGE  = 'open_orchestra_api.keyword.delete.impossible';
    const HUMAN_MESSAGE      = 'open_orchestra_api.keyword.delete.impossible';
    const STATUS_CODE        = '404';
    const ERROR_CODE         = 'x';

    /**
     * Constructor
     */
    public function __construct()
    {
        $developerMessage   = self::DEVELOPER_MESSAGE;
        $humanMessage       = self::HUMAN_MESSAGE;
        $statusCode         = self::STATUS_CODE;
        $errorCode          = self::ERROR_CODE;

        parent::__construct($statusCode, $errorCode, $developerMessage, $humanMessage);
    }
}
