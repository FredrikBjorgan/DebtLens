<?php

namespace Fredrik\DebtLens\Api;

use Fredrik\DebtLens\CodeAnalyzer;
use JsonException;
use PhpParser\Error;
use stdClass;

class AnalyzeApi
{
    public function __construct(private CodeAnalyzer $analyzer)
    {
    }

    /** @return array{status: int, body: array} */
    public function handle(string $method, string $path, string $body): array
    {
        if ($path !== '/api/analyze') {
            return $this->error(404, 'Endpoint not found.');
        }

        if ($method !== 'POST') {
            return $this->error(405, 'Only POST is supported.');
        }

        try {
            $input = json_decode($body, flags: JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            return $this->error(400, 'Request body must be valid JSON.');
        }

        if (!$input instanceof stdClass || !isset($input->code)
            || !is_string($input->code) || trim($input->code) === '') {
            return $this->error(400, 'The code field must be a non-empty string.');
        }

        try {
            $findings = $this->analyzer->analyze($input->code);
        } catch (Error $error) {
            return $this->error(422, $error->getMessage());
        }

        $summary = ['total' => count($findings), 'high' => 0, 'medium' => 0, 'low' => 0];

        foreach ($findings as $finding) {
            $summary[$finding->severity]++;
        }

        return [
            'status' => 200,
            'body' => ['summary' => $summary, 'findings' => $findings],
        ];
    }

    /** @return array{status: int, body: array} */
    private function error(int $status, string $message): array
    {
        return ['status' => $status, 'body' => ['error' => ['message' => $message]]];
    }
}
