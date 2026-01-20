{**
 * Compralosubito24 Blog - Blog Post Template
 *}

<div class="cs24blog-post-wrapper">
    {if isset($error) && $error}
        <div class="alert alert-warning">
            {l s='Blog post not found' mod='cs24blog'}
        </div>
    {else}
        <article class="cs24blog-post" itemscope itemtype="http://schema.org/BlogPosting">
            <header class="post-header">
                <h1 class="post-title" itemprop="headline">{$blog->title|escape:'html':'UTF-8'}</h1>

                <div class="post-meta">
                    <span class="post-date" itemprop="datePublished" content="{$blog->date_add}">
                        <i class="icon-calendar"></i> {dateFormat date=$blog->date_add full=0}
                    </span>

                    {if isset($blog->author)}
                        <span class="post-author" itemprop="author">
                            <i class="icon-user"></i>
                            <a href="{$blog->author_link|escape:'html':'UTF-8'}">{$blog->author|escape:'html':'UTF-8'}</a>
                        </span>
                    {/if}

                    {if isset($blog->category_title)}
                        <span class="post-category">
                            <i class="icon-folder"></i>
                            <a href="{$blog->category_link|escape:'html':'UTF-8'}">{$blog->category_title|escape:'html':'UTF-8'}</a>
                        </span>
                    {/if}

                    {if $blog->hits}
                        <span class="post-views">
                            <i class="icon-eye"></i> {$blog->hits} {l s='views' mod='cs24blog'}
                        </span>
                    {/if}
                </div>
            </header>

            {if $blog->preview_url}
                <div class="post-image">
                    <img src="{$blog->preview_url|escape:'html':'UTF-8'}" alt="{$blog->title|escape:'html':'UTF-8'}" itemprop="image" class="img-fluid"/>
                </div>
            {/if}

            {if $blog->description}
                <div class="post-description" itemprop="description">
                    {$blog->description nofilter}
                </div>
            {/if}

            <div class="post-content" itemprop="articleBody">
                {$blog->content nofilter}
            </div>

            {if isset($tags) && $tags}
                <div class="post-tags">
                    <i class="icon-tag"></i>
                    {foreach from=$tags item=tag}
                        <a href="{$tag.link|escape:'html':'UTF-8'}" class="tag">{$tag.tag|escape:'html':'UTF-8'}</a>
                    {/foreach}
                </div>
            {/if}

            {if isset($_social)}
                {include file="$_social"}
            {/if}

            <div class="post-comments">
                {if $config->get('item_comment_engine', 'local') == 'local' && isset($_local_comment)}
                    {include file="$_local_comment"}
                {elseif $config->get('item_comment_engine', 'local') == 'facebook' && isset($_facebook_comment)}
                    {include file="$_facebook_comment"}
                {elseif $config->get('item_comment_engine', 'local') == 'diquis' && isset($_diquis_comment)}
                    {include file="$_diquis_comment"}
                {/if}
            </div>
        </article>

        {if isset($samecats) && $samecats}
            <div class="related-posts">
                <h3>{l s='Related Posts' mod='cs24blog'}</h3>
                <div class="row">
                    {foreach from=$samecats item=related_blog}
                        <div class="col-md-4">
                            <article class="related-post">
                                {if $related_blog.image}
                                    <a href="{$related_blog.link|escape:'html':'UTF-8'}">
                                        <img src="{$related_blog.image|escape:'html':'UTF-8'}" alt="{$related_blog.title|escape:'html':'UTF-8'}" class="img-fluid"/>
                                    </a>
                                {/if}
                                <h4>
                                    <a href="{$related_blog.link|escape:'html':'UTF-8'}">{$related_blog.title|escape:'html':'UTF-8'}</a>
                                </h4>
                            </article>
                        </div>
                    {/foreach}
                </div>
            </div>
        {/if}
    {/if}
</div>
