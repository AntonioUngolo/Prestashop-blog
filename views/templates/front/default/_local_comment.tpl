{**
 * Compralosubito24 Blog - Local Comments Template
 *}

<div class="blog-comments">
    <h3>{l s='Comments' mod='cs24blog'} {if isset($blog_count_comment)}({$blog_count_comment}){/if}</h3>

    {if isset($comments) && $comments}
        <div class="comments-list">
            {foreach from=$comments item=comment}
                <div class="comment" id="comment-{$comment.id_cs24_blog_comment}">
                    <div class="comment-meta">
                        <strong class="comment-author">{$comment.name|escape:'html':'UTF-8'}</strong>
                        <span class="comment-date">{dateFormat date=$comment.date_add full=0}</span>
                    </div>
                    <div class="comment-content">
                        {$comment.comment|escape:'html':'UTF-8'|nl2br}
                    </div>
                </div>
            {/foreach}
        </div>

        {if isset($_pagination)}
            {include file="$_pagination"}
        {/if}
    {else}
        <p class="no-comments">{l s='No comments yet. Be the first to comment!' mod='cs24blog'}</p>
    {/if}

    {if $config->get('item_comment_engine', 'local') == 'local'}
        <div class="comment-form">
            <h4>{l s='Leave a comment' mod='cs24blog'}</h4>
            <form action="{$blog_link|escape:'html':'UTF-8'}" method="post" class="form-horizontal">
                <div class="form-group">
                    <label for="comment_name">{l s='Name' mod='cs24blog'} *</label>
                    <input type="text" name="name" id="comment_name" class="form-control" required/>
                </div>
                <div class="form-group">
                    <label for="comment_email">{l s='Email' mod='cs24blog'} *</label>
                    <input type="email" name="email" id="comment_email" class="form-control" required/>
                </div>
                <div class="form-group">
                    <label for="comment_content">{l s='Comment' mod='cs24blog'} *</label>
                    <textarea name="comment" id="comment_content" class="form-control" rows="5" required></textarea>
                </div>
                <div class="form-group">
                    <button type="submit" name="submitComment" class="btn btn-primary">
                        {l s='Submit Comment' mod='cs24blog'}
                    </button>
                </div>
            </form>
        </div>
    {/if}
</div>
