<?php
/*
Pupilsight, Flexible & Open School System
*/

namespace Pupilsight\Comms\Drivers;

use Matthewbdaly\SMS\Contracts\Driver;

/**
 * An unknown SMS driver, which always returns a failed response when sending messages.
 * 
 * @version v17
 * @since   v17
 */
class UnknownDriver implements Driver
{
    /**
     * Get driver name.
     *
     * @return string
     */
    public function getDriver(): string
    {
        return 'Unknown';
    }

    /**
     * Get endpoint URL.
     *
     * @return string
     */
    public function getEndpoint(): string
    {
        return '';
    }

    /**
     * Always fail to send the SMS.
     *
     * @param array $message An array containing the message.
     *
     * @return boolean
     */
    public function sendRequest(array $message): bool
    {
        return false;
    }
}
