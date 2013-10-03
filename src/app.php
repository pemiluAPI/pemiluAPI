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

$app->get('/endpoints', function () use ($app) {

    $endpoints = json_decode(file_get_contents(__DIR__.'/../endpoints.json'), true);
    $endpoints = $endpoints['endpoints'];

    $output = array(
        'count' => count($endpoints),
        'data' => $endpoints
    );

    return $app->json($output);
});

$app->get('/endpoints/{slug}', function ($slug) use ($app) {

    $endpoints = json_decode(file_get_contents(__DIR__.'/../endpoints.json'), true);

    $endpoint = array_filter($endpoints['endpoints'], function($endpoint) use ($slug) {
        return $endpoint['slug'] == $slug;
    });

    $output = array('data' => $endpoint);

    return $app->json($output);
});

return $app;
