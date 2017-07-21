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
define('WP_CACHE', true); //Added by WP-Cache Manager
define( 'WPCACHEHOME', '/var/www/html/wp-content/plugins/wp-super-cache/' ); //Added by WP-Cache Manager
define('DB_NAME', 'wordpress');

/** MySQL database username */
define('DB_USER', 'wordpress');

/** MySQL database password */
define('DB_PASSWORD', 'e872b804319825ae08330b30b061711ded55b130003e7779');

/** MySQL hostname */
define('DB_HOST', 'localhost');

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8');

/** The Database Collate type. Don't change this if in doubt. */
define('DB_COLLATE', '');

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         '{@@`?sL,D&`^ e!#MpGwxBv}Sa`{!^vdVzAnj+1Yi`^S@zXH+G*6rLdo8:b/OR$/');
define('SECURE_AUTH_KEY',  '?(r){e!_;u?VhB^T,@hAWY.MUwTC(kg3ks?H)u2UpF~o9jG:hrfr@^D>tZ(HB7vq');
define('LOGGED_IN_KEY',    'j]7*A=n]ur~&Kj&lm~n.WQ_9GiLh#<mPe0|QN;xc_UlR)R][^o~Gqw:(t#xhP=yu');
define('NONCE_KEY',        ':Y#)V6W@l@RqL3e98aAY8Q*xoB.Ken6J<wmU7;BMKrhA}Re-8pUH`UFvI(P5g*{$');
define('AUTH_SALT',        '}7Cq,%tI{ZRCZ*fhcWkFuCp>t(%2*jC7e tAp@40BWb:N!>k*Ou)rk+r/!Nb8#4k');
define('SECURE_AUTH_SALT', '*AZgfevX&KU(}G];l<.gHy[JS[dc,,}n>/[N*^H#W2QJzyD0>E7zfriWbj*%[s$+');
define('LOGGED_IN_SALT',   'n:8BtdZd$xv%v!a7;2=_vt!/2XNIwg#zBj u[Ayl>iBu klcs4e1VXrRXU.TOPuc');
define('NONCE_SALT',       'VU rrq<;1w<`g$K7m@kLJTI1#bn2K[jM$BPB/$wB=GXp;:kVdj`6QTA@sT.-c**^');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'wp_';

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
define('WP_DEBUG', false);
// Enable WP_DEBUG mode
define( 'WP_DEBUG', true );

// Enable Debug logging to the /wp-content/debug.log file
define( 'WP_DEBUG_LOG', true );

// Disable display of errors and warnings 
define( 'WP_DEBUG_DISPLAY', false );
@ini_set( 'display_errors', 0 );

// Use dev versions of core JS and CSS files (only needed if you are modifying these core files)
define( 'SCRIPT_DEBUG', true );
/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');
