<?php

namespace app\processmanager;

use app\processmanager\worker\Worker;

interface BaseProcessManagerConfigurable
{
    /**
     * Checks if there is any jobs in the waiting list
     * @return bool
     */
    public function hasJobs();

    /**
     * Adds the worker to the waiting list
     * @param Worker $worker
     * @return self
     */
    public function addToWaiting(Worker $worker);

    /**
     * Adds the worker to the failed list
     * @param Worker $worker
     * @return self
     */
    public function addToFailed(Worker $worker);

    /**
     * Adds the worker to the completed list
     * @param Worker $worker
     * @return self
     */
    public function addToCompleted(Worker $worker);

    /**
     * Adds the worker to the running list
     * @param Worker $worker
     * @return self
     */
    public function addToRunning(Worker $worker);

    /**
     * Sets the limit of the worker that can run concurrently
     * @param int $limit
     * @return self
     */
    public function setProcessLimit(int $limit);

    /**
     * Binary path of the php
     * @param string $phpBinPath
     * @return mixed
     */
    public function setPhpBinPath(string $phpBinPath);

    /**
     * @return mixed
     */
    public function canAssignWorker();

    /**
     * Returns the waiting list
     * @return array
     */
    public function getWaiting();

    /**
     * Gets the count of waiting workers
     * @return int
     */
    public function getWaitingCount();

    /**
     * Returns the running list
     * @return array
     */
    public function getRunning();

    /**
     * Gets the count of running workers
     * @return int
     */
    public function getRunningCount();

    /**
     * Returns the completed list
     * @return array
     */
    public function getCompleted();

    /**
     * Gets the count of completed workers
     * @return int
     */
    public function getCompletedCount();

    /** The hash of the class
     * @return string
     */
    public function getClassHash();

    /** @return mixed
     * @since the worker class is being serialized
     * and then unserialized in the main.php
     * we need definition of the classes
     */
    public function classDefinitions();
}