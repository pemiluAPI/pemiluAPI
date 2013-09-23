<?php

require_once __DIR__.'/../vendor/autoload.php';

$app = new Silex\Application();

$app->get('/endpoints', function () use ($app) {

    $endpoints = json_decode(file_get_contents(__DIR__.'/../endpoints.json'), true);
    $endpoints = $endpoints['endpoints'];

    $output = array(
        'count' => count($endpoints),
        'data' => $endpoints
    );

    return $app->json($output);
});

$app->run();
