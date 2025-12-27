<?php
$ar = json_decode(file_get_contents('resources/lang/ar.json'), true);
$keys = array_keys($ar);
$page = 61;
$perPage = 20;
$offset = ($page - 1) * $perPage;
$pageKeys = array_slice($keys, $offset, $perPage, true);

echo "Page 61 (keys " . ($offset + 1) . " to " . ($offset + count($pageKeys)) . "):\n\n";

foreach($pageKeys as $key) {
    $arVal = $ar[$key];
    if (preg_match('/[a-zA-Z]{4,}/', $arVal)) {
        echo "Key: $key\n";
        echo "AR: $arVal\n\n";
    }
}

