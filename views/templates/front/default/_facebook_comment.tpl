{**
 * Compralosubito24 Blog - Facebook Comments Template
 *}

{if $config->get('item_comment_engine', 'local') == 'facebook'}
    <div class="blog-facebook-comments">
        <h3>{l s='Comments' mod='cs24blog'}</h3>
        <div id="fb-root"></div>
        <script async defer crossorigin="anonymous" src="https://connect.facebook.net/en_US/sdk.js#xfbml=1&version=v12.0"></script>
        <div class="fb-comments" data-href="{$blog_link|escape:'html':'UTF-8'}" data-width="100%" data-numposts="10"></div>
    </div>
{/if}
