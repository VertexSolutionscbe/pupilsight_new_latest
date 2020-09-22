<?php
/**
 * @author Alexander Gilmanov
 * @since February 2016
 */

namespace WDTReportBuilder;

/**
 * Class ReportBuilder
 * Main controller to generate the reports based on given wpDataTables data
 * @package WDTReportBuilder
 */
class ReportBuilder {

    /**
     * Report ID. Null for new reports
     */
    private $_id = null;

    /**
     * Data Source (wpDataTable) ID. Null if this is a report without a source.
     * @var null
     */
    private $_dataSourceId = null;

    /**
     * Template file for the report
     */
    private $_templateFile = null;

    /**
     * Build facilitator
     */
    private $_builder = null;

    /**
     * Name field
     */
    private $_name = '';

    /**
     * Filename mask for multi-file reports
     */
    private $_filenameMask = '${reportname}-${count}';

    /**
     * Array of custom additional variables defined by report creator
     */
    private $_additionalVars = array();

    /**
     * Generation logic (single file or multiple files in ZIP)
     */
    private $_generationLogic = 'single';

    /**
     * File handling rule (download, save to uploads, ...)
     */
    private $_fileHandling = 'download';

    /**
     * Limit of rows to handle (0 for unlimited)
     */
    private $_rowsLimit = 0;

    /**
     * Follow filtering (if a table is present on the page report will be only generated for visible rows)
     */
    private $_followFiltering = false;

    /**
     * Filtered data sent from the front-end
     */
    private $_filteredData = null;

    /**
     * Report config merged in array for quick passing to front-end
     */
    private $_reportConfig = array();

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->_id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->_id = $id;
    }

    /**
     * Return the array of additional variables
     * @return mixed
     */
    public function getAdditionalVars()
    {
        return $this->_additionalVars;
    }

    /**
     * Set the additional variables
     * @param mixed $additionalVars
     */
    public function setAdditionalVars($additionalVars)
    {
        $this->_additionalVars = $additionalVars;
    }

    /**
     * Define an individual additional var
     * @param $key The name of the additional var
     * @param $value The value of the additional var
     */
    public function setAdditionalVar( $key, $value ){
        $this->_additionalVars[$key] = $value;
    }

    /**
     * Returns the current value of an additional var, or
     * an empty string if such variable doesn't exist
     * @param $key Name of the variable
     * @return string Value or the variable, or empty string
     */
    public function getAdditionalVar( $key ){
        return !empty( $this->_additionalVars[$key] )? $this->_additionalVars[$key] : '';
    }

    /**
     * Return the current report name
     * @return mixed
     */
    public function getName()
    {
        return $this->_name;
    }

    /**
     * Set the report name
     * @param mixed $name
     */
    public function setName($name)
    {
        $this->_name = $name;
    }

    /**
     * Returns the rule defined for file handling
     * @return mixed
     */
    public function getFileHandling()
    {
        return $this->_fileHandling;
    }

    /**
     * Sets the rule for file handling (download = default, save to uploads, ...)
     * @param mixed $fileHandling
     */
    public function setFileHandling($fileHandling)
    {
        $this->_fileHandling = $fileHandling;
    }

    /**
     * @return mixed
     */
    public function getReportConfig()
    {
        return $this->_reportConfig;
    }

    /**
     * @param mixed $reportConfig
     */
    public function setReportConfig($reportConfig)
    {
        $this->_reportConfig = $reportConfig;
    }

    /**
     * @return mixed
     */
    public function getRowsLimit()
    {
        return $this->_rowsLimit;
    }

    /**
     * @param mixed $rowsLimit
     */
    public function setRowsLimit($rowsLimit)
    {
        $this->_rowsLimit = $rowsLimit;
    }

    /**
     * Returns the generation logic
     * @return mixed
     */
    public function getGenerationLogic()
    {
        return $this->_generationLogic;
    }

    /**
     * Set the generation logic for current file (Single file or multiple files in ZIP)
     * @param string $generationLogic
     */
    public function setGenerationLogic( $generationLogic )
    {
        $this->_generationLogic = $generationLogic == 'multiple' ? 'multiple' : 'single';
    }

    /**
     * @return string Path to the template
     */
    public function getTemplateFile()
    {
        return $this->_templateFile;
    }

    /**
     * @param null $templateFile
     */
    public function setTemplateFile($templateFile)
    {
        $this->_templateFile = $templateFile;
    }

    /**
     * @return null
     */
    public function getDataSourceId()
    {
        return $this->_dataSourceId;
    }

    /**
     * @param null $dataSourceId
     */
    public function setDataSourceId( $dataSourceId )
    {
        $this->_dataSourceId = $dataSourceId;
    }

    /**
     * Returns if the follow filtering flag is enabled or disabled
     * @return bool
     */
    public function getFollowFiltering()
    {
        return $this->_followFiltering;
    }

    /**
     * Sets the follow filtering flag
     * @param mixed $followFiltering
     */
    public function setFollowFiltering($followFiltering)
    {
        $this->_followFiltering = (bool) $followFiltering;
    }

    /**
     * @return mixed
     */
    public function getFilteredData()
    {
        return $this->_filteredData;
    }

    /**
     * @param mixed $filteredData
     */
    public function setFilteredData($filteredData)
    {
        $this->_filteredData = $filteredData;
    }

    /**
     * @return mixed
     */
    public function getFilenameMask()
    {
        return $this->_filenameMask;
    }

    /**
     * @param mixed $filenameMask
     */
    public function setFilenameMask( $filenameMask )
    {
        $this->_filenameMask = $filenameMask;
    }

    /**
     * Constructor which initializes the class
     */
    public function __construct(){

    }

    /**
     * Initializes the report settings from DB
     */
    public function loadFromDB(){
        global $wpdb;
        if( !empty( $this->_id ) ){
            $reportConfig = $wpdb->get_row(
                $wpdb->prepare(
                    'SELECT *
                      FROM '.$wpdb->prefix . 'wpdatareports
                      WHERE id = %d',
                    $this->_id
                )
            );

            if( empty( $reportConfig ) ){
                return false;
            }

            // Decoding the JSON string
            $reportConfig->report_config = json_decode( $reportConfig->report_config, true );

            $configArray = array(
                'id' => $this->_id,
                'dataSource' => $reportConfig->table_id,
                'name' => $reportConfig->name,
                'generationLogic' => $reportConfig->report_config['generationLogic'],
                'templateFile' => $reportConfig->report_config['templateFile'],
                'followFiltering' => !empty( $reportConfig->report_config['followFiltering'] )
                    ? $reportConfig->report_config['followFiltering']: 0,
                'additionalVars' =>
                    !empty( $reportConfig->report_config['additionalVars'] )
                        ? $reportConfig->report_config['additionalVars'] : array()
            );

            $configArray = apply_filters( 'reportbuilder_before_init_config', $configArray );

            // Filename mask for multi-file report
            if( $configArray['generationLogic'] == 'multiple'
                && !empty( $reportConfig->report_config['filenameMask'] ) ){
                $configArray['filenameMask'] = $reportConfig->report_config['filenameMask'];
            }

            // Initializing the report
            $this->initReportConfig( $configArray );

            // Save merged report config in case it will be needed in front-end
            $this->setReportConfig( $configArray );

            return true;

        }else{
            return false;
        }
    }

    /**
     * Initialize the report from a given config array
     */
    public function initReportConfig( $wdtReportConfig ){

        // Init the ID if provided
        if( !empty( $wdtReportConfig['id'] ) ){
            $this->setId( $wdtReportConfig['id'] );
        }

        // Init generation logic (single or multiple files)
        $this->setGenerationLogic( sanitize_text_field( $wdtReportConfig['generationLogic'] ) );

        if( $this->getGenerationLogic() == 'multiple' && !empty( $wdtReportConfig['filenameMask'] ) ){
            if( sanitize_text_field( $wdtReportConfig['filenameMask'] ) != '' ){
                $this->setFilenameMask( sanitize_text_field( $wdtReportConfig['filenameMask'] ) );
            }
        }

        // Init data source
        if( !empty( $wdtReportConfig['dataSource'] ) ){
            $this->setDataSourceId((int)$wdtReportConfig['dataSource']);
        }

        // Init follow filtering
        if( !empty( $wdtReportConfig['followFiltering'] ) ){
            $this->setFollowFiltering( (bool) $wdtReportConfig['followFiltering'] );
        }

        // Init report file
        $this->setTemplateFile( self::urlToPath( sanitize_text_field( $wdtReportConfig['templateFile'] ) ) );

        // If a limit is passed set it as well
        if( isset( $wdtReportConfig['rowsLimit'] ) && intval( $wdtReportConfig['rowsLimit'] ) ){
            $this->setRowsLimit( intval( $wdtReportConfig['rowsLimit'] ) );
        }

        // Set the report name
        if( !empty( $wdtReportConfig['name'] ) ){
            $this->setName( sanitize_text_field( $wdtReportConfig['name'] ) );
        }

        // Set the additional vars
        if( !empty( $wdtReportConfig['additionalVars'] ) ){
            $this->setAdditionalVars( $wdtReportConfig['additionalVars'] );
        }

    }

    /**
     * Function that builds the report
     */
    public function build(){
        if( !empty( $this->_templateFile ) ){
            require_once( WDT_RB_ROOT_PATH.'/source/class.builderfactory.php' );
            $this->_builder = BuilderFactory::factory( $this->_templateFile );
            $this->_builder->setGenerationLogic( $this->_generationLogic );
            if( $this->getGenerationLogic() == 'multiple' ){
                $this->_builder->setFilenameMask( $this->_filenameMask );
            }
            $this->_builder->setRowsLimit( $this->_rowsLimit );
            $this->_builder->setName( $this->_name );
            $this->_builder->setAdditionalVars( $this->getAdditionalVars() );
            if( !empty( $this->_filteredData ) ){
                $this->_builder->setFilteredData( $this->_filteredData );
            }
            if( !empty( $this->_dataSourceId ) ){
                $this->_builder->initWpDataTable( $this->_dataSourceId );
            }
            $this->_builder->build();
            // If the file is built successfully handle it (return to browser or save)
            if( $this->_builder->getGeneratedFile() !== '' ){
                $this->handleFile();
            }
        }
    }

    /**
     * Saves the report to DB
     */
    public function save(){
        global $wpdb;

        $report_config_data = array(
            'table_id' => $this->getDataSourceId(),
            'name' => $this->getName(),
            'report_config' =>
                array(
                    'templateFile' => $this->getTemplateFile(),
                    'generationLogic' => $this->getGenerationLogic(),
                    'additionalVars' => $this->getAdditionalVars(),
                    'followFiltering' => $this->getFollowFiltering()
                )
        );

        if( $this->getGenerationLogic() == 'multiple' ){
            $report_config_data['report_config']['filenameMask'] = $this->getFilenameMask();
        }

        // Encode the config before save
        $report_config_data['report_config'] = json_encode( $report_config_data['report_config'] );

        $report_config_data = apply_filters( 'reportbuilder_before_save_to_db', $report_config_data, $this->_id );

        if( empty( $this->_id ) ){
            // Adding a new report
            $wpdb->insert(
                $wpdb->prefix . 'wpdatareports',
                $report_config_data
            );
            if( empty( $wpdb->last_error ) ){
                $this->setId( $wpdb->insert_id );
            }
        }else{
            // Updating an existing report
            $wpdb->update(
                $wpdb->prefix . 'wpdatareports',
                $report_config_data,
                array(
                    'id' => $this->getId()
                )
            );
        }
    }

    /**
     * Handles the generated report as defined
     * Bases on the generationLogic (set via setGenerationLogic)
     * 'save' - save the report to local Media Library
     * 'download' - just download the file to the client's browser
     */
    public function handleFile(){
        switch( $this->_fileHandling ){
            case 'save':
                $media_folder = wp_upload_dir();
                $new_file = $media_folder['path'] . '/' . basename( $this->_builder->getGeneratedFile() );
                if( rename(
                        $this->_builder->getGeneratedFile(),
                        $new_file
                ) ){
                    do_action( 'reportbuilder_save_to_media_library', $new_file );
                    wp_insert_attachment(
                        array(
                            'post_title' => $this->getName(),
                            'post_content' => '',
                            'post_status' => 'publish',
                            'post_mime_type' =>
                                self::getMimeType(
                                    $new_file
                                )
                        ),
                        $new_file
                    );
                }

                break;
            case 'download':
            default:
            ob_end_clean();
            header( "Pragma: public" );
            header( "Expires: 0" );
            header( "Cache-Control: must-revalidate, post-check=0, pre-check=0" );
            header( "Cache-Control: private", false );
            header( 'Content-Type: ' . self::getMimeType( $this->_builder->getGeneratedFile() ) );
            header( 'Content-Length: ' . filesize( $this->_builder->getGeneratedFile() ) );
            header( 'Content-Disposition: attachment; filename="'.basename( $this->_builder->getGeneratedFile() ).'"' );
            readfile( $this->_builder->getGeneratedFile() );
            unlink( $this->_builder->getGeneratedFile() );
            break;
        }
    }

    /**
     * Method which renders the text input for frontend shortcode
     * @param $name The name of the variable for which we render the shortcode
     * @param $text The text to be displayed on the label for input
     * @param $default The default value to be pre-inserted in input
     * @param $class The CSS class to be applied to the input block
     * @return string HTML of the rendered input
     */
    public function renderInput( $name, $text, $default, $class = '' ){
        ob_start();
        include( WDT_RB_ROOT_PATH.'/templates/input.tpl.php' );
        $input = ob_get_contents();
        ob_end_clean();
        return apply_filters( 'reportbuilder_render_input', $input );
    }

    /**
     * Method which renders the Download / Save button for frontend shortcode
     * @param $name The name of the variable for which we render the shortcode
     * @param $text The text to be displayed on the label for input
     * @param $default The default value to be pre-inserted in input
     * @param $class The CSS class to be applied to the input block
     * @return string HTML of the rendered button
     */
    public function renderButton( $type, $text, $class ){
        ob_start();
        include( WDT_RB_ROOT_PATH.'/templates/button.tpl.php' );
        $input = ob_get_contents();
        ob_end_clean();
        return apply_filters( 'reportbuilder_render_button', $input );
    }

    /**
     * Helper function which converts WP upload URL to Path
     */
    public static function urlToPath( $uploadUrl ){
        $uploads_dir = wp_upload_dir();
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $uploadPath = str_replace( $uploads_dir['baseurl'], str_replace('\\', '/', $uploads_dir['basedir']), $uploadUrl );
        }else{
            $uploadPath = str_replace( $uploads_dir['baseurl'], $uploads_dir['basedir'], $uploadUrl );
        }
        return $uploadPath;
    }

    /**
     * Helper function which converts upload path to URL
     */
    public static function pathToUrl( $uploadPath ){
        $uploads_dir = wp_upload_dir();
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $uploadUrl = str_replace( str_replace('\\', '/', $uploads_dir['basedir']), $uploads_dir['baseurl'], $uploadPath );
        }else{
            $uploadUrl = str_replace( $uploads_dir['basedir'], $uploads_dir['baseurl'], $uploadPath );
        }
        return $uploadUrl;
    }


    /**
     * Helper function to determine the MIME filetype, which doesn't crash where deprecated
     *
     * @param $filename
     * @return bool|mixed|string
     */
    public static function getMimeType( $filename ) {
        $realpath = realpath( $filename );
        if ( $realpath
            && function_exists( 'finfo_file' )
            && function_exists( 'finfo_open' )
            && defined( 'FILEINFO_MIME_TYPE' )
        ) {
            // Use the Fileinfo PECL extension (PHP 5.3+)
            return @finfo_file( finfo_open( FILEINFO_MIME_TYPE ), $realpath );
        }
        if ( function_exists( 'mime_content_type' ) ) {
            // Deprecated in PHP 5.3
            return @mime_content_type( $realpath );
        }
        return false;
    }


}