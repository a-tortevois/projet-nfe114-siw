<?php
require_once("config.php");
$page = new page();
$form = new form();

$form->check_vars($_GET);
$form->check_vars($_POST);

$page->set_err_box();

if( !$_SESSION['connected'] )
{
	$page->add( $form->get_login() );
	$page->add( '<nav>' );
	$page->add( '<a href="'.ROOT_PATH.'/user.php?mode=register">Créer un compte</a>' );
	$page->add( '&nbsp;&cir;&nbsp;' );
	$page->add( '<a href="'.ROOT_PATH.'/user.php?mode=reset_pwd">Mot de passe perdu</a>' );
	$page->add( '&nbsp;&cir;&nbsp;' );
	$page->add( '<a href="'.ROOT_PATH.'/user.php?mode=reset_act">Clé d\'activation perdu</a>' );
	$page->add( '</nav>' );
	$page->add_tpl( 'toogle-password' );
}
else
{	
	$wishlist = new wishlist($_GET);
	if( !empty($wl = $wishlist->get_wishlists()) )
	{
		$page->add_wishlists( $wl );
		
		$page_link = 'index.php?';
		
		if( isset($_GET['id_user']) )
			$page_link.= 'id_user='.$_GET['id_user'].'&&';
		
		if( isset($_GET['order_by']) )
			$page_link.= 'order_by='.$_GET['order_by'].'&&';
		
		$page->add( $page->get_pagin( $page_link, $wishlist->get_page(), $wishlist->get_nb_page() ) );
	}
	elseif( !$wishlist->has_error() )
	{
		$page->set_msg_box( "error", "<center>Vous n'êtes pas autorisé a accéder à cette page.<br/><a href=\"".ROOT_PATH."/index.php\">Retour</a></center>" );
		$page->add_meta('<meta http-equiv="refresh" content="3; URL=\'{ROOT_PATH}/index.php\'">');
	}
	else
	{
		$page->set_err_box();
	}
		
	if( !isset($_GET['id_user']) )
		$page->add( $form->get_wishlist() );
}

$page->assign_vars( array( 
	'`{TITLE}`'			=> 'My WishList',
	'`{SITE_URL}`'		=> SITE_URL,
	'`{SITE_NAME}`'		=> SITE_NAME,
	'`{ROOT_PATH}`'		=> ROOT_PATH,
));

$page->footer();
?>