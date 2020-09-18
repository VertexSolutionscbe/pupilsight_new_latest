<?php
/**
 * @author Alexander Gilmanov
 * @since February 2016
 */

namespace WDTReportBuilder;

use PhpOffice\PhpWord\PhpWord;

/**
 * Class Document Builder
 * Builds documents (DOC/DOCX)
 *
 * @package WDTReportBuilder
 */
class DocumentBuilder extends AbstractBuilder {

    /**
     * PHPWord object
     */
    private $_objPhpWord = null;

    /**
     * TemplateProcessor object
     */
    private $_templateProcessor = null;

    /**
     * DocumentBuilder constructor.
     * @param $templateFile location of the file to be used as template
     */
    public function __construct( $templateFile ){
        parent::__construct( $templateFile );
        $this->_objPhpWord = new PhpWord();
    }

    /**
     * Sets all default variables
     */
    public function setDefaultVariables(){
        // Set all default vars
        foreach( $this->getDefaultVariables() as $var => $value ){
            $this->_templateProcessor->setValue( $var, $value );
        }
    }

    /**
     * Sets all user-defined variables
     */
    public function setAdditionalVariables(){
        foreach( $this->getAdditionalVars() as $name => $value ){
            $this->_templateProcessor->setValue( $name, $value );
        }
    }

    /**
     * Fill in the fields in a single-file
     */
    private function fillInSingleFile(){
        $this->_templateProcessor = new TemplateProcessorExtend( $this->_templateFile );
        $rowsCloned = false;
        // Prepare the filename
        $baseReportFilename = !empty( $this->_name ) ? sanitize_title( $this->_name ) : 'report';

        // Fetch all vars
        $vars = $this->_templateProcessor->getVariables();

        if( !empty( $this->_wpDataTable ) ){
            foreach( $this->_wpDataTable->getColumnKeys() as $columnKey ) {
                // Set column names from wpDataTables
                if( in_array( $columnKey.'.name', $vars ) ){
                    $this->_templateProcessor
                        ->setValue(
                            $columnKey.'.name',
                            $this->_wpDataTable
                                ->getColumn( $columnKey )
                                ->getTitle()
                        );
                }

                // Check if we need to calculate a total
                $calcTotal = in_array( $columnKey.'.total', $vars );
                $totalVal  = 0;

                if( in_array( $columnKey.'.all', $vars ) ){
                    // Duplicate rows from wpDataTables

                    // Non-table scenario
                    if( in_array( 'wpDataTable.row', $vars )
                        && !$rowsCloned ){
                        $this->_templateProcessor
                            ->cloneBlock( 'wpDataTable.row', $this->getRowsLimit() );
                        $rowsCloned = true;
                    }

                    // Table scenario
                    if( !$rowsCloned && !in_array( 'wpDataTable.row', $vars ) ){
                        $this->_templateProcessor
                            ->cloneRow(
                                $columnKey.'.all',
                                $this->getRowsLimit()
                            );
                        $rowsCloned = true;
                    }
                }

                // Get the dataset
                if( $this->getFilteredData() != null ){
                    $dataSet = $this->getFilteredData();
                }else{
                    $dataSet = $this->_wpDataTable->getDataRows();
                }

                /**
                 * Set all column values
                 */
                foreach( $dataSet as $rowIndex=>$row ){
                    if( $rowIndex >= $this->getRowsLimit() ){ break; }

                    // Fetch the cell value
                    if( $this->getFilteredData() != null ){
                        $cellVal = $row[ array_search( $columnKey, $this->_wpDataTable->getColumnKeys() ) ];
                    }else{
                        $cellVal = $row[$columnKey];
                    }

                    $this->_templateProcessor
                        ->setValue(
                            $columnKey.'.all#'.($rowIndex+1),
                            $this->getCellOutput( $columnKey, $cellVal )
                        );

                    if( $calcTotal ){
                        if( $this->getFilteredData() != null && is_string( $cellVal ) ){
                            $cellVal = self::unformatNumber( $cellVal );
                        }
                        $totalVal += $cellVal;
                    }
                }

                // Write down the total if necessary
                if( $calcTotal ){
                    $this->_templateProcessor
                        ->setValue(
                            $columnKey.'.total',
                            $this->getCellOutput( $columnKey, $totalVal )
                        );
                }

            }

        }

        // Set all default variables
        $this->setDefaultVariables();

        // Set all additional variables
        $this->setAdditionalVariables();

        $this->_templateProcessor->saveAs( sys_get_temp_dir() . '/' . $baseReportFilename.'.docx' );

        $this->setGeneratedFile( sys_get_temp_dir() . '/' . $baseReportFilename.'.docx' );
    }

    /**
     * Fill in the fields in a single-file
     */
    private function fillInMultipleFiles(){

        // Generate a new file for each table row
        $counter = 1;
        $reportFiles = array();
        $baseReportFilename = !empty( $this->_name ) ? sanitize_title( $this->_name ) : 'report';

        // Get the dataset
        if( $this->getFilteredData() != null ){
            $dataSet = $this->getFilteredData();
        }else{
            $dataSet = $this->_wpDataTable->getDataRows();
        }

        foreach( $dataSet as $rowIndex=>$row ){

            // Generate only for defined number of rows
            if( $rowIndex >= $this->getRowsLimit() ){ break; }

            $this->_templateProcessor = new TemplateProcessorExtend( $this->_templateFile );
            $vars = $this->_templateProcessor->getVariables();

            foreach( $this->_wpDataTable->getColumnKeys() as $columnKey ) {
                // Set column names from wpDataTables
                if( in_array( $columnKey . '.name', $vars ) ){
                    $this->_templateProcessor
                        ->setValue(
                            $columnKey . '.name',
                            $this->_wpDataTable
                                ->getColumn( $columnKey )
                                ->getTitle()
                        );
                }

                // Fetch the cell value
                if( $this->getFilteredData() != null ){
                    $cellVal = $row[ array_search( $columnKey, $this->_wpDataTable->getColumnKeys() ) ];
                }else{
                    $cellVal = $row[$columnKey];
                }

                // Set the var names from row cells
                $this->_templateProcessor
                     ->setValue(
                        $columnKey,
                        $this->getCellOutput( $columnKey, $cellVal )
                     );

            }

            // Set all default variables
            $this->setDefaultVariables();

            // Set all additional variables
            $this->setAdditionalVariables();

            $reportFileName = $this->generateMaskedFilename( $row, $counter ).'.docx';

            $this->_templateProcessor->saveAs( sys_get_temp_dir() . '/' . $reportFileName );
            $reportFiles[] = $reportFileName;

            unset( $this->_templateProcessor );

            $counter++;
        }

        $zip = new \ZipArchive();
        $zipFilename = sys_get_temp_dir() . '/'.$baseReportFilename.'.zip';
        if( $zip->open( $zipFilename, \ZipArchive::CREATE ) !== TRUE ){
            throw new Exception("cannot open <$zipFilename>\n");
        }
        foreach( $reportFiles as $reportFile ){
            $zip->addFile( sys_get_temp_dir() . '/' . $reportFile, $reportFile );
        }
        $zip->close();

        $this->setGeneratedFile( $zipFilename );
    }


    /**
     * Build the DOC report
     */
    public function build(){

        if( $this->_generationLogic == 'single' ){
            // Fill in the file
            $this->fillInSingleFile();
        }else{
            if( !empty( $this->_wpDataTable ) ) {
                $this->fillInMultipleFiles();
            }
        }
    }

}