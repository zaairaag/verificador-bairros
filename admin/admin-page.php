<?php
// Adicionar menu no painel administrativo
add_action('admin_menu', 'vdb_adicionar_menu_admin');

function vdb_adicionar_menu_admin() {
    add_menu_page(
        'Gerenciar Bairros Atendidos',
        'Bairros Atendidos',
        'manage_options',
        'vdb-gerenciar-bairros',
        'vdb_pagina_gerenciar_bairros',
        'dashicons-location',
        30
    );
    
    // Adicionar submenu para os interessados
    add_submenu_page(
        'vdb-gerenciar-bairros',
        'Interessados em Novos Bairros',
        'Interessados',
        'manage_options',
        'vdb-interessados',
        'vdb_pagina_interessados'
    );
}

function vdb_pagina_gerenciar_bairros() {
    global $wpdb;
    $tabela_bairros = $wpdb->prefix . 'vdb_bairros';
    
    // Processar importação de bairros padrão
    if (isset($_POST['importar_bairros_padrao'])) {
        // Chamar a função que adiciona os bairros padrão
        include(dirname(plugin_dir_path(__FILE__)) . '/importar-bairros.php');
        
        echo '<div class="notice notice-success is-dismissible"><p>Bairros padrão importados com sucesso!</p></div>';
    }
    
    // Processar formulário de adição
    if (isset($_POST['adicionar_bairro']) && isset($_POST['bairro']) && isset($_POST['cidade'])) {
        $bairro = sanitize_text_field($_POST['bairro']);
        $cidade = sanitize_text_field($_POST['cidade']);
        
        $wpdb->insert(
            $tabela_bairros,
            array(
                'bairro' => $bairro,
                'cidade' => $cidade
            ),
            array('%s', '%s')
        );
        
        echo '<div class="notice notice-success is-dismissible"><p>Bairro adicionado com sucesso!</p></div>';
    }
    
    // Processar exclusão
    if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
        $id = intval($_GET['id']);
        
        $wpdb->delete(
            $tabela_bairros,
            array('id' => $id),
            array('%d')
        );
        
        echo '<div class="notice notice-success is-dismissible"><p>Bairro removido com sucesso!</p></div>';
    }
    
    // Configuração da paginação
    $itens_por_pagina = 10; // Número de itens por página
    $pagina_atual = isset($_GET['pagina']) ? max(1, intval($_GET['pagina'])) : 1; // Página atual
    $offset = ($pagina_atual - 1) * $itens_por_pagina; // Calcular o deslocamento
    
    // Buscar o total de bairros para paginação
    $total_bairros = $wpdb->get_var("SELECT COUNT(*) FROM $tabela_bairros");
    $total_paginas = ceil($total_bairros / $itens_por_pagina);
    
    // Buscar os bairros com paginação
    $bairros = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT * FROM $tabela_bairros ORDER BY cidade, bairro LIMIT %d OFFSET %d",
            $itens_por_pagina,
            $offset
        )
    );
    
    // Adicionar CSS e JS
    ?>
    <style>
        /* Estilos gerais */
        .vdb-admin-wrap {
            max-width: 1100px;
            margin: 20px 0;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
        }
        
        .vdb-admin-title {
            display: flex;
            align-items: center;
            font-size: 24px;
            color: #333;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid #e5e5e5;
        }
        
        .vdb-admin-title .dashicons {
            margin-right: 10px;
            font-size: 30px;
            height: 30px;
            width: 30px;
        }
        
        /* Cards principais */
        .vdb-admin-section {
            background: white;
            border-radius: 5px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            margin-bottom: 25px;
            overflow: hidden;
        }
        
        .vdb-section-header {
            background: #f5f5f5;
            padding: 15px 20px;
            border-bottom: 1px solid #e0e0e0;
            display: flex;
            align-items: center;
        }
        
        .vdb-section-header h2 {
            margin: 0;
            font-size: 18px;
            font-weight: 600;
            color: #23282d;
            display: flex;
            align-items: center;
        }
        
        .vdb-section-header .dashicons {
            margin-right: 8px;
            color: #0073aa;
        }
        
        .vdb-section-content {
            padding: 20px;
        }
        
        /* Cards de estatísticas */
        .vdb-stat-cards {
            display: flex;
            gap: 20px;
            margin-bottom: 25px;
        }
        
        .vdb-stat-card {
            flex: 1;
            background: white;
            border-radius: 5px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            padding: 20px;
            border-left: 4px solid #0073aa;
        }
        
        .vdb-stat-card h3 {
            margin: 0 0 10px 0;
            font-size: 16px;
            color: #555;
            font-weight: 500;
        }
        
        .vdb-stat-number {
            font-size: 28px;
            font-weight: 600;
            color: #0073aa;
            margin: 0 0 5px 0;
        }
        
        .vdb-stat-desc {
            color: #777;
            font-size: 13px;
            margin: 0;
        }
        
        /* Formulário de cadastro */
        .vdb-form {
            padding: 15px 0;
        }
        
        .vdb-form-row {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            align-items: flex-end;
        }
        
        .vdb-form-field {
            flex: 1;
            min-width: 200px;
        }
        
        .vdb-form-field label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
        }
        
        .vdb-form-field input[type="text"] {
            width: 100%;
            padding: 8px 10px;
            border-radius: 4px;
            border: 1px solid #ddd;
        }
        
        .vdb-form-submit {
            margin-top: 24px;
        }
        
        /* Tabela de listagem */
        .vdb-table-responsive {
            overflow-x: auto;
        }
        
        .vdb-admin-table {
            width: 100%;
            border-collapse: collapse;
            border-spacing: 0;
            font-size: 14px;
        }
        
        .vdb-admin-table th {
            text-align: left;
            padding: 12px 15px;
            background: #f9f9f9;
            border-bottom: 1px solid #e0e0e0;
            font-weight: 600;
            color: #23282d;
        }
        
        .vdb-admin-table td {
            padding: 12px 15px;
            border-bottom: 1px solid #f0f0f0;
            vertical-align: middle;
        }
        
        .vdb-admin-table tr:hover {
            background-color: #f7fafd;
        }
        
        .vdb-admin-table tr:last-child td {
            border-bottom: 0;
        }
        
        /* Botões de ação */
        .vdb-actions-col {
            text-align: right;
            width: 100px;
        }
        
        .vdb-action-buttons {
            display: flex;
            justify-content: flex-end;
            gap: 8px;
        }
        
        .vdb-action-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 32px;
            height: 32px;
            border-radius: 4px;
            color: white;
            text-decoration: none;
            transition: all 0.2s ease;
        }
        
        .vdb-action-btn .dashicons {
            font-size: 16px;
            width: 16px;
            height: 16px;
        }
        
        .vdb-btn-delete {
            background-color: #d63638;
        }
        
        .vdb-btn-delete:hover {
            background-color: #b32d2e;
        }
        
        /* Paginação */
        .vdb-pagination {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 20px;
            border-top: 1px solid #f0f0f0;
            font-size: 13px;
        }
        
        .vdb-pagination-info {
            color: #666;
        }
        
        .vdb-pagination-links {
            display: flex;
            gap: 5px;
        }
        
        .vdb-pagination-link {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 3px;
            text-decoration: none;
            background: #f5f5f5;
            color: #0073aa;
            border: 1px solid #ddd;
        }
        
        .vdb-pagination-link:hover {
            background: #e9e9e9;
        }
        
        .vdb-pagination-active {
            background: #0073aa;
            color: white;
            border-color: #0073aa;
        }
        
        /* Estado vazio */
        .vdb-empty-state {
            text-align: center;
            padding: 40px 20px;
            color: #666;
        }
        
        .vdb-empty-state .dashicons {
            font-size: 48px;
            width: 48px;
            height: 48px;
            color: #ccc;
            margin-bottom: 15px;
        }
        
        /* Seção de ajuda */
        .vdb-help-section {
            padding: 15px 20px;
            background: #f9f9f9;
            border-radius: 4px;
            margin-top: 10px;
        }
        
        .vdb-help-section code {
            background: #fff;
            border: 1px solid #ddd;
            padding: 2px 5px;
            border-radius: 3px;
        }
    </style>
    
    <div class="wrap vdb-admin-wrap">
        <h1 class="vdb-admin-title"><span class="dashicons dashicons-location"></span> Gerenciar Bairros Atendidos</h1>
        
        <div class="vdb-stat-cards">
            <div class="vdb-stat-card">
                <h3>Total de Bairros</h3>
                <p class="vdb-stat-number"><?php echo $total_bairros; ?></p>
                <p class="vdb-stat-desc">bairros cadastrados</p>
            </div>
        </div>
        
        <div class="vdb-admin-section">
            <div class="vdb-section-header">
                <h2><span class="dashicons dashicons-plus-alt"></span> Adicionar Novo Bairro</h2>
            </div>
            
            <div class="vdb-section-content">
                <form method="post" action="" class="vdb-form">
                    <div class="vdb-form-row">
                        <div class="vdb-form-field">
                            <label for="cidade">Cidade</label>
                            <input type="text" id="cidade" name="cidade" required>
                        </div>
                        <div class="vdb-form-field">
                            <label for="bairro">Bairro</label>
                            <input type="text" id="bairro" name="bairro" required>
                        </div>
                        <div class="vdb-form-submit">
                            <input type="submit" name="adicionar_bairro" class="button button-primary" value="Adicionar Bairro">
                        </div>
                    </div>
                </form>
            </div>
        </div>
        
        <?php if ($total_bairros == 0) : ?>
        <div class="vdb-admin-section">
            <div class="vdb-section-header">
                <h2><span class="dashicons dashicons-download"></span> Importar Bairros Padrão</h2>
            </div>
            
            <div class="vdb-section-content">
                <p>Nenhum bairro cadastrado ainda. Deseja importar a lista de bairros padrão?</p>
                <form method="post" action="">
                    <input type="submit" name="importar_bairros_padrao" class="button button-primary" value="Importar Bairros Padrão">
                </form>
            </div>
        </div>
        <?php endif; ?>
        
        <div class="vdb-admin-section">
            <div class="vdb-section-header">
                <h2><span class="dashicons dashicons-list-view"></span> Bairros Cadastrados</h2>
            </div>
            
            <?php if (empty($bairros)) : ?>
                <div class="vdb-empty-state">
                    <span class="dashicons dashicons-marker"></span>
                    <p>Nenhum bairro cadastrado ainda.</p>
                </div>
            <?php else : ?>
                <div class="vdb-table-responsive">
                    <table class="vdb-admin-table">
                        <thead>
                            <tr>
                                <th>Cidade</th>
                                <th>Bairro</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($bairros as $item) : ?>
                                <tr>
                                    <td><?php echo esc_html($item->cidade); ?></td>
                                    <td><?php echo esc_html($item->bairro); ?></td>
                                    <td class="vdb-actions-col">
                                        <div class="vdb-action-buttons">
                                            <a href="?page=vdb-gerenciar-bairros&action=delete&id=<?php echo $item->id; ?>" 
                                               class="vdb-action-btn vdb-btn-delete"
                                               onclick="return confirm('Tem certeza que deseja remover este bairro?');" title="Remover">
                                                <span class="dashicons dashicons-trash"></span>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <?php if ($total_paginas > 1) : ?>
                <div class="vdb-pagination">
                    <div class="vdb-pagination-info">
                        Mostrando <?php echo min($offset + 1, $total_bairros); ?>-<?php echo min($offset + $itens_por_pagina, $total_bairros); ?> de <?php echo $total_bairros; ?> bairros
                    </div>
                    <div class="vdb-pagination-links">
                        <?php
                        // Links de paginação
                        $url_base = '?page=vdb-gerenciar-bairros&pagina=';
                        
                        // Botão de anterior
                        if ($pagina_atual > 1) :
                            echo '<a href="' . esc_url($url_base . ($pagina_atual - 1)) . '" class="vdb-pagination-link">&laquo; Anterior</a>';
                        endif;
                        
                        // Números de páginas
                        $inicio_paginacao = max(1, $pagina_atual - 2);
                        $fim_paginacao = min($total_paginas, $pagina_atual + 2);
                        
                        for ($i = $inicio_paginacao; $i <= $fim_paginacao; $i++) :
                            $active = $i == $pagina_atual ? ' vdb-pagination-active' : '';
                            echo '<a href="' . esc_url($url_base . $i) . '" class="vdb-pagination-link' . $active . '">' . $i . '</a>';
                        endfor;
                        
                        // Botão de próximo
                        if ($pagina_atual < $total_paginas) :
                            echo '<a href="' . esc_url($url_base . ($pagina_atual + 1)) . '" class="vdb-pagination-link">Próximo &raquo;</a>';
                        endif;
                        ?>
                    </div>
                </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
        
        <div class="vdb-admin-section">
            <div class="vdb-section-header">
                <h2><span class="dashicons dashicons-info"></span> Como usar</h2>
            </div>
            <div class="vdb-section-content vdb-help-section">
                <p>Use o shortcode <code>[verificador_bairro]</code> em qualquer página ou post para exibir o formulário de busca.</p>
            </div>
        </div>
    </div>
    <?php
}

/**
 * Página para gerenciar interessados
 */
function vdb_pagina_interessados() {
    global $wpdb;
    $tabela_interessados = $wpdb->prefix . 'vdb_interessados';
    
    // Processar ação de marcar como contatado
    if (isset($_GET['action']) && $_GET['action'] == 'marcar_contatado' && isset($_GET['id'])) {
        $id = intval($_GET['id']);
        
        $wpdb->update(
            $tabela_interessados,
            array('status' => 'contatado'),
            array('id' => $id),
            array('%s'),
            array('%d')
        );
        
        echo '<div class="notice notice-success is-dismissible"><p>Interessado marcado como contatado!</p></div>';
    }
    
    // Processar ação de exclusão
    if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
        $id = intval($_GET['id']);
        
        $wpdb->delete(
            $tabela_interessados,
            array('id' => $id),
            array('%d')
        );
        
        echo '<div class="notice notice-success is-dismissible"><p>Registro removido com sucesso!</p></div>';
    }
    
    // Definir filtro de status
    $status_filter = isset($_GET['status']) ? sanitize_text_field($_GET['status']) : 'todos';
    
    // Configurar paginação
    $itens_por_pagina = 10; // Número de itens por página
    $pagina_atual = isset($_GET['pagina']) ? max(1, intval($_GET['pagina'])) : 1; // Página atual
    $offset = ($pagina_atual - 1) * $itens_por_pagina; // Calcular o deslocamento
    
    // Preparar a consulta SQL com base no filtro
    $where_clause = $status_filter == 'todos' ? "" : $wpdb->prepare(" WHERE status = %s", $status_filter);
    
    // Contar total de registros com filtro aplicado para paginação
    $sql_count = "SELECT COUNT(*) FROM $tabela_interessados $where_clause";
    $total_registros = $wpdb->get_var($sql_count);
    $total_paginas = ceil($total_registros / $itens_por_pagina);
    
    // Buscar interessados com paginação
    $sql = "SELECT * FROM $tabela_interessados $where_clause ORDER BY data_cadastro DESC LIMIT %d OFFSET %d";
    $interessados = $wpdb->get_results($wpdb->prepare($sql, $itens_por_pagina, $offset));
    
    // Contar interessados por status para os cards de estatísticas
    $total_novos = $wpdb->get_var("SELECT COUNT(*) FROM $tabela_interessados WHERE status = 'novo'");
    $total_contatados = $wpdb->get_var("SELECT COUNT(*) FROM $tabela_interessados WHERE status = 'contatado'");
    $total = $total_novos + $total_contatados;
    
    ?>
    <div class="wrap">
        <h1><span class="dashicons dashicons-groups"></span> Interessados em Novos Bairros</h1>
        
        <div class="vdb-cards-container">
            <div class="vdb-info-card">
                <h2>Total</h2>
                <div class="vdb-card-number"><?php echo $total; ?></div>
                <div class="vdb-card-desc">interessados cadastrados</div>
            </div>
            
            <div class="vdb-info-card">
                <h2>Novos</h2>
                <div class="vdb-card-number"><?php echo $total_novos; ?></div>
                <div class="vdb-card-desc">contatos a processar</div>
            </div>
            
            <div class="vdb-info-card">
                <h2>Processados</h2>
                <div class="vdb-card-number"><?php echo $total_contatados; ?></div>
                <div class="vdb-card-desc">contatos já atendidos</div>
            </div>
        </div>
        
        <div class="vdb-main-container">
            <h2 class="vdb-section-title"><span class="dashicons dashicons-list-view"></span> Lista de Interessados</h2>
            
            <div class="vdb-filter-tabs">
                <a href="?page=vdb-interessados&status=todos" class="vdb-tab <?php echo $status_filter == 'todos' ? 'active' : ''; ?>">
                    Todos (<?php echo $total; ?>)
                </a>
                <a href="?page=vdb-interessados&status=novo" class="vdb-tab <?php echo $status_filter == 'novo' ? 'active' : ''; ?>">
                    Novos (<?php echo $total_novos; ?>)
                </a>
                <a href="?page=vdb-interessados&status=contatado" class="vdb-tab <?php echo $status_filter == 'contatado' ? 'active' : ''; ?>">
                    Contatados (<?php echo $total_contatados; ?>)
                </a>
            </div>
            
            <?php if (empty($interessados)) : ?>
                <div class="vdb-empty-state">
                    <span class="dashicons dashicons-format-status"></span>
                    <p>Nenhum interessado cadastrado ainda.</p>
                </div>
            <?php else : ?>
                <div class="vdb-table-container">
                    <table class="wp-list-table widefat fixed striped">
                        <thead>
                            <tr>
                                <th>Nome</th>
                                <th>E-mail</th>
                                <th>Telefone</th>
                                <th>Bairro de Interesse</th>
                                <th>Data de Cadastro</th>
                                <th>Status</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($interessados as $item) : 
                                $status_class = $item->status == 'novo' ? 'novo' : 'contatado';
                                $status_label = $item->status == 'novo' ? 'Novo' : 'Contatado';
                                $data_formatada = date_i18n('d/m/Y \à\s H:i', strtotime($item->data_cadastro));
                            ?>
                                <tr>
                                    <td><strong><?php echo esc_html($item->nome); ?></strong></td>
                                    <td><a href="mailto:<?php echo esc_attr($item->email); ?>"><?php echo esc_html($item->email); ?></a></td>
                                    <td><a href="tel:<?php echo esc_attr($item->telefone); ?>"><?php echo esc_html($item->telefone); ?></a></td>
                                    <td><?php echo esc_html($item->bairro); ?></td>
                                    <td><?php echo $data_formatada; ?></td>
                                    <td><span class="vdb-status-badge status-<?php echo $status_class; ?>"><?php echo $status_label; ?></span></td>
                                    <td>
                                        <div class="vdb-action-links">
                                            <?php if ($item->status == 'novo') : ?>
                                                <a href="?page=vdb-interessados&action=marcar_contatado&id=<?php echo $item->id; ?>&status=<?php echo $status_filter; ?>" 
                                                   title="Marcar como contatado">
                                                    <span class="dashicons dashicons-yes"></span>
                                                </a>
                                            <?php endif; ?>
                                            <a href="https://wa.me/<?php echo preg_replace('/\D/', '', $item->telefone); ?>?text=Olá%20<?php echo urlencode($item->nome); ?>!%20Recebemos%20seu%20interesse%20em%20nossos%20serviços%20no%20bairro%20<?php echo urlencode($item->bairro); ?>." 
                                               target="_blank" title="Enviar WhatsApp">
                                                <span class="dashicons dashicons-whatsapp"></span>
                                            </a>
                                            <a href="?page=vdb-interessados&action=delete&id=<?php echo $item->id; ?>&status=<?php echo $status_filter; ?>" 
                                               onclick="return confirm('Tem certeza que deseja remover este registro?');" 
                                               title="Remover registro">
                                                <span class="dashicons dashicons-trash"></span>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <?php if ($total_paginas > 1) : ?>
                <div class="vdb-pagination-container">
                    <div class="vdb-pagination-info">
                        Mostrando <?php echo min($offset + 1, $total_registros); ?>-<?php echo min($offset + $itens_por_pagina, $total_registros); ?> de <?php echo $total_registros; ?> registros
                    </div>
                    <div class="vdb-pagination">
                        <?php
                        // Links de paginação
                        $url_base = '?page=vdb-interessados&status=' . $status_filter . '&pagina=';
                        
                        // Botão de anterior
                        if ($pagina_atual > 1) :
                            echo '<a href="' . esc_url($url_base . ($pagina_atual - 1)) . '" class="vdb-page-link">&laquo; Anterior</a>';
                        endif;
                        
                        // Números de páginas
                        $inicio_paginacao = max(1, $pagina_atual - 2);
                        $fim_paginacao = min($total_paginas, $pagina_atual + 2);
                        
                        for ($i = $inicio_paginacao; $i <= $fim_paginacao; $i++) :
                            $active = $i == $pagina_atual ? ' vdb-page-current' : '';
                            echo '<a href="' . esc_url($url_base . $i) . '" class="vdb-page-link' . $active . '">' . $i . '</a>';
                        endfor;
                        
                        // Botão de próximo
                        if ($pagina_atual < $total_paginas) :
                            echo '<a href="' . esc_url($url_base . ($pagina_atual + 1)) . '" class="vdb-page-link">Próximo &raquo;</a>';
                        endif;
                        ?>
                    </div>
                </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
    
    <style>
    /* Estilos específicos para a página de interessados */
    .vdb-cards-container {
        display: flex;
        flex-wrap: wrap;
        gap: 20px;
        margin-bottom: 20px;
    }
    
    .vdb-info-card {
        background: white;
        border-radius: 4px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        padding: 15px 20px;
        min-width: 200px;
        flex: 1;
    }
    
    .vdb-info-card h2 {
        margin: 0 0 10px 0;
        font-size: 14px;
        font-weight: 500;
        color: #666;
    }
    
    .vdb-card-number {
        font-size: 32px;
        font-weight: 600;
        color: #0073aa;
        margin-bottom: 5px;
    }
    
    .vdb-card-desc {
        color: #777;
        font-size: 13px;
    }
    
    .vdb-main-container {
        background: white;
        border-radius: 4px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        margin-bottom: 20px;
        overflow: hidden;
    }
    
    .vdb-section-title {
        font-size: 16px;
        padding: 15px 20px;
        margin: 0;
        border-bottom: 1px solid #eee;
        display: flex;
        align-items: center;
    }
    
    .vdb-section-title .dashicons {
        margin-right: 8px;
    }
    
    .vdb-filter-tabs {
        background: #f5f5f5;
        padding: 10px 20px;
        border-bottom: 1px solid #eee;
        display: flex;
        gap: 10px;
    }
    
    .vdb-tab {
        display: inline-block;
        padding: 7px 12px;
        text-decoration: none;
        color: #555;
        border-radius: 3px;
        transition: all 0.2s ease;
    }
    
    .vdb-tab:hover {
        background: #e9e9e9;
    }
    
    .vdb-tab.active {
        background: #0073aa;
        color: white;
    }
    
    .vdb-table-container {
        overflow-x: auto;
    }
    
    .vdb-status-badge {
        display: inline-block;
        padding: 3px 8px;
        border-radius: 3px;
        font-size: 12px;
        font-weight: 500;
    }
    
    .status-novo {
        background-color: #e5f7ff;
        color: #0073aa;
    }
    
    .status-contatado {
        background-color: #e9f9e7;
        color: #46a546;
    }
    
    .vdb-action-links {
        display: flex;
        gap: 8px;
    }
    
    .vdb-action-links a {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 28px;
        height: 28px;
        border-radius: 3px;
        color: white;
        text-decoration: none;
    }
    
    .vdb-action-links a .dashicons {
        font-size: 16px;
        width: 16px;
        height: 16px;
    }
    
    .vdb-action-links a:nth-child(1) {
        background-color: #0073aa;
    }
    
    .vdb-action-links a:nth-child(2) {
        background-color: #25D366;
    }
    
    .vdb-action-links a:nth-child(3) {
        background-color: #d63638;
    }
    
    .vdb-pagination-container {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 15px 20px;
        border-top: 1px solid #eee;
        font-size: 13px;
    }
    
    .vdb-pagination-info {
        color: #666;
    }
    
    .vdb-pagination {
        display: flex;
        gap: 5px;
    }
    
    .vdb-page-link {
        display: inline-block;
        padding: 4px 10px;
        background: #f5f5f5;
        border: 1px solid #ddd;
        border-radius: 3px;
        text-decoration: none;
        color: #0073aa;
    }
    
    .vdb-page-link:hover {
        background: #e9e9e9;
    }
    
    .vdb-page-current {
        background: #0073aa;
        color: white;
        border-color: #0073aa;
    }
    
    .vdb-empty-state {
        text-align: center;
        padding: 40px 20px;
        color: #777;
    }
    
    .vdb-empty-state .dashicons {
        font-size: 48px;
        width: 48px;
        height: 48px;
        color: #ccc;
        margin-bottom: 10px;
    }
    </style>
    <?php
}
