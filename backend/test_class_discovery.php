<?php

require 'vendor/autoload.php';

echo "Autoloader loaded\n";

if (class_exists('Tests\Feature\CallTest')) {
    echo "CallTest class found!\n";
    $ref = new ReflectionClass('Tests\Feature\CallTest');
    echo "File: " . $ref->getFileName() . "\n";
    echo "Methods: " . implode(', ', array_map(fn($m) => $m->getName(), $ref->getMethods())) . "\n";
} else {
    echo "CallTest class NOT found\n";
}

if (class_exists('Tests\TestCase')) {
    echo "TestCase found\n";
} else {
    echo "TestCase NOT found\n";
}
