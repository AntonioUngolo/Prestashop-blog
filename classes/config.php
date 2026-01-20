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

class LeoBlogConfig
{
    public $params;
    public $cat_image_dir = '';
    /** @var int id_lang current language in for, while */
    public $cur_id_lang = '';
    /** @var int id_lang current language in for, while */
    public $cur_prefix_rewrite = '';

    public static function getInstance()
    {
        static $instance;
        if (!$instance) {
            # validate module
            $instance = new LeoBlogConfig();
        }
        return $instance;
    }

    public function __construct()
    {
        //get data for template
        if (Configuration::get(Tools::strtoupper(_LEO_BLOG_PREFIX_.'template_current')) == 'default' || Tools::getValue('bloglayout') == 'default') {
            $data = self::getConfigValue('cfg_global');
        } else {
            $data = self::getConfigValue('cfg_global_'.Configuration::get(Tools::strtoupper(_LEO_BLOG_PREFIX_.'template_current')));
        }
        if ($data && $tmp = json_decode($data, true)) {
            include_once(_PS_MODULE_DIR_.'leoblog/libs/Helper.php');
            $tmp['social_code'] = LeoBlogHelper::correctDeCodeData($tmp['social_code']);
            $this->params = $tmp;
        }
    }

    public function mergeParams($params)
    {
        # validate module
        unset($params);
    }

    public function setVar($key, $value)
    {
        $this->params[$key] = $value;
    }

    public function get($name, $value = '')
    {
        if (isset($this->params[$name])) {
            # validate module
            return $this->params[$name];
        }
        return $value;
    }

    public static function getConfigName($name)
    {
        return Tools::strtoupper(_LEO_BLOG_PREFIX_.$name);
    }

    public static function updateConfigValue($name, $value = '')
    {
        Configuration::updateValue(self::getConfigName($name), $value, true);
    }

    public static function getConfigValue($name)
    {
        return Configuration::get(self::getConfigName($name));
    }
}
