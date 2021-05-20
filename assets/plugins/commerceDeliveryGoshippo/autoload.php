<?php

spl_autoload_register(function ($class) {
    static $classes = null;

    if ($classes === null) {
        $classes = [
            'Helpers\\FS'              => '/../../lib/Helpers/FS.php',
            //'FormLister\\Form'         => '/../../snippets/FormLister/core/controller/Form.php',
//            'Helpers\\Lexicon'         => [
//                '/../../lib/Helpers/Lexicon.php',
//                '/../../snippets/DocLister/lib/Lexicon.php',
//            ],
        ];
    }

    if (isset($classes[$class])) {
        if (is_array($classes[$class])) {
            foreach ($classes[$class] as $classFile) {
                if (is_readable(__DIR__ . $classFile)) {
                    require __DIR__ . $classFile;
                    return;
                }
            }
        } else {
            require __DIR__ . $classes[$class];
        }

        return;
    }


    if (strpos($class, 'CommerceDeliveryGoshippo\\') === 0) {
        $parts = explode('\\', $class);
        array_shift($parts);

        $filename = __DIR__ . '/src/' . implode('/', $parts) . '.php';

        if (is_readable($filename)) {
            require $filename;
        }
    }
}, true);


