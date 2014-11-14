<?php
/**
 * Banlist for WebMCR
 *
 * Install class
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

$content_js .= '<link href="'.BASE_URL.'install_statics/styles/css/install.css" rel="stylesheet">';

class bl_install{
	// Set default variables
	private $cfg			= array();
	private $user			= false;
	private $db				= false;
	private $init			= false;
	private $configs		= array();
	public	$in_header		= '';
	public	$title			= '';

	// Set counstructor values
	public function __construct($init){

		$this->cfg			= $init->cfg;
		$this->user			= $init->user;
		$this->db			= $init->db;
		$this->init			= $init;
		
		if($this->user->lvl < $this->cfg['lvl_admin']){ $this->init->url = ''; $this->init->notify(); }
	}

	private function check_table(){
		$query = $this->db->query("SELECT COUNT(*) FROM `{$this->cfg['table']}`");
		if(!$query){ exit("here0"); return false; }
		$ar = $this->db->get_array($query);
		if(!$ar){ return false; }

		return true;
	}

	/**
	 * filter_text(@param) - Filter for config files
	 *
	 * @param - int,string,boolean
	 *
	 * @return string without chars - < > " '
	 *
	*/
	private function filter_text($var){
		$var = preg_replace("/[\<\>\"\']+/", "", $var);
		return $var;
	}

	private function step_1(){
		ob_start();

		if(!$this->cfg['install']){ $this->init->notify("Установка уже произведена", "", "Ошибка!", 3); }

		$write_menu = $write_cfg = $write_configs = '';

		if(!is_writable(MCR_ROOT.'instruments/menu_items.php')){
			$write_menu = '<div class="alert alert-error"><b>Внимание!</b> Выставите права 777 на файл <b>instruments/menu_items.php</b></div>';
		}

		if(!is_writable(MCR_ROOT.'configs')){
			$write_configs = '<div class="alert alert-error"><b>Внимание!</b> Выставите права 777 на папку <b>configs</b></div>';
		}

		if(!is_writable(MCR_ROOT.'configs/bl.cfg.php')){
			$write_cfg = '<div class="alert alert-error"><b>Внимание!</b> Выставите права 777 на файл <b>configs/bl.cfg.php</b></div>';
		}

		if($_SERVER['REQUEST_METHOD']=='POST'){
			if(!isset($_POST['submit'])){ $this->init->notify("Hacking Attempt!", "&do=install", "403", 3); }

			if(!empty($write_menu) || !empty($write_cfg) || !empty($write_configs)){ $this->init->notify("Требуется выставить необходимые права на запись", "&do=install", "Ошибка!", 3); }

			$this->cfg['table']		= $this->db->safesql($this->filter_text($_POST['table']));
			$this->cfg['r_id']		= $this->db->safesql($this->filter_text($_POST['r_id']));
			$this->cfg['r_login']	= $this->db->safesql($this->filter_text($_POST['r_login']));
			$this->cfg['r_reason']	= $this->db->safesql($this->filter_text($_POST['r_reason']));
			$this->cfg['r_by']		= $this->db->safesql($this->filter_text($_POST['r_by']));
			$this->cfg['r_time']	= $this->db->safesql($this->filter_text($_POST['r_time']));
			$this->cfg['r_temp']	= $this->db->safesql($this->filter_text($_POST['r_temp']));
			$this->cfg['r_type']	= $this->db->safesql($this->filter_text($_POST['r_type']));

			// Check save config
			if(!$this->init->savecfg($this->cfg, "configs/bl.cfg.php")){ $this->init->notify("Ошибка сохранения настроек", "&do=install", "Ошибка!", 3); }

			if(!$this->check_table()){
				$sql = "CREATE TABLE IF NOT EXISTS `{$this->cfg['table']}` (
						  `{$this->cfg['r_id']}` int(10) NOT NULL AUTO_INCREMENT,
						  `{$this->cfg['r_login']}` varchar(64) NOT NULL,
						  `{$this->cfg['r_reason']}` varchar(255) NOT NULL,
						  `{$this->cfg['r_by']}` varchar(64) NOT NULL,
						  `{$this->cfg['r_time']}` int(10) NOT NULL,
						  `{$this->cfg['r_temp']}` int(10) NOT NULL,
						  `{$this->cfg['r_type']}` int(10) NOT NULL,
						  `ip` varchar(32) NOT NULL,
						  PRIMARY KEY (`{$this->cfg['r_id']}`)
						) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;";

				$query = $this->db->query($sql);

				$sql2 = "CREATE TABLE IF NOT EXISTS `banlistip` (
						  `id` int(10) NOT NULL AUTO_INCREMENT,
						  `name` varchar(64) NOT NULL,
						  `lastip` varchar(32) NOT NULL,
						  PRIMARY KEY (`id`)
						) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;";

				$query2 = $this->db->query($sql2);

				if(!$query || !$query2){ $this->init->notify("Ошибка переустановки #2".mysql_error(), "&do=install", "Ошибка!", 3); };
			}

			$_SESSION['install_step'] = "2";

			$this->init->notify("", "&do=install&op=2", "", 2);
		}

		$content = array(
			"WRITE_MENU" => $write_menu,
			"WRITE_CFG" => $write_cfg,
			"WRITE_CONFIGS" => $write_configs,
			"TABLE" => $this->db->HSC($this->cfg['table']),
			"R_ID" => $this->db->HSC($this->cfg['r_id']),
			"R_REASON" => $this->db->HSC($this->cfg['r_reason']),
			"R_BY" => $this->db->HSC($this->cfg['r_by']),
			"R_LOGIN" => $this->db->HSC($this->cfg['r_login']),
			"R_TIME" => $this->db->HSC($this->cfg['r_time']),
			"R_TEMP" => $this->db->HSC($this->cfg['r_temp']),
			"R_TYPE" => $this->db->HSC($this->cfg['r_type']),
		);

		echo $this->init->sp(MCR_ROOT.'install_banlist/styles/step-1.html', $content, true);

		return ob_get_clean();
	}

	private function saveMenu($menu) {
	
		$txt  = "<?php if (!defined('MCR')) exit;".PHP_EOL;
		$txt .= '$menu_items = '.var_export($menu, true).';'.PHP_EOL;

		$result = file_put_contents(MCR_ROOT."instruments/menu_items.php", $txt);

		return (is_bool($result) and $result == false)? false : true;	
	}

	private function step_2(){
		ob_start();

		if($_SERVER['REQUEST_METHOD']=='POST'){
			if(!isset($_POST['submit'])){ $this->init->notify("Hacking Attempt!", "&do=install", "403", 3); }

			require(MCR_ROOT."instruments/menu_items.php");

			if(!isset($menu_items[0]['qexy_banlist'])){
				$menu_items[0]['qexy_banlist'] = array (
				  'name' => 'Бан-лист',
				  'url' => '?mode=statics&do=admin',
				  'parent_id' => 'admin',
				  'lvl' => 15,
				  'permission' => -1,
				  'active' => false,
				  'inner_html' => '',
				);
			}

			if(!$this->saveMenu($menu_items)){ $this->init->notify("Ошибка установки #3", "&do=install", "Ошибка!", 3); }

			$_SESSION['install_step'] = "3";

			$this->init->notify("", "&do=install&op=3", "", 2);
		}

		echo $this->init->sp(MCR_ROOT.'install_banlist/styles/step-2.html', array(), true);

		return ob_get_clean();
	}

	private function step_3(){
		ob_start();

		if($_SERVER['REQUEST_METHOD']=='POST'){
			if(!isset($_POST['submit'])){ $this->init->notify("Hacking Attempt!", "&do=install", "403", 3); }

			$this->cfg['install'] = false;

			if(!$this->init->savecfg($this->cfg, "configs/bl.cfg.php")){ $this->init->notify("Ошибка переустановки #4", "&do=install", "Ошибка!", 3); }

			$_SESSION['install_step'] = "finish";

			$this->init->notify("", "&do=install&op=finish", "", 2);
		}
		
		echo $this->init->sp(MCR_ROOT.'install_banlist/styles/step-3.html', array(), true);

		return ob_get_clean();
	}

	private function finish(){
		ob_start();
	
		$_SESSION['install_finished'] = true;

		unset($_SESSION['install_step']);
		
		echo $this->init->sp(MCR_ROOT.'install_banlist/styles/finish.html', array(), true);

		return ob_get_clean();
	}

	public function _list(){
		ob_start();

		$op = (isset($_GET['op'])) ? $_GET['op'] : 'main';

		/**
		 * Select needed page
		 */

		$step = (!isset($_SESSION['install_step'])) ? "1" : $_SESSION['install_step'];

		switch($step){
			case "2":
				$this->title	= "Установка — Шаг 2"; // Set page title (In tag <title></title>)
				$content		= $this->step_2(); // Set content
				$array = array(
					"Главная" => BASE_URL,
					$this->init->cfg['title'] => BL_URL,
					"Установка" => BL_URL."&do=install",
					"Шаг 2" => ""
				);
				$this->bc		= $this->init->bc($array);
			break;

			case "3":
				$this->title	= "Установка — Шаг 3"; // Set page title (In tag <title></title>)
				$content		= $this->step_3(); // Set content
				$array = array(
					"Главная" => BASE_URL,
					$this->init->cfg['title'] => BL_URL,
					"Установка" => BL_URL."&do=install",
					"Шаг 3" => ""
				);
				$this->bc		= $this->init->bc($array);
			break;

			case "finish":
				$this->title	= "Установка — Конец установки"; // Set page title (In tag <title></title>)
				$content		= $this->finish(); // Set content
				$array = array(
					"Главная" => BASE_URL,
					$this->init->cfg['title'] => BL_URL,
					"Установка" => BL_URL."&do=install",
					"Конец установки" => ""
				);
				$this->bc		= $this->init->bc($array);
			break;

			default:
				$this->title	= "Установка — Шаг 1";
				$content		= $this->step_1();
				$array = array(
					"Главная" => BASE_URL,
					$this->init->cfg['title'] => BL_URL,
					"Установка" => BL_URL."&do=install",
					"Шаг 1" => ""
				);
				$this->bc		= $this->init->bc($array);
			break;
		}

		echo $content;

		return ob_get_clean();
	}
}

/**
 * Banlist for WebMCR
 *
 * Install class
 * 
 * @author Qexy.org (admin@qexy.org)
 *
 * @copyright Copyright (c) 2014 Qexy.org
 *
 * @version 1.1.0
 *
 */
?>
