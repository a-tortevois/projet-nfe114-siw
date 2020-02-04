<?php
function unit_test( $bool, $expected ) 
{
	global $user;
	
	if( $user->get_error_num() == $expected ) // ($bool && $user->get_error_num() == 0) || (!$bool && 
		$str = '<span style="font-weight: bold; color: green;">OK</span>';
	else
	{
		$str = '<span style="font-weight: bold; color: red;">NOK';
		// if( $bool == true )
			if( $user->get_error_num() != 0)
			{
				foreach( $user->get_error() as $value )
				{
					$str.= '<dd>'.$value.'</dd>';
				}
			}
			else
				$str.= "<dd>Pas d'erreur ?</dd>";
		// else
			// $str.= "<dd>Pas d'erreur attendue dans la fonction !</dd>";
		$str.='</span>';
	}
	return $str;
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