<?php

$header = <<<EOF
This file is part of the QueryMap package.

(c) Alexandru Marcu <alumarcu@gmail.com>

EOF;


$finder = Symfony\CS\Finder\DefaultFinder::create()
    ->in(__DIR__.'/src')
    ->in(__DIR__.'/tests');

return Symfony\CS\Config\Config::create()
    ->level(Symfony\CS\FixerInterface::NONE_LEVEL)
    ->fixers(
        array(
            '-psr0',
            'psr1',
            'psr2',
            'symfony',
            'ordered_use',
            'newline_after_open_tag',
            'unused_use',
            'single_quote'
        )
    )
    ->finder($finder);