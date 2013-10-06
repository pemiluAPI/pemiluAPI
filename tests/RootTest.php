<?php

use Silex\WebTestCase;

class RootRouteTest extends WebTestCase
{

    public function createApplication()
    {
        $app = require __DIR__ . '/../src/app.php';
        $app['debug'] = true;
        $app['exception_handler']->disable();

        return $app;
    }

    public function testRootRoute()
    {
        $client = $this->createClient();
        $client->request('GET', '/');
        $response = $client->getResponse();
        $content = json_decode($response->getContent());

        $this->assertTrue($response->isNotFound());
        $this->assertEquals($content->error->type, 'invalid_request_error');
    }

}