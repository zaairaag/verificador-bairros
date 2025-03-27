jQuery(document).ready(function($) {
    // Elementos DOM
    const $input = $('#vdb_bairro');
    const $sugestoes = $('#vdb-sugestoes');
    const $form = $('#vdb-form');
    const $resultadoContainer = $('#vdb-resultado-container');
    const $mainContainer = $('.vdb-container');
    
    // Variáveis de controle
    let timeoutId;
    let selectedIndex = -1;
    let sugestoesList = [];
    
    // Função para carregar sugestões
    function carregarSugestoes(termo) {
        if (termo.length < 2) {
            $sugestoes.hide().empty();
            return;
        }
        
        // Cancelar qualquer requisição anterior
        clearTimeout(timeoutId);
        
        // Aguardar um pouco antes de enviar a requisição
        timeoutId = setTimeout(function() {
            $.ajax({
                url: vdbVars.ajaxurl,
                type: 'GET',
                data: {
                    action: 'vdb_autocompletar',
                    nonce: vdbVars.nonce,
                    termo: termo
                },
                success: function(response) {
                    if (response.success && response.data.length > 0) {
                        mostrarSugestoes(response.data);
                    } else {
                        $sugestoes.hide().empty();
                    }
                }
            });
        }, 300); // Delay de 300ms para evitar muitas requisições
    }
    
    // Função para mostrar as sugestões
    function mostrarSugestoes(data) {
        $sugestoes.empty();
        sugestoesList = data;
        
        data.forEach(function(item, index) {
            const $item = $(`<div class="vdb-sugestao-item" data-index="${index}">${item.bairro} - ${item.cidade}</div>`);
            $sugestoes.append($item);
        });
        
        $sugestoes.show();
    }
    
    // Função para selecionar uma sugestão
    function selecionarSugestao(index) {
        $('.vdb-sugestao-item').removeClass('selected');
        
        if (index >= 0 && index < sugestoesList.length) {
            selectedIndex = index;
            const $selected = $(`.vdb-sugestao-item[data-index="${index}"]`);
            $selected.addClass('selected');
        }
    }
    
    // Função para verificar disponibilidade
    function verificarDisponibilidade(bairro) {
        // LIMPEZA RADICAL - Remover tudo que não seja o formulário principal
        $resultadoContainer.empty();
        $('.vdb-form-interessado, #vdb-form-container, #vdb-form-interessado, .cadastro-interesse').remove();
        
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
                        $resultadoContainer.html(`
                            <div class="vdb-resultado vdb-disponivel">
                                <p>Ótimas notícias! Atendemos no bairro <strong>${response.data.bairro}</strong> 
                                em <strong>${response.data.cidade}</strong>.</p>
                                <p>Caso queira conhecer nossos planos para sua casa, acesse <a href="/todos-planos/" class="vdb-link">planos residenciais</a>, 
                                para sua empresa, veja nossos <a href="/planos-empresariais/" class="vdb-link">planos empresariais</a> 
                                ou se preferir, <a href="https://wa.me/558005917287?text=Olá! Vi que vocês atendem no bairro ${response.data.bairro} e gostaria de mais informações." class="vdb-link" target="_blank">fale conosco pelo WhatsApp</a>.</p>
                            </div>
                        `);
                        
                        // Garantir que não haja formulários na página
                        $('.vdb-form-interessado, #vdb-form-container, #vdb-form-interessado, .cadastro-interesse').remove();
                    } else {
                        // Bairro não disponível
                        $resultadoContainer.html(`
                            <div class="vdb-resultado vdb-indisponivel">
                                <p>Desculpe, ainda não atendemos no bairro <strong>${response.data.termo}</strong>.</p>
                                <p>Estamos em expansão! Deixe seu contato para avisarmos quando chegarmos à sua região.</p>
                            </div>
                        `);
                        
                        // REMOVER QUALQUER FORMULÁRIO EXISTENTE
                        $('.vdb-form-interessado, #vdb-form-container, #vdb-form-interessado, .cadastro-interesse').remove();
                        
                        // Adicionar formulário DIRETAMENTE no DOM principal (sem containers aninhados)
                        const formHTML = `
                            <div class="vdb-form-interessado" id="vdb-form-unico">
                                <h3>Cadastre-se para ser avisado</h3>
                                <div class="vdb-campo-form">
                                    <label for="vdb_nome">Nome</label>
                                    <input type="text" id="vdb_nome" name="vdb_nome" required placeholder="Seu nome completo">
                                </div>
                                <div class="vdb-campo-form">
                                    <label for="vdb_email">E-mail</label>
                                    <input type="email" id="vdb_email" name="vdb_email" required placeholder="Seu melhor e-mail">
                                </div>
                                <div class="vdb-campo-form">
                                    <label for="vdb_telefone">Telefone</label>
                                    <input type="tel" id="vdb_telefone" name="vdb_telefone" required placeholder="(00) 00000-0000">
                                </div>
                                <input type="hidden" id="vdb_bairro_interesse" name="vdb_bairro_interesse" value="${response.data.termo}">
                                <button type="button" id="vdb-enviar-interesse" class="vdb-botao-interesse">Enviar</button>
                                <div id="vdb-mensagem-interesse"></div>
                            </div>
                        `;
                        
                        // Remover formulário existente e adicionar novo
                        $('#vdb-form-unico').remove();
                        $resultadoContainer.after(formHTML);
                        
                        // Adicionar evento para envio do formulário (depois de criado)
                        $('#vdb-enviar-interesse').on('click', function() {
                            const nome = $('#vdb_nome').val().trim();
                            const email = $('#vdb_email').val().trim();
                            const telefone = $('#vdb_telefone').val().trim();
                            const bairro = $('#vdb_bairro_interesse').val().trim();
                            
                            if (!nome || !email || !telefone) {
                                $('#vdb-mensagem-interesse').html('<p class="vdb-erro">Por favor, preencha todos os campos.</p>');
                                return;
                            }
                            
                            $('#vdb-enviar-interesse').prop('disabled', true).text('Enviando...');
                            
                            // Debugar dados
                            console.log('Enviando dados:', {
                                action: 'vdb_salvar_interessado',
                                nonce: vdbVars.nonce,
                                nome: nome,
                                email: email,
                                telefone: telefone,
                                bairro: bairro
                            });
                            
                            $.ajax({
                                url: vdbVars.ajaxurl,
                                type: 'POST',
                                data: {
                                    action: 'vdb_salvar_interessado',
                                    nonce: vdbVars.nonce,
                                    nome: nome,
                                    email: email,
                                    telefone: telefone,
                                    bairro: bairro
                                },
                                success: function(response) {
                                    console.log('Resposta recebida:', response);
                                    if (response.success) {
                                        $('#vdb-form-unico').html(`
                                            <div class="vdb-sucesso">
                                                <p><strong>Obrigado pelo seu interesse!</strong></p>
                                                <p>Seus dados foram salvos com sucesso. Entraremos em contato assim que disponibilizarmos atendimento em sua região.</p>
                                            </div>
                                        `);
                                    } else {
                                        $('#vdb-mensagem-interesse').html(`<p class="vdb-erro">${response.data.mensagem || 'Erro ao salvar seus dados. Tente novamente.'}</p>`);
                                        $('#vdb-enviar-interesse').prop('disabled', false).text('Enviar');
                                    }
                                },
                                error: function(xhr, status, error) {
                                    console.error('Erro AJAX:', status, error);
                                    $('#vdb-mensagem-interesse').html('<p class="vdb-erro">Erro ao enviar seus dados. Tente novamente mais tarde.</p>');
                                    $('#vdb-enviar-interesse').prop('disabled', false).text('Enviar');
                                }
                            });
                        });
                    }
                }
            }
        });
    }
    
    // Eventos de input
    $input.on('input', function() {
        const termo = $(this).val().trim();
        carregarSugestoes(termo);
        selectedIndex = -1;
    });
    
    // Evento de clique nas sugestões
    $sugestoes.on('click', '.vdb-sugestao-item', function() {
        const index = $(this).data('index');
        const item = sugestoesList[index];
        
        $input.val(item.bairro);
        $sugestoes.hide();
        
        verificarDisponibilidade(item.bairro);
    });
    
    // Evento de submit do formulário
    $form.on('submit', function(e) {
        e.preventDefault();
        const bairro = $input.val().trim();
        
        if (bairro.length < 2) {
            return;
        }
        
        verificarDisponibilidade(bairro);
    });
    
    // Navegação pelo teclado
    $(document).on('keydown', function(e) {
        // Se as sugestões estiverem visíveis
        if ($sugestoes.is(':visible')) {
            // Seta para baixo
            if (e.which === 40) {
                e.preventDefault();
                selecionarSugestao(Math.min(selectedIndex + 1, sugestoesList.length - 1));
            }
            // Seta para cima
            else if (e.which === 38) {
                e.preventDefault();
                selecionarSugestao(Math.max(selectedIndex - 1, 0));
            }
            // Enter
            else if (e.which === 13 && selectedIndex >= 0) {
                e.preventDefault();
                const item = sugestoesList[selectedIndex];
                $input.val(item.bairro);
                $sugestoes.hide();
                verificarDisponibilidade(item.bairro);
            }
            // ESC
            else if (e.which === 27) {
                $sugestoes.hide();
            }
        }
    });
    
    // Clicar fora fecha as sugestões
    $(document).on('click', function(e) {
        if (!$(e.target).closest('.vdb-campo, .vdb-sugestoes').length) {
            $sugestoes.hide();
        }
    });
});
