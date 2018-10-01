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

use PHPUnit\Framework\TestListener;
use PHPUnit\Framework\Test;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\TestSuite;
use PHPUnit\Framework\TestResult;
use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\Warning;
use PHPUnit\Runner\BaseTestRunner;
use PHPUnit\Util\Test as TestUtil;
use PHPUnit\Util\Filter as FilterUtil;
use Exception;

/**
 * This is a simple logger test listener trait that classes unable to extend AbstractLoggerTestListener
 * (because they extend another class, etc) can include.
 *
 * @category   PHPUnit
 * @package    LoggerTestListener
 * @author     Laurent Laville <pear@laurent-laville.org>
 * @license    https://opensource.org/licenses/BSD-3-Clause The 3-Clause BSD License
 */
trait LoggerTestListenerTrait
{
    /**
     * Results
     */
    protected $stats = [];
    protected $suites = [];
    protected $endedSuites = 0;
    protected $numAssertions = 0;

    /**
     * An error occurred.
     *
     * @param Test      $test
     * @param Exception $e
     * @param float     $time
     *
     * @return void
     */
    public function addError(
        Test $test,
        Exception $e,
        $time
    ) {
        $testName = $test->getName();
        $context  = [
            'testName'           => $testName,
            'testDescriptionArr' => TestUtil::describe($test, false),
            'testDescriptionStr' => $test->toString(),
            'operation'          => __FUNCTION__,
            'reason'             => $e->getMessage(),
            'trace'              => FilterUtil::getFilteredStacktrace($e, false),
        ];

        $this->logger->error(
            sprintf("Error while running test '%s'.", $testName),
            $context
        );
    }

    /**
     * A warning occurred.
     *
     * @param Test    $test
     * @param Warning $e
     * @param float   $time
     *
     * @return void
     */
    public function addWarning(Test $test, Warning $e, $time)
    {
        $testName = $test->getName();
        $context  = [
            'testName'           => $testName,
            'testDescriptionArr' => TestUtil::describe($test, false),
            'testDescriptionStr' => $test->toString(),
            'operation'          => __FUNCTION__,
            'reason'             => $e->getMessage(),
            'trace'              => FilterUtil::getFilteredStacktrace($e, false),
        ];

        $this->logger->warning(
            sprintf("Warning while running test '%s'.", $testName),
            $context
        );
    }

    /**
     * A failure occurred.
     *
     * @param Test                 $test
     * @param AssertionFailedError $e
     * @param float                $time
     *
     * @return void
     */
    public function addFailure(
        Test $test,
        AssertionFailedError $e,
        $time
    ) {
        $testName = $test->getName();
        $context  = [
            'testName'           => $testName,
            'testDescriptionArr' => TestUtil::describe($test, false),
            'testDescriptionStr' => $test->toString(),
            'operation'          => __FUNCTION__,
            'reason'             => $e->getMessage(),
            'trace'              => FilterUtil::getFilteredStacktrace($e, false),
        ];

        $this->logger->error(
            sprintf("Test '%s' failed.", $testName),
            $context
        );
    }

    /**
     * Incomplete test.
     *
     * @param Test      $test
     * @param Exception $e
     * @param float     $time
     *
     * @return void
     */
    public function addIncompleteTest(
        Test $test,
        Exception $e,
        $time
    ) {
        $testName = $test->getName();
        $context  = [
            'testName'           => $testName,
            'testDescriptionArr' => TestUtil::describe($test, false),
            'testDescriptionStr' => $test->toString(),
            'operation'          => __FUNCTION__,
            'reason'             => $e->getMessage(),
            'trace'              => FilterUtil::getFilteredStacktrace($e, false),
        ];

        $this->logger->warning(
            sprintf("Test '%s' is incomplete.", $testName),
            $context
        );
    }

    /**
     * Risky test.
     *
     * @param Test      $test
     * @param Exception $e
     * @param float     $time
     *
     * @return void
     */
    public function addRiskyTest(
        Test $test,
        Exception $e,
        $time
    ) {
        $testName = $test->getName();
        $context  = [
            'testName'           => $testName,
            'testDescriptionArr' => TestUtil::describe($test, false),
            'testDescriptionStr' => $test->toString(),
            'operation'          => __FUNCTION__,
            'reason'             => $e->getMessage(),
            'trace'              => FilterUtil::getFilteredStacktrace($e, false),
        ];

        $this->logger->warning(
            sprintf("Test '%s' is risky.", $testName),
            $context
        );
    }

    /**
     * Skipped test.
     *
     * @param Test      $test
     * @param Exception $e
     * @param float     $time
     *
     * @return void
     */
    public function addSkippedTest(
        Test $test,
        Exception $e,
        $time
    ) {
        $testName = $test->getName();
        $context  = [
            'testName'           => $testName,
            'testDescriptionArr' => TestUtil::describe($test, false),
            'testDescriptionStr' => $test->toString(),
            'operation'          => __FUNCTION__,
            'reason'             => $e->getMessage(),
            'trace'              => FilterUtil::getFilteredStacktrace($e, false),
        ];

        $this->logger->warning(
            sprintf("Test '%s' has been skipped.", $testName),
            $context
        );
    }

    /**
     * A test started.
     *
     * @param Test $test
     *
     * @return void
     */
    public function startTest(Test $test)
    {
        $testName = $test->getName();
        $context  = [
            'testName'           => $testName,
            'testDescriptionArr' => TestUtil::describe($test, false),
            'testDescriptionStr' => $test->toString(),
            'operation'          => __FUNCTION__,
        ];

        $this->logger->info(
            sprintf("Test '%s' started.", $testName),
            $context
        );
    }

    /**
     * A test ended.
     *
     * @param Test  $test
     * @param float $time
     *
     * @return void
     */
    public function endTest(Test $test, $time)
    {
        if ($test instanceof TestCase) {
            $assertionCount       = $test->getNumAssertions();
            $this->numAssertions += $assertionCount;

            if ($test->getStatus() == BaseTestRunner::STATUS_FAILURE) {
                $status = 'failures';
            } elseif ($test->getStatus() == BaseTestRunner::STATUS_ERROR) {
                $status = 'errors';
            } elseif ($test->getStatus() == BaseTestRunner::STATUS_INCOMPLETE) {
                $status = 'incompletes';
            } elseif ($test->getStatus() == BaseTestRunner::STATUS_SKIPPED) {
                $status = 'skips';
            } elseif ($test->getStatus() == BaseTestRunner::STATUS_RISKY) {
                $status = 'risky';
            } else {
                $status = 'tests';
            }

            if (count($this->suites) - $this->endedSuites > 1) {
                $suiteName = end($this->suites);

                $this->stats[$suiteName][$status]++;
                $this->stats[$suiteName]['assertions'] += $assertionCount;
            }

            // updates also top test suite
            $suiteName = reset($this->suites);
            $this->stats[$suiteName][$status]++;
            $this->stats[$suiteName]['assertions'] += $assertionCount;
        }

        if (method_exists($test, 'hasOutput') && $test->hasOutput()) {
            $output = $test->getActualOutput();
        } else {
            $output = '';
        }

        $testName = $test->getName();
        $context  = [
            'testName'           => $testName,
            'testDescriptionArr' => TestUtil::describe($test, false),
            'testDescriptionStr' => $test->toString(),
            'operation'          => __FUNCTION__,
            'output'             => $output,
        ];

        if (isset($assertionCount)) {
            $context['assertionCount'] = $assertionCount;
        }

        $this->logger->info(
            sprintf("Test '%s' ended.", $testName),
            $context
        );
    }

    /**
     * A test suite started.
     *
     * @param TestSuite $suite
     *
     * @return void
     */
    public function startTestSuite(TestSuite $suite)
    {
        $suiteName = $suite->getName();
        $testCount = $suite->count();
        $context   = [
            'suiteName' => $suiteName,
            'testCount' => $testCount,
            'operation' => __FUNCTION__,
        ];

        $this->suites[] = $suiteName;

        $this->stats[$suiteName] = [
            'tests'       => 0,
            'assertions'  => 0,
            'failures'    => 0,
            'errors'      => 0,
            'incompletes' => 0,
            'skips'       => 0,
            'risky'       => 0,
        ];

        $this->logger->notice(
            sprintf("TestSuite '%s' started with %d tests.", $suiteName, $testCount),
            $context
        );
    }

    /**
     * A test suite ended.
     *
     * @param TestSuite $suite
     *
     * @return void
     */
    public function endTestSuite(TestSuite $suite)
    {
        $this->endedSuites++;

        $suiteName = $suite->getName();

        $context   = [
            'suiteName'       => $suiteName,
            'testCount'       => $this->stats[$suiteName]['tests'],
            'assertionCount'  => $this->stats[$suiteName]['assertions'],
            'failureCount'    => $this->stats[$suiteName]['failures'],
            'errorCount'      => $this->stats[$suiteName]['errors'],
            'incompleteCount' => $this->stats[$suiteName]['incompletes'],
            'skipCount'       => $this->stats[$suiteName]['skips'],
            'riskyCount'      => $this->stats[$suiteName]['risky'],
            'operation'       => __FUNCTION__,
        ];

        $this->logger->notice(
            sprintf("TestSuite '%s' ended.", $suiteName),
            $context
        );
    }

    /**
     * Gets all test suites statistics
     */
    public function getStats()
    {
        return $this->stats;
    }

    /**
     * Prints final results when all tests ended.
     *
     * PHPUnit\TextUI\ResultPrinter compatible
     *
     * @return void
     */
    public function printFooter(TestResult $result)
    {
        $testCount       = $result->count();
        $assertionCount  = $this->numAssertions;
        $failureCount    = $result->failureCount();
        $errorCount      = $result->errorCount();
        $incompleteCount = $result->notImplementedCount();
        $skipCount       = $result->skippedCount();
        $riskyCount      = $result->riskyCount();

        $resultStatus  = ($errorCount + $failureCount) ? 'KO' : 'OK';
        $resultMessage = sprintf('Results %s. ', $resultStatus) .
            $this->formatCounters(
                $testCount,
                $assertionCount,
                $failureCount,
                $errorCount,
                $incompleteCount,
                $skipCount,
                $riskyCount
            )
        ;

        $context = [
            'operation'       => __FUNCTION__,
            'status'          => $resultStatus,
            'testCount'       => $testCount,
            'assertionCount'  => $assertionCount,
            'failureCount'    => $failureCount,
            'errorCount'      => $errorCount,
            'incompleteCount' => $incompleteCount,
            'skipCount'       => $skipCount,
            'riskyCount'      => $riskyCount,
        ];

        $this->logger->notice($resultMessage, $context);
    }

    protected function formatCounters(
        $testCount,
        $assertionCount,
        $failureCount,
        $errorCount,
        $incompleteCount,
        $skipCount,
        $riskyCount
    ) {
        $resultMessage  = "Tests: $testCount, ";
        $resultMessage .= "Assertions: $assertionCount";

        if ($failureCount > 0) {
            $resultMessage .= ", Failures: $failureCount";
        }

        if ($errorCount > 0) {
            $resultMessage .= ", Errors: $errorCount";
        }

        if ($incompleteCount > 0) {
            $resultMessage .= ", Incomplete: $incompleteCount";
        }

        if ($skipCount > 0) {
            $resultMessage .= ", Skipped: $skipCount";
        }

        if ($riskyCount > 0) {
            $resultMessage .= ", Risky: $riskyCount";
        }

        return $resultMessage;
    }
}
