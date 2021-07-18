<?php

$sql = array();
$sql[] = 'DROP TABLE IF EXISTS `'._DB_PREFIX_.'vpos_response`';
$sql[] = 'DROP TABLE IF EXISTS `'._DB_PREFIX_.'vpos_request`';
$sql[] = 'DROP TABLE IF EXISTS `'._DB_PREFIX_.'vpos_card`';

foreach ($sql as $query) {
    if (Db::getInstance()->execute($query) == false) {
        return false;
    }
}


?>