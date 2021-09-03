<?php declare(strict_types=1);

namespace Bartlett\Tests;

use Bartlett\LoggerTestListener;

use Monolog\Handler\TestHandler;
use Monolog\Logger;

use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\Test;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\TestSuite;
use PHPUnit\Framework\Warning;
use PHPUnit\Runner\BaseTestRunner;

use Psr\Log\LogLevel;

use Error;
use function sprintf;
use function strtoupper;
use function time;

/**
 * @since Release 2.2.0
 */
class LoggerTest extends TestCase
{
    /** @var TestHandler  */
    private $testHandler;

    /** @var LoggerTestListener  */
    private $loggerTestListener;

    /**
     * {@inheritDoc}
     */
    protected function setUp(): void
    {
        $this->testHandler = new TestHandler();

        $this->loggerTestListener = new LoggerTestListener(
            new Logger('loggerTest', [$this->testHandler])
        );
    }

    public function testAddError(): void
    {
        $testName = 'testCanAddError';
        $test = $this->getTest($testName, BaseTestRunner::STATUS_ERROR);

        $this->loggerTestListener->addError($test, new Error('Error.'), time());

        // addError operation expectation
        $record = sprintf("Error while running test '%s'.", $testName);
        $this->assertRecordThatContains('addError', $record, LogLevel::ERROR);
    }

    public function testAddWarning(): void
    {
        $testName = 'testCanAddWarning';
        $test = $this->getTest($testName, BaseTestRunner::STATUS_WARNING);

        $this->loggerTestListener->addWarning($test, new Warning('Warning.'), time());

        // addWarning operation expectation
        $record = sprintf("Warning while running test '%s'.", $testName);
        $this->assertRecordThatContains('addWarning', $record, LogLevel::WARNING);
    }

    public function testAddFailure(): void
    {
        $testName = 'testCanAddFailure';
        $test = $this->getTest($testName, BaseTestRunner::STATUS_FAILURE);

        $this->loggerTestListener->addFailure($test, new AssertionFailedError('Failure.'), time());

        // addFailure operation expectation
        $record = sprintf("Test '%s' failed.", $testName);
        $this->assertRecordThatContains('addFailure', $record, LogLevel::ERROR);
    }

    public function testAddIncompleteTest(): void
    {
        $testName = 'testCanAddIncompleteTest';
        $test = $this->getTest($testName, BaseTestRunner::STATUS_INCOMPLETE);

        $this->loggerTestListener->addIncompleteTest($test, new Error('Incomplete.'), time());

        // addIncompleteTest operation expectation
        $record = sprintf("Test '%s' is incomplete.", $testName);
        $this->assertRecordThatContains('addIncompleteTest', $record, LogLevel::WARNING);
    }

    public function testAddRiskyTest(): void
    {
        $testName = 'testCanAddRiskyTest';
        $test = $this->getTest($testName, BaseTestRunner::STATUS_RISKY);

        $this->loggerTestListener->addRiskyTest($test, new Error('Risky.'), time());

        // addRiskyTest operation expectation
        $record = sprintf("Test '%s' is risky.", $testName);
        $this->assertRecordThatContains('addRiskyTest', $record, LogLevel::WARNING);
    }

    public function testAddSkippedTest(): void
    {
        $testName = 'testCanAddSkippedTest';
        $test = $this->getTest($testName, BaseTestRunner::STATUS_SKIPPED);

        $this->loggerTestListener->addSkippedTest($test, new Error('Skipped.'), time());

        // addSkippedTest operation expectation
        $record = sprintf("Test '%s' has been skipped.", $testName);
        $this->assertRecordThatContains('addSkippedTest', $record, LogLevel::WARNING);
    }

    public function testStartTestSuite(): void
    {
        $testName = 'testCanStartTestSuite';
        $test = $this->getTest($testName, BaseTestRunner::STATUS_UNKNOWN);

        $testSuite = $this->getTestSuite(__FUNCTION__, [$test]);

        $this->loggerTestListener->startTestSuite($testSuite);

        // startTestSuite operation expectation
        $record = sprintf("TestSuite '%s' started with %d tests.", $testSuite->getName(), $testSuite->count());
        $this->assertRecordThatContains('startTestSuite', $record, LogLevel::NOTICE);
    }

    public function testEndTestSuite(): void
    {
        $testName = 'testCanEndTestSuite';
        $test = $this->getTest($testName, BaseTestRunner::STATUS_UNKNOWN);

        $testSuite = $this->getTestSuite(__FUNCTION__, [$test]);

        $this->loggerTestListener->startTestSuite($testSuite);
        $this->loggerTestListener->endTestSuite($testSuite);

        // endTestSuite operation expectation
        $record = sprintf("TestSuite '%s' ended.", $testSuite->getName());
        $this->assertRecordThatContains('endTestSuite', $record, LogLevel::NOTICE);
    }

    public function testStartTest(): void
    {
        $testName = 'testCanStartTest';
        $test = $this->getTest($testName, BaseTestRunner::STATUS_UNKNOWN);

        $this->loggerTestListener->startTest($test);

        // startTest operation expectation
        $record = sprintf("Test '%s' started.", $testName);
        $this->assertRecordThatContains('startTest', $record, LogLevel::INFO);
    }

    public function testEndTest(): void
    {
        $testName = 'testCanEndTest';
        $test = $this->getTest($testName, BaseTestRunner::STATUS_UNKNOWN);

        $this->loggerTestListener->endTest($test, time());

        // endTest operation expectation
        $record = sprintf("Test '%s' ended.", $testName);
        $this->assertRecordThatContains('endTest', $record, LogLevel::INFO);
    }

    private function getTestSuite(string $testName, array $tests): TestSuite
    {
        $testTestSuite = $this->getMockBuilder(TestSuite::class)->getMock();
        $testTestSuite->method('getName')->willReturn($testName);
        $testTestSuite->method('count')->willReturn(count($tests));

        /** @var TestSuite $testTestSuite */
        return $testTestSuite;
    }

    private function getTest(string $testName, int $status, ?int $dataSet = null): Test
    {
        $test = $this->getMockBuilder(Test::class)
            ->setMethods(['getName', 'getStatus', 'toString'])
            ->getMockForAbstractClass();
        $test->method('getName')->willReturn(
            sprintf(
                '%s%s',
                $testName,
                (null !== $dataSet) ? " with data set #{$dataSet}" : ''
            )
        );
        $test->method('getStatus')->willReturn($status);
        $test->method('toString')->willReturn(
            sprintf('%s::%s', $this->getName(), $testName)
        );

        /** @var Test $test */
        return $test;
    }

    private function assertRecordThatContains(string $operation, string $record, string $level): void
    {
        $this->assertTrue(
            $this->testHandler->hasRecordThatContains($record, $level),
            sprintf(
                '%s expect to raise %s log message "%s", but nothing equals was found',
                $operation,
                strtoupper($level),
                $record
            )
        );
    }
}
