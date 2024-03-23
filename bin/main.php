<?php

if (!php_sapi_name() == 'cli') {
    exit(0);
}
array_shift($argv);
[$workerId] = $argv;
try {
    $worker = json_decode(getenv($workerId));
    if ($worker instanceof stdClass) {
        /** @var $worker \app\processmanager\worker\Worker */
        $worker->unregisterWorkerFromEnv();
        return $worker->run();
    } else {
        throw new Exception("Could not create worker");
    }
} catch (Exception $e) {
    echo $e->getMessage();
}
exit(0);
echo "<pre>";
var_dump(getenv($workerId), $_SERVER);
echo "</pre>";
die;
foreach ($_SERVER['argv'] as $param) {
    $params[] = $param;
}
echo "<pre>";
var_dump(php_sapi_name(), implode(' : ', $params));
echo "</pre>";
die;