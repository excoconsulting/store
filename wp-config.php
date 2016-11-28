<?php
define('WP_CACHE', true); // Added by WP Rocket
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
define('DB_NAME', 'store');

/** MySQL database username */
define('DB_USER', 'root');

/** MySQL database password */
define('DB_PASSWORD', 'root');

/** MySQL hostname */
define('DB_HOST', 'localhost');

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8mb4');

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
define('AUTH_KEY',         'LsfM47Bn=<ZVwb[[-`=4KB3qRm]s,y3Sv9y<fLnWQ&hcO876z&-clEP+N]TLv!zI');
define('SECURE_AUTH_KEY',  '[oV=:H/$Aiq-8,4{0CmZM:Ubq`fG(thfWZ~=F^t&.%0EFS^0D:vdv6*Xja?_?!B^');
define('LOGGED_IN_KEY',    ',B^I<27.6UGd5g;.a*^L^K^2kH@r6t69gbwDtg3}89B7F<QVBdNXyciKJ4-QE?pk');
define('NONCE_KEY',        '.Y%BsmxL,Kh9iVnN_wma)Xd2H5I)c4<4H:=u[xBodOo*6 Iy:,77)&,- }|t{uJJ');
define('AUTH_SALT',        'T.3IDMtPWz)TgAkU7*z(<ml3xow4U;[;0i[:BIw,]LUDcW^@#F}=P,?4EbbMj>zj');
define('SECURE_AUTH_SALT', 'i]|W0N7.sgc$ItJNYy)LxMrux-^[`:DvGoQG/jo[-wGU^)1`?<C1[[N+RZ[deT[<');
define('LOGGED_IN_SALT',   '8z`tmDc~y**F`9vgy{aXGV@pq`CelfLm+t6CLLiz5&Muc(@=_&/FRMLL#eKSr`#,');
define('NONCE_SALT',       'sy~M12>VrcF*O}WR/Pd@v03&~f7Nb%1ND0.o?Cj(1 p7JGg-d<QvIIW|[qb,,vwt');

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

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');
