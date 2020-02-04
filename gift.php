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
				$gift = new gift($_POST);
				if( $gift->add() )
				{
					$page->set_msg_box( "valid", "<center>Le gift a été ajouté avec succès.<br/><a href=\"".ROOT_PATH."/gift.php\">Retour</a></center>" );
					$page->add_meta('<meta http-equiv="refresh" content="3; URL=\'{ROOT_PATH}/gift.php\'">');
					$box = true;
				}
			}
		break;

		case 'edit' : 
			if( isset($_POST['submit']) )
			{
				$gift = new gift($_POST);
				if( $gift->edit() )
				{
					$page->set_msg_box( "valid", "<center>La modification du gift a été enregistrée.<br/><a href=\"".ROOT_PATH."/index.php\">Retour</a></center>" );
					$page->add_meta('<meta http-equiv="refresh" content="3; URL=\'{ROOT_PATH}/gift.php\'">');
					$box = true;
				}
			}
		break;
		
		case 'delete' : 
			$gift = new gift($_GET);
			if( $gift->delete() )
			{
				$page->set_msg_box( "valid", "<center>La gift a bien été supprimé.<br/><a href=\"".ROOT_PATH."/index.php\">Retour</a></center>" );
				$page->add_meta('<meta http-equiv="refresh" content="3; URL=\'{ROOT_PATH}/gift.php\'">');
				$box = true;
			}
		break;
		
		case 'insert_into_wl' :
			$_POST['id_user'] = $_SESSION['id_user'];
			$gift = new gift($_POST);
			if( $gift->insert_into_wishlist() )
			{
				$page->set_msg_box( "valid", "Le gift a	été ajouté à votre wishlist." );
			}
		break;

		case 'delete_from_wl' :
			$_GET['id_user'] = $_SESSION['id_user'];
			$gift = new gift($_GET);
			if( $gift->delete_from_wishlist() )
			{
				$page->set_msg_box( "valid", "<center>La gift a	été supprimé de votre wishlist.<br/><a href=\"".ROOT_PATH."/gift.php?id_wishlist=".$_GET['id_wishlist']."\">Retour</a></center>" );
				$page->add_meta('<meta http-equiv="refresh" content="3; URL=\'{ROOT_PATH}/gift.php?id_wishlist='.$_GET['id_wishlist'].'\'">');
				$box = true;
			}
		break;
		
		case 'booking' : 
			$_GET['id_user'] = $_SESSION['id_user'];
			$gift = new gift($_GET);
			if( $gift->book_a_gift() )
			{
				$page->set_msg_box( "valid", "<center>Votre réservation a été enregistrée.<br/><a href=\"".ROOT_PATH."/gift.php?id_wishlist=".$_GET['id_wishlist']."\">Retour</a></center>" );
				$page->add_meta('<meta http-equiv="refresh" content="3; URL=\'{ROOT_PATH}/gift.php?id_wishlist='.$_GET['id_wishlist'].'\'">');
				$box = true;
			}
		break;
		
		case 'cancel' :
			$_GET['id_user'] = $_SESSION['id_user'];
			$gift = new gift($_GET);
			if( $gift->cancel_booking_gift() )
			{
				$page->set_msg_box( "valid", "<center>Votre réservation a été annulée.<br/><a href=\"".ROOT_PATH."/gift.php?id_wishlist=".$_GET['id_wishlist']."\">Retour</a></center>" );
				$page->add_meta('<meta http-equiv="refresh" content="3; URL=\'{ROOT_PATH}/gift.php?id_wishlist='.$_GET['id_wishlist'].'\'">');
				$box = true;				
			}
		break;		
		
		case 'share' :
			if( isset($_POST['submit']) && !empty($_POST['id_wishlist']) )
			{
				$gift = new gift($_POST);
				if( $gift->share_by_email() )
				{
					$page->set_msg_box( "valid", "<center>La liste a bien été partagée.<br/><a href=\"".ROOT_PATH."/gift.php?id_wishlist=".$_POST['id_wishlist']."\">Retour</a></center>" );
					$page->add_meta('<meta http-equiv="refresh" content="3; URL=\'{ROOT_PATH}/gift.php?id_wishlist='.$_POST['id_wishlist'].'\'">');
					$box = true;					
				}
			}
		break;
	}
}

// Il y a des erreurs à afficher ?
$page->set_err_box();

if( !isset($_GET['mode']) )
	$_GET['mode'] = '';

if( $_GET['mode'] == 'edit' && !empty($_GET['id_gift']) )
{
	$_GET['id_user'] = $_SESSION['id_user'];
	$gift = new gift($_GET);
	$data = $gift->sql_check_id_gift();
	$page->add_tinymce();
	$page->add( $form->get_gift( 'edit',  get_object_vars($data) ) );
}
elseif( !isset($box) )
{
	$buffer = '';
	$with_pagin = false;
	
	$gift = new gift($_GET);
	if( !empty($_GET['id_gift']) )
	{
		$buffer = $gift->get_gift();
		if( !empty($buffer) )
			$page->add_gift( $buffer );
	}
	elseif( !empty($_GET['id_wishlist']) ) // get gifts list for this wishlist
	{
		$buffer = $gift->get_gifts_for_this_wishlist();
		if( !empty($buffer) )
		{
			$page->add_gifts_list( $buffer );
			$with_pagin = true;
		}
	}
	else // get all gifts list
	{
		$buffer = $gift->get_gifts_list();
		if( !empty($buffer) )
		{
			$page->add_gifts_list( $buffer );
			$with_pagin = true;
		}
	}
	
	if( $with_pagin ) 
	{	
		if( !isset($_GET['id_wishlist']) )
			$page_link = 'gift.php?';
		else
			$page_link = 'gift.php?id_wishlist='.$_GET['id_wishlist'].'&&';
		
		$page->add( $page->get_pagin( $page_link, $gift->get_page(), $gift->get_nb_page() ) );
	}
	
	if( !empty($_GET['id_wishlist']) && !empty($buffer) )
		$page->add( $form->get_share( $_GET['id_wishlist'] ) );
	
	if( (!empty($_GET['id_gift']) || !empty($_GET['id_wishlist']) ) && empty($buffer) && !$gift->has_error() )
	{
		$page->set_msg_box( "error", "<center>Vous n'êtes pas autorisé a accéder à cette page.<br/><a href=\"".ROOT_PATH."/index.php\">Retour</a></center>" );
		$page->add_meta('<meta http-equiv="refresh" content="3; URL=\'{ROOT_PATH}/index.php\'">');
	}
	elseif( $gift->has_error() )
	{
		$page->set_err_box();
	}
	
	if( !isset($_GET['id_wishlist']) && !isset($_GET['id_gift']) )
	{
		$page->add_tinymce();
		$page->add( $form->get_gift() );
	}
}

// On remplace les variables du template
$page->assign_vars( array( 
	'`{TITLE}`'			=> 'My WishList',
	'`{SITE_URL}`'		=> SITE_URL,
	'`{SITE_NAME}`'		=> SITE_NAME,
	'`{ROOT_PATH}`'		=> ROOT_PATH,
));

// On envoie la page
$page->footer();
?>