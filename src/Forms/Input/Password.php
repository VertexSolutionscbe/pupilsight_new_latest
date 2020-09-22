<?php
/*
Pupilsight, Flexible & Open School System
*/

namespace Pupilsight\Forms\Input;

use Pupilsight\Contracts\Database\Connection;

/**
 * Password
 *
 * @version v14
 * @since   v14
 */
class Password extends TextField
{
    /**
     * Attach the validation requirements for the system-wide password policy.
     * @param Connection $pdo
     * @return self
     */
    public function addPasswordPolicy(Connection $pdo)
    {
        $connection2 = $pdo->getConnection();

        $alpha = getSettingByScope($connection2, 'System', 'passwordPolicyAlpha');
        $numeric = getSettingByScope($connection2, 'System', 'passwordPolicyNumeric');
        $punctuation = getSettingByScope($connection2, 'System', 'passwordPolicyNonAlphaNumeric');
        $minLength = getSettingByScope($connection2, 'System', 'passwordPolicyMinLength');

        if ($alpha == 'Y') {
            $this->addValidation('Validate.Format', 'pattern: /.*(?=.*[a-z])(?=.*[A-Z]).*/, failureMessage: "'.__('Does not meet password policy.').'"');
        }
        if ($numeric == 'Y') {
            $this->addValidation('Validate.Format', 'pattern: /.*[0-9]/, failureMessage: "'.__('Does not meet password policy.').'"');
        }
        if ($punctuation == 'Y') {
            $this->addValidation('Validate.Format', 'pattern: /[^a-zA-Z0-9]/, failureMessage: "'.__('Does not meet password policy.').'"');
        }
        if (!empty($minLength) && is_numeric($minLength)) {
            $this->addValidation('Validate.Length', 'minimum: '.$minLength.', failureMessage: "'.__('Does not meet password policy.').'"');
        }

        return $this;
    }

    /**
     * Adds a button to the field that uses JS to generate and insert a password into the form.
     * @param Form $form
     * @return self
     */
    public function addGeneratePasswordButton($form, $sourceField = 'passwordNew', $confirmField = 'passwordConfirm')
    {
        $button = $form->getFactory()->createButton(__('Generate'));
        $button->addClass('generatePassword')
            ->addData('source', $sourceField)
            ->addData('confirm', $confirmField)
            ->addData('alert', __('Copy this password if required:'))
            ->setTabIndex(-1);

        $this->append($button->getOutput());

        return $this;
    }

    /**
     * Adds the validation to indicate this password field is a confirmation for another field.
     * @return self
     */
    public function addConfirmation($fieldName)
    {
        $this->addValidation('Validate.Confirmation', "match: '$fieldName'");

        return $this;
    }

    /**
     * Gets the HTML output for this form element.
     * @return  string
     */
    protected function getElement()
    {
        $output = '<input type="password" '.$this->getAttributeString().' autocomplete="off">';

        return $output;
    }
}
