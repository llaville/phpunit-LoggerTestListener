<?php

$baseDir   = dirname(__DIR__);
$vendorDir = $baseDir . '/vendor';

$loader = require_once $vendorDir . '/autoload.php';
$loader->addClassMap(
    array(
        'Monolog\Handler\GrowlHandler'          =>  $vendorDir  . '/bartlett/GrowlHandler.php',
        'Monolog\Handler\AdvancedFilterHandler' =>  $vendorDir  . '/bartlett/AdvancedFilterHandler.php',
    )
);


use Psr\Log\AbstractLogger;

class YourLogger extends AbstractLogger
{
    private $channel;

    public function __construct($name = 'YourLogger')
    {
        $this->channel = $name;
    }

    public function log($level, $message, array $context = array())
    {
        error_log(
            sprintf(
                '%s.%s: %s',
                $this->channel,
                strtoupper($level),
                $this->interpolate($message, $context)
            )
        );
    }

    protected function interpolate($message, array $context = array())
    {
        // build a replacement array with braces around the context keys
        $replace = array();
        foreach ($context as $key => $val) {
            $replace['{' . $key . '}'] = $val;
        }

        // interpolate replacement values into the message and return
        return strtr($message, $replace);
    }
}

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\GrowlHandler;
use Monolog\Handler\AdvancedFilterHandler;

class YourMonolog extends Logger
{
    public function __construct($name = 'PHPUnit')
    {
        $filter1 = function($record, $handlerLevel) {
            if ($record['level'] < $handlerLevel) {
                return false;
            }
            if ($record['level'] > $handlerLevel) {
                return true;
            }
            return (
                preg_match('/^TestSuite(.*)ended\./', $record['message']) === 1
                and
                $record['level'] == $handlerLevel
            );
        };

        $stream = new StreamHandler('/var/logs/monolog.log');
        $growl  = new GrowlHandler(array(), Logger::NOTICE);

        $filterGrowl = new AdvancedFilterHandler(
            $growl,
            array($filter1)
        );

        parent::__construct($name, array($stream, $filterGrowl));
    }
}
