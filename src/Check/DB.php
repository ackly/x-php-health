<?php

namespace Ackly\Health\Check;


use Ackly\Health\CheckResult;

/**
 * Class DB
 *
 * Check DB status.
 * By default supports mysql and postgresql but could be set to
 * check any installed database by specifying custom ``dsn`` string in init options.
 *
 * Additionally already initialized PDO instance may be used by setting up ``pdo`` init option.
 *
 * @package Ackly\Health\Check
 */
class DB extends BaseCheck
{
    protected $dsn = null;
    protected $user = null;
    protected $password = null;
    protected $instance = null;

    protected $dialect = 'mysql';

    /**
     * DB constructor.
     *
     * @param array $options
     */
    public function __construct($options = [])
    {
        if (isset($options['pdo'])) {
            $this->instance = $options['pdo'];
        } else {
            if (isset($options['dsn'])) {
                $this->dsn = $options['dsn'];
            } else {
                $this->dialect = $options['dialect'] ?? 'mysql';

                $this->dsn = sprintf(
                    '%s:dbname=%s;host=%s;port=%s',
                    $this->dialect,
                    $options['host'],
                    $options['port'] ?? ($this->dialect == 'mysql' ? '3306' : '5432')
                );

                $this->user = $options['user'];
                $this->password = $options['password'];
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function run(): CheckResult
    {
        $result = new CheckResult();
        $instance = $this->instance ?? new \PDO($this->dsn, $this->user, $this->password);

        try {
            $result->info('version', $instance->getAttribute(\PDO::ATTR_SERVER_VERSION));
            $result->info('info', $instance->getAttribute(\PDO::ATTR_SERVER_INFO));

            if ($instance->query('SELECT 1') === false) {
                $result->error('Test query failed (SELECT 1 returned false)');
            }
        } catch(\Exception $error) {
            $result->error($error->getMessage());
        }

        return $result;
    }
}