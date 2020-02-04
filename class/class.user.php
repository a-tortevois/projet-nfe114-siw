<?php
class user
{
	// common
	private $mysqli;
	private $arr_error = array();
	private $num_error = 0;
	// other variables
	private $id = '';
	private $username = '';
	private $password = '';
	private $new_pwd1 = '';
	private $new_pwd2 = '';	
	private	$email = '';
	private $actkey = '';
		
	public function __construct( $arr )
	{
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
						trigger_error('$user->constructor : La variable $user->'.$key.' n\'existe pas.');
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
	 * Activer un compte
	 */		
	public function activate()
	{
		if( !empty($this->id) )
		{
			if( empty($this->actkey) )
				$this->set_error('ERR_ACTKEY_FIELD_EMPTY');
		}
		else
			$this->set_error('ERR_ID_FIELD_EMPTY');
		
		if( !$this->has_error() )
		{
			$sql = 'SELECT * FROM mwl_users WHERE id_user = '.$this->id;
			if( $req = $this->mysqli->my_query($sql) )
			{
				if( $req->num_rows != 0 )
				{
					$row = $req->fetch_object();
					if( $row->is_activate == 0 )
					{
						if( $this->actkey == $row->actkey)
						{
							$sql = 'UPDATE mwl_users SET is_activate = 1 WHERE id_user = '.$this->id;
							if( $req = $this->mysqli->my_query($sql) )
								return true;
						}
						else
							$this->set_error('ERR_ACTKEY_HAS_EXPIRED');
					}
					else
						$this->set_error('ERR_ACCOUNT_ALREADY_ACTIVED');
				}
				else
					$this->set_error('ERR_USER_NOT_FOUND');
			}
		}
		return false;
	}

	/*
	 * Connexion
	 */
	public function login()
	{
		$row = $this->sql_check_username();
		
		if( empty($this->password) )
			$this->set_error('ERR_PWD_FIELD_EMPTY');
		
		if( !$this->has_error() )
		{
			if( password_verify($this->password, $row->password) )
			{
				if( $row->is_activate == 1 )
				{
					$_SESSION['id_user'] = $row->id_user;
					$_SESSION['username'] = $this->username;
					$_SESSION['connected'] = true;
					// TODO : des cookies (pour 4h!)
					return true;
				}
				else
					$this->set_error('ERR_ACCOUNT_NOT_ACTIVED');
			}
			else
				$this->set_error('ERR_PWD_NOT_MATCH');
		}
		return false;
	}

	/*
	 * Enregistrement d'un nouveau compte
	 */
	public function register()
	{
		if( !empty($this->username) )
		{
			$sql = 'SELECT * FROM mwl_users WHERE username = "'.$this->username.'"';
			if( $req = $this->mysqli->my_query($sql) )		
				if( $req->num_rows != 0 )
					$this->set_error('ERR_USER_ALREADY_REGISTERED');
		}
		else
			$this->set_error('ERR_USER_FIELD_EMPTY');
		
		if( empty($this->new_pwd1) )
			$this->set_error('ERR_NEWPWD1_FIELD_EMPTY');

		if( empty($this->new_pwd2) )
			$this->set_error('ERR_NEWPWD2_FIELD_EMPTY');

		if( $this->new_pwd1 !== $this->new_pwd2 )
			$this->set_error('ERR_NEWPWD_NOT_MATCH');
		
		if( !empty($this->email) )
		{		
			$sql = 'SELECT * FROM mwl_users WHERE email = "'.$this->email.'"';
			if( $req = $this->mysqli->my_query($sql) )		
				if( $req->num_rows != 0 )
					$this->set_error('ERR_MAIL_ALREADY_REGISTERED');
		}
		else
			$this->set_error('ERR_MAIL_FIELD_EMPTY');
		
		if( !$this->has_error() )
		{		
			$this->set_act_key();
			$sql = 'INSERT INTO mwl_users SET 
						username = "'.$this->username.'", 
						password = "'.password_hash($this->new_pwd1, PASSWORD_BCRYPT, ["cost" => PWD_HASH_COST]).'", 
						email = "'.$this->email.'", 
						reg_time = NOW(),
						actkey = "'.$this->actkey.'"';
			
			if( $req = $this->mysqli->my_query($sql) )
			{
				$this->id = $this->mysqli->insert_id;
				$link = $this->get_act_link();
				$subject = "Validation de votre inscription";
				$message = file_get_contents(ROOT_PATH.'/template/mail_register.html');
				$assign_vars = array (
									'`{SITE_URL}`'		=> SITE_URL,
									'`{SITE_NAME}`'		=> SITE_NAME,
									'`{ACTKEY_URL}`'	=> $link,
									'`{USERNAME}`'		=> $this->username,
									'`{PASSWORD}`'		=> $this->new_pwd1,
								);
				$message = preg_replace( array_keys($assign_vars), $assign_vars, $message );
				
				return $this->send_mail( $this->email, $subject, $message );
			}
		}
		return false;
	}

	/*
	 * Changer de mot de passe
	 */
	public function change_pwd()
	{
		if( !empty($this->id) )
		{
			if( empty($this->password) )
				$this->set_error('ERR_PWD_FIELD_EMPTY');
			
			if( empty($this->new_pwd1) )
				$this->set_error('ERR_NEWPWD1_FIELD_EMPTY');
			
			if( empty($this->new_pwd2) )
				$this->set_error('ERR_NEWPWD2_FIELD_EMPTY');
			
			if( $this->new_pwd1 !== $this->new_pwd2 )
				$this->set_error('ERR_NEWPWD_NOT_MATCH');				
		}
		else
			$this->set_error('ERR_ID_FIELD_EMPTY');
		
		if( !$this->has_error() )
		{
			$sql = 'SELECT * FROM mwl_users WHERE id_user = '.$this->id;
			if( $req = $this->mysqli->my_query($sql) )
			{
				if( $req->num_rows != 0 )
				{
					$row = $req->fetch_object();
					if( password_verify($this->password, $row->password) )
					{
						$sql = 'UPDATE mwl_users SET password = "'.password_hash($this->new_pwd1, PASSWORD_BCRYPT, ["cost" => PWD_HASH_COST]).'" WHERE id_user = '.$this->id;
						if( $req = $this->mysqli->my_query($sql) )
						{
							$subject = "Modification de votre mot de passe";
							$message = file_get_contents(ROOT_PATH.'/template/mail_pwd.html');
							$assign_vars = array (
												'`{SITE_URL}`'		=> SITE_URL,
												'`{SITE_NAME}`'		=> SITE_NAME,
												'`{USERNAME}`'		=> $row->username,
												'`{PASSWORD}`'		=> $this->new_pwd1,
											);
							$message = preg_replace( array_keys($assign_vars), $assign_vars, $message );
							
							return $this->send_mail( $row->email, $subject, $message );
						}
					}	
					else
						$this->set_error('ERR_PWD_NOT_MATCH');
				}
				else
					$this->set_error('ERR_USER_NOT_FOUND');
			}
		}
		return false;
	}

	/*
	 * Envoyer un nouveau mot de passe
	 */	
	public function reset_pwd()
	{		
		$row = $this->sql_check_username();
		
		if( !$this->has_error() )
		{
			$password = $this->generate_rand_key($opt = ['dig' => 3, 'low' => 3, 'upp' => 3, 'spe' => 3]);
			$sql =  'UPDATE mwl_users SET password = "'.password_hash($password, PASSWORD_BCRYPT, ["cost" => PWD_HASH_COST]).'" WHERE id_user = '.$row->id_user;
			if( $req = $this->mysqli->my_query($sql) )
			{
				$subject = "Votre nouveau mot de passe";
				$message = file_get_contents(ROOT_PATH.'/template/mail_pwd.html');
				$assign_vars = array (
									'`{SITE_URL}`'		=> SITE_URL,
									'`{SITE_NAME}`'		=> SITE_NAME,
									'`{USERNAME}`'		=> $row->username,
									'`{PASSWORD}`'		=> $password,
								);
				$message = preg_replace( array_keys($assign_vars), $assign_vars, $message );
				
				return $this->send_mail( $row->email, $subject, $message );
			}
		}
		return false;
	}

	/*
	 * Envoyer une nouvelle clé d'activation
	 */	
	public function reset_act()
	{
		$row = $this->sql_check_email();
		
		if( $row->is_activate == 1 )
			$this->set_error('ERR_ACCOUNT_ALREADY_ACTIVED');
		
		if( !$this->has_error() )
		{
			$this->set_act_key();
			$sql =  'UPDATE mwl_users SET actkey = "'.$this->get_actkey().'" WHERE id_user = '.$row->id_user;
			if( $req = $this->mysqli->my_query($sql) )
			{
				$this->id = $row->id_user;
				$link = $this->get_act_link();
				$subject = "Lien d'activation de votre compte";
				$message = file_get_contents(ROOT_PATH.'/template/mail_actkey.html');
				$assign_vars = array (
									'`{SITE_URL}`'		=> SITE_URL,
									'`{SITE_NAME}`'		=> SITE_NAME,
									'`{USERNAME}`'		=> $row->username,
									'`{ACTKEY_URL}`'	=> $link,
								);
				$message = preg_replace( array_keys($assign_vars), $assign_vars, $message );
				return $this->send_mail( $row->email, $subject, $message );
			}
		}
		return false;
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
	 * Générer une clé ou un mot de passe aléatoire
	 */		
	public function generate_rand_key($options)
	{	
		mt_srand(make_seed());
		$key = '';
		
		if( isset( $options['dig'] ) )
		{
			$chaine = "0123456789";
			for( $i=0 ; $i<$options['dig'] ; $i++ )
			{
				$key .= $chaine[mt_rand()%strlen($chaine)];
			}
		}
		
		if( isset( $options['low'] ) )
		{
			$chaine = "abcdefghijklmnopqrstuvwxyz";
			for( $i=0 ; $i<$options['low'] ; $i++ )
			{
				$key .= $chaine[mt_rand()%strlen($chaine)];
			}
		}
		
		if( isset( $options['upp'] ) )
		{
			$chaine = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
			for( $i=0 ; $i<$options['upp'] ; $i++ )
			{
				$key .= $chaine[mt_rand()%strlen($chaine)];
			}
		}
		
		if( isset( $options['spe'] ) )
		{
			$chaine = "_&~#{}()[]|_\^@%*+-/=$<>!?,.;:";
			for( $i=0 ; $i<$options['spe'] ; $i++ )
			{
				$key .= $chaine[mt_rand()%strlen($chaine)];
			}
		}
		
		return str_shuffle($key); // Shake it !
	}

	/*
	 * Check si $username est déjà enregistré
	 */	
	private function sql_check_username() 
	{
		if( !empty($this->username) )
		{
			$sql = 'SELECT * FROM mwl_users WHERE username = "'.$this->username.'"';
			if( $req = $this->mysqli->my_query($sql) )
			{
				if( $req->num_rows != 0 )
				{
					return $req->fetch_object();
				}
				else
				{
					$this->set_error('ERR_USER_NOT_FOUND');
				}
			}
		}
		else
			$this->set_error('ERR_USER_FIELD_EMPTY');
		
		return null;
	}

	/*
	 * Check si $email est déjà enregistré
	 */	
	private function sql_check_email() 
	{
		if( !empty($this->email) )
		{
			$sql = 'SELECT * FROM mwl_users WHERE email = "'.$this->email.'"';
			if( $req = $this->mysqli->my_query($sql) )
			{
				if( $req->num_rows != 0 )
				{
					return $req->fetch_object();
				}
				else
				{
					$this->set_error('ERR_MAIL_NOT_FOUND');
				}
			}
		}
		else
			$this->set_error('ERR_MAIL_FIELD_EMPTY');
		
		return null;
	}

	/*
	 * Set $actkey avec un clé aléatoire
	 */		
	public function set_act_key()
	{
		$this->actkey = $this->generate_rand_key($opt = ['dig' => 16, 'low' => 16]);
	}

	/*
	 * Get $actkey
	 */		
	public function get_actkey()
	{
		return $this->actkey;
	}
	
	/*
	 * Retourne lien d'activation
	 */	
	public function get_act_link()
	{
		return SITE_URL.'/user.php?mode=activate&id='.$this->id.'&actkey='.$this->actkey;
	}
	
	/*
	 * Get $id
	 */	
	public function get_id()
	{
		return $this->id;
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
		'ERR_ACTKEY_HAS_EXPIRED'		=> 0x00001,
		'ERR_ACTKEY_FIELD_EMPTY'		=> 0x00002,
		'ERR_ACCOUNT_ALREADY_ACTIVED'	=> 0x00004,
		'ERR_ACCOUNT_NOT_ACTIVED'		=> 0x00008,
		'ERR_ID_FIELD_EMPTY'			=> 0x00010,
		'ERR_USER_NOT_FOUND'			=> 0x00020,
		'ERR_USER_ALREADY_REGISTERED'	=> 0x00040,
		'ERR_USER_FIELD_EMPTY'			=> 0x00080,
		'ERR_PWD_NOT_MATCH'				=> 0x00100,
		'ERR_PWD_FIELD_EMPTY'			=> 0x00200,
		'ERR_NEWPWD_NOT_MATCH'			=> 0x00400,
		'ERR_NEWPWD1_FIELD_EMPTY'		=> 0x00800,
		'ERR_NEWPWD2_FIELD_EMPTY'		=> 0x01000,
		'ERR_MAIL_ALREADY_REGISTERED'	=> 0x02000,
		'ERR_MAIL_FIELD_EMPTY'			=> 0x04000,
		'ERR_MAIL_NOT_SENT'				=> 0x08000,
		'ERR_MAIL_NOT_FOUND'			=> 0x10000,
	);
	
	/*
	 * Pour la gestion des erreurs
	 * Définition des labels (textes associées aux erreurs)
	 */	
	private $err_label = array(
		'ERR_ACTKEY_HAS_EXPIRED'		=> "La clé d'activation est expirée.",
		'ERR_ACTKEY_FIELD_EMPTY'		=> "Le champs actkey est vide.",
		'ERR_ACCOUNT_ALREADY_ACTIVED'	=> "Votre compte est déjà activé.",
		'ERR_ACCOUNT_NOT_ACTIVED'		=> "Votre compte n'est pas activé.",
		'ERR_ID_FIELD_EMPTY'			=> "Le champs id est vide.",
		'ERR_USER_NOT_FOUND'			=> "L'utilisateur n'existe pas.",
		'ERR_USER_ALREADY_REGISTERED'	=> "Cet utilisateur existe déjà.",
		'ERR_USER_FIELD_EMPTY'			=> "Le champs username est vide.",
		'ERR_PWD_NOT_MATCH'				=> "Votre mot passe ne correspond pas.",
		'ERR_PWD_FIELD_EMPTY'			=> "Le champs password est vide.",
		'ERR_NEWPWD_NOT_MATCH'			=> "Les mots de passe ne correspondent pas.",
		'ERR_NEWPWD1_FIELD_EMPTY'		=> "Le champs nouveau mot de passe est vide.",
		'ERR_NEWPWD2_FIELD_EMPTY'		=> "Le champs de confirmation du mot de passe est vide.",
		'ERR_MAIL_ALREADY_REGISTERED'	=> "Cet email existe déjà.",
		'ERR_MAIL_FIELD_EMPTY'			=> "Le champs email est vide.",
		'ERR_MAIL_NOT_SENT'				=> "L'email n'a pas pu être envoyé.",
		'ERR_MAIL_NOT_FOUND'			=> "L'email n'existe pas.",
	);
}