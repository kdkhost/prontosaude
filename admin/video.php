<?php
require_once('header.php');

// 1. Endpoint AJAX: Excluir Vídeo de Forma Segura
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_video') {
    header('Content-Type: application/json; charset=utf-8');
    $id = (int)($_POST['id'] ?? 0);

    if ($id > 0) {
        $stmt_find = $pdo->prepare("SELECT video_iframe FROM tbl_video WHERE video_id=?");
        $stmt_find->execute([$id]);
        $row_v = $stmt_find->fetch(PDO::FETCH_ASSOC);

        if ($row_v) {
            $v_code = trim($row_v['video_iframe'] ?? '');
            if (!empty($v_code) && !preg_match('/<iframe|youtube|vimeo/i', $v_code)) {
                $caminho_limpo = ltrim(str_replace(['../', './'], '', $v_code), '/');
                $arquivo_fisico = __DIR__ . '/../' . $caminho_limpo;
                if (file_exists($arquivo_fisico) && is_file($arquivo_fisico)) {
                    @unlink($arquivo_fisico);
                }
            }
        }

        $stmt_del = $pdo->prepare("DELETE FROM tbl_video WHERE video_id=?");
        $stmt_del->execute([$id]);
        echo json_encode(['status' => 'success', 'message' => 'Vídeo removido com sucesso da galeria!']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Identificador de vídeo inválido.']);
    }
    exit;
}

// 1.1 Endpoint AJAX: Salvar Miniatura de Vídeo MP4 em Alta Resolução
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save_video_thumb') {
    header('Content-Type: application/json; charset=utf-8');
    $thumb_name = basename($_POST['thumb_name'] ?? '');
    $image_data = $_POST['image_data'] ?? '';

    if (!empty($thumb_name) && preg_match('/^[a-zA-Z0-9_\-\.]+\.jpg$/i', $thumb_name) && strpos($image_data, 'data:image/') === 0) {
        $parts = explode(',', $image_data, 2);
        if (isset($parts[1])) {
            $binary = base64_decode($parts[1]);
            $dir = __DIR__ . '/../assets/uploads/videos/thumbs/';
            if (!is_dir($dir)) {
                @mkdir($dir, 0777, true);
            }
            @file_put_contents($dir . $thumb_name, $binary);
            echo json_encode(['status' => 'success', 'file' => $thumb_name]);
            exit;
        }
    }
    echo json_encode(['status' => 'error', 'message' => 'Dados inválidos']);
    exit;
}

// 2. Consulta de Vídeos com Categorias
$statement = $pdo->prepare("SELECT 
                                t1.video_id,
                                t1.video_title,
                                t1.video_iframe,
                                t1.v_category_id,
                                t1.status,
                                t2.v_category_name
                            FROM tbl_video t1
                            LEFT JOIN tbl_category_video t2 ON t1.v_category_id = t2.v_category_id
                            ORDER BY t1.video_id DESC");
$statement->execute();
$videos = $statement->fetchAll(PDO::FETCH_ASSOC);

// Métricas de KPIs
$total_videos    = count($videos);
$total_ativos    = 0;
$total_inativos  = 0;
$categorias_set  = [];

foreach ($videos as $v) {
    if (($v['status'] ?? 'Active') === 'Active') {
        $total_ativos++;
    } else {
        $total_inativos++;
    }
    if (!empty($v['v_category_name'])) {
        $categorias_set[$v['v_category_name']] = true;
    }
}
$total_categorias_com_video = count($categorias_set);

/**
 * Função Auxiliar para Diagnosticar e Resolver Dados do Vídeo
 */
function pronto_resolver_midia_video($raw) {
    $raw = trim((string)$raw);
    $res = [
        'type' => 'mp4',
        'url' => '',
        'thumb' => '',
        'is_embed' => false
    ];

    if (empty($raw)) {
        return $res;
    }

    // Caso 1: Código <iframe> colado
    if (stripos($raw, '<iframe') !== false) {
        $res['is_embed'] = true;
        if (preg_match('/src=["\']([^"\']+)["\']/i', $raw, $m)) {
            $res['url'] = $m[1];
        } else {
            $res['url'] = $raw;
        }

        if (preg_match('/(?:youtube\.com\/(?:embed\/|v\/)|youtu\.be\/)([\w\-]{11})/i', $res['url'], $ym)) {
            $res['type'] = 'youtube';
            $res['thumb'] = "https://img.youtube.com/vi/{$ym[1]}/mqdefault.jpg";
            $res['url'] = "https://www.youtube.com/embed/{$ym[1]}?autoplay=1";
        } elseif (preg_match('/vimeo\.com\/(?:video\/)?(\d+)/i', $res['url'], $vm)) {
            $res['type'] = 'vimeo';
            $res['url'] = "https://player.vimeo.com/video/{$vm[1]}?autoplay=1";
        }
        return $res;
    }

    // Caso 2: URL direta do YouTube
    if (preg_match('/(?:youtube\.com\/(?:watch\?v=|embed\/|v\/)|youtu\.be\/)([\w\-]{11})/i', $raw, $ym)) {
        $res['type'] = 'youtube';
        $res['thumb'] = "https://img.youtube.com/vi/{$ym[1]}/mqdefault.jpg";
        $res['url'] = "https://www.youtube.com/embed/{$ym[1]}?autoplay=1";
        $res['is_embed'] = true;
        return $res;
    }

    // Caso 3: URL direta do Vimeo
    if (preg_match('/vimeo\.com\/(?:video\/)?(\d+)/i', $raw, $vm)) {
        $res['type'] = 'vimeo';
        $res['url'] = "https://player.vimeo.com/video/{$vm[1]}?autoplay=1";
        $res['is_embed'] = true;
        return $res;
    }

    // Caso 4: Arquivo MP4 ou caminho relativo
    $caminho_rel = $raw;
    if (strpos($caminho_rel, 'assets/') === 0) {
        $caminho_rel = '../' . $caminho_rel;
    }
    $caminho_limpo = ltrim(str_replace(['../', './'], '', $raw), '/');
    $nome_arquivo  = basename($caminho_limpo);
    $nome_base     = pathinfo($nome_arquivo, PATHINFO_FILENAME);
    $thumb_file    = $nome_base . '.jpg';
    $caminho_thumb_disco = __DIR__ . '/../assets/uploads/videos/thumbs/' . $thumb_file;

    $thumb_url = '';
    if (file_exists($caminho_thumb_disco) && filesize($caminho_thumb_disco) > 100) {
        $thumb_url = BASE_URL . 'assets/uploads/videos/thumbs/' . $thumb_file;
    }

    $res['type'] = 'mp4';
    $res['url'] = $caminho_rel;
    $res['full_url'] = BASE_URL . $caminho_limpo;
    $res['thumb'] = $thumb_url;
    $res['thumb_file'] = $thumb_file;
    return $res;
}
?>

<style>
.video-card-thumb .play-btn-circle {
    transition: transform 0.2s ease, background-color 0.2s ease;
}
.video-card-thumb:hover .play-overlay {
    background-color: rgba(0, 0, 0, 0.45) !important;
}
.video-card-thumb:hover .play-btn-circle {
    transform: scale(1.18);
    background-color: var(--bs-primary) !important;
}
</style>

<!-- Cabeçalho da Página -->
<div class="app-content-header pb-2 mb-3 border-bottom">
    <div class="container-fluid">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div>
                <h3 class="mb-1 fw-bold">Galeria de Vídeos</h3>
                <ol class="breadcrumb mb-0 small">
                    <li class="breadcrumb-item"><a href="index.php"><i class="bi bi-house-door"></i> Início</a></li>
                    <li class="breadcrumb-item"><a href="video.php">Multimídia</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Vídeos</li>
                </ol>
            </div>
            <div class="d-flex flex-wrap align-items-center gap-2">
                <a href="<?= BASE_URL; ?>page.php?slug=videos" target="_blank" rel="noopener" class="btn btn-outline-info btn-sm shadow-xs" title="Ver galeria de vídeos no site público">
                    <i class="bi bi-box-arrow-up-right me-1"></i> Ver no Site
                </a>
                <a href="video-category.php" class="btn btn-outline-secondary btn-sm shadow-xs" title="Gerenciar categorias de vídeos">
                    <i class="bi bi-tags me-1"></i> Categorias
                </a>
                <a href="video-add.php" class="btn btn-primary btn-sm shadow-xs px-3">
                    <i class="bi bi-plus-lg me-1"></i> Adicionar Vídeo
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
            <!-- KPI 1: Total de Vídeos -->
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card shadow-sm border-0 border-start border-primary border-4 h-100 bg-body">
                    <div class="card-body d-flex align-items-center justify-content-between py-3">
                        <div>
                            <div class="text-body-secondary small text-uppercase fw-bold">Total no Acervo</div>
                            <div class="fs-3 fw-bold text-body mt-1" id="kpiTotal"><?= $total_videos; ?></div>
                        </div>
                        <div class="p-3 bg-primary-subtle text-primary rounded-circle">
                            <i class="bi bi-camera-reels-fill fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- KPI 2: Vídeos Visíveis -->
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card shadow-sm border-0 border-start border-success border-4 h-100 bg-body">
                    <div class="card-body d-flex align-items-center justify-content-between py-3">
                        <div>
                            <div class="text-body-secondary small text-uppercase fw-bold">Visíveis no Site</div>
                            <div class="fs-3 fw-bold text-success mt-1" id="kpiAtivos"><?= $total_ativos; ?></div>
                        </div>
                        <div class="p-3 bg-success-subtle text-success rounded-circle">
                            <i class="bi bi-play-circle-fill fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- KPI 3: Ocultos / Pausados -->
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card shadow-sm border-0 border-start border-warning border-4 h-100 bg-body">
                    <div class="card-body d-flex align-items-center justify-content-between py-3">
                        <div>
                            <div class="text-body-secondary small text-uppercase fw-bold">Ocultos / Pausados</div>
                            <div class="fs-3 fw-bold text-warning mt-1" id="kpiInativos"><?= $total_inativos; ?></div>
                        </div>
                        <div class="p-3 bg-warning-subtle text-warning rounded-circle">
                            <i class="bi bi-pause-circle-fill fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- KPI 4: Categorias com Vídeos -->
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card shadow-sm border-0 border-start border-info border-4 h-100 bg-body">
                    <div class="card-body d-flex align-items-center justify-content-between py-3">
                        <div>
                            <div class="text-body-secondary small text-uppercase fw-bold">Categorias Ativas</div>
                            <div class="fs-3 fw-bold text-info mt-1"><?= $total_categorias_com_video; ?></div>
                        </div>
                        <div class="p-3 bg-info-subtle text-info rounded-circle">
                            <i class="bi bi-folder-check fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabela Principal de Vídeos -->
        <div class="row">
            <div class="col-12">
                <div class="card card-outline card-primary shadow-sm mb-4">
                    
                    <div class="card-header bg-transparent py-3 d-flex flex-wrap align-items-center justify-content-between gap-2 border-bottom">
                        <div class="d-flex align-items-center gap-2">
                            <h5 class="card-title fw-bold mb-0 text-body">
                                <i class="bi bi-collection-play text-primary me-2"></i>Acervo de Vídeos &amp; Procedimentos
                            </h5>
                        </div>

                        <!-- Filtros Rápidos por Status (Pílulas) -->
                        <div class="btn-group btn-group-sm" role="group" aria-label="Filtrar por Status">
                            <button type="button" class="btn btn-outline-secondary active filter-btn" data-filter="all">
                                Todos <span class="badge bg-secondary-subtle text-body ms-1"><?= $total_videos; ?></span>
                            </button>
                            <button type="button" class="btn btn-outline-success filter-btn" data-filter="active">
                                Visíveis <span class="badge bg-success-subtle text-success ms-1"><?= $total_ativos; ?></span>
                            </button>
                            <button type="button" class="btn btn-outline-warning filter-btn" data-filter="inactive">
                                Ocultos <span class="badge bg-warning-subtle text-warning ms-1"><?= $total_inativos; ?></span>
                            </button>
                        </div>
                    </div>

                    <div class="card-body table-responsive">
                        <table id="tabelaVideos" class="table table-hover align-middle w-100">
                            <thead>
                                <tr>
                                    <th width="45" class="text-center">#</th>
                                    <th width="150" class="text-center">Mídia / Player</th>
                                    <th>Título do Conteúdo</th>
                                    <th width="170">Categoria</th>
                                    <th width="150" class="text-center">Status</th>
                                    <th width="140" class="text-center">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $seq = 0;
                                foreach ($videos as $row):
                                    $seq++;
                                    $v_id        = (int)$row['video_id'];
                                    $v_title     = htmlspecialchars($row['video_title'] ?? 'Vídeo #' . $v_id);
                                    $v_cat       = htmlspecialchars($row['v_category_name'] ?? 'Geral');
                                    $v_status    = $row['status'] ?? 'Active';
                                    $is_active   = ($v_status === 'Active');
                                    $info_midia  = pronto_resolver_midia_video($row['video_iframe']);
                                ?>
                                <tr id="tr-video-<?= $v_id; ?>" data-status="<?= $is_active ? 'active' : 'inactive'; ?>">
                                    <td class="text-center text-body-secondary fw-semibold"><?= $seq; ?></td>

                                    <!-- Miniatura do Vídeo / Botão de Play Inteligente -->
                                    <td class="text-center">
                                        <div class="position-relative overflow-hidden rounded-3 border border-secondary-subtle shadow-xs mx-auto bg-dark d-flex align-items-center justify-content-center video-card-thumb"
                                             style="width: 140px; aspect-ratio: 16/9; cursor: pointer;"
                                             onclick="abrirPlayerModal({id: <?= $v_id; ?>, title: '<?= addslashes($v_title); ?>', type: '<?= $info_midia['type']; ?>', url: '<?= addslashes($info_midia['url']); ?>', cat: '<?= addslashes($v_cat); ?>'})"
                                             title="Assistir a este vídeo no player">
                                            
                                            <?php if (!empty($info_midia['thumb'])): ?>
                                                <img src="<?= $info_midia['thumb']; ?>" alt="<?= $v_title; ?>" class="w-100 h-100 object-fit-cover">
                                            <?php elseif ($info_midia['type'] === 'mp4' && !empty($info_midia['url'])): ?>
                                                <div class="w-100 h-100 position-relative d-flex align-items-center justify-content-center bg-black auto-video-thumb-container"
                                                     data-video-url="<?= htmlspecialchars($info_midia['url']); ?>"
                                                     data-thumb-name="<?= htmlspecialchars($info_midia['thumb_file']); ?>">
                                                    <div class="spinner-border spinner-border-sm text-primary thumb-loader" role="status" style="width: 1.25rem; height: 1.25rem;">
                                                        <span class="visually-hidden">Carregando...</span>
                                                    </div>
                                                    <img src="" class="w-100 h-100 object-fit-cover d-none thumb-target-img" alt="<?= $v_title; ?>">
                                                </div>
                                            <?php else: ?>
                                                <div class="w-100 h-100 d-flex flex-column align-items-center justify-content-center bg-black bg-gradient text-white-50">
                                                    <i class="bi bi-file-earmark-play fs-4 text-primary"></i>
                                                </div>
                                            <?php endif; ?>

                                            <!-- Overlay com Ícone de Play Sobreposto -->
                                            <div class="position-absolute w-100 h-100 top-0 start-0 bg-dark bg-opacity-25 d-flex align-items-center justify-content-center play-overlay">
                                                <div class="d-flex align-items-center justify-content-center rounded-circle bg-dark bg-opacity-75 text-white shadow-sm play-btn-circle" style="width: 36px; height: 36px;">
                                                    <i class="bi bi-play-fill fs-5 text-white"></i>
                                                </div>
                                            </div>

                                            <!-- Badge de Formato -->
                                            <span class="position-absolute bottom-0 end-0 badge bg-dark bg-opacity-75 text-uppercase m-1 border border-secondary border-opacity-50" style="font-size: 0.65rem;">
                                                <?= $info_midia['type']; ?>
                                            </span>
                                        </div>
                                    </td>

                                    <!-- Título do Vídeo -->
                                    <td>
                                        <div class="fw-bold text-body fs-6 mb-1 d-flex align-items-center gap-1">
                                            <i class="bi bi-camera-video text-primary me-1"></i>
                                            <span><?= $v_title; ?></span>
                                        </div>
                                        <small class="text-body-secondary">
                                            <i class="bi bi-hash"></i>ID: <?= $v_id; ?> &bull; 
                                            <button type="button" class="btn btn-link btn-sm p-0 text-decoration-none small text-primary" onclick="abrirPlayerModal({id: <?= $v_id; ?>, title: '<?= addslashes($v_title); ?>', type: '<?= $info_midia['type']; ?>', url: '<?= addslashes($info_midia['url']); ?>', cat: '<?= addslashes($v_cat); ?>'})">
                                                <i class="bi bi-play-circle me-1"></i>Reproduzir
                                            </button>
                                        </small>
                                    </td>

                                    <!-- Categoria -->
                                    <td>
                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle small fw-semibold">
                                            <i class="bi bi-tag-fill me-1"></i><?= $v_cat; ?>
                                        </span>
                                    </td>

                                    <!-- Status com Switch AJAX Global -->
                                    <td class="text-center">
                                        <div class="d-inline-flex align-items-center gap-2">
                                            <div class="form-check form-switch mb-0" title="Ativar ou desativar este vídeo no site">
                                                <input class="form-check-input pronto-status-switch" type="checkbox" role="switch"
                                                       data-entity="video"
                                                       data-id="<?= $v_id; ?>"
                                                       data-name="<?= $v_title; ?>"
                                                       data-active-text="Visível"
                                                       data-inactive-text="Oculto"
                                                       id="switch-video-<?= $v_id; ?>"
                                                       <?= $is_active ? 'checked' : ''; ?>>
                                            </div>
                                            <span class="badge status-badge <?= $is_active ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-warning-subtle text-warning border border-warning-subtle'; ?> rounded-pill small" id="badge-status-<?= $v_id; ?>">
                                                <?= $is_active ? 'Visível' : 'Oculto'; ?>
                                            </span>
                                        </div>
                                    </td>

                                    <!-- Ações Padronizadas -->
                                    <td class="text-center text-nowrap">
                                        <div class="d-flex align-items-center justify-content-center gap-1">
                                            <!-- Botão Assistir / Modal -->
                                            <button type="button" class="btn btn-outline-info btn-sm"
                                                    onclick="abrirPlayerModal({id: <?= $v_id; ?>, title: '<?= addslashes($v_title); ?>', type: '<?= $info_midia['type']; ?>', url: '<?= addslashes($info_midia['url']); ?>', cat: '<?= addslashes($v_cat); ?>'})"
                                                    title="Assistir no player">
                                                <i class="bi bi-play-fill fs-6"></i>
                                            </button>

                                            <!-- Botão Editar -->
                                            <a href="video-edit.php?id=<?= $v_id; ?>" class="btn btn-outline-primary btn-sm" title="Editar este vídeo">
                                                <i class="bi bi-pencil-square"></i>
                                            </a>

                                            <!-- Botão Excluir Seguro com SweetAlert2 -->
                                            <button type="button" class="btn btn-outline-danger btn-sm btn-delete-video"
                                                    data-id="<?= $v_id; ?>"
                                                    data-title="<?= $v_title; ?>"
                                                    title="Excluir este vídeo">
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
<!-- MODAL DINÂMICO: PLAYER WIDESCREEN          -->
<!-- ========================================== -->
<div class="modal fade" id="modalPlayerVideo" tabindex="-1" aria-labelledby="modalPlayerVideoLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content bg-body text-body border-0 shadow-lg rounded-3">
            <div class="modal-header bg-body-tertiary py-3 border-bottom border-secondary-subtle d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-primary rounded-pill p-2"><i class="bi bi-play-circle-fill"></i></span>
                    <div>
                        <h5 class="modal-title fw-bold text-body mb-0" id="mpvTitulo">Reproduzir Vídeo</h5>
                        <small class="text-body-secondary" id="mpvCategoria">Galeria de Vídeos Pronto Saúde</small>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>

            <div class="modal-body p-3 bg-black">
                <div class="ratio ratio-16x9 rounded-3 overflow-hidden shadow-sm" id="mpvContainerPlayer">
                    <!-- Vídeo ou Iframe injetado dinamicamente via JavaScript -->
                </div>
            </div>

            <div class="modal-footer bg-body-tertiary border-top border-secondary-subtle py-2 d-flex justify-content-between">
                <div class="small text-body-secondary">
                    <i class="bi bi-shield-check text-success me-1"></i> Visualização Oficial Pronto Saúde
                </div>
                <div class="d-flex gap-2">
                    <a href="#" id="mpvLinkEditar" class="btn btn-primary btn-sm px-3">
                        <i class="bi bi-pencil-square me-1"></i> Editar Vídeo
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
document.addEventListener('DOMContentLoaded', function () {
    // 1. DataTables com Tradução Completa
    let table = null;
    if (typeof window.jQuery !== 'undefined' && typeof window.jQuery.fn.DataTable !== 'undefined') {
        table = $('#tabelaVideos').DataTable({
            responsive: true,
            language: {
                search: "Buscar Vídeo:",
                lengthMenu: "Mostrar _MENU_ registros",
                info: "Exibindo _START_ a _END_ de _TOTAL_ vídeos",
                infoEmpty: "Nenhum vídeo cadastrado",
                infoFiltered: "(filtrado de _MAX_ no total)",
                zeroRecords: "Nenhum vídeo encontrado",
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

    // 2. Filtro Rápido por Pílulas (Todos, Visíveis, Ocultos)
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
                atualizarKPIs();
            }
        }
    });

    function atualizarKPIs() {
        const rows = document.querySelectorAll('#tabelaVideos tbody tr[data-status]');
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

    // 4. Exclusão Segura com SweetAlert2 (Delegação Global)
    window.confirmarExclusaoVideo = function(id, title) {
        document.querySelectorAll('.tooltip').forEach(t => t.remove());

        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Confirmar Exclusão',
                html: `Deseja realmente remover o vídeo <strong>"${title}"</strong> da galeria?<br><small class="text-danger">Os arquivos de mídia associados também serão excluídos.</small>`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: '<i class="bi bi-trash3 me-1"></i> Sim, excluir vídeo',
                cancelButtonText: 'Cancelar',
                customClass: {
                    confirmButton: 'btn btn-danger px-3 me-2',
                    cancelButton: 'btn btn-secondary px-3'
                },
                buttonsStyling: false
            }).then((result) => {
                if (result.isConfirmed) {
                    executarExclusaoVideo(id);
                }
            });
        } else {
            executarExclusaoVideo(id);
        }
    };

    window.executarExclusaoVideo = function(id) {
        fetch('video.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: new URLSearchParams({
                action: 'delete_video',
                id: id
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Vídeo Removido!',
                        text: data.message,
                        timer: 1600,
                        showConfirmButton: false
                    }).then(() => {
                        const tr = document.getElementById('tr-video-' + id);
                        if (tr) {
                            if (table) {
                                table.row($(tr)).remove().draw();
                            } else {
                                tr.remove();
                            }
                            atualizarKPIs();
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
                        text: data.message || 'Não foi possível excluir o vídeo.'
                    });
                }
            }
        })
        .catch(err => {
            console.error(err);
            window.location.href = 'video-delete.php?id=' + id;
        });
    };

    // Delegação de Clique para Botão de Exclusão de Vídeos
    document.addEventListener('click', function(e) {
        const btnDel = e.target.closest('.btn-delete-video');
        if (btnDel) {
            e.preventDefault();
            const id = btnDel.getAttribute('data-id');
            const title = btnDel.getAttribute('data-title') || 'este vídeo';
            window.confirmarExclusaoVideo(id, title);
        }
    });

    // 5. Gerador Automático de Miniaturas em Alta Resolução para Vídeos MP4
    const thumbContainers = document.querySelectorAll('.auto-video-thumb-container');
    if (thumbContainers.length > 0) {
        thumbContainers.forEach(container => {
            const videoUrl  = container.getAttribute('data-video-url');
            const thumbName = container.getAttribute('data-thumb-name');
            const loader    = container.querySelector('.thumb-loader');
            const imgTarget = container.querySelector('.thumb-target-img');

            if (!videoUrl) return;

            const video = document.createElement('video');
            video.crossOrigin = 'anonymous';
            video.muted = true;
            video.playsInline = true;
            video.preload = 'auto';
            video.src = videoUrl;

            let captured = false;

            const captureFrame = function() {
                if (captured) return;
                try {
                    if (video.videoWidth === 0 || video.videoHeight === 0) return;
                    captured = true;

                    const canvas = document.createElement('canvas');
                    canvas.width = Math.min(video.videoWidth, 640);
                    canvas.height = Math.round(canvas.width * (video.videoHeight / video.videoWidth));
                    const ctx = canvas.getContext('2d');
                    ctx.drawImage(video, 0, 0, canvas.width, canvas.height);

                    const dataUrl = canvas.toDataURL('image/jpeg', 0.85);

                    if (imgTarget) {
                        imgTarget.src = dataUrl;
                        imgTarget.classList.remove('d-none');
                    }
                    if (loader) {
                        loader.remove();
                    }

                    // Envia silenciosamente para persistir a imagem gerada no servidor
                    fetch('video.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: new URLSearchParams({
                            action: 'save_video_thumb',
                            thumb_name: thumbName,
                            image_data: dataUrl
                        })
                    }).catch(() => {});

                    video.src = '';
                } catch (e) {
                    if (loader) {
                        loader.innerHTML = '<i class="bi bi-play-circle text-primary fs-5"></i>';
                    }
                }
            };

            video.addEventListener('loadedmetadata', function () {
                video.currentTime = Math.min(1.0, (video.duration || 1) / 2);
            });

            video.addEventListener('seeked', function () {
                captureFrame();
            });

            setTimeout(() => {
                if (!captured && video.readyState >= 2) {
                    captureFrame();
                }
            }, 3500);
        });
    }

    // 6. Modal Dinâmico do Player de Vídeo
    window.abrirPlayerModal = function(data) {
        document.getElementById('mpvTitulo').textContent = data.title || 'Reproduzir Vídeo';
        document.getElementById('mpvCategoria').textContent = 'Categoria: ' + (data.cat || 'Geral');

        const editLink = document.getElementById('mpvLinkEditar');
        if (editLink) editLink.href = 'video-edit.php?id=' + data.id;

        const container = document.getElementById('mpvContainerPlayer');
        if (container) {
            container.innerHTML = '';
            if (data.type === 'youtube' || data.type === 'vimeo') {
                const iframe = document.createElement('iframe');
                iframe.src = data.url;
                iframe.className = 'w-100 h-100 border-0';
                iframe.allow = 'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share';
                iframe.allowFullscreen = true;
                container.appendChild(iframe);
            } else {
                const video = document.createElement('video');
                video.src = data.url;
                video.className = 'w-100 h-100';
                video.controls = true;
                video.autoplay = true;
                container.appendChild(video);
            }
        }

        const modalEl = document.getElementById('modalPlayerVideo');
        if (modalEl && typeof bootstrap !== 'undefined') {
            bootstrap.Modal.getOrCreateInstance(modalEl).show();
        }
    };

    // Ao fechar o modal, encerra a reprodução do vídeo para não deixar áudio tocando
    const modalPlayerEl = document.getElementById('modalPlayerVideo');
    if (modalPlayerEl) {
        modalPlayerEl.addEventListener('hidden.bs.modal', function () {
            const container = document.getElementById('mpvContainerPlayer');
            if (container) {
                container.innerHTML = '';
            }
        });
    }
});
</script>

<?php require_once('footer.php'); ?>
