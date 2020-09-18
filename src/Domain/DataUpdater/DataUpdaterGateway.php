<?php
/*
Pupilsight, Flexible & Open School System
*/

namespace Pupilsight\Domain\DataUpdater;

use Pupilsight\Domain\Gateway;

/**
 * Data Updater Gateway
 *
 * @version v16
 * @since   v16
 */
class DataUpdaterGateway extends Gateway
{
    /**
     * Gets a list of users this person can update data for, checking by family. Always returns the user themself even if not in a family.
     * 
     * @param string $pupilsightPersonID
     * @return \PDOStatement
     */
    public function selectUpdatableUsersByPerson($pupilsightPersonID)
    {
        $data = array('pupilsightPersonID' => $pupilsightPersonID);
        $sql = "
        (SELECT GROUP_CONCAT(pupilsightFamily.pupilsightFamilyID ORDER BY pupilsightFamily.name SEPARATOR ',') as pupilsightFamilyID, pupilsightPerson.surname, pupilsightPerson.preferredName, pupilsightPerson.image_240, pupilsightPerson.pupilsightPersonID, pupilsightPerson.dateStart, 0 as sequenceNumber
            FROM pupilsightPerson 
            LEFT JOIN pupilsightFamilyAdult ON (pupilsightFamilyAdult.pupilsightPersonID=pupilsightPerson.pupilsightPersonID)
            LEFT JOIN pupilsightFamily ON (pupilsightFamilyAdult.pupilsightFamilyID=pupilsightFamily.pupilsightFamilyID)
            WHERE pupilsightPerson.pupilsightPersonID=:pupilsightPersonID AND pupilsightPerson.status='Full' GROUP BY pupilsightPerson.pupilsightPersonID)
        UNION ALL 
        (SELECT pupilsightFamilyAdult.pupilsightFamilyID, child.surname, child.preferredName, child.image_240, child.pupilsightPersonID, child.dateStart, 1 as sequenceNumber
            FROM pupilsightFamilyAdult 
            JOIN pupilsightFamily ON (pupilsightFamilyAdult.pupilsightFamilyID=pupilsightFamily.pupilsightFamilyID) 
            JOIN pupilsightFamilyChild ON (pupilsightFamilyChild.pupilsightFamilyID=pupilsightFamily.pupilsightFamilyID)
            JOIN pupilsightPerson as child ON (pupilsightFamilyChild.pupilsightPersonID=child.pupilsightPersonID) 
            WHERE pupilsightFamilyAdult.pupilsightPersonID=:pupilsightPersonID 
            AND pupilsightFamilyAdult.childDataAccess='Y' AND child.status='Full') 
        UNION ALL 
        (SELECT pupilsightFamily.pupilsightFamilyID, adult.surname, adult.preferredName, adult.image_240, adult.pupilsightPersonID, adult.dateStart, 2 as sequenceNumber
            FROM pupilsightFamilyAdult 
            JOIN pupilsightFamily ON (pupilsightFamilyAdult.pupilsightFamilyID=pupilsightFamily.pupilsightFamilyID)
            JOIN pupilsightFamilyAdult as familyAdult ON (familyAdult.pupilsightFamilyID=pupilsightFamily.pupilsightFamilyID AND familyAdult.pupilsightPersonID<>:pupilsightPersonID)
            JOIN pupilsightPerson as adult ON (familyAdult.pupilsightPersonID=adult.pupilsightPersonID) 
            WHERE pupilsightFamilyAdult.pupilsightPersonID=:pupilsightPersonID AND adult.status='Full')
        ORDER BY sequenceNumber, surname, preferredName
        ";

        return $this->db()->executeQuery($data, $sql);
    }

    /**
     * Gets a list of data updates and the last updated timestamp for a given user.
     * 
     * @param string $pupilsightPersonID
     * @return \PDOStatement
     */
    public function selectDataUpdatesByPerson($pupilsightPersonID, $pupilsightPersonIDSource = '')
    {
        $data = array('pupilsightPersonID' => $pupilsightPersonID, 'pupilsightPersonIDSource' => $pupilsightPersonIDSource);
        $sql = "
        (SELECT 'Personal' as type, pupilsightPerson.pupilsightPersonID as id, 'pupilsightPersonID' as idType, IFNULL(timestamp, 0) as lastUpdated, '' as name
            FROM pupilsightPerson 
            LEFT JOIN pupilsightPersonUpdate ON (pupilsightPersonUpdate.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) 
            WHERE pupilsightPerson.pupilsightPersonID=:pupilsightPersonID ORDER BY timestamp DESC LIMIT 1)
        UNION ALL
        (SELECT 'Medical' as type, pupilsightPerson.pupilsightPersonID as id, 'pupilsightPersonID' as idType, IFNULL(timestamp, 0) as lastUpdated, '' as name
            FROM pupilsightPerson 
            JOIN pupilsightRole ON (FIND_IN_SET(pupilsightRole.pupilsightRoleID, pupilsightPerson.pupilsightRoleIDAll))
            LEFT JOIN pupilsightPersonMedicalUpdate ON (pupilsightPersonMedicalUpdate.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) 
            WHERE pupilsightPerson.pupilsightPersonID=:pupilsightPersonID AND pupilsightRole.category='Student'
            ORDER BY timestamp DESC LIMIT 1)
        UNION ALL
        (SELECT 'Finance' as type, pupilsightFinanceInvoicee.pupilsightFinanceInvoiceeID as id, 'pupilsightFinanceInvoiceeID' as idType, IFNULL(timestamp, 0) as lastUpdated, '' as name
            FROM pupilsightPerson 
            JOIN pupilsightFinanceInvoicee ON (pupilsightFinanceInvoicee.pupilsightPersonID=pupilsightPerson.pupilsightPersonID)
            LEFT JOIN pupilsightFinanceInvoiceeUpdate ON (pupilsightFinanceInvoiceeUpdate.pupilsightFinanceInvoiceeID=pupilsightFinanceInvoicee.pupilsightFinanceInvoiceeID) 
            WHERE pupilsightPerson.pupilsightPersonID=:pupilsightPersonID 
            ORDER BY timestamp DESC LIMIT 1)    
        UNION ALL
        (SELECT 'Family' as type, pupilsightFamilyAdult.pupilsightFamilyID as id, 'pupilsightFamilyID' as idType, IFNULL(timestamp, 0) as lastUpdated, pupilsightFamily.name
            FROM pupilsightFamilyAdult 
            JOIN pupilsightFamily ON (pupilsightFamily.pupilsightFamilyID=pupilsightFamilyAdult.pupilsightFamilyID)
            LEFT JOIN pupilsightFamilyUpdate ON (pupilsightFamilyUpdate.pupilsightFamilyID=pupilsightFamilyAdult.pupilsightFamilyID) 
            WHERE pupilsightFamilyAdult.pupilsightPersonID=:pupilsightPersonID ORDER BY timestamp DESC LIMIT 1)
        UNION ALL
        (SELECT 'Family' as type, pupilsightFamilyChild.pupilsightFamilyID as id, 'pupilsightFamilyID' as idType, IFNULL(timestamp, 0) as lastUpdated, pupilsightFamily.name
            FROM pupilsightFamilyChild 
            JOIN pupilsightFamily ON (pupilsightFamily.pupilsightFamilyID=pupilsightFamilyChild.pupilsightFamilyID)
            JOIN pupilsightFamilyAdult ON (pupilsightFamilyAdult.pupilsightFamilyID=pupilsightFamily.pupilsightFamilyID)
            LEFT JOIN pupilsightFamilyUpdate ON (pupilsightFamilyUpdate.pupilsightFamilyID=pupilsightFamilyChild.pupilsightFamilyID) 
            WHERE pupilsightFamilyChild.pupilsightPersonID=:pupilsightPersonID AND pupilsightFamilyAdult.pupilsightPersonID=:pupilsightPersonIDSource 
            ORDER BY timestamp DESC LIMIT 1)
        ";

        return $this->db()->executeQuery($data, $sql);
    }

    public function countAllRequiredUpdatesByPerson($pupilsightPersonID)
    {
        $updatablePeople = $this->selectUpdatableUsersByPerson($pupilsightPersonID);

        if ($updatablePeople->rowCount() == 0) return 0;

        $cutoffDate = getSettingByScope($this->db()->getConnection(), 'Data Updater', 'cutoffDate');
        $requiredUpdatesByType = getSettingByScope($this->db()->getConnection(), 'Data Updater', 'requiredUpdatesByType');
        $requiredUpdatesByType = explode(',', $requiredUpdatesByType);

        if (empty($requiredUpdatesByType) || empty($cutoffDate)) return 0;
        
        $count = 0;

        // Loop over each updatable person to look for required updates
        foreach ($updatablePeople as $person) {
            $dataUpdatesByType = $this->selectDataUpdatesByPerson($person['pupilsightPersonID'], $pupilsightPersonID)->fetchGrouped();

            foreach ($requiredUpdatesByType as $type) {
                // Skip data update types not applicable to this user
                if (empty($dataUpdatesByType[$type])) continue;

                // Loop over each type of data update and check the last update
                foreach ($dataUpdatesByType[$type] as $dataUpdate) {
                    if (empty($dataUpdate['lastUpdated']) || $dataUpdate['lastUpdated'] < $cutoffDate) {
                        $count++;
                    }
                }
            }
        }

        return $count;
    }
}
