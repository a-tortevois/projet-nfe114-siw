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
		'actkey'  => $actkey
);
$user = new user($arr);
echo "Test activate_1 : " . unit_test($user->activate(), 0) . "\n";
unset($user);

// ERR_USER_FIELD_EMPTY
$arr = array(
		'username' => null, 
);
$user = new user($arr);
echo "Test reset_pwd_1 : " . unit_test($user->reset_pwd(), $user->get_err_code('ERR_USER_FIELD_EMPTY')) . "\n";
unset($user);

// ERR_USER_NOT_FOUND
$arr = array(
		'username' => 'user_test_2', 
);
$user = new user($arr);
echo "Test reset_pwd_2 : " . unit_test($user->reset_pwd(), $user->get_err_code('ERR_USER_NOT_FOUND')) . "\n";
unset($user);

// NO ERROR
$arr = array(
		'username' => $username, 
);
$user = new user($arr);
echo "Test reset_pwd_2 : " . unit_test($user->reset_pwd(), 0) . "\n";
unset($user);

init_mwl_users();

echo "\n" . 'Test '. basename($_SERVER['PHP_SELF']) .' done in ' . round(exec_time()-EXEC_TIME,6) . ' seconds' . "\n";
?>