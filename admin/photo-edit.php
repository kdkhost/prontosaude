<?php
require_once('header.php');

// 1. Validação e Carregamento do Registro da Foto
$id = (int)($_REQUEST['id'] ?? 0);

if ($id <= 0) {
    $_SESSION['erro'] = 'Identificador de foto inválido ou ausente.';
    header('Location: photo.php');
    exit;
}

$stmt_foto = $pdo->prepare("SELECT 
                                t1.photo_id, 
                                t1.photo_caption, 
                                t1.photo_name, 
                                t1.p_category_id, 
                                t1.status,
                                t2.p_category_name
                            FROM tbl_photo t1
                            LEFT JOIN tbl_category_photo t2 ON t1.p_category_id = t2.p_category_id
                            WHERE t1.photo_id = ?");
$stmt_foto->execute([$id]);
$foto = $stmt_foto->fetch(PDO::FETCH_ASSOC);

if (!$foto) {
    $_SESSION['erro'] = 'A foto solicitada (# ' . $id . ') não foi encontrada no acervo.';
    header('Location: photo.php');
    exit;
}

// 2. Carrega todas as categorias de fotos disponíveis
$stmt_cats = $pdo->prepare("SELECT p_category_id, p_category_name, status FROM tbl_category_photo ORDER BY p_category_name ASC");
$stmt_cats->execute();
$categorias = $stmt_cats->fetchAll(PDO::FETCH_ASSOC);

// Métricas do acervo para auxílio
$stmt_total = $pdo->query("SELECT COUNT(*) FROM tbl_photo");
$total_fotos_existentes = (int)$stmt_total->fetchColumn();

// 3. Processamento da Atualização (Suporte completo a AJAX e POST tradicional)
$is_ajax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
           || (isset($_POST['action']) && $_POST['action'] === 'edit_photo_ajax');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && (isset($_POST['form1']) || $is_ajax)) {
    $photo_caption = trim($_POST['photo_caption'] ?? '');
    $p_category_id = (int)($_POST['p_category_id'] ?? 0);
    $status        = trim($_POST['status'] ?? 'Active');
    $previous_photo = trim($_POST['previous_photo'] ?? $foto['photo_name']);

    $valid = 1;
    $erros = [];

    // Validação de Legenda / Título
    if (empty($photo_caption)) {
        $valid = 0;
        $erros[] = 'Informe um título ou legenda descritiva para a foto.';
    }

    // Validação da Categoria
    if ($p_category_id <= 0) {
        $valid = 0;
        $erros[] = 'Selecione uma categoria válida para organizar a foto.';
    }

    if (!in_array($status, ['Active', 'Inactive'])) {
        $status = 'Active';
    }

    $novo_arquivo_salvo = false;
    $final_name = $previous_photo;

    // Processamento de Novo Arquivo de Imagem (opcional)
    if (isset($_FILES['photo']) && !empty($_FILES['photo']['name'])) {
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
                $erros[] = 'Formato de imagem inválido. Formatos suportados: WEBP, JPG, JPEG, PNG e GIF.';
            }

            // Limite de 15MB
            if ($file_size > 15 * 1024 * 1024) {
                $valid = 0;
                $erros[] = 'O arquivo selecionado excede o limite máximo permitido de 15MB.';
            }

            if ($valid === 1) {
                $final_name = 'photo-' . $id . '-' . time() . '.' . $ext;
                $dir_uploads = __DIR__ . '/../assets/uploads/';
                
                if (!is_dir($dir_uploads)) {
                    @mkdir($dir_uploads, 0777, true);
                }

                $destino = $dir_uploads . $final_name;

                if (move_uploaded_file($file_tmp, $destino)) {
                    $novo_arquivo_salvo = true;

                    // Remove com segurança a foto anterior se for diferente e existir
                    if (!empty($previous_photo) && $previous_photo !== $final_name) {
                        $locais_antigos = [
                            __DIR__ . '/../assets/uploads/' . $previous_photo,
                            __DIR__ . '/../assets/uploads/photos/' . $previous_photo
                        ];
                        foreach ($locais_antigos as $old_path) {
                            if (file_exists($old_path) && is_file($old_path)) {
                                @unlink($old_path);
                            }
                        }
                    }
                } else {
                    $valid = 0;
                    $erros[] = 'Não foi possível salvar o novo arquivo no servidor. Verifique permissões de pasta.';
                }
            }
        }
    }

    if ($valid === 1) {
        $stmt_upd = $pdo->prepare("UPDATE tbl_photo SET photo_caption = ?, photo_name = ?, p_category_id = ?, status = ? WHERE photo_id = ?");
        $stmt_upd->execute([$photo_caption, $final_name, $p_category_id, $status, $id]);

        $msg_sucesso = 'Foto #' . $id . ' atualizada com sucesso no acervo!';

        if ($is_ajax) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'status'     => 'success',
                'message'    => $msg_sucesso,
                'photo_id'   => $id,
                'file_name'  => $final_name,
                'caption'    => $photo_caption,
                'new_upload' => $novo_arquivo_salvo,
                'img_url'    => BASE_URL . 'assets/uploads/' . $final_name
            ]);
            exit;
        }

        $_SESSION['sucesso'] = $msg_sucesso;
        header('Location: photo-edit.php?id=' . $id);
        exit;
    }

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

// 4. Diagnóstico Físico da Foto Atual
$foto_atual_nome = trim((string)$foto['photo_name']);
$foto_atual_ext  = strtoupper(pathinfo($foto_atual_nome, PATHINFO_EXTENSION)) ?: 'IMG';
$foto_atual_url  = BASE_URL . 'assets/uploads/' . $foto_atual_nome;
$foto_existe     = false;
$foto_tamanho_kb = 0;
$foto_dimensoes  = '';

$caminhos_busca = [
    __DIR__ . '/../assets/uploads/' . $foto_atual_nome,
    __DIR__ . '/../assets/uploads/photos/' . $foto_atual_nome
];

foreach ($caminhos_busca as $caminho) {
    if (file_exists($caminho) && is_file($caminho)) {
        $foto_existe = true;
        $foto_tamanho_kb = round(filesize($caminho) / 1024, 1);
        $img_info = @getimagesize($caminho);
        if ($img_info && isset($img_info[0], $img_info[1])) {
            $foto_dimensoes = $img_info[0] . ' × ' . $img_info[1] . ' px';
        }
        if (strpos($caminho, 'photos') !== false) {
            $foto_atual_url = BASE_URL . 'assets/uploads/photos/' . $foto_atual_nome;
        }
        break;
    }
}
?>

<style>
.upload-dropzone {
    border-style: dashed !important;
    border-width: 2px !important;
    transition: all 0.25s ease-in-out;
}
.upload-dropzone.dragover {
    border-color: var(--bs-primary) !important;
    background-color: rgba(var(--bs-primary-rgb), 0.08) !important;
    transform: scale(1.01);
}
.cursor-pointer {
    cursor: pointer !important;
}
.photo-live-preview-card {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.photo-live-preview-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
}
.thumb-zoom-hover {
    transition: transform 0.3s ease;
}
.thumb-zoom-hover:hover {
    transform: scale(1.03);
}
</style>

<!-- Cabeçalho da Página -->
<div class="app-content-header pb-2 mb-3 border-bottom">
    <div class="container-fluid">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div>
                <h3 class="mb-1 fw-bold">
                    <i class="bi bi-pencil-square text-primary me-2"></i>Editar Foto #<?= $id; ?>
                </h3>
                <ol class="breadcrumb mb-0 small">
                    <li class="breadcrumb-item"><a href="index.php"><i class="bi bi-house-door"></i> Início</a></li>
                    <li class="breadcrumb-item"><a href="photo.php">Multimídia</a></li>
                    <li class="breadcrumb-item"><a href="photo.php">Galeria de Fotos</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Editar Foto #<?= $id; ?></li>
                </ol>
            </div>
            <div class="d-flex flex-wrap align-items-center gap-2">
                <a href="<?= BASE_URL; ?>page.php?slug=fotos" target="_blank" rel="noopener" class="btn btn-outline-info btn-sm shadow-xs" title="Visualizar galeria de fotos no site público">
                    <i class="bi bi-box-arrow-up-right me-1"></i> Ver no Site
                </a>
                <a href="photo-add.php" class="btn btn-outline-primary btn-sm shadow-xs" title="Adicionar nova foto à galeria">
                    <i class="bi bi-plus-lg me-1"></i> Nova Foto
                </a>
                <a href="photo.php" class="btn btn-outline-secondary btn-sm shadow-xs" title="Voltar à listagem do acervo">
                    <i class="bi bi-arrow-left me-1"></i> Voltar ao Acervo
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

        <div class="row g-4">
            <!-- Coluna Principal do Formulário (8 Colunas) -->
            <div class="col-12 col-xl-8">
                <div class="card shadow-sm border-0 bg-body mb-4">
                    <div class="card-header bg-body-tertiary border-bottom border-secondary-subtle py-3 d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-primary rounded-pill p-2"><i class="bi bi-sliders"></i></span>
                            <div>
                                <h5 class="card-title fw-bold text-body mb-0">Parâmetros da Fotografia</h5>
                                <small class="text-body-secondary">Modifique o título, categoria, status de visibilidade ou substitua o arquivo</small>
                            </div>
                        </div>
                        <span class="badge <?= ($foto['status'] === 'Active') ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-warning-subtle text-warning border border-warning-subtle'; ?> rounded-pill px-3 py-1" id="badgeStatusHeader">
                            <i class="bi <?= ($foto['status'] === 'Active') ? 'bi-check-circle-fill' : 'bi-pause-circle-fill'; ?> me-1"></i>
                            <span id="badgeStatusHeaderText"><?= ($foto['status'] === 'Active') ? 'Ativo na Galeria' : 'Oculto no Site'; ?></span>
                        </span>
                    </div>

                    <div class="card-body p-4">
                        <form action="" method="post" enctype="multipart/form-data" id="formPhotoEdit" novalidate>
                            <input type="hidden" name="form1" value="1">
                            <input type="hidden" name="id" value="<?= $id; ?>">
                            <input type="hidden" name="previous_photo" id="previousPhotoInput" value="<?= htmlspecialchars($foto_atual_nome); ?>">

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
                                           value="<?= htmlspecialchars($foto['photo_caption'] ?? ''); ?>"
                                           placeholder="Ex: Sala de Exames Avançados, Recepção Principal, Fachada...">
                                </div>
                                <div class="form-text text-body-secondary small mt-1">
                                    <i class="bi bi-info-circle me-1"></i> Este texto é exibido no Lightbox ao ampliar e atua como atributo alt para indexação SEO e acessibilidade.
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
                                            <option value="<?= $cat['p_category_id']; ?>" 
                                                    data-name="<?= htmlspecialchars($cat['p_category_name']); ?>"
                                                    <?= ($cat['p_category_id'] == $foto['p_category_id']) ? 'selected' : ''; ?>>
                                                <?= htmlspecialchars($cat['p_category_name']); ?> <?= ($cat['status'] !== 'Active') ? '(Oculta)' : ''; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <a href="photo-category.php" class="btn btn-outline-secondary" title="Gerenciar categorias de fotos">
                                        <i class="bi bi-tags"></i> Categorias
                                    </a>
                                </div>
                                <div class="form-text text-body-secondary small mt-1">
                                    <i class="bi bi-info-circle me-1"></i> Define a aba ou ambiente em que esta imagem será agrupada no site público.
                                </div>
                            </div>

                            <!-- Campo: Status de Visibilidade -->
                            <div class="mb-4 p-3 rounded-3 bg-body-tertiary border border-secondary-subtle">
                                <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                                    <div>
                                        <label class="form-label fw-bold text-body mb-0 d-block">
                                            <i class="bi bi-eye-fill text-primary me-1"></i> Visibilidade na Galeria Pública
                                        </label>
                                        <small class="text-body-secondary">Quando inativo, a foto não aparecerá na galeria para os visitantes</small>
                                    </div>
                                    <div class="form-check form-switch form-switch-lg mb-0">
                                        <input class="form-check-input cursor-pointer" 
                                               type="checkbox" 
                                               role="switch" 
                                               id="statusSwitch" 
                                               name="status_switch"
                                               <?= ($foto['status'] === 'Active') ? 'checked' : ''; ?>
                                               style="width: 3rem; height: 1.6rem;">
                                        <input type="hidden" name="status" id="statusHidden" value="<?= htmlspecialchars($foto['status']); ?>">
                                    </div>
                                </div>
                            </div>

                            <!-- Seção: Arquivo de Imagem (Atual e Nova Opção) -->
                            <div class="mb-4">
                                <label class="form-label fw-bold text-body d-flex align-items-center justify-content-between">
                                    <span>
                                        <i class="bi bi-image text-primary me-1"></i> Arquivo da Imagem
                                    </span>
                                    <span class="badge bg-secondary-subtle text-body-secondary border small">
                                        Formato Atual: <?= $foto_atual_ext; ?>
                                    </span>
                                </label>

                                <!-- Visualização da Foto Atual Cadastrada -->
                                <div class="p-3 mb-3 rounded-3 bg-body-tertiary border border-secondary-subtle" id="boxFotoAtual">
                                    <div class="row align-items-center g-3">
                                        <div class="col-12 col-sm-4 col-md-3 text-center">
                                            <div class="position-relative overflow-hidden rounded-3 border border-secondary-subtle bg-black bg-opacity-25 shadow-xs mx-auto" 
                                                 style="width: 140px; aspect-ratio: 4/3; cursor: pointer;"
                                                 onclick="abrirModalVisualizarFoto('<?= addslashes($foto_atual_url); ?>', '<?= addslashes($foto['photo_caption'] ?? ''); ?>')"
                                                 title="Clique para ampliar esta foto em tamanho original">
                                                <img src="<?= $foto_atual_url; ?>" alt="<?= htmlspecialchars($foto['photo_caption'] ?? ''); ?>" class="w-100 h-100 object-fit-cover thumb-zoom-hover" id="imgAtualDisplay">
                                                <div class="position-absolute bottom-0 end-0 m-1">
                                                    <span class="badge bg-dark bg-opacity-75 text-white small" style="font-size: 0.65rem;">
                                                        <i class="bi bi-zoom-in me-1"></i>Ampliar
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-12 col-sm-8 col-md-9">
                                            <div class="d-flex align-items-center gap-2 mb-1">
                                                <i class="bi bi-file-earmark-check text-success fs-5"></i>
                                                <span class="fw-bold text-body font-monospace small" id="lblNomeArquivoAtual"><?= htmlspecialchars($foto_atual_nome); ?></span>
                                            </div>
                                            <div class="text-body-secondary small mb-2">
                                                <?php if ($foto_existe): ?>
                                                    <span class="text-success"><i class="bi bi-check-circle-fill me-1"></i>Arquivo físico presente</span> &bull; 
                                                    <?= $foto_tamanho_kb; ?> KB <?= $foto_dimensoes ? ' &bull; ' . $foto_dimensoes : ''; ?>
                                                <?php else: ?>
                                                    <span class="text-danger"><i class="bi bi-exclamation-triangle-fill me-1"></i>Arquivo não localizado no disco</span>
                                                <?php endif; ?>
                                            </div>
                                            <div class="d-flex flex-wrap gap-2">
                                                <button type="button" class="btn btn-outline-info btn-sm" onclick="abrirModalVisualizarFoto('<?= addslashes($foto_atual_url); ?>', '<?= addslashes($foto['photo_caption'] ?? ''); ?>')">
                                                    <i class="bi bi-arrows-fullscreen me-1"></i> Visualizar em Tela Cheia
                                                </button>
                                                <label for="photoInput" class="btn btn-outline-primary btn-sm cursor-pointer">
                                                    <i class="bi bi-arrow-repeat me-1"></i> Selecionar Nova Imagem
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Zona de Upload Dropzone (Para Substituição Opcional) -->
                                <div class="upload-dropzone p-4 rounded-3 border-2 border-dashed border-secondary-subtle text-center position-relative bg-body-tertiary bg-opacity-50" id="dropzoneArea" style="cursor: pointer;">
                                    <input type="file" 
                                           name="photo" 
                                           id="photoInput" 
                                           class="position-absolute top-0 start-0 w-100 h-100 opacity-0 cursor-pointer" 
                                           accept="image/jpeg,image/png,image/webp,image/gif">
                                    
                                    <!-- Estado Inicial: Substituição Opcional -->
                                    <div id="dropzoneEmpty" class="py-2">
                                        <div class="mb-2">
                                            <i class="bi bi-cloud-arrow-up display-5 text-primary opacity-75"></i>
                                        </div>
                                        <h6 class="fw-bold text-body mb-1">Deseja trocar esta foto? Arraste ou clique para enviar novo arquivo</h6>
                                        <p class="text-body-secondary small mb-0">Deixe em branco para manter a imagem atual &bull; Suporta WEBP, JPG, PNG e GIF (até 15MB)</p>
                                    </div>

                                    <!-- Estado com Novo Arquivo Selecionado (Preview da Nova Foto) -->
                                    <div id="dropzonePreview" class="d-none py-2">
                                        <div class="position-relative d-inline-block mb-3">
                                            <img src="" id="newPhotoPreviewImg" alt="Prévia da Nova Foto" class="img-fluid rounded-3 border border-secondary-subtle shadow-sm" style="max-height: 220px; object-fit: contain;">
                                            <button type="button" class="btn btn-danger btn-sm rounded-circle position-absolute top-0 end-0 translate-middle shadow" id="btnCancelNewPhoto" title="Descartar nova foto e manter atual" style="width: 28px; height: 28px; padding: 0;">
                                                <i class="bi bi-x-lg"></i>
                                            </button>
                                        </div>
                                        <div class="text-body fw-bold" id="newPhotoName">nome-da-foto.jpg</div>
                                        <div class="text-body-secondary small" id="newPhotoSize">0 KB</div>
                                        <div class="mt-2">
                                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-1 rounded-pill small">
                                                <i class="bi bi-check-circle me-1"></i> Nova imagem pronta para ser gravada
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Botões de Ação do Formulário -->
                            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 pt-3 border-top border-secondary-subtle">
                                <div class="d-flex align-items-center gap-2">
                                    <button type="submit" class="btn btn-primary btn-lg shadow-sm px-4" id="btnSalvar">
                                        <i class="bi bi-check2-circle me-2"></i> Salvar Alterações
                                    </button>
                                    <a href="photo.php" class="btn btn-outline-secondary btn-lg px-3">
                                        Cancelar
                                    </a>
                                </div>
                                <button type="button" class="btn btn-outline-danger btn-sm px-3" id="btnExcluirFoto">
                                    <i class="bi bi-trash3 me-1"></i> Excluir Foto
                                </button>
                            </div>

                        </form>
                    </div>
                </div>
            </div>

            <!-- Coluna Lateral: Prévia Ao Vivo e Metadados (4 Colunas) -->
            <div class="col-12 col-xl-4">
                
                <!-- Card: Pré-Visualização Ao Vivo (Live Preview) -->
                <div class="card shadow-sm border-0 bg-body mb-4 photo-live-preview-card">
                    <div class="card-header bg-body-tertiary border-bottom border-secondary-subtle py-3 d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-info rounded-pill p-2"><i class="bi bi-eye"></i></span>
                            <h6 class="card-title fw-bold text-body mb-0">Prévia no Portal</h6>
                        </div>
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle small">Tempo Real</span>
                    </div>
                    <div class="card-body p-3">
                        <div class="border border-secondary-subtle rounded-3 overflow-hidden shadow-xs bg-body-secondary">
                            <div class="position-relative" style="aspect-ratio: 4/3; overflow: hidden; background: #000;">
                                <img src="<?= $foto_atual_url; ?>" alt="Prévia" id="livePreviewImg" class="w-100 h-100 object-fit-cover">
                                <div class="position-absolute top-0 start-0 m-2">
                                    <span class="badge bg-primary text-white shadow-xs" id="livePreviewCat">
                                        <i class="bi bi-tag-fill me-1"></i><?= htmlspecialchars($foto['p_category_name'] ?? 'Geral'); ?>
                                    </span>
                                </div>
                                <div class="position-absolute bottom-0 end-0 m-2">
                                    <span class="badge bg-dark bg-opacity-75 text-white shadow-xs" id="livePreviewStatus">
                                        <?= ($foto['status'] === 'Active') ? 'Visível' : 'Oculto'; ?>
                                    </span>
                                </div>
                            </div>
                            <div class="p-3 bg-body">
                                <h6 class="fw-bold text-body mb-1 text-truncate" id="livePreviewCaption">
                                    <?= htmlspecialchars($foto['photo_caption'] ?? 'Sem título'); ?>
                                </h6>
                                <div class="d-flex align-items-center justify-content-between text-body-secondary small mt-2 pt-2 border-top border-secondary-subtle">
                                    <span><i class="bi bi-hash"></i>ID: <?= $id; ?></span>
                                    <span class="text-primary"><i class="bi bi-zoom-in me-1"></i>Ver ampliada</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Card: Informações Técnicas do Registro -->
                <div class="card shadow-sm border-0 bg-body mb-4">
                    <div class="card-header bg-body-tertiary border-bottom border-secondary-subtle py-3">
                        <h6 class="card-title fw-bold text-body mb-0">
                            <i class="bi bi-info-circle text-primary me-2"></i>Ficha Técnica do Registro
                        </h6>
                    </div>
                    <div class="card-body p-3">
                        <ul class="list-group list-group-flush small">
                            <li class="list-group-item bg-transparent d-flex justify-content-between align-items-center px-0 py-2">
                                <span class="text-body-secondary">Identificador Único</span>
                                <span class="fw-bold font-monospace text-body">#<?= $id; ?></span>
                            </li>
                            <li class="list-group-item bg-transparent d-flex justify-content-between align-items-center px-0 py-2">
                                <span class="text-body-secondary">Arquivo Físico</span>
                                <span class="fw-bold font-monospace text-body text-truncate ms-2" style="max-width: 180px;" id="metaArquivoNome"><?= htmlspecialchars($foto_atual_nome); ?></span>
                            </li>
                            <li class="list-group-item bg-transparent d-flex justify-content-between align-items-center px-0 py-2">
                                <span class="text-body-secondary">Dimensões Originais</span>
                                <span class="fw-semibold text-body" id="metaDimensoes"><?= $foto_dimensoes ?: 'Auto'; ?></span>
                            </li>
                            <li class="list-group-item bg-transparent d-flex justify-content-between align-items-center px-0 py-2">
                                <span class="text-body-secondary">Peso do Arquivo</span>
                                <span class="fw-semibold text-body" id="metaPeso"><?= $foto_tamanho_kb ? $foto_tamanho_kb . ' KB' : 'Desconhecido'; ?></span>
                            </li>
                            <li class="list-group-item bg-transparent d-flex justify-content-between align-items-center px-0 py-2">
                                <span class="text-body-secondary">Posição no Acervo</span>
                                <span class="badge bg-secondary-subtle text-body-secondary border">Total: <?= $total_fotos_existentes; ?> fotos</span>
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Card: Boas Práticas Fotográficas -->
                <div class="card shadow-sm border-0 bg-body mb-4">
                    <div class="card-header bg-body-tertiary border-bottom border-secondary-subtle py-3">
                        <h6 class="card-title fw-bold text-body mb-0">
                            <i class="bi bi-lightbulb text-warning me-2"></i>Recomendações
                        </h6>
                    </div>
                    <div class="card-body p-3 small text-body-secondary">
                        <div class="d-flex align-items-start gap-2 mb-2">
                            <i class="bi bi-check-circle-fill text-success mt-1"></i>
                            <div><strong>Proporção 4:3 ou 16:9:</strong> Garante melhor alinhamento na grade da galeria do portal.</div>
                        </div>
                        <div class="d-flex align-items-start gap-2 mb-2">
                            <i class="bi bi-check-circle-fill text-success mt-1"></i>
                            <div><strong>Imagens em WEBP/JPG:</strong> Proporcionam carregamento ultra veloz para pacientes no celular.</div>
                        </div>
                        <div class="d-flex align-items-start gap-2">
                            <i class="bi bi-check-circle-fill text-success mt-1"></i>
                            <div><strong>Legendas Claras:</strong> Ajudam na busca de pacientes e fortalecem o SEO no Google Imagens.</div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

    </div>
</div>

<!-- ========================================== -->
<!-- MODAL DINÂMICO: LIGHTBOX DE TELA CHEIA     -->
<!-- ========================================== -->
<div class="modal fade" id="modalVisualizarFoto" tabindex="-1" aria-labelledby="modalVisualizarFotoLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content bg-body text-body border-0 shadow-lg rounded-3">
            <div class="modal-header bg-body-tertiary py-3 border-bottom border-secondary-subtle d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-primary rounded-pill p-2"><i class="bi bi-image"></i></span>
                    <h5 class="modal-title fw-bold text-body mb-0" id="mvfTitulo">Visualizar Foto</h5>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body p-2 bg-black text-center">
                <div class="d-flex align-items-center justify-content-center" style="min-height: 320px; max-height: 72vh;">
                    <img src="" alt="Foto" id="mvfImagem" class="img-fluid rounded-3 shadow-sm object-fit-contain" style="max-height: 70vh;">
                </div>
            </div>
            <div class="modal-footer bg-body-tertiary border-top border-secondary-subtle py-2 d-flex justify-content-between">
                <span class="small text-body-secondary" id="mvfInfo">Foto #<?= $id; ?> &bull; Pronto Saúde</span>
                <button type="button" class="btn btn-secondary btn-sm px-3" data-bs-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>

<script>
// Funções Globais Independentes de DOMContentLoaded (Suporte a SPA e Reload)
window.abrirModalVisualizarFoto = function(url, caption) {
    document.querySelectorAll('.tooltip').forEach(t => t.remove());

    const imgEl = document.getElementById('mvfImagem');
    const titEl = document.getElementById('mvfTitulo');
    const infEl = document.getElementById('mvfInfo');

    if (imgEl) imgEl.src = url;
    if (titEl) titEl.textContent = caption || 'Visualizar Foto #<?= $id; ?>';
    if (infEl) infEl.textContent = '<?= addslashes($foto_atual_nome); ?> &bull; Centro Médico Pronto Saúde';

    const modalEl = document.getElementById('modalVisualizarFoto');
    if (modalEl && typeof bootstrap !== 'undefined') {
        bootstrap.Modal.getOrCreateInstance(modalEl).show();
    }
};

function initPhotoEdit() {
    const formPhotoEdit     = document.getElementById('formPhotoEdit');
    const captionInput      = document.getElementById('photo_caption');
    const charCounter       = document.getElementById('charCounter');
    const categorySelect    = document.getElementById('p_category_id');
    const statusSwitch      = document.getElementById('statusSwitch');
    const statusHidden      = document.getElementById('statusHidden');
    const badgeStatusHeader = document.getElementById('badgeStatusHeader');
    const badgeStatusText   = document.getElementById('badgeStatusHeaderText');
    
    // Elementos de Upload & Preview
    const dropzoneArea      = document.getElementById('dropzoneArea');
    const photoInput        = document.getElementById('photoInput');
    const dropzoneEmpty     = document.getElementById('dropzoneEmpty');
    const dropzonePreview   = document.getElementById('dropzonePreview');
    const newPhotoPreview   = document.getElementById('newPhotoPreviewImg');
    const newPhotoName      = document.getElementById('newPhotoName');
    const newPhotoSize      = document.getElementById('newPhotoSize');
    const btnCancelNewPhoto = document.getElementById('btnCancelNewPhoto');
    const btnSalvar         = document.getElementById('btnSalvar');
    const btnExcluirFoto    = document.getElementById('btnExcluirFoto');

    // Elementos do Live Preview Lateral
    const liveImg           = document.getElementById('livePreviewImg');
    const liveCaption       = document.getElementById('livePreviewCaption');
    const liveCat           = document.getElementById('livePreviewCat');
    const liveStatus        = document.getElementById('livePreviewStatus');
    const originalImgUrl    = '<?= addslashes($foto_atual_url); ?>';

    // 1. Contador de Caracteres & Sincronização do Live Caption
    function updateCaption() {
        if (!captionInput) return;
        const len = captionInput.value.length;
        if (charCounter) charCounter.textContent = `${len}/150 caracteres`;
        if (liveCaption) {
            liveCaption.textContent = captionInput.value.trim() || 'Sem título definido';
        }
    }
    if (captionInput) {
        captionInput.addEventListener('input', updateCaption);
        updateCaption();
    }

    // 2. Sincronização da Categoria no Live Preview
    if (categorySelect) {
        categorySelect.addEventListener('change', function () {
            const selectedOpt = categorySelect.options[categorySelect.selectedIndex];
            const catName = selectedOpt ? (selectedOpt.getAttribute('data-name') || selectedOpt.text) : 'Geral';
            if (liveCat) {
                liveCat.innerHTML = `<i class="bi bi-tag-fill me-1"></i>${catName}`;
            }
        });
    }

    // 3. Sincronização do Switch de Status
    if (statusSwitch && statusHidden) {
        statusSwitch.addEventListener('change', function () {
            const isAtivo = this.checked;
            statusHidden.value = isAtivo ? 'Active' : 'Inactive';
            
            if (badgeStatusHeader) {
                if (isAtivo) {
                    badgeStatusHeader.className = 'badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-1';
                    badgeStatusHeader.innerHTML = '<i class="bi bi-check-circle-fill me-1"></i><span>Ativo na Galeria</span>';
                } else {
                    badgeStatusHeader.className = 'badge bg-warning-subtle text-warning border border-warning-subtle rounded-pill px-3 py-1';
                    badgeStatusHeader.innerHTML = '<i class="bi bi-pause-circle-fill me-1"></i><span>Oculto no Site</span>';
                }
            }

            if (liveStatus) {
                liveStatus.textContent = isAtivo ? 'Visível' : 'Oculto';
            }
        });
    }

    // 4. Manipulação de Arquivo e Prévia Instantânea (Drag & Drop)
    function handleFile(file) {
        if (!file) return;

        const extPermitidas = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
        const ext = file.name.split('.').pop().toLowerCase();

        if (!extPermitidas.includes(ext)) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'error',
                    title: 'Formato não suportado',
                    text: 'Selecione uma imagem nos formatos WEBP, JPG, JPEG, PNG ou GIF.'
                });
            }
            photoInput.value = '';
            return;
        }

        // Limite de 15MB
        if (file.size > 15 * 1024 * 1024) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'error',
                    title: 'Arquivo muito pesado',
                    text: 'O tamanho da imagem não pode ultrapassar 15MB.'
                });
            }
            photoInput.value = '';
            return;
        }

        const reader = new FileReader();
        reader.onload = function (e) {
            if (newPhotoPreview) newPhotoPreview.src = e.target.result;
            if (newPhotoName) newPhotoName.textContent = file.name;
            if (newPhotoSize) newPhotoSize.textContent = (file.size / 1024).toFixed(1) + ' KB';

            if (dropzoneEmpty) dropzoneEmpty.classList.add('d-none');
            if (dropzonePreview) dropzonePreview.classList.remove('d-none');

            // Atualiza também o Live Preview Lateral
            if (liveImg) liveImg.src = e.target.result;
        };
        reader.readAsDataURL(file);
    }

    if (photoInput) {
        photoInput.addEventListener('change', function () {
            if (this.files && this.files[0]) {
                handleFile(this.files[0]);
            }
        });
    }

    // Eventos de Drag & Drop
    if (dropzoneArea) {
        ['dragenter', 'dragover'].forEach(eventName => {
            dropzoneArea.addEventListener(eventName, function (e) {
                e.preventDefault();
                e.stopPropagation();
                dropzoneArea.classList.add('dragover');
            }, false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
            dropzoneArea.addEventListener(eventName, function (e) {
                e.preventDefault();
                e.stopPropagation();
                dropzoneArea.classList.remove('dragover');
            }, false);
        });

        dropzoneArea.addEventListener('drop', function (e) {
            const dt = e.dataTransfer;
            const files = dt ? dt.files : null;
            if (files && files.length > 0) {
                photoInput.files = files;
                handleFile(files[0]);
            }
        }, false);
    }

    // Botão de Cancelar Nova Foto Selecionada
    if (btnCancelNewPhoto) {
        btnCancelNewPhoto.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
            if (photoInput) photoInput.value = '';
            if (dropzonePreview) dropzonePreview.classList.add('d-none');
            if (dropzoneEmpty) dropzoneEmpty.classList.remove('d-none');
            if (liveImg) liveImg.src = originalImgUrl;
        });
    }

    // 5. Envio Assíncrono com AJAX e SweetAlert2
    if (formPhotoEdit) {
        formPhotoEdit.addEventListener('submit', function (e) {
            e.preventDefault();

            // Validação de Campos
            const captionVal = captionInput ? captionInput.value.trim() : '';
            const catVal     = categorySelect ? categorySelect.value : '';

            if (!captionVal) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Título Obrigatório',
                        text: 'Por favor, informe um título ou legenda para a foto antes de salvar.'
                    }).then(() => {
                        captionInput && captionInput.focus();
                    });
                }
                return;
            }

            if (!catVal || parseInt(catVal, 10) <= 0) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Categoria Obrigatória',
                        text: 'Por favor, selecione uma categoria válida para a foto.'
                    }).then(() => {
                        categorySelect && categorySelect.focus();
                    });
                }
                return;
            }

            // Inicia envio AJAX
            const formData = new FormData(formPhotoEdit);
            formData.append('action', 'edit_photo_ajax');

            if (btnSalvar) {
                btnSalvar.disabled = true;
                btnSalvar.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Salvando...';
            }

            fetch('photo-edit.php?id=<?= $id; ?>', {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (btnSalvar) {
                    btnSalvar.disabled = false;
                    btnSalvar.innerHTML = '<i class="bi bi-check2-circle me-2"></i> Salvar Alterações';
                }

                if (data.status === 'success') {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Alterações Salvas!',
                            text: data.message,
                            showCancelButton: true,
                            confirmButtonColor: '#0d6efd',
                            cancelButtonColor: '#6c757d',
                            confirmButtonText: '<i class="bi bi-list-ul me-1"></i> Ir para o Acervo',
                            cancelButtonText: 'Continuar Editando',
                            customClass: {
                                confirmButton: 'btn btn-primary px-3 me-2',
                                cancelButton: 'btn btn-secondary px-3'
                            },
                            buttonsStyling: false
                        }).then((result) => {
                            if (result.isConfirmed) {
                                window.location.href = 'photo.php';
                            } else {
                                // Se houve novo upload, atualiza a exibição da foto atual sem recarregar
                                if (data.new_upload && data.file_name) {
                                    const imgAtual = document.getElementById('imgAtualDisplay');
                                    const lblNome  = document.getElementById('lblNomeArquivoAtual');
                                    const metaArq  = document.getElementById('metaArquivoNome');
                                    const prevInp  = document.getElementById('previousPhotoInput');

                                    if (imgAtual && data.img_url) imgAtual.src = data.img_url;
                                    if (lblNome) lblNome.textContent = data.file_name;
                                    if (metaArq) metaArq.textContent = data.file_name;
                                    if (prevInp) prevInp.value = data.file_name;

                                    if (photoInput) photoInput.value = '';
                                    if (dropzonePreview) dropzonePreview.classList.add('d-none');
                                    if (dropzoneEmpty) dropzoneEmpty.classList.remove('d-none');
                                }
                            }
                        });
                    } else {
                        window.location.href = 'photo.php';
                    }
                } else {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'error',
                            title: 'Não foi possível salvar',
                            html: data.message || 'Verifique os dados informados e tente novamente.'
                        });
                    }
                }
            })
            .catch(err => {
                console.error(err);
                if (btnSalvar) {
                    btnSalvar.disabled = false;
                    btnSalvar.innerHTML = '<i class="bi bi-check2-circle me-2"></i> Salvar Alterações';
                }
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'error',
                        title: 'Erro de Comunicação',
                        text: 'Houve uma falha na conexão com o servidor. Deseja submeter de forma tradicional?'
                    }).then(res => {
                        if (res.isConfirmed) formPhotoEdit.submit();
                    });
                } else {
                    formPhotoEdit.submit();
                }
            });
        });
    }

    // 6. Botão de Exclusão com SweetAlert2
    if (btnExcluirFoto) {
        btnExcluirFoto.addEventListener('click', function () {
            document.querySelectorAll('.tooltip').forEach(t => t.remove());

            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Confirmar Exclusão',
                    html: `Deseja realmente remover permanentemente a foto <strong>#<?= $id; ?></strong> do acervo?<br><small class="text-danger">O arquivo físico da imagem também será excluído do servidor.</small>`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: '<i class="bi bi-trash3 me-1"></i> Sim, excluir definitivamente',
                    cancelButtonText: 'Cancelar',
                    customClass: {
                        confirmButton: 'btn btn-danger px-3 me-2',
                        cancelButton: 'btn btn-secondary px-3'
                    },
                    buttonsStyling: false
                }).then((result) => {
                    if (result.isConfirmed) {
                        fetch('photo.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded',
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            body: new URLSearchParams({
                                action: 'delete_photo',
                                id: <?= $id; ?>
                            })
                        })
                        .then(r => r.json())
                        .then(res => {
                            if (res.status === 'success') {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Foto Excluída!',
                                    text: res.message,
                                    timer: 1600,
                                    showConfirmButton: false
                                }).then(() => {
                                    window.location.href = 'photo.php';
                                });
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Erro ao excluir',
                                    text: res.message
                                });
                            }
                        })
                        .catch(() => {
                            window.location.href = 'photo-delete.php?id=<?= $id; ?>';
                        });
                    }
                });
            } else {
                window.location.href = 'photo-delete.php?id=<?= $id; ?>';
            }
        });
    }
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initPhotoEdit);
} else {
    initPhotoEdit();
}
</script>

<?php require_once('footer.php'); ?>
