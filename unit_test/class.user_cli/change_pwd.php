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

// NO ERROR
$arr = array(
		'id'       => $user_id, 
		'actkey'   => $actkey
);
$user = new user($arr);
echo "Test activate_1 : " . unit_test($user->activate(), 0) . "\n";
unset($user);

// ERR_ID_FIELD_EMPTY
$arr = array(
		'id'       => null, 
		'password' => null, 
		'new_pwd1' => null, 
		'new_pwd2' => null, 
);
$user = new user($arr);
echo "Test change_pwd_1 : " . unit_test($user->change_pwd(), $user->get_err_code('ERR_ID_FIELD_EMPTY')) . "\n";
unset($user);

// ERR_PWD_FIELD_EMPTY
$arr = array(
		'id'       => $user_id, 
		'password' => null, 
		'new_pwd1' => $password, 
		'new_pwd2' => $password, 
);
$user = new user($arr);
echo "Test change_pwd_2 : " . unit_test($user->change_pwd(), $user->get_err_code('ERR_PWD_FIELD_EMPTY')) . "\n";
unset($user);

// ERR_NEWPWD1_FIELD_EMPTY
// ERR_NEWPWD_NOT_MATCH
$arr = array(
		'id'       => $user_id, 
		'password' => $password, 
		'new_pwd1' => null, 
		'new_pwd2' => $password, 
);
$user = new user($arr);
$err_code = $user->get_err_code('ERR_NEWPWD1_FIELD_EMPTY');
$err_code+= $user->get_err_code('ERR_NEWPWD_NOT_MATCH');
echo "Test change_pwd_3 : " . unit_test($user->change_pwd(), $err_code) . "\n";
unset($user);

// ERR_NEWPWD2_FIELD_EMPTY
// ERR_NEWPWD_NOT_MATCH
$arr = array(
		'id'       => $user_id, 
		'password' => $password, 
		'new_pwd1' => $password, 
		'new_pwd2' => null, 
);
$user = new user($arr);
$err_code = $user->get_err_code('ERR_NEWPWD2_FIELD_EMPTY');
$err_code+= $user->get_err_code('ERR_NEWPWD_NOT_MATCH');
echo "Test change_pwd_4 : " . unit_test($user->change_pwd(), $err_code) . "\n";
unset($user);

// ERR_NEWPWD_NOT_MATCH
$arr = array(
		'id'       => $user_id, 
		'password' => $password, 
		'new_pwd1' => $password, 
		'new_pwd2' => '9!B*mpZ6', 
);
$user = new user($arr);
echo "Test change_pwd_5 : " . unit_test($user->change_pwd(), $user->get_err_code('ERR_NEWPWD_NOT_MATCH')) . "\n";
unset($user);

// ERR_USER_NOT_FOUND
$arr = array(
		'id'       => -1, 
		'password' => $password, 
		'new_pwd1' => $password, 
		'new_pwd2' => $password, 
);
$user = new user($arr);
echo "Test change_pwd_7 : " . unit_test($user->change_pwd(), $user->get_err_code('ERR_USER_NOT_FOUND')) . "\n";
unset($user);

// ERR_PWD_NOT_MATCH
$arr = array(
		'id'       => $user_id, 
		'password' => '9!B*mpZ6', 
		'new_pwd1' => $password, 
		'new_pwd2' => $password, 
);
$user = new user($arr);
echo "Test change_pwd_7 : " . unit_test($user->change_pwd(), $user->get_err_code('ERR_PWD_NOT_MATCH')) . "\n";
unset($user);

// NO ERROR
$arr = array(
		'id'       => $user_id, 
		'password' => $password, 
		'new_pwd1' => '9!B*mpZ6', 
		'new_pwd2' => '9!B*mpZ6', 
);
$user = new user($arr);
echo "Test change_pwd_7 : " . unit_test($user->change_pwd(), 0) . "\n";
unset($user);

init_mwl_users();

echo "\n" . 'Test '. basename($_SERVER['PHP_SELF']) .' done in ' . round(exec_time()-EXEC_TIME,6) . ' seconds' . "\n";
?>