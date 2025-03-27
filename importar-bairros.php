<?php
/**
 * Script para importau00e7u00e3o manual dos bairros padru00e3o
 * Este arquivo u00e9 usado apenas para importar os bairros manualmente
 */

// Verificar se o arquivo u00e9 chamado diretamente
if (!defined('ABSPATH')) {
    define('ABSPATH', dirname(__FILE__) . '/');
    require_once(ABSPATH . 'wp-config.php');
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
}

global $wpdb;
$tabela_bairros = $wpdb->prefix . 'vdb_bairros';

// Lista de bairros padru00e3o (bairro, cidade)
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
    array('Canau00e3', 'Viana'),
    array('Cantinho Feliz', 'Cariacica'),
    array('Castelo Branco', 'Vila Velha'),
    array('Caxias do Sul', 'Viana'),
    array('Centro', 'Viana'),
    array('Cobilu00e2ndia', 'Vila Velha'),
    array('Flor de Piranema', 'Cariacica'),
    array('Flor do campo', 'Cariacica'),
    array('Industrial', 'Viana'),
    array('Ipanema', 'Viana'),
    array('Itapemirim', 'Cariacica'),
    array('Jardim Botu00e2nico', 'Vila Velha'),
    array('Jardim Campo Grande', 'Cariacica'),
    array('Jerusalu00e9m', 'Cariacica'),
    array('JK - Juscelino Kubitscheck', 'Vila Velha'),
    array('Maracanu00e3', 'Cariacica'),
    array('Marcu00edlio de Noronha', 'Viana'),
    array('Morada de Bethu00e2nia', 'Viana'),
    array('Morada de Campo Grande', 'Cariacica'),
    array('Morada de Santa Fe', 'Cariacica'),
    array('Mucuri', 'Cariacica'),
    array('Nova Bethu00e2nia', 'Viana'),
    array('Nova Valverde', 'Cariacica'),
    array('Nova Viana', 'Viana'),
    array('Novo Horizonte', 'Cariacica'),
    array('Operu00e1rio', 'Cariacica'),
    array('Parque Gramado', 'Cariacica'),
    array('Parque Industrial', 'Viana'),
    array('Piranema', 'Cariacica'),
    array('Primavera', 'Viana'),
    array('Ribeira', 'Viana'),
    array('Rio Marinho', 'Cariacica'),
    array('Rio Marinho', 'Vila Velha'),
    array('Rosa da Penha', 'Cariacica'),
    array('Santa Bu00e1rbara', 'Cariacica'),
    array('Santa Paula', 'Vila Velha'),
    array('Santa Terezinha', 'Viana'),
    array('Santo Agostinho', 'Viana'),
    array('Su00e3o Benedito', 'Cariacica'),
    array('Su00e3o Geraldo 2', 'Cariacica'),
    array('Su00e3o Gonu00e7alo', 'Cariacica'),
    array('Serra do Anil', 'Cariacica'),
    array('Soteco', 'Viana'),
    array('Sotelu00e2ndia', 'Cariacica'),
    array('Universal', 'Viana'),
    array('Val Parau00edso', 'Cariacica'),
    array('Vale do Sol', 'Viana'),
    array('Vale dos Reis', 'Cariacica'),
    array('Vera Cruz', 'Cariacica'),
    array('Vila Bethu00e2nia (somente o condomu00ednio Vila Topu00e1zio e Vila Safira)', 'Viana'),
    array('Vila Capixaba', 'Cariacica'),
    array('Vila Independu00eancia', 'Cariacica'),
    array('Vila Izabel', 'Cariacica'),
    array('Vila Nova', 'Viana'),
    array('Vila Palestina', 'Cariacica'),
    array('Vista Dourada', 'Cariacica'),
    array('Vista Linda', 'Vila Velha'),
    array('Vista Mar 1', 'Cariacica'),
    array('Vista Mar 2', 'Cariacica')
);

// Contar bairros na tabela
$count = $wpdb->get_var("SELECT COUNT(*) FROM $tabela_bairros");

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
    
    echo "Inserido: {$bairro[0]} - {$bairro[1]}<br>";
}

echo "<h2>Importau00e7u00e3o conclu00edda!</h2>";
echo "<p>Total de bairros antes da importau00e7u00e3o: $count</p>";
echo "<p>Total de bairros importados: " . count($bairros_padrao) . "</p>";
echo "<p>Total atual: " . ($count + count($bairros_padrao)) . "</p>";
echo "<p><a href='admin.php?page=vdb-gerenciar-bairros'>Voltar para o gerenciamento de bairros</a></p>";
