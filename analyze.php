<?php

require __DIR__ . '/vendor/autoload.php';

use Fredrik\DebtLens\CodeAnalyzer;
use Fredrik\DebtLens\Analysis\Rules\TooManyParametersRule;

$code = file_get_contents(
    __DIR__ . '/examples/bad-code.php'
);

$analyzer = new CodeAnalyzer([
    new TooManyParametersRule(),
]);

$findings = $analyzer->analyze($code);

foreach ($findings as $finding) {
    echo sprintf(
        "[%s] %s\n",
        strtoupper($finding->severity),
        $finding->message
    );
}