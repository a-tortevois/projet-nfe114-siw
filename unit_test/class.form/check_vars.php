<?php
require_once("../../config.php");
require_once(ROOT_PATH."/class/class.form.php");

$form = new form();
$user = new user(null);

function unit_test_vars( $return, $expected ) 
{
	global $form;
	if( $form->get_error_num() === $expected )
		$str = '<span style="font-weight: bold; color: green;">OK</span>';
	else
	{
		$str = '<span style="font-weight: bold; color: red;">NOK<dl>';
		if( $form->get_error_num() != 0)
		{
			foreach( $form->get_error() as $value )
			{
				$str.= '<dd>'.$value.'</dd>';
			}
		}
		else
			$str.= "<dd>Pas d'erreur ?</dd>";
		$str.='</dl></span>';
	}
	$form->flush_error();
	return $str;
}

echo '<h4>Begin unit test '. basename($_SERVER['PHP_SELF']) .' ... </h4>';

$var = array( 'submit' => 1 );
echo "Test submit_1 : " . unit_test_vars($form->check_vars($var), 0) . "<br/>";

$var = array( 'submit' => '1' );
echo "Test submit_2 : " . unit_test_vars($form->check_vars($var), 0) . "<br/>"; // $form->get_err_code('ERR_SUBMIT')

$var = array( 'submit' => 0 );
echo "Test submit_3 : " . unit_test_vars($form->check_vars($var), $form->get_err_code('ERR_SUBMIT')) . "<br/>";

$var = array( 'mode' => 'activate' );
echo "Test mode_1 : " . unit_test_vars($form->check_vars($var), 0) . "<br/>";

$var = array( 'mode' => 'login' );
echo "Test mode_2 : " . unit_test_vars($form->check_vars($var), 0) . "<br/>";

$var = array( 'mode' => 'logout' );
echo "Test mode_3 : " . unit_test_vars($form->check_vars($var), 0) . "<br/>";

$var = array( 'mode' => 'register' );
echo "Test mode_4 : " . unit_test_vars($form->check_vars($var), 0) . "<br/>";

$var = array( 'mode' => 'change_pwd' );
echo "Test mode_5 : " . unit_test_vars($form->check_vars($var), 0) . "<br/>";

$var = array( 'mode' => 'reset_pwd' );
echo "Test mode_6 : " . unit_test_vars($form->check_vars($var), 0) . "<br/>";

$var = array( 'mode' => 'reset_act' );
echo "Test mode_7 : " . unit_test_vars($form->check_vars($var), 0) . "<br/>";

$var = array( 'mode' => 'unauthorized' );
echo "Test mode_8 : " . unit_test_vars($form->check_vars($var), $form->get_err_code('ERR_MODE')) . "<br/>";

$var = array( 'id' => 1 );
echo "Test id_1 : " . unit_test_vars($form->check_vars($var), 0) . "<br/>";

$var = array( 'id' => '1' );
echo "Test id_2 : " . unit_test_vars($form->check_vars($var), 0) . "<br/>";

$var = array( 'id' => 'c' );
echo "Test id_3 : " . unit_test_vars($form->check_vars($var), $form->get_err_code('ERR_ID')) . "<br/>";

$var = array( 'username' => 'username' );
echo "Test username_1 : " . unit_test_vars($form->check_vars($var), 0) . "<br/>";

$var = array( 'username' => 'usernÃme' );
echo "Test username_2 : " . unit_test_vars($form->check_vars($var), $form->get_err_code('ERR_USERNAME_CHAR')) . "<br/>";

$var = array( 'username' => 'test' );
echo "Test username_3 : " . unit_test_vars($form->check_vars($var), $form->get_err_code('ERR_USERNAME_LEN')) . "<br/>";

$var = array( 'username' => $user->generate_rand_key($opt = ['dig' => 10, 'low' => 13, 'upp' => 10]) );
echo "Test username_4 : " . unit_test_vars($form->check_vars($var), $form->get_err_code('ERR_USERNAME_LEN')) . "<br/>";

$var = array( 'password' => $user->generate_rand_key($opt = ['dig' => 2, 'low' => 2, 'upp' => 2, 'spe' => 2]) );
echo "Test password_1 : " . unit_test_vars($form->check_vars($var), 0) . "<br/>";

$var = array( 'password' => $user->generate_rand_key($opt = ['dig' => 0, 'low' => 4, 'upp' => 2, 'spe' => 2]) );
echo "Test password_2 : " . unit_test_vars($form->check_vars($var), $form->get_err_code('ERR_PASSWORD_NUM')) . "<br/>";

$var = array( 'password' => $user->generate_rand_key($opt = ['dig' => 2, 'low' => 4, 'upp' => 0, 'spe' => 2]) );
echo "Test password_3 : " . unit_test_vars($form->check_vars($var), $form->get_err_code('ERR_PASSWORD_UPP')) . "<br/>";

$var = array( 'password' => $user->generate_rand_key($opt = ['dig' => 2, 'low' => 4, 'upp' => 2, 'spe' => 0]) );
echo "Test password_4 : " . unit_test_vars($form->check_vars($var), $form->get_err_code('ERR_PASSWORD_SPE')) . "<br/>";

$var = array( 'password' => $user->generate_rand_key($opt = ['dig' => 1, 'low' => 1, 'upp' => 1, 'spe' => 1]) );
echo "Test password_5 : " . unit_test_vars($form->check_vars($var), $form->get_err_code('ERR_PASSWORD_LEN')) . "<br/>";

$var = array( 'password' => $user->generate_rand_key($opt = ['dig' => 1, 'low' => 30, 'upp' => 1, 'spe' => 1]) );
echo "Test password_6 : " . unit_test_vars($form->check_vars($var), $form->get_err_code('ERR_PASSWORD_LEN')) . "<br/>";

$var = array( 'password' => 'é'.$user->generate_rand_key($opt = ['dig' => 1, 'low' => 27, 'upp' => 1, 'spe' => 1]) );
echo "Test password_7 : " . unit_test_vars($form->check_vars($var), $form->get_err_code('ERR_PASSWORD_CHAR')) . "<br/>";

$var = array( 'email' => 'test@mail.com' );
echo "Test mail_1 : " . unit_test_vars($form->check_vars($var), 0) . "<br/>";

$var = array( 'email' => 'te_st@ma_il.com' );
echo "Test mail_2 : " . unit_test_vars($form->check_vars($var), 0) . "<br/>";

$var = array( 'email' => '_test@mail.com' );
echo "Test mail_3 : " . unit_test_vars($form->check_vars($var), $form->get_err_code('ERR_MAIL_CHAR')) . "<br/>";

$var = array( 'email' => 'test@_mail.com' );
echo "Test mail_4 : " . unit_test_vars($form->check_vars($var), $form->get_err_code('ERR_MAIL_CHAR')) . "<br/>";

$var = array( 'email' => 'a@a.aa' );
echo "Test mail_5 : " . unit_test_vars($form->check_vars($var), $form->get_err_code('ERR_MAIL_CHAR')+$form->get_err_code('ERR_MAIL_LEN')) . "<br/>";

$var = array( 'actkey' => $user->generate_rand_key($opt = ['dig' => 16, 'low' => 16]) );
echo "Test actkey_1 : " . unit_test_vars($form->check_vars($var), 0) . "<br/>";

$var = array( 'actkey' => $user->generate_rand_key($opt = ['dig' => 16, 'low' => 16, 'upp' => 1]) );
echo "Test actkey_2 : " . unit_test_vars($form->check_vars($var), $form->get_err_code('ERR_ACTKEY')) . "<br/>";

$var = array( 'actkey' => $user->generate_rand_key($opt = ['dig' => 16, 'low' => 15]) );
echo "Test actkey_3 : " . unit_test_vars($form->check_vars($var), $form->get_err_code('ERR_ACTKEY')) . "<br/>";

echo "<br/>" . 'Test '. basename($_SERVER['PHP_SELF']) .' done in ' . round(exec_time()-EXEC_TIME,6) . ' seconds' . "<br/>";
?>