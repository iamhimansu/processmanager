<?php

namespace app\processmanager\worker;

use Closure;

interface BaseWorkerConfigurable
{
    /**
     * Returns the process id of the worker
     * @return int|string
     */
    public function getPid();

    /**
     * The logic that will be used for processing task
     * must be an instance of closure
     * @param Closure $closure
     * @return mixed
     */
    public function run(Closure $closure);

    /** Opens a child process of the
     * Process Manger
     * @return mixed
     */
    public function openShell();

    /** Closes a child process of the
     * Process Manger
     * @return mixed
     */
    public function closeShell();

    /** If the task is completed
     * @return mixed
     */
    public function completed();

}