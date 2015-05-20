<?php

namespace OpenOrchestra\UserAdminBundle\Tests\Functional\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Bundle\FrameworkBundle\Client;

/**
 * Class AuthorizationControllersTest
 */
class AuthorizationControllersTest extends WebTestCase
{
    /**
     * @var Client
     */
    protected $client;

    /**
     * Set up the test
     */
    public function setUp()
    {
        $this->client = static::createClient();
    }

    /**
     * Test token creation and usage
     */
    public function testTokenCreation()
    {
        $this->client->request('GET', '/oauth/access_token?grant_type=password&username=admin&password=admin', array(), array(), array('PHP_AUTH_USER' => 'test_key', 'PHP_AUTH_PW' => 'test_secret'));
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertSame('application/json', $this->client->getResponse()->headers->get('content-type'));
    }
}
