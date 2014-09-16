<?php

if (!defined('MCR')) exit;

$page = 'Бан-лист'; $menu->SetItemActive('qexy_banlist');

define('BL_VERSION', '1.0');
define('BL_STYLE', STYLE_URL.'Default/banlist/');
define('BL_URL', BASE_URL.'go/banlist/');
define('BL_ROP', 20); // Результатов на страницу
define('BL_TABLE', 'banlist');

$_SESSION['num_q'] = 0;

function MFA($result){		return mysql_fetch_array($result);			}
function MNR($result){		return mysql_num_rows($result);				}
function HSC($result){		return htmlspecialchars($result);			}

function pagination(){
	ob_start();

	if(isset($_GET['pid'])){$pid = intval($_GET['pid']);}else{$pid = 1;}
	$query = BD("SELECT COUNT(*) FROM `".BL_TABLE."`");
	$ar = MFA($query);
	$max = intval(ceil($ar[0] / BL_ROP));

	if($pid<=0 || $pid>$max){ return ob_get_clean(); }

	if($max>1)
	{

		$FirstPge='<li><a href="'.BL_URL.'1"><<</a></li>';
		if($pid-2>0){$Prev2Pge	='<li><a href="'.BL_URL.($pid-2).'">'.($pid-2).'</a></li>';}else{$Prev2Pge ='';}
		if($pid-1>0){$PrevPge	='<li><a href="'.BL_URL.($pid-1).'">'.($pid-1).'</a></li>';}else{$PrevPge ='';}
		$SelectPge = '<li><a href="'.BL_URL.$pid.'"><b>'.$pid.'</b></a></li>';
		if($pid+1<=$max){$NextPge	='<li><a href="'.BL_URL.($pid+1).'">'.($pid+1).'</a></li>';}else{$NextPge ='';}
		if($pid+2<=$max){$Next2Pge	='<li><a href="'.BL_URL.($pid+2).'">'.($pid+2).'</a></li>';}else{$Next2Pge ='';}
		$LastPge='<li><a href="'.BL_URL.$max.'">>></a></li>';
		include(BL_STYLE."pagination.html");
	}

	return ob_get_clean();
}

function bl_object(){
		ob_start();
		$var = base64_decode('Y2xhc3M9ImJsX2NvcHkiPkJhbi1MaXN0IMKpIDxhIGhyZWY9Imh0dHA6Ly9xZXh5Lm9yZyI+UWV4eS5vcmc8L2E+');
		echo '<div '.$var.'</div>';
		return ob_get_clean();
	}

function array_bans(){
	ob_start();

	if(isset($_GET['pid'])){$pid = intval($_GET['pid']);}else{$pid = 1;}
	$start = $pid * BL_ROP - BL_ROP;

	$query = BD("SELECT * FROM `".BL_TABLE."` ORDER BY `id` DESC LIMIT $start,".BL_ROP."");
	if(!$query || MNR($query)<=0){ include_once(BL_STYLE.'ban-none.html'); return ob_get_clean(); }
	while($ar = MFA($query)){
		$id = intval($ar['id']);
		$name = HSC($ar['name']);
		$reason = HSC($ar['reason']);
		$admin = HSC($ar['admin']);
		$bantime_u = intval($ar['time']);
		$bantime = date("d.m.Y в H:i:s", $bantime_u);
		$endtime_u = intval($ar['temptime']);
		$endtime = ($endtime_u==0) ? 'Нет' : date("d.m.Y в H:i:s", $endtime_u);
		$type = intval($ar['type']);

		switch($type){
			case 0: $type = 'Бан'; break;
			case 1: $type = 'Бан по IP'; break;
			case 2: $type = 'Предупреждение'; break;
			case 3: $type = 'Кик'; break;
			case 4: $type = 'Штраф'; break;
			case 5: $type = 'Разбан'; break;
			case 6: $type = 'Тюрьма'; break;
			case 9: $type = 'Перманентный бан'; break;

			default: $type = 'Неизвестно'; break;
		}

		include(BL_STYLE."ban-id.html");
	}
	return ob_get_clean();
}

function banlist(){
	ob_start();
	$pagination = pagination();
	$array_bans = array_bans();
	include_once(BL_STYLE.'main.html');
	return ob_get_clean();
}

$content_main = banlist();
$content_main .= bl_object();

unset($_SESSION['num_q']);

?>