<?php

namespace app\processmanager\worker;

class Worker extends BaseWorkerAbstract
{
    public function run()
    {
        echo "done\n";
        exit(0);
    }
}