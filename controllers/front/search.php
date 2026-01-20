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

include_once(_PS_MODULE_DIR_.'leoblog/loader.php');

class LeoblogsearchModuleFrontController extends ModuleFrontController
{
    public $php_self;
    protected $template_path = '';

    public function __construct()
    {
        parent::__construct();
        $this->context = Context::getContext();
        $this->template_path = _PS_MODULE_DIR_.'leoblog/views/templates/front/';
    }

    /**
     * @see FrontController::initContent()
     */
    public function initContent()
    {
        $this->php_self = 'search';
        
        $config = LeoBlogConfig::getInstance();
        $authors = array();

        /* Load Css and JS File */
        LeoBlogHelper::loadMedia($this->context, $this);

        parent::initContent();

        $helper = LeoBlogHelper::getInstance();
        $limit_leading_blogs = (int)$config->get('listing_leading_limit_items', 1);
        $limit_secondary_blogs = (int)$config->get('listing_secondary_limit_items', 6);

        $limit = (int)$limit_leading_blogs + (int)$limit_secondary_blogs;
        $n = $limit;
        $p = abs((int)(Tools::getValue('p', 1)));
        $template = $config->get('template', 'default');
        // set link demo
        if (Tools::getValue('bloglayout') != null) {
            if (is_dir(_PS_THEME_DIR_.'modules/leoblog/views/templates/front/'.Tools::getValue('bloglayout'))) {
                $template = Tools::getValue('bloglayout');
            } elseif (is_dir(_PS_MODULE_DIR_ .'leoblog/views/templates/front/'.Tools::getValue('bloglayout'))) {
                $template = Tools::getValue('bloglayout');
            }
        }
        //set file include
        if (is_dir(_PS_THEME_DIR_.'modules/leoblog/views/templates/front/'.$template) || is_dir(_PS_MODULE_DIR_.'leoblog/views/templates/front/'.$template)) {
            if (file_exists(_PS_THEME_DIR_.'modules/leoblog/views/templates/front/'.$template.'/_listing_blog.tpl') || file_exists(_PS_MODULE_DIR_.'leoblog/views/templates/front/'.$template.'/_listing_blog.tpl')) {
                $_listing_blog = 'module:leoblog/views/templates/front/'.$template.'/_listing_blog.tpl';
            } else {
                $_listing_blog = 'module:leoblog/views/templates/front/default/_listing_blog.tpl';
            }
            
            if (file_exists(_PS_THEME_DIR_.'modules/leoblog/views/templates/front/'.$template.'/_pagination.tpl') || file_exists(_PS_MODULE_DIR_.'leoblog/views/templates/front/'.$template.'/_pagination.tpl')) {
                $_pagination = 'module:leoblog/views/templates/front/'.$template.'/_pagination.tpl';
            } else {
                $_pagination = 'module:leoblog/views/templates/front/default/_pagination.tpl';
            }

            if (!file_exists(_PS_THEME_DIR_.'modules/leoblog/views/templates/front/'.$template.'/search.tpl') && !file_exists(_PS_MODULE_DIR_.'leoblog/views/templates/front/'.$template.'/search.tpl')) {
                $template = 'default';
            }
        }

        // $blogs = LeoBlogBlog::getListBlogs(null, $this->context->language->id, $n, $n, 'id_leoblog_blog', 'DESC', array(), true);
        $count = LeoBlogBlog::countBlogs(null, $this->context->language->id, true);

        if (Tools::getValue('search_blog') == 'Best_articles') {
            $blogs = LeoBlogBlog::getListBlogs(null, $this->context->language->id, $p, $n, 'hits', 'DESC', array(), true);
        } elseif (Tools::getValue('search_blog') == 'Latest_articles') {
            $blogs = LeoBlogBlog::getListBlogs(null, $this->context->language->id, $p, $n, 'date_add', 'DESC', array(), true);
        } elseif (Tools::getValue('search_blog') == 'Articles_favorite') {
            $blogs = LeoBlogBlog::getListBlogs(null, $this->context->language->id, $p, $n, 'favorite', 'DESC', array('favorite' => 1), true);
            $count = LeoBlogBlog::countBlogs(null, $this->context->language->id, array('favorite' => 1), true);
        } elseif (Tools::getValue('search_blog') != '') {
            $blogs = LeoBlogBlog::getListBlogs(null, $this->context->language->id, $p, $n, 'id_leoblog_blog', 'DESC', array('search' => Tools::getValue('search_blog')), true);
            $count = LeoBlogBlog::countBlogs(null, $this->context->language->id, array('search' => Tools::getValue('search_blog')), true);
        } else {
            $blogs = LeoBlogBlog::getListBlogs(null, $this->context->language->id, $p, $n, 'id_leoblog_blog', 'DESC', array(), true);
        }

        // print_r($count);die;
        
        $authors = array();

        $leading_blogs = array();
        $secondary_blogs = array();
//            $links        =  array();

        if (count($blogs)) {
            $leading_blogs = array_slice($blogs, 0, $limit_leading_blogs);
            $secondary_blogs = array_splice($blogs, $limit_leading_blogs, count($blogs));
        }
        $image_w = (int)$config->get('listing_leading_img_width', 690);
        $image_h = (int)$config->get('listing_leading_img_height', 300);

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

        $image_w = (int)$config->get('listing_secondary_img_width', 390);
        $image_h = (int)$config->get('listing_secondary_img_height', 200);

        foreach ($secondary_blogs as $key => $blog) {
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

            $secondary_blogs[$key] = $blog;
        }

        $nb_blogs = $count;
        $range = 2; /* how many pages around page selected */
        if ($p > (($nb_blogs / $n) + 1)) {
            Tools::redirect(preg_replace('/[&?]p=\d+/', '', $_SERVER['REQUEST_URI']));
        }
        $pages_nb = ceil($nb_blogs / (int)($n));
        $start = (int)($p - $range);
        if ($start < 1) {
            $start = 1;
        }
        $stop = (int)($p + $range);
        if ($stop > $pages_nb) {
            $stop = (int)($pages_nb);
        }

        $r = $helper->getPaginationLink('module-leoblog-search', 'search', array(), false, true);

        if ((bool)Module::isEnabled('appagebuilder')) {
            $appagebuilder = Module::getInstanceByName('appagebuilder');

            foreach ($leading_blogs as $key => &$blog) {
                $blog['description'] = $appagebuilder->buildShortCode($blog['description']);
                $blog['content'] = $appagebuilder->buildShortCode($blog['content']);
            }
        }

        $this->context->smarty->assign(array(
            'leading_blogs' => $leading_blogs,
            'secondary_blogs' => $secondary_blogs,
            'listing_leading_column' => $config->get('listing_leading_column', 1),
            'listing_secondary_column' => $config->get('listing_secondary_column', 3),
            'title_page' => Tools::str_replace_once('_', ' ', Tools::getValue('search_blog')),
            'nb_items' => $count,
            'range' => $range,
            'start' => $start,
            'stop' => $stop,
            'pages_nb' => $pages_nb,
            'config' => $config,
            'p' => (int)$p,
            'n' => (int)$n,
            'meta_title' => 'Blog Search - '.Configuration::get('PS_SHOP_NAME'),
            'meta_keywords' => $config->get('meta_keywords_'.Context::getContext()->language->id),
            'meta_description' => $config->get('meta_description_'.Context::getContext()->language->id),
            'requestPage' => Context::getContext()->link->getModuleLink('leoblog', 'search', array()).'?search_blog='.Tools::getValue('search_blog'),
            'requestNb' => $r,
            '_listing_blog' => $_listing_blog,
            '_pagination' => $_pagination

        ));
        $this->setTemplate('module:leoblog/views/templates/front/'.$template.'/search.tpl');
    }

    public function getTemplateVarPage()
    {
        $page = parent::getTemplateVarPage();
        $config = LeoBlogConfig::getInstance();
        $page['meta']['title'] = 'Blog Search - '.Configuration::get('PS_SHOP_NAME');
        $page['meta']['keywords'] = $config->get('meta_keywords_'.Context::getContext()->language->id);
        $page['meta']['description'] = $config->get('meta_description_'.Context::getContext()->language->id);
        return $page;
    }
    
    public function getBreadcrumbLinks()
    {
        $breadcrumb = parent::getBreadcrumbLinks();
        $helper = LeoBlogHelper::getInstance();
        $link = $helper->getFontBlogLink();
        $config = LeoBlogConfig::getInstance();
        $breadcrumb['links'][] = array(
            'title' => $config->get('blog_link_title_'.$this->context->language->id, $this->l('Blog', 'search')),
            'url' => $link,
        );

        $breadcrumb['links'][] = array(
            'title' => 'Search',
            'url' => Context::getContext()->link->getModuleLink('leoblog', 'search', array()),
        );
        return $breadcrumb;
    }
    
    public function getLayout()
    {
        $entity = 'module-leoblog-'.$this->php_self;
        
        $layout = $this->context->shop->theme->getLayoutRelativePathForPage($entity);
        
        if ($overridden_layout = Hook::exec(
            'overrideLayoutTemplate',
            array(
                'default_layout' => $layout,
                'entity' => $entity,
                'locale' => $this->context->language->locale,
                'controller' => $this,
            )
        )) {
            return $overridden_layout;
        }

        if ((int) Tools::getValue('content_only')) {
            $layout = 'layouts/layout-content-only.tpl';
        }
        if (Configuration::get('LEOBLOG_COLUMN_POSITION') == 'left') {
            $layout = 'layouts/layout-left-column.tpl';
        } else {
            $layout = 'layouts/layout-right-column.tpl';
        }
        return $layout;
    }
}
