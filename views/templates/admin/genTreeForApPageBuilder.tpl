{* 
* @Module Name: Compralosubito24 Blog
* @Website: leotheme.com.com - prestashop template provider
* @author Leotheme <leotheme@gmail.com>
* @copyright  Leotheme
* @description: Content Management
*}

<ol class="level{$level|escape:'htmlall':'UTF-8'}">
    {foreach from=$data item=$menu}
        <li id="list_{$menu.id_cs24_blog_cat|escape:'htmlall':'UTF-8'}">
            <input type="checkbox" value="{$menu.randkey|escape:'htmlall':'UTF-8'}" name="chk_cat[]" id="chk-{$menu.id_cs24_blog_cat|escape:'htmlall':'UTF-8'}" {if $menu.id_cs24_blog_cat|array_search:$select !== false}checked="checked"{/if}/>
            <label for="chk-{$menu.id_cs24_blog_cat|escape:'htmlall':'UTF-8'}">{$menu.title|escape:'htmlall':'UTF-8'} (ID:{$menu.id_cs24_blog_cat|escape:'htmlall':'UTF-8'})</label>
            {if $menu.id_cs24_blog_cat != $parent}
                {$model_cs24_blog_cat->genTreeForApPageBuilder($menu.id_cs24_blog_cat, $level + 1, $select)|escape:'htmlall':'UTF-8'}
            {/if}
        </li>
    {/foreach}
</ol>
