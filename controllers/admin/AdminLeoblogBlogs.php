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

class AdminLeoblogBlogsController extends ModuleAdminController
{
    protected $max_image_size;
    protected $position_identifier = 'id_leoblog_blog';

    public function __construct()
    {
        parent::__construct();
        $this->bootstrap = true;
        $this->table = 'leoblog_blog';
        $this->identifier = 'id_leoblog_blog';
        $this->className = 'LeoBlogBlog';
        $this->lang = true;
        $this->addRowAction('edit');
        $this->addRowAction('delete');
        $this->bulk_actions = array('delete' => array('text' => $this->l('Delete selected'), 'confirm' => $this->l('Delete selected items?'), 'icon' => 'icon-trash'));
        $this->fields_list = array(
            'id_leoblog_blog' => array('title' => $this->l('ID'), 'align' => 'center', 'class' => 'fixed-width-xs'),
            'meta_title' => array('title' => $this->l('Blog Title'), 'filter_key' => 'b!meta_title'),
            'author_name' => array('title' => $this->l('Author Name'), 'filter_key' => 'a!author_name'),
            'title' => array('title' => $this->l('Category Title'), 'filter_key' => 'cl!title'),
            'active' => array('title' => $this->l('Displayed'), 'align' => 'center', 'active' => 'status', 'class' => 'fixed-width-sm', 'type' => 'bool', 'orderby' => true),
            'date_add' => array('title' => $this->l('Date Create'), 'type' => 'date', 'filter_key' => 'a!date_add'),
            'date_upd' => array('title' => $this->l('Date Update'), 'type' => 'datetime', 'filter_key' => 'a!date_upd')
            
        );
        $this->max_image_size = Configuration::get('PS_PRODUCT_PICTURE_MAX_SIZE');
        $this->_select .= ' cl.title ';
        $this->_join .= ' LEFT JOIN '._DB_PREFIX_.'leoblogcat c ON a.id_leoblogcat = c.id_leoblogcat
                                  LEFT JOIN '._DB_PREFIX_.'leoblogcat_lang cl ON cl.id_leoblogcat=c.id_leoblogcat AND cl.id_lang=b.id_lang
                ';
        if (Shop::getContext() == Shop::CONTEXT_SHOP) {
            $this->_join .= ' INNER JOIN `'._DB_PREFIX_.'leoblog_blog_shop` sh ON (sh.`id_leoblog_blog` = b.`id_leoblog_blog` AND sh.id_shop = '.(int)Context::getContext()->shop->id.') ';
        }
        $this->_where = '';
        $this->_group = ' GROUP BY (a.id_leoblog_blog) ';
        $this->_orderBy = 'a.id_leoblog_blog';
        $this->_orderWay = 'DESC';
    }

    public function initPageHeaderToolbar()
    {
        $link = $this->context->link;
        if (Tools::getValue('id_leoblog_blog')) {
            $helper = LeoBlogHelper::getInstance();
            $blog_obj = new LeoBlogBlog(Tools::getValue('id_leoblog_blog'), $this->context->language->id);
            $this->page_header_toolbar_btn['view-blog-preview'] = array(
                'href' => $helper->getBlogLink(get_object_vars($blog_obj)),
                'desc' => $this->l('Preview Blog'),
                'icon' => 'icon-preview leoblog-comment-link-icon icon-3x process-icon-preview',
                'target' => '_blank',
            );
            
            $this->page_header_toolbar_btn['view-blog-comment'] = array(
                'href' => $link->getAdminLink('AdminLeoblogComments').'&id_leoblog_blog='.Tools::getValue('id_leoblog_blog'),
                'desc' => $this->l('Manage Comments'),
                'icon' => 'icon-comment leoblog-comment-link-icon icon-3x process-icon-comment',
                'target' => '_blank',
            );
        }
        
        return parent::initPageHeaderToolbar();
    }
    
    public function renderForm()
    {
        if (!$this->loadObject(true)) {
            if (Validate::isLoadedObject($this->object)) {
                $this->display = 'edit';
            } else {
                $this->display = 'add';
            }
        }
        $this->autoCorrect();
        $this->initToolbar();
        $this->initPageHeaderToolbar();

        $id_leoblogcat = (int)(Tools::getValue('id_leoblogcat'));
        $obj = new leoblogcat($id_leoblogcat);
        $obj->getTree();
        $menus = $obj->getDropdown(null, $obj->id_parent, false);
        array_shift($menus);
        
        $id_shop = (int)Context::getContext()->shop->id;
        if ($this->object->image) {
            $thumb = _THEMES_DIR_.LeoBlogHelper::getThemeName().'/assets/img/modules/leoblog/'.$id_shop.'/b/'.$this->object->image;
        } else {
            $thumb = '';
        }
        $default_author_name = '';
        if (isset($this->context->employee->firstname) && isset($this->context->employee->lastname)) {
            $default_author_name = $this->context->employee->firstname.' '.$this->context->employee->lastname;
        }
        
        if ($this->object->id == '') {
            $this->object->author_name = $default_author_name;
        }
        if ($this->object->thumb) {
            $thumb_img = _THEMES_DIR_.LeoBlogHelper::getThemeName().'/assets/img/modules/leoblog/'.$id_shop.'/b/'.$this->object->thumb;
        } else {
            $thumb_img = '';
        }

        $this->multiple_fieldsets = true;
        
        $this->fields_form[0]['form'] = array(
            'tinymce' => true,
            'legend' => array(
                'title' => $this->l('Blog Form'),
                'icon' => 'icon-folder-close'
            ),
            'input' => array(
                array(
                    'type' => 'select',
                    'label' => $this->l('Category'),
                    'name' => 'id_leoblogcat',
                    'options' => array('query' => $menus,
                        'id' => 'id',
                        'name' => 'title'),
                    'default' => $id_leoblogcat,
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Meta title:'),
                    'name' => 'meta_title',
                    'id' => 'name', // for copyMeta2friendlyURL compatibility
                    'lang' => true,
                    'required' => true,
                    'class' => 'copyMeta2friendlyURL',
                    'hint' => $this->l('Invalid characters:').' &lt;&gt;;=#{}'
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Subtitle:'),
                    'name' => 'subtitle',
                    'id' => 'subname', // for copyMeta2friendlyURL compatibility
                    'lang' => true,
                    'class' => 'copyMeta2friendlyURL',
                    'hint' => $this->l('Invalid characters:').' &lt;&gt;;=#{}'
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Friendly URL'),
                    'name' => 'link_rewrite',
                    'required' => true,
                    'lang' => true,
                    'hint' => $this->l('Only letters and the minus (-) character are allowed')
                ),
                array(
                    'type' => 'tags',
                    'label' => $this->l('Tags'),
                    'name' => 'tags',
                    'lang' => true,
                    'hint' => array(
                        $this->l('Invalid characters:').' &lt;&gt;;=#{}',
                        $this->l('To add "tags" click in the field, write something, and then press "Enter."')
                    )
                ),
                array(
                    'type' => 'hidden',
                    'label' => $this->l('Image Name:'),
                    'name' => 'image',
                ),
                array(
                    'type' => 'file',
                    'label' => $this->l('Image'),
                    'name' => 'image_link',
                    'display_image' => true,
                    'default' => '',
                    'desc' => $this->l('Max file size is: ').($this->max_image_size/1024/1024). 'MB',
                    'thumb' => $thumb,
                    // 'image' => $thumb,
                    // 'delete_url' => self::$currentIndex.'&'.$this->identifier.'=a&token='.$this->token.'&deleteImage=1',
                    'class' => 'leoblog_image_upload',
                ),
                array(
                    'type' => 'hidden',
                    'label' => $this->l('Thumb Name:'),
                    'name' => 'thumb',
                ),
                array(
                    'type' => 'file',
                    'label' => $this->l('Thumb image'),
                    'name' => 'thumb_link',
                    'display_image' => true,
                    'default' => '',
                    'desc' => $this->l('Max file size is: ').($this->max_image_size/1024/1024). 'MB',
                    'thumb' => $thumb_img,
                    // 'image' => $thumb_img,
                    // 'delete_url' => self::$currentIndex.'&'.$this->identifier.'=a&token='.$this->token.'&deleteImage=1',
                    'class' => 'leoblog_image_upload',
                ),
                array(
                    'type' => 'textarea',
                    'label' => $this->l('Video Code'),
                    'name' => 'video_code',
                    'rows' => 5,
                    'cols' => 30,
                    'hint' => $this->l('Enter Video Code Copying From Youtube, Vimeo').' <>;=#{}'
                ),
                array(
                    'type' => 'textarea',
                    'label' => $this->l('Blog description'),
                    'name' => 'description',
                    'autoload_rte' => true,
                    'lang' => true,
                    'rows' => 5,
                    'cols' => 30,
                    'hint' => $this->l('Invalid characters:').' <>;=#{}'
                ),
                array(
                    'type' => 'textarea',
                    'label' => $this->l('Blog Content'),
                    'name' => 'content',
                    'autoload_rte' => true,
                    'lang' => true,
                    'rows' => 5,
                    'cols' => 40,
                    'hint' => $this->l('Invalid characters:').' <>;=#{}'
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Author Name'),
                    'name' => 'author_name',
                    'desc' => $this->l('Name of author will display on front-end')
                ),
                array(
                    'type' => 'date_leoblog',
                    'label' => $this->l('Date Create'),
                    'name' => 'date_add',
                    'default' => date('Y-m-d'),
                ),
                array(
                    'type' => 'switch',
                    'label' => $this->l('Indexation (by search engines):'),
                    'name' => 'indexation',
                    'required' => false,
                    'class' => 't',
                    'is_bool' => true,
                    'values' => array(
                        array(
                            'id' => 'indexation_on',
                            'value' => 1,
                            'label' => $this->l('Enabled')
                        ),
                        array(
                            'id' => 'indexation_off',
                            'value' => 0,
                            'label' => $this->l('Disabled')
                        )
                    ),
                ),
                array(
                    'type' => 'switch',
                    'label' => $this->l('Blog favorite'),
                    'name' => 'favorite',
                    'required' => false,
                    'class' => 't',
                    'is_bool' => true,
                    'default' => '1',
                    'values' => array(
                        array(
                            'id' => 'favorite_on',
                            'value' => 1,
                            'label' => $this->l('Yes')
                        ),
                        array(
                            'id' => 'favorite_off',
                            'value' => 0,
                            'label' => $this->l('No')
                        )
                    ),
                ),
                array(
                    'type' => 'switch',
                    'label' => $this->l('Displayed:'),
                    'name' => 'active',
                    'required' => false,
                    'is_bool' => true,
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
                'class' => 'btn btn-default pull-right'
            ),
            'buttons' => array(
                'save_and_preview' => array(
                    'name' => 'saveandstay',
                    'type' => 'submit',
                    'title' => $this->l('Save and stay'),
                    'class' => 'btn btn-default pull-right',
                    'icon' => 'process-icon-save-and-stay'
                )
            )
        );

        $this->fields_form[1]['form'] = array(
            'tinymce' => true,
            'legend' => array(
                'title' => $this->l('SEO'),
                'icon' => 'icon-folder-close'
            ),
            'input' => array(
                // custom template
                array(
                    'type' => 'textarea',
                    'label' => $this->l('Meta description'),
                    'name' => 'meta_description',
                    'lang' => true,
                    'cols' => 40,
                    'rows' => 10,
                    'hint' => $this->l('Invalid characters:').' &lt;&gt;;=#{}'
                ),
                array(
                    'type' => 'tags',
                    'label' => $this->l('Meta keywords'),
                    'name' => 'meta_keywords',
                    'lang' => true,
                    'hint' => array(
                        $this->l('Invalid characters:').' &lt;&gt;;=#{}',
                        $this->l('To add "tags" click in the field, write something, and then press "Enter."')
                    )
                ),
            )
        );

        Media::addJsDef(array(
            'leoblog_del_img_txt' => $this->l('Delete'),
            'leoblog_del_img_mess' => $this->l('Are you sure delete this?'),
            'PS_ALLOW_ACCENTED_CHARS_URL' => (int)Configuration::get('PS_ALLOW_ACCENTED_CHARS_URL'),
        ));
        return parent::renderForm();
    }

    public function renderList()
    {
        $this->toolbar_title = $this->l('Blogs Management');
        $this->toolbar_btn['new'] = array(
            'href' => self::$currentIndex.'&add'.$this->table.'&token='.$this->token,
            'desc' => $this->l('Add new')
        );

        return parent::renderList();
    }

    public function postProcess()
    {
        if (Tools::isSubmit('viewblog') && ($id_leoblog_blog = (int)Tools::getValue('id_leoblog_blog')) && ($blog = new LeoBlogBlog($id_leoblog_blog, $this->context->language->id)) && Validate::isLoadedObject($blog)) {
            $this->redirect_after = $this->getPreviewUrl($blog);
        }
                
        if (Tools::isSubmit('submitAddleoblog_blog') || Tools::isSubmit('submitAddleoblog_blogAndPreview') || Tools::isSubmit('saveandstay')) {
            if (count($this->errors)) {
                return false;
            }
            
            parent::validateRules();
            
            $id_shop = (int)Context::getContext()->shop->id;
            if (!$id_leoblog_blog = (int)Tools::getValue('id_leoblog_blog')) {
                # ADD
                $blog = new LeoBlogBlog();
                $this->copyFromPost($blog, 'blog');

                if (isset($_FILES['image_link']) && isset($_FILES['image_link']['tmp_name']) && !empty($_FILES['image_link']['tmp_name'])) {
                    if (!$image = $this->_uploadImage($_FILES['image_link'], '', '')) {
                        return false;
                    }
                    $blog->image = $image;
                }
                
                if (isset($_FILES['thumb_link']) && isset($_FILES['thumb_link']['tmp_name']) && !empty($_FILES['thumb_link']['tmp_name'])) {
                    if (!$thumb = $this->_uploadImage($_FILES['thumb_link'], '', '', true)) {
                        return false;
                    }
                    $blog->thumb = $thumb;
                }
                $blog->id_employee = $this->context->employee->id;

                if (!$blog->validateFields(false) || !$blog->validateFieldsLang(false)) {
                    return false;
                } elseif (!$blog->add(false)) {
                    $this->errors[] = $this->l('An error occurred while creating an object.').' <b>'.$this->table.' ('.Db::getInstance()->getMsgError().')</b>';
                } else {
                    # validate module
                    $this->updateAssoShop($blog->id);
                }
            } else {
                # EDIT
                $blog = new LeoBlogBlog($id_leoblog_blog);
                $this->copyFromPost($blog, 'blog');
                                        
                if (isset($_FILES['image_link']) && isset($_FILES['image_link']['tmp_name']) && !empty($_FILES['image_link']['tmp_name'])) {
                    if (file_exists(_LEOBLOG_CACHE_IMG_DIR_.'b/'.$id_shop.'/'.$id_leoblog_blog)) {
                        LeoBlogHelper::rrmdir(_LEOBLOG_CACHE_IMG_DIR_.'b/'.$id_shop.'/'.$id_leoblog_blog);
                    }
                    if (!$image = $this->_uploadImage($_FILES['image_link'], '', '')) {
                        return false;
                    }
                    
                    $blog->image = $image;
                }
                
                if (isset($_FILES['thumb_link']) && isset($_FILES['thumb_link']['tmp_name']) && !empty($_FILES['thumb_link']['tmp_name'])) {
                    if (file_exists(_LEOBLOG_CACHE_IMG_DIR_.'b/'.$id_shop.'/'.$id_leoblog_blog)) {
                        LeoBlogHelper::rrmdir(_LEOBLOG_CACHE_IMG_DIR_.'b/'.$id_shop.'/'.$id_leoblog_blog);
                    }
                    if (!$thumb = $this->_uploadImage($_FILES['thumb_link'], '', '', true)) {
                        return false;
                    }
                    $blog->thumb = $thumb;
                }
                if (!$blog->update()) {
                    $this->errors[] = $this->l('An error occurred while creating an object.').' <b>'.$this->table.' ('.Db::getInstance()->getMsgError().')</b>';
                } else {
                    # validate module
                    $this->updateAssoShop($blog->id);
                }
            }

            if (Tools::isSubmit('submitAddblogAndPreview')) {
                # validate module
                $this->redirect_after = $this->previewUrl($blog);
            } elseif (Tools::isSubmit('saveandstay')) {
                # validate module
                Tools::redirectAdmin(self::$currentIndex.'&'.$this->identifier.'='.$blog->id.'&conf=4&update'.$this->table.'&token='.Tools::getValue('token'));
            } else {
                # validate module
                Tools::redirectAdmin(self::$currentIndex.'&id_leoblogcat='.$blog->id_leoblogcat.'&conf=4&token='.Tools::getValue('token'));
            }
        } else {
            parent::postProcess(true);
        }
    }

    protected function _uploadImage($image, $image_w = null, $image_h = null, $thumb_image = false)
    {
        $folder_theme = _PS_ROOT_DIR_.'/themes/'.LeoBlogHelper::getThemeName().'/';
        
        $res = false;
        $id_shop = (int)Context::getContext()->shop->id;
        LeoBlogHelper::buildFolder($id_shop);
        if (is_array($image) && (ImageManager::validateUpload($image, $this->max_image_size) === false) && ($tmp_name = tempnam(_PS_TMP_IMG_DIR_, 'PS')) && move_uploaded_file($image['tmp_name'], $tmp_name)) {
            $type = Tools::strtolower(Tools::substr(strrchr($image['name'], '.'), 1));
            if ($thumb_image) {
                $img_name = 't-'.Tools::strtolower(str_replace('.'.$type, '', $image['name']).'.'.$type);
            } else {
                $img_name = 'b-'.Tools::strtolower(str_replace('.'.$type, '', $image['name']).'.'.$type);
            }
            
            if (file_exists($folder_theme.'assets/img/modules/leoblog/'.$id_shop.'/b/'.$img_name)) {
                @unlink($folder_theme.'assets/img/modules/leoblog/'.$id_shop.'/b/'.$img_name);
            }
            $image_type = 'jpg';
            if (Configuration::get('LEOBLOG_IMAGE_TYPE') != null) {
                $image_type = Configuration::get('LEOBLOG_IMAGE_TYPE');
            }
            // Configuration::set('PS_IMAGE_QUALITY', 'png_all');
            Configuration::set('PS_IMAGE_QUALITY', $image_type);
            if (ImageManager::resize($tmp_name, $folder_theme.'assets/img/modules/leoblog/'.$id_shop.'/b/'.$img_name, (int)$image_w, (int)$image_h)) {
                $res = true;
            }
        }

//        if (isset($temp_name))
//                @unlink($tmp_name);
        if (!$res) {
            # validate module
            return false;
        }

        return $img_name;
    }

    public function setMedia($isNewTheme = false)
    {
        parent::setMedia($isNewTheme);
        $this->addJqueryUi('ui.widget');
        $this->addJqueryPlugin('tagify');
        $media_dir = $this->module->getMediaDir();
        if (file_exists(_PS_THEME_DIR_.'js/modules/leoblog/assets/admin/form.js')) {
            $this->context->controller->addJS(__PS_BASE_URI__.'modules/leoblog/assets/admin/form.js');
        } else {
            $this->context->controller->addJS(__PS_BASE_URI__.$media_dir.'js/admin/form.js');
        }
        
        if (file_exists(_PS_THEME_DIR_.'css/modules/leoblog/assets/admin/form.css')) {
            $this->context->controller->addCss(__PS_BASE_URI__.'modules/leoblog/assets/admin/form.css');
        } else {
            $this->context->controller->addCss(__PS_BASE_URI__.$media_dir.'css/admin/form.css');
        }
    }

    public function autoCorrect()
    {
        // check `favorite` name column, if not exist auto add
        if (Db::getInstance()->executeS('SHOW TABLES LIKE \'%leoblog_blog%\'') && count(Db::getInstance()->executes('SELECT "favorite" FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = "'._DB_NAME_.'" AND TABLE_NAME = "'._DB_PREFIX_.'leoblog_blog" AND COLUMN_NAME = "favorite"'))<1) {
            Db::getInstance()->execute('ALTER TABLE `'._DB_PREFIX_.'leoblog_blog` ADD `favorite` tinyint(1) NOT NULL');
        }
        // check `subtitle` name column, if not exist auto add
        if (Db::getInstance()->executeS('SHOW TABLES LIKE \'%leoblog_blog_lang%\'') && count(Db::getInstance()->executes('SELECT "subtitle" FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = "'._DB_NAME_.'" AND TABLE_NAME = "'._DB_PREFIX_.'leoblog_blog_lang" AND COLUMN_NAME = "subtitle"'))<1) {
            Db::getInstance()->execute('ALTER TABLE `'._DB_PREFIX_.'leoblog_blog_lang` ADD `subtitle` varchar(250) NULL');
        }
    }

    public function ajaxProcessUpdateblogPositions()
    {
        if ($this->tabAccess['edit'] === '1') {
            $id_leoblog_blog = (int)Tools::getValue('id_leoblog_blog');
            $id_category = (int)Tools::getValue('id_leoblog_blog_category');
            $way = (int)Tools::getValue('way');
            $positions = Tools::getValue('blog');
            if (is_array($positions)) {
                foreach ($positions as $key => $value) {
                    $pos = explode('_', $value);
                    if ((isset($pos[1]) && isset($pos[2])) && ($pos[1] == $id_category && $pos[2] == $id_leoblog_blog)) {
                        $position = $key;
                        break;
                    }
                }
            }
            $blog = new blog($id_leoblog_blog);
            if (Validate::isLoadedObject($blog)) {
                if (isset($position) && $blog->updatePosition($way, $position)) {
                    die(true);
                } else {
                    die('{"hasError" : true, "errors" : "Can not update blog position"}');
                }
            } else {
                die('{"hasError" : true, "errors" : "This blog can not be loaded"}');
            }
        }
    }

    public function ajaxProcessUpdateblogCategoriesPositions()
    {
        if ($this->tabAccess['edit'] === '1') {
            $id_leoblog_blog_category_to_move = (int)Tools::getValue('id_leoblog_blog_category_to_move');
            $id_leoblog_blog_category_parent = (int)Tools::getValue('id_leoblog_blog_category_parent');
            $way = (int)Tools::getValue('way');
            $positions = Tools::getValue('blog_category');
            if (is_array($positions)) {
                foreach ($positions as $key => $value) {
                    $pos = explode('_', $value);
                    if ((isset($pos[1]) && isset($pos[2])) && ($pos[1] == $id_leoblog_blog_category_parent && $pos[2] == $id_leoblog_blog_category_to_move)) {
                        $position = $key;
                        break;
                    }
                }
            }
            $blog_category = new blogCategory($id_leoblog_blog_category_to_move);
            if (Validate::isLoadedObject($blog_category)) {
                if (isset($position) && $blog_category->updatePosition($way, $position)) {
                    die(true);
                } else {
                    die('{"hasError" : true, "errors" : "Can not update blog categories position"}');
                }
            } else {
                die('{"hasError" : true, "errors" : "This blog category can not be loaded"}');
            }
        }
    }

    public function ajaxProcessPublishblog()
    {
        if ($this->tabAccess['edit'] === '1') {
            if ($id_leoblog_blog = (int)Tools::getValue('id_leoblog_blog')) {
                $bo_blog_url = dirname($_SERVER['PHP_SELF']).'/index.php?tab=AdminblogContent&id_leoblog_blog='.(int)$id_leoblog_blog.'&updateblog&token='.$this->token;

                if (Tools::getValue('redirect')) {
                    die($bo_blog_url);
                }

                $blog = new blog((int)(Tools::getValue('id_leoblog_blog')));
                if (!Validate::isLoadedObject($blog)) {
                    die('error: invalid id');
                }

                $blog->active = 1;
                if ($blog->save()) {
                    die($bo_blog_url);
                } else {
                    die('error: saving');
                }
            } else {
                die('error: parameters');
            }
        }
    }
}
