<?php

namespace app\processmanager\worker;

class Worker extends BaseWorkerAbstract
{
    public function run()
    {
        if (!file_exists($dirname = dirname($file = __DIR__ . "/temp/{$this->id}.txt"))) {
            mkdir($dirname, 777);
        }
        $fp = fopen($file, 'w+');
        fwrite($fp, serialize($this->data));
        fclose($fp);
        echo "done\n";
        exit(0);
    }
}