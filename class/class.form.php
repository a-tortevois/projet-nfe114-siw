<?php
class form
{
	// common
	private $arr_error = array();
	private $num_error = 0;
	// other variables
	private $buffer = '';
	private $has_fieldset = false;

	/*
	 * Ajouter (du code html) au $buffer
	 */		
	public function add_buffer( $str )
	{
		$this->buffer.= $str;
	}

	/*
	 * Creer un nouveau formulaire dans le $buffer
	 */		
	public function set_form( $id, $action )
	{
		$this->buffer = '<form id="'.$id.'" method="POST" action="'.$action.'">';
		$this->buffer.= '<input type="hidden" name="submit" value="1" />';
	}

	/*
	 * Ouvre la balise fieldset et défini la legende
	 */		
	public function set_fieldset( $legend )
	{
		$this->has_fieldset = true;
		$this->buffer.= '<fieldset><legend>'.$legend.'</legend>';
	}

	/*
	 * Défini un input
	 */		
	public function set_input( $type , $label, $name, $value = '', $arr_opt = '', $help = '' )
	{
		if( $type != 'hidden' )
		{
			$this->buffer.= '<p>';
			$this->buffer.= '<label for="'.$name.'">'. $label . ' : </label>';
		}
		
		$this->buffer.= '<input type="'.$type.'" id="'.$name.'" name="'.$name.'" value="'.$value.'" ';
		if( !empty($arr_opt) )
		{
			foreach($arr_opt as $key => $value)
			{
				$this->buffer.= $key.'="'.$value.'" ';
			}
		}
		$this->buffer.= '/>';
		
		if( $type != 'hidden' )
		{
			$this->buffer.= '<span class="validator"></span>';
		}
		
		if( $type == 'password' )
			$this->buffer.= '<span data-toggle="#'.$name.'" class="eye-open toggle-password"></span>';
		
		if( !empty($help) )
		{
			$this->buffer.= '<a href="#" class="help">';
			$this->buffer.= '<img src="'.ROOT_PATH.'/img/blank.png" alt="?" width="18" height="18" />';
			// onmouseover="this.src=\''.ROOT_PATH.'/img/help_hover.png\'" onmouseout="this.src=\''.ROOT_PATH.'/img/help.png\'"
			$this->buffer.= '<span>'.$help.'</span>';
			$this->buffer.= '</a>';
		}
		
		if( $type != 'hidden' )
		{
			$this->buffer.= '</p>';
		}
	}

	/*
	 * Défini une checkbox
	 */	
	public function set_checkbox( $label, $name, $value = '' )
	{
		$this->buffer.= '<input type="checkbox" id="'.$name.'" name="'.$name.'"';
		if( !empty($value) )
			$this->buffer.= ' '.$value;
		$this->buffer.=' />';
		$this->buffer.= '<label for="'.$name.'">'. $label . ' : </label>';
	}

	/*
	 * Défini un input "submit"
	 */		
	public function set_input_submit( $value = 'Envoyer' )
	{
		$this->buffer.= '<input type="submit" title="'.$value.'" value="'.$value.'" />';
	}

	/*
	 * Défini un input "cancel"
	 */		
	public function set_input_cancel( $back_link, $value = 'Annuler' )
	{
		$this->buffer.= '<input type="button" title="'.$value.'" value="'.$value.'" class="button_cancel" onClick="window.location=\''.ROOT_PATH.'/'.$back_link.'\';"/>';
	}

	/*
	 * Défini un textarea
	 */		
	public function set_textarea( $label, $name, $value = '' )
	{
		$this->buffer.= '<div id="wysiwyg"><textarea name="'.$name.'">'.$value.'</textarea></div>';
	}

	/*
	 * Ferme la balise fieldset
	 */		
	public function end_fieldset()
	{
		if( $this->has_fieldset )
			$this->buffer.= '</fieldset>';
		$this->has_fieldset = false;
	}

	/*
	 * Retourne le buffer
	 */		
	public function get_buffer()
	{
		$this->end_fieldset();
		$buffer = $this->buffer.'</form>';
		
		$this->clean(); // flush le buffer
		return $buffer;
	}

	/*
	 * Flush le buffer
	 */		
	public function clean()
	{
		$this->buffer = '';		
	}

	/*
	 * Test un tableau de variables avec les RexEx
	 * In : adresse du tableau de variables à tester
	 * Unset la variable si elle n'est pas valide
	 */		
	public function check_vars( &$arr ) 
	{
		foreach( $arr as $key => $value )
		{
			$flag = false;
			switch ($key)
			{
				case 'submit' : // toujours = 1
					if( $value != 1 )
					{
						$flag = true;
						$this->set_error('ERR_SUBMIT');
						unset($arr[$key]);
					}
				break;

				case 'mode' : 
					$defined = array(
								'activate',
								'login',
								'logout',
								'register',
								'change_pwd',
								'reset_pwd',
								'reset_act',
								'edit',
								'add',
								'delete',
								'share',
								'unshare',
								'subscribe',
								'unsubscribe',
								'insert_into_wl',
								'delete_from_wl',
								'booking',
								'cancel',
								'search',
							);
					
					if( !in_array($value, $defined) ) 
					{
						$flag = true;
						$this->set_error('ERR_MODE'); 
						unset($arr[$key]);
					}
				break;
				
				case 'id' :
				case 'id_user' :
				case 'id_wishlist' :
				case 'id_gift' :
					if( !preg_match('`^[0-9]+$`', $value) ) // !is_int($value) => plus strict, à voir
					{
						$flag = true;
						$this->set_error('ERR_ID'); 
					}			
				break;

				case 'username' : 				
					if( !preg_match( '`^'.$this->get_pattern($key).'+$`', $value) ) // exclu : àâçéèêëïîöôüûÀÂÇÈÉÊËÏÎÖÔÜÛ
					{
						$flag = true;
						$this->set_error('ERR_USERNAME_CHAR'); 
					}
					
					if( !is_between(strlen($value), 6, 32) )
					{
						$flag = true;
						$this->set_error('ERR_USERNAME_LEN'); 
					}				
				break;
				
				case 'password' :
				case 'new_pwd1' :
				case 'new_pwd2' :
					if( !preg_match( '`'.$this->get_pattern($key).'`' , $value) )
					{
						$flag = true;
						
						if( !preg_match('`^[0-9a-zA-Z_&~#{}()[\]|^@%*+\-\/=$<>!?,.;:\\\\]+$`', $value) )
							$this->set_error('ERR_PASSWORD_CHAR'); 

						if(	!preg_match('`[0-9]+`', $value) ) // [0-9]+
							$this->set_error('ERR_PASSWORD_NUM'); 
						
						if( !preg_match('`[A-Z]+`', $value) ) //exclu ÀÂÇÈÉÊËÏÎÖÔÜÛ ..
							$this->set_error('ERR_PASSWORD_UPP'); 
						
						if( !preg_match('`[_&~#{}()[\]|^@%*+\-\/=$<>!?,.;:\\\\]+`', $value) )
							$this->set_error('ERR_PASSWORD_SPE'); 
					}

					if( !is_between(strlen($value), 8, 32) )
					{
						$flag = true;
						$this->set_error('ERR_PASSWORD_LEN'); 
					}
				break;
				
				case 'email' :
					if( !preg_match('`^'.$this->get_pattern($key).'$`', $value) )
					{
						$flag = true;
						$this->set_error('ERR_MAIL_CHAR'); 
					}
					
					if( !is_between(strlen($value), 8, 32) )
					{
						$flag = true;
						$this->set_error('ERR_MAIL_LEN'); 
					}
				break;
				
				case 'actkey' :
					if( !preg_match('`^[0-9a-z]{32}$`', $value) )
					{
						$flag = true;
						$this->set_error('ERR_ACTKEY'); 
					}
				break;

				case 'title' :
					// Échappe les espaces avant/après
					$value = trim($value);
					$arr[$key] = trim($value);
					
					if( !preg_match('`^[0-9a-zA-Z_&~#{}()[\]|^@%*+\-\/=$<>!?,.;:\\\\ ]+$`', $value) ) // à voir ?
					{
						$flag = true;
						$this->set_error('ERR_TITLE_CHAR'); 
					}
					
					if( !is_between(strlen($value), 3, 64) )
					{
						$flag = true;
						$this->set_error('ERR_TITLE_LEN'); 
					}
				break;
				
				case 'is_shared' :
					if( $value == 'on' )
						$arr[$key] = 1;
					else
						$arr[$key] = 0;
				break;
				
				case 'texte' : 
					if( empty($value) )
					{
						$flag = true;
						$this->set_error('ERR_TEXT_EMPTY'); 
					}
					elseif( !preg_match('`^.+$`', $value) ) // à voir ?
					{
						$flag = true;
						$this->set_error('ERR_TEXT_CHAR'); 
					}
					
				break;
				
				case 'page' :
					if( !preg_match('`^[0-9]+$`', $value) )
					{
						$flag = true;
						$this->set_error('ERR_PAGE'); 
					}	
				break;
				
				case 'order_by':
					$defined = array(
								'username',
								'title',
								'asc',
								'desc',
							);

					if( preg_match('`-{1}`', $value) )
					{
						$tmp = explode('-', $value);
						if( in_array($tmp[0], $defined) && in_array($tmp[1], $defined) )
						{
							// $arr[$key] = $tmp[0] . ' ' . strtoupper($tmp[1]);
						}
						else
						{
							$flag = true;
							$this->set_error('ERR_ORDER_BY'); 
							unset($arr[$key]);
						}
					}
					else
					{
						$flag = true;
						$this->set_error('ERR_ORDER_BY'); 
						unset($arr[$key]);
					}
				break;
				
				default :
					unset($arr[$key]);
			}
			
			if( $flag && isset($arr[$key]) )
			{
				$arr[$key] = '';
			}
		}
	}

	/*
	 * Quelques RexEx partagée entre check_vars et les formulaires
	 * Nécessaire pour les vérifications automatique du formulaire
	 */	
	private function get_pattern( $fied )
	{
		// TODO : ajouter ici la gestion des longueurs / ajouter tous les champs ... reprendre les tests unitaires !
		switch( $fied )
		{
			case 'username' :
				return '[0-9a-zA-Z_.-]';
			break;
			
			case 'password' : 
			case 'new_pwd1' :
			case 'new_pwd2' :
				return '(?=(.*[0-9]){1,})(?=(.*[A-Z]){1,})(?=(.*[_&~#{}(\)[\]|^@%*+\-\/=$<>!?,.;:\\\\]){1,})(?=[0-9a-zA-Z_&~#{}()[\]|^@%*+\-\/=$<>!?,.;:\\\\]+)'; //(?=(.*[a-z]{1,}))
			break;
			
			case 'email' :
				return '[a-z0-9]+([a-z0-9._-]+)@[a-z0-9]+([a-z0-9._-]+)\.[a-z]{2,4}';
			break;
			
			case 'title' : 
				return '[0-9a-zA-Z_&~#{}()[\]|^@%*+\-\/=$<>!?,.;:\\\\ ]';
			break;
		}
	}
	
	/*
	 * Créer le formulaire d'enregistrement
	 */		
	public function get_register()
	{
		$this->clean();
		$this->set_form( 'login', ROOT_PATH.'/user.php?mode=register' );
		$this->set_fieldset( 'Enregistrement' );
		$this->set_input( 'text', 'Username', 'username', isset($_POST['username']) ? $_POST['username'] : '', array( 'placeholder' => 'Username', 'required' => 'required', 'pattern' => $this->get_pattern( 'username').'{6,32}' ), $this->help['username'] );
		$this->set_input( 'password', 'Password', 'new_pwd1', isset($_POST['new_pwd1']) ? $_POST['new_pwd1'] : '', array( 'placeholder' => 'Password', 'required' => 'required', 'pattern' => $this->get_pattern( 'password').'.{8,32}' ), $this->help['password'] );
		$this->set_input( 'password', 'Confirmer', 'new_pwd2', isset($_POST['new_pwd2']) ? $_POST['new_pwd2'] : '', array( 'placeholder' => 'Password', 'required' => 'required', 'pattern' => $this->get_pattern( 'password').'.{8,32}' ), $this->help['password'] );
		$this->set_input( 'text', 'Email', 'email', isset($_POST['email']) ? $_POST['email'] : '', array( 'placeholder' => 'Email', 'required' => 'required', 'pattern' => $this->get_pattern( 'email').'{8,32}' ) , $this->help['email'] );
		$this->add_buffer('<center>');
		$this->set_input_submit();
		$this->add_buffer('</center>');
		return $this->get_buffer();		
	}
	
	/*
	 * Créer le formulaire de connexion
	 */		
	public function get_login()
	{
		$this->clean();
		$this->set_form( 'login', ROOT_PATH.'/user.php?mode=login' );
		$this->set_fieldset( 'Connexion' );
		$this->set_input( 'text', 'Username', 'username', isset($_POST['username']) ? $_POST['username'] : '', array( 'placeholder' => 'Username', 'required' => 'required', 'pattern' => $this->get_pattern( 'username').'{6,32}' ), $this->help['username'] );
		$this->set_input( 'password', 'Password', 'password', isset($_POST['password']) ? $_POST['password'] : '', array( 'placeholder' => 'Password', 'required' => 'required', 'pattern' => $this->get_pattern( 'password').'.{8,32}' ), $this->help['password'] );
		$this->add_buffer('<center>');
		$this->set_input_submit();
		$this->add_buffer('</center>');
		return $this->get_buffer();
	}

	/*
	 * Créer le formulaire de changement de mot de passe
	 */		
	public function get_change_pwd()
	{
		$this->clean();
		$this->set_form( 'login', ROOT_PATH.'/user.php?mode=change_pwd' );
		$this->set_input( 'hidden', '', 'id', $_SESSION['id_user'] );
		$this->set_fieldset( 'Changer votre mot de passe' );
		$this->set_input( 'password', 'Password', 'password', isset($_POST['password']) ? $_POST['password'] : '', array( 'placeholder' => 'Password', 'required' => 'required', 'pattern' => $this->get_pattern( 'password').'.{8,32}' ), $this->help['password'] );
		$this->set_input( 'password', 'Password', 'new_pwd1', isset($_POST['new_pwd1']) ? $_POST['new_pwd1'] : '', array( 'placeholder' => 'Password', 'required' => 'required', 'pattern' => $this->get_pattern( 'password').'.{8,32}' ), $this->help['password'] );
		$this->set_input( 'password', 'Confirmer', 'new_pwd2', isset($_POST['new_pwd2']) ? $_POST['new_pwd2'] : '', array( 'placeholder' => 'Password', 'required' => 'required', 'pattern' => $this->get_pattern( 'password').'.{8,32}' ), $this->help['password'] );
		$this->add_buffer('<center>');
		$this->set_input_submit();
		$this->add_buffer('</center>');
		return $this->get_buffer();
	}

	/*
	 * Créer le formulaire de reset du mot de passe
	 */		
	public function get_reset_pwd()
	{
		$this->clean();
		$this->set_form( 'login', ROOT_PATH.'/user.php?mode=reset_pwd' );
		$this->set_fieldset( 'Renvoyer un mot de passe' );
		$this->set_input( 'text', 'Username', 'username', isset($_POST['username']) ? $_POST['username'] : '', array( 'placeholder' => 'Username', 'required' => 'required', 'pattern' => $this->get_pattern( 'username').'{6,32}' ), $this->help['username'] );
		$this->add_buffer('<center>');
		$this->set_input_submit();
		$this->add_buffer('</center>');
		return $this->get_buffer();
	}

	/*
	 * Créer le formulaire de reset de la clé d'activation
	 */		
	public function get_reset_act()
	{
		$this->clean();
		$this->set_form( 'login', ROOT_PATH.'/user.php?mode=reset_act' );
		$this->set_fieldset( 'Renvoyer la clé d\'actvation' );
		$this->set_input( 'text', 'Email', 'email', isset($_POST['email']) ? $_POST['email'] : '', array( 'placeholder' => 'Email', 'required' => 'required', 'pattern' => $this->get_pattern( 'email').'{8,32}' ) , $this->help['email'] );
		$this->add_buffer('<center>');
		$this->set_input_submit();
		$this->add_buffer('</center>');
		return $this->get_buffer();		
	}

	/*
	 * Créer le formulaire de reset de la clé d'activation
	 */	
	public function get_wishlist( $mode = 'add', $data = '' )
	{
		$this->clean();
		$this->set_form( 'wishlist', ROOT_PATH.'/wishlist.php?mode='.$mode );
		if( $mode == 'edit' && isset($data['id_wishlist']) )
			$this->set_input( 'hidden', '', 'id_wishlist', $data['id_wishlist'] );
		$this->set_input( 'hidden', '', 'id_user', $_SESSION['id_user'] );
		$this->set_fieldset( $mode == 'edit' ? 'Éditer une wishlist' : 'Ajouter une wishlist' ); 
		$this->set_input( 'text', 'Titre', 'title', isset($data['title']) ? $data['title'] : '', array( 'placeholder' => 'Titre', 'required' => 'required', 'pattern' => $this->get_pattern( 'title').'{3,64}' ) , $this->help['title'] );
		$this->add_buffer('<p class="is_shared">');
		$this->set_checkbox( 'Shared', 'is_shared', isset($data['is_shared']) ? 'checked' : '' );
		$this->add_buffer('</p>');
		$this->add_buffer('<center>');
		$this->set_input_submit( $mode == 'edit' ? 'Confirmer' : 'Ajouter' );
		if( $mode == 'edit' )
			$this->set_input_cancel( 'index.php' );
		$this->add_buffer('</center>');
		return $this->get_buffer();		
	}

	/*
	 * Créer le formulaire de "gift"
	 */	
	public function get_gift( $mode = 'add', $data = '' )
	{
		$this->clean();
		$this->set_form( 'gift', ROOT_PATH.'/gift.php?mode='.$mode );
		if( $mode == 'edit' && isset($data['id_gift']) )
			$this->set_input( 'hidden', '', 'id_gift', $data['id_gift'] );
		$this->set_fieldset( $mode == 'edit' ? 'Éditer un gift' : 'Ajouter un gift' ); 
		$this->set_input( 'text', 'Titre', 'title', isset($data['title']) ? $data['title'] : '', array( 'placeholder' => 'Titre', 'required' => 'required', 'pattern' => $this->get_pattern( 'title').'{3,64}' ) , $this->help['title'] );
		$this->set_textarea( 'Texte', 'texte', isset($data['texte']) ? $data['texte'] : 'Tapez ici une description de l\'article' );
		
		$this->add_buffer('<center>');
		$this->set_input_submit( $mode == 'edit' ? 'Confirmer' : 'Ajouter' );
		if( $mode == 'edit' )
			$this->set_input_cancel( 'gift.php' );
		$this->add_buffer('</center>');
		return $this->get_buffer();		
	}

	/*
	 * Créer le formulaire de selection "wishlist"
	 */		
	public function get_select_wishlist( $id_gift, $data )
	{
		$this->clean();
		$this->set_form( 'gift_'.$id_gift, ROOT_PATH.'/gift.php?mode=insert_into_wl' );
		// TODO : ajouter la $page number pour le retour
		$this->set_input( 'hidden', '', 'id_gift', $id_gift );
		$this->buffer.= '<select name="id_wishlist">';
		foreach( $data as $arr )
		{
			$this->buffer.= '<option value="'.$arr['id_wishlist'].'">'.$arr['title'].'</option>';
		}
		$this->buffer.= '</select>';
				
		
		$this->set_input_submit( 'Ajouter à ma wishlist' );
		
		return $this->get_buffer();
	}

	/*
	 * Créer le formulaire d'envoie par mail "wishlist"
	 */		
	public function get_share( $id_wishlist )
	{
		$this->clean();
		$this->set_form( 'share', ROOT_PATH.'/gift.php?mode=share' );
		$this->set_input( 'hidden', '', 'id_wishlist', $id_wishlist );
		$this->set_input( 'hidden', '', 'id_user', $_SESSION['id_user'] );
		$this->set_fieldset( 'Partager par email' ); 
		$this->set_input( 'text', 'Username', 'username', isset($_POST['username']) ? $_POST['username'] : '', array( 'placeholder' => 'Username', 'required' => 'required', 'pattern' => $this->get_pattern( 'username').'{6,32}' ), $this->help['username'] );
		$this->set_input( 'text', 'Email', 'email', isset($_POST['email']) ? $_POST['email'] : '', array( 'placeholder' => 'Email', 'required' => 'required', 'pattern' => $this->get_pattern( 'email').'{8,32}' ) , $this->help['email'] );
		$this->add_buffer('<center>');
		$this->set_input_submit( );
		$this->add_buffer('</center>');
		return $this->get_buffer();
	}

	/*
	 * Set $num_error et $arr_error suivant la $key
	 */		
	private function set_error( $key )
	{
		global $path;
		if( isset($this->err_code[$key]) && isset($this->err_code[$key]) )
		{	
			$this->arr_error[].= $this->err_label[$key]; // => array_push
			// DEV -->
			$this->num_error += $this->err_code[$key];
			// <-- DEV
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
		return $this->arr_error;
	}

	/*
	 * A-t-on une erreur ?
	 */		
	public function has_error()
	{
		return count($this->arr_error) == 0 ? false : true;
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
	 * Réinitialise les erreurs
	 */		
	public function flush_error()
	{
		$this->arr_error = array();
		$this->num_error = 0;
	}
	
	/*
	 * Les infos bulles des formulaires
	 */			
	private $help = array( 
		'username'	=> 'L\'username peut contenir entre 6 et 32 caractères alphanumériques non accentués.<br />Sont également autorisé le point, l\'underscore et le tiret (._-).',	
		'password'	=> 'Le password peut contenir entre entre 8 et 32 caractères alphanumériques non accentués.<br />Il doit contenir au minimum : <br />- un chiffre, <br />- une majuscule, <br />- un caractère spécial parmis : _&~#{}()[]|^@%*+\-\/=$<>!?,.;:',
		'email' => 'L\'email peut contenir entre 6 et 32 caractères alphanumériques non accentués.',
		'title' => 'Le titre peut contenir entre 3 et 64 caractères alphanumériques non accentués.<br />Les caractères spéciaux suivant sont également autorisés : _&~#{}()[]|^@%*+\-\/=$<>!?,.;:',
	);

	/*
	 * Pour la gestion des erreurs
	 * Définition des codes
	 */		
	private $err_code = array(
		'ERR_SUBMIT'					=> 0x00000001,
		'ERR_MODE'						=> 0x00000002,
		'ERR_ORDER_BY'					=> 0x00000004,
		'ERR_ID'						=> 0x00000008,
		'ERR_USERNAME_CHAR'				=> 0x00000010,
		'ERR_USERNAME_LEN'				=> 0x00000020,
		'ERR_PASSWORD_CHAR'				=> 0x00000040,
		'ERR_PASSWORD_NUM'				=> 0x00000080, 
		'ERR_PASSWORD_UPP'				=> 0x00000100,
		'ERR_PASSWORD_SPE'				=> 0x00000200,
		'ERR_PASSWORD_LEN'				=> 0x00000400,
		'ERR_MAIL_CHAR'					=> 0x00000800,
		'ERR_MAIL_LEN'					=> 0x00001000,
		'ERR_ACTKEY'					=> 0x00002000,
		'ERR_TITLE_CHAR'				=> 0x00004000,
		'ERR_TITLE_LEN'					=> 0x00008000,
		'ERR_TEXT_EMPTY'				=> 0x00010000,
		'ERR_TEXT_CHAR'					=> 0x00012000,
		'ERR_PAGE'						=> 0x00014000,
	);

	/*
	 * Pour la gestion des erreurs
	 * Définition des labels (textes associées aux erreurs)
	 */		
	private $err_label = array(
		'ERR_SUBMIT'					=> "Accès non autorisé.",
		'ERR_MODE'						=> "Accès non autorisé.",
		'ERR_ORDER_BY'					=> "Accès non autorisé.",
		'ERR_ID'						=> "L'id est invalide.",
		'ERR_USERNAME_CHAR'				=> "Le nom d'utilisateur contient des caractères non autorisés.",
		'ERR_USERNAME_LEN'				=> "Le nom d'utilisateur doit contenir entre 6 et 32 caractères.",
		'ERR_PASSWORD_CHAR'				=> "Le mot de passe contient des caractères non autorisés.",
		'ERR_PASSWORD_NUM'				=> "Le mot de passe doit contenir au moins 1 chiffre.",
		'ERR_PASSWORD_UPP'				=> "Le mot de passe doit contenir au moins 1 majuscule.",
		'ERR_PASSWORD_SPE'				=> "Le mot de passe doit contenir au moins 1 caractère spécial.",
		'ERR_PASSWORD_LEN'				=> "Le mot de passe doit contenir entre 8 et 32 caractères.",
		'ERR_MAIL_CHAR'					=> "L'email est invalide.",
		'ERR_MAIL_LEN'					=> "L'email doit contenir au maximum 32 caractères.",
		'ERR_ACTKEY'					=> "La clé est invalide.",
		'ERR_TITLE_CHAR'				=> "Le titre contient des caractères non autorisés.",
		'ERR_TITLE_LEN'					=> "Le titre doit contenir entre 3 et 64 caractères.",
		'ERR_TEXT_EMPTY'				=> "Le champs texte est vide.",
		'ERR_TEXT_CHAR'					=> "Le champs texte contient des caractères non autorisé.",
		'ERR_PAGE'						=> "La page est invalide."
	);	
}
?>