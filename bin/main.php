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
    $worker = unserialize(base64_decode(getenv($workerId)));

    if ($worker instanceof \app\processmanager\worker\Worker) {
        $worker->unregisterWorkerFromEnv();
        return $worker->run();
    } else {
        throw new Exception("Could not create worker");
    }
} catch (Exception $e) {
    echo $e->getMessage();
}
exit(0);
