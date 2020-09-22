<?php
/*
Pupilsight, Flexible & Open School System
*/

namespace Pupilsight\Contracts\Services;

/**
 * Locale Interface
 *
 * @version	v17
 * @since	v17
 */
interface Locale
{
    /**
     * Sets the locale for a given code.
     *
     * @param string $i18nCode
     */
    public function setLocale($i18nCode);

    /**
     * Gets the current locale code.
     *
     * @return string
     */
    public function getLocale();

    /**
     * Sets the default timezone.
     *
     * @param string $timezoneIdentifier
     */
    public function setTimezone($timezoneIdentifier);

    /**
     * Gets the default timezone.
     *
     * @return string
     */
    public function getTimezone();

    /**
     * Binds the system default text domain.
     *
     * @param string $domain
     * @param string $absolutePath
     */
    public function setSystemTextDomain($absolutePath);

    /**
     * Binds a text domain for a given module by name.
     *
     * @param string $module
     * @param string $absolutePath
     */
    public function setModuleTextDomain($module, $absolutePath);

    /**
     * Translate a string using the current locale and string replacements.
     *
     * @param string $text    Text to Translate.
     * @param array  $params  Assoc array of key value pairs for named
     *                        string replacement.
     * @param array  $options Options for translations (e.g. domain).
     *
     * @return string Translated Text
     */
    public function translate(string $text, array $params = [], array $options = []);
}
