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
class WorkFlowGateway extends QueryableGateway
{
    use TableAware;

    private static $tableName = 'workflow';

    private static $searchableColumns = ['academic_year'];
	
   
    public function getAllWorkflowold(QueryCriteria $criteria)
    {
		//`name`,`description`,`code`,`academic_year`,`cuid`,`cdt`,`udt`
        $query = $this
            ->newQuery()
            ->from('workflow')
            ->cols([
                'id', 'name', 'academic_year', 'description', 'code'
            ]);

        return $this->runQuery($query, $criteria);
    }
     
    public function getAllWorkflow(QueryCriteria $criteria){		
        $campaign_id ="";		
        if(isset($_REQUEST['id'])) {		
            $campaign_id= $_REQUEST['id'];		
        }		
        //`name`,`description`,`code`,`academic_year`,`cuid`,`cdt`,`udt`        
        $query = $this            
                ->newQuery()            
                ->from('workflow AS wf')           
                ->cols(['wf.id', 'wf.name AS wfname', 'wf.academic_year', 'wf.description','wf.code',"GROUP_CONCAT(wf_st.name SEPARATOR ',')  AS state",'wf_st.workflowid as wf_id','wfmp.campaign_id'])
                ->leftJoin('workflow_state AS wf_st', 'wf.id=wf_st.workflowid')			
                ->leftJoin('workflow_map AS wfmp', 'wf.id=wfmp.workflow_id')           
                ->where('wfmp.campaign_id = :campaign_id')            
                ->bindValue('campaign_id', $campaign_id)						
                ->groupBy(['wf.id']);        
        return $this->runQuery($query, $criteria);    }
    
    
}
