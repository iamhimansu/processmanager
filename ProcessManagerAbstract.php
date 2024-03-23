<?php

namespace app\processmanager;

use app\processmanager\worker\Worker;
use Exception as PHPBaseException;

class ProcessManagerAbstract implements BaseProcessManagerConfigurable
{
    private const PROCESS_WORKER_PREFIX = 'PMW';

    /**
     * Data will be passed to the worker using file
     * Good to use when data set is large
     * @var bool $useFileData
     */
    public $useFileData = true;

    /**
     * Maintains the count of the workers
     * @var int $workerCount
     */
    private $workerCount = 0;

    /**
     * Path of the binary file of php
     * @var string $_phpBinary
     */
    private $_phpBinary;
    /**
     * Stores all the workers that cannot be processed concurrently
     * because of the number of child process limit
     * memory limit
     * @var Worker[] $_waiting
     */
    private $_waiting = [];

    /**
     * Stores all the workers currently in the running state
     * @var Worker[] $_running
     */
    private $_running = [];

    /**
     * Stores all the workers in the completed state
     * @var Worker[] $_completed
     */
    private $_completed = [];

    /**
     * Stores all the workers failed due to some errors
     * @var Worker[] $_failed
     */
    private $_failed = [];

    /**
     * Stores the list of workers that has been closed
     * @var Worker[] $_closed
     */
    private $_closed = [];

    /**
     * No of child processes to run concurrently
     * @var int $_processLimit
     */
    private $_processLimit = 10;

    /**
     * @var string $_workerPath
     */
    private $_workerPath;

    /**
     * The constructor sets the default configurations
     * sets the worker path
     * @Override to change the configuration
     * @throws PHPBaseException
     */
    public function __construct($dev = true)
    {
        $phpBinary = PHP_BINARY;
        /**
         * Checking for windows
         */
        if (DIRECTORY_SEPARATOR === '\\') {
            $phpBinary = PHP_BINDIR . "/php";
            if ($dev) {
                $phpBinary = 'C:/wamp64/bin/php/php8.2.6/php.exe';
            }
        }
        $this->setPhpBinPath($phpBinary);
        $this->setWorkerPath(realpath(__DIR__ . "/bin/main.php"));
    }

    /**
     * @inheritDoc
     */
    public function setPhpBinPath(string $phpBinPath)
    {
        if (file_exists($phpBinPath)) {
            $this->_phpBinary = $phpBinPath;
            return $this;
        }
        throw new PHPBaseException("PHP binary file at: '$phpBinPath' does not exists.");
    }

    /**
     * @param false|string $workerPath
     * @return self
     * @throws PHPBaseException
     */
    public function setWorkerPath($workerPath)
    {
        if (file_exists($workerPath)) {
            $this->_workerPath = $workerPath;
            return $this;
        }
        throw new PHPBaseException("Worker file at: '$workerPath' does not exists.");
    }

    /**
     * If process manager has any jobs in waiting list
     * @return bool
     * @throws PHPBaseException
     */
    public function hasJobs()
    {
        return $this->canAssignWorker();
    }

    /**
     * @return bool
     */
    public function canAssignWorker()
    {
        if (!empty($this->_waiting) || !empty($this->_running)) {
            foreach ($this->_running as $index => $worker) {
                if ($worker->completed()) {
                    $worker->closeShell();
                    $this->_completed[$index] = $worker;
                    unset($this->_running[$index]);
                }
            }
            if (count($this->_running) < $this->_processLimit) {
                /**
                 * Rest to CPU
                 */
                usleep(2000);
                /**
                 * @since above condition checks if waiting is not empty or running is not empty
                 * It is possible that there are no items in waiting
                 * but there items may be in running
                 */
                if (!empty($worker = array_pop($this->_waiting))) {
                    $worker->openShell();
                    $this->_running[] = $worker;
                }
                return true;
            }
            return true;
        }
        return false;
    }

    /**
     * @inheritDoc
     */
    public function addToWaiting(Worker $worker)
    {
        $worker->processManager = $this;
        $worker->id = $this->generateWorkerId();
        $this->_waiting[] = $worker;
        return $this;
    }

    /** Generate worker id
     * @return string
     */
    private function generateWorkerId()
    {
        ++$this->workerCount;
        return "{$this->workerCount}_" . self::PROCESS_WORKER_PREFIX . "_SPL_OBJ_HASH_" . spl_object_hash($this) . "_" . hash('sha256', serialize($this)) . $this->workerCount;
    }

    /**
     * @inheritDoc
     */
    public function addToFailed(Worker $worker)
    {
        $this->_failed[] = $worker;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function addToCompleted(Worker $worker)
    {
        $this->_completed[] = $worker;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setProcessLimit(int $limit)
    {
        $this->_processLimit = $limit;
        return $this;
    }

    public function wait()
    {
        return usleep(20000) && 1;
    }

    public function popJob()
    {
        return array_pop($this->_waiting);
    }

    /**
     * Adds the worker to the running list
     * @param Worker $worker
     * @return self
     */
    public function addToRunning(Worker $worker)
    {
        $this->_running[] = $worker;
        return $this;
    }

    /**
     * @return string
     */
    public function getPhpBinary()
    {
        return $this->_phpBinary;
    }

    /**
     * Gets the count of waiting workers
     * @return int
     */
    public function getWaitingCount()
    {
        return count($this->getWaiting());
    }

    /**
     * List of the workers in the waiting list
     * @return array
     */
    public function getWaiting()
    {
        return $this->_waiting;
    }

    /**
     * Gets the count of running workers
     * @return int
     */
    public function getRunningCount()
    {
        return count($this->getRunning());
    }

    /**
     * Returns the running list
     * @return array
     */
    public function getRunning()
    {
        return $this->_running;
    }

    /**
     * Gets the count of completed workers
     * @return int
     */
    public function getCompletedCount()
    {
        return count($this->getCompleted());
    }

    /**
     * Returns the completed list
     * @return array
     */
    public function getCompleted()
    {
        return $this->_completed;
    }

    /**
     * @return string
     */
    public function getWorkerPath()
    {
        return $this->_workerPath;
    }
}