<?php

use Fredrik\DebtLens\Analysis\Rules\LargeClassRule;
use PhpParser\Node\Stmt\Nop;
use PhpParser\ParserFactory;

it('only flags classes longer than three hundred lines', function (int $lineCount) {
    $body = str_repeat("    // A source line.\n", $lineCount - 2);
    $code = "<?php\nclass Example {\n".$body.'}';
    $node = (new ParserFactory())->createForNewestSupportedVersion()->parse($code)[0];

    $findings = (new LargeClassRule())->analyze($node);

    if ($lineCount <= 300) {
        expect($findings)->toBe([]);

        return;
    }

    expect($findings)->toHaveCount(1);
    expect($findings[0]->rule)->toBe('large_class');
    expect($findings[0]->severity)->toBe('medium');
    expect($findings[0]->className)->toBe('Example');
    expect($findings[0]->line)->toBe(2);
    expect($findings[0]->actualValue)->toBe(301);
    expect($findings[0]->threshold)->toBe(300);
})->with([299, 300, 301]);

it('ignores nodes that are not classes', function () {
    expect((new LargeClassRule())->analyze(new Nop()))->toBe([]);
});
