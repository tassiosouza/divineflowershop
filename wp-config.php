<?php /* BEGIN KINSTA DEVELOPMENT ENVIRONMENT - DO NOT MODIFY THIS CODE BLOCK */ ?>
<?php if ( !defined('KINSTA_DEV_ENV') ) { define('KINSTA_DEV_ENV', true); /* Kinsta development - don't remove this line */ } ?>
<?php if ( !defined('JETPACK_STAGING_MODE') ) { define('JETPACK_STAGING_MODE', true); /* Kinsta development - don't remove this line */ } ?>
<?php /* END KINSTA DEVELOPMENT ENVIRONMENT - DO NOT MODIFY THIS CODE BLOCK */ ?>
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
 * * Localized language
 * * ABSPATH
 *
 * @link https://wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'Divine_Flower_Shop' );

/** Database username */
define( 'DB_USER', 'root' );

/** Database password */
define( 'DB_PASSWORD', 'AoccDB2YyqwUlFTu' );

/** Database hostname */
define( 'DB_HOST', 'devkinsta_db' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8' );

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
define( 'AUTH_KEY',          'p-j0jsPH@DuF8JSp3!Etjt$.vIF.!Sp},rXeSU@.BJF_O(3i1.7L,j)#/ph}~6d|' );
define( 'SECURE_AUTH_KEY',   'E0kL4dYkwt!l?;::__U`iunz8v~2+YH3c9I!Z*WSkj;HfJ!%c&Z)M.JP/>T/j^Cm' );
define( 'LOGGED_IN_KEY',     '!&[ #+dEQhq5_WfMT~cDENx1~EbXHLuER&!I~sl_XbJ4y8cY2:DP`_)KE&ew{LNj' );
define( 'NONCE_KEY',         'kGt2ck[1AC?RS6kbBM+N&1/uLZ8PtD>^g-]D`+U|Rj7<~Vcv <}r.uMHCUs:i>g@' );
define( 'AUTH_SALT',         '#MFOhGe^0aK_AD0W!BpU.IHZ5Mdb1D PO^F?t2utTu1t+pNtnGCPS|oPw4fPm+Ix' );
define( 'SECURE_AUTH_SALT',  '^qK1>Ea;~P_(?Kv1d(5X<`EotoZH>D#wD]%46_.7VO!4xNUbzFT,5&l{GHp~TRM|' );
define( 'LOGGED_IN_SALT',    'RO!D)Wg*EMsqCg305BJ5>:e:q~^k@rb&*YYf;~kP,%#NE8!6II5iGvnXYOkyg|$j' );
define( 'NONCE_SALT',        'ZmE4`]]G?x0lZju1E#VzeSjPFNp?S2!SEXmDSj&>,(Yrhfusg{}buv- bnoo<Udj' );
define( 'WP_CACHE_KEY_SALT', '^80k}Sj:N^a,l$<aO ?vEtx*Kcu:XH4|{@a),lR=VbWC}mj5?^/Z7]XGAIzUw|yW' );


/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wp_';


/* Add any custom values between this line and the "stop editing" line. */



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
 * @link https://wordpress.org/support/article/debugging-in-wordpress/
 */
if ( ! defined( 'WP_DEBUG' ) ) {
	define( 'WP_DEBUG', false );
}

/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
