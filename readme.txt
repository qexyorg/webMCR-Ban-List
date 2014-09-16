- Устанавливаем на сервер плагин Ultrabans - http://dev.bukkit.org/bukkit-plugins/ultrabans/ (названия таблиц используйте по умолчанию)

- Заливаем все файлы в главный каталог webmcr

- Открываем файл .htaccess и после строки
RewriteRule ^go/([^/]+)/?$ index.php?mode=$1 [L,NE]
Добавляем
RewriteRule ^go/banlist/([0-9]+)/?$ index.php?mode=banlist&pid=$1 [L,NE]

- Открываем файл instruments/menu_items.php и перед
  ),
  
  1 => array (

Добавляем

    'qexy_banlist' => array (
  
      'name' => 'Бан-лист',
      'url' => Rewrite::GetURL('banlist'),
      'parent_id' => -1,
      'lvl' => -1,
      'permission' => -1,
      'active' => false,
      'inner_html' => '',
    ),

Готово