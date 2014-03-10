<?php

use Guzzle\Http\Client;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Debug\ExceptionHandler;

// Convert errors to exceptions
// http://silex.sensiolabs.org/doc/cookbook/error_handler.html
ExceptionHandler::register(false);

$app = new Silex\Application();

$app->get('/', function () use ($app) {
    $output = array(
        'error' => array(
            'message' => 'Unrecognized request URL (GET: /).  Please see http://developer.pemiluapi.org/.',
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

    $app->match('/'.$slug.'/api/{resource}/{id}', function (Request $request, $resource, $id) use ($app, $attributes, $config) {
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

        // Dispatch all query string
        parse_str($request->getQueryString(), $queryString);

        // Set the API server base
        $client = new Client($attributes['base'], array(
            'request.options' => array(
                'exceptions' => false,
                'query' => $queryString
            )
        ));

        // Call the endpoint
        $client = $client->createRequest(
            $request->getMethod(), // method
            $attributes['base'] . '/api/' . $resource . '/' . $id, // uri
            array('Content-Type' => 'application/x-www-form-urlencoded'), // headers
            $request->getContent() // body
        );

        try {
            $response = $client->send();
            $statusCode = $response->getStatusCode();
        } catch (Guzzle\Http\Exception\CurlException $e) {
            $statusCode = 500;
            $output = array(
                'error' => array(
                    'type' => 'connection_timed_out'
                )
            );
        }

        // Prepare output
        switch ($statusCode) {
            case 500:
                $output = array(
                    'error' => array(
                        'type' => 'connection_timed_out'
                    )
                );
                break;

            case 404:
                $output = array(
                    'error' => array(
                        'type' => 'data_not_found'
                    )
                );
                break;
            default:
                $results = $response->json();
                if ($results['type'] == "Topology")
                {
                    $output = $results;
                }
                else {
                    $output = array(
                        'data' => $results
                    );
                }
                break;
         };

        return $app->json($output, $statusCode);

    })->method('GET|POST')->value('id', null);// Only match GET or POST

endforeach; // eo Define endpoint routes

$app->mount('/endpoints', include 'controllers/endpoints.php');
$app->mount('/status', include 'controllers/status.php');

return $app;
