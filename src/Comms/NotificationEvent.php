<?php
/*
Pupilsight, Flexible & Open School System
*/

namespace Pupilsight\Comms;

use Pupilsight\Contracts\Database\Connection;
use Pupilsight\session;
use Pupilsight\Comms\NotificationSender;
use Pupilsight\Domain\System\NotificationGateway;

/**
 * Notification Event
 *
 * Raises an event and collects recipients. Looks for matching event listeners, then pushes resulting notifications to a sender.
 *
 * @version v14
 * @since   v14
 */
class NotificationEvent
{
    protected $moduleName;
    protected $event;
    protected $text;
    protected $actionLink;

    protected $scopes = array();
    protected $recipients = array();

    protected $eventDetails;

    /**
     * Create a new notification event which correlates to an event type defined in pupilsightNotificationEvents.
     *
     * @param  string  $moduleName
     * @param  string  $event
     */
    public function __construct($moduleName, $event)
    {
        $this->moduleName = $moduleName;
        $this->event = $event;
    }

    /**
     * Defines the body text of the notification, added to the notifications page and optionally emailed to recipients.
     *
     * @param  string  $text
     */
    public function setNotificationText($text)
    {
        $this->text = $text;
    }

    /**
     * Sets the link that opens when the notification is viewed and archived.
     *
     * @param  string  $actionLink
     */
    public function setActionLink($actionLink)
    {
        $this->actionLink = $actionLink;
    }

    /**
     * Add a scopeType => scopeID pair to the list. This defines which filters will match when looking for event listeners.
     * Eg: a scopeType of pupilsightYearGroupID will only match listeners for that specific year group ID.
     * Prevent duplicates using a type+id array key
     *
     * @param  string     $type
     * @param  int|array  $id
     */
    public function addScope($type, $id)
    {
        if (empty($type) || empty($id)) return;

        if (is_array($id)) {
            foreach ($id as $idSingle) {
                $arrayKey = $type.intval($idSingle);
                $this->scopes[$arrayKey] = array('type' => $type, 'id' => $idSingle);
            }
        } else {
            $arrayKey = $type.intval($id);
            $this->scopes[$arrayKey] = array('type' => $type, 'id' => $id);
        }
    }

    /**
     * Adds a recipient to the list. Avoids duplicates by checking presence in the the array.
     *
     * @param  int|string  $pupilsightPersonID
     * @return bool
     */
    public function addRecipient($pupilsightPersonID)
    {
        if (empty($pupilsightPersonID)) return false;

        $pupilsightPersonID = intval($pupilsightPersonID);

        if (in_array($pupilsightPersonID, $this->recipients) == false) {
            $this->recipients[] = $pupilsightPersonID;
        }

        return true;
    }

    /**
     * Gets the current recipient count for this event. If called after pushNotifications() it will all include listener count.
     *
     * @return  int
     */
    public function getRecipientCount()
    {
        return (isset($this->recipients) && is_array($this->recipients))? count($this->recipients) : 0;
    }

    /**
     * Collects and sends all notifications for this event, returning a send report array.
     *
     * @param   Connection  $pdo
     * @param   session     $session
     * @param   bool        $bccMode
     * @return  array Send report with success/fail counts.
     */
    public function sendNotifications(Connection $pdo, session $session, $bccMode = false)
    {
        $gateway = new NotificationGateway($pdo);
        $sender = new NotificationSender($gateway, $session);

        $this->pushNotifications($gateway, $sender);

        return $sender->sendNotifications($bccMode);
    }

    /**
     * Send notifications for this event as BCC. Helper method to clarify the intent of the sending option.
     *
     * @param Connection $pdo
     * @param session $session
     * @return array Send report with success/fail counts.
     */
    public function sendNotificationsAsBcc(Connection $pdo, session $session)
    {
        return $this->sendNotifications($pdo, $session, true);
    }

    /**
     * Adds event listeners to the recipients list, then pushes a notification for each recipient to the notification sender.
     * Does not perform the sending of notifications (can be used for bulk processing).
     *
     * @param   NotificationGateway  $gateway
     * @param   NotificationSender   $sender
     * @return  int|bool Final recipient count, false on failure
     */
    public function pushNotifications(NotificationGateway $gateway, NotificationSender $sender)
    {
        $eventDetails = $this->getEventDetails($gateway);

        if (empty($eventDetails) || $eventDetails['active'] == 'N') {
            return false;
        }

        $this->addEventListeners($gateway, $eventDetails['pupilsightNotificationEventID'], $this->scopes);

        if ($this->getRecipientCount() == 0) {
            return false;
        }

        foreach ($this->recipients as $pupilsightPersonID) {
            $sender->addNotification($pupilsightPersonID, $this->text, $this->moduleName, $this->actionLink);
        }

        return $this->getRecipientCount();
    }

    /**
     * Get the event row from the database (lazy-load)
     *
     * @param   NotificationGateway  $gateway
     * @return  array Datbase row, null on failure
     */
    public function getEventDetails(NotificationGateway $gateway, $key = null)
    {
        if (empty($this->eventDetails)) {
            $result = $gateway->selectNotificationEventByName($this->moduleName, $this->event);
            $this->eventDetails = ($result && $result->rowCount() == 1)? $result->fetch() : null;
        }

        return (!empty($key) && isset($this->eventDetails[$key]))? $this->eventDetails[$key] : $this->eventDetails;
    }

    /**
     * Finds all listeners in the database for this event and adds them as recipients. The returned set
     * of listeners are filtered by the event scopes.
     *
     * @param    NotificationGateway  $gateway
     * @param    int                  $pupilsightNotificationEventID
     * @param    array                $scopes
     * @return int Listener count
     */
    protected function addEventListeners(NotificationGateway $gateway, $pupilsightNotificationEventID, $scopes)
    {
        $result = $gateway->selectNotificationListenersByScope($pupilsightNotificationEventID, $scopes);

        if ($result && $result->rowCount() > 0) {
            while ($listener = $result->fetch()) {
                $this->addRecipient($listener['pupilsightPersonID']);
            }
        }

        return $result->rowCount();
    }
}
