<?php
/*
 * Initialise avec les microsecondes
 * Utilisé pour la fonction srand
 * http://php.net/manual/fr/function.srand.php
 */
function make_seed()
{
	list($usec, $sec) = explode(' ', microtime());
	return (float) $sec + ((float) $usec * 100000);
}

/*
 * Retourne si la est comprise entre : $min <= $value <= $max
 */
function is_between( $value, $min, $max )
{
	return ($value >= $min && $value <= $max) ? true : false;
}

/*
 * Cette fonction est été utilisé pour "debug"
 */
function print_d( $value ) 
{
	echo "<pre>";
	print_r( $value );
	echo "</pre>";
}

/*
 * Cette fonction enlève les accents
 * Utilisé pour le mode CLI
 */
function strip_accented($str)
{
	$unaccented = array(
			'À' => 'A', 'Á' => 'A', 'Â' => 'A', 'Ã' => 'A', 'Ä' => 'A', 'Å' => 'A',
			'Ç' => 'C',
			'È' => 'E', 'É' => 'E', 'Ê' => 'E', 'Ë' => 'E',
			'Ì' => 'I', 'Í' => 'I', 'Î' => 'I', 'Ï' => 'I',
			'Ò' => 'O', 'Ó' => 'O', 'Ô' => 'O', 'Õ' => 'O', 'Ö' => 'O',
			'Ù' => 'U', 'Ú' => 'U', 'Û' => 'U', 'Ü' => 'U',
			'Ý' => 'Y',
			'à' => 'a', 'á' => 'a', 'â' => 'a', 'ã' => 'a', 'ä' => 'a', 'å' => 'a',
			'ç' => 'c',
			'è' => 'e', 'é' => 'e', 'ê' => 'e', 'ë' => 'e',
			'ì' => 'i', 'í' => 'i', 'î' => 'i', 'ï' => 'i',
			'ò' => 'o', 'ó' => 'o', 'ô' => 'o', 'õ' => 'o', 'ö' => 'o',
			'ù' => 'u', 'ú' => 'u', 'û' => 'u', 'ü' => 'u',
			'ý' => 'y', 'ÿ' => 'y',
	);
	
	return strtr($str, $unaccented);
}

/*
 * Converti la chaine $order_by pour la rendre exploitable en SQL
 */
function get_order_by( $str )
{
	$tmp = explode('-', $str);
	return $tmp[0] . ' ' . strtoupper($tmp[1]);
}
?>