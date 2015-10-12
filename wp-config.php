<?php
/**
 * The base configurations of the WordPress.
 *
 * This file has the following configurations: MySQL settings, Table Prefix,
 * Secret Keys, WordPress Language, and ABSPATH. You can find more information
 * by visiting {@link http://codex.wordpress.org/Editing_wp-config.php Editing
 * wp-config.php} Codex page. You can get the MySQL settings from your web host.
 *
 * This file is used by the wp-config.php creation script during the
 * installation. You don't have to use the web site, you can just copy this file
 * to "wp-config.php" and fill in the values.
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define('DB_NAME', 'hillswomen');

/** MySQL database username */
define('DB_USER', 'root');

/** MySQL database password */
define('DB_PASSWORD', 'root');

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
define('AUTH_KEY',         'd!>DUIoAkN9*#/QSvL+~.V+/wgz/nz@7MqD*7>UVwM~:i]!<AJA7Im(mGUU.bm~P');
define('SECURE_AUTH_KEY',  'ylNUv`@c53Ys5`g`#`a(EB$)^Q6D*]KeTcC:eVdz2f~6<<a(*4^aA)1&j7MkXT@W');
define('LOGGED_IN_KEY',    'l&?bZA|c>}ef|YbBT;xf}+6AZYB-4M-1XmqYd&m|^H{[%N{,]@nj*DqOPgU7W~p[');
define('NONCE_KEY',        'YC|L|=g-WcmQ9$-F=[t_%`J>IZ-k2wKkA%aEnoB.*w fevL4/QzL527fw2oeQOJ-');
define('AUTH_SALT',        'g^i*hB$TY[$UZZ`1aHGaF}QA,7u%;VvfG+GNpr+t2QO jy9E/+v&D|VvJiqh0Hd-');
define('SECURE_AUTH_SALT', '~qYP|?1W,ucAG1=--.DS_O9U[6i_TGplCa$*tr%ZU7~Og5[[d:1MqFLs9}ZNX*MQ');
define('LOGGED_IN_SALT',   'jOJ8QW>]p3YgG9-d@J_x:?r<bGw(;rQcP V[.hK-rif-C7<y!9R<Ta#:)UjrWjWh');
define('NONCE_SALT',       '8Kla&:E$b;3,?*Sp;eIoY=|Ql~~yjDc2ZIVBHpw<-Fh_qf18a$~|l[3D0m4yxhnO');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each a unique
 * prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'wp_';

/**
 * WordPress Localized Language, defaults to English.
 *
 * Change this to localize WordPress. A corresponding MO file for the chosen
 * language must be installed to wp-content/languages. For example, install
 * de_DE.mo to wp-content/languages and set WPLANG to 'de_DE' to enable German
 * language support.
 */
define('WPLANG', '');

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 */
define('WP_DEBUG', false);

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');
