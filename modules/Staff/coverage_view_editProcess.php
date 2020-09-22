<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Domain\Staff\StaffCoverageGateway;
use Pupilsight\FileUploader;

require_once '../../pupilsight.php';

$pupilsightStaffCoverageID = $_POST['pupilsightStaffCoverageID'] ?? '';

$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Staff/coverage_view_edit.php&pupilsightStaffCoverageID='.$pupilsightStaffCoverageID;

if (isActionAccessible($guid, $connection2, '/modules/Staff/coverage_view_edit.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
    exit;
} else {
    // Proceed!
    $staffCoverageGateway = $container->get(StaffCoverageGateway::class);

    $type = $_POST['attachmentType'] ?? '';
    switch ($type) {
        case 'File': $content = $_POST['attachment'] ?? ''; break;
        case 'Text': $content = $_POST['text'] ?? ''; break;
        case 'Link': $content = $_POST['link'] ?? ''; break;
        default:     $content = '';
    }

    // Validate the required values are present
    if (empty($pupilsightStaffCoverageID)) {
        $URL .= '&return=error1';
        header("Location: {$URL}");
        exit;
    }

    // Validate the database relationships exist
    $coverage = $staffCoverageGateway->getByID($pupilsightStaffCoverageID);

    if (empty($coverage)) {
        $URL .= '&return=error2';
        header("Location: {$URL}");
        exit;
    }

    if ($coverage['pupilsightPersonID'] != $_SESSION[$guid]['pupilsightPersonID'] && $coverage['pupilsightPersonIDStatus'] != $_SESSION[$guid]['pupilsightPersonID']) {
        $URL .= '&return=error0';
        header("Location: {$URL}");
        return;
    }

    // File Upload
    if ($type == 'File' && !empty($_FILES['file'])) {
        // Upload the file, return the /uploads relative path
        $fileUploader = new FileUploader($pdo, $pupilsight->session);
        $content = $fileUploader->uploadFromPost($_FILES['file']);

        if (empty($content)) {
            $URL .= '&return=error3';
            header("Location: {$URL}");
            exit;
        }
    }

    // Update the database
    $updated = $staffCoverageGateway->update($pupilsightStaffCoverageID, [
        'notesStatus'       => $_POST['notesStatus'] ?? '',
        'attachmentType'    => $type,
        'attachmentContent' => $content,
    ]);

    $URL .= !$updated
        ? "&return=error2"
        : "&return=success0";

    header("Location: {$URL}");
}
