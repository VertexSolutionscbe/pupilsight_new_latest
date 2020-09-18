<?php
/*
Pupilsight, Flexible & Open School System
*/

namespace Pupilsight\Comms\Drivers;

use Matthewbdaly\SMS\Contracts\Driver;
use Matthewbdaly\SMS\Exceptions\DriverNotConfiguredException;

/**
 * SMS driver for OneWaySMS: http://onewaysms.hk/
 *
 * @version v17
 * @since   v17
 */
class OneWaySMSDriver implements Driver
{
    /**
     * Message Endpoint.
     *
     * @var
     */
    protected $messageEndpoint;

    /**
     * Credit Endpoint.
     *
     * @var
     */
    protected $creditEndpoint;

    /**
     * URL Query Params.
     *
     * @var
     */
    private $urlParams = [];

    /**
     * Constructor.
     *
     * @param array  $config The configuration.
     * @throws DriverNotConfiguredException Driver not configured correctly.
     *
     * @return void
     */
    public function __construct(array $config)
    {
        if (empty($config['smsURL']) || empty($config['smsUsername']) || empty($config['smsPassword'])) {
            throw new DriverNotConfiguredException();
        }
        $this->messageEndpoint = trim($config['smsURL'], ' ?');
        $this->creditEndpoint = trim($config['smsURLCredit'] ?? '', ' ?');
        $this->urlParams = [
            'apiusername'  => $config['smsUsername'],
            'apipassword'  => $config['smsPassword'],
            'languagetype' => 1,
        ];
    }

    /**
     * Get driver name.
     *
     * @return string
     */
    public function getDriver() : string
    {
        return 'OneWaySMS';
    }

    /**
     * Get endpoint domain.
     *
     * @return string
     */
    public function getEndpoint() : string
    {
        return $this->messageEndpoint;
    }

    /**
     * Send the SMS.
     *
     * @param array $message An array containing the message.
     *
     * @return boolean
     */
    public function sendRequest(array $message) : bool
    {
        try {
            // Prep the url parameters by stripping out invalid characters
            $urlParams = $this->urlParams + [
                'mobileno' => preg_replace('/[^0-9,]/', '', $message['to']),
                'senderid' => preg_replace('/[^a-zA-Z0-9]/', '', $message['from']),
                'message'  => stripslashes(strip_tags($message['content'])),
            ];

            // Fetch the result using a basic HTTP get request
            $url = $this->messageEndpoint.'?'.http_build_query($urlParams);
            $result = @file_get_contents($url);

            return !empty($result);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get the current credit balance using the smsURLCredit url endpoint.
     *
     * @return int
     */
    public function getCreditBalance() : float
    {
        $url = $this->creditEndpoint.'?'.http_build_query($this->urlParams);
        $result = @file_get_contents($url);

        return floatval($result);
    }
}
