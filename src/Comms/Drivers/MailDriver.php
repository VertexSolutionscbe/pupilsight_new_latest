<?php
/*
Pupilsight, Flexible & Open School System
*/

namespace Pupilsight\Comms\Drivers;

use Pupilsight\Contracts\Comms\Mailer;
use Matthewbdaly\SMS\Contracts\Driver;

/**
 * An SMS driver which works via a gateway supporting Mail to SMS.
 * 
 * @version v17
 * @since   v17
 */
class MailDriver implements Driver
{
    /**
     * Mailer.
     *
     * @var
     */
    protected $mail;

    /**
     * Endpoint.
     *
     * @var
     */
    protected $endpoint;

    /**
     * Constructor.
     *
     * @param Mailer $mailer The Mailer instance.
     * @param array  $config The configuration.
     * @throws DriverNotConfiguredException Driver not configured correctly.
     *
     * @return void
     */
    public function __construct(Mailer $mail, array $config)
    {
        $this->mail = $mail;
        if (! array_key_exists('domain', $config)) {
            throw new DriverNotConfiguredException();
        }
        $this->endpoint = trim($config['domain']. ' @');
    }

    /**
     * Get driver name.
     *
     * @return string
     */
    public function getDriver(): string
    {
        return 'Mail';
    }

    /**
     * Get endpoint domain.
     *
     * @return string
     */
    public function getEndpoint(): string
    {
        return $this->endpoint;
    }

    /**
     * Send the SMS.
     *
     * @param array $message An array containing the message.
     *
     * @return boolean
     */
    public function sendRequest(array $message): bool
    {
        try {
            $recipient = preg_replace('/[^0-9,]/', '', $message['to']) . "@" . $this->endpoint;
            $content = trim(stripslashes(strip_tags($message['content'])));

            $this->mail->SetFrom($message['from']);
            $this->mail->AddAddress($recipient);
            $this->mail->Subject = $content;
            $this->mail->Body = $content;

            return $this->mail->Send();
        } catch (\Exception $e) {
            return false;
        }
    }
}
