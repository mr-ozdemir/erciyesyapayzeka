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
define( 'DB_NAME', 'erciyesy_wp74' );

/** Database username */
define( 'DB_USER', 'erciyesy_wp74' );

/** Database password */
define( 'DB_PASSWORD', 'S40-)Ghp1C' );

/** Database hostname */
define( 'DB_HOST', 'localhost' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8mb4' );

/** The database collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );


/**
 * Topluluk login MU-plugin'i için ayrı auth DB ayarları.
 * XAMPP'ta gerekirse bu değerleri localinize göre düzenleyin.
 */
if ( ! defined( 'COMMUNITY_AUTH_DB_HOST' ) ) define( 'COMMUNITY_AUTH_DB_HOST', '127.0.0.1' );
if ( ! defined( 'COMMUNITY_AUTH_DB_PORT' ) ) define( 'COMMUNITY_AUTH_DB_PORT', 3306 );
if ( ! defined( 'COMMUNITY_AUTH_DB_NAME' ) ) define( 'COMMUNITY_AUTH_DB_NAME', 'erciyesy_eruai' );
if ( ! defined( 'COMMUNITY_AUTH_DB_USER' ) ) define( 'COMMUNITY_AUTH_DB_USER', 'root' );
if ( ! defined( 'COMMUNITY_AUTH_DB_PASS' ) ) define( 'COMMUNITY_AUTH_DB_PASS', '' );
if ( ! defined( 'COMMUNITY_AUTH_DB_CHARSET' ) ) define( 'COMMUNITY_AUTH_DB_CHARSET', 'utf8mb4' );

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
define( 'AUTH_KEY',         '5tboair5bfbbw90cq2is1tmwaiawfzfshmhb6kfkj93ayvl47u0i8vuperituwdz' );
define( 'SECURE_AUTH_KEY',  'rfhhotebrxz1qjvi7lqhzygb6e57phoaolcjbk7hpg5ul5ta7spb6xhoyhyxrxjr' );
define( 'LOGGED_IN_KEY',    'jaz6fty2al03ggcfgadofczlgliyzu4f1vb3yvdeopqynlj1xfpcldlmhl6lfnpt' );
define( 'NONCE_KEY',        'kvzxd3bmnyrde64mhdfkrbmu9uqjq5ozatzoppt1va7uylhka7mesycwd0nc4v8d' );
define( 'AUTH_SALT',        '6knrxra7tjkmjen8xfzwoeiovkst7fyzoinehujxremdhc6yjnt8y2gqoayml0qr' );
define( 'SECURE_AUTH_SALT', 'rri9nwql0r3kowkwazquuuztrw5pqvyjrm4sj39hkrj0g4ohxdruimelzjflb5gf' );
define( 'LOGGED_IN_SALT',   'ttiq40czfrhsedvztzmqbsf5w32rdpppscflhxmvwba962erfykc6yd9bit7ly8c' );
define( 'NONCE_SALT',       'l9y7dweggowjdz1cfakdeovixchsidqb1no1pyzqkok8uwdfk12am7pngysss81s' );

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
$table_prefix = 'wpey_';

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
