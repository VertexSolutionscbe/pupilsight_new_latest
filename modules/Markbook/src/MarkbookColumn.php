<?php
/*
Pupilsight, Flexible & Open School System
*/

namespace Pupilsight\Module\Markbook;

/**
 * Helper class to holds and retrieve information for a single markbook column.
 *
 * @version 4th May 2016
 * @since   4th May 2016
 */
class MarkbookColumn
{
    public $pupilsightMarkbookColumnID;

    /**
     * Table row data from pupilsightMarkbookColumn
     * @var array
     */
    protected $data = array();

    /**
     * A count of the number of columns, so header can be spanned correctly
     * @var int
     */
    protected $spanCount;

    /**
     * Y/N to enable/disable effort in column
     * @var string
     */
    protected $enableEffort = '';

    /**
     * Y/N to enable/disable rubrics in column
     * @var string
     */
    protected $enableRubrics = '';

    /**
     * Constructor
     * Takes a row from pupilsightMarkbookColumn and builds a helper class
     *
     * @version  3rd May 2016
     * @since    3rd May 2016
     * @param    array  SQL Data Row
     * @param    Y/N value to enable/disable effort
     * @param    Y/N value to enable/disable rubrics
     * @return   void
     */
    public function __construct($row, $enableEffort, $enableRubrics)
    {
        $this->pupilsightMarkbookColumnID = $row['pupilsightMarkbookColumnID'];

        $this->data = $row;
        $this->spanCount = 0;

        $this->enableEffort = $enableEffort;
        $this->enableRubrics = $enableRubrics;

        if ($this->displayAttainment()) {
            $this->spanCount++;
        }

        if ($this->displayEffort()) {
            $this->spanCount++;
        }

        if ($this->displayComment()) {
            $this->spanCount++;
        }

        if ($this->displayUploadedResponse()) {
            $this->spanCount++;
        }

        if ($this->displaySubmission()) {
            $this->spanCount++;
        }
    }

    /**
     * Get Data
     * Returns field data from the column's row
     *
     * @version 4th May 2016
     * @since   4th May 2016
     * @param   string  $key
     * @return  mixed
     */
    public function getData($key)
    {
        return (isset($this->data[$key])) ? $this->data[$key] : null;
    }

    /**
     * Display Attainment
     * @version 4th May 2016
     * @since   4th May 2016
     * @return  bool
     */
    public function displayAttainment()
    {
        if (isset($this->data['attainment'])) {
            return ($this->data['attainment'] == 'Y' && ($this->hasAttainmentGrade() || $this->hasAttainmentRubric()));
        } else {
            return false;
        }
    }

    /**
     * Display Effort
     * @version 4th May 2016
     * @since   4th May 2016
     * @param Y/N to enable/disable effort
     * @return  bool
     */
    public function displayEffort()
    {
        if (isset($this->data['effort']) and $this->enableEffort == 'Y') {
            return ($this->data['effort'] == 'Y' && ($this->hasEffortGrade() || $this->hasEffortRubric()));
        } else {
            return false;
        }
    }

    /**
     * Display Comment
     * @version 4th May 2016
     * @since   4th May 2016
     * @return  bool
     */
    public function displayComment()
    {
        return (isset($this->data['comment'])) ? $this->data['comment'] == 'Y' : false;
    }

    /**
     * Display Uploaded Response
     * @version 4th May 2016
     * @since   4th May 2016
     * @return  bool
     */
    public function displayUploadedResponse()
    {
        return (isset($this->data['uploadedResponse'])) ? $this->data['uploadedResponse'] == 'Y' : false;
    }

    /**
     * Display Submission
     * @version 4th May 2016
     * @since   4th May 2016
     * @return  bool
     */
    public function displaySubmission()
    {
        return (isset($this->data['submission'])) ? $this->data['submission'] == 'Y' : false;
    }

    /**
     * Display Raw Marks
     * @version 4th May 2016
     * @since   4th May 2016
     * @return  bool
     */
    public function displayRawMarks()
    {
        return (isset($this->data['attainmentRaw'])) ? $this->data['attainmentRaw'] == 'Y' : false;
    }

    /**
     * Has Attainment Grade
     * @version 4th May 2016
     * @since   4th May 2016
     * @return  bool
     */
    public function hasAttainmentGrade()
    {
        return (isset($this->data['pupilsightScaleIDAttainment'])) ? !empty($this->data['pupilsightScaleIDAttainment']) : false;
    }

    /**
     * Has Attainment Raw Max
     * @version 4th May 2016
     * @since   4th May 2016
     * @return  bool
     */
    public function hasAttainmentRawMax()
    {
        return (isset($this->data['attainmentRawMax'])) ? !empty($this->data['attainmentRawMax']) : false;
    }

    /**
     * Has Attainment Rubric
     * @version 4th May 2016
     * @since   4th May 2016
     * @return  bool
     */
    public function hasAttainmentRubric()
    {
        return (isset($this->data['pupilsightRubricIDAttainment']) and $this->enableRubrics == 'Y') ? !empty($this->data['pupilsightRubricIDAttainment']) : false;
    }

    /**
     * Has Attainment Weighting
     * @version 4th May 2016
     * @since   4th May 2016
     * @return  bool
     */
    public function hasAttainmentWeighting()
    {
        return (isset($this->data['attainmentWeighting'])) ? !empty($this->data['attainmentWeighting']) : false;
    }

    /**
     * Has Effort Grade
     * @version 4th May 2016
     * @since   4th May 2016
     * @return  bool
     */
    public function hasEffortGrade()
    {
        return (isset($this->data['pupilsightScaleIDEffort'])) ? !empty($this->data['pupilsightScaleIDEffort']) : false;
    }

    /**
     * Has Effort Rubric
     * @version 4th May 2016
     * @since   4th May 2016
     * @return  bool
     */
    public function hasEffortRubric()
    {
        return (isset($this->data['pupilsightRubricIDEffort']) and $this->enableRubrics == 'Y') ? !empty($this->data['pupilsightRubricIDEffort']) : false;
    }

    /**
     * Has Attachment
     * @version 4th May 2016
     * @since   4th May 2016
     * @param   string  $path  File path to attachment directory
     * @return  bool
     */
    public function hasAttachment($path)
    {
        return (isset($this->data['attachment']) && !empty($this->data['attachment']) && file_exists($path . '/' . $this->data['attachment']));
    }

    /**
     * Get Span Count
     * @version 4th May 2016
     * @since   4th May 2016
     * @return  int
     */
    public function getSpanCount()
    {
        return $this->spanCount;
    }

    /**
     * Set Submission Details
     * @version 4th May 2016
     * @since   4th May 2016
     * @param   array $row
     */
    public function setSubmissionDetails($row)
    {
        if (empty($row)) {
            return false;
        }

        $this->data['lessonDate'] = (isset($row['date'])) ? $row['date'] : '';
        $this->data['homeworkDueDateTime'] = (isset($row['homeworkDueDateTime'])) ? $row['homeworkDueDateTime'] : '';
        $this->data['homeworkSubmissionRequired'] = (isset($row['homeworkSubmissionRequired'])) ? $row['homeworkSubmissionRequired'] : '';

        $this->data['submission'] = (isset($row['homeworkSubmission'])) ? $row['homeworkSubmission'] : 'N';
    }
}
