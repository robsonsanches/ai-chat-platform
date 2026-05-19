<?php
/* Blowfish para criptografia de cookies */
$cfg['blowfish_secret'] = getenv('PMA_BLOWFISH_SECRET');


/**
 * Servers configuration
 */
$i = 0;

/**
 * First server
 */
$i++;
/* Authentication type */
$cfg['Servers'][$i]['auth_type'] = 'cookie';
$cfg['Servers'][$i]['hide_db'] = 'information_schema|performance_schema|mysql|phpmyadmin|sys';

?>