<?php

namespace Ackly\Health;

use Ackly\Health\Check\BaseCheck;
use Ackly\Health\Formatter\JsonFormatter;

/**
 * Class HealthCheck
 *
 * @package Ackly\Health
 */
class HealthCheck
{
    protected $formatter = JsonFormatter::class;

    protected $checks = [];

    protected $name = '';
    protected $dependencies = [];

    public $result = null;

    /**
     * HealthCheck constructor.
     *
     * @param string $name Application name
     */
    public function __construct($name)
    {
        $this->name = $name;
    }

    /**
     * Add external project dependency
     *
     * @param {array|string} $dep
     */
    public function addDependency($dep)
    {
        if (is_array($dep)) {
            $this->dependencies = array_merge($this->dependencies, $dep);
        } else {
            $this->dependencies[] = $dep;
        }
    }

    /**
     * Set custom formatter class
     *
     * @param Formatter\IFormatter $formatterClass
     */
    public function setFormatter($formatterClass)
    {
        $this->formatter = $formatterClass;
    }

    /**
     * Add health check
     *
     * @param string $name
     * @param {string|array|callable} $check
     *
     * @throws \Exception
     */
    public function addCheck(string $name, $check)
    {
        if (array_key_exists($name, $this->checks)) {
            throw new \Exception('Health check with name ' . $name . ' already registered');
        }

        if (!is_callable($check) && !class_exists($check)) {
            throw new \Exception('Health check must be callable object or class name');
        }

        $this->checks[$name] = $check;
    }

    /**
     * Run health checks and get result as string.
     *
     * @param array $args Mapping between service name and array of arguments
     * that will be supplied to check (via call_user_func_array).
     * If check is class then this arguments will be passed to constructor (without spread).
     *
     * @throws \Exception
     * @return string
     */
    public function run(array $args = []): string
    {
        $result = [
            'name' => $this->name,
            'status' => 'ok',
            'components' => [],
            'dependencies' => $this->dependencies
        ];

        foreach ($this->checks as $service => $check) {
            $checkArgs = isset($args[$service]) ? $args[$service] : [];

            if (class_exists($check)) {
                $check = new $check($checkArgs);

                if (!($check instanceof BaseCheck)) {
                    throw new \Exception($service . ' check must be derived from Ackly\\Health\\Check\\BaseCheck');
                }

                $serviceStatus = $check->run();
            } else {
                $serviceStatus = call_user_func_array($check, $checkArgs);
            }

            if (!($serviceStatus instanceof CheckResult)) {
                throw new \Exception('Each health check must return instance of Ackly\\Health\\CheckResult');
            }

            if ($serviceStatus->status == 'warning' && $result['status'] == 'ok') {
                $result['status'] = 'warning';
            }

            if ($serviceStatus->status == 'error') {
                $result['status'] = 'error';
            }

            $result['components'][] = array_merge(['name' => $service], $serviceStatus->toArray());
        }

        $this->result = $result;

        /** @var Formatter\IFormatter $formatter */
        $formatter = new $this->formatter();
        return $formatter->format($result);
    }
}