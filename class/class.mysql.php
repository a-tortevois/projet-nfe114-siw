<?php
class My_SQL extends mysqli
{
	//variable
	private $host;
	private $user;
	private $pass;
	private $dbb;
	private $arr_query = array();
	private $arr_error = array();

	/*
	 * Ouvre la connexion, gère suivant mode : CLI / localhost / serveur distant
	 */		
	public function __construct()
	{
		if( preg_match('/[\\\]/', $_SERVER['PHP_SELF']) ) // CLI : chemin absolu
		{
			$this->host = "localhost";		// serveur
			$this->user = "root";			// nom d'utilisateur
			$this->pass = "";				// mot de passe
			$this->dbb  = "mywishlist";		// nom de la base "locale"		
		}
		else if( preg_match( '`127.0.0.1`', $_SERVER['HTTP_HOST']) ) // localhost 
		{
			$this->host = "localhost";		// serveur
			$this->user = "root";			// nom d'utilisateur
			$this->pass = "";				// mot de passe
			$this->dbb  = "mywishlist";		// nom de la base "locale"
		}
		else // serveur distant
		{
			$this->host = "tortevois.mysql.db";	// serveur
			$this->user = ""; 	// nom d'utilisateur
			$this->pass = "";	// mot de passe
			$this->dbb  = ""; 	// nom de la base 
		}
		
		parent::__construct( $this->host, $this->user, $this->pass, $this->dbb );

		if( $this->connect_errno )
		{
			printf("Connection failed : error #%s\n", $this->connect_errno);
			exit();
		}
			
		$this->set_charset("utf8");
	}

	
	/*
	 * Ferme la connexion
	 */			
    public function __destruct()
	{
        $this->close();
    }

	/*
	 * Extension/Surcharge de $mysqli->query()
	 */		
	public function my_query($sql)
	{
		if( DEBUG_TRACE_SQL ) echo $sql.'<br/>';
		$req = $this->query($sql);
		$this->arr_query[].= $sql;
		if( !$req )
		{
			$this->set_error();
			return false;
		}
		return $req;
	}

	/*
	 * Retourne $arr_query
	 */	
	public function get_nb_query()
	{
		return count( $this->arr_query );
	}
		
	/*
	 * Set $arr_error suivant l'erreur retourné par mysqli
	 */	
	private function set_error()
	{
		if( DEBUG_TRACE_SQL ) echo 'SQL Error : '.$this->error.'<br/>';
		$this->arr_error[].= 'SQL Error : '.$this->error;
	}

	/*
	 * A-t-on une erreur ?
	 */
	public function has_error()
	{
		return count( $this->arr_error ) == 0 ? false : true;
	}

	/*
	 * Retourne $arr_error
	 */	
	public function get_error()
	{
		return $this->arr_error;
	}

	/*
	 * Initialise $arr_error
	 */	
	public function flush_error()
	{
		$this->arr_error = array();
	}

	/*
	 * Retourne une string de $arr_query
	 * Pour du debug
	 */		
	public function get_arr_query()
	{
		$buffer = PHP_EOL;
		$count = 0;
		foreach( $this->arr_query as $query )
		{
			$buffer.= '['.$count.'] : '.$query . PHP_EOL;
			$count++;
		}
		return $buffer;
	}
}

/*
 * Initilise la variable $mysqli dans les autres classes
 */
function init_mysqli()
{
	if( isset($GLOBALS['mysqli']) )
	{
		global $mysqli;
	}
	else
	{
		$mysqli = new My_SQL();
		// trigger_error('La class mysqli n\'est pas instancée.');
		// exit;
	}
	return $mysqli;
}
?>