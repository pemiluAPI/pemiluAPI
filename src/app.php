<?php

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

// Read configuration
$config = json_decode(file_get_contents(__DIR__.'/../pemiluapi.json'), true);

// Load available endpoints
$endpoints = json_decode(file_get_contents(__DIR__.'/../endpoints.json'), true);

// Define endpoint routes
foreach ($endpoints['endpoints'] as $slug => $attributes):

    $app->match('/'.$slug.'/api/{resource}', function (Request $request, $resource) use ($app, $attributes, $config) {

        // Try authenticate apiKey
        $client = new Client($config['host'], array(
            'request.options' => array(
                'query' => array('apiKey' => $request->get('apiKey')),
                'exceptions' => false
            )
        ));

        // Authenticate
        $response = $client->get('/api/authenticate')->send();

        // Failed? Immediately return 401
        if ($response->getStatusCode() == 401) {
            $output = array(
                'error' => array(
                    'type' => 'invalid_request_error'
                )
            );

            return $app->json($output, 401);
        }

        // Set the API server base
        $client = new Client($attributes['base'], array(
            'request.options' => array(
                'exceptions' => false
            )
        ));

        // Call the endpoint
        $response = $client->createRequest($request->getMethod(), '/api/' . $resource)->send();

        // Prepare output
        switch ($response->getStatusCode()) {
            case 404:
                $output = array(
                    'error' => array(
                        'type' => 'data_not_found'
                    )
                );
                break;
            default:
                $output = array(
                    'data' => $response->json()
                );
                break;
         };

        return $app->json($output, $response->getStatusCode());

    })->method('GET|POST');// Only match GET or POST

endforeach; // eo Define endpoint routes

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

            return $app->json($output, $statusCode);
            break;
     };
});

$app->mount('/status', include 'status.php');

return $app;
