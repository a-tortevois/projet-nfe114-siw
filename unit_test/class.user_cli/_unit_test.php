<?php
function unit_test( $bool, $expected ) 
{
	global $user;
	if( $user->get_error_num() == $expected ) // $bool == true && 
		$str = "\033[032m" . 'OK';
	else
	{
		$str = "\033[031m". 'NOK' . "\n\t";
		// if( $bool == true )
			if( $user->get_error_num() != 0)
				$str.= substr( implode("\n\t", $user->get_error()), 0, -1 );
			else
				$str.= "Pas d'erreur ?\n";
		// else
			// $str.= "Pas d'erreur attendue dans la fonction !";
	}
	return strip_accented($str)."\033[0m";
}

function init_mwl_users() 
{
	global $mysqli;
	$sql = 'TRUNCATE TABLE mwl_users';
	if( !($mysqli->my_query($sql)) )
	{
		trigger_error("Unable to initialize the mwl_users table");
		exit;
	}
}
?>