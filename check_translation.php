<?php

$ar = json_decode(file_get_contents('resources/lang/ar.json'), true);
$en = json_decode(file_get_contents('resources/lang/en.json'), true);

$total = count($en);
$translated = 0;
$notTranslated = [];

foreach ($ar as $key => $value) {
    if (preg_match('/[\x{0600}-\x{06FF}]/u', $value)) {
        $translated++;
    } else {
        $notTranslated[$key] = $value;
    }
}

echo "Total keys: $total\n";
echo "Translated: $translated\n";
echo "Not translated: " . ($total - $translated) . "\n";
echo "Coverage: " . round(($translated / $total) * 100, 2) . "%\n\n";

echo "Sample of untranslated entries (first 20):\n";
$count = 0;
foreach ($notTranslated as $key => $value) {
    if ($count++ >= 20) break;
    echo "$key => $value\n";
}

