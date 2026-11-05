<?php
require_once('header.php');

// 1. Carrega todas as categorias de fotos disponíveis
$stmt_cats = $pdo->prepare("SELECT p_category_id, p_category_name, status FROM tbl_category_photo ORDER BY p_category_name ASC");
$stmt_cats->execute();
$categorias = $stmt_cats->fetchAll(PDO::FETCH_ASSOC);

// Métricas rápidas para auxílio visual
$total_categorias = count($categorias);
$stmt_total_fotos = $pdo->query("SELECT COUNT(*) FROM tbl_photo");
$total_fotos_existentes = (int)$stmt_total_fotos->fetchColumn();

// 2. Processamento do Envio (Suporte completo a AJAX e POST tradicional)
$is_ajax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
           || (isset($_POST['action']) && $_POST['action'] === 'add_photo_ajax');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && (isset($_POST['form1']) || $is_ajax)) {
    $photo_caption = trim($_POST['photo_caption'] ?? '');
    $p_category_id = (int)($_POST['p_category_id'] ?? 0);
    $status        = trim($_POST['status'] ?? 'Active');

    $valid = 1;
    $erros = [];

    // Validação de Legenda / Título
    if (empty($photo_caption)) {
        $valid = 0;
        $erros[] = 'Informe um título ou legenda para a foto.';
    }

    // Validação da Categoria
    if ($p_category_id <= 0) {
        $valid = 0;
        $erros[] = 'Selecione uma categoria de fotos válida.';
    }

    // Validação do Arquivo de Imagem
    if (!isset($_FILES['photo']) || empty($_FILES['photo']['name'])) {
        $valid = 0;
        $erros[] = 'Selecione um arquivo de imagem para enviar.';
    } else {
        $file_name = $_FILES['photo']['name'];
        $file_tmp  = $_FILES['photo']['tmp_name'];
        $file_size = $_FILES['photo']['size'];
        $file_err  = $_FILES['photo']['error'];

        if ($file_err !== UPLOAD_ERR_OK) {
            $valid = 0;
            $erros[] = 'Ocorreu um erro no upload da imagem (Código: ' . $file_err . ').';
        } else {
            $ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            $ext_permitidas = ['jpg', 'jpeg', 'png', 'webp', 'gif'];

            if (!in_array($ext, $ext_permitidas)) {
                $valid = 0;
                $erros[] = 'Formato de imagem inválido. Formatos suportados: JPG, JPEG, PNG, WEBP e GIF.';
            }

            // Limite de 15MB
            if ($file_size > 15 * 1024 * 1024) {
                $valid = 0;
                $erros[] = 'A imagem é muito pesada (máximo de 15MB permitido).';
            }
        }
    }

    if (!in_array($status, ['Active', 'Inactive'])) {
        $status = 'Active';
    }

    if ($valid === 1) {
        // Gera nome de arquivo único e seguro
        $final_name = 'photo-' . time() . '-' . mt_rand(1000, 9999) . '.' . $ext;
        $destino = __DIR__ . '/../assets/uploads/' . $final_name;

        if (move_uploaded_file($file_tmp, $destino)) {
            // Salva registro no banco de dados com status ativo/inativo
            $stmt_ins = $pdo->prepare("INSERT INTO tbl_photo (photo_caption, photo_name, p_category_id, status) VALUES (?, ?, ?, ?)");
            $stmt_ins->execute([$photo_caption, $final_name, $p_category_id, $status]);
            $novo_id = $pdo->lastInsertId();

            $msg_sucesso = 'Foto cadastrada com sucesso no acervo!';

            if ($is_ajax) {
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode([
                    'status'    => 'success',
                    'message'   => $msg_sucesso,
                    'photo_id'  => $novo_id,
                    'file_name' => $final_name
                ]);
                exit;
            }

            $_SESSION['sucesso'] = $msg_sucesso;
            header('location: photo.php');
            exit;
        } else {
            $erros[] = 'Falha ao salvar a imagem no diretório do servidor. Verifique permissões de pasta.';
        }
    }

    // Se houve erros
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

// Categoria pré-selecionada caso venha via GET (?cat=X)
$pre_cat_id = (int)($_GET['cat'] ?? 0);
?>

<!-- Cabeçalho da Página -->
<div class="app-content-header pb-2 mb-3 border-bottom">
    <div class="container-fluid">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div>
                <h3 class="mb-1 fw-bold">Adicionar Foto à Galeria</h3>
                <ol class="breadcrumb mb-0 small">
                    <li class="breadcrumb-item"><a href="index.php"><i class="bi bi-house-door"></i> Início</a></li>
                    <li class="breadcrumb-item"><a href="photo.php">Multimídia</a></li>
                    <li class="breadcrumb-item"><a href="photo.php">Galeria de Fotos</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Adicionar Foto</li>
                </ol>
            </div>
            <div class="d-flex flex-wrap align-items-center gap-2">
                <a href="<?= BASE_URL; ?>page.php?slug=fotos" target="_blank" rel="noopener" class="btn btn-outline-info btn-sm shadow-xs" title="Visualizar galeria de fotos no site público">
                    <i class="bi bi-box-arrow-up-right me-1"></i> Ver no Site
                </a>
                <a href="photo-category.php" class="btn btn-outline-secondary btn-sm shadow-xs" title="Gerenciar categorias de fotos">
                    <i class="bi bi-tags me-1"></i> Categorias
                </a>
                <a href="photo.php" class="btn btn-outline-primary btn-sm shadow-xs">
                    <i class="bi bi-images me-1"></i> Ver Listagem
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

        <!-- Mini Cards Informativos do Acervo -->
        <div class="row g-3 mb-4">
            <div class="col-12 col-sm-6 col-xl-4">
                <div class="card shadow-sm border-0 border-start border-primary border-4 h-100 bg-body">
                    <div class="card-body d-flex align-items-center justify-content-between py-3">
                        <div>
                            <div class="text-body-secondary small text-uppercase fw-bold">Total de Fotos no Acervo</div>
                            <div class="fs-4 fw-bold text-body mt-1"><?= $total_fotos_existentes; ?> fotos</div>
                        </div>
                        <div class="kpi-icon-circle bg-primary-subtle text-primary rounded-circle">
                            <i class="bi bi-camera-fill"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-xl-4">
                <div class="card shadow-sm border-0 border-start border-info border-4 h-100 bg-body">
                    <div class="card-body d-flex align-items-center justify-content-between py-3">
                        <div>
                            <div class="text-body-secondary small text-uppercase fw-bold">Categorias Disponíveis</div>
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
                            <div class="text-body-secondary small text-uppercase fw-bold">Formatos Aceitos</div>
                            <div class="mt-1 small">
                                <span class="badge bg-secondary-subtle text-secondary border me-1">WEBP</span>
                                <span class="badge bg-secondary-subtle text-secondary border me-1">JPG</span>
                                <span class="badge bg-secondary-subtle text-secondary border me-1">PNG</span>
                                <span class="badge bg-secondary-subtle text-secondary border">GIF</span>
                            </div>
                        </div>
                        <div class="kpi-icon-circle bg-success-subtle text-success rounded-circle">
                            <i class="bi bi-file-earmark-image-fill"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Formulário de Envio de Nova Foto -->
        <div class="row">
            <div class="col-12 col-xl-8">
                <div class="card shadow-sm border-0 bg-body mb-4">
                    <div class="card-header bg-body-tertiary border-bottom border-secondary-subtle py-3 d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-primary rounded-pill p-2"><i class="bi bi-cloud-arrow-up-fill"></i></span>
                            <div>
                                <h5 class="card-title fw-bold text-body mb-0">Nova Foto para a Galeria</h5>
                                <small class="text-body-secondary">Selecione uma imagem de alta resolução e defina sua categoria</small>
                            </div>
                        </div>
                    </div>

                    <div class="card-body p-4">
                        <form action="" method="post" enctype="multipart/form-data" id="formPhotoAdd" novalidate>
                            <input type="hidden" name="form1" value="1">

                            <!-- Campo: Título / Legenda da Foto -->
                            <div class="mb-4">
                                <label for="photo_caption" class="form-label fw-bold text-body d-flex align-items-center justify-content-between">
                                    <span>
                                        <i class="bi bi-card-text text-primary me-1"></i> Título / Legenda da Foto <span class="text-danger">*</span>
                                    </span>
                                    <small class="text-body-secondary fw-normal" id="charCounter">0/150 caracteres</small>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text bg-body-tertiary text-body-secondary border-secondary-subtle">
                                        <i class="bi bi-type"></i>
                                    </span>
                                    <input type="text" 
                                           class="form-control form-control-lg bg-body text-body border-secondary-subtle" 
                                           id="photo_caption" 
                                           name="photo_caption" 
                                           maxlength="150" 
                                           required 
                                           placeholder="Ex: Sala de Exames Avançados, Recepção Principal, Fachada...">
                                </div>
                                <div class="form-text text-body-secondary small mt-1">
                                    <i class="bi bi-info-circle me-1"></i> Este título será exibido na ampliação da foto no Lightbox e como texto alternativo de acessibilidade.
                                </div>
                            </div>

                            <!-- Campo: Categoria da Foto -->
                            <div class="mb-4">
                                <label for="p_category_id" class="form-label fw-bold text-body">
                                    <i class="bi bi-tag-fill text-primary me-1"></i> Categoria da Foto <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text bg-body-tertiary text-body-secondary border-secondary-subtle">
                                        <i class="bi bi-folder2-open"></i>
                                    </span>
                                    <select class="form-select form-select-lg bg-body text-body border-secondary-subtle" 
                                            id="p_category_id" 
                                            name="p_category_id" 
                                            required>
                                        <option value="">Selecione uma categoria...</option>
                                        <?php foreach ($categorias as $cat): ?>
                                            <option value="<?= $cat['p_category_id']; ?>" <?= ($cat['p_category_id'] == $pre_cat_id) ? 'selected' : ''; ?>>
                                                <?= htmlspecialchars($cat['p_category_name']); ?> <?= ($cat['status'] !== 'Active') ? '(Oculta)' : ''; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <a href="photo-category-add.php" class="btn btn-outline-secondary" title="Cadastrar nova categoria agora">
                                        <i class="bi bi-plus-lg"></i> Nova Categoria
                                    </a>
                                </div>
                                <div class="form-text text-body-secondary small mt-1">
                                    <i class="bi bi-info-circle me-1"></i> Define em qual aba de filtro do site a foto aparecerá.
                                </div>
                            </div>

                            <!-- Zona de Upload com Dropzone & Preview Instantâneo -->
                            <div class="mb-4">
                                <label class="form-label fw-bold text-body">
                                    <i class="bi bi-image text-primary me-1"></i> Arquivo de Imagem <span class="text-danger">*</span>
                                </label>

                                <div class="upload-dropzone p-4 rounded-3 border-2 border-dashed border-secondary-subtle text-center position-relative bg-body-tertiary bg-opacity-50" id="dropzoneArea" style="cursor: pointer; transition: all 0.2s ease;">
                                    <input type="file" 
                                           name="photo" 
                                           id="photoInput" 
                                           class="position-absolute top-0 start-0 w-100 h-100 opacity-0 cursor-pointer" 
                                           accept="image/jpeg,image/png,image/webp,image/gif"
                                           required>
                                    
                                    <!-- Estado Inicial (Sem Arquivo) -->
                                    <div id="dropzoneEmpty" class="py-3">
                                        <div class="mb-3">
                                            <i class="bi bi-cloud-arrow-up display-4 text-primary opacity-75"></i>
                                        </div>
                                        <h6 class="fw-bold text-body mb-1">Arraste e solte sua foto aqui, ou clique para procurar</h6>
                                        <p class="text-body-secondary small mb-0">Formatos aceitos: WEBP, JPG, JPEG, PNG ou GIF &bull; Até 15MB</p>
                                    </div>

                                    <!-- Estado com Imagem Carregada (Preview) -->
                                    <div id="dropzonePreview" class="d-none py-2">
                                        <div class="position-relative d-inline-block shadow-sm rounded-3 overflow-hidden border border-secondary-subtle bg-black" style="max-height: 280px; max-width: 100%;">
                                            <img id="previewImage" src="" alt="Prévia da foto" class="img-fluid rounded-3" style="max-height: 260px; object-fit: contain;">
                                            <button type="button" class="btn btn-danger btn-sm position-absolute top-0 end-0 m-2 rounded-circle shadow" id="btnRemoveFile" title="Remover imagem selecionada" style="width: 32px; height: 32px; padding: 0;">
                                                <i class="bi bi-x-lg"></i>
                                            </button>
                                        </div>
                                        <div class="mt-2 small text-body fw-semibold" id="fileInfo"></div>
                                        <small class="text-primary d-block mt-1"><i class="bi bi-check2-circle me-1"></i>Clique ou arraste outra imagem se desejar substituir</small>
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
                                                        <small class="text-body-secondary">Exibida imediatamente na galeria</small>
                                                    </div>
                                                </div>
                                                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill">
                                                    <i class="bi bi-check-circle-fill me-1"></i>Ativa
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
                                                        <div class="fw-bold text-body">Oculta / Rascunho</div>
                                                        <small class="text-body-secondary">Pausada, você ativa quando quiser</small>
                                                    </div>
                                                </div>
                                                <span class="badge bg-warning-subtle text-warning border border-warning-subtle rounded-pill">
                                                    <i class="bi bi-pause-circle-fill me-1"></i>Oculta
                                                </span>
                                            </div>
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <!-- Barra de Progresso do Upload (Exibida durante o envio AJAX) -->
                            <div class="progress mb-4 d-none" id="uploadProgressBar" style="height: 10px;">
                                <div class="progress-bar progress-bar-striped progress-bar-animated bg-primary" role="progressbar" style="width: 0%;" id="uploadProgressFill"></div>
                            </div>

                            <!-- Botões de Ação -->
                            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 pt-3 border-top border-secondary-subtle">
                                <a href="photo.php" class="btn btn-outline-secondary px-3 shadow-xs">
                                    <i class="bi bi-arrow-left me-1"></i> Cancelar e Voltar
                                </a>
                                <div class="d-flex align-items-center gap-2">
                                    <button type="submit" class="btn btn-primary px-4 shadow-sm" id="btnSalvar">
                                        <i class="bi bi-cloud-arrow-up-fill me-1"></i> Cadastrar Foto
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Coluna Lateral de Dicas e Parâmetros -->
            <div class="col-12 col-xl-4">
                <!-- Card de Boas Práticas de Fotografia -->
                <div class="card shadow-sm border-0 bg-body mb-4">
                    <div class="card-header bg-body-tertiary border-bottom border-secondary-subtle py-3">
                        <h6 class="card-title fw-bold text-body mb-0">
                            <i class="bi bi-aspect-ratio text-primary me-1"></i> Resolução &amp; Enquadramento
                        </h6>
                    </div>
                    <div class="card-body p-3 small text-body-secondary">
                        <p class="mb-2">Para garantir máxima qualidade e harmonia visual na galeria do site:</p>
                        <ul class="mb-0 ps-3 d-flex flex-column gap-2">
                            <li><strong>Proporção ideal:</strong> Formato paisagem (4:3 ou 16:9) funciona perfeitamente em telas de desktop e smartphones.</li>
                            <li><strong>Resolução recomendada:</strong> De 1200x800px a 1920x1080px. O modal Lightbox exibirá a imagem em alta nitidez.</li>
                            <li><strong>Formato WebP:</strong> Se disponível, proporciona menor tempo de carregamento sem perder qualidade.</li>
                        </ul>
                    </div>
                </div>

                <!-- Card de Atalhos Rápidos -->
                <div class="card shadow-sm border-0 bg-body">
                    <div class="card-header bg-body-tertiary border-bottom border-secondary-subtle py-3">
                        <h6 class="card-title fw-bold text-body mb-0">
                            <i class="bi bi-lightning-charge text-warning me-1"></i> Ações Rápidas
                        </h6>
                    </div>
                    <div class="card-body p-3 d-flex flex-column gap-2">
                        <a href="photo-category-add.php" class="btn btn-outline-secondary btn-sm text-start">
                            <i class="bi bi-tag-fill text-primary me-2"></i> Adicionar Nova Categoria de Foto
                        </a>
                        <a href="photo.php" class="btn btn-outline-secondary btn-sm text-start">
                            <i class="bi bi-images text-info me-2"></i> Ver Galeria Completa no Painel
                        </a>
                        <a href="<?= BASE_URL; ?>page.php?slug=fotos" target="_blank" rel="noopener" class="btn btn-outline-secondary btn-sm text-start">
                            <i class="bi bi-box-arrow-up-right text-success me-2"></i> Abrir Galeria Pública no Site
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
    const inputLegenda   = document.getElementById('photo_caption');
    const charCounter    = document.getElementById('charCounter');
    const photoInput     = document.getElementById('photoInput');
    const dropzoneArea   = document.getElementById('dropzoneArea');
    const dropzoneEmpty  = document.getElementById('dropzoneEmpty');
    const dropzonePreview= document.getElementById('dropzonePreview');
    const previewImage   = document.getElementById('previewImage');
    const fileInfo       = document.getElementById('fileInfo');
    const btnRemoveFile  = document.getElementById('btnRemoveFile');
    const form           = document.getElementById('formPhotoAdd');
    const btnSalvar      = document.getElementById('btnSalvar');
    const progressBar    = document.getElementById('uploadProgressBar');
    const progressFill   = document.getElementById('uploadProgressFill');

    // 1. Contador de Caracteres da Legenda
    if (inputLegenda && charCounter) {
        const updateCounter = () => {
            const len = inputLegenda.value.length;
            charCounter.textContent = `${len}/150 caracteres`;
            if (len > 130) {
                charCounter.className = 'text-warning fw-bold small';
            } else {
                charCounter.className = 'text-body-secondary fw-normal small';
            }
        };
        inputLegenda.addEventListener('input', updateCounter);
        updateCounter();
        inputLegenda.focus();
    }

    // 2. Drag & Drop Visual
    ['dragenter', 'dragover'].forEach(eventName => {
        dropzoneArea.addEventListener(eventName, (e) => {
            e.preventDefault();
            e.stopPropagation();
            dropzoneArea.classList.add('dragover');
        });
    });

    ['dragleave', 'drop'].forEach(eventName => {
        dropzoneArea.addEventListener(eventName, (e) => {
            e.preventDefault();
            e.stopPropagation();
            dropzoneArea.classList.remove('dragover');
        });
    });

    // 3. Pré-visualização da Imagem
    function formatBytes(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }

    if (photoInput) {
        photoInput.addEventListener('change', function () {
            const file = this.files[0];
            if (file) {
                if (!file.type.match('image.*')) {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'error',
                            title: 'Arquivo Inválido',
                            text: 'Selecione apenas arquivos de imagem (WEBP, JPG, PNG ou GIF).'
                        });
                    } else {
                        alert('Selecione apenas arquivos de imagem.');
                    }
                    this.value = '';
                    return;
                }

                const reader = new FileReader();
                reader.onload = function (e) {
                    previewImage.src = e.target.result;
                    fileInfo.innerHTML = `<i class="bi bi-file-earmark-image me-1 text-primary"></i> ${file.name} (${formatBytes(file.size)})`;
                    dropzoneEmpty.classList.add('d-none');
                    dropzonePreview.classList.remove('d-none');
                };
                reader.readAsDataURL(file);
            }
        });
    }

    // Botão para remover a imagem selecionada
    if (btnRemoveFile) {
        btnRemoveFile.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
            photoInput.value = '';
            previewImage.src = '';
            fileInfo.textContent = '';
            dropzonePreview.classList.add('d-none');
            dropzoneEmpty.classList.remove('d-none');
        });
    }

    // 4. Efeito Visual nos Cards de Status
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

    // 5. Envio Assíncrono com AJAX e SweetAlert2
    if (form) {
        form.addEventListener('submit', function (e) {
            e.preventDefault();

            const captionVal = inputLegenda.value.trim();
            const catSelect  = document.getElementById('p_category_id');
            const catVal     = catSelect ? catSelect.value : '';
            const hasFile    = photoInput.files && photoInput.files.length > 0;

            if (!captionVal) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Título Obrigatório',
                        text: 'Informe um título ou legenda descritiva para a foto.',
                        confirmButtonText: 'Entendido',
                        customClass: { confirmButton: 'btn btn-primary px-4' },
                        buttonsStyling: false
                    });
                } else {
                    alert('Informe um título para a foto.');
                }
                inputLegenda.focus();
                return;
            }

            if (!catVal) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Categoria Obrigatória',
                        text: 'Selecione uma categoria para organizar a foto na galeria.',
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

            if (!hasFile) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Foto Obrigatória',
                        text: 'Selecione um arquivo de foto para enviar.',
                        confirmButtonText: 'Entendido',
                        customClass: { confirmButton: 'btn btn-primary px-4' },
                        buttonsStyling: false
                    });
                } else {
                    alert('Selecione um arquivo de foto.');
                }
                dropzoneArea.scrollIntoView({ behavior: 'smooth', block: 'center' });
                return;
            }

            // Inicia envio AJAX com acompanhamento de progresso
            const originalBtnHtml = btnSalvar.innerHTML;
            btnSalvar.disabled = true;
            btnSalvar.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Enviando Foto...';
            
            if (progressBar && progressFill) {
                progressBar.classList.remove('d-none');
                progressFill.style.width = '20%';
            }

            const formData = new FormData(form);
            formData.append('action', 'add_photo_ajax');

            const xhr = new XMLHttpRequest();
            xhr.open('POST', 'photo-add.php', true);
            xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');

            // Atualiza barra de progresso do upload
            if (xhr.upload && progressBar && progressFill) {
                xhr.upload.onprogress = function (event) {
                    if (event.lengthComputable) {
                        const percent = Math.round((event.loaded / event.total) * 90);
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
                                title: 'Foto Adicionada!',
                                text: data.message || 'Foto cadastrada com sucesso no acervo.',
                                showCancelButton: true,
                                confirmButtonText: '<i class="bi bi-images me-1"></i> Ver Galeria',
                                cancelButtonText: '<i class="bi bi-plus-lg me-1"></i> Adicionar Mais Uma',
                                customClass: {
                                    confirmButton: 'btn btn-primary px-3 me-2',
                                    cancelButton: 'btn btn-outline-secondary px-3'
                                },
                                buttonsStyling: false
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    window.location.href = 'photo.php';
                                } else {
                                    // Limpa o formulário para adicionar mais uma
                                    form.reset();
                                    dropzonePreview.classList.add('d-none');
                                    dropzoneEmpty.classList.remove('d-none');
                                    inputLegenda.focus();
                                    if (charCounter) charCounter.textContent = '0/150 caracteres';
                                }
                            });
                        } else {
                            window.location.href = 'photo.php';
                        }
                    } else {
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'error',
                                title: 'Atenção',
                                html: data.message || 'Ocorreu um erro ao salvar a foto.',
                                confirmButtonText: 'Corrigir',
                                customClass: { confirmButton: 'btn btn-danger px-4' },
                                buttonsStyling: false
                            });
                        } else {
                            alert(data.message || 'Erro ao enviar a foto.');
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
