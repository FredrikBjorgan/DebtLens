<?php

require __DIR__ . '/vendor/autoload.php';

use Fredrik\DebtLens\CodeAnalyzer;
use Fredrik\DebtLens\Analysis\Rules\TooManyParametersRule;
use Fredrik\DebtLens\Analysis\Rules\CyclomaticComplexityRule;
use Fredrik\DebtLens\Analysis\Rules\LongMethodRule;
use Fredrik\DebtLens\Analysis\Rules\LargeClassRule;

$code = file_get_contents(
    __DIR__ . '/examples/bad-code.php'
);

$analyzer = new CodeAnalyzer([
    new TooManyParametersRule(),
    new CyclomaticComplexityRule(),
    new LongMethodRule(),
    new LargeClassRule(),
]);

$findings = $analyzer->analyze($code);

foreach ($findings as $finding) {
    echo sprintf(
        "[%s] %s\n",
        strtoupper($finding->severity),
        $finding->message
    );
}
