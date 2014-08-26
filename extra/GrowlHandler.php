<?php
/**
 * Growl Handler for Monolog.
 *
 * PHP version 5
 *
 * @author   Laurent Laville <pear@laurent-laville.org>
 * @license  http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version  GIT: $Id$
 * @link     http://pear.php.net/package/Net_Growl
 */

namespace Monolog\Handler;

use Monolog\Logger;

require_once 'Net/Growl/Autoload.php';

class GrowlHandler extends AbstractProcessingHandler
{
    /**
     * Notification types
     */
    const DEBUG     = 'DEBUG';
    const INFO      = 'INFO';
    const NOTICE    = 'NOTICE';
    const WARNING   = 'WARNING';
    const ERROR     = 'ERROR';
    const CRITICAL  = 'CRITICAL';
    const ALERT     = 'ALERT';
    const EMERGENCY = 'EMERGENCY';

    private $growl;

    public function __construct($growl, $level = Logger::DEBUG, $bubble = true)
    {
        parent::__construct($level, $bubble);

        if (is_array($growl)) {

            if (isset($growl['name'])) {
                $name = $growl['name'];
            } else {
                $name = 'Growl for Monolog';
            }

            if (isset($growl['notifications'])) {
                $notifications = $growl['notifications'];
            } else {
                $notifications = array();
            }

            if (empty($notifications)) {
                // default growl channels
                $notifications = array(
                    self::DEBUG,
                    self::INFO,
                    self::NOTICE,
                    self::WARNING,
                    self::ERROR,
                    self::CRITICAL,
                    self::ALERT,
                    self::EMERGENCY,
                );
            }

            if (isset($growl['password'])) {
                $password = $growl['password'];
            } else {
                $password = '';
            }

            if (isset($growl['options'])) {
                $options = $growl['options'];
            } else {
                $options = array();
            }

            if (!isset($options['protocol'])) {
                // changed from default udp protocol to gntp
                $options['protocol'] = 'gntp';
            }

            $this->growl = \Net_Growl::singleton(
                $name, $notifications, $password, $options
            );

        } elseif ($growl instanceof \Net_Growl) {
            $this->growl = $growl;

        } else {
            throw new \InvalidArgumentException(
                'Expect to be either an array or a Net_Growl instance. ' .
                gettype($growl) . ' provided.'
            );
        }

        $response = $this->growl->register();
        if ($response->getStatus() != 'OK') {
            throw new \RuntimeException(
                'Growl Error ' . $response->getErrorCode() .
                ' - ' . $response->getErrorDescription()
            );
        }
    }

    protected function write(array $record)
    {
        $this->growl->notify(
            $record['level_name'],
            $record['channel'],
            $record['message']
        );
    }
}
