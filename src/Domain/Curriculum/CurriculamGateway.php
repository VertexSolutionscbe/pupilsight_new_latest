<?php
/*
Pupilsight, Flexible & Open School System
*/

namespace Pupilsight\Domain\Curriculum;

use Pupilsight\Domain\Traits\TableAware;
use Pupilsight\Domain\QueryCriteria;
use Pupilsight\Domain\QueryableGateway;
use Pupilsight\Domain\DBQuery;

/**
 * School Year Gateway
 *
 * @version v17
 * @since   v17
 */
class CurriculamGateway extends QueryableGateway
{
    use TableAware;

    private static $tableName = 'ac_manage_skill';

    private static $searchableColumns = ['name', 'code'];

    public function getAllcurriculamRemarks(QueryCriteria $criteria)
    {
        //SELECT * FROM `acRemarks` WHERE ,`id`,`remarkcode`,`description`,`pupilsightDepartmentID`,`skill` 
        $query = $this
            ->newQuery()
            ->from('acRemarks')
            ->cols([
                'acRemarks.*', 'pupilsightDepartment.name AS subject', 'ac_manage_skill.name AS skillname'
            ])
            ->leftJoin('ac_manage_skill', 'ac_manage_skill.ID=acRemarks.skill')
            ->leftJoin('pupilsightDepartment ', 'acRemarks.pupilsightDepartmentID=pupilsightDepartment.pupilsightDepartmentID');

        return $this->runQuery($query, $criteria);
    }
    
    
    public function getstudent_subject_assigned_data(QueryCriteria $criteria, $pupilsightSchoolYearID, $pupilsightProgramID, $pupilsightYearGroupID, $pupilsightRollGroupID, $pupilsightPersonID)
    {
        if(!empty($pupilsightProgramID) && !empty($pupilsightYearGroupID) && !empty($pupilsightRollGroupID)){
            //print_r($criteria);
            $pupilsightRoleIDAll = '003';
            $query = $this
                ->newQuery()
                ->from('pupilsightPerson')
                ->cols([
                    'pupilsightPerson.pupilsightPersonID AS stuid', 'pupilsightPerson.pupilsightPersonID', 'pupilsightPerson.officialName AS student_name', 'pupilsightPerson.admission_no','pupilsightProgram.name as progname','pupilsightYearGroup.name as clsname','pupilsightRollGroup.name as secname'
                ])
                ->leftJoin('pupilsightStudentEnrolment', 'pupilsightPerson.pupilsightPersonID=pupilsightStudentEnrolment.pupilsightPersonID')
                ->leftJoin('pupilsightRollGroup', 'pupilsightStudentEnrolment.pupilsightRollGroupID=pupilsightRollGroup.pupilsightRollGroupID')
                ->leftJoin('pupilsightYearGroup', 'pupilsightStudentEnrolment.pupilsightYearGroupID=pupilsightYearGroup.pupilsightYearGroupID')
                ->leftJoin('pupilsightProgram', 'pupilsightStudentEnrolment.pupilsightProgramID=pupilsightProgram.pupilsightProgramID');
            if (!empty($pupilsightProgramID)) {
                $query->where('pupilsightStudentEnrolment.pupilsightProgramID = "' . $pupilsightProgramID . '" ');
            }
            if (!empty($pupilsightSchoolYearID)) {
                $query->where('pupilsightStudentEnrolment.pupilsightSchoolYearID = "' . $pupilsightSchoolYearID . '" ');
            }
            if (!empty($pupilsightYearGroupID)) {
                $query->where('pupilsightStudentEnrolment.pupilsightYearGroupID = "' . $pupilsightYearGroupID . '" ');
            }
            if (!empty($pupilsightRollGroupID)) {
                $query->where('pupilsightStudentEnrolment.pupilsightRollGroupID = "' . $pupilsightRollGroupID . '" ');
            }
            if (!empty($pupilsightPersonID)) {
                $query->where('pupilsightStudentEnrolment.pupilsightPersonID = "' . $pupilsightPersonID . '" ');
            }

            $query->where('pupilsightPerson.pupilsightRoleIDAll = "' . $pupilsightRoleIDAll . '" ')
                ->groupBy(['pupilsightPerson.pupilsightPersonID'])
                ->orderBy(['pupilsightPerson.pupilsightPersonID ASC']);
            //  echo $query;    
            $res = $this->runQuery($query, $criteria);
            $data = $res->data;

            $res->data = $data;
            return $res;
        }
    }

    public function getAllgeneralTestMaster(QueryCriteria $criteria, $pupilsightSchoolYearID)
    {
        $query = $this
            ->newQuery()
            ->from('examinationTestMaster')
            ->cols([
                'examinationTestMaster.*', 'pupilsightSchoolYear.name as academic_year'
            ])
            ->leftJoin('pupilsightSchoolYear', 'examinationTestMaster.pupilsightSchoolYearID=pupilsightSchoolYear.pupilsightSchoolYearID');
            if (!empty($pupilsightSchoolYearID)) {
                $query->where('examinationTestMaster.pupilsightSchoolYearID = "' . $pupilsightSchoolYearID . '" ');
            }
            $query->orderBy(['examinationTestMaster.id DESC']);
            //echo $query;

        return $this->runQuery($query, $criteria, true);
    }

    public function getAllgeneraltest(QueryCriteria $criteria, $pupilsightSchoolYearIDpost, $pupilsightProgramID, $pupilsightYearGroupID, $pupilsightRollGroupID)
    {
        //  `examinationTest` WHERE 1,`pupilsightSchoolYearID`,`name`,`code`
        $query = $this
            ->newQuery()
            ->from('examinationTest')
            ->cols([
                'examinationTest.*', 'pupilsightSchoolYear.name as academic_year','pupilsightYearGroup.name as classname', 'pupilsightProgram.name as progname'
            ])
            ->leftJoin('examinationTestAssignClass', 'examinationTest.id=examinationTestAssignClass.test_id')
            ->leftJoin('pupilsightSchoolYear', 'examinationTest.pupilsightSchoolYearID=pupilsightSchoolYear.pupilsightSchoolYearID')
            ->leftJoin('pupilsightProgram', 'examinationTestAssignClass.pupilsightProgramID=pupilsightProgram.pupilsightProgramID')
            ->leftJoin('pupilsightYearGroup', 'examinationTestAssignClass.pupilsightYearGroupID=pupilsightYearGroup.pupilsightYearGroupID');
            if (!empty($pupilsightSchoolYearIDpost)) {
                $query->where('examinationTest.pupilsightSchoolYearID = "' . $pupilsightSchoolYearIDpost . '" ');
            }
            if (!empty($pupilsightProgramID)) {
                $query->where('examinationTestAssignClass.pupilsightProgramID = "' . $pupilsightProgramID . '" ');
            }
            
            if (!empty($pupilsightYearGroupID)) {
                $query->where('examinationTestAssignClass.pupilsightYearGroupID = "' . $pupilsightYearGroupID . '" ');
            }
            if (!empty($pupilsightRollGroupID)) {
                $query->where('examinationTestAssignClass.pupilsightRollGroupID = "' . $pupilsightRollGroupID . '" ');
            }
            $query->orderby(['examinationTest.id DESC']);
            //echo $query;

        return $this->runQuery($query, $criteria, true);
    }

    public function getSelect(QueryCriteria $criteria, $pupilsightProgramID, $pupilsightYearGroupID, $eid)
    {
        $query = $this
            ->newQuery()
            ->distinct()
            ->from('pupilsightProgramClassSectionMapping')
            ->cols(['pupilsightProgramClassSectionMapping.pupilsightRollGroupID AS id,pupilsightProgramClassSectionMapping.pupilsightYearGroupID, pupilsightRollGroup.nameShort AS rollGroup'])
            ->leftJoin('pupilsightRollGroup', 'pupilsightProgramClassSectionMapping.pupilsightRollGroupID=pupilsightRollGroup.pupilsightRollGroupID')
            ->where('pupilsightProgramClassSectionMapping.pupilsightProgramID = "' . $pupilsightProgramID . '" ')
            ->where('pupilsightProgramClassSectionMapping.pupilsightYearGroupID = "' . $pupilsightYearGroupID . '" ');

        $res = $this->runQuery($query, $criteria);
        $data = $res->data;
        foreach ($data as $k => $d) {
            $secId =  $d['id'];

            $query2 = $this
                ->newQuery()
                ->from('ac_elective_group_section')
                ->cols([
                    'ac_elective_group_section.id'
                ])
                ->where('ac_elective_group_section.ac_elective_group_id = "' . $eid . '" ')
                ->where('ac_elective_group_section.pupilsightRollGroupID = "' . $secId . '" ');

            $newdata = $this->runQuery($query2, $criteria);
            if (!empty($eid) && !empty($newdata->data[0]['id'])) {
                $data[$k]['checked'] = '1';
            } else {
                $data[$k]['checked'] = '';
            }
        }

        $res->data = $data;
        return $res;
    }

    public function getElectiveGrp(QueryCriteria $criteria, $pupilsightSchoolYearID, $pupilsightProgramID, $pupilsightYearGroupID)
    {
        $query = $this
            ->newQuery()
            ->from('ac_elective_group')
            ->cols([
                'ac_elective_group.*'
            ])
            ->where('ac_elective_group.pupilsightSchoolYearID = "' . $pupilsightSchoolYearID . '" ')
            ->where('ac_elective_group.pupilsightProgramID = "' . $pupilsightProgramID . '" ')
            ->where('ac_elective_group.pupilsightYearGroupID = "' . $pupilsightYearGroupID . '" ');
        return $this->runQuery($query, $criteria);
    }

    public function getSub(QueryCriteria $criteria, $pupilsightSchoolYearID, $pupilsightProgramID, $pupilsightYearGroupID, $eid)
    {
        $query = $this
            ->newQuery()
            ->from('subjectToClassCurriculum')
            ->cols([
                'subjectToClassCurriculum.subject_display_name AS name ,subjectToClassCurriculum.pupilsightDepartmentID AS id'
            ])
            ->where('subjectToClassCurriculum.subject_type = "Elective" ')
            ->where('subjectToClassCurriculum.pupilsightSchoolYearID = "' . $pupilsightSchoolYearID . '" ')
            ->where('subjectToClassCurriculum.pupilsightProgramID = "' . $pupilsightProgramID . '" ')
            ->where('subjectToClassCurriculum.pupilsightYearGroupID = "' . $pupilsightYearGroupID . '" ')
            ->groupby(['subjectToClassCurriculum.pupilsightDepartmentID'])
            ->orderby(['subjectToClassCurriculum.pupilsightDepartmentID DESC']);

        $res = $this->runQuery($query, $criteria);
        $data = $res->data;
        foreach ($data as $k => $d) {
            $secId =  $d['id'];

            $query2 = $this
                ->newQuery()
                ->from('ac_elective_group_subjects')
                ->cols([
                    'ac_elective_group_subjects.id'
                ])
                ->where('ac_elective_group_subjects.ac_elective_group_id = "' . $eid . '" ')
                ->where('ac_elective_group_subjects.pupilsightDepartmentID = "' . $secId . '" ');

            $newdata = $this->runQuery($query2, $criteria);
            if (!empty($eid) && !empty($newdata->data[0]['id'])) {
                $data[$k]['checked'] = '1';
            } else {
                $data[$k]['checked'] = '';
            }
        }

        $res->data = $data;
        return $res;
    }

    public function getStudentCoreSubjectClassWise(QueryCriteria $criteria, $pupilsightSchoolYearID, $pupilsightProgramID, $pupilsightYearGroupID)
    {
        $query = $this
            ->newQuery()
            ->from('assign_core_subjects_toclass')
            ->cols([
                'pupilsightDepartment.pupilsightDepartmentID', 'pupilsightDepartment.name', 'pupilsightDepartment.type', 'pupilsightDepartment.nameShort'
            ])
            ->leftJoin('pupilsightProgramClassSectionMapping', 'assign_core_subjects_toclass.pupilsightYearGroupID=pupilsightProgramClassSectionMapping.pupilsightYearGroupID')
            ->leftJoin('pupilsightDepartment', 'assign_core_subjects_toclass.pupilsightDepartmentID=pupilsightDepartment.pupilsightDepartmentID')
            ->where('assign_core_subjects_toclass.pupilsightYearGroupID = "' . $pupilsightYearGroupID . '" ')
            ->where('assign_core_subjects_toclass.pupilsightProgramID = "' . $pupilsightProgramID . '" ')
            ->groupBy(['assign_core_subjects_toclass.pupilsightDepartmentID']);

        $res = $this->runQuery($query, $criteria);
        $data = $res->data;
        $coredata = array();
        if (!empty($data)) {
            foreach ($data as $k => $d) {
                $departmentId = $d['pupilsightDepartmentID'];
                $query2 = $this
                    ->newQuery()
                    ->from('subjectToClassCurriculum')
                    ->cols([
                        'subjectToClassCurriculum.subject_display_name', 'subjectToClassCurriculum.subject_type', 'subjectToClassCurriculum.di_mode', 'subjectToClassCurriculum.id'
                    ])
                    ->where('subjectToClassCurriculum.pupilsightSchoolYearID = "' . $pupilsightSchoolYearID . '" ')
                    ->where('subjectToClassCurriculum.pupilsightProgramID = "' . $pupilsightProgramID . '" ')
                    ->where('subjectToClassCurriculum.pupilsightDepartmentID = "' . $departmentId . '" ')
                    ->where('subjectToClassCurriculum.pupilsightYearGroupID = "' . $pupilsightYearGroupID . '" ')
                    ->where('subjectToClassCurriculum.subject_type = "Core" ');
                $newdata = $this->runQuery($query2, $criteria);
                if (!empty($newdata->data[0]['id'])) {
                    $data[$k]['subject_display_name'] = $newdata->data[0]['subject_display_name'];
                    $data[$k]['subject_type'] = $newdata->data[0]['subject_type'];
                } else {
                    $data[$k]['subject_display_name'] = '';
                    $data[$k]['subject_type'] = '';
                }
                if ($data[$k]['subject_type'] == 'Core') {
                    $coredata = $data;
                } else {
                    unset($data[$k]);
                }
            }
        }
        // echo '<pre>'; 
        // print_r($coredata);
        // echo '</pre>';
        // die(); 

        $res->data = $coredata;
        return $res;
    }

    public function getStudentElectiveSubjectClassSectionWise(QueryCriteria $criteria, $pupilsightSchoolYearID, $pupilsightProgramID, $pupilsightYearGroupID, $pupilsightRollGroupID)
    {
        $query = $this
            ->newQuery()
            ->from('ac_elective_group')
            ->cols([
                'ac_elective_group.*'
            ])
            ->where('ac_elective_group.pupilsightYearGroupID = "' . $pupilsightYearGroupID . '" ')
            ->where('ac_elective_group.pupilsightProgramID = "' . $pupilsightProgramID . '" ');

        $res = $this->runQuery($query, $criteria);
        $data = $res->data;

        $electivedata = array();
        if (!empty($data)) {
            foreach ($data as $k => $d) {
                $electiveId = $d['id'];
                $query2 = $this
                    ->newQuery()
                    ->from('ac_elective_group_section')
                    ->cols([
                        'ac_elective_group_section.id', 'GROUP_CONCAT(ac_elective_group_section.pupilsightRollGroupID) AS secId'
                    ])
                    ->where('ac_elective_group_section.ac_elective_group_id = "' . $electiveId . '" ');
                $newdata = $this->runQuery($query2, $criteria);
                if (!empty($newdata->data[0]['id'])) {

                    $HiddenProducts = explode(',', $newdata->data[0]['secId']);
                    if (in_array($pupilsightRollGroupID, $HiddenProducts)) {
                        $query3 = $this
                            ->newQuery()
                            ->from('ac_elective_group_subjects')
                            ->cols([
                                'ac_elective_group_subjects.id', 'pupilsightDepartment.pupilsightDepartmentID', 'pupilsightDepartment.name', 'pupilsightDepartment.type', 'pupilsightDepartment.nameShort'
                            ])
                            ->leftJoin('pupilsightDepartment', 'ac_elective_group_subjects.pupilsightDepartmentID=pupilsightDepartment.pupilsightDepartmentID')
                            ->where('ac_elective_group_subjects.ac_elective_group_id = "' . $electiveId . '" ')
                            ->where('ac_elective_group_subjects.pupilsightDepartmentID != "" ');
                        $subjectdata = $this->runQuery($query3, $criteria);
                        if (!empty($subjectdata)) {
                            foreach ($subjectdata as $kd => $sd) {
                                $data[$k]['elective'][$kd]['pupilsightDepartmentID'] = $sd['pupilsightDepartmentID'];
                                $data[$k]['elective'][$kd]['subject_display_name'] = $sd['name'];
                            }
                        } else {
                            $data[$k]['elective']['subject_display_name'] = '';
                            $data[$k]['elective']['pupilsightDepartmentID'] = '';
                        }
                        $electivedata = $data;
                    } else {
                        unset($data[$k]);
                    }
                } else {
                    $query3 = $this
                        ->newQuery()
                        ->from('ac_elective_group_subjects')
                        ->cols([
                            'ac_elective_group_subjects.id', 'pupilsightDepartment.pupilsightDepartmentID', 'pupilsightDepartment.name', 'pupilsightDepartment.type', 'pupilsightDepartment.nameShort'
                        ])
                        ->leftJoin('pupilsightDepartment', 'ac_elective_group_subjects.pupilsightDepartmentID=pupilsightDepartment.pupilsightDepartmentID')
                        ->where('ac_elective_group_subjects.ac_elective_group_id = "' . $electiveId . '" ')
                        ->where('ac_elective_group_subjects.pupilsightDepartmentID != "" ');
                    $subjectdata = $this->runQuery($query3, $criteria);
                    if (!empty($subjectdata)) {
                        foreach ($subjectdata as $kd => $sd) {
                            $data[$k]['elective'][$kd]['pupilsightDepartmentID'] = $sd['pupilsightDepartmentID'];
                            $data[$k]['elective'][$kd]['subject_display_name'] = $sd['name'];
                        }
                    } else {
                        $data[$k]['elective']['subject_display_name'] = '';
                        $data[$k]['elective']['pupilsightDepartmentID'] = '';
                    }
                    $electivedata = $data;
                }
            }
        }
        //      echo '<pre>'; 
        //     print_r($electivedata);
        //     echo '</pre>';
        //    die(); 

        $res->data = $electivedata;
        return $res;
    }

    public function getAllGradeSystem(QueryCriteria $criteria)
    {
        //  `examinationTest` WHERE 1,`pupilsightSchoolYearID`,`name`,`code`
        $query = $this
            ->newQuery()
            ->from('examinationGradeSystem')
            ->cols([
                'examinationGradeSystem.*'
            ]);

        return $this->runQuery($query, $criteria, TRUE);
    }

    public function getAllGradeConfigure(QueryCriteria $criteria, $id)
    {
        //  `examinationTest` WHERE 1,`pupilsightSchoolYearID`,`name`,`code`
        $query = $this
            ->newQuery()
            ->from('examinationGradeSystemConfiguration')
            ->cols([
                'examinationGradeSystemConfiguration.*'
            ])
            ->where('examinationGradeSystemConfiguration.gradeSystemId = "' . $id . '" ');

        return $this->runQuery($query, $criteria, TRUE);
    }

     
public function getstdData(QueryCriteria $criteria,$pupilsightYearGroupID, $pupilsightRollGroupID)
{
    $pupilsightRoleIDAll = '003';
    if (!empty($pupilsightYearGroupID)) {
        $query = $this
        ->newQuery()
        ->from('pupilsightPerson')
        ->cols([
            'pupilsightPerson.pupilsightPersonID AS stuid','pupilsightPerson.officialName AS student_name','pupilsightPerson.pupilsightPersonID AS studentID'
        ])
     
       // ->leftJoin('assignstudent_tostaff', 'pupilsightPerson.pupilsightPersonID=assignstudent_tostaff.pupilsightPersonID')
        ->leftJoin('pupilsightStudentEnrolment', 'pupilsightPerson.pupilsightPersonID=pupilsightStudentEnrolment.pupilsightPersonID')
        ->leftJoin('pupilsightSchoolYear', 'pupilsightStudentEnrolment.pupilsightSchoolYearID=pupilsightSchoolYear.pupilsightSchoolYearID')
        ->leftJoin('pupilsightRollGroup', 'pupilsightStudentEnrolment.pupilsightRollGroupID=pupilsightRollGroup.pupilsightRollGroupID')
  
        ->leftJoin('pupilsightYearGroup', 'pupilsightStudentEnrolment.pupilsightYearGroupID=pupilsightYearGroup.pupilsightYearGroupID');

       
        if(!empty($pupilsightYearGroupID)){
            $query->where('pupilsightStudentEnrolment.pupilsightYearGroupID = "'.$pupilsightYearGroupID.'" ');
        } 
        if(!empty($pupilsightRollGroupID)){
            $query->where('pupilsightStudentEnrolment.pupilsightRollGroupID = "'.$pupilsightRollGroupID.'" ');
        } 
    
        $query->where('pupilsightPerson.pupilsightRoleIDAll = "'.$pupilsightRoleIDAll.'" ')
            ->groupBy(['pupilsightPerson.pupilsightPersonID'])
            ->orderBy(['pupilsightPerson.pupilsightPersonID ASC']) ;
          //  echo $query;    
    }else{
        $query = $this
        ->newQuery()
        ->from('pupilsightPerson')
        ->cols([
          'pupilsightPerson.pupilsightPersonID AS stuid','pupilsightPerson.officialName AS student_name','pupilsightPerson.pupilsightPersonID AS studentID'
        ])
        ->leftJoin('pupilsightStudentEnrolment', 'pupilsightPerson.pupilsightPersonID=pupilsightStudentEnrolment.pupilsightPersonID')
      //  ->leftJoin('assignstudent_tostaff', 'pupilsightPerson.pupilsightPersonID=assignstudent_tostaff.pupilsightPersonID')
        ->where('pupilsightPerson.pupilsightRoleIDAll = "'.$pupilsightRoleIDAll.'" ')
        ->groupBy(['pupilsightPerson.pupilsightPersonID'])
        ->orderBy(['pupilsightPerson.pupilsightPersonID ASC']);
        //echo $query;
    }   
   
    return $this->runQuery($query, $criteria,TRUE );
}

    public function getstudent_subject_skill_test_mappingdata(QueryCriteria $criteria, $pupilsightSchoolYearID, $pupilsightProgramID, $pupilsightYearGroupID, $pupilsightRollGroupID, $pupilsightDepartmentID, $skill_id, $test_id, $test_type)
    {
        $pupilsightRoleIDAll = '003';
        $query = $this
            ->newQuery()
            ->from('pupilsightPerson')
            ->cols([
                'pupilsightPerson.pupilsightPersonID AS stuid', 'pupilsightPerson.pupilsightPersonID', 'pupilsightPerson.officialName AS student_name', 'pupilsightPerson.admission_no',
            ])
            ->leftJoin('pupilsightStudentEnrolment', 'pupilsightPerson.pupilsightPersonID=pupilsightStudentEnrolment.pupilsightPersonID')
            ->leftJoin('pupilsightRollGroup', 'pupilsightStudentEnrolment.pupilsightRollGroupID=pupilsightRollGroup.pupilsightRollGroupID')
            ->leftJoin('pupilsightYearGroup', 'pupilsightStudentEnrolment.pupilsightYearGroupID=pupilsightYearGroup.pupilsightYearGroupID');
           // ->leftJoin('subjectToClassCurriculum', 'subjectToClassCurriculum.pupilsightYearGroupID=pupilsightYearGroup.pupilsightYearGroupID')
           // ->leftJoin('subjectSkillMapping', 'subjectSkillMapping.pupilsightYearGroupID=pupilsightYearGroup.pupilsightYearGroupID');
        if (!empty($pupilsightProgramID)) {
            $query->where('pupilsightStudentEnrolment.pupilsightProgramID = "' . $pupilsightProgramID . '" ');
        }
        if (!empty($pupilsightSchoolYearID)) {
            $query->where('pupilsightStudentEnrolment.pupilsightSchoolYearID = "' . $pupilsightSchoolYearID . '" ');
        }
        if (!empty($pupilsightYearGroupID)) {
            $query->where('pupilsightStudentEnrolment.pupilsightYearGroupID = "' . $pupilsightYearGroupID . '" ');
        }
        if (!empty($pupilsightRollGroupID)) {
            $query->where('pupilsightStudentEnrolment.pupilsightRollGroupID = "' . $pupilsightRollGroupID . '" ');
        }
     /*   if (!empty($pupilsightDepartmentID)) {
            $query->where('subjectToClassCurriculum.pupilsightDepartmentID = "' . $pupilsightDepartmentID . '" ');
        }
        if (!empty($skill_id)) {
            $query->where('subjectSkillMapping.pupilsightDepartmentID = "' . $pupilsightDepartmentID . '" AND subjectSkillMapping.skill_id = "' . $skill_id . '"  ');
        }
*/
        $query->where('pupilsightPerson.pupilsightRoleIDAll = "' . $pupilsightRoleIDAll . '" ')
            ->groupBy(['pupilsightPerson.pupilsightPersonID'])
            ->orderBy(['pupilsightPerson.pupilsightPersonID ASC']);
        // echo $query;    
        $res = $this->runQuery($query, $criteria);
        $data = $res->data;

        $res->data = $data;
        return $res;
    }

    public function getStudentTestSubjectClassWise(QueryCriteria $criteria, $pupilsightSchoolYearID, $pupilsightDepartmentID, $pupilsightYearGroupID,$pupilsightRollGroupID, $test_id)
    {
        $query = $this
            ->newQuery()
            ->from('examinationSubjectToTest')
            ->cols([
                'examinationSubjectToTest.*','examinationTest.name','examinationTest.id','examinationTest.lock_marks_entry','examinationGradeSystem.id','examinationGradeSystemConfiguration.id ','examinationGradeSystemConfiguration.gradeSystemId',"GROUP_CONCAT(DISTINCT examinationGradeSystemConfiguration.grade_name ORDER BY examinationGradeSystemConfiguration.rank ASC ) as grade_names","GROUP_CONCAT(DISTINCT examinationGradeSystemConfiguration.id ORDER BY examinationGradeSystemConfiguration.rank ASC) as grade_ids",'subjectToClassCurriculum.subject_display_name'
            ])
            ->leftJoin('examinationTest', 'examinationSubjectToTest.test_id=examinationTest.id')
            ->leftJoin('examinationTestAssignClass', 'examinationTestAssignClass.test_id=examinationSubjectToTest.test_id')
            ->leftJoin('subjectToClassCurriculum', 'subjectToClassCurriculum.pupilsightDepartmentID=examinationSubjectToTest.pupilsightDepartmentID')
            ->leftJoin('examinationGradeSystem', 'examinationSubjectToTest.gradeSystemId=examinationGradeSystem.id')
            ->leftJoin('examinationGradeSystemConfiguration', 'examinationGradeSystemConfiguration.gradeSystemId=examinationGradeSystem.id');
                
            if(!empty($test_id)){
                $query->where('examinationTestAssignClass.test_id IN('.$test_id.') AND examinationSubjectToTest.pupilsightDepartmentID = "'.$pupilsightDepartmentID.'" ');
            } 
            $query ->where('examinationSubjectToTest.pupilsightDepartmentID = "'.$pupilsightDepartmentID.'" ')
            ->where('examinationSubjectToTest.is_tested = "1" ')
            ->where('subjectToClassCurriculum.pupilsightDepartmentID = "'.$pupilsightDepartmentID.'" AND examinationTestAssignClass.pupilsightYearGroupID = "'.$pupilsightYearGroupID.'" AND examinationTestAssignClass.pupilsightRollGroupID = "'.$pupilsightRollGroupID.'" AND examinationTestAssignClass.pupilsightSchoolYearID = "'.$pupilsightSchoolYearID.'" ')
         
            ->groupBy(['examinationSubjectToTest.test_id','examinationGradeSystemConfiguration.gradeSystemId'])
            ->orderBy(['examinationTest.id ASC']) ;
        //$query;
            $res = $this->runQuery($query, $criteria);
            $data = $res->data;
            $res->data = $data;
            return $res;
    }

    public function examinationSubjectToTest(QueryCriteria $criteria)
    {
        //  `examinationTest` WHERE 1,`pupilsightSchoolYearID`,`name`,`code`
        $query = $this
            ->newQuery()
            ->from('examinationSubjectToTest')
            ->cols([
                'examinationSubjectToTest.*'
            ]);
        //->where('examinationGradeSystemConfiguration.gradeSystemId = "'.$id.'" ');

        return $this->runQuery($query, $criteria, TRUE);
    }

    // public function getstdData(QueryCriteria $criteria,$pupilsightYearGroupID, $pupilsightRollGroupID)
    // {
    //     $pupilsightRoleIDAll = '003';
    //     if (!empty($pupilsightYearGroupID)) {
    //         $query = $this
    //         ->newQuery()
    //         ->from('pupilsightPerson')
    //         ->cols([
    //             'pupilsightPerson.pupilsightPersonID AS stuid','pupilsightPerson.officialName AS student_name','assignstudent_tostaff.pupilsightStaffID AS Staffid'
    //         ])

    //         ->leftJoin('assignstudent_tostaff', 'pupilsightPerson.pupilsightPersonID=assignstudent_tostaff.pupilsightPersonID')
    //         ->leftJoin('pupilsightStudentEnrolment', 'pupilsightPerson.pupilsightPersonID=pupilsightStudentEnrolment.pupilsightPersonID')
    //         ->leftJoin('pupilsightSchoolYear', 'pupilsightStudentEnrolment.pupilsightSchoolYearID=pupilsightSchoolYear.pupilsightSchoolYearID')
    //         ->leftJoin('pupilsightRollGroup', 'pupilsightStudentEnrolment.pupilsightRollGroupID=pupilsightRollGroup.pupilsightRollGroupID')

    //         ->leftJoin('pupilsightYearGroup', 'pupilsightStudentEnrolment.pupilsightYearGroupID=pupilsightYearGroup.pupilsightYearGroupID');


    //         if(!empty($pupilsightYearGroupID)){
    //             $query->where('pupilsightStudentEnrolment.pupilsightYearGroupID = "'.$pupilsightYearGroupID.'" ');
    //         } 
    //         if(!empty($pupilsightRollGroupID)){
    //             $query->where('pupilsightStudentEnrolment.pupilsightRollGroupID = "'.$pupilsightRollGroupID.'" ');
    //         } 

    //         $query->where('pupilsightPerson.pupilsightRoleIDAll = "'.$pupilsightRoleIDAll.'" ')
    //             ->groupBy(['pupilsightPerson.pupilsightPersonID'])
    //             ->orderBy(['pupilsightPerson.pupilsightPersonID ASC']) ;
    //         //  echo $query;    
    //     }else{
    //         $query = $this
    //         ->newQuery()
    //         ->from('pupilsightPerson')
    //         ->cols([
    //         'pupilsightPerson.pupilsightPersonID AS stuid','pupilsightPerson.officialName AS student_name',"GROUP_CONCAT(DISTINCT assignstudent_tostaff.pupilsightStaffID SEPARATOR ', ') as staff_id",'assignstudent_tostaff.pupilsightStaffID AS Staffid'
    //         ])
    //         ->leftJoin('pupilsightStudentEnrolment', 'pupilsightPerson.pupilsightPersonID=pupilsightStudentEnrolment.pupilsightPersonID')
    //         ->leftJoin('assignstudent_tostaff', 'pupilsightPerson.pupilsightPersonID=assignstudent_tostaff.pupilsightPersonID')
    //         ->where('pupilsightPerson.pupilsightRoleIDAll = "'.$pupilsightRoleIDAll.'" ')
    //         ->groupBy(['pupilsightPerson.pupilsightPersonID'])
    //         ->orderBy(['pupilsightPerson.pupilsightPersonID ASC']);
    //         //echo $query;
    //     }   

    //     return $this->runQuery($query, $criteria,TRUE );
    // }


    //getTestResults

    public function getsubjectmarksStdWise(QueryCriteria $criteria, $pupilsightProgramID, $pupilsightYearGroupID,$pupilsightSchoolYearID,$testId){
        $query = $this
            ->newQuery()
            ->from('examinationSubjectToTest')
            ->cols([
                        'examinationSubjectToTest.*','examinationTest.name','examinationTest.lock_marks_entry','examinationGradeSystemConfiguration.gradeSystemId',"GROUP_CONCAT(DISTINCT examinationGradeSystemConfiguration.grade_name SEPARATOR ', ') as grade_names","GROUP_CONCAT(DISTINCT examinationGradeSystemConfiguration.id SEPARATOR ', ') as grade_ids",'subjectToClassCurriculum.subject_type', 'pupilsightDepartment.name AS subject','subjectToClassCurriculum.pos'
                    ])
            ->leftJoin('examinationTest', 'examinationSubjectToTest.test_id=examinationTest.id')
            ->leftJoin('pupilsightDepartment', 'examinationSubjectToTest.pupilsightDepartmentID=pupilsightDepartment.pupilsightDepartmentID')     
            ->leftJoin('examinationGradeSystemConfiguration', 'examinationSubjectToTest.gradeSystemId=examinationGradeSystemConfiguration.gradeSystemId')
            ->leftJoin('subjectToClassCurriculum', 'examinationSubjectToTest.pupilsightDepartmentID=subjectToClassCurriculum.pupilsightDepartmentID')  

            ->where('examinationSubjectToTest.is_tested ="1"')
            ->where('examinationSubjectToTest.test_id ="'.$testId.'"')
            ->where('subjectToClassCurriculum.pupilsightSchoolYearID ="'.$pupilsightSchoolYearID.'"')
            ->where('subjectToClassCurriculum.pupilsightProgramID ="'.$pupilsightProgramID.'"')
            ->where('subjectToClassCurriculum.pupilsightYearGroupID ="'.$pupilsightYearGroupID.'"')
            ->groupBy(['examinationSubjectToTest.pupilsightDepartmentID'])
            ->orderBy(['subjectToClassCurriculum.pos ASC']);
            //echo $query;
            $res = $this->runQuery($query, $criteria);
            $data = $res->data;

            foreach($data as $k=>$sd){
                $query2 = $this
                ->newQuery()
                ->from('examinationSubjectToTest')
                ->cols([
                    'examinationSubjectToTest.*','subjectSkillMapping.skill_display_name'
                    ])
                ->leftJoin('subjectSkillMapping', 'examinationSubjectToTest.skill_id=subjectSkillMapping.skill_id')
                ->where('examinationSubjectToTest.is_tested ="1"')
                ->where('examinationSubjectToTest.test_id ="'.$testId.'"')
                ->where('examinationSubjectToTest.pupilsightDepartmentID ="'.$sd['pupilsightDepartmentID'].'"')
                ->groupBy(['examinationSubjectToTest.skill_id']);

                $newdata = $this->runQuery($query2, $criteria);
                if(!empty($newdata)){
                    $data[$k]['skills'] = $newdata;
                } else {
                    $data[$k]['skills'] = '';
                }    
            }
            
            $res->data = $data;
            return $res;

    }

    public function getTestResults(QueryCriteria $criteria, $pupilsightSchoolYearID, $pupilsightYearGroupID, $pupilsightRollGroupID, $test_id)
    {
        $pupilsightRoleIDAll = '003';
        $query = $this
            ->newQuery()
            ->from('pupilsightPerson')
            ->cols([
                'pupilsightPerson.pupilsightPersonID AS stuid', 'pupilsightPerson.pupilsightPersonID', 'pupilsightPerson.officialName AS student_name'
            ])
            ->leftJoin('pupilsightStudentEnrolment', 'pupilsightPerson.pupilsightPersonID=pupilsightStudentEnrolment.pupilsightPersonID')
            ->leftJoin('pupilsightRollGroup', 'pupilsightStudentEnrolment.pupilsightRollGroupID=pupilsightRollGroup.pupilsightRollGroupID')
            ->leftJoin('pupilsightYearGroup', 'pupilsightStudentEnrolment.pupilsightYearGroupID=pupilsightYearGroup.pupilsightYearGroupID');

            // ->innerJoin('examinationMarksEntrybySubject', 'pupilsightPerson.pupilsightPersonID=examinationMarksEntrybySubject.pupilsightPersonID');

        if (!empty($pupilsightSchoolYearID)) {
            $query->where('pupilsightStudentEnrolment.pupilsightSchoolYearID = "' . $pupilsightSchoolYearID . '" ');
        }
        if (!empty($pupilsightYearGroupID)) {
            $query->where('pupilsightStudentEnrolment.pupilsightYearGroupID = "' . $pupilsightYearGroupID . '" ');
        }
        if (!empty($pupilsightRollGroupID)) {
            $query->where('pupilsightStudentEnrolment.pupilsightRollGroupID = "' . $pupilsightRollGroupID . '" ');
        }

        // if (!empty($test_id)) {
        //     $query->where('examinationMarksEntrybySubject.test_id = "' . $test_id . '"   ');
        // }

        //  $query->where('pupilsightPerson.pupilsightRoleIDAll = "'.$pupilsightRoleIDAll.'" ')
        $query->groupBy(['pupilsightPerson.pupilsightPersonID'])
            ->orderBy(['pupilsightPerson.pupilsightPersonID ASC']);
         //echo $query;    
        $res = $this->runQuery($query, $criteria);
        $data = $res->data;

        if(!empty($data)){
            foreach ($data as $k => $d) {
                $query2 = $this
                    ->newQuery()
                    ->from('examinationMarksEntrybySubject')
                    ->cols([
                        'examinationMarksEntrybySubject.*'
                    ])
                    ->where('examinationMarksEntrybySubject.pupilsightPersonID = "' . $d['pupilsightPersonID'] . '" ');
                    if (!empty($test_id)) {
                        $query->where('examinationMarksEntrybySubject.test_id = "' . $test_id . '"   ');
                    }

                $newdata = $this->runQuery($query2, $criteria);
                if (!empty($newdata->data[0]['id'])) {
                    $data[$k]['test_id'] = $newdata->data[0]['test_id'];
                    $data[$k]['skill_id'] = $newdata->data[0]['skill_id'];
                    $data[$k]['marks_obtained'] = $newdata->data[0]['marks_obtained'];
                    $data[$k]['marks_abex'] = $newdata->data[0]['marks_abex'];
                    $data[$k]['gradeId'] = $newdata->data[0]['gradeId'];
                    $data[$k]['remarks'] = $newdata->data[0]['remarks'];
                    $data[$k]['status'] = $newdata->data[0]['status'];
                } else {
                    $data[$k]['checked'] = '';
                    $data[$k]['skill_id'] = '';
                    $data[$k]['marks_obtained'] = '';
                    $data[$k]['marks_abex'] = '';
                    $data[$k]['gradeId'] = '';
                    $data[$k]['remarks'] = '';
                    $data[$k]['status'] = '';
                }
            }
        }

        $res->data = $data;
        return $res;
    }

    public function getStudentSubjectkillsClassWise(QueryCriteria $criteria, $pupilsightSchoolYearID, $pupilsightYearGroupID)
    {
        $query = $this
            ->newQuery()
            ->from('subjectToClassCurriculum')
            ->cols([
                "GROUP_CONCAT(DISTINCT subjectSkillMapping.skill_id SEPARATOR ', ') as skill_ids", "GROUP_CONCAT(DISTINCT subjectSkillMapping.skill_display_name SEPARATOR ', ') as skillname", 'subjectToClassCurriculum.pupilsightDepartmentID', 'pupilsightDepartment.pupilsightDepartmentID', 'pupilsightDepartment.name'
            ])

            ->leftJoin('subjectSkillMapping', 'subjectSkillMapping.pupilsightDepartmentID=subjectToClassCurriculum.pupilsightDepartmentID')
            ->leftJoin('pupilsightDepartment', 'subjectToClassCurriculum.pupilsightDepartmentID=pupilsightDepartment.pupilsightDepartmentID')
            ->where('subjectToClassCurriculum.pupilsightSchoolYearID = "' . $pupilsightSchoolYearID . '" AND subjectToClassCurriculum.pupilsightYearGroupID = "' . $pupilsightYearGroupID . '"  ')
            ->groupBy(['subjectToClassCurriculum.pupilsightDepartmentID', 'subjectSkillMapping.pupilsightProgramID'])
            ->orderBy(['subjectToClassCurriculum.pupilsightDepartmentID ASC']);
        //  echo $query;
        $res = $this->runQuery($query, $criteria);
        $data = $res->data;
        $res->data = $data;
        return $res;
        $query->where('pupilsightPerson.pupilsightRoleIDAll = "' . $pupilsightRoleIDAll . '" ')
            ->groupBy(['pupilsightPerson.pupilsightPersonID'])
            ->orderBy(['pupilsightPerson.pupilsightPersonID ASC']);
        //  echo $query;    
        $res = $this->runQuery($query, $criteria);
        $data = $res->data;

        $res->data = $data;
        return $res;
    }

    public function getSubject($pupilsightYearGroupID)
    {
        $sq = "select pupilsightDepartmentID, subject_display_name, di_mode from subjectToClassCurriculum where pupilsightYearGroupID ='" . $pupilsightYearGroupID . "' order by subject_display_name asc";
        $db = new DBQuery();
        return $db->selectRaw($sq);
    }

    public function getClass()
    {
        $sq = "select y.pupilsightYearGroupID as id, y.name from subjectToClassCurriculum as sm, pupilsightYearGroup as y ";
        $sq .= " where sm.pupilsightYearGroupID = y.pupilsightYearGroupID order by y.name asc";
        $db = new DBQuery();
        return $db->selectRaw($sq);
    }

    // public function getAcRemarks($pupilsightYearGroupID, $pupilsightDepartmentID, $skill_id = NULL)
    // {
    //     $sq1 = "select group_concat(remarkcode) as code from acRemarks where pupilsightYearGroupID='" . $pupilsightYearGroupID . "'";
    //     $db = new DBQuery();
    //     $rs = $db->selectRaw($sq1);
    //     $rcode = $rs[0]["code"];

    //     $sq = "select id, remarkcode, description from acRemarks where pupilsightDepartmentID ='" . $pupilsightDepartmentID . "' ";
    //     if (!empty($skill_id)) {
    //         $sq .= " and skill='" . $skill_id . "' ";
    //     }
    //     if (!empty($rcode)) {
    //         $sq .= " and remarkcode NOT IN(" . $rcode . ") ";
    //     }

    //     $sq .= " and pupilsightYearGroupID IS NULL ";
    //     $sq .= "order by remarkcode asc";

    //     return $db->select($sq);
    // }
    public function getstdDataNew(QueryCriteria $criteria,$pupilsightProgramID,$pupilsightYearGroupID, $pupilsightRollGroupID,$test_id)
    {
    
        $pupilsightRoleIDAll = '003';
        if (!empty($pupilsightYearGroupID)) {
            $query = $this
            ->newQuery()
            ->from('pupilsightPerson')
            ->cols([
                'pupilsightPerson.pupilsightPersonID AS stuid','pupilsightPerson.officialName AS student_name','pupilsightPerson.pupilsightPersonID AS studentID', 'pupilsightPerson.admission_no'
            ])
         
           // ->leftJoin('assignstudent_tostaff', 'pupilsightPerson.pupilsightPersonID=assignstudent_tostaff.pupilsightPersonID')
            ->leftJoin('pupilsightStudentEnrolment', 'pupilsightPerson.pupilsightPersonID=pupilsightStudentEnrolment.pupilsightPersonID')
            ->leftJoin('examinationTestAssignClass', 'pupilsightStudentEnrolment.pupilsightYearGroupID=examinationTestAssignClass.pupilsightYearGroupID')
            ->leftJoin('pupilsightSchoolYear', 'examinationTestAssignClass.pupilsightSchoolYearID=pupilsightSchoolYear.pupilsightSchoolYearID')
            ->leftJoin('pupilsightRollGroup', 'examinationTestAssignClass.pupilsightRollGroupID=pupilsightRollGroup.pupilsightRollGroupID')
      
            ->leftJoin('pupilsightYearGroup', 'examinationTestAssignClass.pupilsightYearGroupID=pupilsightYearGroup.pupilsightYearGroupID');
           if(!empty($pupilsightProgramID)){
               $query->where('pupilsightStudentEnrolment.pupilsightProgramID = "'.$pupilsightProgramID.'" ');
           }
            if(!empty($pupilsightYearGroupID)){
                $query->where('pupilsightStudentEnrolment.pupilsightYearGroupID = "'.$pupilsightYearGroupID.'" ');
            } 
             if(!empty($test_id)){
                $test_id=implode(",",$test_id);
                $query->where('examinationTestAssignClass.test_id IN("'.$test_id.'")');
            } 
            if(!empty($pupilsightRollGroupID)){
                $query->where('pupilsightStudentEnrolment.pupilsightRollGroupID = "'.$pupilsightRollGroupID.'" ');
            } 
           
            $query->where('pupilsightPerson.pupilsightRoleIDAll = "'.$pupilsightRoleIDAll.'" ')
                ->groupBy(['pupilsightPerson.pupilsightPersonID'])
                ->orderBy(['pupilsightPerson.pupilsightPersonID ASC']) ;
               //echo $query;    
        }else{
            $query = $this
            ->newQuery()
            ->from('pupilsightPerson')
            ->cols([
              'pupilsightPerson.pupilsightPersonID AS stuid','pupilsightPerson.officialName AS student_name','pupilsightPerson.pupilsightPersonID AS studentID'
            ])
            ->leftJoin('pupilsightStudentEnrolment', 'pupilsightPerson.pupilsightPersonID=pupilsightStudentEnrolment.pupilsightPersonID')
          //  ->leftJoin('assignstudent_tostaff', 'pupilsightPerson.pupilsightPersonID=assignstudent_tostaff.pupilsightPersonID')
            ->where('pupilsightPerson.pupilsightRoleIDAll = "'.$pupilsightRoleIDAll.'" ')
            ->groupBy(['pupilsightPerson.pupilsightPersonID'])
            ->orderBy(['pupilsightPerson.pupilsightPersonID ASC']);
            //echo $query;
        }   
       
        return $this->runQuery($query, $criteria, true );
    }
    
    public function isRemarksAdded($pupilsightYearGroupID, $pupilsightDepartmentID, $skill_id = NULL)
    {
        $db = new DBQuery();
        $sq = "select id, remarkcode, description from acRemarks where pupilsightDepartmentID ='" . $pupilsightDepartmentID . "' and pupilsightYearGroupID='" . $pupilsightYearGroupID . "' ";
        if (!empty($skill_id)) {
            $sq .= " and skill='" . $skill_id . "' ";
        }

        return $db->select($sq);
    }

    public function getClassAcRemarks($pupilsightYearGroupID, $pupilsightDepartmentID, $skill_id = NULL)
    {
        $db = new DBQuery();
        $sq = "select id, remarkcode, description from acRemarks where pupilsightDepartmentID ='" . $pupilsightDepartmentID . "' and pupilsightYearGroupID='" . $pupilsightYearGroupID . "' ";
        if (!empty($skill_id)) {
            $sq .= " and skill='" . $skill_id . "' ";
        }
        $sq .= "order by remarkcode asc ";

        return $db->select($sq);
    }

    public function getExamMarksNotEntered($search=NULL)
    {
        $db = new DBQuery();
        $examids = $this->getAllActiveExams($search);
        
        $len = count($examids);
        $i = 0;
        while($i<$len){

            $examids[$i]["pupilsightYearGroup"] = $db->getColVal("pupilsightYearGroup","name","pupilsightYearGroupID",$examids[$i]["pupilsightYearGroupID"]);
            $examids[$i]["pupilsightDepartment"] = $db->getColVal("pupilsightDepartment","name","pupilsightDepartmentID",$examids[$i]["pupilsightDepartmentID"]);
            $examids[$i]["pupilsightRollGroup"] = $db->getColVal("pupilsightRollGroup","name","pupilsightRollGroupID",$examids[$i]["pupilsightRollGroupID"]);
            $examids[$i]["skill"] = $db->getColVal("ac_manage_skill","name","id",$examids[$i]["skill_id"]);
            
            if(empty($examids[$i]["pupilsightRollGroup"])){
                $examids[$i]["pupilsightRollGroup"] = "";
                $examids[$i]["totalStudents"] = "-";
                $examids[$i]["staff"] = "-";
                $tme = $this->totalMarksEntered($examids[$i]["test_id"], $examids[$i]["pupilsightYearGroupID"], $examids[$i]["pupilsightDepartmentID"]);
                
                $examids[$i]["marksStatus"] ="Incomplete";
                $examids[$i]["marksEntered"] =0;
                if($tme>0){
                    $examids[$i]["marksStatus"] ="In Progress";
                    $examids[$i]["marksEntered"] =$tme;
                }
                //$result[] = $examids[$i];
            }else{
                $ts = $this->classTotalStudent($examids[$i]["pupilsightSchoolYearID"],$examids[$i]["pupilsightProgramID"],$examids[$i]["pupilsightYearGroupID"], $examids[$i]["pupilsightRollGroupID"]);
                
                $examids[$i]["totalStudents"] = "-";
                if($ts>0){
                    $examids[$i]["totalStudents"] = $ts;
                }
                
                $examids[$i]["staff"] = $this->getStaffName($examids[$i]["pupilsightYearGroupID"], $examids[$i]["pupilsightRollGroupID"], $examids[$i]["pupilsightDepartmentID"]);
                $tme = $this->totalMarksEntered($examids[$i]["test_id"], $examids[$i]["pupilsightYearGroupID"], $examids[$i]["pupilsightDepartmentID"], $examids[$i]["pupilsightRollGroupID"]);
                
                $examids[$i]["marksStatus"] ="Incomplete";
                $examids[$i]["marksEntered"] =0;
                if($tme>0){
                    if($tme>=$ts){
                        $examids[$i]["marksStatus"] ="Complete";
                        $examids[$i]["marksEntered"] =$tme;
                        unset($examids[$i]); 
                    }else{
                        $examids[$i]["marksStatus"] ="In Progress";
                        $examids[$i]["marksEntered"] =$tme;
                    }
                }
                //$result[] = $examids[$i];
            }

            //$pupilsightProgramID = $examids[$i]["pupilsightProgramID"];
            //$pupilsightDepartmentID = $examids[$i]["pupilsightDepartmentID"];
            //$skill_id = $examids[$i]["skill_id"];
            
            $i++;
        }
        return $db->convertDataset($examids);
    }

    public function getAllActiveExams($search=NULL)
    {

        $db = new DBQuery();
        $sq = "select DISTINCT t.id as test_id, t.name, c.pupilsightSchoolYearID, c.pupilsightProgramID, c.pupilsightYearGroupID, c.pupilsightRollGroupID, sub.pupilsightDepartmentID, sub.skill_id ";
        $sq .= "from examinationTest as t ";
        $sq .= "left join examinationTestAssignClass as c on c.test_id= t.id ";
        //$sq .= "left join examinationTestSubjectCategory as sub on sub.test_id= t.id ";
        $sq .= "left join examinationSubjectToTest as sub on sub.test_id = t.id ";
        $sq .= "where t.end_date < DATE(NOW()) AND sub.is_tested = 1 ";
        if(!empty($search)){
            if(!empty($search["test_id"])){
                $sq .= "and c.test_master_id='".$search["test_id"]."' ";
            }
            if(!empty($search['pupilsightProgramIDBytest'])){
               $sq.=" AND c.pupilsightProgramID= ".$search['pupilsightProgramIDBytest']." ";
            }
            if(!empty($search["pupilsightYearGroupIDT"])){
                $pupilsightYearGroupIDT=implode(',', $search["pupilsightYearGroupIDT"]);
                $sq .= "and c.pupilsightYearGroupID IN (".$pupilsightYearGroupIDT.")";
            }

            if(!empty($search["pupilsightRollGroupID"])){
                $sq .= "and c.pupilsightRollGroupID='".$search["pupilsightRollGroupID"]."' ";
            }

            if(!empty($search["pupilsightDepartmentID"])){
                $sq .= "and sub.pupilsightDepartmentID='".$search["pupilsightDepartmentID"]."' ";
            }

            if(!empty($search["skill_id"])){
                $sq .= "and sub.skill_id='".$search["skill_id"]."' ";
            }
        }
        return $db->selectRaw($sq);
    }

    public function getRollGroup($pupilsightRollGroupID){
        $db = new DBQuery();
        $sq = "select pupilsightRollGroupID, name from pupilsightRollGroup where pupilsightRollGroupID IN(".$pupilsightRollGroupID.") ";
        return $db->selectRaw($sq);
    }

    public function totalMarksEntered($testId, $pupilsightYearGroupID=NULL, $pupilsightDepartmentID=NULL, $pupilsightRollGroupID=NULL){
        $db = new DBQuery();
        $sq = "select count(id) as count from examinationMarksEntrybySubject ";
        $sq .= "where test_id='".$testId."' ";

        if($pupilsightYearGroupID){
            $sq .= "and pupilsightYearGroupID='".$pupilsightYearGroupID."' ";
        }
        
        if($pupilsightDepartmentID){
            $sq .= "and pupilsightDepartmentID='".$pupilsightDepartmentID."' ";
        }

        if($pupilsightRollGroupID){
            $sq .= "and pupilsightRollGroupID='".$pupilsightRollGroupID."' ";
        }
        $row = $db->selectRaw($sq);
        if(!empty($row)){
            return $row[0]["count"];
        }
        return 0;
    }

    public function classTotalStudent($pupilsightSchoolYearID=NULL,$pupilsightProgramID=NULL,$pupilsightYearGroupID=NULL, $pupilsightRollGroupID=NULL){
        if(!empty($pupilsightYearGroupID) && !empty($pupilsightRollGroupID)){
            $db = new DBQuery();
            $sq = "select count(a.pupilsightStudentEnrolmentID) as count from pupilsightStudentEnrolment AS a LEFT JOIN pupilsightPerson AS b ON a.pupilsightPersonID = b.pupilsightPersonID  ";
            $sq .= "WHERE b.pupilsightRoleIDPrimary = '003' AND a.pupilsightSchoolYearID='".$pupilsightSchoolYearID."' ";
            $sq .= " AND a.pupilsightProgramID= ".$pupilsightProgramID." ";
            $sq .= "AND a.pupilsightYearGroupID= ".$pupilsightYearGroupID." ";
            if($pupilsightRollGroupID){
                $sq .= "AND a.pupilsightRollGroupID='".$pupilsightRollGroupID."' ";
            }
            //echo $sq;
           
            $row = $db->selectRaw($sq);
            if(!empty($row)){
                return $row[0]["count"];
            }
        }
        return 0;
    }

    public function getStaffName($pupilsightYearGroupID=NULL, $pupilsightRollGroupID=NULL, $pupilsightDepartmentID=NULL){
        
        if(!empty($pupilsightYearGroupID) && !empty($pupilsightRollGroupID)){
           

            $db = new DBQuery();
            $sq = "select group_concat(p.pupilsightPersonID) as staffID from assignstaff_toclasssection as a ";
            $sq .="left join pupilsightProgramClassSectionMapping as m on m.pupilsightMappingID = a.pupilsightMappingID ";
            $sq .="left join pupilsightPerson as p on p.pupilsightPersonID=a.pupilsightPersonID ";
            $sq .="where m.pupilsightYearGroupID = '".$pupilsightYearGroupID."' and m.pupilsightRollGroupID='".$pupilsightRollGroupID."' ";
            $row = $db->selectRaw($sq);
            if(!empty($row)){
                $staffIds =  $row[0]["staffID"];
                $db = new DBQuery();
                $sq = 'SELECT GROUP_CONCAT(p.officialName) as staff FROM assignstaff_tosubject AS a LEFT JOIN pupilsightStaff as s on a.pupilsightStaffID=s.pupilsightStaffID LEFT JOIN pupilsightPerson as p on s.pupilsightPersonID=p.pupilsightPersonID WHERE a.pupilsightdepartmentID = '.$pupilsightDepartmentID.' AND p.pupilsightPersonID IN ('.$staffIds.') ';
                //echo $sq;
                $row = $db->selectRaw($sq);
                if(!empty($row)){
                    return $row[0]["staff"];
                }
            }
        }
        return "";
        
    }

    public function getExamList()
    {
        $db = new DBQuery();
        $sq = "select t.id, t.name from examinationTest as t ";
        $sq .= "where t.end_date < DATE(NOW()) ";
        return $db->selectRaw($sq);
    }


    public function getStudentSubjectskillsClassWise_ATT(QueryCriteria $criteria, $pupilsightSchoolYearID,$pupilsightYearGroupID,$pupilsightDepartmentID,$skill_id,$test_id,$test_type){
      //  echo $skill_id;
        $query = $this
            ->newQuery()
            ->from('subjectSkillMapping')
            ->cols([
               'DISTINCT subjectSkillMapping.skill_id as skill_ids','subjectSkillMapping.skill_display_name as skillname','examinationSubjectToTest.max_marks'
            ])
            
           // ->leftJoin('subjectSkillMapping', 'subjectSkillMapping.pupilsightDepartmentID=subjectToClassCurriculum.pupilsightDepartmentID')
            ->leftJoin('pupilsightDepartment', 'subjectSkillMapping.pupilsightDepartmentID=pupilsightDepartment.pupilsightDepartmentID')     
           ->leftJoin('examinationSubjectToTest', 'examinationSubjectToTest.pupilsightDepartmentID=subjectSkillMapping.pupilsightDepartmentID') ;
           
           if(!empty($pupilsightSchoolYearID)){
            $query->where('subjectSkillMapping.pupilsightSchoolYearID = "'.$pupilsightSchoolYearID.'" ');
        }
        if(!empty($pupilsightYearGroupID)){
            $query->where('subjectSkillMapping.pupilsightYearGroupID = "'.$pupilsightYearGroupID.'" ');
        } 
       
       if(!empty($test_id)){
            $query->where('examinationSubjectToTest.test_id = "'.$test_id.'"  AND examinationSubjectToTest.pupilsightDepartmentID="'.$pupilsightDepartmentID.'" ');
        } 
        if(!empty($skill_id)){
            $query->where('subjectSkillMapping.skill_id = "'.$skill_id.'"   ');
        } 

        $query ->where('subjectSkillMapping.pupilsightSchoolYearID = "'.$pupilsightSchoolYearID.'" AND subjectSkillMapping.pupilsightYearGroupID = "'.$pupilsightYearGroupID.'" AND subjectSkillMapping.pupilsightDepartmentID = "'.$pupilsightDepartmentID.'" ')
            ->groupBy(['subjectSkillMapping.skill_id '])
          ->orderBy(['subjectSkillMapping.id ASC']) ;
       // echo $query;
            $res = $this->runQuery($query, $criteria);
            $data = $res->data;
            $res->data = $data;
            return $res;
    }

    public function getstudent_subject_assigned_data_for_AAT(QueryCriteria $criteria,  $pupilsightSchoolYearID,$pupilsightYearGroupID, $pupilsightRollGroupID,$pupilsightDepartmentID,$skill_id,$test_id,$test_type)
    {
      //  $pupilsightRoleIDAll = '003';
            $query = $this
            ->newQuery()
            ->from('examinationMarksEntrybySubject')
            ->cols([
                'examinationMarksEntrybySubject.*','examinationSubjectToTest.*','pupilsightPerson.pupilsightPersonID AS stuid','pupilsightPerson.pupilsightPersonID','pupilsightPerson.officialName AS student_name',"( CASE WHEN examinationMarksEntrybySubject.skill_id  != '0' THEN GROUP_CONCAT(DISTINCT examinationMarksEntrybySubject.skill_id ORDER BY examinationMarksEntrybySubject.skill_id ASC)   ELSE examinationMarksEntrybySubject.pupilsightDepartmentID  END) as columname","GROUP_CONCAT(DISTINCT subjectSkillMapping.skill_display_name ORDER BY subjectSkillMapping.skill_id ASC) as skill_names",'pupilsightDepartment.pupilsightDepartmentID', 'pupilsightDepartment.name'
            ])
            ->leftJoin('pupilsightDepartment', 'examinationMarksEntrybySubject.pupilsightDepartmentID=pupilsightDepartment.pupilsightDepartmentID')     
            ->leftJoin('pupilsightPerson', 'pupilsightPerson.pupilsightPersonID=examinationMarksEntrybySubject.pupilsightPersonID')
            ->leftJoin('examinationSubjectToTest', 'examinationSubjectToTest.test_id=examinationMarksEntrybySubject.test_id')
            ->leftJoin('subjectSkillMapping', 'subjectSkillMapping.skill_id=examinationMarksEntrybySubject.skill_id');
         
            if(!empty($pupilsightYearGroupID)){
                $query->where('examinationMarksEntrybySubject.pupilsightYearGroupID = "'.$pupilsightYearGroupID.'" ');
            } 
            if(!empty($pupilsightRollGroupID)){
                $query->where('examinationMarksEntrybySubject.pupilsightRollGroupID = "'.$pupilsightRollGroupID.'" ');
            } 
            if(!empty($pupilsightDepartmentID)){
                $query->where('examinationMarksEntrybySubject.pupilsightDepartmentID = "'.$pupilsightDepartmentID.'" ');
            } 
           if(!empty($test_id)){
                $query->where('examinationMarksEntrybySubject.test_id = "'.$test_id.'"   ');
            } 
            if(!empty($skill_id)){
                $query->where('examinationMarksEntrybySubject.skill_id = "'.$skill_id.'"  ');
            } 
            $query->where('examinationSubjectToTest.aat = 1 AND examinationMarksEntrybySubject.pupilsightDepartmentID = "'.$pupilsightDepartmentID.'"'   );

               $query ->groupBy(['examinationMarksEntrybySubject.pupilsightPersonID'])
                ->orderBy(['pupilsightPerson.pupilsightPersonID ASC']) ;
             //   echo $query;    
            $res = $this->runQuery($query, $criteria);
            $data = $res->data;
        
            $res->data = $data;
            return $res;
    }


    public function getSubNew(QueryCriteria $criteria, $pupilsightSchoolYearID, $pupilsightProgramID, $pupilsightYearGroupID, $eid, $subId)
    {
        
        $query = $this
            ->newQuery()
            ->from('subjectToClassCurriculum')
            ->cols([
                'subjectToClassCurriculum.subject_display_name AS name ,subjectToClassCurriculum.pupilsightDepartmentID AS id'
            ])
            ->where('subjectToClassCurriculum.subject_type = "Elective" ')
            ->where('subjectToClassCurriculum.pupilsightSchoolYearID = "' . $pupilsightSchoolYearID . '" ')
            ->where('subjectToClassCurriculum.pupilsightProgramID = "' . $pupilsightProgramID . '" ')
            ->where('subjectToClassCurriculum.pupilsightYearGroupID = "' . $pupilsightYearGroupID . '" ');
            if(!empty($subId)){
                $query->where('subjectToClassCurriculum.pupilsightDepartmentID  Not In ('.$subId.') ');
            }
            $query->groupby(['subjectToClassCurriculum.pupilsightDepartmentID'])
            ->orderby(['subjectToClassCurriculum.pupilsightDepartmentID DESC']);
        //echo $query;
        $res = $this->runQuery($query, $criteria);
        $data = $res->data;
        foreach ($data as $k => $d) {
            $data[$k]['checked'] = '';
        }

        $res->data = $data;
        return $res;
    }

    public function getTestRoom(QueryCriteria $criteria)
    {
        //SELECT * FROM `acRemarks` WHERE ,`id`,`remarkcode`,`description`,`pupilsightDepartmentID`,`skill` 
        $query = $this
            ->newQuery()
            ->from('examinationRoomMaster')
            ->cols([
                'examinationRoomMaster.*'
            ])
            ->orderby(['examinationRoomMaster.id DESC']);

        return $this->runQuery($query, $criteria, true);
    }


    public function getAllcurriculamRemarksNew(QueryCriteria $criteria)
    {
        //SELECT * FROM `acRemarks` WHERE ,`id`,`remarkcode`,`description`,`pupilsightDepartmentID`,`skill` 
        $query = $this
            ->newQuery()
            ->from('acRemarks')
            ->cols([
                'acRemarks.*', 'pupilsightDepartment.name AS subject', 'ac_manage_skill.name AS skillname'
            ])
            ->leftJoin('ac_manage_skill', 'ac_manage_skill.ID=acRemarks.skill')
            ->leftJoin('pupilsightDepartment ', 'acRemarks.pupilsightDepartmentID=pupilsightDepartment.pupilsightDepartmentID');

        return $this->runQuery($query, $criteria);
    }

    public function getReportTemplate(QueryCriteria $criteria)
    {
        $query = $this
            ->newQuery()
            ->from('examinationReportTemplateMaster')
            ->cols([
                'examinationReportTemplateMaster.*'
            ])
            ->orderby(['examinationReportTemplateMaster.id DESC']);

        return $this->runQuery($query, $criteria, true);
    }

    public function getSketchTemplate(QueryCriteria $criteria, $id)
    {
        $query = $this
            ->newQuery()
            ->from('examinationReportSketchTemplateMaster')
            ->cols([
                'examinationReportSketchTemplateMaster.*','pupilsightProgram.name as progName','pupilsightYearGroup.name as clsName'
            ])
            ->leftJoin('pupilsightYearGroup', 'examinationReportSketchTemplateMaster.pupilsightYearGroupID=pupilsightYearGroup.pupilsightYearGroupID')
            ->leftJoin('pupilsightProgram', 'examinationReportSketchTemplateMaster.pupilsightProgramID=pupilsightProgram.pupilsightProgramID')
            ->where('examinationReportSketchTemplateMaster.sketch_id = "' . $id . '" ')
            ->orderby(['examinationReportSketchTemplateMaster.id DESC']);

        return $this->runQuery($query, $criteria, true);
    }


}