<?php

use Silex\WebTestCase;

class RootTest extends WebTestCase
{
    public function createApplication()
    {
        $app = require __DIR__ . '/../src/app.php';
        $app['debug'] = true;
        $app['exception_handler']->disable();

        return $app;
    }

    public function testRoot()
    {
        $client = $this->createClient();
        $client->request('GET', '/');
        $response = $client->getResponse();
        $content = json_decode($response->getContent());

        $this->assertEquals($response->getStatusCode(), 404);
        $this->assertEquals($content->error->type, 'invalid_request_error');
    }
}