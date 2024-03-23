<?php

namespace app\processmanager\worker;

use app\processmanager\ProcessManager;
use app\processmanager\shell\Shell;
use Closure;
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

    /**
     * The logic that will be used for processing task
     * must be an instance of closure
     * @param Closure $closure
     * @return mixed
     * @throws WorkerLogicException
     */
    public function run(Closure $closure)
    {
        throw new WorkerLogicException(static::class . '::run not handled.');
    }

    /** Opens a child process of the
     * Process-manger
     * @return resource|false
     */
    public function openShell()
    {
        if (empty($this->_shell)) {
            $worker = $this->getProcessManager()->getWorkerPath();
            $this->_shell = Shell::create("{$this->getProcessManager()->getPhpBinary()} $worker {$this->id}", $this->data, $this->getProcessManager());
            return $this->getShell()->exec();
        }
        return $this->getShell()->getProcessStatus();
    }

    /**
     * @return ProcessManager
     */
    public function getProcessManager(): ProcessManager
    {
        return $this->processManager;
    }

    /**
     * Get the shell associated with the worker
     * @return mixed
     */
    public function getShell()
    {
        return $this->_shell;
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
}