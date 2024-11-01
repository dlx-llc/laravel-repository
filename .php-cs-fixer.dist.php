<?php

require_once 'php-cs-fixer/LineBreakAfterStatementsFixer.php';

$finder = (new PhpCsFixer\Finder())
    ->in(__DIR__);

return (new PhpCsFixer\Config())
    ->registerCustomFixers([
        new LineBreakAfterStatementsFixer(),
    ])
    ->setRules([
        '@PHP82Migration' => true,
        '@PSR12' => true,
        'strict_param' => true,
        'PhpCsFixer/line_break_after_statements_fixer' => true,
        'no_extra_blank_lines' => [
            'tokens' => [
                'attribute',
                'break',
                'case',
                'continue',
                'curly_brace_block',
                'default',
                'extra',
                'parenthesis_brace_block',
                'return',
                'square_brace_block',
                'switch',
                'throw',
                'use',
            ]
        ],
        'concat_space' => [
            'spacing' => 'one',
        ],
        'method_argument_space' => [
            'keep_multiple_spaces_after_comma' => false,
            'on_multiline' => 'ensure_fully_multiline',
        ],
        'method_chaining_indentation' => true,
        'multiline_whitespace_before_semicolons' => true,
        'no_superfluous_phpdoc_tags' => [
            'allow_mixed' => true,
        ],
        'no_unneeded_control_parentheses' => true,
        'no_useless_else' => true,
        'no_useless_return' => true,
        'self_static_accessor' => true,
        'cast_spaces' => [
            'space' => 'none',
        ],
        'class_attributes_separation' => [
            'elements' => [
                'const' => 'none',
                'property' => 'none',
                'method' => 'one',
                'trait_import' => 'none',
            ],
        ],
        'class_reference_name_casing' => true,
        'declare_parentheses' => true,
        'fully_qualified_strict_types' => true,
        'global_namespace_import' => true,
        'lambda_not_used_import' => true,
        'no_blank_lines_after_phpdoc' => true,
        'no_empty_comment' => true,
        'no_empty_statement' => true,
        'no_empty_phpdoc' => true,
        'no_leading_namespace_whitespace' => true,
        'no_multiline_whitespace_around_double_arrow' => true,
        'no_spaces_around_offset' => true,
        'no_trailing_comma_in_singleline' => true,
        'no_unneeded_import_alias' => true,
        'no_useless_nullsafe_operator' => true,
        'no_whitespace_before_comma_in_array' => true,
        'object_operator_without_whitespace' => true,
        'operator_linebreak' => [
            'position' => 'end',
            'only_booleans' => true,
        ],
        'single_space_around_construct' => true,
        'space_after_semicolon' => [
            'remove_in_empty_for_expressions' => true,
        ],
        'standardize_not_equals' => true,
        'trim_array_spaces' => true,
        'type_declaration_spaces' => true,
        'whitespace_after_comma_in_array' => [
            'ensure_single_space' => true,
        ],
        'ordered_imports' => [
            'sort_algorithm' => 'length',
        ],
        'ordered_class_elements' => true,
        'no_unused_imports' => true,
        'octal_notation' => false,
        'binary_operator_spaces' => true,
        'blank_line_before_statement' => [
            'statements' => ['return', 'if','continue', 'break', 'declare',  'phpdoc', 'switch', 'throw', 'try', 'yield', 'yield_from', 'while', 'for']
        ],
    ])
    ->setFinder($finder);
