<?php
/*
Pupilsight, Flexible & Open School System
*/

namespace Pupilsight\Comms;

use Pupilsight\Contracts\Comms\Mailer;
use Pupilsight\Contracts\Services\Session;
use Pupilsight\Domain\System\NotificationGateway;

/**
 * Notification Sender
 *
 * Holds a collection of notifications. Sends notifications by inserting in the database and optionally sends
 * by email based on the recipient's notification settings.
 *
 * TODO: Add background processing for notification emails.
 *
 * @version v14
 * @since   v14
 */
class NotificationSender
{
    protected $notifications = array();

    protected $gateway;
    protected $session;

    /**
     * Injects a gateway and session dependency, used for database inserts and email formatting.
     *
     * @param  NotificationGateway  $gateway
     * @param  session              $session
     */
    public function __construct(NotificationGateway $gateway, Session $session)
    {
        $this->gateway = $gateway;
        $this->session = $session;
    }

    /**
     * Adds a notification to the collection as an array.
     *
     * @param  int|string  $pupilsightPersonID
     * @param  string  $text
     * @param  string  $moduleName
     * @param  string  $actionLink
     * @return self
     */
    public function addNotification($pupilsightPersonID, $text, $moduleName, $actionLink)
    {
        $this->notifications[] = array(
            'pupilsightPersonID' => $pupilsightPersonID,
            'text'           => $text,
            'moduleName'     => $moduleName,
            'actionLink'     => $actionLink
        );

        return $this;
    }

    /**
     * Gets the current notification count.
     *
     * @return  int
     */
    public function getNotificationCount()
    {
        return count($this->notifications);
    }

    /**
     * Delivers all notifications by inserting/updating in database, and optionally by sends by email.
     *
     * @return  array Send report with success/fail counts.
     */
    public function sendNotifications($bccMode = false)
    {
        $sendReport = array(
            'count' => $this->getNotificationCount(),
            'inserts' => 0,
            'updates' => 0,
            'emailSent' => 0,
            'emailFailed' => 0
        );

        if ($this->getNotificationCount() == 0) {
            return $sendReport;
        }

        $mail = $this->setupEmail();

        // Clear the internal notification list before sending. In case there's an error we don't want to double-send.
        $notificationsToSend = $this->notifications;
        $this->notifications = [];

        foreach ($notificationsToSend as $notification) {
            // Check for existence of notification in new status
            $result = $this->gateway->selectNotificationByStatus($notification, 'New');

            if ($result && $result->rowCount() > 0) {
                $row = $result->fetch();
                $pupilsightNotificationID = $row['pupilsightNotificationID'];
                $this->gateway->updateNotificationCount($pupilsightNotificationID, $row['count']+1);
                $sendReport['updates']++;
            } else {
                $pupilsightNotificationID = $this->gateway->insertNotification($notification);
                $sendReport['inserts']++;
            }

            // Check for email notification preference and email address, and send if required
            $emailPreference = $this->gateway->getNotificationPreference($notification['pupilsightPersonID']);

            if (!empty($emailPreference) && $emailPreference['receiveNotificationEmails'] == 'Y') {
                // Format the email content
                $mail->renderBody('mail/notification.twig.html', [
                    'title'  => __('Notification').' - '.$notification['moduleName'],
                    'body'   => $notification['text'],
                    'button' => [
                        'url'  => 'notificationsActionProcess.php?pupilsightNotificationID='.$pupilsightNotificationID,
                        'text' => __('View Details'),
                    ],
                ]);

                // Add the recipients
                if ($bccMode == true) {
                    $mail->AddBcc($emailPreference['email']);
                } else {
                    $mail->clearAllRecipients();
                    $mail->AddAddress($emailPreference['email']);
                }

                // Not BCC mode? Send one email per recipient
                if ($bccMode == false) {
                    if ($mail->Send()) {
                        $sendReport['emailSent']++;
                    } else {
                        $sendReport['emailFailed']++;
                    }
                }
            }
        }

        // BCC mode? Send only one email, after the foreach loop.
        if ($bccMode == true) {
            if ($mail->Send()) {
                $sendReport['emailSent']++;
            } else {
                $sendReport['emailFailed']++;
            }
        }

        return $sendReport;
    }

    /**
     * Delivers all notifications. Helper method to clarify the intent of the Bcc sending option.
     *
     * @return array Send report with success/fail counts.
     */
    public function sendNotificationsAsBcc()
    {
        return $this->sendNotifications(true);
    }

    /**
     * Create a mailer and setup the email subject and sender.
     *
     * @return PupilsightMailer
     */
    protected function setupEmail()
    {
        global $container;
        $mail = $container->get(Mailer::class);

        // Format the sender
        $organisationName = $this->session->get('organisationName');
        $organisationEmail = $this->session->get('organisationEmail');
        $organisationAdministratorEmail = $this->session->get('organisationAdministratorEmail');
        $fromEmail = (!empty($organisationEmail))? $organisationEmail : $organisationAdministratorEmail;

        $mail->SetFrom($fromEmail, $organisationName);
        $mail->Subject = $this->getNotificationSubject();

        return $mail;
    }

    /**
     * Formatted notification subject.
     * @return  string
     */
    protected function getNotificationSubject()
    {
        return sprintf(__('You have received a notification on %1$s at %2$s (%3$s %4$s)'), $this->session->get('systemName'), $this->session->get('organisationNameShort'), date('H:i'), dateConvertBack($this->session->guid(), date('Y-m-d')));
    }

    /**
     * Formatted notification link.
     * @return  string
     */
    protected function getNotificationLink()
    {
        return sprintf(__('Login to %1$s and use the notification icon to check your new notification, or %2$sclick here%3$s.'), $this->session->get('systemName'), "<a href='".$this->session->get('absoluteURL')."/index.php?q=notifications.php'>", '</a>');
    }

    /**
     * Formatted notification footer.
     * @return  string
     */
    protected function getNotificationFooter()
    {
        $output = '<br/><br/>';
        $output .= '<hr/>';
        $output .= "<p style='font-style: italic; font-size: 85%'>";
        $output .= sprintf(__('If you do not wish to receive email notifications from %1$s, please %2$sclick here%3$s to adjust your preferences:'), $this->session->get('systemName'), "<a href='".$this->session->get('absoluteURL')."/index.php?q=preferences.php'>", '</a>');
        $output .= '<br/><br/>';
        $output .= sprintf(__('Email sent via %1$s at %2$s.'), $this->session->get('systemName'), $this->session->get('organisationName'));
        $output .= '</p>';

        return $output;
    }
}
