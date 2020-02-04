<?php
include("../../config.php");
require_once(ROOT_PATH."/class/class.form.php");

$form = new form();
$user = new user(null);
$flag = 0;

echo "\n" . 'Begin unit test '. basename($_SERVER['PHP_SELF']) .' ... ' . "\n";

function unit_test( $return, $expected ) 
{
	global $flag, $form, $var;
	if( $form->get_error_num() !== $expected )
	{
		$flag++;
		echo implode('', $var) . ' | 0x' . dechex($form->get_error_num()) . ' | NOK' . "\n";
	}
	$form->flush_error();
}

for( $i = 0 ; $i < 1000000 ; $i++ ) 
{
	$var = array( 'password' => $user->generate_rand_key($opt = ['dig' => 2, 'low' => 2, 'upp' => 2, 'spe' => 2]) );
	unit_test($form->check_vars($var), 0);

	$var = array( 'password' => $user->generate_rand_key($opt = ['dig' => 0, 'low' => 4, 'upp' => 2, 'spe' => 2]) );
	unit_test($form->check_vars($var), $form->get_err_code('ERR_PASSWORD_NUM'));

	$var = array( 'password' => $user->generate_rand_key($opt = ['dig' => 2, 'low' => 4, 'upp' => 0, 'spe' => 2]) );
	unit_test($form->check_vars($var), $form->get_err_code('ERR_PASSWORD_UPP'));

	$var = array( 'password' => $user->generate_rand_key($opt = ['dig' => 2, 'low' => 4, 'upp' => 2, 'spe' => 0]) );
	unit_test($form->check_vars($var), $form->get_err_code('ERR_PASSWORD_SPE'));

	$var = array( 'password' => $user->generate_rand_key($opt = ['dig' => 1, 'low' => 1, 'upp' => 1, 'spe' => 1]) );
	unit_test($form->check_vars($var), $form->get_err_code('ERR_PASSWORD_LEN'));

	$var = array( 'password' => $user->generate_rand_key($opt = ['dig' => 1, 'low' => 30, 'upp' => 1, 'spe' => 1]) );
	unit_test($form->check_vars($var), $form->get_err_code('ERR_PASSWORD_LEN'));
}

echo "\n" . ($i - $flag) . ' / '. $i .' tests were successfull' . "\n";
echo "\n" . 'Test '. basename($_SERVER['PHP_SELF']) .' done in ' . round(exec_time()-EXEC_TIME,6) . ' seconds' . "\n";
?>