<?php
/*
Pupilsight, Flexible & Open School System
*/

namespace Pupilsight\Services;

/**
 * BackgroundProcess
 *
 * Extend this class to create a background process, which allows a method to be run by
 * a separate php instance. Aimed to make long-running processes as painless as possible.
 * Allows for dynamic method calls, for classes that handle multiple actions.
 *
 * The constructors for BackgroundProcess classes are auto-wired by the DI container.
 * Any arguments called to start the process are passed to the method call when running
 * the process. Eg: startMyProcess(foo, bar) will call runMyProcess(foo, bar)
 */
abstract class BackgroundProcess
{
    private $processor;

    /**
     * Set via the DI container inflector. This allows the constructor for child classes
     * to be auto-wired by the container when instantiated by the BackgroundProcessor.
     *
     * @param BackgroundProcessor $processor
     */
    public function setProcessor(BackgroundProcessor $processor)
    {
        $this->processor = $processor;
    }

    /**
     * Call a method named startX on a background process and transform it
     * into a method call for runX. Passes a variable number of arguments to the
     * BackgroundProcessor class.
     *
     * @param string    $name
     * @param array     $arguments
     * @return bool     Process was successfully started.
     */
    public function __call(string $name, array $arguments = []) : bool
    {
        $method = 'run'.substr($name, 5);
        return $this->processor->startProcess(static::class, $method, $arguments);
    }

    /**
     * Starts a background process, which calls the run method on the class itself.
     * The fully resolved class name is passed to the BackgroundProcessor, which
     * enables it to instantiate the child class via the DI container.
     *
     * @param mixed     ...$arguments
     * @return bool     Process was successfully started.
     */
    public function start(...$arguments) : bool
    {
        return $this->processor->startProcess(static::class, 'run', $arguments);
    }

    /**
     * Dynamically call a method on the process class. This allows for the method signature
     * used in scripts to match the method signature in the class itself.
     *
     * @param string    $method
     * @param array     $arguments
     * @return string   The process output.
     */
    public function execute($method = '', array $arguments = []) : string
    {
        $output = $this->$method(...$arguments);
        return is_array($output)? json_encode($output) : strval($output);
    }

    /**
     * Check to see if any named processes for this class are currently running.
     *
     * @return bool
     */
    public function isRunning()
    {
        $processName = substr(strrchr(static::class, '\\'), 1);
        if ($processID = $this->processor->getProcessIDByName($processName)) {
            return $this->processor->isProcessRunning($processID);
        }

        return false;
    }
}
