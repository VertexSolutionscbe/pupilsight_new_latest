<?php
/**
 * @author Alexander Gilmanov
 * @since February 2016
 */

namespace WDTReportBuilder;

use PhpOffice\PhpSpreadsheet\IOFactory;

/**
 * Class Spreadsheet Builder
 * Builds spreadsheets (XLS/XLSX)
 *
 * @package WDTReportBuilder
 */
class SpreadsheetBuilder extends AbstractBuilder {

    /**
     * PHPExcel object
     */
    private $_objPhpExcel = null;

    /**
     * PHPExcelReader object
     */
    private $_phpExcelReader = null;

    /**
     * PHPExcelWriter object
     */
    private $_phpExcelWriter = null;

    /**
     * Generation logic (single file or multiple files)
     */
    protected $_generationLogic = 'single';

    /**
     * SpreadsheetBuilder constructor.
     * @param $templateFile Path to the template file
     */
    public function __construct( $templateFile ){
        parent::__construct( $templateFile );
    }

    /**
     * Helper workaround to find cells by content using PHPExcel
     * @param $content Content that we want to find coordinates for
     */
    public function getCoordinatesByContent( $content  ){
        $foundInCells = array();
        foreach( $this->_objPhpExcel->getWorksheetIterator() as $worksheet ) {
            $ws = $worksheet->getTitle();
            $highestRow = $worksheet->getHighestDataRow();
            $highestColumn = $worksheet->getHighestDataColumn();
            foreach( $worksheet->getRowIterator() as $row ) {
                $cellIterator = $row->getCellIterator();
                // Workaround for empty rows
                try {
                    $cellIterator->setIterateOnlyExistingCells(true);
                }catch( \Exception $e ){
                    continue;
                }
                foreach( $cellIterator as $cell ) {
                    if ( $cell->getValue() === $content ) {
                        $foundInCells[] = $ws . '!' . $cell->getCoordinate();
                    }
                }
            }
        }
        return $foundInCells;
    }

    /**
     * Sets the default/predefined variables
     */
    public function setDefaultVariables(){
        foreach( $this->getDefaultVariables() as $var => $value ){
            $this->setTemplateVarValue( $var, $value );
        }
    }

    /**
     * Sets all user-defined variables
     */
    public function setAdditionalVariables(){
        foreach( $this->getAdditionalVars() as $name => $value ){
            $this->setTemplateVarValue( $name, $value );
        }
    }

    /**
     * Helper function that inserts single template values
     */
    public function setTemplateVarValue( $var, $value ){

        $cells = $this->getCoordinatesByContent( '${'.$var.'}' );
        foreach( $cells as $cell ){
            $this->_objPhpExcel
                ->getActiveSheet()
                ->getCell( $cell )
                ->setValue( $value );
        }

    }

    /**
     * Fills in a single file
     */
    private function fillInSingleFile(){

        $this->_phpExcelReader = IOFactory::createReaderForFile( $this->_templateFile );
        $this->_objPhpExcel = $this->_phpExcelReader->load( $this->_templateFile );
        $this->_phpExcelWriter = IOFactory::createWriter( $this->_objPhpExcel, 'Xlsx' );

        // Prepare a base filename
        $baseReportFilename = !empty( $this->_name ) ? sanitize_title( $this->_name ) : 'report';

        $rowsDuplicated = false;
        if( !empty( $this->_wpDataTable ) ){
            foreach( $this->_wpDataTable->getColumnKeys() as $columnKey ){
                $cells = $this->getCoordinatesByContent( '${'.$columnKey.'.all}' );
                if( !empty( $cells ) ){

                    // Check if a total calc is required
                    $totalCells = $this->getCoordinatesByContent('${'.$columnKey.'.total}');
                    $totalCalc = !empty( $totalCells );
                    $totalVal = 0;

                    $this->setTemplateVarValue(
                        $columnKey.'.name',
                        $this->_wpDataTable
                            ->getColumn( $columnKey )
                            ->getTitle()
                    );

                    $writeArray = array();
                    // If this is the first row found duplicate it as many times as needed
                    if( !$rowsDuplicated ){
                        $firstRowIndex =  $this->_objPhpExcel
                            ->getActiveSheet()
                            ->getCell( $cells[0] )
                            ->getRow();
                        $this->_objPhpExcel
                            ->getActiveSheet()
                            ->insertNewRowBefore( $firstRowIndex + 1, $this->getRowsLimit() - 1 );

                        $rowsDuplicated = true;
                    }

                    $firstCell = $this->_objPhpExcel
                        ->getActiveSheet()
                        ->getCell( $cells[0] )
                        ->getCoordinate();

                    // Get the dataset
                    if( $this->getFilteredData() != null ){
                        $dataSet = $this->getFilteredData();
                    }else{
                        $dataSet = $this->_wpDataTable->getDataRows();
                    }

                    // Fill in the values from wpDataTable
                    foreach( $dataSet as $rowIndex => $row ){

                        if( $rowIndex >= $this->getRowsLimit() ){ break; }

                        // Fetch the cell value
                        if( $this->getFilteredData() != null ){
                            $cellVal = $row[ array_search( $columnKey, $this->_wpDataTable->getColumnKeys() ) ];
                        }else{
                            $cellVal = $row[$columnKey];
                        }

                        $writeArray[] = array( $this->getCellOutput( $columnKey, $cellVal ) );

                        if( $totalCalc ){
                            if( $this->getFilteredData() != null && is_string( $cellVal ) ){
                                $cellVal = self::unformatNumber( $cellVal );
                            }
                            $totalVal += (float) $cellVal;
                        }

                    }
                    $this->_objPhpExcel
                        ->getActiveSheet()
                        ->fromArray(
                            $writeArray,
                            '',
                            $firstCell,
                            false
                        );

                    if( $totalCalc ){
                        $this->setTemplateVarValue(
                            $columnKey.'.total',
                            $this->getCellOutput( $columnKey, $totalVal )
                        );
                    }

                }
            }

        }

        // Set predefined variables
        $this->setDefaultVariables();

        // Set user-defined variables
        $this->setAdditionalVariables();

        // Prepare the file in temp folder
        $generatedFilePath = sys_get_temp_dir() . '/'.$baseReportFilename.'.xlsx';
        // Save the file
        $this->_phpExcelWriter->save( $generatedFilePath );
        // Save the path to file
        $this->setGeneratedFile( $generatedFilePath );
    }

    /**
     * Fill in multiple files
     * (only if wpDataTable used as a data source)
     */
    public function fillInMultipleFiles(){

        // Generate a new file for each table row
        $counter = 1;
        $reportFiles = array();

        // Prepare a base filename
        $baseReportFilename = !empty( $this->_name ) ? sanitize_file_name( $this->_name ) : 'report';

        // Get the dataset
        if( $this->getFilteredData() != null ){
            $dataSet = $this->getFilteredData();
        }else{
            $dataSet = $this->_wpDataTable->getDataRows();
        }

        foreach( $dataSet as $rowIndex=>$row ) {

            if( $rowIndex >= $this->getRowsLimit() ){ break; }

            $this->_phpExcelReader = IOFactory::createReaderForFile( $this->_templateFile );
            $this->_objPhpExcel = $this->_phpExcelReader->load( $this->_templateFile );
            $this->_phpExcelWriter = IOFactory::createWriter( $this->_objPhpExcel, 'Xlsx' );

            foreach( $this->_wpDataTable->getColumnKeys() as $columnKey ) {

                // Fetch the cell value
                if( $this->getFilteredData() != null ){
                    $cellVal = $row[ array_search( $columnKey, $this->_wpDataTable->getColumnKeys() ) ];
                }else{
                    $cellVal = $row[$columnKey];
                }

                $columnType = $this->_wpDataTable->getColumn($columnKey)->getDataType();

                $this->setTemplateVarValue(
                    $columnKey . '.name',
                    $this->_wpDataTable
                        ->getColumn($columnKey)
                        ->getTitle()
                );

                $writeVal = $this->getCellOutput( $columnKey, $cellVal );

                $this->setTemplateVarValue( $columnKey, $writeVal );

            }

            // Set predefined variables
            $this->setDefaultVariables();

            // Set user-defined variables
            $this->setAdditionalVariables();

            $reportFileName = $this->generateMaskedFilename( $row, $counter ).'.xlsx';
            $this->_phpExcelWriter->save( sys_get_temp_dir() . '/' . $reportFileName );
            $reportFiles[] = $reportFileName;
            $counter++;

            // Freeing the memory
            unset( $this->_phpExcelReader );
            unset( $this->_objPhpExcel );
            unset( $this->_phpExcelWriter );

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
     * Build the XLS or XLSX report
     */
    public function build(){

        /**
         * Disable the timeout
         */
        set_time_limit ( 0 );
        if ($this->_generationLogic == 'single') {
            // Fill in the file
            $this->fillInSingleFile();
        } else {
            if( !empty( $this->_wpDataTable ) ){
                $this->fillInMultipleFiles();
            }
        }

    }

}