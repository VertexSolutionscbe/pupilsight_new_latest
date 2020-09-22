<?php
/*
Pupilsight, Flexible & Open School System
*/

namespace Pupilsight\Forms\Input;

use Pupilsight\Forms\Element;

/**
 * TextField
 *
 * @version v14
 * @since   v14
 */
class TextField extends Input
{
    protected $autocomplete;
    protected $unique;

    /**
     * Set a max character count for this text field.
     * @param   string  $value
     * @return  self
     */
    public function maxLength($value = '')
    {
        if (!empty($value)) {
            $this->setAttribute('maxlength', $value);
            $this->addValidation('Validate.Length', 'maximum: '.$value);
        }

        return $this;
    }

    /**
     * Set the default text that appears before any text has been entered.
     * @param   string  $value
     * @return  self
     */
    public function placeholder($value = '')
    {
        $this->setAttribute('placeholder', $value);

        return $this;
    }

    /**
     * Enables javascript autocompletion from the supplied set of values.
     * @param   string|array  $value
     * @return  self
     */
    public function autocomplete($value = '')
    {
        $this->autocomplete = (is_array($value))? $value : array($value);
        $this->setAttribute('autocomplete', 'on');

        return $this;
    }

    /**
     * @deprecated Remove setters that start with isXXX for code consistency.
     */
    public function isUnique($ajaxURL, $data = [])
    {
        return $this->uniqueField($ajaxURL, $data);
    }

    /**
     * Add an AJAX uniqueness check to this field using the given URL.
     *
     * @param string $ajaxURL
     * @param array $data
     * @return self
     */
    public function uniqueField($ajaxURL, $data = [])
    {
        $label = $this->row->getElement('label'.$this->getName());
        $fieldLabel = (!empty($label))? $label->getLabelText() : ucfirst($this->getName());

        $this->unique = array(
            'ajaxURL'      => $ajaxURL,
            'ajaxData'     => array_replace(array('fieldName' => $this->getName()), $data),
            'alertSuccess' => sprintf(__('%1$s available'), $fieldLabel),
            'alertFailure' => sprintf(__('%1$s already in use'), $fieldLabel),
            'alertError'   => __('An error has occurred.'),
        );

        return $this;
    }

    /**
     * Adds uniqueness text to the label description (if not already present)
     * @return string|bool
     */
    public function getLabelContext($label)
    {
        if (!empty($this->unique)) {
            return __('Must be unique.');
        }

        return false;
    }

    /**
     * Gets the HTML output for this form element.
     * @return  string
     */
    protected function getElement()
    {
        $output = '<input type="text" '.$this->getAttributeString().'>';

        if (!empty($this->autocomplete)) {
            $source = implode(',', array_map(function ($str) { return sprintf('"%s"', $str); }, $this->autocomplete));
            $output .= '<script type="text/javascript">';
            $output .= '$("#'.$this->getID().'").autocomplete({source: ['.$source.']});';
            $output .= '</script>';
        }

        if (!empty($this->unique)) {
            $output .= '<script type="text/javascript">
                $("#'.$this->getID().'").pupilsightUniquenessCheck('.json_encode($this->unique).');
            </script>';
        }

        return $output;
    }
}
