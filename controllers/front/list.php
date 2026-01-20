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

class LeobloglistModuleFrontController extends ModuleFrontController
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
        // $this->php_self = 'module-leoblog-list';
        $this->php_self = 'list';
        
        $config = LeoBlogConfig::getInstance();
        $authors = array();

        /* Load Css and JS File */
        LeoBlogHelper::loadMedia($this->context, $this);

        parent::initContent();

        $helper = LeoBlogHelper::getInstance();

        $limit_leading_blogs = (int)$config->get('listing_leading_limit_items', 1);
        $limit_secondary_blogs = (int)$config->get('listing_secondary_limit_items', 6);
        //$latest_limit_items = (int)$config->get( 'latest_limit_items', 20 );
        $author = Tools::getValue('author');
        $tag = trim(Tools::getValue('tag'));
        $n = (int)$limit_leading_blogs + (int)$limit_secondary_blogs;
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

            if (!file_exists(_PS_THEME_DIR_.'modules/leoblog/views/templates/front/'.$template.'/listing.tpl') && !file_exists(_PS_MODULE_DIR_.'leoblog/views/templates/front/'.$template.'/listing.tpl')) {
                $template = 'default';
            }
        }

        $condition = array();
        
        if ($author) {
            $employee_obj = new Employee($author);
            if (isset($employee_obj) && $employee_obj->id != '') {
                $condition = array(
                    'type' => 'author',
                    'id_employee' => $author,
                    'employee' => new Employee($author)
                );
            } else {
                $condition = array(
                    'type' => 'author',
                    'author_name' => $author,
                );
            }
            $r = $helper->getPaginationLink('module-leoblog-list', 'list', array('author' => $author));
        }
        if ($tag) {
            $condition = array(
                'type' => 'tag',
                'tag' => urldecode($tag)
            );
            $r = $helper->getPaginationLink('module-leoblog-list', 'list', array('tag' => $tag));
        }

        $blogs = LeoBlogBlog::getListBlogs(null, $this->context->language->id, $p, $n, 'id_leoblog_blog', 'DESC', $condition, true);
        $count = LeoBlogBlog::countBlogs(null, $this->context->language->id, $condition, true);

        $leading_blogs = array();
        $secondary_blogs = array();

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

        if (!isset($r)) {
            $r = $helper->getPaginationLink('module-leoblog-list', 'list', array(), false, true);
        }

        $url_rss = '';
        $enbrss = (int)$config->get('indexation', 0);
        if ($enbrss == 1) {
            $url_rss = Tools::htmlentitiesutf8('http://'.$_SERVER['HTTP_HOST'].__PS_BASE_URI__).'modules/leoblog/rss.php';
        }
        
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
            'filter' => $condition,
            'nb_items' => $count,
            'range' => $range,
            'start' => $start,
            'stop' => $stop,
            'pages_nb' => $pages_nb,
            'config' => $config,
            'p' => (int)$p,
            'n' => (int)$n,
            'meta_title' => $config->get('meta_title_'.Context::getContext()->language->id, 'Blog').' - '.Configuration::get('PS_SHOP_NAME'),
            'meta_keywords' => $config->get('meta_keywords_'.Context::getContext()->language->id),
            'meta_description' => $config->get('meta_description_'.Context::getContext()->language->id),
            'requestPage' => $r['requestUrl'],
            'requestNb' => $r,
            'controller' => 'latest',
            'url_rss' => $url_rss,
            '_listing_blog' => $_listing_blog,
            '_pagination' => $_pagination
        ));
        $this->setTemplate('module:leoblog/views/templates/front/'.$template.'/listing.tpl');
    }
    
    public function getTemplateVarPage()
    {
        $page = parent::getTemplateVarPage();
        $config = LeoBlogConfig::getInstance();
        $page['meta']['title'] = $config->get('meta_title_'.Context::getContext()->language->id, 'Blog').' - '.Configuration::get('PS_SHOP_NAME');
        $page['meta']['keywords'] = $config->get('meta_keywords_'.Context::getContext()->language->id);
        $page['meta']['description'] = $config->get('meta_description_'.Context::getContext()->language->id);
        $page['canonical'] = LeoBlogHelper::getInstance()->getFontBlogLink();
        return $page;
    }
    
    public function getBreadcrumbLinks()
    {
        $breadcrumb = parent::getBreadcrumbLinks();
        $link = LeoBlogHelper::getInstance()->getFontBlogLink();
        $config = LeoBlogConfig::getInstance();
        $breadcrumb['links'][] = array(
            'title' => $config->get('blog_link_title_'.$this->context->language->id, $this->l('Blog', 'list')),
            'url' => $link,
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

        return $layout;
    }
}
