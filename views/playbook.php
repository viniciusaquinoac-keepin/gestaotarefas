<?php
$abaAtiva = $abaAtiva ?? 'autoitec';
?>

<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
    <div>
        <div class="d-flex align-items-center gap-2 mb-1">
            <span class="badge bg-primary fs-6"><i class="bi bi-journal-bookmark-fill"></i> Treinamento & Metodologia Comercial</span>
            <span class="badge bg-secondary">Versão 2.0</span>
        </div>
        <h1 class="h3 text-light mb-0">Playbooks Comerciais do Grupo Empresarial</h1>
    </div>

    <!-- Campo de Busca Rápida no Playbook -->
    <div class="d-flex align-items-center gap-2">
        <div class="input-group input-group-sm">
            <span class="input-group-text bg-dark border-secondary text-secondary"><i class="bi bi-search"></i></span>
            <input type="text" id="playbookSearch" class="form-control form-control-sm bg-dark text-light border-secondary" placeholder="Buscar no playbook..." onkeyup="filtrarPlaybook()">
        </div>
    </div>
</div>

<!-- ABAS INTERATIVAS NO TOPO -->
<ul class="nav nav-pills mb-4 gap-2 border-bottom border-secondary pb-3" id="playbookTabs" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link <?= $abaAtiva === 'autoitec' ? 'active bg-primary' : 'text-light bg-dark border border-secondary' ?> fw-bold px-4 py-2" id="autoitec-tab" data-bs-toggle="tab" data-bs-target="#tab-autoitec" type="button" role="tab">
            <i class="bi bi-gear-wide-connected me-2"></i> Playbook Autoitec (Industrial B2B)
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link <?= $abaAtiva === 'keepin' ? 'active bg-success' : 'text-light bg-dark border border-secondary' ?> fw-bold px-4 py-2" id="keepin-tab" data-bs-toggle="tab" data-bs-target="#tab-keepin" type="button" role="tab">
            <i class="bi bi-shop me-2"></i> Playbook Keepin (IoT Varejo & Field Sales)
        </button>
    </li>
</ul>

<div class="tab-content" id="playbookTabContent">

    <!-- ========================================== -->
    <!-- ABA 1: PLAYBOOK AUTOITEC (B2B INDUSTRIAL) -->
    <!-- ========================================== -->
    <div class="tab-pane fade <?= $abaAtiva === 'autoitec' ? 'show active' : '' ?>" id="tab-autoitec" role="tabpanel">
        
        <!-- Bloco Modelo de Negócio -->
        <div class="card bg-dark border-primary mb-4 shadow-sm playbook-section">
            <div class="card-header bg-primary bg-opacity-25 border-primary py-3">
                <h5 class="card-title text-light mb-0"><i class="bi bi-shield-check text-primary me-2"></i> Modelo de Negócio: Automação por Assinatura / Comodato & Programa Risco Zero (90 dias)</h5>
            </div>
            <div class="card-body p-4">
                <div class="row g-4">
                    <div class="col-md-6">
                        <h6 class="text-primary fw-bold mb-2">Por que o cliente industrial compra?</h6>
                        <p class="text-secondary small leading-relaxed">
                            No modelo tradicional, indústrias hesitam em aprovar projetos de automação devido ao <strong>CAPEX elevado</strong> (R$ 100k a R$ 500k de desembolso inicial) e ao medo de o fornecedor entregar o painel e abandonar o suporte técnico.
                        </p>
                        <p class="text-secondary small leading-relaxed">
                            A <strong>Autoitec</strong> elimina essa objeção transformando o projeto em <strong>OPEX por Assinatura / Comodato</strong>: a indústria não descapitaliza, nós instalamos e mantemos os painéis, CLPs e telemetria funcionando com SLA estrito de manutenção contínua.
                        </p>
                    </div>
                    <div class="col-md-6">
                        <div class="bg-primary bg-opacity-10 border border-primary rounded p-3">
                            <h6 class="text-light fw-bold mb-2"><i class="bi bi-award-fill text-warning me-1"></i> O Que é o "Programa Risco Zero (90 Dias)"?</h6>
                            <ul class="text-light small mb-0 ps-3">
                                <li class="mb-1">O cliente assina a automação sem cláusula de fidelidade nos primeiros 90 dias.</li>
                                <li class="mb-1">Se os indicadores de eficiência, redução de perdas ou rastreabilidade prometidos não forem comprovados no dashboard, o contrato pode ser cancelado sem multa.</li>
                                <li>Isso transfere todo o risco da decisão para a Autoitec, facilitando a assinatura imediata pela diretoria.</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bloco SPIN Selling Interativo -->
        <div class="card bg-dark border-secondary mb-4 shadow-sm playbook-section">
            <div class="card-header bg-dark border-secondary py-3 d-flex justify-content-between align-items-center">
                <h5 class="card-title text-light mb-0"><i class="bi bi-lightbulb-fill text-info me-2"></i> Metodologia Prática: SPIN Selling na Indústria</h5>
                <span class="badge bg-info text-dark">4 Fases de Diagnóstico</span>
            </div>
            <div class="card-body p-4">
                <p class="text-secondary small mb-3">
                    Clique em cada etapa para ver exemplos de perguntas investigativas para fazer em reuniões técnicas com gerentes de fábrica e diretores de operações:
                </p>

                <div class="accordion accordion-flush" id="spinAccordion">
                    
                    <!-- [S] Situação -->
                    <div class="accordion-item bg-dark border-secondary">
                        <h2 class="accordion-header">
                            <button class="accordion-button bg-dark text-info fw-bold collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#spin-s">
                                [S] Perguntas de SITUAÇÃO (Mapeamento de Cenário Atual)
                            </button>
                        </h2>
                        <div id="spin-s" class="accordion-collapse collapse show" data-bs-parent="#spinAccordion">
                            <div class="accordion-body text-secondary small bg-secondary bg-opacity-10 border-top border-secondary">
                                <p class="text-light fw-bold mb-2">Objetivo: Levantar fatos, dados e a infraestrutura fabril sem parecer interrogatório.</p>
                                <ul class="mb-0">
                                    <li><em>"Qual a marca e modelo dos CLPs e inversores que controlam essa linha atualmente?"</em></li>
                                    <li><em>"Quantas toneladas/peças por turno esta unidade tem capacidade de processar?"</em></li>
                                    <li><em>"Como a sua equipe monitora hoje as temperaturas, correntes e vibrações dos motores críticos?"</em></li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- [P] Problema -->
                    <div class="accordion-item bg-dark border-secondary">
                        <h2 class="accordion-header">
                            <button class="accordion-button bg-dark text-warning fw-bold collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#spin-p">
                                [P] Perguntas de PROBLEMA (Descobrindo Dores e Gargalos)
                            </button>
                        </h2>
                        <div id="spin-p" class="accordion-collapse collapse" data-bs-parent="#spinAccordion">
                            <div class="accordion-body text-secondary small bg-secondary bg-opacity-10 border-top border-secondary">
                                <p class="text-light fw-bold mb-2">Objetivo: Fazer o cliente admitir as insatisfações com o processo manual ou obsoleto.</p>
                                <ul class="mb-0">
                                    <li><em>"Com que frequência ocorrem paradas não programadas por falhas elétricas ou travamento de relés?"</em></li>
                                    <li><em>"Existe muita variação de qualidade ou desperdício de matéria-prima durante a troca de turnos?"</em></li>
                                    <li><em>"Qual é a maior dor de cabeça do seu gerente de manutenção aos finais de semana?"</em></li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- [I] Implicação -->
                    <div class="accordion-item bg-dark border-secondary">
                        <h2 class="accordion-header">
                            <button class="accordion-button bg-dark text-danger fw-bold collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#spin-i">
                                [I] Perguntas de IMPLICAÇÃO (A FASE MAIS CRÍTICA - Quantificando em R$)
                            </button>
                        </h2>
                        <div id="spin-i" class="accordion-collapse collapse" data-bs-parent="#spinAccordion">
                            <div class="accordion-body text-secondary small bg-secondary bg-opacity-10 border-top border-secondary">
                                <p class="text-light fw-bold mb-2">Objetivo: Fazer o cliente sentir a gravidade do problema antes de falar em preço. Se não quantificar em dinheiro, o projeto vira "despesa".</p>
                                <ul class="mb-0">
                                    <li><em>"Quando essa linha de produção para por 3 horas, qual é o prejuízo em horas de operadores parados e pedidos atrasados?"</em></li>
                                    <li><em>"Se um motor queimar sem alerta prévio, quanto custa o rebobinamento urgente e o refugo gerado?"</em></li>
                                    <li><em>"Esse atraso de entrega já colocou em risco o contrato com seus maiores clientes?"</em></li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- [N] Necessidade -->
                    <div class="accordion-item bg-dark border-secondary">
                        <h2 class="accordion-header">
                            <button class="accordion-button bg-dark text-success fw-bold collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#spin-n">
                                [N] Perguntas de NECESSIDADE DE SOLUÇÃO (O Cliente se Auto-Convence)
                            </button>
                        </h2>
                        <div id="spin-n" class="accordion-collapse collapse" data-bs-parent="#spinAccordion">
                            <div class="accordion-body text-secondary small bg-secondary bg-opacity-10 border-top border-secondary">
                                <p class="text-light fw-bold mb-2">Objetivo: Conduzir o cliente a declarar os benefícios da automação Autoitec.</p>
                                <ul class="mb-0">
                                    <li><em>"Se você pudesse receber no seu celular um alerta 2 horas antes de qualquer superaquecimento, como isso impactaria sua tranquilidade?"</em></li>
                                    <li><em>"Reduzir o refugo em 15% pagaria tranquilamente uma mensalidade de automação por assinatura?"</em></li>
                                    <li><em>"Ter relatórios automáticos de OEE facilitaria a prestação de contas para a diretoria?"</em></li>
                                </ul>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <!-- Bloco Checklist MEDDPICC -->
        <div class="card bg-dark border-secondary mb-4 shadow-sm playbook-section">
            <div class="card-header bg-dark border-secondary py-3 d-flex justify-content-between align-items-center">
                <h5 class="card-title text-light mb-0"><i class="bi bi-check2-circle text-primary me-2"></i> Framework MEDDPICC (Liberação de Propostas B2B)</h5>
                <span class="badge bg-primary">Qualificação Enterprise</span>
            </div>
            <div class="card-body p-4">
                <p class="text-secondary small mb-3">
                    Antes de gastar horas de engenharia elaborando uma proposta complexa, o vendedor deve validar os 8 critérios no sistema:
                </p>
                <div class="row g-3">
                    <div class="col-md-3">
                        <div class="bg-secondary bg-opacity-10 border border-secondary rounded p-3 h-100">
                            <strong class="text-primary d-block mb-1">[M] Metrics</strong>
                            <small class="text-secondary">Qual o retorno financeiro mensurável (ROI, payback, economia de insumos)?</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="bg-secondary bg-opacity-10 border border-secondary rounded p-3 h-100">
                            <strong class="text-primary d-block mb-1">[E] Economic Buyer</strong>
                            <small class="text-secondary">Quem tem a caneta para assinar o cheque (Diretor Industrial, CEO, CFO)?</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="bg-secondary bg-opacity-10 border border-secondary rounded p-3 h-100">
                            <strong class="text-primary d-block mb-1">[D] Decision Criteria</strong>
                            <small class="text-secondary">Quais os parâmetros técnicos (protocolo Modbus/MQTT, robustez, garantia)?</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="bg-secondary bg-opacity-10 border border-secondary rounded p-3 h-100">
                            <strong class="text-primary d-block mb-1">[D] Decision Process</strong>
                            <small class="text-secondary">Quais as etapas burocráticas internas até a emissão do Pedido de Compras?</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="bg-secondary bg-opacity-10 border border-secondary rounded p-3 h-100">
                            <strong class="text-primary d-block mb-1">[P] Paper Process</strong>
                            <small class="text-secondary">Como funciona a aprovação jurídica, cadastral e a minuta de comodato?</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="bg-secondary bg-opacity-10 border border-secondary rounded p-3 h-100">
                            <strong class="text-primary d-block mb-1">[I] Identify Pain</strong>
                            <small class="text-secondary">A dor é urgente e latente ou é apenas um desejo secundário?</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="bg-secondary bg-opacity-10 border border-secondary rounded p-3 h-100">
                            <strong class="text-primary d-block mb-1">[C] Champion</strong>
                            <small class="text-secondary">Temos um aliado interno (ex: coordenador de elétrica) defendendo nosso projeto?</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="bg-secondary bg-opacity-10 border border-secondary rounded p-3 h-100">
                            <strong class="text-primary d-block mb-1">[C] Competition</strong>
                            <small class="text-secondary">Estamos disputando contra integradores locais ou contra o "não fazer nada"?</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- ========================================== -->
    <!-- ABA 2: PLAYBOOK KEEPIN (FIELD SALES VAREJO) -->
    <!-- ========================================== -->
    <div class="tab-pane fade <?= $abaAtiva === 'keepin' ? 'show active' : '' ?>" id="tab-keepin" role="tabpanel">

        <!-- Bloco Modelo e Preços Keepin -->
        <div class="card bg-dark border-success mb-4 shadow-sm playbook-section">
            <div class="card-header bg-success bg-opacity-25 border-success py-3">
                <h5 class="card-title text-light mb-0"><i class="bi bi-tag-fill text-success me-2"></i> Tabela Comercial & Metas de Campo (Field Sales)</h5>
            </div>
            <div class="card-body p-4">
                <div class="row g-4 align-items-center">
                    <div class="col-md-7">
                        <h6 class="text-success fw-bold mb-2">Público-Alvo Prioritário:</h6>
                        <p class="text-secondary small leading-relaxed mb-3">
                            Supermercados, Mercearias, Açougues, Frigoríficos, Padarias e Casas de Frios. Qualquer estabelecimento com <strong>câmaras frias, ilhas congeladas ou balcões refrigerados</strong> onde o desligamento de um compressor signifique perda imediata de milhares de reais em mercadorias.
                        </p>
                        <div class="row g-2">
                            <div class="col-md-6">
                                <div class="bg-secondary bg-opacity-10 border border-secondary rounded p-3">
                                    <span class="text-secondary small d-block">Produto Principal Varejo:</span>
                                    <strong class="text-light fs-5">KPRemote Locação</strong>
                                    <div class="fs-4 fw-bold text-success mt-1">R$ 40,00 <span class="fs-6 text-secondary">/mês</span></div>
                                    <small class="text-secondary">Telemetria 24h em nuvem com alertas no WhatsApp.</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="bg-secondary bg-opacity-10 border border-secondary rounded p-3">
                                    <span class="text-secondary small d-block">Hardwares / Automação:</span>
                                    <strong class="text-light fs-5">Placas Keepin IoT</strong>
                                    <div class="fs-4 fw-bold text-primary mt-1">R$ 4.000,00</div>
                                    <small class="text-secondary">Automação de acionamentos, degelos e relés inteligentes.</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-5">
                        <div class="bg-warning bg-opacity-10 border border-warning rounded p-3">
                            <h6 class="text-warning fw-bold mb-2"><i class="bi bi-speedometer text-warning me-1"></i> Ritmo Obrigatório de Campo</h6>
                            <ul class="text-light small mb-0 ps-3">
                                <li class="mb-2"><strong>Meta Semanal:</strong> 15 visitas presenciais na rua por vendedor.</li>
                                <li class="mb-2"><strong>Registro Obrigatório:</strong> Toda visita deve ser registrada no botão <em>"Registrar Visita de Rua"</em> para pontuar no ranking.</li>
                                <li><strong>Pontuação:</strong> 2 pontos por visita registrada (até o teto de 30 pts no trimestre).</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bloco Script de Abordagem 10 Minutos -->
        <div class="card bg-dark border-secondary mb-4 shadow-sm playbook-section">
            <div class="card-header bg-dark border-secondary py-3 d-flex justify-content-between align-items-center">
                <h5 class="card-title text-light mb-0"><i class="bi bi-chat-quote-fill text-warning me-2"></i> Script de Abordagem de 10 Minutos: O Gatilho da Madrugada</h5>
                <span class="badge bg-warning text-dark">Passo a Passo de Rua</span>
            </div>
            <div class="card-body p-4">
                <div class="timeline-container ps-3 border-start border-secondary position-relative">
                    
                    <!-- Passo 1 -->
                    <div class="mb-4 position-relative">
                        <span class="position-absolute translate-middle p-2 bg-warning border border-light rounded-circle" style="left: -17px;"></span>
                        <h6 class="text-warning fw-bold mb-1">Minuto 1 a 2: Quebra de Gelo e Conexão com o Dono/Gerente</h6>
                        <div class="bg-secondary bg-opacity-10 rounded p-3 border border-secondary text-light small">
                            <em>"Bom dia/tarde, [Nome]. Meu nome é [Seu Nome], sou especialista em monitoramento de refrigeração comercial da Keepin. Estou visitando os principais mercados da região porque na semana passada um comerciante aqui próximo perdeu R$ 18 mil em carnes por um compressor desarmado no domingo de madrugada. Posso lhe fazer apenas uma pergunta rápida de 1 minuto?"</em>
                        </div>
                    </div>

                    <!-- Passo 2 -->
                    <div class="mb-4 position-relative">
                        <span class="position-absolute translate-middle p-2 bg-danger border border-light rounded-circle" style="left: -17px;"></span>
                        <h6 class="text-danger fw-bold mb-1">Minuto 3 a 5: O Gatilho Emocional do "Pesadelo da Madrugada"</h6>
                        <div class="bg-secondary bg-opacity-10 rounded p-3 border border-secondary text-light small">
                            <em>"[Nome], você já passou pela péssima experiência de chegar aqui na loja em uma segunda-feira às 7h da manhã, abrir a câmara fria e sentir aquele calor ou cheiro de carne descongelada porque o disjuntor caiu no sábado à noite sem ninguém saber? Como você monitora hoje a temperatura quando as portas estão fechadas?"</em>
                        </div>
                    </div>

                    <!-- Passo 3 -->
                    <div class="mb-4 position-relative">
                        <span class="position-absolute translate-middle p-2 bg-info border border-light rounded-circle" style="left: -17px;"></span>
                        <h6 class="text-info fw-bold mb-1">Minuto 6 a 8: Apresentação da Solução KPRemote</h6>
                        <div class="bg-secondary bg-opacity-10 rounded p-3 border border-secondary text-light small">
                            <em>"Nós desenvolvemos o KPRemote justamente para que você nunca mais passe por esse estresse. Instalamos um sensor inteligente na sua câmara. Se a temperatura subir mais de 2 graus ou se faltar energia na madrugada, o sistema dispara imediatamente um alerta sonoro no seu WhatsApp pessoal e dos seus gerentes. Você resolve antes de perder um único real de mercadoria."</em>
                        </div>
                    </div>

                    <!-- Passo 4 -->
                    <div class="position-relative">
                        <span class="position-absolute translate-middle p-2 bg-success border border-light rounded-circle" style="left: -17px;"></span>
                        <h6 class="text-success fw-bold mb-1">Minuto 9 a 10: O Fechamento com Contraste de Preço</h6>
                        <div class="bg-secondary bg-opacity-10 rounded p-3 border border-secondary text-light small">
                            <em>"E o melhor de tudo: você não precisa gastar milhares de reais comprando equipamentos caros. Nós deixamos instalado sob locação por apenas <strong>R$ 40,00 por mês</strong>. Isso dá pouco mais de R$ 1,30 por dia para garantir que seu estoque de R$ 50 mil esteja 100% protegido. Podemos instalar o teste nesta semana?"</em>
                        </div>
                    </div>

                </div>
            </div>
        </div>

    </div>

</div>

<script>
function filtrarPlaybook() {
    const input = document.getElementById('playbookSearch');
    const filter = input.value.toLowerCase();
    const sections = document.querySelectorAll('.playbook-section');

    sections.forEach(sec => {
        const text = sec.innerText.toLowerCase();
        if (text.includes(filter)) {
            sec.style.display = '';
        } else {
            sec.style.display = 'none';
        }
    });
}
</script>
