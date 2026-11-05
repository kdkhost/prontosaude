<?php
require_once('header.php');

// 1. Carrega as categorias de vídeo
$stmt_cats = $pdo->prepare("SELECT v_category_id, v_category_name, status FROM tbl_category_video ORDER BY v_category_name ASC");
$stmt_cats->execute();
$categorias = $stmt_cats->fetchAll(PDO::FETCH_ASSOC);

// Métricas de suporte
$total_categorias = count($categorias);
$stmt_total_videos = $pdo->query("SELECT COUNT(*) FROM tbl_video");
$total_videos_existentes = (int)$stmt_total_videos->fetchColumn();

// 2. Processamento do Envio (Suporta AJAX e POST tradicional)
$is_ajax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
           || (isset($_POST['action']) && $_POST['action'] === 'add_video_ajax');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && (isset($_POST['form1']) || $is_ajax)) {
    $video_title   = trim($_POST['video_title'] ?? '');
    $video_source  = trim($_POST['video_source_type'] ?? 'url'); // 'url' ou 'upload'
    $video_iframe  = trim($_POST['video_iframe'] ?? '');
    $v_category_id = (int)($_POST['v_category_id'] ?? 0);
    $status        = trim($_POST['status'] ?? 'Active');
    $thumb_data    = $_POST['video_thumb_base64'] ?? ''; // Miniatura capturada via Canvas (para MP4)

    $valid = 1;
    $erros = [];

    // Validação do Título
    if (empty($video_title)) {
        $valid = 0;
        $erros[] = 'Informe um título descritivo para o vídeo.';
    }

    // Validação da Categoria
    if ($v_category_id <= 0) {
        $valid = 0;
        $erros[] = 'Selecione uma categoria de vídeos válida.';
    }

    if (!in_array($status, ['Active', 'Inactive'])) {
        $status = 'Active';
    }

    $final_video_code = '';

    // Processamento do Conteúdo do Vídeo
    if ($video_source === 'upload') {
        // Upload de Arquivo MP4
        if (!isset($_FILES['video_file']) || empty($_FILES['video_file']['name'])) {
            $valid = 0;
            $erros[] = 'Selecione um arquivo de vídeo MP4 para enviar.';
        } else {
            $file_name = $_FILES['video_file']['name'];
            $file_tmp  = $_FILES['video_file']['tmp_name'];
            $file_size = $_FILES['video_file']['size'];
            $file_err  = $_FILES['video_file']['error'];

            if ($file_err !== UPLOAD_ERR_OK) {
                $valid = 0;
                $erros[] = 'Erro durante o upload do arquivo de vídeo (Código: ' . $file_err . ').';
            } else {
                $ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
                if ($ext !== 'mp4') {
                    $valid = 0;
                    $erros[] = 'Formato de vídeo inválido. Por favor, envie um arquivo em formato MP4.';
                }

                // Limite de 100MB
                if ($file_size > 100 * 1024 * 1024) {
                    $valid = 0;
                    $erros[] = 'O arquivo de vídeo excede o limite máximo permitido de 100MB.';
                }
            }

            if ($valid === 1) {
                $base_clean = preg_replace('/[^a-zA-Z0-9_\-]/', '_', pathinfo($file_name, PATHINFO_FILENAME));
                $final_file_name = $base_clean . '_' . time() . '.mp4';
                $dir_videos = __DIR__ . '/../assets/uploads/videos/';
                
                if (!is_dir($dir_videos)) {
                    @mkdir($dir_videos, 0777, true);
                }

                $destino = $dir_videos . $final_file_name;

                if (move_uploaded_file($file_tmp, $destino)) {
                    $final_video_code = '../assets/uploads/videos/' . $final_file_name;

                    // Se enviou miniatura gerada via Canvas, salva em assets/uploads/videos/thumbs/
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
                    $erros[] = 'Falha ao salvar o vídeo no servidor. Verifique permissões de pasta.';
                }
            }
        }
    } else {
        // Link ou Código de Incorporação (YouTube, Vimeo, etc.)
        if (empty($video_iframe)) {
            $valid = 0;
            $erros[] = 'Insira o link do YouTube/Vimeo ou o código &lt;iframe&gt; de incorporação.';
        } else {
            $final_video_code = $video_iframe;
        }
    }

    if ($valid === 1) {
        $stmt_ins = $pdo->prepare("INSERT INTO tbl_video (video_title, video_iframe, v_category_id, status) VALUES (?, ?, ?, ?)");
        $stmt_ins->execute([$video_title, $final_video_code, $v_category_id, $status]);
        $novo_id = $pdo->lastInsertId();

        $msg_sucesso = 'Vídeo adicionado com sucesso ao acervo!';

        if ($is_ajax) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'status'   => 'success',
                'message'  => $msg_sucesso,
                'video_id' => $novo_id
            ]);
            exit;
        }

        $_SESSION['sucesso'] = $msg_sucesso;
        header('location: video.php');
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

// Categoria pré-selecionada caso venha via URL (?cat=X)
$pre_cat_id = (int)($_GET['cat'] ?? 0);
?>

<!-- Cabeçalho da Página -->
<div class="app-content-header pb-2 mb-3 border-bottom">
    <div class="container-fluid">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div>
                <h3 class="mb-1 fw-bold">Adicionar Vídeo</h3>
                <ol class="breadcrumb mb-0 small">
                    <li class="breadcrumb-item"><a href="index.php"><i class="bi bi-house-door"></i> Início</a></li>
                    <li class="breadcrumb-item"><a href="video.php">Multimídia</a></li>
                    <li class="breadcrumb-item"><a href="video.php">Galeria de Vídeos</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Adicionar Vídeo</li>
                </ol>
            </div>
            <div class="d-flex flex-wrap align-items-center gap-2">
                <a href="<?= BASE_URL; ?>page.php?slug=videos" target="_blank" rel="noopener" class="btn btn-outline-info btn-sm shadow-xs" title="Ver galeria de vídeos no site público">
                    <i class="bi bi-box-arrow-up-right me-1"></i> Ver no Site
                </a>
                <a href="video-category.php" class="btn btn-outline-secondary btn-sm shadow-xs" title="Gerenciar categorias de vídeos">
                    <i class="bi bi-tags me-1"></i> Categorias
                </a>
                <a href="video.php" class="btn btn-outline-primary btn-sm shadow-xs">
                    <i class="bi bi-camera-reels me-1"></i> Ver Listagem
                </a>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">

        <!-- Mensagens Tradicionais de Sessão (Fallback) -->
        <?php if (!empty($error_message)): ?>
            <div class="alert alert-danger alert-dismissible fade show shadow-xs mb-4" role="alert">
                <div class="d-flex align-items-center">
                    <i class="bi bi-exclamation-triangle-fill fs-5 me-2"></i>
                    <div><?= $error_message; ?></div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
            </div>
        <?php endif; ?>

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

        <!-- Cards de Indicadores (KPIs) com Adaptação Completa Dark/Light -->
        <div class="row g-3 mb-4">
            <div class="col-12 col-sm-6 col-xl-4">
                <div class="card shadow-sm border-0 border-start border-primary border-4 h-100 bg-body">
                    <div class="card-body d-flex align-items-center justify-content-between py-3">
                        <div>
                            <div class="text-body-secondary small text-uppercase fw-bold">Vídeos no Acervo</div>
                            <div class="fs-4 fw-bold text-body mt-1"><?= $total_videos_existentes; ?> vídeos</div>
                        </div>
                        <div class="kpi-icon-circle bg-primary-subtle text-primary rounded-circle">
                            <i class="bi bi-camera-reels-fill"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-xl-4">
                <div class="card shadow-sm border-0 border-start border-info border-4 h-100 bg-body">
                    <div class="card-body d-flex align-items-center justify-content-between py-3">
                        <div>
                            <div class="text-body-secondary small text-uppercase fw-bold">Categorias Ativas</div>
                            <div class="fs-4 fw-bold text-info mt-1"><?= $total_categorias; ?> categorias</div>
                        </div>
                        <div class="kpi-icon-circle bg-info-subtle text-info rounded-circle">
                            <i class="bi bi-tags-fill"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-xl-4">
                <div class="card shadow-sm border-0 border-start border-success border-4 h-100 bg-body">
                    <div class="card-body d-flex align-items-center justify-content-between py-3">
                        <div>
                            <div class="text-body-secondary small text-uppercase fw-bold">Fontes Suportadas</div>
                            <div class="mt-1 small">
                                <span class="badge bg-danger-subtle text-danger border me-1"><i class="bi bi-youtube me-1"></i>YouTube</span>
                                <span class="badge bg-info-subtle text-info border me-1"><i class="bi bi-vimeo me-1"></i>Vimeo</span>
                                <span class="badge bg-primary-subtle text-primary border"><i class="bi bi-file-earmark-play me-1"></i>MP4 Direto</span>
                            </div>
                        </div>
                        <div class="kpi-icon-circle bg-success-subtle text-success rounded-circle">
                            <i class="bi bi-play-btn-fill"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Formulário de Cadastro de Vídeo -->
        <div class="row">
            <div class="col-12 col-xl-8">
                <div class="card shadow-sm border-0 bg-body mb-4">
                    <div class="card-header bg-body-tertiary border-bottom border-secondary-subtle py-3 d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-primary rounded-pill p-2"><i class="bi bi-camera-video-fill"></i></span>
                            <div>
                                <h5 class="card-title fw-bold text-body mb-0">Novo Conteúdo Audiovisual</h5>
                                <small class="text-body-secondary">Adicione depoimentos, procedimentos ou vídeos institucionais</small>
                            </div>
                        </div>
                    </div>

                    <div class="card-body p-4">
                        <form action="" method="post" enctype="multipart/form-data" id="formVideoAdd" novalidate>
                            <input type="hidden" name="form1" value="1">
                            <input type="hidden" name="video_source_type" id="video_source_type" value="url">
                            <input type="hidden" name="video_thumb_base64" id="video_thumb_base64" value="">

                            <!-- Campo: Título do Vídeo -->
                            <div class="mb-4">
                                <label for="video_title" class="form-label fw-bold text-body d-flex align-items-center justify-content-between">
                                    <span>
                                        <i class="bi bi-type text-primary me-1"></i> Título do Vídeo <span class="text-danger">*</span>
                                    </span>
                                    <small class="text-body-secondary fw-normal" id="charCounter">0/150 caracteres</small>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text bg-body-tertiary text-body-secondary border-secondary-subtle">
                                        <i class="bi bi-film"></i>
                                    </span>
                                    <input type="text" 
                                           class="form-control form-control-lg bg-body text-body border-secondary-subtle" 
                                           id="video_title" 
                                           name="video_title" 
                                           maxlength="150" 
                                           required 
                                           placeholder="Ex: Depoimento Dra. Camila, Tour pela Nova UTI, Cuidados Preventivos...">
                                </div>
                                <div class="form-text text-body-secondary small mt-1">
                                    <i class="bi bi-info-circle me-1"></i> O título identifica o vídeo nas listagens e no player do portal.
                                </div>
                            </div>

                            <!-- Campo: Categoria de Vídeo -->
                            <div class="mb-4">
                                <label for="v_category_id" class="form-label fw-bold text-body">
                                    <i class="bi bi-tag-fill text-primary me-1"></i> Categoria de Vídeo <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text bg-body-tertiary text-body-secondary border-secondary-subtle">
                                        <i class="bi bi-folder2-open"></i>
                                    </span>
                                    <select class="form-select form-select-lg bg-body text-body border-secondary-subtle" 
                                            id="v_category_id" 
                                            name="v_category_id" 
                                            required>
                                        <option value="">Selecione uma categoria de vídeo...</option>
                                        <?php foreach ($categorias as $cat): ?>
                                            <option value="<?= $cat['v_category_id']; ?>" <?= ($cat['v_category_id'] == $pre_cat_id) ? 'selected' : ''; ?>>
                                                <?= htmlspecialchars($cat['v_category_name']); ?> <?= ($cat['status'] !== 'Active') ? '(Oculta)' : ''; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <a href="video-category-add.php" class="btn btn-outline-secondary" title="Cadastrar nova categoria de vídeo">
                                        <i class="bi bi-plus-lg"></i> Nova Categoria
                                    </a>
                                </div>
                            </div>

                            <!-- Abas de Origem da Mídia (Link do YouTube vs Upload de MP4) -->
                            <div class="mb-4">
                                <label class="form-label fw-bold text-body d-block">
                                    <i class="bi bi-hdd-network text-primary me-1"></i> Origem do Vídeo <span class="text-danger">*</span>
                                </label>

                                <ul class="nav nav-pills nav-fill mb-3 p-1 rounded-3 bg-body-tertiary border border-secondary-subtle" id="pillsVideoOrigin" role="tablist">
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link active fw-bold py-2" id="tab-url" data-bs-toggle="pill" data-bs-target="#content-url" type="button" role="tab" aria-selected="true">
                                            <i class="bi bi-youtube text-danger me-1"></i> Link do YouTube / Vimeo / Iframe
                                        </button>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link fw-bold py-2" id="tab-upload" data-bs-toggle="pill" data-bs-target="#content-upload" type="button" role="tab" aria-selected="false">
                                            <i class="bi bi-file-earmark-play text-primary me-1"></i> Enviar Arquivo MP4
                                        </button>
                                    </li>
                                </ul>

                                <div class="tab-content" id="pillsVideoContent">
                                    <!-- Opção 1: Link do YouTube / Iframe -->
                                    <div class="tab-pane fade show active" id="content-url" role="tabpanel">
                                        <div class="mb-2">
                                            <textarea class="form-control bg-body text-body border-secondary-subtle" 
                                                      id="video_iframe" 
                                                      name="video_iframe" 
                                                      rows="3" 
                                                      placeholder="Cole aqui a URL completa do YouTube (ex: https://www.youtube.com/watch?v=dQw4w9WgXcQ) ou o código <iframe> completo."></textarea>
                                        </div>
                                        <div class="form-text text-body-secondary small">
                                            <i class="bi bi-magic me-1 text-primary"></i> Detecta e extrai automaticamente o ID do vídeo do YouTube para gerar miniatura e player em alta definição.
                                        </div>

                                        <!-- Preview Dinâmico do YouTube -->
                                        <div id="youtubePreviewContainer" class="mt-3 p-3 rounded-3 border border-secondary-subtle bg-body-tertiary d-none">
                                            <div class="d-flex align-items-center justify-content-between mb-2">
                                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle">
                                                    <i class="bi bi-youtube me-1"></i> Vídeo Reconhecido
                                                </span>
                                                <small class="text-body-secondary" id="ytVideoIdDisplay"></small>
                                            </div>
                                            <div class="ratio ratio-16x9 rounded-3 overflow-hidden shadow-xs border border-secondary-subtle bg-black">
                                                <img id="ytThumbPreview" src="" alt="Miniatura YouTube" class="w-100 h-100 object-fit-cover">
                                                <div class="position-absolute w-100 h-100 top-0 start-0 d-flex align-items-center justify-content-center bg-dark bg-opacity-25">
                                                    <div class="rounded-circle bg-danger text-white d-flex align-items-center justify-content-center shadow" style="width: 48px; height: 48px;">
                                                        <i class="bi bi-play-fill fs-3"></i>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Opção 2: Upload de Arquivo MP4 Direto -->
                                    <div class="tab-pane fade" id="content-upload" role="tabpanel">
                                        <div class="upload-dropzone p-4 rounded-3 border-2 border-dashed border-secondary-subtle text-center position-relative bg-body-tertiary bg-opacity-50" id="videoDropzoneArea" style="cursor: pointer; transition: all 0.2s ease;">
                                            <input type="file" 
                                                   name="video_file" 
                                                   id="videoFileInput" 
                                                   class="position-absolute top-0 start-0 w-100 h-100 opacity-0 cursor-pointer" 
                                                   accept="video/mp4">
                                            
                                            <!-- Estado Vazio -->
                                            <div id="videoDropzoneEmpty" class="py-3">
                                                <div class="mb-3">
                                                    <i class="bi bi-cloud-arrow-up display-4 text-primary opacity-75"></i>
                                                </div>
                                                <h6 class="fw-bold text-body mb-1">Arraste seu arquivo de vídeo MP4 aqui, ou clique para procurar</h6>
                                                <p class="text-body-secondary small mb-0">Formato aceito: .MP4 &bull; Até 100MB</p>
                                            </div>

                                            <!-- Estado com Vídeo Carregado (Preview) -->
                                            <div id="videoDropzonePreview" class="d-none py-2">
                                                <div class="ratio ratio-16x9 rounded-3 overflow-hidden shadow-sm border border-secondary-subtle bg-black mx-auto" style="max-width: 480px;">
                                                    <video id="mp4PreviewPlayer" src="" controls class="w-100 h-100" preload="metadata"></video>
                                                </div>
                                                <div class="mt-2 small text-body fw-semibold" id="mp4FileInfo"></div>
                                                <div class="mt-1 small text-success">
                                                    <i class="bi bi-camera-fill me-1"></i> Miniatura oficial gerada automaticamente a partir do 1º segundo
                                                </div>
                                                <button type="button" class="btn btn-outline-danger btn-sm mt-2" id="btnRemoveMp4">
                                                    <i class="bi bi-x-circle me-1"></i> Remover Vídeo
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Campo: Status de Visibilidade Inicial -->
                            <div class="mb-4">
                                <label class="form-label fw-bold text-body d-block">
                                    <i class="bi bi-toggle-on text-primary me-1"></i> Visibilidade Inicial no Portal
                                </label>
                                
                                <div class="row g-3">
                                    <div class="col-12 col-sm-6">
                                        <label class="card h-100 p-3 border border-primary bg-primary-subtle bg-opacity-10 rounded-3 radio-status-card" style="cursor: pointer;">
                                            <div class="d-flex align-items-center justify-content-between">
                                                <div class="d-flex align-items-center gap-3">
                                                    <input class="form-check-input mt-0" type="radio" name="status" id="statusActive" value="Active" checked>
                                                    <div>
                                                        <div class="fw-bold text-body">Visível no Site</div>
                                                        <small class="text-body-secondary">Exibido imediatamente na galeria</small>
                                                    </div>
                                                </div>
                                                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill">
                                                    <i class="bi bi-check-circle-fill me-1"></i>Ativo
                                                </span>
                                            </div>
                                        </label>
                                    </div>

                                    <div class="col-12 col-sm-6">
                                        <label class="card h-100 p-3 border border-secondary-subtle bg-body rounded-3 radio-status-card" style="cursor: pointer;">
                                            <div class="d-flex align-items-center justify-content-between">
                                                <div class="d-flex align-items-center gap-3">
                                                    <input class="form-check-input mt-0" type="radio" name="status" id="statusInactive" value="Inactive">
                                                    <div>
                                                        <div class="fw-bold text-body">Oculto / Pausado</div>
                                                        <small class="text-body-secondary">Pausado, você ativa quando quiser</small>
                                                    </div>
                                                </div>
                                                <span class="badge bg-warning-subtle text-warning border border-warning-subtle rounded-pill">
                                                    <i class="bi bi-pause-circle-fill me-1"></i>Oculto
                                                </span>
                                            </div>
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <!-- Barra de Progresso de Upload -->
                            <div class="progress mb-4 d-none" id="uploadProgressBar" style="height: 10px;">
                                <div class="progress-bar progress-bar-striped progress-bar-animated bg-primary" role="progressbar" style="width: 0%;" id="uploadProgressFill"></div>
                            </div>

                            <!-- Botões de Ação -->
                            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 pt-3 border-top border-secondary-subtle">
                                <a href="video.php" class="btn btn-outline-secondary px-3 shadow-xs">
                                    <i class="bi bi-arrow-left me-1"></i> Cancelar e Voltar
                                </a>
                                <div class="d-flex align-items-center gap-2">
                                    <button type="submit" class="btn btn-primary px-4 shadow-sm" id="btnSalvar">
                                        <i class="bi bi-cloud-arrow-up-fill me-1"></i> Cadastrar Vídeo
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Coluna Lateral com Informações e Dicas -->
            <div class="col-12 col-xl-4">
                <!-- Card de Boas Práticas de Vídeo -->
                <div class="card shadow-sm border-0 bg-body mb-4">
                    <div class="card-header bg-body-tertiary border-bottom border-secondary-subtle py-3">
                        <h6 class="card-title fw-bold text-body mb-0">
                            <i class="bi bi-lightbulb text-warning me-1"></i> Dicas de Desempenho
                        </h6>
                    </div>
                    <div class="card-body p-3 small text-body-secondary">
                        <ul class="mb-0 ps-3 d-flex flex-column gap-2">
                            <li><strong>YouTube Recomendado:</strong> Hospedar vídeos longos no YouTube proporciona streaming adaptativo ultrarrápido para conexões 4G/5G.</li>
                            <li><strong>Vídeos MP4 Locais:</strong> Ideais para pequenos depoimentos de até 2 minutos e clipes institucionais objetivos.</li>
                            <li><strong>Miniatura Automática:</strong> Nos vídeos MP4, o primeiro frame é capturado e salvo em alta definição automaticamente para carregar instantaneamente.</li>
                        </ul>
                    </div>
                </div>

                <!-- Card de Ações Rápidas -->
                <div class="card shadow-sm border-0 bg-body">
                    <div class="card-header bg-body-tertiary border-bottom border-secondary-subtle py-3">
                        <h6 class="card-title fw-bold text-body mb-0">
                            <i class="bi bi-lightning-charge text-primary me-1"></i> Ações Rápidas
                        </h6>
                    </div>
                    <div class="card-body p-3 d-flex flex-column gap-2">
                        <a href="video-category-add.php" class="btn btn-outline-secondary btn-sm text-start">
                            <i class="bi bi-tag-fill text-primary me-2"></i> Adicionar Nova Categoria de Vídeo
                        </a>
                        <a href="video.php" class="btn btn-outline-secondary btn-sm text-start">
                            <i class="bi bi-camera-reels text-info me-2"></i> Ver Galeria de Vídeos no Painel
                        </a>
                        <a href="<?= BASE_URL; ?>page.php?slug=videos" target="_blank" rel="noopener" class="btn btn-outline-secondary btn-sm text-start">
                            <i class="bi bi-box-arrow-up-right text-success me-2"></i> Abrir Vídeos no Site Público
                        </a>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<style>
.upload-dropzone.dragover {
    border-color: var(--bs-primary) !important;
    background-color: rgba(var(--bs-primary-rgb), 0.1) !important;
}
.radio-status-card {
    transition: all 0.2s ease-in-out;
}
.radio-status-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 10px rgba(0,0,0,0.06);
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const inputTitulo      = document.getElementById('video_title');
    const charCounter      = document.getElementById('charCounter');
    const inputIframe      = document.getElementById('video_iframe');
    const sourceInput      = document.getElementById('video_source_type');
    const thumbBase64Input = document.getElementById('video_thumb_base64');
    const tabUrlBtn        = document.getElementById('tab-url');
    const tabUploadBtn     = document.getElementById('tab-upload');
    const form             = document.getElementById('formVideoAdd');
    const btnSalvar        = document.getElementById('btnSalvar');
    const progressBar      = document.getElementById('uploadProgressBar');
    const progressFill     = document.getElementById('uploadProgressFill');

    // 1. Contador de Caracteres do Título
    if (inputTitulo && charCounter) {
        const updateCounter = () => {
            const len = inputTitulo.value.length;
            charCounter.textContent = `${len}/150 caracteres`;
            if (len > 130) {
                charCounter.className = 'text-warning fw-bold small';
            } else {
                charCounter.className = 'text-body-secondary fw-normal small';
            }
        };
        inputTitulo.addEventListener('input', updateCounter);
        updateCounter();
        inputTitulo.focus();
    }

    // 2. Controle de Abas (URL vs Upload)
    if (tabUrlBtn && tabUploadBtn && sourceInput) {
        tabUrlBtn.addEventListener('shown.bs.tab', () => {
            sourceInput.value = 'url';
        });
        tabUploadBtn.addEventListener('shown.bs.tab', () => {
            sourceInput.value = 'upload';
        });
    }

    // 3. Detecção em Tempo Real de YouTube / Vimeo
    const ytContainer = document.getElementById('youtubePreviewContainer');
    const ytThumb     = document.getElementById('ytThumbPreview');
    const ytIdDisplay = document.getElementById('ytVideoIdDisplay');

    function extractYouTubeId(url) {
        const reg = /(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([\w\-]{11})/i;
        const match = url.match(reg);
        return match ? match[1] : null;
    }

    if (inputIframe) {
        inputIframe.addEventListener('input', function () {
            const val = this.value.trim();
            const ytid = extractYouTubeId(val);

            if (ytid) {
                ytThumb.src = `https://img.youtube.com/vi/${ytid}/mqdefault.jpg`;
                ytIdDisplay.textContent = `ID: ${ytid}`;
                ytContainer.classList.remove('d-none');
            } else {
                ytContainer.classList.add('d-none');
            }
        });
    }

    // 4. Upload de Arquivo MP4 com Drag & Drop e Captura de Frame Canvas
    const videoDropzoneArea = document.getElementById('videoDropzoneArea');
    const videoFileInput    = document.getElementById('videoFileInput');
    const dropzoneEmpty     = document.getElementById('videoDropzoneEmpty');
    const dropzonePreview   = document.getElementById('videoDropzonePreview');
    const mp4Player         = document.getElementById('mp4PreviewPlayer');
    const mp4FileInfo       = document.getElementById('mp4FileInfo');
    const btnRemoveMp4      = document.getElementById('btnRemoveMp4');

    function formatBytes(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }

    // Eventos visuais de Drag & Drop
    ['dragenter', 'dragover'].forEach(eventName => {
        videoDropzoneArea.addEventListener(eventName, (e) => {
            e.preventDefault();
            e.stopPropagation();
            videoDropzoneArea.classList.add('dragover');
        });
    });

    ['dragleave', 'drop'].forEach(eventName => {
        videoDropzoneArea.addEventListener(eventName, (e) => {
            e.preventDefault();
            e.stopPropagation();
            videoDropzoneArea.classList.remove('dragover');
        });
    });

    if (videoFileInput) {
        videoFileInput.addEventListener('change', function () {
            const file = this.files[0];
            if (file) {
                if (!file.type.match('video/mp4') && !file.name.toLowerCase().endsWith('.mp4')) {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'error',
                            title: 'Formato Não Suportado',
                            text: 'Selecione um arquivo de vídeo no formato MP4.'
                        });
                    } else {
                        alert('Selecione um arquivo MP4.');
                    }
                    this.value = '';
                    return;
                }

                const fileUrl = URL.createObjectURL(file);
                mp4Player.src = fileUrl;
                mp4FileInfo.innerHTML = `<i class="bi bi-file-earmark-play-fill text-primary me-1"></i> ${file.name} (${formatBytes(file.size)})`;

                dropzoneEmpty.classList.add('d-none');
                dropzonePreview.classList.remove('d-none');

                // Captura silenciosamente o primeiro frame (segundo 1.0) para gerar o thumbnail
                mp4Player.onloadeddata = function () {
                    mp4Player.currentTime = 1.0;
                };

                mp4Player.onseeked = function () {
                    try {
                        const canvas = document.createElement('canvas');
                        canvas.width = Math.min(mp4Player.videoWidth, 640);
                        canvas.height = Math.round(canvas.width * (mp4Player.videoHeight / mp4Player.videoWidth));
                        const ctx = canvas.getContext('2d');
                        ctx.drawImage(mp4Player, 0, 0, canvas.width, canvas.height);
                        thumbBase64Input.value = canvas.toDataURL('image/jpeg', 0.85);
                    } catch (err) {
                        console.warn('Canvas frame capture error:', err);
                    }
                };
            }
        });
    }

    if (btnRemoveMp4) {
        btnRemoveMp4.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
            videoFileInput.value = '';
            mp4Player.src = '';
            thumbBase64Input.value = '';
            mp4FileInfo.textContent = '';
            dropzonePreview.classList.add('d-none');
            dropzoneEmpty.classList.remove('d-none');
        });
    }

    // 5. Efeito Visual nos Cards de Status
    const radioCards = document.querySelectorAll('.radio-status-card');
    const radioInputs = document.querySelectorAll('input[name="status"]');

    radioInputs.forEach(radio => {
        radio.addEventListener('change', function () {
            radioCards.forEach(c => {
                c.classList.remove('border-primary', 'bg-primary-subtle', 'border-warning', 'bg-warning-subtle', 'bg-opacity-10');
                c.classList.add('bg-body');
            });
            const parentCard = this.closest('.radio-status-card');
            if (this.value === 'Active') {
                parentCard.classList.remove('bg-body');
                parentCard.classList.add('border-primary', 'bg-primary-subtle', 'bg-opacity-10');
            } else {
                parentCard.classList.remove('bg-body');
                parentCard.classList.add('border-warning', 'bg-warning-subtle', 'bg-opacity-10');
            }
        });
    });

    // 6. Envio Assíncrono com AJAX e SweetAlert2
    if (form) {
        form.addEventListener('submit', function (e) {
            e.preventDefault();

            const tituloVal  = inputTitulo.value.trim();
            const catSelect  = document.getElementById('v_category_id');
            const catVal     = catSelect ? catSelect.value : '';
            const sourceType = sourceInput.value;

            if (!tituloVal) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Título Obrigatório',
                        text: 'Informe um título descritivo para o vídeo.',
                        confirmButtonText: 'Entendido',
                        customClass: { confirmButton: 'btn btn-primary px-4' },
                        buttonsStyling: false
                    });
                } else {
                    alert('Informe um título para o vídeo.');
                }
                inputTitulo.focus();
                return;
            }

            if (!catVal) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Categoria Obrigatória',
                        text: 'Selecione uma categoria para classificar o vídeo.',
                        confirmButtonText: 'Entendido',
                        customClass: { confirmButton: 'btn btn-primary px-4' },
                        buttonsStyling: false
                    });
                } else {
                    alert('Selecione uma categoria.');
                }
                if (catSelect) catSelect.focus();
                return;
            }

            if (sourceType === 'url') {
                const iframeVal = inputIframe.value.trim();
                if (!iframeVal) {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Link ou Código Obrigatório',
                            text: 'Cole o link do YouTube/Vimeo ou o código <iframe> de incorporação.',
                            confirmButtonText: 'Entendido',
                            customClass: { confirmButton: 'btn btn-primary px-4' },
                            buttonsStyling: false
                        });
                    } else {
                        alert('Cole o link do vídeo.');
                    }
                    inputIframe.focus();
                    return;
                }
            } else {
                const hasFile = videoFileInput.files && videoFileInput.files.length > 0;
                if (!hasFile) {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Arquivo MP4 Obrigatório',
                            text: 'Selecione um arquivo de vídeo MP4 para enviar.',
                            confirmButtonText: 'Entendido',
                            customClass: { confirmButton: 'btn btn-primary px-4' },
                            buttonsStyling: false
                        });
                    } else {
                        alert('Selecione um arquivo MP4.');
                    }
                    return;
                }
            }

            // Inicia envio AJAX com barra de progresso
            const originalBtnHtml = btnSalvar.innerHTML;
            btnSalvar.disabled = true;
            btnSalvar.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Enviando Vídeo...';

            if (progressBar && progressFill) {
                progressBar.classList.remove('d-none');
                progressFill.style.width = '15%';
            }

            const formData = new FormData(form);
            formData.append('action', 'add_video_ajax');

            const xhr = new XMLHttpRequest();
            xhr.open('POST', 'video-add.php', true);
            xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');

            if (xhr.upload && progressBar && progressFill) {
                xhr.upload.onprogress = function (event) {
                    if (event.lengthComputable) {
                        const percent = Math.round((event.loaded / event.total) * 92);
                        progressFill.style.width = percent + '%';
                    }
                };
            }

            xhr.onload = function () {
                btnSalvar.disabled = false;
                btnSalvar.innerHTML = originalBtnHtml;
                if (progressBar) progressBar.classList.add('d-none');

                try {
                    const data = JSON.parse(xhr.responseText);
                    if (data.status === 'success') {
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'success',
                                title: 'Vídeo Adicionado!',
                                text: data.message || 'Vídeo cadastrado com sucesso no acervo.',
                                showCancelButton: true,
                                confirmButtonText: '<i class="bi bi-camera-reels me-1"></i> Ver Galeria',
                                cancelButtonText: '<i class="bi bi-plus-lg me-1"></i> Adicionar Mais Um',
                                customClass: {
                                    confirmButton: 'btn btn-primary px-3 me-2',
                                    cancelButton: 'btn btn-outline-secondary px-3'
                                },
                                buttonsStyling: false
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    window.location.href = 'video.php';
                                } else {
                                    form.reset();
                                    inputIframe.value = '';
                                    ytContainer.classList.add('d-none');
                                    dropzonePreview.classList.add('d-none');
                                    dropzoneEmpty.classList.remove('d-none');
                                    thumbBase64Input.value = '';
                                    inputTitulo.focus();
                                    if (charCounter) charCounter.textContent = '0/150 caracteres';
                                }
                            });
                        } else {
                            window.location.href = 'video.php';
                        }
                    } else {
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'error',
                                title: 'Atenção',
                                html: data.message || 'Ocorreu um erro ao salvar o vídeo.',
                                confirmButtonText: 'Corrigir',
                                customClass: { confirmButton: 'btn btn-danger px-4' },
                                buttonsStyling: false
                            });
                        } else {
                            alert(data.message || 'Erro ao salvar o vídeo.');
                        }
                    }
                } catch (e) {
                    console.error(e, xhr.responseText);
                    form.submit(); // fallback tradicional
                }
            };

            xhr.onerror = function () {
                btnSalvar.disabled = false;
                btnSalvar.innerHTML = originalBtnHtml;
                if (progressBar) progressBar.classList.add('d-none');
                form.submit();
            };

            xhr.send(formData);
        });
    }
});
</script>

<?php require_once('footer.php'); ?>
