<?php

$sql = array();
$sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'vpos_response`(
		`vpos_response_id` int(11) NOT NULL AUTO_INCREMENT,
		`cart_id` INT( 11 ) NOT NULL,
        `order_number` VARCHAR( 155 ) NOT NULL ,
        `response_code` VARCHAR( 15 ) NOT NULL ,
        `response_description` VARCHAR( 255 ) NOT NULL,
        `authorization_number` VARCHAR( 15 ) NOT NULL,
        PRIMARY KEY (`vpos_response_id`)
    ) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET= utf8;';

$sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'vpos_request` (
		`vpos_request_id` int(11) NOT NULL AUTO_INCREMENT,
		`shop_process_id` varchar( 255 ) NOT NULL ,
        `cart_id` INT( 11 ) NOT NULL ,
        `date_added` datetime NOT NULL ,
        `rollback_time` datetime NOT NULL,
         PRIMARY KEY (`vpos_request_id`)
    ) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET= utf8;';
    
$sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'vpos_card` (
        `vpos_card_id` int(11) NOT NULL AUTO_INCREMENT,
        `card_id` INT( 11 ) NOT NULL ,
        `user_id` INT( 11 ) NOT NULL ,
        `token` varchar( 255 ) NOT NULL ,
        `user_cell` varchar( 255 ) NOT NULL ,
        `user_mail` varchar( 255 ) NOT NULL ,
        `date_added` datetime NOT NULL ,
         PRIMARY KEY (`vpos_card_id`)
    ) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET= utf8;';

	foreach ($sql as $query) {
	    if (Db::getInstance()->execute($query) == false) {
	        return false;
	    }
	}

?>