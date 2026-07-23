<?php

$file = file_get_contents('tests/Feature/CallTest.php');

echo "File size: " . strlen($file) . " bytes\n";
echo "File starts with: " . bin2hex(substr($file, 0, 10)) . "\n";

if (strpos($file, 'class CallTest') !== false) {
    echo "Found 'class CallTest' string\n";
    $pos = strpos($file, 'class CallTest');
    echo "Position: $pos\n";
    echo "Context:\n" . substr($file, $pos - 30, 80) . "\n";
} else {
    echo "'class CallTest' NOT found\n";
}

if (strpos($file, 'namespace Tests\Feature') !== false) {
    echo "Found namespace Tests\Feature\n";
} else {
    echo "namespace Tests\Feature NOT found\n";
}

if (strpos($file, 'extends TestCase') !== false) {
    echo "Found extends TestCase\n";
} else {
    echo "extends TestCase NOT found\n";
}

echo "\nTrying to get tokens...\n";
$tokens = @token_get_all($file);
echo "Got " . count($tokens) . " tokens\n";

// Look for class declaration
foreach ($tokens as $i => $token) {
    if (is_array($token) && $token[0] === T_CLASS) {
        echo "Found T_CLASS token at index $i\n";
        // Print next few tokens
        for ($j = $i; $j < min($i + 10, count($tokens)); $j++) {
            if (is_array($tokens[$j])) {
                echo "  Token: " . token_name($tokens[$j][0]) . " => " . $tokens[$j][1] . "\n";
            } else {
                echo "  Token: " . $tokens[$j] . "\n";
            }
        }
    }
}
