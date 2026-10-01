<?php

namespace Fredrik\DebtLens\Analysis;

class Finding
{
    public function __construct(
        public string $rule,
        public string $severity,
        public string $message,
        public ?string $className = null,
        public ?string $methodName = null,
        public ?int $line = null,
        public ?int $actualValue = null,
        public ?int $threshold = null,
    ) {
    }
}