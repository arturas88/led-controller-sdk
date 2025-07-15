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
        '@PHP80Migration' => true,
        '@PHP81Migration' => true,
        '@PhpCsFixer' => true,
        '@PhpCsFixer:risky' => true,
        
        // Array formatting
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
        
        // Method and function formatting
        'method_chaining_indentation' => true,
        'multiline_whitespace_before_semicolons' => false,
        'single_line_throw' => false,
        
        // String formatting
        'single_quote' => true,
        'concat_space' => ['spacing' => 'one'],
        
        // Class formatting
        'class_attributes_separation' => [
            'elements' => [
                'method' => 'one',
                'property' => 'one',
                'trait_import' => 'none',
                'case' => 'none',
            ],
        ],
        'single_class_element_per_statement' => true,
        
        // Control structure formatting
        'control_structure_continuation_position' => [
            'position' => 'same_line',
        ],
        'control_structure_braces' => true,
        'elseif' => true,
        'include' => true,
        
        // Documentation
        'phpdoc_align' => ['align' => 'left'],
        'phpdoc_line_span' => [
            'const' => 'single',
            'property' => 'single',
            'method' => 'multi',
        ],
        'phpdoc_order' => true,
        'phpdoc_separation' => true,
        'phpdoc_summary' => true,
        
        // Strict types
        'declare_strict_types' => true,
        
        // Disable some rules that might conflict with existing codebase
        'php_unit_internal_class' => false,
        'php_unit_test_class_requires_covers' => false,
        'final_internal_class' => false,
        'global_namespace_import' => false,
        'phpdoc_to_comment' => false,
        'yoda_style' => false,
        'increment_style' => false,
        'cast_spaces' => false,
        'concat_space' => false,
        'operator_linebreak' => false,
        'multiline_comment_opening_closing' => false,
        'comment_to_phpdoc' => false,
    ])
    ->setFinder($finder); 