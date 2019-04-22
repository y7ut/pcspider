<?php
// PHP Coding Standards Fixer 配置文件
// 详情请访问： https://cs.sensiolabs.org/

$finder = PhpCsFixer\Finder::create()
    ->exclude('test')
    ->notPath('some_special_file.php')
    ->in(__DIR__ . '/app')
;

return PhpCsFixer\Config::create()
    ->setRules([
        // 使用 Symfony 的风格设置
        '@Symfony' => true,
        // 取消或修改一些 Symfony 的设置
        'phpdoc_summary' => false,  // @Symfony 使用 true
        'binary_operator_spaces' => [
            'default' => 'single_space',  // @Symfony 使用 'single_space'
        ],
        // 增加一些我们自己的设置
        'array_syntax' => ['syntax' => 'short'],
        'is_null' => true,
        'phpdoc_add_missing_param_annotation' => [
            'only_untyped' =>false
        ],
        'phpdoc_order' => true,
    ])
    ->setRiskyAllowed(true)
    ->setCacheFile(__DIR__.'/.php_cs.cache')
    ->setFinder($finder)
;
