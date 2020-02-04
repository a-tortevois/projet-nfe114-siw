<?php
require_once("../../config.php");
require_once("_unit_test.php");

$mysqli = new My_SQL();

echo "\n" . 'Begin unit test '. basename($_SERVER['PHP_SELF']) .' ... ' . "\n\n";

init_mwl_users();

$username = 'user_test';
$password = '9!B*mpZ5';
$usermail = 'alexandre.tortevois@wanadoo.fr';

// ERR_USER_FIELD_EMPTY
$arr = array(
		'username' => '', 
		'new_pwd1' => $password, 
		'new_pwd2' => $password, 
		'email'    => $usermail, 
);
$user = new user($arr);
echo "Test register_1 : " . unit_test($user->register(), $user->get_err_code('ERR_USER_FIELD_EMPTY')) . "\n";
unset($user);

// ERR_NEWPWD1_FIELD_EMPTY
// ERR_NEWPWD_NOT_MATCH
$arr = array(
		'username' => $username, 
		'new_pwd1' => '', 
		'new_pwd2' => $password, 
		'email'    => $usermail, 
);
$user = new user($arr);
$err_code = $user->get_err_code('ERR_NEWPWD1_FIELD_EMPTY');
$err_code+= $user->get_err_code('ERR_NEWPWD_NOT_MATCH');
echo "Test register_2 : " . unit_test($user->register(), $err_code) . "\n";
unset($user);

// ERR_NEWPWD2_FIELD_EMPTY
// ERR_NEWPWD_NOT_MATCH
$arr = array(
		'username' => $username, 
		'new_pwd1' => $password, 
		'new_pwd2' => '', 
		'email'    => $usermail, 
);
$user = new user($arr);
$err_code = $user->get_err_code('ERR_NEWPWD2_FIELD_EMPTY');
$err_code+= $user->get_err_code('ERR_NEWPWD_NOT_MATCH');
echo "Test register_3 : " . unit_test($user->register(), $err_code) . "\n";
unset($user);

// ERR_NEWPWD_NOT_MATCH
$arr = array(
		'username' => $username, 
		'new_pwd1' => $password, 
		'new_pwd2' => '9!B*mpZ6', 
		'email'    => $usermail, 
);
$user = new user($arr);
echo "Test register_4 : " . unit_test($user->register(), $user->get_err_code('ERR_NEWPWD_NOT_MATCH')) . "\n";
unset($user);

// ERR_MAIL_FIELD_EMPTY
$arr = array(
		'username' => $username, 
		'new_pwd1' => $password, 
		'new_pwd2' => $password, 
		'email'	   => '', 
);
$user = new user($arr);
echo "Test register_5 : " . unit_test($user->register(), $user->get_err_code('ERR_MAIL_FIELD_EMPTY')) . "\n";
unset($user);

// ERR_USER_FIELD_EMPTY
// ERR_NEWPWD1_FIELD_EMPTY
// ERR_NEWPWD2_FIELD_EMPTY
// ERR_MAIL_FIELD_EMPTY
$arr = array(
		'username' => '',  
		'new_pwd1' => '', 
		'new_pwd2' => '', 
		'email'	   => '', 
);
$user = new user($arr);
$err_code = $user->get_err_code('ERR_USER_FIELD_EMPTY');
$err_code+= $user->get_err_code('ERR_NEWPWD1_FIELD_EMPTY');
$err_code+= $user->get_err_code('ERR_NEWPWD2_FIELD_EMPTY');
$err_code+= $user->get_err_code('ERR_MAIL_FIELD_EMPTY');
echo "Test register_6 : " . unit_test($user->register(), $err_code) . "\n";
unset($user);

// NO ERROR
$arr = array(
		'username' => $username,  
		'new_pwd1' => $password, 
		'new_pwd2' => $password, 
		'email'    => $usermail, 
);
$user = new user($arr);
echo "Test register_7 : " . unit_test($user->register(), 0) . "\n";

// ERR_USER_ALREADY_REGISTERED
$arr = array(
		'username' => $username, 
		'new_pwd1' => $password, 
		'new_pwd2' => $password, 
		'email'    => 'user_test@mail.com', 
);
$user = new user($arr);
echo "Test register_8 : " . unit_test($user->register(), $user->get_err_code('ERR_USER_ALREADY_REGISTERED')) . "\n";

// ERR_MAIL_ALREADY_REGISTERED
$arr = array(
		'username' => 'user_test_2', 
		'new_pwd1' => $password, 
		'new_pwd2' => $password, 
		'email'    => $usermail, 
);
$user = new user($arr);
echo "Test register_9 : " . unit_test($user->register(), $user->get_err_code('ERR_MAIL_ALREADY_REGISTERED')) . "\n";

unset($user);

// Ces tests doivent renvoyer des erreurs ... (à décommenter pour la démo)
// -->
// $arr = array(
		// 'username' => $username,  
		// 'new_pwd1' => $password, 
		// 'new_pwd2' => '9!B*mpZ6', 
		// 'email'    => $usermail, 
// );
// $user = new user($arr);
// echo "Test register_10 : " . unit_test($user->register(), 0) . "\n";
// unset($user);

// $arr = array(
		// 'username' => $username,  
		// 'new_pwd1' => $password, 
		// 'new_pwd2' => $password, 
		// 'email'    => $usermail, 
// );
// $user = new user($arr);
// echo "Test register_11 : " . unit_test($user->register(), $user->get_err_code('ERR_MAIL_ALREADY_REGISTERED')) . "\n";
// unset($user);
// <--

init_mwl_users();

echo "\n" . 'Test '. basename($_SERVER['PHP_SELF']) .' done in ' . round(exec_time()-EXEC_TIME,6) . ' seconds' . "\n";
?>