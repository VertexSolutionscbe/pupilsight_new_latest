<?php
namespace WDTReportBuilder;

/**
 * @author Alexander Gilmanov
 * @since February 2016
 */
use WDTConfigController;
use WPDataTable;

/**
 * Class AbstractBuilder
 * Class is a parent to all (DOCX, PPTX, XLSX) classes
 * @package WDTReportBuilder
 */
abstract class AbstractBuilder {

    /**
     * @var $_templateFile
     * Template file for the report
     */
    protected $_templateFile = null;

    /**
     * @var string
     * The report name
     */
    protected $_name = '';

    /**
     * @var string
     * Flag which defines whether to generate a single file or multiple files
     */
    protected $_generationLogic = 'single';

    /**
     * @var string
     * The path to generated file
     */
    protected $_generatedFile = '';


    /**
     * Array of custom additional variables defined by report creator
     */
    private $_additionalVars = array();

    /**
     * Filtered data passed from the front-end
     */
    private $_filteredData = null;

    /**
     * Filename mask for multiple reports
     */
    protected $_filenameMask = '${reportname}-${count}';

    /**
     * @var $_wpDataTable
     * wpDataTable object which facilitates the data
     */
    protected $_wpDataTable = null;

    /**
     * Return the array of additional variables
     * @return mixed
     */
    public function getAdditionalVars()
    {
        return $this->_additionalVars;
    }

    /**
     * Define or re-define a single additional var
     */
    public function setAdditionalVar( $key, $value ){
        $this->_additionalVars[$key] = $value;
    }

    /**
     * Get a single additional variable
     */
    public function getAdditionalVar( $key ){
        if( !empty( $this->_additionalVars[$key] ) ){
            return $this->_additionalVars[$key];
        }else{
            return null;
        }
    }

    /**
     * Set the additional variables
     * @param array $additionalVars
     */
    public function setAdditionalVars( $additionalVars )
    {
        $this->_additionalVars = $additionalVars;
    }

    /**
     * Returns the array of filtered data
     * @return array
     */
    public function getFilteredData()
    {
        return $this->_filteredData;
    }

    /**
     * Sets the array of filtered data
     * @param mixed $filteredData
     */
    public function setFilteredData( $filteredData )
    {
        $this->_filteredData = $filteredData;
    }

    /**
     * Returns the filename mask
     * @return mixed
     */
    public function getFilenameMask()
    {
        return $this->_filenameMask;
    }

    /**
     * Sets the filename mask
     * @param mixed $filenameMask
     */
    public function setFilenameMask($filenameMask)
    {
        $this->_filenameMask = $filenameMask;
    }

    /**
     * Returns the current name of the report
     * @return string
     */
    public function getName()
    {
        return $this->_name;
    }

    /**
     * Sets a report name
     * @param string $name
     */
    public function setName($name)
    {
        $this->_name = $name;
    }

    /**
     * Returns the defined limit for rows to fetch from data source for generation
     * @return int
     */
    public function getRowsLimit()
    {
        return $this->_rowsLimit;
    }

    /**
     * Sets the limit for rows to fetch from data source for generation
     * @param int $rowsLimit
     */
    public function setRowsLimit($rowsLimit)
    {
        $this->_rowsLimit = $rowsLimit;
    }

    /**
     * Returns the current generation logic rule (single file or multiple files)
     * @return string
     */
    public function getGenerationLogic()
    {
        return $this->_generationLogic;
    }

    /**
     * Returns the path to generated file
     * @return string
     */
    public function getGeneratedFile()
    {
        return $this->_generatedFile;
    }

    /**
     * Defines the path to generated file
     * @param string $generatedFile
     */
    public function setGeneratedFile( $generatedFile )
    {
        $this->_generatedFile = $generatedFile;
    }

    /**
     * Sets the generation logic rule (single file or multiple files)
     * @param string $generationLogic
     */
    public function setGenerationLogic( $generationLogic )
    {
        $this->_generationLogic = $generationLogic;
    }

    /**
     * @return mixed
     */
    public function getWpDataTable()
    {
        return $this->_wpDataTable;
    }

    /**
     * @param mixed $wpDataTable
     */
    public function setWpDataTable( $wpDataTable )
    {
        $this->_wpDataTable = $wpDataTable;
    }

    /**
     * Initializes wpDataTable by the given ID
     * @param $wdtId
     */
    public function initWpDataTable( $wdtId )
    {
        if ( !empty( $wdtId ) ) {
            $disableLimit = $this->_filteredData != null ? false : ( $this->getRowsLimit() == 0 );
            $this->_wpDataTable = WPDataTable::loadWpDataTable($wdtId, null, $disableLimit);
            $column_data = WDTConfigController::loadColumnsFromDB( $wdtId );
            foreach( $column_data as $column ){
                $this->_wpDataTable
                        ->getColumn( $column->orig_header )
                        ->setTextBefore( $column->text_before );
                $this->_wpDataTable
                        ->getColumn( $column->orig_header )
                        ->setTextAfter( $column->text_after );
            }
            // Re-initialize the row count
            $rowCount = $this->_filteredData != null ? count( $this->_filteredData ) : count( $this->_wpDataTable->getDataRows() );
            if( $this->getRowsLimit() == 0 || $rowCount < $this->getRowsLimit() ){
                $this->setRowsLimit( $rowCount );
            }
            if( !empty( $this->_filteredData )
                && count( $this->_filteredData ) < $rowCount ){
                $this->setRowsLimit( count( $this->_filteredData ) );
            }
        }
    }

    /**
     * @return mixed
     */
    public function getTemplateFile()
    {
        return $this->_templateFile;
    }

    /**
     * @param mixed $templateFile
     */
    public function setTemplateFile( $templateFile )
    {
        $this->_templateFile = $templateFile;
    }

    /**
     * AbstractBuilder constructor.
     * @param $templateFile
     */
    public function __construct( $templateFile ){
        $this->setTemplateFile( $templateFile );
    }

    /**
     * This function needs to be overriden in the child classes
     * It performs the generation of the report
     */
    abstract public function build();

    /**
     * Returns the list of default/predefined variables
     */
    public function getDefaultVariables(){

        wp_get_current_user();
        global $current_user;

        return array(
            'today'         => date( get_option('wdtDateFormat') ),
            'time'          => current_time( get_option('time_format') ),
            'creator'       => $current_user->display_name,
            'generatorname' => $current_user->display_name,
            'sitename'      => get_bloginfo( 'name' ),
            'reportname'    => $this->getName()
        );
    }

    /**
     * Formats the cell output
     */
    protected function getCellOutput( $columnKey, $cellValue ){
        $return_val = $this->_wpDataTable
                            ->getColumn( $columnKey )
                            ->getTextBefore();
        switch( $this->_wpDataTable->getColumn( $columnKey )->getDataType() ){
            case 'link':
            case 'image':
            case 'string':

                $cellValue = str_replace(["<br>", "<br/>", "<br />", "\r\n"], " \n ", $cellValue);

                if (is_a($this, '\WDTReportBuilder\DocumentBuilder' ))  {
                    if (strpos($cellValue, '&amp;') === false) {
                        $cellValue = htmlspecialchars($cellValue);
                    }
                }

                if (is_a($this, '\WDTReportBuilder\SpreadsheetBuilder' ))  {
                    if (strpos($cellValue, '&amp;') !== false) {
                        $cellValue = str_replace('&amp;', '&', $cellValue);
                    }
                }

                $return_val .=
                        $this->_wpDataTable
                            ->getColumn( $columnKey )
                            ->returnCellValue( $cellValue );
                break;
            case 'email':
                $return_val .=
                    stripslashes(
                        strip_tags(
                            $this->_wpDataTable
                            ->getColumn( $columnKey )
                            ->returnCellValue( $cellValue )
                        )
                    );
                break;
	        case 'date' :
		        if( $this->_filteredData !== null && $cellValue && is_string( $cellValue ) ){
			        $cellValue = self::dateToTimestamp( $cellValue );
		        }
		        $return_val .=
			        $this->_wpDataTable
				        ->getColumn( $columnKey )
				        ->returnCellValue( $cellValue );
		        break;
            case 'int':
            case 'float':
            case 'formula':
                // Remove thousands separator for data pulled from front-end
                if( $this->_filteredData != null && is_string( $cellValue ) ){
                    $cellValue = self::unformatNumber( $cellValue );
                }
            default:
                $return_val .= $this->_wpDataTable
                    ->getColumn( $columnKey )
                    ->returnCellValue( $cellValue );
        }
        $return_val .= $this->_wpDataTable
                            ->getColumn( $columnKey )
                            ->getTextAfter();
        if( $return_val === '' ){ $return_val = ' '; }
        return apply_filters( 'reportbuilder_before_render_cell_output', $return_val, $this->_wpDataTable->getColumn( $columnKey )->getDataType() );
    }


    /**
     * Generates the filename for multi-file report based on the mask
     * @param $row Row with data
     * @param $counter Counter of the generated rows
     * @return String Sanitized filename without extension
     */
    public function generateMaskedFilename( $row, $counter ){

        $baseReportFilename = !empty( $this->_name ) ? $this->_name : 'report';

        $filename = $this->getFilenameMask();

        $filename = str_replace( '${reportname}', $baseReportFilename, $filename );
        $filename = str_replace( '${count}', $counter, $filename );

        if( $this->_wpDataTable != null ){
            foreach( $this->_wpDataTable->getColumnKeys() as $columnKey ){
                if( strpos( $filename, '${'.$columnKey.'}' ) !== false ){
                    // Fetch the cell value
                    if( $this->getFilteredData() != null ){
                        $filename = str_replace(
                            '${'.$columnKey.'}',
                            $row[ array_search( $columnKey, $this->_wpDataTable->getColumnKeys() ) ],
                            $filename
                        );
                    }else{
                        $filename = str_replace(
                            '${'.$columnKey.'}',
                            $row[$columnKey],
                            $filename
                        );
                    }
                }
            }
        }

        return apply_filters( 'reportbuilder_masked_filename', sanitize_file_name( $filename ) );
    }

    public static function unformatNumber( $number ){
        if( get_option( 'wdtNumberFormat') == '1' ){
            $number = str_replace( '.', '', $number );
            $number = str_replace( ',', '.', $number );
        }else{
            $number = str_replace( ',', '', $number );
        }
        return (float) $number;
    }

	public static function dateToTimestamp ( $date ) {
        $date = date_create_from_format(get_option('wdtDateFormat'), $date);
        if ($date instanceof \DateTime) {
            $date = $date->format('U');
            return $date;
        }

        return '';
	}

}
