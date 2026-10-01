<?php

use Fredrik\DebtLens\Analysis\Rules\CyclomaticComplexityRule;
use Fredrik\DebtLens\Analysis\Rules\LargeClassRule;
use Fredrik\DebtLens\Analysis\Rules\LongMethodRule;
use Fredrik\DebtLens\Analysis\Rules\TooManyParametersRule;
use Fredrik\DebtLens\Api\AnalyzeApi;
use Fredrik\DebtLens\CodeAnalyzer;

ini_set('display_errors', '0');
header('Content-Type: application/json; charset=utf-8');

try {
    require dirname(__DIR__).'/vendor/autoload.php';

    $api = new AnalyzeApi(new CodeAnalyzer([
        new TooManyParametersRule(),
        new CyclomaticComplexityRule(),
        new LongMethodRule(),
        new LargeClassRule(),
    ]));

    $response = $api->handle(
        $_SERVER['REQUEST_METHOD'],
        parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?: '/',
        file_get_contents('php://input'),
    );
} catch (Throwable $error) {
    error_log((string) $error);
    $response = ['status' => 500, 'body' => ['error' => ['message' => 'Internal server error.']]];
}

http_response_code($response['status']);

if ($response['status'] === 405) {
    header('Allow: POST');
}

echo json_encode($response['body'], JSON_INVALID_UTF8_SUBSTITUTE);
