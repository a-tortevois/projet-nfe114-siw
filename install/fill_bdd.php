<?php
include("../config.php");
// Création de gifts
for( $i = 1 ; $i < 200 ; $i++ )
{
	$sql = 'INSERT INTO mwl_gifts SET 
				title = "gift_'.$i.'",
				texte = "description pour le gift #'.$i.'",
				add_time = NOW(),
				upd_time = NOW()';
						
	if( $req = $mysqli->my_query($sql) )
		echo $sql.'<br/>';
}

// Création d'user
for( $i = 1 ; $i < 10 ; $i++ )
{
	$sql = 'INSERT INTO mwl_users SET 
				username = "user_'.$i.'",
				password = "'.password_hash('@Zerty!7', PASSWORD_BCRYPT, ["cost" => PWD_HASH_COST]).'",
				email = "user_'.$i.'@test.com",
				reg_time = NOW(),
				is_activate = 1,
				actkey = "'.uniqid().'"';
	if( $req = $mysqli->my_query($sql) )
		echo $sql.'<br/>';
}

// Création de wishlists
for( $i = 1 ; $i < 10 ; $i++ )
{
	for( $j = 1 ; $j <= 20 ; $j++ )
	{
		mt_srand(make_seed());
		$sql = 'INSERT INTO mwl_wishlists SET 
					id_user = '.$i.',
					title = "Liste #'.$j.' de user_'.$i.'",
					is_shared = "'. floor( mt_rand(1, 199) / 100) .'",
					add_time = NOW(),
					upd_time = NOW()';
		if( $req = $mysqli->my_query($sql) )
			echo $sql.'<br/>';
	}
}

// Création de wishlist_gifts
for( $i = 1 ; $i < 200 ; $i ++ )
{
	for( $j = 1 ; $j <= 50 ; $j++ )
	{
		mt_srand(make_seed());
		$sql = 'INSERT INTO mwl_wishlist_gifts SET 
					id_wishlist = '.$i.',
					id_gift = '.floor( mt_rand(101, 1999) / 100 ).',
					id_reserver = '.floor( mt_rand(1, 999) / 100).',
					add_time = NOW()';
		if( $req = $mysqli->my_query($sql) )
			echo $sql.'<br/>';
	}
}

$sql = 'UPDATE `mwl_wishlist_gifts` SET id_reserver = NULL WHERE id_reserver = 0';
$req = $mysqli->my_query($sql);

//Création de subscribers
for( $i = 1 ; $i < 20 ; $i++ )
{
	$nb = floor( mt_rand(101, 999) / 100 );
	$arr = array();
	for( $j = 1 ; $j <= $nb ; $j++ )
	{
		do 
		{
			$id_user = floor( mt_rand(101, 999) / 100 );
		}
		while( in_array($id_user, $arr) );
		$arr[].= $id_user;
		
		$sql = 'INSERT INTO mwl_subscribers SET 
					id_wishlist = '.$i.',
					id_user = '.$id_user;
					
		if( $req = $mysqli->my_query($sql) )
			echo $sql.'<br/>';
	}
}
?>