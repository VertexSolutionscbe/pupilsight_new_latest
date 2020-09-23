<?php
/*
Pupilsight, Flexible & Open School System
*/

namespace Pupilsight\Forms\Input;

use Pupilsight\Forms\FormFactoryInterface;

/**
 * PhoneNumber
 *
 * @version v14
 * @since   v14
 */
class PhoneNumber extends Input
{
    protected $column;

    protected $phoneType;
    protected $phoneCodes;
    protected $phoneNumber;

    protected $countryCodes = array();

    /**
     * Create a number input that holds an internal Column object of phoneType, phoneCodes, and phoneNumber inputs.
     * @param  FormFactoryInterface  &$factory
     * @param  string                $name
     * @param  array                 $countryCodes
     */
    public function __construct(FormFactoryInterface &$factory, $name, $countryCodes = array())
    {
        $this->setName($name);
        $this->setClass('');

        $types = array(
            'Mobile' => __('Mobile'),
            'Home'   => __('Home'),
            'Work'   => __('Work'),
            'Fax'    => __('Fax'),
            'Pager'  => __('Pager'),
            'Other'  => __('Other'),
        );

        // Create an internal column to hold the set of phone number fields
        $this->column = $factory->createColumn();

        $this->phoneType = $this->column
            ->addSelect($name.'Type')
            ->fromArray($types)
            ->placeholder()
            ->setClass('mr-1 w-1/3 sm:w-1/4');
        
        $this->phoneCodes = $this->column
            ->addSelect($name.'CountryCode')
            ->fromArray($countryCodes)
            ->placeholder()
            ->setClass('mr-1 w-1/3 sm:w-1/4');
            
        $this->phoneNumber = $this->column
            ->addTextField($name)
            ->setClass('w-2/3 sm:w-1/2');
    }

    /**
     * Set an array of possible country codes.
     * @param  array  $countryCodes
     * @return self
     */
    public function setCountryCodeOptions($countryCodes)
    {
        $this->phoneCodes->fromArray($countryCodes);

        return $this;
    }

    /**
     * Set the phone number.
     * @param  array  $value
     * @return self
     */
    public function setValue($value = '')
    {
        $this->phoneNumber->setValue($value);
        return $this;
    }

    /**
     * Gets the current phone number value.
     * @return  string
     */
    public function getValue()
    {
        return $this->phoneNumber->getValue();
    }

    /**
     * Pass an array of $key => $value pairs into the internal Column object.
     * @param   string  &$data
     * @return  object Column
     */
    public function loadFrom(&$data)
    {
        return $this->column->loadFrom($data);
    }

    /**
     * Get the validation output from the internal Column object.
     * @return  string
     */
    public function getValidationOutput()
    {
        return $this->column->getValidationOutput();
    }

    /**
     * Gets the HTML output for this form element.
     * @return  string
     */
    protected function getElement()
    {
        // Pass any specific attributes along to the phone number field
        $this->phoneType->setRequired($this->getRequired());
        $this->phoneCodes->setRequired($this->getRequired());
        $this->phoneNumber->setRequired($this->getRequired());

        $this->phoneNumber->setSize($this->getSize());
        $this->phoneNumber->setDisabled($this->getDisabled());

        $output = '<div class="w-full sm:max-w-xs flex justify-between">';
        $output .= $this->phoneType->getElement();
        $output .= $this->phoneCodes->getElement();
        $output .= $this->phoneNumber->getElement();
        $output .= '</div>';

        return $output;
    }
}
