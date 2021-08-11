<?php declare(strict_types=1);

require_once dirname(__DIR__) . '/vendor/autoload.php';

use Bartlett\LoggerTestListenerTrait;

use PHPUnit\Framework\TestListener;
use PHPUnit\Framework\TestResult;
use PHPUnit\Runner\Version;
use PHPUnit\Util\Getopt as GetOptUtil;
use PHPUnit\Util\Printer;

use Psr\Log\LoggerAwareTrait;
use Psr\Log\AbstractLogger;
use Psr\Log\LogLevel;

use SebastianBergmann\CliParser\Parser as CliParser;

/**
 * Helper to detect phpunit switches, due to lack of implementation in custom printer classes
 * @see https://github.com/sebastianbergmann/phpunit/issues/1674
 */
trait GetOpt
{
    public function isVerbose(): bool
    {
        list ($opts, $non_opts) = $this->parseCliArguments();

        $key = array_search('--verbose', $non_opts);
        if ($key === false) {
            foreach ($opts as $opt) {
                $key = array_search('v', $opt);
                if (is_int($key)) {
                    return true;
                }
            }
            return false;
        }
        return is_int($key);
    }

    public function isDebug(): bool
    {
        list ($opts, $non_opts) = $this->parseCliArguments();

        $key = array_search('--debug', $non_opts);
        return is_int($key);
    }

    private function parseCliArguments(): array
    {
        $parameters = $_SERVER['argv'];
        $shortOptions = 'd:c:hv';
        if (version_compare(Version::id(), '9.3.8', 'ge')) {
            return (new CliParser)->parse($parameters, $shortOptions);
        }
        if (version_compare(Version::id(), '8.5.9', 'ge')) {
            // @see https://github.com/sebastianbergmann/phpunit/commit/d5d1ee19a5f04a022ae1dd00590ccf60ec269b16
            return GetOptUtil::parse($parameters, $shortOptions);
        }
        return GetOptUtil::getopt($parameters, $shortOptions);
    }
}

class Psr3ConsoleLogger extends AbstractLogger
{
    protected $channel;
    protected $level;

    /**
     * Logging levels from syslog protocol defined in RFC 5424
     *
     * @var array $levels Logging levels
     */
    protected static $levels = array(
        100 => 'DEBUG',
        200 => 'INFO',
        250 => 'NOTICE',
        300 => 'WARNING',
        400 => 'ERROR',
        500 => 'CRITICAL',
        550 => 'ALERT',
        600 => 'EMERGENCY',
    );

    public function __construct($name = 'YourLogger', $level = LogLevel::DEBUG)
    {
        $this->channel = $name;
        $this->level   = array_search(strtoupper($level), self::$levels);
    }

    public function log($level, $message, array $context = array())
    {
        if (array_search(strtoupper($level), self::$levels) < $this->level) {
            return;
        }

        echo
            sprintf(
                '%s - %s',
                $this->channel,
                $message
            ),
            PHP_EOL
        ;
    }
}

abstract class BaseResultPrinter extends Printer
{
    use LoggerTestListenerTrait, LoggerAwareTrait, GetOpt;

    /**
     * {@inheritDoc}
     */
    public function __construct($out = null)
    {
        parent::__construct($out);

        if ($this->isDebug()) {
            $level = LogLevel::DEBUG;
        } elseif ($this->isVerbose()) {
            $level = LogLevel::INFO;
        } else {
            $level = LogLevel::NOTICE;
        }
        $this->setLogger(new Psr3ConsoleLogger('PHPUnitPrinterLogger', $level));
    }
}

/**
 * Since PHPUnit 9.0.0, PHPUnit\TextUI\ResultPrinter became an interface
 * [#4024](https://github.com/sebastianbergmann/phpunit/issues/4024)
 */
if (version_compare(Version::id(), '9.0.0', 'ge')) {
    final class ResultPrinter extends BaseResultPrinter implements \PHPUnit\TextUI\ResultPrinter
    {
        public function printResult(TestResult $result): void
        {
            // none implementation to avoid default printing behavior
        }
    }
} else {
    final class ResultPrinter extends BaseResultPrinter implements TestListener
    {
    }
}
