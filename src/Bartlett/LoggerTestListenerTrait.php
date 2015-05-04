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

/**
 * This is a simple logger test listener trait that classes unable to extend AbstractLoggerTestListener
 * (because they extend another class, etc) can include.
 *
 * @category   PHPUnit
 * @package    LoggerTestListener
 * @author     Laurent Laville <pear@laurent-laville.org>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    Release: @package_version@
 */
trait LoggerTestListenerTrait
{
    /**
     * Results
     */
    protected $stats = array();
    protected $suites = array();
    protected $endedSuites = 0;

    /**
     * An error occurred.
     *
     * @param \PHPUnit_Framework_Test $test
     * @param \Exception              $e
     * @param float                   $time
     *
     * @return void
     */
    public function addError(
        \PHPUnit_Framework_Test $test,
        \Exception $e,
        $time
    ) {
        $testName = $test->getName();
        $context  = array(
            'testName'           => $testName,
            'testDescriptionArr' => \PHPUnit_Util_Test::describe($test, false),
            'testDescriptionStr' => $test->toString(),
            'operation'          => __FUNCTION__,
            'reason'             => $e->getMessage(),
        );

        $this->logger->error(
            sprintf("Error while running test '%s'.", $testName),
            $context
        );
    }

    /**
     * A failure occurred.
     *
     * @param \PHPUnit_Framework_Test                 $test
     * @param \PHPUnit_Framework_AssertionFailedError $e
     * @param float                                   $time
     *
     * @return void
     */
    public function addFailure(
        \PHPUnit_Framework_Test $test,
        \PHPUnit_Framework_AssertionFailedError $e,
        $time
    ) {
        $testName = $test->getName();
        $context  = array(
            'testName'           => $testName,
            'testDescriptionArr' => \PHPUnit_Util_Test::describe($test, false),
            'testDescriptionStr' => $test->toString(),
            'operation'          => __FUNCTION__,
            'reason'             => $e->getMessage(),
        );

        $this->logger->error(
            sprintf("Test '%s' failed.", $testName),
            $context
        );
    }

    /**
     * Incomplete test.
     *
     * @param \PHPUnit_Framework_Test $test
     * @param \Exception              $e
     * @param float                   $time
     *
     * @return void
     */
    public function addIncompleteTest(
        \PHPUnit_Framework_Test $test,
        \Exception $e,
        $time
    ) {
        $testName = $test->getName();
        $context  = array(
            'testName'           => $testName,
            'testDescriptionArr' => \PHPUnit_Util_Test::describe($test, false),
            'testDescriptionStr' => $test->toString(),
            'operation'          => __FUNCTION__,
            'reason'             => $e->getMessage(),
        );

        $this->logger->warning(
            sprintf("Test '%s' is incomplete.", $testName),
            $context
        );
    }

    /**
     * Risky test.
     *
     * @param \PHPUnit_Framework_Test $test
     * @param \Exception              $e
     * @param float                   $time
     *
     * @return void
     */
    public function addRiskyTest(
        \PHPUnit_Framework_Test $test,
        \Exception $e,
        $time
    ) {
        $testName = $test->getName();
        $context  = array(
            'testName'           => $testName,
            'testDescriptionArr' => \PHPUnit_Util_Test::describe($test, false),
            'testDescriptionStr' => $test->toString(),
            'operation'          => __FUNCTION__,
            'reason'             => $e->getMessage(),
        );

        $this->logger->warning(
            sprintf("Test '%s' is risky.", $testName),
            $context
        );
    }

    /**
     * Skipped test.
     *
     * @param \PHPUnit_Framework_Test $test
     * @param \Exception              $e
     * @param float                   $time
     *
     * @return void
     */
    public function addSkippedTest(
        \PHPUnit_Framework_Test $test,
        \Exception $e,
        $time
    ) {
        $testName = $test->getName();
        $context  = array(
            'testName'           => $testName,
            'testDescriptionArr' => \PHPUnit_Util_Test::describe($test, false),
            'testDescriptionStr' => $test->toString(),
            'operation'          => __FUNCTION__,
            'reason'             => $e->getMessage(),
        );

        $this->logger->warning(
            sprintf("Test '%s' has been skipped.", $testName),
            $context
        );
    }

    /**
     * A test started.
     *
     * @param \PHPUnit_Framework_Test $test
     *
     * @return void
     */
    public function startTest(\PHPUnit_Framework_Test $test)
    {
        $testName = $test->getName();
        $context  = array(
            'testName'           => $testName,
            'testDescriptionArr' => \PHPUnit_Util_Test::describe($test, false),
            'testDescriptionStr' => $test->toString(),
            'operation'          => __FUNCTION__,
        );

        $this->logger->info(
            sprintf("Test '%s' started.", $testName),
            $context
        );
    }

    /**
     * A test ended.
     *
     * @param \PHPUnit_Framework_Test $test
     * @param float                   $time
     *
     * @return void
     */
    public function endTest(\PHPUnit_Framework_Test $test, $time)
    {
        if ($test instanceof \PHPUnit_Framework_TestCase) {
            $assertionCount       = $test->getNumAssertions();
            $this->numAssertions += $assertionCount;

            if ($test->getStatus() == \PHPUnit_Runner_BaseTestRunner::STATUS_FAILURE) {
                $status = 'failures';
            } elseif ($test->getStatus() == \PHPUnit_Runner_BaseTestRunner::STATUS_ERROR) {
                $status = 'errors';
            } elseif ($test->getStatus() == \PHPUnit_Runner_BaseTestRunner::STATUS_INCOMPLETE) {
                $status = 'incompletes';
            } elseif ($test->getStatus() == \PHPUnit_Runner_BaseTestRunner::STATUS_SKIPPED) {
                $status = 'skips';
            } elseif ($test->getStatus() == \PHPUnit_Runner_BaseTestRunner::STATUS_RISKY) {
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

        $testName = $test->getName();
        $context  = array(
            'testName'           => $testName,
            'testDescriptionArr' => \PHPUnit_Util_Test::describe($test, false),
            'testDescriptionStr' => $test->toString(),
            'operation'          => __FUNCTION__,
        );

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
     * @param \PHPUnit_Framework_TestSuite $suite
     *
     * @return void
     */
    public function startTestSuite(\PHPUnit_Framework_TestSuite $suite)
    {
        $suiteName = $suite->getName();
        $testCount = $suite->count();
        $context   = array(
            'suiteName' => $suiteName,
            'testCount' => $testCount,
            'operation' => __FUNCTION__,
        );

        $this->suites[] = $suiteName;

        $this->stats[$suiteName] = array(
            'tests'       => 0,
            'assertions'  => 0,
            'failures'    => 0,
            'errors'      => 0,
            'incompletes' => 0,
            'skips'       => 0,
            'risky'       => 0,
        );

        $this->logger->notice(
            sprintf("TestSuite '%s' started with %d tests.", $suiteName, $testCount),
            $context
        );
    }

    /**
     * A test suite ended.
     *
     * @param \PHPUnit_Framework_TestSuite $suite
     *
     * @return void
     */
    public function endTestSuite(\PHPUnit_Framework_TestSuite $suite)
    {
        $this->endedSuites++;

        $suiteName = $suite->getName();

        $context   = array(
            'suiteName'       => $suiteName,
            'testCount'       => $this->stats[$suiteName]['tests'],
            'assertionCount'  => $this->stats[$suiteName]['assertions'],
            'failureCount'    => $this->stats[$suiteName]['failures'],
            'errorCount'      => $this->stats[$suiteName]['errors'],
            'incompleteCount' => $this->stats[$suiteName]['incompletes'],
            'skipCount'       => $this->stats[$suiteName]['skips'],
            'riskyCount'      => $this->stats[$suiteName]['risky'],
            'operation'       => __FUNCTION__,
        );

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
     * PHPUnit_TextUI_ResultPrinter compatible
     *
     * @return void
     */
    public function printFooter(\PHPUnit_Framework_TestResult $result)
    {
        $testCount       = $result->count();
        $assertionCount  = $this->numAssertions;
        $failureCount    = $result->failureCount();
        $errorCount      = $result->errorCount();
        $incompleteCount = $result->notImplementedCount();
        $skipCount       = $result->skippedCount();
        $riskyCount      = $result->riskyCount();

        $resultStatus   = ($errorCount + $failureCount) ? 'KO' : 'OK';
        $resultMessage  = sprintf('Results %s. ', $resultStatus) .
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

        $context = array(
            'operation'       => __FUNCTION__,
            'status'          => $resultStatus,
            'testCount'       => $testCount,
            'assertionCount'  => $assertionCount,
            'failureCount'    => $failureCount,
            'errorCount'      => $errorCount,
            'incompleteCount' => $incompleteCount,
            'skipCount'       => $skipCount,
            'riskyCount'      => $riskyCount,
        );

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
