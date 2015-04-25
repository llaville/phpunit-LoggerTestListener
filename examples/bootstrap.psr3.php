<?php

$baseDir   = dirname(__DIR__);
$vendorDir = $baseDir . '/vendor';

require_once $vendorDir . '/autoload.php';

use Psr\Log\AbstractLogger;
use Psr\Log\LogLevel;

class YourPsr3Logger extends AbstractLogger
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

        echo
            sprintf(
                '%s.%s: %s',
                $this->channel,
                strtoupper($level),
                $this->interpolate($message, $context)
            ),
            PHP_EOL
        ;
    }

    protected function interpolate($message, array $context = array())
    {
        // build a replacement array with braces around the context keys
        $replace = array();
        foreach ($context as $key => $val) {
            if (is_scalar($val)) {
                $replace['{' . $key . '}'] = $val;
            }
        }

        // interpolate replacement values into the message and return
        return strtr($message, $replace);
    }
}
