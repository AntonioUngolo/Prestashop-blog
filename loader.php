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

define('_CS24_BLOG_PREFIX_', 'CS24BLOG_');
require_once(_PS_MODULE_DIR_.'cs24blog/classes/config.php');

$config = Cs24BlogConfig::getInstance();


define('_CS24BLOG_BLOG_IMG_DIR_', _PS_MODULE_DIR_.'cs24blog/views/img/');
define('_CS24BLOG_BLOG_IMG_URI_', __PS_BASE_URI__.'modules/cs24blog/views/img/');


define('_CS24BLOG_CATEGORY_IMG_URI_', _PS_MODULE_DIR_.'cs24blog/views/img/');
define('_CS24BLOG_CATEGORY_IMG_DIR_', __PS_BASE_URI__.'modules/cs24blog/views/img/');

define('_CS24BLOG_CACHE_IMG_DIR_', _PS_IMG_DIR_.'leoblog/');
define('_CS24BLOG_CACHE_IMG_URI_', _PS_IMG_.'leoblog/');

$link_rewrite = 'link_rewrite'.'_'.Context::getContext()->language->id;
define('_LEO_BLOG_REWRITE_ROUTE_', $config->get($link_rewrite, 'blog'));

if (!is_dir(_CS24BLOG_BLOG_IMG_DIR_.'c')) {
    # validate module
    mkdir(_CS24BLOG_BLOG_IMG_DIR_.'c', 0777, true);
}

if (!is_dir(_CS24BLOG_BLOG_IMG_DIR_.'b')) {
    # validate module
    mkdir(_CS24BLOG_BLOG_IMG_DIR_.'b', 0777, true);
}

if (!is_dir(_CS24BLOG_CACHE_IMG_DIR_)) {
    # validate module
    mkdir(_CS24BLOG_CACHE_IMG_DIR_, 0777, true);
}
if (!is_dir(_CS24BLOG_CACHE_IMG_DIR_.'c')) {
    # validate module
    mkdir(_CS24BLOG_CACHE_IMG_DIR_.'c', 0777, true);
}
if (!is_dir(_CS24BLOG_CACHE_IMG_DIR_.'b')) {
    # validate module
    mkdir(_CS24BLOG_CACHE_IMG_DIR_.'b', 0777, true);
}

require_once(_PS_MODULE_DIR_.'cs24blog/libs/Helper.php');
require_once(_PS_MODULE_DIR_.'cs24blog/classes/cat.php');
require_once(_PS_MODULE_DIR_.'cs24blog/classes/blog.php');
require_once(_PS_MODULE_DIR_.'cs24blog/classes/link.php');
require_once(_PS_MODULE_DIR_.'cs24blog/classes/comment.php');
require_once(_PS_MODULE_DIR_.'cs24blog/classes/Cs24BlogOwlCarousel.php');
