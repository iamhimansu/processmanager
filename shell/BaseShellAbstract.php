<?php

namespace app\processmanager\shell;

use app\processmanager\ProcessManager;

class BaseShellAbstract implements BaseShellConfigurable
{
    /**
     * @var ProcessManager
     */
    private $_processManager;
    private $_command;
    private $_params;

    /** @var resource|false $_processPointer */
    private $_processPointer;

    /** @var array $_processStatus */
    private $_processStatus;

    /**
     * Is the shell closed
     * @var bool $_isShellClosed
     */
    private $_isShellClosed;

    private function __construct($command, &$params, $processManager)
    {
        $this->_processManager = $processManager;
        $this->_command = $command;
        $this->_params = $params;
        $this->_isShellClosed = false;
    }

    public static function create($command, $params, $processManager)
    {
        $parameters = array_map("escapeshellarg", $params);
        array_unshift($parameters, $command);
        $command = call_user_func_array("sprintf", $parameters);

        return new static($command, $params, $processManager);
    }

    /**
     * @inheritDoc
     */
    public function exec()
    {
        $this->_isShellClosed = false;
        $descriptorSpec = [STDIN, STDOUT, STDERR];
        return $this->_processPointer = proc_open($this->_command, $descriptorSpec, $pipes);
    }

    /**
     * Close the process
     * @return mixed
     */
    public function close()
    {
        $this->_isShellClosed = true;
        $this->_processStatus = $this->getProcessStatus();
        return proc_close($this->getProcessPointer());
    }

    public function getProcessStatus()
    {
        if ($this->_isShellClosed) {
            return $this->_processStatus;
        }
        return $this->_processStatus = proc_get_status($this->getProcessPointer());
    }

    /**
     * @return false|resource
     */
    public function getProcessPointer()
    {
        return $this->_processPointer;
    }
}