<?php
require_once('header.php');

// 1. Validação de ID da Categoria
$id = isset($_REQUEST['id']) ? (int)$_REQUEST['id'] : 0;

if ($id <= 0) {
    $_SESSION['erro'] = 'Identificador de categoria inválido ou não fornecido.';
    header('location: photo-category.php');
    exit;
}

// 2. Busca os dados atuais da Categoria
$statement = $pdo->prepare("SELECT * FROM tbl_category_photo WHERE p_category_id = ?");
$statement->execute([$id]);
$category = $statement->fetch(PDO::FETCH_ASSOC);

if (!$category) {
    $_SESSION['erro'] = 'A categoria de fotos solicitada não foi encontrada no banco de dados.';
    header('location: photo-category.php');
    exit;
}

// 3. Contagem de fotos vinculadas a esta categoria
$stmt_photos = $pdo->prepare("SELECT COUNT(*) as total, 
                                     SUM(CASE WHEN status = 'Active' THEN 1 ELSE 0 END) as ativas,
                                     SUM(CASE WHEN status != 'Active' THEN 1 ELSE 0 END) as inativas
                              FROM tbl_photo WHERE p_category_id = ?");
$stmt_photos->execute([$id]);
$stats_photos = $stmt_photos->fetch(PDO::FETCH_ASSOC);

$total_fotos_cat   = (int)($stats_photos['total'] ?? 0);
$fotos_ativas_cat  = (int)($stats_photos['ativas'] ?? 0);
$fotos_inativas_cat= (int)($stats_photos['inativas'] ?? 0);

// 4. Processamento da Atualização (Suporta AJAX e POST tradicional)
$is_ajax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
           || (isset($_POST['action']) && $_POST['action'] === 'save_category_ajax');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && (isset($_POST['form1']) || $is_ajax)) {
    $p_category_name = trim($_POST['p_category_name'] ?? '');
    $status          = trim($_POST['status'] ?? 'Active');

    // Validações
    $valid = 1;
    $erros = [];

    if (empty($p_category_name)) {
        $valid = 0;
        $erros[] = 'O nome da categoria não pode ficar em branco.';
    } else {
        // Verifica duplicidade de nome com outras categorias
        $stmt_check = $pdo->prepare("SELECT p_category_id FROM tbl_category_photo WHERE p_category_name = ? AND p_category_id != ?");
        $stmt_check->execute([$p_category_name, $id]);
        if ($stmt_check->rowCount() > 0) {
            $valid = 0;
            $erros[] = 'Já existe uma outra categoria com este mesmo nome cadastrada.';
        }
    }

    if (!in_array($status, ['Active', 'Inactive'])) {
        $status = 'Active';
    }

    if ($valid === 1) {
        $stmt_update = $pdo->prepare("UPDATE tbl_category_photo SET p_category_name = ?, status = ? WHERE p_category_id = ?");
        $stmt_update->execute([$p_category_name, $status, $id]);

        $msg_sucesso = 'Categoria de fotos "' . htmlspecialchars($p_category_name) . '" atualizada com sucesso!';

        if ($is_ajax) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'status'  => 'success',
                'message' => $msg_sucesso,
                'name'    => $p_category_name,
                'cat_status'  => $status
            ]);
            exit;
        }

        $_SESSION['sucesso'] = $msg_sucesso;
        header('location: photo-category.php');
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
$p_category_name = htmlspecialchars($_POST['p_category_name'] ?? $category['p_category_name']);
$status          = $_POST['status'] ?? $category['status'] ?? 'Active';
$is_active       = ($status === 'Active');
?>

<!-- Cabeçalho da Página -->
<div class="app-content-header pb-2 mb-3 border-bottom">
    <div class="container-fluid">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div>
                <h3 class="mb-1 fw-bold">Editar Categoria de Foto</h3>
                <ol class="breadcrumb mb-0 small">
                    <li class="breadcrumb-item"><a href="index.php"><i class="bi bi-house-door"></i> Início</a></li>
                    <li class="breadcrumb-item"><a href="photo.php">Multimídia</a></li>
                    <li class="breadcrumb-item"><a href="photo-category.php">Categorias de Fotos</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Editar: #<?= $id; ?></li>
                </ol>
            </div>
            <div class="d-flex flex-wrap align-items-center gap-2">
                <a href="<?= BASE_URL; ?>page.php?slug=fotos" target="_blank" rel="noopener" class="btn btn-outline-info btn-sm shadow-xs" title="Visualizar a galeria de fotos no site público">
                    <i class="bi bi-box-arrow-up-right me-1"></i> Ver no Site
                </a>
                <a href="photo.php" class="btn btn-outline-secondary btn-sm shadow-xs" title="Ir para a galeria de fotos">
                    <i class="bi bi-images me-1"></i> Fotos do Acervo
                </a>
                <a href="photo-category.php" class="btn btn-outline-primary btn-sm shadow-xs" title="Voltar à listagem de categorias">
                    <i class="bi bi-tags me-1"></i> Todas as Categorias
                </a>
                <a href="photo-category-add.php" class="btn btn-primary btn-sm shadow-xs px-3">
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
                                <?= htmlspecialchars($category['p_category_name']); ?>
                            </small>
                        </div>
                        <div class="kpi-icon-circle bg-primary-subtle text-primary rounded-circle">
                            <i class="bi bi-tag-fill"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card 2: Fotos Associadas -->
            <div class="col-12 col-md-4">
                <div class="card shadow-sm border-0 border-start border-success border-4 h-100 bg-body">
                    <div class="card-body d-flex align-items-center justify-content-between py-3">
                        <div>
                            <div class="text-body-secondary small text-uppercase fw-bold">Fotos Vinculadas</div>
                            <div class="fs-4 fw-bold text-success mt-1"><?= $total_fotos_cat; ?> <span class="fs-6 fw-normal text-body-secondary">fotos</span></div>
                            <small class="text-body-secondary">
                                <?= $fotos_ativas_cat; ?> visíveis &bull; <?= $fotos_inativas_cat; ?> ocultas
                            </small>
                        </div>
                        <div class="kpi-icon-circle bg-success-subtle text-success rounded-circle">
                            <i class="bi bi-images"></i>
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
                                    <?= $is_active ? 'Ativa no Site' : 'Oculta / Pausada'; ?>
                                </span>
                            </div>
                        </div>
                        <div class="kpi-icon-circle <?= $is_active ? 'bg-info-subtle text-info' : 'bg-warning-subtle text-warning'; ?> rounded-circle">
                            <i class="bi <?= $is_active ? 'bi-eye-fill' : 'bi-eye-slash-fill'; ?>"></i>
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
                                <small class="text-body-secondary">Altere os dados para reorganizar o filtro de fotos</small>
                            </div>
                        </div>
                        <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">
                            ID: <?= $id; ?>
                        </span>
                    </div>

                    <div class="card-body p-4">
                        <form action="" method="post" id="formEditarCategoriaPhoto" novalidate>
                            <input type="hidden" name="id" value="<?= $id; ?>">
                            <input type="hidden" name="form1" value="1">

                            <!-- Campo: Nome da Categoria -->
                            <div class="mb-4">
                                <label for="p_category_name" class="form-label fw-bold text-body d-flex align-items-center justify-content-between">
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
                                           id="p_category_name" 
                                           name="p_category_name" 
                                           value="<?= $p_category_name; ?>" 
                                           maxlength="100" 
                                           required 
                                           placeholder="Ex: Consultórios Médicos, Fachada, Tecnologia...">
                                </div>
                                <div class="form-text text-body-secondary small mt-1">
                                    <i class="bi bi-info-circle me-1"></i> Este nome será exibido como aba de filtro interativa na galeria de fotos do site público.
                                </div>
                            </div>

                            <!-- Campo: Status de Visibilidade (Estilo Seletor Interativo) -->
                            <div class="mb-4">
                                <label class="form-label fw-bold text-body d-block">
                                    <i class="bi bi-toggle-on text-primary me-1"></i> Visibilidade no Site
                                </label>
                                
                                <div class="row g-3">
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

                                    <div class="col-12 col-sm-6">
                                        <label class="card h-100 p-3 border border-secondary-subtle rounded-3 cursor-pointer radio-status-card <?= !$is_active ? 'border-warning bg-warning-subtle bg-opacity-10' : 'bg-body'; ?>" style="cursor: pointer;">
                                            <div class="d-flex align-items-center justify-content-between">
                                                <div class="d-flex align-items-center gap-3">
                                                    <input class="form-check-input mt-0" type="radio" name="status" id="statusInactive" value="Inactive" <?= !$is_active ? 'checked' : ''; ?>>
                                                    <div>
                                                        <div class="fw-bold text-body">Inativa / Oculta</div>
                                                        <small class="text-body-secondary">Temporariamente oculta no site</small>
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
                                <a href="photo-category.php" class="btn btn-outline-secondary px-3 shadow-xs">
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

            <!-- Coluna Lateral de Ajuda e Estatísticas -->
            <div class="col-12 col-xl-4">
                <!-- Card de Impacto das Fotos Vinculadas -->
                <div class="card shadow-sm border-0 bg-body mb-4">
                    <div class="card-header bg-body-tertiary border-bottom border-secondary-subtle py-3">
                        <h6 class="card-title fw-bold text-body mb-0">
                            <i class="bi bi-info-circle text-info me-1"></i> Impacto no Acervo
                        </h6>
                    </div>
                    <div class="card-body p-3">
                        <?php if ($total_fotos_cat > 0): ?>
                            <div class="alert alert-info border-0 bg-info-subtle text-info-emphasis d-flex gap-2 p-3 mb-3 rounded-3" role="alert">
                                <i class="bi bi-shield-exclamation fs-4 flex-shrink-0"></i>
                                <div class="small">
                                    Existem <strong><?= $total_fotos_cat; ?> foto(s)</strong> categorizadas sob esta categoria. Ao renomeá-la, todas as fotos vinculadas refletirão o novo nome automaticamente.
                                </div>
                            </div>

                            <a href="photo.php" class="btn btn-outline-primary btn-sm w-100 shadow-xs mb-2">
                                <i class="bi bi-images me-1"></i> Ver Fotos Desta Categoria
                            </a>
                        <?php else: ?>
                            <div class="text-body-secondary small mb-3">
                                <i class="bi bi-check2-circle text-success me-1"></i> Não há fotos vinculadas a esta categoria no momento. Você pode editá-la livremente ou adicionar novas fotos nela.
                            </div>

                            <a href="photo-add.php" class="btn btn-outline-success btn-sm w-100 shadow-xs mb-2">
                                <i class="bi bi-cloud-arrow-up me-1"></i> Adicionar Foto nesta Categoria
                            </a>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Card de Dicas Rápidas -->
                <div class="card shadow-sm border-0 bg-body">
                    <div class="card-header bg-body-tertiary border-bottom border-secondary-subtle py-3">
                        <h6 class="card-title fw-bold text-body mb-0">
                            <i class="bi bi-lightbulb text-warning me-1"></i> Boas Práticas
                        </h6>
                    </div>
                    <div class="card-body p-3 small text-body-secondary">
                        <ul class="mb-0 ps-3 d-flex flex-column gap-2">
                            <li>Prefira nomes curtos e objetivos (ex: <em>"Recepção"</em>, <em>"Equipamentos"</em>, <em>"Leitos"</em>).</li>
                            <li>Evite duplicar categorias com sentidos semelhantes para não poluir os filtros públicos.</li>
                            <li>Categorias com status <strong>Oculta</strong> não exibem suas respectivas fotos no portal.</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<style>
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
    const inputNome   = document.getElementById('p_category_name');
    const charCounter = document.getElementById('charCounter');
    const form        = document.getElementById('formEditarCategoriaPhoto');
    const btnSalvar   = document.getElementById('btnSalvar');

    // Contador de Caracteres em Tempo Real
    if (inputNome && charCounter) {
        const updateCounter = () => {
            const len = inputNome.value.length;
            charCounter.textContent = `${len}/100 caracteres`;
            if (len > 85) {
                charCounter.className = 'text-warning fw-bold small';
            } else {
                charCounter.className = 'text-body-secondary fw-normal small';
            }
        };
        inputNome.addEventListener('input', updateCounter);
        updateCounter();
        inputNome.focus();
    }

    // Efeito visual nos cards de status radio
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

            // Atualiza badge do topo em tempo real
            const badgeTopo = document.getElementById('badgeStatusTopo');
            if (badgeTopo) {
                if (this.value === 'Active') {
                    badgeTopo.className = 'badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-1 fs-6';
                    badgeTopo.innerHTML = '<i class="bi bi-check-circle-fill me-1"></i> Ativa no Site';
                } else {
                    badgeTopo.className = 'badge bg-warning-subtle text-warning border border-warning-subtle rounded-pill px-3 py-1 fs-6';
                    badgeTopo.innerHTML = '<i class="bi bi-pause-circle-fill me-1"></i> Oculta / Pausada';
                }
            }
        });
    });

    // Envio Assíncrono com AJAX e Notificação SweetAlert2 / Notyf
    if (form) {
        form.addEventListener('submit', function (e) {
            e.preventDefault();

            const nomeVal = inputNome.value.trim();
            if (!nomeVal) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Campo Obrigatório',
                        text: 'Por favor, informe o nome da categoria de fotos.',
                        confirmButtonText: 'Entendido',
                        customClass: { confirmButton: 'btn btn-primary px-4' },
                        buttonsStyling: false
                    });
                } else {
                    alert('Por favor, informe o nome da categoria de fotos.');
                }
                inputNome.focus();
                return;
            }

            // Desabilita botão durante o processamento
            const originalBtnHtml = btnSalvar.innerHTML;
            btnSalvar.disabled = true;
            btnSalvar.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Salvando...';

            const formData = new FormData(form);
            formData.append('action', 'save_category_ajax');

            fetch('photo-category-edit.php?id=<?= $id; ?>', {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                btnSalvar.disabled = false;
                btnSalvar.innerHTML = originalBtnHtml;

                if (data.status === 'success') {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Sucesso!',
                            text: data.message || 'Categoria atualizada com sucesso!',
                            showCancelButton: true,
                            confirmButtonText: '<i class="bi bi-list-ul me-1"></i> Ir para Listagem',
                            cancelButtonText: '<i class="bi bi-pencil me-1"></i> Continuar Editando',
                            customClass: {
                                confirmButton: 'btn btn-primary px-3 me-2',
                                cancelButton: 'btn btn-outline-secondary px-3'
                            },
                            buttonsStyling: false
                        }).then((result) => {
                            if (result.isConfirmed) {
                                window.location.href = 'photo-category.php';
                            }
                        });
                    } else {
                        window.location.href = 'photo-category.php';
                    }
                } else {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'error',
                            title: 'Atenção',
                            html: data.message || 'Não foi possível salvar as alterações.',
                            confirmButtonText: 'Corrigir',
                            customClass: { confirmButton: 'btn btn-danger px-4' },
                            buttonsStyling: false
                        });
                    } else {
                        alert(data.message || 'Erro ao salvar.');
                    }
                }
            })
            .catch(err => {
                console.error(err);
                btnSalvar.disabled = false;
                btnSalvar.innerHTML = originalBtnHtml;

                // Fallback para envio tradicional caso a requisição AJAX falhe
                form.submit();
            });
        });
    }
});
</script>

<?php require_once('footer.php'); ?>
