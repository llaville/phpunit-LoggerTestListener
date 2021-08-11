<?php declare(strict_types=1);

require_once dirname(__DIR__) . '/vendor/autoload.php';

use Psr\Log\AbstractLogger;
use Psr\Log\LogLevel;

class LineFormatter
{
    const DEFAULT_FORMAT = "[%datetime%] %channel%.%level_name%: %message%\n";

    protected $format;

    public function __construct($format = null)
    {
        $this->format = $format ?: static::DEFAULT_FORMAT;
    }

    public function format(array $record)
    {
        $output = $this->format;

        $vars = $record;
        $vars['message'] = $this->interpolate($record['message'], $record['context']);

        foreach ($vars as $var => $val) {
            if (false !== strpos($output, '%'.$var.'%')) {
                if ($val instanceof \DateTime) {
                    $val = $val->format("Y-m-d H:i:s");
                }
                $output = str_replace('%'.$var.'%', $val, $output);
            }
        }

        return $output;
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

class YourPsr3Logger extends AbstractLogger
{
    protected $channel;
    protected $level;
    protected $formatter;
    protected static $timezone;
    protected $stream;

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
        $this->stream  = fopen(__DIR__ . DIRECTORY_SEPARATOR . 'psr3TestListener.log', 'a');
    }

    public function __destruct()
    {
        if (is_resource($this->stream)) {
            fclose($this->stream);
        }
        $this->stream = null;
    }

    public function log($level, $message, array $context = array())
    {
        if (!is_resource($this->stream)) {
            return;
        }

        if (array_search(strtoupper($level), self::$levels) < $this->level) {
            return;
        }

        if (!static::$timezone) {
            static::$timezone = new \DateTimeZone(date_default_timezone_get() ?: 'UTC');
        }

        $record = array(
            'message'    => (string) $message,
            'context'    => $context,
            'level'      => array_search(strtoupper($level), self::$levels),
            'level_name' => strtoupper($level),
            'channel'    => $this->channel,
            'datetime'   => \DateTime::createFromFormat(
                'U.u',
                sprintf('%.6F', microtime(true)),
                static::$timezone
            )->setTimezone(static::$timezone),
            'extra'      => array(),
        );

        $formatted = $this->getFormatter()->format($record);

        fwrite($this->stream, $formatted);
    }

    public function setFormatter($formatter)
    {
        $this->formatter = $formatter;
        return $this;
    }

    public function getFormatter()
    {
        if (!$this->formatter) {
            $this->formatter = $this->getDefaultFormatter();
        }
        return $this->formatter;
    }

    protected function getDefaultFormatter()
    {
        return new \LineFormatter();
    }
}
