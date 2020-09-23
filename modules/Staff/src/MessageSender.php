<?php
/*
Pupilsight, Flexible & Open School System
*/

namespace Pupilsight\Module\Staff;

use Pupilsight\Contracts\Comms\Mailer as MailerContract;
use Pupilsight\Contracts\Comms\SMS as SMSContract;
use Pupilsight\Domain\System\NotificationGateway;
use Pupilsight\Domain\System\SettingGateway;
use Pupilsight\Domain\User\UserGateway;

/**
 * MessageSender
 *
 * @version v18
 * @since   v18
 */
class MessageSender
{
    protected $notificationGateway;
    protected $userGateway;
    protected $settings;
    protected $mail;
    protected $sms;
    protected $via;

    public function __construct(SettingGateway $settingGateway, MailerContract $mail, SMSContract $sms, NotificationGateway $notificationGateway, UserGateway $userGateway)
    {
        $this->settings = [
            'absoluteURL' => $settingGateway->getSettingByScope('System', 'absoluteURL'),
        ];
        $this->notificationGateway = $notificationGateway;
        $this->userGateway = $userGateway;
        $this->mail = $mail;
        $this->sms = $sms;
    }

    /**
     * Send a message class to a group of recipients via multiple channels.
     *
     * @param Message   $message
     * @param array     $recipients pupilsightPersonID
     * @param string    $senderID   pupilsightPersonID
     * @return array
     */
    public function send(Message $message, array $recipients, $senderID = '') : array
    {
        // Get the user data per pupilsightPersonID
        $sender = !empty($senderID) ? $this->userGateway->getByID($senderID) : [];
        $recipients = array_map(function ($pupilsightPersonID) {
            return $this->userGateway->getByID($pupilsightPersonID);
        }, array_filter(array_unique($recipients)));

        $result = [];

        foreach ($message->via() as $via) {
            switch ($via) {
                case 'sms':
                    $sent = $this->sendViaSMS($message, $recipients);
                    break;

                case 'mail':
                    $sent = $this->sendViaMail($message, $recipients, $sender);
                    break;

                case 'database':
                    $sent = $this->sendViaDatabase($message, $recipients);
                    break;
            }
            $result[$via] = $sent;
        }

        return $result;
    }

    /**
     * Sends the message via SMS and returns an array of the successful phone numbers.
     *
     * @param Message   $message
     * @param array     $recipients
     * @return array
     */
    protected function sendViaSMS(Message $message, array $recipients = []) : array
    {
        if (empty($this->sms)) return [];

        $phoneNumbers = array_map(function ($person) {
            return ($person['phone1CountryCode'] ?? '').($person['phone1'] ?? '');
        }, $recipients);

        $sent = $this->sms
            ->content($message->toSMS()."\n".'['.$this->settings['absoluteURL'].']')
            ->send($phoneNumbers);

        return is_array($sent) ? $sent : [$sent];
    }

    /**
     * Sends the message via Email and returns an array of the successful email addresses.
     *
     * @param Message   $message
     * @param array     $recipients
     * @param array     $sender
     * @return array
     */
    protected function sendViaMail(Message $message, array $recipients = [], array $sender = []) : array
    {
        if (empty($this->mail)) return [];

        $this->mail->setDefaultSender($message->toMail()['subject']);
        $this->mail->renderBody('mail/message.twig.html', $message->toMail());

        if (!empty($sender['email'])) {
            $this->mail->addReplyTo($sender['email'], $sender['preferredName'].' '.$sender['surname']);
        }

        $sent = [];
        foreach ($recipients as $person) {
            if (empty($person['email']) || $person['receiveNotificationEmails'] == 'N') continue;

            $this->mail->clearAllRecipients();
            $this->mail->AddAddress($person['email'], $person['preferredName'].' '.$person['surname']);

            if ($this->mail->Send()) {
                $sent[] = $person['email'];
            }
        }

        return $sent;
    }

    /**
     * Inserts a message into the Notification table and returns an array of pupilsightPersonID.
     *
     * @param Message   $message
     * @param array     $recipients
     * @return array
     */
    protected function sendViaDatabase(Message $message, array $recipients = []) : array
    {
        if (empty($this->notificationGateway)) return [];

        $sent = [];
        foreach ($recipients as $person) {
            $notification = $message->toDatabase() + ['pupilsightPersonID' => $person['pupilsightPersonID']];
            $row = $this->notificationGateway->selectNotificationByStatus($notification, 'New')->fetch();

            $success = !empty($row)
                ? $this->notificationGateway->updateNotificationCount($row['pupilsightNotificationID'], $row['count']+1)
                : $this->notificationGateway->insertNotification($notification);

            if ($success) {
                $sent[] = $person['pupilsightPersonID'];
            }
        }

        return $sent;
    }
}
