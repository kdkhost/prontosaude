<?php
require_once('header.php');

// 1. Endpoint AJAX: Exclusão Segura de Notícia
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_news') {
    header('Content-Type: application/json; charset=utf-8');
    $id = (int)($_POST['id'] ?? 0);

    if ($id > 0) {
        try {
            // Busca dados da notícia para remoção de mídia física
            $stmt_find = $pdo->prepare("SELECT photo FROM tbl_news WHERE news_id = ?");
            $stmt_find->execute([$id]);
            $row = $stmt_find->fetch(PDO::FETCH_ASSOC);

            if ($row) {
                $photo = trim($row['photo'] ?? '');
                if (!empty($photo)) {
                    $caminhos = [
                        __DIR__ . '/../assets/uploads/' . $photo,
                        __DIR__ . '/../assets/uploads/news/' . $photo
                    ];
                    foreach ($caminhos as $path) {
                        if (file_exists($path) && is_file($path)) {
                            @unlink($path);
                        }
                    }
                }
            }

            // Exclui a notícia do banco
            $stmt_del = $pdo->prepare("DELETE FROM tbl_news WHERE news_id = ?");
            $stmt_del->execute([$id]);

            // Limpa comentários órfãos se a tabela existir
            try {
                $pdo->prepare("DELETE FROM tbl_news_comment WHERE news_id = ?")->execute([$id]);
            } catch (Exception $e) {}

            echo json_encode([
                'status'  => 'success',
                'message' => 'Notícia removida com sucesso do portal!'
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'status'  => 'error',
                'message' => 'Erro ao excluir notícia: ' . $e->getMessage()
            ]);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Identificador de notícia inválido.']);
    }
    exit;
}

// 2. Consulta de Notícias com Categorias
$statement = $pdo->prepare("SELECT 
                                t1.news_id,
                                t1.news_title,
                                t1.news_slug,
                                t1.news_content,
                                t1.news_date,
                                t1.photo,
                                t1.category_id,
                                t1.publisher,
                                t1.total_view,
                                t1.status,
                                t2.category_name,
                                t2.category_slug
                            FROM tbl_news t1
                            LEFT JOIN tbl_category t2 ON t1.category_id = t2.category_id
                            ORDER BY t1.news_id DESC");
$statement->execute();
$news_list = $statement->fetchAll(PDO::FETCH_ASSOC);

// 3. Métricas de KPIs
$total_noticias      = count($news_list);
$total_publicadas    = 0;
$total_rascunhos     = 0;
$total_visualizacoes = 0;

foreach ($news_list as $n) {
    if (($n['status'] ?? 'Active') === 'Active') {
        $total_publicadas++;
    } else {
        $total_rascunhos++;
    }
    $total_visualizacoes += (int)($n['total_view'] ?? 0);
}

/**
 * Função Auxiliar para Resolver Foto da Notícia
 */
function pronto_resolver_capa_noticia($photo) {
    $arq = trim((string)$photo);
    if (empty($arq)) {
        return [
            'url'    => BASE_URL . 'assets/images/no-image-found.png',
            'exists' => false,
            'ext'    => 'IMG'
        ];
    }

    $ext = strtoupper(pathinfo($arq, PATHINFO_EXTENSION)) ?: 'IMG';

    // 1. Verifica diretamente na raiz de assets/uploads/
    if (file_exists(__DIR__ . '/../assets/uploads/' . $arq) && is_file(__DIR__ . '/../assets/uploads/' . $arq)) {
        return [
            'url'    => '../assets/uploads/' . $arq,
            'exists' => true,
            'ext'    => $ext
        ];
    }

    // 2. Verifica se está em subpasta de uploads (se existir)
    if (file_exists(__DIR__ . '/../assets/uploads/news/' . $arq) && is_file(__DIR__ . '/../assets/uploads/news/' . $arq)) {
        return [
            'url'    => '../assets/uploads/news/' . $arq,
            'exists' => true,
            'ext'    => $ext
        ];
    }

    // Fallback: Se o arquivo não existir fisicamente no disco, exibe placeholder limpo sem quebrar o layout
    return [
        'url'    => '../assets/images/no-image-found.png',
        'exists' => false,
        'ext'    => $ext
    ];
}
?>

<style>
/* Miniatura com Proporção de Notícia (16:9 / 4:3) */
.news-thumb-card {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.news-thumb-card:hover {
    transform: scale(1.03);
}
.news-thumb-card .news-overlay {
    background-color: rgba(0, 0, 0, 0.45);
    opacity: 0;
    transition: opacity 0.2s ease;
}
.news-thumb-card:hover .news-overlay {
    opacity: 1;
}

/* Modal de Visualização da Notícia */
.modal-news-body img {
    max-width: 100%;
    height: auto;
    border-radius: 0.5rem;
}
</style>

<!-- Cabeçalho da Página -->
<div class="app-content-header pb-2 mb-3 border-bottom">
    <div class="container-fluid">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div>
                <h3 class="mb-1 fw-bold text-body">
                    <i class="bi bi-newspaper text-primary me-2"></i>Gestão de Notícias &amp; Blog
                </h3>
                <ol class="breadcrumb mb-0 small">
                    <li class="breadcrumb-item"><a href="index.php" class="text-decoration-none"><i class="bi bi-house-door"></i> Início</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Notícias</li>
                </ol>
            </div>
            <div class="d-flex flex-wrap align-items-center gap-2">
                <a href="../news.php" target="_blank" rel="noopener" class="btn btn-outline-info btn-sm rounded-pill px-3 shadow-sm" data-bs-toggle="tooltip" title="Ver portal de notícias e blog público">
                    <i class="bi bi-box-arrow-up-right me-1"></i> Ver no Site
                </a>
                <a href="category.php" class="btn btn-outline-secondary btn-sm rounded-pill px-3 shadow-sm" data-bs-toggle="tooltip" title="Gerenciar categorias de notícias">
                    <i class="bi bi-tags me-1"></i> Categorias
                </a>
                <a href="news-add.php" class="btn btn-primary btn-sm rounded-pill px-3 shadow-sm" data-bs-toggle="tooltip" title="Publicar uma nova matéria ou artigo">
                    <i class="bi bi-plus-lg me-1"></i> Adicionar Notícia
                </a>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">

        <!-- Mini Cards de Indicadores (KPIs) com .kpi-icon-circle perfeitamente redondo -->
        <div class="row g-3 mb-4">
            <!-- Total de Notícias -->
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card border-0 shadow-sm rounded-3 h-100 kpi-card">
                    <div class="card-body d-flex align-items-center justify-content-between p-3">
                        <div>
                            <span class="text-uppercase fw-semibold text-body-secondary small d-block mb-1">Total de Artigos</span>
                            <h3 class="mb-0 fw-bold text-body" id="kpiTotal"><?= $total_noticias; ?></h3>
                            <span class="text-body-secondary small">Notícias registradas</span>
                        </div>
                        <div class="kpi-icon-circle bg-primary-subtle text-primary">
                            <i class="bi bi-newspaper fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Publicadas no Site -->
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card border-0 shadow-sm rounded-3 h-100 kpi-card">
                    <div class="card-body d-flex align-items-center justify-content-between p-3">
                        <div>
                            <span class="text-uppercase fw-semibold text-body-secondary small d-block mb-1">Publicadas no Site</span>
                            <h3 class="mb-0 fw-bold text-success" id="kpiAtivos"><?= $total_publicadas; ?></h3>
                            <span class="text-body-secondary small">Visíveis ao público</span>
                        </div>
                        <div class="kpi-icon-circle bg-success-subtle text-success">
                            <i class="bi bi-check-circle-fill fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Rascunhos / Ocultas -->
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card border-0 shadow-sm rounded-3 h-100 kpi-card">
                    <div class="card-body d-flex align-items-center justify-content-between p-3">
                        <div>
                            <span class="text-uppercase fw-semibold text-body-secondary small d-block mb-1">Rascunhos / Ocultas</span>
                            <h3 class="mb-0 fw-bold text-warning" id="kpiInativos"><?= $total_rascunhos; ?></h3>
                            <span class="text-body-secondary small">Aguardando revisão</span>
                        </div>
                        <div class="kpi-icon-circle bg-warning-subtle text-warning">
                            <i class="bi bi-pause-circle-fill fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Total de Visualizações -->
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card border-0 shadow-sm rounded-3 h-100 kpi-card">
                    <div class="card-body d-flex align-items-center justify-content-between p-3">
                        <div>
                            <span class="text-uppercase fw-semibold text-body-secondary small d-block mb-1">Leituras Totais</span>
                            <h3 class="mb-0 fw-bold text-info" id="kpiViews"><?= number_format($total_visualizacoes, 0, ',', '.'); ?></h3>
                            <span class="text-body-secondary small">Alcance de visualizações</span>
                        </div>
                        <div class="kpi-icon-circle bg-info-subtle text-info">
                            <i class="bi bi-eye-fill fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabela Principal de Notícias -->
        <div class="row">
            <div class="col-12">
                <div class="card card-outline card-primary shadow-sm mb-4 border-0">
                    
                    <div class="card-header bg-transparent py-3 d-flex flex-wrap align-items-center justify-content-between gap-2 border-bottom">
                        <div class="d-flex align-items-center gap-2">
                            <h5 class="card-title fw-bold mb-0 text-body">
                                <i class="bi bi-journal-text text-primary me-2"></i>Acervo de Artigos &amp; Matérias
                            </h5>
                        </div>

                        <!-- Filtros Rápidos por Status (Pílulas) -->
                        <div class="btn-group btn-group-sm" role="group" aria-label="Filtrar por Status">
                            <button type="button" class="btn btn-outline-secondary active filter-btn" data-filter="all">
                                Todas <span class="badge bg-secondary-subtle text-body ms-1" id="filterCountAll"><?= $total_noticias; ?></span>
                            </button>
                            <button type="button" class="btn btn-outline-success filter-btn" data-filter="active">
                                Publicadas <span class="badge bg-success-subtle text-success ms-1" id="filterCountActive"><?= $total_publicadas; ?></span>
                            </button>
                            <button type="button" class="btn btn-outline-warning filter-btn" data-filter="inactive">
                                Rascunhos <span class="badge bg-warning-subtle text-warning ms-1" id="filterCountInactive"><?= $total_rascunhos; ?></span>
                            </button>
                        </div>
                    </div>

                    <div class="card-body table-responsive">
                        <table id="tabelaNoticias" class="table table-hover align-middle w-100">
                            <thead>
                                <tr>
                                    <th width="45" class="text-center">#</th>
                                    <th width="130" class="text-center">Capa / Foto</th>
                                    <th>Título &amp; Identificação</th>
                                    <th width="150">Categoria</th>
                                    <th width="130" class="text-center">Data</th>
                                    <th width="100" class="text-center">Leituras</th>
                                    <th width="150" class="text-center">Status</th>
                                    <th width="140" class="text-center">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $seq = 0;
                                foreach ($news_list as $row):
                                    $seq++;
                                    $n_id        = (int)$row['news_id'];
                                    $n_title     = htmlspecialchars($row['news_title'] ?? 'Sem título');
                                    $n_slug      = htmlspecialchars($row['news_slug'] ?? '');
                                    $n_cat       = htmlspecialchars($row['category_name'] ?? 'Geral');
                                    $n_date      = $row['news_date'] ?? '';
                                    $n_author    = htmlspecialchars($row['publisher'] ?? 'Equipe Pronto Saúde');
                                    $n_views     = (int)($row['total_view'] ?? 0);
                                    $n_status    = $row['status'] ?? 'Active';
                                    $is_active   = ($n_status === 'Active');
                                    $info_foto   = pronto_resolver_capa_noticia($row['photo']);

                                    // Resumo de texto puro sem tags HTML
                                    $resumo_puro = strip_tags($row['news_content'] ?? '');
                                    $resumo_puro = preg_replace('/\s+/', ' ', $resumo_puro);
                                    $resumo_exibicao = (mb_strlen($resumo_puro, 'UTF-8') > 90) ? mb_substr($resumo_puro, 0, 90, 'UTF-8') . '...' : $resumo_puro;

                                    // Data Formatada
                                    $data_formatada = function_exists('pronto_format_date') ? pronto_format_date($n_date) : $n_date;
                                ?>
                                <tr id="tr-news-<?= $n_id; ?>" data-status="<?= $is_active ? 'active' : 'inactive'; ?>">
                                    <td class="text-center text-body-secondary fw-semibold"><?= $seq; ?></td>

                                    <!-- Capa / Foto Miniatura -->
                                    <td class="text-center">
                                        <div class="position-relative overflow-hidden rounded-3 border border-secondary-subtle shadow-xs mx-auto bg-body-secondary d-flex align-items-center justify-content-center news-thumb-card btn-view-news"
                                             style="width: 110px; aspect-ratio: 16/10; cursor: pointer;"
                                             data-id="<?= $n_id; ?>"
                                             data-title="<?= htmlspecialchars($n_title, ENT_QUOTES, 'UTF-8'); ?>"
                                             data-slug="<?= htmlspecialchars($n_slug, ENT_QUOTES, 'UTF-8'); ?>"
                                             data-cat="<?= htmlspecialchars($n_cat, ENT_QUOTES, 'UTF-8'); ?>"
                                             data-date="<?= htmlspecialchars($data_formatada, ENT_QUOTES, 'UTF-8'); ?>"
                                             data-author="<?= htmlspecialchars($n_author, ENT_QUOTES, 'UTF-8'); ?>"
                                             data-views="<?= $n_views; ?>"
                                             data-photo="<?= htmlspecialchars($info_foto['url'], ENT_QUOTES, 'UTF-8'); ?>"
                                             title="Visualizar detalhes da notícia">
                                            
                                            <img src="<?= $info_foto['url']; ?>" alt="<?= $n_title; ?>" class="w-100 h-100 object-fit-cover" loading="lazy" onerror="this.onerror=null;this.src='../assets/images/no-image-found.png';">

                                            <!-- Overlay de Zoom -->
                                            <div class="position-absolute w-100 h-100 top-0 start-0 d-flex align-items-center justify-content-center news-overlay">
                                                <i class="bi bi-arrows-fullscreen text-white fs-5"></i>
                                            </div>

                                            <!-- Badge de Extensão -->
                                            <span class="position-absolute bottom-0 end-0 badge bg-dark bg-opacity-75 text-uppercase m-1 border border-secondary border-opacity-50" style="font-size: 0.6rem;">
                                                <?= $info_foto['ext']; ?>
                                            </span>
                                        </div>
                                    </td>

                                    <!-- Título, Slug e Resumo -->
                                    <td>
                                        <div class="fw-bold text-body fs-6 mb-1">
                                            <a href="javascript:void(0)" class="text-decoration-none text-body hover-primary btn-view-news"
                                               data-id="<?= $n_id; ?>"
                                               data-title="<?= htmlspecialchars($n_title, ENT_QUOTES, 'UTF-8'); ?>"
                                               data-slug="<?= htmlspecialchars($n_slug, ENT_QUOTES, 'UTF-8'); ?>"
                                               data-cat="<?= htmlspecialchars($n_cat, ENT_QUOTES, 'UTF-8'); ?>"
                                               data-date="<?= htmlspecialchars($data_formatada, ENT_QUOTES, 'UTF-8'); ?>"
                                               data-author="<?= htmlspecialchars($n_author, ENT_QUOTES, 'UTF-8'); ?>"
                                               data-views="<?= $n_views; ?>"
                                               data-photo="<?= htmlspecialchars($info_foto['url'], ENT_QUOTES, 'UTF-8'); ?>">
                                                <?= $n_title; ?>
                                            </a>
                                        </div>
                                        <div class="small text-body-secondary mb-1">
                                            <?= $resumo_exibicao ?: 'Sem descrição detalhada.'; ?>
                                        </div>
                                        <div class="d-flex flex-wrap align-items-center gap-2 small text-body-secondary">
                                            <span><i class="bi bi-person me-1"></i><?= $n_author; ?></span>
                                            <span>&bull;</span>
                                            <?php if (!empty($n_slug)): ?>
                                                <a href="../news.php?slug=<?= urlencode($n_slug); ?>" target="_blank" class="text-decoration-none text-primary" title="Abrir artigo no site">
                                                    <i class="bi bi-link-45deg me-1"></i>/news/<?= $n_slug; ?>
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </td>

                                    <!-- Categoria -->
                                    <td>
                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle small fw-semibold">
                                            <i class="bi bi-tag-fill me-1"></i><?= $n_cat; ?>
                                        </span>
                                    </td>

                                    <!-- Data -->
                                    <td class="text-center text-nowrap small text-body-secondary">
                                        <i class="bi bi-calendar3 me-1 text-primary"></i><?= $data_formatada; ?>
                                    </td>

                                    <!-- Leituras / Views -->
                                    <td class="text-center">
                                        <span class="badge bg-info-subtle text-info border border-info-subtle rounded-pill small fw-semibold">
                                            <i class="bi bi-eye me-1"></i><?= number_format($n_views, 0, ',', '.'); ?>
                                        </span>
                                    </td>

                                    <!-- Status com Switch AJAX Global -->
                                    <td class="text-center">
                                        <div class="d-inline-flex align-items-center gap-2">
                                            <div class="form-check form-switch mb-0" title="Publicar ou salvar como rascunho">
                                                <input class="form-check-input pronto-status-switch" type="checkbox" role="switch"
                                                       data-entity="news"
                                                       data-id="<?= $n_id; ?>"
                                                       data-name="<?= $n_title; ?>"
                                                       data-active-text="Publicada"
                                                       data-inactive-text="Rascunho"
                                                       id="switch-news-<?= $n_id; ?>"
                                                       <?= $is_active ? 'checked' : ''; ?>>
                                            </div>
                                            <span class="badge status-badge <?= $is_active ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-warning-subtle text-warning border border-warning-subtle'; ?> rounded-pill small" id="badge-status-<?= $n_id; ?>">
                                                <?= $is_active ? 'Publicada' : 'Rascunho'; ?>
                                            </span>
                                        </div>
                                    </td>

                                    <!-- Ações Padronizadas -->
                                    <td class="text-center text-nowrap">
                                        <div class="d-flex align-items-center justify-content-center gap-1">
                                            <!-- Ver Detalhes (Modal Rápido) -->
                                            <button type="button" class="btn btn-outline-info btn-sm btn-view-news"
                                                    data-id="<?= $n_id; ?>"
                                                    data-title="<?= htmlspecialchars($n_title, ENT_QUOTES, 'UTF-8'); ?>"
                                                    data-slug="<?= htmlspecialchars($n_slug, ENT_QUOTES, 'UTF-8'); ?>"
                                                    data-cat="<?= htmlspecialchars($n_cat, ENT_QUOTES, 'UTF-8'); ?>"
                                                    data-date="<?= htmlspecialchars($data_formatada, ENT_QUOTES, 'UTF-8'); ?>"
                                                    data-author="<?= htmlspecialchars($n_author, ENT_QUOTES, 'UTF-8'); ?>"
                                                    data-views="<?= $n_views; ?>"
                                                    data-photo="<?= htmlspecialchars($info_foto['url'], ENT_QUOTES, 'UTF-8'); ?>"
                                                    data-bs-toggle="tooltip"
                                                    title="Visualizar notícia">
                                                <i class="bi bi-eye"></i>
                                            </button>

                                            <!-- Editar -->
                                            <a href="news-edit.php?id=<?= $n_id; ?>" class="btn btn-outline-primary btn-sm" data-bs-toggle="tooltip" title="Editar esta notícia">
                                                <i class="bi bi-pencil-square"></i>
                                            </a>

                                            <!-- Excluir Seguro com SweetAlert2 -->
                                            <button type="button" class="btn btn-outline-danger btn-sm btn-delete-news"
                                                    data-id="<?= $n_id; ?>"
                                                    data-title="<?= htmlspecialchars($n_title, ENT_QUOTES, 'UTF-8'); ?>"
                                                    data-thumb="<?= htmlspecialchars($info_foto['url'], ENT_QUOTES, 'UTF-8'); ?>"
                                                    data-bs-toggle="tooltip"
                                                    title="Excluir notícia">
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
<!-- MODAL: DETALHES E PREVIEW DA NOTÍCIA       -->
<!-- ========================================== -->
<div class="modal fade" id="modalVisualizarNoticia" tabindex="-1" aria-labelledby="modalVisualizarNoticiaLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content bg-body text-body border-0 shadow-lg rounded-3">
            <div class="modal-header bg-body-tertiary py-3 border-bottom d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-primary rounded-pill p-2"><i class="bi bi-newspaper"></i></span>
                    <div>
                        <h5 class="modal-title fw-bold text-body mb-0" id="mvnTitulo">Visualizar Artigo</h5>
                        <small class="text-body-secondary" id="mvnCategoria">Notícias Pronto Saúde</small>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>

            <div class="modal-body p-4 modal-news-body">
                <!-- Imagem de Capa Panorâmica -->
                <div class="position-relative overflow-hidden rounded-3 mb-3 bg-body-secondary text-center" style="max-height: 320px;">
                    <img src="" id="mvnImagem" alt="Capa da Notícia" class="img-fluid w-100 object-fit-cover rounded-3" style="max-height: 320px;">
                </div>

                <!-- Metadados da Notícia -->
                <div class="d-flex flex-wrap align-items-center gap-3 p-2 mb-3 bg-body-tertiary rounded-3 small">
                    <div><i class="bi bi-calendar3 text-primary me-1"></i><span id="mvnData">-</span></div>
                    <div><i class="bi bi-person text-primary me-1"></i><span id="mvnAutor">-</span></div>
                    <div><i class="bi bi-tag text-primary me-1"></i><span id="mvnCatBadge">-</span></div>
                    <div><i class="bi bi-eye text-primary me-1"></i><span id="mvnViews">-</span> visualizações</div>
                </div>

                <!-- Título em Destaque -->
                <h4 class="fw-bold text-body mb-3" id="mvnTituloDestaque"></h4>

                <div class="alert alert-info py-2 px-3 small d-flex align-items-center justify-content-between mb-0">
                    <div>
                        <i class="bi bi-globe me-1"></i><strong>URL no Portal:</strong> <span id="mvnLinkTexto" class="font-monospace"></span>
                    </div>
                    <a href="#" id="mvnLinkSite" target="_blank" class="btn btn-info btn-sm text-white">
                        <i class="bi bi-box-arrow-up-right me-1"></i> Abrir Artigo
                    </a>
                </div>
            </div>

            <div class="modal-footer bg-body-tertiary border-top py-2 d-flex justify-content-between">
                <span class="small text-body-secondary" id="mvnIdLabel">Notícia ID #</span>
                <div class="d-flex gap-2">
                    <a href="#" id="mvnLinkEditar" class="btn btn-primary btn-sm px-3">
                        <i class="bi bi-pencil-square me-1"></i> Editar Artigo
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
// 1. Variável Global da Tabela
var tabelaNoticiasDT = null;

// 2. Abertura do Modal de Visualização da Notícia
window.abrirModalNoticia = function(data) {
    document.querySelectorAll('.tooltip').forEach(t => t.remove());

    const tituloEl = document.getElementById('mvnTitulo');
    const tituloDestEl = document.getElementById('mvnTituloDestaque');
    if (tituloEl) tituloEl.textContent = data.title || 'Visualizar Notícia';
    if (tituloDestEl) tituloDestEl.textContent = data.title || '';

    const catEl = document.getElementById('mvnCategoria');
    const catBadge = document.getElementById('mvnCatBadge');
    if (catEl) catEl.textContent = 'Categoria: ' + (data.cat || 'Geral');
    if (catBadge) catBadge.textContent = data.cat || 'Geral';

    const imgEl = document.getElementById('mvnImagem');
    if (imgEl) imgEl.src = data.photo || '../assets/images/no-image-found.png';

    const dataEl = document.getElementById('mvnData');
    if (dataEl) dataEl.textContent = data.date || '-';

    const autorEl = document.getElementById('mvnAutor');
    if (autorEl) autorEl.textContent = data.author || 'Equipe';

    const viewsEl = document.getElementById('mvnViews');
    if (viewsEl) viewsEl.textContent = Number(data.views || 0).toLocaleString('pt-BR');

    const idLabel = document.getElementById('mvnIdLabel');
    if (idLabel) idLabel.textContent = 'Notícia #' + (data.id || '');

    const linkSite = document.getElementById('mvnLinkSite');
    const linkTexto = document.getElementById('mvnLinkTexto');
    if (data.slug) {
        const fullUrl = '../news.php?slug=' + encodeURIComponent(data.slug);
        if (linkSite) {
            linkSite.href = fullUrl;
            linkSite.classList.remove('d-none');
        }
        if (linkTexto) linkTexto.textContent = '/news/' + data.slug;
    } else {
        if (linkSite) linkSite.classList.add('d-none');
        if (linkTexto) linkTexto.textContent = 'Sem slug configurado';
    }

    const linkEdit = document.getElementById('mvnLinkEditar');
    if (linkEdit) linkEdit.href = 'news-edit.php?id=' + (data.id || '');

    const modalEl = document.getElementById('modalVisualizarNoticia');
    if (modalEl && typeof bootstrap !== 'undefined') {
        const modalInst = bootstrap.Modal.getOrCreateInstance(modalEl);
        modalInst.show();
    }
};

// 3. Exclusão Segura com SweetAlert2
window.confirmarExclusaoNoticia = function(id, title, thumb) {
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
            html: `${imgHtml}Deseja realmente remover o artigo <strong>"${title}"</strong> do portal?<br><small class="text-danger">A foto vinculada e registros associados serão apagados permanentemente.</small>`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="bi bi-trash3 me-1"></i> Sim, excluir notícia',
            cancelButtonText: 'Cancelar',
            customClass: {
                confirmButton: 'btn btn-danger px-3 me-2',
                cancelButton: 'btn btn-secondary px-3'
            },
            buttonsStyling: false
        }).then((result) => {
            if (result.isConfirmed) {
                window.executarExclusaoNoticia(id);
            }
        });
    } else {
        window.executarExclusaoNoticia(id);
    }
};

window.executarExclusaoNoticia = function(id) {
    fetch('news.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: new URLSearchParams({
            action: 'delete_news',
            id: id
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'success',
                    title: 'Notícia Excluída!',
                    text: data.message,
                    timer: 1600,
                    showConfirmButton: false
                }).then(() => {
                    const tr = document.getElementById('tr-news-' + id);
                    if (tr) {
                        if (tabelaNoticiasDT) {
                            tabelaNoticiasDT.row($(tr)).remove().draw();
                        } else {
                            tr.remove();
                        }
                        window.atualizarKPIsNoticias();
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
                    text: data.message || 'Não foi possível excluir a notícia.'
                });
            }
        }
    })
    .catch(err => {
        console.error(err);
        window.location.href = 'news-delete.php?id=' + id;
    });
};

// 4. Atualização Dinâmica dos KPIs
window.atualizarKPIsNoticias = function() {
    const rows = document.querySelectorAll('#tabelaNoticias tbody tr[data-status]');
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
    const filterAll = document.getElementById('filterCountAll');
    const filterActive = document.getElementById('filterCountActive');
    const filterInactive = document.getElementById('filterCountInactive');

    if (kpiTotal) kpiTotal.textContent = total;
    if (kpiAtivos) kpiAtivos.textContent = ativos;
    if (kpiInativos) kpiInativos.textContent = inativos;
    if (filterAll) filterAll.textContent = total;
    if (filterActive) filterActive.textContent = ativos;
    if (filterInactive) filterInactive.textContent = inativos;
};

// 5. Delegação Global de Cliques
document.addEventListener('click', function (e) {
    // A. Visualizar Notícia no Modal
    const btnView = e.target.closest('.btn-view-news');
    if (btnView) {
        e.preventDefault();
        const id     = btnView.getAttribute('data-id');
        const title  = btnView.getAttribute('data-title') || 'Notícia';
        const slug   = btnView.getAttribute('data-slug') || '';
        const cat    = btnView.getAttribute('data-cat') || 'Geral';
        const date   = btnView.getAttribute('data-date') || '';
        const author = btnView.getAttribute('data-author') || 'Equipe';
        const views  = btnView.getAttribute('data-views') || '0';
        const photo  = btnView.getAttribute('data-photo') || '';
        window.abrirModalNoticia({ id, title, slug, cat, date, author, views, photo });
        return;
    }

    // B. Excluir Notícia
    const btnDel = e.target.closest('.btn-delete-news');
    if (btnDel) {
        e.preventDefault();
        const id    = btnDel.getAttribute('data-id');
        const title = btnDel.getAttribute('data-title') || 'esta notícia';
        const thumb = btnDel.getAttribute('data-thumb') || '';
        window.confirmarExclusaoNoticia(id, title, thumb);
        return;
    }

    // C. Filtros de Status (Todas, Publicadas, Rascunhos)
    const filterBtn = e.target.closest('.filter-btn');
    if (filterBtn) {
        e.preventDefault();
        document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
        filterBtn.classList.add('active');
        const filter = filterBtn.getAttribute('data-filter');

        if (tabelaNoticiasDT) {
            if (filter === 'all') {
                tabelaNoticiasDT.column(6).search('').draw();
            } else if (filter === 'active') {
                tabelaNoticiasDT.column(6).search('Publicada').draw();
            } else if (filter === 'inactive') {
                tabelaNoticiasDT.column(6).search('Rascunho').draw();
            }
        }
        return;
    }
});

// 6. Atualização em Mudança de Switch
document.addEventListener('change', function (e) {
    if (e.target && e.target.classList.contains('pronto-status-switch')) {
        const tr = e.target.closest('tr');
        if (tr) {
            tr.setAttribute('data-status', e.target.checked ? 'active' : 'inactive');
            window.atualizarKPIsNoticias();
        }
    }
});

// 7. Inicialização do DataTables
function initTabelaNoticias() {
    if (typeof window.jQuery !== 'undefined' && typeof window.jQuery.fn.DataTable !== 'undefined') {
        const $table = $('#tabelaNoticias');
        if ($table.length && !$.fn.DataTable.isDataTable('#tabelaNoticias')) {
            tabelaNoticiasDT = $table.DataTable({
                responsive: true,
                language: {
                    search: "Buscar Notícia:",
                    lengthMenu: "Mostrar _MENU_ notícias",
                    info: "Exibindo _START_ a _END_ de _TOTAL_ artigos",
                    infoEmpty: "Nenhuma notícia cadastrada",
                    infoFiltered: "(filtrado de _MAX_ no total)",
                    zeroRecords: "Nenhuma notícia encontrada",
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
    document.addEventListener('DOMContentLoaded', initTabelaNoticias);
} else {
    initTabelaNoticias();
}
</script>

<?php require_once('footer.php'); ?>
