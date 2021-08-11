<?php

require_once dirname(__DIR__) . '/vendor' . '/autoload.php';

use Monolog\Logger;
use Monolog\Handler\RotatingFileHandler;

use Bartlett\Monolog\Handler\GrowlHandler;
use Bartlett\Monolog\Handler\CallbackFilterHandler;

class YourMonolog extends Logger
{
    public function __construct($name = 'PHPUnit', $level = 'debug')
    {
        /**
         * Filter growl notifications and send only
         * - test failures ($handerLevel = Logger::NOTICE; see GrowlHandler constructor)
         * - summary of test suites (message "Results OK ...", or "Results KO ..."
         */
        $filters = array(
            function($record, $handlerLevel) {
                if ($record['level'] > $handlerLevel) {
                    return true;
                }
                return (preg_match('/^Results/', $record['message']) === 1);
            }
        );

        $stream = new RotatingFileHandler(
            __DIR__ . DIRECTORY_SEPARATOR . 'monologTestListener.log',
            0, // maximal amount of files to keep (0 means unlimited)
            Logger::toMonologLevel($level)
        );
        $stream->setFilenameFormat('{filename}-{date}', 'Ymd');

        $handlers = array($stream);

        if (class_exists(GrowlHandler::class)) {
            try {
                // be notified only for test suites and test failures
                $growl = new GrowlHandler(array(), Logger::NOTICE);

                $handlers[] = new CallbackFilterHandler($growl, $filters);

            } catch (\Exception $e) {
                // Growl server is probably not started
                echo $e->getMessage(), PHP_EOL, PHP_EOL;
            }
        }

        parent::__construct($name, $handlers);
    }
}
