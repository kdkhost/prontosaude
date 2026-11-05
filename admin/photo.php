<?php
require_once('header.php');

// 1. Endpoint AJAX: Excluir Foto de Forma Segura
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_photo') {
    header('Content-Type: application/json; charset=utf-8');
    $id = (int)($_POST['id'] ?? 0);

    if ($id > 0) {
        $stmt_find = $pdo->prepare("SELECT photo_name FROM tbl_photo WHERE photo_id=?");
        $stmt_find->execute([$id]);
        $row_p = $stmt_find->fetch(PDO::FETCH_ASSOC);

        if ($row_p) {
            $photo_name = trim($row_p['photo_name'] ?? '');
            if (!empty($photo_name)) {
                $caminhos = [
                    __DIR__ . '/../assets/uploads/' . $photo_name,
                    __DIR__ . '/../assets/uploads/photos/' . $photo_name
                ];
                foreach ($caminhos as $path) {
                    if (file_exists($path) && is_file($path)) {
                        @unlink($path);
                    }
                }
            }
        }

        $stmt_del = $pdo->prepare("DELETE FROM tbl_photo WHERE photo_id=?");
        $stmt_del->execute([$id]);
        echo json_encode(['status' => 'success', 'message' => 'Foto removida com sucesso da galeria!']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Identificador de foto inválido.']);
    }
    exit;
}

// 2. Consulta de Fotos com Categorias
$statement = $pdo->prepare("SELECT 
                                t1.photo_id,
                                t1.photo_caption,
                                t1.photo_name,
                                t1.p_category_id,
                                t1.status,
                                t2.p_category_name
                            FROM tbl_photo t1
                            LEFT JOIN tbl_category_photo t2 ON t1.p_category_id = t2.p_category_id
                            ORDER BY t1.photo_id DESC");
$statement->execute();
$photos = $statement->fetchAll(PDO::FETCH_ASSOC);

// Métricas de KPIs
$total_photos    = count($photos);
$total_ativas    = 0;
$total_inativas  = 0;
$categorias_set  = [];

foreach ($photos as $p) {
    if (($p['status'] ?? 'Active') === 'Active') {
        $total_ativas++;
    } else {
        $total_inativas++;
    }
    if (!empty($p['p_category_name'])) {
        $categorias_set[$p['p_category_name']] = true;
    }
}
$total_categorias_com_foto = count($categorias_set);

/**
 * Função Auxiliar para Resolver Caminho e URL da Foto
 */
function pronto_resolver_caminho_foto($photo_name) {
    $arq = trim((string)$photo_name);
    if (empty($arq)) {
        return [
            'url' => BASE_URL . 'assets/images/no-image-found.png',
            'exists' => false,
            'ext' => 'IMG'
        ];
    }

    $ext = strtoupper(pathinfo($arq, PATHINFO_EXTENSION)) ?: 'IMG';
    $caminhos = [
        __DIR__ . '/../assets/uploads/' . $arq,
        __DIR__ . '/../assets/uploads/photos/' . $arq
    ];

    foreach ($caminhos as $path) {
        if (file_exists($path) && is_file($path)) {
            $rel = (strpos($path, 'photos') !== false) 
                ? '../assets/uploads/photos/' . $arq 
                : '../assets/uploads/' . $arq;
            return [
                'url' => $rel,
                'exists' => true,
                'ext' => $ext
            ];
        }
    }

    return [
        'url' => '../assets/uploads/' . $arq,
        'exists' => false,
        'ext' => $ext
    ];
}
?>

<style>
.photo-card-thumb .zoom-icon-circle {
    transition: transform 0.2s ease, background-color 0.2s ease;
}
.photo-card-thumb:hover .photo-overlay {
    background-color: rgba(0, 0, 0, 0.45) !important;
}
.photo-card-thumb:hover .zoom-icon-circle {
    transform: scale(1.18);
    background-color: var(--bs-primary) !important;
}
</style>

<!-- Cabeçalho da Página -->
<div class="app-content-header pb-2 mb-3 border-bottom">
    <div class="container-fluid">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div>
                <h3 class="mb-1 fw-bold">Galeria de Fotos</h3>
                <ol class="breadcrumb mb-0 small">
                    <li class="breadcrumb-item"><a href="index.php"><i class="bi bi-house-door"></i> Início</a></li>
                    <li class="breadcrumb-item"><a href="photo.php">Multimídia</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Fotos</li>
                </ol>
            </div>
            <div class="d-flex flex-wrap align-items-center gap-2">
                <a href="<?= BASE_URL; ?>page.php?slug=fotos" target="_blank" rel="noopener" class="btn btn-outline-info btn-sm shadow-xs" title="Ver galeria de fotos no site público">
                    <i class="bi bi-box-arrow-up-right me-1"></i> Ver no Site
                </a>
                <a href="photo-category.php" class="btn btn-outline-secondary btn-sm shadow-xs" title="Gerenciar categorias de fotos">
                    <i class="bi bi-tags me-1"></i> Categorias
                </a>
                <a href="photo-add.php" class="btn btn-primary btn-sm shadow-xs px-3">
                    <i class="bi bi-plus-lg me-1"></i> Adicionar Foto
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
            <!-- KPI 1: Total de Fotos -->
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card shadow-sm border-0 border-start border-primary border-4 h-100 bg-body">
                    <div class="card-body d-flex align-items-center justify-content-between py-3">
                        <div>
                            <div class="text-body-secondary small text-uppercase fw-bold">Total no Acervo</div>
                            <div class="fs-3 fw-bold text-body mt-1" id="kpiTotal"><?= $total_photos; ?></div>
                        </div>
                        <div class="kpi-icon-circle bg-primary-subtle text-primary rounded-circle">
                            <i class="bi bi-images"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- KPI 2: Fotos Visíveis -->
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card shadow-sm border-0 border-start border-success border-4 h-100 bg-body">
                    <div class="card-body d-flex align-items-center justify-content-between py-3">
                        <div>
                            <div class="text-body-secondary small text-uppercase fw-bold">Visíveis no Site</div>
                            <div class="fs-3 fw-bold text-success mt-1" id="kpiAtivos"><?= $total_ativas; ?></div>
                        </div>
                        <div class="kpi-icon-circle bg-success-subtle text-success rounded-circle">
                            <i class="bi bi-check-circle-fill"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- KPI 3: Ocultas / Pausadas -->
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card shadow-sm border-0 border-start border-warning border-4 h-100 bg-body">
                    <div class="card-body d-flex align-items-center justify-content-between py-3">
                        <div>
                            <div class="text-body-secondary small text-uppercase fw-bold">Ocultas / Pausadas</div>
                            <div class="fs-3 fw-bold text-warning mt-1" id="kpiInativos"><?= $total_inativas; ?></div>
                        </div>
                        <div class="kpi-icon-circle bg-warning-subtle text-warning rounded-circle">
                            <i class="bi bi-eye-slash-fill"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- KPI 4: Categorias com Fotos -->
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card shadow-sm border-0 border-start border-info border-4 h-100 bg-body">
                    <div class="card-body d-flex align-items-center justify-content-between py-3">
                        <div>
                            <div class="text-body-secondary small text-uppercase fw-bold">Categorias Ativas</div>
                            <div class="fs-3 fw-bold text-info mt-1"><?= $total_categorias_com_foto; ?></div>
                        </div>
                        <div class="kpi-icon-circle bg-info-subtle text-info rounded-circle">
                            <i class="bi bi-folder-check"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabela Principal de Fotos -->
        <div class="row">
            <div class="col-12">
                <div class="card card-outline card-primary shadow-sm mb-4">
                    
                    <div class="card-header bg-transparent py-3 d-flex flex-wrap align-items-center justify-content-between gap-2 border-bottom">
                        <div class="d-flex align-items-center gap-2">
                            <h5 class="card-title fw-bold mb-0 text-body">
                                <i class="bi bi-images text-primary me-2"></i>Acervo de Fotos &amp; Instalações
                            </h5>
                        </div>

                        <!-- Filtros Rápidos por Status (Pílulas) -->
                        <div class="btn-group btn-group-sm" role="group" aria-label="Filtrar por Status">
                            <button type="button" class="btn btn-outline-secondary active filter-btn" data-filter="all">
                                Todos <span class="badge bg-secondary-subtle text-body ms-1"><?= $total_photos; ?></span>
                            </button>
                            <button type="button" class="btn btn-outline-success filter-btn" data-filter="active">
                                Visíveis <span class="badge bg-success-subtle text-success ms-1"><?= $total_ativas; ?></span>
                            </button>
                            <button type="button" class="btn btn-outline-warning filter-btn" data-filter="inactive">
                                Ocultas <span class="badge bg-warning-subtle text-warning ms-1"><?= $total_inativas; ?></span>
                            </button>
                        </div>
                    </div>

                    <div class="card-body table-responsive">
                        <table id="tabelaFotos" class="table table-hover align-middle w-100">
                            <thead>
                                <tr>
                                    <th width="45" class="text-center">#</th>
                                    <th width="140" class="text-center">Foto / Miniatura</th>
                                    <th>Legenda / Identificação</th>
                                    <th width="180">Categoria</th>
                                    <th width="150" class="text-center">Status</th>
                                    <th width="140" class="text-center">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $seq = 0;
                                foreach ($photos as $row):
                                    $seq++;
                                    $p_id        = (int)$row['photo_id'];
                                    $p_caption   = htmlspecialchars($row['photo_caption'] ?? 'Foto #' . $p_id);
                                    $p_name      = htmlspecialchars($row['photo_name'] ?? '');
                                    $p_cat       = htmlspecialchars($row['p_category_name'] ?? 'Geral');
                                    $p_status    = $row['status'] ?? 'Active';
                                    $is_active   = ($p_status === 'Active');
                                    $info_foto   = pronto_resolver_caminho_foto($row['photo_name']);
                                ?>
                                <tr id="tr-photo-<?= $p_id; ?>" data-status="<?= $is_active ? 'active' : 'inactive'; ?>">
                                    <td class="text-center text-body-secondary fw-semibold"><?= $seq; ?></td>

                                    <!-- Miniatura com Proporção Widescreen/Fotográfica -->
                                    <td class="text-center">
                                        <div class="position-relative overflow-hidden rounded-3 border border-secondary-subtle shadow-xs mx-auto bg-body-secondary d-flex align-items-center justify-content-center photo-card-thumb btn-view-photo"
                                             style="width: 120px; aspect-ratio: 4/3; cursor: pointer;"
                                             data-id="<?= $p_id; ?>"
                                             data-title="<?= htmlspecialchars($p_caption, ENT_QUOTES, 'UTF-8'); ?>"
                                             data-url="<?= htmlspecialchars($info_foto['url'], ENT_QUOTES, 'UTF-8'); ?>"
                                             data-cat="<?= htmlspecialchars($p_cat, ENT_QUOTES, 'UTF-8'); ?>"
                                             data-file="<?= htmlspecialchars($p_name, ENT_QUOTES, 'UTF-8'); ?>"
                                             title="Clique para ampliar esta foto">
                                            
                                            <img src="<?= $info_foto['url']; ?>" alt="<?= $p_caption; ?>" class="w-100 h-100 object-fit-cover" loading="lazy">

                                            <!-- Overlay com Ícone de Lupa Centralizado -->
                                            <div class="position-absolute w-100 h-100 top-0 start-0 bg-dark bg-opacity-10 d-flex align-items-center justify-content-center photo-overlay">
                                                <div class="d-flex align-items-center justify-content-center rounded-circle bg-dark bg-opacity-75 text-white shadow-sm zoom-icon-circle" style="width: 32px; height: 32px;">
                                                    <i class="bi bi-zoom-in fs-6 text-white"></i>
                                                </div>
                                            </div>

                                            <!-- Badge de Extensão -->
                                            <span class="position-absolute bottom-0 end-0 badge bg-dark bg-opacity-75 text-uppercase m-1 border border-secondary border-opacity-50" style="font-size: 0.65rem;">
                                                <?= $info_foto['ext']; ?>
                                            </span>
                                        </div>
                                    </td>

                                    <!-- Legenda e Nome do Arquivo -->
                                    <td>
                                        <div class="fw-bold text-body fs-6 mb-1 d-flex align-items-center gap-1">
                                            <i class="bi bi-image text-primary me-1"></i>
                                            <span><?= $p_caption; ?></span>
                                        </div>
                                        <small class="text-body-secondary d-block">
                                            <i class="bi bi-file-earmark-image me-1"></i><?= $p_name ?: 'sem arquivo'; ?> &bull; 
                                            <button type="button" class="btn btn-link btn-sm p-0 text-decoration-none small text-primary btn-view-photo"
                                                    data-id="<?= $p_id; ?>"
                                                    data-title="<?= htmlspecialchars($p_caption, ENT_QUOTES, 'UTF-8'); ?>"
                                                    data-url="<?= htmlspecialchars($info_foto['url'], ENT_QUOTES, 'UTF-8'); ?>"
                                                    data-cat="<?= htmlspecialchars($p_cat, ENT_QUOTES, 'UTF-8'); ?>"
                                                    data-file="<?= htmlspecialchars($p_name, ENT_QUOTES, 'UTF-8'); ?>">
                                                <i class="bi bi-arrows-fullscreen me-1"></i>Ampliar
                                            </button>
                                        </small>
                                    </td>

                                    <!-- Categoria -->
                                    <td>
                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle small fw-semibold">
                                            <i class="bi bi-tag-fill me-1"></i><?= $p_cat; ?>
                                        </span>
                                    </td>

                                    <!-- Status com Switch AJAX Global -->
                                    <td class="text-center">
                                        <div class="d-inline-flex align-items-center gap-2">
                                            <div class="form-check form-switch mb-0" title="Ativar ou desativar esta foto na galeria">
                                                <input class="form-check-input pronto-status-switch" type="checkbox" role="switch"
                                                       data-entity="photo"
                                                       data-id="<?= $p_id; ?>"
                                                       data-name="<?= $p_caption; ?>"
                                                       data-active-text="Visível"
                                                       data-inactive-text="Oculta"
                                                       id="switch-photo-<?= $p_id; ?>"
                                                       <?= $is_active ? 'checked' : ''; ?>>
                                            </div>
                                            <span class="badge status-badge <?= $is_active ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-warning-subtle text-warning border border-warning-subtle'; ?> rounded-pill small" id="badge-status-<?= $p_id; ?>">
                                                <?= $is_active ? 'Visível' : 'Oculta'; ?>
                                            </span>
                                        </div>
                                    </td>

                                    <!-- Ações Padronizadas -->
                                    <td class="text-center text-nowrap">
                                        <div class="d-flex align-items-center justify-content-center gap-1">
                                            <!-- Botão Ampliar / Lightbox -->
                                            <button type="button" class="btn btn-outline-info btn-sm btn-view-photo"
                                                    data-id="<?= $p_id; ?>"
                                                    data-title="<?= htmlspecialchars($p_caption, ENT_QUOTES, 'UTF-8'); ?>"
                                                    data-url="<?= htmlspecialchars($info_foto['url'], ENT_QUOTES, 'UTF-8'); ?>"
                                                    data-cat="<?= htmlspecialchars($p_cat, ENT_QUOTES, 'UTF-8'); ?>"
                                                    data-file="<?= htmlspecialchars($p_name, ENT_QUOTES, 'UTF-8'); ?>"
                                                    title="Ampliar foto em detalhes">
                                                <i class="bi bi-zoom-in"></i>
                                            </button>

                                            <!-- Botão Editar -->
                                            <a href="photo-edit.php?id=<?= $p_id; ?>" class="btn btn-outline-primary btn-sm" title="Editar esta foto">
                                                <i class="bi bi-pencil-square"></i>
                                            </a>

                                            <!-- Botão Excluir Seguro com SweetAlert2 -->
                                            <button type="button" class="btn btn-outline-danger btn-sm btn-delete-photo"
                                                    data-id="<?= $p_id; ?>"
                                                    data-title="<?= htmlspecialchars($p_caption, ENT_QUOTES, 'UTF-8'); ?>"
                                                    data-thumb="<?= htmlspecialchars($info_foto['url'], ENT_QUOTES, 'UTF-8'); ?>"
                                                    title="Excluir esta foto">
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
<!-- MODAL DINÂMICO: LIGHTBOX DE ALTA RESOLUÇÃO -->
<!-- ========================================== -->
<div class="modal fade" id="modalLightboxFoto" tabindex="-1" aria-labelledby="modalLightboxFotoLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content bg-body text-body border-0 shadow-lg rounded-3">
            <div class="modal-header bg-body-tertiary py-3 border-bottom border-secondary-subtle d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-primary rounded-pill p-2"><i class="bi bi-image"></i></span>
                    <div>
                        <h5 class="modal-title fw-bold text-body mb-0" id="mlfTitulo">Visualizar Foto</h5>
                        <small class="text-body-secondary" id="mlfCategoria">Galeria de Fotos Pronto Saúde</small>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>

            <div class="modal-body p-3 bg-black text-center">
                <div class="d-flex align-items-center justify-content-center" style="min-height: 320px; max-height: 72vh;">
                    <img src="" alt="Foto Ampliada" id="mlfImagem" class="img-fluid rounded-3 shadow-sm object-fit-contain" style="max-height: 70vh;">
                </div>
            </div>

            <div class="modal-footer bg-body-tertiary border-top border-secondary-subtle py-2 d-flex justify-content-between">
                <div class="small text-body-secondary" id="mlfArquivo">
                    <i class="bi bi-file-earmark-image me-1"></i> imagem.jpg
                </div>
                <div class="d-flex gap-2">
                    <a href="#" id="mlfLinkEditar" class="btn btn-primary btn-sm px-3">
                        <i class="bi bi-pencil-square me-1"></i> Editar Foto
                    </a>
                    <button type="button" class="btn btn-secondary btn-sm px-3" data-bs-dismiss="modal">
                        Fechar
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// 1. Variável global da tabela
var tabelaFotosDT = null;

// 2. Funções Globais Independentes de DOMContentLoaded (Compatível com SPA e Reload)
window.abrirLightboxModal = function(data) {
    // Remove qualquer tooltip ativo na tela imediatamente
    document.querySelectorAll('.tooltip').forEach(t => t.remove());

    const tituloEl = document.getElementById('mlfTitulo');
    if (tituloEl) tituloEl.textContent = data.title || 'Visualizar Foto';

    const catEl = document.getElementById('mlfCategoria');
    if (catEl) catEl.textContent = 'Categoria: ' + (data.cat || 'Geral');

    const imgEl = document.getElementById('mlfImagem');
    if (imgEl) imgEl.src = data.url || '';

    const arqEl = document.getElementById('mlfArquivo');
    if (arqEl) arqEl.innerHTML = `<i class="bi bi-file-earmark-image me-1"></i> ${data.file || 'imagem'}`;

    const editLink = document.getElementById('mlfLinkEditar');
    if (editLink) editLink.href = 'photo-edit.php?id=' + (data.id || '');

    const modalEl = document.getElementById('modalLightboxFoto');
    if (modalEl && typeof bootstrap !== 'undefined') {
        const modalInst = bootstrap.Modal.getOrCreateInstance(modalEl);
        modalInst.show();
    }
};

window.confirmarExclusaoFoto = function(id, title, thumb) {
    // Remove qualquer tooltip ativo na tela imediatamente
    document.querySelectorAll('.tooltip').forEach(t => t.remove());

    let imgHtml = '';
    if (thumb) {
        imgHtml = `<div class="mb-3 mt-2 text-center">
            <img src="${thumb}" alt="${title}" class="rounded-3 border border-secondary-subtle shadow-xs" style="max-height: 120px; width: auto; max-width: 100%;">
        </div>`;
    }

    if (typeof Swal !== 'undefined') {
        Swal.fire({
            title: 'Confirmar Exclusão',
            html: `${imgHtml}Deseja realmente remover <strong>"${title}"</strong> do acervo?<br><small class="text-danger">O arquivo físico da imagem também será excluído do servidor.</small>`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="bi bi-trash3 me-1"></i> Sim, excluir foto',
            cancelButtonText: 'Cancelar',
            customClass: {
                confirmButton: 'btn btn-danger px-3 me-2',
                cancelButton: 'btn btn-secondary px-3'
            },
            buttonsStyling: false
        }).then((result) => {
            if (result.isConfirmed) {
                window.executarExclusaoFoto(id);
            }
        });
    } else {
        window.executarExclusaoFoto(id);
    }
};

window.executarExclusaoFoto = function(id) {
    fetch('photo.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: new URLSearchParams({
            action: 'delete_photo',
            id: id
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'success',
                    title: 'Foto Removida!',
                    text: data.message,
                    timer: 1600,
                    showConfirmButton: false
                }).then(() => {
                    const tr = document.getElementById('tr-photo-' + id);
                    if (tr) {
                        if (tabelaFotosDT) {
                            tabelaFotosDT.row($(tr)).remove().draw();
                        } else {
                            tr.remove();
                        }
                        window.atualizarKPIsFotos();
                    } else {
                        window.location.reload();
                    }
                });
            } else {
                window.location.reload();
            }
        } else {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'error',
                    title: 'Erro ao excluir',
                    text: data.message || 'Não foi possível excluir a foto.'
                });
            }
        }
    })
    .catch(err => {
        console.error(err);
        window.location.href = 'photo-delete.php?id=' + id;
    });
};

window.atualizarKPIsFotos = function() {
    const rows = document.querySelectorAll('#tabelaFotos tbody tr[data-status]');
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
};

// 3. Delegação Global de Cliques (Resistente a Paginação, Ordenação e SPA)
document.addEventListener('click', function (e) {
    // A. Botão Ampliar / Ver Foto
    const btnView = e.target.closest('.btn-view-photo');
    if (btnView) {
        e.preventDefault();
        const id    = btnView.getAttribute('data-id');
        const title = btnView.getAttribute('data-title') || 'Foto';
        const url   = btnView.getAttribute('data-url') || '';
        const cat   = btnView.getAttribute('data-cat') || 'Geral';
        const file  = btnView.getAttribute('data-file') || '';
        window.abrirLightboxModal({ id, title, url, cat, file });
        return;
    }

    // B. Botão Excluir Foto
    const btnDel = e.target.closest('.btn-delete-photo');
    if (btnDel) {
        e.preventDefault();
        const id    = btnDel.getAttribute('data-id');
        const title = btnDel.getAttribute('data-title') || 'esta foto';
        const thumb = btnDel.getAttribute('data-thumb') || '';
        window.confirmarExclusaoFoto(id, title, thumb);
        return;
    }

    // C. Filtros de Status (Todos, Visíveis, Ocultas)
    const filterBtn = e.target.closest('.filter-btn');
    if (filterBtn) {
        e.preventDefault();
        document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
        filterBtn.classList.add('active');
        const filter = filterBtn.getAttribute('data-filter');

        if (tabelaFotosDT) {
            if (filter === 'all') {
                tabelaFotosDT.column(4).search('').draw();
            } else if (filter === 'active') {
                tabelaFotosDT.column(4).search('Visível').draw();
            } else if (filter === 'inactive') {
                tabelaFotosDT.column(4).search('Oculta').draw();
            }
        }
        return;
    }
});

// 4. Atualização de KPIs em Mudança de Switch
document.addEventListener('change', function (e) {
    if (e.target && e.target.classList.contains('pronto-status-switch')) {
        const tr = e.target.closest('tr');
        if (tr) {
            tr.setAttribute('data-status', e.target.checked ? 'active' : 'inactive');
            window.atualizarKPIsFotos();
        }
    }
});

// 5. Inicialização Única do DataTables
function initTabelaFotos() {
    if (typeof window.jQuery !== 'undefined' && typeof window.jQuery.fn.DataTable !== 'undefined') {
        const $table = $('#tabelaFotos');
        if ($table.length && !$.fn.DataTable.isDataTable('#tabelaFotos')) {
            tabelaFotosDT = $table.DataTable({
                responsive: true,
                language: {
                    search: "Buscar Foto:",
                    lengthMenu: "Mostrar _MENU_ registros",
                    info: "Exibindo _START_ a _END_ de _TOTAL_ fotos",
                    infoEmpty: "Nenhuma foto cadastrada",
                    infoFiltered: "(filtrado de _MAX_ no total)",
                    zeroRecords: "Nenhuma foto encontrada",
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
    document.addEventListener('DOMContentLoaded', initTabelaFotos);
} else {
    initTabelaFotos();
}
</script>

<?php require_once('footer.php'); ?>
