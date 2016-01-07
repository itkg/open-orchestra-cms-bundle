<?php

namespace OpenOrchestra\LogBundle\Tests\Processor;

use OpenOrchestra\BaseBundle\Tests\AbstractTest\AbstractBaseTestCase;
use Phake;
use OpenOrchestra\LogBundle\Processor\LogUserProcessor;

/**
 * Class LogUserProcessorTest
 */
class LogUserProcessorTest extends AbstractBaseTestCase
{
    /**
     * @var LogUserProcessor
     */
    protected $processor;

    protected $siteName = 'site test';
    protected $userName = 'benjamin';
    protected $ip = '192.168.33.10';
    protected $requestStack;
    protected $security;
    protected $request;
    protected $context;
    protected $token;

    /**
     * Set up the test
     */
    public function setUp()
    {
        $this->token = Phake::mock('Symfony\Component\Security\Core\Authentication\Token\TokenInterface');
        Phake::when($this->token)->getUsername()->thenReturn($this->userName);
        $this->security = Phake::mock('Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface');
        Phake::when($this->security)->getToken()->thenReturn($this->token);

        $this->request = Phake::mock('Symfony\Component\HttpFoundation\Request');
        Phake::when($this->request)->getClientIp()->thenReturn($this->ip);
        $this->requestStack = Phake::mock('Symfony\Component\HttpFoundation\RequestStack');

        $this->context = Phake::mock('OpenOrchestra\Backoffice\Context\ContextManager');
        Phake::when($this->context)->getCurrentSiteName()->thenReturn($this->siteName);

        $this->processor = new LogUserProcessor($this->security, $this->requestStack, $this->context);
    }

    /**
     * Test processRecord
     */
    public function testProcessRecord()
    {
        Phake::when($this->requestStack)->getCurrentRequest()->thenReturn($this->request);
        $result = $this->processor->processRecord(array());

        $this->assertSame($result, array('extra' => array(
            'user_ip' => $this->ip,
            'user_name' => $this->userName,
            'site_name' => $this->siteName
        )));
    }

    /**
     * Test processRecord with empty request
     */
    public function testProcessRecordWithEmptyRequest()
    {
        $this->ip = '0.0.0.0';

        $result = $this->processor->processRecord(array());

        $this->assertSame($result, array('extra' => array(
            'user_ip' => $this->ip,
            'site_name' => $this->siteName
        )));
    }
}
