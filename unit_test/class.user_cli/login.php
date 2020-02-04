<?php
require_once("../../config.php");
require_once("_unit_test.php");

$mysqli = new My_SQL();

echo "\n" . 'Begin unit test '. basename($_SERVER['PHP_SELF']) .' ... ' . "\n\n";

init_mwl_users();

$username = 'user_test';
$password = '9!B*mpZ5';
$usermail = 'alexandre.tortevois@wanadoo.fr';

// NO ERROR
$arr = array(
		'username' => $username, 
		'new_pwd1' => $password, 
		'new_pwd2' => $password, 
		'email'    => $usermail, 
);
$user = new user($arr);
echo "Test register_1 : " . unit_test($user->register(), 0) . "\n";
$user_id = $user->get_id();
$actkey = $user->get_actkey();
unset($user);

// ERR_USER_FIELD_EMPTY
$arr = array(
		'username' => null, 
		'password' => $password, 
);
$user = new user($arr);
echo "Test login_1 : " . unit_test($user->login(), $user->get_err_code('ERR_USER_FIELD_EMPTY')) . "\n";
unset($user);

// ERR_USER_NOT_FOUND
$arr = array(
		'username' => 'user_test_2', 
		'password' => $password,
);
$user = new user($arr);
echo "Test login_2 : " . unit_test($user->login(), $user->get_err_code('ERR_USER_NOT_FOUND')) . "\n";
unset($user);

// ERR_PWD_FIELD_EMPTY
$arr = array(
		'username' => $username, 
		'password' => null, 
);
$user = new user($arr);
echo "Test login_3 : " . unit_test($user->login(), $user->get_err_code('ERR_PWD_FIELD_EMPTY')) . "\n";
unset($user);

// ERR_PWD_NOT_MATCH
$arr = array(
		'username' => $username, 
		'password' => '9!B*mpZ6', 
);
$user = new user($arr);
echo "Test login_4 : " . unit_test($user->login(), $user->get_err_code('ERR_PWD_NOT_MATCH')) . "\n";
unset($user);

// ERR_ACCOUNT_NOT_ACTIVED
$arr = array(
		'username' => $username, 
		'password' => $password, 
);
$user = new user($arr);
echo "Test login_5 : " . unit_test($user->login(), $user->get_err_code('ERR_ACCOUNT_NOT_ACTIVED')) . "\n";
unset($user);

// NO ERROR
$arr = array(
		'id'       => $user_id,
		'actkey'  => $actkey
);
$user = new user($arr);
echo "Test activate_1 : " . unit_test($user->activate(), 0) . "\n";
unset($user);

// NO ERROR
$arr = array(
		'username' => $username, 
		'password' => $password, 
);
$user = new user($arr);
echo "Test login_6 : " . unit_test($user->login(), 0) . "\n";
unset($user);

echo "Test login_7 : ";
if( $_SESSION['connected'] === true )
		echo "\033[032m" . 'OK';
	else
		echo "\033[031m". 'NOK';
echo "\033[0m";

init_mwl_users();

echo "\n" . 'Test '. basename($_SERVER['PHP_SELF']) .' done in ' . round(exec_time()-EXEC_TIME,6) . ' seconds' . "\n";
?>