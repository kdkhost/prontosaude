<?php
require_once('header.php');

// 1. Validação de ID da Notícia
$news_id = (int)($_REQUEST['id'] ?? 0);
if ($news_id <= 0) {
    header('Location: news.php');
    exit;
}

// 2. Busca dados da notícia atual
$stmt_find = $pdo->prepare("SELECT * FROM tbl_news WHERE news_id = ?");
$stmt_find->execute([$news_id]);
$current_news = $stmt_find->fetch(PDO::FETCH_ASSOC);

if (!$current_news) {
    header('Location: news.php');
    exit;
}

// 3. Carrega todas as categorias disponíveis
$stmt_cats = $pdo->prepare("SELECT category_id, category_name, category_slug FROM tbl_category ORDER BY category_name ASC");
$stmt_cats->execute();
$categorias = $stmt_cats->fetchAll(PDO::FETCH_ASSOC);

// Métricas para os KPIs do topo
$total_noticias    = (int)$pdo->query("SELECT COUNT(*) FROM tbl_news")->fetchColumn();
$total_ativas      = (int)$pdo->query("SELECT COUNT(*) FROM tbl_news WHERE status = 'Active'")->fetchColumn();
$noticia_views     = (int)($current_news['total_view'] ?? 0);
$noticia_status    = $current_news['status'] ?? 'Active';

// Nome da categoria atual
$cat_atual_nome = 'Geral';
foreach ($categorias as $c) {
    if ((int)$c['category_id'] === (int)$current_news['category_id']) {
        $cat_atual_nome = $c['category_name'];
        break;
    }
}

// 4. Processamento da Edição (Suporta AJAX e POST tradicional)
$is_ajax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
           || (isset($_POST['action']) && $_POST['action'] === 'edit_news_ajax');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && (isset($_POST['form1']) || $is_ajax)) {
    $news_title       = trim($_POST['news_title'] ?? '');
    $news_slug_input  = trim($_POST['news_slug'] ?? '');
    $news_content     = trim($_POST['news_content'] ?? '');
    $news_date        = trim($_POST['news_date'] ?? date('d/m/Y'));
    $category_id      = (int)($_POST['category_id'] ?? 0);
    $publisher        = trim($_POST['publisher'] ?? '');
    $status           = trim($_POST['status'] ?? 'Active');
    $previous_photo   = trim($_POST['previous_photo'] ?? $current_news['photo']);

    $meta_title       = trim(strip_tags($_POST['meta_title'] ?? ''));
    $meta_keyword     = trim(strip_tags($_POST['meta_keyword'] ?? ''));
    $meta_description = trim(strip_tags($_POST['meta_description'] ?? ''));
    $meta_description = preg_replace('/[\r\n\t]+/', ' ', $meta_description);
    $meta_description = preg_replace('/\s+/', ' ', $meta_description);

    $valid = 1;
    $erros = [];

    // Validação de Título
    if (empty($news_title)) {
        $valid = 0;
        $erros[] = 'Informe o título da notícia.';
    } else {
        // Verifica duplicidade de título com outras notícias
        $stmt_check = $pdo->prepare("SELECT news_id FROM tbl_news WHERE news_title = ? AND news_id != ?");
        $stmt_check->execute([$news_title, $news_id]);
        if ($stmt_check->rowCount() > 0) {
            $valid = 0;
            $erros[] = 'Já existe outra notícia cadastrada com este mesmo título. Escolha um título diferente.';
        }
    }

    // Validação de Conteúdo
    if (empty($news_content)) {
        $valid = 0;
        $erros[] = 'O corpo/conteúdo da notícia não pode ficar vazio.';
    }

    // Validação de Data
    if (empty($news_date)) {
        $valid = 0;
        $erros[] = 'Informe a data de publicação da notícia.';
    }

    // Validação de Categoria
    if ($category_id <= 0) {
        $valid = 0;
        $erros[] = 'Selecione uma categoria para a notícia.';
    }

    if (!in_array($status, ['Active', 'Inactive'])) {
        $status = 'Active';
    }

    if (empty($publisher)) {
        $publisher = $_SESSION['user']['full_name'] ?? 'Equipe Pronto Saúde';
    }

    // Validação de Nova Foto (se enviada)
    $nova_foto_enviada = isset($_FILES['photo']) && !empty($_FILES['photo']['name']);
    $final_photo_name = $previous_photo;

    if ($nova_foto_enviada) {
        $file_name = $_FILES['photo']['name'];
        $file_tmp  = $_FILES['photo']['tmp_name'];
        $file_size = $_FILES['photo']['size'];
        $file_err  = $_FILES['photo']['error'];

        if ($file_err !== UPLOAD_ERR_OK) {
            $valid = 0;
            $erros[] = 'Erro durante o upload da nova imagem de destaque (Código: ' . $file_err . ').';
        } else {
            $ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            $ext_permitidas = ['jpg', 'jpeg', 'png', 'webp', 'gif'];

            if (!in_array($ext, $ext_permitidas)) {
                $valid = 0;
                $erros[] = 'Formato de imagem inválido. Formatos aceitos: JPG, JPEG, PNG, WEBP e GIF.';
            }

            if ($file_size > 15 * 1024 * 1024) {
                $valid = 0;
                $erros[] = 'A imagem selecionada excede o limite máximo permitido de 15MB.';
            }
        }
    }

    if ($valid === 1) {
        // Geração / Validação do Slug
        $base_slug = !empty($news_slug_input) ? $news_slug_input : $news_title;
        $temp_slug = strtolower(trim($base_slug));
        $temp_slug = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $temp_slug);
        $final_slug = preg_replace('/[^a-z0-9\-]+/', '-', $temp_slug);
        $final_slug = trim(preg_replace('/-+/', '-', $final_slug), '-');

        if (empty($final_slug)) {
            $final_slug = 'noticia-' . $news_id;
        }

        // Verifica duplicidade de slug com outras notícias
        $stmt_slug = $pdo->prepare("SELECT news_id FROM tbl_news WHERE news_slug = ? AND news_id != ?");
        $stmt_slug->execute([$final_slug, $news_id]);
        if ($stmt_slug->rowCount() > 0) {
            $final_slug .= '-' . time();
        }

        // Se enviou nova foto, processa o upload e remove a antiga
        if ($nova_foto_enviada && !empty($file_tmp)) {
            $ext = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
            $final_photo_name = 'news-' . $news_id . '-' . time() . '.' . $ext;
            $dir_uploads = __DIR__ . '/../assets/uploads/';

            if (!is_dir($dir_uploads)) {
                @mkdir($dir_uploads, 0777, true);
            }

            if (move_uploaded_file($file_tmp, $dir_uploads . $final_photo_name)) {
                // Remove a foto antiga física se existir
                if (!empty($previous_photo)) {
                    $caminhos_antigos = [
                        $dir_uploads . $previous_photo,
                        $dir_uploads . 'news/' . $previous_photo
                    ];
                    foreach ($caminhos_antigos as $p) {
                        if (file_exists($p) && is_file($p)) {
                            @unlink($p);
                        }
                    }
                }
            } else {
                $valid = 0;
                $erros[] = 'Não foi possível salvar o novo arquivo de imagem no servidor.';
            }
        }

        if ($valid === 1) {
            try {
                $stmt_upd = $pdo->prepare("UPDATE tbl_news SET 
                    news_title = ?,
                    news_slug = ?,
                    news_content = ?,
                    news_date = ?,
                    photo = ?,
                    category_id = ?,
                    publisher = ?,
                    status = ?,
                    meta_title = ?,
                    meta_keyword = ?,
                    meta_description = ?
                    WHERE news_id = ?");

                $stmt_upd->execute([
                    $news_title,
                    $final_slug,
                    $news_content,
                    $news_date,
                    $final_photo_name,
                    $category_id,
                    $publisher,
                    $status,
                    $meta_title,
                    $meta_keyword,
                    $meta_description,
                    $news_id
                ]);

                if ($is_ajax) {
                    header('Content-Type: application/json; charset=utf-8');
                    echo json_encode([
                        'status'   => 'success',
                        'message'  => 'Notícia atualizada com sucesso!',
                        'redirect' => 'news.php'
                    ]);
                    exit;
                } else {
                    $_SESSION['sucesso'] = 'Notícia atualizada com sucesso!';
                    header('Location: news.php');
                    exit;
                }
            } catch (Exception $e) {
                $erros[] = 'Erro ao atualizar no banco de dados: ' . $e->getMessage();
            }
        }
    }

    if ($is_ajax) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'status'  => 'error',
            'message' => implode('<br>', $erros)
        ]);
        exit;
    } else {
        $error_message = implode('<br>', $erros);
    }
}

// 5. Dados Finais para Renderização do Formulário
$f_title       = $current_news['news_title'];
$f_slug        = $current_news['news_slug'];
$f_content     = $current_news['news_content'];
$f_date        = $current_news['news_date'];
$f_photo       = $current_news['photo'];
$f_category_id = (int)$current_news['category_id'];
$f_publisher   = $current_news['publisher'] ?: ($_SESSION['user']['full_name'] ?? 'Equipe');
$f_status      = $current_news['status'] ?? 'Active';
$f_meta_title  = $current_news['meta_title'] ?? '';
$f_meta_kw     = $current_news['meta_keyword'] ?? '';
$f_meta_desc   = $current_news['meta_description'] ?? '';

// Resolve Foto Atual
$foto_atual_url = '';
$foto_atual_existe = false;
if (!empty($f_photo)) {
    if (file_exists(__DIR__ . '/../assets/uploads/' . $f_photo) && is_file(__DIR__ . '/../assets/uploads/' . $f_photo)) {
        $foto_atual_url = '../assets/uploads/' . $f_photo;
        $foto_atual_existe = true;
    } elseif (file_exists(__DIR__ . '/../assets/uploads/news/' . $f_photo) && is_file(__DIR__ . '/../assets/uploads/news/' . $f_photo)) {
        $foto_atual_url = '../assets/uploads/news/' . $f_photo;
        $foto_atual_existe = true;
    } else {
        $foto_atual_url = '../assets/uploads/' . $f_photo;
    }
} else {
    $foto_atual_url = BASE_URL . 'assets/images/no-image-found.png';
}
?>

<style>
/* Abas Modernas com Transição Suave */
.nav-pills-custom .nav-link {
    color: var(--bs-secondary-color);
    font-weight: 600;
    font-size: 0.95rem;
    padding: 0.75rem 1.25rem;
    border-radius: 0.5rem;
    border: 1px solid transparent;
    transition: all 0.2s ease-in-out;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
}
.nav-pills-custom .nav-link:hover {
    color: var(--bs-primary);
    background-color: var(--bs-tertiary-bg);
}
.nav-pills-custom .nav-link.active {
    color: #fff !important;
    background-color: var(--bs-primary) !important;
    box-shadow: 0 4px 12px rgba(10, 107, 116, 0.25);
}

/* Upload & Dropzone Moderno */
.upload-dropzone {
    border: 2px dashed var(--bs-border-color);
    border-radius: 0.75rem;
    padding: 1.5rem 1rem;
    text-align: center;
    background-color: var(--bs-tertiary-bg);
    cursor: pointer;
    transition: border-color 0.2s ease, background-color 0.2s ease;
    position: relative;
}
.upload-dropzone:hover, .upload-dropzone.dragover {
    border-color: var(--bs-primary);
    background-color: rgba(10, 107, 116, 0.05);
}

/* Google Snippet Preview */
.google-preview-box {
    background-color: #ffffff;
    border: 1px solid #dfe1e5;
    border-radius: 8px;
    padding: 16px;
    max-width: 650px;
    font-family: Arial, sans-serif;
}
[data-bs-theme="dark"] .google-preview-box {
    background-color: #202124;
    border-color: #3c4043;
}
.google-preview-url {
    font-size: 13px;
    color: #202124;
    margin-bottom: 4px;
    display: flex;
    align-items: center;
    gap: 6px;
}
[data-bs-theme="dark"] .google-preview-url {
    color: #bdc1c6;
}
.google-preview-title {
    font-size: 19px;
    line-height: 1.3;
    color: #1a0dab;
    text-decoration: none;
    cursor: pointer;
    margin-bottom: 4px;
    font-weight: 400;
}
[data-bs-theme="dark"] .google-preview-title {
    color: #8ab4f8;
}
.google-preview-desc {
    font-size: 14px;
    line-height: 1.5;
    color: #4d5156;
    word-break: break-word;
}
[data-bs-theme="dark"] .google-preview-desc {
    color: #bdc1c6;
}
</style>

<!-- Cabeçalho da Página -->
<div class="app-content-header pb-2 mb-3 border-bottom">
    <div class="container-fluid">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div>
                <h3 class="mb-1 fw-bold text-body">
                    <i class="bi bi-pencil-square text-primary me-2"></i>Editar Notícia
                </h3>
                <ol class="breadcrumb mb-0 small">
                    <li class="breadcrumb-item"><a href="index.php" class="text-decoration-none"><i class="bi bi-house-door"></i> Início</a></li>
                    <li class="breadcrumb-item"><a href="news.php" class="text-decoration-none">Notícias</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Editar Notícia #<?= $news_id; ?></li>
                </ol>
            </div>
            <div class="d-flex flex-wrap align-items-center gap-2">
                <?php if (!empty($f_slug)): ?>
                    <a href="../news.php?slug=<?= urlencode($f_slug); ?>" target="_blank" rel="noopener" class="btn btn-outline-info btn-sm rounded-pill px-3 shadow-sm" data-bs-toggle="tooltip" title="Visualizar esta matéria no site público">
                        <i class="bi bi-box-arrow-up-right me-1"></i> Ver no Site
                    </a>
                <?php endif; ?>
                <a href="news.php" class="btn btn-outline-secondary btn-sm rounded-pill px-3 shadow-sm" data-bs-toggle="tooltip" title="Voltar para a listagem geral de notícias">
                    <i class="bi bi-list-ul me-1"></i> Notícias
                </a>
                <a href="news-add.php" class="btn btn-primary btn-sm rounded-pill px-3 shadow-sm" data-bs-toggle="tooltip" title="Publicar uma nova matéria">
                    <i class="bi bi-plus-lg me-1"></i> Nova Notícia
                </a>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">

        <!-- Mini Cards de Indicadores (KPIs) com .kpi-icon-circle perfeitamente redondo -->
        <div class="row g-3 mb-4">
            <!-- Alcance Desta Notícia -->
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card border-0 shadow-sm rounded-3 h-100 kpi-card">
                    <div class="card-body d-flex align-items-center justify-content-between p-3">
                        <div>
                            <span class="text-uppercase fw-semibold text-body-secondary small d-block mb-1">Alcance da Notícia</span>
                            <h3 class="mb-0 fw-bold text-primary" id="kpiViews"><?= number_format($noticia_views, 0, ',', '.'); ?></h3>
                            <span class="text-body-secondary small">Leituras registradas</span>
                        </div>
                        <div class="kpi-icon-circle bg-primary-subtle text-primary">
                            <i class="bi bi-eye fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Status da Notícia -->
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card border-0 shadow-sm rounded-3 h-100 kpi-card">
                    <div class="card-body d-flex align-items-center justify-content-between p-3">
                        <div>
                            <span class="text-uppercase fw-semibold text-body-secondary small d-block mb-1">Visibilidade no Site</span>
                            <h3 class="mb-0 fw-bold <?= $f_status === 'Active' ? 'text-success' : 'text-warning'; ?>" id="kpiStatusText">
                                <?= $f_status === 'Active' ? 'Publicada' : 'Rascunho'; ?>
                            </h3>
                            <span class="text-body-secondary small" id="kpiStatusSub">
                                <?= $f_status === 'Active' ? 'Acessível aos visitantes' : 'Oculta do portal público'; ?>
                            </span>
                        </div>
                        <div class="kpi-icon-circle <?= $f_status === 'Active' ? 'bg-success-subtle text-success' : 'bg-warning-subtle text-warning'; ?>" id="kpiStatusIcon">
                            <i class="bi <?= $f_status === 'Active' ? 'bi-check-circle-fill' : 'bi-pause-circle-fill'; ?> fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Data de Publicação -->
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card border-0 shadow-sm rounded-3 h-100 kpi-card">
                    <div class="card-body d-flex align-items-center justify-content-between p-3">
                        <div>
                            <span class="text-uppercase fw-semibold text-body-secondary small d-block mb-1">Data de Publicação</span>
                            <h3 class="mb-0 fw-bold text-info" id="kpiData"><?= htmlspecialchars($f_date); ?></h3>
                            <span class="text-body-secondary small">Data oficial da matéria</span>
                        </div>
                        <div class="kpi-icon-circle bg-info-subtle text-info">
                            <i class="bi bi-calendar3 fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Categoria Vinculada -->
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card border-0 shadow-sm rounded-3 h-100 kpi-card">
                    <div class="card-body d-flex align-items-center justify-content-between p-3">
                        <div>
                            <span class="text-uppercase fw-semibold text-body-secondary small d-block mb-1">Categoria Vinculada</span>
                            <h3 class="mb-0 fw-bold text-body fs-5 text-truncate" style="max-width: 170px;" id="kpiCategoria"><?= htmlspecialchars($cat_atual_nome); ?></h3>
                            <span class="text-body-secondary small">Editoria temática</span>
                        </div>
                        <div class="kpi-icon-circle bg-secondary-subtle text-body">
                            <i class="bi bi-tag fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Formulário Principal -->
        <form action="" method="post" enctype="multipart/form-data" id="formEditNews">
            <input type="hidden" name="action" value="edit_news_ajax">
            <input type="hidden" name="id" value="<?= $news_id; ?>">
            <input type="hidden" name="previous_photo" value="<?= htmlspecialchars($f_photo); ?>">

            <div class="row g-4">
                
                <!-- Coluna Principal com Abas (8 colunas) -->
                <div class="col-12 col-lg-8">
                    <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
                        
                        <!-- Navegação por Abas -->
                        <div class="card-header bg-transparent border-bottom p-3">
                            <ul class="nav nav-pills nav-pills-custom gap-2" id="newsEditTabs" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active" id="tab-conteudo-btn" data-bs-toggle="pill" data-bs-target="#tab-conteudo" type="button" role="tab" aria-controls="tab-conteudo" aria-selected="true">
                                        <i class="bi bi-file-earmark-richtext"></i> Redação &amp; Conteúdo
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="tab-seo-btn" data-bs-toggle="pill" data-bs-target="#tab-seo" type="button" role="tab" aria-controls="tab-seo" aria-selected="false">
                                        <i class="bi bi-google"></i> Google &amp; SEO
                                    </button>
                                </li>
                            </ul>
                        </div>

                        <!-- Conteúdo das Abas -->
                        <div class="card-body p-4">
                            <div class="tab-content" id="newsEditTabsContent">

                                <!-- ABA 1: REDAÇÃO & CONTEÚDO -->
                                <div class="tab-pane fade show active" id="tab-conteudo" role="tabpanel" aria-labelledby="tab-conteudo-btn">
                                    <div class="row g-3">
                                        
                                        <!-- Título da Notícia -->
                                        <div class="col-12">
                                            <label class="form-label fw-bold text-body" for="news_title">
                                                Título da Notícia / Artigo <span class="text-danger">*</span>
                                            </label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-body-tertiary"><i class="bi bi-newspaper text-primary"></i></span>
                                                <input type="text" class="form-control form-control-lg fw-semibold" id="news_title" name="news_title" value="<?= htmlspecialchars($f_title); ?>" placeholder="Ex: Avanços da Cardiologia Preventiva no Pronto Saúde..." required autocomplete="off">
                                            </div>
                                            <div class="form-text small text-body-secondary mt-1">
                                                Título principal exibido em destaque no portal e nas páginas de notícias.
                                            </div>
                                        </div>

                                        <!-- Categoria e Editor em 2 Colunas -->
                                        <div class="col-12 col-md-6">
                                            <label class="form-label fw-semibold text-body" for="category_id">
                                                Categoria / Editoria <span class="text-danger">*</span>
                                            </label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-body-tertiary"><i class="bi bi-tags text-primary"></i></span>
                                                <select class="form-select" id="category_id" name="category_id" required>
                                                    <option value="">Selecione uma categoria...</option>
                                                    <?php foreach ($categorias as $cat): ?>
                                                        <option value="<?= $cat['category_id']; ?>" data-name="<?= htmlspecialchars($cat['category_name']); ?>" <?= ((int)$cat['category_id'] === $f_category_id) ? 'selected' : ''; ?>>
                                                            <?= htmlspecialchars($cat['category_name']); ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="col-12 col-md-6">
                                            <label class="form-label fw-semibold text-body" for="publisher">
                                                Autor / Redator da Notícia
                                            </label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-body-tertiary"><i class="bi bi-person-badge text-primary"></i></span>
                                                <input type="text" class="form-control" id="publisher" name="publisher" value="<?= htmlspecialchars($f_publisher); ?>" placeholder="Ex: Dra. Mariana Vasconcellos ou Equipe Pronto Saúde">
                                            </div>
                                        </div>

                                        <!-- Data de Publicação -->
                                        <div class="col-12 col-md-6">
                                            <label class="form-label fw-semibold text-body" for="news_date">
                                                Data de Publicação <span class="text-danger">*</span>
                                            </label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-body-tertiary"><i class="bi bi-calendar3 text-primary"></i></span>
                                                <input type="text" class="form-control mask-date" id="news_date" name="news_date" value="<?= htmlspecialchars($f_date); ?>" placeholder="DD/MM/AAAA" required>
                                            </div>
                                            <div class="form-text small text-body-secondary mt-1">Formato brasileiro: DD/MM/AAAA</div>
                                        </div>

                                        <!-- Corpo da Notícia (Summernote) -->
                                        <div class="col-12 mt-3">
                                            <label class="form-label fw-bold text-body" for="news_content">
                                                Conteúdo Completo do Artigo <span class="text-danger">*</span>
                                            </label>
                                            <textarea class="form-control" name="news_content" id="news_content" rows="12"><?= htmlspecialchars($f_content); ?></textarea>
                                        </div>

                                    </div>
                                </div>

                                <!-- ABA 2: GOOGLE & SEO -->
                                <div class="tab-pane fade" id="tab-seo" role="tabpanel" aria-labelledby="tab-seo-btn">
                                    <div class="pb-3 mb-3 border-bottom">
                                        <h6 class="fw-bold text-body mb-1">
                                            <i class="bi bi-google text-primary me-2"></i>Otimização para Mecanismos de Busca (Google SEO)
                                        </h6>
                                        <p class="text-body-secondary small mb-0">
                                            Personalize as meta tags e veja em tempo real como este artigo aparece nos resultados de busca do Google.
                                        </p>
                                    </div>

                                    <!-- Google Search Snippet Preview -->
                                    <div class="mb-4">
                                        <label class="form-label small fw-bold text-uppercase text-body-secondary mb-2">
                                            <i class="bi bi-eye text-primary me-1"></i>Pré-visualização do Resultado no Google
                                        </label>
                                        <div class="google-preview-box shadow-xs">
                                            <div class="google-preview-url">
                                                <span>https://prontosaude.com.br</span>
                                                <span>&rsaquo;</span>
                                                <span>news</span>
                                                <span>&rsaquo;</span>
                                                <span id="googlePreviewSlug"><?= htmlspecialchars($f_slug ?: 'noticia'); ?></span>
                                            </div>
                                            <div class="google-preview-title" id="googlePreviewTitle">
                                                <?= htmlspecialchars($f_meta_title ?: ($f_title . ' | Pronto Saúde')); ?>
                                            </div>
                                            <div class="google-preview-desc" id="googlePreviewDesc">
                                                <?= htmlspecialchars($f_meta_desc ?: 'Leia a notícia completa no portal do Pronto Saúde com informações atualizadas, orientações de especialistas e novidades médicas.'); ?>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Campos de Metadados -->
                                    <div class="row g-3">
                                        <div class="col-12">
                                            <div class="d-flex justify-content-between align-items-center mb-1">
                                                <label class="form-label fw-semibold text-body mb-0" for="meta_title">
                                                    Meta Título (Título SEO)
                                                </label>
                                                <small class="text-body-secondary" id="metaTitleCount"><?= mb_strlen($f_meta_title, 'UTF-8'); ?> / 60 caracteres</small>
                                            </div>
                                            <input type="text" class="form-control" id="meta_title" name="meta_title" value="<?= htmlspecialchars($f_meta_title); ?>" placeholder="Ex: <?= htmlspecialchars($f_title); ?> | Pronto Saúde" maxlength="70">
                                        </div>

                                        <div class="col-12">
                                            <label class="form-label fw-semibold text-body mb-1" for="meta_keyword">
                                                Palavras-chave (Separadas por vírgula)
                                            </label>
                                            <input type="text" class="form-control" id="meta_keyword" name="meta_keyword" value="<?= htmlspecialchars($f_meta_kw); ?>" placeholder="saude, medicina, dicas medicas, pronto saude, prevencao">
                                        </div>

                                        <div class="col-12">
                                            <div class="d-flex justify-content-between align-items-center mb-1">
                                                <label class="form-label fw-semibold text-body mb-0" for="meta_description">
                                                    Meta Descrição (Resumo do Google)
                                                </label>
                                                <small class="text-body-secondary" id="metaDescCount"><?= mb_strlen($f_meta_desc, 'UTF-8'); ?> / 160 caracteres</small>
                                            </div>
                                            <textarea class="form-control no-summernote" data-no-summernote="true" id="meta_description" name="meta_description" rows="3" placeholder="Resuma em até 160 caracteres o conteúdo da notícia para atrair cliques dos usuários no Google..." maxlength="180"><?= htmlspecialchars($f_meta_desc); ?></textarea>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>

                    </div>
                </div>

                <!-- Coluna Lateral (4 colunas): Publicação, Status e Capa -->
                <div class="col-12 col-lg-4">
                    <div class="d-flex flex-column gap-4">

                        <!-- Card de Publicação & Status -->
                        <div class="card border-0 shadow-sm rounded-3">
                            <div class="card-header bg-transparent border-bottom p-3">
                                <h6 class="fw-bold text-body mb-0">
                                    <i class="bi bi-send-check text-primary me-2"></i>Status &amp; Publicação
                                </h6>
                            </div>
                            <div class="card-body p-3">
                                
                                <!-- Switch de Status (Publicada / Rascunho) -->
                                <div class="p-3 bg-body-tertiary rounded-3 mb-3 border d-flex align-items-center justify-content-between">
                                    <div>
                                        <div class="fw-bold text-body small">Status da Notícia</div>
                                        <small class="text-body-secondary d-block" id="statusLabelDesc">
                                            <?= $f_status === 'Active' ? 'Visível publicamente no site' : 'Salva como rascunho oculto'; ?>
                                        </small>
                                    </div>
                                    <div class="form-check form-switch mb-0">
                                        <input class="form-check-input" type="checkbox" role="switch" id="statusSwitch" style="width: 2.8em; height: 1.4em; cursor: pointer;" <?= $f_status === 'Active' ? 'checked' : ''; ?>>
                                        <input type="hidden" name="status" id="statusInput" value="<?= htmlspecialchars($f_status); ?>">
                                    </div>
                                </div>

                                <!-- Slug Amigável com Prefixo -->
                                <div class="mb-3">
                                    <label class="form-label small fw-semibold text-body-secondary mb-1" for="news_slug">
                                        URL Amigável (Slug)
                                    </label>
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text bg-body-tertiary">/news/</span>
                                        <input type="text" class="form-control font-monospace" id="news_slug" name="news_slug" value="<?= htmlspecialchars($f_slug); ?>">
                                    </div>
                                    <div class="form-text small text-body-secondary mt-1">
                                        Identificador de URL único para este artigo.
                                    </div>
                                </div>

                                <hr class="my-3 opacity-25">

                                <!-- Botões de Ação -->
                                <div class="d-grid gap-2">
                                    <button type="submit" class="btn btn-primary btn-lg shadow-sm" id="btnSalvarNoticia" name="form1">
                                        <i class="bi bi-check-lg me-1"></i> Salvar Alterações
                                    </button>
                                    <?php if (!empty($f_slug)): ?>
                                        <a href="../news.php?slug=<?= urlencode($f_slug); ?>" target="_blank" rel="noopener" class="btn btn-outline-info">
                                            <i class="bi bi-box-arrow-up-right me-1"></i> Visualizar Artigo no Site
                                        </a>
                                    <?php endif; ?>
                                    <a href="news.php" class="btn btn-outline-secondary">
                                        <i class="bi bi-arrow-left me-1"></i> Cancelar e Voltar
                                    </a>
                                </div>

                            </div>
                        </div>

                        <!-- Card de Foto de Capa (Destaque) -->
                        <div class="card border-0 shadow-sm rounded-3">
                            <div class="card-header bg-transparent border-bottom p-3">
                                <h6 class="fw-bold text-body mb-0">
                                    <i class="bi bi-image text-primary me-2"></i>Foto de Capa (Destaque)
                                </h6>
                            </div>
                            <div class="card-body p-3 text-center">
                                
                                <!-- Foto Atual / Preview -->
                                <div class="position-relative overflow-hidden rounded-3 mb-3 border bg-body-secondary" style="aspect-ratio: 16/10;">
                                    <img src="<?= htmlspecialchars($foto_atual_url); ?>" id="imgCapaPreview" alt="Capa da Notícia" class="w-100 h-100 object-fit-cover rounded-3 shadow-xs">
                                    
                                    <!-- Badge de Estado -->
                                    <span class="position-absolute bottom-0 start-0 badge bg-dark bg-opacity-75 m-2 small" id="badgeFotoEstado">
                                        <?= $foto_atual_existe ? 'Foto Atual no Servidor' : 'Sem foto cadastrada'; ?>
                                    </span>
                                </div>

                                <!-- Dropzone de Upload -->
                                <div class="upload-dropzone mb-2" id="dropzoneCapa">
                                    <input type="file" id="photo" name="photo" class="d-none" accept="image/jpeg,image/png,image/webp,image/gif">
                                    <i class="bi bi-cloud-arrow-up fs-3 text-primary d-block mb-1"></i>
                                    <span class="fw-bold text-body small d-block">Clique para trocar a foto de capa</span>
                                    <small class="text-body-secondary d-block" style="font-size: 0.75rem;">Ou arraste uma nova imagem (JPG, PNG ou WEBP)</small>
                                </div>

                                <!-- Botão de Restaurar Foto Original (caso o usuário selecione um arquivo e desista) -->
                                <button type="button" class="btn btn-sm btn-outline-secondary d-none w-100 mb-2" id="btnRestaurarFoto">
                                    <i class="bi bi-arrow-counterclockwise me-1"></i>Manter Foto Atual
                                </button>

                                <div class="p-2 bg-body-tertiary rounded-3 text-start small border">
                                    <div class="d-flex align-items-center gap-1 text-primary fw-semibold mb-1">
                                        <i class="bi bi-info-circle"></i> Dimensões Recomendadas:
                                    </div>
                                    <div class="text-body-secondary" style="font-size: 0.78rem;">
                                        &bull; <strong>1200 &times; 630 px</strong> (Proporção 1.91:1)<br>
                                        &bull; Ideal para prévias no <strong>WhatsApp, Facebook e Twitter</strong>.
                                    </div>
                                </div>

                            </div>
                        </div>

                    </div>
                </div>

            </div>
        </form>

    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // 1. Inicialização do Summernote com Adaptação Dark/Light
    if (typeof $ !== 'undefined' && $.fn.summernote) {
        $('#news_content').summernote({
            placeholder: 'Escreva aqui o artigo completo com parágrafos, subtítulos, citações médicas e imagens...',
            tabsize: 2,
            height: 380,
            toolbar: [
                ['style', ['style']],
                ['font', ['bold', 'italic', 'underline', 'clear']],
                ['fontname', ['fontname']],
                ['color', ['color']],
                ['para', ['ul', 'ol', 'paragraph']],
                ['table', ['table']],
                ['insert', ['link', 'picture', 'video', 'hr']],
                ['view', ['fullscreen', 'codeview', 'help']]
            ],
            lang: 'pt-BR'
        });
    }

    // 2. Máscara de Data (DD/MM/AAAA)
    const dateInput = document.getElementById('news_date');
    if (dateInput) {
        dateInput.addEventListener('input', function (e) {
            let v = e.target.value.replace(/\D/g, '');
            if (v.length > 4) {
                v = v.replace(/^(\d{2})(\d{2})(\d{0,4}).*/, '$1/$2/$3');
            } else if (v.length > 2) {
                v = v.replace(/^(\d{2})(\d{0,2})/, '$1/$2');
            }
            e.target.value = v;

            const kpiData = document.getElementById('kpiData');
            if (kpiData) kpiData.textContent = v || '-';
        });
    }

    // 3. Atualização de Categoria no KPI
    const selectCat = document.getElementById('category_id');
    if (selectCat) {
        selectCat.addEventListener('change', function () {
            const opt = this.options[this.selectedIndex];
            const nomeCat = opt ? opt.getAttribute('data-name') || opt.text : 'Geral';
            const kpiCat = document.getElementById('kpiCategoria');
            if (kpiCat) kpiCat.textContent = nomeCat;
        });
    }

    // 4. Switch de Status Interativo
    const statusSwitch = document.getElementById('statusSwitch');
    const statusInput = document.getElementById('statusInput');
    const statusLabelDesc = document.getElementById('statusLabelDesc');
    const kpiStatusText = document.getElementById('kpiStatusText');
    const kpiStatusSub = document.getElementById('kpiStatusSub');
    const kpiStatusIcon = document.getElementById('kpiStatusIcon');

    if (statusSwitch && statusInput) {
        statusSwitch.addEventListener('change', function () {
            if (this.checked) {
                statusInput.value = 'Active';
                if (statusLabelDesc) statusLabelDesc.textContent = 'Visível publicamente no site';
                if (kpiStatusText) {
                    kpiStatusText.textContent = 'Publicada';
                    kpiStatusText.className = 'mb-0 fw-bold text-success';
                }
                if (kpiStatusSub) kpiStatusSub.textContent = 'Acessível aos visitantes';
                if (kpiStatusIcon) {
                    kpiStatusIcon.className = 'kpi-icon-circle bg-success-subtle text-success';
                    kpiStatusIcon.innerHTML = '<i class="bi bi-check-circle-fill fs-4"></i>';
                }
            } else {
                statusInput.value = 'Inactive';
                if (statusLabelDesc) statusLabelDesc.textContent = 'Salva como rascunho oculto';
                if (kpiStatusText) {
                    kpiStatusText.textContent = 'Rascunho';
                    kpiStatusText.className = 'mb-0 fw-bold text-warning';
                }
                if (kpiStatusSub) kpiStatusSub.textContent = 'Oculta do portal público';
                if (kpiStatusIcon) {
                    kpiStatusIcon.className = 'kpi-icon-circle bg-warning-subtle text-warning';
                    kpiStatusIcon.innerHTML = '<i class="bi bi-pause-circle-fill fs-4"></i>';
                }
            }
        });
    }

    // 5. Slug e Google Snippet Preview Reativos
    const titleInput = document.getElementById('news_title');
    const slugInput = document.getElementById('news_slug');
    const metaTitleInput = document.getElementById('meta_title');
    const metaDescInput = document.getElementById('meta_description');
    const googlePreviewSlug = document.getElementById('googlePreviewSlug');
    const googlePreviewTitle = document.getElementById('googlePreviewTitle');
    const googlePreviewDesc = document.getElementById('googlePreviewDesc');
    const metaTitleCount = document.getElementById('metaTitleCount');
    const metaDescCount = document.getElementById('metaDescCount');

    function sanitizarSlug(texto) {
        return texto
            .toString()
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .toLowerCase()
            .trim()
            .replace(/[^a-z0-9\s-]/g, '')
            .replace(/\s+/g, '-')
            .replace(/-+/g, '-');
    }

    if (slugInput) {
        slugInput.addEventListener('input', function () {
            const s = sanitizarSlug(this.value);
            this.value = s;
            if (googlePreviewSlug) googlePreviewSlug.textContent = s || 'noticia';
        });
    }

    if (titleInput) {
        titleInput.addEventListener('input', function () {
            if (!metaTitleInput.value && googlePreviewTitle) {
                googlePreviewTitle.textContent = (this.value ? this.value + ' | Pronto Saúde' : 'Título da Notícia | Pronto Saúde');
            }
        });
    }

    if (metaTitleInput) {
        metaTitleInput.addEventListener('input', function () {
            const len = this.value.length;
            if (metaTitleCount) metaTitleCount.textContent = `${len} / 60 caracteres`;
            if (googlePreviewTitle) {
                googlePreviewTitle.textContent = this.value || (titleInput.value ? titleInput.value + ' | Pronto Saúde' : 'Título da Notícia | Pronto Saúde');
            }
        });
    }

    if (metaDescInput) {
        metaDescInput.addEventListener('input', function () {
            const len = this.value.length;
            if (metaDescCount) metaDescCount.textContent = `${len} / 160 caracteres`;
            if (googlePreviewDesc) {
                googlePreviewDesc.textContent = this.value || 'Leia a notícia completa no portal do Pronto Saúde com informações atualizadas, orientações de especialistas e novidades médicas.';
            }
        });
    }

    // 6. Upload de Imagem de Capa com Preview e Reversão
    const dropzone = document.getElementById('dropzoneCapa');
    const inputPhoto = document.getElementById('photo');
    const imgCapa = document.getElementById('imgCapaPreview');
    const badgeEstado = document.getElementById('badgeFotoEstado');
    const btnRestaurar = document.getElementById('btnRestaurarFoto');
    const fotoOriginalSrc = imgCapa ? imgCapa.src : '';

    if (dropzone && inputPhoto) {
        dropzone.addEventListener('click', function () {
            inputPhoto.click();
        });

        dropzone.addEventListener('dragover', function (e) {
            e.preventDefault();
            dropzone.classList.add('dragover');
        });

        dropzone.addEventListener('dragleave', function () {
            dropzone.classList.remove('dragover');
        });

        dropzone.addEventListener('drop', function (e) {
            e.preventDefault();
            dropzone.classList.remove('dragover');
            if (e.dataTransfer.files && e.dataTransfer.files[0]) {
                inputPhoto.files = e.dataTransfer.files;
                carregarPreview(e.dataTransfer.files[0]);
            }
        });

        inputPhoto.addEventListener('change', function () {
            if (this.files && this.files[0]) {
                carregarPreview(this.files[0]);
            }
        });

        function carregarPreview(file) {
            const reader = new FileReader();
            reader.onload = function (e) {
                if (imgCapa) imgCapa.src = e.target.result;
                if (badgeEstado) {
                    badgeEstado.textContent = 'Nova Capa Selecionada';
                    badgeEstado.className = 'position-absolute bottom-0 start-0 badge bg-primary m-2 small';
                }
                if (btnRestaurar) btnRestaurar.classList.remove('d-none');
            };
            reader.readAsDataURL(file);
        }

        if (btnRestaurar) {
            btnRestaurar.addEventListener('click', function () {
                inputPhoto.value = '';
                if (imgCapa) imgCapa.src = fotoOriginalSrc;
                if (badgeEstado) {
                    badgeEstado.textContent = 'Foto Atual no Servidor';
                    badgeEstado.className = 'position-absolute bottom-0 start-0 badge bg-dark bg-opacity-75 m-2 small';
                }
                btnRestaurar.classList.add('d-none');
            });
        }
    }

    // 7. Submissão do Formulário via AJAX & SweetAlert2
    const form = document.getElementById('formEditNews');
    const btnSalvar = document.getElementById('btnSalvarNoticia');

    if (form) {
        form.addEventListener('submit', function (e) {
            e.preventDefault();

            // Sincroniza o conteúdo do Summernote com o textarea
            if (typeof $ !== 'undefined' && $.fn.summernote) {
                $('#news_content').val($('#news_content').summernote('code'));
            }

            const titleVal = titleInput ? titleInput.value.trim() : '';
            if (!titleVal) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Atenção',
                        text: 'Por favor, informe o título da notícia.',
                        confirmButtonText: 'OK'
                    }).then(() => {
                        const tabBtn = document.getElementById('tab-conteudo-btn');
                        if (tabBtn && typeof bootstrap !== 'undefined') {
                            bootstrap.Tab.getOrCreateInstance(tabBtn).show();
                        }
                        if (titleInput) titleInput.focus();
                    });
                }
                return;
            }

            const catVal = selectCat ? selectCat.value : '';
            if (!catVal || catVal === '0') {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Categoria Obrigatória',
                        text: 'Por favor, selecione uma categoria para a notícia.',
                        confirmButtonText: 'OK'
                    }).then(() => {
                        const tabBtn = document.getElementById('tab-conteudo-btn');
                        if (tabBtn && typeof bootstrap !== 'undefined') {
                            bootstrap.Tab.getOrCreateInstance(tabBtn).show();
                        }
                        if (selectCat) selectCat.focus();
                    });
                }
                return;
            }

            const originalBtnHtml = btnSalvar.innerHTML;
            btnSalvar.disabled = true;
            btnSalvar.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status"></span>Salvando...';

            const formData = new FormData(form);

            fetch('news-edit.php?id=<?= $news_id; ?>', {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Sucesso!',
                            text: data.message || 'Notícia atualizada com sucesso!',
                            timer: 1800,
                            showConfirmButton: false
                        }).then(() => {
                            window.location.href = data.redirect || 'news.php';
                        });
                    } else {
                        window.location.href = data.redirect || 'news.php';
                    }
                } else {
                    btnSalvar.disabled = false;
                    btnSalvar.innerHTML = originalBtnHtml;
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'error',
                            title: 'Erro ao atualizar',
                            html: data.message || 'Ocorreu um erro ao atualizar os dados da notícia.',
                            confirmButtonText: 'Entendido'
                        });
                    }
                }
            })
            .catch(err => {
                console.error(err);
                btnSalvar.disabled = false;
                btnSalvar.innerHTML = originalBtnHtml;
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'error',
                        title: 'Erro de Comunicação',
                        text: 'Houve uma falha ao enviar os dados para o servidor. Tente novamente.',
                        confirmButtonText: 'Fechar'
                    });
                }
            });
        });
    }
});
</script>

<?php require_once('footer.php'); ?>
