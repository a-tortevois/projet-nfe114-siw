<?php
error_reporting(E_ALL); // mettre à 0 pour la production
ini_set('default_charset', 'utf-8' );
header('Content-Type: text/html; charset=utf-8');

/*
 * Pour déterminer le temps d'exécution de l'application
 */
function exec_time()
{
    $t = explode(' ', microtime());
    return $t[0]+$t[1];
}
define('EXEC_TIME', exec_time());

// Session
session_start();
if( !isset($_SESSION['connected']) )
	$_SESSION['connected'] = 0;

// POUR LE DEV -->
define('DEBUG_TRACE_SQL', 0);
define('RESUME_TRACE_SQL', 0);
// <--

// Nom du site
define('SITE_NAME', 'My WishList'); 
// URL du site 
define('SITE_URL', 'http://mywishlist.tortevois.fr/'); 
// Adresse mail utilisé pour envoyer les mails
define('FROM_MAIL', 'mywishlist@tortevois.fr'); 
// Nom utilisé pour envoyer les mails
define('FROM_NAME', 'My WishList'); 
// Nombre de ligne max par page
define('VIEW_PER_PAGE', 15); 
// Coût de l'algorithmique qui doit être utilisé pour password_hash
define('PWD_HASH_COST', 10); 

/*
 * Détermine le répertoire racine et "set" la constante ROOT_PATH
 */
$search_path = 'mywishlist'; // modifier le $search_path en fonction du dossier racine
if( preg_match('/[\\\]/', $_SERVER['PHP_SELF']) ) // Cas CLI : chemin absolu
{
	$cli = true;
	$path = str_replace( "\\", "/", dirname($_SERVER['PHP_SELF']));
	$offset = 0;
}
else if( preg_match('#/#', $_SERVER['PHP_SELF']) ) // Cas serveur : chemin relatif
{
	$cli = false;
	$path = dirname($_SERVER['PHP_SELF']);
	$offset = ( preg_match( '`'.$search_path.'`', $_SERVER['HTTP_HOST']) ) ? 0 : 1;
}
else
{
	echo 'Error : path not found' . PHP_EOL;
	exit;
}

if( $path[0] == "/" )
	$path = substr( $path, 1 );

if( substr( $path, -1 ) == "/" )
	$path = substr( $path, 0, -1 );

$dir = explode('/', $path);
$nb_dir = count($dir) - $offset;
$path = '';

if( strlen(dirname($_SERVER['PHP_SELF'])) > 1 && $nb_dir > 0) // exclus la racine du sous-domaine
{
	for( $i=0 ; $i < $nb_dir ; $i++)
	{
		if( $cli )
		{
			$path.= $dir[$i].'/';
			if( preg_match("`".$search_path."`",$dir[$i]) )
				break;
		}
		else
		{
			$path.= '../';
		}
	}
	$path = substr( $path, 0, -1 );
}
else
{
	$path = ($cli) ? '' : '.';
}
define('ROOT_PATH', $path);
// <-- END ROOT_PATH

// Include
require_once(ROOT_PATH."/class/class.mysql.php");
require_once(ROOT_PATH."/class/class.page.php");
require_once(ROOT_PATH."/class/class.form.php");
require_once(ROOT_PATH."/class/class.user.php");
require_once(ROOT_PATH."/class/class.gift.php");
require_once(ROOT_PATH."/class/class.wishlist.php");
require_once(ROOT_PATH."/functions.php");

// Initialisation de $mysqli
$mysqli = new My_SQL();
?>