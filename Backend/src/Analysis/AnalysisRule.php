<?php

namespace Fredrik\DebtLens\Analysis;

use PhpParser\Node;

interface AnalysisRule{
    /**
     * @return findings[]
     * 
     */
    public function analyze(Node $node): array;
} 