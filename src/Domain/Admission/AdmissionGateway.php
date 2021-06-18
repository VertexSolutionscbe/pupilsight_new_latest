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

    private static $tableName = "campaign";

    private static $searchableColumns = ["name", "academic_year"];

    public function getAllCampaign(
        QueryCriteria $criteria,
        $pupilsightSchoolYearID
    ) {
        $query = $this->newQuery()
            ->from($this->getTableName())
            ->cols([
                "id",
                "name",
                "academic_year",
                "seats",
                "start_date",
                "end_date",
                "status",
            ])
            ->where("academic_id = " . $pupilsightSchoolYearID . " ");

        return $this->runQuery($query, $criteria, true);
    }

    public function getAllWorkflowstate(QueryCriteria $criteria)
    {
        //`workflowid`,`name`,`code`,`display_name`,`notification`,`cuid`
        $query = $this->newQuery()
            ->from("workflow_state")
            ->cols(["id", "name", "code", "display_name", "notification"]);

        return $this->runQuery($query, $criteria);
    }
    public function getAllWorkflowTransition(QueryCriteria $criteria)
    {
        $query = $this->newQuery()
            ->from("workflow_transition")
            ->cols(["id", "from_state", "to_state"]);

        return $this->runQuery($query, $criteria);
    }

    public function getApp_status(QueryCriteria $criteria, $submissionId, $cuid)
    {
        //echo $submissionId;
        $query = $this->newQuery()
            ->from("wp_fluentform_entry_details as we")
            ->cols([
                "cs.id",
                "cm.id as campaign_id",
                "cm.form_id",
                "cs.submission_id",
                "cs.state",
                "cs.state_id",
                "cs.status",
                "cm.name",
                "ws.created_at",
                "ws.id as subid",
                "ws.pupilsightProgramID",
                "ws.pupilsightYearGroupID",
                "ws.pupilsightPersonID",
                "ws.is_contract_generated",
                "we.field_name",
                "we.sub_field_name",
                "we.field_value",
                "pupilsightPerson.email",
                "pupilsightPerson.phone1",
                "cm.is_fee_generate",
                "(select workflow_transition.transition_display_name AS state from campaign_form_status LEFT JOIN workflow_transition ON campaign_form_status.state_id = workflow_transition.id where campaign_form_status.submission_id=we.submission_id and campaign_form_status.status=1 order by campaign_form_status.id desc limit 1) as workflowstate",
            ])
            ->leftJoin(
                "wp_fluentform_submissions AS ws",
                "we.submission_id=ws.id"
            )
            ->leftJoin("campaign AS cm", "we.form_id=cm.form_id")
            ->leftJoin("campaign_form_status AS cs", "ws.id=cs.submission_id")
            ->leftJoin(
                "pupilsightPerson",
                "ws.pupilsightPersonID=pupilsightPerson.pupilsightPersonID"
            )
            ->where("ws.id IN (" . $submissionId . ") ")
            //->where("cm.form_id in (".$form_id.")")
            ->groupBy(["ws.id"]);
        // echo $query;
        // die();
        return $this->runQuery($query, $criteria);
    }

    public function getCampaignFormList(QueryCriteria $criteria, $formId)
    {
        $form_id = $formId;

        if (!empty($form_id)) {
            $query = $this->newQuery()
                ->from("wp_fluentform_entry_details as fd")
                // ->cols([
                //     'fd.submission_id', "GROUP_CONCAT(case when fd.sub_field_name IS NULL OR fd.sub_field_name = '' OR fd.sub_field_name = '0' then fd.field_name else fd.sub_field_name end) as field_name", "GROUP_CONCAT(field_value) as field_value",'(select state from campaign_form_status where submission_id=fd.submission_id and status=1 order by id desc limit 1) as workflowstate'
                // ]);
                ->cols([
                    "fd.submission_id as submission_no",
                    "fd.submission_id",
                    "GROUP_CONCAT(fd.field_name order by fd.id) as field_name",
                    "fd.status",
                    "GROUP_CONCAT(field_value order by fd.id SEPARATOR '|$$|') as field_value",
                    "(select workflow_transition.transition_display_name AS state from campaign_form_status LEFT JOIN workflow_transition ON campaign_form_status.state_id = workflow_transition.id where campaign_form_status.submission_id=fd.submission_id and campaign_form_status.status=1 order by campaign_form_status.id desc limit 1) as workflowstate"
                ])
                ->where("fd.form_id = " . $form_id . " ")
                ->groupBy(["fd.submission_id"])
                ->orderBy(["fd.submission_id DESC"]);
        } else {
            $query = $this->newQuery()
                ->from("wp_fluentform_entry_details as fd")
                ->cols([
                    "fd.submission_id as submission_no",
                    "fd.submission_id",
                    "fd.status",
                    "GROUP_CONCAT(case when fd.sub_field_name IS NULL OR fd.sub_field_name = '' then fd.field_name else fd.sub_field_name end) as field_name",
                    "GROUP_CONCAT(field_value SEPARATOR '|$$|') as field_value",
                ]);
            $query
                ->where('fd.form_id = "0" ')
                ->orderBy(["fd.submission_id DESC"]);
        }

        $res = $this->runQuery($query, $criteria);
        $data = $res->data;

        if (!empty($data)) {
            foreach ($data as $k => $d) {
                $submission_id = $d["submission_id"];
                $query2 = $this->newQuery()
                    ->from("wp_fluentform_submissions")
                    ->cols(["wp_fluentform_submissions.*"])
                    ->where(
                        'wp_fluentform_submissions.id = "' .
                            $submission_id .
                            '" '
                    );

                $newdata = $this->runQuery($query2, $criteria);
                if (!empty($newdata->data[0]["application_id"])) {
                    $data[$k]["application_id"] =
                        $newdata->data[0]["application_id"];
                    $data[$k]["pupilsightProgramID"] =
                        $newdata->data[0]["pupilsightProgramID"];
                    $data[$k]["pupilsightYearGroupID"] =
                        $newdata->data[0]["pupilsightYearGroupID"];
                    $data[$k]["fn_fee_invoice_id"] =
                        $newdata->data[0]["fn_fee_invoice_id"];
                } else {
                    $data[$k]["application_id"] = "";
                    $data[$k]["pupilsightProgramID"] = "";
                    $data[$k]["pupilsightYearGroupID"] = "";
                    $data[$k]["fn_fee_invoice_id"] = "";
                }
            }
        }
        //  echo '<pre>';
        //     print_r($data);
        //     echo '</pre>';
        //     die();
        $res->data = $data;
        return $res;
    }

    public function getSearchCampaignFormList(
        QueryCriteria $criteria,
        $submissionIds,
        $application_id,
        $applicationStatus,
        $applicantClass,
        $applicantProg
    ) {
        $query = $this->newQuery()
            ->from("wp_fluentform_entry_details as fd")
            // ->cols([
            //     'fd.submission_id', "GROUP_CONCAT(case when fd.sub_field_name IS NULL OR fd.sub_field_name = '' then fd.field_name else fd.sub_field_name end) as field_name", "GROUP_CONCAT(field_value) as field_value",'(select state from campaign_form_status where submission_id=fd.submission_id and status=1 order by id desc limit 1) as state'
            // ]);

            ->cols([
                "fd.submission_id",
                "GROUP_CONCAT(fd.field_name) as field_name",
                "GROUP_CONCAT(fd.field_value  SEPARATOR '|$$|') as field_value",
                "fd.status",
                "(select workflow_transition.transition_display_name AS state from campaign_form_status AS cs LEFT JOIN workflow_transition ON cs.state_id = workflow_transition.id where cs.submission_id=fd.submission_id and cs.status=1 order by cs.id desc limit 1) as workflowstate",
                "ws.id",
                "ws.application_id",
            ])
            ->leftJoin(
                "wp_fluentform_submissions AS ws",
                "fd.submission_id=ws.id"
            );
        if (!empty($applicationStatus) && $applicationStatus != "Submitted") {
            $query->leftJoin(
                "campaign_form_status AS cfs",
                "fd.submission_id=cfs.submission_id"
            );
        }
        if (!empty($submissionIds)) {
            $query->where("fd.submission_id IN (" . $submissionIds . ") ");
        } else {
            $query->where("fd.submission_id IN (0) ");
        }
        if (!empty($application_id)) {
            $query->where('ws.application_id = "' . $application_id . '" ');
        }
        if (!empty($applicantProg)) {
            $query->where('ws.pupilsightProgramID = "' . $applicantProg . '" ');
        }
        if (!empty($applicantClass)) {
            $query->where(
                'ws.pupilsightYearGroupID = "' . $applicantClass . '" '
            );
        }
        if (!empty($applicationStatus) && $applicationStatus != "Submitted") {
            $query->where('cfs.state_id = "' . $applicationStatus . '" ');
        }

        $query
            ->groupBy(["fd.submission_id"])
            ->orderBy(["fd.submission_id DESC"]);

        //echo $query;

        return $this->runQuery($query, $criteria);
        // $data = $res->data;

        // if(!empty($data)){
        //     foreach($data as $k=>$d){
        //         $submission_id = $d['submission_id'];
        //          $query2 = $this
        //             ->newQuery()
        //             ->from('wp_fluentform_submissions')
        //             ->cols([
        //                 'wp_fluentform_submissions.*'
        //             ])
        //             ->where('wp_fluentform_submissions.id = "'.$submission_id.'" ');

        //             $newdata = $this->runQuery($query2, $criteria);
        //             if(!empty($newdata->data[0]['application_id'])){
        //                 if($newdata->data[0]['application_id'] == $application_id){
        //                     $data[$k]['application_id'] = $newdata->data[0]['application_id'];
        //                     $data[$k]['pupilsightProgramID'] = $newdata->data[0]['pupilsightProgramID'];
        //                     $data[$k]['pupilsightYearGroupID'] = $newdata->data[0]['pupilsightYearGroupID'];
        //                     $data[$k]['fn_fee_invoice_id'] = $newdata->data[0]['fn_fee_invoice_id'];
        //                 } else {
        //                     $data = array();
        //                 }

        //             } else {
        //                 $data[$k]['application_id'] = '';
        //                 $data[$k]['pupilsightProgramID'] = '';
        //                 $data[$k]['pupilsightYearGroupID'] = '';
        //                 $data[$k]['fn_fee_invoice_id'] = '';
        //             }

        //     }
        // }
        //  echo '<pre>';
        //     print_r($data);
        //     echo '</pre>';
        //     die();
        // $res->data = $data;
        // return $res;
    }

    public function getFeeStructure(
        QueryCriteria $criteria,
        $pupilsightSchoolYearIDpost,
        $type,
        $feestgId,
        $pupilsightProgramID
    ) {
        if ($type == 2 && !empty($feestgId)) {
            $query = $this->newQuery()
                ->from("fn_fee_structure")
                // ->cols([
                //     'fn_fee_structure.*','pupilsightSchoolYear.name AS acedemic_year','COUNT(fn_fee_structure_item.id) as kountitem', 'SUM(fn_fee_structure_item.total_amount) as totalamount','fn_fee_admission_settings.classes'
                // ])
                ->cols([
                    "fn_fee_structure.*",
                    "pupilsightSchoolYear.name AS acedemic_year",
                    "COUNT(fn_fee_structure_item.id) as kountitem",
                    "SUM(fn_fee_structure_item.total_amount) as totalamount",
                ])
                ->leftJoin(
                    "pupilsightSchoolYear",
                    "fn_fee_structure.pupilsightSchoolYearID=pupilsightSchoolYear.pupilsightSchoolYearID"
                )
                ->leftJoin(
                    "fn_fee_structure_item",
                    "fn_fee_structure.id=fn_fee_structure_item.fn_fee_structure_id"
                )
                //->leftJoin('fn_fee_admission_settings', 'fn_fee_structure.id=fn_fee_admission_settings.fn_fee_structure_id')
                ->where(
                    'fn_fee_structure.pupilsightSchoolYearID = "' .
                        $pupilsightSchoolYearIDpost .
                        '" '
                )
                //->where('fn_fee_admission_settings.id IN ('.$feestgId.') ')
                ->groupBy(["fn_fee_structure.id"])
                ->orderBy(["fn_fee_structure.id DESC"]);
            // echo $query;
            // die();

            $res = $this->runQuery($query, $criteria);
            $data = $res->data;

            if (!empty($data)) {
                foreach ($data as $k => $cd) {
                    $query2 = $this->newQuery()
                        ->from("fn_fee_admission_settings")
                        ->cols([
                            "fn_fee_admission_settings.id as settingid",
                            "fn_fee_admission_settings.classes",
                            "fn_fee_admission_settings.no_of_invoices",
                            "fn_fee_admission_settings.state_id",
                        ])
                        ->where(
                            "fn_fee_admission_settings.id IN (" .
                                $feestgId .
                                ") "
                        )
                        ->where(
                            'fn_fee_admission_settings.fn_fee_structure_id = "' .
                                $cd["id"] .
                                '" '
                        )
                        ->where(
                            'fn_fee_admission_settings.pupilsightProgramID IN (' .
                                $pupilsightProgramID .
                                ') '
                        )
                        ->orderBy(["fn_fee_admission_settings.id DESC"]);

                    $newdata = $this->runQuery($query2, $criteria);
                    if (!empty($newdata->data[0]["classes"])) {
                        $data[$k]["classes"] = $newdata->data[0]["classes"];
                        $data[$k]["settingid"] = $newdata->data[0]["settingid"];
                        $data[$k]["no_of_invoices"] =
                            $newdata->data[0]["no_of_invoices"];
                        $data[$k]["state_id"] = $newdata->data[0]["state_id"];
                    } else {
                        $data[$k]["classes"] = "";
                        $data[$k]["settingid"] = "";
                        $data[$k]["no_of_invoices"] = "";
                        $data[$k]["state_id"] = "";
                    }
                }
            }
            //  echo '<pre>';
            // print_r($data);
            // echo '</pre>';
            // die();
            $res->data = $data;
            return $res;
        } else {
            $query = $this->newQuery()
                ->from("fn_fee_structure")
                ->cols([
                    "fn_fee_structure.*",
                    "pupilsightSchoolYear.name AS acedemic_year",
                    "COUNT(fn_fee_structure_item.id) as kountitem",
                    "SUM(fn_fee_structure_item.total_amount) as totalamount",
                ])
                ->leftJoin(
                    "pupilsightSchoolYear",
                    "fn_fee_structure.pupilsightSchoolYearID=pupilsightSchoolYear.pupilsightSchoolYearID"
                )
                ->leftJoin(
                    "fn_fee_structure_item",
                    "fn_fee_structure.id=fn_fee_structure_item.fn_fee_structure_id"
                )
                ->where(
                    'fn_fee_structure.pupilsightSchoolYearID = "' .
                        $pupilsightSchoolYearIDpost .
                        '" '
                )
                ->groupBy(["fn_fee_structure.id"])
                ->orderBy(["fn_fee_structure.id DESC"]);
            return $this->runQuery($query, $criteria, true);
        }
    }

    public function getApplicationFormList(QueryCriteria $criteria, $formId)
    {
        $form_id = $formId;

        if (!empty($form_id)) {
            $query = $this->newQuery()
                ->from("wp_fluentform_entry_details as fd")
                // ->cols([
                //     'fd.submission_id', "GROUP_CONCAT(case when fd.sub_field_name IS NULL OR fd.sub_field_name = '' OR fd.sub_field_name = '0' then fd.field_name else fd.sub_field_name end) as field_name", "GROUP_CONCAT(field_value) as field_value",'(select state from campaign_form_status where submission_id=fd.submission_id and status=1 order by id desc limit 1) as workflowstate'
                // ]);
                ->cols([
                    "fd.submission_id",
                    "GROUP_CONCAT(fd.field_name) as field_name",
                    "GROUP_CONCAT(field_value) as field_value",
                    "(select workflow_transition.transition_display_name AS state from campaign_form_status LEFT JOIN workflow_transition ON campaign_form_status.state_id = workflow_transition.id where campaign_form_status.submission_id=fd.submission_id and campaign_form_status.status=1 order by campaign_form_status.id desc limit 1) as workflowstate",
                ])
                ->where("fd.form_id = " . $form_id . " ")
                ->where('fd.status = "0" ')
                ->groupBy(["fd.submission_id"])
                ->orderBy(["fd.submission_id DESC"]);
        } else {
            $query = $this->newQuery()
                ->from("wp_fluentform_entry_details as fd")
                ->cols([
                    "fd.submission_id",
                    "GROUP_CONCAT(case when fd.sub_field_name IS NULL OR fd.sub_field_name = '' then fd.field_name else fd.sub_field_name end) as field_name",
                    "GROUP_CONCAT(field_value) as field_value",
                ]);
            $query
                ->where('fd.form_id = "0" ')
                ->where('fd.status = "0" ')
                ->orderBy(["fd.submission_id DESC"]);
        }

        $res = $this->runQuery($query, $criteria);
        $data = $res->data;

        if (!empty($data)) {
            foreach ($data as $k => $d) {
                $submission_id = $d["submission_id"];
                $query2 = $this->newQuery()
                    ->from("wp_fluentform_submissions")
                    ->cols(["wp_fluentform_submissions.*"])
                    ->where(
                        'wp_fluentform_submissions.id = "' .
                            $submission_id .
                            '" '
                    );

                $newdata = $this->runQuery($query2, $criteria);
                if (!empty($newdata->data[0]["application_id"])) {
                    $data[$k]["application_id"] =
                        $newdata->data[0]["application_id"];
                    $data[$k]["pupilsightProgramID"] =
                        $newdata->data[0]["pupilsightProgramID"];
                    $data[$k]["pupilsightYearGroupID"] =
                        $newdata->data[0]["pupilsightYearGroupID"];
                    $data[$k]["fn_fee_invoice_id"] =
                        $newdata->data[0]["fn_fee_invoice_id"];
                } else {
                    $data[$k]["application_id"] = "";
                    $data[$k]["pupilsightProgramID"] = "";
                    $data[$k]["pupilsightYearGroupID"] = "";
                    $data[$k]["fn_fee_invoice_id"] = "";
                }
            }
        }
        //  echo '<pre>';
        //     print_r($data);
        //     echo '</pre>';
        //     die();
        $res->data = $data;
        return $res;
    }

    public function getCampaignSeries(
        QueryCriteria $criteria,
        $pupilsightSchoolYearID
    ) {
        $query = $this->newQuery()
            ->from("fn_fee_series")
            ->cols([
                "fn_fee_series.*",
                "pupilsightSchoolYear.name AS acedemic_year",
                "COUNT(a.id) as invkount",
                "COUNT(b.id) as reckount",
            ])
            ->leftJoin(
                "pupilsightSchoolYear",
                "fn_fee_series.pupilsightSchoolYearID=pupilsightSchoolYear.pupilsightSchoolYearID"
            )
            ->leftJoin(
                "fn_fee_invoice AS a",
                "fn_fee_series.id=a.inv_fn_fee_series_id"
            )
            ->leftJoin(
                "fn_fee_invoice AS b",
                "fn_fee_series.id=b.rec_fn_fee_series_id"
            )
            ->where(
                'fn_fee_series.pupilsightSchoolYearID = "' .
                    $pupilsightSchoolYearID .
                    '" '
            )
            ->where('fn_fee_series.type != "Finance" ')
            ->where('fn_fee_series.type != "TC" ')
            ->groupBy(["fn_fee_series.id"]);

        return $this->runQuery($query, $criteria, true);
    }

    public function getAllRegisterUserCampaign(QueryCriteria $criteria, $cid)
    {
        $query = $this->newQuery()
            ->from("campaign_parent_registration")
            ->cols(["campaign_parent_registration.*"])
            ->where(
                'campaign_parent_registration.campaign_id = "' . $cid . '" '
            )
            ->orderBy(["campaign_parent_registration.id DESC"]);

        $res = $this->runQuery($query, $criteria, true);
        $data = $res->data;

        if (!empty($data)) {
            foreach ($data as $k => $cd) {
                $query2 = $this->newQuery()
                    ->from("wp_fluentform_submissions")
                    ->cols(["wp_fluentform_submissions.id"])
                    ->where(
                        'wp_fluentform_submissions.pupilsightPersonID = "' .
                            $cd["pupilsightPersonID"] .
                            '" '
                    );

                $newdata = $this->runQuery($query2, $criteria);
                if (!empty($newdata->data[0]["id"])) {
                    $data[$k]["status"] = "Applied";
                } else {
                    $data[$k]["status"] = "Not Apply";
                }
            }
        }
        $res->data = $data;
        return $res;
    }

    public function getApplicationTemplate(QueryCriteria $criteria, $id)
    {
        $query = $this->newQuery()
            ->from("campaign")
            ->cols(["campaign.*"])
            ->where('campaign.id = "' . $id . '" ')
            ->where('campaign.template_path != "" ');

        return $this->runQuery($query, $criteria, true);
    }

    public function getAllPaymentDetails(QueryCriteria $criteria)
    {
        $query = $this->newQuery()
            ->from("fn_fee_payment_details")
            ->cols(["fn_fee_payment_details.*"])
            ->orderBy(["fn_fee_payment_details.id DESC"]);

        return $this->runQuery($query, $criteria, true);
    }

    public function getAllPayReceipts(QueryCriteria $criteria, $cid, $sid)
    {
        $query = $this->newQuery()
            ->from("campaign_payment_attachment")
            ->cols(["campaign_payment_attachment.*"])
            ->where('campaign_payment_attachment.campaign_id = "' . $cid . '" ')
            ->where('campaign_payment_attachment.submission_id = "' . $sid . '" ')
            ->orderBy(['campaign_payment_attachment.id DESC ']);

        return $this->runQuery($query, $criteria, true);
    }
}
