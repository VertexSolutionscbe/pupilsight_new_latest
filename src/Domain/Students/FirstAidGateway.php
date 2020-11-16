<?php
/*
Pupilsight, Flexible & Open School System
*/

namespace Pupilsight\Domain\Students;

use Pupilsight\Domain\Traits\TableAware;
use Pupilsight\Domain\QueryCriteria;
use Pupilsight\Domain\QueryableGateway;

/**
 * @version v16
 * @since   v16
 */
class FirstAidGateway extends QueryableGateway
{
    use TableAware;

    private static $tableName = 'pupilsightFirstAid';

    private static $searchableColumns = [''];
    
    /**
     * @param QueryCriteria $criteria
     * @return DataSet
     */
    public function queryFirstAidBySchoolYear(QueryCriteria $criteria, $pupilsightSchoolYearID, $pupilsightProgramID=NULL, $pupilsightYearGroupID=NULL, $pupilsightRollGroupID=NULL,$search=NULL)
    {
        if(!empty($_SESSION['firstAid_search'])){
            $pupilsightProgramID = $_SESSION['firstAid_search']['pupilsightProgramID'];
            $pupilsightYearGroupID = $_SESSION['firstAid_search']['pupilsightYearGroupID'];
            $pupilsightRollGroupID = $_SESSION['firstAid_search']['pupilsightRollGroupID'];
            $search = $_SESSION['firstAid_search']['search'];
        } 
        $query = $this
            ->newQuery()
            ->from($this->getTableName())
            ->cols([
                'pupilsightFirstAidID', 'pupilsightFirstAid.date', 'pupilsightFirstAid.timeIn', 'pupilsightFirstAid.timeOut', 'pupilsightFirstAid.description', 'pupilsightFirstAid.actionTaken', 'pupilsightFirstAid.followUp', 'pupilsightFirstAid.date', 'patient.surname AS surnamePatient', 'patient.preferredName AS preferredNamePatient', 'pupilsightFirstAid.pupilsightPersonIDPatient', 'pupilsightRollGroup.name as rollGroup', 'firstAider.title', 'firstAider.surname AS surnameFirstAider', 'firstAider.preferredName AS preferredNameFirstAider'
            ])
            ->innerJoin('pupilsightPerson AS patient', 'pupilsightFirstAid.pupilsightPersonIDPatient=patient.pupilsightPersonID')
            ->innerJoin('pupilsightStudentEnrolment', 'patient.pupilsightPersonID=pupilsightStudentEnrolment.pupilsightPersonID')
            ->innerJoin('pupilsightProgram', 'pupilsightStudentEnrolment.pupilsightProgramID=pupilsightProgram.pupilsightProgramID')
            ->innerJoin('pupilsightYearGroup', 'pupilsightStudentEnrolment.pupilsightYearGroupID=pupilsightYearGroup.pupilsightYearGroupID')
            ->innerJoin('pupilsightRollGroup', 'pupilsightStudentEnrolment.pupilsightRollGroupID=pupilsightRollGroup.pupilsightRollGroupID')
            ->leftJoin('pupilsightPerson AS firstAider', 'pupilsightFirstAid.pupilsightPersonIDFirstAider=firstAider.pupilsightPersonID')
            ->where('pupilsightStudentEnrolment.pupilsightSchoolYearID = '.$pupilsightSchoolYearID.' ');

        if (!empty($pupilsightProgramID)) {
            $query->where('pupilsightStudentEnrolment.pupilsightProgramID = "' . $pupilsightProgramID . '" ');
        }
        
        if (!empty($pupilsightYearGroupID)) {
            $query->where('pupilsightStudentEnrolment.pupilsightYearGroupID = "' . $pupilsightYearGroupID . '" ');
        }
        if (!empty($pupilsightRollGroupID)) {
            $query->where('pupilsightStudentEnrolment.pupilsightRollGroupID = "' . $pupilsightRollGroupID . '" ');
        }
        if (!empty($search)) {
            $query->where('patient.officialName LIKE "%' . $search . '%" ')
            ->orwhere('patient.admission_no = "' . $search . '" ');
        }
        
        //if(!empty($criteria->))

        // $criteria->addFilterRules([
        //     'student' => function ($query, $pupilsightPersonID) {
        //         return $query
        //             ->where('pupilsightFirstAid.pupilsightPersonIDPatient = :pupilsightPersonID')
        //             ->bindValue('pupilsightPersonID', $pupilsightPersonID);
        //     },

            // 'program' => function ($query, $pupilsightProgramID) {
            //     return $query
            //         ->where('pupilsightStudentEnrolment.pupilsightProgramID = '.$pupilsightProgramID.'');
            //         //->bindValue('pupilsightProgramID', $pupilsightProgramID);
            // },

            // 'rollGroup' => function ($query, $pupilsightRollGroupID) {
            //     return $query
            //         ->where('pupilsightStudentEnrolment.pupilsightRollGroupID = '.$pupilsightRollGroupID.' ');
            //         //->bindValue('pupilsightRollGroupID', $pupilsightRollGroupID);
            // },

            // 'yearGroup' => function ($query, $pupilsightYearGroupID) {
            //     return $query
            //         ->where('pupilsightStudentEnrolment.pupilsightYearGroupID = '.$pupilsightYearGroupID.'');
            //         //->bindValue('pupilsightYearGroupID', $pupilsightYearGroupID);
            // },
        //]);
        //echo $query;
        return $this->runQuery($query, $criteria);
    }
}
