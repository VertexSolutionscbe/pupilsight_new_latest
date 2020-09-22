<?php
/*
Pupilsight, Flexible & Open School System
*/

namespace Pupilsight\Forms;

use Pupilsight\Forms\FormFactory;
use Pupilsight\Contracts\Database\Connection;
use Pupilsight\Services\Format;

/**
 * DatabaseFormFactory
 *
 * Handles Form object creation that are pre-loaded from SQL queries
 *
 * @version v14
 * @since   v14
 */
class DatabaseFormFactory extends FormFactory
{
    protected $pdo;

    protected $cachedQueries = array();

    /**
     * Create a factory with access to the provided a database connection.
     * @param  Pupilsight\Contracts\Database\Connection  $pdo
     */
    public function __construct(Connection $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Create and return an instance of DatabaseFormFactory.
     * @return  object DatabaseFormFactory
     */
    public static function create(Connection $pdo = null)
    {
        return new DatabaseFormFactory($pdo);
    }

    public function createSelectSchoolYear($name, $status = 'All', $orderBy = 'ASC')
    {
        $orderBy = ($orderBy == 'ASC' || $orderBy == 'DESC') ? $orderBy : 'ASC';
        switch ($status) {
            case 'Active':
                $sql = "SELECT pupilsightSchoolYearID as value, name FROM pupilsightSchoolYear WHERE status='Current' OR status='Upcoming' ORDER BY sequenceNumber $orderBy"; break;

            case 'Upcoming':
                $sql = "SELECT pupilsightSchoolYearID as value, name FROM pupilsightSchoolYear WHERE status='Upcoming' ORDER BY sequenceNumber $orderBy"; break;

            case 'Past':
                $sql = "SELECT pupilsightSchoolYearID as value, name FROM pupilsightSchoolYear WHERE status='Past' ORDER BY sequenceNumber $orderBy"; break;

            case 'All':
            case 'Any':
            default:
                $sql = "SELECT pupilsightSchoolYearID as value, name FROM pupilsightSchoolYear ORDER BY sequenceNumber $orderBy"; break;
        }
        $results = $this->pdo->executeQuery(array(), $sql);

        return $this->createSelect($name)->fromResults($results)->placeholder();
    }

    /*
    The optional $all function adds an option to the top of the select, using * to allow selection of all year groups
    */
    public function createSelectYearGroup($name, $all = false)
    {
        $sql = "SELECT pupilsightYearGroupID as value, name FROM pupilsightYearGroup ORDER BY sequenceNumber";
        $results = $this->pdo->executeQuery(array(), $sql);

        if (!$all)
            return $this->createSelect($name)->fromResults($results)->placeholder();
        else
            return $this->createSelect($name)->fromArray(array("*" => "All"))->fromResults($results)->placeholder();
    }

    /*
    The optional $all function adds an option to the top of the select, using * to allow selection of all roll groups
    */
    public function createSelectRollGroup($name, $pupilsightSchoolYearID, $all = false)
    {
        $data = array('pupilsightSchoolYearID' => $pupilsightSchoolYearID);
        $sql = "SELECT pupilsightRollGroupID as value, name FROM pupilsightRollGroup WHERE pupilsightSchoolYearID=:pupilsightSchoolYearID ORDER BY LENGTH(name), name";
        $results = $this->pdo->executeQuery($data, $sql);

        if (!$all)
            return $this->createSelect($name)->fromResults($results)->placeholder();
        else
            return $this->createSelect($name)->fromArray(array("*" => "All"))->fromResults($results)->placeholder();
    }

    public function createSelectClass($name, $pupilsightSchoolYearID, $pupilsightPersonID = null, $params = array())
    {
        $classes = array();
        if (!empty($pupilsightPersonID)) {
            $data = array('pupilsightSchoolYearID' => $pupilsightSchoolYearID, 'pupilsightPersonID' => $pupilsightPersonID);
            $sql = "SELECT pupilsightCourseClass.pupilsightCourseClassID as value, CONCAT(pupilsightCourse.nameShort, '.', pupilsightCourseClass.nameShort) as name FROM pupilsightCourseClassPerson JOIN pupilsightCourseClass ON (pupilsightCourseClassPerson.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID) JOIN pupilsightCourse ON (pupilsightCourseClass.pupilsightCourseID=pupilsightCourse.pupilsightCourseID) WHERE pupilsightCourse.pupilsightSchoolYearID=:pupilsightSchoolYearID AND pupilsightPersonID=:pupilsightPersonID";
            if (isset($params['attendance'])) {
                $data['attendance'] = $params['attendance'];
                $sql .= " AND pupilsightCourseClass.attendance=:attendance";
            }
            if (isset($params['reportable'])) {
                $data['reportable'] = $params['reportable'];
                $sql .= " AND pupilsightCourseClass.reportable=:reportable";
            }
            $sql .= " ORDER BY name";
            $result = $this->pdo->executeQuery($data, $sql);
            if ($result->rowCount() > 0) {
                $classes['--'. __('My Classes') . '--'] = $result->fetchAll(\PDO::FETCH_KEY_PAIR);
            }
        }

        $data=array('pupilsightSchoolYearID'=>$pupilsightSchoolYearID);
        $sql= "SELECT pupilsightCourseClass.pupilsightCourseClassID AS value, CONCAT(pupilsightCourse.nameShort, '.', pupilsightCourseClass.nameShort) AS name FROM pupilsightCourse JOIN pupilsightCourseClass ON (pupilsightCourseClass.pupilsightCourseID=pupilsightCourse.pupilsightCourseID) WHERE pupilsightCourse.pupilsightSchoolYearID=:pupilsightSchoolYearID";
        if (isset($params['attendance'])) {
            $data['attendance'] = $params['attendance'];
            $sql .= " AND pupilsightCourseClass.attendance=:attendance";
        }
        if (isset($params['reportable'])) {
            $data['reportable'] = $params['reportable'];
            $sql .= " AND pupilsightCourseClass.reportable=:reportable";
        }
        $sql .= " ORDER BY name";
        $result = $this->pdo->executeQuery($data, $sql);

        if ($result->rowCount() > 0) {
            if (!empty($pupilsightPersonID)) {
                $classes['--' . __('All Classes') . '--'] = $result->fetchAll(\PDO::FETCH_KEY_PAIR);
            } else {
                $classes = $result->fetchAll(\PDO::FETCH_KEY_PAIR);
            }
        }

        return $this->createSelect($name)->fromArray($classes)->placeholder();
    }

    public function createCheckboxYearGroup($name)
    {
        $sql = "SELECT pupilsightYearGroupID as `value`, name FROM pupilsightYearGroup ORDER BY sequenceNumber";
        $results = $this->pdo->executeQuery(array(), $sql);

        // Get the yearGroups in a $key => $value array
        $yearGroups = ($results && $results->rowCount() > 0)? $results->fetchAll(\PDO::FETCH_KEY_PAIR) : array();

        return $this->createCheckbox($name)->fromArray($yearGroups);
    }

    public function createCheckboxSchoolYearTerm($name, $pupilsightSchoolYearID)
    {
        $data = array('pupilsightSchoolYearID' => $pupilsightSchoolYearID);
        $sql = "SELECT pupilsightSchoolYearTermID as `value`, name FROM pupilsightSchoolYearTerm WHERE pupilsightSchoolYearID=:pupilsightSchoolYearID ORDER BY sequenceNumber";
        $results = $this->pdo->executeQuery($data, $sql);

        // Get the terms in a $key => $value array
        $terms = ($results && $results->rowCount() > 0)? $results->fetchAll(\PDO::FETCH_KEY_PAIR) : array();

        return $this->createCheckbox($name)->fromArray($terms);
    }

    public function createSelectDepartment($name)
    {
        $sql = "SELECT type, pupilsightDepartmentID as value, name FROM pupilsightDepartment ORDER BY name";
        $results = $this->pdo->executeQuery(array(), $sql);

        $departments = array();

        if ($results && $results->rowCount() > 0) {
            while ($row = $results->fetch()) {
                $departments[$row['type']][$row['value']] = $row['name'];
            }
        }

        return $this->createSelect($name)->fromArray($departments)->placeholder();
    }

    public function createSelectSchoolYearTerm($name, $pupilsightSchoolYearID)
    {
        $data = array('pupilsightSchoolYearID' => $pupilsightSchoolYearID);
        $sql = "SELECT pupilsightSchoolYearTermID as `value`, name FROM pupilsightSchoolYearTerm WHERE pupilsightSchoolYearID=:pupilsightSchoolYearID ORDER BY sequenceNumber";
        $results = $this->pdo->executeQuery($data, $sql);

        return $this->createSelect($name)->fromResults($results)->placeholder();
    }

    public function createSelectTheme($name)
    {
        $sql = "SELECT pupilsightThemeID as value, (CASE WHEN active='Y' THEN CONCAT(name, ' (', '".__('System Default')."', ')') ELSE name END) AS name FROM pupilsightTheme ORDER BY name";
        $results = $this->pdo->executeQuery(array(), $sql);

        return $this->createSelect($name)->fromResults($results)->placeholder();
    }

    public function createSelectI18n($name)
    {
        $sql = "SELECT * FROM pupilsighti18n WHERE active='Y' ORDER BY code";
        $results = $this->pdo->select($sql);

        $values = array_reduce($results->fetchAll(), function ($group, $item) {
            if (isset($item['installed']) && $item['installed'] == 'Y') {
                $group[$item['pupilsighti18nID']] = $item['systemDefault'] == 'Y'? $item['name'].' ('.__('System Default').')' : $item['name'];
            }
            return $group;
        }, []);

        return $this->createSelect($name)->fromArray($values)->placeholder();
    }

    public function createSelectLanguage($name)
    {
        $sql = "SELECT name as value, name FROM pupilsightLanguage ORDER BY name";
        $results = $this->pdo->executeQuery(array(), $sql);

        return $this->createSelect($name)->fromResults($results)->placeholder();
    }

    public function createSelectCountry($name)
    {
        $sql = "SELECT printable_name as value, printable_name as name FROM pupilsightCountry ORDER BY printable_name";
        $results = $this->pdo->executeQuery(array(), $sql);

        return $this->createSelect($name)->fromResults($results)->placeholder();
    }

    public function createSelectRole($name)
    {
        $sql = "SELECT pupilsightRoleID as value, name FROM pupilsightRole ORDER BY name";
        $results = $this->pdo->executeQuery(array(), $sql);

        return $this->createSelect($name)->fromResults($results)->placeholder();
    }

    public function createSelectStatus($name)
    {
        $statuses = array(
            'Full'     => __('Full'),
            'Expected' => __('Expected'),
            'Left'     => __('Left'),
        );

        if (getSettingByScope($this->pdo->getConnection(), 'User Admin', 'enablePublicRegistration') == 'Y') {
            $statuses['Pending Approval'] = __('Pending Approval');
        }

        return $this->createSelect($name)->fromArray($statuses);
    }

    public function createSelectStaff($name)
    {
        $sql = "SELECT pupilsightPerson.pupilsightPersonID, title, surname, preferredName
                FROM pupilsightPerson JOIN pupilsightStaff ON (pupilsightPerson.pupilsightPersonID=pupilsightStaff.pupilsightPersonID)
                WHERE status='Full' ORDER BY surname, preferredName";

        $staff = $this->pdo->select($sql)->fetchGroupedUnique();

        $staff = array_map(function ($person) {
            return Format::name($person['title'], $person['preferredName'], $person['surname'], 'Staff', true, true);
        }, $staff);

        return $this->createSelectPerson($name)->fromArray($staff);
    }

    public function createSelectUsersFromList($name, $people = [])
    {
        $data = ['pupilsightPersonIDList' => implode(',', $people)];
        $sql = "SELECT pupilsightPerson.pupilsightPersonID, title, surname, preferredName
                FROM pupilsightPerson
                WHERE status='Full' 
                AND FIND_IN_SET(pupilsightPersonID, :pupilsightPersonIDList)
                ORDER BY FIND_IN_SET(pupilsightPersonID, :pupilsightPersonIDList), surname, preferredName";

        $people = $this->pdo->select($sql, $data)->fetchGroupedUnique();

        $people = array_map(function ($person) {
            return Format::name($person['title'], $person['preferredName'], $person['surname'], 'Staff', true, true);
        }, $people);

        return $this->createSelectPerson($name)->fromArray($people);
    }

    public function createSelectUsers($name, $pupilsightSchoolYearID = false, $params = array())
    {
        $params = array_replace(['includeStudents' => false, 'includeStaff' => false], $params);

        $users = array();

        if ($params['includeStaff'] == true) {
            $data = array('pupilsightSchoolYearID' => $pupilsightSchoolYearID, 'date' => date('Y-m-d'));
            $sql = "SELECT pupilsightPerson.pupilsightPersonID, preferredName, surname 
                    FROM pupilsightPerson 
                    JOIN pupilsightStaff ON (pupilsightPerson.pupilsightPersonID=pupilsightStaff.pupilsightPersonID) 
                    WHERE pupilsightPerson.status='Full' 
                    ORDER BY pupilsightPerson.surname, pupilsightPerson.preferredName";
            $result = $this->pdo->executeQuery($data, $sql);
            if ($result->rowCount() > 0) {
                $users[__('Staff')] = array_reduce($result->fetchAll(), function ($group, $item) {
                    $group[$item['pupilsightPersonID']] = formatName('', htmlPrep($item['preferredName']), htmlPrep($item['surname']), 'Staff', true, true);
                    return $group;
                }, array());
            }
        }

        if ($params['includeStudents'] == true) {
            $data = array('pupilsightSchoolYearID' => $pupilsightSchoolYearID, 'date' => date('Y-m-d'));
            $sql = "SELECT pupilsightPerson.pupilsightPersonID, preferredName, surname, pupilsightRollGroup.name AS rollGroupName 
                    FROM pupilsightPerson
                    JOIN pupilsightStudentEnrolment ON (pupilsightPerson.pupilsightPersonID=pupilsightStudentEnrolment.pupilsightPersonID) 
                    JOIN pupilsightRollGroup ON (pupilsightStudentEnrolment.pupilsightRollGroupID=pupilsightRollGroup.pupilsightRollGroupID)
                    JOIN pupilsightYearGroup ON (pupilsightStudentEnrolment.pupilsightYearGroupID=pupilsightYearGroup.pupilsightYearGroupID)
                    WHERE pupilsightStudentEnrolment.pupilsightSchoolYearID=:pupilsightSchoolYearID
                    AND pupilsightPerson.status='FULL' 
                    AND (dateStart IS NULL OR dateStart<=:date) AND (dateEnd IS NULL  OR dateEnd>=:date) 
                    ORDER BY rollGroupName, pupilsightPerson.surname, pupilsightPerson.preferredName";
            $result = $this->pdo->executeQuery($data, $sql);
        
            if ($result->rowCount() > 0) {
                $users[__('Enrolable Students')] = array_reduce($result->fetchAll(), function($group, $item) {
                    $group[$item['pupilsightPersonID']] = $item['rollGroupName'].' - '.formatName('', $item['preferredName'], $item['surname'], 'Student', true);
                    return $group;
                }, array());
            }
        }

        $sql = "SELECT pupilsightPerson.pupilsightPersonID, title, surname, preferredName, username, pupilsightRole.category
                FROM pupilsightPerson
                JOIN pupilsightRole ON (pupilsightRole.pupilsightRoleID=pupilsightPerson.pupilsightRoleIDPrimary)
                WHERE status='Full' OR status='Expected' 
                ORDER BY surname, preferredName";
        $result = $this->pdo->executeQuery(array(), $sql);

        if ($result->rowCount() > 0) {
            $users[__('All Users')] = array_reduce($result->fetchAll(), function ($group, $item) {
                $group[$item['pupilsightPersonID']] = formatName('', $item['preferredName'], $item['surname'], 'Student', true).' ('.$item['username'].', '.$item['category'].')';
                return $group;
            }, array());
        }

        return $this->createSelectPerson($name)->fromArray($users);
    }

    /*
    $params is an array, with the following options as keys:
        allStudents - false by default. true displays students regardless of status and start/end date
        byName - true by default. Adds students organised by name
        byRoll - false by default. Adds students organised by roll group. Can be used in conjunction with byName to have multiple sections
        showRoll - true by default. Displays roll group beside student's name, when organised byName. Incompatible with allStudents
    */
    public function createSelectStudent($name, $pupilsightSchoolYearID, $params = array())
    {
        //Create arrays for use later on
        $values = array();
        $data = array();

        // Check params and set defaults if not defined
        $params = array_replace(array('allStudents' => false, 'byName' => true, 'byRoll' => false, 'showRoll' => true), $params);

        //Check for multiple by methods, so we know when to apply optgroups
        $multipleBys = false;
        if ($params["byName"] && $params["byRoll"]) {
            $multipleBys = true;
        }

        //Add students by roll group
        if ($params["byRoll"]) {
            if ($params["allStudents"]) {
                $data = array('pupilsightSchoolYearID' => $pupilsightSchoolYearID);
                $sql = "SELECT pupilsightPerson.pupilsightPersonID, preferredName, surname, pupilsightRollGroup.name AS name
                    FROM pupilsightPerson
                        JOIN pupilsightStudentEnrolment ON (pupilsightStudentEnrolment.pupilsightPersonID=pupilsightPerson.pupilsightPersonID)
                        JOIN pupilsightRollGroup ON (pupilsightStudentEnrolment.pupilsightRollGroupID=pupilsightRollGroup.pupilsightRollGroupID)
                    WHERE pupilsightRollGroup.pupilsightSchoolYearID=:pupilsightSchoolYearID
                    ORDER BY name, surname, preferredName";

            } else {
                $data = array('pupilsightSchoolYearID' => $pupilsightSchoolYearID, 'date' => date('Y-m-d'));
                $sql = "SELECT pupilsightPerson.pupilsightPersonID, preferredName, surname, pupilsightRollGroup.name AS name
                    FROM pupilsightPerson
                        JOIN pupilsightStudentEnrolment ON (pupilsightStudentEnrolment.pupilsightPersonID=pupilsightPerson.pupilsightPersonID)
                        JOIN pupilsightRollGroup ON (pupilsightStudentEnrolment.pupilsightRollGroupID=pupilsightRollGroup.pupilsightRollGroupID)
                    WHERE status='Full'
                        AND (dateStart IS NULL OR dateStart<=:date)
                        AND (dateEnd IS NULL  OR dateEnd>=:date)
                        AND pupilsightRollGroup.pupilsightSchoolYearID=:pupilsightSchoolYearID
                    ORDER BY name, surname, preferredName";
            }

            $results = $this->pdo->executeQuery($data, $sql);

            if ($results && $results->rowCount() > 0) {
                while ($row = $results->fetch()) {
                    if ($multipleBys) {
                        $values[__('Students by Roll Group')][$row['pupilsightPersonID']] = htmlPrep($row['name']).' - '.formatName('', htmlPrep($row['preferredName']), htmlPrep($row['surname']), 'Student', true);
                    } else {
                        $values[$row['pupilsightPersonID']] = htmlPrep($row['name']).' - '.formatName('', htmlPrep($row['preferredName']), htmlPrep($row['surname']), 'Student', true);
                    }
                }
            }
        }

        //Add students by name
        if ($params["byName"]) {
            if ($params["allStudents"]) {
                $sql = "SELECT pupilsightPerson.pupilsightPersonID, title, surname, preferredName, null AS name
                    FROM pupilsightPerson
                        JOIN pupilsightRole ON (pupilsightPerson.pupilsightRoleIDPrimary=pupilsightRole.pupilsightRoleID)
                    WHERE pupilsightRole.category='Student'
                    ORDER BY surname, preferredName";
            } else {
                $data = array('pupilsightSchoolYearID' => $pupilsightSchoolYearID, 'date' => date('Y-m-d'));
                $sql = "SELECT pupilsightPerson.pupilsightPersonID, title, surname, preferredName, pupilsightRollGroup.name AS name
                    FROM pupilsightPerson
                        JOIN pupilsightStudentEnrolment ON (pupilsightPerson.pupilsightPersonID=pupilsightStudentEnrolment.pupilsightPersonID)
                        JOIN pupilsightRollGroup ON (pupilsightStudentEnrolment.pupilsightRollGroupID=pupilsightRollGroup.pupilsightRollGroupID)
                    WHERE status='Full'
                        AND (dateStart IS NULL OR dateStart<=:date)
                        AND (dateEnd IS NULL  OR dateEnd>=:date)
                        AND pupilsightRollGroup.pupilsightSchoolYearID=:pupilsightSchoolYearID
                    ORDER BY surname, preferredName";
            }

            $results = $this->pdo->executeQuery($data, $sql);

            if ($results && $results->rowCount() > 0) {
                while ($row = $results->fetch()) {
                    if ($multipleBys) {
                        if (!$params['allStudents'] && $params['byName'] && $params['showRoll']) {
                            $values[__('Students by Name')][$row['pupilsightPersonID']] = formatName(htmlPrep($row['title']), ($row['preferredName']), htmlPrep($row['surname']), 'Student', true, true)." (".$row['name'].")";
                        }
                        else {
                            $values[__('Students by Name')][$row['pupilsightPersonID']] = formatName(htmlPrep($row['title']), ($row['preferredName']), htmlPrep($row['surname']), 'Student', true, true);
                        }
                    } else {
                        if (!$params['allStudents'] && $params['byName'] && $params['showRoll']) {
                            $values[$row['pupilsightPersonID']] = formatName(htmlPrep($row['title']), ($row['preferredName']), htmlPrep($row['surname']), 'Student', true, true)." (".$row['name'].")";
                        }
                        else {
                            $values[$row['pupilsightPersonID']] = formatName(htmlPrep($row['title']), ($row['preferredName']), htmlPrep($row['surname']), 'Student', true, true);
                        }
                    }
                }
            }
        }

        return $this->createSelectPerson($name)->fromArray($values);
    }

    public function createSelectGradeScale($name)
    {
        $sql = "SELECT pupilsightScaleID as value, name FROM pupilsightScale WHERE (active='Y') ORDER BY name";

        return $this->createSelect($name)->fromQuery($this->pdo, $sql)->placeholder();
    }

    public function createSelectGradeScaleGrade($name, $pupilsightScaleID, $params = array())
    {
        // Check params and set defaults if not defined
        $params = array_replace(array(
            'honourDefault' => true,
            'valueMode' => 'value',
            'labelMode' => 'value',
        ), $params);

        $valueQuery = ($params['valueMode'] == 'id')? 'pupilsightScaleGradeID as value' : 'value';
        $labelQuery = ($params['labelMode'] == 'descriptor')? 'descriptor' : 'value';

        $data = array('pupilsightScaleID' => $pupilsightScaleID);
        $sql = "SELECT {$valueQuery}, {$labelQuery} as name, isDefault FROM pupilsightScaleGrade WHERE pupilsightScaleID=:pupilsightScaleID ORDER BY sequenceNumber";
        $results = $this->pdo->executeQuery($data, $sql);

        $grades = ($results->rowCount() > 0)? $results->fetchAll() : array();
        $gradeOptions = array_combine(array_column($grades, 'value'), array_column($grades, 'name'));

        $default = array_search('Y', array_column($grades, 'isDefault'));
        $selected = ($params['honourDefault'] && !empty($default))? $grades[$default]['value'] : '';

        return $this->createSelect($name)->fromArray($gradeOptions)->selected($selected)->placeholder()->addClass('gradeSelect');
    }

    public function createSelectRubric($name, $pupilsightYearGroupIDList = '', $pupilsightDepartmentID = '')
    {
        $data = array('pupilsightYearGroupIDList' => $pupilsightYearGroupIDList, 'pupilsightDepartmentID' => $pupilsightDepartmentID, 'rubrics' => __('Rubrics'));
        $sql = "SELECT CONCAT(scope, ' ', :rubrics) as groupBy, pupilsightRubricID as value, 
                (CASE WHEN category <> '' THEN CONCAT(category, ' - ', pupilsightRubric.name) ELSE pupilsightRubric.name END) as name 
                FROM pupilsightRubric 
                JOIN pupilsightYearGroup ON (FIND_IN_SET(pupilsightYearGroup.pupilsightYearGroupID, pupilsightRubric.pupilsightYearGroupIDList))
                WHERE pupilsightRubric.active='Y' 
                AND FIND_IN_SET(pupilsightYearGroup.pupilsightYearGroupID, :pupilsightYearGroupIDList) 
                AND (scope='School' OR (scope='Learning Area' AND pupilsightDepartmentID=:pupilsightDepartmentID))
                GROUP BY pupilsightRubric.pupilsightRubricID
                ORDER BY scope, category, name";

        return $this->createSelect($name)->fromQuery($this->pdo, $sql, $data, 'groupBy')->placeholder();
    }

    public function createPhoneNumber($name)
    {
        $countryCodes = $this->getCachedQuery('phoneNumber');

        if (empty($countryCodes)) {
            $sql = 'SELECT iddCountryCode, printable_name FROM pupilsightCountry ORDER BY printable_name';
            $results = $this->pdo->executeQuery(array(), $sql);
            if ($results && $results->rowCount() > 0) {
                $countryCodes = $results->fetchAll();

                // Transform the row data into value => name pairs
                $countryCodes = array_reduce($countryCodes, function($codes, $item) {
                    $codes[$item['iddCountryCode']] = $item['iddCountryCode'].' - '.__($item['printable_name']);
                    return $codes;
                }, array());
            }
            $this->setCachedQuery('phoneNumber', $countryCodes);
        }

        return new Input\PhoneNumber($this, $name, $countryCodes);
    }

    public function createSequenceNumber($name, $tableName, $sequenceNumber = '', $columnName = null)
    {
        $columnName = empty($columnName)? $name : $columnName;

        $data = array('sequenceNumber' => $sequenceNumber);
        $sql = "SELECT GROUP_CONCAT(DISTINCT `{$columnName}` SEPARATOR '\',\'') FROM `{$tableName}` WHERE (`{$columnName}` IS NOT NULL AND `{$columnName}` <> :sequenceNumber) ORDER BY `{$columnName}`";
        $results = $this->pdo->executeQuery($data, $sql);

        $field = $this->createNumber($name)->minimum(1)->onlyInteger(true);

        if ($results && $results->rowCount() > 0) {
            $field->addValidation('Validate.Exclusion', 'within: [\''.$results->fetchColumn(0).'\'], failureMessage: "'.__('Value already in use!').'", partialMatch: false, caseSensitive: false');
        }

        if (!empty($sequenceNumber) || $sequenceNumber === false) {
            $field->setValue($sequenceNumber);
        } else {
            $sql = "SELECT MAX(`{$columnName}`) FROM `{$tableName}`";
            $results = $this->pdo->executeQuery(array(), $sql);
            $sequenceNumber = ($results && $results->rowCount() > 0)? $results->fetchColumn(0) : 1;

            $field->setValue($sequenceNumber+1);
        }

        return $field;
    }

    /*
    The optional $all function adds an option to the top of the select, using * to allow selection of all year groups
    */
    public function createSelectTransport($name, $all = false)
    {
        $sql = "SELECT DISTINCT transport AS value, transport AS name FROM pupilsightPerson WHERE status='Full' AND NOT transport='' ORDER BY transport";
        $results = $this->pdo->executeQuery(array(), $sql);

        if (!$all)
            return $this->createSelect($name)->fromResults($results)->placeholder();
        else
            return $this->createSelect($name)->fromArray(array("*" => "All"))->fromResults($results)->placeholder();
    }

    public function createSelectSpace($name)
    {
        $sql = "SELECT pupilsightSpaceID as value, name FROM pupilsightSpace ORDER BY name";
        $results = $this->pdo->executeQuery(array(), $sql);

        return $this->createSelect($name)->fromResults($results)->placeholder();
    }

    public function createTextFieldDistrict($name)
    {
        $sql = "SELECT DISTINCT name FROM pupilsightDistrict ORDER BY name";
        $result = $this->pdo->executeQuery(array(), $sql);
        $districts = ($result && $result->rowCount() > 0)? $result->fetchAll(\PDO::FETCH_COLUMN) : array();

        return $this->createTextField($name)->maxLength(30)->autocomplete($districts);
    }

    public function createSelectAlert($name)
    {
        $sql = 'SELECT pupilsightAlertLevelID AS value, name FROM pupilsightAlertLevel ORDER BY sequenceNumber';
        $results = $this->pdo->executeQuery(array(), $sql);

        return $this->createSelect($name)->fromResults($results)->placeholder();
    }

    protected function getCachedQuery($name)
    {
        return (isset($this->cachedQueries[$name]))? $this->cachedQueries[$name] : array();
    }

    protected function setCachedQuery($name, $results)
    {
        $this->cachedQueries[$name] = $results;
    }
}
