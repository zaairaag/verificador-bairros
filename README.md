# Verificador de Bairros

## Descrição
O Verificador de Bairros é um plugin WordPress que permite aos visitantes do site verificar se determinado bairro/cidade é atendido pelo prestador de serviços. Se o bairro não for atendido, o plugin captura os dados de contato dos interessados para notificação futura.

## Funcionalidades

### Lado Público
- **Verificação de Bairros**: Campo de busca com autocompletar para verificar se um bairro é atendido
- **Captura de Interessados**: Formulário para cadastro de usuários interessados em bairros ainda não atendidos
- **Interface Responsiva**: Design amigável para dispositivos móveis e desktop

### Área Administrativa
- **Gerenciamento de Bairros**: Interface para adicionar, editar e excluir bairros atendidos
- **Lista de Interessados**: Visualização de pessoas interessadas em bairros não atendidos
- **Filtros e Ordenação**: Possibilidade de filtrar interessados por status e ordenar por data
- **Marcação de Contatados**: Sistema para marcar quais interessados já foram contatados

## Instalação

1. Faça upload da pasta `verificador-bairros` para o diretório `/wp-content/plugins/`
2. Ative o plugin na área administrativa do WordPress
3. O plugin criará automaticamente as tabelas necessárias no banco de dados

## Uso

### Shortcode
Incorpore o verificador em qualquer página ou post usando o shortcode:
```
[verificador_bairro]
```

### Gerenciamento de Bairros
Acesse o menu "Verificador de Bairros" no painel administrativo do WordPress para:
1. Adicionar novos bairros atendidos
2. Gerenciar interessados em bairros não atendidos
3. Marcar interessados como contatados

## Configurações
O plugin funciona automaticamente após a ativação. Para personalizar as URLs dos links de planos residenciais e empresariais, edite o arquivo `public/js/vdb-script.js`.

## Estrutura de Arquivos
```
verificador-bairros/
├── admin/
│   ├── admin-page.php      # Interface administrativa
│   └── admin-functions.php # Funções administrativas
├── public/
│   ├── css/
│   │   └── vdb-style.css   # Estilos do front-end
│   ├── js/
│   │   └── vdb-script.js   # JavaScript do front-end
│   └── shortcode.php       # Implementação do shortcode
└── verificador-bairros.php # Arquivo principal do plugin
```

## Tabelas no Banco de Dados
O plugin cria duas tabelas no banco de dados:

1. `{prefixo}_vdb_bairros` - Armazena os bairros atendidos
   - id (chave primária)
   - bairro (nome do bairro)
   - cidade (nome da cidade)

2. `{prefixo}_vdb_interessados` - Armazena os dados de interessados
   - id (chave primária)
   - nome (nome do interessado)
   - email (email para contato)
   - telefone (telefone para contato)
   - bairro (bairro de interesse)
   - data_cadastro (data do cadastro)
   - status (status do contato: 'novo' ou 'contatado')

## Requisitos
- WordPress 5.0 ou superior
- PHP 7.2 ou superior
- MySQL 5.6 ou superior

## Desenvolvido por
[Zaira Gonçalves](https://zairagoncalves.com/)

## Versão
2.2