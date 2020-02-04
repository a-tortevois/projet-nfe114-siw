<?php
include("../config.php");

function exec_sql( $sql )
{
	global $mysqli;
	if( $req = $mysqli->my_query($sql) )
		echo ' <span style="font-weight: bold; color: green;">OK</span>';
	else
	{
		echo ' <span style="font-weight: bold; color: red;">NOK</span>';
		echo implode( ' ', $mysqli->get_error() );
		$mysqli->flush_error();
	}
	echo '<br/>';
}

if( !isset($_GET['confirm']) )
{	
?>
<script type="text/javascript" language="javascript">
res = confirm("Cette opération va effacer toute les données éventuellement existantes.\n Êtes vous sur de vouloir (ré)initialiser la base de donnée ?");
if (res == true) {
	window.location = './install.php?confirm=ok';
}
else {
	window.location = './';
}
</script>
<?php
}
if( $_GET['confirm'] == 'ok' )
{
	$sql = "SHOW TABLES";
	if( $req = $mysqli->my_query($sql) )
	{
		if( $req->num_rows != 0 )
		{
			while( $row = $req->fetch_row() )
			{
				if( preg_match( '/^mwl_/', $row[0] ) )
				{
					echo 'Suppresion de la table `'.$row[0].'` ... ';
					$sql = 'DROP TABLE `'.$row[0].'`';
					exec_sql($sql);
				}
			}		
		}
	}
	else
	{
		echo implode( ' ', $mysqli->get_error() );
		exit;
	}

	$sql = 'SELECT VERSION()';
	$version = false;
	if( $req = $mysqli->my_query($sql) )
	{
		if( $req->num_rows != 0 )
		{
			$row = $req->fetch_row();
			preg_match_all( '/([\d]+)/', $row[0], $match );
				
			$v = array( '5', '6', '50' );
			
			for( $i == 0 ; $i < 2 ; $i++ )
			{
				if( $match[0][$i] > $v[$i] )
				{
					$version = true;
					break;
				}
			}
		}

	}
	else
	{
		echo implode( ' ', $mysqli->get_error() );
		exit;
	}

	// --------------------------------------------------------
	// Structure de la table `mwl_gifts`
	// --------------------------------------------------------

	echo 'Création de la table `mwl_gifts` ... ';
	$sql = "CREATE TABLE `mwl_gifts` (
				  `id_gift` int(11) NOT NULL,
				  `title` varchar(64) COLLATE utf8_bin NOT NULL,
				  `texte` text COLLATE utf8_bin NOT NULL,
				  `add_time` timestamp NULL DEFAULT NULL,
				  `upd_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
				) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;";
	exec_sql($sql);

	// --------------------------------------------------------
	// Structure de la table `mwl_subscribers`
	// --------------------------------------------------------

	echo 'Création de la table `mwl_subscribers` ... ';
	$sql = "CREATE TABLE `mwl_subscribers` (
				  `id_user` int(11) NOT NULL,
				  `id_wishlist` int(11) NOT NULL
				) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;";
	exec_sql($sql);

	// --------------------------------------------------------
	// Structure de la table `mwl_users`
	// --------------------------------------------------------

	echo 'Création de la table `mwl_users` ... ';
	$sql = "CREATE TABLE `mwl_users` (
				  `id_user` int(11) NOT NULL,
				  `username` varchar(32) COLLATE utf8_bin NOT NULL,
				  `password` varchar(60) COLLATE utf8_bin NOT NULL,
				  `email` varchar(32) COLLATE utf8_bin NOT NULL,
				  `reg_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
				  `is_activate` tinyint(1) DEFAULT '0',
				  `actkey` varchar(32) COLLATE utf8_bin NOT NULL
				) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;";
	exec_sql($sql);

	// --------------------------------------------------------
	// Structure de la table `mwl_wishlists`
	// --------------------------------------------------------

	echo 'Création de la table `mwl_wishlists` ... ';
	$sql = "CREATE TABLE `mwl_wishlists` (
				  `id_wishlist` int(11) NOT NULL,
				  `id_user` int(11) NOT NULL,
				  `title` varchar(64) COLLATE utf8_bin NOT NULL,
				  `is_shared` tinyint(1) DEFAULT NULL,
				  `add_time` timestamp NULL DEFAULT NULL,
				  `upd_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
				) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;";
	exec_sql($sql);

	// --------------------------------------------------------
	// Structure de la table `mwl_wishlist_gifts`
	// --------------------------------------------------------

	echo 'Création de la table `mwl_wishlist_gifts` ... ';
	$sql = "CREATE TABLE `mwl_wishlist_gifts` (
				  `id_wishlist` int(11) NOT NULL,
				  `id_gift` int(11) NOT NULL,
				  `id_reserver` int(11) DEFAULT NULL,
				  `add_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
				) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;";
	exec_sql($sql);

	// --------------------------------------------------------
	// Index pour la table `mwl_gifts`
	// --------------------------------------------------------

	echo 'Création des index de la table `mwl_gifts` ... ';
	$sql = "ALTER TABLE `mwl_gifts` 
					ADD PRIMARY KEY (`id_gift`),
					MODIFY `id_gift` int(11) NOT NULL AUTO_INCREMENT;";
	exec_sql($sql);				

	// --------------------------------------------------------
	// Index pour la table `mwl_subscribers`
	// --------------------------------------------------------

	echo 'Création des index de la table `mwl_subscribers` ... ';
	$sql = "ALTER TABLE `mwl_subscribers` 
					ADD UNIQUE KEY `subscribe` (`id_user`,`id_wishlist`) USING BTREE;";
	exec_sql($sql);	

	// --------------------------------------------------------
	// Index pour la table `mwl_users`
	// --------------------------------------------------------

	echo 'Création des index de la table `mwl_users` ... ';
	$sql = "ALTER TABLE `mwl_users`
					ADD PRIMARY KEY (`id_user`),
					MODIFY `id_user` int(11) NOT NULL AUTO_INCREMENT;";
	exec_sql($sql);	

	// --------------------------------------------------------
	// Index pour la table `mwl_wishlists`
	// --------------------------------------------------------

	echo 'Création des index de la table `mwl_wishlists` ... ';
	$sql = "ALTER TABLE `mwl_wishlists`
					ADD PRIMARY KEY (`id_wishlist`),
					MODIFY `id_wishlist` int(11) NOT NULL AUTO_INCREMENT;";
	exec_sql($sql);	

	// --------------------------------------------------------
	// Index pour la table `mwl_wishlist_gifts`
	// --------------------------------------------------------

	echo 'Création des index de la table `mwl_wishlist_gifts` ... ';
	$sql = "ALTER TABLE `mwl_wishlist_gifts`
					ADD UNIQUE KEY `id_wishlist` (`id_wishlist`,`id_gift`);";
	exec_sql($sql);	

	if( $version )
	{
		echo 'Modification du champ `add_time` de la table `mwl_gifts` ... ';
		$sql = "ALTER TABLE `mwl_gifts`
						MODIFY `add_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP;";
		exec_sql($sql);
		
		echo 'Modification du champ `add_time` de la table `mwl_wishlists` ... ';
		$sql = "ALTER TABLE `mwl_wishlists`
						MODIFY `add_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP;";
		exec_sql($sql);
	}
}
?>