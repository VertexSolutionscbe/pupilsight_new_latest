<?php


namespace WDTReportBuilder;

use PhpOffice\PhpWord\TemplateProcessor as TemplateProcessor;

class TemplateProcessorExtend extends TemplateProcessor
{

    public function __construct($documentTemplate)
    {
        parent::__construct($documentTemplate);
    }

    /**
     * Clone a block.
     *
     * @param string $blockname
     * @param integer $clones
     * @param boolean $replace
     *
     * @return string|null
     */
    public function cloneBlock($blockname, $clones = 1, $replace = true)
    {
        $xmlBlock = null;
        preg_match(
            '/(<\?xml.*)(<w:p\b.*>\${' . $blockname . '}<\/w:.*?p>)(.*)(<w:p\b.*\${\/' . $blockname . '}<\/w:.*?p>)/is',
            $this->tempDocumentMainPart,
            $matches
        );

        if (isset($matches[3])) {
            $xmlBlock = $matches[3];
            $cloned = array();
            for ($i = 1; $i <= $clones; $i++) {
                // CHANGED FOR REPORTBUILDER
                $cloned[] = preg_replace('/\$\{(.*?)\}/', '\${\\1#' . $i . '}', $xmlBlock);
            }

            if ($replace) {
                $this->tempDocumentMainPart = str_replace(
                    $matches[2] . $matches[3] . $matches[4],
                    implode('', $cloned),
                    $this->tempDocumentMainPart
                );
            }
        }
        return $xmlBlock;
    }
}
