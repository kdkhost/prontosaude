/**
 * ==============================================================================
 * PRONTO SAÚDE - SELETOR GLOBAL DE ÍCONES DINÂMICOS (ICON PICKER)
 * Transforma qualquer campo de ícone em um seletor visual e interativo com modal,
 * categorias organizadas, busca instantânea e pré-visualização em tempo real.
 * ==============================================================================
 */

(function (window, document) {
    'use strict';

    // Biblioteca Curada de Ícones para Clínicas, Saúde, Planos e Interface
    const ICON_DATABASE = [
        // 1. Saúde, Medicina & Odonto (med)
        { icon: 'fas fa-user-md', label: 'Médico Geral', cat: 'med', tags: 'doutor medico clinico medicina consulta homem saude' },
        { icon: 'fas fa-user-doctor', label: 'Especialista', cat: 'med', tags: 'doutor medico especialista consulta doutora' },
        { icon: 'fas fa-stethoscope', label: 'Estetoscópio', cat: 'med', tags: 'estetoscopio consulta diagnostico ausculta coracao' },
        { icon: 'fas fa-hospital', label: 'Hospital', cat: 'med', tags: 'hospital clinica predio centro medico pronto socorro' },
        { icon: 'fas fa-hospital-user', label: 'Atendimento', cat: 'med', tags: 'atendimento paciente recepcionista acolhimento' },
        { icon: 'fas fa-clinic-medical', label: 'Clínica Médica', cat: 'med', tags: 'clinica ambulatorio consultorio posto' },
        { icon: 'fas fa-heartbeat', label: 'Cardiologia', cat: 'med', tags: 'cardiologia coracao batimentos eletro pulso' },
        { icon: 'fas fa-tooth', label: 'Odontologia', cat: 'med', tags: 'dente dentista odonto molar sorriso limpeza' },
        { icon: 'fas fa-briefcase-medical', label: 'Maleta Médica', cat: 'med', tags: 'maleta kit primeiros socorros emergencia visita' },
        { icon: 'fas fa-first-aid', label: 'Primeiros Socorros', cat: 'med', tags: 'socorros cruz vermelha caixa atendimento rapido' },
        { icon: 'fas fa-ambulance', label: 'Ambulância', cat: 'med', tags: 'ambulancia resgate emergencia samu socorro' },
        { icon: 'fas fa-pills', label: 'Medicamentos', cat: 'med', tags: 'remedios pilulas farmacia tratamento comprimidos' },
        { icon: 'fas fa-tablets', label: 'Comprimidos', cat: 'med', tags: 'capsulas remedio medicamento dosagem' },
        { icon: 'fas fa-syringe', label: 'Injeção / Vacina', cat: 'med', tags: 'vacina injecao picada seringa imunizacao sangue' },
        { icon: 'fas fa-prescription-bottle-alt', label: 'Frasco Remédio', cat: 'med', tags: 'farmacia frasco xarope gota receita' },
        { icon: 'fas fa-band-aid', label: 'Curativo', cat: 'med', tags: 'curativo ferimento esparadrapo bandaid cicatriz' },
        { icon: 'fas fa-eye', label: 'Oftalmologia', cat: 'med', tags: 'olho visao otica oftalmologista vista' },
        { icon: 'fas fa-lungs', label: 'Pneumologia', cat: 'med', tags: 'pulmao respiracao ar folego asma' },
        { icon: 'fas fa-brain', label: 'Neurologia', cat: 'med', tags: 'cerebro mente neurologista memoria cabeca' },
        { icon: 'fas fa-bone', label: 'Ortopedia', cat: 'med', tags: 'osso ortopedia fratura coluna esqueleto' },
        { icon: 'fas fa-baby', label: 'Pediatria', cat: 'med', tags: 'bebe crianca pediatra infantil recem nascido' },
        { icon: 'fas fa-female', label: 'Ginecologia', cat: 'med', tags: 'mulher ginecologista saude da mulher obstetra' },
        { icon: 'fas fa-dna', label: 'Genética', cat: 'med', tags: 'dna celula genetica genoma biologia' },
        { icon: 'fas fa-wheelchair', label: 'Acessibilidade', cat: 'med', tags: 'cadeirante inclusao reabilitacao fisioterapia' },
        { icon: 'fas fa-crutch', label: 'Fisioterapia', cat: 'med', tags: 'muleta apoio recuperacao movimento' },
        { icon: 'fas fa-thermometer-half', label: 'Termômetro', cat: 'med', tags: 'febre temperatura medicacao grau' },
        { icon: 'fas fa-hand-holding-medical', label: 'Cuidado & Apoio', cat: 'med', tags: 'cuidado acolhimento carinho apoio saude' },
        { icon: 'fas fa-disease', label: 'Patologia', cat: 'med', tags: 'virus bacteria doenca controle infeccao' },

        // 2. Exames & Laboratório (exam)
        { icon: 'fas fa-flask', label: 'Exame de Sangue', cat: 'exam', tags: 'laboratorio sangue coleta frasco quimica exame' },
        { icon: 'fas fa-microscope', label: 'Microscópio', cat: 'exam', tags: 'microscopio biologia pesquisa analise laudo' },
        { icon: 'fas fa-vial', label: 'Tubo de Coleta', cat: 'exam', tags: 'tubo ensaio sangue urina fezes coleta' },
        { icon: 'fas fa-vials', label: 'Amostras Laboratoriais', cat: 'exam', tags: 'amostras tubos exames sorologia hemograma' },
        { icon: 'fas fa-x-ray', label: 'Raio-X / Imagem', cat: 'exam', tags: 'raio x radiologia tomografia ressonancia ultrassom' },
        { icon: 'fas fa-file-medical', label: 'Prontuário / Laudo', cat: 'exam', tags: 'laudo prontuario resultado papel exame' },
        { icon: 'fas fa-file-medical-alt', label: 'Resultado de Exame', cat: 'exam', tags: 'relatorio medico diagnostico impresso digital' },
        { icon: 'fas fa-file-prescription', label: 'Receituário', cat: 'exam', tags: 'receita prescricao medicacao orientacao' },
        { icon: 'fas fa-notes-medical', label: 'Histórico Médico', cat: 'exam', tags: 'historico anamnese ficha cadastro paciente' },
        { icon: 'fas fa-clipboard-check', label: 'Check-up Concluído', cat: 'exam', tags: 'checkup rotina prevencao aprovado' },

        // 3. Cartão Fidelidade, Planos & Benefícios (card)
        { icon: 'fas fa-id-card', label: 'Cartão Fidelidade', cat: 'card', tags: 'cartao fidelidade plano desconto associado cracha' },
        { icon: 'fas fa-id-card-alt', label: 'Cartão com Chip', cat: 'card', tags: 'cartao inteligente chip identificacao plano' },
        { icon: 'fas fa-id-badge', label: 'Crachá / Identificação', cat: 'card', tags: 'cracha identificacao credencial carteirinha' },
        { icon: 'fas fa-credit-card', label: 'Cartão de Crédito', cat: 'card', tags: 'pagamento credito debito mensalidade parcelamento' },
        { icon: 'fas fa-wallet', label: 'Carteira / Economia', cat: 'card', tags: 'carteira dinheiro economia bolso desconto' },
        { icon: 'fas fa-hand-holding-usd', label: 'Desconto Real', cat: 'card', tags: 'desconto economia dinheiro preco baixo acessivel' },
        { icon: 'fas fa-percentage', label: 'Até 50% Off', cat: 'card', tags: 'porcentagem percentual desconto promocao beneficio' },
        { icon: 'fas fa-percent', label: 'Taxa / Desconto', cat: 'card', tags: 'porcentagem desconto economia promocao' },
        { icon: 'fas fa-tag', label: 'Etiqueta de Plano', cat: 'card', tags: 'tag preco oferta plano pacote valor' },
        { icon: 'fas fa-tags', label: 'Pacotes Promocionais', cat: 'card', tags: 'promocoes pacotes combos planos etiquetas' },
        { icon: 'fas fa-piggy-bank', label: 'Cofrinho Economia', cat: 'card', tags: 'cofrinho poupanca economia mensalidade' },
        { icon: 'fas fa-money-bill-wave', label: 'Dinheiro / Pagamento', cat: 'card', tags: 'dinheiro valor moeda pagamento a vista' },
        { icon: 'fas fa-coins', label: 'Moedas / Valores', cat: 'card', tags: 'moedas economia centavos taxa' },
        { icon: 'fas fa-receipt', label: 'Comprovante / Recibo', cat: 'card', tags: 'recibo nota fiscal comprovante pagamento' },
        { icon: 'fas fa-shield-alt', label: 'Sem Carências', cat: 'card', tags: 'seguranca protecao garantia sem carencia' },
        { icon: 'fas fa-gem', label: 'Plano VIP / Premium', cat: 'card', tags: 'diamante vip premium ouro top saude joia' },
        { icon: 'fas fa-crown', label: 'Plano Master', cat: 'card', tags: 'coroa lider master superior ouro destaque' },
        { icon: 'fas fa-medal', label: 'Benefício Exclusivo', cat: 'card', tags: 'medalha conquista primeiro lugar premio' },

        // 4. Atendimento, Contato & Localização (contact)
        { icon: 'fab fa-whatsapp', label: 'WhatsApp', cat: 'contact', tags: 'whatsapp zap mensagem suporte contato conversa' },
        { icon: 'fas fa-phone-alt', label: 'Telefone Fixo', cat: 'contact', tags: 'telefone ligacao central chamada ramal' },
        { icon: 'fas fa-phone', label: 'Telefone', cat: 'contact', tags: 'telefone chamada ligue contato' },
        { icon: 'fas fa-headset', label: 'Central de Apoio', cat: 'contact', tags: 'headset fone atendente teleatendimento suporte' },
        { icon: 'fas fa-comments', label: 'Mensagens / Chat', cat: 'contact', tags: 'chat conversa mensagens duvidas atendimento' },
        { icon: 'fas fa-comment-medical', label: 'Suporte Clínico', cat: 'contact', tags: 'orientacao balcao duvida medica' },
        { icon: 'fas fa-clock', label: 'Horário / Agilidade', cat: 'contact', tags: 'relogio hora atendimento rapido sem espera' },
        { icon: 'fas fa-calendar-alt', label: 'Agendamento Online', cat: 'contact', tags: 'calendario agendar consulta dia data' },
        { icon: 'fas fa-calendar-check', label: 'Consulta Confirmada', cat: 'contact', tags: 'agendado confirmado marcado horario' },
        { icon: 'fas fa-map-marker-alt', label: 'Localização / Bairro', cat: 'contact', tags: 'mapa endereco local gardenia azul jacarepagua onde' },
        { icon: 'fas fa-map-pin', label: 'Ponto no Mapa', cat: 'contact', tags: 'marcador endereco ponto unidade' },
        { icon: 'fas fa-directions', label: 'Como Chegar', cat: 'contact', tags: 'rota trajeto direcao transito acesso' },
        { icon: 'fas fa-envelope', label: 'E-mail Oficial', cat: 'contact', tags: 'email mensagem correio eletrônico contato' },
        { icon: 'fas fa-envelope-open-text', label: 'Aviso por E-mail', cat: 'contact', tags: 'notificacao comunicado mensagem' },
        { icon: 'fas fa-bell', label: 'Lembretes e Avisos', cat: 'contact', tags: 'alerta sino notificacao lembrete' },
        { icon: 'fas fa-question-circle', label: 'Dúvidas Frequentes', cat: 'contact', tags: 'faq duvidas perguntas respostas ajuda' },

        // 5. Métricas, Destaques & Qualidade (stats)
        { icon: 'fas fa-star', label: 'Avaliação Estrela', cat: 'stats', tags: 'estrela nota satisfacao qualidade excelencia' },
        { icon: 'fas fa-star-half-alt', label: 'Meia Estrela', cat: 'stats', tags: 'estrela metade avaliacao ranking' },
        { icon: 'fas fa-thumbs-up', label: 'Recomendação Positiva', cat: 'stats', tags: 'positivo legal aprovado recomendo curtiu' },
        { icon: 'fas fa-check-circle', label: 'Aprovado / Sucesso', cat: 'stats', tags: 'check verificado correto garantido liberado' },
        { icon: 'fas fa-chart-line', label: 'Crescimento & Métricas', cat: 'stats', tags: 'grafico subida estatistica evolução' },
        { icon: 'fas fa-chart-pie', label: 'Gráfico de Indicadores', cat: 'stats', tags: 'pizza relatorio indicadores dados' },
        { icon: 'fas fa-chart-bar', label: 'Barra de Desempenho', cat: 'stats', tags: 'barras metricas pesquisa estatistica' },
        { icon: 'fas fa-users', label: 'Milhares de Pacientes', cat: 'stats', tags: 'pacientes equipe pessoas familia comunidade' },
        { icon: 'fas fa-user-friends', label: 'Família Completa', cat: 'stats', tags: 'amigos casal familia dependentes' },
        { icon: 'fas fa-smile', label: 'Paciente Satisfeito', cat: 'stats', tags: 'sorriso feliz satisfacao alegria contente' },
        { icon: 'fas fa-award', label: 'Premiação / Mérito', cat: 'stats', tags: 'premio trofeu reconhecimento excelencia' },
        { icon: 'fas fa-certificate', label: 'Certificação Médica', cat: 'stats', tags: 'diploma certificado alvara selo de qualidade' },
        { icon: 'fas fa-fire', label: 'Mais Escolhido', cat: 'stats', tags: 'fogo destaque popular mais procurado' },
        { icon: 'fas fa-rocket', label: 'Rapidez Extrema', cat: 'stats', tags: 'foguete agil rapido veloz sem filas' },
        { icon: 'fas fa-bolt', label: 'Instantâneo', cat: 'stats', tags: 'raio relampago energia imediato' },

        // 6. Geral & Interface (general)
        { icon: 'fas fa-home', label: 'Início / Home', cat: 'general', tags: 'casa inicio principal sede' },
        { icon: 'fas fa-building', label: 'Edifício / Estrutura', cat: 'general', tags: 'predio sede estrutura instalacoes' },
        { icon: 'fas fa-search', label: 'Pesquisa', cat: 'general', tags: 'lupa buscar achar localizar' },
        { icon: 'fas fa-info-circle', label: 'Informação', cat: 'general', tags: 'info detalhes saber mais instrucao' },
        { icon: 'fas fa-arrow-right', label: 'Seta Direcional', cat: 'general', tags: 'seta ir prosseguir continuar proximo' },
        { icon: 'fas fa-external-link-alt', label: 'Link Externo', cat: 'general', tags: 'link externo abrir nova aba' },
        { icon: 'fas fa-link', label: 'Vínculo / Link', cat: 'general', tags: 'link conexao atalho url' },
        { icon: 'fas fa-lock', label: 'Segurança / LGPD', cat: 'general', tags: 'cadeado seguro dados privacidade' },
        { icon: 'fas fa-shield-virus', label: 'Biossegurança', cat: 'general', tags: 'esterilizado limpo seguro protecao virus' },
        { icon: 'fas fa-globe', label: 'Portal Online', cat: 'general', tags: 'globo internet web site mundo' },
        { icon: 'fas fa-cog', label: 'Configurações', cat: 'general', tags: 'engrenagem ajuste sistema configurar' },
        { icon: 'fas fa-sliders-h', label: 'Filtros & Opções', cat: 'general', tags: 'filtros ajustes parametros' },
        { icon: 'fas fa-qrcode', label: 'QR Code', cat: 'general', tags: 'qrcode codigo celular camera acesso' },
        { icon: 'fas fa-barcode', label: 'Código de Barras', cat: 'general', tags: 'codigo barras boleto pagamento' },
        { icon: 'fas fa-bookmark', label: 'Favoritos / Salvo', cat: 'general', tags: 'marcador salvo favorito guardar' },
        { icon: 'fas fa-bullseye', label: 'Precisão / Alvo', cat: 'general', tags: 'alvo precisao objetivo foco' }
    ];

    // Mapeamento de Categorias
    const CATEGORIES = [
        { id: 'all', name: 'Todos os Ícones', icon: 'bi-grid-fill' },
        { id: 'med', name: 'Saúde & Medicina', icon: 'bi-heart-pulse-fill' },
        { id: 'exam', name: 'Exames & Diagnósticos', icon: 'bi-file-earmark-medical-fill' },
        { id: 'card', name: 'Cartão & Planos', icon: 'bi-credit-card-2-front-fill' },
        { id: 'contact', name: 'Contato & Atendimento', icon: 'bi-chat-dots-fill' },
        { id: 'stats', name: 'Métricas & Destaques', icon: 'bi-star-fill' },
        { id: 'general', name: 'Interface & Geral', icon: 'bi-compass-fill' }
    ];

    let currentTargetInput = null;
    let modalInstance = null;
    let currentFilterCat = 'all';

    /**
     * Cria e injeta o Modal Global do Seletor de Ícones no DOM
     */
    function createModalDom() {
        if (document.getElementById('prontoIconPickerModal')) return;

        const modalHtml = `
        <div class="modal fade" id="prontoIconPickerModal" tabindex="-1" aria-labelledby="prontoIconPickerModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
                <div class="modal-content shadow-lg">
                    <div class="modal-header d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center gap-2">
                            <div class="bg-white text-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                                <i class="bi bi-grid-3x3-gap-fill fs-5"></i>
                            </div>
                            <div>
                                <h5 class="modal-title fw-bold mb-0 text-white" id="prontoIconPickerModalLabel">Selecione um Ícone</h5>
                                <small class="text-white-50" id="prontoIconTargetDesc">Clique no ícone desejado para inseri-lo no campo</small>
                            </div>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                    </div>

                    <div class="modal-body p-3 p-md-4">
                        <!-- Barra de Busca Instantânea -->
                        <div class="icon-search-wrap mb-3">
                            <i class="bi bi-search"></i>
                            <input type="text" class="form-control icon-search-input" id="prontoIconSearch" placeholder="Pesquise por nome, especialidade ou palavra-chave (ex: medico, dente, cartao, whatsapp, estrela)..." autocomplete="off">
                            <button type="button" class="btn-clear-search" id="prontoBtnClearSearch" title="Limpar busca">
                                <i class="bi bi-x-circle-fill"></i>
                            </button>
                        </div>

                        <!-- Filtro por Categorias (Pills) -->
                        <div class="icon-cat-pills mb-3" id="prontoIconCatPills"></div>

                        <!-- Contador de Resultados -->
                        <div class="d-flex align-items-center justify-content-between mb-2 px-1">
                            <small class="text-secondary fw-semibold" id="prontoIconCount">Carregando ícones...</small>
                            <small class="text-muted fst-italic">Dica: Selecione qualquer ícone com 1 clique</small>
                        </div>

                        <!-- Grid Visual de Ícones -->
                        <div class="pronto-icon-grid" id="prontoIconGrid"></div>
                    </div>

                    <div class="modal-footer bg-body-tertiary d-flex align-items-center justify-content-between py-2 px-3">
                        <div class="d-flex align-items-center gap-2">
                            <span class="small text-secondary">Ícone Atual:</span>
                            <span class="badge text-bg-primary-subtle text-primary border border-primary-subtle px-2 py-1 font-monospace" id="prontoCurrentBadge">
                                <i id="prontoCurrentBadgeIcon" class="fas fa-icons me-1"></i> <span id="prontoCurrentBadgeClass">nenhum</span>
                            </span>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <button type="button" class="btn btn-outline-danger btn-sm" id="prontoBtnRemoveIcon">
                                <i class="bi bi-trash3 me-1"></i> Remover Ícone
                            </button>
                            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">
                                Fechar
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>`;

        document.body.insertAdjacentHTML('beforeend', modalHtml);

        // Renderiza as categorias
        const catContainer = document.getElementById('prontoIconCatPills');
        if (catContainer) {
            CATEGORIES.forEach(cat => {
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className = `icon-cat-btn ${cat.id === 'all' ? 'active' : ''}`;
                btn.dataset.category = cat.id;
                btn.innerHTML = `<i class="bi ${cat.icon} me-1"></i>${cat.name}`;
                btn.addEventListener('click', function () {
                    document.querySelectorAll('#prontoIconCatPills .icon-cat-btn').forEach(b => b.classList.remove('active'));
                    this.classList.add('active');
                    currentFilterCat = this.dataset.category;
                    renderIcons();
                });
                catContainer.appendChild(btn);
            });
        }

        // Eventos de Busca
        const searchInput = document.getElementById('prontoIconSearch');
        const clearBtn = document.getElementById('prontoBtnClearSearch');

        if (searchInput) {
            searchInput.addEventListener('input', function () {
                if (clearBtn) {
                    clearBtn.style.display = this.value.trim() ? 'block' : 'none';
                }
                renderIcons();
            });
        }

        if (clearBtn) {
            clearBtn.addEventListener('click', function () {
                if (searchInput) {
                    searchInput.value = '';
                    this.style.display = 'none';
                    searchInput.focus();
                    renderIcons();
                }
            });
        }

        // Botão de Limpar / Remover Ícone
        const btnRemove = document.getElementById('prontoBtnRemoveIcon');
        if (btnRemove) {
            btnRemove.addEventListener('click', function () {
                if (currentTargetInput) {
                    currentTargetInput.value = '';
                    currentTargetInput.dispatchEvent(new Event('input', { bubbles: true }));
                    currentTargetInput.dispatchEvent(new Event('change', { bubbles: true }));
                    updateInputPreview(currentTargetInput);
                    if (window.toastr) {
                        toastr.info('Ícone removido com sucesso.', '', { timeOut: 1800, positionClass: 'toast-bottom-right' });
                    }
                }
                if (modalInstance) modalInstance.hide();
            });
        }
    }

    /**
     * Renderiza o grid de ícones baseado na categoria e busca
     */
    function renderIcons() {
        const grid = document.getElementById('prontoIconGrid');
        const countEl = document.getElementById('prontoIconCount');
        const searchInput = document.getElementById('prontoIconSearch');
        if (!grid) return;

        const query = (searchInput?.value || '').toLowerCase().trim();
        const currentVal = (currentTargetInput?.value || '').trim();

        const filtered = ICON_DATABASE.filter(item => {
            const matchesCat = (currentFilterCat === 'all' || item.cat === currentFilterCat);
            if (!matchesCat) return false;

            if (!query) return true;
            return item.label.toLowerCase().includes(query) ||
                   item.icon.toLowerCase().includes(query) ||
                   item.tags.toLowerCase().includes(query);
        });

        if (countEl) {
            countEl.textContent = `${filtered.length} ícone(s) encontrado(s)`;
        }

        if (filtered.length === 0) {
            grid.innerHTML = `
                <div class="pronto-icon-empty">
                    <i class="bi bi-search"></i>
                    <p class="mb-1 fw-semibold">Nenhum ícone encontrado para "${query}"</p>
                    <small class="text-muted">Tente buscar por termos médicos, odontologia, cartões ou contato.</small>
                </div>
            `;
            return;
        }

        let html = '';
        filtered.forEach(item => {
            const isSelected = (currentVal === item.icon);
            html += `
                <div class="pronto-icon-item ${isSelected ? 'selected' : ''}" data-icon="${item.icon}" title="${item.label} (${item.icon})">
                    <i class="${item.icon}"></i>
                    <span class="pronto-icon-label">${item.label}</span>
                    <span class="pronto-icon-class">${item.icon}</span>
                </div>
            `;
        });

        grid.innerHTML = html;

        // Adiciona evento de clique em cada item
        grid.querySelectorAll('.pronto-icon-item').forEach(el => {
            el.addEventListener('click', function () {
                const iconClass = this.getAttribute('data-icon');
                selectIcon(iconClass);
            });
        });
    }

    /**
     * Aplica o ícone selecionado ao campo de destino
     */
    function selectIcon(iconClass) {
        if (!currentTargetInput) return;

        currentTargetInput.value = iconClass;
        currentTargetInput.dispatchEvent(new Event('input', { bubbles: true }));
        currentTargetInput.dispatchEvent(new Event('change', { bubbles: true }));
        updateInputPreview(currentTargetInput);

        if (window.toastr) {
            toastr.success(`Ícone <strong>${iconClass}</strong> selecionado!`, 'Pronto!', {
                timeOut: 2000,
                positionClass: 'toast-bottom-right'
            });
        }

        if (modalInstance) {
            modalInstance.hide();
        }
    }

    /**
     * Atualiza o badge de pré-visualização ao lado do input
     */
    function updateInputPreview(input) {
        if (!input) return;
        const group = input.closest('.pronto-icon-picker-group');
        if (!group) return;

        const previewIcon = group.querySelector('.pronto-icon-preview i');
        if (!previewIcon) return;

        const val = input.value.trim();
        if (val) {
            previewIcon.className = val;
        } else {
            previewIcon.className = 'bi bi-question-lg text-secondary';
        }
    }

    /**
     * Abre o modal do Icon Picker direcionado para o input especificado
     */
    function openPickerFor(input) {
        currentTargetInput = input;
        createModalDom();

        const modalEl = document.getElementById('prontoIconPickerModal');
        if (!modalEl) return;

        // Atualiza cabeçalho e badge atual
        const targetDesc = document.getElementById('prontoIconTargetDesc');
        const badgeIcon = document.getElementById('prontoCurrentBadgeIcon');
        const badgeClass = document.getElementById('prontoCurrentBadgeClass');
        const searchInput = document.getElementById('prontoIconSearch');

        const currentVal = input.value.trim();
        const inputLabel = input.closest('.mb-1, .mb-2, .mb-3, div')?.querySelector('label')?.textContent || input.getAttribute('name') || 'Campo';

        if (targetDesc) {
            targetDesc.textContent = `Escolhendo ícone para: ${inputLabel.replace('Ícone', '').replace('FontAwesome', '').trim() || 'Campo'}`;
        }

        if (badgeIcon && badgeClass) {
            if (currentVal) {
                badgeIcon.className = `${currentVal} me-1`;
                badgeClass.textContent = currentVal;
            } else {
                badgeIcon.className = 'bi bi-dash-circle me-1';
                badgeClass.textContent = 'nenhum';
            }
        }

        if (searchInput) {
            searchInput.value = '';
            const clearBtn = document.getElementById('prontoBtnClearSearch');
            if (clearBtn) clearBtn.style.display = 'none';
        }

        // Reseta para categoria "Todos" ou mantém
        currentFilterCat = 'all';
        document.querySelectorAll('#prontoIconCatPills .icon-cat-btn').forEach(b => {
            b.classList.toggle('active', b.dataset.category === 'all');
        });

        renderIcons();

        if (!modalInstance && window.bootstrap && window.bootstrap.Modal) {
            modalInstance = new window.bootstrap.Modal(modalEl);
        }

        if (modalInstance) {
            modalInstance.show();
            setTimeout(() => {
                searchInput?.focus();
            }, 300);
        }
    }

    /**
     * Envolve e inicializa um input como Icon Picker interativo
     */
    function initIconInput(input) {
        if (!input || input.dataset.iconPickerInit === 'true') return;
        input.dataset.iconPickerInit = 'true';

        // Verifica se já está em um .pronto-icon-picker-group
        let group = input.closest('.pronto-icon-picker-group');
        if (!group) {
            // Cria dinamicamente o wrapper input-group
            group = document.createElement('div');
            group.className = 'input-group input-group-sm pronto-icon-picker-group';

            input.parentNode.insertBefore(group, input);

            const preview = document.createElement('span');
            preview.className = 'input-group-text pronto-icon-preview text-primary bg-body';
            preview.title = 'Clique para escolher outro ícone';
            preview.innerHTML = `<i class="${input.value.trim() || 'bi bi-question-lg'}"></i>`;

            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'btn btn-outline-primary btn-sm btn-open-icon-picker';
            btn.title = 'Abrir catálogo visual de ícones';
            btn.innerHTML = '<i class="bi bi-grid-3x3-gap-fill me-1"></i> Escolher';

            group.appendChild(preview);
            group.appendChild(input);
            group.appendChild(btn);
        }

        input.classList.add('pronto-icon-input');

        // Atualiza preview em tempo real ao digitar
        input.addEventListener('input', function () {
            updateInputPreview(this);
        });

        // Clique no preview abre o seletor
        const previewEl = group.querySelector('.pronto-icon-preview');
        if (previewEl) {
            previewEl.addEventListener('click', function () {
                openPickerFor(input);
            });
        }

        // Clique no botão "Escolher" abre o seletor
        const btnEl = group.querySelector('.btn-open-icon-picker');
        if (btnEl) {
            btnEl.addEventListener('click', function () {
                openPickerFor(input);
            });
        }

        // Inicializa o preview inicial
        updateInputPreview(input);
    }

    /**
     * Varre a página procurando todos os campos de ícones para inicializar
     */
    function autoDiscover() {
        // 1. Inputs com classe ou data-attribute explícitos
        const explicitInputs = document.querySelectorAll('.pronto-icon-input, input[data-icon-picker], input.icon-picker');
        explicitInputs.forEach(initIconInput);

        // 2. Inputs de texto cujo nome termine com _icon ou contenha icon
        const iconNameInputs = document.querySelectorAll('input[type="text"][name$="_icon"], input[type="text"][name*="icon_"], input[type="text"][name="icon"]');
        iconNameInputs.forEach(initIconInput);
    }

    // Inicialização automática após carregamento do DOM
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', autoDiscover);
    } else {
        autoDiscover();
    }

    // Exporta utilitário globalmente para reuso e SPA
    window.ProntoIconPicker = {
        init: autoDiscover,
        open: openPickerFor,
        database: ICON_DATABASE
    };

})(window, document);
