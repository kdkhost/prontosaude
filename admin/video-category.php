<?php
require_once('header.php');

// 1. Endpoint AJAX: Excluir Categoria e seus Vídeos Vinculados
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_video_category') {
    header('Content-Type: application/json; charset=utf-8');
    $id = (int)($_POST['id'] ?? 0);

    if ($id > 0) {
        // Remove os vídeos vinculados à categoria
        $stmt_vid = $pdo->prepare("DELETE FROM tbl_video WHERE v_category_id=?");
        $stmt_vid->execute([$id]);

        // Remove a categoria de vídeo
        $stmt_cat = $pdo->prepare("DELETE FROM tbl_category_video WHERE v_category_id=?");
        $stmt_cat->execute([$id]);

        echo json_encode([
            'status'  => 'success', 
            'message' => 'Categoria de vídeo e seus conteúdos vinculados foram removidos com sucesso!'
        ]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Identificador de categoria inválido.']);
    }
    exit;
}

// 2. Consulta de Categorias com Contagem de Vídeos Vinculados (KPIs)
$statement = $pdo->prepare("SELECT 
                                c.v_category_id, 
                                c.v_category_name, 
                                c.status,
                                COUNT(v.video_id) AS total_videos,
                                SUM(CASE WHEN v.status = 'Active' THEN 1 ELSE 0 END) AS total_videos_ativos
                            FROM tbl_category_video c
                            LEFT JOIN tbl_video v ON c.v_category_id = v.v_category_id
                            GROUP BY c.v_category_id, c.v_category_name, c.status
                            ORDER BY c.v_category_id ASC");
$statement->execute();
$categories = $statement->fetchAll(PDO::FETCH_ASSOC);

$total_categorias     = count($categories);
$total_videos_geral   = 0;
$total_videos_ativos  = 0;
$categorias_com_video = 0;

foreach ($categories as $cat) {
    $qtd = (int)$cat['total_videos'];
    $qtd_ativos = (int)$cat['total_videos_ativos'];
    $total_videos_geral += $qtd;
    $total_videos_ativos += $qtd_ativos;
    if ($qtd > 0) {
        $categorias_com_video++;
    }
}
$categorias_vazias = max(0, $total_categorias - $categorias_com_video);
?>

<!-- Cabeçalho da Página -->
<div class="app-content-header pb-2 mb-3 border-bottom">
    <div class="container-fluid">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div>
                <h3 class="mb-1 fw-bold">Categorias de Vídeo</h3>
                <ol class="breadcrumb mb-0 small">
                    <li class="breadcrumb-item"><a href="index.php"><i class="bi bi-house-door"></i> Início</a></li>
                    <li class="breadcrumb-item"><a href="video.php">Vídeos</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Categorias</li>
                </ol>
            </div>
            <div class="d-flex flex-wrap align-items-center gap-2">
                <a href="<?= BASE_URL; ?>page.php?slug=videos" target="_blank" rel="noopener" class="btn btn-outline-info btn-sm shadow-xs" title="Ver galeria de vídeos no site">
                    <i class="bi bi-box-arrow-up-right me-1"></i> Ver no Site
                </a>
                <a href="video.php" class="btn btn-outline-secondary btn-sm shadow-xs" title="Ver todos os vídeos cadastrados">
                    <i class="bi bi-camera-video me-1"></i> Ver Todos os Vídeos
                </a>
                <a href="video-category-add.php" class="btn btn-primary btn-sm shadow-xs px-3">
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

            <!-- KPI 2: Vídeos Vinculados -->
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card shadow-sm border-0 border-start border-success border-4 h-100 bg-body">
                    <div class="card-body d-flex align-items-center justify-content-between py-3">
                        <div>
                            <div class="text-body-secondary small text-uppercase fw-bold">Vídeos Vinculados</div>
                            <div class="fs-3 fw-bold text-success mt-1" id="kpiVideos"><?= $total_videos_geral; ?></div>
                        </div>
                        <div class="p-3 bg-success-subtle text-success rounded-circle">
                            <i class="bi bi-camera-reels-fill fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- KPI 3: Categorias com Vídeos -->
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card shadow-sm border-0 border-start border-info border-4 h-100 bg-body">
                    <div class="card-body d-flex align-items-center justify-content-between py-3">
                        <div>
                            <div class="text-body-secondary small text-uppercase fw-bold">Com Vídeos Ativos</div>
                            <div class="fs-3 fw-bold text-info mt-1" id="kpiComVideos"><?= $categorias_com_video; ?></div>
                        </div>
                        <div class="p-3 bg-info-subtle text-info rounded-circle">
                            <i class="bi bi-collection-play-fill fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- KPI 4: Categorias sem Vídeos -->
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

        <!-- Tabela Principal de Categorias de Vídeo -->
        <div class="row">
            <div class="col-12">
                <div class="card card-outline card-primary shadow-sm mb-4">
                    
                    <div class="card-header bg-transparent py-3 d-flex flex-wrap align-items-center justify-content-between gap-2 border-bottom">
                        <div class="d-flex align-items-center gap-2">
                            <h5 class="card-title fw-bold mb-0 text-body">
                                <i class="bi bi-folder-play text-primary me-2"></i>Gerenciar Categorias de Vídeos
                            </h5>
                        </div>
                        <span class="badge bg-secondary-subtle text-secondary small">
                            Organização da Galeria de Vídeos &amp; Procedimentos
                        </span>
                    </div>

                    <div class="card-body table-responsive">
                        <table id="tabelaVideoCat" class="table table-hover align-middle w-100">
                            <thead>
                                <tr>
                                    <th width="50" class="text-center">#</th>
                                    <th>Nome da Categoria</th>
                                    <th width="190" class="text-center">Vídeos Vinculados</th>
                                    <th width="140" class="text-center">Status</th>
                                    <th width="140" class="text-center">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $seq = 0;
                                foreach ($categories as $row):
                                    $seq++;
                                    $cat_id       = (int)$row['v_category_id'];
                                    $cat_name     = htmlspecialchars($row['v_category_name'] ?? 'Categoria #' . $cat_id);
                                    $cat_status   = $row['status'] ?? 'Active';
                                    $is_active    = ($cat_status === 'Active' || $cat_status === 'Ativo');
                                    $total_v      = (int)($row['total_videos'] ?? 0);
                                    $total_ativos = (int)($row['total_videos_ativos'] ?? 0);
                                ?>
                                <tr id="tr-cat-<?= $cat_id; ?>">
                                    <td class="text-center text-body-secondary fw-semibold"><?= $seq; ?></td>
                                    
                                    <!-- Nome da Categoria -->
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="p-2 bg-primary-subtle text-primary rounded-3">
                                                <i class="bi bi-folder-fill fs-5"></i>
                                            </div>
                                            <div>
                                                <div class="fw-bold text-body fs-6"><?= $cat_name; ?></div>
                                                <small class="text-body-secondary">Identificador: #<?= $cat_id; ?></small>
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Vídeos Vinculados -->
                                    <td class="text-center">
                                        <?php if ($total_v > 0): ?>
                                            <a href="video.php" class="badge bg-primary-subtle text-primary border border-primary-subtle text-decoration-none px-3 py-2 rounded-pill small" title="Ver vídeos desta categoria">
                                                <i class="bi bi-camera-video-fill me-1"></i> <?= $total_v; ?> vídeo(s)
                                                <?php if ($total_ativos > 0): ?>
                                                    <span class="text-success ms-1">(<?= $total_ativos; ?> ativos)</span>
                                                <?php endif; ?>
                                            </a>
                                        <?php else: ?>
                                            <span class="badge bg-secondary-subtle text-body-secondary border border-secondary-subtle px-3 py-2 rounded-pill small">
                                                <i class="bi bi-dash-circle me-1"></i> Nenhum vídeo
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
                                            <button type="button" class="btn btn-outline-info btn-sm"
                                                    onclick="abrirModalCat({id: <?= $cat_id; ?>, name: '<?= addslashes($cat_name); ?>', total: <?= $total_v; ?>, status: '<?= $is_active ? 'Ativo' : 'Inativo'; ?>'})"
                                                    title="Visualizar detalhes da categoria">
                                                <i class="bi bi-eye"></i>
                                            </button>

                                            <!-- Botão Editar -->
                                            <a href="video-category-edit.php?id=<?= $cat_id; ?>" class="btn btn-outline-primary btn-sm" title="Editar categoria">
                                                <i class="bi bi-pencil-square"></i>
                                            </a>

                                            <!-- Botão Excluir Seguro com SweetAlert2 -->
                                            <button type="button" class="btn btn-outline-danger btn-sm btn-delete-cat"
                                                    data-id="<?= $cat_id; ?>"
                                                    data-name="<?= $cat_name; ?>"
                                                    data-total="<?= $total_v; ?>"
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
<div class="modal fade" id="modalVideoCatDetalhes" tabindex="-1" aria-labelledby="modalVideoCatDetalhesLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content bg-body text-body border-0 shadow-lg rounded-3">
            <div class="modal-header bg-body-tertiary py-3 border-bottom border-secondary-subtle">
                <h5 class="modal-title fw-bold text-body d-flex align-items-center gap-2" id="modalVideoCatDetalhesLabel">
                    <i class="bi bi-folder-fill text-primary"></i>
                    <span>Detalhes da Categoria</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>

            <div class="modal-body p-4 bg-body-tertiary">
                <div class="card border border-secondary-subtle shadow-sm rounded-3 p-4 bg-body">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-1 rounded-pill small" id="mcdBadgeId">
                            Categoria #0
                        </span>
                        <div id="mcdBadgeStatus"></div>
                    </div>

                    <h4 class="fw-bold text-body mb-3" id="mcdNome">Nome da Categoria</h4>

                    <div class="p-3 bg-body-secondary rounded-3 border border-secondary-subtle">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="text-body-secondary small fw-semibold">Vídeos vinculados no sistema:</span>
                            <span class="fw-bold text-body" id="mcdTotalVideos">0 vídeos</span>
                        </div>
                        <p class="text-body-secondary small mb-0">
                            Ao organizar seus vídeos em categorias, eles são agrupados na galeria pública do portal para os pacientes.
                        </p>
                    </div>
                </div>
            </div>

            <div class="modal-footer bg-body border-top border-secondary-subtle py-2 d-flex justify-content-between">
                <a href="video.php" class="btn btn-outline-secondary btn-sm px-3">
                    <i class="bi bi-camera-video me-1"></i> Abrir Vídeos
                </a>
                <div class="d-flex gap-2">
                    <a href="#" id="mcdLinkEditar" class="btn btn-primary btn-sm px-3">
                        <i class="bi bi-pencil-square me-1"></i> Editar Categoria
                    </a>
                    <button type="button" class="btn btn-secondary btn-sm px-3" data-bs-dismiss="modal">Fechar</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // 1. DataTables com Tradução Completa
    let table = null;
    if (typeof window.jQuery !== 'undefined' && typeof window.jQuery.fn.DataTable !== 'undefined') {
        table = $('#tabelaVideoCat').DataTable({
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

    // 2. Exclusão Segura com SweetAlert2
    const deleteButtons = document.querySelectorAll('.btn-delete-cat');
    deleteButtons.forEach(btn => {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            const id = this.dataset.id;
            const name = this.dataset.name || 'esta categoria';
            const total = parseInt(this.dataset.total || '0', 10);

            let alertHtml = `Tem certeza de que deseja excluir a categoria <strong>"${name}"</strong>?`;
            if (total > 0) {
                alertHtml += `<div class="alert alert-warning border-0 mt-3 mb-0 text-start small">
                    <i class="bi bi-exclamation-triangle-fill text-warning me-1"></i>
                    <strong>Atenção:</strong> Esta categoria possui <strong>${total} vídeo(s)</strong> vinculados. Ao confirmar, todos esses vídeos também serão excluídos do sistema!
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
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        executarExclusao(id);
                    }
                });
            } else {
                executarExclusao(id);
            }
        });
    });

    function executarExclusao(id) {
        fetch('video-category.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: new URLSearchParams({
                action: 'delete_video_category',
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
                        timer: 1800,
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
                } else {
                    alert(data.message);
                }
            }
        })
        .catch(err => {
            console.error(err);
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'error',
                    title: 'Falha de comunicação',
                    text: 'Não foi possível concluir a solicitação. A página será recarregada.'
                }).then(() => {
                    window.location.href = 'video-category-delete.php?id=' + id;
                });
            } else {
                window.location.href = 'video-category-delete.php?id=' + id;
            }
        });
    }

    // 3. Modal de Detalhes da Categoria
    window.abrirModalCat = function(data) {
        document.getElementById('mcdBadgeId').textContent = 'Categoria #' + data.id;
        document.getElementById('mcdNome').textContent = data.name;
        document.getElementById('mcdTotalVideos').textContent = data.total + (data.total === 1 ? ' vídeo cadastrado' : ' vídeos cadastrados');
        
        const badgeStatus = document.getElementById('mcdBadgeStatus');
        if (data.status === 'Ativo') {
            badgeStatus.innerHTML = '<span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-1 rounded-pill small"><i class="bi bi-check-circle-fill me-1"></i> Ativo</span>';
        } else {
            badgeStatus.innerHTML = '<span class="badge bg-secondary-subtle text-body-secondary border border-secondary-subtle px-3 py-1 rounded-pill small"><i class="bi bi-pause-circle-fill me-1"></i> Inativo</span>';
        }

        const editLink = document.getElementById('mcdLinkEditar');
        if (editLink) editLink.href = 'video-category-edit.php?id=' + data.id;

        const modalEl = document.getElementById('modalVideoCatDetalhes');
        if (modalEl && typeof bootstrap !== 'undefined') {
            bootstrap.Modal.getOrCreateInstance(modalEl).show();
        }
    };
});
</script>

<?php require_once('footer.php'); ?>
