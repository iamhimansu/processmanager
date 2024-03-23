<?php

namespace app\processmanager\shell;

interface BaseShellConfigurable
{
    /** Executes the command
     * @return mixed
     */
    public function exec();

    /**
     * Close the process
     * @return mixed
     */
    public function close();
}