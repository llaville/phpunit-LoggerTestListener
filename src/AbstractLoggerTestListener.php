<?php declare(strict_types=1);
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

use PHPUnit\Framework\TestListener;

/**
 * This is a simple Logger test listener implementation that other listeners can inherit from.
 *
 * @category   PHPUnit
 * @package    LoggerTestListener
 * @author     Laurent Laville <pear@laurent-laville.org>
 * @license    https://opensource.org/licenses/BSD-3-Clause The 3-Clause BSD License
 */
abstract class AbstractLoggerTestListener implements TestListener
{
    use LoggerTestListenerTrait;
}
