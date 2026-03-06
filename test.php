<?php
$lines = [
    '123,MotoSierra 14" Hercules,50.00',
    '456,Siguiente MotoSierra,100'
];

foreach ($lines as $rawLine) {
    if (empty(trim($rawLine))) continue;
    $separator = (strpos($rawLine, ';') !== false) ? ';' : ',';
    // str_getcsv to the rescue!
    $data = str_getcsv($rawLine, $separator, '"', '\\');
    print_r($data);
}
