<?php
/**
 * Banlist for WebMCR
 *
 * General proccess
 * 
 * @author Qexy.org (admin@qexy.org)
 *
 * @copyright Copyright (c) 2014 Qexy.org
 *
 * @version 1.1.0
 *
 */

// Check webmcr constant
if (!defined('MCR')){ exit("Hacking Attempt!"); }

define('QEXY', true);
define('BL_VERSION', '1.1.0');												// Module version
define('BL_STYLE', STYLE_URL.'Default/modules/qexy/banlist/');				// Module style folder
define('BL_URL', BASE_URL.'?mode=banlist');									// Base module URL
define('BL_STYLE_ADMIN', BL_STYLE.'admin/');								// Module style admin folder
define('BL_ADMIN_URL', BL_URL.'&do=admin');									// Base module admin url
define('BL_CLASS_PATH', MCR_ROOT.'instruments/modules/qexy/banlist/');		// Root module class folder
define('MCR_URL_ROOT', 'http://'.$_SERVER['SERVER_NAME']);					// Full base url webmcr

// Loading config
require_once(MCR_ROOT.'configs/bl.cfg.php');

// Loading API
if(!file_exists(MCR_ROOT."instruments/modules/qexy/api/api.class.php")){ exit("API not found! <a href=\"https://github.com/qexyorg/webMCR-API\" target=\"_blank\">Download</a>"); }
require_once(MCR_ROOT."instruments/modules/qexy/api/api.class.php");

// Set default url for module
$api->url = "?mode=banlist";

// Set default style path for module
$api->style = BL_STYLE;

// Set module cfg
$api->cfg = $cfg;

// Check access user level
if($api->user->lvl < $cfg['lvl_access']){ header('Location: '.BASE_URL.'?mode=403'); exit; }

// Set active menu
$menu->SetItemActive('qexy_banlist');

// Set default module page
$do = (isset($_GET['do'])) ? $_GET['do'] : $cfg['main'];

// Set installation variable
if($cfg['install']==true){ $install = true; }

// Check installation
if(isset($install) && $do!=='install'){ $api->notify("Требуется установка", "&do=install", "Внимание!", 4); }

function get_menu($api){
	ob_start();

	if($api->user->lvl < $api->cfg['lvl_admin']){ return ob_get_clean(); }

	echo $api->sp("admin/menu.html");

	return ob_get_clean();
}

// Select page
switch($do){
	// Load module admin
	case 'admin':
		require_once(BL_CLASS_PATH.'admin.class.php');
		$bl_module		= new bl_admin($api);
		$bl_content		= $bl_module->_list();
		$bl_title		= $bl_module->title;
		$bl_bc			= $bl_module->bc;
	break;

	// Load module main
	case 'main':
		require_once(BL_CLASS_PATH.'main.class.php');
		$bl_module		= new bl_main($api);
		$bl_content		= $bl_module->_list();
		$bl_title		= $bl_module->title;
		$bl_bc			= $bl_module->bc;
	break;

	// Load installation
	case 'install':
		if(!isset($install) && !isset($_SESSION['install_finished'])){ $api->notify("Установка уже произведена", "", "Упс!", 4); }
		require_once(MCR_ROOT."install_banlist/install.class.php");
		$bl_module		= new bl_install($api);
		$bl_content		= $bl_module->_list();
		$bl_title		= $bl_module->title;
		$bl_bc			= $bl_module->bc;
	break;

	// Load default menu
	default: $api->notify("Страница не найдена", "&do=main", "404", 3); break;
}
// Set default page title
$page = $cfg['title'].' — '.$bl_title;

// Set data values
$content_data = array(
	"CONTENT" => $bl_content,
	"BC" => $bl_bc,
	"API_INFO" => $api->get_notify(),
	"MENU" => get_menu($api),
);

$content_js .= '<link href="'.BL_STYLE.'css/style.css" rel="stylesheet">';

// Set returned content
$content_main = $api->sp("global.html", $content_data);

/**
 * Banlist for WebMCR
 *
 * General proccess
 * 
 * @author Qexy.org (admin@qexy.org)
 *
 * @copyright Copyright (c) 2014 Qexy.org
 *
 * @version 1.1.0
 *
 */
?>