<?php
// Registrar shortcode
add_shortcode('verificador_bairro', 'vdb_shortcode_verificador');

// Registrar endpoint AJAX para autocompletar
add_action('wp_ajax_vdb_autocompletar', 'vdb_autocompletar_bairros');
add_action('wp_ajax_nopriv_vdb_autocompletar', 'vdb_autocompletar_bairros');

// Registrar endpoint AJAX para verificar disponibilidade
add_action('wp_ajax_vdb_verificar_disponibilidade', 'vdb_verificar_disponibilidade');
add_action('wp_ajax_nopriv_vdb_verificar_disponibilidade', 'vdb_verificar_disponibilidade');

// Registrar endpoint AJAX para salvar interessados
add_action('wp_ajax_vdb_salvar_interessado', 'vdb_salvar_interessado');
add_action('wp_ajax_nopriv_vdb_salvar_interessado', 'vdb_salvar_interessado');

// Função para autocompletar bairros
function vdb_autocompletar_bairros() {
    global $wpdb;
    $termo = isset($_GET['termo']) ? sanitize_text_field($_GET['termo']) : '';
    
    if (empty($termo)) {
        wp_send_json_success(array());
        return;
    }
    
    $tabela_bairros = $wpdb->prefix . 'vdb_bairros';
    
    $resultados = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT bairro, cidade FROM $tabela_bairros WHERE bairro LIKE %s OR cidade LIKE %s ORDER BY cidade, bairro LIMIT 10",
            '%' . $wpdb->esc_like($termo) . '%',
            '%' . $wpdb->esc_like($termo) . '%'
        )
    );
    
    wp_send_json_success($resultados);
}

// Função para verificar disponibilidade
function vdb_verificar_disponibilidade() {
    global $wpdb;
    $bairro = isset($_POST['bairro']) ? sanitize_text_field($_POST['bairro']) : '';
    
    if (empty($bairro)) {
        wp_send_json_error(array('mensagem' => 'Bairro não informado'));
        return;
    }
    
    $tabela_bairros = $wpdb->prefix . 'vdb_bairros';
    
    // Buscar o bairro (ignorando maiúsculas/minúsculas)
    $bairro_encontrado = $wpdb->get_row(
        $wpdb->prepare(
            "SELECT * FROM $tabela_bairros WHERE LOWER(bairro) = LOWER(%s)",
            $bairro
        )
    );
    
    if ($bairro_encontrado) {
        wp_send_json_success(array(
            'disponivel' => true,
            'bairro' => $bairro_encontrado->bairro,
            'cidade' => $bairro_encontrado->cidade
        ));
    } else {
        wp_send_json_success(array(
            'disponivel' => false,
            'termo' => $bairro
        ));
    }
}

// Função para salvar interessados no banco
function vdb_salvar_interessado() {
    global $wpdb;
    
    // Log para debug
    error_log('Função vdb_salvar_interessado chamada');
    error_log('POST recebido: ' . print_r($_POST, true));
    
    // Obter e sanitizar dados
    $nome = isset($_POST['nome']) ? sanitize_text_field($_POST['nome']) : '';
    $email = isset($_POST['email']) ? sanitize_email($_POST['email']) : '';
    $telefone = isset($_POST['telefone']) ? sanitize_text_field($_POST['telefone']) : '';
    $bairro = isset($_POST['bairro']) ? sanitize_text_field($_POST['bairro']) : '';
    
    // Validar dados
    if (empty($nome) || empty($email) || empty($telefone) || empty($bairro)) {
        wp_send_json_error(array('mensagem' => 'Todos os campos são obrigatórios'));
        return;
    }
    
    // Verificar se a tabela existe e criar se necessário
    $tabela_interessados = $wpdb->prefix . 'vdb_interessados';
    
    // Verificar se a tabela existe
    $tabela_existe = $wpdb->get_var("SHOW TABLES LIKE '$tabela_interessados'");
    
    // Se não existir, criar a tabela
    if (!$tabela_existe) {
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE $tabela_interessados (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            nome varchar(100) NOT NULL,
            email varchar(100) NOT NULL,
            telefone varchar(20) NOT NULL,
            bairro varchar(100) NOT NULL,
            data_cadastro datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            status varchar(20) DEFAULT 'novo' NOT NULL,
            PRIMARY KEY (id)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
        
        // Verificar novamente se a tabela foi criada
        $tabela_existe = $wpdb->get_var("SHOW TABLES LIKE '$tabela_interessados'");
        
        if (!$tabela_existe) {
            error_log('Não foi possível criar a tabela: ' . $tabela_interessados);
            wp_send_json_error(array('mensagem' => 'Não foi possível criar a tabela no banco de dados. Por favor, contate o administrador.'));
            return;
        }
        
        error_log('Tabela criada com sucesso: ' . $tabela_interessados);
    }
    
    try {
        // Inserir no banco de dados
        $resultado = $wpdb->insert(
            $tabela_interessados,
            array(
                'nome' => $nome,
                'email' => $email,
                'telefone' => $telefone,
                'bairro' => $bairro,
                'data_cadastro' => current_time('mysql'),
                'status' => 'novo'
            )
        );
        
        if ($resultado) {
            wp_send_json_success(array('mensagem' => 'Seus dados foram salvos com sucesso!'));
        } else {
            error_log('Erro ao inserir no banco: ' . $wpdb->last_error);
            wp_send_json_error(array('mensagem' => 'Erro ao salvar seus dados: ' . $wpdb->last_error));
        }
    } catch (Exception $e) {
        error_log('Exceção ao inserir no banco: ' . $e->getMessage());
        wp_send_json_error(array('mensagem' => 'Erro ao salvar seus dados: ' . $e->getMessage()));
    }
}

// Função do shortcode
function vdb_shortcode_verificador() {
    // Obter a versão do plugin para forçar atualização de cache
    $plugin_data = get_file_data(plugin_dir_path(__DIR__) . 'verificador-bairros.php', array('Version' => 'Version'));
    $plugin_version = !empty($plugin_data['Version']) ? $plugin_data['Version'] : '2.1.0';
    
    // Enfileirar CSS e JS
    wp_enqueue_style('vdb-estilo', plugin_dir_url(dirname(__FILE__)) . 'public/css/vdb-style.css', array(), $plugin_version);
    wp_enqueue_script('vdb-script', plugin_dir_url(dirname(__FILE__)) . 'public/js/vdb-script.js', array('jquery'), $plugin_version, true);
    
    // Enfileirar Font Awesome para ícones
    wp_enqueue_style('font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css');
    
    // Passar variáveis para o JavaScript
    wp_localize_script('vdb-script', 'vdbVars', array(
        'ajaxurl' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('vdb_nonce')
    ));
    
    // Iniciar buffer de saída
    ob_start();
    
    // Formulário HTML simplificado - apenas o campo de pesquisa
    ?>
    <div class="vdb-container">
        <div class="vdb-form-container">
            <form method="post" class="vdb-form" id="vdb-form">
                <div class="vdb-campo">
                    <input type="text" id="vdb_bairro" name="vdb_bairro" required placeholder="Pesquise seu bairro" autocomplete="off">
                    <div class="vdb-sugestoes" id="vdb-sugestoes"></div>
                </div>
            </form>
        </div>
        
        <div id="vdb-resultado-container"></div>
        
        <div class="vdb-page-background"></div>
    </div>
    <?php
    
    // JavaScript direto para garantir que os botões sejam adicionados
    ?>
    <script>
    jQuery(document).ready(function($) {
        // Sobrescrever a função de verificar disponibilidade diretamente no shortcode
        // para garantir que os botões sejam exibidos
        function verificarDisponibilidadeDireta(bairro) {
            $.ajax({
                url: vdbVars.ajaxurl,
                type: 'POST',
                data: {
                    action: 'vdb_verificar_disponibilidade',
                    nonce: vdbVars.nonce,
                    bairro: bairro
                },
                success: function(response) {
                    if (response.success) {
                        if (response.data.disponivel) {
                            // Bairro disponível com texto melhorado e links embutidos
                            $('#vdb-resultado-container').html(`
                                <div class="vdb-resultado vdb-disponivel">
                                    <p>Ótimas notícias! Atendemos no bairro <strong>${response.data.bairro}</strong> 
                                    em <strong>${response.data.cidade}</strong>.</p>
                                    <p>Caso queira conhecer nossos planos para sua casa, acesse <a href="/todos-planos/" class="vdb-link">planos residenciais</a>, 
                                    para sua empresa, veja nossos <a href="/planos-empresariais/" class="vdb-link">planos empresariais</a> 
                                    ou se preferir, <a href="https://wa.me/558005917287?text=Olá! Vi que vocês atendem no bairro ${response.data.bairro} e gostaria de mais informações." class="vdb-link" target="_blank">fale conosco pelo WhatsApp</a>.</p>
                                </div>
                            `);
                            
                            // Remover qualquer formulário que possa ter sido adicionado
                            $('.vdb-form-interessado, #vdb-form-container, #vdb-form-interessado, #vdb-form-unico').remove();
                        } else {
                            // Bairro não disponível - apenas mostrar a mensagem, o JS vai cuidar do formulário
                            $('#vdb-resultado-container').html(`
                                <div class="vdb-resultado vdb-indisponivel">
                                    <p>Desculpe, ainda não atendemos no bairro <strong>${response.data.termo}</strong>.</p>
                                    <p>Estamos em expansão! Deixe seu contato para avisarmos quando chegarmos à sua região.</p>
                                </div>
                            `);
                            
                            // Deixar que o JS adicione o formulário
                            // Isso evita conflitos entre o formulário adicionado aqui e o adicionado pelo JS
                            verificarDisponibilidade(response.data.termo);
                        }
                    }
                }
            });
        }

        // Sobrescrever os eventos de clique nas sugestões
        $('#vdb-sugestoes').on('click', '.vdb-sugestao-item', function() {
            const bairro = $(this).text().split(' - ')[0];
            $('#vdb_bairro').val(bairro);
            $('#vdb-sugestoes').hide();
            verificarDisponibilidadeDireta(bairro);
        });

        // Sobrescrever o evento de submit do formulário
        $('#vdb-form').on('submit', function(e) {
            e.preventDefault();
            const bairro = $('#vdb_bairro').val().trim();
            if (bairro) {
                verificarDisponibilidadeDireta(bairro);
            }
        });
    });
    </script>
    <?php
    
    // Retornar o conteúdo do buffer
    return ob_get_clean();
}
