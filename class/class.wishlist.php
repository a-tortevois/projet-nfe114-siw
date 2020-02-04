<?php
class wishlist
{
	// common
	private $mysqli;
	private $arr_error = array();
	private $num_error = 0;
	// other variables
	private $mode = '';
	private $id_wishlist = 0;
	private $id_user = 0;
	private $title = '';
	private $order_by = 'upd_time-desc';
	private $is_shared = 0;
	private $id_session = 0;
	private $page = 1;
	
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
				if( $key != 'submit' )
				{
					if( isset( $this->$key ) )
						$this->$key = $this->mysqli->real_escape_string($value);
					else
					{
						trigger_error('$wishlist->constructor : La variable $wishlist->'.$key.' n\'existe pas.');
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
	 * Ajouter une wishlist en bdd
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
		
		if( !$this->has_error() )
		{							
			$sql = 'INSERT INTO mwl_wishlists SET 
						id_user = '.$this->id_user.',
						title = "'.$this->title.'",
						add_time = NOW(),
						upd_time = NOW()';
						
			if( $this->is_shared )
				$sql.= ' , is_shared = 1';
						
			if( $req = $this->mysqli->my_query($sql) )
			{
				// $this->id_wishlist = $this->mysqli->insert_id;
				return true;
			}
		}
		return false;
	}

	/*
	 * Modifier une wishlist en bdd
	 */	
	public function edit()
	{
		if( !empty($this->id_wishlist) )
		{
			$this->sql_check_id_wishlist($this->id_wishlist);
			
			if( empty($this->title) )
				$this->set_error('ERR_TITLE_FIELD_EMPTY');				
		}
		else
			$this->set_error('ERR_ID_FIELD_EMPTY');

		if( !$this->has_error() )
		{							
			$sql = 'UPDATE mwl_wishlists SET 
						title = "'.$this->title.'",
						upd_time = NOW()';
			
			if( $this->is_shared )
				$sql.= ' , is_shared = 1';
			else
				$sql.= ' , is_shared = 0';
			
			$sql.= ' WHERE id_wishlist = '.$this->id_wishlist;
			
			if( $req = $this->mysqli->my_query($sql) )
			{
				// TODO : if( $this->notify() )
				return true;
			}
		}
		return false;
	}

	/*
	 * Supprimer une wishlist en bdd
	 */	
	public function delete()
	{
		if( !empty($this->id_wishlist) )
		{
			$this->sql_check_id_wishlist($this->id_wishlist);
		}
		else 
			$this->set_error('ERR_ID_FIELD_EMPTY');

		if( !$this->has_error() )
		{							
			$sql = 'DELETE FROM mwl_wishlists WHERE id_wishlist = '.$this->id_wishlist;			
			if( $req = $this->mysqli->my_query($sql) )
			{
				// Don't forget, purge bdd !
				$sql = 'DELETE FROM mwl_wishlist_gifts WHERE id_wishlist = '.$this->id_wishlist;
				if( $req = $this->mysqli->my_query($sql) )
					$sql = 'DELETE FROM mwl_subscribers WHERE id_wishlist = '.$this->id_wishlist;
						if( $req = $this->mysqli->my_query($sql) )
							return true;
				// TODO : if( $this->notify() )
			}
		}
		return false;
	}

	/*
	 * Obtenir la liste des wishlist en bdd
	 * Filtre les wishlist suivant $id_user et $order_by 
	 * Retourne un tableau exploité par $page->add_wishlists( $arr );
	 */
	public function get_wishlists()
	{	
		$sql = 'SELECT mwl_wishlists.*, mwl_users.username FROM mwl_wishlists, mwl_users WHERE mwl_wishlists.id_user = mwl_users.id_user ';
		
		if( !empty($this->id_user) )
		{
			$sql.= 'AND mwl_wishlists.id_user = '.$this->id_user;
			if( $this->id_user != $this->id_session)
				$sql.= ' AND mwl_wishlists.is_shared = 1';
		}
		else
			$sql.= 'AND (mwl_wishlists.id_user = '.$this->id_session.' OR (mwl_wishlists.id_user != '.$this->id_session.' AND mwl_wishlists.is_shared = 1))';
	
		if( !empty($this->order_by) )
		{
			$this->order_by = get_order_by($this->order_by);
			if( preg_match('`title`', $this->order_by) )
				$this->order_by = 'mwl_wishlists.'.$this->order_by;

			if( preg_match('`username`', $this->order_by) )
				$this->order_by = 'mwl_users.'.$this->order_by;			
			
			if( preg_match('`upd_time`', $this->order_by) )
				$this->order_by = 'mwl_wishlists.'.$this->order_by;
			
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
				$arr = array();
				while( $row = $req->fetch_object() )
				{
					$arr[]= array( 
								'id'			=> array( 'wishlist' => $row->id_wishlist, 'user' => $row->id_user),
								'title'			=> $row->title,
								'username'		=> $row->username,
								'actions'		=> $this->get_actions( $row->id_user, $row->id_wishlist, $row->is_shared ),
							);						
				}
				return $arr;
			}
			else
				$this->set_error('ERR_SQL_NO_ENTRIES');
		}
		return ''; // or set an error ?
	}

	/*
	 * Rendre public une liste
	 */	
	public function share()
	{	
		// TODO : $this->notify()
		
		if( empty($this->id_wishlist) )
			$this->set_error('ERR_ID_FIELD_EMPTY');

		if( !$this->has_error() )
		{
			$sql = 'UPDATE mwl_wishlists SET upd_time = NOW(), is_shared = 1 WHERE id_wishlist = '.$this->id_wishlist;
			if( $req = $this->mysqli->my_query($sql) )
			{
				return true;
			}
		}
		return false;
	}

	/*
	 * Rendre privée une liste
	 */		
	public function unshare()
	{
		// TODO : $this->notify()
		
		if( empty($this->id_wishlist) )
			$this->set_error('ERR_ID_FIELD_EMPTY');

		if( !$this->has_error() )
		{
			$sql = 'UPDATE mwl_wishlists SET upd_time = NOW(), is_shared = 0 WHERE id_wishlist = '.$this->id_wishlist;
			if( $req = $this->mysqli->my_query($sql) )
			{
				return true;
			}
		}
		return false;
	}

	/*
	 * S'inscrire comme "suscribers" aux notifications de MAJ sur $id_wishlist
	 */		
	public function subscribe() 
	{
		if( empty($this->id_wishlist) )
			$this->set_error('ERR_ID_FIELD_EMPTY');
		
		// TODO : check id_user ?

		if( !$this->has_error() )
		{
			$sql = 'INSERT INTO mwl_subscribers SET id_user = '.$this->id_user.', id_wishlist = '.$this->id_wishlist;
			if( $req = $this->mysqli->my_query($sql) )
			{
				return true;
			}
		}
		return false;
	}

	/*
	 * Se désinscrire des "suscribers" de $id_wishlist
	 */			
	public function unsubscribe()
	{
		if( empty($this->id_wishlist) )
			$this->set_error('ERR_ID_FIELD_EMPTY');
		
		// TODO : check id_user ?

		if( !$this->has_error() )
		{
			$sql = 'DELETE FROM mwl_subscribers WHERE id_user = '.$this->id_user.' AND id_wishlist = '.$this->id_wishlist;
			if( $req = $this->mysqli->my_query($sql) )
			{
				return true;
			}
		}
		return false;
	}

	/*
	 * $id_user est "suscribers" sur $id_wishlist ?
	 */		
	private function has_subscribed( $id_user, $id_wishlist )
	{
		$sql = 'SELECT * FROM mwl_subscribers WHERE id_user = '.$id_user.' AND id_wishlist = '.$id_wishlist;
		
		if( $req = $this->mysqli->my_query($sql) )
		{
			if( $req->num_rows != 0 )
				return true;
		}
		return false;
	}

	/*
	 * Notifier les "suscribers" de $id_wishlist
	 */		
	public function notify()
	{
		// TODO : ajouter des code aux notifications
		
		$sql = 'SELECT mwl_users.username, mwl_users.email 
					FROM mwl_users, mwl_wishlists, mwl_subscribers 
					WHERE mwl_users.id_user = mwl_subscribers.id_user 
					  AND mwl_wishlists.id_wishlist = '.$this->id_wishlist.'
					  AND mwl_wishlists.is_shared = 1
					  AND mwl_subscribers.id_wishlist = '.$this->id_wishlist;
		
		if( $req = $this->mysqli->my_query($sql) )
		{
			if( $req->num_rows != 0 )
			{
				if( empty($this->title) )
				{
					$row = $this->sql_check_id_wishlist();
					if( !empty($row) )
					{
						$this->title = $row->title;
						$this->is_shared = $row->is_shared;
					}
				}
			
				if( !$this->has_error() )
				{
					$flag = true;
					while( $row = $req->fetch_object() )
					{				
						$link = SITE_URL.'gift.php?id_wishlist='.$this->id_wishlist;
						$subject = "Wishlist mise à jour";
						$message = file_get_contents(ROOT_PATH.'/template/mail_wl_update.html');
						$assign_vars = array (
											'`{SITE_URL}`'			=> SITE_URL,
											'`{SITE_NAME}`'			=> SITE_NAME,
											'`{USERNAME}`'			=> $row->username,
											'`{WISHLIST_TITLE}`'	=> $this->title,
											'`{WISHLIST_URL}`'		=> $link,
										);
						$message = preg_replace( array_keys($assign_vars), $assign_vars, $message );
						$flag &= $this->send_mail( $row->email, $subject, $message );
					}
					return $flag;
				}
			}
			else
				return true;
		}
		return false;
	}

	/*
	 * Pour envoyer les mail
	 */		
	private function send_mail( $to, $subject, $message )
	{
		// file_put_contents(ROOT_PATH.'/email.log', $to.'\n', FILE_APPEND);
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
	 * Obtenir les actions possibles pour $id_user sur $id_wishlist 
	 */			
	public function get_actions( $id_user, $id_wishlist, $is_shared = 0 ) 
	{
		$actions = array();
		if( $id_user == $this->id_session )
		{
			// edit, delete, share / unshare
			$actions[]= 'edit';
			$actions[]= 'delete';
			
			if( $is_shared )
				$actions[]= 'unshare';
			else
				$actions[]= 'share';
		}
		else
		{
			// subscribe / unsubscribe
			if( $this->has_subscribed( $this->id_session, $id_wishlist) )
				$actions[]= 'unsubscribe';
			else
				$actions[]= 'subscribe';			
		}
		return $actions;
	}

	/*
	 * Check si $id_wishlist existe en bdd
	 */	
	public function sql_check_id_wishlist()
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
			$this->set_error('ERR_ID_FIELD_EMPTY');
		
		return null;
	}

	/*
	 * Calcul du nombre de page total
	 */	
	public function get_nb_page()
	{		
		$sql = 'SELECT COUNT(*) AS total FROM mwl_wishlists WHERE ';
				
		if( !empty($this->id_user) )
		{
			$sql.= ' id_user = '.$this->id_user;
			
			if( $this->id_user != $this->id_session )
				$sql.= ' AND is_shared = 1';
		}
		else
			$sql.= '(id_user != '.$this->id_session.' AND is_shared = 1) OR id_user = '.$this->id_session;
				
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
		'ERR_SQL_NO_ENTRIES'			=> 0x0001,
		'ERR_WISHLIST_NOT_FOUND'		=> 0x0002,
		'ERR_TITLE_FIELD_EMPTY'			=> 0x0004,
		'ERR_ID_FIELD_EMPTY'			=> 0x0008,
		'ERR_MAIL_NOT_SENT'				=> 0x0010,
	);

	/*
	 * Pour la gestion des erreurs
	 * Définition des labels (textes associées aux erreurs)
	 */	
	private $err_label = array(
		'ERR_SQL_NO_ENTRIES'			=> "La table wishlists est vide.",
		'ERR_WISHLIST_NOT_FOUND'		=> "Cette wishlist n'existe pas.",
		'ERR_TITLE_FIELD_EMPTY'			=> "Le champs titre est vide.",
		'ERR_ID_FIELD_EMPTY'			=> "Le champs id est vide.",
		'ERR_MAIL_NOT_SENT'				=> "Erreur email non envoyé.",
	);
}
?>