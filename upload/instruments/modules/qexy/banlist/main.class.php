<?php
/**
 * Banlist for WebMCR
 *
 * Main class
 * 
 * @author Qexy.org (admin@qexy.org)
 *
 * @copyright Copyright (c) 2014 Qexy.org
 *
 * @version 1.1.0
 *
 */

// Check Qexy constant
if (!defined('QEXY')){ exit("Hacking Attempt!"); }

class bl_main{

	// Set default variables
	private $user			= false;
	private $db				= false;
	private $api			= false;
	public	$title			= '';
	public	$bc				= '';
	private	$cfg			= array();

	// Set constructor vars
	public function __construct($api){
		$this->user			= $api->user;
		$this->db			= $api->db;
		$this->cfg			= $api->cfg;
		$this->api			= $api;
	}

	private function bl_array(){
		ob_start();

		$start		= $this->api->pagination($this->cfg['rop_list'], 0, 0); // Set start pagination

		$end		= $this->cfg['rop_list']; // Set end pagination

		$query = $this->db->query("SELECT `{$this->cfg['r_id']}`, `{$this->cfg['r_login']}`, `{$this->cfg['r_by']}`,
										`{$this->cfg['r_reason']}`, `{$this->cfg['r_time']}`, `{$this->cfg['r_temp']}`,
										`{$this->cfg['r_type']}`
									FROM `{$this->cfg['table']}`
									ORDER BY `{$this->cfg['r_id']}` DESC
									LIMIT $start,$end");

		if(!$query || $this->db->num_rows($query)<=0){ echo $this->api->sp("list/bl-none.html"); return ob_get_clean(); } // Check returned result

		while($ar = $this->db->get_row($query)){
			$end = (intval($ar[$this->cfg['r_temp']])<=0) ? "Перманентно" : date("d.m.Y в H:i", $ar[$this->cfg['r_temp']]);

			$type = intval($ar[$this->cfg['r_type']]);

			$login = $this->db->HSC($ar[$this->cfg['r_login']]);

			if($this->cfg['use_us']){ $login = '<a href="'.MCR_URL_ROOT.'/?mode=users&do=full&op='.$login.'" target="_blank">'.$login.'</a>'; }

			switch($type){
				case 0: $type = 'Бан'; break;
				case 1: $type = 'Бан по IP'; break;
				case 2: $type = 'Предупреждение'; break;
				case 3: $type = 'Кик'; break;
				case 4: $type = 'Штраф'; break;
				case 5: $type = 'Разбан'; break;
				case 6: $type = 'Тюрьма'; break;
				case 9: $type = 'Перманентно'; break;

				default: $type = 'Неизвестно'; break;
			}

			$data = array(
				"ID"		=> intval($ar[$this->cfg['r_id']]),
				"LOGIN"		=> $login,
				"BY"		=> $this->db->HSC($ar[$this->cfg['r_by']]),
				"REASON"	=> $this->db->HSC($ar[$this->cfg['r_reason']]),
				"BAN_START"	=> date("d.m.Y в H:i", $ar[$this->cfg['r_time']]),
				"BAN_END"	=> $end,
				"TYPE"		=> $type
			);
			echo $this->api->sp("list/bl-id.html", $data);
		}

		return ob_get_clean();
	}

	public function _list(){
		ob_start();

		$array = array(
			"Главная" => BASE_URL,
			$this->cfg['title'] => BL_URL,
		);

		$sql			= "SELECT COUNT(*) FROM `{$this->cfg['table']}`"; // Set SQL query for pagination function

		$page			= "&do=main&pid="; // Set url for pagination function

		$pagination		= $this->api->pagination($this->cfg['rop_list'], $page, $sql); // Set pagination

		$list			= $this->bl_array(); // Set content to variable

		$data = array(
			"PAGINATION" => $pagination,
			"CONTENT" => $list
		);

		$this->bc		= $this->api->bc($array); // Set breadcrumbs

		echo $this->api->sp('list/bl-list.html', $data);

		return ob_get_clean();
	}
}

/**
 * Banlist for WebMCR
 *
 * Main class
 * 
 * @author Qexy.org (admin@qexy.org)
 *
 * @copyright Copyright (c) 2014 Qexy.org
 *
 * @version 1.1.0
 *
 */
?>