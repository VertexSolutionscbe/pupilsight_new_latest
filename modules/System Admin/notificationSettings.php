<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Tables\DataTable;
use Pupilsight\Services\Format;
use Pupilsight\Domain\System\NotificationGateway;

if (isActionAccessible($guid, $connection2, '/modules/System Admin/notificationSettings.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $page->breadcrumbs->add(__('Notification Settings'));

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

    echo '<h3>';
    echo __('Notification Events');
    echo '</h3>';

    echo '<p>';
    echo __('This section allows you to manage system-wide notifications. When a notification event occurs, any users subscribed to that event will receive a notification. Each event below can optionally be turned off to prevent all notifications of that type.');
    echo '</p>';

    $gateway = new NotificationGateway($pdo);
    $result = $gateway->selectAllNotificationEvents();

    $nameFormat = function ($row) use ($guid) {
        $output = __($row['event']);
        if ($row['type'] == 'CLI') {
            $output .= " <img title='".__('This is a CLI notification event. It will only run if the corresponding CLI script has been setup on the server.')."' src='./themes/".$_SESSION[$guid]['pupilsightThemeName']."/img/run.png'/ style='float: right; width:20px; height:20px;margin: -4px 0 -4px 4px;opacity: 0.6;'>";
        }
        return $output;
    };

    $table = DataTable::create('notificationEvents');

    $table->modifyRows(function($notification, $row) {
        if ($notification['active'] == 'N') $row->addClass('error');
        return $row;
    });

    $table->addColumn('moduleName', __('Module'))->translatable();
    $table->addColumn('event', __('Name'))->format($nameFormat);
    $table->addColumn('listenerCount', __('Subscribers'));
    $table->addColumn('active', __('Active'))->format(Format::using('yesNo', 'active'));

    $actions = $table->addActionColumn()->addParam('pupilsightNotificationEventID');
    $actions->addAction('edit', __('Edit'))
            ->setURL('/modules/System Admin/notificationSettings_manage_edit.php');

    echo $table->render($result->toDataSet());
}
