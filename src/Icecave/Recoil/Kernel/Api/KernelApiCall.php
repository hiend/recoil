<?php
namespace Icecave\Recoil\Kernel\Api;

use BadMethodCallException;
use Exception;
use Icecave\Recoil\Coroutine\CoroutineInterface;
use Icecave\Recoil\Kernel\Strand\StrandInterface;
use LogicException;

/**
 * Represents a call to a feature provided by the Kernel API.
 *
 * @see Icecave\Recoil\Kernel\KernelApiInterface
 * @see Icecave\Recoil\Kernel\KernelInterface::api()
 */
class KernelApiCall implements CoroutineInterface
{
    /**
     * @param string $name      The name of the kernel API function to invoke.
     * @param array  $arguments The arguments to the kernel API function.
     */
    public function __construct($name, array $arguments)
    {
        $this->name = $name;
        $this->arguments = $arguments;
    }

    /**
     * Fetch the name of the kernel API function to invoke.
     *
     * @return string The name of the kernel API function to invoke.
     */
    public function name()
    {
        return $this->name;
    }

    /**
     * Fetch the arguments to the kernel API function.
     *
     * @return array The arguments to the kernel API function.
     */
    public function arguments()
    {
        return $this->arguments;
    }

    /**
     * Execute the next unit of work.
     *
     * @param StrandInterface $strand The strand that is executing the coroutine.
     */
    public function tick(StrandInterface $strand)
    {
        $method = [$strand->kernel()->api(), $this->name()];

        if (is_callable($method)) {
            $strand->pop();
            $arguments = $this->arguments();
            array_unshift($arguments, $strand);
            call_user_func_array($method, $arguments);
        } else {
            $strand->throwException(
                new BadMethodCallException('The kernel API does not have an operation named "' . $this->name . '".')
            );
        }
    }

    /**
     * Store a value to send to the coroutine on the next tick.
     *
     * @codeCoverageIgnore
     *
     * @param mixed $value The value to send.
     */
    public function sendOnNextTick($value)
    {
        throw new LogicException('Not supported.');
    }

    /**
     * Store an exception to send to the coroutine on the next tick.
     *
     * @codeCoverageIgnore
     *
     * @param Exception $exception The exception to send.
     */
    public function throwOnNextTick(Exception $exception)
    {
        throw new LogicException('Not supported.');
    }

    /**
     * Instruct the coroutine to terminate execution on the next tick.
     *
     * @codeCoverageIgnore
     */
    public function terminateOnNextTick()
    {
        throw new LogicException('Not supported.');
    }

    private $name;
    private $arguments;
}