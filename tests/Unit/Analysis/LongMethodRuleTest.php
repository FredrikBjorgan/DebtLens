<?php

use Fredrik\DebtLens\Analysis\Rules\LongMethodRule;
use PhpParser\Node\Stmt\Nop;
use PhpParser\ParserFactory;

it('only flags methods and functions longer than forty lines', function (int $lineCount, bool $isMethod) {
    $declaration = $isMethod ? 'public function example()' : 'function example()';
    $prefix = $isMethod ? "<?php\nclass Example {\n" : "<?php\n";
    $body = str_repeat("    // A source line.\n", $lineCount - 2);
    $code = $prefix.$declaration." {\n".$body.'}'.($isMethod ? "\n}" : '');
    $node = (new ParserFactory())->createForNewestSupportedVersion()->parse($code)[0];
    $node = $isMethod ? $node->getMethods()[0] : $node;

    $findings = (new LongMethodRule())->analyze($node);

    if ($lineCount <= 40) {
        expect($findings)->toBe([]);

        return;
    }

    expect($findings)->toHaveCount(1);
    expect($findings[0]->rule)->toBe('long_method');
    expect($findings[0]->severity)->toBe('medium');
    expect($findings[0]->methodName)->toBe('example');
    expect($findings[0]->line)->toBe($isMethod ? 3 : 2);
    expect($findings[0]->actualValue)->toBe(41);
    expect($findings[0]->threshold)->toBe(40);
})->with([39, 40, 41])->with([
    'function' => false,
    'method' => true,
]);

it('ignores nodes that are not functions or methods', function () {
    expect((new LongMethodRule())->analyze(new Nop()))->toBe([]);
});

it('ignores method declarations without a body', function () {
    $code = "<?php\nabstract class Example {\nabstract public function example(\n"
        .str_repeat("\n", 41).");\n}";
    $node = (new ParserFactory())->createForNewestSupportedVersion()->parse($code)[0];

    expect((new LongMethodRule())->analyze($node->getMethods()[0]))->toBe([]);
});
