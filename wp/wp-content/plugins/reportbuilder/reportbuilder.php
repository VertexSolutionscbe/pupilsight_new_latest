<?php

namespace WDTReportBuilder;

use WDTTools;
use WP_Error;

/**
 * @package Report Builder for wpDataTables
 * @version 1.2
 */
/*
Plugin Name: Report Builder for wpDataTables
Plugin URI: http://wpreportbuilder.com
Description: Generate DOCX and XLSX with realtime data from your wpDataTables
Version: 1.2
Author: TMS-Plugins
Author URI: https://tms-outsource.com/
Text Domain: wpdatatables
Domain Path: /languages
*/

add_action('plugins_loaded', array('WDTReportBuilder\Plugin', 'init'), 10);

define('WDT_RB_ROOT_PATH', plugin_dir_path(__FILE__)); // full path to the Report Builder root directory
define('WDT_RB_ROOT_URL', plugin_dir_url(__FILE__)); // URL of Report Builder plugin
define('WDT_RB_VERSION', '1.2'); // Current version of Report Builder plugin
define('WDT_RB_VERSION_TO_CHECK', '3.0');

// Activation hook to create the DB tables
register_activation_hook(__FILE__, array('WDTReportBuilder\Plugin', 'activationHook'));

// Uninstall hook to delete the reports
register_uninstall_hook(__FILE__, array('WDTReportBuilder\Plugin', 'uninstallHook'));

// Shortcode handler
add_shortcode('reportbuilder', array('WDTReportBuilder\Plugin', 'shortcodeHandler'));

// Add Report Builder activation setting
add_action('wdt_add_activation', array('WDTReportBuilder\Plugin', 'addReportBuilderActivation'));

// Enqueue Report Builder add-on files on back-end settings page
add_action('wdt_enqueue_on_settings_page', array('WDTReportBuilder\Plugin', 'wdtReportEnqueueBackendSettings'));

// Check auto update
add_filter('pre_set_site_transient_update_plugins', array('WDTReportBuilder\Plugin', 'wdtCheckUpdateReport'));

// Check plugin info
add_filter('plugins_api', array('WDTReportBuilder\Plugin', 'wdtCheckInfoReport'), 10, 3);

// Add a message for unavailable auto update if plugin is not activated
add_action('in_plugin_update_message-' . plugin_basename(__FILE__), array('WDTReportBuilder\Plugin', 'addMessageOnPluginsPageReport'));

// Add error message on plugin update if plugin is not activated
add_filter('upgrader_pre_download', array('WDTReportBuilder\Plugin', 'addMessageOnUpdateReport'), 10, 4);

// Create reports on every new site (multisite)
add_action('wpmu_new_blog', array('WDTReportBuilder\Plugin', 'rbOnCreateSiteOnMultisiteNetwork'));

// Delete table on site delete (multisite)
add_filter('wpmu_drop_tables',  array('WDTReportBuilder\Plugin', 'rbOnDeleteSiteOnMultisiteNetwork'));

class Plugin
{

    public static $initialized = false;

    /**
     * Instantiates the class
     */
    public static function init()
    {
        // Check if wpDataTables is installed
        if (!defined('WDT_ROOT_PATH')) {
            add_action('admin_notices', array('WDTReportBuilder\Plugin', 'wdtNotInstalled'));
        } elseif (version_compare(WDT_CURRENT_VERSION, WDT_RB_VERSION_TO_CHECK) < 0) {
            add_action('admin_notices', array('WDTReportBuilder\Plugin', 'wdtRequiredVersionMissing'));
        } else {
            self::$initialized = true;
            // Initialize the core class
            require_once(WDT_RB_ROOT_PATH . '/source/class.reportbuilder.php');

            // Initialize the download action handler
            add_action('wp_ajax_report_builder_download_report', array('WDTReportBuilder\Plugin', 'downloadReport'));
            add_action('wp_ajax_nopriv_report_builder_download_report', array('WDTReportBuilder\Plugin', 'downloadReport'));

            // Initialize the admin part for back-end area
            if (is_admin()) {
                // Init PHPOffice
                self::initPhpOffice();
                // Init the admin zone
                require_once(WDT_RB_ROOT_PATH . '/source/class.admin.php');
                \WDTReportBuilder\Admin::init();
                add_action('admin_menu', array('WDTReportBuilder\Admin', 'wdtrbAdminMenu'), 10);

                // Optional Visual Composer integration
                if (function_exists('vc_map')) {
                    require_once(WDT_RB_ROOT_PATH . '/source/class.admin.php');
                    include(WDT_RB_ROOT_PATH . '/source/class.vcintegration.php');
                }

            }
        }

    }

    /**
     * Show message if wpDataTables is not installed
     */
    public static function wdtNotInstalled()
    {
        $message = __('Report Builder is an add-on for wpDataTables - please install and activate wpDataTables to be able to generate reports!', 'wpdatatables');
        echo "<div class=\"error\"><p>{$message}</p></div>";
    }

    /**
     * Show message if required wpDataTables version is not installed
     */
    public static function wdtRequiredVersionMissing()
    {
        $message = __('Report Builder add-on requires wpDataTables version ' . WDT_RB_VERSION_TO_CHECK . '. Please update wpDataTables plugin to be able to use it!', 'wpdatatables');
        echo "<div class=\"error\"><p>{$message}</p></div>";
    }

    /**
     * Initialize the PHPOffice Autoloaders
     * (only on necessary pages not to cause additional init)
     */
    public static function initPhpOffice()
    {
        require_once WDT_RB_ROOT_PATH . '/lib/autoload.php';
    }
    /**
     * Create reports on every new site (multisite)
     * @param $blogId
     */
    public static function rbOnCreateSiteOnMultisiteNetwork($blogId) {
        if (is_plugin_active_for_network('reportbuilder/reportbuilder.php')) {
            switch_to_blog($blogId);
            self::rbActivationCreateTables();
            restore_current_blog();
        }
    }

    /**
     * Delete table on site delete (multisite)
     * @param $tables
     * @return array
     */
    public static function rbOnDeleteSiteOnMultisiteNetwork($tables) {
        global $wpdb;
        $tables[] = $wpdb->prefix . 'wpdatareports';

        return $tables;
    }

    /**
     * Activation hook
     * @param $networkWide
     */
    public static function activationHook($networkWide) {
        global $wpdb;

        if (function_exists('is_multisite') && is_multisite()) {
            //check if it is network activation if so run the activation function for each id
            if ($networkWide) {
                $oldBlog = $wpdb->blogid;
                //Get all blog ids
                $blogIds = $wpdb->get_col("SELECT blog_id FROM $wpdb->blogs");

                foreach ($blogIds as $blogId) {
                    switch_to_blog($blogId);
                    //Create database table for reports if not exists
                    self::rbActivationCreateTables();
                }
                switch_to_blog($oldBlog);

                return;
            }
        }
        //Create database table for reports if not exists
        self::rbActivationCreateTables();
    }

    /**
     *
     * This method generates the MySQL table needed to store the report metadata
     */
    public static function rbActivationCreateTables()
    {
        global $wpdb;
        $reports_table_name = $wpdb->prefix . 'wpdatareports';
        $reports_sql = "CREATE TABLE {$reports_table_name} (
						id INT( 11 ) NOT NULL AUTO_INCREMENT,
						table_id int(11) NULL,
						name varchar(255) NOT NULL,
                        report_config TEXT NOT NULL default '',
						UNIQUE KEY id (id)
						) DEFAULT CHARSET=utf8 COLLATE utf8_general_ci";
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($reports_sql);
    }

    /**
     * Shortcode handler
     */
    public static function shortcodeHandler($atts, $content = null)
    {
        extract(
            shortcode_atts(
                array(
                    'id' => '0',
                    'element' => 'button',
                    'type' => 'download',
                    'text' => 'Download',
                    'class' => 'btn',
                    'name' => '',
                    'default' => ''
                ),
                $atts
            )
        );

        if (!$id) {
            return '';
        }

        $reportBuilder = new \WDTReportBuilder\ReportBuilder();
        $reportBuilder->setId($id);
        if (!$reportBuilder->loadFromDB()) {
            return __('Report with ID ' . $id . ' does not exist', 'wpdatatables');
        }

        // Enqueue the front-end JS
        wp_enqueue_script('reportbuilder', WDT_RB_ROOT_URL . 'assets/js/frontend/report_builder.js', array('jquery'), WDT_RB_VERSION);
        wp_enqueue_script('reportbuilder_funcs', WDT_RB_ROOT_URL . 'assets/js/common/report_builder_funcs.js', array('jquery'), WDT_RB_VERSION);
        wp_enqueue_script('jquery_redirect', WDT_RB_ROOT_URL . 'assets/js/common/jquery.redirect.js', array('jquery'), WDT_RB_VERSION);

        // Enqueue Dashicons as we're using for preloader
        wp_enqueue_style('dashicons', get_stylesheet_uri(), 'dashicons');

        // Front-end styles
        wp_enqueue_style('reportbuilder', WDT_RB_ROOT_URL . 'assets/css/front/report_builder.css', array(), WDT_RB_VERSION);

        // Pass the needed variables to JS
        wp_localize_script(
            'reportbuilder',
            'reportbuilderobj',
            array()
        );

        // Report-specific data
        wp_localize_script(
            'reportbuilder',
            'reportbuilder_' . $id,
            array(
                'follow_filtering' => (int)$reportBuilder->getFollowFiltering(),
                'ajaxurl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('build_report_' . $id)
            )
        );

        switch ($element) {
            case 'varInput';
                return $reportBuilder->renderInput($name, $text, $default, $class);
                break;
            case 'button';
                return $reportBuilder->renderButton($type, $text, $class);
                break;
        }

    }

    /**
     * Download report handler
     */
    public static function downloadReport()
    {

        setcookie('wdtDownloadToken', $_POST['wdtDownloadToken'], time() + 500, '/', $_SERVER['HTTP_HOST']);
        $reportId = intval($_POST['wdtReportConfig']['id']);

        // Verify nonce in front-end area to avoid abusing
        if (is_admin() || wp_verify_nonce($_POST['nonce'], 'build_report_' . $reportId)) {

            $reportBuilder = new \WDTReportBuilder\ReportBuilder();
            $reportBuilder->setId($reportId);
            $reportBuilder->loadFromDB();
            if (!empty($_POST['wdtReportConfig']['additionalVars'])) {
                foreach ($_POST['wdtReportConfig']['additionalVars'] as $additionalVar) {
                    $reportBuilder->setAdditionalVar(
                        sanitize_text_field(urldecode($additionalVar['name'])),
                        sanitize_text_field(urldecode($additionalVar['value']))
                    );
                }
            }
            if (!empty($_POST['wdtReportConfig']['filteredData'])) {
                $reportBuilder->setFilteredData(
                    json_decode(
                        urldecode(
                            stripslashes_deep($_POST['wdtReportConfig']['filteredData'])
                        )
                    )
                );
            }
            if (!empty($_POST['downloadType'])) {
                if (sanitize_text_field($_POST['downloadType']) == 'save') {
                    $reportBuilder->setFileHandling('save');
                }
            }
            $reportBuilder->build();
        }

        exit();

    }

    /**
     * Uninstall hook
     */
    public static function uninstallHook()
    {
        if (function_exists('is_multisite') && is_multisite()) {
            global $wpdb;
            $oldBlog = $wpdb->blogid;
            //Get all blog ids
            $blogIds = $wpdb->get_col("SELECT blog_id FROM $wpdb->blogs");
            foreach ($blogIds as $blogId) {
                switch_to_blog($blogId);
                self::rbUninstallDelete();
            }
            switch_to_blog($oldBlog);
        } else {
            self::rbUninstallDelete();
        }
    }

    /**
     * Delete reports table from datatabase after uninstall
     */
    public static function rbUninstallDelete()
    {
        global $wpdb;
        $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}wpdatareports");
    }


    /**
     * Add Report Builder activation on wpDataTables settings page
     */
    public static function addReportBuilderActivation()
    {
        ob_start();
        include WDT_RB_ROOT_PATH . 'templates/activation.inc.php';
        $activation = ob_get_contents();
        ob_end_clean();

        echo $activation;
    }

    /**
     * Enqueue Report Builder add-on files on back-end settings page
     */
    public static function wdtReportEnqueueBackendSettings()
    {
        if (self::$initialized) {
            wp_enqueue_script(
                'wdt-rb-settings',
                WDT_RB_ROOT_URL . 'assets/js/admin/wdt.rb.settings.js',
                array(),
                WDT_RB_VERSION,
                true
            );
        }
    }

    /**
     * @param $transient
     *
     * @return mixed
     */
    public static function wdtCheckUpdateReport($transient)
    {

        if (class_exists('WDTTools')) {
            $pluginSlug = plugin_basename(__FILE__);

            if (empty($transient->checked)) {
                return $transient;
            }

            $purchaseCode = get_option('wdtPurchaseCodeStoreReport');

            $envatoTokenEmail = get_option('wdtEnvatoTokenEmailReport');

            // Get the remote info
            $remoteInformation = WDTTools::getRemoteInformation('reportbuilder', $purchaseCode, $envatoTokenEmail);

            // If a newer version is available, add the update
            if ($remoteInformation && version_compare(WDT_RB_VERSION, $remoteInformation->new_version, '<')) {
                $remoteInformation->package = $remoteInformation->download_link;
                $transient->response[$pluginSlug] = $remoteInformation;
            }
        }

        return $transient;
    }

    /**
     * @param $response
     * @param $action
     * @param $args
     *
     * @return bool|mixed
     */
    public static function wdtCheckInfoReport($response, $action, $args)
    {

        if (class_exists('WDTTools')) {

            $pluginSlug = plugin_basename(__FILE__);

            if ('plugin_information' !== $action) {
                return $response;
            }

            if (empty($args->slug)) {
                return $response;
            }

            $purchaseCode = get_option('wdtPurchaseCodeStoreReport');

            $envatoTokenEmail = get_option('wdtEnvatoTokenEmailReport');

            if ($args->slug === $pluginSlug) {
                return WDTTools::getRemoteInformation('reportbuilder', $purchaseCode, $envatoTokenEmail);
            }
        }

        return $response;
    }


    public static function addMessageOnPluginsPageReport()
    {
        /** @var bool $activated */
        $activated = get_option('wdtActivatedReport');

        /** @var string $url */
        $url = get_site_url() . '/wp-admin/admin.php?page=wpdatatables-settings&activeTab=activation';

        /** @var string $redirect */
        $redirect = '<a href="' . $url . '" target="_blank">' . __('settings', 'wpdatatables') . '</a>';

        if (!$activated) {
            echo sprintf(' ' . __('To receive automatic updates license activation is required. Please visit %s to activate Report Builder for wpDataTables.', 'wpdatatables'), $redirect);
        }
    }

    public static function addMessageOnUpdateReport($reply, $package, $updater)
    {
        if (isset($updater->skin->plugin_info['Name']) && $updater->skin->plugin_info['Name'] === get_plugin_data( __FILE__ )['Name']) {
            /** @var string $url */
            $url = get_site_url() . '/wp-admin/admin.php?page=wpdatatables-settings&activeTab=activation';

            /** @var string $redirect */
            $redirect = '<a href="' . $url . '" target="_blank">' . __('settings', 'wpdatatables') . '</a>';

            if (!$package) {
                return new WP_Error(
                    'wpdatatables_report_not_activated',
                    sprintf(' ' . __('To receive automatic updates license activation is required. Please visit %s to activate Report Builder for wpDataTables.', 'wpdatatables'), $redirect)
                );
            }

            return $reply;
        }

        return $reply;
    }
}
