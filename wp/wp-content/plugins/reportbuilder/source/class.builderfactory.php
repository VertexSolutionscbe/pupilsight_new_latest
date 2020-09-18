<?php

namespace WDTReportBuilder;

/**
 * @author Alexander Gilmanov
 * @since February 2016
 */

/**
 * Class BuilderFactory
 * Factory which creates one of the child builders depending on the template
 * @package WDTReportBuilder
 */
class BuilderFactory {

    /**
     * Helper function to determine extension based on the file path
     * @param $templateFile
     * @return string Extension
     */
    public static function getExtension( $templateFile ){
        return pathinfo( $templateFile, PATHINFO_EXTENSION );
    }

    /**
     * Function that generates a builder based on the given file type
     * @param $templateFile Path to the file
     */
    public static function factory( $templateFile ){
        if( file_exists( $templateFile ) ){
            require_once( WDT_RB_ROOT_PATH.'/source/class.abstract.builder.php' );
            switch( self::getExtension( $templateFile ) ){
                case 'doc':
                case 'odt':
                case 'docx':
                    require_once( WDT_RB_ROOT_PATH.'/source/class.document.builder.php' );
                    require_once( WDT_RB_ROOT_PATH.'/source/class.template.processor.extend.php' );
                    return new DocumentBuilder( $templateFile );
                    break;
                case 'xls':
                case 'xlsx':
                    require_once( WDT_RB_ROOT_PATH.'/source/class.spreadsheet.builder.php' );
                    return new SpreadsheetBuilder( $templateFile );
                    break;
            }
        }
    }

}

?>