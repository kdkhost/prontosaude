<?php
require_once('header.php');

// 1. Validação e Carregamento do Vídeo Solicitado
$id = (int)($_REQUEST['id'] ?? 0);

if ($id <= 0) {
    $_SESSION['erro'] = 'Identificador de vídeo inválido ou ausente.';
    header('Location: video.php');
    exit;
}

$stmt_video = $pdo->prepare("SELECT 
                                v.video_id, 
                                v.video_title, 
                                v.video_iframe, 
                                v.v_category_id, 
                                v.status,
                                c.v_category_name
                            FROM tbl_video v
                            LEFT JOIN tbl_category_video c ON v.v_category_id = c.v_category_id
                            WHERE v.video_id = ?");
$stmt_video->execute([$id]);
$video = $stmt_video->fetch(PDO::FETCH_ASSOC);

if (!$video) {
    $_SESSION['erro'] = 'O vídeo solicitado (# ' . $id . ') não foi encontrado no acervo.';
    header('Location: video.php');
    exit;
}

// 2. Carrega todas as categorias de vídeos disponíveis
$stmt_cats = $pdo->prepare("SELECT v_category_id, v_category_name, status FROM tbl_category_video ORDER BY v_category_name ASC");
$stmt_cats->execute();
$categorias = $stmt_cats->fetchAll(PDO::FETCH_ASSOC);

// Métricas de apoio
$stmt_total = $pdo->query("SELECT COUNT(*) FROM tbl_video");
$total_videos_existentes = (int)$stmt_total->fetchColumn();

// Detecta o tipo de mídia atual (MP4 local ou URL/Embed externo)
$current_iframe = trim($video['video_iframe'] ?? '');
$is_current_local_mp4 = (strpos($current_iframe, '../assets/uploads/videos/') === 0 || strpos($current_iframe, '.mp4') !== false);

// 3. Processamento da Atualização (Suporte completo a AJAX e POST tradicional)
$is_ajax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
           || (isset($_POST['action']) && $_POST['action'] === 'edit_video_ajax');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && (isset($_POST['form1']) || $is_ajax)) {
    $video_title       = trim($_POST['video_title'] ?? '');
    $video_source_type = trim($_POST['video_source_type'] ?? ($is_current_local_mp4 ? 'upload' : 'url'));
    $video_iframe      = trim($_POST['video_iframe'] ?? '');
    $v_category_id     = (int)($_POST['v_category_id'] ?? 0);
    $status            = trim($_POST['status'] ?? 'Active');
    $thumb_data        = $_POST['video_thumb_base64'] ?? '';

    $valid = 1;
    $erros = [];

    // Validação de Título
    if (empty($video_title)) {
        $valid = 0;
        $erros[] = 'Informe um título descritivo para o vídeo.';
    }

    // Validação de Categoria
    if ($v_category_id <= 0) {
        $valid = 0;
        $erros[] = 'Selecione uma categoria válida para organizar o vídeo.';
    }

    if (!in_array($status, ['Active', 'Inactive'])) {
        $status = 'Active';
    }

    $final_video_code = $current_iframe;

    // Se selecionou aba de Upload de Arquivo MP4
    if ($video_source_type === 'upload') {
        if (isset($_FILES['video_file']) && !empty($_FILES['video_file']['name'])) {
            $file_name = $_FILES['video_file']['name'];
            $file_tmp  = $_FILES['video_file']['tmp_name'];
            $file_size = $_FILES['video_file']['size'];
            $file_err  = $_FILES['video_file']['error'];

            if ($file_err !== UPLOAD_ERR_OK) {
                $valid = 0;
                $erros[] = 'Erro durante o upload do novo vídeo MP4 (Código: ' . $file_err . ').';
            } else {
                $ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
                if ($ext !== 'mp4') {
                    $valid = 0;
                    $erros[] = 'Formato de vídeo inválido. Envie um arquivo em formato MP4.';
                }

                // Limite de 100MB
                if ($file_size > 100 * 1024 * 1024) {
                    $valid = 0;
                    $erros[] = 'O arquivo de vídeo excede o limite máximo permitido de 100MB.';
                }
            }

            if ($valid === 1) {
                // Remove arquivo antigo anterior se era um MP4 local
                if ($is_current_local_mp4) {
                    $old_file = __DIR__ . '/' . $current_iframe;
                    if (file_exists($old_file) && is_file($old_file)) {
                        @unlink($old_file);
                    }
                    $old_thumb = __DIR__ . '/../assets/uploads/videos/thumbs/' . pathinfo($current_iframe, PATHINFO_FILENAME) . '.jpg';
                    if (file_exists($old_thumb) && is_file($old_thumb)) {
                        @unlink($old_thumb);
                    }
                }

                $base_clean = preg_replace('/[^a-zA-Z0-9_\-]/', '_', pathinfo($file_name, PATHINFO_FILENAME));
                $final_file_name = 'video_' . $id . '_' . $base_clean . '_' . time() . '.mp4';
                $dir_videos = __DIR__ . '/../assets/uploads/videos/';

                if (!is_dir($dir_videos)) {
                    @mkdir($dir_videos, 0777, true);
                }

                $destino = $dir_videos . $final_file_name;

                if (move_uploaded_file($file_tmp, $destino)) {
                    $final_video_code = '../assets/uploads/videos/' . $final_file_name;

                    // Salva nova miniatura via Canvas se enviada
                    if (!empty($thumb_data) && strpos($thumb_data, 'data:image/') === 0) {
                        $parts = explode(',', $thumb_data, 2);
                        if (isset($parts[1])) {
                            $thumb_binary = base64_decode($parts[1]);
                            $thumb_dir = $dir_videos . 'thumbs/';
                            if (!is_dir($thumb_dir)) {
                                @mkdir($thumb_dir, 0777, true);
                            }
                            $thumb_filename = pathinfo($final_file_name, PATHINFO_FILENAME) . '.jpg';
                            @file_put_contents($thumb_dir . $thumb_filename, $thumb_binary);
                        }
                    }
                } else {
                    $valid = 0;
                    $erros[] = 'Falha ao salvar o novo vídeo no servidor. Verifique permissões de pasta.';
                }
            }
        }
    } else {
        // Aba de Link ou Código Embed (YouTube / Vimeo / iframe)
        if (empty($video_iframe)) {
            $valid = 0;
            $erros[] = 'Insira o link do YouTube, Vimeo ou o código de incorporação <iframe>.';
        } else {
            $final_video_code = $video_iframe;

            // Se mudou de MP4 para URL externa, limpa o MP4 antigo do servidor
            if ($is_current_local_mp4 && $final_video_code !== $current_iframe) {
                $old_file = __DIR__ . '/' . $current_iframe;
                if (file_exists($old_file) && is_file($old_file)) {
                    @unlink($old_file);
                }
            }
        }
    }

    if ($valid === 1) {
        $stmt_upd = $pdo->prepare("UPDATE tbl_video SET video_title = ?, video_iframe = ?, v_category_id = ?, status = ? WHERE video_id = ?");
        $stmt_upd->execute([$video_title, $final_video_code, $v_category_id, $status, $id]);

        $msg_sucesso = 'Vídeo atualizado com sucesso no acervo!';

        if ($is_ajax) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'status'   => 'success',
                'message'  => $msg_sucesso,
                'video_id' => $id
            ]);
            exit;
        }

        $_SESSION['sucesso'] = $msg_sucesso;
        header('Location: video.php');
        exit;
    } else {
        $msg_erro = implode('<br>', $erros);
        if ($is_ajax) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'status'  => 'error',
                'message' => $msg_erro
            ]);
            exit;
        }
        $error_message = $msg_erro;
    }
}

// Recarrega dados atualizados
$video_title   = htmlspecialchars($video['video_title'] ?? '', ENT_QUOTES, 'UTF-8');
$video_iframe  = $video['video_iframe'] ?? '';
$v_category_id = (int)$video['v_category_id'];
$video_status  = $video['status'] ?? 'Active';
$cat_name      = htmlspecialchars($video['v_category_name'] ?? 'Sem Categoria', ENT_QUOTES, 'UTF-8');
?>

<!-- Cabeçalho da Página -->
<div class="app-content-header pb-2 mb-3 border-bottom">
    <div class="container-fluid">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div>
                <div class="d-flex align-items-center gap-2 mb-1">
                    <h3 class="mb-0 fw-bold text-body">
                        <i class="bi bi-camera-reels text-primary me-2"></i>Editar Vídeo
                    </h3>
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-3 py-1 font-monospace">
                        #<?= $id; ?>
                    </span>
                    <span class="badge <?= $video_status === 'Active' ? 'bg-success-subtle text-success border-success-subtle' : 'bg-warning-subtle text-warning border-warning-subtle'; ?> border rounded-pill px-2 py-1">
                        <i class="bi <?= $video_status === 'Active' ? 'bi-check-circle-fill' : 'bi-eye-slash-fill'; ?> me-1"></i>
                        <?= $video_status === 'Active' ? 'Visível' : 'Oculto'; ?>
                    </span>
                </div>
                <ol class="breadcrumb mb-0 small">
                    <li class="breadcrumb-item"><a href="index.php" class="text-decoration-none"><i class="bi bi-house-door"></i> Início</a></li>
                    <li class="breadcrumb-item"><a href="video.php" class="text-decoration-none">Multimídia</a></li>
                    <li class="breadcrumb-item"><a href="video.php" class="text-decoration-none">Galeria de Vídeos</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Editar #<?= $id; ?></li>
                </ol>
            </div>
            <div class="d-flex flex-wrap align-items-center gap-2">
                <a href="../page.php?slug=videos" target="_blank" rel="noopener" class="btn btn-outline-info btn-sm rounded-pill px-3 shadow-sm" data-bs-toggle="tooltip" title="Visualizar galeria de vídeos no site público">
                    <i class="bi bi-box-arrow-up-right me-1"></i> Ver no Site
                </a>
                <a href="video-category.php" class="btn btn-outline-secondary btn-sm rounded-pill px-3 shadow-sm" data-bs-toggle="tooltip" title="Gerenciar categorias de vídeos">
                    <i class="bi bi-tags me-1"></i> Categorias
                </a>
                <a href="video-add.php" class="btn btn-outline-primary btn-sm rounded-pill px-3 shadow-sm" data-bs-toggle="tooltip" title="Cadastrar novo vídeo">
                    <i class="bi bi-plus-lg me-1"></i> Adicionar Vídeo
                </a>
                <a href="video.php" class="btn btn-secondary btn-sm rounded-pill px-3 shadow-sm" data-bs-toggle="tooltip" title="Retornar à listagem geral de vídeos">
                    <i class="bi bi-arrow-left me-1"></i> Voltar à Lista
                </a>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">

        <!-- Mensagens Tradicionais de Sessão (Fallback) -->
        <?php if (!empty($error_message)): ?>
            <div class="alert alert-danger alert-dismissible fade show shadow-sm mb-4" role="alert">
                <div class="d-flex align-items-center">
                    <i class="bi bi-exclamation-triangle-fill fs-5 me-2"></i>
                    <div><?= $error_message; ?></div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
            </div>
        <?php endif; ?>

        <!-- Mini Cards de Indicadores (KPIs) com .kpi-icon-circle perfeitamente redondo -->
        <div class="row g-3 mb-4">
            <!-- Total de Vídeos -->
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card border-0 shadow-sm rounded-3 h-100 kpi-card">
                    <div class="card-body d-flex align-items-center justify-content-between p-3">
                        <div>
                            <span class="text-uppercase fw-semibold text-body-secondary small d-block mb-1">Acervo Geral</span>
                            <h3 class="mb-0 fw-bold text-body"><?= $total_videos_existentes; ?></h3>
                            <span class="text-body-secondary small">Vídeos cadastrados</span>
                        </div>
                        <div class="kpi-icon-circle bg-primary-subtle text-primary">
                            <i class="bi bi-camera-reels fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Categoria Atual -->
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card border-0 shadow-sm rounded-3 h-100 kpi-card">
                    <div class="card-body d-flex align-items-center justify-content-between p-3">
                        <div>
                            <span class="text-uppercase fw-semibold text-body-secondary small d-block mb-1">Categoria Vinculada</span>
                            <h5 class="mb-0 fw-bold text-info text-truncate" style="max-width: 170px;" title="<?= $cat_name; ?>"><?= $cat_name; ?></h5>
                            <span class="text-body-secondary small">Organização temática</span>
                        </div>
                        <div class="kpi-icon-circle bg-info-subtle text-info">
                            <i class="bi bi-tag fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Status do Vídeo -->
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card border-0 shadow-sm rounded-3 h-100 kpi-card">
                    <div class="card-body d-flex align-items-center justify-content-between p-3">
                        <div>
                            <span class="text-uppercase fw-semibold text-body-secondary small d-block mb-1">Visibilidade no Site</span>
                            <h4 class="mb-0 fw-bold <?= $video_status === 'Active' ? 'text-success' : 'text-warning'; ?>" id="kpiStatusText">
                                <?= $video_status === 'Active' ? 'Ativo / Visível' : 'Oculto / Pausado'; ?>
                            </h4>
                            <span class="text-body-secondary small">Status de exibição</span>
                        </div>
                        <div class="kpi-icon-circle <?= $video_status === 'Active' ? 'bg-success-subtle text-success' : 'bg-warning-subtle text-warning'; ?>" id="kpiStatusIcon">
                            <i class="bi <?= $video_status === 'Active' ? 'bi-check-circle' : 'bi-eye-slash'; ?> fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tipo de Fonte -->
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card border-0 shadow-sm rounded-3 h-100 kpi-card">
                    <div class="card-body d-flex align-items-center justify-content-between p-3">
                        <div>
                            <span class="text-uppercase fw-semibold text-body-secondary small d-block mb-1">Formato da Mídia</span>
                            <h5 class="mb-0 fw-bold text-body">
                                <?= $is_current_local_mp4 ? 'Arquivo MP4' : 'Link / Embed Externo'; ?>
                            </h5>
                            <span class="text-body-secondary small"><?= $is_current_local_mp4 ? 'Hospedado no servidor' : 'YouTube / Vimeo / Iframe'; ?></span>
                        </div>
                        <div class="kpi-icon-circle bg-danger-subtle text-danger">
                            <i class="bi <?= $is_current_local_mp4 ? 'bi-file-earmark-play' : 'bi-youtube'; ?> fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Formulário de Edição -->
        <form action="" method="post" enctype="multipart/form-data" id="formVideoEdit" novalidate>
            <input type="hidden" name="form1" value="1">
            <input type="hidden" name="id" value="<?= $id; ?>">
            <input type="hidden" name="video_source_type" id="video_source_type" value="<?= $is_current_local_mp4 ? 'upload' : 'url'; ?>">
            <input type="hidden" name="video_thumb_base64" id="video_thumb_base64" value="">

            <div class="row g-4">
                <!-- Coluna Principal (Formulário e Players) -->
                <div class="col-12 col-xl-8">
                    <div class="card border-0 shadow-sm rounded-3 mb-4">
                        <div class="card-header bg-body py-3 border-bottom d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center gap-2">
                                <i class="bi bi-pencil-square text-primary fs-5"></i>
                                <h5 class="card-title mb-0 fw-bold text-body">Dados do Vídeo</h5>
                            </div>
                            <span class="text-body-secondary small">
                                <i class="bi bi-info-circle me-1"></i>Preencha os campos obrigatórios (<span class="text-danger">*</span>)
                            </span>
                        </div>

                        <div class="card-body p-4">
                            <!-- Campo: Título do Vídeo -->
                            <div class="mb-4">
                                <label for="video_title" class="form-label fw-bold text-body d-flex align-items-center justify-content-between">
                                    <span>
                                        <i class="bi bi-type text-primary me-1"></i> Título do Vídeo <span class="text-danger">*</span>
                                    </span>
                                    <small class="text-body-secondary fw-normal" id="charCounter">0/150 caracteres</small>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text bg-body-tertiary text-body-secondary border">
                                        <i class="bi bi-film"></i>
                                    </span>
                                    <input type="text" 
                                           class="form-control form-control-lg bg-body text-body border" 
                                           id="video_title" 
                                           name="video_title" 
                                           maxlength="150" 
                                           required 
                                           value="<?= $video_title; ?>" 
                                           placeholder="Ex: Apresentação da Clínica, Cirurgias e Procedimentos...">
                                </div>
                                <div class="form-text text-body-secondary small mt-1">
                                    <i class="bi bi-info-circle me-1"></i> O título identifica o vídeo nas galerias e na busca do portal.
                                </div>
                            </div>

                            <!-- Campo: Categoria de Vídeo -->
                            <div class="mb-4">
                                <label for="v_category_id" class="form-label fw-bold text-body">
                                    <i class="bi bi-tag text-primary me-1"></i> Categoria do Vídeo <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text bg-body-tertiary text-body-secondary border">
                                        <i class="bi bi-folder2-open"></i>
                                    </span>
                                    <select class="form-select form-select-lg bg-body text-body border" id="v_category_id" name="v_category_id" required>
                                        <option value="" disabled>Selecione uma categoria...</option>
                                        <?php foreach ($categorias as $cat): ?>
                                            <option value="<?= $cat['v_category_id']; ?>" <?= ($cat['v_category_id'] == $v_category_id) ? 'selected' : ''; ?>>
                                                <?= htmlspecialchars($cat['v_category_name'], ENT_QUOTES, 'UTF-8'); ?>
                                                <?= ($cat['status'] === 'Inactive') ? ' (Inativa)' : ''; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-text text-body-secondary small mt-1">
                                    <i class="bi bi-info-circle me-1"></i> Organiza este conteúdo na aba correspondente da galeria pública.
                                </div>
                            </div>

                            <!-- Abas de Seleção de Fonte do Vídeo -->
                            <div class="mb-4">
                                <label class="form-label fw-bold text-body d-block mb-2">
                                    <i class="bi bi-hdd-network text-primary me-1"></i> Origem do Vídeo <span class="text-danger">*</span>
                                </label>

                                <ul class="nav nav-pills nav-justified p-1 bg-body-tertiary rounded-3 border mb-3" id="videoSourceTabs" role="tablist">
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link <?= !$is_current_local_mp4 ? 'active' : ''; ?> rounded-2 py-2 fw-semibold" 
                                                id="tab-url-btn" 
                                                data-bs-toggle="pill" 
                                                data-bs-target="#tab-url" 
                                                type="button" 
                                                role="tab" 
                                                aria-controls="tab-url" 
                                                aria-selected="<?= !$is_current_local_mp4 ? 'true' : 'false'; ?>">
                                            <i class="bi bi-youtube text-danger me-1"></i> Link ou Embed (YouTube / Vimeo / Iframe)
                                        </button>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link <?= $is_current_local_mp4 ? 'active' : ''; ?> rounded-2 py-2 fw-semibold" 
                                                id="tab-upload-btn" 
                                                data-bs-toggle="pill" 
                                                data-bs-target="#tab-upload" 
                                                type="button" 
                                                role="tab" 
                                                aria-controls="tab-upload" 
                                                aria-selected="<?= $is_current_local_mp4 ? 'true' : 'false'; ?>">
                                            <i class="bi bi-upload text-primary me-1"></i> Arquivo MP4 Local
                                        </button>
                                    </li>
                                </ul>

                                <div class="tab-content border rounded-3 p-3 bg-body-tertiary" id="videoSourceTabsContent">
                                    <!-- ABA 1: URL / CÓDIGO EMBED -->
                                    <div class="tab-pane fade <?= !$is_current_local_mp4 ? 'show active' : ''; ?>" id="tab-url" role="tabpanel" aria-labelledby="tab-url-btn">
                                        <label for="video_iframe" class="form-label fw-semibold text-body small">
                                            Link do YouTube/Vimeo ou Código &lt;iframe&gt; Completo:
                                        </label>
                                        <div class="input-group mb-2">
                                            <span class="input-group-text bg-body text-body-secondary border">
                                                <i class="bi bi-link-45deg"></i>
                                            </span>
                                            <textarea class="form-control bg-body text-body border font-monospace small" 
                                                      id="video_iframe" 
                                                      name="video_iframe" 
                                                      rows="4" 
                                                      placeholder="Cole aqui a URL (https://www.youtube.com/watch?v=... ou https://vimeo.com/...) ou o código de incorporação <iframe>"><?= !$is_current_local_mp4 ? htmlspecialchars($video_iframe, ENT_QUOTES, 'UTF-8') : ''; ?></textarea>
                                        </div>
                                        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                                            <small class="text-body-secondary">
                                                <i class="bi bi-magic me-1 text-primary"></i>Suporta URLs diretas do YouTube, Shorts, links curtos youtu.be e Vimeo.
                                            </small>
                                            <button type="button" class="btn btn-outline-primary btn-sm rounded-pill px-3" id="btnTestarLink">
                                                <i class="bi bi-play-circle me-1"></i> Atualizar Pré-visualização
                                            </button>
                                        </div>
                                    </div>

                                    <!-- ABA 2: UPLOAD DE NOVO ARQUIVO MP4 -->
                                    <div class="tab-pane fade <?= $is_current_local_mp4 ? 'show active' : ''; ?>" id="tab-upload" role="tabpanel" aria-labelledby="tab-upload-btn">
                                        <?php if ($is_current_local_mp4): ?>
                                            <div class="alert alert-info border-0 d-flex align-items-center gap-2 py-2 px-3 mb-3 small">
                                                <i class="bi bi-info-circle-fill fs-5"></i>
                                                <div>
                                                    <strong>Arquivo MP4 Atual:</strong> <code><?= basename($video_iframe); ?></code>
                                                    <br><span class="text-body-secondary">Envie um novo arquivo apenas se desejar substituir o vídeo atual.</span>
                                                </div>
                                            </div>
                                        <?php endif; ?>

                                        <label for="video_file" class="form-label fw-semibold text-body small">
                                            <?= $is_current_local_mp4 ? 'Substituir por Novo Vídeo MP4 (Opcional):' : 'Selecione o arquivo de vídeo MP4:*'; ?>
                                        </label>
                                        <div class="input-group mb-2">
                                            <input type="file" 
                                                   class="form-control bg-body text-body border" 
                                                   id="video_file" 
                                                   name="video_file" 
                                                   accept="video/mp4">
                                            <button class="btn btn-outline-secondary" type="button" id="btnClearVideoFile">Limpar</button>
                                        </div>
                                        <div class="small text-body-secondary d-flex flex-wrap justify-content-between gap-2">
                                            <span><i class="bi bi-check2-circle text-success me-1"></i> Formato aceito: <strong>MP4</strong></span>
                                            <span><i class="bi bi-hdd me-1"></i> Limite: <strong>100MB</strong></span>
                                        </div>

                                        <!-- Barra de Progresso de Upload -->
                                        <div class="progress mt-3 d-none rounded-pill" id="uploadProgressBar" style="height: 10px;">
                                            <div class="progress-bar progress-bar-striped progress-bar-animated bg-primary" 
                                                 role="progressbar" 
                                                 style="width: 0%" 
                                                 id="uploadProgressIndicator" 
                                                 aria-valuenow="0" 
                                                 aria-valuemin="0" 
                                                 aria-valuemax="100"></div>
                                        </div>
                                        <div class="text-center small text-primary fw-semibold mt-1 d-none" id="uploadProgressText">Enviando vídeo: 0%</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Player de Pré-visualização Interativo -->
                            <div class="mt-4">
                                <label class="form-label fw-bold text-body d-flex align-items-center justify-content-between">
                                    <span>
                                        <i class="bi bi-play-btn text-primary me-1"></i> Pré-visualização do Player
                                    </span>
                                    <span class="badge bg-body-secondary text-body-secondary border font-monospace small" id="badgeFormatoPreview">
                                        <?= $is_current_local_mp4 ? 'MP4 LOCAL' : 'PLAYER EXTERNO'; ?>
                                    </span>
                                </label>

                                <div class="ratio ratio-16x9 rounded-3 overflow-hidden border bg-black shadow-sm position-relative d-flex align-items-center justify-content-center" id="playerContainer">
                                    <?php if ($is_current_local_mp4): ?>
                                        <video controls class="w-100 h-100" id="previewLocalVideo" preload="metadata">
                                            <source src="<?= htmlspecialchars($video_iframe, ENT_QUOTES, 'UTF-8'); ?>" type="video/mp4">
                                            Seu navegador não suporta a tag de vídeo HTML5.
                                        </video>
                                    <?php else: ?>
                                        <?php
                                        // Renderiza iframe do link atual com segurança
                                        $iframe_output = $video_iframe;
                                        if (strpos($iframe_output, '<iframe') === false) {
                                            // Tenta extrair ID do YouTube
                                            if (preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/|youtube\.com/shorts/)([^"&?/ ]{11})%i', $iframe_output, $match)) {
                                                $iframe_output = '<iframe src="https://www.youtube.com/embed/' . $match[1] . '" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>';
                                            } elseif (preg_match('/vimeo\.com\/(?:channels\/(?:\w+\/)?|groups\/([^\/]*)\/videos\/|album\/(\d+)\/video\/|video\/|)(\d+)/', $iframe_output, $vimeo_match)) {
                                                $iframe_output = '<iframe src="https://player.vimeo.com/video/' . $vimeo_match[3] . '" frameborder="0" allow="autoplay; fullscreen; picture-in-picture" allowfullscreen></iframe>';
                                            }
                                        }
                                        echo $iframe_output;
                                        ?>
                                    <?php endif; ?>
                                </div>
                                <div class="text-body-secondary small mt-2">
                                    <i class="bi bi-eye me-1"></i> O player acima reflete exatamente como o vídeo será exibido aos pacientes e visitantes.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Coluna Lateral (Publicação, Status e Boas Práticas) -->
                <div class="col-12 col-xl-4">
                    <!-- Card de Publicação & Status -->
                    <div class="card border-0 shadow-sm rounded-3 mb-4">
                        <div class="card-header bg-body py-3 border-bottom">
                            <h6 class="card-title mb-0 fw-bold text-body d-flex align-items-center gap-2">
                                <i class="bi bi-gear-fill text-primary"></i> Publicação & Status
                            </h6>
                        </div>
                        <div class="card-body p-3">
                            <!-- Switch Estilizado de Status -->
                            <div class="p-3 rounded-3 bg-body-tertiary border mb-3">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div>
                                        <label for="status" class="fw-bold text-body mb-0 d-block cursor-pointer">Visibilidade</label>
                                        <small class="text-body-secondary" id="statusLabelDesc">
                                            <?= $video_status === 'Active' ? 'Exibido publicamente no portal' : 'Oculto para os visitantes'; ?>
                                        </small>
                                    </div>
                                    <div class="form-check form-switch fs-4 mb-0">
                                        <input class="form-check-control form-check-input" 
                                               type="checkbox" 
                                               role="switch" 
                                               id="statusSwitch" 
                                               <?= $video_status === 'Active' ? 'checked' : ''; ?>>
                                        <input type="hidden" name="status" id="statusInput" value="<?= $video_status; ?>">
                                    </div>
                                </div>
                            </div>

                            <!-- Informações do Registro -->
                            <div class="d-flex flex-column gap-2 small text-body-secondary mb-3 p-2">
                                <div class="d-flex justify-content-between border-bottom pb-1">
                                    <span>Identificador:</span>
                                    <span class="fw-bold text-body font-monospace">#<?= $id; ?></span>
                                </div>
                                <div class="d-flex justify-content-between border-bottom pb-1">
                                    <span>Formato Atual:</span>
                                    <span class="fw-semibold text-body"><?= $is_current_local_mp4 ? 'MP4 Local' : 'Embed/URL'; ?></span>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span>Galeria Vinculada:</span>
                                    <span class="fw-semibold text-body text-truncate" style="max-width: 150px;"><?= $cat_name; ?></span>
                                </div>
                            </div>

                            <!-- Botões de Ação -->
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary rounded-pill py-2 shadow-sm fw-semibold" id="btnSalvarVideo">
                                    <i class="bi bi-check-lg me-1"></i> Salvar Alterações
                                </button>
                                <a href="video.php" class="btn btn-outline-secondary rounded-pill py-2">
                                    Cancelar
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Card de Dicas de Incorporação -->
                    <div class="card border-0 shadow-sm rounded-3 mb-4">
                        <div class="card-header bg-body py-3 border-bottom">
                            <h6 class="card-title mb-0 fw-bold text-body d-flex align-items-center gap-2">
                                <i class="bi bi-lightbulb-fill text-warning"></i> Dicas e Formatos Suportados
                            </h6>
                        </div>
                        <div class="card-body p-3 small text-body-secondary">
                            <ul class="list-unstyled mb-0 d-flex flex-column gap-2">
                                <li class="d-flex align-items-start gap-2">
                                    <i class="bi bi-check-circle-fill text-success mt-1"></i>
                                    <div><strong>YouTube:</strong> Cole links comuns (<code>watch?v=...</code>), links curtos (<code>youtu.be/...</code>) ou Shorts.</div>
                                </li>
                                <li class="d-flex align-items-start gap-2">
                                    <i class="bi bi-check-circle-fill text-info mt-1"></i>
                                    <div><strong>Vimeo:</strong> Cole a URL simples do vídeo (<code>vimeo.com/123456</code>).</div>
                                </li>
                                <li class="d-flex align-items-start gap-2">
                                    <i class="bi bi-check-circle-fill text-primary mt-1"></i>
                                    <div><strong>MP4 Local:</strong> Arquivos até 100MB são processados com preview e miniatura automática.</div>
                                </li>
                                <li class="d-flex align-items-start gap-2">
                                    <i class="bi bi-code-slash text-secondary mt-1"></i>
                                    <div><strong>Código Iframe:</strong> Tags <code>&lt;iframe&gt;</code> completas também são aceitas e renderizadas de forma 100% responsiva.</div>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </form>

    </div>
</div>

<!-- ======================================================= -->
<!-- Scripts JavaScript Integrados                           -->
<!-- ======================================================= -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    // 1. Contador de Caracteres do Título
    const inputTitulo = document.getElementById('video_title');
    const counterEl   = document.getElementById('charCounter');
    
    function atualizarContador() {
        if (!inputTitulo || !counterEl) return;
        const len = inputTitulo.value.length;
        counterEl.textContent = `${len}/150 caracteres`;
        if (len > 130) {
            counterEl.className = 'text-warning small fw-bold';
        } else {
            counterEl.className = 'text-body-secondary small fw-normal';
        }
    }
    if (inputTitulo) {
        inputTitulo.addEventListener('input', atualizarContador);
        atualizarContador();
    }

    // 2. Controle do Switch de Status
    const statusSwitch = document.getElementById('statusSwitch');
    const statusInput  = document.getElementById('statusInput');
    const statusDesc   = document.getElementById('statusLabelDesc');
    const kpiText      = document.getElementById('kpiStatusText');
    const kpiIcon      = document.getElementById('kpiStatusIcon');

    if (statusSwitch && statusInput) {
        statusSwitch.addEventListener('change', function () {
            if (this.checked) {
                statusInput.value = 'Active';
                if (statusDesc) statusDesc.textContent = 'Exibido publicamente no portal';
                if (kpiText) {
                    kpiText.textContent = 'Ativo / Visível';
                    kpiText.className = 'mb-0 fw-bold text-success';
                }
                if (kpiIcon) {
                    kpiIcon.className = 'kpi-icon-circle bg-success-subtle text-success';
                    kpiIcon.innerHTML = '<i class="bi bi-check-circle fs-4"></i>';
                }
            } else {
                statusInput.value = 'Inactive';
                if (statusDesc) statusDesc.textContent = 'Oculto para os visitantes';
                if (kpiText) {
                    kpiText.textContent = 'Oculto / Pausado';
                    kpiText.className = 'mb-0 fw-bold text-warning';
                }
                if (kpiIcon) {
                    kpiIcon.className = 'kpi-icon-circle bg-warning-subtle text-warning';
                    kpiIcon.innerHTML = '<i class="bi bi-eye-slash fs-4"></i>';
                }
            }
        });
    }

    // 3. Controle das Abas de Origem de Vídeo
    const sourceTypeInput = document.getElementById('video_source_type');
    const tabUrlBtn       = document.getElementById('tab-url-btn');
    const tabUploadBtn    = document.getElementById('tab-upload-btn');
    const badgeFormato    = document.getElementById('badgeFormatoPreview');
    const playerContainer = document.getElementById('playerContainer');
    const inputIframe     = document.getElementById('video_iframe');
    const inputFile       = document.getElementById('video_file');
    const btnClearFile    = document.getElementById('btnClearVideoFile');
    const thumbHidden     = document.getElementById('video_thumb_base64');

    if (tabUrlBtn) {
        tabUrlBtn.addEventListener('shown.bs.tab', function () {
            if (sourceTypeInput) sourceTypeInput.value = 'url';
            if (badgeFormato) badgeFormato.textContent = 'PLAYER EXTERNO';
            renderizarPreviewUrl();
        });
    }

    if (tabUploadBtn) {
        tabUploadBtn.addEventListener('shown.bs.tab', function () {
            if (sourceTypeInput) sourceTypeInput.value = 'upload';
            if (badgeFormato) badgeFormato.textContent = 'MP4 LOCAL';
            if (inputFile && inputFile.files && inputFile.files[0]) {
                carregarPreviewArquivoLocal(inputFile.files[0]);
            }
        });
    }

    // 4. Renderização Dinâmica do Player para URLs (YouTube, Vimeo, Iframe)
    function extrairEmbedHtml(valor) {
        if (!valor || !valor.trim()) {
            return '<div class="text-body-secondary small text-center p-4"><i class="bi bi-play-circle fs-1 d-block mb-2 text-body-tertiary"></i>Insira um link do YouTube, Vimeo ou código &lt;iframe&gt; para visualizar o player.</div>';
        }

        const texto = valor.trim();

        // Se já for uma tag <iframe>
        if (texto.toLowerCase().includes('<iframe')) {
            // Garante classes responsivas e segurança básica
            return texto.replace(/<iframe/i, '<iframe class="w-100 h-100 border-0"');
        }

        // YouTube: Links comuns, curtos ou Shorts
        const ytRegex = /(?:youtube(?:-nocookie)?\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/|youtube\.com\/shorts\/)([^"&?\/ ]{11})/i;
        const ytMatch = texto.match(ytRegex);
        if (ytMatch && ytMatch[1]) {
            const videoId = ytMatch[1];
            return `<iframe class="w-100 h-100 border-0" src="https://www.youtube.com/embed/${videoId}?rel=0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>`;
        }

        // Vimeo
        const vimeoRegex = /(?:vimeo\.com\/(?:channels\/(?:\w+\/)?|groups\/([^\/]*)\/videos\/|album\/(\d+)\/video\/|video\/|))(\d+)/i;
        const vimeoMatch = texto.match(vimeoRegex);
        if (vimeoMatch && vimeoMatch[3]) {
            const vimeoId = vimeoMatch[3];
            return `<iframe class="w-100 h-100 border-0" src="https://player.vimeo.com/video/${vimeoId}" allow="autoplay; fullscreen; picture-in-picture" allowfullscreen></iframe>`;
        }

        // Caso seja URL direta terminada em .mp4
        if (texto.toLowerCase().endsWith('.mp4')) {
            return `<video controls class="w-100 h-100"><source src="${texto}" type="video/mp4">Seu navegador não suporta este formato.</video>`;
        }

        return `<div class="text-warning small text-center p-4"><i class="bi bi-exclamation-triangle fs-1 d-block mb-2"></i>Link não reconhecido como YouTube ou Vimeo. Tente colar o código &lt;iframe&gt; completo fornecido pela plataforma.</div>`;
    }

    function renderizarPreviewUrl() {
        if (!playerContainer || !inputIframe) return;
        playerContainer.innerHTML = extrairEmbedHtml(inputIframe.value);
    }

    const btnTestar = document.getElementById('btnTestarLink');
    if (btnTestar) {
        btnTestar.addEventListener('click', renderizarPreviewUrl);
    }
    if (inputIframe) {
        inputIframe.addEventListener('blur', renderizarPreviewUrl);
    }

    // 5. Preview e Captura de Frame do Arquivo MP4 Local
    function carregarPreviewArquivoLocal(file) {
        if (!file || !playerContainer) return;

        if (file.type !== 'video/mp4' && !file.name.toLowerCase().endsWith('.mp4')) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'warning',
                    title: 'Formato Inválido',
                    text: 'Selecione exclusivamente um arquivo de vídeo com extensão .MP4.'
                });
            }
            if (inputFile) inputFile.value = '';
            return;
        }

        const objectUrl = URL.createObjectURL(file);
        playerContainer.innerHTML = `
            <video controls class="w-100 h-100" id="previewLocalVideo" preload="metadata">
                <source src="${objectUrl}" type="video/mp4">
                Seu navegador não suporta reprodução direta de vídeos MP4.
            </video>
        `;

        // Captura automática de thumbnail via Canvas após carregar metadados
        const videoEl = document.getElementById('previewLocalVideo');
        if (videoEl) {
            videoEl.addEventListener('loadeddata', function () {
                // Posiciona no segundo 1 para evitar tela preta
                if (videoEl.duration > 1) {
                    videoEl.currentTime = 1;
                }
            });

            videoEl.addEventListener('seeked', function () {
                try {
                    const canvas = document.createElement('canvas');
                    canvas.width = 640;
                    canvas.height = 360;
                    const ctx = canvas.getContext('2d');
                    ctx.drawImage(videoEl, 0, 0, canvas.width, canvas.height);
                    const dataUrl = canvas.toDataURL('image/jpeg', 0.85);
                    if (thumbHidden) thumbHidden.value = dataUrl;
                } catch (e) {
                    console.log('Thumbnail canvas notice:', e);
                }
            });
        }
    }

    if (inputFile) {
        inputFile.addEventListener('change', function () {
            if (this.files && this.files[0]) {
                carregarPreviewArquivoLocal(this.files[0]);
            }
        });
    }

    if (btnClearFile) {
        btnClearFile.addEventListener('click', function () {
            if (inputFile) inputFile.value = '';
            if (thumbHidden) thumbHidden.value = '';
            if (sourceTypeInput && sourceTypeInput.value === 'upload') {
                // Restaura o vídeo original se houver
                <?php if ($is_current_local_mp4): ?>
                    playerContainer.innerHTML = `
                        <video controls class="w-100 h-100" id="previewLocalVideo" preload="metadata">
                            <source src="<?= htmlspecialchars($video_iframe, ENT_QUOTES, 'UTF-8'); ?>" type="video/mp4">
                            Seu navegador não suporta a tag de vídeo HTML5.
                        </video>
                    `;
                <?php else: ?>
                    playerContainer.innerHTML = '<div class="text-body-secondary small text-center p-4">Nenhum arquivo de vídeo selecionado.</div>';
                <?php endif; ?>
            }
        });
    }

    // 6. Submissão Segura via AJAX com Barra de Progresso e SweetAlert2
    const formVideo = document.getElementById('formVideoEdit');
    const btnSalvar = document.getElementById('btnSalvarVideo');
    const progressBar = document.getElementById('uploadProgressBar');
    const progressIndicator = document.getElementById('uploadProgressIndicator');
    const progressText = document.getElementById('uploadProgressText');

    if (formVideo) {
        formVideo.addEventListener('submit', function (e) {
            e.preventDefault();

            // Validações no frontend
            const tituloVal = (inputTitulo ? inputTitulo.value.trim() : '');
            const catVal = document.getElementById('v_category_id').value;
            const sourceType = sourceTypeInput.value;

            if (!tituloVal) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Título Obrigatório',
                        text: 'Por favor, informe um título descritivo para o vídeo.'
                    });
                }
                if (inputTitulo) inputTitulo.focus();
                return;
            }

            if (!catVal || parseInt(catVal, 10) <= 0) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Categoria Obrigatória',
                        text: 'Por favor, selecione uma categoria para organizar o vídeo.'
                    });
                }
                return;
            }

            if (sourceType === 'url') {
                const iframeVal = (inputIframe ? inputIframe.value.trim() : '');
                if (!iframeVal) {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Link ou Embed Obrigatório',
                            text: 'Insira o link do YouTube, Vimeo ou código <iframe> de incorporação.'
                        });
                    }
                    if (inputIframe) inputIframe.focus();
                    return;
                }
            }

            // Prepara envio via FormData
            const formData = new FormData(formVideo);
            formData.append('action', 'edit_video_ajax');

            // Feedback visual no botão
            const btnOriginalHtml = btnSalvar.innerHTML;
            btnSalvar.disabled = true;
            btnSalvar.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Salvando alterações...';

            // Requisição com XMLHttpRequest para acompanhar progresso de envio de vídeo
            const xhr = new XMLHttpRequest();
            xhr.open('POST', 'video-edit.php?id=<?= $id; ?>', true);
            xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');

            // Monitor de progresso
            if (xhr.upload && inputFile && inputFile.files && inputFile.files.length > 0) {
                progressBar.classList.remove('d-none');
                progressText.classList.remove('d-none');

                xhr.upload.addEventListener('progress', function (event) {
                    if (event.lengthComputable) {
                        const percent = Math.round((event.loaded / event.total) * 100);
                        progressIndicator.style.width = percent + '%';
                        progressIndicator.setAttribute('aria-valuenow', percent);
                        progressText.textContent = `Enviando vídeo para o servidor: ${percent}%`;
                    }
                });
            }

            xhr.onload = function () {
                btnSalvar.disabled = false;
                btnSalvar.innerHTML = btnOriginalHtml;
                progressBar.classList.add('d-none');
                progressText.classList.add('d-none');

                if (xhr.status >= 200 && xhr.status < 300) {
                    try {
                        const resp = JSON.parse(xhr.responseText);
                        if (resp.status === 'success') {
                            if (typeof Swal !== 'undefined') {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Vídeo Atualizado!',
                                    text: resp.message || 'As alterações foram salvas com sucesso.',
                                    timer: 1800,
                                    showConfirmButton: false
                                }).then(() => {
                                    window.location.href = 'video.php';
                                });
                            } else {
                                window.location.href = 'video.php';
                            }
                        } else {
                            if (typeof Swal !== 'undefined') {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Erro ao Salvar',
                                    html: resp.message || 'Não foi possível salvar as alterações.'
                                });
                            }
                        }
                    } catch (err) {
                        console.error('Resposta não-JSON recebida:', xhr.responseText);
                        window.location.href = 'video.php';
                    }
                } else {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'error',
                            title: 'Erro de Servidor',
                            text: 'Ocorreu um erro HTTP ' + xhr.status + ' ao processar a solicitação.'
                        });
                    }
                }
            };

            xhr.onerror = function () {
                btnSalvar.disabled = false;
                btnSalvar.innerHTML = btnOriginalHtml;
                progressBar.classList.add('d-none');
                progressText.classList.add('d-none');
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'error',
                        title: 'Falha de Conexão',
                        text: 'Não foi possível conectar ao servidor. Verifique sua conexão e tente novamente.'
                    });
                }
            };

            xhr.send(formData);
        });
    }

    // 7. Limpeza rigorosa de tooltips
    if (typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
        document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => {
            new bootstrap.Tooltip(el, { trigger: 'hover', boundary: 'window' });
            el.addEventListener('mouseleave', () => {
                const tip = bootstrap.Tooltip.getInstance(el);
                if (tip) tip.hide();
            });
            el.addEventListener('click', () => {
                const tip = bootstrap.Tooltip.getInstance(el);
                if (tip) tip.hide();
            });
        });
    }
});
</script>

<?php require_once('footer.php'); ?>
