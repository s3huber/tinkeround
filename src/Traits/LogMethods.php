<?php

namespace Tinkeround\Traits;

/**
 * Trait including various log methods.
 */
trait LogMethods
{
    /**
     * Log one or more arguments.
     *
     * In case the argument is a string, the first one is right trimmed,
     * subsequent string arguments are trimmed at both sides.
     *
     * In case the argument is null, if it's the only argument it's dumped as is,
     * otherwise its string representation is concatenated.
     *
     * In case the argument is a boolean, if it's the only argument it's dumped as is,
     * otherwise its string representation is concatenated.
     *
     * In case the argument is an integer, if it's the only argument it's dumped as is,
     * otherwise its string representation is concatenated.
     *
     * In case the argument is an array, if it's the only argument and is empty it's dumped as is,
     * otherwise its string representation is concatenated.
     *
     * In case the argument is an object, the string representation of its class name is concatenated.
     *
     * @param mixed $arg
     * @param mixed ...$more
     */
    public function log($arg, ...$more): void
    {
        if ((func_num_args() === 1)) {
            $this->logSingleArgument($arg);
        } else {
            $args = func_get_args();
            $this->logMultipleArguments($args);
        }
    }

    private function logSingleArgument($arg): void
    {
        if (is_string($arg)) {
            $string = rtrim($arg);
            $this->dump($string);
        } else if (is_bool($arg) || is_int($arg) || empty($arg)) {
            $this->dump($arg);
        } else {
            $this->logMultipleArguments([$arg]);
        }
    }

    private function logMultipleArguments(array $args): void
    {
        $line = '';

        foreach ($args as $index => $arg) {
            if (is_string($arg)) {
                $line .= (($index === 0) ? rtrim($arg) : trim($arg));
            } else if (is_object($arg)) {
                $className = get_class($arg);
                $line .= "`{$className}`";
            } else {
                $string = json_encode($arg);
                $line .= "`{$string}`";
            }

            $line .= ' ';
        }

        $line = rtrim($line);
        $this->dump($line);
    }
}