<?php
require_once('header.php');

// 1. Validação de ID da Categoria
$id = isset($_REQUEST['id']) ? (int)$_REQUEST['id'] : 0;

if ($id <= 0) {
    $_SESSION['erro'] = 'Identificador de categoria inválido ou não fornecido.';
    header('location: video-category.php');
    exit;
}

// 2. Busca os dados atuais da Categoria de Vídeo
$statement = $pdo->prepare("SELECT * FROM tbl_category_video WHERE v_category_id = ?");
$statement->execute([$id]);
$category = $statement->fetch(PDO::FETCH_ASSOC);

if (!$category) {
    $_SESSION['erro'] = 'A categoria de vídeos solicitada (# ' . $id . ') não foi localizada no banco de dados.';
    header('location: video-category.php');
    exit;
}

// 3. Contagem de vídeos vinculados a esta categoria
$stmt_videos = $pdo->prepare("SELECT COUNT(*) as total, 
                                     SUM(CASE WHEN status = 'Active' THEN 1 ELSE 0 END) as ativos,
                                     SUM(CASE WHEN status != 'Active' THEN 1 ELSE 0 END) as inativos
                              FROM tbl_video WHERE v_category_id = ?");
$stmt_videos->execute([$id]);
$stats_videos = $stmt_videos->fetch(PDO::FETCH_ASSOC);

$total_videos_cat    = (int)($stats_videos['total'] ?? 0);
$videos_ativos_cat   = (int)($stats_videos['ativos'] ?? 0);
$videos_inativos_cat = (int)($stats_videos['inativos'] ?? 0);

// 4. Processamento da Atualização (Suporta AJAX e POST tradicional)
$is_ajax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
           || (isset($_POST['action']) && $_POST['action'] === 'save_category_ajax');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && (isset($_POST['form1']) || $is_ajax)) {
    $v_category_name = trim($_POST['v_category_name'] ?? '');
    $status          = trim($_POST['status'] ?? 'Active');

    $valid = 1;
    $erros = [];

    // Validação de Nome da Categoria
    if (empty($v_category_name)) {
        $valid = 0;
        $erros[] = 'O nome da categoria não pode ficar em branco.';
    } else {
        // Verifica duplicidade de nome com outras categorias de vídeo
        $stmt_check = $pdo->prepare("SELECT v_category_id FROM tbl_category_video WHERE v_category_name = ? AND v_category_id != ?");
        $stmt_check->execute([$v_category_name, $id]);
        if ($stmt_check->rowCount() > 0) {
            $valid = 0;
            $erros[] = 'Já existe uma outra categoria com este mesmo nome cadastrada no sistema.';
        }
    }

    if (!in_array($status, ['Active', 'Inactive'])) {
        $status = 'Active';
    }

    if ($valid === 1) {
        $stmt_update = $pdo->prepare("UPDATE tbl_category_video SET v_category_name = ?, status = ? WHERE v_category_id = ?");
        $stmt_update->execute([$v_category_name, $status, $id]);

        $msg_sucesso = 'Categoria de vídeos "' . htmlspecialchars($v_category_name) . '" atualizada com sucesso!';

        if ($is_ajax) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'status'     => 'success',
                'message'    => $msg_sucesso,
                'name'       => $v_category_name,
                'cat_status' => $status
            ]);
            exit;
        }

        $_SESSION['sucesso'] = $msg_sucesso;
        header('location: video-category.php');
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

// Dados para preenchimento dos campos
$v_category_name = htmlspecialchars($_POST['v_category_name'] ?? $category['v_category_name']);
$status          = $_POST['status'] ?? $category['status'] ?? 'Active';
$is_active       = ($status === 'Active');
?>

<style>
.radio-status-card {
    transition: all 0.2s ease-in-out;
}
.radio-status-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 10px rgba(0,0,0,0.06);
}
.cursor-pointer {
    cursor: pointer !important;
}
</style>

<!-- Cabeçalho da Página -->
<div class="app-content-header pb-2 mb-3 border-bottom">
    <div class="container-fluid">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div>
                <h3 class="mb-1 fw-bold">
                    <i class="bi bi-pencil-square text-primary me-2"></i>Editar Categoria de Vídeo #<?= $id; ?>
                </h3>
                <ol class="breadcrumb mb-0 small">
                    <li class="breadcrumb-item"><a href="index.php"><i class="bi bi-house-door"></i> Início</a></li>
                    <li class="breadcrumb-item"><a href="video.php">Multimídia</a></li>
                    <li class="breadcrumb-item"><a href="video-category.php">Categorias de Vídeos</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Editar: #<?= $id; ?></li>
                </ol>
            </div>
            <div class="d-flex flex-wrap align-items-center gap-2">
                <a href="<?= BASE_URL; ?>page.php?slug=videos" target="_blank" rel="noopener" class="btn btn-outline-info btn-sm shadow-xs" title="Visualizar a galeria de vídeos no site público">
                    <i class="bi bi-box-arrow-up-right me-1"></i> Ver no Site
                </a>
                <a href="video.php" class="btn btn-outline-secondary btn-sm shadow-xs" title="Ir para a galeria de vídeos">
                    <i class="bi bi-collection-play me-1"></i> Vídeos do Acervo
                </a>
                <a href="video-category.php" class="btn btn-outline-primary btn-sm shadow-xs" title="Voltar à listagem de categorias">
                    <i class="bi bi-tags me-1"></i> Todas as Categorias
                </a>
                <a href="video-category-add.php" class="btn btn-primary btn-sm shadow-xs px-3">
                    <i class="bi bi-plus-lg me-1"></i> Nova Categoria
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

        <!-- Cards Informativos da Categoria Selecionada (KPIs Rápidos) -->
        <div class="row g-3 mb-4">
            <!-- Card 1: Identificação -->
            <div class="col-12 col-md-4">
                <div class="card shadow-sm border-0 border-start border-primary border-4 h-100 bg-body">
                    <div class="card-body d-flex align-items-center justify-content-between py-3">
                        <div>
                            <div class="text-body-secondary small text-uppercase fw-bold">ID da Categoria</div>
                            <div class="fs-4 fw-bold text-body mt-1">#<?= $id; ?></div>
                            <small class="text-body-secondary text-truncate d-block" style="max-width: 180px;">
                                <?= htmlspecialchars($category['v_category_name']); ?>
                            </small>
                        </div>
                        <div class="p-3 bg-primary-subtle text-primary rounded-circle">
                            <i class="bi bi-folder-fill fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card 2: Vídeos Associados -->
            <div class="col-12 col-md-4">
                <div class="card shadow-sm border-0 border-start border-success border-4 h-100 bg-body">
                    <div class="card-body d-flex align-items-center justify-content-between py-3">
                        <div>
                            <div class="text-body-secondary small text-uppercase fw-bold">Vídeos Vinculados</div>
                            <div class="fs-4 fw-bold text-success mt-1"><?= $total_videos_cat; ?> <span class="fs-6 fw-normal text-body-secondary">vídeo(s)</span></div>
                            <small class="text-body-secondary">
                                <?= $videos_ativos_cat; ?> visíveis &bull; <?= $videos_inativos_cat; ?> ocultos
                            </small>
                        </div>
                        <div class="p-3 bg-success-subtle text-success rounded-circle">
                            <i class="bi bi-camera-reels-fill fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card 3: Status no Portal -->
            <div class="col-12 col-md-4">
                <div class="card shadow-sm border-0 border-start <?= $is_active ? 'border-info' : 'border-warning'; ?> border-4 h-100 bg-body">
                    <div class="card-body d-flex align-items-center justify-content-between py-3">
                        <div>
                            <div class="text-body-secondary small text-uppercase fw-bold">Status Atual</div>
                            <div class="mt-2">
                                <span class="badge <?= $is_active ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-warning-subtle text-warning border border-warning-subtle'; ?> rounded-pill px-3 py-1 fs-6" id="badgeStatusTopo">
                                    <i class="bi <?= $is_active ? 'bi-check-circle-fill' : 'bi-pause-circle-fill'; ?> me-1"></i>
                                    <span id="badgeStatusTopoText"><?= $is_active ? 'Ativa no Site' : 'Oculta / Pausada'; ?></span>
                                </span>
                            </div>
                        </div>
                        <div class="p-3 <?= $is_active ? 'bg-info-subtle text-info' : 'bg-warning-subtle text-warning'; ?> rounded-circle" id="iconBoxStatusTopo">
                            <i class="bi <?= $is_active ? 'bi-eye-fill' : 'bi-eye-slash-fill'; ?> fs-4" id="iconStatusTopo"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Formulário de Edição Modernizado -->
        <div class="row">
            <div class="col-12 col-xl-8">
                <div class="card shadow-sm border-0 bg-body mb-4">
                    <div class="card-header bg-body-tertiary border-bottom border-secondary-subtle py-3 d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-primary rounded-pill p-2"><i class="bi bi-pencil-square"></i></span>
                            <div>
                                <h5 class="card-title fw-bold text-body mb-0">Informações da Categoria</h5>
                                <small class="text-body-secondary">Altere os dados para reorganizar a galeria e os filtros de vídeos</small>
                            </div>
                        </div>
                        <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">
                            ID: <?= $id; ?>
                        </span>
                    </div>

                    <div class="card-body p-4">
                        <form action="" method="post" id="formEditarCategoriaVideo" novalidate>
                            <input type="hidden" name="id" value="<?= $id; ?>">
                            <input type="hidden" name="form1" value="1">

                            <!-- Campo: Nome da Categoria -->
                            <div class="mb-4">
                                <label for="v_category_name" class="form-label fw-bold text-body d-flex align-items-center justify-content-between">
                                    <span>
                                        <i class="bi bi-tag text-primary me-1"></i> Nome da Categoria <span class="text-danger">*</span>
                                    </span>
                                    <small class="text-body-secondary fw-normal" id="charCounter">0/100 caracteres</small>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text bg-body-tertiary text-body-secondary border-secondary-subtle">
                                        <i class="bi bi-folder-fill"></i>
                                    </span>
                                    <input type="text" 
                                           class="form-control form-control-lg bg-body text-body border-secondary-subtle" 
                                           id="v_category_name" 
                                           name="v_category_name" 
                                           value="<?= $v_category_name; ?>" 
                                           maxlength="100" 
                                           required 
                                           placeholder="Ex: Depoimentos de Pacientes, Procedimentos Médicos, Estrutura...">
                                </div>
                                <div class="form-text text-body-secondary small mt-1">
                                    <i class="bi bi-info-circle me-1"></i> Este nome será exibido como aba de filtro interativa na galeria de vídeos do portal.
                                </div>
                            </div>

                            <!-- Campo: Status de Visibilidade (Estilo Seletor Interativo com Cards) -->
                            <div class="mb-4">
                                <label class="form-label fw-bold text-body d-block">
                                    <i class="bi bi-toggle-on text-primary me-1"></i> Visibilidade no Portal
                                </label>
                                
                                <div class="row g-3">
                                    <!-- Opção: Ativa / Visível -->
                                    <div class="col-12 col-sm-6">
                                        <label class="card h-100 p-3 border border-secondary-subtle rounded-3 cursor-pointer radio-status-card <?= $is_active ? 'border-primary bg-primary-subtle bg-opacity-10' : 'bg-body'; ?>" style="cursor: pointer;">
                                            <div class="d-flex align-items-center justify-content-between">
                                                <div class="d-flex align-items-center gap-3">
                                                    <input class="form-check-input mt-0" type="radio" name="status" id="statusActive" value="Active" <?= $is_active ? 'checked' : ''; ?>>
                                                    <div>
                                                        <div class="fw-bold text-body">Ativa / Visível</div>
                                                        <small class="text-body-secondary">Exibida nos filtros da galeria pública</small>
                                                    </div>
                                                </div>
                                                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill">
                                                    <i class="bi bi-check-circle-fill me-1"></i>Visível
                                                </span>
                                            </div>
                                        </label>
                                    </div>

                                    <!-- Opção: Inativa / Oculta -->
                                    <div class="col-12 col-sm-6">
                                        <label class="card h-100 p-3 border border-secondary-subtle rounded-3 cursor-pointer radio-status-card <?= !$is_active ? 'border-warning bg-warning-subtle bg-opacity-10' : 'bg-body'; ?>" style="cursor: pointer;">
                                            <div class="d-flex align-items-center justify-content-between">
                                                <div class="d-flex align-items-center gap-3">
                                                    <input class="form-check-input mt-0" type="radio" name="status" id="statusInactive" value="Inactive" <?= !$is_active ? 'checked' : ''; ?>>
                                                    <div>
                                                        <div class="fw-bold text-body">Inativa / Oculta</div>
                                                        <small class="text-body-secondary">Temporariamente oculta no portal</small>
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

                            <!-- Botões de Ação -->
                            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 pt-3 border-top border-secondary-subtle">
                                <a href="video-category.php" class="btn btn-outline-secondary px-3 shadow-xs">
                                    <i class="bi bi-arrow-left me-1"></i> Cancelar e Voltar
                                </a>
                                <div class="d-flex align-items-center gap-2">
                                    <button type="submit" class="btn btn-primary px-4 shadow-sm" id="btnSalvar">
                                        <i class="bi bi-check-lg me-1"></i> Salvar Alterações
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Coluna Lateral de Impacto e Boas Práticas -->
            <div class="col-12 col-xl-4">
                <!-- Card de Impacto dos Vídeos Vinculados -->
                <div class="card shadow-sm border-0 bg-body mb-4">
                    <div class="card-header bg-body-tertiary border-bottom border-secondary-subtle py-3">
                        <h6 class="card-title fw-bold text-body mb-0">
                            <i class="bi bi-camera-video text-primary me-2"></i>Impacto no Acervo
                        </h6>
                    </div>
                    <div class="card-body p-3">
                        <?php if ($total_videos_cat > 0): ?>
                            <div class="alert alert-info border-0 bg-info-subtle text-info-emphasis d-flex gap-2 p-3 mb-3 rounded-3" role="alert">
                                <i class="bi bi-shield-exclamation fs-4 flex-shrink-0"></i>
                                <div class="small">
                                    Existem <strong><?= $total_videos_cat; ?> vídeo(s)</strong> vinculados a esta categoria. Ao renomeá-la, todos os vídeos associados exibirão o novo nome em tempo real.
                                </div>
                            </div>

                            <a href="video.php" class="btn btn-outline-primary btn-sm w-100 shadow-xs mb-2">
                                <i class="bi bi-camera-video-fill me-1"></i> Ver Vídeos Desta Categoria
                            </a>
                        <?php else: ?>
                            <div class="text-body-secondary small mb-3">
                                <i class="bi bi-check2-circle text-success me-1"></i> Não há vídeos vinculados a esta categoria no momento. Você pode editá-la livremente ou adicionar novos vídeos nela.
                            </div>

                            <a href="video-add.php" class="btn btn-outline-success btn-sm w-100 shadow-xs mb-2">
                                <i class="bi bi-plus-circle me-1"></i> Adicionar Vídeo nesta Categoria
                            </a>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Card de Boas Práticas -->
                <div class="card shadow-sm border-0 bg-body">
                    <div class="card-header bg-body-tertiary border-bottom border-secondary-subtle py-3">
                        <h6 class="card-title fw-bold text-body mb-0">
                            <i class="bi bi-lightbulb text-warning me-2"></i>Boas Práticas
                        </h6>
                    </div>
                    <div class="card-body p-3 small text-body-secondary">
                        <ul class="mb-0 ps-3 d-flex flex-column gap-2">
                            <li>Prefira nomes claros e diretos (ex: <em>"Depoimentos"</em>, <em>"Especialidades"</em>, <em>"Consultórios"</em>).</li>
                            <li>Evite duplicar temas parecidos para manter os filtros intuitivos para os pacientes no site.</li>
                            <li>Categorias com status <strong>Oculta</strong> mantêm seus vídeos cadastrados, mas pausam a exibição pública.</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<script>
function initVideoCategoryEdit() {
    const inputNome   = document.getElementById('v_category_name');
    const charCounter = document.getElementById('charCounter');
    const form        = document.getElementById('formEditarCategoriaVideo');
    const btnSalvar   = document.getElementById('btnSalvar');

    // 1. Contador Dinâmico de Caracteres
    if (inputNome && charCounter) {
        const updateCounter = () => {
            const count = inputNome.value.length;
            charCounter.textContent = `${count}/100 caracteres`;
        };
        inputNome.addEventListener('input', updateCounter);
        updateCounter();
    }

    // 2. Interatividade Visual dos Cards de Status
    const radioCards = document.querySelectorAll('.radio-status-card');
    const radioInputs = document.querySelectorAll('input[name="status"]');
    const badgeTopo = document.getElementById('badgeStatusTopo');
    const badgeTopoText = document.getElementById('badgeStatusTopoText');
    const iconTopo = document.getElementById('iconStatusTopo');
    const iconBoxTopo = document.getElementById('iconBoxStatusTopo');

    function updateRadioVisual(val) {
        radioCards.forEach(card => {
            const r = card.querySelector('input[type="radio"]');
            if (r.value === 'Active') {
                if (r.checked) {
                    card.classList.add('border-primary', 'bg-primary-subtle', 'bg-opacity-10');
                    card.classList.remove('bg-body');
                } else {
                    card.classList.remove('border-primary', 'bg-primary-subtle', 'bg-opacity-10');
                    card.classList.add('bg-body');
                }
            } else if (r.value === 'Inactive') {
                if (r.checked) {
                    card.classList.add('border-warning', 'bg-warning-subtle', 'bg-opacity-10');
                    card.classList.remove('bg-body');
                } else {
                    card.classList.remove('border-warning', 'bg-warning-subtle', 'bg-opacity-10');
                    card.classList.add('bg-body');
                }
            }
        });

        if (badgeTopo && badgeTopoText && iconTopo && iconBoxTopo) {
            if (val === 'Active') {
                badgeTopo.className = 'badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-1 fs-6';
                badgeTopo.innerHTML = '<i class="bi bi-check-circle-fill me-1"></i><span>Ativa no Site</span>';
                iconBoxTopo.className = 'p-3 bg-info-subtle text-info rounded-circle';
                iconTopo.className = 'bi bi-eye-fill fs-4';
            } else {
                badgeTopo.className = 'badge bg-warning-subtle text-warning border border-warning-subtle rounded-pill px-3 py-1 fs-6';
                badgeTopo.innerHTML = '<i class="bi bi-pause-circle-fill me-1"></i><span>Oculta / Pausada</span>';
                iconBoxTopo.className = 'p-3 bg-warning-subtle text-warning rounded-circle';
                iconTopo.className = 'bi bi-eye-slash-fill fs-4';
            }
        }
    }

    radioInputs.forEach(input => {
        input.addEventListener('change', function () {
            updateRadioVisual(this.value);
        });
    });

    // 3. Submissão Assíncrona com AJAX e SweetAlert2
    if (form) {
        form.addEventListener('submit', function (e) {
            e.preventDefault();

            const nomeVal = inputNome ? inputNome.value.trim() : '';
            if (!nomeVal) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Atenção',
                        text: 'O nome da categoria de vídeos não pode ficar em branco.'
                    }).then(() => {
                        inputNome && inputNome.focus();
                    });
                }
                return;
            }

            if (btnSalvar) {
                btnSalvar.disabled = true;
                btnSalvar.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Salvando...';
            }

            const formData = new FormData(form);
            formData.append('action', 'save_category_ajax');

            fetch('video-category-edit.php?id=<?= $id; ?>', {
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
                    btnSalvar.innerHTML = '<i class="bi bi-check-lg me-1"></i> Salvar Alterações';
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
                            confirmButtonText: '<i class="bi bi-list-ul me-1"></i> Ir para Categorias',
                            cancelButtonText: 'Continuar Editando',
                            customClass: {
                                confirmButton: 'btn btn-primary px-3 me-2',
                                cancelButton: 'btn btn-secondary px-3'
                            },
                            buttonsStyling: false
                        }).then((result) => {
                            if (result.isConfirmed) {
                                window.location.href = 'video-category.php';
                            }
                        });
                    } else {
                        window.location.href = 'video-category.php';
                    }
                } else {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'error',
                            title: 'Erro ao Salvar',
                            html: data.message || 'Verifique as informações e tente novamente.'
                        });
                    }
                }
            })
            .catch(err => {
                console.error(err);
                if (btnSalvar) {
                    btnSalvar.disabled = false;
                    btnSalvar.innerHTML = '<i class="bi bi-check-lg me-1"></i> Salvar Alterações';
                }

                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'error',
                        title: 'Falha de Comunicação',
                        text: 'Não foi possível conectar ao servidor via AJAX. Deseja tentar o envio padrão?'
                    }).then(res => {
                        if (res.isConfirmed) form.submit();
                    });
                } else {
                    form.submit();
                }
            });
        });
    }
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initVideoCategoryEdit);
} else {
    initVideoCategoryEdit();
}
</script>

<?php require_once('footer.php'); ?>
