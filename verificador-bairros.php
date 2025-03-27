<?php
/**
 * Plugin Name: Verificador de Bairros
 * Description: Permite verificar se determinado bairro/cidade é atendido pelo prestador de serviços.
 * Version: 2.2
 * Author: <a href="https://zairagoncalves.com/">Zaíra Gonçalves</a>
 */

// Evitar acesso direto
if (!defined('ABSPATH')) {
    exit;
}

// Ativação do plugin
register_activation_hook(__FILE__, 'vdb_ativar_plugin');

// Desativação do plugin
register_deactivation_hook(__FILE__, 'vdb_desativar_plugin');

// Inicialização do plugin
add_action('init', 'vdb_inicializar_plugin');

// Incluir arquivos necessários
function vdb_inicializar_plugin() {
    include(plugin_dir_path(__FILE__) . 'admin/admin-page.php');
    include(plugin_dir_path(__FILE__) . 'public/shortcode.php');
}

// Criar tabela no banco de dados durante a ativação
function vdb_ativar_plugin() {
    global $wpdb;
    
    $tabela_bairros = $wpdb->prefix . 'vdb_bairros';
    $tabela_interessados = $wpdb->prefix . 'vdb_interessados';
    
    $charset_collate = $wpdb->get_charset_collate();
    
    $sql = "CREATE TABLE $tabela_bairros (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        bairro varchar(100) NOT NULL,
        cidade varchar(100) NOT NULL,
        PRIMARY KEY (id)
    ) $charset_collate;";
    
    $sql2 = "CREATE TABLE $tabela_interessados (
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
    dbDelta($sql2);
    
    // Adicionar bairros padrão apenas se a tabela estiver vazia
    $count = $wpdb->get_var("SELECT COUNT(*) FROM $tabela_bairros");
    
    if ($count == 0) {
        vdb_adicionar_bairros_padrao();
    }
}

// Função para adicionar bairros padrão
function vdb_adicionar_bairros_padrao() {
    global $wpdb;
    $tabela_bairros = $wpdb->prefix . 'vdb_bairros';
    
    // Lista de bairros padrão (bairro, cidade)
    $bairros_padrao = array(
        array('13 de Maio / Primavera', 'Viana'),
        array('Areinha', 'Viana'),
        array('Arlindo Vilaschi', 'Viana'),
        array('Bandeirantes', 'Cariacica'),
        array('Bela Aurora', 'Cariacica'),
        array('Bela Vista', 'Cariacica'),
        array('Bom Pastor', 'Viana'),
        array('Campo Belo 1', 'Cariacica'),
        array('Campo Belo 2', 'Cariacica'),
        array('Campo Grande', 'Cariacica'),
        array('Campo Verde', 'Viana'),
        array('Canã', 'Viana'),
        array('Cantinho Feliz', 'Cariacica'),
        array('Castelo Branco', 'Vila Velha'),
        array('Caxias do Sul', 'Viana'),
        array('Centro', 'Viana'),
        array('Cobrilândia', 'Vila Velha'),
        array('Flor de Piranema', 'Cariacica'),
        array('Flor do campo', 'Cariacica'),
        array('Industrial', 'Viana'),
        array('Ipanema', 'Viana'),
        array('Itapemirim', 'Cariacica'),
        array('Jardim Botânico', 'Vila Velha'),
        array('Jardim Campo Grande', 'Cariacica'),
        array('Jerusalém', 'Cariacica'),
        array('JK - Juscelino Kubitscheck', 'Vila Velha'),
        array('Maracanã', 'Cariacica'),
        array('Marcílio de Noronha', 'Viana'),
        array('Morada de Bethânia', 'Viana'),
        array('Morada de Campo Grande', 'Cariacica'),
        array('Morada de Santa Fe', 'Cariacica'),
        array('Mucuri', 'Cariacica'),
        array('Nova Bethânia', 'Viana'),
        array('Nova Valverde', 'Cariacica'),
        array('Nova Viana', 'Viana'),
        array('Novo Horizonte', 'Cariacica'),
        array('Operário', 'Cariacica'),
        array('Parque Gramado', 'Cariacica'),
        array('Parque Industrial', 'Viana'),
        array('Piranema', 'Cariacica'),
        array('Primavera', 'Viana'),
        array('Ribeira', 'Viana'),
        array('Rio Marinho', 'Cariacica'),
        array('Rio Marinho', 'Vila Velha'),
        array('Rosa da Penha', 'Cariacica'),
        array('Santa Bárbara', 'Cariacica'),
        array('Santa Paula', 'Vila Velha'),
        array('Santa Terezinha', 'Viana'),
        array('Santo Agostinho', 'Viana'),
        array('São Benedito', 'Cariacica'),
        array('São Geraldo 2', 'Cariacica'),
        array('São Gonçalo', 'Cariacica'),
        array('Serra do Anil', 'Cariacica'),
        array('Soteco', 'Viana'),
        array('Sotelândia', 'Cariacica'),
        array('Universal', 'Viana'),
        array('Val Paraíso', 'Cariacica'),
        array('Vale do Sol', 'Viana'),
        array('Vale dos Reis', 'Cariacica'),
        array('Vera Cruz', 'Cariacica'),
        array('Vila Bethânia (somente o condomínio Vila Topázio e Vila Safira)', 'Viana'),
        array('Vila Capixaba', 'Cariacica'),
        array('Vila Independência', 'Cariacica'),
        array('Vila Izabel', 'Cariacica'),
        array('Vila Nova', 'Viana'),
        array('Vila Palestina', 'Cariacica'),
        array('Vista Dourada', 'Cariacica'),
        array('Vista Linda', 'Vila Velha'),
        array('Vista Mar 1', 'Cariacica'),
        array('Vista Mar 2', 'Cariacica')
    );
    
    // Inserir bairros na tabela
    foreach ($bairros_padrao as $bairro) {
        $wpdb->insert(
            $tabela_bairros,
            array(
                'bairro' => $bairro[0],
                'cidade' => $bairro[1]
            ),
            array('%s', '%s')
        );
    }
}

// Função de desativação
function vdb_desativar_plugin() {
    // Você pode optar por remover a tabela na desativação
    // global $wpdb;
    // $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}vdb_bairros");
    // $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}vdb_interessados");
}
