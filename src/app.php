<?php

use Guzzle\Http\Client;
use Symfony\Component\HttpFoundation\Request;

$app = new Silex\Application();

$app->get('/', function () use ($app) {
    $output = array(
        'error' => array(
            'message' => 'Unrecognized request URL (GET: /).  Please see http://docs.pemiluapi.org/.',
            'type' => 'invalid_request_error'
        )
    );

    return $app->json($output, 404);
});

$app->get('/endpoints', function (Request $request) use ($app) {
    // Read configuration
    $config = json_decode(file_get_contents(__DIR__.'/../pemiluapi.json'), true);

    // Try authenticate apiKey
    $client = new Client($config['host'], array(
        'request.options' => array(
            'query' => array('apiKey' => $request->get('apiKey')),
            'exceptions' => false
        )
    ));
    $response = $client->get('/api/authenticate')->send();

    // Return based on status code
    switch ($response->getStatusCode()) {
        case 401:
            $output = array(
                'error' => array(
                    'type' => 'invalid_request_error'
                )
            );

            return $app->json($output, 401);
            break;

        case 200:
            $endpoints = json_decode(file_get_contents(__DIR__.'/../endpoints.json'), true);
            $endpoints = $endpoints['endpoints'];

            $output = array(
                'count' => count($endpoints),
                'data' => $endpoints
            );

            return $app->json($output);
            break;
     };

});

$app->get('/endpoints/{slug}', function (Request $request, $slug) use ($app) {
    // Read configuration
    $config = json_decode(file_get_contents(__DIR__.'/../pemiluapi.json'), true);

    // Try authenticate apiKey
    $client = new Client($config['host'], array(
        'request.options' => array(
            'query' => array('apiKey' => $request->get('apiKey')),
            'exceptions' => false
        )
    ));
    $response = $client->get('/api/authenticate')->send();

    // Return based on status code
    switch ($response->getStatusCode()) {
        case 401:
            $output = array(
                'error' => array(
                    'type' => 'invalid_request_error'
                )
            );

            return $app->json($output, 401);
            break;

        case 200:
            $endpoints = json_decode(file_get_contents(__DIR__.'/../endpoints.json'), true);
            $endpoint = array_filter($endpoints['endpoints'], function($endpoint) use ($slug) {
                return $endpoint['slug'] == $slug;
            });

            $output = array('data' => $endpoint);

            return $app->json($output);
            break;
     };
});

$app->get('/status', function (Request $request) use ($app) {
    // Read configuration
    $config = json_decode(file_get_contents(__DIR__.'/../pemiluapi.json'), true);

    // Try authenticate apiKey
    $client = new Client($config['host'], array(
        'request.options' => array(
            'query' => array('apiKey' => $request->get('apiKey')),
            'exceptions' => false
        )
    ));
    $response = $client->get('/api/authenticate')->send();

    // Return based on status code
    switch ($response->getStatusCode()) {
        case 401:
            $output = array(
                'error' => array(
                    'type' => 'invalid_request_error'
                )
            );

            return $app->json($output, 401);
            break;

        case 200:
            $client = new Client($config['host'], array(
                'request.options' => array(
                    'query' => array('apiKey' => $request->get('apiKey')),
                    'exceptions' => false
                )
            ));

            $response = $client->get('/api/application')->send()->json();

            $output = array(
                'data' => array(
                    'pemiluAPIVersion' => 1,
                    'applicationName' => $response['data']['title']
                )
            );

            return $app->json($output, 200);
            break;
     } ;
});

return $app;
