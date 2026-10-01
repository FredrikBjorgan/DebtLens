<?php

use Tests\Support\ApiServer;

$apiServer = null;

beforeAll(function () use (&$apiServer) {
    $apiServer = new ApiServer();
});

afterAll(function () use (&$apiServer) {
    $apiServer?->stop();
});

it('returns findings and severity totals as JSON', function () use (&$apiServer) {
    $code = '<?php class OrderService { public function processOrder($a, $b, $c, $d, $e, $f) {'
        .str_repeat('if ($a) {}', 10).'} }';
    $response = $apiServer->request('POST', '/api/analyze', json_encode(['code' => $code]));

    expect($response['status'])->toBe(200);
    expect($response['headers'])->toContain('Content-Type: application/json; charset=utf-8');
    expect($response['body']['summary'])->toBe(['total' => 2, 'high' => 1, 'medium' => 1, 'low' => 0]);
    expect($response['body']['findings'])->toHaveCount(2);
    expect($response['body']['findings'][1])->toBe([
        'rule' => 'cyclomatic_complexity',
        'severity' => 'high',
        'message' => 'processOrder has cyclomatic complexity 11. Recommended maximum is 10.',
        'className' => null,
        'methodName' => 'processOrder',
        'line' => 1,
        'actualValue' => 11,
        'threshold' => 10,
    ]);
});

it('returns empty findings for clean code and accepts query strings', function () use (&$apiServer) {
    $response = $apiServer->request('POST', '/api/analyze?example=1', json_encode([
        'code' => '<?php function example() { return true; }',
    ]));

    expect($response['status'])->toBe(200);
    expect($response['body'])->toBe([
        'summary' => ['total' => 0, 'high' => 0, 'medium' => 0, 'low' => 0],
        'findings' => [],
    ]);
});

it('runs the line count rules through the API', function () use (&$apiServer) {
    $code = "<?php\nclass Example {\npublic function example() {\n"
        .str_repeat("\n", 39)."}\n".str_repeat("\n", 258).'}';
    $response = $apiServer->request('POST', '/api/analyze', json_encode(['code' => $code]));

    expect($response['status'])->toBe(200);
    expect($response['body']['summary'])->toBe(['total' => 2, 'high' => 0, 'medium' => 2, 'low' => 0]);
    expect(array_column($response['body']['findings'], 'rule'))->toBe(['large_class', 'long_method']);
    expect(array_column($response['body']['findings'], 'actualValue'))->toBe([301, 41]);
    expect($response['body']['findings'][0]['className'])->toBe('Example');
});

it('parses submitted code without executing it', function () use (&$apiServer) {
    $response = $apiServer->request('POST', '/api/analyze', json_encode([
        'code' => '<?php throw new RuntimeException("Submitted code must not execute.");',
    ]));

    expect($response['status'])->toBe(200);
    expect($response['body'])->toBe([
        'summary' => ['total' => 0, 'high' => 0, 'medium' => 0, 'low' => 0],
        'findings' => [],
    ]);
});

it('rejects invalid input with a JSON error', function (string $body) use (&$apiServer) {
    $response = $apiServer->request('POST', '/api/analyze', $body);

    expect($response['status'])->toBe(400);
    expect($response['headers'])->toContain('Content-Type: application/json; charset=utf-8');
    expect($response['body']['error']['message'])->toBeString()->not->toBeEmpty();
})->with([
    'missing code' => '{}',
    'empty code' => '{"code":""}',
    'whitespace code' => json_encode(['code' => " \n\t"]),
    'null code' => '{"code":null}',
    'numeric code' => '{"code":42}',
    'array code' => '{"code":[]}',
    'array body' => '[]',
    'null body' => 'null',
    'malformed JSON' => '{',
    'empty body' => '',
]);

it('returns a JSON error for invalid PHP syntax', function () use (&$apiServer) {
    $response = $apiServer->request('POST', '/api/analyze', json_encode([
        'code' => '<?php function broken( {',
    ]));

    expect($response['status'])->toBe(422);
    expect($response['headers'])->toContain('Content-Type: application/json; charset=utf-8');
    expect($response['body']['error']['message'])->toContain('Syntax error');
});

it('returns JSON errors for unknown routes and unsupported methods', function (string $method, string $path, int $status) use (&$apiServer) {
    $response = $apiServer->request($method, $path, '{}');

    expect($response['status'])->toBe($status);
    expect($response['headers'])->toContain('Content-Type: application/json; charset=utf-8');
    expect($response['body']['error']['message'])->toBeString()->not->toBeEmpty();

    if ($status === 405) {
        expect($response['headers'])->toContain('Allow: POST');
    }
})->with([
    ['GET', '/api/analyze', 405],
    ['PUT', '/api/analyze', 405],
    ['POST', '/missing', 404],
]);
