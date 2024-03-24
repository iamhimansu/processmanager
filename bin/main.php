<?php
if (php_sapi_name() != 'cli') {
    exit(0);
}

array_shift($argv);
[$workerId, $classDefinitions] = $argv;

/**
 * Load class definitions
 */
foreach (unserialize(base64_decode($classDefinitions)) as $definition) {
    require_once $definition;
}

try {
    $pa = __DIR__ . "/../transporter/data/$workerId.bin";
    if (file_exists($workerFile = $pa)) {
        $worker = unserialize(base64_decode(file_get_contents($workerFile)));
    } else {
        $worker = unserialize(base64_decode(getenv($workerId)));
    }
    if ($worker instanceof \app\processmanager\worker\Worker) {
        $status = $worker->run();
        $worker->unregisterWorkerFromEnv();
        return $status;
    } else {
        throw new Exception("Could not create worker");
    }
} catch (Exception $e) {
    echo $e->getMessage();
}
exit(0);
