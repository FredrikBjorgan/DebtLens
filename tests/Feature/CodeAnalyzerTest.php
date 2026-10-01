<?php

use Fredrik\DebtLens\Analysis\Rules\CyclomaticComplexityRule;
use Fredrik\DebtLens\Analysis\Rules\LargeClassRule;
use Fredrik\DebtLens\Analysis\Rules\LongMethodRule;
use Fredrik\DebtLens\Analysis\Rules\TooManyParametersRule;
use Fredrik\DebtLens\CodeAnalyzer;

it('runs all four rules through the existing analyzer', function () {
    $code = "<?php\nclass Example {\n"
        ."public function example(\$a, \$b, \$c, \$d, \$e, \$f) {\n"
        .str_repeat("if (\$a) {}\n", 10)
        .str_repeat("\n", 29)
        ."}\n"
        .str_repeat("\n", 258)
        ."}\n";

    $analyzer = new CodeAnalyzer([
        new TooManyParametersRule(),
        new CyclomaticComplexityRule(),
        new LongMethodRule(),
        new LargeClassRule(),
    ]);

    $findings = $analyzer->analyze($code);

    expect($findings)->toHaveCount(4);
    expect(array_column($findings, 'rule'))->toBe([
        'large_class',
        'too_many_parameters',
        'cyclomatic_complexity',
        'long_method',
    ]);
    expect(array_column($findings, 'actualValue'))->toBe([301, 6, 11, 41]);
});
