<?php
require_once('header.php');

// 1. Endpoint AJAX: Excluir Categoria e suas Fotos Vinculadas
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_photo_category') {
    header('Content-Type: application/json; charset=utf-8');
    $id = (int)($_POST['id'] ?? 0);

    if ($id > 0) {
        // Remove os arquivos físicos das fotos vinculadas
        $statement = $pdo->prepare("SELECT photo_name FROM tbl_photo WHERE p_category_id = ?");
        $statement->execute([$id]);
        $photos = $statement->fetchAll(PDO::FETCH_ASSOC);

        foreach ($photos as $p) {
            $arq = trim($p['photo_name'] ?? '');
            if (!empty($arq)) {
                $caminhos = [
                    __DIR__ . '/../assets/uploads/' . $arq,
                    __DIR__ . '/../assets/uploads/photos/' . $arq
                ];
                foreach ($caminhos as $path) {
                    if (file_exists($path) && is_file($path)) {
                        @unlink($path);
                    }
                }
            }
        }

        // Remove as fotos vinculadas
        $stmt_photos = $pdo->prepare("DELETE FROM tbl_photo WHERE p_category_id = ?");
        $stmt_photos->execute([$id]);

        // Remove a categoria de foto
        $stmt_cat = $pdo->prepare("DELETE FROM tbl_category_photo WHERE p_category_id = ?");
        $stmt_cat->execute([$id]);

        echo json_encode([
            'status'  => 'success', 
            'message' => 'Categoria de fotos e suas imagens vinculadas foram removidas com sucesso!'
        ]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Identificador de categoria inválido.']);
    }
    exit;
}

// 2. Consulta de Categorias com Contagem de Fotos Vinculadas (KPIs)
$statement = $pdo->prepare("SELECT 
                                c.p_category_id, 
                                c.p_category_name, 
                                c.status,
                                COUNT(p.photo_id) AS total_photos,
                                SUM(CASE WHEN p.status = 'Active' THEN 1 ELSE 0 END) AS total_photos_ativas
                            FROM tbl_category_photo c
                            LEFT JOIN tbl_photo p ON c.p_category_id = p.p_category_id
                            GROUP BY c.p_category_id, c.p_category_name, c.status
                            ORDER BY c.p_category_id ASC");
$statement->execute();
$categories = $statement->fetchAll(PDO::FETCH_ASSOC);

$total_categorias     = count($categories);
$total_photos_geral   = 0;
$total_photos_ativas  = 0;
$categorias_com_photo = 0;

foreach ($categories as $cat) {
    $qtd = (int)$cat['total_photos'];
    $qtd_ativas = (int)$cat['total_photos_ativas'];
    $total_photos_geral += $qtd;
    $total_photos_ativas += $qtd_ativas;
    if ($qtd > 0) {
        $categorias_com_photo++;
    }
}
$categorias_vazias = max(0, $total_categorias - $categorias_com_photo);
?>

<!-- Cabeçalho da Página -->
<div class="app-content-header pb-2 mb-3 border-bottom">
    <div class="container-fluid">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div>
                <h3 class="mb-1 fw-bold">Categorias de Foto</h3>
                <ol class="breadcrumb mb-0 small">
                    <li class="breadcrumb-item"><a href="index.php"><i class="bi bi-house-door"></i> Início</a></li>
                    <li class="breadcrumb-item"><a href="photo.php">Galeria de Fotos</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Categorias</li>
                </ol>
            </div>
            <div class="d-flex flex-wrap align-items-center gap-2">
                <a href="<?= BASE_URL; ?>page.php?slug=fotos" target="_blank" rel="noopener" class="btn btn-outline-info btn-sm shadow-xs" title="Ver galeria de fotos no site público">
                    <i class="bi bi-box-arrow-up-right me-1"></i> Ver no Site
                </a>
                <a href="photo.php" class="btn btn-outline-secondary btn-sm shadow-xs" title="Ver todas as fotos cadastradas">
                    <i class="bi bi-images me-1"></i> Ver Todas as Fotos
                </a>
                <a href="photo-category-add.php" class="btn btn-primary btn-sm shadow-xs px-3">
                    <i class="bi bi-plus-lg me-1"></i> Adicionar Categoria
                </a>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">

        <!-- Mensagens Tradicionais de Sessão (Fallback) -->
        <?php if (!empty($_SESSION['sucesso'])): ?>
            <div class="alert alert-success alert-dismissible fade show shadow-xs mb-4" role="alert">
                <div class="d-flex align-items-center">
                    <i class="bi bi-check-circle-fill fs-5 me-2"></i>
                    <div><?= $_SESSION['sucesso']; ?></div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
            </div>
            <?php unset($_SESSION['sucesso']); ?>
        <?php endif; ?>

        <?php if (!empty($_SESSION['erro'])): ?>
            <div class="alert alert-danger alert-dismissible fade show shadow-xs mb-4" role="alert">
                <div class="d-flex align-items-center">
                    <i class="bi bi-exclamation-triangle-fill fs-5 me-2"></i>
                    <div><?= $_SESSION['erro']; ?></div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
            </div>
            <?php unset($_SESSION['erro']); ?>
        <?php endif; ?>

        <!-- Cards de Indicadores (KPIs) com Adaptação Completa Dark/Light -->
        <div class="row g-3 mb-4">
            <!-- KPI 1: Total de Categorias -->
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card shadow-sm border-0 border-start border-primary border-4 h-100 bg-body">
                    <div class="card-body d-flex align-items-center justify-content-between py-3">
                        <div>
                            <div class="text-body-secondary small text-uppercase fw-bold">Total de Categorias</div>
                            <div class="fs-3 fw-bold text-body mt-1" id="kpiTotal"><?= $total_categorias; ?></div>
                        </div>
                        <div class="p-3 bg-primary-subtle text-primary rounded-circle">
                            <i class="bi bi-tags-fill fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- KPI 2: Fotos Vinculadas -->
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card shadow-sm border-0 border-start border-success border-4 h-100 bg-body">
                    <div class="card-body d-flex align-items-center justify-content-between py-3">
                        <div>
                            <div class="text-body-secondary small text-uppercase fw-bold">Fotos no Acervo</div>
                            <div class="fs-3 fw-bold text-success mt-1" id="kpiPhotos"><?= $total_photos_geral; ?></div>
                        </div>
                        <div class="p-3 bg-success-subtle text-success rounded-circle">
                            <i class="bi bi-images fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- KPI 3: Categorias com Fotos -->
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card shadow-sm border-0 border-start border-info border-4 h-100 bg-body">
                    <div class="card-body d-flex align-items-center justify-content-between py-3">
                        <div>
                            <div class="text-body-secondary small text-uppercase fw-bold">Com Fotos Ativas</div>
                            <div class="fs-3 fw-bold text-info mt-1" id="kpiComPhotos"><?= $categorias_com_photo; ?></div>
                        </div>
                        <div class="p-3 bg-info-subtle text-info rounded-circle">
                            <i class="bi bi-camera-fill fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- KPI 4: Categorias sem Fotos -->
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card shadow-sm border-0 border-start border-warning border-4 h-100 bg-body">
                    <div class="card-body d-flex align-items-center justify-content-between py-3">
                        <div>
                            <div class="text-body-secondary small text-uppercase fw-bold">Categorias Vazias</div>
                            <div class="fs-3 fw-bold text-warning mt-1" id="kpiVazias"><?= $categorias_vazias; ?></div>
                        </div>
                        <div class="p-3 bg-warning-subtle text-warning rounded-circle">
                            <i class="bi bi-folder-x fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabela Principal de Categorias de Foto -->
        <div class="row">
            <div class="col-12">
                <div class="card card-outline card-primary shadow-sm mb-4">
                    
                    <div class="card-header bg-transparent py-3 d-flex flex-wrap align-items-center justify-content-between gap-2 border-bottom">
                        <div class="d-flex align-items-center gap-2">
                            <h5 class="card-title fw-bold mb-0 text-body">
                                <i class="bi bi-folder-symlink text-primary me-2"></i>Gerenciar Categorias da Galeria de Fotos
                            </h5>
                        </div>
                        <span class="badge bg-secondary-subtle text-secondary small">
                            Organização de Instalações, Recepção &amp; Consultórios
                        </span>
                    </div>

                    <div class="card-body table-responsive">
                        <table id="tabelaPhotoCat" class="table table-hover align-middle w-100">
                            <thead>
                                <tr>
                                    <th width="50" class="text-center">#</th>
                                    <th>Nome da Categoria</th>
                                    <th width="190" class="text-center">Fotos Vinculadas</th>
                                    <th width="140" class="text-center">Status</th>
                                    <th width="140" class="text-center">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $seq = 0;
                                foreach ($categories as $row):
                                    $seq++;
                                    $cat_id       = (int)$row['p_category_id'];
                                    $cat_name     = htmlspecialchars($row['p_category_name'] ?? 'Categoria #' . $cat_id);
                                    $cat_status   = $row['status'] ?? 'Active';
                                    $is_active    = ($cat_status === 'Active' || $cat_status === 'Ativo');
                                    $total_p      = (int)($row['total_photos'] ?? 0);
                                    $total_ativas = (int)($row['total_photos_ativas'] ?? 0);
                                ?>
                                <tr id="tr-cat-<?= $cat_id; ?>">
                                    <td class="text-center text-body-secondary fw-semibold"><?= $seq; ?></td>
                                    
                                    <!-- Nome da Categoria -->
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="p-2 bg-primary-subtle text-primary rounded-3">
                                                <i class="bi bi-images fs-5"></i>
                                            </div>
                                            <div>
                                                <div class="fw-bold text-body fs-6"><?= $cat_name; ?></div>
                                                <small class="text-body-secondary">Identificador: #<?= $cat_id; ?></small>
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Fotos Vinculadas -->
                                    <td class="text-center">
                                        <?php if ($total_p > 0): ?>
                                            <a href="photo.php" class="badge bg-primary-subtle text-primary border border-primary-subtle text-decoration-none px-3 py-2 rounded-pill small" title="Ver fotos desta categoria">
                                                <i class="bi bi-camera-fill me-1"></i> <?= $total_p; ?> foto(s)
                                                <?php if ($total_ativas > 0): ?>
                                                    <span class="text-success ms-1">(<?= $total_ativas; ?> ativas)</span>
                                                <?php endif; ?>
                                            </a>
                                        <?php else: ?>
                                            <span class="badge bg-secondary-subtle text-body-secondary border border-secondary-subtle px-3 py-2 rounded-pill small">
                                                <i class="bi bi-dash-circle me-1"></i> Nenhuma foto
                                            </span>
                                        <?php endif; ?>
                                    </td>

                                    <!-- Status da Categoria -->
                                    <td class="text-center">
                                        <?php if ($is_active): ?>
                                            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-1 small">
                                                <i class="bi bi-check-circle-fill me-1"></i> Ativo
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary-subtle text-body-secondary border border-secondary-subtle rounded-pill px-3 py-1 small">
                                                <i class="bi bi-pause-circle-fill me-1"></i> Inativo
                                            </span>
                                        <?php endif; ?>
                                    </td>

                                    <!-- Ações Padronizadas -->
                                    <td class="text-center text-nowrap">
                                        <div class="d-flex align-items-center justify-content-center gap-1">
                                            <!-- Botão Detalhes -->
                                            <button type="button" class="btn btn-outline-info btn-sm btn-view-cat"
                                                    data-id="<?= $cat_id; ?>"
                                                    data-name="<?= htmlspecialchars($cat_name, ENT_QUOTES, 'UTF-8'); ?>"
                                                    data-total="<?= $total_p; ?>"
                                                    data-status="<?= $is_active ? 'Ativo' : 'Inativo'; ?>"
                                                    title="Visualizar detalhes da categoria">
                                                <i class="bi bi-eye"></i>
                                            </button>

                                            <!-- Botão Editar -->
                                            <a href="photo-category-edit.php?id=<?= $cat_id; ?>" class="btn btn-outline-primary btn-sm" title="Editar categoria">
                                                <i class="bi bi-pencil-square"></i>
                                            </a>

                                            <!-- Botão Excluir Seguro com SweetAlert2 -->
                                            <button type="button" class="btn btn-outline-danger btn-sm btn-delete-cat"
                                                    data-id="<?= $cat_id; ?>"
                                                    data-name="<?= htmlspecialchars($cat_name, ENT_QUOTES, 'UTF-8'); ?>"
                                                    data-total="<?= $total_p; ?>"
                                                    title="Excluir categoria">
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

<!-- ========================================== -->
<!-- MODAL DINÂMICO: DETALHES DA CATEGORIA      -->
<!-- ========================================== -->
<div class="modal fade" id="modalPhotoCatDetalhes" tabindex="-1" aria-labelledby="modalPhotoCatDetalhesLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content bg-body text-body border-0 shadow-lg rounded-3">
            <div class="modal-header bg-body-tertiary py-3 border-bottom border-secondary-subtle">
                <h5 class="modal-title fw-bold text-body d-flex align-items-center gap-2" id="modalPhotoCatDetalhesLabel">
                    <i class="bi bi-images text-primary"></i>
                    <span>Detalhes da Categoria de Foto</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>

            <div class="modal-body p-4 bg-body-tertiary">
                <div class="card border border-secondary-subtle shadow-sm rounded-3 p-4 bg-body">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-1 rounded-pill small" id="mpcBadgeId">
                            Categoria #0
                        </span>
                        <div id="mpcBadgeStatus"></div>
                    </div>

                    <h4 class="fw-bold text-body mb-3" id="mpcNome">Nome da Categoria</h4>

                    <div class="p-3 bg-body-secondary rounded-3 border border-secondary-subtle">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="text-body-secondary small fw-semibold">Fotos cadastradas no acervo:</span>
                            <span class="fw-bold text-body" id="mpcTotalPhotos">0 fotos</span>
                        </div>
                        <p class="text-body-secondary small mb-0">
                            As categorias agrupam os ambientes e fotos do centro médico na galeria do portal e em tours virtuais.
                        </p>
                    </div>
                </div>
            </div>

            <div class="modal-footer bg-body border-top border-secondary-subtle py-2 d-flex justify-content-between">
                <a href="photo.php" class="btn btn-outline-secondary btn-sm px-3">
                    <i class="bi bi-images me-1"></i> Abrir Fotos
                </a>
                <div class="d-flex gap-2">
                    <a href="#" id="mpcLinkEditar" class="btn btn-primary btn-sm px-3">
                        <i class="bi bi-pencil-square me-1"></i> Editar Categoria
                    </a>
                    <button type="button" class="btn btn-secondary btn-sm px-3" data-bs-dismiss="modal">Fechar</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Funções Globais Independentes de DOMContentLoaded (Suporte Completo a SPA e Reload)
window.abrirModalCat = function(data) {
    // Remove qualquer tooltip ativo na tela imediatamente
    document.querySelectorAll('.tooltip').forEach(t => t.remove());

    const badgeIdEl = document.getElementById('mpcBadgeId');
    if (badgeIdEl) badgeIdEl.textContent = 'Categoria #' + (data.id || '0');

    const nomeEl = document.getElementById('mpcNome');
    if (nomeEl) nomeEl.textContent = data.name || 'Categoria';

    const totalEl = document.getElementById('mpcTotalPhotos');
    if (totalEl) {
        const total = parseInt(data.total || '0', 10);
        totalEl.textContent = total + (total === 1 ? ' foto cadastrada' : ' fotos cadastradas');
    }

    const badgeStatus = document.getElementById('mpcBadgeStatus');
    if (badgeStatus) {
        if (data.status === 'Ativo') {
            badgeStatus.innerHTML = '<span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-1 rounded-pill small"><i class="bi bi-check-circle-fill me-1"></i> Ativo</span>';
        } else {
            badgeStatus.innerHTML = '<span class="badge bg-secondary-subtle text-body-secondary border border-secondary-subtle px-3 py-1 rounded-pill small"><i class="bi bi-pause-circle-fill me-1"></i> Inativo</span>';
        }
    }

    const editLink = document.getElementById('mpcLinkEditar');
    if (editLink) editLink.href = 'photo-category-edit.php?id=' + (data.id || '');

    const modalEl = document.getElementById('modalPhotoCatDetalhes');
    if (modalEl && typeof bootstrap !== 'undefined') {
        const modalInst = bootstrap.Modal.getOrCreateInstance(modalEl);
        modalInst.show();
    }
};

window.confirmarExclusaoCat = function(id, name, total) {
    // Remove qualquer tooltip ativo na tela imediatamente
    document.querySelectorAll('.tooltip').forEach(t => t.remove());

    let alertHtml = `Tem certeza de que deseja excluir a categoria <strong>"${name}"</strong>?`;
    if (total > 0) {
        alertHtml += `<div class="alert alert-warning border-0 mt-3 mb-0 text-start small">
            <i class="bi bi-exclamation-triangle-fill text-warning me-1"></i>
            <strong>Atenção:</strong> Esta categoria possui <strong>${total} foto(s)</strong> vinculadas. Ao confirmar, todas essas fotos e seus arquivos serão excluídos do sistema!
        </div>`;
    } else {
        alertHtml += `<br><small class="text-body-secondary">Esta ação não poderá ser desfeita.</small>`;
    }

    if (typeof Swal !== 'undefined') {
        Swal.fire({
            title: 'Confirmar Exclusão',
            html: alertHtml,
            icon: total > 0 ? 'warning' : 'question',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="bi bi-trash3 me-1"></i> Sim, excluir tudo',
            cancelButtonText: 'Cancelar',
            customClass: {
                confirmButton: 'btn btn-danger px-3 me-2',
                cancelButton: 'btn btn-secondary px-3'
            },
            buttonsStyling: false
        }).then((result) => {
            if (result.isConfirmed) {
                executarExclusaoCat(id);
            }
        });
    } else {
        executarExclusaoCat(id);
    }
};

window.executarExclusaoCat = function(id) {
    fetch('photo-category.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: new URLSearchParams({
            action: 'delete_photo_category',
            id: id
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'success',
                    title: 'Excluído com sucesso!',
                    text: data.message,
                    timer: 1600,
                    showConfirmButton: false
                }).then(() => {
                    window.location.reload();
                });
            } else {
                window.location.reload();
            }
        } else {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'error',
                    title: 'Erro ao excluir',
                    text: data.message || 'Não foi possível excluir a categoria.'
                });
            }
        }
    })
    .catch(err => {
        console.error(err);
        window.location.href = 'photo-category-delete.php?id=' + id;
    });
};

// Delegação Global de Cliques (Resistente a Paginação, Ordenação e SPA)
document.addEventListener('click', function (e) {
    // 1. Botão Ver Detalhes
    const btnView = e.target.closest('.btn-view-cat');
    if (btnView) {
        e.preventDefault();
        const id     = btnView.getAttribute('data-id');
        const name   = btnView.getAttribute('data-name');
        const total  = parseInt(btnView.getAttribute('data-total') || '0', 10);
        const status = btnView.getAttribute('data-status');
        window.abrirModalCat({ id, name, total, status });
        return;
    }

    // 2. Botão Excluir
    const btnDel = e.target.closest('.btn-delete-cat');
    if (btnDel) {
        e.preventDefault();
        const id    = btnDel.getAttribute('data-id');
        const name  = btnDel.getAttribute('data-name') || 'esta categoria';
        const total = parseInt(btnDel.getAttribute('data-total') || '0', 10);
        window.confirmarExclusaoCat(id, name, total);
        return;
    }
});

// Inicialização Única do DataTables
function initTabelaPhotoCat() {
    if (typeof window.jQuery !== 'undefined' && typeof window.jQuery.fn.DataTable !== 'undefined') {
        const $table = $('#tabelaPhotoCat');
        if ($table.length && !$.fn.DataTable.isDataTable('#tabelaPhotoCat')) {
            $table.DataTable({
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
    }
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initTabelaPhotoCat);
} else {
    initTabelaPhotoCat();
}
</script>

<?php require_once('footer.php'); ?>
