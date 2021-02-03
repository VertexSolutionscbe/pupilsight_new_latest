<?php
/*
Pupilsight, Flexible & Open School System
*/

namespace Pupilsight\Domain\School;

use Pupilsight\Domain\Traits\TableAware;
use Pupilsight\Domain\QueryCriteria;
use Pupilsight\Domain\QueryableGateway;

/**
 * YearGroup Gateway
 *
 * @version v16
 * @since   v16
 */
class CurriculumGateway extends QueryableGateway
{
    use TableAware;

    private static $tableName = 'pupilsightProgram';
    private static $searchableColumns = [];

    public function getSubjectDate(QueryCriteria $criteria, $pupilsightSchoolYearID, $pupilsightProgramID, $pupilsightYearGroupID)
    {

        $query = $this
            ->newQuery()
            ->from('assign_core_subjects_toclass')
            ->cols([
                'pupilsightDepartment.pupilsightDepartmentID', 'pupilsightDepartment.name', 'pupilsightDepartment.type', 'pupilsightDepartment.nameShort'
            ])
            ->leftJoin('pupilsightProgramClassSectionMapping', 'assign_core_subjects_toclass.pupilsightYearGroupID=pupilsightProgramClassSectionMapping.pupilsightYearGroupID')
            ->leftJoin('pupilsightDepartment', 'assign_core_subjects_toclass.pupilsightDepartmentID=pupilsightDepartment.pupilsightDepartmentID')
            ->where('assign_core_subjects_toclass.pupilsightYearGroupID ="' . $pupilsightYearGroupID . '"')
            ->where('assign_core_subjects_toclass.pupilsightProgramID = "' . $pupilsightProgramID . '" ')
            ->where('pupilsightProgramClassSectionMapping.pupilsightSchoolYearID = "' . $pupilsightSchoolYearID . '" ')
            ->groupBy(['assign_core_subjects_toclass.pupilsightDepartmentID'])
            ->orderBy(['assign_core_subjects_toclass.pos ASC']);
        // echo $query;
        $res = $this->runQuery($query, $criteria);
        $data = $res->data;
        if (!empty($data)) {
            $i = 50;
            foreach ($data as $k => $d) {
                $departmentId = $d['pupilsightDepartmentID'];
                $query2 = $this
                    ->newQuery()
                    ->from('subjectToClassCurriculum')
                    ->cols([
                        'subjectToClassCurriculum.subject_display_name', 'subjectToClassCurriculum.subject_type', 'subjectToClassCurriculum.di_mode', 'subjectToClassCurriculum.id', 'subjectToClassCurriculum.pos'
                    ])
                    ->where('subjectToClassCurriculum.pupilsightSchoolYearID = "' . $pupilsightSchoolYearID . '" ')
                    ->where('subjectToClassCurriculum.pupilsightProgramID = "' . $pupilsightProgramID . '" ')
                    ->where('subjectToClassCurriculum.pupilsightDepartmentID = "' . $departmentId . '" ')
                    ->where('subjectToClassCurriculum.pupilsightYearGroupID = "' . $pupilsightYearGroupID . '" ');
                $newdata = $this->runQuery($query2, $criteria);
                if (!empty($newdata->data[0]['id'])) {
                    $data[$k]['subject_display_name'] = $newdata->data[0]['subject_display_name'];
                    $data[$k]['subject_type'] = $newdata->data[0]['subject_type'];
                    $data[$k]['di_mode'] = $newdata->data[0]['di_mode'];
                    $data[$k]['id'] = $newdata->data[0]['id'];
                    $data[$k]['checked'] = '1';
                    $data[$k]['pos'] = $newdata->data[0]['pos'];
                } else {
                    $data[$k]['subject_display_name'] = '';
                    $data[$k]['subject_type'] = '';
                    $data[$k]['di_mode'] = '';
                    $data[$k]['id'] = '';
                    $data[$k]['checked'] = '';
                    $data[$k]['pos'] = $i;
                    $i++;
                }
            }
        }

        $returndata = array();
        if (!empty($data)) {
            foreach ($data as $k => $dk) {
                $pos = $dk['pos'];
                $returndata[$pos] = $dk;
            }
            ksort($returndata);
            $returndata = array_values($returndata);
        }


        // echo '<pre>';
        // print_r($returndata);
        // echo '</pre>';

        $res->data = $returndata;
        return $res;
    }

    public function getSkill(QueryCriteria $criteria)
    {
        $query = $this
            ->newQuery()
            ->from('ac_manage_skill')
            ->cols([
                'id', 'name', 'code', 'description'
            ])
            ->groupby(['ac_manage_skill.id'])
            ->orderby(['id DESC']);

        return $this->runQuery($query, $criteria, TRUE);
    }
}
