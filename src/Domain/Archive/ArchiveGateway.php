<?php
/*
Pupilsight, Flexible & Open School System
*/

namespace Pupilsight\Domain\Archive;

use Pupilsight\Domain\Traits\TableAware;
use Pupilsight\Domain\QueryCriteria;
use Pupilsight\Domain\QueryableGateway;



/**
 * Archive Gateway
 *
 * @version v17
 * @since   v17
 */
class ArchiveGateway extends QueryableGateway
{
    use TableAware;

    private static $tableName = 'archive_feeInvoices';

    public function listFeeInvoiceTerm($con){
        $sq = "SELECT DISTINCT Term FROM archive_feeInvoices where  Term <> '' order by Term ";
        $result = $con->query($sq);
        return $result->fetchAll();
    }

    public function listFeeInvoiceAcademicYear($con){
        $sq = "SELECT DISTINCT AcademicYear FROM archive_feeInvoices where  AcademicYear <> '' order by AcademicYear ";
        $result = $con->query($sq);
        return $result->fetchAll();
    }

    public function listFeeInvoiceStream($con){
        $sq = "SELECT DISTINCT Stream FROM archive_feeInvoices where  Stream <> '' order by Stream ";
        $result = $con->query($sq);
        return $result->fetchAll();
    }

    public function listFeeTransTerm($con){
        $sq = "SELECT DISTINCT Term FROM archive_fee_transactions_backup where  Term <> '' order by Term ";
        $result = $con->query($sq);
        return $result->fetchAll();
    }

    public function listFeeTransAcademicYear($con){
        $sq = "SELECT DISTINCT AcademicYear FROM archive_fee_transactions_backup where  AcademicYear <> '' order by AcademicYear ";
        $result = $con->query($sq);
        return $result->fetchAll();
    }

    public function listFeeTransStream($con){
        $sq = "SELECT DISTINCT Stream FROM archive_fee_transactions_backup where  Stream <> '' order by Stream ";
        $result = $con->query($sq);
        return $result->fetchAll();
    }

    

    public function createOption($list, $key){
        $len = count($list);
        $i = 0;
        $str = "\n<option value=''>Select</option>";
        while($i<$len){
            $str .="\n<option value='".$list[$i][$key]."'>".$list[$i][$key]."</option>";
            $i++;
        }
        return $str;
    }

}