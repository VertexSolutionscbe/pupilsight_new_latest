<?php
/*
Pupilsight, Flexible & Open School System
*/

namespace Pupilsight\Domain\Finance;

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
class FeesGateway extends QueryableGateway
{
    use TableAware;

    private static $tableName = 'fn_fee_item_type';

    private static $searchableColumns = [];

    /**
     * @param QueryCriteria $criteria
     * @return DataSet
     */
    public function getFeesItemType(QueryCriteria $criteria)
    {
        /*
        $query = $this
            ->newQuery()
            ->from('fn_fee_item_type')
            ->cols([
                'id','name'
            ]);
        $rs = $this->runQuery($query, $criteria, TRUE);
        print_r($rs);
print_r($rs);

 echo "\n\n<br>\n\n";
        */



        $db = new DBQuery();
        $rs = $db->select_serial("select id, name from fn_fee_item_type");
        return $rs;
    }

    public function getFeesItem(QueryCriteria $criteria, $search, $pupilsightSchoolYearID)
    {
        $query = $this
            ->newQuery()
            ->from('fn_fee_items')
            ->cols([
                'fn_fee_items.id', 'fn_fee_items.name', 'fn_fee_items.code', 'fn_fee_items.pupilsightSchoolYearID', 'fn_fee_items.fn_fee_item_type_id', 'pupilsightSchoolYear.name AS acedemic_year', 'fn_fee_item_type.name AS fee_item_type'
            ])
            ->leftJoin('pupilsightSchoolYear', 'fn_fee_items.pupilsightSchoolYearID=pupilsightSchoolYear.pupilsightSchoolYearID')
            ->leftJoin('fn_fee_item_type', 'fn_fee_items.fn_fee_item_type_id=fn_fee_item_type.id')
            ->where('fn_fee_items.pupilsightSchoolYearID = "' . $pupilsightSchoolYearID . '" ');
        if (!empty($search)) {
            $query->where('fn_fee_items.name LIKE "%' . $search . '%" ')
                ->orwhere('fn_fee_items.code = "' . $search . '" ');
        }


        return $this->runQuery($query, $criteria, TRUE);
    }

    public function getFeesCounter(QueryCriteria $criteria)
    {
        $date = date('Y-m-d');
        //$date = '2020-09-01';
        $query = $this
            ->newQuery()
            ->from('fn_fees_counter')
            ->cols([
                'fn_fees_counter.id', 'fn_fees_counter.name', 'fn_fees_counter.code', 'fn_fees_counter.status'
            ])
            ->groupby(['fn_fees_counter.id']);


        $res = $this->runQuery($query, $criteria);
        $data = $res->data;
        if (!empty($data)) {
            foreach ($data as $k => $d) {
                $corunterid = $d['id'];
                $query2 = $this
                    ->newQuery()
                    ->from('fn_fees_collection')
                    ->cols([
                        'SUM(fn_fees_collection.amount_paying) as collection'
                    ])
                    ->where('fn_fees_collection.payment_date = "' . $date . '" ')
                    ->where('fn_fees_collection.fn_fees_counter_id = ' . $corunterid . ' ');
                //echo $query2;

                $newdata = $this->runQuery($query2, $criteria);
                if (!empty($newdata->data[0]['collection'])) {
                    $data[$k]['collection'] = $newdata->data[0]['collection'];
                } else {
                    $data[$k]['collection'] = '';
                }
            }
        }

        $res->data = $data;
        return $res;
    }

    public function getFeesCounterUsedBy(QueryCriteria $criteria, $id, $input)
    {
        if (!empty($_SESSION['fee_counter_search'])) {
            $input = $_SESSION['fee_counter_search'];
        }
        $query = $this
            ->newQuery()
            ->from('fn_fees_counter')
            ->cols([
                'fn_fees_counter.id', 'fn_fees_counter.name', 'fn_fees_counter.code', 'fn_fees_counter.status', 'fn_fees_counter_map.active_date', 'fn_fees_counter_map.start_time', 'fn_fees_counter_map.end_time', 'pupilsightPerson.officialName'
            ])
            ->leftJoin('fn_fees_counter_map', 'fn_fees_counter.id=fn_fees_counter_map.fn_fees_counter_id')
            ->leftJoin('pupilsightPerson', 'fn_fees_counter_map.pupilsightPersonID=pupilsightPerson.pupilsightPersonID')
            ->where('fn_fees_counter.id = "' . $id . '" ');
        if (!empty($input['from_date'])) {
            $fd = explode('/', $input['from_date']);
            $fdate  = date('Y-m-d', strtotime(implode('-', array_reverse($fd))));
            $query->where('fn_fees_counter_map.active_date >= "' . $fdate . '" ');
        }

        if (!empty($input['to_date'])) {
            $td = explode('/', $input['to_date']);
            $tdate  = date('Y-m-d', strtotime(implode('-', array_reverse($td))));
            $query->where('fn_fees_counter_map.active_date <= "' . $tdate . '" ');
        }


        //echo $query;
        $res = $this->runQuery($query, $criteria);
        $data = $res->data;

        if (!empty($data)) {
            foreach ($data as $k => $d) {
                $query2 = '';
                $newdata = '';
                $newdata2 = '';
                $corunterid = $id;
                $cdate = $d['active_date'];
                $stime = $d['start_time'];
                $etime = $d['end_time'];
                $cdts = $cdate . ' ' . $stime;
                $cdte = $cdate . ' ' . $etime;

                if (!empty($corunterid) && !empty($stime) && !empty($etime)) {
                    // $query2 = $this
                    // ->newQuery()
                    // ->from('fn_fees_collection')
                    // ->cols([
                    //     'fn_fees_collection.amount_paying','fn_fees_collection.payment_mode_id'
                    // ])
                    // ->where('fn_fees_collection.cdt >= "'.$cdts.'" ')
                    // ->where('fn_fees_collection.cdt <= "'.$cdte.'" ')
                    // ->where('fn_fees_collection.fn_fees_counter_id = '.$corunterid.' ');
                    //echo $query2;

                    //$newdata = $this->runQuery($query2, $criteria);

                    $db = new DBQuery();
                    $newdata = $db->select('SELECT fn_fees_collection.amount_paying, fn_fees_collection.payment_mode_id, fn_masters.name as pname FROM `fn_fees_collection` LEFT JOIN fn_masters ON fn_fees_collection.payment_mode_id = fn_masters.id WHERE fn_fees_collection.cdt >= "' . $cdts . '" AND fn_fees_collection.cdt <= "' . $cdte . '" AND fn_fees_collection.fn_fees_counter_id = ' . $corunterid . ' ');

                    if (!empty($newdata->data[0]['amount_paying'])) {
                        $data[$k]['amount_paying'] = $newdata->data[0]['amount_paying'];
                        $data[$k]['pname'] = $newdata->data[0]['pname'];
                    } else {
                        $data[$k]['amount_paying'] = '';
                        $data[$k]['pname'] = '';
                    }
                } else {
                    $data[$k]['amount_paying'] = '';
                    $data[$k]['pname'] = '';
                }
            }
        }
        // echo '<pre>';
        // print_r($data);
        // echo '</pre>';

        $res->data = $data;
        return $res;
    }

    public function getFeesCounterUsedTotal(QueryCriteria $criteria, $id, $input)
    {
        if (!empty($_SESSION['fee_counter_search'])) {
            $input = $_SESSION['fee_counter_search'];
        }
        $query = $this
            ->newQuery()
            ->from('fn_fees_collection')
            ->cols([
                'SUM(fn_fees_collection.amount_paying) as amount_paying'
            ])
            //->where('fn_fees_collection.payment_date = "'.$date.'" ')
            ->where('fn_fees_collection.fn_fees_counter_id = ' . $id . ' ');
        if (!empty($input['from_date'])) {
            $fd = explode('/', $input['from_date']);
            $fdate  = date('Y-m-d', strtotime(implode('-', array_reverse($fd))));
            $query->where('fn_fees_collection.payment_date >= "' . $fdate . '" ');
        }

        if (!empty($input['to_date'])) {
            $td = explode('/', $input['to_date']);
            $tdate  = date('Y-m-d', strtotime(implode('-', array_reverse($td))));
            $query->where('fn_fees_collection.payment_date <= "' . $tdate . '" ');
        }
        //echo $query;   
        return $query;
    }

    public function getDepositAccount(QueryCriteria $criteria)
    {
        $query = $this
            ->newQuery()
            ->from('fn_fees_deposit_account')
            ->cols([
                'fn_fees_deposit_account.id', 'fn_fees_deposit_account.ac_name', 'fn_fees_deposit_account.ac_code', 'fn_fees_deposit_account.overpayment_account', 'fn_fee_items.name as fee_item', 'SUM(if(fn_fees_collection_deposit.status = "Credit",fn_fees_collection_deposit.amount,0) ) AS creditData'
            ])
            ->leftJoin('fn_fee_items', 'fn_fees_deposit_account.fn_fee_item_id=fn_fee_items.id')
            ->leftJoin('fn_fees_collection_deposit', 'fn_fees_deposit_account.id=fn_fees_collection_deposit.deposit_account_id')
            //->where('fn_fees_collection_deposit.status = "Credit" ')
            ->groupBy(['fn_fees_deposit_account.id']);

        //return $this->runQuery($query, $criteria, TRUE);
        //echo $query;

        $res = $this->runQuery($query, $criteria, TRUE);
        $data = $res->data;
        // echo '<pre>';
        // print_r($data);
        // echo '</pre>';
        // die();
        if (!empty($data)) {
            foreach ($data as $k => $d) {
                $deposit_account_id = $d['id'];
                $creditdata = $d['creditData'];
                $query2 = $this
                    ->newQuery()
                    ->from('fn_fees_collection_deposit')
                    ->cols([
                        'SUM(fn_fees_collection_deposit.amount) AS debitData'
                    ])
                    ->where('fn_fees_collection_deposit.deposit_account_id = "' . $deposit_account_id . '" ')
                    ->where('fn_fees_collection_deposit.status = "Debit" ');

                $newdata = $this->runQuery($query2, $criteria);
                if (!empty($newdata->data[0]['debitData'])) {
                    $debitdata = $newdata->data[0]['debitData'];
                    $depAmount = $creditdata - $debitdata;
                    $data[$k]['amount'] = $depAmount;
                } else {
                    $depAmount = $creditdata;
                    $data[$k]['amount'] = $depAmount;
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

    public function getFeesSeries(QueryCriteria $criteria, $pupilsightSchoolYearID)
    {

        $query = $this
            ->newQuery()
            ->from('fn_fee_series')
            ->cols([
                'fn_fee_series.*', 'pupilsightSchoolYear.name AS acedemic_year', 'COUNT(a.id) as invkount', 'COUNT(b.id) as reckount'
            ])
            ->leftJoin('pupilsightSchoolYear', 'fn_fee_series.pupilsightSchoolYearID=pupilsightSchoolYear.pupilsightSchoolYearID')
            ->leftJoin('fn_fee_invoice AS a', 'fn_fee_series.id=a.inv_fn_fee_series_id')
            ->leftJoin('fn_fee_invoice AS b', 'fn_fee_series.id=b.rec_fn_fee_series_id')
            ->where('fn_fee_series.pupilsightSchoolYearID = "' . $pupilsightSchoolYearID . '" ')
            ->where('fn_fee_series.type = "Finance" ')
            ->groupBy(['fn_fee_series.id']);

        return $this->runQuery($query, $criteria, TRUE);
    }

    public function getFeePaymentGateway(QueryCriteria $criteria)
    {
        $query = $this
            ->newQuery()
            ->from('fn_fee_payment_gateway')
            ->cols([
                'id', 'name', 'gateway_name'
            ]);

        return $this->runQuery($query, $criteria);
    }

    public function getFeesHead(QueryCriteria $criteria, $pupilsightSchoolYearID)
    {

        $query = $this
            ->newQuery()
            ->from('fn_fees_head')
            ->cols([
                'fn_fees_head.id', 'fn_fees_head.name', 'fn_fees_head.account_code', 'fn_fees_head.description', 'fn_fees_head.bank_name', 'fn_fees_head.ac_no', 'COUNT(fn_fee_structure.id) as structurekount', 'pupilsightSchoolYear.name AS acedemic_year'
            ])
            ->leftJoin('fn_fee_structure', 'fn_fees_head.id=fn_fee_structure.fn_fees_head_id')
            ->leftJoin('pupilsightSchoolYear', 'fn_fees_head.pupilsightSchoolYearID=pupilsightSchoolYear.pupilsightSchoolYearID')
            ->where('fn_fees_head.pupilsightSchoolYearID = "' . $pupilsightSchoolYearID . '" ')
            ->groupBy(['fn_fees_head.id']);

        return $this->runQuery($query, $criteria, TRUE);
    }

    public function getFeeFineRule(QueryCriteria $criteria)
    {
        $query = $this
            ->newQuery()
            ->from('fn_fees_fine_rule')
            ->cols([
                'id', 'name', 'code', 'description'
            ]);

        return $this->runQuery($query, $criteria, TRUE);
    }

    public function getFeeDiscountRule(QueryCriteria $criteria, $pupilsightSchoolYearID)
    {
        $query = $this
            ->newQuery()
            ->from('fn_fees_discount')
            ->cols([
                'fn_fees_discount.*', 'pupilsightSchoolYear.name AS acedemic_year'
            ])
            ->leftJoin('pupilsightSchoolYear', 'fn_fees_discount.pupilsightSchoolYearID=pupilsightSchoolYear.pupilsightSchoolYearID')
            ->where('fn_fees_discount.pupilsightSchoolYearID = "' . $pupilsightSchoolYearID . '" ');


        return $this->runQuery($query, $criteria, TRUE);
    }

    public function getFeeStructure(QueryCriteria $criteria, $pupilsightSchoolYearIDpost, $input)
    {
        $fn_fees_head_id = "";
        if (!empty($_SESSION['fn_fees_head_id_search'])) {
            $fn_fees_head_id = $_SESSION['fn_fees_head_id_search'];
        } else if (isset($input['fn_fees_head_id'])) {
            $fn_fees_head_id = $input['fn_fees_head_id'];
        }
        if (!empty($input)) {
            $query = $this
                ->newQuery()
                ->from('fn_fee_structure')
                ->cols([
                    'fn_fee_structure.*', 'pupilsightSchoolYear.name AS acedemic_year', 'fn_fees_head.name AS account_head', 'COUNT(fn_fee_structure_item.id) as kountitem', 'SUM(fn_fee_structure_item.total_amount) as totalamount'
                ])
                ->leftJoin('pupilsightSchoolYear', 'fn_fee_structure.pupilsightSchoolYearID=pupilsightSchoolYear.pupilsightSchoolYearID')
                ->leftJoin('fn_fee_structure_item', 'fn_fee_structure.id=fn_fee_structure_item.fn_fee_structure_id')
                //->leftJoin('fn_fee_series', 'fn_fee_structure.invoice_title_id=fn_fee_series.id')
                ->leftJoin('fn_fees_head', 'fn_fee_structure.fn_fees_head_id=fn_fees_head.id')
                ->where('fn_fee_structure.pupilsightSchoolYearID = "' . $pupilsightSchoolYearIDpost . '" ');
            if (!empty($input['name'])) {
                $query->where('fn_fee_structure.name LIKE "%' . $input['name'] . '%" ');
            }
            if (!empty($fn_fees_head_id)) {
                $query->where('fn_fee_structure.fn_fees_head_id = "' . $fn_fees_head_id . '" ');
            }
            $query->groupBy(['fn_fee_structure.id']);
            //echo $query;
            //die();
        } else {
            $query = $this
                ->newQuery()
                ->from('fn_fee_structure')
                ->cols([
                    'fn_fee_structure.*', 'pupilsightSchoolYear.name AS acedemic_year', 'fn_fees_head.name AS account_head', 'COUNT(fn_fee_structure_item.id) as kountitem', 'SUM(fn_fee_structure_item.total_amount) as totalamount'
                ])
                ->leftJoin('pupilsightSchoolYear', 'fn_fee_structure.pupilsightSchoolYearID=pupilsightSchoolYear.pupilsightSchoolYearID')
                ->leftJoin('fn_fee_structure_item', 'fn_fee_structure.id=fn_fee_structure_item.fn_fee_structure_id')
                //->leftJoin('fn_fee_series', 'fn_fee_structure.invoice_title_id=fn_fee_series.id')
                ->leftJoin('fn_fees_head', 'fn_fee_structure.fn_fees_head_id=fn_fees_head.id')
                ->where('fn_fee_structure.pupilsightSchoolYearID = "' . $pupilsightSchoolYearIDpost . '" ')
                ->groupBy(['fn_fee_structure.id']);
        }

        return $this->runQuery($query, $criteria, TRUE);
    }

    public function getFeesStructureAssignItem(QueryCriteria $criteria)
    {
        $fn_fee_structure_id = "";
        if (isset($_REQUEST['id'])) {
            $fn_fee_structure_id = $_REQUEST['id'];
        }
        $query = $this
            ->newQuery()
            ->from('fn_fees_class_assign')
            ->cols([
                'fn_fees_class_assign.*', 'fn_fee_structure.name AS structure_name', 'pupilsightProgram.name AS program_name', 'pupilsightYearGroup.name AS class'
            ])
            ->leftJoin('fn_fee_structure', 'fn_fees_class_assign.fn_fee_structure_id=fn_fee_structure.id')
            ->leftJoin('pupilsightProgram', 'fn_fees_class_assign.pupilsightProgramID=pupilsightProgram.pupilsightProgramID')
            ->leftJoin('pupilsightYearGroup', 'fn_fees_class_assign.pupilsightYearGroupID=pupilsightYearGroup.pupilsightYearGroupID')
            //->leftJoin('pupilsightRollGroup', 'fn_fees_class_assign.pupilsightRollGroupID=pupilsightRollGroup.pupilsightRollGroupID')
            ->where('fn_fees_class_assign.fn_fee_structure_id = :fn_fee_structure_id')
            ->bindValue('fn_fee_structure_id', $fn_fee_structure_id);


        return $this->runQuery($query, $criteria);
    }

    public function getFeesStructureAssignStudent(QueryCriteria $criteria, $input)
    {


        if (!empty($_SESSION['fee_str_search'])) {
            $input = $_SESSION['fee_str_search'];
        }
        //print_r($input);

        $pupilsightRoleIDAll = '003';
        $query = $this
            ->newQuery()
            ->from('pupilsightPerson')
            ->cols([
                'fn_fees_student_assign.*', 'pupilsightPerson.officialName AS student_name', 'pupilsightPerson.pupilsightPersonID AS stuid', 'pupilsightPerson.admission_no', "GROUP_CONCAT(DISTINCT fn_fee_structure.name SEPARATOR ', ') as structure_name", 'pupilsightPerson.pupilsightPersonID AS studentid', 'pupilsightYearGroup.name as classname', 'pupilsightProgram.name as progname'
            ])
            ->leftJoin('fn_fees_student_assign', 'pupilsightPerson.pupilsightPersonID=fn_fees_student_assign.pupilsightPersonID')
            ->leftJoin('fn_fee_structure', 'fn_fees_student_assign.fn_fee_structure_id=fn_fee_structure.id')
            ->leftJoin('pupilsightStudentEnrolment', 'pupilsightPerson.pupilsightPersonID=pupilsightStudentEnrolment.pupilsightPersonID')
            ->leftJoin('pupilsightYearGroup', 'pupilsightStudentEnrolment.pupilsightYearGroupID=pupilsightYearGroup.pupilsightYearGroupID')
            ->leftJoin('pupilsightProgram', 'pupilsightStudentEnrolment.pupilsightProgramID=pupilsightProgram.pupilsightProgramID')

            ->leftJoin('pupilsightFamilyRelationship', 'pupilsightPerson.pupilsightPersonID=pupilsightFamilyRelationship.pupilsightPersonID2')
            ->leftJoin('pupilsightPerson AS a', 'pupilsightFamilyRelationship.pupilsightPersonID1=a.pupilsightPersonID')

            ->leftJoin('pupilsightFamilyRelationship AS m', 'pupilsightPerson.pupilsightPersonID=m.pupilsightPersonID2')
            ->leftJoin('pupilsightPerson AS b', 'm.pupilsightPersonID1=b.pupilsightPersonID')

            ->where('pupilsightPerson.pupilsightRoleIDAll = "' . $pupilsightRoleIDAll . '" ');
        if (!empty($input['pupilsightProgramID'])) {
            $query->where('pupilsightStudentEnrolment.pupilsightProgramID = "' . $input['pupilsightProgramID'] . '" ');
        }
        if (!empty($input['pupilsightSchoolYearIDpost'])) {
            $query->where('pupilsightStudentEnrolment.pupilsightSchoolYearID = "' . $input['pupilsightSchoolYearIDpost'] . '" ');
        }
        if (!empty($input['pupilsightYearGroupID'])) {
            $query->where('pupilsightStudentEnrolment.pupilsightYearGroupID = "' . $input['pupilsightYearGroupID'] . '" ');
        }
        if (!empty($input['pupilsightRollGroupID'])) {
            $query->where('pupilsightStudentEnrolment.pupilsightRollGroupID = "' . $input['pupilsightRollGroupID'] . '" ');
        }
        if (!empty($input['searchfield'])) {
            $query->where('pupilsightPerson.' . $input['searchfield'] . ' = "' . $input['search'] . '" ');
        }
        if (!empty($input['search']) && empty($input['searchfield'])) {
            $search = $input['search'];
            $query->where('pupilsightPerson.officialName LIKE "%' . $search . '%" ')
                ->orwhere('pupilsightPerson.pupilsightPersonID = "' . $search . '" ')
                ->orwhere('pupilsightPerson.admission_no = "' . $search . '" ');
            // ->orwhere('a.officialName  LIKE "%'.$search.'%" ')
            // ->orwhere('a.phone1  = "'.$search.'" ')
            // ->orwhere('a.email  = "'.$search.'" ')
            // ->orwhere('b.officialName  LIKE "%'.$search.'%" ')
            // ->orwhere('b.phone1  = "'.$search.'" ')
            // ->orwhere('b.email  = "'.$search.'" ');
        }
        if (!empty($input['simplesearch']) && empty($input['searchfield'])) {
            $search = $input['simplesearch'];
            $query->where('pupilsightPerson.officialName LIKE "%' . $search . '%" ')
                ->orwhere('pupilsightPerson.pupilsightPersonID = "' . $search . '" ')
                ->orwhere('pupilsightPerson.admission_no = "' . $search . '" ');
            //->orwhere('a.officialName  LIKE "%'.$search.'%" ')
            //->orwhere('a.phone1  = "'.$search.'" ')
            //->orwhere('a.email  = "'.$search.'" ')
            // ->orwhere('b.officialName  LIKE "%'.$search.'%" ')
            // ->orwhere('b.phone1  = "'.$search.'" ')
            // ->orwhere('b.email  = "'.$search.'" ');
        }
        $query->groupBy(['pupilsightPerson.pupilsightPersonID'])
            ->orderBy(['pupilsightPerson.pupilsightPersonID']);
        //echo $query;    
        //     die();
        return $this->runQuery($query, $criteria, TRUE);
    }

    public function getStudentlist_quick_cashpayment(QueryCriteria $criteria)
    {

        // $query = $this
        //     ->newQuery()
        //     ->from('fn_fees_student_assign')
        //     ->cols([
        //         'fn_fees_student_assign.*','fn_fee_structure.name AS structure_name','pupilsightPerson.officialName AS student_name'
        //     ])
        //     ->leftJoin('fn_fee_structure', 'fn_fees_student_assign.fn_fee_structure_id=fn_fee_structure.id')
        //     ->leftJoin('pupilsightPerson', 'fn_fees_student_assign.pupilsightPersonID=pupilsightPerson.pupilsightPersonID');
        $pupilsightRoleIDAll = '003';
        $query = $this
            ->newQuery()
            ->from('pupilsightPerson')
            ->cols([
                'pupilsightPerson.officialName AS student_name', 'pupilsightPerson.pupilsightPersonID AS studentid', 'pupilsightPerson.pupilsightPersonID AS stuid'
            ])

            ->where('pupilsightPerson.pupilsightRoleIDAll = :pupilsightRoleIDAll')
            ->bindValue('pupilsightRoleIDAll', $pupilsightRoleIDAll)
            ->groupBy(['pupilsightPerson.pupilsightPersonID'])
            ->orderBy(['pupilsightPerson.pupilsightPersonID']);

        return $this->runQuery($query, $criteria);
    }

    public function getInv1oice(QueryCriteria $criteria, $input, $pupilsightSchoolYearID)
    {

        $query = $this
            ->newQuery()
            ->from('fn_fee_invoice')
            ->cols([
                'fn_fee_invoice.*', 'fn_fee_series.series_name', 'fn_fees_head.name AS account_head', 'pupilsightProgram.name AS program'
            ])
            // ->leftJoin('pupilsightSchoolYear', 'fn_fee_structure.pupilsightSchoolYearID=pupilsightSchoolYear.pupilsightSchoolYearID')
            ->leftJoin('fn_fee_series', 'fn_fee_invoice.inv_fn_fee_series_id=fn_fee_series.id')
            ->leftJoin('fn_fee_invoice_class_assign', ' fn_fee_invoice.id=fn_fee_invoice_class_assign.fn_fee_invoice_id')
            ->leftJoin('pupilsightProgram', ' fn_fee_invoice_class_assign.pupilsightProgramID=pupilsightProgram.pupilsightProgramID')

            ->leftJoin('fn_fees_head', 'fn_fee_invoice.fn_fees_head_id=fn_fees_head.id')
            ->where('fn_fee_invoice.pupilsightSchoolYearID  = "' . $pupilsightSchoolYearID . '" ')
            ->orderBy(['fn_fee_invoice.id DESC']);


        return $this->runQuery($query, $criteria);
    }


    public function getInvoiceTotal(QueryCriteria $criteria, $input, $pupilsightSchoolYearID)
    {
        if (!empty($_SESSION['invoice_search'])) {
            $input = $_SESSION['invoice_search'];
        }

        if (!empty($input['admission_no']) || !empty($input['pupilsightProgramID']) || !empty($input['pupilsightRollGroupID']) || !empty($input['student_name']) || !empty($input['due_date']) || !empty($input['invoice_status']) || !empty($input['invoice_title']) || !empty($input['invoice_no']) || !empty($input['invoice_date'])) {

            $query = $this
                ->newQuery()
                ->from('fn_fee_invoice')
                ->cols([
                    'fn_fee_invoice.*', 'fn_fee_series.series_name', 'fn_fees_head.name AS account_head', 'pupilsightProgram.name AS program', 'pupilsightYearGroup.name as class', 'pupilsightPerson.pupilsightPersonID as std_id', 'pupilsightPerson.officialName as std_name', 'pupilsightPerson.admission_no', 'pupilsightRollGroup.name as section', 'fn_fee_invoice_student_assign.invoice_no', 'fn_fee_invoice_student_assign.id as invid', 'fn_fee_invoice_student_assign.invoice_status as invstatus', 'fn_fee_invoice_student_assign.status as chkstatus', 'fn_fee_invoice_student_assign.id as insid'
                ])
                ->leftJoin('fn_fee_invoice_student_assign', ' fn_fee_invoice.id=fn_fee_invoice_student_assign.fn_fee_invoice_id')

                ->leftJoin('fn_fee_invoice_class_assign', ' fn_fee_invoice.id=fn_fee_invoice_class_assign.fn_fee_invoice_id')
                ->leftJoin('pupilsightProgram', ' fn_fee_invoice_class_assign.pupilsightProgramID=pupilsightProgram.pupilsightProgramID')

                ->leftJoin('pupilsightPerson', ' fn_fee_invoice_student_assign.pupilsightPersonID=pupilsightPerson.pupilsightPersonID')

                ->leftJoin('pupilsightYearGroup', ' fn_fee_invoice_class_assign.pupilsightYearGroupID=pupilsightYearGroup.pupilsightYearGroupID')

                ->leftJoin('pupilsightProgramClassSectionMapping', ' fn_fee_invoice_class_assign.pupilsightYearGroupID=pupilsightProgramClassSectionMapping.pupilsightYearGroupID')
                ->leftJoin('pupilsightRollGroup', ' pupilsightProgramClassSectionMapping.pupilsightRollGroupID=pupilsightRollGroup.pupilsightRollGroupID')
                ->leftJoin('pupilsightStudentEnrolment', 'fn_fee_invoice_student_assign.pupilsightPersonID=pupilsightStudentEnrolment.pupilsightPersonID')
                ->leftJoin('fn_fee_series', 'fn_fee_invoice.inv_fn_fee_series_id=fn_fee_series.id')
                ->leftJoin('fn_fees_head', 'fn_fee_invoice.fn_fees_head_id=fn_fees_head.id');

            if (!empty($input['pupilsightProgramID'])) {
                $query->where('fn_fee_invoice_class_assign.pupilsightProgramID = "' . $input['pupilsightProgramID'] . '" ');
            }
            //   if(!empty($input['pupilsightYearGroupID'])){
            // $query->where('fn_fee_invoice_class_assign.pupilsightYearGroupID = "'.$input['pupilsightYearGroupID'].'" ');
            // }


            // if(!empty($input['pupilsightRollGroupID'])){
            //     $query->where('pupilsightStudentEnrolment.pupilsightRollGroupID = "'.$input['pupilsightRollGroupID'].'" ');
            // }

            if (!empty($input['pupilsightYearGroupID'])) {
                $query->where('fn_fee_invoice_class_assign.pupilsightYearGroupID IN (' . implode(',', $input['pupilsightYearGroupID']) . ') ');
            }
            if (!empty($input['pupilsightRollGroupID'])) {
                $query->where('pupilsightStudentEnrolment.pupilsightRollGroupID IN (' . implode(',', $input['pupilsightRollGroupID']) . ') ');
            }

            if (!empty($input['admission_no'])) {
                $query->where('pupilsightPerson.admission_no = "' . $input['admission_no'] . '" ');
            }

            if (!empty($input['student_name'])) {
                $query->where('pupilsightPerson.officialName LIKE "%' . $input['student_name'] . '%" ');
            }
            if (!empty($input['due_date'])) {
                $sd = explode('/', $input['due_date']);
                $sdate  = date('Y-m-d', strtotime(implode('-', array_reverse($sd))));

                $query->where('fn_fee_invoice.due_date = "' . $sdate . '" ');
            }
            // if($input['invoice_status']!="All"){
            /*if($input['invoice_status']=="Active"){
                        $status=1;
                    } else {
                        $status=2;
                    }*/
            //     $query->where('fn_fee_invoice.status = "'.$input['invoice_status'].'" ');
            // }
            // if(!empty($input['pupilsightSchoolYearID'])){
            //     $query->where('fn_fee_invoice.pupilsightSchoolYearID = "'.$input['pupilsightSchoolYearID'].'" ');
            //     }
            if (!empty($input['invoice_title'])) {
                $query->where('fn_fee_invoice.title = "' . $input['invoice_title'] . '" ');
            }
            if (!empty($input['invoice_no'])) {
                $query->where('fn_fee_invoice_student_assign.invoice_no LIKE "%' . $input['invoice_no'] . '%" ');
            }
            if (!empty($input['invoice_date'])) {
                $fd = explode('/', $input['invoice_date']);
                $fdate  = date('Y-m-d', strtotime(implode('-', array_reverse($fd))));
                $query->where('fn_fee_invoice.cdt = "' . $fdate . '" ');
            }
            if (!empty($input['invoice_status'])) {
                $query->where('fn_fee_invoice_student_assign.invoice_status = "' . $input['invoice_status'] . '" ');
            }
            // echo $query;
            $query->where('fn_fee_invoice.pupilsightSchoolYearID = "' . $pupilsightSchoolYearID . '" ')
                ->where('pupilsightPerson.pupilsightRoleIDPrimary = 003')
                //->where('fn_fee_invoice_student_assign.status = 1 ')
                ->groupby(['fn_fee_invoice_student_assign.id'])
                ->orderby(['fn_fee_invoice_student_assign.id DESC']);
            //echo $query;
            // die();
        } else {
            $query = $this
                ->newQuery()
                ->from('fn_fee_invoice')
                ->cols([
                    'fn_fee_invoice.*'
                ])
                // ->leftJoin('pupilsightSchoolYear', 'fn_fee_structure.pupilsightSchoolYearID=pupilsightSchoolYear.pupilsightSchoolYearID')
                ->leftJoin('fn_fee_series', 'fn_fee_invoice.inv_fn_fee_series_id=fn_fee_series.id')
                ->leftJoin('fn_fee_invoice_class_assign', ' fn_fee_invoice.id=fn_fee_invoice_class_assign.fn_fee_invoice_id')
                ->leftJoin('fn_fee_invoice_student_assign', ' fn_fee_invoice.id=fn_fee_invoice_student_assign.fn_fee_invoice_id')
                ->leftJoin('pupilsightProgram', ' fn_fee_invoice_class_assign.pupilsightProgramID=pupilsightProgram.pupilsightProgramID')
                ->leftJoin('pupilsightPerson', ' fn_fee_invoice_student_assign.pupilsightPersonID=pupilsightPerson.pupilsightPersonID')

                ->leftJoin('pupilsightYearGroup', ' fn_fee_invoice_class_assign.pupilsightYearGroupID=pupilsightYearGroup.pupilsightYearGroupID')

                ->leftJoin('pupilsightProgramClassSectionMapping', ' fn_fee_invoice_class_assign.pupilsightYearGroupID=pupilsightProgramClassSectionMapping.pupilsightYearGroupID')
                ->leftJoin('pupilsightRollGroup', ' pupilsightProgramClassSectionMapping.pupilsightRollGroupID=pupilsightRollGroup.pupilsightRollGroupID')
                ->leftJoin('fn_fees_head', 'fn_fee_invoice.fn_fees_head_id=fn_fees_head.id')
                ->where('fn_fee_invoice.pupilsightSchoolYearID  = "' . $pupilsightSchoolYearID . '" AND fn_fee_invoice.status="1" ')
                //->groupby(['fn_fee_invoice.id']);
                //->where('fn_fee_invoice_student_assign.status = 1 ')
                ->where('pupilsightPerson.pupilsightRoleIDPrimary = 003')
                ->groupby(['fn_fee_invoice_student_assign.id']);
        }
        $res = $this->runQuery($query, $criteria);
        $data = $res->data;
        // echo '<pre>';
        // print_r($data);
        // echo '</pre>';
        // die();
        if (!empty($data)) {
            foreach ($data as $k => $d) {
                $invid = $d['id'];
                $query2 = $this
                    ->newQuery()
                    ->from('fn_fee_invoice_item')
                    ->cols([
                        'SUM(fn_fee_invoice_item.total_amount) as tot_amount', 'GROUP_CONCAT(fn_fee_invoice_item.id) AS itemid'
                    ])
                    ->where('fn_fee_invoice_item.fn_fee_invoice_id = "' . $invid . '" ');

                $newdata = $this->runQuery($query2, $criteria);
                if (!empty($newdata->data[0]['tot_amount'])) {
                    //$data[$k]['tot_amount'] = $newdata->data[0]['tot_amount'];
                    if (!empty($newdata->data[0]['itemid'])) {
                        $query3 = $this
                            ->newQuery()
                            ->from('fn_fee_item_level_discount')
                            ->cols([
                                'SUM(fn_fee_item_level_discount.discount) as tot_discount'
                            ])
                            ->where('fn_fee_item_level_discount.item_id IN (' . $newdata->data[0]['itemid'] . ') ')
                            ->where('fn_fee_item_level_discount.pupilsightPersonID = "' . $d['std_id'] . '" ');

                        $newdata2 = $this->runQuery($query3, $criteria);
                        if (!empty($newdata2->data[0]['tot_discount'])) {
                            $amt = $newdata->data[0]['tot_amount'] - $newdata2->data[0]['tot_discount'];
                            $data[$k]['inv_amount'] = $amt;
                        } else {
                            $data[$k]['inv_amount'] = $newdata->data[0]['tot_amount'];
                        }
                    } else {
                        $data[$k]['inv_amount'] = '';
                    }
                } else {
                    $data[$k]['tot_amount'] = '0';
                    $data[$k]['inv_amount'] = '0';
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
    public function getInvoice(QueryCriteria $criteria, $input, $pupilsightSchoolYearID)
    {


        if (!empty($_SESSION['invoice_search'])) {
            $input = $_SESSION['invoice_search'];
        }

        if (!empty($input['admission_no']) || !empty($input['pupilsightProgramID']) || !empty($input['pupilsightRollGroupID']) || !empty($input['student_name']) || !empty($input['due_date']) || !empty($input['invoice_status']) || !empty($input['invoice_title']) || !empty($input['invoice_no']) || !empty($input['invoice_date'])) {

            $query = $this
                ->newQuery()
                ->from('fn_fee_invoice')
                ->cols([
                    'fn_fee_invoice.*', 'fn_fee_series.series_name', 'fn_fees_head.name AS account_head', 'pupilsightProgram.name AS program', 'pupilsightYearGroup.name as class', 'pupilsightPerson.pupilsightPersonID as std_id', 'pupilsightPerson.officialName as std_name', 'pupilsightPerson.admission_no', 'pupilsightRollGroup.name as section', 'fn_fee_invoice_student_assign.invoice_no', 'fn_fee_invoice_student_assign.id as invid', 'fn_fee_invoice_student_assign.invoice_status as invstatus', 'fn_fee_invoice_student_assign.status as chkstatus', 'fn_fee_invoice_student_assign.id as insid'
                ])
                ->leftJoin('fn_fee_invoice_student_assign', ' fn_fee_invoice.id=fn_fee_invoice_student_assign.fn_fee_invoice_id')

                ->leftJoin('fn_fee_invoice_class_assign', ' fn_fee_invoice.id=fn_fee_invoice_class_assign.fn_fee_invoice_id')
                ->leftJoin('pupilsightProgram', ' fn_fee_invoice_class_assign.pupilsightProgramID=pupilsightProgram.pupilsightProgramID')

                ->leftJoin('pupilsightPerson', ' fn_fee_invoice_student_assign.pupilsightPersonID=pupilsightPerson.pupilsightPersonID')

                ->leftJoin('pupilsightYearGroup', ' fn_fee_invoice_class_assign.pupilsightYearGroupID=pupilsightYearGroup.pupilsightYearGroupID')

                // ->leftJoin('pupilsightProgramClassSectionMapping', ' fn_fee_invoice_class_assign.pupilsightYearGroupID=pupilsightProgramClassSectionMapping.pupilsightYearGroupID')
                // ->leftJoin('pupilsightRollGroup', ' pupilsightProgramClassSectionMapping.pupilsightRollGroupID=pupilsightRollGroup.pupilsightRollGroupID')
                ->leftJoin('pupilsightStudentEnrolment', 'fn_fee_invoice_student_assign.pupilsightPersonID=pupilsightStudentEnrolment.pupilsightPersonID')
                ->leftJoin('pupilsightRollGroup', ' pupilsightStudentEnrolment.pupilsightRollGroupID=pupilsightRollGroup.pupilsightRollGroupID')
                ->leftJoin('fn_fee_series', 'fn_fee_invoice.inv_fn_fee_series_id=fn_fee_series.id')
                ->leftJoin('fn_fees_head', 'fn_fee_invoice.fn_fees_head_id=fn_fees_head.id');

            if (!empty($input['pupilsightProgramID'])) {
                $query->where('fn_fee_invoice_class_assign.pupilsightProgramID = "' . $input['pupilsightProgramID'] . '" ');
            }
            //   if(!empty($input['pupilsightYearGroupID'])){
            // $query->where('fn_fee_invoice_class_assign.pupilsightYearGroupID = "'.$input['pupilsightYearGroupID'].'" ');
            // }


            // if(!empty($input['pupilsightRollGroupID'])){
            //     $query->where('pupilsightStudentEnrolment.pupilsightRollGroupID = "'.$input['pupilsightRollGroupID'].'" ');
            // }

            if (!empty($input['pupilsightYearGroupID'])) {
                $query->where('fn_fee_invoice_class_assign.pupilsightYearGroupID IN (' . implode(',', $input['pupilsightYearGroupID']) . ') ');
            }
            if (!empty($input['pupilsightRollGroupID'])) {
                $query->where('pupilsightStudentEnrolment.pupilsightRollGroupID IN (' . implode(',', $input['pupilsightRollGroupID']) . ') ');
            }

            if (!empty($input['admission_no'])) {
                $query->where('pupilsightPerson.admission_no = "' . $input['admission_no'] . '" ');
            }

            if (!empty($input['student_name'])) {
                $query->where('pupilsightPerson.officialName LIKE "%' . $input['student_name'] . '%" ');
            }
            if (!empty($input['due_date'])) {
                $sd = explode('/', $input['due_date']);
                $sdate  = date('Y-m-d', strtotime(implode('-', array_reverse($sd))));

                $query->where('fn_fee_invoice.due_date = "' . $sdate . '" ');
            }
            // if($input['invoice_status']!="All"){
            /*if($input['invoice_status']=="Active"){
                        $status=1;
                    } else {
                        $status=2;
                    }*/
            //     $query->where('fn_fee_invoice.status = "'.$input['invoice_status'].'" ');
            // }
            // if(!empty($input['pupilsightSchoolYearID'])){
            //     $query->where('fn_fee_invoice.pupilsightSchoolYearID = "'.$input['pupilsightSchoolYearID'].'" ');
            //     }
            if (!empty($input['invoice_title'])) {
                $query->where('fn_fee_invoice.title = "' . $input['invoice_title'] . '" ');
            }
            if (!empty($input['invoice_no'])) {
                $query->where('fn_fee_invoice_student_assign.invoice_no LIKE "%' . $input['invoice_no'] . '%" ');
            }
            if (!empty($input['invoice_date'])) {
                $fd = explode('/', $input['invoice_date']);
                $fdate  = date('Y-m-d', strtotime(implode('-', array_reverse($fd))));
                $query->where('fn_fee_invoice.cdt = "' . $fdate . '" ');
            }
            if (!empty($input['invoice_status'])) {
                $query->where('fn_fee_invoice_student_assign.invoice_status = "' . $input['invoice_status'] . '" ');
            }
            // echo $query;
            $query->where('fn_fee_invoice.pupilsightSchoolYearID = "' . $pupilsightSchoolYearID . '" ')
                ->where('pupilsightPerson.pupilsightRoleIDPrimary = 003')
                //->where('fn_fee_invoice_student_assign.status = 1 ')
                ->groupby(['fn_fee_invoice_student_assign.id'])
                ->orderby(['fn_fee_invoice_student_assign.id DESC']);
            //echo $query;
            // die();
        } else {
            $query = $this
                ->newQuery()
                ->from('fn_fee_invoice')
                ->cols([
                    'fn_fee_invoice.*', 'fn_fee_series.series_name', 'fn_fees_head.name AS account_head', 'pupilsightProgram.name AS program', 'pupilsightYearGroup.name as class', 'pupilsightPerson.pupilsightPersonID as std_id', 'pupilsightPerson.officialName as std_name', 'pupilsightPerson.admission_no', 'pupilsightRollGroup.name as section', 'fn_fee_invoice_student_assign.invoice_no', 'fn_fee_invoice_student_assign.id as invid', 'fn_fee_invoice_student_assign.invoice_status as invstatus', 'fn_fee_invoice_student_assign.status as chkstatus', 'fn_fee_invoice_student_assign.id as insid'
                ])
                // ->leftJoin('pupilsightSchoolYear', 'fn_fee_structure.pupilsightSchoolYearID=pupilsightSchoolYear.pupilsightSchoolYearID')
                ->leftJoin('fn_fee_series', 'fn_fee_invoice.inv_fn_fee_series_id=fn_fee_series.id')
                ->leftJoin('fn_fee_invoice_class_assign', ' fn_fee_invoice.id=fn_fee_invoice_class_assign.fn_fee_invoice_id')
                ->leftJoin('fn_fee_invoice_student_assign', ' fn_fee_invoice.id=fn_fee_invoice_student_assign.fn_fee_invoice_id')
                ->leftJoin('pupilsightProgram', ' fn_fee_invoice_class_assign.pupilsightProgramID=pupilsightProgram.pupilsightProgramID')
                ->leftJoin('pupilsightPerson', ' fn_fee_invoice_student_assign.pupilsightPersonID=pupilsightPerson.pupilsightPersonID')

                ->leftJoin('pupilsightYearGroup', ' fn_fee_invoice_class_assign.pupilsightYearGroupID=pupilsightYearGroup.pupilsightYearGroupID')

                // ->leftJoin('pupilsightProgramClassSectionMapping', ' fn_fee_invoice_class_assign.pupilsightYearGroupID=pupilsightProgramClassSectionMapping.pupilsightYearGroupID')
                // ->leftJoin('pupilsightRollGroup', ' pupilsightProgramClassSectionMapping.pupilsightRollGroupID=pupilsightRollGroup.pupilsightRollGroupID')
                ->leftJoin('pupilsightStudentEnrolment', 'fn_fee_invoice_student_assign.pupilsightPersonID=pupilsightStudentEnrolment.pupilsightPersonID')
                ->leftJoin('pupilsightRollGroup', ' pupilsightStudentEnrolment.pupilsightRollGroupID=pupilsightRollGroup.pupilsightRollGroupID')
                ->leftJoin('fn_fees_head', 'fn_fee_invoice.fn_fees_head_id=fn_fees_head.id')
                ->where('fn_fee_invoice.pupilsightSchoolYearID  = "' . $pupilsightSchoolYearID . '" AND fn_fee_invoice.status="1" ')
                //->where('fn_fee_invoice_student_assign.status = 1 ')
                ->where('pupilsightPerson.pupilsightRoleIDPrimary = 003')
                ->groupby(['fn_fee_invoice_student_assign.id'])
                ->orderby(['fn_fee_invoice_student_assign.id DESC']);
            //echo $query;
        }

        //return $this->runQuery($query, $criteria, true);
        $res = $this->runQuery($query, $criteria, true);
        $data = $res->data;
        // echo '<pre>';
        // print_r($data);
        // echo '</pre>';
        // die();
        if (!empty($data)) {
            foreach ($data as $k => $d) {
                $invid = $d['id'];
                $chkstatus = $d['chkstatus'];

                $query2 = $this
                    ->newQuery()
                    ->from('fn_fees_student_collection')
                    ->cols([
                        'SUM(fn_fees_student_collection.total_amount_collection) as paid_amount', 'transaction_id'
                    ])
                    ->where('fn_fees_student_collection.fn_fees_invoice_id = "' . $invid . '" ')
                    ->where('fn_fees_student_collection.pupilsightPersonID = "' . $d['std_id'] . '" ');

                $newdata = $this->runQuery($query2, $criteria);

                if (!empty($newdata->data[0]['transaction_id'])) {
                    $query7 = $this
                        ->newQuery()
                        ->from('fn_fees_collection')
                        ->cols([
                            'invoice_status'
                        ])
                        ->where('fn_fees_collection.transaction_id = "' . $newdata->data[0]['transaction_id'] . '" ')
                        ->where('fn_fees_collection.transaction_status = "1" ');
                    $newdata7 = $this->runQuery($query7, $criteria);
                    if (!empty($newdata7->data[0]['invoice_status'])) {
                        if ($newdata7->data[0]['invoice_status'] == 'Fully Paid') {
                            $data[$k]['chkinvstatus'] = 'paid';
                            $data[$k]['paid'] = '';
                        } else {
                            if (!empty($newdata->data[0]['paid_amount'])) {
                                $data[$k]['chkinvstatus'] = 'paid';
                                if (!empty($newdata->data[0]['paid_amount'])) {
                                    $data[$k]['paid'] = $newdata->data[0]['paid_amount'];
                                } else {
                                    $data[$k]['paid'] = '';
                                }
                            } else {
                                $data[$k]['chkinvstatus'] = '';
                                $data[$k]['paid'] = '';
                            }
                        }
                    } else {
                        if (!empty($newdata->data[0]['paid_amount'])) {
                            $data[$k]['chkinvstatus'] = 'paid';
                            if (!empty($newdata->data[0]['paid_amount'])) {
                                $data[$k]['paid'] = $newdata->data[0]['paid_amount'];
                            } else {
                                $data[$k]['paid'] = '';
                            }
                        } else {
                            $data[$k]['chkinvstatus'] = '';
                            $data[$k]['paid'] = '';
                        }
                    }
                } else {
                    $data[$k]['chkinvstatus'] = '';
                    $data[$k]['paid'] = '';
                }

                $query3 = $this
                    ->newQuery()
                    ->from('fn_fee_invoice_item')
                    ->cols([
                        'SUM(fn_fee_invoice_item.total_amount) as tot_amount', 'GROUP_CONCAT(fn_fee_invoice_item.id) AS itemid'
                    ])
                    ->where('fn_fee_invoice_item.fn_fee_invoice_id = "' . $invid . '" ');

                $newdata1 = $this->runQuery($query3, $criteria);
                if (!empty($newdata1->data[0]['tot_amount'])) {
                    if (!empty($newdata1->data[0]['itemid'])) {
                        $query3 = $this
                            ->newQuery()
                            ->from('fn_fee_item_level_discount')
                            ->cols([
                                'SUM(fn_fee_item_level_discount.discount) as tot_discount'
                            ])
                            ->where('fn_fee_item_level_discount.item_id IN (' . $newdata1->data[0]['itemid'] . ') ')
                            ->where('fn_fee_item_level_discount.pupilsightPersonID = "' . $d['std_id'] . '" ');

                        $newdata2 = $this->runQuery($query3, $criteria);
                        if (!empty($newdata2->data[0]['tot_discount'])) {
                            $amt = $newdata1->data[0]['tot_amount'] - $newdata2->data[0]['tot_discount'];
                            $data[$k]['inv_amount'] = $amt;
                        } else {
                            $data[$k]['inv_amount'] = $newdata1->data[0]['tot_amount'];
                        }
                    } else {
                        $data[$k]['inv_amount'] = '';
                    }
                } else {
                    $data[$k]['inv_amount'] = '';
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

    public function getInvoiceAssignItem(QueryCriteria $criteria)
    {
        $fn_fee_invoice_id = "";
        if (isset($_REQUEST['id'])) {
            $fn_fee_invoice_id = $_REQUEST['id'];
        }
        $query = $this
            ->newQuery()
            ->from('fn_fee_invoice_class_assign')
            ->cols([
                'fn_fee_invoice_class_assign.*', 'fn_fee_invoice.title AS invoice_name', 'fn_fee_invoice.id AS invoice_id', 'pupilsightProgram.name AS program_name', "GROUP_CONCAT(DISTINCT pupilsightYearGroup.name SEPARATOR ', ') as class"
            ])
            ->leftJoin('fn_fee_invoice', 'fn_fee_invoice_class_assign.fn_fee_invoice_id=fn_fee_invoice.id')
            ->leftJoin('pupilsightProgram', 'fn_fee_invoice_class_assign.pupilsightProgramID=pupilsightProgram.pupilsightProgramID')
            ->leftJoin('pupilsightYearGroup', 'fn_fee_invoice_class_assign.pupilsightYearGroupID=pupilsightYearGroup.pupilsightYearGroupID')
            //->leftJoin('pupilsightRollGroup', 'fn_fee_invoice_class_assign.pupilsightRollGroupID=pupilsightRollGroup.pupilsightRollGroupID')
            ->where('fn_fee_invoice_class_assign.fn_fee_invoice_id = :fn_fee_invoice_id')
            ->bindValue('fn_fee_invoice_id', $fn_fee_invoice_id)
            ->groupBy(['fn_fee_invoice_class_assign.pupilsightProgramID']);


        return $this->runQuery($query, $criteria);
    }

    public function getFeesMaster(QueryCriteria $criteria)
    {
        $query = $this
            ->newQuery()
            ->from('fn_masters')
            ->cols([
                'id', 'name', 'type'
            ]);

        $criteria->addFilterRules([
            'type' => function ($query, $type) {
                return $query
                    ->where('type = :type')
                    ->bindValue('type', ucfirst($type));
            },
        ]);
        $query->orderby(['fn_masters.id DESC']);;


        return $this->runQuery($query, $criteria);
    }

    public function getCollectionInvoice(QueryCriteria $criteria, $stuId)
    {
        // if(!empty($pupilsightProgramID) && !empty($pupilsightSchoolYearIDpost) && !empty($pupilsightYearGroupID) && !empty($pupilsightRollGroupID) && !empty($search) ) {

        $query = $this
            ->newQuery()
            ->from('fn_fee_invoice')
            ->cols([
                'fn_fee_invoice.*', 'fn_fee_invoice.id as invoiceid', "SUM(fn_fee_invoice_item.amount) as totalamount", "SUM(fn_fee_invoice_item.amount) as pendingamount", 'fn_fee_invoice_student_assign.invoice_no as stu_invoice_no'
            ])
            ->leftJoin('pupilsightStudentEnrolment', 'fn_fee_invoice.pupilsightSchoolYearID=pupilsightStudentEnrolment.pupilsightSchoolYearID')
            ->leftJoin('pupilsightPerson', 'pupilsightStudentEnrolment.pupilsightPersonID=pupilsightPerson.pupilsightPersonID')
            ->leftJoin('fn_fee_invoice_item', 'fn_fee_invoice.id=fn_fee_invoice_item.fn_fee_invoice_id')
            ->leftJoin('fn_fee_invoice_student_assign', 'pupilsightPerson.pupilsightPersonID=fn_fee_invoice_student_assign.pupilsightPersonID');
        if (!empty($stuId)) {
            $query->where('pupilsightPerson.pupilsightPersonID = "' . $stuId . '" ');
        }
        $query->groupBy(['fn_fee_invoice.id']);
        // } else {
        //    $query = $this
        //     ->newQuery()
        //     ->from('fn_fee_invoice')
        //     ->cols([
        //         'fn_fee_invoice.*','fn_fee_invoice.id as invoiceid','fn_fee_series.format','fn_fee_series.start_number','fn_fee_series.no_of_digit','fn_fee_series.start_char','fn_fees_head.name AS account_head',"SUM(fn_fee_invoice_item.amount) as totalamount", "SUM(fn_fee_invoice_item.amount) as pendingamount"
        //     ])
        //     ->leftJoin('fn_fee_invoice_item', 'fn_fee_invoice.id=fn_fee_invoice_item.fn_fee_invoice_id')
        //     ->leftJoin('fn_fee_series', 'fn_fee_invoice.inv_fn_fee_series_id=fn_fee_series.id')
        //     ->leftJoin('fn_fees_head', 'fn_fee_invoice.fn_fees_head_id=fn_fees_head.id')
        //     ->groupBy(['fn_fee_invoice.id']);
        // }
        //echo $query;
        $data = $this->runQuery($query, $criteria);
        foreach ($data as $k => $d) {
            $invid =  $d['invoiceid'];
            $invno =  $d['stu_invoice_no'];
            $sqla = 'SELECT GROUP_CONCAT(fn_fee_invoice_item_id) AS invitemid FROM fn_fees_student_collection WHERE invoice_no = "' . $invno . '"';
            $resulta = $connection2->query($sqla);
            $inv = $resulta->fetch();

            $itemids = $inv['invitemid'];
            $sqlp = 'SELECT SUM(amount) as paidtotalamount FROM fn_fee_invoice_item WHERE fn_fee_invoice_id IN "(' . $itemids . ')"';
            $resultp = $connection2->query($sqlp);
            $amt = $resultp->fetch();
            $totalamt = $amt['paidtotalamount'];
            $data[$k]['paidamount'] = $totalamt;
        }
        echo '<pre>';
        print_r($data);
        echo '</pre>';
        die();
    }

    public function getCollectionTransaction(QueryCriteria $criteria, $stuId)
    {
        $query = $this
            ->newQuery()
            ->from('fn_fees_collection')
            ->cols([
                'fn_fees_collection.*'
            ])
            ->where('fn_fees_collection.pupilsightPersonID = "' . $stuId . '" ')
            ->where('fn_fees_collection.transaction_status = "1" ');


        return $this->runQuery($query, $criteria);
    }

    public function getInvoiceFeeItems(QueryCriteria $criteria)
    {
        $query = $this
            ->newQuery()
            ->from('fn_fee_invoice_item')
            ->cols([
                'fn_fee_invoice_item.*'
            ]);
        //->where('fn_fee_invoice_item.id = "0" ');


        return $this->runQuery($query, $criteria);
    }

    public function getFeesTransactiontotal(QueryCriteria $criteria, $input, $pupilsightSchoolYearID)
    {
        if (!empty($_SESSION['trnsaction_search'])) {
            $input = $_SESSION['trnsaction_search'];
        }
        if (!empty($input)) {
            $query = $this
                ->newQuery()
                ->from('fn_fees_collection')
                ->cols([
                    'fn_fees_collection.*', 'pupilsightPerson.officialName as student_name', 'pupilsightPerson.email', 'pupilsightPerson.phone1', 'pupilsightPerson.pupilsightPersonID as stu_id', 'fn_fees_collection.id as collection_id', 'GROUP_CONCAT(DISTINCT fn_fees_student_collection.invoice_no) as invoice_no', 'fn_masters.name as paymentmode', 'bnk.name as bankname', 'pupilsightStudentEnrolment.pupilsightYearGroupID as classid', 'pupilsightStudentEnrolment.pupilsightRollGroupID as sectionid'
                ])
                ->leftJoin('pupilsightPerson', 'fn_fees_collection.pupilsightPersonID=pupilsightPerson.pupilsightPersonID')
                ->leftJoin('pupilsightStudentEnrolment', 'fn_fees_collection.pupilsightPersonID=pupilsightStudentEnrolment.pupilsightPersonID')
                ->leftJoin('fn_fees_student_collection', 'fn_fees_collection.transaction_id=fn_fees_student_collection.transaction_id')
                //->leftJoin('fn_fee_invoice_student_assign', 'fn_fees_student_collection.invoice_no=fn_fee_invoice_student_assign.invoice_no')
                ->leftJoin('fn_masters', 'fn_fees_collection.payment_mode_id=fn_masters.id')
                ->leftJoin('fn_masters as bnk', 'fn_fees_collection.bank_id=bnk.id');
            if (!empty($input['transaction_id'])) {
                $query->where('fn_fees_collection.transaction_id = "' . $input['transaction_id'] . '" ');
            }
            // if(!empty($input['pupilsightPersonID'])){
            //     $query->where('fn_fees_collection.pupilsightPersonID = "'.$input['pupilsightPersonID'].'" ');
            // }
            if (!empty($input['receipt_number'])) {
                $query->where('fn_fees_collection.receipt_number = "' . $input['receipt_number'] . '" ');
            }
            if (!empty($input['instrument_no'])) {
                $query->where('fn_fees_collection.instrument_no = "' . $input['instrument_no'] . '" ');
            }
            if (!empty($input['bank_id'])) {
                $query->where('fn_fees_collection.bank_id = "' . $input['bank_id'] . '" ');
            }
            if (!empty($input['payment_mode_id'])) {
                $query->where('fn_fees_collection.payment_mode_id = "' . $input['payment_mode_id'] . '" ');
            }
            if (!empty($input['payment_status'])) {
                if ($input['payment_status'] == 'Transaction Successful') {
                    $query->where('fn_fees_collection.payment_status IS NULL ');
                } else {
                    $query->where('fn_fees_collection.payment_status = "' . $input['payment_status'] . '" ');
                }
            }
            if (!empty($input['admission_no'])) {
                $query->where('pupilsightPerson.admission_no = "' . $input['admission_no'] . '" ');
            }
            if (!empty($input['student_name'])) {
                $query->where('pupilsightPerson.officialName LIKE "%' . $input['student_name'] . '%" ');
            }
            if (!empty($input['pupilsightProgramID'])) {
                $query->where('pupilsightStudentEnrolment.pupilsightProgramID = "' . $input['pupilsightProgramID'] . '" ');
            }
            if (!empty($input['pupilsightYearGroupID'])) {
                $query->where('pupilsightStudentEnrolment.pupilsightYearGroupID IN (' . implode(',', $input['pupilsightYearGroupID']) . ') ');
            }
            if (!empty($input['pupilsightRollGroupID'])) {
                $query->where('pupilsightStudentEnrolment.pupilsightRollGroupID IN (' . implode(',', $input['pupilsightRollGroupID']) . ') ');
            }
            if (!empty($input['startdate']) && !empty($input['enddate'])) {
                $sd = explode('/', $_POST['startdate']);
                $sdate  = date('Y-m-d', strtotime(implode('-', array_reverse($sd))));
                $ed = explode('/', $_POST['enddate']);
                $edate  = date('Y-m-d', strtotime(implode('-', array_reverse($ed))));

                $query->where('fn_fees_collection.payment_date >= "' . $sdate . '" ')
                    ->where('fn_fees_collection.payment_date <= "' . $edate . '" ');
            }
            $query->where('fn_fees_collection.transaction_status = "1" ')
                ->where('fn_fees_collection.pupilsightSchoolYearID = "' . $pupilsightSchoolYearID . '" ')
                ->groupBy(['fn_fees_collection.id']);
        } else {
            $query = $this
                ->newQuery()
                ->from('fn_fees_collection')
                ->cols([
                    'fn_fees_collection.*', 'pupilsightPerson.officialName as student_name', 'pupilsightPerson.email', 'pupilsightPerson.phone1', 'pupilsightPerson.pupilsightPersonID as stu_id', 'fn_fees_collection.id as collection_id', 'GROUP_CONCAT(DISTINCT fn_fees_student_collection.invoice_no) as invoice_no', 'fn_masters.name as paymentmode', 'bnk.name as bankname', 'pupilsightStudentEnrolment.pupilsightYearGroupID as classid', 'pupilsightStudentEnrolment.pupilsightRollGroupID as sectionid'
                ])
                ->leftJoin('pupilsightPerson', 'fn_fees_collection.pupilsightPersonID=pupilsightPerson.pupilsightPersonID')
                ->leftJoin('fn_fees_student_collection', 'fn_fees_collection.transaction_id=fn_fees_student_collection.transaction_id')
                ->leftJoin('pupilsightStudentEnrolment', 'fn_fees_collection.pupilsightPersonID=pupilsightStudentEnrolment.pupilsightPersonID')
                //->leftJoin('fn_fee_invoice_student_assign', 'fn_fees_student_collection.invoice_no=fn_fee_invoice_student_assign.invoice_no')
                ->leftJoin('fn_masters', 'fn_fees_collection.payment_mode_id=fn_masters.id')
                ->leftJoin('fn_masters as bnk', 'fn_fees_collection.bank_id=bnk.id')
                ->where('fn_fees_collection.transaction_status = "1" ')
                ->where('fn_fees_collection.pupilsightSchoolYearID = "' . $pupilsightSchoolYearID . '" ')
                ->groupBy(['fn_fees_collection.id']);
        }

        return $query;
    }

    public function getFeesTransaction(QueryCriteria $criteria, $input, $pupilsightSchoolYearID)
    {
        if (!empty($_SESSION['trnsaction_search'])) {
            $input = $_SESSION['trnsaction_search'];
        }

        if (!empty($input)) {
            $query = $this
                ->newQuery()
                ->from('fn_fees_collection')
                ->cols([
                    'fn_fees_collection.*', 'pupilsightPerson.officialName as student_name', 'pupilsightPerson.email', 'pupilsightPerson.phone1', 'pupilsightPerson.pupilsightPersonID as stu_id', 'fn_fees_collection.id as collection_id', 'GROUP_CONCAT(DISTINCT fn_fees_student_collection.invoice_no) as invoice_no', 'fn_masters.name as paymentmode', 'bnk.name as bankname', 'pupilsightStudentEnrolment.pupilsightYearGroupID as classid', 'pupilsightStudentEnrolment.pupilsightRollGroupID as sectionid', 'pupilsightPerson.admission_no', 'pupilsightYearGroup.name as class', 'pupilsightRollGroup.name as section'
                ])
                ->leftJoin('pupilsightPerson', 'fn_fees_collection.pupilsightPersonID=pupilsightPerson.pupilsightPersonID')
                ->leftJoin('pupilsightStudentEnrolment', 'fn_fees_collection.pupilsightPersonID=pupilsightStudentEnrolment.pupilsightPersonID')
                ->leftJoin('pupilsightYearGroup', 'pupilsightStudentEnrolment.pupilsightYearGroupID=pupilsightYearGroup.pupilsightYearGroupID')
                ->leftJoin('pupilsightRollGroup', 'pupilsightStudentEnrolment.pupilsightRollGroupID=pupilsightRollGroup.pupilsightRollGroupID')
                ->leftJoin('fn_fees_student_collection', 'fn_fees_collection.transaction_id=fn_fees_student_collection.transaction_id')
                //->leftJoin('fn_fee_invoice_student_assign', 'fn_fees_student_collection.invoice_no=fn_fee_invoice_student_assign.invoice_no')
                ->leftJoin('fn_masters', 'fn_fees_collection.payment_mode_id=fn_masters.id')
                ->leftJoin('fn_masters as bnk', 'fn_fees_collection.bank_id=bnk.id');
            if (!empty($input['transaction_id'])) {
                $query->where('fn_fees_collection.transaction_id = "' . $input['transaction_id'] . '" ');
            }
            // if(!empty($input['pupilsightPersonID'])){
            //     $query->where('fn_fees_collection.pupilsightPersonID = "'.$input['pupilsightPersonID'].'" ');
            // }
            if (!empty($input['receipt_number'])) {
                $query->where('fn_fees_collection.receipt_number = "' . $input['receipt_number'] . '" ');
            }
            if (!empty($input['instrument_no'])) {
                $query->where('fn_fees_collection.instrument_no = "' . $input['instrument_no'] . '" ');
            }
            if (!empty($input['bank_id'])) {
                $query->where('fn_fees_collection.bank_id = "' . $input['bank_id'] . '" ');
            }
            if (!empty($input['payment_mode_id'])) {
                $query->where('fn_fees_collection.payment_mode_id = "' . $input['payment_mode_id'] . '" ');
            }
            if (!empty($input['payment_status'])) {
                if ($input['payment_status'] == 'Transaction Successful') {
                    $query->where('fn_fees_collection.payment_status IS NULL ');
                } else {
                    $query->where('fn_fees_collection.payment_status = "' . $input['payment_status'] . '" ');
                }
            }
            if (!empty($input['admission_no'])) {
                $query->where('pupilsightPerson.admission_no = "' . $input['admission_no'] . '" ');
            }
            if (!empty($input['student_name'])) {
                $query->where('pupilsightPerson.officialName LIKE "%' . $input['student_name'] . '%" ');
            }
            if (!empty($input['pupilsightProgramID'])) {
                $query->where('pupilsightStudentEnrolment.pupilsightProgramID = "' . $input['pupilsightProgramID'] . '" ');
            }
            if (!empty($input['pupilsightYearGroupID'])) {
                $query->where('pupilsightStudentEnrolment.pupilsightYearGroupID IN (' . implode(',', $input['pupilsightYearGroupID']) . ') ');
            }
            if (!empty($input['pupilsightRollGroupID'])) {
                $query->where('pupilsightStudentEnrolment.pupilsightRollGroupID IN (' . implode(',', $input['pupilsightRollGroupID']) . ') ');
            }

            if (!empty($input['startdate']) && !empty($input['enddate'])) {
                $sd = explode('/', $_POST['startdate']);
                $sdate  = date('Y-m-d', strtotime(implode('-', array_reverse($sd))));
                $ed = explode('/', $_POST['enddate']);
                $edate  = date('Y-m-d', strtotime(implode('-', array_reverse($ed))));

                $query->where('fn_fees_collection.payment_date >= "' . $sdate . '" ')
                    ->where('fn_fees_collection.payment_date <= "' . $edate . '" ');
            }
            $query->where('fn_fees_collection.transaction_status = "1" ')
                ->where('fn_fees_collection.pupilsightSchoolYearID = "' . $pupilsightSchoolYearID . '" ')
                ->where('pupilsightStudentEnrolment.pupilsightSchoolYearID = "' . $pupilsightSchoolYearID . '" ')
                ->groupBy(['fn_fees_collection.id'])
                ->orderBy(['fn_fees_collection.id DESC']);
            //echo $query;
        } else {
            $query = $this
                ->newQuery()
                ->from('fn_fees_collection')
                ->cols([
                    'fn_fees_collection.*', 'pupilsightPerson.officialName as student_name', 'pupilsightPerson.email', 'pupilsightPerson.phone1', 'pupilsightPerson.pupilsightPersonID as stu_id', 'fn_fees_collection.id as collection_id', 'GROUP_CONCAT(DISTINCT fn_fees_student_collection.invoice_no) as invoice_no', 'fn_masters.name as paymentmode', 'bnk.name as bankname', 'pupilsightStudentEnrolment.pupilsightYearGroupID as classid', 'pupilsightStudentEnrolment.pupilsightRollGroupID as sectionid', 'pupilsightYearGroup.name as class', 'pupilsightRollGroup.name as section'
                ])

                ->leftJoin('pupilsightPerson', 'fn_fees_collection.pupilsightPersonID=pupilsightPerson.pupilsightPersonID')
                ->leftJoin('fn_fees_student_collection', 'fn_fees_collection.transaction_id=fn_fees_student_collection.transaction_id')
                ->leftJoin('pupilsightStudentEnrolment', 'fn_fees_collection.pupilsightPersonID=pupilsightStudentEnrolment.pupilsightPersonID')
                ->leftJoin('pupilsightYearGroup', 'pupilsightStudentEnrolment.pupilsightYearGroupID=pupilsightYearGroup.pupilsightYearGroupID')
                ->leftJoin('pupilsightRollGroup', 'pupilsightStudentEnrolment.pupilsightRollGroupID=pupilsightRollGroup.pupilsightRollGroupID')
                //->leftJoin('fn_fee_invoice_student_assign', 'fn_fees_student_collection.invoice_no=fn_fee_invoice_student_assign.invoice_no')
                ->leftJoin('fn_masters', 'fn_fees_collection.payment_mode_id=fn_masters.id')
                ->leftJoin('fn_masters as bnk', 'fn_fees_collection.bank_id=bnk.id')
                ->where('fn_fees_collection.transaction_status = "1" ')
                ->where('fn_fees_collection.pupilsightSchoolYearID = "' . $pupilsightSchoolYearID . '" ')
                ->where('pupilsightStudentEnrolment.pupilsightSchoolYearID = "' . $pupilsightSchoolYearID . '" ')
                ->groupBy(['fn_fees_collection.id'])
                ->orderBy(['fn_fees_collection.id DESC']);
        }
        //echo $query;  
        return $this->runQuery($query, $criteria);
    }
    public function getFeesCancelTransaction(QueryCriteria $criteria, $input, $pupilsightSchoolYearID)
    {
        if (!empty($_SESSION['can_trnsaction_search'])) {
            $input = $_SESSION['can_trnsaction_search'];
        }
        if (!empty($input)) {
            $query = $this
                ->newQuery()
                ->from('fn_fees_collection')
                ->cols([
                    'fn_fees_collection.*', 'pupilsightPerson.officialName as student_name', 'pupilsightPerson.pupilsightPersonID as stu_id', 'pupilsightPerson.admission_no', 'fn_fees_collection.id as collection_id', 'GROUP_CONCAT(DISTINCT fn_fees_student_cancel_collection.invoice_no) as invoice_no', 'fn_masters.name as paymentmode', 'bnk.name as bankname', 'fn_fees_cancel_collection.remarks AS cancelreason', 'a.officialName as stfName', 'fn_fees_cancel_collection.cdt'
                ])
                ->leftJoin('pupilsightPerson', 'fn_fees_collection.pupilsightPersonID=pupilsightPerson.pupilsightPersonID')
                ->leftJoin('pupilsightStudentEnrolment', 'fn_fees_collection.pupilsightPersonID=pupilsightStudentEnrolment.pupilsightPersonID')
                ->leftJoin('fn_fees_student_cancel_collection', 'fn_fees_collection.transaction_id=fn_fees_student_cancel_collection.transaction_id')
                ->leftJoin('fn_fee_invoice_student_assign', 'fn_fees_student_cancel_collection.invoice_no=fn_fee_invoice_student_assign.invoice_no')
                ->leftJoin('fn_masters', 'fn_fees_collection.payment_mode_id=fn_masters.id')
                ->leftJoin('fn_masters as bnk', 'fn_fees_collection.bank_id=bnk.id')
                ->leftJoin('fn_fees_cancel_collection', 'fn_fees_collection.id=fn_fees_cancel_collection.fn_fees_collection_id')
                ->leftJoin('pupilsightPerson AS a', 'fn_fees_cancel_collection.canceled_by=a.pupilsightPersonID');
            if (!empty($input['transaction_id'])) {
                $query->where('fn_fees_collection.transaction_id = "' . $input['transaction_id'] . '" ');
            }
            if (!empty($input['admission_no'])) {
                $query->where('pupilsightPerson.admission_no = "' . $input['admission_no'] . '" ');
            }
            if (!empty($input['receipt_number'])) {
                $query->where('fn_fees_collection.receipt_number = "' . $input['receipt_number'] . '" ');
            }
            if (!empty($input['instrument_no'])) {
                $query->where('fn_fees_collection.instrument_no = "' . $input['instrument_no'] . '" ');
            }
            if (!empty($input['bank_id'])) {
                $query->where('fn_fees_collection.bank_id = "' . $input['bank_id'] . '" ');
            }
            if (!empty($input['payment_mode_id'])) {
                $query->where('fn_fees_collection.payment_mode_id = "' . $input['payment_mode_id'] . '" ');
            }
            if (!empty($input['student_name'])) {
                $query->where('pupilsightPerson.officialName LIKE "%' . $input['student_name'] . '%" ');
            }
            if (!empty($input['pupilsightYearGroupID'])) {
                $query->where('pupilsightStudentEnrolment.pupilsightYearGroupID = "' . $input['pupilsightYearGroupID'] . '" ');
            }
            if (!empty($input['pupilsightRollGroupID'])) {
                $query->where('pupilsightStudentEnrolment.pupilsightRollGroupID = "' . $input['pupilsightRollGroupID'] . '" ');
            }
            if (!empty($input['startdate']) && !empty($input['enddate'])) {
                $sd = explode('/', $_POST['startdate']);
                $sdate  = date('Y-m-d', strtotime(implode('-', array_reverse($sd))));
                $ed = explode('/', $_POST['enddate']);
                $edate  = date('Y-m-d', strtotime(implode('-', array_reverse($ed))));

                $query->where('fn_fees_collection.payment_date >= "' . $sdate . '" ')
                    ->where('fn_fees_collection.payment_date <= "' . $edate . '" ');
            }
            $query->where('fn_fees_collection.transaction_status = "2" ')
                ->where('fn_fees_collection.pupilsightSchoolYearID = "' . $pupilsightSchoolYearID . '" ')
                ->groupBy(['fn_fees_collection.id']);
        } else {
            $query = $this
                ->newQuery()
                ->from('fn_fees_collection')
                ->cols([
                    'fn_fees_collection.*', 'pupilsightPerson.officialName as student_name', 'pupilsightPerson.pupilsightPersonID as stu_id', 'pupilsightPerson.admission_no', 'fn_fees_collection.id as collection_id', 'GROUP_CONCAT(DISTINCT fn_fees_student_cancel_collection.invoice_no) as invoice_no', 'fn_masters.name as paymentmode', 'bnk.name as bankname', 'fn_fees_cancel_collection.remarks AS cancelreason', 'a.officialName as stfName', 'fn_fees_cancel_collection.cdt'
                ])
                ->leftJoin('pupilsightPerson', 'fn_fees_collection.pupilsightPersonID=pupilsightPerson.pupilsightPersonID')
                ->leftJoin('fn_fees_student_cancel_collection', 'fn_fees_collection.transaction_id=fn_fees_student_cancel_collection.transaction_id')
                ->leftJoin('fn_fee_invoice_student_assign', 'fn_fees_student_cancel_collection.invoice_no=fn_fee_invoice_student_assign.invoice_no')
                ->leftJoin('fn_masters', 'fn_fees_collection.payment_mode_id=fn_masters.id')
                ->leftJoin('fn_masters as bnk', 'fn_fees_collection.bank_id=bnk.id')
                ->leftJoin('fn_fees_cancel_collection', 'fn_fees_collection.id=fn_fees_cancel_collection.fn_fees_collection_id')
                ->leftJoin('pupilsightPerson AS a', 'fn_fees_cancel_collection.canceled_by=a.pupilsightPersonID')
                ->where('fn_fees_collection.transaction_status = "2" ')
                ->where('fn_fees_collection.pupilsightSchoolYearID = "' . $pupilsightSchoolYearID . '" ')
                ->groupBy(['fn_fees_collection.id']);
        }
        //echo $query;

        return $this->runQuery($query, $criteria);
    }

    public function getFeesCancelTransactionTotal(QueryCriteria $criteria, $input, $pupilsightSchoolYearID)
    {

        if (!empty($_SESSION['can_trnsaction_search'])) {
            $input = $_SESSION['can_trnsaction_search'];
        }
        if (!empty($input)) {
            $query = $this
                ->newQuery()
                ->from('fn_fees_collection')
                ->cols([
                    'fn_fees_collection.*', 'pupilsightPerson.officialName as student_name', 'pupilsightPerson.pupilsightPersonID as stu_id', 'fn_fees_collection.id as collection_id', 'GROUP_CONCAT(DISTINCT fn_fees_student_cancel_collection.invoice_no) as invoice_no', 'fn_masters.name as paymentmode', 'bnk.name as bankname', 'fn_fees_cancel_collection.remarks AS cancelreason'
                ])
                ->leftJoin('pupilsightPerson', 'fn_fees_collection.pupilsightPersonID=pupilsightPerson.pupilsightPersonID')
                ->leftJoin('pupilsightStudentEnrolment', 'fn_fees_collection.pupilsightPersonID=pupilsightStudentEnrolment.pupilsightPersonID')
                ->leftJoin('fn_fees_student_cancel_collection', 'fn_fees_collection.transaction_id=fn_fees_student_cancel_collection.transaction_id')
                ->leftJoin('fn_fee_invoice_student_assign', 'fn_fees_student_cancel_collection.invoice_no=fn_fee_invoice_student_assign.invoice_no')
                ->leftJoin('fn_masters', 'fn_fees_collection.payment_mode_id=fn_masters.id')
                ->leftJoin('fn_masters as bnk', 'fn_fees_collection.bank_id=bnk.id')
                ->leftJoin('fn_fees_cancel_collection', 'fn_fees_collection.id=fn_fees_cancel_collection.fn_fees_collection_id');
            if (!empty($input['transaction_id'])) {
                $query->where('fn_fees_collection.transaction_id = "' . $input['transaction_id'] . '" ');
            }
            if (!empty($input['admission_no'])) {
                $query->where('pupilsightPerson.admission_no = "' . $input['admission_no'] . '" ');
            }
            if (!empty($input['receipt_number'])) {
                $query->where('fn_fees_collection.receipt_number = "' . $input['receipt_number'] . '" ');
            }
            if (!empty($input['instrument_no'])) {
                $query->where('fn_fees_collection.instrument_no = "' . $input['instrument_no'] . '" ');
            }
            if (!empty($input['bank_id'])) {
                $query->where('fn_fees_collection.bank_id = "' . $input['bank_id'] . '" ');
            }
            if (!empty($input['payment_mode_id'])) {
                $query->where('fn_fees_collection.payment_mode_id = "' . $input['payment_mode_id'] . '" ');
            }
            if (!empty($input['student_name'])) {
                $query->where('pupilsightPerson.officialName LIKE "%' . $input['student_name'] . '%" ');
            }
            if (!empty($input['pupilsightYearGroupID'])) {
                $query->where('pupilsightStudentEnrolment.pupilsightYearGroupID = "' . $input['pupilsightYearGroupID'] . '" ');
            }
            if (!empty($input['pupilsightRollGroupID'])) {
                $query->where('pupilsightStudentEnrolment.pupilsightRollGroupID = "' . $input['pupilsightRollGroupID'] . '" ');
            }
            if (!empty($input['startdate']) && !empty($input['enddate'])) {
                $sd = explode('/', $_POST['startdate']);
                $sdate  = date('Y-m-d', strtotime(implode('-', array_reverse($sd))));
                $ed = explode('/', $_POST['enddate']);
                $edate  = date('Y-m-d', strtotime(implode('-', array_reverse($ed))));

                $query->where('fn_fees_collection.payment_date >= "' . $sdate . '" ')
                    ->where('fn_fees_collection.payment_date <= "' . $edate . '" ');
            }
            $query->where('fn_fees_collection.transaction_status = "2" ')
                ->where('fn_fees_collection.pupilsightSchoolYearID = "' . $pupilsightSchoolYearID . '" ')
                ->groupBy(['fn_fees_collection.id']);
        } else {
            $query = $this
                ->newQuery()
                ->from('fn_fees_collection')
                ->cols([
                    'fn_fees_collection.*', 'pupilsightPerson.officialName as student_name', 'pupilsightPerson.pupilsightPersonID as stu_id', 'fn_fees_collection.id as collection_id', 'GROUP_CONCAT(DISTINCT fn_fees_student_cancel_collection.invoice_no) as invoice_no', 'fn_masters.name as paymentmode', 'bnk.name as bankname', 'fn_fees_cancel_collection.remarks AS cancelreason'
                ])
                ->leftJoin('pupilsightPerson', 'fn_fees_collection.pupilsightPersonID=pupilsightPerson.pupilsightPersonID')
                ->leftJoin('fn_fees_student_cancel_collection', 'fn_fees_collection.transaction_id=fn_fees_student_cancel_collection.transaction_id')
                ->leftJoin('fn_fee_invoice_student_assign', 'fn_fees_student_cancel_collection.invoice_no=fn_fee_invoice_student_assign.invoice_no')
                ->leftJoin('fn_masters', 'fn_fees_collection.payment_mode_id=fn_masters.id')
                ->leftJoin('fn_masters as bnk', 'fn_fees_collection.bank_id=bnk.id')
                ->leftJoin('fn_fees_cancel_collection', 'fn_fees_collection.id=fn_fees_cancel_collection.fn_fees_collection_id')
                ->where('fn_fees_collection.transaction_status = "2" ')
                ->where('fn_fees_collection.pupilsightSchoolYearID = "' . $pupilsightSchoolYearID . '" ')
                ->groupBy(['fn_fees_collection.id']);
        }
        return $query;
    }

    public function getFeesRefundTransactionTotal(QueryCriteria $criteria, $input, $pupilsightSchoolYearID)
    {
        if (!empty($_SESSION['ref_trnsaction_search'])) {
            $input = $_SESSION['ref_trnsaction_search'];
        }
        if (!empty($input)) {
            $query = $this
                ->newQuery()
                ->from('fn_fees_collection')
                ->cols([
                    'pupilsightPerson.officialName as student_name', 'pupilsightPerson.pupilsightPersonID as stu_id', 'fn_fees_collection.id as collection_id', 'fn_fees_collection_refund.*', 'fn_masters.name as paymentmode', 'bnk.name as bankname', 'refby.officialName as refundby', 'GROUP_CONCAT(DISTINCT fn_fees_student_collection.invoice_no) as invoice_no',
                ])
                ->leftJoin('fn_fees_collection_refund', 'fn_fees_collection.id=fn_fees_collection_refund.fn_fees_collection_id')
                ->leftJoin('pupilsightPerson', 'fn_fees_collection_refund.pupilsightPersonID=pupilsightPerson.pupilsightPersonID')
                ->leftJoin('pupilsightStudentEnrolment', 'fn_fees_collection.pupilsightPersonID=pupilsightStudentEnrolment.pupilsightPersonID')
                ->leftJoin('fn_masters', 'fn_fees_collection_refund.payment_mode_id=fn_masters.id')
                ->leftJoin('fn_masters as bnk', 'fn_fees_collection_refund.bank_id=bnk.id')
                ->leftJoin('pupilsightPerson as refby', 'fn_fees_collection_refund.refund_by=refby.pupilsightPersonID')
                ->leftJoin('fn_fees_student_collection', 'fn_fees_collection.transaction_id=fn_fees_student_collection.transaction_id')
                ->leftJoin('fn_fee_invoice_student_assign', 'fn_fees_student_collection.invoice_no=fn_fee_invoice_student_assign.invoice_no');
            if (!empty($input['transaction_id'])) {
                $query->where('fn_fees_collection.transaction_id = "' . $input['transaction_id'] . '" ');
            }
            if (!empty($input['admission_no'])) {
                $query->where('pupilsightPerson.admission_no = "' . $input['admission_no'] . '" ');
            }
            if (!empty($input['receipt_number'])) {
                $query->where('fn_fees_collection_refund.receipt_number = "' . $input['receipt_number'] . '" ');
            }
            if (!empty($input['instrument_no'])) {
                $query->where('fn_fees_collection_refund.instrument_no = "' . $input['instrument_no'] . '" ');
            }
            if (!empty($input['bank_id'])) {
                $query->where('fn_fees_collection_refund.bank_id = "' . $input['bank_id'] . '" ');
            }
            if (!empty($input['student_name'])) {
                $query->where('pupilsightPerson.officialName LIKE "%' . $input['student_name'] . '%" ');
            }
            if (!empty($input['startdate']) && !empty($input['enddate'])) {
                $sd = explode('/', $_POST['startdate']);
                $sdate  = date('Y-m-d', strtotime(implode('-', array_reverse($sd))));
                $ed = explode('/', $_POST['enddate']);
                $edate  = date('Y-m-d', strtotime(implode('-', array_reverse($ed))));

                $query->where('fn_fees_collection_refund.refund_date >= "' . $sdate . '" ')
                    ->where('fn_fees_collection_refund.refund_date <= "' . $edate . '" ');
            }
            $query->where('fn_fees_collection.transaction_status = "3" ')
                ->where('fn_fees_collection.pupilsightSchoolYearID = "' . $pupilsightSchoolYearID . '" ')
                ->groupBy(['fn_fees_collection.id']);
            // echo $query;    
        } else {
            $query = $this
                ->newQuery()
                ->from('fn_fees_collection')
                ->cols([
                    'pupilsightPerson.officialName as student_name', 'pupilsightPerson.pupilsightPersonID as stu_id', 'fn_fees_collection.id as collection_id', 'fn_fees_collection_refund.*', 'fn_masters.name as paymentmode', 'bnk.name as bankname', 'refby.officialName as refundby', 'GROUP_CONCAT(DISTINCT fn_fees_student_collection.invoice_no) as invoice_no',
                ])
                ->leftJoin('fn_fees_collection_refund', 'fn_fees_collection.id=fn_fees_collection_refund.fn_fees_collection_id')
                ->leftJoin('pupilsightPerson', 'fn_fees_collection_refund.pupilsightPersonID=pupilsightPerson.pupilsightPersonID')
                ->leftJoin('pupilsightStudentEnrolment', 'fn_fees_collection.pupilsightPersonID=pupilsightStudentEnrolment.pupilsightPersonID')
                ->leftJoin('fn_masters', 'fn_fees_collection_refund.payment_mode_id=fn_masters.id')
                ->leftJoin('fn_masters as bnk', 'fn_fees_collection_refund.bank_id=bnk.id')
                ->leftJoin('pupilsightPerson as refby', 'fn_fees_collection_refund.refund_by=refby.pupilsightPersonID')
                ->leftJoin('fn_fees_student_collection', 'fn_fees_collection.transaction_id=fn_fees_student_collection.transaction_id')
                ->leftJoin('fn_fee_invoice_student_assign', 'fn_fees_student_collection.invoice_no=fn_fee_invoice_student_assign.invoice_no')
                ->where('fn_fees_collection.transaction_status = "3" ')
                ->where('fn_fees_collection.pupilsightSchoolYearID = "' . $pupilsightSchoolYearID . '" ')
                ->groupBy(['fn_fees_collection.id']);
        }


        return $query;
    }
    public function getFeesRefundTransaction(QueryCriteria $criteria, $input, $pupilsightSchoolYearID)
    {
        if (!empty($_SESSION['ref_trnsaction_search'])) {
            $input = $_SESSION['ref_trnsaction_search'];
        }
        if (!empty($input)) {
            $query = $this
                ->newQuery()
                ->from('fn_fees_collection')
                ->cols([
                    'pupilsightPerson.officialName as student_name', 'pupilsightPerson.pupilsightPersonID as stu_id', 'pupilsightPerson.admission_no', 'fn_fees_collection.id as collection_id', 'fn_fees_collection.amount_paying as transaction_amount', 'fn_fees_collection.transcation_amount as transaction_pending_amount', 'fn_fees_collection_refund.*', 'fn_masters.name as paymentmode', 'bnk.name as bankname', 'refby.officialName as refundby', 'GROUP_CONCAT(DISTINCT fn_fees_student_collection.invoice_no) as invoice_no',
                ])
                ->leftJoin('fn_fees_collection_refund', 'fn_fees_collection.id=fn_fees_collection_refund.fn_fees_collection_id')
                ->leftJoin('pupilsightPerson', 'fn_fees_collection_refund.pupilsightPersonID=pupilsightPerson.pupilsightPersonID')
                ->leftJoin('pupilsightStudentEnrolment', 'fn_fees_collection.pupilsightPersonID=pupilsightStudentEnrolment.pupilsightPersonID')
                ->leftJoin('fn_masters', 'fn_fees_collection_refund.payment_mode_id=fn_masters.id')
                ->leftJoin('fn_masters as bnk', 'fn_fees_collection_refund.bank_id=bnk.id')
                ->leftJoin('pupilsightPerson as refby', 'fn_fees_collection_refund.refund_by=refby.pupilsightPersonID')
                ->leftJoin('fn_fees_student_collection', 'fn_fees_collection.transaction_id=fn_fees_student_collection.transaction_id')
                ->leftJoin('fn_fee_invoice_student_assign', 'fn_fees_student_collection.invoice_no=fn_fee_invoice_student_assign.invoice_no');
            if (!empty($input['transaction_id'])) {
                $query->where('fn_fees_collection.transaction_id = "' . $input['transaction_id'] . '" ');
            }
            if (!empty($input['admission_no'])) {
                $query->where('pupilsightPerson.admission_no = "' . $input['admission_no'] . '" ');
            }
            if (!empty($input['receipt_number'])) {
                $query->where('fn_fees_collection_refund.receipt_number = "' . $input['receipt_number'] . '" ');
            }
            if (!empty($input['instrument_no'])) {
                $query->where('fn_fees_collection_refund.instrument_no = "' . $input['instrument_no'] . '" ');
            }
            if (!empty($input['bank_id'])) {
                $query->where('fn_fees_collection_refund.bank_id = "' . $input['bank_id'] . '" ');
            }
            if (!empty($input['student_name'])) {
                $query->where('pupilsightPerson.officialName LIKE "%' . $input['student_name'] . '%" ');
            }
            if (!empty($input['startdate']) && !empty($input['enddate'])) {
                $sd = explode('/', $_POST['startdate']);
                $sdate  = date('Y-m-d', strtotime(implode('-', array_reverse($sd))));
                $ed = explode('/', $_POST['enddate']);
                $edate  = date('Y-m-d', strtotime(implode('-', array_reverse($ed))));

                $query->where('fn_fees_collection_refund.refund_date >= "' . $sdate . '" ')
                    ->where('fn_fees_collection_refund.refund_date <= "' . $edate . '" ');
            }
            $query->where('fn_fees_collection.transaction_status = "3" ')
                ->where('fn_fees_collection.pupilsightSchoolYearID = "' . $pupilsightSchoolYearID . '" ')
                ->groupBy(['fn_fees_collection.id']);
            // echo $query;    
        } else {
            $query = $this
                ->newQuery()
                ->from('fn_fees_collection')
                ->cols([
                    'pupilsightPerson.officialName as student_name', 'pupilsightPerson.pupilsightPersonID as stu_id', 'pupilsightPerson.admission_no', 'fn_fees_collection.id as collection_id',  'fn_fees_collection.amount_paying as transaction_amount', 'fn_fees_collection.transcation_amount as transaction_pending_amount', 'fn_fees_collection_refund.*', 'fn_masters.name as paymentmode', 'bnk.name as bankname', 'refby.officialName as refundby', 'GROUP_CONCAT(DISTINCT fn_fees_student_collection.invoice_no) as invoice_no',
                ])
                ->leftJoin('fn_fees_collection_refund', 'fn_fees_collection.id=fn_fees_collection_refund.fn_fees_collection_id')
                ->leftJoin('pupilsightPerson', 'fn_fees_collection_refund.pupilsightPersonID=pupilsightPerson.pupilsightPersonID')
                ->leftJoin('pupilsightStudentEnrolment', 'fn_fees_collection.pupilsightPersonID=pupilsightStudentEnrolment.pupilsightPersonID')
                ->leftJoin('fn_masters', 'fn_fees_collection_refund.payment_mode_id=fn_masters.id')
                ->leftJoin('fn_masters as bnk', 'fn_fees_collection_refund.bank_id=bnk.id')
                ->leftJoin('pupilsightPerson as refby', 'fn_fees_collection_refund.refund_by=refby.pupilsightPersonID')
                ->leftJoin('fn_fees_student_collection', 'fn_fees_collection.transaction_id=fn_fees_student_collection.transaction_id')
                ->leftJoin('fn_fee_invoice_student_assign', 'fn_fees_student_collection.invoice_no=fn_fee_invoice_student_assign.invoice_no')
                ->where('fn_fees_collection.transaction_status = "3" ')
                ->where('fn_fees_collection.pupilsightSchoolYearID = "' . $pupilsightSchoolYearID . '" ')
                ->groupBy(['fn_fees_collection.id']);
        }


        return $this->runQuery($query, $criteria);
    }

    public function getCollectionInvoiceRefund(QueryCriteria $criteria, $transids)
    {
        //echo $transids;
        $query = $this
            ->newQuery()
            ->from('fn_fee_invoice')
            ->cols([
                'fn_fee_invoice.*', 'fn_fee_invoice.id as invoiceid', "SUM(fn_fee_invoice_item.total_amount) as totalamount", "SUM(fn_fee_invoice_item.total_amount) as invoiceamount", "SUM(fn_fee_invoice_item.amount) as pendingamount", 'fn_fees_student_collection.invoice_no', 'fn_fees_collection.fine', 'fn_fees_collection.discount', 'fn_fees_collection.payment_status'
            ])
            ->leftJoin('fn_fees_student_collection', 'fn_fee_invoice.id=fn_fees_student_collection.fn_fees_invoice_id')
            ->leftJoin('fn_fee_invoice_item', 'fn_fees_student_collection.fn_fee_invoice_item_id=fn_fee_invoice_item.id')
            ->leftJoin('fn_fees_collection', 'fn_fees_student_collection.transaction_id=fn_fees_collection.transaction_id')
            ->where('fn_fees_collection.id IN ("' . $transids . '") ')
            ->where('fn_fees_collection.transaction_status = "1" ')
            ->groupBy(['fn_fee_invoice.id']);
        return $this->runQuery($query, $criteria);
    }


    // public function getStudentData(QueryCriteria $criteria, $pupilsightProgramID, $pupilsightSchoolYearIDpost, $pupilsightYearGroupID, $pupilsightRollGroupID, $search)
    // {

    //     if(!empty($pupilsightProgramID) || !empty($pupilsightSchoolYearIDpost) || !empty($pupilsightYearGroupID) || !empty($pupilsightRollGroupID) || !empty($search) ) {

    //     $query = $this
    //         ->newQuery()
    //         ->from('fn_fee_invoice')
    //         ->cols([
    //             'fn_fee_invoice.*','fn_fee_invoice.id as invoiceid','fn_fee_series.format','fn_fee_series.start_number','fn_fee_series.no_of_digit','fn_fee_series.start_char','fn_fees_head.name AS account_head',"SUM(fn_fee_invoice_item.amount) as totalamount", "SUM(fn_fee_invoice_item.amount) as pendingamount",'fn_fee_invoice_student_assign.invoice_no as stu_invoice_no'
    //         ])
    //         ->leftJoin('pupilsightStudentEnrolment', 'fn_fee_invoice.pupilsightSchoolYearID=pupilsightStudentEnrolment.pupilsightSchoolYearID')
    //         ->leftJoin('pupilsightPerson', 'pupilsightStudentEnrolment.pupilsightPersonID=pupilsightPerson.pupilsightPersonID')
    //         ->leftJoin('fn_fee_invoice_item', 'fn_fee_invoice.id=fn_fee_invoice_item.fn_fee_invoice_id')
    //         ->leftJoin('fn_fee_series', 'fn_fee_invoice.inv_fn_fee_series_id=fn_fee_series.id')
    //         ->leftJoin('fn_fees_head', 'fn_fee_invoice.fn_fees_head_id=fn_fees_head.id')
    //         ->leftJoin('fn_fee_invoice_student_assign', 'pupilsightPerson.pupilsightPersonID=fn_fee_invoice_student_assign.pupilsightPersonID')
    //         ->where('pupilsightStudentEnrolment.pupilsightSchoolYearID = "'.$pupilsightSchoolYearIDpost.'" ')
    //         ->where('pupilsightStudentEnrolment.pupilsightProgramID = "'.$pupilsightProgramID.'" ')
    //         ->where('pupilsightStudentEnrolment.pupilsightYearGroupID = "'.$pupilsightYearGroupID.'" ')
    //         ->where('pupilsightStudentEnrolment.pupilsightRollGroupID = "'.$pupilsightRollGroupID.'" ')
    //         ->where('pupilsightPerson.officialName LIKE "%'.$search.'%" ')
    //         ->groupBy(['fn_fee_invoice.id']);
    //     } else {
    //        $query = $this
    //         ->newQuery()
    //         ->from('fn_fee_invoice')
    //         ->cols([
    //             'fn_fee_invoice.*','fn_fee_invoice.id as invoiceid','fn_fee_series.format','fn_fee_series.start_number','fn_fee_series.no_of_digit','fn_fee_series.start_char','fn_fees_head.name AS account_head',"SUM(fn_fee_invoice_item.amount) as totalamount", "SUM(fn_fee_invoice_item.amount) as pendingamount"
    //         ])
    //         ->leftJoin('fn_fee_invoice_item', 'fn_fee_invoice.id=fn_fee_invoice_item.fn_fee_invoice_id')
    //         ->leftJoin('fn_fee_series', 'fn_fee_invoice.inv_fn_fee_series_id=fn_fee_series.id')
    //         ->leftJoin('fn_fees_head', 'fn_fee_invoice.fn_fees_head_id=fn_fees_head.id')
    //         ->groupBy(['fn_fee_invoice.id']);
    //     }
    //    return $this->runQuery($query, $criteria);
    // }

    public function getChildInvoice(QueryCriteria $criteria)
    {
        $query = $this
            ->newQuery()
            ->from('fn_fee_invoice')
            ->cols([
                'fn_fee_invoice.*', 'fn_fee_series.series_name', 'fn_fees_head.name AS account_head'
            ])
            // ->leftJoin('pupilsightSchoolYear', 'fn_fee_structure.pupilsightSchoolYearID=pupilsightSchoolYear.pupilsightSchoolYearID')
            ->leftJoin('fn_fee_series', 'fn_fee_invoice.inv_fn_fee_series_id=fn_fee_series.id')
            ->leftJoin('fn_fees_head', 'fn_fee_invoice.fn_fees_head_id=fn_fees_head.id');


        return $this->runQuery($query, $criteria);
    }

    public function getReceiptTemplate(QueryCriteria $criteria)
    {
        $query = $this
            ->newQuery()
            ->from('fn_fees_receipt_template_master')
            ->cols([
                'id', 'name', 'path', 'filename', 'type'
            ])
            ->orderBy(['id DESC']);;

        return $this->runQuery($query, $criteria, true);
    }

    public function getFeesCategory(QueryCriteria $criteria)
    {
        $db = new DBQuery();
        $rs = $db->select_serial("select id, name from fee_category");
        return $rs;
    }

    public function getInvoiceforDiscount(QueryCriteria $criteria, $input, $pupilsightSchoolYearID)
    {


        if (!empty($_SESSION['invoice_discount_search'])) {
            $input = $_SESSION['invoice_discount_search'];
        }

        if (!empty($input['fn_fee_invoice_id']) || !empty($input['pupilsightProgramID']) || !empty($input['pupilsightRollGroupID']) || !empty($input['pupilsightYearGroupID'])) {

            $query = $this
                ->newQuery()
                ->from('fn_fee_invoice')
                ->cols([
                    'fn_fee_invoice.*', 'fn_fee_series.series_name', 'fn_fees_head.name AS account_head', 'pupilsightProgram.name AS program', 'pupilsightYearGroup.name as class', 'pupilsightPerson.pupilsightPersonID as std_id', 'pupilsightPerson.officialName as std_name', 'pupilsightPerson.admission_no', 'pupilsightRollGroup.name as section', 'fn_fee_invoice_student_assign.invoice_no', 'fn_fee_invoice_student_assign.id as invid', 'fn_fee_invoice_student_assign.invoice_status as invstatus', 'fn_fee_invoice_student_assign.status as chkstatus', 'fn_fee_invoice_student_assign.id as insid'
                ])
                ->leftJoin('fn_fee_invoice_student_assign', ' fn_fee_invoice.id=fn_fee_invoice_student_assign.fn_fee_invoice_id')

                ->leftJoin('fn_fee_invoice_class_assign', ' fn_fee_invoice.id=fn_fee_invoice_class_assign.fn_fee_invoice_id')
                ->leftJoin('pupilsightProgram', ' fn_fee_invoice_class_assign.pupilsightProgramID=pupilsightProgram.pupilsightProgramID')

                ->leftJoin('pupilsightPerson', ' fn_fee_invoice_student_assign.pupilsightPersonID=pupilsightPerson.pupilsightPersonID')

                ->leftJoin('pupilsightYearGroup', ' fn_fee_invoice_class_assign.pupilsightYearGroupID=pupilsightYearGroup.pupilsightYearGroupID')

                ->leftJoin('pupilsightProgramClassSectionMapping', ' fn_fee_invoice_class_assign.pupilsightYearGroupID=pupilsightProgramClassSectionMapping.pupilsightYearGroupID')
                ->leftJoin('pupilsightRollGroup', ' pupilsightProgramClassSectionMapping.pupilsightRollGroupID=pupilsightRollGroup.pupilsightRollGroupID')
                ->leftJoin('pupilsightStudentEnrolment', 'fn_fee_invoice_student_assign.pupilsightPersonID=pupilsightStudentEnrolment.pupilsightPersonID')
                ->leftJoin('fn_fee_series', 'fn_fee_invoice.inv_fn_fee_series_id=fn_fee_series.id')
                ->leftJoin('fn_fees_head', 'fn_fee_invoice.fn_fees_head_id=fn_fees_head.id');

            if (!empty($input['pupilsightProgramID'])) {
                $query->where('fn_fee_invoice_class_assign.pupilsightProgramID = "' . $input['pupilsightProgramID'] . '" ');
            }

            if (!empty($input['pupilsightYearGroupID'])) {
                $query->where('fn_fee_invoice_class_assign.pupilsightYearGroupID = "' . $input['pupilsightYearGroupID'] . '" ');
            }
            if (!empty($input['pupilsightRollGroupID'])) {
                $query->where('pupilsightStudentEnrolment.pupilsightRollGroupID IN (' . implode(',', $input['pupilsightRollGroupID']) . ') ');
            }

            // if(!empty($input['admission_no'])){
            //     $query->where('pupilsightPerson.admission_no = "'.$input['admission_no'].'" ');
            // }

            // if(!empty($input['student_name'])){
            //     $query->where('pupilsightPerson.officialName LIKE "%'.$input['student_name'].'%" ');
            // }
            // if(!empty($input['due_date'])){
            //     $sd = explode('/', $input['due_date']);
            //     $sdate  = date('Y-m-d', strtotime(implode('-', array_reverse($sd))));

            //     $query->where('fn_fee_invoice.due_date = "'.$sdate.'" ');
            // }
            // if(!empty($input['invoice_title'])){
            //     $query->where('fn_fee_invoice.title = "'.$input['invoice_title'].'" ');
            //     }
            // if(!empty($input['invoice_no'])){
            //     $query->where('fn_fee_invoice_student_assign.invoice_no LIKE "%'.$input['invoice_no'].'%" ');
            // }
            // if(!empty($input['invoice_date'])){
            //     $fd = explode('/', $input['invoice_date']);
            //     $fdate  = date('Y-m-d', strtotime(implode('-', array_reverse($fd))));
            //     $query->where('fn_fee_invoice.cdt = "'.$fdate.'" ');
            // }
            // if(!empty($input['invoice_status'])){
            //     $query->where('fn_fee_invoice_student_assign.invoice_status = "'.$input['invoice_status'].'" ');
            // } 
            // echo $query;
            if (!empty($input['fn_fee_invoice_id'])) {
                $query->where('fn_fee_invoice.title = "' . $input['fn_fee_invoice_id'] . '" ');
            }
            // $query->where('fn_fee_invoice_student_assign.fn_fee_structure_id = "'.$input['fn_fee_invoice_id'].'" ')
            $query->where('fn_fee_invoice_student_assign.invoice_status = "Not Paid" ')
                ->where('pupilsightPerson.pupilsightRoleIDPrimary = 003')
                //->where('fn_fee_invoice_student_assign.status = 1 ')
                ->groupby(['fn_fee_invoice_student_assign.id'])
                ->orderby(['fn_fee_invoice_student_assign.id DESC']);
            //echo $query;
            // die();
        } else {
            $query = $this
                ->newQuery()
                ->from('fn_fee_invoice')
                ->cols([
                    'fn_fee_invoice.*', 'fn_fee_series.series_name', 'fn_fees_head.name AS account_head', 'pupilsightProgram.name AS program', 'pupilsightYearGroup.name as class', 'pupilsightPerson.pupilsightPersonID as std_id', 'pupilsightPerson.officialName as std_name', 'pupilsightPerson.admission_no', 'pupilsightRollGroup.name as section', 'fn_fee_invoice_student_assign.invoice_no', 'fn_fee_invoice_student_assign.id as invid', 'fn_fee_invoice_student_assign.invoice_status as invstatus', 'fn_fee_invoice_student_assign.status as chkstatus', 'fn_fee_invoice_student_assign.id as insid'
                ])
                // ->leftJoin('pupilsightSchoolYear', 'fn_fee_structure.pupilsightSchoolYearID=pupilsightSchoolYear.pupilsightSchoolYearID')
                ->leftJoin('fn_fee_series', 'fn_fee_invoice.inv_fn_fee_series_id=fn_fee_series.id')
                ->leftJoin('fn_fee_invoice_class_assign', ' fn_fee_invoice.id=fn_fee_invoice_class_assign.fn_fee_invoice_id')
                ->leftJoin('fn_fee_invoice_student_assign', ' fn_fee_invoice.id=fn_fee_invoice_student_assign.fn_fee_invoice_id')
                ->leftJoin('pupilsightProgram', ' fn_fee_invoice_class_assign.pupilsightProgramID=pupilsightProgram.pupilsightProgramID')
                ->leftJoin('pupilsightPerson', ' fn_fee_invoice_student_assign.pupilsightPersonID=pupilsightPerson.pupilsightPersonID')

                ->leftJoin('pupilsightYearGroup', ' fn_fee_invoice_class_assign.pupilsightYearGroupID=pupilsightYearGroup.pupilsightYearGroupID')

                ->leftJoin('pupilsightProgramClassSectionMapping', ' fn_fee_invoice_class_assign.pupilsightYearGroupID=pupilsightProgramClassSectionMapping.pupilsightYearGroupID')
                ->leftJoin('pupilsightRollGroup', ' pupilsightProgramClassSectionMapping.pupilsightRollGroupID=pupilsightRollGroup.pupilsightRollGroupID')
                ->leftJoin('fn_fees_head', 'fn_fee_invoice.fn_fees_head_id=fn_fees_head.id')
                ->where('fn_fee_invoice.pupilsightSchoolYearID  = "' . $pupilsightSchoolYearID . '" AND fn_fee_invoice.status="1" ')
                //->where('fn_fee_invoice_student_assign.status = 1 ')
                ->where('pupilsightPerson.pupilsightRoleIDPrimary = 003')
                ->groupby(['fn_fee_invoice_student_assign.id'])
                ->orderby(['fn_fee_invoice_student_assign.id DESC']);
            //echo $query;
        }


        //return $this->runQuery($query, $criteria, true);
        $res = $this->runQuery($query, $criteria, true);
        $data = $res->data;
        // echo '<pre>';
        // print_r($data);
        // echo '</pre>';
        // die();
        if (!empty($data)) {
            foreach ($data as $k => $d) {
                $invid = $d['id'];
                $query2 = $this
                    ->newQuery()
                    ->from('fn_fees_student_collection')
                    ->cols([
                        'SUM(fn_fees_student_collection.total_amount_collection) as paid_amount'
                    ])
                    ->where('fn_fees_student_collection.fn_fees_invoice_id = "' . $invid . '" ')
                    ->where('fn_fees_student_collection.pupilsightPersonID = "' . $d['std_id'] . '" ');

                $newdata = $this->runQuery($query2, $criteria);
                if (!empty($newdata->data[0]['paid_amount'])) {
                    $data[$k]['chkinvstatus'] = 'paid';
                    if (!empty($newdata->data[0]['paid_amount'])) {
                        $data[$k]['paid'] = $newdata->data[0]['paid_amount'];
                    } else {
                        $data[$k]['paid'] = '';
                    }
                } else {
                    $data[$k]['chkinvstatus'] = '';
                    $data[$k]['paid'] = '';
                }

                $query3 = $this
                    ->newQuery()
                    ->from('fn_fee_invoice_item')
                    ->cols([
                        'SUM(fn_fee_invoice_item.total_amount) as tot_amount', 'GROUP_CONCAT(fn_fee_invoice_item.id) AS itemid'
                    ])
                    ->where('fn_fee_invoice_item.fn_fee_invoice_id = "' . $invid . '" ');

                $newdata1 = $this->runQuery($query3, $criteria);
                if (!empty($newdata1->data[0]['tot_amount'])) {
                    if (!empty($newdata1->data[0]['itemid'])) {
                        $query3 = $this
                            ->newQuery()
                            ->from('fn_fee_item_level_discount')
                            ->cols([
                                'SUM(fn_fee_item_level_discount.discount) as tot_discount'
                            ])
                            ->where('fn_fee_item_level_discount.item_id IN (' . $newdata1->data[0]['itemid'] . ') ')
                            ->where('fn_fee_item_level_discount.pupilsightPersonID = "' . $d['std_id'] . '" ');

                        $newdata2 = $this->runQuery($query3, $criteria);
                        if (!empty($newdata2->data[0]['tot_discount'])) {
                            $amt = $newdata1->data[0]['tot_amount'] - $newdata2->data[0]['tot_discount'];
                            $data[$k]['inv_amount'] = $amt;
                            $data[$k]['tot_discount'] = $newdata2->data[0]['tot_discount'];
                        } else {
                            $data[$k]['inv_amount'] = $newdata1->data[0]['tot_amount'];
                            $data[$k]['tot_discount'] = '';
                        }
                    } else {
                        $data[$k]['inv_amount'] = '';
                        $data[$k]['tot_discount'] = '';
                    }
                } else {
                    $data[$k]['inv_amount'] = '';
                    $data[$k]['tot_discount'] = '';
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

    public function getDepositAccountDetails($criteria, $id, $pupilsightPersonID)
    {
        $query = $this
            ->newQuery()
            ->from('fn_fees_deposit_account')
            ->cols([
                'fn_fees_deposit_account.id', 'fn_fees_deposit_account.ac_name', 'fn_fees_collection_deposit.pupilsightPersonID', 'fn_fees_collection_deposit.amount', 'fn_fees_collection_deposit.transaction_id', 'fn_fees_collection_deposit.status', 'fn_fees_collection_deposit.cdt', 'fn_fees_collection.receipt_number', 'fn_fees_collection.payment_mode_id', 'fn_fees_student_collection.invoice_no', 'pupilsightPerson.officialName', 'fn_masters.name as paymentMode'
            ])
            ->leftJoin('fn_fees_collection_deposit', 'fn_fees_deposit_account.id=fn_fees_collection_deposit.deposit_account_id')
            ->leftJoin('fn_fees_collection', 'fn_fees_collection_deposit.transaction_id=fn_fees_collection.transaction_id')
            ->leftJoin('fn_fees_student_collection', 'fn_fees_collection_deposit.transaction_id=fn_fees_student_collection.transaction_id')
            ->leftJoin('fn_masters', 'fn_fees_collection.payment_mode_id=fn_masters.id')
            ->leftJoin('pupilsightPerson', 'fn_fees_collection_deposit.pupilsightPersonID=pupilsightPerson.pupilsightPersonID')
            ->where('fn_fees_collection_deposit.pupilsightPersonID = "' . $pupilsightPersonID . '" ')
            ->where('fn_fees_deposit_account.id = "' . $id . '" ')
            ->groupBy(['fn_fees_collection_deposit.id']);

        return $this->runQuery($query, $criteria, TRUE);
    }

    public function getDepositAccountForStudent(QueryCriteria $criteria, $pupilsightPersonID)
    {
        $query = $this
            ->newQuery()
            ->from('fn_fees_deposit_account')
            ->cols([
                'fn_fees_deposit_account.id', 'fn_fees_deposit_account.ac_name', 'fn_fees_deposit_account.ac_code', 'fn_fees_deposit_account.overpayment_account', 'fn_fee_items.name as fee_item', 'SUM(if(fn_fees_collection_deposit.status = "Credit",fn_fees_collection_deposit.amount,0) ) AS creditData', 'fn_fees_collection_deposit.pupilsightPersonID'
            ])
            ->leftJoin('fn_fee_items', 'fn_fees_deposit_account.fn_fee_item_id=fn_fee_items.id')
            ->leftJoin('fn_fees_collection_deposit', 'fn_fees_deposit_account.id=fn_fees_collection_deposit.deposit_account_id')
            ->where('fn_fees_collection_deposit.pupilsightPersonID = "' . $pupilsightPersonID . '" ')
            ->groupBy(['fn_fees_deposit_account.id']);

        //return $this->runQuery($query, $criteria, TRUE);
        //echo $query;

        $res = $this->runQuery($query, $criteria, TRUE);
        $data = $res->data;
        // echo '<pre>';
        // print_r($data);
        // echo '</pre>';
        // die();
        if (!empty($data)) {
            foreach ($data as $k => $d) {
                $deposit_account_id = $d['id'];
                $creditdata = $d['creditData'];
                $query2 = $this
                    ->newQuery()
                    ->from('fn_fees_collection_deposit')
                    ->cols([
                        'SUM(fn_fees_collection_deposit.amount) AS debitData'
                    ])
                    ->where('fn_fees_collection_deposit.deposit_account_id = "' . $deposit_account_id . '" ')
                    ->where('fn_fees_collection_deposit.pupilsightPersonID = "' . $pupilsightPersonID . '" ')
                    ->where('fn_fees_collection_deposit.status = "Debit" ');

                $newdata = $this->runQuery($query2, $criteria);
                if (!empty($newdata->data[0]['debitData'])) {
                    $debitdata = $newdata->data[0]['debitData'];
                    $depAmount = $creditdata - $debitdata;
                    $data[$k]['amount'] = $depAmount;
                } else {
                    $depAmount = $creditdata;
                    $data[$k]['amount'] = $depAmount;
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


    public function getWaiveOffForStudent($criteria, $pupilsightPersonID)
    {
        $query = $this
            ->newQuery()
            ->from('fn_fee_waive_off')
            ->cols([
                'fn_fee_waive_off.*', 'pupilsightPerson.officialName'
            ])
            ->leftJoin('pupilsightPerson', 'fn_fee_waive_off.assigned_by=pupilsightPerson.pupilsightPersonID')
            ->where('fn_fee_waive_off.pupilsightPersonID = "' . $pupilsightPersonID . '" ');

        return $this->runQuery($query, $criteria, TRUE);
    }

    public function getAllPaymentDetails(QueryCriteria $criteria, $pupilsightSchoolYearID)
    {
        $query = $this
            ->newQuery()
            ->from('fn_fee_payment_details')
            ->cols([
                'fn_fee_payment_details.*', 'pupilsightPerson.officialName', 'pupilsightYearGroup.name as class', 'pupilsightRollGroup.name as section'
            ])
            ->leftJoin('pupilsightPerson', 'fn_fee_payment_details.pupilsightPersonID=pupilsightPerson.pupilsightPersonID')
            ->leftJoin('pupilsightStudentEnrolment', 'pupilsightPerson.pupilsightPersonID=pupilsightStudentEnrolment.pupilsightPersonID')
            ->leftJoin('pupilsightYearGroup', 'pupilsightStudentEnrolment.pupilsightYearGroupID=pupilsightYearGroup.pupilsightYearGroupID')
            ->leftJoin('pupilsightRollGroup', 'pupilsightStudentEnrolment.pupilsightRollGroupID=pupilsightRollGroup.pupilsightRollGroupID')
            ->where('pupilsightStudentEnrolment.pupilsightSchoolYearID = "' . $pupilsightSchoolYearID . '" ')
            ->orderBy(['fn_fee_payment_details.id DESC']);

        return $this->runQuery($query, $criteria, TRUE);
    }
}
