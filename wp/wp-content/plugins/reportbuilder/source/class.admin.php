<?php
/**
 * @author Alexander Gilmanov
 * @since January 2016
 */

namespace WDTReportBuilder;

/**
 * Class Admin
 * Controller for the WP Admin backend area of the plugin
 * @package WDTReportBuilder
 */
class Admin {

    /**
     * Initializes the hooks
     */
    public static function init() {
        // Download preview action
        add_action('wp_ajax_report_builder_download_preview', array('WDTReportBuilder\Admin', 'downloadReport'));
        // Save report
        add_action('wp_ajax_report_builder_save_report', array('WDTReportBuilder\Admin', 'saveReport'));
        // Get report data
        add_action('wp_ajax_report_builder_get_report_data', array('WDTReportBuilder\Admin', 'getReportData'));
        // Add plugin for MCE with Report Builder button
        add_filter('mce_external_plugins', array('WDTReportBuilder\Admin', 'addMceButtons'));
        // Register the buttons
        add_filter('mce_buttons', array('WDTReportBuilder\Admin', 'registerMceButtons'));
        // Action to list all reports
        add_action('wp_ajax_report_builder_get_all_reports', array('WDTReportBuilder\Admin', 'getAllReportsJson'));
        // Action to get shortcodes form for Visual Editor
        add_action('wp_ajax_report_builder_get_report_shortcodes_form', array('WDTReportBuilder\Admin', 'getReportShortcodesForm'));
        // Enqueue admin scripts and styles
        add_action('admin_enqueue_scripts', array('WDTReportBuilder\Admin', 'wdtrbAdminEnqueue'));
    }

    /**
     * Adds the admin menu
     */
    public static function wdtrbAdminMenu() {
        add_menu_page(
            __('Report Builder', 'wpdatatables'),
            __('Report Builder', 'wpdatatables'),
            'manage_options',
            'wpdatareports',
            array('WDTReportBuilder\Admin', 'wdtrbBrowseReports'),
            'none'
        );
        add_submenu_page(
            'wpdatareports',
            __('Browse reports', 'wpdatatables'),
            __('Browse reports', 'wpdatatables'),
            'manage_options',
            'wpdatareports',
            array('WDTReportBuilder\Admin', 'wdtrbBrowseReports')
        );
        add_submenu_page(
            'wpdatareports',
            __('Create a new report', 'wpdatatables'),
            __('Create a new report', 'wpdatatables'),
            'manage_options',
            'wpdatareports-wizard',
            array('WDTReportBuilder\Admin', 'wdtrbReport')
        );
    }

    /**
     * Enqueue JS and CSS files for the Admin pages
     * @param $hook
     */
    public static function wdtrbAdminEnqueue($hook) {
        add_filter('admin_body_class', 'wdtAddBodyClass');

        wp_register_script('wdt-rb-doc-js', WDT_RB_ROOT_URL . 'assets/js/admin/doc.js', array('jquery', 'wdt-common'), WDT_RB_VERSION, true);
        wp_register_script('wdt-common', WDT_ROOT_URL . 'assets/js/wpdatatables/admin/common.js', array('jquery'), WDT_RB_VERSION, true);
        wp_register_script('wdt-rb-js-render', WDT_JS_PATH . 'jsrender/jsrender.min.js', array('jquery'), WDT_RB_VERSION, true);
        wp_register_script('wdt-rb-func', WDT_RB_ROOT_URL . 'assets/js/common/report_builder_funcs.js', array('jquery'), WDT_RB_VERSION, true);
        wp_register_script('wdt-rb-jq-redirect', WDT_RB_ROOT_URL . 'assets/js/common/jquery.redirect.js', array('jquery'), WDT_RB_VERSION, true);

        // Add icon on all pages
        wp_enqueue_style('report_builder_icon', WDT_RB_ROOT_URL . 'assets/css/admin/report_builder_icon.css',array(), WDT_RB_VERSION);

        wp_enqueue_script('media-upload');

        switch ($hook) {
            case 'toplevel_page_wpdatareports':
                self::wdtrbBrowseReportsEnqueue();
                break;
            case 'report-builder_page_wpdatareports-wizard':
                self::wdtrbReportEnqueue();
                break;
        }
    }

    /**
     * Enqueue JS and CSS files for the "Browse reports" Page
     */
    public static function wdtrbBrowseReportsEnqueue() {
        \WDTTools::wdtUIKitEnqueue();

        wp_enqueue_style('wdt-browse-css', WDT_CSS_PATH . 'admin/browse.css',array(), WDT_RB_VERSION);
        wp_enqueue_style('wrp-browse-css', WDT_RB_ROOT_URL . 'assets/css/admin/browse.css',array(), WDT_RB_VERSION);

        wp_enqueue_script('wdt-common');
        wp_enqueue_script('wdt-rb-js-render');
        wp_enqueue_script('wdt-rb-func');
        wp_enqueue_script('wdt-rb-jq-redirect');
        wp_enqueue_script('wdt-rb-doc-js');
        wp_enqueue_script('wdt-rb-browse', WDT_RB_ROOT_URL . 'assets/js/admin/reports_browse.js', array('jquery'), WDT_RB_VERSION, true);

        wp_localize_script('wdt-rb-browse', 'wdt_rb_vars', self::getJSVars());
    }

    public static function wdtrbReportEnqueue() {
        \WDTTools::wdtUIKitEnqueue();

        wp_enqueue_style('wdt-rb-wizard', WDT_RB_ROOT_URL . '/assets/css/admin/wizard.css',array(), WDT_RB_VERSION);

        wp_enqueue_script('wdt-common');
        wp_enqueue_script('wdt-rb-js-render');
        wp_enqueue_script('wdt-rb-func');
        wp_enqueue_script('wdt-rb-jq-redirect');
        wp_enqueue_script('wdt-rb-doc-js');
        wp_enqueue_script('wdt-rb-wizard', WDT_RB_ROOT_URL . '/assets/js/admin/report_builder_wizard.js', array('jquery'), WDT_RB_VERSION, true);

        wp_localize_script('wdt-rb-wizard', 'wdt_rb_strings', self::translationStrings());
        wp_localize_script('wdt-rb-wizard', 'wdt_rb_vars', self::getJSVars());
    }

    /**
     * Browse existing reports
     */
    public static function wdtrbBrowseReports() {
        // If a delete is requested perform this action
        $action = '';
        if (isset($_REQUEST['action']) && -1 != $_REQUEST['action']) {
            $action = $_REQUEST['action'];
        }
        if (isset($_REQUEST['action2']) && -1 != $_REQUEST['action2']) {
            $action = $_REQUEST['action2'];
        }

        if ($action === 'delete') {
            if (!is_array($_REQUEST['id'])) {
                self::deleteReport((int)$_REQUEST['id']);
            } else {
                self::deleteReports($_REQUEST['id']);
            }
        }

        include(WDT_RB_ROOT_PATH . '/source/class.browsereports.php');
        $browseReportsTable = new BrowseReportsTable();
        $browseReportsTable->prepare_items();
        ob_start();
        $browseReportsTable->display();
        /** @noinspection PhpUnusedLocalVariableInspection */
        $tableHTML = ob_get_contents();
        ob_end_clean();

        // Page template
        include(WDT_RB_ROOT_PATH . '/templates/browse.tpl.php');
    }

    /**
     * Add a new report
     */
    public static function wdtrbReport() {
        if (isset($_GET['id'])) {
            $reportId = (int)$_GET['id'];
            $report = new ReportBuilder();
            $report->setId($reportId);
            $report->loadFromDB();

            $reportConfig = $report->getReportConfig();
            // Replace the path to template with URL to avoid exposing server paths
            $reportConfig['templateFile'] = ReportBuilder::pathToUrl($reportConfig['templateFile']);
            // Send the config to front-end
            wp_localize_script('wdt-rb-wizard', 'wdt_report_config_db', $reportConfig);
        }

        include WDT_RB_ROOT_PATH . '/templates/wizard.tpl.php';
    }

    /**
     * Returns the strings for front-end
     */
    public static function translationStrings() {
        return array(
            'choose_file' => __('Choose the template file', 'wpdatatables'),
            'error' => __('Error!', 'wpdatatables'),
            'fileUploadEmptyFile' => __('Please upload or choose a template from Media Library!', 'wpdatatables'),
            'reportNameEmpty' => __('Report name can not be empty! Please provide a name for your report.', 'wpdatatables')
        );
    }

    /**
     * Returns the vars that are needed on the front-end
     */
    public static function getJSVars() {
        return array(
            'preloader_path' => WDT_RB_ROOT_URL . 'assets/css',
            'browse_url' => get_admin_url(null, 'admin.php?page=wpdatareports'),
            'siteUrl' => menu_page_url('wpdatareports', false)
        );
    }

    /**
     * Donwload the report preview
     */
    public static function downloadReport() {
        require_once(WDT_RB_ROOT_PATH . '/source/class.reportbuilder.php');
        setcookie('wdtDownloadToken', $_POST['wdtDownloadToken']);
        $reportBuilder = new \WDTReportBuilder\ReportBuilder();
        $reportBuilder->initReportConfig($_POST['wdtReportConfig']);
        $reportBuilder->build();
        exit();
    }

    /**
     * Save the report
     */
    public static function saveReport() {
        require_once(WDT_RB_ROOT_PATH . '/source/class.reportbuilder.php');
        $reportBuilder = new \WDTReportBuilder\ReportBuilder();
        $reportBuilder->initReportConfig($_POST['wdtReportConfig']);
        $reportBuilder->save();
        if ($reportBuilder->getId() !== null) {
            echo json_encode(array('result' => 'success', 'id' => $reportBuilder->getId()));
        } else {
            global $wpdb;
            echo json_encode(array('result' => 'error', 'message' => $wpdb->last_error));
        }
        exit();

    }

    /**
     * Get all the reports for the browse page
     */
    public static function getAllReports($per_page = 0) {
        global $wpdb;

        $query = "SELECT * FROM " . $wpdb->prefix . "wpdatareports ";

        if (isset($_REQUEST['s'])) {
            $query .= " WHERE name LIKE '%" . sanitize_text_field($_REQUEST['s']) . "%' ";
        }

        if (isset($_REQUEST['orderby'])) {
            if (in_array($_REQUEST['orderby'], array('id', 'name'))) {
                $query .= " ORDER BY " . sanitize_text_field($_GET['orderby']);
                if ($_REQUEST['order'] == 'desc') {
                    $query .= " DESC";
                } else {
                    $query .= " ASC";
                }
            }
        } else {
            $query .= " ORDER BY id ASC ";
        }

        if (isset($_REQUEST['paged'])) {
            $paged = (int)$_REQUEST['paged'];
        } else {
            $paged = 1;
        }

        if ($per_page) {
            $query .= " LIMIT " . ($paged - 1) * $per_page . ", " . $per_page;
        }

        $all_reports = $wpdb->get_results($query, ARRAY_A);

        $all_reports = apply_filters('reportbuilder_filter_browse_reports', $all_reports);

        return $all_reports;

    }

    /**
     * Echo all reports in JSON
     */
    public static function getAllReportsJson($per_page = 0) {
        echo json_encode(self::getAllReports($per_page));
        exit();
    }

    /**
     * Get the total count of reports
     */
    public static function getReportCount() {
        global $wpdb;
        $count = (int)$wpdb->get_var("SELECT count(id) FROM " . $wpdb->prefix . "wpdatareports");
        return $count;
    }

    /**
     * Delete a report
     * @param int $id ID of the report to delete
     */
    public static function deleteReport($id) {
        global $wpdb;
        if (!empty($id)) {
            $wpdb->delete(
                $wpdb->prefix . "wpdatareports",
                array(
                    'id' => $id
                ),
                array(
                    '%d'
                )
            );
        }
    }

    /**
     * Delete multiple reports
     * @param array $reportIds
     */
    public static function deleteReports($reportIds) {
        global $wpdb;
        $params = array();
        $query_vars = array();
        $query = "DELETE FROM " . $wpdb->prefix . "wpdatareports WHERE id IN (";
        foreach ($reportIds as $reportId) {
            $params[] = (int)$reportId;
            $query_vars[] = '%d';
        }
        $query .= implode(', ', $query_vars) . ')';
        $wpdb->query(
            $wpdb->prepare(
                $query,
                $params
            )
        );
    }

    /**
     * Plugin for Report Builder in WP MCE editor
     */
    public static function addMceButtons($plugin_array) {
        $plugin_array['reportbuilder'] = WDT_RB_ROOT_URL . 'assets/js/admin/mce.js';
        return $plugin_array;
    }

    /**
     * Button for Report Builder in WP MCE editor
     */
    public static function registerMceButtons($buttons) {
        array_push($buttons, 'reportbuilder');
        return $buttons;
    }

    /**
     * Get the report data in JSON
     */
    public static function getReportData() {
        $id = (int)$_REQUEST['id'];
        if (!empty($id)) {
            $reportBuilder = new ReportBuilder();
            $reportBuilder->setId($id);
            $reportBuilder->loadFromDB();
            echo json_encode($reportBuilder->getReportConfig());
        }
        exit();
    }

    /**
     * Get the shortcodes form template
     */
    public static function getReportShortcodesForm() {
        $id = (int)$_REQUEST['id'];
        if (!empty($id)) {
            $reportBuilder = new ReportBuilder();
            $reportBuilder->setId($id);
            $reportBuilder->loadFromDB();
            include WDT_RB_ROOT_PATH . 'templates/shortcodes_form.tpl.php';
            exit();
        }
    }
}