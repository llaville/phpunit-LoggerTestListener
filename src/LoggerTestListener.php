<?php
/**
 * A PHPUnit Test Listener pushing the test results to any logger compatible PSR-3.
 *
 * PHP version 5
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
     * @var Psr\Log\LoggerInterface Compatible PSR-3 logger
     */
    protected $logger;

    /**
     * Initialize test listener.
     *
     * @param Psr\Log\LoggerInterface            $logger     Any logger compatible PSR-3
     * @param mixed                              $channel    The logging channel
     * @param Monolog\Handler\HandlerInterface[] $handlers   Optional stack of handlers
     * @param callable[]                         $processors Optional array of processors
     */
    public function __construct(
        LoggerInterface $logger,
        $channel = 'LoggerTestListener',
        array $handlers = null,
        array $processors = null
    ) {
        $this->logger = $logger;

        if ($logger instanceof Monolog\Logger) {
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
