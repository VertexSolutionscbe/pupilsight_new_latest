<?php
/*
Pupilsight, Flexible & Open School System
*/

namespace Pupilsight\Domain\Traits;

/**
 * Provides common filter and row highlight logic for tables that deal with user role and status.
 */
trait SharedUserLogic
{
    protected function getSharedUserFilterRules()
    {
        return [
            'role' => function ($query, $roleCategory) {
                return $query
                    ->where('pupilsightRole.category = :roleCategory')
                    ->bindValue('roleCategory', ucfirst($roleCategory));
            },

            'status' => function ($query, $status) {
                return $query
                    ->where('pupilsightPerson.status = :status')
                    ->bindValue('status', ucfirst($status));
            },

            'date' => function ($query, $dateType) {
                return $query
                    ->where(($dateType == 'starting')
                        ? '(pupilsightPerson.dateStart IS NOT NULL AND pupilsightPerson.dateStart >= :today)'
                        : '(pupilsightPerson.dateEnd IS NOT NULL AND pupilsightPerson.dateEnd <= :today)')
                    ->bindValue('today', date('Y-m-d'));
            },
        ];
    }

    public function getSharedUserRowHighlighter()
    {
        return function($person, $row) {
            $highlight = '';
            if (!empty($person['status']) && $person['status'] != 'Full') $highlight = 'error';
            if (!empty($person['roleCategory']) && $person['roleCategory'] == 'Student') {
                if (!(empty($person['dateStart']) || $person['dateStart'] <= date('Y-m-d'))) $highlight = 'error';
                if (!(empty($person['dateEnd'] ) || $person['dateEnd'] >= date('Y-m-d'))) $highlight = 'error';
                if (empty($person['pupilsightStudentEnrolmentID'])) $highlight = 'error';
            }
            return $row->addClass($highlight);
        };
    }
}
