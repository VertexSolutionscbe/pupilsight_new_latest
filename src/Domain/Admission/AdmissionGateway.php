<?php
/*
Pupilsight, Flexible & Open School System
*/

namespace Pupilsight\Domain\Admission;

use Pupilsight\Domain\Traits\TableAware;
use Pupilsight\Domain\QueryCriteria;
use Pupilsight\Domain\QueryableGateway;


/**
 * School Year Gateway
 *
 * @version v17
 * @since   v17
 */
class AdmissionGateway extends QueryableGateway
{
    use TableAware;

    private static $tableName = 'campaign';

    private static $searchableColumns = ['name', 'academic_year'];
	
    public function getAllCampaign(QueryCriteria $criteria, $pupilsightSchoolYearID) {
        $query = $this
            ->newQuery()
            ->from($this->getTableName())
            ->cols([
                'id', 'name', 'academic_year', 'seats', 'start_date', 'end_date', 'status'
            ])
            ->where("academic_id = ".$pupilsightSchoolYearID." ");

        return $this->runQuery($query, $criteria, TRUE);
    }

	
    public function getAllWorkflowstate(QueryCriteria $criteria)
    {
		//`workflowid`,`name`,`code`,`display_name`,`notification`,`cuid`
        $query = $this
            ->newQuery()
            ->from('workflow_state')
            ->cols([
                'id', 'name', 'code', 'display_name', 'notification'
            ]);

        return $this->runQuery($query, $criteria);
    }
    public function getAllWorkflowTransition(QueryCriteria $criteria) {		
        $query = $this
            ->newQuery()
            ->from('workflow_transition')
            ->cols([
                'id', 'from_state', 'to_state'
            ]);

        return $this->runQuery($query, $criteria);
    }
    
    public function getApp_status(QueryCriteria $criteria, $submissionId, $cuid) {	
        //echo $submissionId;
        $query = $this
            ->newQuery()
            ->from('wp_fluentform_entry_details as we')
            ->cols([
                'cs.id', 'cs.campaign_id', 'cm.form_id','cs.submission_id','cs.state','cs.state_id','cs.status','cm.name', 'ws.created_at','ws.id as subid','ws.pupilsightProgramID','ws.pupilsightYearGroupID','ws.pupilsightPersonID','we.field_name','we.sub_field_name','we.field_value','pupilsightPerson.email','pupilsightPerson.phone1'
            ])
            ->leftJoin('wp_fluentform_submissions AS ws', 'we.submission_id=ws.id')
            ->leftJoin('campaign AS cm', 'we.form_id=cm.form_id')
            ->leftJoin('campaign_form_status AS cs', 'ws.id=cs.submission_id')
            ->leftJoin('pupilsightPerson', 'ws.pupilsightPersonID=pupilsightPerson.pupilsightPersonID')
            ->where("ws.id IN (".$submissionId.") ")
            //->where("cm.form_id in (".$form_id.")")
            ->groupBy(['ws.id']);  
//echo $query;
         return $this->runQuery($query, $criteria);
    }

    public function getCampaignFormList(QueryCriteria $criteria, $formId) {
        $form_id = $formId;
        
        if(!empty($form_id)){
        $query = $this
            ->newQuery()
            ->from('wp_fluentform_entry_details as fd')
            ->cols([
                'fd.submission_id', "GROUP_CONCAT(case when fd.sub_field_name IS NULL OR fd.sub_field_name = '' OR fd.sub_field_name = '0' then fd.field_name else fd.sub_field_name end) as field_name", "GROUP_CONCAT(field_value) as field_value",'(select state from campaign_form_status where submission_id=fd.submission_id and status=1 order by id desc limit 1) as workflowstate'
            ]);
            if(!empty($form_id)){
                $query->where('fd.form_id = '.$form_id.' ');
            }
            $query->groupBy(['fd.submission_id'])
                 ->orderBy(['fd.submission_id DESC']);
        } else {
            $query = $this
            ->newQuery()
            ->from('wp_fluentform_entry_details as fd')
            ->cols([
                'fd.submission_id', "GROUP_CONCAT(case when fd.sub_field_name IS NULL OR fd.sub_field_name = '' then fd.field_name else fd.sub_field_name end) as field_name", "GROUP_CONCAT(field_value) as field_value"
            ]);
            $query->where('fd.form_id = "0" ')
            ->orderBy(['fd.submission_id DESC']);
           
        }    

            
        
        return $this->runQuery($query, $criteria);
       
    }

    public function getSearchCampaignFormList(QueryCriteria $criteria, $submissionIds) {
        
        $query = $this
            ->newQuery()
            ->from('wp_fluentform_entry_details as fd')
            ->cols([
                'fd.submission_id', "GROUP_CONCAT(case when fd.sub_field_name IS NULL OR fd.sub_field_name = '' then fd.field_name else fd.sub_field_name end) as field_name", "GROUP_CONCAT(field_value) as field_value",'(select state from campaign_form_status where submission_id=fd.submission_id and status=1 order by id desc limit 1) as state'
            ]);
            if(!empty($submissionIds)){
                $query->where('fd.submission_id IN ('.$submissionIds.') ');
            } else {
                $query->where('fd.submission_id IN (0) ');
            }
           
            $query->groupBy(['fd.submission_id']);
            
        
        return $this->runQuery($query, $criteria);
       
    }

    public function getFeeStructure(QueryCriteria $criteria, $pupilsightSchoolYearIDpost, $type, $feestgId, $pupilsightProgramID)
    {
       if($type == 2 && !empty($feestgId)){
            $query = $this
                ->newQuery()
                ->from('fn_fee_structure')
                // ->cols([
                //     'fn_fee_structure.*','pupilsightSchoolYear.name AS acedemic_year','COUNT(fn_fee_structure_item.id) as kountitem', 'SUM(fn_fee_structure_item.total_amount) as totalamount','fn_fee_admission_settings.classes'
                // ])
                ->cols([
                    'fn_fee_structure.*','pupilsightSchoolYear.name AS acedemic_year','COUNT(fn_fee_structure_item.id) as kountitem', 'SUM(fn_fee_structure_item.total_amount) as totalamount'
                ])
                ->leftJoin('pupilsightSchoolYear', 'fn_fee_structure.pupilsightSchoolYearID=pupilsightSchoolYear.pupilsightSchoolYearID')
                ->leftJoin('fn_fee_structure_item', 'fn_fee_structure.id=fn_fee_structure_item.fn_fee_structure_id')
                //->leftJoin('fn_fee_admission_settings', 'fn_fee_structure.id=fn_fee_admission_settings.fn_fee_structure_id')
                ->where('fn_fee_structure.pupilsightSchoolYearID = "'.$pupilsightSchoolYearIDpost.'" ')
                //->where('fn_fee_admission_settings.id IN ('.$feestgId.') ')
                ->groupBy(['fn_fee_structure.id'])
                ->orderBy(['fn_fee_structure.id DESC']);
            // echo $query;
            // die();

            $res = $this->runQuery($query, $criteria);
            $data = $res->data;
            
            if(!empty($data)){
                foreach($data as $k=>$cd){
                    $query2 = $this
                        ->newQuery()
                        ->from('fn_fee_admission_settings')
                        ->cols([
                            'fn_fee_admission_settings.id as settingid','fn_fee_admission_settings.classes'
                        ])
                        ->where('fn_fee_admission_settings.id IN ('.$feestgId.') ')
                        ->where('fn_fee_admission_settings.fn_fee_structure_id = "'.$cd['id'].'" ')
                        ->where('fn_fee_admission_settings.pupilsightProgramID = "'.$pupilsightProgramID.'" ');

                        $newdata = $this->runQuery($query2, $criteria);
                        if(!empty($newdata->data[0]['classes'])){
                            $data[$k]['classes'] = $newdata->data[0]['classes'];
                            $data[$k]['settingid'] = $newdata->data[0]['settingid'];
                        } else {
                            $data[$k]['classes'] = '';
                            $data[$k]['settingid'] = '';
                        }    
                }
            }    
            $res->data = $data;
            return $res;
            // echo '<pre>';
            // print_r($data);
            // echo '</pre>';
            // die();
        } else {    
            $query = $this
                ->newQuery()
                ->from('fn_fee_structure')
                ->cols([
                    'fn_fee_structure.*','pupilsightSchoolYear.name AS acedemic_year','COUNT(fn_fee_structure_item.id) as kountitem', 'SUM(fn_fee_structure_item.total_amount) as totalamount'
                ])
                ->leftJoin('pupilsightSchoolYear', 'fn_fee_structure.pupilsightSchoolYearID=pupilsightSchoolYear.pupilsightSchoolYearID')
                ->leftJoin('fn_fee_structure_item', 'fn_fee_structure.id=fn_fee_structure_item.fn_fee_structure_id')
                ->where('fn_fee_structure.pupilsightSchoolYearID = "'.$pupilsightSchoolYearIDpost.'" ')
                ->groupBy(['fn_fee_structure.id'])
                ->orderBy(['fn_fee_structure.id DESC']);    
                return $this->runQuery($query, $criteria, TRUE);
        }    
        
    }

    public function getApplicationFormList(QueryCriteria $criteria, $formId) {
        $form_id = $formId;
        
        if(!empty($form_id)){
        $query = $this
            ->newQuery()
            ->from('wp_fluentform_entry_details as fd')
            ->cols([
                'fd.submission_id as sid', "GROUP_CONCAT(case when fd.sub_field_name IS NULL OR fd.sub_field_name = '' then fd.field_name else fd.sub_field_name end) as field_name", "GROUP_CONCAT(field_value) as field_value",'(select state from campaign_form_status where submission_id=fd.submission_id and status=1 order by id desc limit 1) as state'
            ]);
            if(!empty($form_id)){
                $query->where('fd.form_id = '.$form_id.' ');
            }
            $query->where('fd.status = "0" ')
                    ->groupBy(['fd.submission_id']);
        } else {
            $query = $this
            ->newQuery()
            ->from('wp_fluentform_entry_details as fd')
            ->cols([
                'fd.submission_id as sid', "GROUP_CONCAT(case when fd.sub_field_name IS NULL OR fd.sub_field_name = '' then fd.field_name else fd.sub_field_name end) as field_name", "GROUP_CONCAT(field_value) as field_value"
            ]);
            $query->where('fd.status = "0" ')
                  ->where('fd.form_id = "0" ');
           
        }    
        return $this->runQuery($query, $criteria);
    }


    public function getCampaignSeries(QueryCriteria $criteria, $pupilsightSchoolYearID)
    {
        
        $query = $this
            ->newQuery()
            ->from('fn_fee_series')
            ->cols([
                'fn_fee_series.*','pupilsightSchoolYear.name AS acedemic_year','COUNT(a.id) as invkount','COUNT(b.id) as reckount'
            ])
            ->leftJoin('pupilsightSchoolYear', 'fn_fee_series.pupilsightSchoolYearID=pupilsightSchoolYear.pupilsightSchoolYearID')
            ->leftJoin('fn_fee_invoice AS a', 'fn_fee_series.id=a.inv_fn_fee_series_id')
            ->leftJoin('fn_fee_invoice AS b', 'fn_fee_series.id=b.rec_fn_fee_series_id')
            ->where('fn_fee_series.pupilsightSchoolYearID = "'.$pupilsightSchoolYearID.'" ')
            ->where('fn_fee_series.type != "Finance" ')
            ->groupBy(['fn_fee_series.id']);
            
        return $this->runQuery($query, $criteria, TRUE);
    }


    
}