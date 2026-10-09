<?php
define( 'WP_CACHE', true ); // Added by WP Rocket

# Database Configuration
define( 'DB_NAME', 'loukesir');
define( 'DB_USER', 'loukesir');
define( 'DB_PASSWORD', 'owy41KB4kGSF0Yz' );
define( 'DB_HOST', 'localhost');
define( 'DB_CHARSET', 'utf8' );
define( 'DB_COLLATE', 'utf8_unicode_ci' );
$table_prefix = 'wp_';
//if (! defined('WP_DEBUG') ) { define( 'WP_DEBUG', true ); } // line added by the MyKinsta

define( 'WP_DEBUG_LOG', true );
define( 'WP_DEBUG', true );
define( 'WP_DEBUG_DISPLAY', true );
define( 'WP_DEBUG_LOG', true );

# Security Salts, Keys, Etc
define('AUTH_KEY',         'W#*a%7ZFD)f`]*Ef@`||LUOl,`CIu|(Z7J)Ld[}ZE=J1eN:+#lfE 5m(<6nMU5_>');
define('SECURE_AUTH_KEY',  'Eq@71HfE2iK6:K*~gjp^2!#4<H$xcM]cAoh[y9;&iB;-FB>MC!Uo<Q(u*4DVe0L.');
define('LOGGED_IN_KEY',    'arcejH-%;,K=M#d&7[/gtETXFknN.)G-<aOZL??-L]Hvxo7Fky(}3,=F+1V8F,q-');
define('NONCE_KEY',        'Z?<gYQA;_/:9a`an+A%8uMVP-NN@(QXXMn6B;dJ0t6;j-_(c L9bTS/O;iR5Y{<v');
define('AUTH_SALT',        'CfO0$vDy^1t|k+`R9=.LAeB}9M0>u.)qZAcs,0kwO,~o!~37+SZA}B._;GTD p#Y');
define('SECURE_AUTH_SALT', ';8rkI,j1kRj,=&|q[4JdC5l:yM!|bJX[X; fUUDB`sjda#r2-]1P4g>:awEo]WP/');
define('LOGGED_IN_SALT',   'XBDLUdhjBl .yvMpKiAK;m]BtDS|(9*4uZ{wAs;t,566x*g:ndgVTqgh8/<9U[hv');
define('NONCE_SALT',       '7(P? 0GMAFQag12KB-L!9MZw2!D:j[^hYytrqo0j<WG-8Z|$cajX?NanJ)&-)BCR');

# Localized Language Stuff
define( 'WP_AUTO_UPDATE_CORE', false );
define( 'FS_METHOD', 'direct' );
define( 'FS_CHMOD_DIR', 0755 );
define( 'FS_CHMOD_FILE', 0644 );
define( 'DISALLOW_FILE_MODS', FALSE );
define( 'DISALLOW_FILE_EDIT', FALSE );
define( 'DISABLE_WP_CRON', false );
define( 'COOKIE_DOMAIN', $_SERVER['HTTP_HOST'] );
define( 'WP_POST_REVISIONS', 3 );
define( 'WPLANG', '' );
define( 'WP_MEMORY_LIMIT', '512M' );
define( 'ALLOW_UNFILTERED_UPLOADS', true );

# That's It. Pencils down
if ( !defined('ABSPATH') ) {
  define('ABSPATH', dirname(__FILE__) . '/');
}
set_time_limit(600);
require_once(ABSPATH . 'wp-settings.php');