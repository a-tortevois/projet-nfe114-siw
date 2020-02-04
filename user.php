<?php
require_once("config.php");
$page = new page();
$form = new form();

$form->check_vars($_GET);
$form->check_vars($_POST);

if( !$form->has_error() && isset($_GET['mode']) )
{	
	switch( $_GET['mode'] ) 
	{
		case 'activate' : 
			if( !$_SESSION['connected'] )
			{
				$user = new user($_GET);
				if( $user->activate() )
				{
					$page->set_msg_box( "valid", "Votre compte a été activé avec succès." );
					$_GET['mode'] = 'login';
				}
				
				if( ($user->get_error_num()&$user->get_err_code('ERR_ACTKEY_HAS_EXPIRED')) == $user->get_err_code('ERR_ACTKEY_HAS_EXPIRED') )
				{
					$_GET['mode'] = 'reset_act';
				}
			}
		break;

		case 'login' : 
			if( isset($_POST['submit']) && !$_SESSION['connected'] )
			{
				$user = new user($_POST);
				if( $user->login() )
				{
					$page->set_msg_box( "valid", "<center>Vous êtes maintenant connecté.<br/><a href=\"".ROOT_PATH."/index.php\">Retour</a></center>" );
					$page->add_meta('<meta http-equiv="refresh" content="3; URL=\'{ROOT_PATH}/index.php\'">');
				}
				
				if( ($user->get_error_num()&$user->get_err_code('ERR_ACCOUNT_NOT_ACTIVED')) == $user->get_err_code('ERR_ACCOUNT_NOT_ACTIVED') )
				{
					$_GET['mode'] = 'reset_act';
				}
			}
		break;	

		case 'logout' : 
			session_unset();
			session_destroy();
			$page->go_to('index');
		break;

		case 'register' : 
			if( isset($_POST['submit']) && !$_SESSION['connected'] )
			{
				$user = new user($_POST);
				if( $user->register() )
				{
					$page->set_msg_box( "valid", "Votre compte a bien été créé. Cependant, il doit être activé. Une clé d’activation vous a été envoyée par email." );
					$_GET['mode'] = 'login';
				}
			}	
		break;

		case 'change_pwd' : 
			if( isset($_POST['submit']) && $_SESSION['connected'] )
			{
				$user = new user($_POST);
				if( $user->change_pwd() )
				{
					$page->set_msg_box( "valid", "<center>Votre mot de passe a bien été modifié.<br/><a href=\"".ROOT_PATH."/index.php\">Retour</a></center>" );
					$page->add_meta('<meta http-equiv="refresh" content="3; URL=\'{ROOT_PATH}/index.php\'">');
				}
			}
		break;
		
		case 'reset_pwd' : 
			if( isset($_POST['submit']) && !$_SESSION['connected'] )
			{
				$user = new user($_POST);
				if( $user->reset_pwd() )
				{
					$page->set_msg_box( "valid", "Un nouveau mot de passe vous a été envoyé par email." );
					$_GET['mode'] = 'login';
				}
			}				
		break;
		
		case 'reset_act' :
			if( isset($_POST['submit']) && !$_SESSION['connected'] )
			{
				$user = new user($_POST);
				if( $user->reset_act() )
				{
					$page->set_msg_box( "valid", "Une nouvelle clé d'activation vous a été envoyé par email." );
					$_GET['mode'] = 'login';					
				}				
			}				
		break;

		// default : 	
	}
}

// Il y a des erreurs à afficher ?
$page->set_err_box();

if( !isset($_GET['mode']) )
	$_GET['mode'] = '';

if( !$_SESSION['connected'] )
{
	switch( $_GET['mode'] )
	{
		case 'activate' : 
			if( !isset($user) )
				$page->add( $form->get_reset_act() );
			elseif( $user->has_error() )
				$page->add( $form->get_reset_act() );
		break;
		
		case 'register' : 
			$page->add( $form->get_register() );
			$page->add_tpl( 'toogle-password' );
		break;

		case 'reset_pwd' : 
			$page->add( $form->get_reset_pwd() );
			$page->add( '<nav><a href="'.ROOT_PATH.'/user.php?mode=register">Créer un compte</a></nav>' );
		break;

		case 'reset_act' :
			$page->add( $form->get_reset_act() );
			$page->add( '<nav><a href="'.ROOT_PATH.'/user.php?mode=register">Créer un compte</a></nav>' );
		break;
		
		case 'login' : 
		default :
			$page->add( $form->get_login() );
			$page->add( '<nav>' );
			$page->add( '<a href="'.ROOT_PATH.'/user.php?mode=register">Créer un compte</a>' );
			$page->add( '&nbsp;&cir;&nbsp;' );
			$page->add( '<a href="'.ROOT_PATH.'/user.php?mode=reset_pwd">Mot de passe perdu</a>' );
			$page->add( '&nbsp;&cir;&nbsp;' );
			$page->add( '<a href="'.ROOT_PATH.'/user.php?mode=reset_act">Clé d\'activation perdu</a>' );
			$page->add( '</nav>' );
			$page->add_tpl( 'toogle-password' );
		break;
	}	
}
else
{
	switch( $_GET['mode'] )
	{	
		case 'login' : 
			if( !isset($_POST['submit']) ) // $form->has_error()
			{
				$page->go_to('index');
			}
		break;	

		case 'change_pwd' : 
			if( !isset($_POST['submit']) || $user->has_error() ) // TODO : be carreful isset($user) ??
			{
				$page->add( $form->get_change_pwd() );
				$page->add_tpl( 'toogle-password' );
			}
		break;
	
		default : $page->go_to('index');
	}
}

$page->assign_vars( array( 
	'`{TITLE}`'			=> 'My WishList',
	'`{SITE_URL}`'		=> SITE_URL,
	'`{SITE_NAME}`'		=> SITE_NAME,
	'`{ROOT_PATH}`'		=> ROOT_PATH,
));

$page->footer();
?>