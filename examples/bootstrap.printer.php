<?php

$baseDir   = dirname(__DIR__);
$vendorDir = $baseDir . '/vendor';
$extraDir  = $baseDir . '/extra';

require_once $vendorDir . '/autoload.php';

use Bartlett\LoggerTestListenerTrait;

use Psr\Log\LoggerAwareTrait;
use Psr\Log\AbstractLogger;
use Psr\Log\LogLevel;

/**
 * Helper to detect phpunit switches, due to lack of implementation in custom printer classes
 * @see https://github.com/sebastianbergmann/phpunit/issues/1674
 */
trait GetOpt
{
    public function isVerbose()
    {
        list ($opts, $non_opts) = \PHPUnit_Util_Getopt::getopt(
            $_SERVER['argv'],
            'd:c:hv'
        );
        $key = array_search('--verbose', $non_opts);
        if ($key === false) {
            foreach ($opts as $opt) {
                $key = array_search('v', $opt);
                if (is_int($key)) {
                    return true;
                }
            }
            return false;
        }
        return is_int($key);
    }

    public function isDebug()
    {
        list ($opts, $non_opts) = \PHPUnit_Util_Getopt::getopt(
            $_SERVER['argv'],
            'd:c:hv'
        );
        $key = array_search('--debug', $non_opts);
        return is_int($key);
    }
}

class Psr3ConsoleLogger extends AbstractLogger
{
    protected $channel;
    protected $level;

    /**
     * Logging levels from syslog protocol defined in RFC 5424
     *
     * @var array $levels Logging levels
     */
    protected static $levels = array(
        100 => 'DEBUG',
        200 => 'INFO',
        250 => 'NOTICE',
        300 => 'WARNING',
        400 => 'ERROR',
        500 => 'CRITICAL',
        550 => 'ALERT',
        600 => 'EMERGENCY',
    );

    public function __construct($name = 'YourLogger', $level = LogLevel::DEBUG)
    {
        $this->channel = $name;
        $this->level   = array_search(strtoupper($level), self::$levels);
    }

    public function log($level, $message, array $context = array())
    {
        if (array_search(strtoupper($level), self::$levels) < $this->level) {
            return;
        }

        if ($this->level == 100  // DEBUG
            && isset($context['operation'])
            && 'startTest' == $context['operation']
        ) {
            $describe = PHPUnit_Util_Test::describe($context['test']);
            $pos      = strpos($describe, $context['testName']);
            $describe = substr($describe, $pos);
            $message  = str_replace($context['testName'], $describe, $message);
        }

        echo
            sprintf(
                '%s - %s',
                $this->channel,
                $message
            ),
            PHP_EOL
        ;
    }
}

class ResultPrinter extends \PHPUnit_Util_Printer implements \PHPUnit_Framework_TestListener
{
    use LoggerTestListenerTrait, LoggerAwareTrait, GetOpt;

    /**
     * {@inheritDoc}
     */
    public function __construct($out = null)
    {
        parent::__construct($out);

        if ($this->isDebug()) {
            $level = LogLevel::DEBUG;
        } elseif ($this->isVerbose()) {
            $level = LogLevel::INFO;
        } else {
            $level = LogLevel::NOTICE;
        }
        $this->setLogger(new \Psr3ConsoleLogger('PHPUnitPrinterLogger', $level));
    }
}
