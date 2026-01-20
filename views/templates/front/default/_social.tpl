{**
 * Compralosubito24 Blog - Social Sharing Template
 *}

{if $config->get('item_show_social', 1)}
    <div class="post-social-share">
        <h4>{l s='Share this post' mod='cs24blog'}</h4>
        <div class="social-buttons">
            <a href="https://www.facebook.com/sharer/sharer.php?u={$blog_link|escape:'url'}" target="_blank" class="btn btn-facebook" title="{l s='Share on Facebook' mod='cs24blog'}">
                <i class="icon-facebook"></i> Facebook
            </a>
            <a href="https://twitter.com/intent/tweet?url={$blog_link|escape:'url'}&text={$blog->title|escape:'url'}" target="_blank" class="btn btn-twitter" title="{l s='Share on Twitter' mod='cs24blog'}">
                <i class="icon-twitter"></i> Twitter
            </a>
            <a href="https://www.linkedin.com/shareArticle?mini=true&url={$blog_link|escape:'url'}&title={$blog->title|escape:'url'}" target="_blank" class="btn btn-linkedin" title="{l s='Share on LinkedIn' mod='cs24blog'}">
                <i class="icon-linkedin"></i> LinkedIn
            </a>
            <a href="https://pinterest.com/pin/create/button/?url={$blog_link|escape:'url'}&description={$blog->title|escape:'url'}" target="_blank" class="btn btn-pinterest" title="{l s='Share on Pinterest' mod='cs24blog'}">
                <i class="icon-pinterest"></i> Pinterest
            </a>
        </div>
    </div>
{/if}
