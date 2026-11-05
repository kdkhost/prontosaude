<?php
require_once('header.php');

// 1. Endpoint AJAX: Excluir FAQ de Forma Segura
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_faq') {
    header('Content-Type: application/json; charset=utf-8');
    $id = (int)($_POST['id'] ?? 0);

    if ($id > 0) {
        $stmt_del = $pdo->prepare("DELETE FROM tbl_faq WHERE faq_id=?");
        $stmt_del->execute([$id]);
        echo json_encode(['status' => 'success', 'message' => 'Pergunta frequente removida com sucesso!']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Identificador inválido.']);
    }
    exit;
}

// 2. Consulta de FAQs com Categoria e Indicadores (KPIs)
$statement = $pdo->prepare("SELECT 
                                t1.faq_id,
                                t1.faq_title,
                                t1.faq_content,
                                t1.faq_category_id,
                                t1.status,
                                t2.faq_category_id AS cat_id,
                                t2.faq_category_name
                            FROM tbl_faq t1
                            LEFT JOIN tbl_faq_category t2 ON t1.faq_category_id = t2.faq_category_id
                            ORDER BY t1.faq_id DESC");
$statement->execute();
$faqs = $statement->fetchAll(PDO::FETCH_ASSOC);

$total_faqs    = count($faqs);
$total_ativos  = 0;
$total_inativos = 0;
$categorias_unicas = [];

foreach ($faqs as $f) {
    if (($f['status'] ?? 'Active') === 'Active') {
        $total_ativos++;
    } else {
        $total_inativos++;
    }
    if (!empty($f['faq_category_name'])) {
        $categorias_unicas[$f['faq_category_name']] = true;
    }
}
$total_categorias = count($categorias_unicas);
?>

<!-- Cabeçalho da Página -->
<div class="app-content-header pb-2 mb-3 border-bottom">
    <div class="container-fluid">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div>
                <h3 class="mb-1 fw-bold">Perguntas Frequentes (FAQs)</h3>
                <ol class="breadcrumb mb-0 small">
                    <li class="breadcrumb-item"><a href="index.php"><i class="bi bi-house-door"></i> Início</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Perguntas Frequentes</li>
                </ol>
            </div>
            <div class="d-flex flex-wrap align-items-center gap-2">
                <a href="<?= BASE_URL; ?>page.php?slug=faq" target="_blank" rel="noopener" class="btn btn-outline-info btn-sm shadow-xs" title="Ver central de dúvidas no site público">
                    <i class="bi bi-box-arrow-up-right me-1"></i> Ver no Site
                </a>
                <a href="faq-category.php" class="btn btn-outline-secondary btn-sm shadow-xs" title="Gerenciar categorias de dúvidas">
                    <i class="bi bi-tags me-1"></i> Categorias
                </a>
                <a href="faq-add.php" class="btn btn-primary btn-sm shadow-xs px-3">
                    <i class="bi bi-plus-lg me-1"></i> Adicionar Pergunta
                </a>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">

        <!-- Mensagens Tradicionais de Sessão (Fallback) -->
        <?php if(!empty($_SESSION['msg'])): ?>
            <div class="alert alert-success alert-dismissible fade show shadow-xs mb-4" role="alert">
                <div class="d-flex align-items-center">
                    <i class="bi bi-check-circle-fill fs-5 me-2"></i>
                    <div><?= $_SESSION['msg']; ?></div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
            </div>
            <?php unset($_SESSION['msg']); ?>
        <?php endif; ?>

        <!-- Cards de Indicadores (KPIs) -->
        <div class="row g-3 mb-4">
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card shadow-sm border-0 border-start border-primary border-4 h-100 bg-body">
                    <div class="card-body d-flex align-items-center justify-content-between py-3">
                        <div>
                            <div class="text-body-secondary small text-uppercase fw-bold">Total de Dúvidas</div>
                            <div class="fs-3 fw-bold text-dark mt-1" id="kpiTotal"><?= $total_faqs; ?></div>
                        </div>
                        <div class="p-3 bg-primary-subtle text-primary rounded-circle">
                            <i class="bi bi-question-circle-fill fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card shadow-sm border-0 border-start border-success border-4 h-100 bg-body">
                    <div class="card-body d-flex align-items-center justify-content-between py-3">
                        <div>
                            <div class="text-body-secondary small text-uppercase fw-bold">Visíveis no Site</div>
                            <div class="fs-3 fw-bold text-success mt-1" id="kpiAtivos"><?= $total_ativos; ?></div>
                        </div>
                        <div class="p-3 bg-success-subtle text-success rounded-circle">
                            <i class="bi bi-check-circle-fill fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card shadow-sm border-0 border-start border-warning border-4 h-100 bg-body">
                    <div class="card-body d-flex align-items-center justify-content-between py-3">
                        <div>
                            <div class="text-body-secondary small text-uppercase fw-bold">Ocultas / Pausadas</div>
                            <div class="fs-3 fw-bold text-warning mt-1" id="kpiInativos"><?= $total_inativos; ?></div>
                        </div>
                        <div class="p-3 bg-warning-subtle text-warning rounded-circle">
                            <i class="bi bi-eye-slash-fill fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card shadow-sm border-0 border-start border-info border-4 h-100 bg-body">
                    <div class="card-body d-flex align-items-center justify-content-between py-3">
                        <div>
                            <div class="text-body-secondary small text-uppercase fw-bold">Categorias Ativas</div>
                            <div class="fs-3 fw-bold text-info mt-1"><?= $total_categorias; ?></div>
                        </div>
                        <div class="p-3 bg-info-subtle text-info rounded-circle">
                            <i class="bi bi-tags-fill fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabela Principal de FAQs -->
        <div class="row">
            <div class="col-12">
                <div class="card card-outline card-primary shadow-sm mb-4">
                    
                    <div class="card-header bg-transparent py-3 d-flex flex-wrap align-items-center justify-content-between gap-2 border-bottom">
                        <div class="d-flex align-items-center gap-2">
                            <h5 class="card-title fw-bold mb-0 text-body">
                                <i class="bi bi-chat-left-text text-primary me-2"></i>Gerenciar Perguntas & Respostas
                            </h5>
                        </div>

                        <!-- Filtros Rápidos por Status -->
                        <div class="btn-group btn-group-sm" role="group" aria-label="Filtrar por Status">
                            <button type="button" class="btn btn-outline-secondary active filter-btn" data-filter="all">
                                Todas <span class="badge bg-secondary-subtle text-body ms-1"><?= $total_faqs; ?></span>
                            </button>
                            <button type="button" class="btn btn-outline-success filter-btn" data-filter="active">
                                Visíveis <span class="badge bg-success-subtle text-success ms-1"><?= $total_ativos; ?></span>
                            </button>
                            <button type="button" class="btn btn-outline-warning filter-btn" data-filter="inactive">
                                Ocultas <span class="badge bg-warning-subtle text-warning ms-1"><?= $total_inativos; ?></span>
                            </button>
                        </div>
                    </div>

                    <div class="card-body table-responsive">
                        <table id="tabelaFaq" class="table table-hover align-middle w-100">
                            <thead class="table-light">
                                <tr>
                                    <th width="45" class="text-center">#</th>
                                    <th>Pergunta (Título da Dúvida)</th>
                                    <th>Resposta / Conteúdo</th>
                                    <th width="160">Categoria</th>
                                    <th width="120" class="text-center">Status</th>
                                    <th width="130" class="text-center">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $seq = 0;
                                foreach ($faqs as $row):
                                    $seq++;
                                    $faq_id = (int)$row['faq_id'];
                                    $is_active = (($row['status'] ?? 'Active') === 'Active');
                                    
                                    $titulo_fmt    = htmlspecialchars($row['faq_title'] ?? 'Dúvida #' . $faq_id);
                                    $conteudo_raw  = $row['faq_content'] ?? '';
                                    $conteudo_txt  = strip_tags($conteudo_raw);
                                    $categoria_fmt = !empty($row['faq_category_name']) ? htmlspecialchars($row['faq_category_name']) : 'Geral';
                                ?>
                                <tr id="tr-faq-<?= $faq_id; ?>" data-status="<?= $is_active ? 'active' : 'inactive'; ?>">
                                    <td class="text-center text-muted fw-semibold"><?= $seq; ?></td>
                                    
                                    <!-- Título da FAQ -->
                                    <td>
                                        <div class="fw-bold text-body fs-6 mb-1">
                                            <i class="bi bi-question-circle-fill text-primary me-1"></i><?= $titulo_fmt; ?>
                                        </div>
                                    </td>

                                    <!-- Resposta Truncada com Prévia -->
                                    <td>
                                        <div class="small text-body-secondary text-truncate" style="max-width: 380px;" title="<?= htmlspecialchars($conteudo_txt); ?>">
                                            <?= htmlspecialchars(mb_strimwidth($conteudo_txt, 0, 95, '...')); ?>
                                        </div>
                                    </td>

                                    <!-- Categoria da FAQ -->
                                    <td>
                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle small fw-semibold">
                                            <i class="bi bi-tag-fill me-1"></i><?= $categoria_fmt; ?>
                                        </span>
                                    </td>

                                    <!-- Status com Switch AJAX Global -->
                                    <td class="text-center">
                                        <div class="d-inline-flex align-items-center gap-2">
                                            <div class="form-check form-switch mb-0" title="Ativar ou desativar esta dúvida frequente no site">
                                                <input class="form-check-input pronto-status-switch" type="checkbox" role="switch"
                                                       data-entity="faq"
                                                       data-id="<?= $faq_id; ?>"
                                                       data-name="<?= $titulo_fmt; ?>"
                                                       data-active-text="Visível"
                                                       data-inactive-text="Oculto"
                                                       id="switch-faq-<?= $faq_id; ?>"
                                                       <?= $is_active ? 'checked' : ''; ?>>
                                            </div>
                                            <span class="badge status-badge <?= $is_active ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-warning-subtle text-warning border border-warning-subtle'; ?> rounded-pill small" id="badge-status-<?= $faq_id; ?>">
                                                <?= $is_active ? 'Visível' : 'Oculto'; ?>
                                            </span>
                                        </div>
                                    </td>

                                    <!-- Ações -->
                                    <td class="text-center text-nowrap">
                                        <div class="d-flex align-items-center justify-content-center gap-1">
                                            
                                            <!-- Botão Prévia / Modal -->
                                            <button type="button" class="btn btn-sm btn-outline-info shadow-xs" 
                                                    title="Visualizar pergunta e resposta completa"
                                                    onclick="abrirModalFaq(<?= htmlspecialchars(json_encode([
                                                        'id'        => $faq_id,
                                                        'title'     => $titulo_fmt,
                                                        'category'  => $categoria_fmt,
                                                        'content'   => $conteudo_raw,
                                                        'status'    => $is_active ? 'Visível' : 'Oculto'
                                                    ], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE)); ?>)">
                                                <i class="bi bi-eye"></i>
                                            </button>

                                            <!-- Botão Editar -->
                                            <a href="faq-edit.php?id=<?= $faq_id; ?>" class="btn btn-sm btn-primary shadow-xs" title="Editar Pergunta">
                                                <i class="bi bi-pencil-square"></i>
                                            </a>

                                            <!-- Botão Excluir -->
                                            <button type="button" class="btn btn-sm btn-danger shadow-xs btn-delete-faq"
                                                    data-id="<?= $faq_id; ?>"
                                                    data-title="<?= $titulo_fmt; ?>"
                                                    title="Excluir Pergunta">
                                                <i class="bi bi-trash3"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>
        </div>

    </div>
</div>

<!-- Modal Elegante de Prévia da FAQ (Estilo Accordion Card) -->
<div class="modal fade" id="modalFaqDetalhes" tabindex="-1" aria-labelledby="modalFaqDetalhesLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content shadow-lg border-0 rounded-4 overflow-hidden">
            <div class="modal-header bg-primary text-white border-0 py-3">
                <h5 class="modal-title fw-bold d-flex align-items-center gap-2" id="modalFaqDetalhesLabel">
                    <i class="bi bi-question-circle-fill"></i> Prévia da Dúvida Frequente
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body p-4 bg-light">
                
                <div class="card border-0 shadow-sm rounded-3 p-4 bg-white mb-3">
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-2 rounded-pill small" id="mfCategoria">
                            <i class="bi bi-tag-fill me-1"></i>Categoria
                        </span>
                        <div id="mfBadgeStatus"></div>
                    </div>

                    <h4 class="fw-bold text-dark mb-3 d-flex align-items-start gap-2" id="mfTitulo">
                        <i class="bi bi-chat-left-dots text-primary mt-1"></i>
                        <span>Pergunta</span>
                    </h4>

                    <div class="p-3 bg-light rounded-3 border-start border-primary border-4 text-secondary" id="mfConteudo" style="font-size: 0.98rem; line-height: 1.6; max-height: 280px; overflow-y: auto;">
                    </div>
                </div>

            </div>
            <div class="modal-footer bg-body border-0 py-2 d-flex justify-content-between">
                <span class="small text-muted"><i class="bi bi-shield-check text-success me-1"></i> Exibição Oficial Pronto Saúde</span>
                <div>
                    <a href="#" id="mfLinkEditar" class="btn btn-primary btn-sm px-3"><i class="bi bi-pencil-square me-1"></i> Editar Pergunta</a>
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Fechar</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // 1. Inicialização do DataTables com Tradução Completa
    let table = null;
    if (typeof window.jQuery !== 'undefined' && typeof window.jQuery.fn.DataTable !== 'undefined') {
        table = $('#tabelaFaq').DataTable({
            responsive: true,
            language: {
                search: "Buscar Pergunta:",
                lengthMenu: "Mostrar _MENU_ registros",
                info: "Exibindo _START_ a _END_ de _TOTAL_ perguntas",
                infoEmpty: "Nenhuma pergunta cadastrada",
                infoFiltered: "(filtrado de _MAX_ no total)",
                zeroRecords: "Nenhuma pergunta encontrada",
                paginate: {
                    first: "Primeira",
                    previous: "Anterior",
                    next: "Próxima",
                    last: "Última"
                }
            },
            order: [[0, 'asc']]
        });
    }

    // 2. Filtro Rápido por Pílulas (Todas, Visíveis, Ocultas)
    const filterButtons = document.querySelectorAll('.filter-btn');
    filterButtons.forEach(btn => {
        btn.addEventListener('click', function () {
            filterButtons.forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            const filter = this.dataset.filter;

            if (table) {
                if (filter === 'all') {
                    table.column(4).search('').draw();
                } else if (filter === 'active') {
                    table.column(4).search('Visível').draw();
                } else if (filter === 'inactive') {
                    table.column(4).search('Oculto').draw();
                }
            }
        });
    });

    // 3. Atualização Dinâmica de KPIs após Toggle de Status
    document.addEventListener('change', function (e) {
        if (e.target && e.target.classList.contains('pronto-status-switch')) {
            const tr = e.target.closest('tr');
            if (tr) {
                tr.setAttribute('data-status', e.target.checked ? 'active' : 'inactive');
                setTimeout(atualizarKPIs, 300);
            }
        }
    });

    // 4. Exclusão Segura com Confirmação SweetAlert2 via AJAX
    document.querySelectorAll('.btn-delete-faq').forEach(btn => {
        btn.addEventListener('click', function () {
            const faqId = this.dataset.id;
            const titulo = this.dataset.title || 'esta pergunta';

            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Deseja excluir esta pergunta?',
                    html: `Tem certeza que deseja remover a dúvida:<br><strong>"${titulo}"</strong>?<br><span class="text-muted small">Esta ação apagará a pergunta e resposta permanentemente.</span>`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: '<i class="bi bi-trash3 me-1"></i> Sim, excluir!',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        const formData = new FormData();
                        formData.append('action', 'delete_faq');
                        formData.append('id', faqId);

                        fetch(window.location.href, {
                            method: 'POST',
                            body: formData
                        })
                        .then(res => res.json())
                        .then(data => {
                            if (data.status === 'success') {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Excluída!',
                                    text: 'A pergunta foi removida com sucesso.',
                                    timer: 1600,
                                    showConfirmButton: false
                                });

                                // Remove a linha da tabela na hora
                                if (table) {
                                    table.row($('#tr-faq-'.concat(faqId))).remove().draw();
                                } else {
                                    const tr = document.getElementById('tr-faq-'.concat(faqId));
                                    if (tr) tr.remove();
                                }
                                atualizarKPIs();
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Erro ao excluir',
                                    text: data.message || 'Não foi possível excluir a pergunta.'
                                });
                            }
                        })
                        .catch(err => {
                            console.error(err);
                            Swal.fire({
                                icon: 'error',
                                title: 'Falha de comunicação',
                                text: 'Não foi possível concluir a exclusão.'
                            });
                        });
                    }
                });
            } else {
                // Fallback tradicional
                window.location.href = 'faq-delete.php?id=' + faqId;
            }
        });
    });

    // 5. Atualização Dinâmica dos Contadores de KPIs
    function atualizarKPIs() {
        const rows = document.querySelectorAll('#tabelaFaq tbody tr[data-status]');
        let total = rows.length;
        let ativos = 0;
        let inativos = 0;

        rows.forEach(r => {
            if (r.getAttribute('data-status') === 'active') ativos++;
            else inativos++;
        });

        const kpiTotal = document.getElementById('kpiTotal');
        const kpiAtivos = document.getElementById('kpiAtivos');
        const kpiInativos = document.getElementById('kpiInativos');

        if (kpiTotal) kpiTotal.textContent = total;
        if (kpiAtivos) kpiAtivos.textContent = ativos;
        if (kpiInativos) kpiInativos.textContent = inativos;
    }

    // 6. Função Global para o Modal de Prévia de FAQ
    window.abrirModalFaq = function(data) {
        document.getElementById('mfTitulo').innerHTML = '<i class="bi bi-chat-left-dots text-primary mt-1 me-1"></i> ' + (data.title || 'Pergunta');
        document.getElementById('mfCategoria').innerHTML = '<i class="bi bi-tag-fill me-1"></i> ' + (data.category || 'Geral');
        document.getElementById('mfConteudo').innerHTML = data.content || '<em class="text-muted">Nenhuma resposta informada</em>';
        
        const badgeBox = document.getElementById('mfBadgeStatus');
        if (data.status === 'Visível') {
            badgeBox.innerHTML = '<span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 small">Visível no Site</span>';
        } else {
            badgeBox.innerHTML = '<span class="badge bg-warning-subtle text-warning border border-warning-subtle px-2 py-1 small">Oculto</span>';
        }

        const editLink = document.getElementById('mfLinkEditar');
        if (editLink) editLink.href = 'faq-edit.php?id=' + data.id;

        const modalEl = document.getElementById('modalFaqDetalhes');
        if (modalEl && typeof bootstrap !== 'undefined') {
            const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
            modal.show();
        }
    };
});
</script>

<?php require_once('footer.php'); ?>
