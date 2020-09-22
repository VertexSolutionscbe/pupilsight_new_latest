<?php
/**
 * Controller for Visual Composer Integration
 * @author Alexander Gilmanov
 */

namespace WDTReportBuilder;

class VCIntegration
{
    /**
     * Returns all reports for the dropdown
     */
    public static function getAllReports(){
        $all_reports = \WDTReportBuilder\Admin::getAllReports();
        $all_reports_sorted = array( __( 'Choose...', 'wpdatatables' ) => '' );
        foreach( $all_reports as $report ){
            $all_reports_sorted[ $report['name'] ] = $report['id'];
        }
        return $all_reports_sorted;
    }

    public static function initReportBuilderButton(){
        vc_map(
            array(
                'name' => __( 'Report Builder', 'wpdatatables' ),
                'base' => 'reportbuilder',
                'description' => __('DOCX and XLSX Report Builder buttons and inputs','wpdatatables'),
                'category' => __('Content'),
                'icon' => plugin_dir_url( dirname( __FILE__ ) ).'/assets/img/vc-icon.png',
                'admin_enqueue_js' => array(
                    plugin_dir_url( dirname( __FILE__ ) ).'assets/js/admin/vc.js'
                ),
                'front_enqueue_js' => array(
                    plugin_dir_url( dirname( __FILE__ ) ).'assets/js/admin/vc.js'
                ),
                'js_view' => 'reportBuilderView',
                'params' => array(
                    array(
                        'type' => 'dropdown',
                        'class' => '',
                        'heading' => __('Report', 'wpdatatables'),
                        'admin_label' => true,
                        'param_name' => 'id',
                        'value' => self::getAllReports(),
                        'description' => __('Choose one of the reports from a dropdown', 'wpdatatables'),
                        'save_always' => true
                    ),
                    array(
                        'type' => 'dropdown',
                        'class' => '',
                        'heading' => __('Element type', 'wpdatatables'),
                        'admin_label' => true,
                        'dependency' => array(
                            'element' => 'id',
                            'not_empty' => true
                         ),
                        'param_name' => 'element',
                        'value' => array( 'Button' => 'button', 'Input' => 'varInput' ),
                        'description' => __('Choose the type of element that you would like to render', 'wpdatatables'),
                        'save_always' => true,
                    ),
                    array(
                        'type' => 'dropdown',
                        'class' => '',
                        'heading' => __( 'Button action', 'wpdatatables' ),
                        'admin_label' => true,
                        'dependency' => array(
                            'element' => 'element',
                            'value' => array('button')
                        ),
                        'param_name' => 'type',
                        'value' => array( 'Download button' => 'download', 'Save to WP Media Library' => 'save' ),
                        'description' => __('Choose the type of element that you would like to render', 'wpdatatables'),
                        'save_always' => true,
                    ),
                    array(
                        'type' => 'textfield',
                        'class' => '',
                        'heading' => __( 'Display text', 'wpdatatables' ),
                        'admin_label' => true,
                        'dependency' => array(
                            'element' => 'id',
                            'not_empty' => true
                        ),
                        'param_name' => 'text',
                        'value' => '',
                        'description' => __('Set the text label for the element', 'wpdatatables'),
                        'save_always' => true
                    ),
                    array(
                        'type' => 'dropdown',
                        'class' => '',
                        'heading' => __( 'Variable name', 'wpdatatables' ),
                        'admin_label' => true,
                        'dependency' => array(
                            'element' => 'element',
                            'value' => array('varInput')
                        ),
                        'param_name' => 'name',
                        'value' => '',
                        'description' => __('Which variable to place the input for', 'wpdatatables'),
                        'save_always' => true
                    ),
                    array(
                        'type' => 'textfield',
                        'class' => '',
                        'heading' => __( 'Default value', 'wpdatatables' ),
                        'admin_label' => true,
                        'dependency' => array(
                            'element' => 'element',
                            'value' => array('varInput')
                        ),
                        'param_name' => 'default',
                        'value' => '',
                        'description' => __('Set the default predefined value for variable', 'wpdatatables'),
                        'save_always' => true
                    ),
                    array(
                        'type' => 'textfield',
                        'class' => '',
                        'heading' => __( 'CSS Class', 'wpdatatables' ),
                        'admin_label' => true,
                        'dependency' => array(
                            'element' => 'id',
                            'not_empty' => true
                        ),
                        'param_name' => 'class',
                        'value' => '',
                        'description' => __('Define a CSS class if you want', 'wpdatatables'),
                        'save_always' => true
                    )
                )
            )
        );

    }

}

VCIntegration::initReportBuilderButton();