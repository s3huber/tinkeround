<?php

namespace Tinkeround\Traits;

use Countable;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Model;
use RuntimeException;

/**
 * Trait including log methods.
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

    /**
     * Log attributes of given model.
     *
     * @param Model|null $model
     * @param string[] $only (optional) List of attribute keys to log, the rest is filtered out
     */
    public function logAttributes(?Model $model, array $only = null): void
    {
        if (!$model) {
            $this->log(null);
        } else {
            $attributes = $this->filterAttributes($model, $only ?? []);
            $this->dump($attributes);
        }
    }

    /**
     * Log number of items contained in given list.
     *
     * @param array|Countable $list List of items which are counted
     * @param string $name (optional) Name for count (context), defaults to 'count'
     */
    public function logCount($list, string $name = null): void
    {
        $name = $name ?? 'Count';
        $count = count($list);

        $this->log("{$name}: {$count}");
    }

    /**
     * Log representation of given list.
     *
     * @param array|Arrayable $list
     * @param int $limit (optional) Limit number of list items which are logged
     * @throws RuntimeException In case invalid argument for list parameter is given
     */
    public function logList($list, int $limit = null): void
    {
        $this->checkListParameter($list);

        if ($list instanceof Arrayable) {
            $list = $list->toArray();
        }

        if ($limit > 0) {
            $list = array_splice($list, 0, $limit);
        }

        $this->dump($list);
    }

    /**
     * Log type of given variable. Class name in case object is given, otherwise the data type.
     *
     * @param mixed $var Variable for which the type is logged
     */
    public function logType($var): void
    {
        $type = gettype($var);
        $type = strtolower($type);

        if (is_string($var) || is_object($var)) {
            $value = $var;

            if (is_object($var)) {
                $value = get_class($var);
            }

            $this->log('Type:', "`{$type}`", "({$value})");
        } else {
            if (is_float($var)) {
                $type = 'float';
            }

            $this->log('Type:', "`{$type}`");
        }
    }

    private function checkListParameter($list): void
    {
        if (is_array($list) || $list instanceof Arrayable) {
            return;
        }

        throw new RuntimeException('Given argument for list parameter is invalid.');
    }

    private function filterAttributes(Model $model, array $only): array
    {
        $filtered = [];
        $all = $model->getAttributes();

        if (!$only) {
            return $all;
        }

        foreach ($only as $key) {
            if (array_key_exists($key, $all)) {
                $filtered[$key] = $all[$key];
            }
        }

        return $filtered;
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
