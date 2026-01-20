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

include_once(_PS_MODULE_DIR_.'leoblog/loader.php');

class Cs24BlogCategoryModuleFrontController extends ModuleFrontController
{
    public $php_self;
    protected $template_path = '';

    public function __construct()
    {
        parent::__construct();
        $this->context = Context::getContext();
        $this->template_path = _PS_MODULE_DIR_.'cs24blog/views/templates/front/';
    }

    /**
     * @see FrontController::initContent()
     */
    public function initContent()
    {
        $config = Cs24BlogConfig::getInstance();

        /* Load Css and JS File */
        Cs24BlogHelper::loadMedia($this->context, $this);

        $this->php_self = 'category';

        // $this->php_self = 'module-leoblog-category';
        
        parent::initContent();

        $id_category = (int)Tools::getValue('id');

        $helper = Cs24BlogHelper::getInstance();

        $limit_leading_blogs = (int)$config->get('listing_leading_limit_items', 1);
        $limit_secondary_blogs = (int)$config->get('listing_secondary_limit_items', 6);

        $limit = (int)$limit_leading_blogs + (int)$limit_secondary_blogs;
        $n = $limit;
        $p = abs((int)(Tools::getValue('p', 1)));
        if ($config->get('url_use_id', 1)) {
            // URL HAVE ID
            $category = new Cs24BlogCat($id_category, $this->context->language->id);
        } else {
            // REMOVE ID FROM URL
            $url_rewrite = explode('/', $_SERVER['REQUEST_URI']) ;
            $url_last_item = count($url_rewrite) - 1;
            if (strpos($url_rewrite[$url_last_item], '?')) {
                $url_rewrite = explode('?', $url_rewrite[$url_last_item])[0];
                $url_rewrite = rtrim($url_rewrite, 'html');
            } else {
                $url_rewrite = rtrim($url_rewrite[$url_last_item], 'html');
            }

            $url_rewrite = rtrim($url_rewrite, '\.');    // result : product.html -> product.
            $category = Cs24BlogCat::findByRewrite(array('link_rewrite'=>$url_rewrite));
        }
        
        $template = $config->get('template', 'default');
        // set link demo
        if (Tools::getValue('bloglayout') != null) {
            if (is_dir(_PS_THEME_DIR_.'modules/cs24blog/views/templates/front/'.Tools::getValue('bloglayout'))) {
                $template = Tools::getValue('bloglayout');
            } elseif (is_dir(_PS_MODULE_DIR_ .'cs24blog/views/templates/front/'.Tools::getValue('bloglayout'))) {
                $template = Tools::getValue('bloglayout');
            }
        }
        //set file include
        if (is_dir(_PS_THEME_DIR_.'modules/cs24blog/views/templates/front/'.$template) || is_dir(_PS_MODULE_DIR_.'cs24blog/views/templates/front/'.$template)) {
            if (file_exists(_PS_THEME_DIR_.'modules/cs24blog/views/templates/front/'.$template.'/_listing_blog.tpl') || file_exists(_PS_MODULE_DIR_.'cs24blog/views/templates/front/'.$template.'/_listing_blog.tpl')) {
                $_listing_blog = 'module:cs24blog/views/templates/front/'.$template.'/_listing_blog.tpl';
            } else {
                $_listing_blog = 'module:cs24blog/views/templates/front/default/_listing_blog.tpl';
            }
            if (file_exists(_PS_THEME_DIR_.'modules/cs24blog/views/templates/front/'.$template.'/_pagination.tpl') || file_exists(_PS_MODULE_DIR_.'cs24blog/views/templates/front/'.$template.'/_pagination.tpl')) {
                $_pagination = 'module:cs24blog/views/templates/front/'.$template.'/_pagination.tpl';
            } else {
                $_pagination = 'module:cs24blog/views/templates/front/default/_pagination.tpl';
            }

            if (!file_exists(_PS_THEME_DIR_.'modules/cs24blog/views/templates/front/'.$template.'/category.tpl') && !file_exists(_PS_MODULE_DIR_.'cs24blog/views/templates/front/'.$template.'/category.tpl')) {
                $template = 'default';
            }
        }

        if ($category->id_cs24_blog_cat && $category->active) {
//            $_GET['rewrite'] = $category->link_rewrite;
            $id_shop = $this->context->shop->id;
            $url = _PS_BASE_URL_;
            if (Tools::usingSecureMode()) {
                # validate module
                $url = _PS_BASE_URL_SSL_;
            }
            if ($category->image) {
                # validate module
                $category->image = $url._THEME_DIR_.'assets/img/modules/cs24blog/'.$id_shop.'/c/'.$category->image;
            }

            $blogs = Cs24BlogBlog::getListBlogs($category->id_cs24_blog_cat, $this->context->language->id, $p, $limit, 'id_cs24_blog_blog', 'DESC', array(), true);
            $count = Cs24BlogBlog::countBlogs($category->id_cs24_blog_cat, $this->context->language->id, true);
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
                $blog = Cs24BlogHelper::buildBlog($helper, $blog, $image_w, $image_h, $config);
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
                $blog = Cs24BlogHelper::buildBlog($helper, $blog, $image_w, $image_h, $config);
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

            $params = array(
                'rewrite' => $category->link_rewrite,
                'id' => $category->id_cs24_blog_cat
            );

            /* breadcrumb */
            $r = $helper->getPaginationLink('module-leoblog-category', 'category', $params, false, true);
            $all_cats = array();
            self::parentCategories($category, $all_cats);

            /* sub categories */
            $categories = $category->getChild($category->id_cs24_blog_cat, $this->context->language->id);

            $childrens = array();

            if ($categories) {
                foreach ($categories as $child) {
                    $params = array(
                        'rewrite' => $child['link_rewrite'],
                        'id' => $child['id_cs24_blog_cat']
                    );

                    $child['thumb'] = $url._THEME_DIR_.'assets/img/modules/cs24blog/'.$id_shop.'/c/'.$child['image'];

                    $child['category_link'] = $helper->getBlogCatLink($params);
                    if ($child['active']) {
                        $childrens[] = $child;
                    }
                }
            }
            
            if ((bool)Module::isEnabled('appagebuilder')) {
                $appagebuilder = Module::getInstanceByName('appagebuilder');
                $category->content_text = $appagebuilder->buildShortCode($category->content_text);
                
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
                'config' => $config,
                'range' => $range,
                'category' => $category,
                'start' => $start,
                'childrens' => $childrens,
                'stop' => $stop,
                'pages_nb' => $pages_nb,
                'nb_items' => $count,
                'p' => (int)$p,
                'n' => (int)$n,
                'meta_title' => Tools::ucfirst($category->title).' - '.Configuration::get('PS_SHOP_NAME'),
                'meta_keywords' => $category->meta_keywords,
                'meta_description' => $category->meta_description,
                'requestPage' => $r['requestUrl'],
                'requestNb' => $r,
                '_listing_blog' => $_listing_blog,
                '_pagination' => $_pagination
            ));
        } else {
            $this->context->smarty->assign(array(
                'active' => '0',
                'leading_blogs' => array(),
                'secondary_blogs' => array(),
                'controller' => 'category',
                'category' => $category
            ));
        }

        $this->setTemplate('module:cs24blog/views/templates/front/'.$template.'/category.tpl');
    }

    public static function parentCategories($current, &$return)
    {
        if ($current->id_parent) {
            $obj = new Cs24BlogCat($current->id_parent, Context::getContext()->language->id);
            self::parentCategories($obj, $return);
        }
        $return[] = $current;
    }
    
    //DONGND:: add meta
    public function getTemplateVarPage()
    {
        $page = parent::getTemplateVarPage();
        $config = Cs24BlogConfig::getInstance();
        if ($config->get('url_use_id', 1)) {
            // URL HAVE ID
            $category = new Cs24BlogCat((int)Tools::getValue('id'), $this->context->language->id);
        } else {
            // REMOVE ID FROM URL
            $url_rewrite = explode('/', $_SERVER['REQUEST_URI']) ;
            $url_last_item = count($url_rewrite) - 1;
            $url_rewrite = rtrim($url_rewrite[$url_last_item], '.html');
            $category = Cs24BlogCat::findByRewrite(array('link_rewrite' => $url_rewrite));
        }
        $page['meta']['title'] = Tools::ucfirst($category->title).' - '.Configuration::get('PS_SHOP_NAME');
        $page['meta']['keywords'] = $category->meta_keywords;
        $page['meta']['description'] = $category->meta_description;

        $params = array(
            'rewrite' => $category->link_rewrite,
            'id' => $category->id_cs24_blog_cat
        );
        $page['canonical'] = Cs24BlogHelper::getInstance()->getBlogCatLink($params);
        
        return $page;
    }
    
    //DONGND:: add breadcrumb
    public function getBreadcrumbLinks()
    {
        $breadcrumb = parent::getBreadcrumbLinks();
        $helper = Cs24BlogHelper::getInstance();
        $link = $helper->getFontBlogLink();
        $config = Cs24BlogConfig::getInstance();
        $breadcrumb['links'][] = array(
            'title' => $config->get('blog_link_title_'.$this->context->language->id, $this->l('Blog', 'category')),
            'url' => $link,
        );
        
        if ($config->get('url_use_id', 1)) {
            // URL HAVE ID
            $category = new Cs24BlogCat((int)Tools::getValue('id'), $this->context->language->id);
        } else {
            // REMOVE ID FROM URL
            $url_rewrite = explode('/', $_SERVER['REQUEST_URI']) ;
            $url_last_item = count($url_rewrite) - 1;
            $url_rewrite = rtrim($url_rewrite[$url_last_item], '.html');
            $category = Cs24BlogCat::findByRewrite(array('link_rewrite'=>$url_rewrite));
        }
                
        $params = array(
            'rewrite' => $category->link_rewrite,
            'id' => $category->id_cs24_blog_cat
        );

        $category_link = $helper->getBlogCatLink($params);
        
        $breadcrumb['links'][] = array(
            'title' => $category->title,
            'url' => $category_link,
        );

        return $breadcrumb;
    }
    
    //DONGND:: get layout
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
