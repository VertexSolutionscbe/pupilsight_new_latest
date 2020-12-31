<?php
/*
Pupilsight, Flexible & Open School System
*/

function getBorrowingRecord($guid, $connection2, $pupilsightPersonID)
{
    $output = false;

    try {
        $data = array('pupilsightPersonID' => $pupilsightPersonID);
        $sql = 'SELECT pupilsightLibraryItem.*, pupilsightLibraryType.fields AS typeFields, timestampOut FROM pupilsightLibraryItem JOIN pupilsightLibraryType ON (pupilsightLibraryItem.pupilsightLibraryTypeID=pupilsightLibraryType.pupilsightLibraryTypeID) JOIN pupilsightLibraryItemEvent ON (pupilsightLibraryItemEvent.pupilsightLibraryItemID=pupilsightLibraryItem.pupilsightLibraryItemID) WHERE pupilsightLibraryItemEvent.pupilsightPersonIDStatusResponsible=:pupilsightPersonID ORDER BY timestampOut DESC';
        $result = $connection2->prepare($sql);
        $result->execute($data);
    } catch (PDOException $e) {
        $output .= "<div class='alert alert-danger'>" . $e->getMessage() . '</div>';
    }
    if ($result->rowCount() < 1) {
        $output .= "<div class='alert alert-danger'>";
        $output .= __('There are no records to display.');
        $output .= '</div>';
    } else {
        $output .= "<table class='mini' cellspacing='0' style='width: 100%'>";
        $output .= "<tr class='head'>";
        $output .= "<th style='text-align: center'>";

        $output .= '</th>';
        $output .= '<th>';
        $output .= __('Name') . '<br/>';
        $output .= "<span style='font-size: 85%; font-style: italic'>" . __('Author/Producer') . '</span>';
        $output .= '</th>';
        $output .= '<th>';
        $output .= __('ID');
        $output .= '</th>';
        $output .= '<th>';
        $output .= __('Location');
        $output .= '</th>';
        $output .= '<th>';
        $output .= __('Borrow Date') . '<br/>';
        $output .= "<span style='font-size: 85%; font-style: italic'>" . __('Return Date') . '</span>';
        $output .= '</th>';
        $output .= '<th>';
        $output .= __('Actions');
        $output .= '</th>';
        $output .= '</tr>';

        $count = 0;
        $rowNum = 'odd';
        while ($row = $result->fetch()) {
            if ($count % 2 == 0) {
                $rowNum = 'even';
            } else {
                $rowNum = 'odd';
            }
            if ((strtotime(date('Y-m-d')) - strtotime($row['returnExpected'])) / (60 * 60 * 24) > 0 and $row['status'] == 'On Loan') {
                $rowNum = 'error';
            }

            //COLOR ROW BY STATUS!
            $output .= "<tr class=$rowNum style='opacity: 1.0'>";
            $output .= "<td style='width: 260px'>";
            $output .= getImage($guid, $row['imageType'], $row['imageLocation'], false);
            $output .= '</td>';
            $output .= "<td style='width: 130px'>";
            $output .= '<b>' . $row['name'] . '</b><br/>';
            $output .= "<span style='font-size: 85%; font-style: italic'>" . $row['producer'] . '</span>';
            $output .= '</td>';
            $output .= "<td style='width: 130px'>";
            $output .= '<b>' . $row['id'] . '</b><br/>';
            $output .= '</td>';
            $output .= "<td style='width: 130px'>";
            if ($row['pupilsightSpaceID'] != '') {
                try {
                    $dataSpace = array('pupilsightSpaceID' => $row['pupilsightSpaceID']);
                    $sqlSpace = 'SELECT * FROM pupilsightSpace WHERE pupilsightSpaceID=:pupilsightSpaceID';
                    $resultSpace = $connection2->prepare($sqlSpace);
                    $resultSpace->execute($dataSpace);
                } catch (PDOException $e) {
                    $output .= "<div class='alert alert-danger'>" . $e->getMessage() . '</div>';
                }
                if ($resultSpace->rowCount() == 1) {
                    $rowSpace = $resultSpace->fetch();
                    $output .= '<b>' . $rowSpace['name'] . '</b><br/>';
                }
            }
            if ($row['locationDetail'] != '') {
                $output .= "<span style='font-size: 85%; font-style: italic'>" . $row['locationDetail'] . '</span>';
            }
            $output .= '</td>';
            $output .= "<td style='width: 130px'>";
            $output .= dateConvertBack($guid, substr($row['timestampOut'], 0, 10)) . '<br/>';
            if ($row['status'] == 'On Loan') {
                $output .= "<span style='font-size: 85%; font-style: italic'>" . dateConvertBack($guid, $row['returnExpected']) . '</span>';
            }
            $output .= '</td>';
            $output .= '<td>';
            $output .= "<script type='text/javascript'>";
            $output .= '$(document).ready(function(){';
            $output .= "\$(\".description-$count\").hide();";
            $output .= "\$(\".show_hide-$count\").fadeIn(1000);";
            $output .= "\$(\".show_hide-$count\").click(function(){";
            $output .= "\$(\".description-$count\").fadeToggle(1000);";
            $output .= '});';
            $output .= '});';
            $output .= '</script>';
            if ($row['fields'] != '') {
                $output .= "<a title='" . __('View Description') . "' class='show_hide-$count' onclick='false' href='#'><img style='padding-right: 5px' src='" . $_SESSION[$guid]['absoluteURL'] . '/themes/' . $_SESSION[$guid]['pupilsightThemeName'] . "/img/page_down.png' alt='Show Details' onclick='return false;' /></a>";
            }
            $output .= '</td>';
            $output .= '</tr>';
            if ($row['fields'] != '') {
                $output .= "<tr class='description-$count' id='fields-$count' style='background-color: #fff; display: none'>";
                $output .= '<td colspan=6>';
                $output .= "<table cellspacing='0' style='width: 100%'>";
                $typeFields = unserialize($row['typeFields']);
                $fields = unserialize($row['fields']);
                foreach ($typeFields as $typeField) {
                    if ($fields[$typeField['name']] != '') {
                        $output .= '<tr>';
                        $output .= "<td style='vertical-align: top; width: 200px'>";
                        $output .= '<b>' . __($typeField['name']) . '</b>';
                        $output .= '</td>';
                        $output .= "<td style='vertical-align: top'>";
                        if ($typeField['type'] == 'URL') {
                            $output .= "<a target='_blank' href='" . $fields[$typeField['name']] . "'>" . $fields[$typeField['name']] . '</a><br/>';
                        } else {
                            $output .= $fields[$typeField['name']] . '<br/>';
                        }
                        $output .= '</td>';
                        $output .= '</tr>';
                    }
                }
                $output .= '</table>';
                $output .= '</td>';
                $output .= '</tr>';
            }
            $output .= '</tr>';

            ++$count;
        }
        $output .= '</table>';
    }

    return $output;
}

function getImage($guid, $type, $location, $border = true)
{
    $output = false;

    $borderStyle = '';
    if ($border == true) {
        $borderStyle = '; border: 1px dashed #666';
    }

    if ($location == '') {
        $output .= "<img style='height: 240px; width: 240px; opacity: 1.0' class='user' src='" . $_SESSION[$guid]['absoluteURL'] . '/themes/' . $_SESSION[$guid]['pupilsightThemeName'] . "/img/anonymous_75.jpg'/><br/>";
    } else {
        if ($type == 'Link') {
            $output .= "<div style='height: 240px; width: 240px; display:table-cell; vertical-align:middle; text-align:center $borderStyle'>";
            $output .= "<img class='user' style='max-height: 240px; max-width: 240px; opacity: 1.0; margin: auto' src='" . $location . "'/><br/>";
            $output .= '</div>';
        }
        if ($type == 'File') {
            if (is_file($_SESSION[$guid]['absolutePath'] . '/' . $location)) {
                $output .= "<div style='height: 240px; width: 240px; display:table-cell; vertical-align:middle; text-align:center; $borderStyle'>";
                $output .= "<img class='user' style='max-height: 240px; max-width: 240px; opacity: 1.0; margin: auto' title='' src='" . $_SESSION[$guid]['absoluteURL'] . '/' . $location . "'/><br/>";
                $output .= '</div>';
            } else {
                $output .= "<img style='height: 240px; width: 240px; opacity: 1.0' class='user' src='" . $_SESSION[$guid]['absoluteURL'] . '/themes/' . $_SESSION[$guid]['pupilsightThemeName'] . "/img/anonymous_75.jpg'/><br/>";
            }
        }
    }

    return $output;
}
