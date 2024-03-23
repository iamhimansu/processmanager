<?php

$params = [];

foreach ($_SERVER['argv'] as $param) {
    $params[] = $param;
}

echo "<pre>";
var_dump(implode(' : ', $params));
echo "</pre>";
die;