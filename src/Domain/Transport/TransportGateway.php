<?php
/*
Pupilsight, Flexible & Open School System
*/

namespace Pupilsight\Domain\Transport;

use Pupilsight\Domain\DBQuery;
use Pupilsight\Domain\Traits\TableAware;
use Pupilsight\Domain\QueryCriteria;
use Pupilsight\Domain\QueryableGateway;


/**
 * Invoice Gateway
 *
 * @version v16
 * @since   v16
*/

class TransportGateway extends QueryableGateway
{
    use TableAware;

    private static $tableName = 'trans_bus_details';
    private static $searchableColumns = [];

    /**
     * @param QueryCriteria $criteria
     * @return DataSet
     */

    public function getRouteStructure(QueryCriteria $criteria)
    {
        $query = $this
            ->newQuery()
            ->from('trans_routes')
            ->cols([
                'trans_routes.*','trans_bus_details.name as busname','COUNT(trans_route_stops.id) as totalstops'
            ])
            ->leftJoin('trans_bus_details', 'trans_routes.bus_id=trans_bus_details.id')
            ->leftJoin('trans_route_stops', 'trans_routes.id=trans_route_stops.route_id')
            ->groupby(['trans_routes.id'])
            ->orderby(['id DESC']);
        return $this->runQuery($query, $criteria, TRUE);
    }

    public function getBusdetails(QueryCriteria $criteria)
    {
        
        $query = $this
        ->newQuery()
        ->from('trans_bus_details')
        ->cols([
            'trans_bus_details.*','COUNT(trans_routes.id) as chkkount'
        ])
        ->leftJoin('trans_routes', 'trans_bus_details.id=trans_routes.bus_id')
        ->groupby(['trans_bus_details.id'])
        ->orderby(['id DESC']);

        return $this->runQuery($query, $criteria, TRUE);
    }

    public function getBusdetails_byid(QueryCriteria $criteria, $id)
    {
      $query = $this
        ->newQuery()
        ->from('trans_bus_details')
        ->cols([
            'trans_bus_details.*'
        ])
        ->where('trans_bus_details.id = "'.$id.'" ');
         return $this->runQuery($query, $criteria);
    }

    public function getStudentData(QueryCriteria $criteria, $pupilsightProgramID, $pupilsightSchoolYearIDpost, $pupilsightYearGroupID, $pupilsightRollGroupID, $search)
    {
        $pupilsightRoleIDAll = '003';
        if (!empty($pupilsightProgramID)) {
            $query = $this
            ->newQuery()
            ->from('pupilsightPerson')
            ->cols([
                'trans_route_assign.*','pupilsightPerson.pupilsightPersonID AS stuid','pupilsightPerson.pupilsightPersonID AS studentid','pupilsightPerson.officialName AS student_name','pupilsightRollGroup.name AS section','pupilsightYearGroup.name AS class',
                'trans_route_stops.stop_name','trans_routes.route_name','pupilsightSchoolYear.name as academicyear'
            ])
         
            ->leftJoin('trans_route_assign', 'pupilsightPerson.pupilsightPersonID=trans_route_assign.pupilsightPersonID')
            ->leftJoin('pupilsightStudentEnrolment', 'pupilsightPerson.pupilsightPersonID=pupilsightStudentEnrolment.pupilsightPersonID')
            ->leftJoin('pupilsightSchoolYear', 'pupilsightStudentEnrolment.pupilsightSchoolYearID=pupilsightSchoolYear.pupilsightSchoolYearID')
            ->leftJoin(' pupilsightRollGroup', 'pupilsightStudentEnrolment.pupilsightRollGroupID=pupilsightRollGroup.pupilsightRollGroupID')
            ->leftJoin('pupilsightProgram', 'pupilsightStudentEnrolment.pupilsightProgramID=pupilsightProgram.pupilsightProgramID')
            ->leftJoin(' pupilsightYearGroup', 'pupilsightStudentEnrolment.pupilsightYearGroupID=pupilsightYearGroup.pupilsightYearGroupID')
            ->leftJoin('trans_route_stops', 'trans_route_assign.route_stop_id=trans_route_stops.id')
            ->leftJoin('trans_routes', 'trans_route_assign.route_id=trans_routes.id');

            if(!empty($pupilsightProgramID)){
                $query->where('pupilsightProgram.pupilsightProgramID = "'.$pupilsightProgramID.'" ');
            } 
            if(!empty($pupilsightSchoolYearIDpost)){
                $query->where('pupilsightStudentEnrolment.pupilsightSchoolYearID = "'.$pupilsightSchoolYearIDpost.'" ');
            } 
            if(!empty($pupilsightYearGroupID)){
                $query->where('pupilsightStudentEnrolment.pupilsightYearGroupID = "'.$pupilsightYearGroupID.'" ');
            } 
            if(!empty($pupilsightRollGroupID)){
                $query->where('pupilsightStudentEnrolment.pupilsightRollGroupID = "'.$pupilsightRollGroupID.'" ');
            } 
            if(!empty($search)){
                $query->where('pupilsightPerson.officialName LIKE "%'.$search.'%" ')
                ->orwhere('pupilsightPerson.pupilsightPersonID = "'.$search.'" ');;

            } 
            $query->where('pupilsightPerson.pupilsightRoleIDAll = "'.$pupilsightRoleIDAll.'" ')
                ->groupBy(['pupilsightPerson.pupilsightPersonID'])
                ->orderBy(['pupilsightPerson.pupilsightPersonID ASC']) ;
          // echo $query;
        }else{
            $query = $this
            ->newQuery()
            ->from('pupilsightPerson')
            ->cols([
                'trans_route_assign.*','pupilsightPerson.pupilsightPersonID AS stuid','pupilsightPerson.pupilsightPersonID AS studentid','pupilsightPerson.officialName AS student_name','pupilsightRollGroup.name AS section','pupilsightYearGroup.name AS class',
                'trans_route_stops.stop_name','trans_routes.route_name','pupilsightSchoolYear.name as academicyear'
            ])
           
            ->leftJoin('trans_route_assign', 'pupilsightPerson.pupilsightPersonID=trans_route_assign.pupilsightPersonID')
            ->leftJoin('pupilsightStudentEnrolment', 'pupilsightPerson.pupilsightPersonID=pupilsightStudentEnrolment.pupilsightPersonID')
            ->leftJoin('pupilsightSchoolYear', 'pupilsightStudentEnrolment.pupilsightSchoolYearID=pupilsightSchoolYear.pupilsightSchoolYearID')
            ->leftJoin(' pupilsightRollGroup', 'pupilsightStudentEnrolment.pupilsightRollGroupID=pupilsightRollGroup.pupilsightRollGroupID')
            ->leftJoin(' pupilsightYearGroup', 'pupilsightStudentEnrolment.pupilsightYearGroupID=pupilsightYearGroup.pupilsightYearGroupID')
            ->leftJoin('trans_route_stops', 'trans_route_assign.route_stop_id=trans_route_stops.id')
            ->leftJoin('trans_routes', 'trans_route_assign.route_id=trans_routes.id')
            ->where('pupilsightPerson.pupilsightRoleIDAll = "'.$pupilsightRoleIDAll.'" ')
            ->where('trans_route_assign.pupilsightSchoolYearID = "'.$pupilsightSchoolYearIDpost.'" ')
            ->groupBy(['pupilsightPerson.pupilsightPersonID'])
            ->orderBy(['pupilsightPerson.pupilsightPersonID ASC']);
            
        }
        $res = $this->runQuery($query, $criteria);
        $data = $res->data;

       

        if(!empty($data)){
            foreach($data as $k=>$d){
                $pid = $d['pupilsightPersonID'];
                $rid = $d['route_id'];
                if(!empty($rid)){
                    $chkpayment = $this->chkTransportPayment($rid,$pid);
                    $data[$k]['chk_payment'] = $chkpayment;
                } else {
                    $data[$k]['chk_payment'] = '';
                }
               
                $type = $d['type'];
                
                if($type == 'onward'){
                    $newtype = 'return';
                    $data[$k]['onward_route_name'] = $d['route_name'];
                    $data[$k]['onward_stop_name'] = $d['stop_name'];
                    
                //   $query2 = $this
                //     ->newQuery()
                //     ->from('trans_route_assign')
                //     ->cols([
                //         'trans_route_stops.stop_name as return_stop_name','trans_routes.route_name as return_route_name'
                //     ])
                //     ->leftJoin('trans_route_stops', 'trans_route_assign.route_stop_id=trans_route_stops.id')
                //     ->leftJoin('trans_routes', 'trans_route_assign.route_id=trans_routes.id')
                //     ->where('trans_route_assign.pupilsightPersonID = "'.$pid.'" ')
                //     ->where('trans_route_assign.type = "'.$newtype.'" ')
                //     ->groupby(['trans_route_assign.pupilsightPersonID']);
                
                //     $newdata = $this->runQuery($query2, $criteria);

                    $db = new DBQuery();
                    $str = "SELECT  `trans_route_stops`.`stop_name` AS `return_stop_name`, `trans_routes`.`id` AS `return_route_id`, `trans_routes`.`route_name` AS `return_route_name` FROM `trans_route_assign` LEFT JOIN `trans_route_stops` ON `trans_route_assign`.`route_stop_id`=`trans_route_stops`.`id` LEFT JOIN `trans_routes` ON `trans_route_assign`.`route_id`=`trans_routes`.`id` WHERE `trans_route_assign`.`pupilsightPersonID` = '".$pid."' AND `trans_route_assign`.`type` = '".$newtype."' GROUP BY `trans_route_assign`.`pupilsightPersonID`";
                    $newdata = $db->select($str);
                    
                    if(!empty($newdata->data[0]['return_route_name'])){
                            $data[$k]['return_route_id'] = $newdata->data[0]['return_route_id'];
                            $data[$k]['return_route_name'] = $newdata->data[0]['return_route_name'];
                            $data[$k]['return_stop_name'] = $newdata->data[0]['return_stop_name'];
                            $rid = $newdata->data[0]['return_route_id'];
                            if(!empty($rid)){
                                $chkpayment = $this->chkTransportPayment($rid,$pid);
                                $data[$k]['return_chk_payment'] = $chkpayment;
                            }
                    } else {
                            $data[$k]['return_chk_payment'] = '';
                            $data[$k]['return_route_id'] = '';
                            $data[$k]['return_route_name'] = '';
                            $data[$k]['return_stop_name'] = '';
                    }
                } else {
                    $newtype = 'onward';
                    $data[$k]['return_route_name'] = $d['route_name'];
                    $data[$k]['return_stop_name'] = $d['stop_name'];
                    $data[$k]['return_chk_payment'] = '';
                    
                //    echo $query2 = $this
                //     ->newQuery()
                //     ->from('trans_route_assign')
                //     ->cols([
                //         'trans_route_stops.stop_name as return_stop_name','trans_routes.route_name as return_route_name'
                //     ])
                //     ->leftJoin('trans_route_stops', 'trans_route_assign.route_stop_id=trans_route_stops.id')
                //     ->leftJoin('trans_routes', 'trans_route_assign.route_id=trans_routes.id')
                //     ->where('trans_route_assign.pupilsightPersonID = "'.$pid.'" ')
                //     ->where('trans_route_assign.type = "'.$newtype.'" ')
                //     ->groupby(['trans_route_assign.pupilsightPersonID']);
                    
                    $db = new DBQuery();
                    $newdata = $db->select("SELECT `trans_route_stops`.`stop_name` AS `return_stop_name`, `trans_routes`.`route_name` AS `return_route_name` FROM `trans_route_assign` LEFT JOIN `trans_route_stops` ON `trans_route_assign`.`route_stop_id`=`trans_route_stops`.`id` LEFT JOIN `trans_routes` ON `trans_route_assign`.`route_id`=`trans_routes`.`id` WHERE `trans_route_assign`.`pupilsightPersonID` = '".$pid."' AND `trans_route_assign`.`type` = '".$newtype."' GROUP BY `trans_route_assign`.`pupilsightPersonID`");

                    if(!empty($newdata->data[0]['return_route_name'])){
                            $data[$k]['onward_route_name'] = $newdata->data[0]['return_route_name'];
                            $data[$k]['onward_stop_name'] = $newdata->data[0]['return_stop_name'];
                    } else {
                            $data[$k]['onward_route_name'] = '';
                            $data[$k]['onward_stop_name'] = '';
                    }
                }
                
               
            }
             
           
        }
         //echo '<pre>';
         //print_r($data);
         //echo '</pre>';
         //die();
        $res->data = $data;
        return $res;
    }

    function chkTransportPayment($rid,$pid){
        $db = new DBQuery();
        $chkdata = $db->select("SELECT GROUP_CONCAT(DISTINCT c.fn_fee_invoice_id) as invid FROM trans_route_price AS a LEFT JOIN fn_fee_invoice AS b ON a.schedule_id = b.transport_schedule_id LEFT JOIN fn_fee_invoice_student_assign AS c ON b.id = c.fn_fee_invoice_id WHERE a.route_id = '".$rid."' AND c.pupilsightPersonID = '".$pid."' GROUP BY c.pupilsightPersonID ");
        
        $chk = '';
        if(!empty($chkdata->data[0]['invid'])){
            $invid = $chkdata->data[0]['invid'];
            $paychk = $db->select("SELECT GROUP_CONCAT(DISTINCT fn_fees_invoice_id) as chkid FROM fn_fees_student_collection WHERE fn_fees_invoice_id IN (".$invid.") AND pupilsightPersonID = '".$pid."' ");
        //     echo '<pre>';
        // print_r($paychk);
        // echo '</pre>';
            if(!empty($paychk->data[0]['invid'])){
                $chk = 'paid';
            } else {
                $chk = 'unpaid';
            }
        }
        return $chk;
    }

    public function getStaffData(QueryCriteria $criteria, $search, $pupilsightSchoolYearID)
    {
       
        $pupilsightRoleIDAll = '001,002,006';
      
           // print_r($pupilsightProgramID);
            $query = $this
            ->newQuery()
            ->from('pupilsightPerson')
            ->cols([
                'trans_route_assign.*','pupilsightPerson.pupilsightPersonID AS stuid','pupilsightPerson.officialName AS student_name','trans_route_stops.stop_name','trans_routes.route_name'
            ])
         
            ->leftJoin('trans_route_assign', 'pupilsightPerson.pupilsightPersonID=trans_route_assign.pupilsightPersonID')
            ->leftJoin('trans_route_stops', 'trans_route_assign.route_stop_id=trans_route_stops.id')
            ->leftJoin('trans_routes', 'trans_route_assign.route_id=trans_routes.id');
            if(!empty($search)){
                $query->where('pupilsightPerson.officialName LIKE "%'.$search.'%" ');
            } 
            $query->where('pupilsightPerson.pupilsightRoleIDAll IN ('.$pupilsightRoleIDAll.') ')
                //->where('trans_route_assign.pupilsightSchoolYearID = "'.$pupilsightSchoolYearID.'" ')
                ->groupBy(['pupilsightPerson.pupilsightPersonID'])
                ->orderBy(['pupilsightPerson.pupilsightPersonID ASC']);
          // echo $query;
       
        $res = $this->runQuery($query, $criteria);
        $data = $res->data;
        // echo '<pre>';
        // print_r($data);
        // echo '</pre>';
        // die();
        if(!empty($data)){
            foreach($data as $k=>$d){
                $type = $d['type'];
                $pid = $d['pupilsightPersonID'];
                if($type == 'onward'){
                    $newtype = 'return';
                    $data[$k]['onward_route_name'] = $d['route_name'];
                    $data[$k]['onward_stop_name'] = $d['stop_name'];
                    
                    $query2 = $this
                    ->newQuery()
                    ->from('trans_route_assign')
                    ->cols([
                        'trans_route_stops.stop_name as return_stop_name','trans_routes.route_name as return_route_name'
                    ])
                    ->leftJoin('trans_route_stops', 'trans_route_assign.route_stop_id=trans_route_stops.id')
                    ->leftJoin('trans_routes', 'trans_route_assign.route_id=trans_routes.id')
                    ->where('trans_route_assign.pupilsightPersonID = "'.$pid.'" ')
                    ->where('trans_route_assign.type = "'.$newtype.'" ')
                    ->groupby(['trans_route_assign.pupilsightPersonID']);
                
                    $newdata = $this->runQuery($query2, $criteria);
                    if(!empty($newdata->data[0]['return_route_name'])){
                            $data[$k]['return_route_name'] = $newdata->data[0]['return_route_name'];
                            $data[$k]['return_stop_name'] = $newdata->data[0]['return_stop_name'];
                    } else {
                            $data[$k]['return_route_name'] = '';
                            $data[$k]['return_stop_name'] = '';
                    }
                } else {
                    $newtype = 'onward';
                    $data[$k]['return_route_name'] = $d['route_name'];
                    $data[$k]['return_stop_name'] = $d['stop_name'];
                    
                    $query2 = $this
                    ->newQuery()
                    ->from('trans_route_assign')
                    ->cols([
                        'trans_route_stops.stop_name as return_stop_name','trans_routes.route_name as return_route_name'
                    ])
                    ->leftJoin('trans_route_stops', 'trans_route_assign.route_stop_id=trans_route_stops.id')
                    ->leftJoin('trans_routes', 'trans_route_assign.route_id=trans_routes.id')
                    ->where('trans_route_assign.pupilsightPersonID = "'.$pid.'" ')
                    ->where('trans_route_assign.type = "'.$newtype.'" ')
                    ->groupby(['trans_route_assign.pupilsightPersonID']);
                
                    $newdata = $this->runQuery($query2, $criteria);
                    if(!empty($newdata->data[0]['return_route_name'])){
                            $data[$k]['onward_route_name'] = $newdata->data[0]['return_route_name'];
                            $data[$k]['onward_stop_name'] = $newdata->data[0]['return_stop_name'];
                    } else {
                            $data[$k]['onward_route_name'] = '';
                            $data[$k]['onward_stop_name'] = '';
                    }
                }
                
               
            }
           
        }
        // echo '<pre>';
        // print_r($data);
        // echo '</pre>';
        // die();
        $res->data = $data;
        return $res;
    }

    public function getViewMember(QueryCriteria $criteria, $pupilsightSchoolYearID){
        $pupilsightRoleIDAll = '003';
        $query = $this
            ->newQuery()
            ->from('trans_route_assign')
            ->cols([
                'trans_route_assign.*','pupilsightPerson.pupilsightPersonID AS stuid','pupilsightPerson.officialName AS student_name','pupilsightRollGroup.name AS section','pupilsightYearGroup.name AS class',
                'trans_route_stops.stop_name','trans_routes.id as routeid','trans_routes.route_name','pupilsightSchoolYear.name as academic_year','trans_bus_details.name as bus_name','pupilsightRole.category'
            ])
            
            ->leftJoin('pupilsightPerson', 'trans_route_assign.pupilsightPersonID=pupilsightPerson.pupilsightPersonID')
            ->leftJoin('pupilsightStudentEnrolment', 'pupilsightPerson.pupilsightPersonID=pupilsightStudentEnrolment.pupilsightPersonID')
            ->leftJoin('pupilsightSchoolYear', 'pupilsightStudentEnrolment.pupilsightSchoolYearID=pupilsightSchoolYear.pupilsightSchoolYearID')
            ->leftJoin(' pupilsightRollGroup', 'pupilsightStudentEnrolment.pupilsightRollGroupID=pupilsightRollGroup.pupilsightRollGroupID')
            ->leftJoin(' pupilsightYearGroup', 'pupilsightStudentEnrolment.pupilsightYearGroupID=pupilsightYearGroup.pupilsightYearGroupID')
            ->leftJoin('trans_route_stops', 'trans_route_assign.route_stop_id=trans_route_stops.id')
            ->leftJoin('trans_routes', 'trans_route_assign.route_id=trans_routes.id')
            ->leftJoin('trans_bus_details', 'trans_routes.bus_id=trans_bus_details.id')
            ->leftJoin('pupilsightRole', 'pupilsightPerson.pupilsightRoleIDPrimary=pupilsightRole.pupilsightRoleID')
            ->where('pupilsightPerson.canLogin = "Y" ')
            ->where('trans_route_assign.pupilsightSchoolYearID = "'.$pupilsightSchoolYearID.'" ')
            ->groupBy(['pupilsightPerson.pupilsightPersonID'])
            ->groupBy(['trans_route_assign.route_id']);
            $criteria->addFilterRules([
                'type' => function ($query, $type) {
                        return $query
                            ->where('trans_routes.id = :type')
                            ->bindValue('type', ucfirst($type));
                },
            ]);
            $query->orderBy(['pupilsightPerson.pupilsightPersonID ASC']);
        
            $res = $this->runQuery($query, $criteria);
            $data = $res->data;
            // echo '<pre>';
            // print_r($data);
            // echo '</pre>';
            // die();
            if(!empty($data)){
                foreach($data as $k=>$d){
                    $type = $d['type'];
                    $pid = $d['pupilsightPersonID'];
                    $route_id = $d['route_id'];
                    if($type == 'onward'){
                        $newtype = 'return';
                        $data[$k]['onward_route_name'] = $d['route_name'];
                        $data[$k]['onward_stop_name'] = $d['stop_name'];
                        
                        $query2 = $this
                        ->newQuery()
                        ->from('trans_route_assign')
                        ->cols([
                            'trans_route_stops.stop_name as return_stop_name','trans_routes.route_name as return_route_name'
                        ])
                        ->leftJoin('trans_route_stops', 'trans_route_assign.route_stop_id=trans_route_stops.id')
                        ->leftJoin('trans_routes', 'trans_route_assign.route_id=trans_routes.id')
                        ->where('trans_route_assign.pupilsightPersonID = "'.$pid.'" ')
                        ->where('trans_route_assign.type = "'.$newtype.'" ')
                        ->where('trans_route_assign.route_id = "'.$route_id.'" ')
                        ->groupby(['trans_route_assign.pupilsightPersonID']);
                    
                        $newdata = $this->runQuery($query2, $criteria);
                        if(!empty($newdata->data[0]['return_route_name'])){
                                $data[$k]['return_route_name'] = $newdata->data[0]['return_route_name'];
                                $data[$k]['return_stop_name'] = $newdata->data[0]['return_stop_name'];
                        } else {
                                $data[$k]['return_route_name'] = '';
                                $data[$k]['return_stop_name'] = '';
                        }
                    } else {
                        $newtype = 'onward';
                        $data[$k]['return_route_name'] = $d['route_name'];
                        $data[$k]['return_stop_name'] = $d['stop_name'];
                        
                        $query2 = $this
                        ->newQuery()
                        ->from('trans_route_assign')
                        ->cols([
                            'trans_route_stops.stop_name as return_stop_name','trans_routes.route_name as return_route_name'
                        ])
                        ->leftJoin('trans_route_stops', 'trans_route_assign.route_stop_id=trans_route_stops.id')
                        ->leftJoin('trans_routes', 'trans_route_assign.route_id=trans_routes.id')
                        ->where('trans_route_assign.pupilsightPersonID = "'.$pid.'" ')
                        ->where('trans_route_assign.type = "'.$newtype.'" ')
                        ->where('trans_route_assign.route_id = "'.$route_id.'" ')
                        ->groupby(['trans_route_assign.pupilsightPersonID']);
                    
                        $newdata = $this->runQuery($query2, $criteria);
                        if(!empty($newdata->data[0]['return_route_name'])){
                                $data[$k]['onward_route_name'] = $newdata->data[0]['return_route_name'];
                                $data[$k]['onward_stop_name'] = $newdata->data[0]['return_stop_name'];
                        } else {
                                $data[$k]['onward_route_name'] = '';
                                $data[$k]['onward_stop_name'] = '';
                        }
                    }
                    if(!empty($data[$k]['onward_stop_name']) && !empty($data[$k]['return_stop_name'])){
                        $data[$k]['route_type'] = 'Two Way';
                    } else if(!empty($data[$k]['onward_stop_name']) && empty($data[$k]['return_stop_name'])){
                        $data[$k]['route_type'] = 'One Way';
                    } else if(empty($data[$k]['onward_stop_name']) && !empty($data[$k]['return_stop_name'])){
                        $data[$k]['route_type'] = 'One Way';
                    } else {
                        $data[$k]['route_type'] = 'No Way';
                    }
                   
                }
               
            }
            // echo '<pre>';
            // print_r($data);
            // echo '</pre>';
            // die();
            $res->data = $data;
            return $res;   
    }
    
    public function getStaff(QueryCriteria $criteria){
    
    $query = $this
    ->newQuery()
    ->from('pupilsightStaff')
    ->cols([
        'pupilsightPerson.pupilsightPersonID', 'pupilsightPerson.title', 'pupilsightPerson.surname', 'pupilsightPerson.preferredName', 'pupilsightPerson.status', 'pupilsightPerson.username', 'pupilsightPerson.image_240',
        'pupilsightStaff.pupilsightStaffID  as stuid', 'pupilsightStaff.initials', 'pupilsightStaff.type', 'pupilsightStaff.jobTitle'
    ])
    
     ->innerJoin('pupilsightPerson', 'pupilsightPerson.pupilsightPersonID=pupilsightStaff.pupilsightPersonID');
    
     if (!$criteria->hasFilter('all')) {
        $query->where('pupilsightPerson.status = "Full"');
    }

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


    public function getstops(QueryCriteria $criteria , $inputdata){
        // print_r($inputdata);
      
        if(!empty($inputdata)){

            $query = $this 
            ->newQuery()
            ->from('trans_route_stops')
            ->cols([
                'trans_route_stops.*','trans_route_stops.oneway_price * "'.$inputdata.'" AS onway' ,'trans_route_stops.twoway_price * "'.$inputdata.'" AS twoway'
            ]);
        }
        else{
            $query = $this 
            ->newQuery()
            ->from('trans_route_stops')
            ->cols([
                'trans_route_stops.*'
            ]);

        }
    
        return $this->runQuery($query, $criteria);
    }
    public function getroute(QueryCriteria $criteria ){
        $query = $this 
        ->newQuery()
        ->from('trans_routes')
        ->cols([
            'trans_routes.*'
        ]);
        return $this->runQuery($query,$criteria);
    }

    public function getclasses(QueryCriteria $criteria, $pupilsightProgramID, $pupilsightSchoolYearIDpost)
    {
        // die();
       
        if(!empty($pupilsightProgramID) ) {
            // print_r($pupilsightProgramID);
                   
        $pupilsightRoleIDAll = '003';
             $query = $this
             ->newQuery()
             ->from('pupilsightYearGroup')
             ->cols([
                'pupilsightYearGroup.pupilsightYearGroupID AS id','pupilsightYearGroup.name AS class'
             ])
          
             ->leftJoin('pupilsightStudentEnrolment', 'pupilsightYearGroup.pupilsightYearGroupID=pupilsightStudentEnrolment.pupilsightYearGroupID')
             ->leftJoin('pupilsightProgram', 'pupilsightStudentEnrolment.pupilsightProgramID=pupilsightProgram.pupilsightProgramID');
           
             if(!empty($pupilsightProgramID)){
                 $query->where('pupilsightProgram.pupilsightProgramID = "'.$pupilsightProgramID.'" ');
             } 
             if(!empty($pupilsightSchoolYearIDpost)){
                 $query->where('pupilsightStudentEnrolment.pupilsightSchoolYearID = "'.$pupilsightSchoolYearIDpost.'" ');
             } 
          
         
         }else{
             $query = $this
             ->newQuery()
             
             ->from('pupilsightYearGroup')
             ->cols([
                 'pupilsightYearGroup.pupilsightYearGroupID AS id','pupilsightYearGroup.name AS class'
                
             ]);
          
          
               // ->leftJoin('pupilsightStudentEnrolment', 'pupilsightSchoolYear.pupilsightSchoolYearID=pupilsightStudentEnrolment.pupilsightSchoolYearID')
             
           //  echo $query;    
         }

        return $this->runQuery($query, $criteria);
    }
    

    public function getTransSchedule(QueryCriteria $criteria){
        $query = $this
        ->newQuery()
        ->from('trans_schedule')
        ->cols([
            'trans_schedule.*','fn_fee_items.name as feeitemname','pupilsightSchoolYear.name as academic_year','fn_fees_head.name as feeheadname','inv.series_name as invoicename','rec.series_name as receiptname','trans_routes.route_name'
        ])
        ->leftJoin('trans_routes', 'trans_schedule.route_id=trans_routes.id')
        ->leftJoin('fn_fee_items', 'trans_schedule.fee_item_id=fn_fee_items.id')
        ->leftJoin('pupilsightSchoolYear', 'trans_schedule.pupilsightSchoolYearID=pupilsightSchoolYear.pupilsightSchoolYearID')
        ->leftJoin('fn_fees_head', 'trans_schedule.fee_head_id=fn_fees_head.id')
        ->leftJoin('fn_fee_series as inv', 'trans_schedule.invoice_series_id=inv.id')
        ->leftJoin('fn_fee_series as rec', 'trans_schedule.receipt_series_id=rec.id')
        ->orderby(['trans_schedule.id DESC']);
        
        return $this->runQuery($query, $criteria, TRUE);
    }    

    public function getTransportAssignItem(QueryCriteria $criteria)
    {
        $schedule_id ="";		
        if(isset($_REQUEST['id'])) {		
           $schedule_id= $_REQUEST['id'];		
        }	
        $query = $this
            ->newQuery()
            ->from('trans_schedule_assign_class')
            ->cols([
                'trans_schedule_assign_class.*','trans_schedule.schedule_name','pupilsightProgram.name AS program_name','pupilsightYearGroup.name AS class'
            ])
            ->leftJoin('trans_schedule', 'trans_schedule_assign_class.schedule_id=trans_schedule.id')
            ->leftJoin('pupilsightProgram', 'trans_schedule_assign_class.pupilsightProgramID=pupilsightProgram.pupilsightProgramID')
            ->leftJoin('pupilsightYearGroup', 'trans_schedule_assign_class.pupilsightYearGroupID=pupilsightYearGroup.pupilsightYearGroupID')
            ->where('trans_schedule_assign_class.schedule_id = '.$schedule_id);            
            //->bindValue('schedule_id', $schedule_id);

        return $this->runQuery($query, $criteria);
    }

    public function TransSchedule(QueryCriteria $criteria ){
        $query = $this 
        ->newQuery()
        ->from('trans_schedule')
        ->cols([
            'trans_schedule.*'
        ]);
        return $this->runQuery($query,$criteria);
    }

    
    public function getTransSchedulePrice(QueryCriteria $criteria){
        $schedule_id ="";		
        if(isset($_REQUEST['id'])) {		
           $schedule_id= $_REQUEST['id'];		
        }	
        $query = $this
        ->newQuery()
        ->from('trans_route_price')
        ->cols([
            'trans_schedule.schedule_name','trans_route_price.*','trans_routes.route_name','trans_route_stops.stop_name'
        ])
        ->leftJoin('trans_routes', 'trans_route_price.route_id=trans_routes.id')
        ->leftJoin('trans_schedule', 'trans_route_price.schedule_id=trans_schedule.id')
        ->leftJoin('trans_route_stops', 'trans_route_price.stop_id=trans_route_stops.id')
        ->where('trans_route_price.schedule_id = :schedule_id')            
        ->bindValue('schedule_id', $schedule_id);
        return $this->runQuery($query, $criteria, TRUE);
    }
    
    
    public function getchildData(QueryCriteria $criteria , $inputdata)
    {
       // print_r($inputdata);die();
        $pupilsightRoleIDAll = '003';
      
            $query = $this
            ->newQuery()
            ->from('pupilsightPerson')
            ->cols([
                'trans_route_assign.*','pupilsightPerson.pupilsightPersonID AS stuid','pupilsightPerson.pupilsightPersonID AS studentid','pupilsightPerson.officialName AS student_name','pupilsightRollGroup.name AS section','pupilsightYearGroup.name AS class',
                'trans_route_stops.stop_name','trans_routes.route_name','trans_route_stops.pickup_time','trans_route_stops.drop_time','pupilsightSchoolYear.name as academicyear','trans_bus_details.name','trans_bus_details.driver_name','trans_bus_details.driver_mobile','trans_bus_details.vehicle_number'
            ])
           
            ->leftJoin('trans_route_assign', 'pupilsightPerson.pupilsightPersonID=trans_route_assign.pupilsightPersonID')
            ->leftJoin('pupilsightStudentEnrolment', 'pupilsightPerson.pupilsightPersonID=pupilsightStudentEnrolment.pupilsightPersonID')
            ->leftJoin('pupilsightSchoolYear', 'pupilsightStudentEnrolment.pupilsightSchoolYearID=pupilsightSchoolYear.pupilsightSchoolYearID')
            ->leftJoin(' pupilsightRollGroup', 'pupilsightStudentEnrolment.pupilsightRollGroupID=pupilsightRollGroup.pupilsightRollGroupID')
            ->leftJoin(' pupilsightYearGroup', 'pupilsightStudentEnrolment.pupilsightYearGroupID=pupilsightYearGroup.pupilsightYearGroupID')
            ->leftJoin('trans_route_stops', 'trans_route_assign.route_stop_id=trans_route_stops.id')
            ->leftJoin('trans_routes', 'trans_route_assign.route_id=trans_routes.id')
            ->leftJoin('trans_bus_details', 'trans_routes.bus_id=trans_bus_details.id')
            
            ->where('pupilsightPerson.pupilsightPersonID = "'.$inputdata.'" ')
            ->groupBy(['pupilsightPerson.pupilsightPersonID'])
            ->orderBy(['pupilsightPerson.pupilsightPersonID ASC']);
            
    
        $res = $this->runQuery($query, $criteria);
        $data = $res->data;

        

        if(!empty($data)){
            foreach($data as $k=>$d){
                $type = $d['type'];
                $pid = $d['pupilsightPersonID'];
                if($type == 'onward'){
                    $newtype = 'return';
                    $data[$k]['onward_route_name'] = $d['route_name'];
                    $data[$k]['onward_stop_name'] = $d['stop_name'];
                    
                    $query2 = $this
                    ->newQuery()
                    ->from('trans_route_assign')
                    ->cols([
                        'trans_route_stops.stop_name as return_stop_name','trans_routes.route_name as return_route_name'
                    ])
                    ->leftJoin('trans_route_stops', 'trans_route_assign.route_stop_id=trans_route_stops.id')
                    ->leftJoin('trans_routes', 'trans_route_assign.route_id=trans_routes.id')
                    ->where('trans_route_assign.pupilsightPersonID = "'.$pid.'" ')
                    ->where('trans_route_assign.type = "'.$newtype.'" ')
                    ->groupby(['trans_route_assign.pupilsightPersonID']);
                
                    $newdata = $this->runQuery($query2, $criteria);
                    if(!empty($newdata->data[0]['return_route_name'])){
                            $data[$k]['return_route_name'] = $newdata->data[0]['return_route_name'];
                            $data[$k]['return_stop_name'] = $newdata->data[0]['return_stop_name'];
                    } else {
                            $data[$k]['return_route_name'] = '';
                            $data[$k]['return_stop_name'] = '';
                    }
                } else {
                    $newtype = 'onward';
                    $data[$k]['return_route_name'] = $d['route_name'];
                    $data[$k]['return_stop_name'] = $d['stop_name'];
                    
                    $query2 = $this
                    ->newQuery()
                    ->from('trans_route_assign')
                    ->cols([
                        'trans_route_stops.stop_name as return_stop_name','trans_routes.route_name as return_route_name'
                    ])
                    ->leftJoin('trans_route_stops', 'trans_route_assign.route_stop_id=trans_route_stops.id')
                    ->leftJoin('trans_routes', 'trans_route_assign.route_id=trans_routes.id')
                    ->where('trans_route_assign.pupilsightPersonID = "'.$pid.'" ')
                    ->where('trans_route_assign.type = "'.$newtype.'" ')
                    ->groupby(['trans_route_assign.pupilsightPersonID']);
                
                    $newdata = $this->runQuery($query2, $criteria);
                    if(!empty($newdata->data[0]['return_route_name'])){
                            $data[$k]['onward_route_name'] = $newdata->data[0]['return_route_name'];
                            $data[$k]['onward_stop_name'] = $newdata->data[0]['return_stop_name'];
                    } else {
                            $data[$k]['onward_route_name'] = '';
                            $data[$k]['onward_stop_name'] = '';
                    }
                }
                
               
            }
           
        }
        // echo '<pre>';
        // print_r($data);
        // echo '</pre>';
        // die();
        $res->data = $data;
        return $res;
    }
}