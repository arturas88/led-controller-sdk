<?php

declare(strict_types=1);

$finder = (new PhpCsFixer\Finder())
    ->in(__DIR__)
    ->exclude(['vendor', 'coverage', 'docs'])
    ->name('*.php')
    ->notName('*.blade.php')
    ->ignoreDotFiles(true)
    ->ignoreVCS(true);

return (new PhpCsFixer\Config())
    ->setRiskyAllowed(true)
    ->setRules([
        '@PSR12' => true,
        '@PHP82Migration' => true,
        
        // Modern PHP features
        'declare_strict_types' => true,
        'array_syntax' => ['syntax' => 'short'],
        'trailing_comma_in_multiline' => [
            'elements' => ['arrays', 'arguments', 'parameters'],
        ],
        
        // Import optimization
        'ordered_imports' => [
            'imports_order' => ['class', 'function', 'const'],
            'sort_algorithm' => 'alpha',
        ],
        'no_unused_imports' => true,
        'single_line_after_imports' => true,
        
        // String formatting
        'single_quote' => true,
        
        // Documentation
        'phpdoc_align' => ['align' => 'left'],
        'phpdoc_order' => true,
        'phpdoc_separation' => true,
        'phpdoc_summary' => true,
        
        // Disable overly strict rules
        'yoda_style' => false,
        'increment_style' => false,
        'cast_spaces' => false,
        'concat_space' => false,
        'operator_linebreak' => false,
        'multiline_comment_opening_closing' => false,
        'comment_to_phpdoc' => false,
    ])
    ->setFinder($finder); 