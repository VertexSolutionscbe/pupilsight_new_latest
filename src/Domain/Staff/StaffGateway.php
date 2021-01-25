<?php
/*
Pupilsight, Flexible & Open School System
*/

namespace Pupilsight\Domain\Staff;

use Pupilsight\Domain\Traits\TableAware;
use Pupilsight\Domain\QueryCriteria;
use Pupilsight\Domain\QueryableGateway;

/**
 * Staff Gateway
 *
 * @version v16
 * @since   v16
 */
class StaffGateway extends QueryableGateway
{
    use TableAware;

    private static $tableName = 'pupilsightStaff';

    private static $searchableColumns = ['preferredName', 'surname', 'username', 'pupilsightStaff.jobTitle', 'email', 'type', 'phone1'];

    /**
     * Queries the list of users for the Manage Staff page.
     *
     * @param QueryCriteria $criteria
     * @return DataSet
     */
    public function queryAllStaff(QueryCriteria $criteria, $pupilsightSchoolYearID = NULL, $pupilsightProgramID = NULL, $pupilsightDepartmentID = NULL)
    {
        $query = $this
            ->newQuery()
            ->distinct()
            ->from($this->getTableName())
            ->cols([
                'pupilsightPerson.pupilsightPersonID', 'pupilsightPerson.officialName', 'assignstaff_toclasssection.pupilsightMappingID', 'pupilsightProgramClassSectionMapping.pupilsightProgramID', 'pupilsightPerson.title', 'pupilsightPerson.surname', 'pupilsightPerson.email', 'pupilsightPerson.phone1', 'pupilsightPerson.preferredName', 'pupilsightPerson.status', 'pupilsightPerson.username', 'pupilsightPerson.image_240', 'pupilsightStaff.staff_status AS stat',
                'pupilsightStaff.pupilsightStaffID', 'pupilsightPerson.pupilsightPersonID AS stuid', 'pupilsightStaff.initials', 'pupilsightStaff.type', 'pupilsightStaff.jobTitle', 'pupilsightPerson.passwordStrong as stfPassword', 'pupilsightPerson.username as stfUsername', 'pupilsightPerson.dob', 'pupilsightPerson.gender', 'pupilsightPerson.phone2', 'pupilsightPerson.phone3', 'pupilsightPerson.phone4'
            ])
            ->innerJoin('pupilsightPerson', 'pupilsightPerson.pupilsightPersonID=pupilsightStaff.pupilsightPersonID')
            ->leftJoin('assignstaff_toclasssection', 'assignstaff_toclasssection.pupilsightPersonID=pupilsightStaff.pupilsightPersonID')
            ->leftJoin('pupilsightProgramClassSectionMapping', 'pupilsightProgramClassSectionMapping.pupilsightMappingID=assignstaff_toclasssection.pupilsightMappingID')
            ->leftJoin('assignstaff_tosubject', 'assignstaff_tosubject.pupilsightStaffID=pupilsightStaff.pupilsightStaffID');

        if (!$criteria->hasFilter('all')) {
            $query->where('pupilsightPerson.status = "Full"');
        }
        if (!empty($pupilsightProgramID)) {
            $query->where('pupilsightProgramClassSectionMapping.pupilsightProgramID = "' . $pupilsightProgramID . '" ');
        }
        if (!empty($pupilsightDepartmentID)) {
            $query->where('assignstaff_tosubject.pupilsightdepartmentID = "' . $pupilsightDepartmentID . '" ');
        }
        $query->where('pupilsightPerson.pupilsightRoleIDPrimary != "003" ')
            ->where('pupilsightPerson.pupilsightRoleIDPrimary != "004" ')
            ->groupBy(['pupilsightPerson.pupilsightPersonID'])
            ->orderBy(['pupilsightPerson.pupilsightPersonID ASC']);

        $criteria->addFilterRules([
            'type' => function ($query, $type) {
                if ($type == 'other') {
                    return $query
                        ->where('pupilsightStaff.type <> "Teaching"')
                        ->where('pupilsightStaff.type <> "Support"');
                } else {
                    return $query
                        ->where('pupilsightStaff.type = :type')
                        ->bindValue('type', ucfirst($type));
                }
            },

            'status' => function ($query, $status) {
                return $query
                    ->where('pupilsightPerson.status = :status')
                    ->bindValue('status', ucfirst($status));
            },
        ]);

        return $this->runQuery($query, $criteria);
    }

    public function selectStaffByID($pupilsightPersonID, $type = null)
    {
        $data = array('pupilsightPersonID' => $pupilsightPersonID);
        $sql = "SELECT pupilsightPerson.pupilsightPersonID, pupilsightPerson.title, pupilsightPerson.preferredName, pupilsightPerson.surname, pupilsightPerson.image_240, pupilsightStaff.type, pupilsightStaff.jobTitle
                FROM pupilsightStaff 
                JOIN pupilsightPerson ON (pupilsightPerson.pupilsightPersonID=pupilsightStaff.pupilsightPersonID)
                WHERE pupilsightStaff.pupilsightPersonID=:pupilsightPersonID 
                AND pupilsightPerson.status='Full'";

        if (!empty($type)) $sql .= " AND pupilsightStaff.type='Teaching'";

        return $this->db()->select($sql, $data);
    }
    public function getStudentData(QueryCriteria $criteria, $pupilsightSchoolYearID, $pupilsightProgramID,  $pupilsightYearGroupID, $pupilsightRollGroupID)
    {
        //  $pupilsightRoleIDAll = '003';
        if (!empty($pupilsightProgramID)) {

            $query = $this
                ->newQuery()
                ->distinct()
                ->from('pupilsightProgramClassSectionMapping')
                ->cols(['pupilsightProgramClassSectionMapping.pupilsightMappingID AS stuid', 'pupilsightProgram.name AS program', 'pupilsightYearGroup.nameShort AS yearGroup', 'pupilsightRollGroup.nameShort AS rollGroup'])
                ->leftJoin('pupilsightProgram', 'pupilsightProgramClassSectionMapping.pupilsightProgramID=pupilsightProgram.pupilsightProgramID')
                ->leftJoin('pupilsightYearGroup', 'pupilsightProgramClassSectionMapping.pupilsightYearGroupID=pupilsightYearGroup.pupilsightYearGroupID')
                ->leftJoin('pupilsightRollGroup', 'pupilsightProgramClassSectionMapping.pupilsightRollGroupID=pupilsightRollGroup.pupilsightRollGroupID')
                ->where('pupilsightProgramClassSectionMapping.pupilsightSchoolYearID = "' . $pupilsightSchoolYearID . '" ');
            if (!empty($pupilsightProgramID)) {
                $query->where('pupilsightProgramClassSectionMapping.pupilsightProgramID = "' . $pupilsightProgramID . '" ');
            }
            if (!empty($pupilsightYearGroupID)) {
                $query->where('pupilsightProgramClassSectionMapping.pupilsightYearGroupID = "' . $pupilsightYearGroupID . '" ');
            }
            if (!empty($pupilsightRollGroupID)) {
                $rollIds = implode(',', $pupilsightRollGroupID);
                $query->where('pupilsightProgramClassSectionMapping.pupilsightRollGroupID IN (' . $rollIds . ') ');
            }

            $query->orderBy(['pupilsightProgramClassSectionMapping.pupilsightYearGroupID ASC']);
        } else {
            $query = $this
                ->newQuery()
                ->distinct()
                ->from('pupilsightProgramClassSectionMapping')
                ->cols(['pupilsightProgramClassSectionMapping.pupilsightMappingID AS stuid', 'pupilsightProgram.name AS program', 'pupilsightYearGroup.nameShort AS yearGroup', 'pupilsightRollGroup.nameShort AS rollGroup'])
                ->leftJoin('pupilsightProgram', 'pupilsightProgramClassSectionMapping.pupilsightProgramID=pupilsightProgram.pupilsightProgramID')
                ->leftJoin('pupilsightYearGroup', 'pupilsightProgramClassSectionMapping.pupilsightYearGroupID=pupilsightYearGroup.pupilsightYearGroupID')
                ->leftJoin('pupilsightRollGroup', 'pupilsightProgramClassSectionMapping.pupilsightRollGroupID=pupilsightRollGroup.pupilsightRollGroupID');
            $query->orderBy(['pupilsightProgramClassSectionMapping.pupilsightYearGroupID ASC']);
        }
        return $this->runQuery($query, $criteria);
    }
    public function getStaff(QueryCriteria $criteria)
    {
        $query = $this
            ->newQuery()
            ->from($this->getTableName())
            ->cols([
                'pupilsightPerson.pupilsightPersonID', 'pupilsightPerson.title', 'pupilsightPerson.surname', 'pupilsightPerson.preferredName', 'pupilsightPerson.status', 'pupilsightPerson.username', 'pupilsightPerson.image_240',
                'pupilsightStaff.pupilsightStaffID', 'pupilsightPerson.email', 'pupilsightPerson.phone1 AS phone', 'pupilsightStaff.staff_status', 'pupilsightPerson.pupilsightPersonID AS staffid', 'pupilsightStaff.initials', 'pupilsightStaff.type', 'pupilsightStaff.jobTitle'
            ])
            ->innerJoin('pupilsightPerson', 'pupilsightPerson.pupilsightPersonID=pupilsightStaff.pupilsightPersonID')
            ->where('pupilsightPerson.pupilsightRoleIDPrimary != "003" ')
            ->where('pupilsightPerson.pupilsightRoleIDPrimary != "004" ');

        return $this->runQuery($query, $criteria);
    }
    public function getassignedstaff(QueryCriteria $criteria)
    {
        $query = $this
            ->newQuery()
            ->from('assignstaff_toclasssection')
            ->cols([
                'assignstaff_toclasssection.id AS st_id', 'assignstaff_toclasssection.pupilsightMappingID', 'pupilsightProgram.name AS program', 'pupilsightYearGroup.nameShort AS yearGroup', "GROUP_CONCAT(DISTINCT pupilsightPerson.pupilsightPersonID) as pid", 'pupilsightRollGroup.nameShort AS rollGroup'
            ])

            ->leftJoin('pupilsightProgramClassSectionMapping', 'assignstaff_toclasssection.pupilsightMappingID=pupilsightProgramClassSectionMapping.pupilsightMappingID')
            ->leftJoin('pupilsightProgram', 'pupilsightProgramClassSectionMapping.pupilsightProgramID=pupilsightProgram.pupilsightProgramID')
            ->leftJoin('pupilsightYearGroup', 'pupilsightProgramClassSectionMapping.pupilsightYearGroupID=pupilsightYearGroup.pupilsightYearGroupID')
            ->leftJoin('pupilsightRollGroup', 'pupilsightProgramClassSectionMapping.pupilsightRollGroupID=pupilsightRollGroup.pupilsightRollGroupID')
            ->leftJoin('pupilsightPerson', 'assignstaff_toclasssection.pupilsightPersonID=pupilsightPerson.pupilsightPersonID')
            ->groupBy(['pupilsightProgramClassSectionMapping.pupilsightMappingID']);

        $res = $this->runQuery($query, $criteria);
        $data = $res->data;

        foreach ($data as $k => $sd) {
            if(!empty($sd['pid'])){
                $query2 = $this
                    ->newQuery()
                    ->from('pupilsightPerson')
                    ->cols([
                        "GROUP_CONCAT(DISTINCT pupilsightPerson.officialName SEPARATOR ', ') as pname",
                    ])
                    ->where('pupilsightPerson.pupilsightPersonID IN (' . $sd['pid'] . ') ');

                $newdata = $this->runQuery($query2, $criteria);

                // die();
                if (!empty($newdata)) {
                    $data[$k]['name'] = $newdata->data[0]['pname'];
                } else {
                    $data[$k]['name'] = '';
                }
            } else {
                $data[$k]['name'] = '';
            }
        }

        $res->data = $data;
        return $res;
    }

    public function getstdData(QueryCriteria $criteria, $pupilsightProgramID, $pupilsightSchoolYearIDpost, $pupilsightYearGroupID, $pupilsightRollGroupID, $pupilsightDepartmentID, $search, $subType)
    {
        $pupilsightRoleIDAll = '003';
        if ($subType == 'Elective') {
            $query = $this
                ->newQuery()
                ->from('pupilsightPerson')
                ->cols([
                    'pupilsightPerson.pupilsightPersonID AS stuid', 'pupilsightPerson.officialName AS student_name', 'GROUP_CONCAT(assignstudent_tostaff.pupilsightStaffID) AS staffIds'
                ])

                ->leftJoin('assignstudent_tostaff', 'pupilsightPerson.pupilsightPersonID=assignstudent_tostaff.pupilsightPersonID')
                ->leftJoin('pupilsightStudentEnrolment', 'pupilsightPerson.pupilsightPersonID=pupilsightStudentEnrolment.pupilsightPersonID')
                ->leftJoin('pupilsightSchoolYear', 'pupilsightStudentEnrolment.pupilsightSchoolYearID=pupilsightSchoolYear.pupilsightSchoolYearID')
                ->leftJoin('pupilsightRollGroup', 'pupilsightStudentEnrolment.pupilsightRollGroupID=pupilsightRollGroup.pupilsightRollGroupID')
                ->leftJoin('pupilsightProgram', 'pupilsightStudentEnrolment.pupilsightProgramID=pupilsightProgram.pupilsightProgramID')
                ->leftJoin('pupilsightYearGroup', 'pupilsightStudentEnrolment.pupilsightYearGroupID=pupilsightYearGroup.pupilsightYearGroupID')
                ->leftJoin('assign_elective_subjects_tostudents', 'pupilsightPerson.pupilsightPersonID=assign_elective_subjects_tostudents.pupilsightPersonID');

            if (!empty($pupilsightProgramID)) {
                $query->where('pupilsightProgram.pupilsightProgramID = "' . $pupilsightProgramID . '" ');
            }
            if (!empty($pupilsightSchoolYearIDpost)) {
                $query->where('pupilsightStudentEnrolment.pupilsightSchoolYearID = "' . $pupilsightSchoolYearIDpost . '" ');
            }
            if (!empty($pupilsightYearGroupID)) {
                $query->where('pupilsightStudentEnrolment.pupilsightYearGroupID = "' . $pupilsightYearGroupID . '" ');
            }
            if (!empty($pupilsightRollGroupID)) {
                $query->where('pupilsightStudentEnrolment.pupilsightRollGroupID = "' . $pupilsightRollGroupID . '" ');
            }
            if (!empty($pupilsightDepartmentID)) {
                $query->where('assign_elective_subjects_tostudents.pupilsightDepartmentID = "' . $pupilsightDepartmentID . '" ');
            }
            if (!empty($search)) {
                $query->where('pupilsightPerson.officialName LIKE "%' . $search . '%" ')
                    ->orwhere('pupilsightPerson.pupilsightPersonID = "' . $search . '" ');;
            }
            $query->where('pupilsightPerson.pupilsightRoleIDAll = "' . $pupilsightRoleIDAll . '" ')
                ->groupBy(['pupilsightPerson.pupilsightPersonID'])
                ->orderBy(['pupilsightPerson.pupilsightPersonID ASC']);
        } else {

            if (!empty($pupilsightProgramID)) {
                $query = $this
                    ->newQuery()
                    ->from('pupilsightPerson')
                    ->cols([
                        'pupilsightPerson.pupilsightPersonID AS stuid', 'pupilsightPerson.officialName AS student_name', 'GROUP_CONCAT(assignstudent_tostaff.pupilsightStaffID) AS staffIds'
                    ])

                    ->leftJoin('assignstudent_tostaff', 'pupilsightPerson.pupilsightPersonID=assignstudent_tostaff.pupilsightPersonID')
                    ->leftJoin('pupilsightStudentEnrolment', 'pupilsightPerson.pupilsightPersonID=pupilsightStudentEnrolment.pupilsightPersonID')
                    ->leftJoin('pupilsightSchoolYear', 'pupilsightStudentEnrolment.pupilsightSchoolYearID=pupilsightSchoolYear.pupilsightSchoolYearID')
                    ->leftJoin('pupilsightRollGroup', 'pupilsightStudentEnrolment.pupilsightRollGroupID=pupilsightRollGroup.pupilsightRollGroupID')
                    ->leftJoin('pupilsightProgram', 'pupilsightStudentEnrolment.pupilsightProgramID=pupilsightProgram.pupilsightProgramID')
                    ->leftJoin('pupilsightYearGroup', 'pupilsightStudentEnrolment.pupilsightYearGroupID=pupilsightYearGroup.pupilsightYearGroupID');
                // ->leftJoin('pupilsightPerson AS stf', 'assignstudent_tostaff.pupilsightStaffID=stf.pupilsightPersonID');

                if (!empty($pupilsightProgramID)) {
                    $query->where('pupilsightProgram.pupilsightProgramID = "' . $pupilsightProgramID . '" ');
                }
                if (!empty($pupilsightSchoolYearIDpost)) {
                    $query->where('pupilsightStudentEnrolment.pupilsightSchoolYearID = "' . $pupilsightSchoolYearIDpost . '" ');
                }
                if (!empty($pupilsightYearGroupID)) {
                    $query->where('pupilsightStudentEnrolment.pupilsightYearGroupID = "' . $pupilsightYearGroupID . '" ');
                }
                if (!empty($pupilsightRollGroupID)) {
                    $query->where('pupilsightStudentEnrolment.pupilsightRollGroupID = "' . $pupilsightRollGroupID . '" ');
                }

                if (!empty($search)) {
                    $query->where('pupilsightPerson.officialName LIKE "%' . $search . '%" ')
                        ->orwhere('pupilsightPerson.pupilsightPersonID = "' . $search . '" ');;
                }
                $query->where('pupilsightPerson.pupilsightRoleIDAll = "' . $pupilsightRoleIDAll . '" ')
                    ->groupBy(['pupilsightPerson.pupilsightPersonID'])
                    ->orderBy(['pupilsightPerson.pupilsightPersonID ASC']);
                // echo $query;    
            } else {

                $query = $this
                    ->newQuery()
                    ->from('pupilsightPerson')
                    ->cols([
                        'pupilsightPerson.pupilsightPersonID AS stuid', 'pupilsightPerson.officialName AS student_name', "GROUP_CONCAT(DISTINCT assignstudent_tostaff.pupilsightStaffID SEPARATOR ', ') as staff_id", 'assignstudent_tostaff.pupilsightStaffID AS Staffid', 'stf.officialName as staff_name'
                    ])
                    ->leftJoin('assignstudent_tostaff', 'pupilsightPerson.pupilsightPersonID=assignstudent_tostaff.pupilsightPersonID')
                    ->leftJoin('pupilsightPerson AS stf', 'assignstudent_tostaff.pupilsightStaffID=stf.pupilsightPersonID')
                    ->where('pupilsightPerson.pupilsightRoleIDAll = "' . $pupilsightRoleIDAll . '" ')
                    ->groupBy(['pupilsightPerson.pupilsightPersonID'])
                    ->orderBy(['pupilsightPerson.pupilsightPersonID ASC']);
                //echo $query;
            }
        }
        //echo $query;
        $res = $this->runQuery($query, $criteria);
        $data = $res->data;

        foreach ($data as $k => $sd) {
            if (!empty($sd['staffIds'])) {
                $query2 = $this
                    ->newQuery()
                    ->from('pupilsightPerson')
                    ->cols([
                        "GROUP_CONCAT(DISTINCT pupilsightPerson.officialName SEPARATOR ', ') as pname",
                    ])
                    ->where('pupilsightPerson.pupilsightPersonID IN (' . $sd['staffIds'] . ') ');

                $newdata = $this->runQuery($query2, $criteria);

                // die();
                if (!empty($newdata)) {
                    $data[$k]['staff_name'] = $newdata->data[0]['pname'];
                } else {
                    $data[$k]['staff_name'] = '';
                }
            } else {
                $data[$k]['staff_name'] = '';
            }
        }

        $res->data = $data;
        return $res;
    }

    public function getselectedStaff(QueryCriteria $criteria)
    {
        $query = $this
            ->newQuery()
            ->from('assignstaff_tosubject')
            ->cols([
                'assignstaff_tosubject.*', 'assignstaff_tosubject.pupilsightStaffID AS st_id', 'pupilsightPerson.officialName AS fname', "GROUP_CONCAT(DISTINCT pupilsightDepartment.name SEPARATOR ', ') as dep_name"
            ])
            ->leftJoin('pupilsightStaff', 'assignstaff_tosubject.pupilsightStaffID=pupilsightStaff.pupilsightStaffID')
            ->leftJoin('pupilsightPerson', 'pupilsightStaff.pupilsightPersonID=pupilsightPerson.pupilsightPersonID')
            ->leftJoin('pupilsightDepartment', 'assignstaff_tosubject.pupilsightdepartmentID=pupilsightDepartment.pupilsightDepartmentID')
            ->groupBy(['pupilsightPerson.pupilsightPersonID']);
        return $this->runQuery($query, $criteria);
    }

    public function getStaffExportData(QueryCriteria $criteria)
    {
        $query = $this
            ->newQuery()
            ->from($this->getTableName())
            ->cols([
                'pupilsightPerson.pupilsightPersonID', 'pupilsightPerson.preferredName'
            ])
            ->innerJoin('pupilsightPerson', 'pupilsightPerson.pupilsightPersonID=pupilsightStaff.pupilsightPersonID')
            ->where('pupilsightPerson.pupilsightRoleIDPrimary = 002');

        return $this->runQuery($query, $criteria);
    }


    public function getStaffByFilter(QueryCriteria $criteria, $staffIds)
    {
        $query = $this
            ->newQuery()
            ->from('assignstaff_tosubject')
            ->cols([
                'assignstaff_tosubject.*', 'assignstaff_tosubject.pupilsightStaffID AS st_id', 'pupilsightPerson.officialName AS fname', "GROUP_CONCAT(DISTINCT pupilsightDepartment.name SEPARATOR ', ') as dep_name"
            ])
            ->leftJoin('pupilsightStaff', 'assignstaff_tosubject.pupilsightStaffID=pupilsightStaff.pupilsightStaffID')
            ->leftJoin('pupilsightPerson', 'pupilsightStaff.pupilsightPersonID=pupilsightPerson.pupilsightPersonID')
            ->leftJoin('pupilsightDepartment', 'assignstaff_tosubject.pupilsightdepartmentID=pupilsightDepartment.pupilsightDepartmentID');
        if (!empty($staffIds)) {
            $query->WHERE('assignstaff_tosubject.pupilsightStaffID IN (' . $staffIds . ') ');
        }

        $query->groupBy(['pupilsightPerson.pupilsightPersonID']);
        // echo $query;
        // die();
        return $this->runQuery($query, $criteria);
    }

    public function getFeedbackCategory(QueryCriteria $criteria)
    {
        $query = $this
            ->newQuery()
            ->from('pupilsightFeedbackCategory')
            ->cols([
                'pupilsightFeedbackCategory.*'
            ])
            ->orderBy(['pupilsightFeedbackCategory.id DESC']);

        return $this->runQuery($query, $criteria, TRUE);
    }

    public function getFeedback(QueryCriteria $criteria, $staff_id)
    {
        $query = $this
            ->newQuery()
            ->from('pupilsightFeedback')
            ->cols([
                'pupilsightFeedback.*', 'pupilsightFeedbackCategory.name as catName', 'pupilsightDepartment.name as subjectName', 'pupilsightProgram.name AS program', 'pupilsightYearGroup.name AS class', 'pupilsightRollGroup.name AS section'
            ])
            ->leftJoin('pupilsightFeedbackCategory', 'pupilsightFeedback.category_id=pupilsightFeedbackCategory.id')
            ->leftJoin('pupilsightProgram', 'pupilsightFeedback.pupilsightProgramID=pupilsightProgram.pupilsightProgramID')
            ->leftJoin('pupilsightYearGroup', 'pupilsightFeedback.pupilsightYearGroupID=pupilsightYearGroup.pupilsightYearGroupID')
            ->leftJoin('pupilsightRollGroup', 'pupilsightFeedback.pupilsightRollGroupID=pupilsightRollGroup.pupilsightRollGroupID')
            ->leftJoin('pupilsightDepartment', 'pupilsightFeedback.pupilsightdepartmentID=pupilsightDepartment.pupilsightDepartmentID')
            ->where('pupilsightFeedback.pupilsightPersonID = ' . $staff_id . ' ')
            ->orderBy(['pupilsightFeedback.id DESC']);

        return $this->runQuery($query, $criteria, TRUE);
    }
}
