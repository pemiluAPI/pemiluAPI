<?php

use Guzzle\Http\Client;
use Symfony\Component\HttpFoundation\Request;

$endpointsController = $app['controllers_factory'];

$endpointsController->get('/', function (Request $request) use ($app, $config, $endpoints) {
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
            $header = getContentHeader($output);
            return $app->json($output, 401, $header);
            break;

        case 200:
            $endpoints = $endpoints['endpoints'];

            $output = array(
                'count' => count($endpoints),
                'data' => $endpoints
            );
            
            $header = getContentHeader($output);
            return $app->json($output, 200, $header);
            break;
     };

});

$endpointsController->get('/{slug}', function (Request $request, $slug) use ($app, $config, $endpoints) {
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
            $header = getContentHeader($output);
            return $app->json($output, 401, $header );
            break;

        case 200:
            $endpoint = $endpoints['endpoints'][$slug];

            if (empty($endpoint)) {
                $statusCode = 404;
                $output = array(
                    'error' => array(
                        'type' => 'data_not_found'
                    )
                );
            } else {
                $statusCode = 200;
                $output = array('data' => $endpoint);
            }
            $header = getContentHeader($output);
            return $app->json($output, $statusCode, $header);
            break;
     };
});

return $endpointsController;
