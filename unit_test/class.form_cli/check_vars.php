<?php
include("../../config.php");
require_once(ROOT_PATH."/class/class.form.php");

$form = new form();
$user = new user(null);

function unit_test( $return, $expected ) 
{
	global $form;
	if( $form->get_error_num() === $expected )
		$str = "\033[032m" . 'OK';
	else
	{
		$str = "\033[031m". 'NOK' . "\n\t";
		if( $form->get_error_num() != 0)
			$str.= substr( implode("\n\t", $form->get_error()), 0, -1 );
		else
			$str.= "Pas d'erreur ?\n";
	}
	$form->flush_error();
	return strip_accented($str)."\033[0m";
}

echo "\n" . 'Begin unit test '. basename($_SERVER['PHP_SELF']) .' ... ' . "\n";

$var = array( 'submit' => 1 );
echo "Test submit_1 : " . unit_test($form->check_vars($var), 0) . "\n";

$var = array( 'submit' => '1' );
echo "Test submit_2 : " . unit_test($form->check_vars($var), 0) . "\n"; // $form->get_err_code('ERR_SUBMIT')

$var = array( 'submit' => 0 );
echo "Test submit_3 : " . unit_test($form->check_vars($var), $form->get_err_code('ERR_SUBMIT')) . "\n";

$var = array( 'mode' => 'activate' );
echo "Test mode_1 : " . unit_test($form->check_vars($var), 0) . "\n";

$var = array( 'mode' => 'login' );
echo "Test mode_2 : " . unit_test($form->check_vars($var), 0) . "\n";

$var = array( 'mode' => 'logout' );
echo "Test mode_3 : " . unit_test($form->check_vars($var), 0) . "\n";

$var = array( 'mode' => 'register' );
echo "Test mode_4 : " . unit_test($form->check_vars($var), 0) . "\n";

$var = array( 'mode' => 'change_pwd' );
echo "Test mode_5 : " . unit_test($form->check_vars($var), 0) . "\n";

$var = array( 'mode' => 'reset_pwd' );
echo "Test mode_6 : " . unit_test($form->check_vars($var), 0) . "\n";

$var = array( 'mode' => 'reset_act' );
echo "Test mode_7 : " . unit_test($form->check_vars($var), 0) . "\n";

$var = array( 'mode' => 'unauthorized' );
echo "Test mode_8 : " . unit_test($form->check_vars($var), $form->get_err_code('ERR_MODE')) . "\n";

$var = array( 'id' => 1 );
echo "Test id_1 : " . unit_test($form->check_vars($var), 0) . "\n";

$var = array( 'id' => '1' );
echo "Test id_2 : " . unit_test($form->check_vars($var), 0) . "\n";

$var = array( 'id' => 'c' );
echo "Test id_3 : " . unit_test($form->check_vars($var), $form->get_err_code('ERR_ID')) . "\n";

$var = array( 'username' => 'username' );
echo "Test username_1 : " . unit_test($form->check_vars($var), 0) . "\n";

$var = array( 'username' => 'usernÃme' );
echo "Test username_2 : " . unit_test($form->check_vars($var), $form->get_err_code('ERR_USERNAME_CHAR')) . "\n";

$var = array( 'username' => 'test' );
echo "Test username_3 : " . unit_test($form->check_vars($var), $form->get_err_code('ERR_USERNAME_LEN')) . "\n";

$var = array( 'username' => $user->generate_rand_key($opt = ['dig' => 10, 'low' => 13, 'upp' => 10]) );
echo "Test username_4 : " . unit_test($form->check_vars($var), $form->get_err_code('ERR_USERNAME_LEN')) . "\n";

$var = array( 'password' => $user->generate_rand_key($opt = ['dig' => 2, 'low' => 2, 'upp' => 2, 'spe' => 2]) );
echo "Test password_1 : " . unit_test($form->check_vars($var), 0) . "\n";

$var = array( 'password' => $user->generate_rand_key($opt = ['dig' => 0, 'low' => 4, 'upp' => 2, 'spe' => 2]) );
echo "Test password_2 : " . unit_test($form->check_vars($var), $form->get_err_code('ERR_PASSWORD_NUM')) . "\n";

$var = array( 'password' => $user->generate_rand_key($opt = ['dig' => 2, 'low' => 4, 'upp' => 0, 'spe' => 2]) );
echo "Test password_3 : " . unit_test($form->check_vars($var), $form->get_err_code('ERR_PASSWORD_UPP')) . "\n";

$var = array( 'password' => $user->generate_rand_key($opt = ['dig' => 2, 'low' => 4, 'upp' => 2, 'spe' => 0]) );
echo "Test password_4 : " . unit_test($form->check_vars($var), $form->get_err_code('ERR_PASSWORD_SPE')) . "\n";

$var = array( 'password' => $user->generate_rand_key($opt = ['dig' => 1, 'low' => 1, 'upp' => 1, 'spe' => 1]) );
echo "Test password_5 : " . unit_test($form->check_vars($var), $form->get_err_code('ERR_PASSWORD_LEN')) . "\n";

$var = array( 'password' => $user->generate_rand_key($opt = ['dig' => 1, 'low' => 30, 'upp' => 1, 'spe' => 1]) );
echo "Test password_6 : " . unit_test($form->check_vars($var), $form->get_err_code('ERR_PASSWORD_LEN')) . "\n";

$var = array( 'password' => 'é'.$user->generate_rand_key($opt = ['dig' => 1, 'low' => 27, 'upp' => 1, 'spe' => 1]) );
echo "Test password_7 : " . unit_test($form->check_vars($var), $form->get_err_code('ERR_PASSWORD_CHAR')) . "\n";

$var = array( 'email' => 'test@mail.com' );
echo "Test mail_1 : " . unit_test($form->check_vars($var), 0) . "\n";

$var = array( 'email' => 'te_st@ma_il.com' );
echo "Test mail_2 : " . unit_test($form->check_vars($var), 0) . "\n";

$var = array( 'email' => '_test@mail.com' );
echo "Test mail_3 : " . unit_test($form->check_vars($var), $form->get_err_code('ERR_MAIL_CHAR')) . "\n";

$var = array( 'email' => 'test@_mail.com' );
echo "Test mail_4 : " . unit_test($form->check_vars($var), $form->get_err_code('ERR_MAIL_CHAR')) . "\n";

$var = array( 'email' => 'a@a.aa' );
echo "Test mail_5 : " . unit_test($form->check_vars($var), $form->get_err_code('ERR_MAIL_CHAR')+$form->get_err_code('ERR_MAIL_LEN')) . "\n";

$var = array( 'actkey' => $user->generate_rand_key($opt = ['dig' => 16, 'low' => 16]) );
echo "Test actkey_1 : " . unit_test($form->check_vars($var), 0) . "\n";

$var = array( 'actkey' => $user->generate_rand_key($opt = ['dig' => 16, 'low' => 16, 'upp' => 1]) );
echo "Test actkey_2 : " . unit_test($form->check_vars($var), $form->get_err_code('ERR_ACTKEY')) . "\n";

$var = array( 'actkey' => $user->generate_rand_key($opt = ['dig' => 16, 'low' => 15]) );
echo "Test actkey_3 : " . unit_test($form->check_vars($var), $form->get_err_code('ERR_ACTKEY')) . "\n";

echo "\n" . 'Test '. basename($_SERVER['PHP_SELF']) .' done in ' . round(exec_time()-EXEC_TIME,6) . ' seconds' . "\n";
?>