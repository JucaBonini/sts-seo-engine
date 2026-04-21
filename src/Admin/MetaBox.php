<?php

namespace STSSearch\Admin;

if (!defined('ABSPATH')) exit;

class MetaBox
{
    public function __construct()
    {
        add_action('add_meta_boxes', [$this, 'add_seo_meta_box']);
        add_action('save_post', [$this, 'save_seo_data']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_assets']);
    }

    public function add_seo_meta_box()
    {
        $screens = ['post', 'page', 'artigo', 'cardapio'];
        foreach ($screens as $screen) {
            add_meta_box(
                'sts_seo_meta_box',
                'SEO Engine Pro - Snippet Editor',
                [$this, 'render_meta_box'],
                $screen,
                'normal',
                'high'
            );
        }
    }

    public function render_meta_box($post)
    {
        $seo_title = get_post_meta($post->ID, '_sts_seo_title', true);
        $seo_desc = get_post_meta($post->ID, '_sts_seo_desc', true);
        $focus_kw = get_post_meta($post->ID, '_sts_focus_keyword', true);
        
        wp_nonce_field('sts_seo_save_action', 'sts_seo_nonce');
        ?>
        <div class="sts-seo-editor">
            <div style="display: flex; gap: 20px;">
                <!-- Left: Editor -->
                <div style="flex: 2;">
                    <!-- Snippet Preview -->
                    <div class="sts-seo-preview">
                        <div class="preview-url"><?php echo home_url('/'); ?>...</div>
                        <div class="preview-title" id="sts-preview-title"><?php echo $seo_title ?: get_the_title($post->ID); ?></div>
                        <div class="preview-desc" id="sts-preview-desc"><?php echo $seo_desc ?: 'Insira uma descrição meta para ver como este post aparecerá nos resultados de pesquisa.'; ?></div>
                    </div>

                    <div class="sts-seo-fields">
                        <div class="field-group">
                            <label>Palavra-chave Foco</label>
                            <input type="text" id="sts_focus_keyword_input" name="sts_focus_keyword" value="<?php echo esc_attr($focus_kw); ?>" placeholder="Ex: Bolo de Chocolate">
                        </div>

                        <div class="field-group">
                            <label>Título SEO</label>
                            <input type="text" id="sts_seo_title_input" name="sts_seo_title" value="<?php echo esc_attr($seo_title); ?>" placeholder="Título customizado para o Google">
                            <div class="char-count"><span id="title-count">0</span> / 60</div>
                        </div>

                        <div class="field-group">
                            <label>Meta Descrição</label>
                            <textarea id="sts_seo_desc_input" name="sts_seo_desc" rows="3" placeholder="Resumo para o Google..."><?php echo esc_textarea($seo_desc); ?></textarea>
                            <div class="char-count"><span id="desc-count">0</span> / 160</div>
                        </div>

                        <div class="field-group" style="margin-top: 10px; padding: 10px; background: #fff5f5; border-radius: 6px; border: 1px solid #fed7d7;">
                            <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; color: #c53030;">
                                <input type="checkbox" name="sts_seo_noindex" value="1" <?php checked(get_post_meta($post->ID, '_sts_seo_noindex', true), '1'); ?>>
                                <b>Remover do Google (No Index)</b>
                            </label>
                            <p style="margin: 5px 0 0 24px; font-size: 10px; color: #9b2c2c;">Marque esta opção para que esta página NÃO apareça nos resultados de busca.</p>
                        </div>
                    </div>
                </div>

                <!-- Right: Analysis -->
                <div style="flex: 1; min-width: 250px;">
                    <div class="sts-seo-analysis">
                        <h3>Análise SEO Pro</h3>
                        <ul id="sts-analysis-list">
                            <li id="check-kw-title">Palavra-chave no título</li>
                            <li id="check-title-len">Tamanho do título</li>
                            <li id="check-desc-len">Tamanho da descrição</li>
                            <li id="check-kw-content">Palavra-chave no conteúdo</li>
                            <li id="check-url">URL Amigável</li>
                        </ul>
                        <div class="seo-score">
                            <span>Score SEO</span>
                            <div class="score-bar"><div id="score-fill" style="width: 0%;"></div></div>
                            <b id="score-text">0/100</b>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <style>
            .sts-seo-editor { padding: 10px; }
            .sts-seo-preview { background: #fff; border: 1px solid #ddd; padding: 15px; border-radius: 8px; margin-bottom: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
            .preview-url { color: #202124; font-size: 14px; margin-bottom: 4px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
            .preview-title { color: #1a0dab; font-size: 20px; line-height: 1.3; margin-bottom: 3px; }
            .preview-desc { color: #4d5156; font-size: 14px; line-height: 1.58; }
            
            .sts-seo-fields { display: flex; flex-direction: column; gap: 15px; }
            .field-group label { display: block; font-weight: 600; margin-bottom: 5px; font-size: 12px; color: #50575e; }
            .field-group input, .field-group textarea { width: 100%; padding: 8px; border: 1px solid #ccd0d4; border-radius: 4px; }
            .char-count { font-size: 10px; color: #646970; text-align: right; margin-top: 2px; }

            .sts-seo-analysis { background: #f6f7f7; padding: 15px; border-radius: 8px; border: 1px solid #ccd0d4; }
            .sts-seo-analysis h3 { margin: 0 0 15px 0; font-size: 14px; }
            .sts-seo-analysis ul { list-style: none; padding: 0; margin: 0; }
            .sts-seo-analysis li { font-size: 12px; padding-left: 20px; margin-bottom: 8px; position: relative; color: #50575e; }
            .sts-seo-analysis li::before { content: '○'; position: absolute; left: 0; color: #ccd0d4; font-weight: bold; }
            .sts-seo-analysis li.pass::before { content: '✓'; color: #00a32a; }
            .sts-seo-analysis li.fail::before { content: '✕'; color: #d63638; }
            .sts-seo-analysis li.warn::before { content: '⚠'; color: #dba617; }
            
            .seo-score { margin-top: 20px; padding-top: 15px; border-top: 1px solid #ccd0d4; }
            .seo-score span { font-size: 11px; display: block; margin-bottom: 5px; font-weight: 600; }
            .score-bar { background: #ccd0d4; height: 8px; border-radius: 4px; overflow: hidden; margin-bottom: 5px; }
            #score-fill { height: 100%; background: #00a32a; transition: width 0.3s ease; }
            #score-text { font-size: 16px; color: #1d2327; }
        </style>

        <script>
            (function() {
                // Main update function
                function updateSEOEngine() {
                    const container = document.querySelector('.sts-seo-editor');
                    if (!container) return;

                    const titleIn = container.querySelector('#sts_seo_title_input');
                    const descIn = container.querySelector('#sts_seo_desc_input');
                    const kwIn = container.querySelector('#sts_focus_keyword_input');
                    
                    if (!titleIn || !descIn || !kwIn) return;

                    const title = titleIn.value.trim() || "<?php echo esc_js(get_the_title($post->ID)); ?>";
                    const desc = descIn.value.trim();
                    const kw = kwIn.value.trim().toLowerCase();
                    let score = 0;

                    // 1. Title Length (30-65 is green)
                    const tLen = title.length;
                    let titleStatus = 'fail';
                    if (tLen >= 30 && tLen <= 65) { titleStatus = 'pass'; score += 20; }
                    else if (tLen > 0) { titleStatus = 'warn'; score += 5; }
                    
                    const elTitleCheck = container.querySelector('#check-title-len');
                    if (elTitleCheck) elTitleCheck.className = titleStatus;
                    
                    const elTitleCnt = container.querySelector('#title-count');
                    if (elTitleCnt) elTitleCnt.textContent = tLen;

                    // 2. Desc Length (70-160 is green)
                    const dLen = desc.length;
                    let descStatus = 'fail';
                    if (dLen >= 70 && dLen <= 160) { descStatus = 'pass'; score += 20; }
                    else if (dLen > 0) { descStatus = 'warn'; score += 5; }
                    
                    const elDescCheck = container.querySelector('#check-desc-len');
                    if (elDescCheck) elDescCheck.className = descStatus;

                    const elDescCnt = container.querySelector('#desc-count');
                    if (elDescCnt) elDescCnt.textContent = dLen;

                    // 3. Keyword in Title
                    const passKWTitle = kw && title.toLowerCase().includes(kw);
                    const elKWTitleCheck = container.querySelector('#check-kw-title');
                    if (elKWTitleCheck) elKWTitleCheck.className = passKWTitle ? 'pass' : 'fail';
                    if (passKWTitle) score += 20;

                    // 4. Keyword in Content
                    let content = '';
                    try {
                        if (window.wp && wp.data && wp.data.select) {
                            const editor = wp.data.select('core/editor');
                            if (editor) content = (editor.getEditedPostContent() || '').toLowerCase();
                        } else {
                            const contentEl = document.getElementById('content');
                            if (contentEl) content = contentEl.value.toLowerCase();
                        }
                    } catch(e) {}
                    
                    const passKWContent = kw && content.includes(kw);
                    const elKWContentCheck = container.querySelector('#check-kw-content');
                    if (elKWContentCheck) elKWContentCheck.className = passKWContent ? 'pass' : 'fail';
                    if (passKWContent) score += 20;

                    // 5. Friendly URL Audit
                    let permalink = '';
                    try {
                        if (window.wp && wp.data && wp.data.select) {
                            permalink = wp.data.select('core/editor').getPermalink() || '';
                        }
                    } catch(e) {}

                    let urlStatus = 'pass';
                    if (permalink) {
                        const isQuery = permalink.includes('?p=') || permalink.includes('&');
                        const hasDate = /\/\d{4}\/\d{2}\/\d{2}\//.test(permalink);
                        if (isQuery || hasDate) urlStatus = 'fail';
                    } else {
                        urlStatus = 'warn'; // Document not saved yet?
                    }

                    const elURLCheck = container.querySelector('#check-url');
                    if (elURLCheck) elURLCheck.className = urlStatus;
                    if (urlStatus === 'pass') score += 20;

                    // Update Snippet Preview
                    const elPrevTitle = container.querySelector('#sts-preview-title');
                    const elPrevDesc = container.querySelector('#sts-preview-desc');
                    const elPrevUrl = container.querySelector('.preview-url');
                    if (elPrevTitle) elPrevTitle.textContent = title;
                    if (elPrevDesc) elPrevDesc.textContent = desc || 'Insira uma descrição meta...';
                    if (elPrevUrl && permalink) elPrevUrl.textContent = permalink;

                    // Update Score UI
                    const scoreFill = container.querySelector('#score-fill');
                    const scoreText = container.querySelector('#score-text');

                    if (scoreFill && scoreText) {
                        scoreFill.style.width = score + '%';
                        scoreText.textContent = score + '/100';
                        
                        let color = '#d63638'; // Red
                        if (score >= 80) color = '#00a32a'; // Green
                        else if (score >= 40) color = '#dba617'; // Orange
                        scoreFill.style.setProperty('background', color, 'important');
                    }
                }

                // Listeners
                document.addEventListener('input', function(e) {
                    if (e.target.id && e.target.id.startsWith('sts_')) {
                        updateSEOEngine();
                    }
                });

                if (window.wp && wp.data) {
                    wp.data.subscribe(updateSEOEngine);
                }

                // Initial run
                setTimeout(updateSEOEngine, 1000);
            })();
        </script>
        <?php
    }

    public function save_seo_data($post_id)
    {
        if (!isset($_POST['sts_seo_nonce']) || !wp_verify_nonce($_POST['sts_seo_nonce'], 'sts_seo_save_action')) {
            return;
        }

        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
        if (!current_user_can('edit_post', $post_id)) return;

        if (isset($_POST['sts_seo_title'])) {
            update_post_meta($post_id, '_sts_seo_title', sanitize_text_field($_POST['sts_seo_title']));
        }
        if (isset($_POST['sts_seo_desc'])) {
            update_post_meta($post_id, '_sts_seo_desc', sanitize_textarea_field($_POST['sts_seo_desc']));
        }
        if (isset($_POST['sts_focus_keyword'])) {
            update_post_meta($post_id, '_sts_focus_keyword', sanitize_text_field($_POST['sts_focus_keyword']));
        }
        
        $noindex = isset($_POST['sts_seo_noindex']) ? '1' : '0';
        update_post_meta($post_id, '_sts_seo_noindex', $noindex);
    }

    public function enqueue_assets($hook)
    {
        if (!in_array($hook, ['post.php', 'post-new.php'])) return;
        
        wp_enqueue_script('wp-data');
        wp_enqueue_script('wp-editor');
    }
}
