<?php
require_once('header.php');

// 1. Endpoint AJAX: Excluir Categoria e suas FAQs Vinculadas
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_faq_category') {
    header('Content-Type: application/json; charset=utf-8');
    $id = (int)($_POST['id'] ?? 0);

    if ($id > 0) {
        // Remove as FAQs vinculadas à categoria
        $stmt_faqs = $pdo->prepare("DELETE FROM tbl_faq WHERE faq_category_id=?");
        $stmt_faqs->execute([$id]);

        // Remove a categoria de FAQ
        $stmt_cat = $pdo->prepare("DELETE FROM tbl_faq_category WHERE faq_category_id=?");
        $stmt_cat->execute([$id]);

        echo json_encode([
            'status'  => 'success', 
            'message' => 'Categoria de FAQ e suas dúvidas vinculadas foram removidas com sucesso!'
        ]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Identificador de categoria inválido.']);
    }
    exit;
}

// 2. Consulta de Categorias com Contagem de Perguntas Vinculadas (KPIs)
$statement = $pdo->prepare("SELECT 
                                c.faq_category_id, 
                                c.faq_category_name, 
                                COUNT(f.faq_id) AS total_faqs,
                                SUM(CASE WHEN f.status = 'Active' THEN 1 ELSE 0 END) AS total_faqs_ativas
                            FROM tbl_faq_category c
                            LEFT JOIN tbl_faq f ON c.faq_category_id = f.faq_category_id
                            GROUP BY c.faq_category_id, c.faq_category_name
                            ORDER BY c.faq_category_id ASC");
$statement->execute();
$categories = $statement->fetchAll(PDO::FETCH_ASSOC);

$total_categorias = count($categories);
$total_faqs_geral = 0;
$categorias_com_faq = 0;

foreach ($categories as $cat) {
    $qtd = (int)$cat['total_faqs'];
    $total_faqs_geral += $qtd;
    if ($qtd > 0) {
        $categorias_com_faq++;
    }
}
?>

<!-- Cabeçalho da Página -->
<div class="app-content-header pb-2 mb-3 border-bottom">
    <div class="container-fluid">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div>
                <h3 class="mb-1 fw-bold">Categorias de Dúvidas (FAQ)</h3>
                <ol class="breadcrumb mb-0 small">
                    <li class="breadcrumb-item"><a href="index.php"><i class="bi bi-house-door"></i> Início</a></li>
                    <li class="breadcrumb-item"><a href="faq.php">Perguntas Frequentes</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Categorias</li>
                </ol>
            </div>
            <div class="d-flex flex-wrap align-items-center gap-2">
                <a href="faq.php" class="btn btn-outline-info btn-sm shadow-xs" title="Ver lista de perguntas cadastradas">
                    <i class="bi bi-question-circle me-1"></i> Ver Todas as FAQs
                </a>
                <a href="faq-category-add.php" class="btn btn-primary btn-sm shadow-xs px-3">
                    <i class="bi bi-plus-lg me-1"></i> Adicionar Categoria
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
            <div class="col-12 col-sm-6 col-xl-4">
                <div class="card shadow-sm border-0 border-start border-primary border-4 h-100 bg-body">
                    <div class="card-body d-flex align-items-center justify-content-between py-3">
                        <div>
                            <div class="text-body-secondary small text-uppercase fw-bold">Total de Categorias</div>
                            <div class="fs-3 fw-bold text-dark mt-1" id="kpiTotal"><?= $total_categorias; ?></div>
                        </div>
                        <div class="p-3 bg-primary-subtle text-primary rounded-circle">
                            <i class="bi bi-tags-fill fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-xl-4">
                <div class="card shadow-sm border-0 border-start border-success border-4 h-100 bg-body">
                    <div class="card-body d-flex align-items-center justify-content-between py-3">
                        <div>
                            <div class="text-body-secondary small text-uppercase fw-bold">Perguntas Vinculadas</div>
                            <div class="fs-3 fw-bold text-success mt-1" id="kpiFaqs"><?= $total_faqs_geral; ?></div>
                        </div>
                        <div class="p-3 bg-success-subtle text-success rounded-circle">
                            <i class="bi bi-question-circle-fill fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-xl-4">
                <div class="card shadow-sm border-0 border-start border-info border-4 h-100 bg-body">
                    <div class="card-body d-flex align-items-center justify-content-between py-3">
                        <div>
                            <div class="text-body-secondary small text-uppercase fw-bold">Com Dúvidas Ativas</div>
                            <div class="fs-3 fw-bold text-info mt-1" id="kpiAtivas"><?= $categorias_com_faq; ?></div>
                        </div>
                        <div class="p-3 bg-info-subtle text-info rounded-circle">
                            <i class="bi bi-check-circle-fill fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabela Principal de Categorias de FAQ -->
        <div class="row">
            <div class="col-12">
                <div class="card card-outline card-primary shadow-sm mb-4">
                    
                    <div class="card-header bg-transparent py-3 d-flex flex-wrap align-items-center justify-content-between gap-2 border-bottom">
                        <div class="d-flex align-items-center gap-2">
                            <h5 class="card-title fw-bold mb-0 text-body">
                                <i class="bi bi-folder2-open text-primary me-2"></i>Gerenciar Categorias Temáticas
                            </h5>
                        </div>
                        <span class="badge bg-secondary-subtle text-secondary small">
                            Organização do Acervo de Perguntas Frequentes
                        </span>
                    </div>

                    <div class="card-body table-responsive">
                        <table id="tabelaFaqCat" class="table table-hover align-middle w-100">
                            <thead class="table-light">
                                <tr>
                                    <th width="45" class="text-center">#</th>
                                    <th>Nome da Categoria</th>
                                    <th width="180" class="text-center">Dúvidas Vinculadas</th>
                                    <th width="160" class="text-center">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $seq = 0;
                                foreach ($categories as $row):
                                    $seq++;
                                    $cat_id   = (int)$row['faq_category_id'];
                                    $cat_nome = htmlspecialchars($row['faq_category_name'] ?? 'Categoria #' . $cat_id);
                                    $qtd_faqs = (int)$row['total_faqs'];
                                    $qtd_ativas = (int)($row['total_faqs_ativas'] ?? 0);
                                ?>
                                <tr id="tr-cat-<?= $cat_id; ?>">
                                    <td class="text-center text-muted fw-semibold"><?= $seq; ?></td>
                                    
                                    <!-- Nome da Categoria -->
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="p-2 rounded bg-primary-subtle text-primary">
                                                <i class="bi bi-tag-fill fs-5"></i>
                                            </div>
                                            <div>
                                                <div class="fw-bold text-body fs-6 mb-0"><?= $cat_nome; ?></div>
                                                <div class="small text-muted">ID: #<?= $cat_id; ?></div>
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Total de Perguntas Vinculadas -->
                                    <td class="text-center">
                                        <?php if ($qtd_faqs > 0): ?>
                                            <a href="faq.php" class="btn btn-sm btn-outline-primary rounded-pill px-3 shadow-xs" title="Ver perguntas desta categoria">
                                                <i class="bi bi-question-circle me-1"></i>
                                                <strong><?= $qtd_faqs; ?></strong> <?= $qtd_faqs === 1 ? 'pergunta' : 'perguntas'; ?>
                                            </a>
                                        <?php else: ?>
                                            <span class="badge bg-secondary-subtle text-secondary border small">
                                                Nenhuma pergunta vinculada
                                            </span>
                                        <?php endif; ?>
                                    </td>

                                    <!-- Ações -->
                                    <td class="text-center text-nowrap">
                                        <div class="d-flex align-items-center justify-content-center gap-1">
                                            
                                            <!-- Botão Prévia / Modal -->
                                            <button type="button" class="btn btn-sm btn-outline-info shadow-xs" 
                                                    title="Visualizar detalhes da categoria"
                                                    onclick="abrirModalCategoria(<?= htmlspecialchars(json_encode([
                                                        'id'     => $cat_id,
                                                        'name'   => $cat_nome,
                                                        'total'  => $qtd_faqs,
                                                        'ativas' => $qtd_ativas
                                                    ], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE)); ?>)">
                                                <i class="bi bi-eye"></i>
                                            </button>

                                            <!-- Botão Editar -->
                                            <a href="faq-category-edit.php?id=<?= $cat_id; ?>" class="btn btn-sm btn-primary shadow-xs" title="Editar Categoria">
                                                <i class="bi bi-pencil-square"></i>
                                            </a>

                                            <!-- Botão Excluir -->
                                            <button type="button" class="btn btn-sm btn-danger shadow-xs btn-delete-cat"
                                                    data-id="<?= $cat_id; ?>"
                                                    data-name="<?= $cat_nome; ?>"
                                                    data-total="<?= $qtd_faqs; ?>"
                                                    title="Excluir Categoria">
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

<!-- Modal Elegante de Detalhes da Categoria -->
<div class="modal fade" id="modalCategoriaDetalhes" tabindex="-1" aria-labelledby="modalCategoriaDetalhesLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow-lg border-0 rounded-4 overflow-hidden">
            <div class="modal-header bg-primary text-white border-0 py-3">
                <h5 class="modal-title fw-bold d-flex align-items-center gap-2" id="modalCategoriaDetalhesLabel">
                    <i class="bi bi-tag-fill"></i> Detalhes da Categoria
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body p-4 bg-light text-center">
                
                <div class="card border-0 shadow-sm rounded-3 p-4 bg-white mb-3">
                    <div class="d-inline-flex p-3 rounded-circle bg-primary-subtle text-primary mb-3">
                        <i class="bi bi-folder-fill fs-2"></i>
                    </div>

                    <h4 class="fw-bold text-dark mb-2" id="mcNome"></h4>
                    
                    <div class="p-3 bg-light rounded-3 border mb-3">
                        <div class="row g-2 text-center">
                            <div class="col-6 border-end">
                                <div class="text-muted small">Total Cadastradas</div>
                                <div class="fs-4 fw-bold text-primary" id="mcTotalFaqs">0</div>
                            </div>
                            <div class="col-6">
                                <div class="text-muted small">Visíveis no Site</div>
                                <div class="fs-4 fw-bold text-success" id="mcAtivasFaqs">0</div>
                            </div>
                        </div>
                    </div>

                    <div class="d-grid gap-2">
                        <a href="faq.php" class="btn btn-outline-primary btn-sm"><i class="bi bi-list-ul me-1"></i> Ver Perguntas Desta Categoria</a>
                    </div>
                </div>

            </div>
            <div class="modal-footer bg-body border-0 py-2 d-flex justify-content-between">
                <span class="small text-muted"><i class="bi bi-info-circle me-1"></i> Pronto Saúde</span>
                <div>
                    <a href="#" id="mcLinkEditar" class="btn btn-primary btn-sm px-3"><i class="bi bi-pencil-square me-1"></i> Editar Categoria</a>
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
        table = $('#tabelaFaqCat').DataTable({
            responsive: true,
            language: {
                search: "Buscar Categoria:",
                lengthMenu: "Mostrar _MENU_ registros",
                info: "Exibindo _START_ a _END_ de _TOTAL_ categorias",
                infoEmpty: "Nenhuma categoria cadastrada",
                infoFiltered: "(filtrado de _MAX_ no total)",
                zeroRecords: "Nenhuma categoria encontrada",
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

    // 2. Exclusão Segura com Confirmação SweetAlert2 via AJAX
    document.querySelectorAll('.btn-delete-cat').forEach(btn => {
        btn.addEventListener('click', function () {
            const catId = this.dataset.id;
            const nome = this.dataset.name || 'esta categoria';
            const totalFaqs = parseInt(this.dataset.total || 0, 10);

            let alertHtml = `Tem certeza que deseja remover a categoria <strong>"${nome}"</strong>?`;
            if (totalFaqs > 0) {
                alertHtml += `<br><br><span class="text-danger fw-bold"><i class="bi bi-exclamation-triangle-fill me-1"></i> Atenção:</span> Esta categoria possui <strong>${totalFaqs} pergunta(s)</strong> vinculada(s) que também serão <strong>apagadas do sistema</strong>.`;
            }

            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Deseja excluir esta categoria?',
                    html: alertHtml,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: '<i class="bi bi-trash3 me-1"></i> Sim, excluir!',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        const formData = new FormData();
                        formData.append('action', 'delete_faq_category');
                        formData.append('id', catId);

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
                                    text: 'A categoria foi removida com sucesso.',
                                    timer: 1600,
                                    showConfirmButton: false
                                });

                                // Remove a linha da tabela na hora
                                if (table) {
                                    table.row($('#tr-cat-'.concat(catId))).remove().draw();
                                } else {
                                    const tr = document.getElementById('tr-cat-'.concat(catId));
                                    if (tr) tr.remove();
                                }
                                atualizarKPIs();
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Erro ao excluir',
                                    text: data.message || 'Não foi possível excluir a categoria.'
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
                window.location.href = 'faq-category-delete.php?id=' + catId;
            }
        });
    });

    // 3. Atualização Dinâmica do Contador de KPIs
    function atualizarKPIs() {
        const rows = document.querySelectorAll('#tabelaFaqCat tbody tr');
        let total = rows.length;
        const kpiTotal = document.getElementById('kpiTotal');
        if (kpiTotal) kpiTotal.textContent = total;
    }

    // 4. Função Global para o Modal de Detalhes da Categoria
    window.abrirModalCategoria = function(data) {
        document.getElementById('mcNome').textContent = data.name || 'Categoria';
        document.getElementById('mcTotalFaqs').textContent = data.total || 0;
        document.getElementById('mcAtivasFaqs').textContent = data.ativas || 0;

        const editLink = document.getElementById('mcLinkEditar');
        if (editLink) editLink.href = 'faq-category-edit.php?id=' + data.id;

        const modalEl = document.getElementById('modalCategoriaDetalhes');
        if (modalEl && typeof bootstrap !== 'undefined') {
            const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
            modal.show();
        }
    };
});
</script>

<?php require_once('footer.php'); ?>
