<?php
/**
 * Banlist for WebMCR
 *
 * Admin class
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

class bl_admin{

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

		if($this->user->lvl < $this->cfg['lvl_admin']){ $this->api->notify("", "&do=main", "", 3); }
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

	private function _main(){
		ob_start();

		$array = array(
			"Главная" => BASE_URL,
			$this->cfg['title'] => BL_URL,
			"Панель управления" => BL_ADMIN_URL
		);

		$this->bc = $this->api->bc($array); // Set breadcrumbs

		// CSRF Security name
		$bl_f_security = 'bl_settings';

		// Check for post method and CSRF hacking
		if($_SERVER['REQUEST_METHOD']=='POST'){
			if(!isset($_POST['submit']) || !$this->api->csrf_check($bl_f_security)){ $this->api->notify("Hacking Attempt!", "&do=main", "403", 3); }

			// Filter saving vars [Start]
			$this->cfg['title']			= $this->filter_text($_POST['title']);
			$this->cfg['rop_list']		= (intval($_POST['rop_list'])<=0) ? 1 : intval($_POST['rop_list']);
			$this->cfg['lvl_access']	= intval($_POST['lvl_access']);
			$this->cfg['lvl_admin']		= intval($_POST['lvl_admin']);
			$this->cfg['use_us']		= (intval(@$_POST['use_us'])==1) ? true : false;
			// Filter saving vars [End]

			// Check save config
			if(!$this->api->savecfg($this->cfg, "configs/bl.cfg.php")){ $this->api->notify("Ошибка сохранения настроек", "&do=admin", "Ошибка!", 3); }

			$this->api->notify("Настройки успешно сохранены", "&do=admin", "Успех!", 1);
		}

		$use_us = ($this->cfg['use_us']) ? 'selected' : '';

		$content = array(
			"TITLE" => $this->db->HSC($this->cfg['title']),
			"ROP_LIST" => intval($this->cfg['rop_list']),
			"LVL_ACCESS" => intval($this->cfg['lvl_access']),
			"LVL_ADMIN" => intval($this->cfg['lvl_admin']),
			"USE_US" => $use_us,
			"BL_F_SET" => $this->api->csrf_set($bl_f_security),
			"BL_F_SECURITY" => $bl_f_security
		);

		echo $this->api->sp("admin/settings_main.html", $content);

		return ob_get_clean();
	}

	private function _base(){
		ob_start();

		$array = array(
			"Главная" => BASE_URL,
			$this->cfg['title'] => BL_URL,
			"Панель управления" => BL_ADMIN_URL,
			"Настройки базы" => '',
		);

		$this->bc = $this->api->bc($array); // Set breadcrumbs

		// CSRF Security name
		$bl_f_security = 'bl_settings_base';

		// Check for post method and CSRF hacking
		if($_SERVER['REQUEST_METHOD']=='POST'){
			if(!isset($_POST['submit']) || !$this->api->csrf_check($bl_f_security)){ $this->api->notify("Hacking Attempt!", "&do=main", "403", 3); }

			// Filter saving vars [Start]
			$this->cfg['table']			= $this->filter_text($_POST['table']);
			$this->cfg['r_id']			= $this->filter_text($_POST['r_id']);
			$this->cfg['r_by']			= $this->filter_text($_POST['r_by']);
			$this->cfg['r_reason']		= $this->filter_text($_POST['r_reason']);
			$this->cfg['r_login']		= $this->filter_text($_POST['r_login']);
			$this->cfg['r_time']		= $this->filter_text($_POST['r_time']);
			$this->cfg['r_temp']		= $this->filter_text($_POST['r_temp']);
			$this->cfg['r_type']		= $this->filter_text($_POST['r_type']);
			// Filter saving vars [End]

			// Check save config
			if(!$this->api->savecfg($this->cfg, "configs/bl.cfg.php")){ $this->api->notify("Ошибка сохранения настроек", "&do=admin&op=base", "Ошибка!", 3); }

			$this->api->notify("Настройки успешно сохранены", "&do=admin&op=base", "Успех!", 1);
		}

		$content = array(
			"TABLE" => $this->db->HSC($this->cfg['table']),
			"R_ID" => $this->db->HSC($this->cfg['r_id']),
			"R_REASON" => $this->db->HSC($this->cfg['r_reason']),
			"R_BY" => $this->db->HSC($this->cfg['r_by']),
			"R_LOGIN" => $this->db->HSC($this->cfg['r_login']),
			"R_TIME" => $this->db->HSC($this->cfg['r_time']),
			"R_TEMP" => $this->db->HSC($this->cfg['r_temp']),
			"R_TYPE" => $this->db->HSC($this->cfg['r_type']),
			"BL_F_SET" => $this->api->csrf_set($bl_f_security),
			"BL_F_SECURITY" => $bl_f_security
		);

		echo $this->api->sp("admin/settings_base.html", $content);

		return ob_get_clean();
	}

	public function _list(){
		ob_start();

		$op = (isset($_GET['op'])) ? $_GET['op'] : 'main';

		switch($op){
			case 'main': echo $this->_main(); break;
			case 'base': echo $this->_base(); break;

			default: $this->api->notify("Страница не найдена", "&do=main", "404", 3);
		}

		return ob_get_clean();
	}
}

/**
 * Banlist for WebMCR
 *
 * Admin class
 * 
 * @author Qexy.org (admin@qexy.org)
 *
 * @copyright Copyright (c) 2014 Qexy.org
 *
 * @version 1.1.0
 *
 */
?>