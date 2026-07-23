<?php

require 'vendor/autoload.php';

echo "Before include\n";
require 'tests/Feature/CallTest.php';
echo "After include\n";

echo "Classes defined: " . implode(', ', get_declared_classes()) . "\n";

if (class_exists('Tests\Feature\CallTest')) {
    echo "Found it!\n";
} else {
    echo "Not found\n";
}
