<?php
require_once("../../config.php");
require_once(ROOT_PATH."/class/class.form.php");

$form = new form();
$user = new user(null);
$flag = 0;

echo '<h4>Begin unit test '. basename($_SERVER['PHP_SELF']) .' ... </h4>';

function unit_test_password( $return, $expected ) 
{
	global $form, $flag;
	if( $form->get_error_num() === $expected )
	{
		$str = '<span style="font-weight: bold; color: green;">OK</span>';
	}
	else
	{
		$flag++;
		$str = '<span style="font-weight: bold; color: red;">NOK';
		if( $form->get_error_num() != 0)
		{
			foreach( $form->get_error() as $value )
			{
				$str.= '<dd>'.$value.'</dd>';
			}
		}
		else
			$str.= "<dd>Pas d'erreur ?</dd>";
		$str.='</span>';
	}
	$form->flush_error();
	return $str;
}

for( $i = 0 ; $i < 100 ; $i++ ) 
{
	$var = array( 'password' => $user->generate_rand_key($opt = ['dig' => 2, 'low' => 2, 'upp' => 2, 'spe' => 2]) );
	echo 'test password_' . $i . '_1 : ' . unit_test_password($form->check_vars($var), 0) . "<br/>";

	$var = array( 'password' => $user->generate_rand_key($opt = ['dig' => 0, 'low' => 4, 'upp' => 2, 'spe' => 2]) );
	echo 'test password_' . $i . '_2 : ' . unit_test_password($form->check_vars($var), $form->get_err_code('ERR_PASSWORD_NUM')) . "<br/>";

	$var = array( 'password' => $user->generate_rand_key($opt = ['dig' => 2, 'low' => 4, 'upp' => 0, 'spe' => 2]) );
	echo 'test password_' . $i . '_3 : ' . unit_test_password($form->check_vars($var), $form->get_err_code('ERR_PASSWORD_UPP')) . "<br/>";

	$var = array( 'password' => $user->generate_rand_key($opt = ['dig' => 2, 'low' => 4, 'upp' => 2, 'spe' => 0]) );
	echo 'test password_' . $i . '_4 : ' . unit_test_password($form->check_vars($var), $form->get_err_code('ERR_PASSWORD_SPE')) . "<br/>";

	$var = array( 'password' => $user->generate_rand_key($opt = ['dig' => 1, 'low' => 1, 'upp' => 1, 'spe' => 1]) );
	echo 'test password_' . $i . '_5 : ' . unit_test_password($form->check_vars($var), $form->get_err_code('ERR_PASSWORD_LEN')) . "<br/>";

	$var = array( 'password' => $user->generate_rand_key($opt = ['dig' => 1, 'low' => 30, 'upp' => 1, 'spe' => 1]) );
	echo 'test password_' . $i . '_6 : ' . unit_test_password($form->check_vars($var), $form->get_err_code('ERR_PASSWORD_LEN')) . "<br/>";
}

echo "<br/>" . ($i - $flag) . ' / '. $i .' tests were successfull' . "<br/>";
echo "<br/>" . 'Test '. basename($_SERVER['PHP_SELF']) .' done in ' . round(exec_time()-EXEC_TIME,6) . ' seconds' . "<br/>";
?>