<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Domain\Messenger\GroupGateway;

// Pupilsight system-wide include
require_once '../../pupilsight.php';

if (isActionAccessible($guid, $connection2, '/modules/User Admin/staffSettings.php') == false) {
    // Access denied
    die(__('Your request failed because you do not have access to this action.') );
} else {
    $searchTerm = $_REQUEST['q'] ?? '';

    // Cancel out early for empty searches
    if (empty($searchTerm)) die('[]');

    // Search
    $groupGateway = $container->get(GroupGateway::class);
    $criteria = $groupGateway->newQueryCriteria()
        ->searchBy($groupGateway->getSearchableColumns(), $searchTerm)
        ->sortBy('name');

    $results = $groupGateway->queryGroups($criteria, $pupilsight->session->get('pupilsightSchoolYearID'))->toArray();

    $list = array_map(function ($token) {
        return [
            'id'       => $token['pupilsightGroupID'],
            'name'     => $token['name'],
        ];
    }, $results);

    // Output the json
    echo json_encode($list);
}
