<?php
class gift
{
	// common
	private $mysqli;
	private $arr_error = array();
	private $num_error = 0;
	// other variables
	private $id_gift = 0;
	private $title = '';
	private $texte = '';
	private $order_by = 'upd_time-desc';
	private $id_user = 0;
	private $id_wishlist = 0;
	private $id_session = 0;
	private $page = 1;
	
	// for share_by_email()
	private $username = ''; 
	private $email = '';
	
	public function __construct( $arr = array() )
	{
		// TODO : améliorer cette initialisation "brute"
		$this->id_session = $_SESSION['id_user'];
		
		// initialisation de $mysqli
		$this->mysqli = init_mysqli();
		
		if( isset( $arr ) )
		{
			foreach( $arr as $key => $value )
			{
				if( $key != 'submit' && $key != 'mode' )
				{
					if( isset( $this->$key ) )
						$this->$key = $this->mysqli->real_escape_string($value); // $value;
					else
					{
						trigger_error('$gift->constructor : La variable $gift->'.$key.' n\'existe pas.');
						exit;
					}
				}
			}
		}
	}

    public function __destruct()
	{
		// nothing to do ...
    }

	/*
	 * Ajouter un gift en bdd
	 */	
	public function add()
	{
		if( !empty($this->title) )
		{
			// TODO : check if exist
			// $sql = '';
			// if( $req = $this->mysqli->my_query($sql) )		
				// if( $req->num_rows != 0 )
					// $this->set_error('ERR_ALREADY_REGISTERED');			
		}
		else
			$this->set_error('ERR_TITLE_FIELD_EMPTY');

		if( empty($this->texte) )
			$this->set_error('ERR_TEXTE_FIELD_EMPTY');
		
		if( !$this->has_error() )
		{							
			$sql = 'INSERT INTO mwl_gifts SET 
						title = "'.$this->title.'",
						texte = "'.$this->texte.'",
						add_time = NOW(),
						upd_time = NOW()';
						
			if( $req = $this->mysqli->my_query($sql) )
			{
				// $this->id_gift = $this->mysqli->insert_id;
				return true;
			}
		}
		return false;
	}

	/*
	 * Modifier un gift en bdd
	 */	
	public function edit()
	{
		if( !empty($this->id_gift) )
		{
			$this->sql_check_id_gift($this->id_gift);
			
			if( empty($this->title) )
				$this->set_error('ERR_TITLE_FIELD_EMPTY');

			if( empty($this->texte) )
				$this->set_error('ERR_TEXTE_FIELD_EMPTY');			
		}
		else
			$this->set_error('ERR_ID_FIELD_EMPTY');

		if( !$this->has_error() )
		{							
			$sql = 'UPDATE mwl_gifts SET 
						title = "'.$this->title.'",
						texte = "'.$this->texte.'",
						upd_time = NOW()
					WHERE id_gift = '.$this->id_gift;
			
			if( $req = $this->mysqli->my_query($sql) )
			{
				return true;
			}
		}
		return false;
	}

	/*
	 * Supprimer un gift en bdd
	 */	
	public function delete()
	{
		if( !empty($this->id_gift) )
		{
			$this->sql_check_id_gift($this->id_gift);
		}
		else 
			$this->set_error('ERR_ID_FIELD_EMPTY');

		if( !$this->has_error() )
		{							
			$sql = 'DELETE FROM mwl_gifts WHERE id_gift = '.$this->id_gift;		
			if( $req = $this->mysqli->my_query($sql) )
			{
				// Don't forget, purge bdd !
				$sql = 'DELETE FROM mwl_wishlist_gifts WHERE id_gift = '.$this->id_gift;
				if( $req = $this->mysqli->my_query($sql) )
					return true;
			}
		}
		return false;
	}

	/*
	 * Obtenir un gift 
	 * Retourne un tableau exploité par $page->add_gift( $arr );
	 */
	public function get_gift()
	{
		if( !empty($this->id_wishlist) )
		{
			if( $wl = $this->sql_check_id_wishlist() )
				$wl_gift = $this->sql_check_in_wishlist();
		}
		
		$row = $this->sql_check_id_gift();
		
		if( !$this->has_error() )
		{
			$action = array('select');
			
			if( !empty($this->id_wishlist) )
			{
				if( $wl->id_user == $this->id_session || ($wl->id_user != $this->id_session && $wl->is_shared == 1) )
				{
					if( $wl_gift->id_reserver != NULL && $wl_gift->id_reserver != $this->id_session ) // is booked
						$action[]= 'booked';
					if( $wl->id_user == $this->id_session ) // owner
						$action[]= 'delete_from_wl';
					elseif( $wl_gift->id_reserver == NULL ) // not reserved
						$action[]= 'booking';
					elseif( $wl_gift->id_reserver == $this->id_session ) // cancel booking
						$action[]= 'cancel';
				}
			}
			
			$wishlists = $this->get_select_list( $row->id_gift, $this->id_session );
			
			$arr = array( 
							'id'		=> array( 'wishlist' => $this->id_wishlist,
												  'gift'	 => $row->id_gift,
												  'user'	 => $this->id_user,
												),
							'title'		=> $row->title,
							'texte'		=> $row->texte,
							'actions'	=> $action,
							'wishlists'	=> isset( $wishlists ) ? $wishlists : '',
			);
			return $arr;
		}
		return ''; // or set an error ?
	}

	/*
	 * Obtenir un la liste des gift 
	 * Retourne un tableau exploité par $page->add_gifts_list( $arr );
	 */	
	public function get_gifts_list()
	{
		$sql = 'SELECT * FROM mwl_gifts';
		
		if( !empty($this->order_by) )
		{
			$this->order_by = get_order_by($this->order_by);
			if( preg_match('`upd_time`', $this->order_by) )
				$this->order_by = 'mwl_gifts.'.$this->order_by;
			
			$sql.= ' ORDER BY '.$this->order_by;
		}
			
		//--> ADD for LIMIT
		$nb_page = $this->get_nb_page();
		if( $this->page > $nb_page )
		{
			$this->page = $nb_page;
		}
		$lim = ($this->page-1)*VIEW_PER_PAGE;
		$sql.= ' LIMIT '.$lim.','.VIEW_PER_PAGE;
		//<--
		
		if( $req = $this->mysqli->my_query($sql) )
		{
			if( $req->num_rows != 0 )
			{
				$action = array('select', 'edit', 'delete');
				$arr = array();
				while( $row = $req->fetch_object() )
				{
					$wishlists = $this->get_select_list( $row->id_gift, $this->id_session );
					$arr[]= array( 
									'id'		=> array( 'wishlist' => $this->id_wishlist,
														  'gift'	 => $row->id_gift,
														  'user'	 => $this->id_user,
														),
									'title'		=> $row->title,
									'texte'		=> $row->texte,
									'actions'	=> $action,
									'wishlists'	=> isset( $wishlists ) ? $wishlists : '',
								);
				}
				return $arr;
			}
			else
				$this->set_error('ERR_SQL_NO_GIFTS');
		}
			
		return ''; // or set an error ?
	}

	/*
	 * Obtenir un la liste des gift 
	 * Retourne un tableau exploité par $page->add_gifts_list( $arr );
	 */		
	public function get_gifts_for_this_wishlist()
	{
		$this->sql_check_id_wishlist();
		
		if( !$this->has_error() )
		{
			$sql = 'SELECT mwl_wishlists.id_user, mwl_wishlists.is_shared, mwl_gifts.title, mwl_gifts.texte, mwl_wishlist_gifts.* 
					FROM mwl_wishlist_gifts, mwl_wishlists, mwl_gifts 
					WHERE mwl_wishlist_gifts.id_gift = mwl_gifts.id_gift 
					  AND mwl_wishlist_gifts.id_wishlist = mwl_wishlists.id_wishlist
					  AND mwl_wishlist_gifts.id_wishlist = '.$this->id_wishlist;
			
			//--> ADD for LIMIT
			$nb_page = $this->get_nb_page();
			if( $this->page > $nb_page )
			{
				$this->page = $nb_page;
			}
			$lim = ($this->page-1)*VIEW_PER_PAGE;
			$sql.= ' LIMIT '.$lim.','.VIEW_PER_PAGE;
			//<--
			
			if( $req = $this->mysqli->my_query($sql) )
			{  
				if( $req->num_rows != 0 )
				{
					$arr = array();
					while( $row = $req->fetch_object() )
					{
						if( $row->id_user == $this->id_session || ($row->id_user != $this->id_session && $row->is_shared == 1) ) // TODO : to include in SQL
						{
							$action = array();
							if( $row->id_reserver != NULL && $row->id_reserver != $this->id_session ) // is booked
								$action[]= 'booked';
							if( $row->id_user == $this->id_session ) // owner
								$action[]= 'delete_from_wl';
							elseif( $row->id_reserver == NULL ) // not reserved
								$action[]= 'booking';
							elseif( $row->id_reserver == $this->id_session ) // cancel booking
								$action[]= 'cancel';

							$arr[]= array( 
									'id'		=> array( 'wishlist' => $this->id_wishlist,
														  'gift'	 => $row->id_gift,
														  'user'	 => $this->id_user,
														),
									'title'		=> $row->title,
									'texte'		=> $row->texte,
									'actions'	=> $action,
									// 'wishlists'	=> isset( $wishlists ) ? $wishlists : null,
								);	
						}
					}
					return $arr;
				}
				else
					$this->set_error('ERR_SQL_NO_GIFT_IN_WISHLIST');
			}
		}
		return ''; // or set an error ?
	}

	/*
	 * Calcul du nombre de page total
	 */	
	public function get_nb_page()
	{
		if( !empty($this->id_wishlist) ) // get gifts list for this wishlist
			$sql = 'SELECT COUNT(*) AS total FROM mwl_wishlist_gifts WHERE id_wishlist = '.$this->id_wishlist;
		else // get all gifts list
			$sql = 'SELECT COUNT(*) AS total FROM mwl_gifts';
		
		if( $req = $this->mysqli->my_query($sql) )
		{
			$row = $req->fetch_object();
			$nb_page = ceil( $row->total / VIEW_PER_PAGE );
			return ($nb_page != 0) ? $nb_page : 1;
		}
		return 1;
	}

	/*
	 * Retourne la page en cours
	 */	
	public function get_page()
	{
		return $this->page;
	}

	/*
	 * Retourne la liste des wishlist dans lesquelles on peut ajouter le gift
	 */	
	private function get_select_list( $id_gift, $id_user )
	{
		$sql = 'SELECT id_wishlist, title
				FROM mwl_wishlists 
				WHERE id_user = '.$id_user.' 
				  AND NOT EXISTS ( 
								SELECT id_wishlist
								FROM mwl_wishlist_gifts 
								WHERE id_gift = '.$id_gift.'
								  AND mwl_wishlist_gifts.id_wishlist = mwl_wishlists.id_wishlist
								 )';
		
		if( $req = $this->mysqli->my_query($sql) )
		{
			if( $req->num_rows != 0 )
			{
				return $req->fetch_all(MYSQLI_ASSOC);
			}
		}
		return null;
	}

	/*
	 * Ajouter un gift à la wishlist
	 */	
	public function insert_into_wishlist()
	{
		if( $this->sql_check_id_gift() ) // this gift exist ?
		if( $row = $this-> sql_check_id_wishlist() ) // this wishlist exist ?
		if( !$this->sql_check_in_wishlist() ) // this gift is already in this wishlist ?
		if( $this->id_user == $row->id_user ) // only wishlist owners should be add a gift
		{
			$sql = 'INSERT INTO mwl_wishlist_gifts SET
						id_wishlist = '.$this->id_wishlist.',
						id_gift = '.$this->id_gift.',
						add_time = NOW()';
			
			if( $req = $this->mysqli->my_query($sql) )
			{
				if( $this->notify() )
					return true;
			}
		}
		else 
			$this->set_error('ERR_NOT_ALLOWED');
		return false;
	}

	/*
	 * Supprimer un gift de la wishlist
	 */	
	public function delete_from_wishlist()
	{
		if( $this->sql_check_id_gift() ) // this gift exist ?
		if( $row = $this-> sql_check_id_wishlist() ) // this wishlist exist ?
		if( $this->sql_check_in_wishlist() ) // this gift is in this wishlist ?
		if( $this->id_user == $row->id_user ) // only wishlist owners should be add a gift
		{
			$sql = 'DELETE FROM mwl_wishlist_gifts WHERE id_wishlist = '.$this->id_wishlist.' AND id_gift = '.$this->id_gift;
			if( $req = $this->mysqli->my_query($sql) )
			{
				if( $this->notify() )
					return true;
			}
		}
		else 
			$this->set_error('ERR_NOT_ALLOWED');
		return false;
	}	
	
	/*
	 * Reserver un gift de la wishlist
	 */
	public function book_a_gift()
	{
		if( $this->sql_check_id_gift() ) // this gift exist ?
		if( $row = $this-> sql_check_id_wishlist() ) // this wishlist exist ?
		if( $row2 = $this->sql_check_in_wishlist() ) // this gift is in this wishlist ?
		if( !isset($row2->id_user) ) // this gift is already reserved ?
		{
			if( $this->id_user != $row->id_user ) // only other should be reserve a gift
			{
				$sql = 'UPDATE mwl_wishlist_gifts SET id_reserver = '.$this->id_user.' WHERE id_wishlist = '.$this->id_wishlist.' AND id_gift = '.$this->id_gift;
				if( $req = $this->mysqli->my_query($sql) )
				{
					// TODO : if( $this->notify() )
					return true;
				}				
			}
			else
				$this->set_error('ERR_NOT_ALLOWED');
		}
		else
			$this->set_error('ERR_ALREADY_RESERVED');
		
		return false;
	}

	/*
	 * Annuler la réservation d'un gift de la wishlist
	 */
	public function cancel_booking_gift()
	{
		if( $this->sql_check_id_gift() ) // this gift exist ?
		if( $this-> sql_check_id_wishlist() ) // this wishlist exist ?
		if( $row = $this->sql_check_in_wishlist() ) // this gift is in this wishlist ?
		if( $this->id_user == $row->id_user ) // this gift is already reserved ? & only bookers should be unreserve
		{
			$sql = 'UPDATE mwl_wishlist_gifts SET id_reserver = NULL WHERE id_wishlist = '.$this->id_wishlist.' AND id_gift = '.$this->id_gift;
			if( $req = $this->mysqli->my_query($sql) )
			{
				// TODO : if( $this->notify() )
				return true;				
			}
		}
		else
			$this->set_error('ERR_NOT_ALLOWED');
		
		return false;		
	}

	/*
	 * Envoyer par email l'adresse de la wishlist
	 */
	public function share_by_email()
	{
		$row = $this->sql_check_id_wishlist();
		if( !empty($row) )
			if( $row->is_shared != 1 )
				$this->set_error('ERR_WISHLIST_NOT_SHARED');
		
		if( !$this->has_error() )
		{
			$link = SITE_URL.'gift.php?id_wishlist='.$this->id_wishlist;
			$subject = $_SESSION['username']." vous invite à découvrir cette wishlist";
			$message = file_get_contents(ROOT_PATH.'/template/mail_wl_share.html');
			$assign_vars = array (
								'`{SITE_URL}`'		=> SITE_URL,
								'`{SITE_NAME}`'		=> SITE_NAME,
								'`{USERNAME}`'		=> $this->username,
								'`{SESSION}`'		=> $_SESSION['username'],
								'`{WISHLIST_URL}`'	=> $link,
							);
			$message = preg_replace( array_keys($assign_vars), $assign_vars, $message );
			return $this->send_mail( $this->email, utf8_decode($subject), $message ); // utf8_decode pour les accents !
		}
		return false;
	}

	/*
	 * Envoyer une notification en cas de modification
	 */	
	private function notify()
	{	
		// TODO : ajouter des codes notifications
		$wishlist = new wishlist( array( 'id_wishlist' => $this->id_wishlist ) );
		return $wishlist->notify();
	}
	
	/*
	 * Pour envoyer les mail
	 */		
	private function send_mail( $to, $subject, $message )
	{
		$headers  = 'MIME-Version: 1.0' . "\r\n";
		$headers .= 'Content-type: text/html; charset=UTF-8' ."\r\n";
		$headers .= 'From: '.FROM_NAME.' <'.FROM_MAIL.'>' . "\r\n";
		$headers .= 'Reply-To: <'.FROM_MAIL.'>' . "\r\n";
		$headers .= 'Subject: '.$subject . "\r\n";
		$headers .= 'X-Priority: 3' . "\r\n";
		$headers .= 'X-Mailer: PHP/'.phpversion() . "\r\n";
		
		if( !mail($to, $subject, $message, $headers) )
		{
			$this->set_error('ERR_MAIL_NOT_SENT');
			return false;
		}
		return true;
	}
	
	/*
	 * Check si $id_gift est dans $id_wishlist
	 * Retourne la ligne de la bdd correspondante, sinon false
	 */	
	private function sql_check_in_wishlist()
	{
		if( empty($this->id_gift) )
		{
			$this->set_error('ERR_ID_FIELD_EMPTY');
		}
		
		if( empty($this->id_wishlist) )
		{
			$this->set_error('ERR_WISHLIST_ID_FIELD_EMPTY');
		}
		
		if( !$this->has_error() )
		{
			$sql = 'SELECT * FROM mwl_wishlist_gifts WHERE id_wishlist = '.$this->id_wishlist.' AND id_gift = '.$this->id_gift;
			
			if( $req = $this->mysqli->my_query($sql) )
			{
				if( $req->num_rows != 0 )
				{
					return $req->fetch_object(); // true;
				}
			}			
		}
		return false;
	}

	/*
	 * Check si $id_wishlist existe en bdd
	 */		
	private function sql_check_id_wishlist() // this function is copied from class.wishlist ... TODO : should be optimized !
	{
		if( !empty($this->id_wishlist) )
		{
			$sql = 'SELECT * FROM mwl_wishlists WHERE id_wishlist = '.$this->id_wishlist;
			if( $req = $this->mysqli->my_query($sql) )
			{
				if( $req->num_rows != 0 )
				{
					return $req->fetch_object();
				}
				else
				{
					$this->set_error('ERR_WISHLIST_NOT_FOUND');
				}
			}
		}
		else
			$this->set_error('ERR_WISHLIST_ID_FIELD_EMPTY');
		
		return null;
	}

	/*
	 * Check si $id_gift existe en bdd
	 */	
	public function sql_check_id_gift()
	{
		if( !empty($this->id_gift) )
		{
			$sql = 'SELECT * FROM mwl_gifts WHERE id_gift = '.$this->id_gift;
			if( $req = $this->mysqli->my_query($sql) )
			{
				if( $req->num_rows != 0 )
				{
					return $req->fetch_object();
				}
				else
				{
					$this->set_error('ERR_GIFT_NOT_FOUND');
				}
			}
		}
		else
			$this->set_error('ERR_ID_FIELD_EMPTY');
		
		return null;
	}

	/*
	 * Set $num_error et $arr_error suivant la $key
	 */		
	private function set_error( $key )
	{
		if( isset($this->err_code[$key]) && isset($this->err_code[$key]) )
		{	
			$this->arr_error[].= $this->err_label[$key]; // => array_push
			$this->num_error += $this->err_code[$key];
		}
		else
		{
			trigger_error( $key . ' undefined');
			exit;
		}
	}

	/*
	 * Retourne $arr_error
	 */	
	public function get_error()
	{
		$this->merge_error();
		return $this->arr_error;
	}

	/*
	 * A-t-on une erreur ?
	 */	
	public function has_error()
	{
		$this->merge_error();
		return count($this->arr_error) == 0 ? false : true;
	}

	/*
	 * Merge les error mysqli
	 */	
	private function merge_error()
	{
		if( $this->mysqli->has_error() )
		{
			$this->arr_error = array_merge($this->arr_error, $this->mysqli->get_error());
			$this->mysqli->flush_error();
		}
	}
		
	/*
	 * Quelle numéro d'erreur ?
	 */		
	public function get_error_num()
	{		
		return $this->num_error;
	}

	/*
	 * Quelle $err_code suivant $key ?
	 */	
	public function get_err_code( $key )
	{
		if( isset($this->err_code[$key]) )
			return $this->err_code[$key];
		else return null;
	}
	
	/*
	 * Pour la gestion des erreurs
	 * Définition des codes
	 */	
	private $err_code = array(
		'ERR_SQL_NO_GIFTS'				=> 0x0001,
		'ERR_SQL_NO_GIFT_IN_WISHLIST'	=> 0x0002,
		'ERR_GIFT_NOT_FOUND'			=> 0x0004,
		'ERR_TITLE_FIELD_EMPTY'			=> 0x0008,
		'ERR_TEXTE_FIELD_EMPTY'			=> 0x0010,
		'ERR_ID_FIELD_EMPTY'			=> 0x0020,
		'ERR_NOT_ALLOWED'				=> 0x0040,
		'ERR_WISHLIST_NOT_FOUND'		=> 0x0080,
		'ERR_WISHLIST_ID_FIELD_EMPTY'	=> 0x0100,
		'ERR_USER_ID_FIELD_EMPTY'		=> 0x0200,
		'ERR_ALREADY_RESERVED'			=> 0x0400,
		'ERR_MAIL_NOT_SENT'				=> 0x0800,
		'ERR_WISHLIST_NOT_SHARED'		=> 0x1000,
	);

	/*
	 * Pour la gestion des erreurs
	 * Définition des labels (textes associées aux erreurs)
	 */	
	private $err_label = array(
		'ERR_SQL_NO_GIFTS'				=> "La table gifts est vide.",
		'ERR_SQL_NO_GIFT_IN_WISHLIST'	=> "Votre wishlist est vide.",
		'ERR_GIFT_NOT_FOUND'			=> "Ce cadeau n'existe pas.",
		'ERR_TITLE_FIELD_EMPTY'			=> "Le champs titre est vide.",
		'ERR_TITLE_FIELD_EMPTY'			=> "Le champs texte est vide.",
		'ERR_ID_FIELD_EMPTY'			=> "Le champs id est vide.",
		'ERR_NOT_ALLOWED'				=> "Action non autorisée.",
		'ERR_WISHLIST_NOT_FOUND'		=> "Cette wishlist n'existe pas.",
		'ERR_WISHLIST_ID_FIELD_EMPTY'	=> "Le champs id_wishlist est vide.",
		'ERR_USER_ID_FIELD_EMPTY'		=> "Le champs id_user est vide.",
		'ERR_ALREADY_RESERVED'			=> "Le gift est déjà réservé.",
		'ERR_MAIL_NOT_SENT'				=> "Impossible d'envoyer le mail.",
		'ERR_WISHLIST_NOT_SHARED'		=> "La wishlist n'est pas partagée.",
	);
}
?>