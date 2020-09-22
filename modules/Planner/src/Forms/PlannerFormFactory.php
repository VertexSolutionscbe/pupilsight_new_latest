<?php
/*
Pupilsight, Flexible & Open School System
*/

namespace Pupilsight\Module\Planner\Forms;

use Pupilsight\Forms\DatabaseFormFactory;
use Pupilsight\Forms\OutputableInterface;
use Pupilsight\Contracts\Database\Connection;
use Pupilsight\Contracts\Services\Session;

/**
 * PlannerFormFactory
 *
 * @version v16
 * @since   v16
 */
class PlannerFormFactory extends DatabaseFormFactory
{
    /**
     * Create and return an instance of DatabaseFormFactory.
     * @return  object DatabaseFormFactory
     */
    public static function create(Connection $pdo = null)
    {
        return new PlannerFormFactory($pdo);
    }

    /**
     * Creates a fully-configured CustomBlocks input for Smart Blocks in the lesson planner.
     *
     * @param string $name
     * @param Session $session
     * @param string $guid
     * @return OutputableInterface
     */
    public function createPlannerSmartBlocks($name, $session, $guid) : OutputableInterface
    {
        $blockTemplate = $this->createSmartBlockTemplate($guid);

        // Create and initialize the Custom Blocks
        $customBlocks = $this->createCustomBlocks($name, $session)
            ->fromTemplate($blockTemplate)
            ->settings([
                'inputNameStrategy' => 'string',
                'addOnEvent'        => 'click',
                'sortable'          => true,
                'orderName'         => 'order',
            ])
            ->placeholder(__('Smart Blocks listed here...'))
            ->addBlockButton('showHide', __('Show/Hide'), 'plus.png');

        return $customBlocks;
    }

    /**
     * Creates a template for displaying Outcomes in a CustomBlocks input.
     *
     * @param string $guid
     * @return OutputableInterface
     */
    public function createSmartBlockTemplate($guid) : OutputableInterface
    {
        $blockTemplate = $this->createTable()->setClass('blank w-full');
            $row = $blockTemplate->addRow();
            $row->addTextField('title')
                ->setClass('w-3/4 title focus:bg-white')
                ->placeholder(__('Title'))
                ->append('<input type="hidden" id="pupilsightUnitClassBlockID" name="pupilsightUnitClassBlockID" value="">')
                ->append('<input type="hidden" id="pupilsightUnitBlockID" name="pupilsightUnitBlockID" value="">');

            $row = $blockTemplate->addRow()->addClass('w-3/4 flex justify-between mt-1');
                $row->addTextField('type')->placeholder(__('type (e.g. discussion, outcome)'))
                    ->setClass('w-full focus:bg-white mr-1');
                $row->addTextField('length')->placeholder(__('length (min)'))
                    ->setClass('w-24 focus:bg-white')->prepend('');

            $smartBlockTemplate = getSettingByScope($this->pdo->getConnection(), 'Planner', 'smartBlockTemplate');
            $col = $blockTemplate->addRow()->addClass('showHide w-full')->addColumn();
                $col->addLabel('contentsLabel', __('Block Contents'))->setClass('mt-3 -mb-2');
                $col->addTextArea('contents', $guid)->setRows(20)->addData('tinymce')->addData('media', '1')->setValue($smartBlockTemplate);

            $col = $blockTemplate->addRow()->addClass('showHide w-full')->addColumn();
                $col->addLabel('teachersNotesLabel', __('Teacher\'s Notes'))->setClass('mt-3 -mb-2');
                $col->addTextArea('teachersNotes', $guid)->setRows(20)->addData('tinymce')->addData('media', '1');

        return $blockTemplate;
    }

    /**
     * Creates a fully-configured CustomBlocks input for Outcomes in the lesson planner.
     *
     * @param string $name
     * @param Session $session
     * @param string $pupilsightYearGroupIDList
     * @param string $pupilsightDepartmentID
     * @param bool $allowOutcomeEditing
     * @return OutputableInterface
     */
    public function createPlannerOutcomeBlocks($name, $session, $pupilsightYearGroupIDList = '', $pupilsightDepartmentID = '', $allowOutcomeEditing = false) : OutputableInterface
    {
        $outcomeSelector = $this->createSelectOutcome('addOutcome', $pupilsightYearGroupIDList, $pupilsightDepartmentID);
        $blockTemplate = $this->createOutcomeBlockTemplate($allowOutcomeEditing);

        // Create and initialize the Custom Blocks
        $customBlocks = $this->createCustomBlocks($name, $session)
            ->fromTemplate($blockTemplate)
            ->settings([
                'inputNameStrategy' => 'string',
                'addOnEvent'        => 'change',
                'preventDuplicates' => true,
                'sortable'          => true,
                'orderName'         => 'outcomeorder',
            ])
            ->placeholder(__('Key outcomes listed here...'))
            ->addToolInput($outcomeSelector)
            ->addBlockButton('showHide', __('Show/Hide'), 'plus.png');

        // Add predefined block data (for creating new blocks, triggered with the outcome selector)
        $data = ['pupilsightYearGroupIDList' => $pupilsightYearGroupIDList];
        $sql = "SELECT pupilsightOutcomeID as outcomepupilsightOutcomeID, pupilsightOutcome.name as outcometitle, category as outcomecategory, description as outcomecontents
                FROM pupilsightOutcome JOIN pupilsightYearGroup ON (FIND_IN_SET(pupilsightYearGroup.pupilsightYearGroupID, pupilsightOutcome.pupilsightYearGroupIDList))
                WHERE FIND_IN_SET(pupilsightYearGroup.pupilsightYearGroupID, :pupilsightYearGroupIDList)";
        $outcomeData = $this->pdo->select($sql, $data)->fetchAll();

        foreach ($outcomeData as $outcome) {
            $customBlocks->addPredefinedBlock($outcome['outcomepupilsightOutcomeID'], $outcome);
        }

        return $customBlocks;
    }

    /**
     * Creates a drop-down list of available outcomes by year group. Groups outcomes by school-wide and by department.
     *
     * @param string $name
     * @param string $pupilsightYearGroupIDList
     * @param string $pupilsightDepartmentID
     * @return OutputableInterface
     */
    public function createSelectOutcome($name, $pupilsightYearGroupIDList, $pupilsightDepartmentID) : OutputableInterface
    {
        // Get School Outcomes
        $data = ['pupilsightYearGroupIDList' => $pupilsightYearGroupIDList];
        $sql = "SELECT category AS groupBy, CONCAT('all ', category) as chainedTo, pupilsightOutcomeID AS value, pupilsightOutcome.name AS name
                FROM pupilsightOutcome
                JOIN pupilsightYearGroup ON (FIND_IN_SET(pupilsightYearGroup.pupilsightYearGroupID, pupilsightOutcome.pupilsightYearGroupIDList))
                WHERE active='Y' AND scope='School'
                AND FIND_IN_SET(pupilsightYearGroup.pupilsightYearGroupID, :pupilsightYearGroupIDList)
                GROUP BY pupilsightOutcome.pupilsightOutcomeID
                ORDER BY groupBy, name";

        // Get Departmental Outcomes
        $data2 = ['pupilsightYearGroupIDList' => $pupilsightYearGroupIDList, 'pupilsightDepartmentID' => $pupilsightDepartmentID];
        $sql2 = "SELECT CONCAT(pupilsightDepartment.name, ': ', category) AS groupBy, CONCAT('all ', category) as chainedTo, pupilsightOutcomeID AS value, pupilsightOutcome.name AS name
                FROM pupilsightOutcome
                JOIN pupilsightDepartment ON (pupilsightOutcome.pupilsightDepartmentID=pupilsightDepartment.pupilsightDepartmentID)
                JOIN pupilsightYearGroup ON (FIND_IN_SET(pupilsightYearGroup.pupilsightYearGroupID, pupilsightOutcome.pupilsightYearGroupIDList))
                WHERE active='Y' AND scope='Learning Area'
                AND pupilsightDepartment.pupilsightDepartmentID=:pupilsightDepartmentID
                AND FIND_IN_SET(pupilsightYearGroup.pupilsightYearGroupID, :pupilsightYearGroupIDList)
                GROUP BY pupilsightOutcome.pupilsightOutcomeID
                ORDER BY groupBy, pupilsightOutcome.name";

        $col = $this->createColumn($name.'Col')->setClass('');

        $col->addSelect($name)
            ->setClass('addBlock floatNone standardWidth')
            ->fromArray(['' => __('Choose an outcome to add it to this lesson')])
            ->fromArray([__('SCHOOL OUTCOMES') => []])
            ->fromQueryChained($this->pdo, $sql, $data, $name.'Filter', 'groupBy')
            ->fromArray([__('LEARNING AREAS') => []])
            ->fromQueryChained($this->pdo, $sql2, $data2, $name.'Filter', 'groupBy');

        // Get Categories by Year Group
        $data3 = ['pupilsightYearGroupIDList' => $pupilsightYearGroupIDList];
        $sql3 = "SELECT category as value, category as name
                FROM pupilsightOutcome
                JOIN pupilsightYearGroup ON (FIND_IN_SET(pupilsightYearGroup.pupilsightYearGroupID, pupilsightOutcome.pupilsightYearGroupIDList))
                WHERE active='Y' AND FIND_IN_SET(pupilsightYearGroup.pupilsightYearGroupID, :pupilsightYearGroupIDList)
                GROUP BY pupilsightOutcome.category";

        $col->addSelect($name.'Filter')
            ->setClass('floatNone standardWidth mt-px')
            ->fromArray(['all' => __('View All')])
            ->fromQuery($this->pdo, $sql3, $data3);

        return $col;
    }

    /**
     * Creates a template for displaying Outcomes in a CustomBlocks input.
     *
     * @param string $allowOutcomeEditing
     * @return OutputableInterface
     */
    public function createOutcomeBlockTemplate($allowOutcomeEditing) : OutputableInterface
    {
        $blockTemplate = $this->createTable()->setClass('blank w-full');
            $row = $blockTemplate->addRow();
            $row->addTextField('outcometitle')
                ->setClass('w-3/4 title readonly')
                ->readonly()
                ->placeholder(__('Outcome Name'))
                ->append('<input type="hidden" id="outcomepupilsightOutcomeID" name="outcomepupilsightOutcomeID" value="">');

            $row = $blockTemplate->addRow();
            $row->addTextField('outcomecategory')
                ->setClass('w-3/4 readonly mt-1')
                ->readonly();

            $col = $blockTemplate->addRow()->addClass('showHide fullWidth')->addColumn();
            if ($allowOutcomeEditing == 'Y') {
                $col->addTextArea('outcomecontents')->setRows(10)->addData('tinymce');
            } else {
                $col->addContent('')->wrap('<label for="outcomecontents" class="block pt-2">', '</label>')
                    ->append('<input type="hidden" id="outcomecontents" name="outcomecontents" value="">');
            }

        return $blockTemplate;
    }
}
