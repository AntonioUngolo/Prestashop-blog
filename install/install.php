<?php
/**
 * 2024-2026 Compralosubito24
 *
 * NOTICE OF LICENSE
 *
 *  Content Management
 *
 * DISCLAIMER
 *
 *  @author    Compralosubito24 <info@compralosubito24.it>
 *  @copyright 2024-2026 Compralosubito24
 *  @license   https://compralosubito24.it - prestashop template provider
 */

if (!defined('_PS_VERSION_')) {
    # module validation
    exit;
}
$path = dirname(_PS_ADMIN_DIR_);

include_once($path.'/config/config.inc.php');
include_once($path.'/init.php');


$res = (bool)Db::getInstance()->execute('
    CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'cs24_blog_cat` (
        `id_cs24_blog_cat` int(11) NOT NULL AUTO_INCREMENT,
      `image` varchar(255) NOT NULL,
      `id_parent` int(11) NOT NULL,
      `item` varchar(255) DEFAULT NULL,
      `level_depth` smallint(6) NOT NULL,
      `active` tinyint(1) NOT NULL,
      `show_title` tinyint(1) NOT NULL,
      `position` int(11) NOT NULL,
      `submenu_content` text NOT NULL,
      `privacy` smallint(6) DEFAULT NULL,
      `position_type` varchar(25) DEFAULT NULL,
      `menu_class` varchar(25) DEFAULT NULL,
      `content` text,
      `icon_class` varchar(255) DEFAULT NULL,
      `level` int(11) NOT NULL,
      `left` int(11) NOT NULL,
      `right` int(11) NOT NULL,
      `date_add` datetime DEFAULT NULL,
      `date_upd` datetime DEFAULT NULL,
      `template` varchar(200) NOT NULL,
      `randkey` varchar(255) DEFAULT NULL,
         PRIMARY KEY (`id_cs24_blog_cat`)
    ) ENGINE='._MYSQL_ENGINE_.'  DEFAULT CHARSET=utf8;
');
$res &= (bool)Db::getInstance()->execute('
    CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'cs24_blog_cat_lang` (
        `id_cs24_blog_cat` int(11) NOT NULL,
      `id_lang` int(11) NOT NULL,
      `title` varchar(255) DEFAULT NULL,
      `content_text` text,
      `description` varchar(200) NOT NULL,
      `meta_keywords` varchar(255) NOT NULL,
      `meta_description` varchar(255) NOT NULL,
      `link_rewrite` varchar(250) NOT NULL,
      PRIMARY KEY (`id_cs24_blog_cat`,`id_lang`)
    ) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;
');

$res &= (bool)Db::getInstance()->execute('
    CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'cs24_blog_cat_shop` (
        `id_cs24_blog_cat` int(11) NOT NULL DEFAULT \'0\',
          `id_shop` int(11) NOT NULL DEFAULT \'0\',
          PRIMARY KEY (`id_cs24_blog_cat`,`id_shop`)
    ) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;
');


$res &= (bool)Db::getInstance()->execute('
CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'cs24_blog_comment` (
  `id_comment` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_shop` int(11) NOT NULL DEFAULT \'0\',
  `id_cs24_blog_blog` int(11) unsigned NOT NULL,
  `comment` text NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT \'0\',
  `date_add` datetime DEFAULT NULL,
  `user` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  PRIMARY KEY (`id_comment`,`id_shop`),
  KEY `FK_blog_comment` (`id_cs24_blog_blog`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8; ');


$res &= (bool)Db::getInstance()->execute('
CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'cs24_blog_blog` (
  `id_cs24_blog_blog` int(11) NOT NULL AUTO_INCREMENT,
  `id_cs24_blog_cat` int(11) NOT NULL,
  `position` int(11) NOT NULL,
  `date_add` date NOT NULL,
  `active` tinyint(1) NOT NULL,
  `user_id` int(11) NOT NULL,
  `hits` int(11) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `thumb` varchar(255) DEFAULT NULL,
  `date_upd` datetime NOT NULL,
  `video_code` text DEFAULT NULL,
  `params` text DEFAULT NULL,
  `featured` tinyint(1) NOT NULL,
  `favorite` tinyint(1) NOT NULL,
  `indexation` int(11) NOT NULL,
  `id_employee` int(11) NOT NULL,
  `product_ids` varchar(255) DEFAULT NULL,
  `author_name` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id_cs24_blog_blog`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8; ');

$res &= (bool)Db::getInstance()->execute('
CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'cs24_blog_blog_lang` (
  `id_cs24_blog_blog` int(11) NOT NULL,
  `id_lang` int(11) NOT NULL,
  `meta_description` varchar(255) NOT NULL,
  `meta_keywords` varchar(250) NOT NULL,
  `meta_title` varchar(250) NOT NULL,
  `subtitle` varchar(250) NULL,
  `link_rewrite` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `description` text NOT NULL,
  `tags` varchar(255) NOT NULL,
  PRIMARY KEY (`id_cs24_blog_blog`,`id_lang`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8; ');

$res &= (bool)Db::getInstance()->execute('
    CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'cs24_blog_blog_shop` (
        `id_cs24_blog_blog` int(11) NOT NULL DEFAULT \'0\',
          `id_shop` int(11) NOT NULL DEFAULT \'0\',
          PRIMARY KEY (`id_cs24_blog_blog`,`id_shop`)
    ) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;
');



$rows = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('SELECT id_cs24_blog_cat FROM `'._DB_PREFIX_.'cs24_blog_cat`');

if (count($rows) <= 0) {
    $res &= (bool)Db::getInstance()->execute('
        INSERT INTO `'._DB_PREFIX_.'cs24_blog_cat`(`image`,`id_parent`) VALUES(\'\', 0 )
    ');
    $languages = Language::getLanguages(false);
    foreach ($languages as $lang) {
        $res &= (bool)Db::getInstance()->execute('
            INSERT INTO `'._DB_PREFIX_.'cs24_blog_cat_lang`(`id_cs24_blog_cat`,`id_lang`,`title`) VALUES(1, '.(int)$lang['id_lang'].', \'Root\')
        ');
    }
    /*
      $res &= (bool)Db::getInstance()->execute('
      INSERT INTO `'._DB_PREFIX_.'cs24_blog_cat_shop`(`id_cs24_blog_cat`,`id_shop`) VALUES( 1, '.(int)($this->context->shop->id).' )
      ');
     */
    $context = Context::getContext();
    $res &= (bool)Db::getInstance()->execute('
        INSERT INTO `'._DB_PREFIX_.'cs24_blog_cat_shop`(`id_cs24_blog_cat`,`id_shop`) VALUES( 1, '.(int)($context->shop->id).' )
    ');
}


$rows = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('SELECT id_cs24_blog_blog FROM `'._DB_PREFIX_.'cs24_blog_blog`');
if (count($rows) <= 0 && file_exists(_PS_MODULE_DIR_.'leoblog/install/sample.php')) {
    # validate module
    include_once(_PS_MODULE_DIR_.'leoblog/install/sample.php');
} else {
    # validate module
    include_once(_PS_MODULE_DIR_.'leoblog/install/config.php');
}
/* END REQUIRED */
