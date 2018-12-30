<?php

declare(strict_types=1);

/**
 * A PHPUnit Test Listener pushing the test results to any logger compatible PSR-3.
 *
 * PHP version 7
 *
 * @category   PHPUnit
 * @package    LoggerTestListener
 * @author     Laurent Laville <pear@laurent-laville.org>
 * @license    https://opensource.org/licenses/BSD-3-Clause The 3-Clause BSD License
 */

namespace Bartlett;

use Psr\Log\LoggerInterface;

/**
 * A concrete PHPUnit Test Listener pushing the test results to any logger compatible PSR-3.
 *
 * @category   PHPUnit
 * @package    LoggerTestListener
 * @author     Laurent Laville <pear@laurent-laville.org>
 * @license    https://opensource.org/licenses/BSD-3-Clause The 3-Clause BSD License
 */
class LoggerTestListener extends AbstractLoggerTestListener
{
    /**
     * Initialize test listener.
     *
     * @param LoggerInterface                     $logger     Any logger compatible PSR-3
     * @param \Monolog\Handler\HandlerInterface[] $handlers   Optional stack of handlers
     * @param callable[]                          $processors Optional array of processors
     */
    public function __construct(
        LoggerInterface $logger,
        array $handlers = [],
        array $processors = []
    ) {
        $this->logger = $logger;

        if ($logger instanceof \Monolog\Logger) {
            // add some handlers
            foreach ($handlers as $handler) {
                $this->logger->pushHandler($handler);
            }
            // add some processors
            foreach ($processors as $processor) {
                $this->logger->pushProcessor($processor);
            }
        }
    }
}
