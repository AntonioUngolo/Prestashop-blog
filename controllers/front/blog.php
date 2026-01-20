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

class LeoblogblogModuleFrontController extends ModuleFrontController
{
    public $php_self;
    protected $template_path = '';
    // public $rewrite;
    
    public function __construct()
    {
        parent::__construct();
        $this->context = Context::getContext();
        $this->template_path = _PS_MODULE_DIR_.'leoblog/views/templates/front/';
        // $this->rewrite = 'aaaa';
    }

    public function captcha()
    {
        include_once(_PS_MODULE_DIR_.'leoblog/classes/captcha.php');
        $captcha = new LeoCaptcha();
        $this->context->cookie->leocaptch = $captcha->getCode();
        $captcha->showImage();
    }

    /**
     * @param object &$object Object
     * @param string $table Object table
     * @ DONE
     */
    protected function copyFromPost(&$object, $table, $post = array())
    {
        /* Classical fields */
        foreach ($post as $key => $value) {
            if (property_exists($object, $key) && $key != 'id_'.$table) {
                /* Do not take care of password field if empty */
                if ($key == 'passwd' && Tools::getValue('id_'.$table) && empty($value)) {
                    continue;
                }
                if ($key == 'passwd' && !empty($value)) {
                    /* Automatically encrypt password in MD5 */
                    $value = Tools::encrypt($value);
                }
                $object->{$key} = $value;
            }
        }
        
        /* Multilingual fields */
        $rules = call_user_func(array(get_class($object), 'getValidationRules'), get_class($object));
        if (count($rules['validateLang'])) {
            $languages = Language::getLanguages(false);
            foreach ($languages as $language) {
                foreach (array_keys($rules['validateLang']) as $field) {
                    $field_name = $field.'_'.(int)($language['id_lang']);
                    $value = Tools::getValue($field_name);
                    if (isset($value)) {
                        # validate module
                        $object->{$field}[(int)($language['id_lang'])] = $value;
                    }
                }
            }
        }
    }

    /**
     * Save user comment
     */
    protected function comment()
    {
        $post = array();
        $post['user'] = Tools::getValue('user');
        $post['email'] = Tools::getValue('email');
        $post['comment'] = Tools::getValue('comment');
        $post['captcha'] = Tools::getValue('captcha');
        $post['id_leoblog_blog'] = Tools::getValue('id_leoblog_blog');
        $post['submitcomment'] = Tools::getValue('submitcomment');

        if (!empty($post)) {
            $comment = new LeoBlogComment();
            $captcha = Tools::getValue('captcha');
            $this->copyFromPost($comment, 'comment', $post);

            $error = new stdClass();
            $error->error = true;

            if (isset($this->context->cookie->leocaptch) && $captcha && $captcha == $this->context->cookie->leocaptch) {
                if ($comment->validateFields(false) && $comment->validateFieldsLang(false)) {
                    $comment->save();
                    $error->message = $this->l('Thanks for your comment, it will be published soon!!!', 'blog');
                    $error->error = false;
                } else {
                    # validate module
                    $error->message = $this->l('An error occurred while sending the comment. Please recorrect data in fields!!!', 'blog');
                }
            } else {
                # validate module
                $error->message = $this->l('An error with captcha code, please try to recorrect!!!', 'blog');
            }


            die(json_encode($error));
        }
    }

    /**
     * @see FrontController::initContent()
     */
    public function initContent()
    {
        
        $this->php_self = 'blog';
        // $this->php_self = 'module-leoblog-blog';
        
        if (Tools::getValue('captchaimage')) {
            $this->captcha();
            exit();
        }
        $config = LeoBlogConfig::getInstance();

        /* Load Css and JS File */
        LeoBlogHelper::loadMedia($this->context, $this);

        parent::initContent();

        if (Tools::isSubmit('submitcomment')) {
            # validate module
            $this->comment();
        }

        $helper = LeoBlogHelper::getInstance();
        if ($config->get('url_use_id', 1)) {
            // URL HAVE ID
            $blog = new LeoBlogBlog(Tools::getValue('id'), $this->context->language->id);
        } else {
            // REMOVE ID FROM URL
            $url_rewrite = explode('/', $_SERVER['REQUEST_URI']) ;
            $url_last_item = count($url_rewrite) - 1;
            $url_rewrite = rtrim($url_rewrite[$url_last_item], 'html');
            $url_rewrite = rtrim($url_rewrite, '\.');    // result : product.html -> product.
            $blog = LeoBlogBlog::findByRewrite(array('link_rewrite'=>$url_rewrite));
        }

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
            if (file_exists(_PS_THEME_DIR_.'modules/leoblog/views/templates/front/'.$template.'/_social.tpl') || file_exists(_PS_MODULE_DIR_.'leoblog/views/templates/front/'.$template.'/_social.tpl')) {
                $_social = 'module:leoblog/views/templates/front/'.$template.'/_social.tpl';
            } else {
                $_social = 'module:leoblog/views/templates/front/default/_social.tpl';
            }

            if (file_exists(_PS_THEME_DIR_.'modules/leoblog/views/templates/front/'.$template.'/_facebook_comment.tpl') || file_exists(_PS_MODULE_DIR_.'leoblog/views/templates/front/'.$template.'/_facebook_comment.tpl')) {
                $_facebook_comment = 'module:leoblog/views/templates/front/'.$template.'/_facebook_comment.tpl';
            } else {
                $_facebook_comment = 'module:leoblog/views/templates/front/default/_facebook_comment.tpl';
            }

            if (file_exists(_PS_THEME_DIR_.'modules/leoblog/views/templates/front/'.$template.'/_diquis_comment.tpl') || file_exists(_PS_MODULE_DIR_.'leoblog/views/templates/front/'.$template.'/_diquis_comment.tpl')) {
                $_diquis_comment = 'module:leoblog/views/templates/front/'.$template.'/_diquis_comment.tpl';
            } else {
                $_diquis_comment = 'module:leoblog/views/templates/front/default/_diquis_comment.tpl';
            }

            if (file_exists(_PS_THEME_DIR_.'modules/leoblog/views/templates/front/'.$template.'/_local_comment.tpl') || file_exists(_PS_MODULE_DIR_.'leoblog/views/templates/front/'.$template.'/_local_comment.tpl')) {
                $_local_comment = 'module:leoblog/views/templates/front/'.$template.'/_local_comment.tpl';
            } else {
                $_local_comment = 'module:leoblog/views/templates/front/default/_local_comment.tpl';
            }

            if (file_exists(_PS_THEME_DIR_.'modules/leoblog/views/templates/front/'.$template.'/_pagination.tpl') || file_exists(_PS_MODULE_DIR_.'leoblog/views/templates/front/'.$template.'/_pagination.tpl')) {
                $_pagination = 'module:leoblog/views/templates/front/'.$template.'/_pagination.tpl';
            } else {
                $_pagination = 'module:leoblog/views/templates/front/default/_pagination.tpl';
            }

            if (!file_exists(_PS_THEME_DIR_.'modules/leoblog/views/templates/front/'.$template.'/blog.tpl') && !file_exists(_PS_MODULE_DIR_.'leoblog/views/templates/front/'.$template.'/blog.tpl')) {
                $template = 'default';
            }
        }
        
        if (!$blog->id_leoblog_blog) {
            $vars = array(
                'error' => true,
            );
            $this->context->smarty->assign($vars);

            return $this->setTemplate('module:leoblog/views/templates/front/'.$template.'/blog.tpl');
        }

        $category = new leoblogcat($blog->id_leoblogcat, $this->context->language->id);


        $image_w = $config->get('item_img_width', 690);
        $image_h = $config->get('item_img_height', 300);
        

        $url = _PS_BASE_URL_;
        if (Tools::usingSecureMode()) {
            # validate module
            $url = _PS_BASE_URL_SSL_;
        }

        $id_shop = $this->context->shop->id;
        $blog->preview_url = '';
        if ($blog->image) {
            $blog->image_url = $url._THEME_DIR_.'assets/img/modules/leoblog/'.$id_shop.'/b/'.$blog->image;
//            if (!file_exists(_LEOBLOG_CACHE_IMG_DIR_.'b/'.$id_shop.'/'.$blog->id.'/lg-'.$blog->image)) {
            # FIX : CHANGE CONFIG IN BACKOFFICE NOT CHANGE AT FRONTEND
                @mkdir(_LEOBLOG_CACHE_IMG_DIR_.'b/'.$id_shop, 0777);
                @mkdir(_LEOBLOG_CACHE_IMG_DIR_.'b/'.$id_shop.'/'.$blog->id, 0777);
                    
            if (ImageManager::resize(_PS_THEME_DIR_.'assets/img/modules/leoblog/'.$id_shop.'/b/'.$blog->image, _LEOBLOG_CACHE_IMG_DIR_.'b/'.$id_shop.'/'.$blog->id.'/lg-'.$blog->image, $image_w, $image_h)) {
                    # validate module
                    $blog->preview_url = $url._LEOBLOG_CACHE_IMG_URI_.'b/'.$id_shop.'/'.$blog->id.'/lg-'.$blog->image;
            }
//            }
            
            $blog->preview_url = $url._LEOBLOG_CACHE_IMG_URI_.'b/'.$id_shop.'/'.$blog->id.'/lg-'.$blog->image;
        }

        $captcha_image = $helper->getBlogLink(get_object_vars($blog), array('captchaimage' => 1));
        $blog_link = $helper->getBlogLink(get_object_vars($blog));
        $params = array(
            'rewrite' => $category->link_rewrite,
            'id' => $category->id_leoblogcat
        );

        $blog->category_link = $helper->getBlogCatLink($params);
        $blog->category_title = $category->title;
        
        //DONGND:: author name
        if ($blog->author_name != '') {
            # HAVE AUTHOR IN BO
            $blog->author = $blog->author_name;
            $blog->author_link = $helper->getBlogAuthorLink($blog->author_name);
        } else {
            # AUTO GENERATE AUTHOR
            $employee = new Employee($blog->id_employee);
            $blog->author = $employee->firstname.' '.$employee->lastname;
            $blog->author_link = $helper->getBlogAuthorLink($employee->id);
        }
               
        $tags = array();
        if ($blog->tags && $tmp = explode(',', $blog->tags)) {
            foreach ($tmp as $tag) {
                $tags[] = array(
                    'tag' => $tag,
                    'link' => $helper->getBlogTagLink($tag)
                );
            }
        }

        $blog->hits = $blog->hits + 1;
        $blog->updateField($blog->id, array('hits' => $blog->hits));
        $limit = 5;
        $samecats = LeoBlogBlog::getListBlogs($category->id_leoblogcat, $this->context->language->id, 0, $limit, 'date_add', 'DESC', array('type' => 'samecat', 'id_leoblog_blog' => $blog->id_leoblog_blog), true);
        foreach ($samecats as $key => $sblog) {
            $sblog['link'] = $helper->getBlogLink($sblog);
            $samecats[$key] = $sblog;
        }

        $tagrelated = array();

        if ($blog->tags) {
            $tagrelated = LeoBlogBlog::getListBlogs($category->id_leoblogcat, $this->context->language->id, 0, $limit, 'id_leoblog_blog', 'DESC', array('type' => 'tag', 'tag' => $blog->tags), true);
            foreach ($tagrelated as $key => $tblog) {
                $tblog['link'] = $helper->getBlogLink($tblog);
                $tagrelated[$key] = $tblog;
            }
        }

        /* Comments */
        $evars = array();
        if ($config->get('item_comment_engine', 'local') == 'local') {
            $count_comment = 0;
            if ($config->get('comment_engine', 'local') == 'local') {
                # validate module
                $count_comment = LeoBlogComment::countComments($blog->id_leoblog_blog, true);
            }

            $blog_link = $helper->getBlogLink(get_object_vars($blog));
            $limit = (int)$config->get('item_limit_comments', 10);
            $n = $limit;
            $p = abs((int)(Tools::getValue('p', 1)));

            $comment = new LeoBlogComment();
            $comments = $comment->getList($blog->id_leoblog_blog, $this->context->language->id, $p, $limit);

            $nb_blogs = $count_comment;
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

            $evars = array('pages_nb' => $pages_nb,
                'nb_items' => $count_comment,
                'p' => (int)$p,
                'n' => (int)$n,
                'requestPage' => $blog_link,
                'requestNb' => $blog_link,
                'start' => $start,
                'comments' => $comments,
                'range' => $range,
                'blog_count_comment' => $count_comment,
                'stop' => $stop);
        }
        if ((bool)Module::isEnabled('smartshortcode')) {
            if (context::getcontext()->controller->controller_type == 'front') {
                $smartshortcode = Module::getInstanceByName('smartshortcode');
                $blog->content = $smartshortcode->parse($blog->content);
            }
        }
        if ((bool)Module::isEnabled('appagebuilder')) {
            $appagebuilder = Module::getInstanceByName('appagebuilder');
            $blog->description = $appagebuilder->buildShortCode($blog->description);
            $blog->content = $appagebuilder->buildShortCode($blog->content);
        }

        $vars = array(
            'tags' => $tags,
            'meta_title' => Tools::ucfirst($blog->meta_title).' - '.Configuration::get('PS_SHOP_NAME'),
            'meta_keywords' => $blog->meta_keywords,
            'meta_description' => $blog->meta_description,
            'blog' => $blog,
            'samecats' => $samecats,
            'tagrelated' => $tagrelated,
            'config' => $config,
            'id_leoblog_blog' => $blog->id_leoblog_blog,
            'is_active' => $blog->active,
            'productrelated' => array(),
            'module_tpl' => $template,
            'captcha_image' => $captcha_image,
            'blog_link' => $blog_link,
            '_social' => $_social,
            '_facebook_comment' => $_facebook_comment,
            '_diquis_comment' => $_diquis_comment,
            '_local_comment' => $_local_comment,
            '_pagination' => $_pagination
        );

        $vars = array_merge($vars, $evars);

        $this->context->smarty->assign($vars);

        // $this->setTemplate($template.'/blog.tpl');
        $this->setTemplate('module:leoblog/views/templates/front/'.$template.'/blog.tpl');
    }
    
    // DONGND:: add meta
    public function getTemplateVarPage()
    {
        $page = parent::getTemplateVarPage();
        $config = LeoBlogConfig::getInstance();
        if ($config->get('url_use_id', 1)) {
            $blog = new LeoBlogBlog(Tools::getValue('id'), $this->context->language->id);
        } else {
            $url_rewrite = explode('/', $_SERVER['REQUEST_URI']) ;
            $url_last_item = count($url_rewrite) - 1;
            $url_rewrite = rtrim($url_rewrite[$url_last_item], '.html');
            $blog = LeoBlogBlog::findByRewrite(array('link_rewrite' => $url_rewrite));
        }
        $page['meta']['title'] = Tools::ucfirst($blog->meta_title).' - '.Configuration::get('PS_SHOP_NAME');
        $page['meta']['keywords'] = $blog->meta_keywords;
        $page['meta']['description'] = $blog->meta_description;
        $page['canonical'] = LeoBlogHelper::getInstance()->getBlogLink(get_object_vars($blog));

        //add blog images to post facebook, instagram
        $url = _PS_BASE_URL_;
        if (Tools::usingSecureMode()) {
            # validate module
            $url = _PS_BASE_URL_SSL_;
        }
        $id_shop = $this->context->shop->id;
        $blog->preview_url = '';
        if ($blog->image) {
            $blog->image_url = $url._THEME_DIR_.'assets/img/modules/leoblog/'.$id_shop.'/b/'.$blog->image;
                @mkdir(_LEOBLOG_CACHE_IMG_DIR_.'b/'.$id_shop, 0777);
                @mkdir(_LEOBLOG_CACHE_IMG_DIR_.'b/'.$id_shop.'/'.$blog->id, 0777);
                    
            if (ImageManager::resize(_PS_THEME_DIR_.'assets/img/modules/leoblog/'.$id_shop.'/b/'.$blog->image, _LEOBLOG_CACHE_IMG_DIR_.'b/'.$id_shop.'/'.$blog->id.'/lg-'.$blog->image, $config->get('item_img_width', 690), $config->get('item_img_height', 300))) {
                    # validate module
                    $blog->preview_url = $url._LEOBLOG_CACHE_IMG_URI_.'b/'.$id_shop.'/'.$blog->id.'/lg-'.$blog->image;
            }
            $blog->preview_url = $url._LEOBLOG_CACHE_IMG_URI_.'b/'.$id_shop.'/'.$blog->id.'/lg-'.$blog->image;
        }
        $page['meta']['image'] = $blog->preview_url;
        
        return $page;
    }
    //DONGND:: add breadcrumb
    public function getBreadcrumbLinks()
    {
        $breadcrumb = parent::getBreadcrumbLinks();
        $helper = LeoBlogHelper::getInstance();
        $link = $helper->getFontBlogLink();
        $config = LeoBlogConfig::getInstance();
        $breadcrumb['links'][] = array(
            'title' => $config->get('blog_link_title_'.$this->context->language->id, $this->l('Blog', 'blog')),
            'url' => $link,
        );
        
        if ($config->get('url_use_id', 1)) {
            $blog = new LeoBlogBlog(Tools::getValue('id'), $this->context->language->id);
        } else {
            $url_rewrite = explode('/', $_SERVER['REQUEST_URI']) ;
            $url_last_item = count($url_rewrite) - 1;
            $url_rewrite = rtrim($url_rewrite[$url_last_item], '.html');
            $blog = LeoBlogBlog::findByRewrite(array('link_rewrite' => $url_rewrite));
        }
        
        $category = new leoblogcat($blog->id_leoblogcat, $this->context->language->id);
        $params = array(
            'rewrite' => $category->link_rewrite,
            'id' => $category->id_leoblogcat
        );

        $category_link = $helper->getBlogCatLink($params);
        
        $breadcrumb['links'][] = array(
            'title' => $category->title,
            'url' => $category_link,
        );

        
        $breadcrumb['links'][] = array(
            'title' => Tools::ucfirst($blog->meta_title),
            'url' => $helper->getBlogLink(get_object_vars($blog)),
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
