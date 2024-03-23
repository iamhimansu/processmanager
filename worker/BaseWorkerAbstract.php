<?php

namespace app\processmanager\worker;

use app\processmanager\ProcessManager;
use app\processmanager\shell\Shell;
use Exception as WorkerException;
use Exception as WorkerLogicException;

class BaseWorkerAbstract implements BaseWorkerConfigurable
{
    /**
     * The worker id
     * hashed value of the worker
     * sha256 + serialization($worker)
     * @see ProcessManager::generateWorkerId()
     * @var string|mix
     */
    public $id;

    /**
     * Default property where the data can be passed
     * @var array|string|mixed $data
     */
    public $data;

    /**
     * The Process manager
     * @var ProcessManager $processManager
     */
    public $processManager;
    /**
     * Contains the process id [PID] of the worker
     * @var int|string $_pid
     */
    private $_pid;

    /**
     * The shell in which the execution is bein done
     * @var Shell $_shell
     */
    private $_shell;

    /**
     * Status of the process
     * @var array $_processStatus
     */
    private $_processStatus;

    /**
     * @throws WorkerException
     */
    public function __construct($configs = [])
    {
        foreach ($configs as $property => $data) {
            if (property_exists($this, $property)) {
                $this->$property = $data;
            } else {
                throw new WorkerException(static::class . "::$property does not exists.");
            }
        }
    }

    /**
     * Returns the process id of the worker
     * @return int|string
     * @throws WorkerException
     */
    public function getPid()
    {
        throw new WorkerException(static::class . '::getPid not handled.');
    }

    /** Opens a child process of the
     * Process-manger
     * @return resource|false
     * @throws WorkerLogicException
     */
    public function openShell()
    {
        if (empty($this->_shell)) {
            $workerPath = $this->getProcessManager()->getWorkerPath();
            $this->registerWorkerToEnv();
            $this->_shell = Shell::create("{$this->getProcessManager()->getPhpBinary()} $workerPath {$this->id}", $params, $this->getProcessManager());
            return $this->getShell()->exec();
        }
        return $this->getShell()->getProcessStatus();
    }

    /**
     * @return ProcessManager
     */
    public function getProcessManager()
    {
        return $this->processManager;
    }

    /** Registers worker class to the env so that the script can use it
     * @return bool
     */
    public function registerWorkerToEnv()
    {
        if (!getenv($this->id)) {
            /**
             * Values of variables with dots in their names are not output when using getenv(), but are still present and can be explicitly queried.
             * @see https://www.php.net/manual/en/function.putenv.php
             */
            return putenv("{$this->id}={$this}");
        }
        return false;
    }

    /**
     * Get the shell associated with the worker
     * @return mixed
     */
    public function getShell()
    {
        return $this->_shell;
    }

    /**
     * The logic that will be used for processing task
     * must be an instance of closure
     * @return mixed
     * @throws WorkerLogicException
     */
    public function run()
    {
        throw new WorkerLogicException(static::class . '::run not handled.');
    }

    /** If the task is completed
     * @return mixed
     */
    public function completed()
    {
        return false == ($this->getShell()->getProcessStatus()['running'] ?? false);
    }

    /**
     * Closes the running shell
     * @return mixed
     */
    public function closeShell()
    {
        return $this->getShell()->close();
    }


    /** Un-registers worker class to the env after execution
     * @return mixed
     */
    public function unregisterWorkerFromEnv()
    {
        if (getenv($this->id)) {
            return putenv("$this->id");
        }
        return false;
    }

    public function __toString()
    {
        return json_encode($this, JSON_PRETTY_PRINT);
    }

}