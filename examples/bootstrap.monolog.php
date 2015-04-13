<?php

$baseDir   = dirname(__DIR__);
$vendorDir = $baseDir . '/vendor';
$extraDir  = $baseDir . '/extra';

$loader = require_once $vendorDir . '/autoload.php';
$loader->addClassMap(
    array(
        'Monolog\Handler\GrowlHandler'          =>  $extraDir  . '/GrowlHandler.php',
        'Monolog\Handler\CallbackFilterHandler' =>  $extraDir  . '/CallbackFilterHandler.php',
    )
);

use Monolog\Logger;
use Monolog\Handler\RotatingFileHandler;
// not yet part of Monolog distribution
use Monolog\Handler\GrowlHandler;
use Monolog\Handler\CallbackFilterHandler;

class YourMonolog extends Logger
{
    public function __construct($name = 'PHPUnit')
    {
        /**
         * Filter growl notifications and send only
         * - test failures ($handerLevel = Logger::NOTICE; see GrowlHandler constructor)
         * - summary of test suites (message "Results OK ...", or "Results KO ..."
         */
        $filter1 = function($record, $handlerLevel) {
            if ($record['level'] > $handlerLevel) {
                return true;
            }
            return (preg_match('/^Results/', $record['message']) === 1);
        };

        $stream = new RotatingFileHandler(__DIR__ . DIRECTORY_SEPARATOR . 'monologTestListener.log');
        $stream->setFilenameFormat('{filename}-{date}', 'Ymd');

        $handlers = array($stream);

        try {
            // be notified only for test suites and test failures
            $growl = new GrowlHandler(array(), Logger::NOTICE);

            $filterGrowl = new CallbackFilterHandler(
                $growl,
                array($filter1)
            );
            $handlers[] = $filterGrowl;

        } catch (\Exception $e) {
            // Growl server is probably not started
            echo $e->getMessage(), PHP_EOL, PHP_EOL;
        }

        parent::__construct($name, $handlers);
    }
}
