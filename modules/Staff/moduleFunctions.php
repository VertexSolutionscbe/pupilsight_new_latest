<?php
/*
Pupilsight, Flexible & Open School System
*/
//$student, $staff, $parent, $other, $applicationForm, $dataUpdater should all be TRUE/FALSE/NULL
//Returns query result
function getCustomFields($connection2, $guid, $student = null, $staff = null, $parent = null, $other = null, $applicationForm = null, $dataUpdater = null, $publicRegistration = null)
{
    $return = false;

    try {
        $data = array();
        $where = '';
        $whereInner = '';
        if ($student) {
            $data['student'] = $student;
            $whereInner .= 'activePersonStudent=:student OR ';
        }
        if ($staff) {
            $data['staff'] = $staff;
            $whereInner .= 'activePersonStaff=:staff OR ';
        }
        if ($parent) {
            $data['parent'] = $parent;
            $whereInner .= 'activePersonParent=:parent OR ';
        }
        if ($other) {
            $data['other'] = $other;
            $whereInner .= 'activePersonOther=:other OR ';
        }
        if ($applicationForm) {
            $data['applicationForm'] = $applicationForm;
            $where .= ' AND activeApplicationForm=:applicationForm';
        }
        if ($dataUpdater) {
            $data['dataUpdater'] = $dataUpdater;
            $where .= ' AND activeDataUpdater=:dataUpdater';
        }
        if ($publicRegistration) {
            $data['publicRegistration'] = $publicRegistration;
            $where .= ' AND activePublicRegistration=:publicRegistration';
        }

        if ($whereInner != '') {
            $whereInner = ' AND ('.substr($whereInner, 0, -4).') ';
        }

        $sql = "SELECT * FROM pupilsightPersonField WHERE active='Y' $whereInner $where";
        $result = $connection2->prepare($sql);
        $result->execute($data);
    } catch (PDOException $e) {
    }

    if ($result !== false) {
        $return = $result;
    }

    return $return;
}

//$row is the database row draw from pupilsightPersonField, $value is the current value of that field
function renderCustomFieldRow($connection2, $guid, $row, $value = null, $fieldNameSuffix = '', $rowClass = '', $ignoreRequired = false)
{
    $return = '';

    $return .= "<tr class='$rowClass'>";
    $return .= '<td>';
    $return .= '<b>'.__($row['name']).'</b>';
    if ($row['required'] == 'Y' and $ignoreRequired == false) {
        $return .= ' *';
    }
    if ($row['description'] == 'Y') {
        $return .= '<br/>';
        $return .= "<span style='font-size: 90%'><i>".__($row['description']).'<br/>';
        $return .= '</span>';
    }
    $return .= '</td>';
    $return .= '<td class="right">';
    if ($row['type'] == 'varchar') {
        $return .= '<input name="'.$fieldNameSuffix.'custom'.$row['pupilsightPersonFieldID'].'" id="'.$fieldNameSuffix.'custom'.$row['pupilsightPersonFieldID']."\" maxlength='".$row['options']."' value=\"$value\" type=\"text\" style=\"width: 300px\">";
        if ($row['required'] == 'Y' and $ignoreRequired == false) { //is required
                    $return .= '<script type="text/javascript">';
            $return .= 'var '.$fieldNameSuffix.'custom'.$row['pupilsightPersonFieldID']."=new LiveValidation('".$fieldNameSuffix.'custom'.$row['pupilsightPersonFieldID']."');";
            $return .= $fieldNameSuffix.'custom'.$row['pupilsightPersonFieldID'].'.add(Validate.Presence);';
            $return .= '</script>';
        }
    } elseif ($row['type'] == 'text') {
        $return .= '<textarea name="'.$fieldNameSuffix.'custom'.$row['pupilsightPersonFieldID'].'" id="'.$fieldNameSuffix.'custom'.$row['pupilsightPersonFieldID']."\" rows='".$row['options']."' style=\"width: 300px\">$value</textarea>";
        if ($row['required'] == 'Y' and $ignoreRequired == false) { //is required
                    $return .= '<script type="text/javascript">';
            $return .= 'var '.$fieldNameSuffix.'custom'.$row['pupilsightPersonFieldID']."=new LiveValidation('".$fieldNameSuffix.'custom'.$row['pupilsightPersonFieldID']."');";
            $return .= $fieldNameSuffix.'custom'.$row['pupilsightPersonFieldID'].'.add(Validate.Presence);';
            $return .= '</script>';
        }
    } elseif ($row['type'] == 'date') {
        $return .= '<input name="'.$fieldNameSuffix.'custom'.$row['pupilsightPersonFieldID'].'" id="'.$fieldNameSuffix.'custom'.$row['pupilsightPersonFieldID']."\" maxlength='10' value=\"".dateConvertBack($guid, $value).'" type="text" style="width: 300px">';
        $return .= '<script type="text/javascript">';
        $return .= 'var custom'.$row['pupilsightPersonFieldID']."=new LiveValidation('custom".$row['pupilsightPersonFieldID']."');";
        $return .= 'custom'.$row['pupilsightPersonFieldID'].'.add( Validate.Format, {pattern: ';
        if ($_SESSION[$guid]['i18n']['dateFormatRegEx'] == '') {
            $return .= "/^(0[1-9]|[12][0-9]|3[01])[- /.](0[1-9]|1[012])[- /.](19|20)\d\d$/i";
        } else {
            $return .= $_SESSION[$guid]['i18n']['dateFormatRegEx'];
        }
        $return .= ', failureMessage: "Use ';
        if ($_SESSION[$guid]['i18n']['dateFormat'] == '') {
            $return .= 'dd/mm/yyyy';
        } else {
            $return .= $_SESSION[$guid]['i18n']['dateFormat'];
        }
        $return .= '." } );';
        $return .= '</script>';
        $return .= '<script type="text/javascript">';
        $return .= '$(function() {';
        $return .= '$( "#custom'.$row['pupilsightPersonFieldID'].'" ).datepicker();';
        $return .= '});';
        $return .= '</script>';
        if ($row['required'] == 'Y' and $ignoreRequired == false) { //is required
                    $return .= '<script type="text/javascript">';
            $return .= 'var '.$fieldNameSuffix.'custom'.$row['pupilsightPersonFieldID']."=new LiveValidation('".$fieldNameSuffix.'custom'.$row['pupilsightPersonFieldID']."');";
            $return .= $fieldNameSuffix.'custom'.$row['pupilsightPersonFieldID'].'.add(Validate.Presence);';
            $return .= '</script>';
        }
    } elseif ($row['type'] == 'url') {
        $return .= '<input name="'.$fieldNameSuffix.'custom'.$row['pupilsightPersonFieldID'].'" id="'.$fieldNameSuffix.'custom'.$row['pupilsightPersonFieldID']."\" maxlength='255' value=\"$value\" type=\"text\" style=\"width: 300px\">";
        if ($row['required'] == 'Y' and $ignoreRequired == false) { //is required
                    $return .= '<script type="text/javascript">';
            $return .= 'var '.$fieldNameSuffix.'custom'.$row['pupilsightPersonFieldID']."=new LiveValidation('".$fieldNameSuffix.'custom'.$row['pupilsightPersonFieldID']."');";
            $return .= $fieldNameSuffix.'custom'.$row['pupilsightPersonFieldID'].".add( Validate.Format, { pattern: /(http|https):\/\/(\w+:{0,1}\w*@)?(\S+)(:[0-9]+)?(\/|\/([\w#!:.?+=&%@!\-\/]))?/, failureMessage: \"Must start with http:// or https://\" } );";
            $return .= $fieldNameSuffix.'custom'.$row['pupilsightPersonFieldID'].'.add(Validate.Presence);';
            $return .= '</script>';
        }
    } elseif ($row['type'] == 'select') {
        $return .= '<select style="width: 302px" name="'.$fieldNameSuffix.'custom'.$row['pupilsightPersonFieldID'].'" id="'.$fieldNameSuffix.'custom'.$row['pupilsightPersonFieldID'].'">';
        if ($row['required'] == 'Y' and $ignoreRequired == false) { //is required
                        $return .= '<option value="Please select...">'.__('Please select...').'</option>';
        } else {
            $return .= '<option value=""></option>';
        }
        $options = explode(',', $row['options']);
        foreach ($options as $option) {
            $selected = '';
            if (trim($option) == $value) {
                $selected = 'selected';
            }
            $return .= "<option $selected value=\"".trim($option).'">'.trim($option).'</option>';
        }

        $return .= '</select>';
        if ($row['required'] == 'Y' and $ignoreRequired == false) { //is required
                    $return .= '<script type="text/javascript">';
            $return .= 'var '.$fieldNameSuffix.'custom'.$row['pupilsightPersonFieldID']."=new LiveValidation('".$fieldNameSuffix.'custom'.$row['pupilsightPersonFieldID']."');";
            $return .= $fieldNameSuffix.'custom'.$row['pupilsightPersonFieldID'].".add(Validate.Exclusion, { within: ['Please select...'], failureMessage: \"".__('Select something!').'"});';
            $return .= '</script>';
        }
    }
    $return .= '</td>';
    $return .= '</tr>';

    return $return;
}
