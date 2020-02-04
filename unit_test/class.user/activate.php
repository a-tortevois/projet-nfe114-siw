<?php
require_once("../../config.php");
require_once("_unit_test.php");

$mysqli = new My_SQL();

echo '<h4>Begin unit test '. basename($_SERVER['PHP_SELF']) .' ... </h4>';

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
echo "Test register_1 : " . unit_test($user->register(), 0) . "<br/>";
$user_id = $user->get_id();
$actkey = $user->get_actkey();
unset($user);

// ERR_ACTKEY_FIELD_EMPTY
$arr = array(
		'id'      => $user_id, 
		'actkey'  => null
);
$user = new user($arr);
echo "Test activate_1 : " . unit_test($user->activate(), $user->get_err_code('ERR_ACTKEY_FIELD_EMPTY')) . "<br/>";
unset($user);

// ERR_USER_NOT_FOUND
$arr = array(
		'id'       => -1, 
		'actkey'  => null
);
$user = new user($arr);
$user->set_act_key();
echo "Test activate_2 : " . unit_test($user->activate(), $user->get_err_code('ERR_USER_NOT_FOUND')) . "<br/>";
unset($user);

// ERR_ACTKEY_HAS_EXPIRED
$arr = array(
		'id'       => $user_id, 
		'actkey'  => null
);
$user = new user($arr);
$user->set_act_key();
echo "Test activate_3 : " . unit_test($user->activate(), $user->get_err_code('ERR_ACTKEY_HAS_EXPIRED')) . "<br/>";
unset($user);

// NO ERROR
$arr = array(
		'id'       => $user_id, 
		'actkey'  => $actkey
);
$user = new user($arr);
echo "Test activate_4 : " . unit_test($user->activate(), 0) . "<br/>";

// ERR_ACCOUNT_ALREADY_ACTIVED
echo "Test activate_5 : " . unit_test($user->activate(), $user->get_err_code('ERR_ACCOUNT_ALREADY_ACTIVED')) . "<br/>";

init_mwl_users();

echo "<br/>" . 'Test '. basename($_SERVER['PHP_SELF']) .' done in ' . round(exec_time()-EXEC_TIME,6) . ' seconds' . "<br/>";
?>