<?php
/**
 * 2007-2015 Leotheme
 *
 * NOTICE OF LICENSE
 *
 *  Content Management
 *
 * DISCLAIMER
 *
 *  @author    leotheme <leotheme@gmail.com>
 *  @copyright 2007-2015 Leotheme
 *  @license   http://leotheme.com - prestashop template provider
 */

define('_LEO_BLOG_PREFIX_', 'LEOBLOG_');
require_once(_PS_MODULE_DIR_.'leoblog/classes/config.php');

$config = LeoBlogConfig::getInstance();


define('_LEOBLOG_BLOG_IMG_DIR_', _PS_MODULE_DIR_.'leoblog/views/img/');
define('_LEOBLOG_BLOG_IMG_URI_', __PS_BASE_URI__.'modules/leoblog/views/img/');


define('_LEOBLOG_CATEGORY_IMG_URI_', _PS_MODULE_DIR_.'leoblog/views/img/');
define('_LEOBLOG_CATEGORY_IMG_DIR_', __PS_BASE_URI__.'modules/leoblog/views/img/');

define('_LEOBLOG_CACHE_IMG_DIR_', _PS_IMG_DIR_.'leoblog/');
define('_LEOBLOG_CACHE_IMG_URI_', _PS_IMG_.'leoblog/');

$link_rewrite = 'link_rewrite'.'_'.Context::getContext()->language->id;
define('_LEO_BLOG_REWRITE_ROUTE_', $config->get($link_rewrite, 'blog'));

if (!is_dir(_LEOBLOG_BLOG_IMG_DIR_.'c')) {
    # validate module
    mkdir(_LEOBLOG_BLOG_IMG_DIR_.'c', 0777, true);
}

if (!is_dir(_LEOBLOG_BLOG_IMG_DIR_.'b')) {
    # validate module
    mkdir(_LEOBLOG_BLOG_IMG_DIR_.'b', 0777, true);
}

if (!is_dir(_LEOBLOG_CACHE_IMG_DIR_)) {
    # validate module
    mkdir(_LEOBLOG_CACHE_IMG_DIR_, 0777, true);
}
if (!is_dir(_LEOBLOG_CACHE_IMG_DIR_.'c')) {
    # validate module
    mkdir(_LEOBLOG_CACHE_IMG_DIR_.'c', 0777, true);
}
if (!is_dir(_LEOBLOG_CACHE_IMG_DIR_.'b')) {
    # validate module
    mkdir(_LEOBLOG_CACHE_IMG_DIR_.'b', 0777, true);
}

require_once(_PS_MODULE_DIR_.'leoblog/libs/Helper.php');
require_once(_PS_MODULE_DIR_.'leoblog/classes/leoblogcat.php');
require_once(_PS_MODULE_DIR_.'leoblog/classes/blog.php');
require_once(_PS_MODULE_DIR_.'leoblog/classes/link.php');
require_once(_PS_MODULE_DIR_.'leoblog/classes/comment.php');
require_once(_PS_MODULE_DIR_.'leoblog/classes/LeoblogOwlCarousel.php');
