<?php
/*
Pupilsight, Flexible & Open School System
*/

//$mode can be blank or "disabled". $archive is a serialized array of values previously archived
function printINStatusTable($connection2, $guid, $pupilsightPersonID, $mode = '', $archive = '')
{
    $output = false;

    try {
        $dataDescriptors = array();
        $sqlDescriptors = 'SELECT * FROM pupilsightINDescriptor ORDER BY sequenceNumber, nameShort';
        $resultDescriptors = $connection2->prepare($sqlDescriptors);
        $resultDescriptors->execute($dataDescriptors);
    } catch (PDOException $e) {
        $output .= "<div class='alert alert-danger'>".$e->getMessage().'</div>';
    }

    try {
        $dataSeverity = array();
        $sqlSeverity = 'SELECT * FROM pupilsightAlertLevel ORDER BY sequenceNumber, nameShort';
        $resultSeverity = $connection2->prepare($sqlSeverity);
        $resultSeverity->execute($dataSeverity);
    } catch (PDOException $e) {
        $output .= "<div class='alert alert-danger'>".$e->getMessage().'</div>';
    }

    if ($resultDescriptors->rowCount() < 1 or $resultSeverity->rowCount() < 1) {
        $output .= "<div class='alert alert-danger'>";
        $output .= __('Individual needs descriptors or severity levels have not been set.');
        $output .= '</div>';
    } else {
        $descriptors = array();
        $count = 0;
        while ($rowDescriptors = $resultDescriptors->fetch()) {
            $descriptors[$count][0] = $rowDescriptors['pupilsightINDescriptorID'];
            $descriptors[$count][1] = $rowDescriptors['name'];
            $descriptors[$count][2] = $rowDescriptors['nameShort'];
            $descriptors[$count][3] = $rowDescriptors['description'];
            ++$count;
        }

        $severity = array();
        $count = 0;
        while ($rowSeverity = $resultSeverity->fetch()) {
            $severity[$count][0] = $rowSeverity['pupilsightAlertLevelID'];
            $severity[$count][1] = __($rowSeverity['name']);
            $severity[$count][2] = $rowSeverity['nameShort'];
            $severity[$count][3] = __($rowSeverity['description']);
            $severity[$count][4] = $rowSeverity['color'];
            ++$count;
        }

        $personDescriptors = array();
        $count = 0;
        if ($archive == '') { //Not an archive, get live data
            try {
                $dataPersonDescriptors = array('pupilsightPersonID' => $pupilsightPersonID);
                $sqlPersonDescriptors = 'SELECT * FROM pupilsightINPersonDescriptor WHERE pupilsightPersonID=:pupilsightPersonID';
                $resultPersonDescriptors = $connection2->prepare($sqlPersonDescriptors);
                $resultPersonDescriptors->execute($dataPersonDescriptors);
            } catch (PDOException $e) {
                $output .= "<div class='alert alert-danger'>".$e->getMessage().'</div>';
            }
            while ($rowPersonDescriptors = $resultPersonDescriptors->fetch()) {
                $personDescriptors[$count][0] = $rowPersonDescriptors['pupilsightINDescriptorID'];
                $personDescriptors[$count][1] = $rowPersonDescriptors['pupilsightAlertLevelID'];
                ++$count;
            }
        } else { //It is an archive, so populate array
            $archive = unserialize($archive);
            if (count($archive) > 0) {
                foreach ($archive as $archiveEntry) {
                    $personDescriptors[$count][0] = $archiveEntry['pupilsightINDescriptorID'];
                    $personDescriptors[$count][1] = $archiveEntry['pupilsightAlertLevelID'];
                    ++$count;
                }
            }
        }

        //Print IN Status table
        $output .= "<table class='table'>";
        $output .= "<tr class='head'>";
        $output .= '<th>';
        $output .= __('Descriptor');
        $output .= '<th>';
        for ($i = 0; $i < count($severity); ++$i) {
            $output .= '<th>';
            $output .= "<span title='".$severity[$i][3]."'>".$severity[$i][1].'</span>';
            $output .= '<th>';
        }
        $output .= '</tr>';
        for ($n = 0; $n < count($descriptors); ++$n) {
            if ($n % 2 == 0) {
                $rowNum = 'even';
            } else {
                $rowNum = 'odd';
            }

            $output .= "<tr class=$rowNum>";
            $output .= '<td>';
            $output .= "<span title='".__($descriptors[$n][3])."'>".__($descriptors[$n][1]).'</span>';
            $output .= '<td>';
            for ($i = 0; $i < count($severity); ++$i) {
                $output .= "<td style='width: 10%'>";
                $checked = '';
                for ($j = 0; $j < count($personDescriptors); ++$j) {
                    if ($personDescriptors[$j][0] == $descriptors[$n][0] and $personDescriptors[$j][1] == $severity[$i][0]) {
                        $checked = 'checked';
                    }
                }
                $output .= "<input $mode $checked type='checkbox' name='status[]' value='".$descriptors[$n][0].'-'.$severity[$i][0]."'>";
                $output .= '<td>';
            }
            $output .= '</tr>';
        }
        $output .= '</table>';
    }

    return $output;
}
