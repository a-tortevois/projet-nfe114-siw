<?php
class page
{
	private $buffer = '';
	private $form;
	
	public function __construct()
	{
		ob_start();
		$this->header();
		
		// require_once( 'class.form.php' );
		if( isset($GLOBALS['form']) )
			$this->form = $GLOBALS['form'];
		else
			$this->form = new form();
	}

    public function __destruct()
	{	
		$this->flush_buffer();
		
		// TODO : DANGER $GLOBALS !!
		if( RESUME_TRACE_SQL && isset($GLOBALS['mysqli']) )
			echo PHP_EOL . '<!--' . $GLOBALS['mysqli']->get_arr_query() . '-->';
    }
	
	/*
	 * Génère le code html pour "view les wishlist"
	 * Appel de $wishlist->get_wishlists nécessaire pour récupérer le tableau
	 */		
	public function add_wishlists( $arr )
	{
		$this->buffer.= '<table id="wishlist"><thead><tr>';
		$this->buffer.= '<th class="title">';
		$this->buffer.= '<a href="'.ROOT_PATH.'/index.php?order_by=title-asc"><img src="'.ROOT_PATH.'/img/asc.png" alt="asc" width="9" height="6" /></a>';
		$this->buffer.= ' Titre ';
		$this->buffer.= '<a href="'.ROOT_PATH.'/index.php?order_by=title-desc"><img src="'.ROOT_PATH.'/img/desc.png" alt="desc" width="9" height="6" /></a>';
		$this->buffer.= '</th>';
		$this->buffer.= '<th class="username">';
		$this->buffer.= '<a href="'.ROOT_PATH.'/index.php?order_by=username-asc"><img src="'.ROOT_PATH.'/img/asc.png" alt="asc" width="9" height="6" /></a>';
		$this->buffer.= ' Username ';
		$this->buffer.= '<a href="'.ROOT_PATH.'/index.php?order_by=username-desc"><img src="'.ROOT_PATH.'/img/desc.png" alt="desc" width="9" height="6" /></a>';
		$this->buffer.= '</th> ';
		$this->buffer.= '<th class="action">Action</th>';
		$this->buffer.= '</tr></thead><tbody>';
		$count = 0;
		foreach( $arr as $row )
		{
			$this->buffer.= '<tr';
			$this->buffer.= ( $count %2 != 0 ) ? ' class="odd">' : '>';
			$this->buffer.= '<td class="title"><a href="'.ROOT_PATH.'/gift.php?id_wishlist='.$row['id']['wishlist'].'">'.$row['title'].'</a></td>';
			$this->buffer.= '<td class="username"><a href="'.ROOT_PATH.'/index.php?id_user='.$row['id']['user'].'">'.$row['username'].'</a></td>';
			$this->buffer.= '<td class="action">'.$this->get_actions( 'wishlist', $row['id'], $row['actions'] ) .'</td>';
			$this->buffer.= '</tr>';
			$count++;
		}
		$this->buffer.= '</tbody></table>';	
	}

	/*
	 * Génère le code html pour "view gift list"
	 * Appel de $gift->get_gifts_list() ou $gift->get_gifts_for_this_wishlist() nécessaire pour récupérer le tableau
	 */		
	public function add_gifts_list( $arr )
	{		
		$count = 0;
		foreach( $arr as $row )
		{					
			if( $count == 0 )
			{
				$this->buffer.= '<table id="gift"><thead><tr><th class="title">Titre</th>';

				if( isset($row['wishlists']) ) // with col form
				{
					$class_sufixe = 'wcf-';
					$this->buffer.= '<th colspan="2" class="wcf-action">Action</th>';
				}
				else
				{
					$class_sufixe = '';
					$this->buffer.= '<th class="action">Action</th>';
				}
				$this->buffer.= '</tr></thead><tbody>';
			}
			
			$this->buffer.= '<tr';
			$this->buffer.= ( $count %2 != 0 ) ? ' class="odd">' : '>';
			$this->buffer.= '<td class="title"><a href="'.ROOT_PATH.'/gift.php?'.( !empty($row['id']['wishlist']) ? 'id_wishlist='.$row['id']['wishlist'].'&&' : '' ) . 'id_gift='. $row['id']['gift'].'">'.$row['title'].'</a></td>';
			
			if( isset($row['wishlists']) )
			{
				if( !empty($row['wishlists']) && !empty($row['id']['gift']) )
				{
					// TODO : if( isset($row['actions']['select']) ) ??
					// {
						$this->buffer.= '<td class="action-form">';
						$this->buffer.= $this->form->get_select_wishlist($row['id']['gift'], $row['wishlists']);
						$this->buffer.= '</td>';
					// }
				}
				else
				{
					$this->buffer.= '<td class="action-form"></td>';
				}
			}
			
			$this->buffer.= '<td class="'.$class_sufixe.'action">'.$this->get_actions( 'gift', $row['id'], $row['actions'] ).'</td>';
			
			$this->buffer.= '</tr>';
			$count++;
		}
		$this->buffer.= '</tbody></table>';	
	}

	/*
	 * Génère le code html pour "view gift"
	 * Appel de $gift->get_gift() nécessaire pour récupérer le tableau
	 */	
	public function add_gift( $row )
	{
		$this->buffer.= '<div id="viewgift"><h1>'.$row['title'].'</h1>';
		$this->buffer.= '<div id="gift-action">'.$this->get_actions( 'gift', $row['id'], array( 'edit', 'delete' ) ).'</div>';
		$this->buffer.= '<div id="text">'.$row['texte'].'</div>';
		$this->buffer.= '<div id="action">'. ( ( !empty($row['wishlists']) && !empty($row['id']['gift']) ) ? $this->form->get_select_wishlist($row['id']['gift'], $row['wishlists']) : '' );
		$this->buffer.= $this->get_actions( 'gift', $row['id'], $row['actions'] ).'</div>';
		$this->buffer.= '</div>';
	}

	/*
	 * Génère le code html pour les actions
	 */		
	public function get_actions( $src, $arr_id, $actions )
	{
		$buffer = '';
		
		switch( $src )
		{
			case 'wishlist':
				$id = $arr_id['wishlist'];
			break;
			
			case 'gift':
				$id = $arr_id['gift'];
			break;			
		}

		foreach( $actions as $value )
		{
			switch($value)
			{
				case 'edit' :
					$buffer.= '<a href="'.ROOT_PATH.'/'.$src.'.php?mode=edit&&id_'.$src.'='.$id.'" title="Éditer"><img src="'.ROOT_PATH.'/img/'.$src.'_edit.png" alt="edit" width="30" height="30" /></a>';
				break;
				
				case 'delete' :
					$buffer.= '<a href="'.ROOT_PATH.'/'.$src.'.php?mode=delete&&id_'.$src.'='.$id.'" title="Supprimer"><img src="'.ROOT_PATH.'/img/'.$src.'_delete.png" alt="delete" width="30" height="30" /></a>';
				break;
				
				case 'share' : // wishlist
					$buffer.= '<a href="'.ROOT_PATH.'/'.$src.'.php?mode=share&&id_wishlist='.$id.'" title="Partager"><img src="'.ROOT_PATH.'/img/'.$src.'_share.png" alt="share" width="30" height="30" /></a>';
				break;
				
				case 'unshare' : // wishlist
					$buffer.= '<a href="'.ROOT_PATH.'/wishlist.php?mode=unshare&&id_wishlist='.$id.'" title="Privé"><img src="'.ROOT_PATH.'/img/wishlist_unshare.png" alt="unshare" width="30" height="30" /></a>';
				break;
				
				case 'subscribe' : // wishlist
					$buffer.= '<a href="'.ROOT_PATH.'/wishlist.php?mode=subscribe&&id_wishlist='.$id.'" title="S\'inscrire"><img src="'.ROOT_PATH.'/img/wishlist_subscribe.png" alt="subscribe" width="30" height="30" /></a>';
				break;
				
				case 'unsubscribe' : // wishlist
					$buffer.= '<a href="'.ROOT_PATH.'/wishlist.php?mode=unsubscribe&&id_wishlist='.$id.'" title="Se désincrire"><img src="'.ROOT_PATH.'/img/wishlist_unsubscribe.png" alt="unsubscribe" width="30" height="30" /></a>';
				break;
				
				/*
				case 'select' : // gift
					if( !empty($wishlists) && isset($GLOBALS['form']) )
					{
						$buffer.= $GLOBALS['form']->get_select_wishlist($id, $wishlists);
					}

				break;
				*/
				
				case 'booking' : // gift
					$buffer.= '<a href="'.ROOT_PATH.'/gift.php?mode=booking&&id_wishlist='.$arr_id['wishlist'].'&&id_gift='.$arr_id['gift'].'" title="Réserver"><img src="'.ROOT_PATH.'/img/gift_valid.png" alt="booking" width="30" height="30" /></a>';
				break;

				case 'cancel' : // gift
					$buffer.= '<a href="'.ROOT_PATH.'/gift.php?mode=cancel&&id_wishlist='.$arr_id['wishlist'].'&&id_gift='.$arr_id['gift'].'" title="Annuler la réservation"><img src="'.ROOT_PATH.'/img/gift_cancel.png" alt="cancel" width="30" height="30" /></a>';
				break;
				
				case 'booked' : // gift
					$buffer.= '<img src="'.ROOT_PATH.'/img/gift_booked.png" alt="booked" title="Ce gift est déjà réservé" width="30" height="30" />';
				break;
				
				case 'delete_from_wl' : // gift
					$buffer.= '<a href="'.ROOT_PATH.'/gift.php?mode=delete_from_wl&&id_wishlist='.$arr_id['wishlist'].'&&id_gift='.$arr_id['gift'].'" title="Supprimer"><img src="'.ROOT_PATH.'/img/gift_delete.png" alt="delete" width="30" height="30" /></a>';
				break;
			}
		}
		return $buffer;
	}

	/*
	 * Génère et ajoute la pagination $buffer
	 * $page -> pour le lien de la page // basename($_SERVER['PHP_SELF'])
	 * $this_page -> la page sur laquelle on est
	 * $nb_page -> le nombre total de page
	 */		
	public function get_pagin( $page, $this_page, $nb_page )
	{
		$buffer = '<div id="pagin">';
		if( $this_page >= 2 )
		{
			$buffer.= '<a href="'.ROOT_PATH.'/'.$page.'page=1">&lt;&lt;</a>&nbsp;';
		}
		if( $this_page > 2 )
		{	
			$buffer.= '<a href="'.ROOT_PATH.'/'.$page.'page='.($this_page - 1).'">&lt;</a>&nbsp;';			
		}
						
		$buffer.= 'Page '.$this_page;

		if( ($nb_page - $this_page) >= 2 )
		{
			$buffer.= '&nbsp;<a href="'.ROOT_PATH.'/'.$page.'page='.($this_page + 1).'">&gt;</a>';
		}
		if( ($nb_page - $this_page) >= 1 )
		{
			$buffer.= '&nbsp;<a href="'.ROOT_PATH.'/'.$page.'page='.$nb_page.'">&gt;&gt;</a>';
		}						

		$buffer.= '</div>';
		return $buffer;
	}

	/*
	 * Ajoute une boite de dialogue au $buffer
	 * $type -> error ou valid 
	 * $msg -> message à afficher
	 */		
	public function set_msg_box( $type, $str )
	{
		if( is_array( $str ) )
			$str = implode( '<br/>', $str);

		$this->buffer.= '<div id="msg_box" class="'.$type.'">';
		$this->buffer.= $str;
		$this->buffer.= '</div>';
	}
	
	/*
	 * Check des $GLOBALS
	 * Si une erreur est trouvé -> set_msg_box( "error", $msg )
	 */		
	public function set_err_box()
	{
		$msg = '';
		if( isset($GLOBALS['mysqli']) )
		{
			if( $GLOBALS['mysqli']->has_error() )
			$msg.= implode( '<br/>', $GLOBALS['mysqli']->get_error());
		} 
		
		if( isset($GLOBALS['form']) )
		{
			if( $GLOBALS['form']->has_error() )
			$msg.= implode( '<br/>', $GLOBALS['form']->get_error());
		} 
		
		if( isset($GLOBALS['user']) )
		{		
			if( $GLOBALS['user']->has_error() )
				$msg.= implode( '<br/>', $GLOBALS['user']->get_error());
		}

		if( isset($GLOBALS['wishlist']) )
		{		
			if( $GLOBALS['wishlist']->has_error() )
				$msg.= implode( '<br/>', $GLOBALS['wishlist']->get_error());
		}

		if( isset($GLOBALS['gift']) )
		{		
			if( $GLOBALS['gift']->has_error() )
				$msg.= implode( '<br/>', $GLOBALS['gift']->get_error());
		}
		
		// not used ...
		// if( isset($_SESSION['msg_error']) )
		// {		
			// if( !empty($_SESSION['msg_error']) )
				// $msg.= implode( '<br/>', $GLOBALS['user']->get_error());
		// }
		
		if( !empty($msg) )
			$this->set_msg_box( "error", $msg );
	}

	/*
	 * Ajouter (du code html) au $buffer
	 */		
	public function add( $buffer )
	{
		$this->buffer.= $buffer;
	}	
	
	/*
	 * Nettoyer le $buffer
	 * Attention : réinitialise par destruction du contenu
	 */		
	public function clean_buffer()
	{
		$this->buffer = '';
	}

	/*
	 * Envoie le $buffer à l'affichage
	 */		
	public function flush_buffer()
	{
		echo $this->buffer;
		ob_end_flush();
	}
	
	/*
	 * Ajouter une balise meta au $buffer
	 * Avec auto-détermination de la position avant <title>
	 */		
	public function add_meta( $meta )
	{
		$pos = strpos($this->buffer, '<title>');
		$this->buffer = substr($this->buffer, 0, $pos) . $meta . substr($this->buffer, $pos);		
	}

	/*
	 * Ajouter l'éditeur tinymce au $buffer
	 */		
	public function add_tinymce()
	{
		if (($tpl = file_get_contents(ROOT_PATH.'/template/tinymce.html')) === false)
		{
			trigger_error('Could not load tinymce template');
		}
		else
		{
			$pos = strpos($this->buffer, '</head>');
			$this->buffer = substr($this->buffer, 0, $pos) . $tpl . substr($this->buffer, $pos);
		}
	}

	/*
	 * Ajouter un fichier template au $buffer
	 */		
	public function add_tpl( $file )
	{
		if (($tpl = file_get_contents(ROOT_PATH.'/template/'.$file.'.html')) === false)
			trigger_error('Could not load '.$file.' template');
		else
			$this->buffer.= $tpl;	
	}

	/*
	 * Remplace les variables du $buffer
	 */	
	public function assign_vars( $arr )
	{
		$this->buffer = preg_replace( array_keys($arr), $arr, $this->buffer );
	}

	/*
	 * Changement de destination ...
	 */
	public function go_to( $page )
	{
		header("Location: ".ROOT_PATH."/".$page.".php");
		exit;
	}
	
	/*
	 * Ajout du "header.html" au $buffer
	 */
	private function header()
	{	
		if (($tpl = file_get_contents(ROOT_PATH.'/template/header.html')) === false)
		{
			trigger_error('Could not load header template');
		}
		else
		{
			$this->add( $tpl );
		}
		
		if( $_SESSION['connected'] )
		{	
			$tpl = '<div id="user_box"><div id="user_box-content">Bienvenue, '.$_SESSION['username'].'<br/>';
			$tpl.= '<a href="'.ROOT_PATH.'/user.php?mode=logout">Déconnexion</a><br/>';
			$tpl.= '<a href="'.ROOT_PATH.'/user.php?mode=change_pwd">Changer de mot de passe</a><br/>';
			$tpl.= '<a href="'.ROOT_PATH.'/index.php?id_user='.$_SESSION['id_user'].'">Mes Listes</a>';
			$tpl.= '</div></div>';
			
			$pos = strpos($this->buffer, '</header>');
			$this->buffer = substr($this->buffer, 0, $pos) . $tpl . substr($this->buffer, $pos);
			
			$this->add( '<nav><a href="{ROOT_PATH}/index.php">Liste d\'envie</a> - <a href="{ROOT_PATH}/gift.php">Liste des cadeaux</a></nav>' );
		}
	}

	/*
	 * Ajout du "footer.html" au $buffer
	 */	
	public function footer()
	{
		
		if (($tpl = file_get_contents(ROOT_PATH.'/template/footer.html')) === false)
		{
			trigger_error('Could not load footer template');
		}
		else
		{
			$this->add( $tpl );
			
			$sql = '';
			// TODO DANGER $GLOBALS !!
			if( isset($GLOBALS['mysqli']) )
			{
				$nb_queries = $GLOBALS['mysqli']->get_nb_query();
				if( $nb_queries != 0 )
				{
					$sql = ' avec '.$nb_queries.' requête';
					if( $nb_queries > 1 )
						$sql .= 's';
					$sql .= ' SQL';
				}
			}
			
			// TODO : à optimiser pour ne pas reparcourir tout le buffer ...
			$this->assign_vars( array(
				'`{EXEC_TIME}`'	=> round( exec_time() - EXEC_TIME, 6),
				'`{SQL}`'		=> $sql,
			));
		}	
	}
}