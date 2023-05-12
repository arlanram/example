<?php

$rules = [
    '@PSR2'                  => true,
    'array_indentation'      => true,
    'array_syntax'           => ['syntax' => 'short'],
    'binary_operator_spaces' => [
        'operators' => [
            '=>'  => 'align',
            '='   => 'align',
        ],
    ],
    'blank_line_before_statement'  => [
        'statements' => [
            'if',
            'for',
            'foreach',
            'switch',
            'break',
            'throw',
            'try',
            'return',
        ],
    ],
    'cast_spaces'                            => true,
    'concat_space'                           => ['spacing' => 'one'],
    'fully_qualified_strict_types'           => true,
    'function_typehint_space'                => true,
    'general_phpdoc_tag_rename'              => true,
    'heredoc_to_nowdoc'                      => true,
    'include'                                => true,
    'increment_style'                        => ['style' => 'post'],
    'linebreak_after_opening_tag'            => true,
    'magic_method_casing'                    => true,
    'magic_constant_casing'                  => true,
    'method_argument_space'                  => ['on_multiline' => 'ignore'],
    'multiline_whitespace_before_semicolons' => ['strategy' => 'no_multi_line'],
    'native_function_casing'                 => true,
    'no_alias_functions'                     => true,
    'no_extra_blank_lines'                   => [
        'tokens' => [
            'extra',
            'throw',
            'use',
        ],
    ],
    'no_blank_lines_after_phpdoc'                => true,
    'no_empty_phpdoc'                            => true,
    'no_empty_statement'                         => true,
    'no_singleline_whitespace_before_semicolons' => true,
    'no_spaces_around_offset'                    => [
        'positions' => ['inside', 'outside'],
    ],
    'no_trailing_comma_in_singleline'   => true,
    'no_unneeded_control_parentheses'   => [
        'statements' => ['break', 'clone', 'continue', 'echo_print', 'return', 'switch_case', 'yield'],
    ],
    'no_useless_return'                   => true,
    'no_whitespace_before_comma_in_array' => true,
    'normalize_index_brace'               => true,
    'not_operator_with_successor_space'   => true,
    'object_operator_without_whitespace'  => true,
    'phpdoc_align'                        => true,
    'phpdoc_scalar'                       => true,
    'phpdoc_separation'                   => false,
    'phpdoc_summary'                      => false,
    'simplified_null_return'              => false,
    'single_line_comment_style'           => [
        'comment_types' => ['hash'],
    ],
    'space_after_semicolon'             => true,
    'standardize_not_equals'            => true,
    'trailing_comma_in_multiline'       => true,
    'trim_array_spaces'                 => true,
    'whitespace_after_comma_in_array'   => true,
    'no_empty_comment'                  => false,
    'no_unused_imports'                 => true,
    'method_chaining_indentation'       => true,
    'is_null'                           => true,
    'class_attributes_separation'       => [
        'elements' => [
            'method' => 'one',
        ],
    ],
];

$finder = Finder::create()
    ->in([__DIR__ . '/app', __DIR__ . '/routes', __DIR__ . '/config'])
    ->name('*.php')
    ->notName('*.blade.php')
    ->ignoreDotFiles(true)
    ->ignoreVCS(true);

return (new Config())
    ->setFinder($finder)
    ->setRules($rules)
    ->setRiskyAllowed(true)
    ->setUsingCache(true);