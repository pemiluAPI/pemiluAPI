<?php

use Silex\WebTestCase;

class StatusTest extends WebTestCase
{
    public function createApplication()
    {
        $app = require __DIR__ . '/../src/app.php';
        $app['debug'] = true;
        $app['exception_handler']->disable();

        return $app;
    }

    public function testRetrieveAPIStatus()
    {
        $client = $this->createClient();
        $client->followRedirects();
        $client->request('GET', 'status', array('apiKey' => '06ec082d057daa3d310b27483cc3962e'));
        $response = $client->getResponse();
        $content = json_decode($response->getContent());

        $this->assertEquals($response->getStatusCode(), 200);
        $this->assertEquals($content->data->pemiluAPIVersion, 1);
        $this->assertEquals($content->data->applicationName, 'PemiluAPI Developer');
    }

    public function testInvalidApiKey()
    {
        $client = $this->createClient();
        $client->followRedirects();
        $client->request('GET', 'status', array('apiKey' => 'insertinvalidkeyhere'));
        $response = $client->getResponse();
        $content = json_decode($response->getContent());

        $this->assertEquals($response->getStatusCode(), 401);
        $this->assertEquals($content->error->type, 'invalid_request_error');
    }
}