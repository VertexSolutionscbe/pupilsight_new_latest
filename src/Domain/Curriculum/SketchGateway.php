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
class SketchGateway extends QueryableGateway
{  
    use TableAware;

    private static $tableName = 'examinationReportTemplateSketch';

    private static $searchableColumns = ['sketch_name', 'sketch_code'];

    public function getAllSketch(QueryCriteria $criteria)
    {
        
        $query = $this
            ->newQuery()
            ->from('examinationReportTemplateSketch')
            ->cols([
                'examinationReportTemplateSketch.*','pupilsightSchoolYear.name AS acedemic_year','pupilsightProgram.name as progname'
            ])
            ->leftJoin('pupilsightSchoolYear', 'examinationReportTemplateSketch.pupilsightSchoolYearID=pupilsightSchoolYear.pupilsightSchoolYearID')
            ->leftJoin('pupilsightProgram', 'examinationReportTemplateSketch.pupilsightProgramID=pupilsightProgram.pupilsightProgramID')
            ->orderBy(['examinationReportTemplateSketch.id DESC']);
            
            $res = $this->runQuery($query, $criteria, true);
            $data = $res->data;
            if(!empty($data)){   
                foreach($data as $k => $d){
                    $sketch_id = $d['id'];
                    $query2 = $this
                        ->newQuery()
                        ->from('examinationTest')
                        ->cols([
                            'GROUP_CONCAT(DISTINCT name) AS test_name'
                        ])
                        ->where('examinationTest.sketch_id = "'.$sketch_id.'" ');
                    $newdata = $this->runQuery($query2, $criteria);
                    if(!empty($newdata->data[0]['test_name'])){     
                        $data[$k]['test_name'] = $newdata->data[0]['test_name'];
                    } else {
                        $data[$k]['test_name'] = '';
                    }

                    $classId= explode(',', $d['class_ids']);
                    $newclassId= implode(',', $classId);
                    $newclassId= rtrim($newclassId, ", ");
                    //echo $newclassId;
                    // print_r($classId);
                     //die();
                    $query3 = $this
                        ->newQuery()
                        ->from('pupilsightYearGroup')
                        ->cols([
                            'pupilsightYearGroupID AS id','GROUP_CONCAT(DISTINCT name) AS class_name'
                        ])
                        ->where('pupilsightYearGroupID IN ('.$newclassId.') ');
                    // echo $query3;
                    // die(); 
                    $classdata = $this->runQuery($query3, $criteria);

                    if(!empty($classdata->data[0]['class_name'])){     
                        $data[$k]['class_name'] = $classdata->data[0]['class_name'];
                    } else {
                        $data[$k]['class_name'] = '';
                    }
                }
            }    
            
        $res->data = $data;
        return $res;

    }
    
    
}