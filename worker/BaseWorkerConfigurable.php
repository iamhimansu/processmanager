<?php

namespace app\processmanager\worker;

interface BaseWorkerConfigurable
{
    /**
     * Returns the process id of the worker
     * @return int|string
     */
    public function getPid();

    /**
     * The logic that will be used for processing task
     * @return mixed
     */
    public function run();

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

    /** Registers worker class to the env so that the script can use it
     * @return mixed
     */
    public function registerWorkerToEnv();

    /** Un-registers worker class to the env after execution
     * @return mixed
     */
    public function unregisterWorkerFromEnv();
}