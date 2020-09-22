<?php
/*
Pupilsight, Flexible & Open School System
*/

namespace Pupilsight\Domain\System;

use Pupilsight\Contracts\Database\Connection;

/**
 * Notification Gateway
 *
 * Provides a data access layer for the pupilsightNotification table
 *
 * @version v14
 * @since   v14
 */
class NotificationGateway
{
    protected $pdo;

    public function __construct(Connection $pdo)
    {
        $this->pdo = $pdo;
    }

    /* NOTIFICATIONS */
    public function selectNotification($pupilsightNotificationID)
    {
        $data = array('pupilsightNotificationID' => $pupilsightNotificationID);
        $sql = "SELECT * FROM pupilsightNotification WHERE pupilsightNotificationID=:pupilsightNotificationID";

        return $this->pdo->select($sql, $data);
    }

    public function selectNotificationByStatus($data, $status = 'New')
    {
        $data['status'] = $status;
        $sql = "SELECT * FROM pupilsightNotification WHERE pupilsightPersonID=:pupilsightPersonID AND text=:text AND actionLink=:actionLink AND pupilsightModuleID=(SELECT pupilsightModuleID FROM pupilsightModule WHERE name=:moduleName) AND status=:status";

        return $this->pdo->select($sql, $data);
    }

    public function updateNotificationCount($pupilsightNotificationID, $count)
    {
        $data = array('pupilsightNotificationID' => $pupilsightNotificationID, 'count' => $count);
        $sql = "UPDATE pupilsightNotification SET count=:count, timestamp=now() WHERE pupilsightNotificationID=:pupilsightNotificationID";

        return $this->pdo->update($sql, $data);
    }

    public function insertNotification($data)
    {
        $sql = 'INSERT INTO pupilsightNotification SET pupilsightPersonID=:pupilsightPersonID, pupilsightModuleID=(SELECT pupilsightModuleID FROM pupilsightModule WHERE name=:moduleName), text=:text, actionLink=:actionLink, timestamp=now()';

        return $this->pdo->insert($sql, $data);
    }

    /* NOTIFICATION EVENTS */
    public function selectNotificationEventByID($pupilsightNotificationEventID)
    {
        $data = array('pupilsightNotificationEventID' => $pupilsightNotificationEventID);
        $sql = "SELECT * FROM pupilsightNotificationEvent WHERE pupilsightNotificationEventID=:pupilsightNotificationEventID";

        return $this->pdo->select($sql, $data);
    }

    public function selectNotificationEventByName($moduleName, $event)
    {
        $data = array('moduleName' => $moduleName, 'event' => $event);
        $sql = "SELECT pupilsightNotificationEvent.*
                FROM pupilsightNotificationEvent
                JOIN pupilsightModule ON (pupilsightNotificationEvent.moduleName=pupilsightModule.name)
                WHERE pupilsightNotificationEvent.moduleName=:moduleName
                AND pupilsightNotificationEvent.event=:event
                AND pupilsightModule.active='Y'";

        return $this->pdo->select($sql, $data);
    }

    public function selectAllNotificationEvents()
    {
        $sql = "SELECT pupilsightNotificationEvent.*, COUNT(pupilsightNotificationListenerID) as listenerCount FROM pupilsightNotificationEvent JOIN pupilsightModule ON (pupilsightNotificationEvent.moduleName=pupilsightModule.name) LEFT JOIN pupilsightNotificationListener ON (pupilsightNotificationEvent.pupilsightNotificationEventID=pupilsightNotificationListener.pupilsightNotificationEventID) WHERE pupilsightModule.active='Y' GROUP BY pupilsightNotificationEvent.pupilsightNotificationEventID ORDER BY pupilsightModule.name, pupilsightNotificationEvent.event";

        return $this->pdo->select($sql);
    }

    public function updateNotificationEvent($update)
    {
        $data = array('pupilsightNotificationEventID' => $update['pupilsightNotificationEventID'], 'active' => $update['active']);
        $sql = "UPDATE pupilsightNotificationEvent SET active=:active WHERE pupilsightNotificationEventID=:pupilsightNotificationEventID";

        return $this->pdo->update($sql, $data);
    }

    /* NOTIFICATION LISTENERS */
    public function selectNotificationListener($pupilsightNotificationListenerID)
    {
        $data = array('pupilsightNotificationListenerID' => $pupilsightNotificationListenerID);
        $sql = "SELECT * FROM pupilsightNotificationListener WHERE pupilsightNotificationListenerID=:pupilsightNotificationListenerID";

        return $this->pdo->select($sql, $data);
    }

    public function selectAllNotificationListeners($pupilsightNotificationEventID, $groupByPerson = true)
    {
        $data = array('pupilsightNotificationEventID' => $pupilsightNotificationEventID);
        $sql = "SELECT pupilsightNotificationListener.*, pupilsightPerson.surname, pupilsightPerson.preferredName, pupilsightPerson.title, pupilsightPerson.receiveNotificationEmails
                FROM pupilsightNotificationListener
                JOIN pupilsightNotificationEvent ON (pupilsightNotificationListener.pupilsightNotificationEventID=pupilsightNotificationEvent.pupilsightNotificationEventID)
                JOIN pupilsightPerson ON (pupilsightNotificationListener.pupilsightPersonID=pupilsightPerson.pupilsightPersonID)
                JOIN pupilsightRole ON (pupilsightPerson.pupilsightRoleIDPrimary=pupilsightRole.pupilsightRoleID OR FIND_IN_SET(pupilsightRole.pupilsightRoleID, pupilsightPerson.pupilsightRoleIDAll))
                JOIN pupilsightPermission ON (pupilsightRole.pupilsightRoleID=pupilsightPermission.pupilsightRoleID)
                JOIN pupilsightAction ON (pupilsightPermission.pupilsightActionID=pupilsightAction.pupilsightActionID)
                WHERE pupilsightNotificationListener.pupilsightNotificationEventID=:pupilsightNotificationEventID
                AND pupilsightNotificationEvent.actionName=pupilsightAction.name";

        if ($groupByPerson) {
            $sql .= " GROUP BY pupilsightNotificationListener.pupilsightPersonID";
        } else {
            $sql .= " GROUP BY pupilsightNotificationListener.pupilsightNotificationListenerID";
        }

        return $this->pdo->select($sql, $data);
    }

    public function selectNotificationListenersByScope($pupilsightNotificationEventID, $scopes = array())
    {
        $data = array('pupilsightNotificationEventID' => $pupilsightNotificationEventID);
        $sql = "SELECT DISTINCT pupilsightPersonID FROM pupilsightNotificationListener WHERE pupilsightNotificationEventID=:pupilsightNotificationEventID";

        if (is_array($scopes) && count($scopes) > 0) {
            $sql .= " AND (scopeType='All' ";
            $i = 0;
            foreach ($scopes as $scope) {
                $data['scopeType'.$i] = $scope['type'];
                $data['scopeTypeID'.$i] = $scope['id'];
                $sql .= " OR (scopeType=:scopeType{$i} AND scopeID=:scopeTypeID{$i})";
                $i++;
            }
            $sql .= ")";
        } else {
            $sql .= " AND scopeType='All'";
        }

        return $this->pdo->select($sql, $data);
    }

    public function insertNotificationListener($data)
    {
        $sql = 'INSERT INTO pupilsightNotificationListener SET pupilsightNotificationEventID=:pupilsightNotificationEventID, pupilsightPersonID=:pupilsightPersonID, scopeType=:scopeType, scopeID=:scopeID';

        return $this->pdo->insert($sql, $data);
    }

    public function deleteNotificationListener($pupilsightNotificationListenerID)
    {
        $data = array('pupilsightNotificationListenerID' => $pupilsightNotificationListenerID);
        $sql = 'DELETE FROM pupilsightNotificationListener WHERE pupilsightNotificationListenerID=:pupilsightNotificationListenerID';

        return $this->pdo->delete($sql, $data);
    }

    /* NOTIFICATION PREFERENCES */
    public function getNotificationPreference($pupilsightPersonID)
    {
        $data = array('pupilsightPersonID' => $pupilsightPersonID);
        $sql = "SELECT email, receiveNotificationEmails FROM pupilsightPerson WHERE pupilsightPersonID=:pupilsightPersonID AND receiveNotificationEmails='Y' AND NOT email=''";

        return $this->pdo->selectOne($sql, $data);
    }
}
