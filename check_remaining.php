<?php
$ar = json_decode(file_get_contents('resources/lang/ar.json'), true);
$count = 0;
$incomplete = [];

foreach($ar as $key => $value) {
    if (preg_match('/[a-zA-Z]{4,}/', $value)) {
        $incomplete[] = ['key' => $key, 'value' => $value];
        $count++;
    }
}

echo "Total incomplete translations: $count\n\n";
echo "First 30 incomplete:\n\n";

foreach(array_slice($incomplete, 0, 30) as $item) {
    echo "Key: {$item['key']}\n";
    echo "Value: {$item['value']}\n\n";
}

