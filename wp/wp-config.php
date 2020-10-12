<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the
 * installation. You don't have to use the web site, you can
 * copy this file to "wp-config.php" and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * MySQL settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://codex.wordpress.org/Editing_wp-config.php
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
include_once($_SERVER['DOCUMENT_ROOT'] . '/config.php');

define( 'DB_NAME', "pupilsight");
//define( 'DB_NAME', "pd_demo");

/** MySQL database username */
//define( 'DB_USER', 'root' );
define( 'DB_USER', "root");

/** MySQL database password */
//define( 'DB_PASSWORD', '' );
define( 'DB_PASSWORD', "");

/** MySQL hostname */
define( 'DB_HOST', "127.0.0.1");

/** Database Charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8mb4' );

/** The Database Collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */

define( 'AUTH_KEY',         '04LWQPB/I03ndtG#*;T#+vTK3vl#DDL*:e34JX&w!?-SYGGwp)Bi`>(DL8tN]-yh' );
define( 'SECURE_AUTH_KEY',  'N`4UCD?DMkA0l/E_KbI8i _1L|t/+%W<k~{{.VVAu[zWvu^&R=D$4()C>P>(<*g%' );
define( 'LOGGED_IN_KEY',    '(zSdws$[v,?@.V{K/v;[IS_J6A6W{g pCa-GQ;E=/#fnBKb@ZHzT[pEew!u5Z&TJ' );
define( 'NONCE_KEY',        '(y.=otb0(a.vQgG;m2sb|bGH(c6pQMCwTp|-F9 MYT?4<>_LTp{vMA*EQCpy#iaf' );
define( 'AUTH_SALT',        'FSQQS@#xG5:InWfpc8&b@krdcZZ/ h@U0.;$J{F&(.*S`tD5)Nt1Ik6e7&LUH$~i' );
define( 'SECURE_AUTH_SALT', 'spCP_p,);7-@|?JwpFZ@Lh Z,XqXrm?OYe`[BCB)V;FJfHWeou5Hj/2g(F{k0}8t' );
define( 'LOGGED_IN_SALT',   '>+}4#YA_7z/0hxlO_}$}zL[0mYx0ndl[#A!^Nh<<!aVir|]EPcPa$4/{HKDGB8_K' );
define( 'NONCE_SALT',       'bc>o7[Sa+.};Vdw,6Q,rxskG(zp`/FiF$jcyGW}#&#+*3eT=~jJ6GVv@MP@qY 81' );

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wp_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the Codex.
 *
 * @link https://codex.wordpress.org/Debugging_in_WordPress
 */
define('FS_METHOD', 'direct');
define( 'WP_DEBUG', true );

/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', dirname( __FILE__ ) . '/' );
}

/** Sets up WordPress vars and included files. */
require_once( ABSPATH . 'wp-settings.php' );
