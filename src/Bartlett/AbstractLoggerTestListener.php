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
 * This is a simple Logger test listener implementation that other listeners can inherit from.
 *
 * @category   PHPUnit
 * @package    LoggerTestListener
 * @author     Laurent Laville <pear@laurent-laville.org>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    Release: @package_version@
 */
abstract class AbstractLoggerTestListener implements \PHPUnit_Framework_TestListener
{
    /**
     * Results
     */
    protected $stats = array();
    protected $numAssertions = 0;
    protected $suites = array();
    protected $endedSuites = 0;
    protected $suiteSkipped;

    /**
     * @var \PHPUnit_Framework_TestResult
     */
    private $result;

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
            'testName'  => $testName,
            'operation' => __FUNCTION__,
            'reason'    => $e->getMessage(),
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
            'testName'  => $testName,
            'operation' => __FUNCTION__,
            'reason'    => $e->getMessage(),
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
            'testName'  => $testName,
            'operation' => __FUNCTION__,
            'reason'    => $e->getMessage(),
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
            'testName'  => $testName,
            'operation' => __FUNCTION__,
            'reason'    => $e->getMessage(),
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
            'testName'  => $testName,
            'operation' => __FUNCTION__,
            'reason'    => $e->getMessage(),
        );

        $this->suiteSkipped = true;

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
            'testName'  => $testName,
            'operation' => __FUNCTION__,
            'test'      => $test,
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

            if (count($this->suites) - $this->endedSuites > 1) {
                $suiteName = end($this->suites);

                if ($test->getStatus() == \PHPUnit_Runner_BaseTestRunner::STATUS_FAILURE) {
                    $this->stats[$suiteName]['failures']++;

                } elseif ($test->getStatus() == \PHPUnit_Runner_BaseTestRunner::STATUS_ERROR) {
                    $this->stats[$suiteName]['errors'] ++;

                } elseif ($test->getStatus() == \PHPUnit_Runner_BaseTestRunner::STATUS_INCOMPLETE) {
                    $this->stats[$suiteName]['incompletes']++;

                } elseif ($test->getStatus() == \PHPUnit_Runner_BaseTestRunner::STATUS_SKIPPED) {
                    $this->stats[$suiteName]['skips']++;

                } elseif ($test->getStatus() == \PHPUnit_Runner_BaseTestRunner::STATUS_RISKY) {
                    $this->stats[$suiteName]['risky']++;
                }

                $this->stats[$suiteName]['assertions']  += $assertionCount;
            }

            $this->result = $test->getTestResultObject();
        }

        $testName = $test->getName();
        $context  = array(
            'testName'  => $testName,
            'operation' => __FUNCTION__,
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
        $context   = array(
            'suiteName' => $suiteName,
            'operation' => __FUNCTION__,
        );

        $this->suites[] = $suiteName;

        $this->stats[$suiteName] = array(
            'tests'       => $suite->count(),
            'assertions'  => 0,
            'failures'    => 0,
            'errors'      => 0,
            'incompletes' => 0,
            'skips'       => 0,
            'risky'       => 0,
        );

        $this->suiteSkipped = false;

        $this->logger->notice(
            sprintf("TestSuite '%s' started.", $suiteName),
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

        if (count($this->suites) > $this->endedSuites) {
            $context   = array(
                'testCount'       => $this->stats[$suiteName]['tests'],
                'assertionCount'  => $this->stats[$suiteName]['assertions'],
                'failureCount'    => $this->stats[$suiteName]['failures'],
                'errorCount'      => $this->stats[$suiteName]['errors'],
                'incompleteCount' => $this->stats[$suiteName]['incompletes'],
                'skipCount'       => $this->stats[$suiteName]['skips'],
                'riskyCount'      => $this->stats[$suiteName]['risky'],
            );
        } else {
            $context   = array(
                'testCount'       => $this->result->count(),
                'assertionCount'  => $this->numAssertions,
                'failureCount'    => $this->result->failureCount(),
                'errorCount'      => $this->result->errorCount(),
                'incompleteCount' => $this->result->notImplementedCount(),
                'skipCount'       => $this->result->skippedCount(),
                'riskyCount'      => $this->result->riskyCount(),
            );
        }
        $context = array_merge(
            array(
                'suiteName'       => $suiteName,
                'operation'       => __FUNCTION__,
            ),
            $context
        );

        $skipped = $this->suiteSkipped ? ' has been skipped' : '';

        $this->logger->notice(
            sprintf("TestSuite '%s' ended%s.", $suiteName, $skipped),
            $context
        );

        if (count($this->suites) > $this->endedSuites) {
            return;
        }

        if (null !== $this->result) {
            $this->printResult();
        }
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
     * @return void
     */
    protected function printResult()
    {
        $testCount       = $this->result->count();
        $assertionCount  = $this->numAssertions;
        $failureCount    = $this->result->failureCount();
        $errorCount      = $this->result->errorCount();
        $incompleteCount = $this->result->notImplementedCount();
        $skipCount       = $this->result->skippedCount();
        $riskyCount      = $this->result->riskyCount();

        $resultMessage  = sprintf('Results %s. ', ($errorCount + $failureCount ? 'KO' : 'OK'));
        $resultMessage .= "Tests: {$testCount}, ";
        $resultMessage .= "Assertions: {$assertionCount}";

        if ($failureCount > 0) {
            $resultMessage .= ", Failures: {$failureCount}";
        }

        if ($errorCount > 0) {
            $resultMessage .= ", Errors: {$errorCount}";
        }

        if ($incompleteCount > 0) {
            $resultMessage .= ", Incomplete: {$incompleteCount}";
        }

        if ($skipCount > 0) {
            $resultMessage .= ", Skipped: {$skipCount}";
        }

        if ($riskyCount > 0) {
            $resultMessage .= ", Risky: {$riskyCount}";
        }

        $context = array(
            'operation'       => __FUNCTION__,
            'testCount'       => $testCount,
            'assertionCount'  => $assertionCount,
            'failureCount'    => $failureCount,
            'errorCount'      => $errorCount,
            'incompleteCount' => $incompleteCount,
            'skipCount'       => $skipCount,
            'riskyCount'      => $riskyCount,
        );

        $this->logger->notice(
            $resultMessage,
            $context
        );
    }
}
