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

class Dispatcher extends DispatcherCore
{
	protected function loadRoutes($id_shop = null)
    {
        $context = Context::getContext();

        if (isset($context->shop) && $id_shop === null) {
            $id_shop = (int) $context->shop->id;
        }

        // Load custom routes from modules
        $modules_routes = Hook::exec('moduleRoutes', ['id_shop' => $id_shop], null, true, false);
        if (is_array($modules_routes) && count($modules_routes)) {
            foreach ($modules_routes as $module_route) {
                if (is_array($module_route) && count($module_route)) {
                    foreach ($module_route as $route => $route_details) {
                        if (array_key_exists('controller', $route_details)
                            && array_key_exists('rule', $route_details)
                            && array_key_exists('keywords', $route_details)
                            && array_key_exists('params', $route_details)
                        ) {
                            if (!isset($this->default_routes[$route])) {
                                $this->default_routes[$route] = [];
                            }
                            if ($route == 'module-leoblog-list' || $route == 'module-leoblog-blog' || $route == 'module-leoblog-category') {
                                $this->default_routes = array_merge_recursive(array("{$route}" => $route_details), $this->default_routes);
                            } else {
                                $this->default_routes[$route] = array_merge($this->default_routes[$route], $route_details);
                            }
                        }
                    }
                }
            }
        }

        $language_ids = Language::getIDs();

        if (isset($context->language) && !in_array($context->language->id, $language_ids)) {
            $language_ids[] = (int) $context->language->id;
        }

		include_once(_PS_MODULE_DIR_.'leoblog/loader.php');
        $config = LeoBlogConfig::getInstance();
            if (!isset($this->default_routes['module-leoblog-list'])) {
            	$this->default_routes['module-leoblog-list'] = array(
		            'controller' => 'list',
		            'rule' => _LEO_BLOG_REWRITE_ROUTE_.'.html',
		            'keywords' => array(
		            ),
		            'params' => array(
		                'fc' => 'module',
		                'module' => 'leoblog'
		            )
		        );
            }
            if ($config->get('url_use_id', 1) && (!Tools::getIsset('configure') || (Tools::getIsset('configure') && Tools::getValue('configure') != 'gsitemap'))) {
	            // URL HAVE ID
	            if (!isset($this->default_routes['module-leoblog-blog'])) {
	            	$this->default_routes['module-leoblog-blog'] = array(
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
	            }
	            if (!isset($this->default_routes['module-leoblog-category'])) {
	            	$this->default_routes['module-leoblog-category'] = array(
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
	            }
	        } elseif (!Tools::getIsset('configure') || (Tools::getIsset('configure') && Tools::getValue('configure') != 'gsitemap')) {
	            // REMOVE ID FROM URL
	            $category_rewrite = 'category_rewrite'.'_'.Context::getContext()->language->id;
	            $category_rewrite = $config->get($category_rewrite, 'category');
	            $detail_rewrite = 'detail_rewrite'.'_'.Context::getContext()->language->id;
	            $detail_rewrite = $config->get($detail_rewrite, 'detail');

	            if (!isset($this->default_routes['module-leoblog-blog'])) {
	            	$this->default_routes['module-leoblog-blog'] = array(
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
	            }
	            if (!isset($this->default_routes['module-leoblog-category'])) {
	            	$this->default_routes['module-leoblog-category'] = array(
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
	        }

        // Set default routes
        foreach ($this->default_routes as $id => $route) {
            $route = $this->computeRoute(
                $route['rule'],
                $route['controller'],
                $route['keywords'],
                isset($route['params']) ? $route['params'] : []
            );
            foreach ($language_ids as $id_lang) {
                // the default routes are the same, whatever the language
                $this->routes[$id_shop][$id_lang][$id] = $route;
            }
        }

        // Load the custom routes prior the defaults to avoid infinite loops
        if ($this->use_routes) {
            // Load routes from meta table
            $sql = 'SELECT m.page, ml.url_rewrite, ml.id_lang
					FROM `' . _DB_PREFIX_ . 'meta` m
					LEFT JOIN `' . _DB_PREFIX_ . 'meta_lang` ml ON (m.id_meta = ml.id_meta' . Shop::addSqlRestrictionOnLang('ml', (int) $id_shop) . ')
					ORDER BY LENGTH(ml.url_rewrite) DESC';
            if ($results = Db::getInstance()->executeS($sql)) {
                foreach ($results as $row) {
                    if ($row['url_rewrite']) {
                        $this->addRoute(
                            $row['page'],
                            $row['url_rewrite'],
                            $row['page'],
                            $row['id_lang'],
                            [],
                            [],
                            $id_shop
                        );
                    }
                }
            }

            // Set default empty route if no empty route (that's weird I know)
            if (!$this->empty_route) {
                $this->empty_route = [
                    'routeID' => 'index',
                    'rule' => '',
                    'controller' => 'index',
                ];
            }

            // Load custom routes
            foreach ($this->default_routes as $route_id => $route_data) {
                if ($custom_route = Configuration::get('PS_ROUTE_' . $route_id, null, null, $id_shop)) {
                    if (isset($context->language) && !in_array($context->language->id, $language_ids)) {
                        $language_ids[] = (int) $context->language->id;
                    }

                    $route = $this->computeRoute(
                        $custom_route,
                        $route_data['controller'],
                        $route_data['keywords'],
                        isset($route_data['params']) ? $route_data['params'] : []
                    );
                    foreach ($language_ids as $id_lang) {
                        // those routes are the same, whatever the language
                        $this->routes[$id_shop][$id_lang][$route_id] = $route;
                    }
                }
                if (strpos($route_id, 'leoblog') !== false) {
                    if (isset($context->language) && !in_array($context->language->id, $language_ids)) {
                        $language_ids[] = (int) $context->language->id;
                    }
                    $blog_controller = explode('-', $route_id)[count(explode('-', $route_id))-1];
                    $rule = '';
                    $keywords = [];
                    $keywords = [
                        'id' => array('regexp' => '[0-9]+', 'param' => 'id'),
                        'rewrite' => array('regexp' => '[_a-zA-Z0-9-\pL]*', 'param' => 'rewrite'),
                    ];

                    foreach ($language_ids as $id_lang) {
                        $category_rewrite = $config->get('category_rewrite'.'_'.$id_lang, 'category');
                        $detail_rewrite = $config->get('detail_rewrite'.'_'.$id_lang, 'detail');
                        if ($config->get('url_use_id', 1) && (!Tools::getIsset('configure') || (Tools::getIsset('configure') && Tools::getValue('configure') != 'gsitemap'))) {
                            switch ($blog_controller) {
                                case 'list':
                                    $rule = '.html';
                                    $keywords = [];
                                    break;
                                case 'blog':
                                    $rule = '/{rewrite}-b{id}.html';
                                    break;
                                case 'category':
                                    $rule = '/{rewrite}-c{id}.html';
                                    break;
                            }
                        } elseif (!Tools::getIsset('configure') || (Tools::getIsset('configure') && Tools::getValue('configure') != 'gsitemap')) {
                            switch ($blog_controller) {
                                case 'list':
                                    $rule = '.html';
                                    $keywords = [];
                                    break;
                                case 'blog':
                                    $rule = '/'.$detail_rewrite.'/{rewrite}.html';
                                    break;
                                case 'category':
                                    $rule = '/'.$category_rewrite.'/{rewrite}.html';
                                    break;
                            }
                        }
                        $route = $this->computeRoute(
                            $config->get('link_rewrite_'.$id_lang, 'blog').$rule,
                            $blog_controller,
                            $keywords,
                            array(
                                'fc' => 'module',
                                'module' => 'leoblog'
                            )
                        );
                        $this->routes[$id_shop][$id_lang][$route_id] = $route;
                    }
                }
            }
        }
    }
}