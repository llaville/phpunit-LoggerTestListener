<?php
/**
 * A PHPUnit Test Listener pushing the test results to any logger compatible PSR-3.
 *
 * PHP version 5
 *
 * @category   PHPUnit
 * @package    LoggerTestListener
 * @author     Laurent Laville <pear@laurent-laville.org>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    GIT: $Id$
 */

namespace Bartlett;

use Psr\Log\LoggerInterface;

/**
 * A PHPUnit Test Listener pushing the test results to any logger compatible PSR-3.
 *
 * @category   PHPUnit
 * @package    LoggerTestListener
 * @author     Laurent Laville <pear@laurent-laville.org>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    Release: @package_version@
 */
class LoggerTestListener implements \PHPUnit_Framework_TestListener
{
    /**
     * @var Psr\Log\LoggerInterface Compatible PSR-3 logger
     */
    protected $logger;

    /**
     * Results
     */
    protected $errors         = array();
    protected $failures       = array();
    protected $incompletes    = array();
    protected $skips          = array();
    protected $risky          = array();
    protected $tests          = array();
    protected $suites         = array();
    protected $endedSuites    = 0;
    protected $assertionCount = 0;
    protected $suiteSkipped;

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

    /**
     * An error occurred.
     *
     * @param PHPUnit_Framework_Test $test
     * @param Exception              $e
     * @param float                  $time
     *
     * @return void
     */
    public function addError(
        \PHPUnit_Framework_Test $test,
        \Exception $e,
        $time
    ) {
        $this->errors[] = $test->getName();

        $this->logger->error(
            sprintf("Error while running test '%s'.", $test->getName())
        );
    }

    /**
     * A failure occurred.
     *
     * @param PHPUnit_Framework_Test                 $test
     * @param PHPUnit_Framework_AssertionFailedError $e
     * @param float                                  $time
     *
     * @return void
     */
    public function addFailure(
        \PHPUnit_Framework_Test $test,
        \PHPUnit_Framework_AssertionFailedError $e,
        $time
    ) {
        $this->failures[] = $test->getName();

        $this->logger->error(
            sprintf("Test '%s' failed.", $test->getName())
        );
    }

    /**
     * Incomplete test.
     *
     * @param PHPUnit_Framework_Test $test
     * @param Exception              $e
     * @param float                  $time
     *
     * @return void
     */
    public function addIncompleteTest(
        \PHPUnit_Framework_Test $test,
        \Exception $e,
        $time
    ) {
        $this->incompletes[] = $test->getName();

        $this->logger->warning(
            sprintf("Test '%s' is incomplete.", $test->getName())
        );
    }

    /**
     * Risky test.
     *
     * @param PHPUnit_Framework_Test $test
     * @param Exception              $e
     * @param float                  $time
     *
     * @return void
     */
    public function addRiskyTest(
        \PHPUnit_Framework_Test $test,
        \Exception $e,
        $time
    ) {
        $this->risky[] = $test->getName();

        $this->logger->warning(
            sprintf("Test '%s' is risky.", $test->getName())
        );
    }

    /**
     * Skipped test.
     *
     * @param PHPUnit_Framework_Test $test
     * @param Exception              $e
     * @param float                  $time
     *
     * @return void
     */
    public function addSkippedTest(
        \PHPUnit_Framework_Test $test,
        \Exception $e,
        $time
    ) {
        $this->skips[] = $test->getName();

        $this->suiteSkipped = true;

        $this->logger->warning(
            sprintf("Test '%s' has been skipped.", $test->getName())
        );
    }

    /**
     * A test started.
     *
     * @param PHPUnit_Framework_Test $test
     *
     * @return void
     */
    public function startTest(\PHPUnit_Framework_Test $test)
    {
        $this->tests[] = $test->getName();

        $this->logger->info(
            sprintf("Test '%s' started.", $test->getName())
        );
    }

    /**
     * A test ended.
     *
     * @param PHPUnit_Framework_Test $test
     * @param float                  $time
     *
     * @return void
     */
    public function endTest(\PHPUnit_Framework_Test $test, $time)
    {
        $this->assertionCount += $test->getNumAssertions();

        $this->logger->info(
            sprintf("Test '%s' ended.", $test->getName())
        );
    }

    /**
     * A test suite started.
     *
     * @param PHPUnit_Framework_TestSuite $suite
     *
     * @return void
     */
    public function startTestSuite(\PHPUnit_Framework_TestSuite $suite)
    {
        $this->suites[] = $suite->getName();

        $this->suiteSkipped = false;

        $this->logger->notice(
            sprintf("TestSuite '%s' started.", $suite->getName())
        );
    }

    /**
     * A test suite ended.
     *
     * @param PHPUnit_Framework_TestSuite $suite
     *
     * @return void
     */
    public function endTestSuite(\PHPUnit_Framework_TestSuite $suite)
    {
        $this->endedSuites++;

        if (count($this->suites) > $this->endedSuites) {
            $skipped = $this->suiteSkipped ? ' has been skipped' : '';

            $this->logger->notice(
                sprintf("TestSuite '%s' ended%s.", $suite->getName(), $skipped)
            );
            return;
        }

        $testCount       = count($this->tests);
        $failureCount    = count($this->failures);
        $errorCount      = count($this->errors);
        $incompleteCount = count($this->incompletes);
        $skipCount       = count($this->skips);
        $riskyCount      = count($this->risky);

        $resultMessage  = "Tests: {$testCount}, ";
        $resultMessage .= "Assertions: {$this->assertionCount}";

        if ($failureCount > 0) {
            $resultMessage .= ", Failures: {$failureCount}";
        }

        if ($errorCount > 0) {
            $resultMessage .= ", Errors: {$errorCount}";
        }

        if ($incompleteCount > 0) {
            $resultMessage .= ", Incompleted: {$incompleteCount}";
        }

        if ($skipCount > 0) {
            $resultMessage .= ", Skipped: {$skipCount}";
        }

        if ($riskyCount > 0) {
            $resultMessage .= ", Risky: {$riskyCount}";
        }

        $this->logger->notice(
            sprintf("TestSuite '%s' ended. %s", $suite->getName(), $resultMessage)
        );
    }
}
