<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the web site, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://wordpress.org/documentation/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'wpadnan' );

/** Database username */
define( 'DB_USER', 'root' );

/** Database password */
define( 'DB_PASSWORD', '' );

/** Database hostname */
define( 'DB_HOST', 'localhost' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8mb4' );

/** The database collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**#@+
 * Authentication unique keys and salts.
 *
 * Change these to different unique phrases! You can generate these using
 * the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}.
 *
 * You can change these at any point in time to invalidate all existing cookies.
 * This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',         'iGs*$<#6o@@H0>GP2%q6Jt;}VUxxNfpblL2!vI#qZFL0pRdph$492>,ED%]ng@KD' );
define( 'SECURE_AUTH_KEY',  'Bazjh@JBBe(IWCJ* 5psKtCGTsg:Gtuu./1Q#71z{90Nk[M dS99u[Y|Z4~yOB-f' );
define( 'LOGGED_IN_KEY',    'WA8(I)aPr+{7t|k[Ie YP<V`p@&Bj=mHKorzVP8,^K]`yQjzY?iR=t}_I:v|^,q!' );
define( 'NONCE_KEY',        'je+``e>nW0}w-=c:s!pR{`n!UMr]TjAM9q@AIl65$c|I`6HLIOpbf1c3P[TGphC:' );
define( 'AUTH_SALT',        'F iao7Yy%`J6fM$[< ,eGJ$f+/xMHz} `} DD@S2~_XN[iVU8|IP4$(~_NuSE!_f' );
define( 'SECURE_AUTH_SALT', 'i<S=$NZ6z}-%yZj&[dRHo=#[]Tmz6)9YeZc9J64veFHXhN%f6UfM{%-Y[/(Ime*,' );
define( 'LOGGED_IN_SALT',   'c;Qitj#E;9|Mzp!T&A-UmF<E@<BGgKYVf`It=7_{E547$?3xuF&MAt}VZ/IW*%Xt' );
define( 'NONCE_SALT',       'k#OHa.l1x#y!*|x_0.yb<R;+-HF6Lt[hvW+8<d~o{%SDJrM7ckdnP/]wrw5$TYlN' );

/**#@-*/

/**
 * WordPress database table prefix.
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
 * visit the documentation.
 *
 * @link https://wordpress.org/documentation/article/debugging-in-wordpress/
 */
define( 'WP_DEBUG', false );

/* Add any custom values between this line and the "stop editing" line. */



/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
