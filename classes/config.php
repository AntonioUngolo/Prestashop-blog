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

class Cs24BlogConfig
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
            $instance = new Cs24BlogConfig();
        }
        return $instance;
    }

    public function __construct()
    {
        //get data for template
        if (Configuration::get(Tools::strtoupper(_CS24_BLOG_PREFIX_.'template_current')) == 'default' || Tools::getValue('bloglayout') == 'default') {
            $data = self::getConfigValue('cfg_global');
        } else {
            $data = self::getConfigValue('cfg_global_'.Configuration::get(Tools::strtoupper(_CS24_BLOG_PREFIX_.'template_current')));
        }
        if ($data && $tmp = json_decode($data, true)) {
            include_once(_PS_MODULE_DIR_.'cs24blog/libs/Helper.php');
            $tmp['social_code'] = Cs24BlogHelper::correctDeCodeData($tmp['social_code']);
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
        return Tools::strtoupper(_CS24_BLOG_PREFIX_.$name);
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
