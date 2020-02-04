<?php
require_once("config.php");
$page = new page();

if( !$_SESSION['connected'] )
	$page->go_to('index');

$form = new form();
$form->check_vars($_GET);
$form->check_vars($_POST);

if( !$form->has_error() && isset($_GET['mode']) )
{
	switch( $_GET['mode'] )
	{
		case 'add' : 
			if( isset($_POST['submit']) )
			{
				$wishlist = new wishlist($_POST);
				if( $wishlist->add() )
				{
					$page->set_msg_box( "valid", "<center>Votre wishlist a été ajouté avec succès.<br/><a href=\"".ROOT_PATH."/index.php\">Retour</a></center>" );
					$page->add_meta('<meta http-equiv="refresh" content="3; URL=\'{ROOT_PATH}/index.php\'">');
					$box = true;
				}
			}
		break;

		case 'edit' : 
			if( isset($_POST['submit']) )
			{
				$wishlist = new wishlist($_POST);
				if( $wishlist->edit() )
				{
					$_GET['id_wishlist'] = $_POST['id_wishlist'];
					$page->set_msg_box( "valid", "<center>La modification de votre wishlist a été enregistrée.<br/><a href=\"".ROOT_PATH."/index.php\">Retour</a></center>" );
					$page->add_meta('<meta http-equiv="refresh" content="3; URL=\'{ROOT_PATH}/index.php\'">');
					$box = true;
				}
			}
		break;
		
		case 'delete' : 
			$wishlist = new wishlist($_GET);
			if( $wishlist->delete() )
			{
				$page->set_msg_box( "valid", "<center>Votre wishlist a bien été supprimée.<br/><a href=\"".ROOT_PATH."/index.php\">Retour</a></center>" );
				// $page->add_meta('<meta http-equiv="refresh" content="3; URL=\'{ROOT_PATH}/index.php\'">');
				$box = true;
			}
		break;

		case 'share' : 
			echo 'share';
			$wishlist = new wishlist($_GET);
			if( $wishlist->share() )
			{
				$page->set_msg_box( "valid", "<center>Votre wishlist est maintenant public.<br/><a href=\"".ROOT_PATH."/index.php\">Retour</a></center>" );
				$page->add_meta('<meta http-equiv="refresh" content="3; URL=\'{ROOT_PATH}/index.php\'">');
				$box = true;
			}
		break;

		case 'unshare' : 
			$wishlist = new wishlist($_GET);
			if( $wishlist->unshare() )
			{
				$page->set_msg_box( "valid", "<center>Votre wishlist est maintenant privée.<br/><a href=\"".ROOT_PATH."/index.php\">Retour</a></center>" );
				$page->add_meta('<meta http-equiv="refresh" content="3; URL=\'{ROOT_PATH}/index.php\'">');
				$box = true;
			}
		break;

		case 'subscribe' : 
			$_GET['id_user'] = $_SESSION['id_user'];
			$wishlist = new wishlist($_GET);
			if( $wishlist->subscribe() )
			{
				$page->set_msg_box( "valid", "<center>Vous êtes maintenant inscrit à la wishlist.<br/><a href=\"".ROOT_PATH."/index.php\">Retour</a></center>" );
				$page->add_meta('<meta http-equiv="refresh" content="3; URL=\'{ROOT_PATH}/index.php\'">');
				$box = true;
			}
		break;

		case 'unsubscribe' : 
			$_GET['id_user'] = $_SESSION['id_user'];
			$wishlist = new wishlist($_GET);
			if( $wishlist->unsubscribe() )
			{
				$page->set_msg_box( "valid", "<center>Vous n'êtes plus inscrit à la wishlist.<br/><a href=\"".ROOT_PATH."/index.php\">Retour</a></center>" );
				$page->add_meta('<meta http-equiv="refresh" content="3; URL=\'{ROOT_PATH}/index.php\'">');
				$box = true;
			}
		break;
		
		// default : $page->go_to('index');
	}
}

// Il y a des erreurs à afficher ?
$page->set_err_box();

if( !isset($_GET['mode']) )
	$_GET['mode'] = '';

if( !isset($box) )
{
	switch( $_GET['mode'] )
	{
		case 'edit' :
			$_GET['id_user'] = $_SESSION['id_user'];
			$wishlist = new wishlist($_GET);
			$data = $wishlist->sql_check_id_wishlist();
			$page->add( $form->get_wishlist( 'edit',  get_object_vars($data) ) );
		break;
		
		default : 
			$page->go_to('index');
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