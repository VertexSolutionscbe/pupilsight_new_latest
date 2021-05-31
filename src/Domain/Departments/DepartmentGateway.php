<?php
/*
Pupilsight, Flexible & Open School System
*/

namespace Pupilsight\Domain\Departments;

use Pupilsight\Domain\Traits\TableAware;
use Pupilsight\Domain\QueryCriteria;
use Pupilsight\Domain\QueryableGateway;

/**
 * @version v17
 * @since   v17
 */
class DepartmentGateway extends QueryableGateway
{
    use TableAware;

    private static $tableName = 'pupilsightDepartment';

    private static $searchableColumns = ['name','type'];
    
    /**
     * @param QueryCriteria $criteria
     * @return DataSet
     */
    public function queryDepartments(QueryCriteria $criteria, $serchType)
    {
        $query = $this
            ->newQuery()
            ->from($this->getTableName())
            ->cols([
                'pupilsightDepartmentID', 'name', 'nameShort', 'type'
            ]);
            if(!empty($serchType)){
                $query->where('type = "'.$serchType.'" ');
            }

        return $this->runQuery($query, $criteria);
    }

    public function selectStaffByDepartment($pupilsightDepartmentID)
    {
        $data = array('pupilsightDepartmentID' => $pupilsightDepartmentID);
        $sql = "SELECT preferredName, surname, title
                FROM pupilsightDepartmentStaff 
                JOIN pupilsightPerson ON (pupilsightDepartmentStaff.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) 
                WHERE pupilsightPerson.status='Full' AND pupilsightDepartmentID=:pupilsightDepartmentID 
                ORDER BY surname, preferredName";

        return $this->db()->select($sql, $data);
    }

    public function selectMemberOfDepartmentByRole($pupilsightDepartmentID, $pupilsightPersonID, array $roles = ['Teacher'])
    {
        $data = array('pupilsightDepartmentID' => $pupilsightDepartmentID, 'pupilsightPersonID' => $pupilsightPersonID, 'roles' => implode(',', $roles));
        $sql = "SELECT pupilsightDepartmentStaff.* 
                FROM pupilsightDepartment 
                JOIN pupilsightDepartmentStaff ON (pupilsightDepartmentStaff.pupilsightDepartmentID=pupilsightDepartment.pupilsightDepartmentID) 
                WHERE pupilsightDepartment.pupilsightDepartmentID=:pupilsightDepartmentID 
                AND .pupilsightDepartmentStaff.pupilsightPersonID=:pupilsightPersonID 
                AND FIND_IN_SET(pupilsightDepartmentStaff.role, :roles)";

        return $this->db()->select($sql, $data);
    }



    public function get_assignedsub_toclass(QueryCriteria $criteria) {
      

//`id`,`pupilsightProgramID`,`pupilsightYearGroupID`,`pupilsightDepartmentID`

            $query = $this
            ->newQuery()
            ->from('assign_core_subjects_toclass ')
            ->cols([
                'assign_core_subjects_toclass.*','pupilsightProgram.name AS program_name','pupilsightDepartment.pupilsightDepartmentID','pupilsightProgram.pupilsightProgramID','pupilsightYearGroup.pupilsightYearGroupID',"GROUP_CONCAT(DISTINCT pupilsightDepartment.name SEPARATOR ', ') as subject","GROUP_CONCAT(DISTINCT pupilsightYearGroup.name SEPARATOR ', ') as class",'pupilsightSchoolYear.name as academic'
            ])
            ->leftJoin('pupilsightDepartment', 'assign_core_subjects_toclass.pupilsightDepartmentID=pupilsightDepartment.pupilsightDepartmentID')
            ->leftJoin('pupilsightSchoolYear', 'assign_core_subjects_toclass.pupilsightSchoolYearID=pupilsightSchoolYear.pupilsightSchoolYearID')
            ->leftJoin('pupilsightProgram', 'assign_core_subjects_toclass.pupilsightProgramID=pupilsightProgram.pupilsightProgramID')
            ->leftJoin('pupilsightYearGroup', 'assign_core_subjects_toclass.pupilsightYearGroupID=pupilsightYearGroup.pupilsightYearGroupID')
            
            //->where('fn_fee_invoice_class_assign.fn_fee_invoice_id = :fn_fee_invoice_id')            
           // ->bindValue('fn_fee_invoice_id', $fn_fee_invoice_id)
            ->groupBy(['assign_core_subjects_toclass.pupilsightYearGroupID,assign_core_subjects_toclass.pupilsightProgramID']);

 
            return $this->runQuery($query, $criteria);
        }    


        

        public function get_secondlang_assigned_toclass(QueryCriteria $criteria) {
      

            //`id`,`pupilsightProgramID`,`pupilsightYearGroupID`,`pupilsightDepartmentID`
            
                        $query = $this
                        ->newQuery()
                        ->from('assign_second_language_toclass')
                        ->cols([
                            'assign_second_language_toclass.*','pupilsightProgram.name AS program_name',"GROUP_CONCAT(DISTINCT pupilsightDepartment.name SEPARATOR ', ') as subject","GROUP_CONCAT(DISTINCT pupilsightYearGroup.name SEPARATOR ', ') as class"
                        ])
                       ->leftJoin('pupilsightDepartment', 'assign_second_language_toclass.pupilsightDepartmentID=pupilsightDepartment.pupilsightDepartmentID')
                        ->leftJoin('pupilsightProgram', 'assign_second_language_toclass.pupilsightProgramID=pupilsightProgram.pupilsightProgramID')
                        ->leftJoin('pupilsightYearGroup', 'assign_second_language_toclass.pupilsightYearGroupID=pupilsightYearGroup.pupilsightYearGroupID')
                        
                        //->where('fn_fee_invoice_class_assign.fn_fee_invoice_id = :fn_fee_invoice_id')            
                       // ->bindValue('fn_fee_invoice_id', $fn_fee_invoice_id)
                        ->groupBy(['assign_second_language_toclass.pupilsightYearGroupID,assign_second_language_toclass.pupilsightProgramID']);
            
             
                        return $this->runQuery($query, $criteria);
                    }   


                    public function get_thirdlang_assigned_toclass(QueryCriteria $criteria) {
      

                        //`id`,`pupilsightProgramID`,`pupilsightYearGroupID`,`pupilsightDepartmentID`
                        
                                    $query = $this
                                    ->newQuery()
                                    ->from('assign_third_language_toclass')
                                    ->cols([
                                        'assign_third_language_toclass.*','pupilsightProgram.name AS program_name',"GROUP_CONCAT(DISTINCT pupilsightDepartment.name SEPARATOR ', ') as subject","GROUP_CONCAT(DISTINCT pupilsightYearGroup.name SEPARATOR ', ') as class"
                                    ])
                                   ->leftJoin('pupilsightDepartment', 'assign_third_language_toclass.pupilsightDepartmentID=pupilsightDepartment.pupilsightDepartmentID')
                                    ->leftJoin('pupilsightProgram', 'assign_third_language_toclass.pupilsightProgramID=pupilsightProgram.pupilsightProgramID')
                                    ->leftJoin('pupilsightYearGroup', 'assign_third_language_toclass.pupilsightYearGroupID=pupilsightYearGroup.pupilsightYearGroupID')
                                    
                                    //->where('fn_fee_invoice_class_assign.fn_fee_invoice_id = :fn_fee_invoice_id')            
                                   // ->bindValue('fn_fee_invoice_id', $fn_fee_invoice_id)
                                    ->groupBy(['assign_third_language_toclass.pupilsightYearGroupID,assign_third_language_toclass.pupilsightProgramID']);
                        
                         
                                    return $this->runQuery($query, $criteria);
                                } 
   
        
       
       
   
}
