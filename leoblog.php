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

if (!defined('_PS_VERSION_')) {
    # module validation
    exit;
}

include_once(_PS_MODULE_DIR_.'leoblog/loader.php');

class Leoblog extends Module
{
    private static $leo_xml_fields = array('title', 'guid', 'description', 'author', 'comments', 'pubDate', 'source', 'link', 'content');
    public $base_config_url;
	public $errors;
    private $_html = '';

    public function __construct()
    {
        $currentIndex = '';

        $this->name = 'leoblog';
        $this->tab = 'front_office_features';
        $this->version = '3.1.0';
        $this->author = 'LeoTheme';
        $this->controllers = array('blog', 'category', 'list');
        $this->need_instance = 0;
        $this->bootstrap = true;

        $this->secure_key = Tools::encrypt($this->name);

        parent::__construct();

        $this->base_config_url = $currentIndex.'&configure='.$this->name.'&token='.Tools::getValue('token');
        $this->displayName = $this->l('Leo Blog Management');
        $this->description = $this->l('Manage Blog Content');
    }

    /**
     * Uninstall
     */
    private function uninstallModuleTab($class_sfx = '')
    {
        $tab_class = 'Admin'.Tools::ucfirst($this->name).Tools::ucfirst($class_sfx);

        $id_tab = Tab::getIdFromClassName($tab_class);
        if ($id_tab != 0) {
            $tab = new Tab($id_tab);
            $tab->delete();
            return true;
        }
        return false;
    }

    /**
     * Install Module Tabs
     */
    private function installModuleTab($title, $class_sfx = '', $parent = '')
    {
        $class = 'Admin'.Tools::ucfirst($this->name).Tools::ucfirst($class_sfx);
        @copy(_PS_MODULE_DIR_.$this->name.'/logo.gif', _PS_IMG_DIR_.'t/'.$class.'.gif');
        if ($parent == '') {
            # validate module
            $position = Tab::getCurrentTabId();
        } else {
            # validate module
            $position = Tab::getIdFromClassName($parent);
        }

        $tab1 = new Tab();
        $tab1->class_name = $class;
        $tab1->module = $this->name;
        $tab1->id_parent = (int)$position;
        $langs = Language::getLanguages(false);
        foreach ($langs as $l) {
            # validate module
            $tab1->name[$l['id_lang']] = $title;
        }
//        $id_tab1 = $tab1->add(true, false);
        $tab1->add(true, false);
    }

    /**
     * @see Module::install()
     */
    public function install()
    {
        /* Adds Module */
        if (parent::install() && $this->createTables()) {
            $res = true;
            /* Creates tables */
//            $res &= $this->createTables();
            
            Configuration::updateValue('LEOBLOG_CATEORY_MENU', 1);
            Configuration::updateValue('LEOBLOG_COLUMN_POSITION', 'left');
            Configuration::updateValue('LEOBLOG_SHARE_FB', 1);
            Configuration::updateValue('LEOBLOG_SHARE_TW', 1);
            Configuration::updateValue('LEOBLOG_SOCIAL_LIKE', 1);

            Configuration::updateValue('LEOBLOG_IMAGE_TYPE', 'jpg');
            Configuration::updateValue('LEOBLOG_DASHBOARD_DEFAULTTAB', '#fieldset_0');
            Configuration::updateValue('AP_INSTALLED_LEOBLOG', '1');
            
            //DONGND: check thumb column, if not exist auto add
            if (Db::getInstance()->executeS('SHOW TABLES LIKE \'%leoblog_blog%\'') && count(Db::getInstance()->executes('SELECT "thumb" FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = "'._DB_NAME_.'" AND TABLE_NAME = "'._DB_PREFIX_.'leoblog_blog" AND COLUMN_NAME = "thumb"'))<1) {
                Db::getInstance()->execute('ALTER TABLE `'._DB_PREFIX_.'leoblog_blog` ADD `thumb` varchar(255) DEFAULT NULL');
            }
            
            // $this->registerHook('header'); # remove code in 2016
            $id_parent = Tab::getIdFromClassName('IMPROVE');
            
            $class = 'Admin'.Tools::ucfirst($this->name).'Management';
            $tab1 = new Tab();
            $tab1->class_name = $class;
            $tab1->module = $this->name;
            $tab1->id_parent = $id_parent;
            $langs = Language::getLanguages(false);
            foreach ($langs as $l) {
                # validate module
                $tab1->name[$l['id_lang']] = $this->l('Leo Blog Management');
            }
//            $id_tab1 = $tab1->add(true, false);
            $tab1->add(true, false);
            
            # insert icon for tab
            Db::getInstance()->execute(' UPDATE `'._DB_PREFIX_.'tab` SET `icon` = "create" WHERE `id_tab` = "'.(int)$tab1->id.'"');
            
            $this->installModuleTab('Blog Dashboard', 'dashboard', 'AdminLeoblogManagement');
            $this->installModuleTab('Categories Management', 'categories', 'AdminLeoblogManagement');
            $this->installModuleTab('Blogs Management', 'blogs', 'AdminLeoblogManagement');
            $this->installModuleTab('Comment Management', 'comments', 'AdminLeoblogManagement');
            $this->installModuleTab('Leo Blog Configuration', 'module', 'AdminLeoblogManagement');
            
            //DONGND:: move image folder from module to theme
            $this->moveImageFolder();
            
            return (bool)$res;
        }
        return false;
    }

    public function lazydatabase()
    {
        $sql = ' SELECT * FROM '._DB_PREFIX_.'leoblog_blog_lang';
        $result = Db::getInstance()->executes($sql);
        foreach ($result as $value) {
            $content = '';
            $description = '';
            if (strpos($value['content'], '<img') !== false) {
                $content = $this->addLazy($value['content']);
            }
            if (strpos($value['description'], '<img') !== false) {
                $description = $this->addLazy($value['description']);
            }
            if ($content != '' || $description != '') {
                $sql = 'UPDATE `'._DB_PREFIX_.'leoblog_blog_lang` set content = \''.$content.'\' , description = \''.$description.'\' WHERE id_leoblog_blog = \''.(int)$value['id_leoblog_blog'].'\' and id_lang=\''.(int)$value['id_lang'].'\'';
                Db::getInstance()->execute($sql);
            }
        }
    }

    public function addLazy($data)
    {
        preg_match_all('/<img[^>]+>/i', $data, $imgs);

        foreach ($imgs[0] as $img) {
            //if have lazy break
            if(version_compare(Configuration::get('PS_VERSION_DB'), '1.7.8.0', '>=')) {
                if (strpos($img, 'loading') === false) {
                    $imga = str_replace('<img', '<img loading="lazy"', $img);
                    $data = str_replace($img, $imga, $data);
                }
            } else {
                if (strpos($img, 'lazy') === false && strpos($img, 'lazyOwl') === false && strpos($img, 'data-src') === false) {
                    $imga = str_replace('src', 'data-src', $img);

                    //has class add more class
                    if (strpos($imga, "class") !== false) {
                        $imga = str_replace('class="', 'class="lazy ', $imga);
                        $imga = str_replace("class='", "class='lazy ", $imga);
                    } else {
                        $imga = str_replace('<img', '<img class="lazy"', $imga);
                    }
                    $data = str_replace($img, $imga, $data);
                }    
            }  
        }
        
        return $data;
    }


    public function lazyrolbackdatabase()
    {
        $sql = ' SELECT * FROM '._DB_PREFIX_.'leoblog_blog_lang';
        $result = Db::getInstance()->executes($sql);
        foreach ($result as $value) {
            $content = '';
            $description = '';
            if (strpos($value['content'], '<img') !== false) {
                $content = $this->removeLazy($value['content']);
            }
            if (strpos($value['description'], '<img') !== false) {
                $description = $this->removeLazy($value['description']);
            }
            if ($content != '' || $description != '') {
                $sql = 'UPDATE `'._DB_PREFIX_.'leoblog_blog_lang` set content = \''.$content.'\' , description = \''.$description.'\' WHERE id_leoblog_blog = \''.(int)$value['id_leoblog_blog'].'\' and id_lang=\''.(int)$value['id_lang'].'\'';
                
                Db::getInstance()->execute($sql);
            }
        }
    }

    public function removeLazy($data)
    {
        preg_match_all('/<img[^>]+>/i', $data, $imgs);
        foreach ($imgs[0] as $img) {
            if(version_compare(Configuration::get('PS_VERSION_DB'), '1.7.8.0', '>=')) {
                if (strpos($img, 'lazy') !== false) {
                    $imga = str_replace('loading="lazy"', '', $img);
                    $data = str_replace($img, $imga, $data);
                }
            } else {
                //if have lazy break
                if (strpos($img, 'lazy ') !== false || strpos($img, '"lazy') !== false || strpos($img, "'lazy") !== false || strpos($img, " lazy") !== false) {
                    $imga = str_replace('data-src', 'src', $img);
                    $imga = str_replace('<img class="lazy"', '<img', $imga);
                    $imga = str_replace('lazy ', '', $imga);
                    $imga = str_replace(' lazy', '', $imga);
                    $data = str_replace($img, $imga, $data);
                }
            }
        }
        
        return $data;
    }

    public function hookDisplayBackOfficeHeader()
    {
        $media_dir = $this->getMediaDir();
        if (file_exists(_PS_THEME_DIR_.'css/modules/leoblog/assets/admin/blogmenu.css')) {
            $this->context->controller->addCss($this->_path.'assets/admin/blogmenu.css');
        } else {
            $this->context->controller->addCss(__PS_BASE_URI__.$media_dir.'css/admin/blogmenu.css');
        }
        Media::addJsDef(array(
            'url_ajax_blog' => $this->context->shop->getBaseURL(true, true).'modules/leoblog/adminajax.php',
        ));
    }

    public function postProcess()
    {
        if (count($this->errors) > 0) {
            return;
        }
        
        if (Tools::isSubmit('submitBlockCategories')) {
            # VALIDATE MODULE
            Configuration::updateValue('LEOBLOG_CATEORY_MENU', (int)Tools::getValue('LEOBLOG_CATEORY_MENU'));
            Configuration::updateValue('LEOBLOG_COLUMN_POSITION', Tools::getValue('LEOBLOG_COLUMN_POSITION'));
            Configuration::updateValue('LEOBLOG_SHARE_FB', Tools::getValue('LEOBLOG_SHARE_FB'));
            Configuration::updateValue('LEOBLOG_SHARE_TW', Tools::getValue('LEOBLOG_SHARE_TW'));
            Configuration::updateValue('LEOBLOG_SOCIAL_LIKE', Tools::getValue('LEOBLOG_SOCIAL_LIKE'));

            Configuration::updateValue('LEOBLOG_IMAGE_TYPE', Tools::getValue('LEOBLOG_IMAGE_TYPE'));
        }
        
        if (Tools::getValue('correctmodule')) {
            $this->correctModule();
        }
        
        if (Tools::getValue('success')) {
            switch (Tools::getValue('success')) {
                case 'correct':
                    $this->_html .= $this->displayConfirmation($this->l('Correct Module is successful'));
                    break;
            }
        }
    }
    
    public function getContent()
    {
        $this->errors = array();
        if (!$this->access('configure')) {
            $this->errors[] = $this->trans('You do not have permission to configure this.', array(), 'Admin.Notifications.Error');
            $this->context->smarty->assign('errors', $this->errors);
            $this->_html .= $this->context->smarty->fetch($this->local_path.'views/templates/admin/errors.tpl');
        }
        
        $this->postProcess();

        $this->context->smarty->assign(array(
            'link' => Context::getContext()->link,
        ));
        $this->_html .= Context::getContext()->smarty->fetch($this->local_path.'views/templates/admin/correct_module.tpl');
        return $this->_html.$this->renderForm();
    }

    public function getTreeForApPageBuilder($selected)
    {
        $cat = new Leoblogcat();
        return $cat->getTreeForApPageBuilder($selected);
    }

    public function renderForm()
    {
        $fields_form = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Settings'),
                    'icon' => 'icon-cogs'
                ),
                'input' => array(
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Enable Categories Tree Block'),
                        'name' => 'LEOBLOG_CATEORY_MENU',
                        'desc' => $this->l('Activate  The Module.'),
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Enabled')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('Disabled')
                            )
                        ),
                    ),
                    array(
                        'type' => 'select',
                        'label' => $this->l('Image type'),
                        'name' => 'LEOBLOG_IMAGE_TYPE',
                        'options' => array(
                            'query' => array(
                                array(
                                    'id' => 'jpg',
                                    'name' => $this->l('jpg')
                                ),
                                array(
                                    'id' => 'png',
                                    'name' => $this->l('png')
                                ),
                            ),
                            'id' => 'id',
                            'name' => 'name'
                        ),
                        'desc' => $this->l('For images png type. Keep png type or optimize to jpg type'),
                    ),
                    array(
                        'type' => 'select',
                        'label' => $this->l('Show column'),
                        'name' => 'LEOBLOG_COLUMN_POSITION',
                        'desc' => $this->l('Choose a position for block search'),
                        'options' => array(
                            'query' => array(
                                array(
                                    'id' => 'left',
                                    'name' => $this->l('left')
                                ),
                                array(
                                    'id' => 'right',
                                    'name' => $this->l('right')
                                )
                            ),
                            'id' => 'id',
                            'name' => 'name'
                        ),
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Button share facebook'),
                        'name' => 'LEOBLOG_SHARE_FB',
                        'desc' => $this->l('Show button share facebook on the blogs.'),
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Enabled')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('Disabled')
                            )
                        ),
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Button share twitter'),
                        'name' => 'LEOBLOG_SHARE_TW',
                        'desc' => $this->l('Show button share twitter on the blogs.'),
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Enabled')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('Disabled')
                            )
                        ),
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Button like social'),
                        'name' => 'LEOBLOG_SOCIAL_LIKE',
                        'desc' => $this->l('Show button like social on the blogs.'),
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Enabled')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('Disabled')
                            )
                        ),
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                    'class' => 'btn btn-default')
            ),
        );

        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $lang = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
        $helper->default_form_language = $lang->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;
        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitBlockCategories';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false).'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFieldsValues(),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id
        );

        return $helper->generateForm(array($fields_form));
    }

    public function getConfigFieldsValues()
    {
        return array(
            'LEOBLOG_CATEORY_MENU' => Tools::getValue('LEOBLOG_CATEORY_MENU', Configuration::get('LEOBLOG_CATEORY_MENU')),
            'LEOBLOG_COLUMN_POSITION' => Tools::getValue('LEOBLOG_COLUMN_POSITION', Configuration::get('LEOBLOG_COLUMN_POSITION')),
            'LEOBLOG_SHARE_FB' => Tools::getValue('LEOBLOG_SHARE_FB', Configuration::get('LEOBLOG_SHARE_FB')),
            'LEOBLOG_SHARE_TW' => Tools::getValue('LEOBLOG_SHARE_TW', Configuration::get('LEOBLOG_SHARE_TW')),
            'LEOBLOG_SOCIAL_LIKE' => Tools::getValue('LEOBLOG_SOCIAL_LIKE', Configuration::get('LEOBLOG_SOCIAL_LIKE')),
            'LEOBLOG_IMAGE_TYPE' => Tools::getValue('LEOBLOG_IMAGE_TYPE', Configuration::get('LEOBLOG_IMAGE_TYPE')),
            
        );
    }

    public function _prepareHook()
    {
        $helper = LeoBlogHelper::getInstance();

        $category = new Leoblogcat(Tools::getValue('id_leoblogcat'), $this->context->language->id);

        $tree = $category->getFrontEndTree((int)$category->id_leoblogcat > 1 ? $category->id_leoblogcat : 1, $helper);
        $this->smarty->assign('tree', $tree);
        if ($category->id_leoblogcat) {
            # validate module
            $this->smarty->assign('currentCategory', $category);
        }

        return true;
    }

    public function hookDisplayHeader()
    {
        $config = LeoBlogConfig::getInstance();
        $template = $config->get('template');
        $media_dir = $this->getMediaDir();
        if (Tools::getValue('bloglayout') != null) {
            if (is_dir(_PS_THEME_DIR_.'modules/leoblog/views/templates/front/'.Tools::getValue('bloglayout'))
                || is_dir(_PS_MODULE_DIR_ .'leoblog/views/templates/front/'.Tools::getValue('bloglayout'))) {
                $template = Tools::getValue('bloglayout');
            }
        }
        if (file_exists(_PS_THEME_DIR_.$media_dir.'css/'.$template.'.css') || file_exists(_PS_THEME_DIR_.'assets/css/'.$media_dir.'css/'.$template.'.css')) {
            Context::getContext()->controller->addCSS(__PS_BASE_URI__.$media_dir.'css/'.$template.'.css', 'all');
        } else {
            if (file_exists(_PS_MODULE_DIR_ .'leoblog/views/css/'.$template.'.css')) {
                Context::getContext()->controller->addCSS(_PS_MODULE_DIR_ .'leoblog/views/css/'.$template.'.css');
            } else {
                if (file_exists(_PS_THEME_DIR_.'css/modules/leoblog/assets/leoblog.css')) {
                    Context::getContext()->controller->addCSS(__PS_BASE_URI__.$media_dir.'assets/leoblog.css', 'all');
                } else {
                    Context::getContext()->controller->addCSS(__PS_BASE_URI__.$media_dir.'css/leoblog.css', 'all');
                }
            }
        }
                    
        //DONGND:: update language link
        if (Tools::getValue('module') == 'leoblog') {
            $langs = Language::getLanguages(false);
            if (count($langs) > 1) {
                $config = LeoBlogConfig::getInstance();
                $array_list_rewrite = array();
                $array_category_rewrite = array();
                $array_config_category_rewrite = array();
                $array_blog_rewrite = array();
                $array_config_blog_rewrite = array();
                $config_url_use_id = $config->get('url_use_id');
                
                $page_name = Dispatcher::getInstance()->getController();
                
                if ($page_name == 'blog') {
                    if ($config_url_use_id == 1) {
                        $id_blog = Tools::getValue('id');
                    } else {
                        $id_shop = (int)Context::getContext()->shop->id;
                        $block_rewrite = Tools::getValue('rewrite');
                        $sql = 'SELECT bl.id_leoblog_blog FROM '._DB_PREFIX_.'leoblog_blog_lang bl';
                        $sql .= ' INNER JOIN '._DB_PREFIX_.'leoblog_blog_shop bs on bl.id_leoblog_blog=bs.id_leoblog_blog AND id_shop='.(int)$id_shop;
                        $sql .= " AND link_rewrite = '".pSQL($block_rewrite)."'";
                        if ($row = Db::getInstance()->getRow($sql)) {
                            $id_blog = $row['id_leoblog_blog'];
                        }
                    }
                    $blog_obj = new Leoblogblog($id_blog);
                }
                
                if ($page_name == 'category') {
                    if ($config_url_use_id == 1) {
                        $id_category = Tools::getValue('id');
                    } else {
                        $id_shop = (int)Context::getContext()->shop->id;
                        $category_rewrite = Tools::getValue('rewrite');
                        $sql = 'SELECT cl.id_leoblogcat FROM '._DB_PREFIX_.'leoblogcat_lang cl';
                        $sql .= ' INNER JOIN '._DB_PREFIX_.'leoblogcat_shop cs on cl.id_leoblogcat=cs.id_leoblogcat AND id_shop='.(int)$id_shop;
                        $sql .= ' INNER JOIN '._DB_PREFIX_.'leoblogcat cc on cl.id_leoblogcat=cc.id_leoblogcat AND cl.id_leoblogcat != cc.id_parent';  # FIX : PARENT IS NOT THIS CATEGORY
                        $sql .= " AND link_rewrite = '".pSQL($category_rewrite)."'";

                        if ($row = Db::getInstance()->getRow($sql)) {
                            $id_category = $row['id_leoblogcat'];
                        }
                    }
                    $blog_category_obj = new Leoblogcat($id_category);
                }
                
                foreach ($langs as $lang) {
                    $array_list_rewrite[$lang['iso_code']] = $config->get('link_rewrite_'.$lang['id_lang'], 'blog');
                    
                    if (isset($id_blog)) {
                        $array_blog_rewrite[$lang['iso_code']] = $blog_obj->link_rewrite[$lang['id_lang']];
                        if ($config_url_use_id == 0) {
                            $array_config_blog_rewrite[$lang['iso_code']] = $config->get('detail_rewrite_'.$lang['id_lang'], 'detail');
                        }
                    }
                    
                    if (isset($id_category)) {
                        $array_category_rewrite[$lang['iso_code']] = $blog_category_obj->link_rewrite[$lang['id_lang']];
                        if ($config_url_use_id == 0) {
                            $array_config_category_rewrite[$lang['iso_code']] = $config->get('category_rewrite_'.$lang['id_lang'], 'category');
                        }
                    }
                };
                
                Media::addJsDef(array(
                    'array_list_rewrite' => $array_list_rewrite,
                    'array_category_rewrite' => $array_category_rewrite,
                    'array_blog_rewrite' => $array_blog_rewrite,
                    'array_config_category_rewrite' => $array_config_category_rewrite,
                    'array_config_blog_rewrite' => $array_config_blog_rewrite,
                    'config_url_use_id' => $config_url_use_id
                ));
            }
        }
    }

    public function hookleoLeftBlog()
    {
        $html = '';
        $fc = Tools::getValue('fc');
        $module = Tools::getValue('module');

        if ($fc == 'module' && $module =='leoblog') {
            if (Configuration::get('LEOBLOG_COLUMN_POSITION') == 'left') {
                $html .= $this->searchBlog();
            }
            $html .= $this->leftCategoryBlog();
            $html .= $this->leftPopularBlog();
            $html .= $this->leftRecentBlog();
            $html .= $this->lefTagBlog();
        }
        
        return $html;
    }

    public function hookLeftColumn()
    {
        $html = '';
        $fc = Tools::getValue('fc');
        $module = Tools::getValue('module');
        
        if ($fc == 'module' && $module =='leoblog') {
            if (Configuration::get('LEOBLOG_COLUMN_POSITION') == 'left') {
                $html .= $this->searchBlog();
            }
            $html .= $this->leftCategoryBlog();
            $html .= $this->leftPopularBlog();
            $html .= $this->leftRecentBlog();
            $html .= $this->lefTagBlog();
        }
        
        return $html;
    }

    public function searchBlog()
    {
        $html = '';
        
        if (Configuration::get('LEOBLOG_COLUMN_POSITION') && $this->_prepareHook()) {
            $html .= $this->display(__FILE__, 'views/templates/hook/search_blog.tpl');
        }
        
        return $html;
    }

    public function leftCategoryBlog()
    {
        $html = '';
        
        if (Configuration::get('LEOBLOG_CATEORY_MENU') && $this->_prepareHook()) {
            $html .= $this->display(__FILE__, 'views/templates/hook/categories_menu.tpl');
        }
        
        return $html;
    }
    
    public function leftPopularBlog()
    {
        $html = '';
        
        $config = LeoBlogConfig::getInstance();
        if ($config->get('show_popular_blog', 0)) {
            $limit = (int)$config->get('limit_popular_blog', 5);
            $helper = LeoBlogHelper::getInstance();
            $image_w = (int)$config->get('listing_leading_img_width', 690);
            $image_h = (int)$config->get('listing_leading_img_height', 300);
            $authors = array();

            $leading_blogs = array();
            if ($limit > 0) {
                $leading_blogs = LeoBlogBlog::getListBlogs(null, $this->context->language->id, 1, $limit, 'hits', 'DESC', array(), true);
            }
            foreach ($leading_blogs as $key => $blog) {
                $blog = LeoBlogHelper::buildBlog($helper, $blog, $image_w, $image_h, $config);
                if ($blog['id_employee']) {
                    if (!isset($authors[$blog['id_employee']])) {
                        # validate module
                        $authors[$blog['id_employee']] = new Employee($blog['id_employee']);
                    }

                    if ($blog['author_name'] != '') {
                        $blog['author'] = $blog['author_name'];
                        $blog['author_link'] = $helper->getBlogAuthorLink($blog['author_name']);
                    } else {
                        $blog['author'] = $authors[$blog['id_employee']]->firstname.' '.$authors[$blog['id_employee']]->lastname;
                        $blog['author_link'] = $helper->getBlogAuthorLink($authors[$blog['id_employee']]->id);
                    }
                } else {
                    $blog['author'] = '';
                    $blog['author_link'] = '';
                }

                $leading_blogs[$key] = $blog;
            }

            $this->smarty->assign('leading_blogs', $leading_blogs);
            $html .= $this->display(__FILE__, 'views/templates/hook/left_popular.tpl');
        }
        
        return $html;
    }
    
    public function leftRecentBlog()
    {
        $html = '';
        
        $config = LeoBlogConfig::getInstance();
        if ($config->get('show_recent_blog', 0)) {
            $limit = (int)$config->get('limit_recent_blog', 5);
            $config = LeoBlogConfig::getInstance();
            $helper = LeoBlogHelper::getInstance();
            $image_w = (int)$config->get('listing_leading_img_width', 690);
            $image_h = (int)$config->get('listing_leading_img_height', 300);
            $authors = array();

            $leading_blogs = array();
            if ($limit > 0) {
                $leading_blogs = LeoBlogBlog::getListBlogs(null, $this->context->language->id, 1, $limit, 'date_add', 'DESC', array(), true);
            }
            foreach ($leading_blogs as $key => $blog) {
                $blog = LeoBlogHelper::buildBlog($helper, $blog, $image_w, $image_h, $config);
                if ($blog['id_employee']) {
                    if (!isset($authors[$blog['id_employee']])) {
                        # validate module
                        $authors[$blog['id_employee']] = new Employee($blog['id_employee']);
                    }

                    if ($blog['author_name'] != '') {
                            $blog['author'] = $blog['author_name'];
                            $blog['author_link'] = $helper->getBlogAuthorLink($blog['author_name']);
                    } else {
                            $blog['author'] = $authors[$blog['id_employee']]->firstname.' '.$authors[$blog['id_employee']]->lastname;
                            $blog['author_link'] = $helper->getBlogAuthorLink($authors[$blog['id_employee']]->id);
                    }
                } else {
                    $blog['author'] = '';
                    $blog['author_link'] = '';
                }

                $leading_blogs[$key] = $blog;
            }

            $this->smarty->assign('leading_blogs', $leading_blogs);
            $html .= $this->display(__FILE__, 'views/templates/hook/left_recent.tpl');
        }
        
        return $html;
    }

    public function lefTagBlog()
    {
        $html = '';
        $helper = LeoBlogHelper::getInstance();
        
        $config = LeoBlogConfig::getInstance();
        if ($config->get('show_all_tags', 0)) {
            $leading_blogs = LeoBlogBlog::getListBlogs(null, $this->context->language->id, 1, 100000, 'date_add', 'DESC', array(), true);

            $tags_temp = array();
            foreach ($leading_blogs as $key => $value) {
                $tags_temp = array_merge($tags_temp, explode(",", $value['tags']));
            }
            // validate module
            unset($key);

            $tags_temp = array_unique($tags_temp);
            $tags = array();
            foreach ($tags_temp as $tag_temp) {
                $tags[] = array(
                    'name' => $tag_temp,
                    'link' => $helper->getBlogTagLink($tag_temp)
                );
            }
            
            $this->smarty->assign('leoblogtags', $tags);
            $html .= $this->display(__FILE__, 'views/templates/hook/left_leoblogtags.tpl');
        }
        
        return $html;
    }
    
    /*
      function hookRSS($params)
      {
      if (!$this->isCached('leoblogrss.tpl', $cacheId)) {
      // Getting data
      $config = LeoBlogConfig::getInstance();
      $title = strval($config->get('rss_title_item', 'RSS FEED'));
      $url = Tools::htmlentitiesutf8('http://'.$_SERVER['HTTP_HOST'].__PS_BASE_URI__).'modules/leoblog/rss.php';
      $nb = (int)$config->get('rss_limit_item', 1);
      $cacheId = $this->getCacheId($this->name.'-'.date("YmdH"));
      $rss_links = array();
      if ($url && ($contents = Tools::file_get_contents($url)))
      try
      {
      if (@$src = new XML_Feed_Parser($contents))
      for ($i = 0; $i < ($nb ? $nb : 5); $i++)
      if (@$item = $src->getEntryByOffset($i)) {
      $xmlValues = array();
      foreach (self::$leo_xml_fields as $xmlField)
      $xmlValues[$xmlField] = $item->__get($xmlField);
      $xmlValues['enclosure'] = $item->getEnclosure();
      # Compatibility
      $xmlValues['url'] = $xmlValues['link'];
      $rss_links[] = $xmlValues;
      }
      }
      catch (XML_Feed_Parser_Exception $e) {
      Tools::dieOrLog(sprintf($this->l('Error: invalid RSS feed in "leoblogrss" module: %s'), $e->getMessage()), false);
      }

      // Display smarty
      $this->smarty->assign(array('title' => ($title ? $title : $this->l('RSS feed')), 'rss_links' => $rss_links));
      }

      return $this->display(__FILE__, 'views/templates/hook/leoblogrss.tpl', $cacheId);
      }
     */

    protected function getCacheId($name = null)
    {
        $name = ($name ? $name.'|' : '').implode('-', Customer::getGroupsStatic($this->context->customer->id));
        return parent::getCacheId($name);
    }

    public function hookRightcolumn($params)
    {
        $html = '';
        $fc = Tools::getValue('fc');
        $module = Tools::getValue('module');
        
        if ($fc == 'module' && $module =='leoblog') {
            if (Configuration::get('LEOBLOG_COLUMN_POSITION') == 'right') {
                $html .= $this->searchBlog();
            }
            $html .= $this->leftCategoryBlog();
            $html .= $this->leftPopularBlog();
            $html .= $this->leftRecentBlog();
            $html .= $this->lefTagBlog();
        }
        
        return $html;
    }

    /**
     * @see Module::uninstall()
     */
    public function uninstall()
    {
        if (parent::uninstall()) {
            $res = true;
            $this->unregisterLeoHook();
            $this->uninstallModuleTab('management');
            $this->uninstallModuleTab('dashboard');
            $this->uninstallModuleTab('categories');
            $this->uninstallModuleTab('blogs');
            $this->uninstallModuleTab('comments');
            $this->uninstallModuleTab('module');
            
            $res &= $this->deleteTables();
            $this->deleteConfiguration();

            return (bool)$res;
        }
        return false;
    }

    public function deleteTables()
    {
        return Db::getInstance()->execute('
            DROP TABLE IF EXISTS
            `'._DB_PREFIX_.'leoblogcat`,
            `'._DB_PREFIX_.'leoblogcat_lang`,
            `'._DB_PREFIX_.'leoblogcat_shop`,
            `'._DB_PREFIX_.'leoblog_comment`,
            `'._DB_PREFIX_.'leoblog_blog`,
            `'._DB_PREFIX_.'leoblog_blog_lang`,
            `'._DB_PREFIX_.'leoblog_blog_shop`');
    }

    public function deleteConfiguration()
    {
        Configuration::deleteByName('LEOBLOG_CATEORY_MENU');
        Configuration::deleteByName('LEOBLOG_COLUMN_POSITION');
        Configuration::deleteByName('LEOBLOG_SHARE_FB');
        Configuration::deleteByName('LEOBLOG_SHARE_TW');
        Configuration::deleteByName('LEOBLOG_SOCIAL_LIKE');
        Configuration::deleteByName('LEOBLOG_IMAGE_TYPE');
        
        Configuration::deleteByName('LEOBLOG_DASHBOARD_DEFAULTTAB');
        Configuration::deleteByName('LEOBLOG_CFG_GLOBAL');
        return true;
    }

    /**
     * Creates tables
     */
    protected function createTables()
    {
        if ($this->_installDataSample()) {
            return true;
        } else {
            $res = true;
    //        $res &= $this->createConfiguration();
            $res &= $this->registerLeoHook();
            include_once(dirname(__FILE__).'/install/install.php');
            return $res;
        }
    }

    private function _installDataSample()
    {
        if (file_exists(_PS_MODULE_DIR_.'leoelements/libs/LeoDataSample.php')) {
            require_once(_PS_MODULE_DIR_.'leoelements/libs/LeoDataSample.php');
        }elseif (file_exists(_PS_MODULE_DIR_.'appagebuilder/libs/LeoDataSample.php')) {
            require_once(_PS_MODULE_DIR_.'appagebuilder/libs/LeoDataSample.php');
        }else{
            return false;
        }

        $sample = new Datasample(1);
        return $sample->processImport($this->name);
    }

    protected function installSample()
    {
        $res = 1;
        include_once(dirname(__FILE__).'/install/sample.php');
        return $res;
    }

//    public function hookDisplayNav($params)
//    {
//        return $this->hookDisplayTop($params);
//    }

    /**
     * Show correct re_write url on BlockLanguage module
     * http://ps_1609_test/vn/index.php?controller=blog?id=9&fc=module&module=leoblog
     *     $default_rewrite = array(
      '1' => 'http://ps_1609_test/en/blog/lang-en-b9.html',
      '2' => 'http://ps_1609_test/vn/blog/lang-vn-b9.html',
      '3' => 'http://ps_1609_test/cb/blog/lang-cb-b9.html',
      );
     *
     */
    public function hookDisplayBanner()
    {
        if (Module::isEnabled('blocklanguages')) {
            $default_rewrite = array();
            $module = Validate::isModuleName(Tools::getValue('module')) ? Tools::getValue('module') : '';
            $controller = Tools::getValue('controller');
            if ($module == 'leoblog' && $controller == 'blog' && ($id_blog = (int)Tools::getValue('id'))) {
                $languages = Language::getLanguages(true, $this->context->shop->id);
                if (!count($languages)) {
                    return false;
                }
                $link = new Link();

                foreach ($languages as $lang) {
                    $config = LeoBlogConfig::getInstance();
                    $config->cur_id_lang = $lang['id_lang'];

                    $cur_key = 'link_rewrite'.'_'.Context::getContext()->language->id;
                    $cur_prefix = '/'.$config->cur_prefix_rewrite = $config->get($cur_key, 'blog').'/';

                    $other_key = 'link_rewrite'.'_'.$lang['id_lang'];
                    $other_prefix = '/'.$config->cur_prefix_rewrite = $config->get($other_key, 'blog').'/';

                    $blog = new LeoBlogBlog($id_blog, $lang['id_lang']);
                    $temp_link = $link->getModuleLink($module, $controller, array('id' => $id_blog, 'rewrite' => $blog->link_rewrite), null, $lang['id_lang']);
                    $default_rewrite[$lang['id_lang']] = str_replace($cur_prefix, $other_prefix, $temp_link);
//                    $default_rewrite[$lang['id_lang']] = $link->getModuleLink($module, $controller, array('id'=>$id_blog, 'rewrite'=>$blog->link_rewrite), null, $lang['id_lang']);
                }
            } elseif ($module == 'leoblog' && $controller == 'category' && ($id_blog = (int)Tools::getValue('id'))) {
                $languages = Language::getLanguages(true, $this->context->shop->id);
                if (!count($languages)) {
                    return false;
                }
                $link = new Link();

                foreach ($languages as $lang) {
                    $config = LeoBlogConfig::getInstance();
                    $config->cur_id_lang = $lang['id_lang'];

                    $cur_key = 'link_rewrite'.'_'.Context::getContext()->language->id;
                    $cur_prefix = '/'.$config->cur_prefix_rewrite = $config->get($cur_key, 'blog').'/';

                    $other_key = 'link_rewrite'.'_'.$lang['id_lang'];
                    $other_prefix = '/'.$config->cur_prefix_rewrite = $config->get($other_key, 'blog').'/';

                    $blog = new Leoblogcat($id_blog, $lang['id_lang']);
                    $temp_link = $link->getModuleLink($module, $controller, array('id' => $id_blog, 'rewrite' => $blog->link_rewrite), null, $lang['id_lang']);
                    $default_rewrite[$lang['id_lang']] = str_replace($cur_prefix, $other_prefix, $temp_link);
//                    $default_rewrite[$lang['id_lang']] = $link->getModuleLink($module, $controller, array('id'=>$id_blog, 'rewrite'=>$blog->link_rewrite), null, $lang['id_lang']);
                }
            } elseif ($module == 'leoblog' && $controller == 'list') {
                $languages = Language::getLanguages(true, $this->context->shop->id);
                if (!count($languages)) {
                    return false;
                }
                $link = new Link();

                foreach ($languages as $lang) {
                    $config = LeoBlogConfig::getInstance();
                    $config->cur_id_lang = $lang['id_lang'];

                    $cur_key = 'link_rewrite'.'_'.Context::getContext()->language->id;
                    $cur_prefix = '/'.$config->cur_prefix_rewrite = $config->get($cur_key, 'blog').'';

                    $other_key = 'link_rewrite'.'_'.$lang['id_lang'];
                    $other_prefix = '/'.$config->cur_prefix_rewrite = $config->get($other_key, 'blog').'';

                    $temp_link = $link->getModuleLink($module, $controller, array(), null, $lang['id_lang']);
                    $default_rewrite[$lang['id_lang']] = str_replace($cur_prefix, $other_prefix, $temp_link);
                }
            }

            $this->context->smarty->assign('lang_leo_rewrite_urls', $default_rewrite);
        }
    }

    /**
     * Hook Display Top
     */
    public function hookDisplayTop($params)
    {
        # validate module
        unset($params);
        $this->smarty->assign(array(
            'hook_name' => 'hookDisplayTop',
            'title' => LeoBlogConfig::getInstance()->get('blog_link_title_'.Context::getContext()->language->id, 'Blog'),
            'link' => LeoBlogHelper::getInstance()->getFontBlogLink(),
        ));
        return $this->display(__FILE__, 'link_list.tpl');
    }

    /**
     * Hook ModuleRoutes
     */
    public function hookModuleRoutes($route = '', $detail = array())
    {
        $config = LeoBlogConfig::getInstance();
        Configuration::deleteByName('PS_ROUTE_module-leoblog-list');
        Configuration::deleteByName('PS_ROUTE_module-leoblog-blog');
        Configuration::deleteByName('PS_ROUTE_module-leoblog-category');
        
        $routes = array();

        $routes['module-leoblog-list'] = array(
            'controller' => 'list',
            'rule' => _LEO_BLOG_REWRITE_ROUTE_.'.html',
            'keywords' => array(
            ),
            'params' => array(
                'fc' => 'module',
                'module' => 'leoblog'
            )
        );
        if (Tools::getIsset('configure') && Tools::getValue('configure') == 'gsitemap') {
            return $routes;
        }
        if ($config->get('url_use_id', 1)) {
            // URL HAVE ID
            $routes['module-leoblog-blog'] = array(
                'controller' => 'blog',
                'rule' => _LEO_BLOG_REWRITE_ROUTE_.'/{rewrite}-b{id}.html',
                'keywords' => array(
                    'id' => array('regexp' => '[0-9]+', 'param' => 'id'),
                    'rewrite' => array('regexp' => '[_a-zA-Z0-9-\pL]*', 'param' => 'rewrite'),
                ),
                'params' => array(
                    'fc' => 'module',
                    'module' => 'leoblog',
                    
                )
            );

            $routes['module-leoblog-category'] = array(
                'controller' => 'category',
                'rule' => _LEO_BLOG_REWRITE_ROUTE_.'/{rewrite}-c{id}.html',
                'keywords' => array(
                    'id' => array('regexp' => '[0-9]+', 'param' => 'id'),
                    'rewrite' => array('regexp' => '[_a-zA-Z0-9-\pL]*', 'param' => 'rewrite'),
                ),
                'params' => array(
                    'fc' => 'module',
                    'module' => 'leoblog',
                            
                )
            );
        } else {
            // REMOVE ID FROM URL
            $category_rewrite = 'category_rewrite'.'_'.Context::getContext()->language->id;
            $category_rewrite = $config->get($category_rewrite, 'category');
            $detail_rewrite = 'detail_rewrite'.'_'.Context::getContext()->language->id;
            $detail_rewrite = $config->get($detail_rewrite, 'detail');

            $routes['module-leoblog-blog'] = array(
                'controller' => 'blog',
                'rule' => _LEO_BLOG_REWRITE_ROUTE_.'/'.$detail_rewrite.'/{rewrite}.html',
                'keywords' => array(
                    'id' => array('regexp' => '[0-9]+', 'param' => 'id'),
                    'rewrite' => array('regexp' => '[_a-zA-Z0-9-\pL]*', 'param' => 'rewrite'),
                ),
                'params' => array(
                    'fc' => 'module',
                    'module' => 'leoblog',
                )
            );

            $routes['module-leoblog-category'] = array(
                'controller' => 'category',
                'rule' => _LEO_BLOG_REWRITE_ROUTE_.'/'.$category_rewrite.'/{rewrite}.html',
                'keywords' => array(
                    'id' => array('regexp' => '[0-9]+', 'param' => 'id'),
                    'rewrite' => array('regexp' => '[_a-zA-Z0-9-\pL]*', 'param' => 'rewrite'),
                ),
                'params' => array(
                    'fc' => 'module',
                    'module' => 'leoblog',
                )
            );
        }
        return $routes;
    }

    /**
     * Get lastest blog for ApPageBuilder module
     * @param type $params
     * @return type
     */
    public function getBlogsFont($params)
    {
        $config = LeoBlogConfig::getInstance();
        $id_categories = '';
        if (isset($params['chk_cat'])) {
            # validate module
            $id_categories = $params['chk_cat'];
        }
        $order_by = isset($params['order_by']) ? $params['order_by'] : 'id_leoblog_blog';
        $order_way = isset($params['order_way']) ? $params['order_way'] : 'DESC';
        $helper = LeoBlogHelper::getInstance();
        $limit = (int)$params['nb_blogs'];
        $blogs = LeoBlogBlog::getListBlogsForApPageBuilder($id_categories, $this->context->language->id, $limit, $order_by, $order_way, array(), true);
        // $authors = array(); #validate module
        $image_w = (int)$config->get('listing_leading_img_width', 690);
        $image_h = (int)$config->get('listing_leading_img_height', 300);
        foreach ($blogs as $key => &$blog) {
            $blog = LeoBlogHelper::buildBlog($helper, $blog, $image_w, $image_h, $config, true);
            
            if ((bool)Module::isEnabled('appagebuilder')) {
                $appagebuilder = Module::getInstanceByName('appagebuilder');
                $blog['description'] = $appagebuilder->buildShortCode($blog['description']);
                $blog['content'] = $appagebuilder->buildShortCode($blog['content']);
            }
            
            if ($blog['author_name']) {
                # HAVE AUTHOR IN BO
                $blog['author'] = $blog['author_name'];
                $blog['author_link'] = $helper->getBlogAuthorLink($blog['author_name']);
            } elseif ($blog['id_employee']) {
                # AUTO GENERATE AUTHOR
                $employee = new Employee($blog['id_employee']);
                $blog['author'] = $employee->firstname.' '.$employee->lastname;
                $blog['author_link'] = $helper->getBlogAuthorLink($employee->id);
            } else {
                $blog['author'] = '';
                $blog['author_link'] = '';
            }
            
            # validate module
            unset($key);
        }
        return $blogs;
    }
    
    /**
     * Run only one when install/change Theme_of_Leo
     */
    public function hookActionAdminBefore($params)
    {
        if (isset($params) && isset($params['controller']) && isset($params['controller']->theme_manager)) {
            // Validate : call hook from theme_manager
        } else {
            // Other module call this hook -> duplicate data
            return;
        }
        
        $this->unregisterHook('actionAdminBefore');
        
        # FIX : update Prestashop by 1-Click module -> NOT NEED RESTORE DATABASE
        $ap_version = Configuration::get('AP_CURRENT_VERSION');
        if ($ap_version != false) {
            $ps_version = Configuration::get('PS_VERSION_DB');
            $versionCompare =  version_compare($ap_version, $ps_version);
            if ($versionCompare != 0) {
                // Just update Prestashop
                Configuration::updateValue('AP_CURRENT_VERSION', $ps_version);
                return;
            }
        }
        
        # WHENE INSTALL THEME, INSERT HOOK FROM DATASAMPLE IN THEME
        $primary_module = 'appagebuilder';
        if (file_exists(_PS_MODULE_DIR_.'leoelements/libs/LeoDataSample.php')) {
            $primary_module = 'leoelements';
        }
        $hook_from_theme = false;
        if (file_exists(_PS_MODULE_DIR_.$primary_module.'/libs/LeoDataSample.php')) {
            require_once(_PS_MODULE_DIR_.$primary_module.'/libs/LeoDataSample.php');
            $sample = new Datasample();
            if ($sample->processHook($this->name)) {
                $hook_from_theme = true;
            };
        }
        
        # INSERT HOOK FROM MODULE_DATASAMPLE
        if ($hook_from_theme == false) {
            $this->registerLeoHook();
        }
        
        # WHEN INSTALL MODULE, NOT NEED RESTORE DATABASE IN THEME
        $install_module = (int)Configuration::get('AP_INSTALLED_LEOBLOG', 0);
        if ($install_module) {
            Configuration::updateValue('AP_INSTALLED_LEOBLOG', '0');    // next : allow restore sample
            return;
        }
        
        # INSERT DATABASE FROM THEME_DATASAMPLE
        if (file_exists(_PS_MODULE_DIR_.$primary_module.'/libs/LeoDataSample.php')) {
            require_once(_PS_MODULE_DIR_.$primary_module.'/libs/LeoDataSample.php');
            $sample = new Datasample();
            $sample->processImport($this->name);
        }
    }
    
    /**
     * Common method
     * Resgister all hook for module
     */
    public function registerLeoHook()
    {
        $res = true;
        $res &= $this->registerHook('header');
        $res &= $this->registerHook('displayTop');
        $res &= $this->registerHook('leftColumn');
        $res &= $this->registerHook('leoLeftBlog');
        $res &= $this->registerHook('rightColumn');
        $res &= $this->registerHook('moduleRoutes');
        $res &= $this->registerHook('displayBackOfficeHeader');
        # Multishop create new shop
        $res &= $this->registerHook('actionAdminShopControllerSaveAfter');
        $res &= $this->registerHook('actionAdminControllerSetMedia');
        return $res;
    }

    /**
     * Common method
     * Unresgister all hook for module
     */
    public function unregisterLeoHook()
    {
        $res = true;
        $res &= $this->unregisterHook('header');
        $res &= $this->unregisterHook('displayTop');
        $res &= $this->unregisterHook('leftColumn');
        $res &= $this->unregisterHook('leoLeftBlog');
        $res &= $this->unregisterHook('rightColumn');
        $res &= $this->unregisterHook('moduleRoutes');
        $res &= $this->unregisterHook('displayBackOfficeHeader');
        # Multishop create new shop
        $res &= $this->unregisterHook('actionAdminShopControllerSaveAfter');
        $res &= $this->unregisterHook('actionAdminControllerSetMedia');
        return $res;
    }
    
    public function correctModule()
    {
        //DONGND:: check thumb column, if not exist auto add
        if (Db::getInstance()->executeS('SHOW TABLES LIKE \'%leoblog_blog%\'') && count(Db::getInstance()->executes('SELECT "thumb" FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = "'._DB_NAME_.'" AND TABLE_NAME = "'._DB_PREFIX_.'leoblog_blog" AND COLUMN_NAME = "thumb"'))<1) {
            Db::getInstance()->execute('ALTER TABLE `'._DB_PREFIX_.'leoblog_blog` ADD `thumb` varchar(255) DEFAULT NULL');
        }
        
        //DONGND:: check author name column, if not exist auto add
        // Db::getInstance()->execute('ALTER TABLE `'._DB_PREFIX_.'leoblog_blog` ADD `author_name` varchar(255) DEFAULT NULL');
        if (Db::getInstance()->executeS('SHOW TABLES LIKE \'%leoblog_blog%\'') && count(Db::getInstance()->executes('SELECT "author_name" FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = "'._DB_NAME_.'" AND TABLE_NAME = "'._DB_PREFIX_.'leoblog_blog" AND COLUMN_NAME = "author_name"'))<1) {
            Db::getInstance()->execute('ALTER TABLE `'._DB_PREFIX_.'leoblog_blog` ADD `author_name` varchar(255) DEFAULT NULL');
        }
        // check `favorite` name column, if not exist auto add
        if (Db::getInstance()->executeS('SHOW TABLES LIKE \'%leoblog_blog%\'') && count(Db::getInstance()->executes('SELECT "favorite" FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = "'._DB_NAME_.'" AND TABLE_NAME = "'._DB_PREFIX_.'leoblog_blog" AND COLUMN_NAME = "favorite"'))<1) {
            Db::getInstance()->execute('ALTER TABLE `'._DB_PREFIX_.'leoblog_blog` ADD `favorite` tinyint(1) NOT NULL');
        }
        // check `subtitle` name column, if not exist auto add
        if (Db::getInstance()->executeS('SHOW TABLES LIKE \'%leoblog_blog_lang%\'') && count(Db::getInstance()->executes('SELECT "subtitle" FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = "'._DB_NAME_.'" AND TABLE_NAME = "'._DB_PREFIX_.'leoblog_blog_lang" AND COLUMN_NAME = "subtitle"'))<1) {
            Db::getInstance()->execute('ALTER TABLE `'._DB_PREFIX_.'leoblog_blog_lang` ADD `subtitle` varchar(250) NULL');
        }
        if (!is_dir(_PS_THEME_DIR_.'assets/img/modules/leoblog')) {
            $this->moveImageFolder();
        }
    }
    
    //DONGND:: move image folder from module to theme
    public function moveImageFolder()
    {
        //DONGND:: copy image from module to theme
        if (!file_exists(_PS_THEME_DIR_.'assets/img/index.php')) {
            @copy(_LEOBLOG_BLOG_IMG_DIR_.'index.php', _PS_THEME_DIR_.'assets/img/index.php');
        }
        
        if (!is_dir(_PS_THEME_DIR_.'assets/img/modules')) {
            mkdir(_PS_THEME_DIR_.'assets/img/modules', 0777, true);
        }
        
        if (!file_exists(_PS_THEME_DIR_.'assets/img/modules/index.php')) {
            @copy(_LEOBLOG_BLOG_IMG_DIR_.'index.php', _PS_THEME_DIR_.'assets/img/modules/index.php');
        }
        
        if (!is_dir(_PS_THEME_DIR_.'assets/img/modules/leoblog')) {
            mkdir(_PS_THEME_DIR_.'assets/img/modules/leoblog', 0777, true);
        }
        
        if (!file_exists(_PS_THEME_DIR_.'assets/img/modules/leoblog/index.php')) {
            @copy(_LEOBLOG_BLOG_IMG_DIR_.'index.php', _PS_THEME_DIR_.'assets/img/modules/leoblog/index.php');
        }
        
        if (!is_dir(_PS_THEME_DIR_.'assets/img/modules/leoblog/sample')) {
            mkdir(_PS_THEME_DIR_.'assets/img/modules/leoblog/sample', 0777, true);
            
            mkdir(_PS_THEME_DIR_.'assets/img/modules/leoblog/sample/b', 0777, true);
            mkdir(_PS_THEME_DIR_.'assets/img/modules/leoblog/sample/c', 0777, true);
            
            if (is_dir(_LEOBLOG_BLOG_IMG_DIR_.'b') && is_dir(_PS_THEME_DIR_.'assets/img/modules/leoblog/sample/b')) {
                $objects_b = scandir(_LEOBLOG_BLOG_IMG_DIR_.'b');
                $objects_theme_b = scandir(_PS_THEME_DIR_.'assets/img/modules/leoblog/sample/b');
                if (count($objects_b) > 2 && count($objects_theme_b) <= 2) {
                    foreach ($objects_b as $objects_b_val) {
                        if ($objects_b_val != '.' && $objects_b_val != '..') {
                            if (filetype(_LEOBLOG_BLOG_IMG_DIR_.'b'.'/'.$objects_b_val) == 'file') {
                                @copy(_LEOBLOG_BLOG_IMG_DIR_.'b'.'/'.$objects_b_val, _PS_THEME_DIR_.'assets/img/modules/leoblog/sample/b/'.$objects_b_val);
                            }
                        }
                    }
                }
            }
            
            if (is_dir(_LEOBLOG_BLOG_IMG_DIR_.'c') && is_dir(_PS_THEME_DIR_.'assets/img/modules/leoblog/sample/c')) {
                $objects_c = scandir(_LEOBLOG_BLOG_IMG_DIR_.'c');
                $objects_theme_c = scandir(_PS_THEME_DIR_.'assets/img/modules/leoblog/sample/c');
                if (count($objects_c) > 2 && count($objects_theme_c) <= 2) {
                    foreach ($objects_c as $objects_c_val) {
                        if ($objects_c_val != '.' && $objects_c_val != '..') {
                            if (filetype(_LEOBLOG_BLOG_IMG_DIR_.'c'.'/'.$objects_c_val) == 'file') {
                                @copy(_LEOBLOG_BLOG_IMG_DIR_.'c'.'/'.$objects_c_val, _PS_THEME_DIR_.'assets/img/modules/leoblog/sample/c/'.$objects_c_val);
                            }
                        }
                    }
                }
            }
        }
        
        if (!file_exists(_PS_THEME_DIR_.'assets/img/modules/leoblog/sample/index.php')) {
            @copy(_LEOBLOG_BLOG_IMG_DIR_.'index.php', _PS_THEME_DIR_.'assets/img/modules/leoblog/sample/index.php');
        }
        
        // if (!is_dir(_PS_THEME_DIR_.'assets/img/modules/leoblog/b')) {
            // mkdir(_PS_THEME_DIR_.'assets/img/modules/leoblog/b', 0777, true);
        // }
        
        // if (!file_exists(_PS_THEME_DIR_.'assets/img/modules/leoblog/b/index.php')) {
            // @copy(_LEOBLOG_BLOG_IMG_DIR_.'index.php', _PS_THEME_DIR_.'assets/img/modules/leoblog/b/index.php');
        // }
        
        // if (!is_dir(_PS_THEME_DIR_.'assets/img/modules/leoblog/c')) {
            // mkdir(_PS_THEME_DIR_.'assets/img/modules/leoblog/c', 0777, true);
        // }
        
        // if (!file_exists(_PS_THEME_DIR_.'assets/img/modules/leoblog/c/index.php')) {
            // @copy(_LEOBLOG_BLOG_IMG_DIR_.'index.php', _PS_THEME_DIR_.'assets/img/modules/leoblog/c/index.php');
        // }
        
        //DONGND:: get list id_shop from database of blog
        $list_id_shop = Db::getInstance()->executes('SELECT `id_shop` FROM `'._DB_PREFIX_.'leoblog_blog_shop` GROUP BY `id_shop`');
            
        if (count($list_id_shop) > 0) {
            foreach ($list_id_shop as $list_id_shop_val) {
                if (!is_dir(_PS_THEME_DIR_.'assets/img/modules/leoblog/'.$list_id_shop_val['id_shop'])) {
                    mkdir(_PS_THEME_DIR_.'assets/img/modules/leoblog/'.$list_id_shop_val['id_shop'], 0777, true);
                    
                    @copy(_LEOBLOG_BLOG_IMG_DIR_.'index.php', _PS_THEME_DIR_.'assets/img/modules/leoblog/'.$list_id_shop_val['id_shop'].'/index.php');
                    
                    mkdir(_PS_THEME_DIR_.'assets/img/modules/leoblog/'.$list_id_shop_val['id_shop'].'/b', 0777, true);
                    
                    mkdir(_PS_THEME_DIR_.'assets/img/modules/leoblog/'.$list_id_shop_val['id_shop'].'/c', 0777, true);
                    
                    if (is_dir(_LEOBLOG_BLOG_IMG_DIR_.'b') && is_dir(_PS_THEME_DIR_.'assets/img/modules/leoblog/'.$list_id_shop_val['id_shop'].'/b')) {
                        $objects_b = scandir(_LEOBLOG_BLOG_IMG_DIR_.'b');
                        $objects_theme_b = scandir(_PS_THEME_DIR_.'assets/img/modules/leoblog/'.$list_id_shop_val['id_shop'].'/b');
                        if (count($objects_b) > 2 && count($objects_theme_b) <= 2) {
                            foreach ($objects_b as $objects_b_val) {
                                if ($objects_b_val != '.' && $objects_b_val != '..') {
                                    if (filetype(_LEOBLOG_BLOG_IMG_DIR_.'b'.'/'.$objects_b_val) == 'file') {
                                        @copy(_LEOBLOG_BLOG_IMG_DIR_.'b'.'/'.$objects_b_val, _PS_THEME_DIR_.'assets/img/modules/leoblog/'.$list_id_shop_val['id_shop'].'/b/'.$objects_b_val);
                                    }
                                }
                            }
                        }
                    }
                    
                    if (is_dir(_LEOBLOG_BLOG_IMG_DIR_.'c') && is_dir(_PS_THEME_DIR_.'assets/img/modules/leoblog/'.$list_id_shop_val['id_shop'].'/c')) {
                        $objects_c = scandir(_LEOBLOG_BLOG_IMG_DIR_.'c');
                        $objects_theme_c = scandir(_PS_THEME_DIR_.'assets/img/modules/leoblog/'.$list_id_shop_val['id_shop'].'/c');
                        if (count($objects_c) > 2 && count($objects_theme_c) <= 2) {
                            foreach ($objects_c as $objects_c_val) {
                                if ($objects_c_val != '.' && $objects_c_val != '..') {
                                    if (filetype(_LEOBLOG_BLOG_IMG_DIR_.'c'.'/'.$objects_c_val) == 'file') {
                                        @copy(_LEOBLOG_BLOG_IMG_DIR_.'c'.'/'.$objects_c_val, _PS_THEME_DIR_.'assets/img/modules/leoblog/'.$list_id_shop_val['id_shop'].'/c/'.$objects_c_val);
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }
    
    /**
     * @Action Create new shop, choose theme then auto restore datasample.
     */
    public function hookActionAdminShopControllerSaveAfter($param)
    {
        if (Tools::getIsset('controller') !== false && Tools::getValue('controller') == 'AdminShop'
                && Tools::getIsset('submitAddshop') !== false && Tools::getValue('submitAddshop')
                && Tools::getIsset('theme_name') !== false && Tools::getValue('theme_name')) {
            $shop = $param['return'];
            
            if (file_exists(_PS_MODULE_DIR_.'appagebuilder/libs/LeoDataSample.php')) {
                require_once(_PS_MODULE_DIR_.'appagebuilder/libs/LeoDataSample.php');
                $sample = new Datasample();
                LeoBlogHelper::$id_shop = $shop->id;
                $sample->_id_shop = $shop->id;
                $sample->processImport('leoblog');
            }
        }
    }
    
    /**
     * Add the CSS & JavaScript files you want to be loaded in the BO.
     */
    public function hookActionAdminControllerSetMedia()
    {
        $this->autoRestoreSampleData();
    }
    
    // public function isCached($template, $cache_id = null, $compile_id = null)
    // {
        // if (version_compare(_PS_VERSION_, '1.7.4.0', '>=') || version_compare(Configuration::get('PS_VERSION_DB'), '1.7.4.0', '>=')) {
            // return false;
        // }
        // return parent::isCached($template, $cache_id, $compile_id);
    // }

    /**
     * FIX BUG 1.7.3.3 : install theme lose hook displayHome, displayLeoProfileProduct
     * because ajax not run hookActionAdminBefore();
     */
    public function autoRestoreSampleData()
    {
        if (Hook::isModuleRegisteredOnHook($this, 'actionAdminBefore', (int)Context::getContext()->shop->id)) {
            $theme_manager = new stdclass();
            $theme_manager->theme_manager = 'theme_manager';
            $this->hookActionAdminBefore(array(
                'controller' => $theme_manager,
            ));
        }
    }

    public function getMediaDir()
    {
        $media_dir = '';
        if (version_compare(_PS_VERSION_, '1.7.4.0', '>=') || version_compare(Configuration::get('PS_VERSION_DB'), '1.7.4.0', '>=')) {
            $media_dir = 'modules/'.$this->name.'/views/';
        } else {
            $media_dir = 'modules/'.$this->name.'/';
        }
        return $media_dir;
    }
    
    /**
     * PERMISSION ACCOUNT demo@demo.com
     */
    public function getPermission($variable, $employee = null)
    {
        if ($variable == 'configure') {
            // Allow see form if permission is : configure, view
            $configure = Module::getPermissionStatic($this->id, 'configure', $employee);
            $view = Module::getPermissionStatic($this->id, 'view', $employee);
            return ($configure || $view);
        }
        
        return Module::getPermissionStatic($this->id, $variable, $employee);
    }
    
    /**
     * PERMISSION ACCOUNT demo@demo.com
     */
    public function access($action)
    {
        $employee = null;
        return Module::getPermissionStatic($this->id, $action, $employee);
    }
}
