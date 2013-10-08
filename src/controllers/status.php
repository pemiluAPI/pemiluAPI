<?php

use Guzzle\Http\Client;
use Symfony\Component\HttpFoundation\Request;

$status = $app['controllers_factory'];

$status->get('/', function (Request $request) use ($app, $config) {
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

return $status;

