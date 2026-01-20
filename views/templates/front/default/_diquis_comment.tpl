{**
 * Compralosubito24 Blog - Disqus Comments Template
 *}

{if $config->get('item_comment_engine', 'local') == 'diquis'}
    <div class="blog-disqus-comments">
        <h3>{l s='Comments' mod='cs24blog'}</h3>
        <div id="disqus_thread"></div>
        <script>
            var disqus_config = function () {
                this.page.url = '{$blog_link|escape:'javascript':'UTF-8'}';
                this.page.identifier = 'cs24blog_{$id_cs24_blog_blog}';
            };
            (function() {
                var d = document, s = d.createElement('script');
                s.src = 'https://YOUR-DISQUS-SHORTNAME.disqus.com/embed.js';
                s.setAttribute('data-timestamp', +new Date());
                (d.head || d.body).appendChild(s);
            })();
        </script>
        <noscript>
            {l s='Please enable JavaScript to view the comments powered by Disqus.' mod='cs24blog'}
        </noscript>
    </div>
{/if}
