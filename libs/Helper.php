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

class Cs24BlogHelper
{
    public $bloglink = null;
    public $ssl;

    public static function getInstance()
    {
        static $instance = null;
        if (!$instance) {
            # validate module
            $instance = new Cs24BlogHelper();
        }

        return $instance;
    }

    public function __construct()
    {
        if (Configuration::get('PS_SSL_ENABLED') && Configuration::get('PS_SSL_ENABLED_EVERYWHERE')) {
            $this->ssl = true;
        }

        $protocol_link = (Configuration::get('PS_SSL_ENABLED') || Tools::usingSecureMode()) ? 'https://' : 'http://';
        $use_ssl = ((isset($this->ssl) && $this->ssl && Configuration::get('PS_SSL_ENABLED')) || Tools::usingSecureMode()) ? true : false;
        $protocol_content = ($use_ssl) ? 'https://' : 'http://';
        $this->bloglink = new Cs24BlogLink($protocol_link, $protocol_content);
    }
    
    public static function correctDeCodeData($data)
    {
        $functionName = 'b'.'a'.'s'.'e'.'6'.'4'.'_'.'decode';
        return call_user_func($functionName, $data);
    }

    public static function correctEnCodeData($data)
    {
        $functionName = 'b'.'a'.'s'.'e'.'6'.'4'.'_'.'encode';
        return call_user_func($functionName, $data);
    }

    public static function loadMedia($context, $obj)
    {
		//DONGND:: update new direction for media
		$media_dir = $obj->module->getMediaDir();
        $config = Cs24BlogConfig::getInstance();
        $template = $config->get('template');
        if (Tools::getValue('bloglayout') != null) {
            if (is_dir(_PS_THEME_DIR_.'modules/cs24blog/views/templates/front/'.Tools::getValue('bloglayout'))
                || is_dir(_PS_MODULE_DIR_ .'cs24blog/views/templates/front/'.Tools::getValue('bloglayout'))) {
                $template = Tools::getValue('bloglayout');
            }
        }
        if (file_exists(_PS_THEME_DIR_.$media_dir.'css/'.$template.'.css') || file_exists(_PS_THEME_DIR_.'assets/css/'.$media_dir.'css/'.$template.'.css')) {
            $context->controller->addCSS(__PS_BASE_URI__.$media_dir.'css/'.$template.'.css', 'all');
        } else {
            if (file_exists(_PS_MODULE_DIR_ .'cs24blog/views/css/'.$template.'.css')) {
                $context->controller->addCSS(_PS_MODULE_DIR_ .'cs24blog/views/css/'.$template.'.css');
            } else {
                if (file_exists(_PS_THEME_DIR_.'css/modules/cs24blog/assets/leoblog.css')) {
                    $context->controller->addCSS(__PS_BASE_URI__.$media_dir.'assets/leoblog.css', 'all');
                } else {
                    $context->controller->addCSS(__PS_BASE_URI__.$media_dir.'css/leoblog.css', 'all');
                }
            }
            
        }

        if (file_exists(_PS_THEME_DIR_.'js/modules/cs24blog/assets/leoblog.js')) {
            $context->controller->addJs(__PS_BASE_URI__.$media_dir.'assets/leoblog.js');
        } else {
            $context->controller->addJs(__PS_BASE_URI__.$media_dir.'js/leoblog.js');
        }
    }

    public function getLinkObject()
    {
        return $this->bloglink;
    }

    public function getModuleLink($route_id, $controller, array $params = array(), $ssl = null, $id_lang = null, $id_shop = null)
    {
        return $this->getLinkObject()->getLink($route_id, $controller, $params, $ssl, $id_lang, $id_shop);
    }

    public function getFontBlogLink()
    {
        return $this->getModuleLink('module-cs24blog-list', 'list', array());
    }

    public function getPaginationLink($route_id, $controller, array $params = array(), $nb = false, $sort = false, $pagination = false, $array = true)
    {
        return $this->getLinkObject()->getLeoPaginationLink('leoblog', $route_id, $controller, $params, $nb, $sort, $pagination, $array);
    }

    public function getBlogLink($blog, $params1 = array())
    {
        $params = array(
            'id' => $blog['id_cs24_blog_blog'],
            'rewrite' => $blog['link_rewrite'],
        );

        $params = array_merge($params, $params1);
        return $this->getModuleLink('module-cs24blog-blog', 'blog', $params);
    }

    public function getTagLink($tag)
    {
        $params = array(
            'tag' => $tag,
        );

        return $this->getModuleLink('blog_user_filter_rule', 'blog', $params);
    }

    public function getBlogCatLink($cparams)
    {
        $params = array(
            'id' => '',
            'rewrite' => ''
        );
        $params = array_merge($params, $cparams);
        return $this->getModuleLink('module-cs24blog-category', 'category', $params);
    }

    public function getBlogTagLink($tag, $cparams = array())
    {
        $params = array(
            'tag' => urlencode($tag),
        );
        $params = array_merge($params, $cparams);
        return $this->getModuleLink('module-cs24blog-list', 'list', $params);
    }

    public function getBlogAuthorLink($author, $cparams = array())
    {
        $params = array(
            'author' => $author,
        );
        $params = array_merge($params, $cparams);
        return $this->getModuleLink('module-cs24blog-list', 'list', $params);
    }

    public static function getTemplates()
    {
        $theme = self::getThemeName();
        $path = _PS_MODULE_DIR_.'leoblog';
        $tpath = _PS_ALL_THEMES_DIR_.$theme.'/modules/cs24blog/views/templates/front/*';

        $output = array();

        $templates = glob($path.'/views/templates/front/*', GLOB_ONLYDIR);

        $ttemplates = glob($tpath, GLOB_ONLYDIR);
        if ($templates) {
            foreach ($templates as $t) {
                # validate module
                $output[basename($t)] = array('type' => 'module', 'template' => basename($t));
            }
        }
        if ($ttemplates) {
            foreach ($ttemplates as $t) {
                # validate module
                $output[basename($t)] = array('type' => 'module', 'template' => basename($t));
            }
        }
        
        //add data for custom layout
        if (!Configuration::get(Tools::strtoupper(_CS24_BLOG_PREFIX_.'template_current'))) {
            Configuration::updateValue(Tools::strtoupper(_CS24_BLOG_PREFIX_.'template_current'), 'default');            
        }

        foreach ($output as $key => $value) {
            if ($key != 'default' && !Configuration::get(Tools::strtoupper(_CS24_BLOG_PREFIX_.'cfg_global_'.$key))) {
                if (!Configuration::get(Tools::strtoupper(_CS24_BLOG_PREFIX_.'cfg_global_'.$key))) {
                    Configuration::updateValue(Tools::strtoupper(_CS24_BLOG_PREFIX_.'cfg_global_'.$key), Configuration::get(Tools::strtoupper(_CS24_BLOG_PREFIX_.'cfg_global')));
                }
            }
        }
        return $output;
    }

    public static function buildBlog($helper, $blog, $image_w, $image_h, $config, $check_thumb = false)
    {
        # module validation
        !is_null($image_w) ? true : $image_w = 0;
        !is_null($image_h) ? true : $image_h = 0;

        $url = _PS_BASE_URL_;
        if (Tools::usingSecureMode()) {
            # validate module
            $url = _PS_BASE_URL_SSL_;
        }

        $id_shop = (int)Context::getContext()->shop->id;
        $blog['preview_url'] = '';
        $blog['image_url'] = '';
        
        $blog['preview_thumb_url'] = '';
        $blog['thumb_url'] = '';
        //DONGND:: check callback for Appagebuilder, only create thumb image if exists
        if ($check_thumb && isset($blog['thumb']) && $blog['thumb'] != '') {
            if (isset($blog['thumb']) && $blog['thumb'] != '') {
                $blog['thumb_url'] = (Tools::usingSecureMode() ? 'https://' : 'http://').Tools::getMediaServer($url)._THEME_DIR_.'assets/img/modules/cs24blog/'.$id_shop.'/b/'.$blog['thumb'];
                if (!file_exists(_CS24BLOG_CACHE_IMG_DIR_.'b/'.$id_shop.'/'.$blog['id_cs24_blog_blog'].'/'.$image_w.'_'.$image_h.'/'.$blog['thumb'])) {
                    @mkdir(_CS24BLOG_CACHE_IMG_DIR_.'b/'.$id_shop, 0777);
                    @mkdir(_CS24BLOG_CACHE_IMG_DIR_.'b/'.$id_shop.'/'.$blog['id_cs24_blog_blog'], 0777);
                    @mkdir(_CS24BLOG_CACHE_IMG_DIR_.'b/'.$id_shop.'/'.$blog['id_cs24_blog_blog'].'/'.$image_w.'_'.$image_h, 0777);
                    if (ImageManager::resize(_PS_THEME_DIR_.'assets/img/modules/cs24blog/'.$id_shop.'/b/'.$blog['thumb'], _CS24BLOG_CACHE_IMG_DIR_.'b/'.$id_shop.'/'.$blog['id_cs24_blog_blog'].'/'.$image_w.'_'.$image_h.'/'.$blog['thumb'], $image_w, $image_h)) {
                        # validate module
                        $blog['preview_thumb_url'] = (Tools::usingSecureMode() ? 'https://' : 'http://').Tools::getMediaServer($url)._CS24BLOG_CACHE_IMG_DIR_.'b/'.$id_shop.'/'.$blog['id_cs24_blog_blog'].'/'.$image_w.'_'.$image_h.'/'.$blog['thumb'];
                    }
                }
                $blog['thumb_url'] = (Tools::usingSecureMode() ? 'https://' : 'http://').Tools::getMediaServer($url)._THEME_DIR_.'assets/img/modules/cs24blog/'.$id_shop.'/b/'.$blog['thumb'];
                $blog['preview_thumb_url'] = (Tools::usingSecureMode() ? 'https://' : 'http://').Tools::getMediaServer($url)._CS24BLOG_CACHE_IMG_URI_.'b/'.$id_shop.'/'.$blog['id_cs24_blog_blog'].'/'.$image_w.'_'.$image_h.'/'.$blog['thumb'];
            }
        } else {
            if ($blog['image']) {
                $blog['image_url'] = (Tools::usingSecureMode() ? 'https://' : 'http://').Tools::getMediaServer($url)._THEME_DIR_.'assets/img/modules/cs24blog/'.$id_shop.'/b/'.$blog['image'];
                if (!file_exists(_CS24BLOG_CACHE_IMG_DIR_.'b/'.$id_shop.'/'.$blog['id_cs24_blog_blog'].'/'.$image_w.'_'.$image_h.'/'.$blog['image'])) {
                    @mkdir(_CS24BLOG_CACHE_IMG_DIR_.'b/'.$id_shop, 0777);
                    @mkdir(_CS24BLOG_CACHE_IMG_DIR_.'b/'.$id_shop.'/'.$blog['id_cs24_blog_blog'], 0777);
                    @mkdir(_CS24BLOG_CACHE_IMG_DIR_.'b/'.$id_shop.'/'.$blog['id_cs24_blog_blog'].'/'.$image_w.'_'.$image_h, 0777);
                    if (ImageManager::resize(_PS_THEME_DIR_.'assets/img/modules/cs24blog/'.$id_shop.'/b/'.$blog['image'], _CS24BLOG_CACHE_IMG_DIR_.'b/'.$id_shop.'/'.$blog['id_cs24_blog_blog'].'/'.$image_w.'_'.$image_h.'/'.$blog['image'], $image_w, $image_h)) {
                        # validate module
                        $blog['preview_url'] = (Tools::usingSecureMode() ? 'https://' : 'http://').Tools::getMediaServer($url)._CS24BLOG_CACHE_IMG_DIR_.'b/'.$id_shop.'/'.$blog['id_cs24_blog_blog'].'/'.$image_w.'_'.$image_h.'/'.$blog['image'];
                    }
                }
                $blog['image_url'] = (Tools::usingSecureMode() ? 'https://' : 'http://').Tools::getMediaServer($url)._THEME_DIR_.'assets/img/modules/cs24blog/'.$id_shop.'/b/'.$blog['image'];
                $blog['preview_url'] = (Tools::usingSecureMode() ? 'https://' : 'http://').Tools::getMediaServer($url)._CS24BLOG_CACHE_IMG_URI_.'b/'.$id_shop.'/'.$blog['id_cs24_blog_blog'].'/'.$image_w.'_'.$image_h.'/'.$blog['image'];
            }
        }
        
        $params = array(
            'rewrite' => $blog['category_link_rewrite'],
            'id' => $blog['id_cs24_blog_cat']
        );
        //    if (!$config->get( 'listing_show_counter' , 1)  ) {
        if ($config->get('item_comment_engine', 'local') == 'local') {
            # validate module
            $blog['comment_count'] = Cs24BlogComment::countComments($blog['id_cs24_blog_blog'], true, true);
        }
        //    } else {
        //    $blog['comment_count'] = 0;
        //    }
        $blog['category_link'] = $helper->getBlogCatLink($params);
        $blog['link'] = $helper->getBlogLink($blog);
        return $blog;
    }

    public static function rrmdir($dir)
    {
        if (is_dir($dir)) {
            $objects = scandir($dir);
            foreach ($objects as $object) {
                if ($object != '.' && $object != '..') {
                    if (filetype($dir.'/'.$object) == 'dir') {
                        self::rrmdir($dir.'/'.$object);
                    } else {
                        unlink($dir.'/'.$object);
                    }
                }
            }
            $objects = scandir($dir);
            reset($objects);
            rmdir($dir);
        }
    }

    public static function getConfigKey($multi_lang = false)
    {
        if ($multi_lang == false) {
            return array(
                'saveConfiguration',
                'template',
                'indexation',
                'rss_limit_item',
                'rss_title_item',
                'listing_show_categoryinfo',
                'listing_show_subcategories',
                'listing_leading_column',
                'listing_leading_limit_items',
                'listing_leading_img_width',
                'listing_leading_img_height',
                'listing_secondary_column',
                'listing_secondary_limit_items',
                'listing_secondary_img_width',
                'listing_secondary_img_height',
                'listing_show_title',
                'listing_show_description',
                'listing_show_readmore',
                'listing_show_image',
                'listing_show_author',
                'listing_show_category',
                'listing_show_created',
                'listing_show_hit',
                'listing_show_counter',
                'item_img_width',
                'item_img_height',
                'item_show_description',
                'item_show_image',
                'item_show_author',
                'item_show_category',
                'item_show_created',
                'item_show_hit',
                'item_show_counter',
                'social_code',
                'item_show_listcomment',
                'item_show_formcomment',
                'item_comment_engine',
                'item_limit_comments',
                'item_diquis_account',
                'item_facebook_appid',
                'item_facebook_width',
                'url_use_id',
                'show_popular_blog',
                'limit_popular_blog',
                'show_recent_blog',
                'limit_recent_blog',
                'show_all_tags',
            );
        } else {
            return array(
                'blog_link_title',
                'link_rewrite',
                'category_rewrite',
                'detail_rewrite',
                'meta_title',
                'meta_description',
                'meta_keywords',
            );
        }
    }

    /**
     * @return day in month
     * 1st, 2nd, 3rd, 4th, ...
     */
    public function ordinal($number)
    {
        $ends = array('th', 'st', 'nd', 'rd', 'th', 'th', 'th', 'th', 'th', 'th');
        if ((($number % 100) >= 11) && (($number % 100) <= 13))
            return $number.'th';
        else
            return $number.$ends[$number % 10];
    }

    /**
     * @return day in month
     * st, nd, rd, th, ...
     */
    public function string_ordinal($number)
    {
        $number = (int) $number;
        $ends = array('th', 'st', 'nd', 'rd', 'th', 'th', 'th', 'th', 'th', 'th');
        if ((($number % 100) >= 11) && (($number % 100) <= 13))
            return 'th';
        else
            return $ends[$number % 10];
    }
    
    public static function genKey()
    {
        return md5(time().rand());
    }
    
    //DONGND:: create folder image in theme if not exists
    public static function buildFolder($id_shop)
    {
        //DONGND:: copy image from module to theme
        if (!file_exists(_PS_THEME_DIR_.'assets/img/index.php')) {
            @copy(_CS24BLOG_BLOG_IMG_DIR_.'index.php', _PS_THEME_DIR_.'assets/img/index.php');
        }
        
        if (!is_dir(_PS_THEME_DIR_.'assets/img/modules')) {
            mkdir(_PS_THEME_DIR_.'assets/img/modules', 0777, true);
        }
        
        if (!file_exists(_PS_THEME_DIR_.'assets/img/modules/index.php')) {
            @copy(_CS24BLOG_BLOG_IMG_DIR_.'index.php', _PS_THEME_DIR_.'assets/img/modules/index.php');
        }
        
        if (!is_dir(_PS_THEME_DIR_.'assets/img/modules/cs24blog')) {
            mkdir(_PS_THEME_DIR_.'assets/img/modules/cs24blog', 0777, true);
        }
        
        if (!file_exists(_PS_THEME_DIR_.'assets/img/modules/cs24blog/index.php')) {
            @copy(_CS24BLOG_BLOG_IMG_DIR_.'index.php', _PS_THEME_DIR_.'assets/img/modules/cs24blog/index.php');
        }
        
        if (!is_dir(_PS_THEME_DIR_.'assets/img/modules/cs24blog/'.$id_shop)) {
            mkdir(_PS_THEME_DIR_.'assets/img/modules/cs24blog/'.$id_shop, 0777, true);
            
        }
        if (!file_exists(_PS_THEME_DIR_.'assets/img/modules/cs24blog/index.php')) {
            @copy(_CS24BLOG_BLOG_IMG_DIR_.'index.php', _PS_THEME_DIR_.'assets/img/modules/cs24blog/'.$id_shop.'/index.php');
        }
        
        if (!is_dir(_PS_THEME_DIR_.'assets/img/modules/cs24blog/'.$id_shop.'/b')) {
            mkdir(_PS_THEME_DIR_.'assets/img/modules/cs24blog/'.$id_shop.'/b', 0777, true);
            
        }
        
        if (!file_exists(_PS_THEME_DIR_.'assets/img/modules/cs24blog/'.$id_shop.'/b/index.php')) {
            @copy(_CS24BLOG_BLOG_IMG_DIR_.'index.php', _PS_THEME_DIR_.'assets/img/modules/cs24blog/'.$id_shop.'/b/index.php');
        }
        
        if (!is_dir(_PS_THEME_DIR_.'assets/img/modules/cs24blog/'.$id_shop.'/c')) {
            mkdir(_PS_THEME_DIR_.'assets/img/modules/cs24blog/'.$id_shop.'/c', 0777, true);
            
        }
        
        if (!file_exists(_PS_THEME_DIR_.'assets/img/modules/cs24blog/'.$id_shop.'/c/index.php')) {
            @copy(_CS24BLOG_BLOG_IMG_DIR_.'index.php', _PS_THEME_DIR_.'assets/img/modules/cs24blog/'.$id_shop.'/c/index.php');
        }
        
        if (!is_dir(_PS_THEME_DIR_.'assets/img/modules/cs24blog/sample')) {
            mkdir(_PS_THEME_DIR_.'assets/img/modules/cs24blog/sample', 0777, true);
            
            mkdir(_PS_THEME_DIR_.'assets/img/modules/cs24blog/sample/b', 0777, true);
            mkdir(_PS_THEME_DIR_.'assets/img/modules/cs24blog/sample/c', 0777, true);
            
            if (is_dir(_CS24BLOG_BLOG_IMG_DIR_.'b') && is_dir(_PS_THEME_DIR_.'assets/img/modules/cs24blog/sample/b')) {
                $objects_b = scandir(_CS24BLOG_BLOG_IMG_DIR_.'b');
                $objects_theme_b = scandir(_PS_THEME_DIR_.'assets/img/modules/cs24blog/sample/b');
                if (count($objects_b) > 2 && count($objects_theme_b) <= 2) {
                    foreach ($objects_b as $objects_b_val) {
                        if ($objects_b_val != '.' && $objects_b_val != '..') {
                            if (filetype(_CS24BLOG_BLOG_IMG_DIR_.'b'.'/'.$objects_b_val) == 'file') {
                                @copy(_CS24BLOG_BLOG_IMG_DIR_.'b'.'/'.$objects_b_val, _PS_THEME_DIR_.'assets/img/modules/cs24blog/sample/b/'.$objects_b_val);
                            }
                        }
                    }
                }
            }
            
            if (is_dir(_CS24BLOG_BLOG_IMG_DIR_.'c') && is_dir(_PS_THEME_DIR_.'assets/img/modules/cs24blog/sample/c')) {
                $objects_c = scandir(_CS24BLOG_BLOG_IMG_DIR_.'c');
                $objects_theme_c = scandir(_PS_THEME_DIR_.'assets/img/modules/cs24blog/sample/c');
                if (count($objects_c) > 2 && count($objects_theme_c) <= 2) {
                    foreach ($objects_c as $objects_c_val) {
                        if ($objects_c_val != '.' && $objects_c_val != '..') {
                            if (filetype(_CS24BLOG_BLOG_IMG_DIR_.'c'.'/'.$objects_c_val) == 'file') {
                                @copy(_CS24BLOG_BLOG_IMG_DIR_.'c'.'/'.$objects_c_val, _PS_THEME_DIR_.'assets/img/modules/cs24blog/sample/c/'.$objects_c_val);
                            }
                        }
                    }
                }
            }
        }
        
        if (!file_exists(_PS_THEME_DIR_.'assets/img/modules/cs24blog/sample/index.php')) {
            @copy(_CS24BLOG_BLOG_IMG_DIR_.'index.php', _PS_THEME_DIR_.'assets/img/modules/cs24blog/sample/index.php');
        }
    }
    
    static $id_shop;
    /**
     * FIX Install multi theme
     * Cs24BlogHelper::getIDShop();
     */
    public static function getIDShop()
    {
        if ((int)self::$id_shop) {
            $id_shop = (int)self::$id_shop;
        } else {
            $id_shop = (int)Context::getContext()->shop->id;
        }
        return $id_shop;
    }
    
    public static function getThemeName()
    {
        static $theme_name;
        if (!$theme_name) {
            # DEFAULT SINGLE_SHOP
            $theme_name = _THEME_NAME_;

            # GET THEME_NAME MULTI_SHOP
            if (Shop::getTotalShops(false, null) >= 2) {
                $id_shop = Context::getContext()->shop->id;

                $shop_arr = Shop::getShop($id_shop);
                if (is_array($shop_arr) && !empty($shop_arr)) {
                    $theme_name = $shop_arr['theme_name'];
                }
            }
        }
        
        return $theme_name;
    }
}
