<?php

namespace app\processmanager\worker;

final class Worker extends BaseWorkerAbstract
{
    public function run()
    {
        if (!file_exists($dirname = dirname($file = __DIR__ . "/temp/{$this->id}.txt"))) {
            mkdir($dirname, 777);
        }
        $fp = fopen($file, 'w+');
        fwrite($fp, print_r($this->data, 1));
        fclose($fp);

        echo "done\n";
    }
}