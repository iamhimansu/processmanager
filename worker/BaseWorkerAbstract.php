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
        if (empty($this->processManager)) {
            $this->processManager = new ProcessManager();
        }
        /**
         * Add to class definitions
         */
        if (!isset($this->getProcessManager()->getClassDefinitions()[__FILE__])) {
            $this->getProcessManager()->addClassDefinitions(__FILE__);
        }
    }

    /**
     * @return ProcessManager
     */
    public function getProcessManager()
    {
        return $this->processManager;
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
        $this->clearData();
        if (empty($this->_shell)) {
            $workerPath = $this->getProcessManager()->getWorkerPath();
            $classDefinitions = base64_encode(serialize($this->getProcessManager()->classDefinitions()));
            $clone = clone $this;
            if ($clone->handleDataRegistration()) {
                $params = [
                    $workerPath,
                    $this->id,
                    $classDefinitions
                ];
                $this->_shell = Shell::create("{$this->getProcessManager()->getPhpBinary()} %s %s %s", $params, $this->getProcessManager());
                return $this->getShell()->exec();
            }
        }
        return $this->getShell()->getProcessStatus();
    }

    public function clearData()
    {
        if (file_exists($file = __DIR__ . "/../transporter/data/$this->id.bin")) {
            return unlink($file);
        }
        return true;
    }

    /** Creates a registry for data
     * @return bool
     */
    private function handleDataRegistration()
    {
        if ($this->getProcessManager()->useFileData) {
            if (!file_exists($dirname = dirname($file = __DIR__ . "/../transporter/data/$this->id.bin"))) {
                mkdir($dirname, 777);
            }
            try {
                $fp = fopen($file, 'w');
                try {
                    fwrite($fp, $this);
                } catch (WorkerLogicException $e) {
                }
                return fclose($fp);
            } catch (WorkerLogicException $e) {
            }
            return false;
        } else {
            return $this->registerWorkerToEnv();
        }
    }

    /** Registers worker class to the env so that the script can use it
     * Values of variables with dots in their names are not output when using getenv(), but are still present and can be explicitly queried.
     * @see https://www.php.net/manual/en/function.putenv.php
     * @return bool
     */
    public function registerWorkerToEnv()
    {
        if (!getenv($this->id)) {
            if ($this->getProcessManager()->useFileData) {
                $keyVal = sprintf("%s=%s", $this->id, $this->id);
            } else {
                $keyVal = sprintf("%s=%s", $this->id, $this);
            }
            return putenv($keyVal);
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
        $this->clearData();
        return $this->getShell()->close();
    }

    /** Un-registers worker class to the env after execution
     * @return mixed
     */
    public function unregisterWorkerFromEnv()
    {
        if (getenv($this->id)) {
            return putenv(sprintf('%s', $this->id));
        }
        return false;
    }

    public function __toString()
    {
        return base64_encode(serialize($this));
    }
}