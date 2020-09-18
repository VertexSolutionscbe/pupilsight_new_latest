<?php
/*
Pupilsight, Flexible & Open School System
*/

namespace Pupilsight;

use Pupilsight\Contracts\Services\Session as SessionInterface;
use Pupilsight\Contracts\Database\Connection;
use Psr\Container\ContainerInterface;

/**
 * Session Class
 *
 * @version	v13
 * @since	v12
 */
class Session implements SessionInterface
{
    /**
     * string
     */
    private	$guid ;

    /**
     * Pupilsight\Contracts\Database\Connection
     */
    private	$pdo ;

    /**
     * Construct
     */
    public function __construct(ContainerInterface $container)
    {
        global $guid;

        // Start the session (this should be the first time called)
        if (session_status() !== PHP_SESSION_ACTIVE) {
            //Prevent breakage of back button on POST pages
            ini_set('session.cache_limiter', 'private');
            session_cache_limiter(false);

            $options = [
                'cookie_httponly'  => true,
                'cookie_secure'    => isset($_SERVER['HTTPS']),
            ];

            if (version_compare(phpversion(), '7.3.0', '>=')) {
                $options['cookie_samesite'] = 'Strict';
            }

            session_start($options);

           // header('X-Frame-Options: SAMEORIGIN');
        }

        // Backwards compatibility for external modules
        $this->guid = $container->has('config')? $container->get('config')->guid() : $guid;

        // Detect the current module from the GET 'q' param. Fallback to the POST 'address',
        // which is currently used in many Process pages.
        // TODO: replace this logic when switching to routing.
        $address = $_GET['q'] ?? $_POST['address'] ?? '';

        $this->set('address', $address);
        $this->set('module', $address ? getModuleName($address) : '');
        $this->set('action', $address ? getActionName($address) : '');
        $this->set('guid', $this->guid);
    }

    public function setGuid(string $_guid)
    {
        $this->guid = $_guid;
    }

    /**
     * Set Database Connection
     * @version  v13
     * @since    v13
     * @param    Pupilsight\Contracts\Database\Connection  $pdo
     */
    public function setDatabaseConnection(Connection $pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Return the guid string
     * TODO: Remove this
     *
     * @return	string
     */
    public function guid() {
        return $this->guid;
    }

    /**
     * Checks if one or more keys exist.
     *
     * @param  string|array  $keys
     * @return bool
     */
    public function exists($keys)
    {
        $keys = is_array($keys)? $keys : [$keys];
        $exists = !empty($keys);

        foreach ($keys as $key) {
            $exists &= array_key_exists($key, $_SESSION[$this->guid]);
        }

        return $exists;
    }

    /**
     * Checks if one or more keys are present and not null.
     *
     * @param  string|array  $key
     * @return bool
     */
    public function has($keys)
    {
        $keys = is_array($keys)? $keys : [$keys];
        $has = !empty($keys);

        foreach ($keys as $key) {
            $has &= !empty($_SESSION[$this->guid][$key]);
        }

        return $has;
    }

    /**
     * Get an item from the session.
     *
     * @param	string	$key
     * @param	mixed	$default Define a value to return if the variable is empty
     *
     * @return	mixed
     */
    public function get($key, $default = null)
    {
        if (is_array($key)) {
            // Fetch a value from multi-dimensional array with an array of keys
            $retrieve = function($array, $keys, $default) {
                foreach($keys as $key) {
                    if (!isset($array[$key])) return $default;
                    $array = $array[$key];
                }
                return $array;
            };

            return $retrieve($_SESSION[$this->guid], $key, $default);
        }

        return (isset($_SESSION[$this->guid][$key]))? $_SESSION[$this->guid][$key] : $default;
    }

    /**
     * Set a key / value pair or array of key / value pairs in the session.
     *
     * @param	string	$key
     * @param	mixed	$value
     */
    public function set($key, $value = null)
    {
        $keyValuePairs = is_array($key)? $key : [$key => $value];

        foreach ($keyValuePairs as $key => $value) {
            $_SESSION[$this->guid][$key] = $value ;
        }
    }

    /**
     * Remove an item from the session, returning its value.
     *
     * @param  string  $key
     * @return mixed
     */
    public function remove($key)
    {
        $value = $this->get($key);
        unset($_SESSION[$this->guid][$key]);

        return $value;
    }

    /**
     * Remove one or many items from the session.
     *
     * @param  string|array  $keys
     */
    public function forget($keys)
    {
        $keys = is_array($keys)? $keys : [$keys];

        foreach ($keys as $key) {
            $this->remove($key);
        }
    }

    public function loadSystemSettings(Connection $pdo)
    {
        // System settings from pupilsightSetting
        $sql = "SELECT name, value FROM pupilsightSetting WHERE scope='System'";
        $result = $pdo->executeQuery(array(), $sql);

        while ($row = $result->fetch()) {
            $this->set($row['name'], $row['value']);
        }
    }

    public function loadLanguageSettings(Connection $pdo)
    {
        // Language settings from pupilsighti18n
        $sql = "SELECT * FROM pupilsighti18n WHERE systemDefault='Y'";
        $result = $pdo->executeQuery(array(), $sql);

        while ($row = $result->fetch()) {
            $this->set('i18n', $row);
        }
    }

    public function createUserSession($username, $userData) {

        $this->set('username', $username);
        $this->set('passwordStrong', $userData['passwordStrong']);
        $this->set('passwordStrongSalt', $userData['passwordStrongSalt']);
        $this->set('passwordForceReset', $userData['passwordForceReset']);
        $this->set('pupilsightPersonID', $userData['pupilsightPersonID']);
        $this->set('surname', $userData['surname']);
        $this->set('firstName', $userData['firstName']);
        $this->set('preferredName', $userData['preferredName']);
        $this->set('officialName', $userData['officialName']);
        $this->set('email', $userData['email']);
        $this->set('emailAlternate', $userData['emailAlternate']);
        $this->set('website', filter_var($userData['website'], FILTER_VALIDATE_URL));
        $this->set('gender', $userData['gender']);
        $this->set('status', $userData['status']);
        $this->set('pupilsightRoleIDPrimary', $userData['pupilsightRoleIDPrimary']);
        $this->set('pupilsightRoleIDCurrent', $userData['pupilsightRoleIDPrimary']);
        $this->set('pupilsightRoleIDCurrentCategory', getRoleCategory($userData['pupilsightRoleIDPrimary'], $this->pdo->getConnection()) );
        $this->set('pupilsightRoleIDAll', getRoleList($userData['pupilsightRoleIDAll'], $this->pdo->getConnection()) );
        $this->set('image_240', $userData['image_240']);
        $this->set('lastTimestamp', $userData['lastTimestamp']);
        $this->set('calendarFeedPersonal', filter_var($userData['calendarFeedPersonal'], FILTER_VALIDATE_EMAIL));
        $this->set('viewCalendarSchool', $userData['viewCalendarSchool']);
        $this->set('viewCalendarPersonal', $userData['viewCalendarPersonal']);
        $this->set('viewCalendarSpaceBooking', $userData['viewCalendarSpaceBooking']);
        $this->set('dateStart', $userData['dateStart']);
        $this->set('personalBackground', $userData['personalBackground']);
        $this->set('messengerLastBubble', $userData['messengerLastBubble']);
        $this->set('pupilsighti18nIDPersonal', $userData['pupilsighti18nIDPersonal']);
        $this->set('googleAPIRefreshToken', $userData['googleAPIRefreshToken']);
        $this->set('receiveNotificationEmails', $userData['receiveNotificationEmails']);
        $this->set('pupilsightHouseID', $userData['pupilsightHouseID']);

        //Deal with themes
        $this->set('pupilsightThemeIDPersonal', null);
        if (!empty($userData['pupilsightThemeIDPersonal'])) {
            $data = array( 'pupilsightThemeID' => $userData['pupilsightThemeIDPersonal']);
            $sql = "SELECT pupilsightThemeID FROM pupilsightTheme WHERE active='Y' AND pupilsightThemeID=:pupilsightThemeID";
            $result = $this->pdo->executeQuery($data, $sql);

            if ($result->rowCount() > 0) {
                $this->set('pupilsightThemeIDPersonal', $userData['pupilsightThemeIDPersonal']);
            }
        }

        // Cache FF actions on login
        $this->cacheFastFinderActions($userData['pupilsightRoleIDPrimary']);
    }

    /**
     * Cache translated FastFinder actions to allow searching actions with the current locale
     * @version  v13
     * @since    v13
     * @param    string  $pupilsightRoleIDCurrent
     */
    public function cacheFastFinderActions($pupilsightRoleIDCurrent) {

        // Get the accesible actions for the current user
        $data = array( 'pupilsightRoleID' => $pupilsightRoleIDCurrent );
        $sql = "SELECT DISTINCT concat(pupilsightModule.name, '/', pupilsightAction.entryURL) AS id, SUBSTRING_INDEX(pupilsightAction.name, '_', 1) AS name, pupilsightModule.type, pupilsightModule.name AS module
                FROM pupilsightModule
                JOIN pupilsightAction ON (pupilsightAction.pupilsightModuleID=pupilsightModule.pupilsightModuleID)
                JOIN pupilsightPermission ON (pupilsightPermission.pupilsightActionID=pupilsightAction.pupilsightActionID)
                WHERE active='Y'
                AND menuShow='Y'
                AND pupilsightPermission.pupilsightRoleID=:pupilsightRoleID
                ORDER BY name";

        $result = $this->pdo->executeQuery($data, $sql);

        if ($result->rowCount() > 0) {
            $actions = array();

            // Translate the action names
            while ($row = $result->fetch()) {
                $row['name'] = __($row['name']);
                $actions[] = $row;
            }

            // Cache the resulting set of translated actions
            $this->set('fastFinderActions', $actions);
        }
        return $actions;
    }
}
