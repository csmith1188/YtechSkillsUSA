<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the website, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://developer.wordpress.org/advanced-administration/wordpress/wp-config/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'wordpress_skills' );

/** Database username */
define( 'DB_USER', 'ytechskills' );

/** Database password */
define( 'DB_PASSWORD', 'ytechSk1ll$' );

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
define( 'AUTH_KEY',         '2VV[oFXF8Qr1)~%NN_VV&eG?+G(~9<.jb$bzM^z)5.!*_h@hZuhmkU[9bMiS=+Cu' );
define( 'SECURE_AUTH_KEY',  '>!52,<[IDq?-9dg4)Jq}LLVXCcGYj9@1xCB]$`foK|1bYj{|H64FN2X9eWr^[n6{' );
define( 'LOGGED_IN_KEY',    'F74Kt?$!}6agZ/wW_{nZPuT<5!lO{R8(H3D%rNtmhc9bT89mAea16>MKa{&G$5;}' );
define( 'NONCE_KEY',        'DQ(f$9laU1P;x-l[ 0?<#2Rq2i4)6F6._vno>*UZ(32KZ-=+?oH^<<YESGZ2C^O`' );
define( 'AUTH_SALT',        'OlVGOnxlNvN3Nz~^Uu93EQip.<6HaoX=BF(G}iUh A)|^X+b}f,DUVOA~!-+o^d]' );
define( 'SECURE_AUTH_SALT', 'tYcPm/c5@t05mL^]m^gvODeuq)?imM0fjr9:r1Vc!o}DHz3X2c_n_>JyiYZ[uEra' );
define( 'LOGGED_IN_SALT',   '!:TAATW2)|-}NqZ?UvXX]JhwOoq>#]@e%EKQa/Y~))53UHmrer3yEtSJ2tTfhcgM' );
define( 'NONCE_SALT',       '`@29?~/$501O)h5hb+&fxbKWduC*eh76F^U|K*obNb;-&5we]VajV}:b2mg_:^+t' );

/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 *
 * At the installation time, database tables are created with the specified prefix.
 * Changing this value after WordPress is installed will make your site think
 * it has not been installed.
 *
 * @link https://developer.wordpress.org/advanced-administration/wordpress/wp-config/#table-prefix
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
 * @link https://developer.wordpress.org/advanced-administration/debug/debug-wordpress/
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
