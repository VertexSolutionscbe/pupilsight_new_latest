<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Services\Format;
use Pupilsight\Domain\Staff\StaffGateway;

// Pupilsight system-wide include
require_once '../../pupilsight.php';

if (isActionAccessible($guid, $connection2, '/modules/Staff/staff_view.php') == false) {
    // Access denied
    die(__('Your request failed because you do not have access to this action.'));
} else {
    $searchTerm = $_REQUEST['q'] ?? '';

    // Allow for * as wildcard (as well as %)
    $searchTerm = str_replace('*', '%', $searchTerm);

    // Cancel out early for empty searches
    if (empty($searchTerm)) die('[]');

    // Search
    $staffGateway = $container->get(StaffGateway::class);
    $criteria = $staffGateway->newQueryCriteria()
        ->searchBy($staffGateway->getSearchableColumns(), $searchTerm)
        ->sortBy(['preferredName', 'surname']);

    $results = $staffGateway->queryAllStaff($criteria)->toArray();

    $absoluteURL = $pupilsight->session->get('absoluteURL');
    $list = array_map(function ($token) use ($absoluteURL) {
        return [
            'id'       => $token['pupilsightPersonID'],
            'name'     => Format::name('', $token['preferredName'], $token['surname'], 'Staff', false, true),
            'jobTitle' => !empty($token['jobTitle']) ? $token['jobTitle'] : $token['type'],
            'image'    => $absoluteURL.'/'.$token['image_240'],
        ];
    }, $results);

    // Output the json
    echo json_encode($list);
}
