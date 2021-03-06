<?php
/*
Pupilsight, Flexible & Open School System
*/

namespace Pupilsight\Forms\Input;

/**
 * Date
 *
 * @version v14
 * @since   v14
 */
class Date extends TextField
{
    protected $from;
    protected $to;

    /**
     * Overload the base loadFrom method to handle converting date formats.
     * @param   array  &$data
     * @return  self
     */
    public function loadFrom(&$data)
    {
        $name = str_replace('[]', '', $this->getName());

        if (!empty($data[$name]) && $data[$name] != '0000-00-00') {
            $this->setDateFromValue($data[$name]);
        }

        return $this;
    }

    /**
     * Set the input value by converting a YYYY-MM-DD format back to localized value.
     * @param  string  $value
     * @return  self
     */
    public function setDateFromValue($value)
    {
        global $guid;

        $this->setAttribute('value', dateConvertBack($guid, $value));

        return $this;
    }

    /**
     * Adds date format to the label description (if not already present)
     * @return string|bool
     */
    public function getLabelContext($label)
    {
        global $guid;

        if (stristr($label->getDescription(), 'Format') === false) {
            return __('Format').': '.$_SESSION[$guid]['i18n']['dateFormat'];
        }

        return false;
    }

    /**
     * Provide the ID of another date input to connect the input values in a date range.
     * Chaining a value TO another date range will set the upper limit to that date's value.
     * @param   string  $value
     * @return  self
     */
    public function chainedTo($value)
    {
        $this->to = $value;

        return $this;
    }

    /**
     * Provide the ID of another date input to connect the input values in a date range.
     * Chaining a value FROM another date range will set the lower limit to that date's value.
     * @param   string  $value
     * @return  self
     */
    public function chainedFrom($value)
    {
        $this->from = $value;

        return $this;
    }

    /**
     * Gets the HTML output for this form element.
     * @return  string
     */
    protected function getElement()
    {
        global $guid;

        $validationFormat = '';
        $dateFormat = $_SESSION[$guid]['i18n']['dateFormat'];
        $dateFormatRegex = $_SESSION[$guid]['i18n']['dateFormatRegEx'];
        
        $this->setAttribute('autocomplete', 'off');

        if ($dateFormatRegex == '') {
            $validationFormat .= "pattern: /^(0[1-9]|[12][0-9]|3[01])[- /.](0[1-9]|1[012])[- /.](19|20)\d\d$/i";
        } else {
            $validationFormat .= 'pattern: '.$dateFormatRegex;
        }

        if ($dateFormat == '') {
            $validationFormat .= ', failureMessage: "Use dd/mm/yyyy"';
        } else {
            $validationFormat .= ', failureMessage: "Use '.$dateFormat.'"';
        }

        $this->addValidation('Validate.Format', $validationFormat);

        $today = dateConvertBack($guid, date('Y-m-d'));

        $output = '<input type="text" '.$this->getAttributeString().' maxlength="10">';

        $onSelect = 'function(){$(this).blur();}';

        if ($this->from) {
            $onSelect = 'function() {
                '.$this->from.'.datepicker( "option", "maxDate", getDate(this) );
                $(this).blur();
            }';
        }
        if ($this->to) {
            $onSelect = 'function() {
                '.$this->to.'.datepicker( "option", "minDate", getDate(this) );
                if ($("#'.$this->to.'").val() == "") {
                    '.$this->to.'.datepicker( "setDate", getDate(this) );
                }
                $(this).blur();
            }';
        }

        $output .= '<script type="text/javascript">';
        $output .= '$(function() { '.$this->getID().' = $("#'.$this->getID().'").datepicker({ changeMonth: true, changeYear: true, yearRange: "-50:+50", onSelect: '.$onSelect.', onClose: function(){$(this).change();} }); });';

        if ($this->to || $this->from) {
            $output .= 'function getDate(element) {
                try {
                  return $.datepicker.parseDate("'.substr($dateFormat, 0, 8).'", element.value);
                } catch( error ) {
                  return null;
                }
            }';
        }

        $output .= '</script>';

        return $output;
    }
}
