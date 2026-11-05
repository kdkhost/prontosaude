<?php
require_once('header.php');

// 1. Contagens para KPIs informativos
$stmt_cats = $pdo->query("SELECT COUNT(*) as total,
                                 SUM(CASE WHEN status = 'Active' THEN 1 ELSE 0 END) as ativas,
                                 SUM(CASE WHEN status != 'Active' THEN 1 ELSE 0 END) as inativas
                          FROM tbl_category_photo");
$stats_cats = $stmt_cats->fetch(PDO::FETCH_ASSOC);

$total_categorias = (int)($stats_cats['total'] ?? 0);
$total_ativas     = (int)($stats_cats['ativas'] ?? 0);
$total_inativas   = (int)($stats_cats['inativas'] ?? 0);

$stmt_fotos = $pdo->query("SELECT COUNT(*) FROM tbl_photo");
$total_fotos = (int)$stmt_fotos->fetchColumn();

// 2. Processamento do Envio (Suporte completo a AJAX e POST tradicional)
$is_ajax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
           || (isset($_POST['action']) && $_POST['action'] === 'add_category_ajax');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && (isset($_POST['form1']) || $is_ajax)) {
    $p_category_name = trim($_POST['p_category_name'] ?? '');
    $status          = trim($_POST['status'] ?? 'Active');

    $valid = 1;
    $erros = [];

    // Validação de Nome Obrigatório
    if (empty($p_category_name)) {
        $valid = 0;
        $erros[] = 'Por favor, informe o nome da categoria de fotos.';
    } else {
        // Validação de Duplicidade
        $stmt_check = $pdo->prepare("SELECT p_category_id FROM tbl_category_photo WHERE p_category_name = ?");
        $stmt_check->execute([$p_category_name]);
        if ($stmt_check->rowCount() > 0) {
            $valid = 0;
            $erros[] = 'Já existe uma categoria de foto cadastrada com este mesmo nome.';
        }
    }

    if (!in_array($status, ['Active', 'Inactive'])) {
        $status = 'Active';
    }

    if ($valid === 1) {
        $stmt_ins = $pdo->prepare("INSERT INTO tbl_category_photo (p_category_name, status) VALUES (?, ?)");
        $stmt_ins->execute([$p_category_name, $status]);
        $novo_id = $pdo->lastInsertId();

        $msg_sucesso = 'Categoria de fotos "' . htmlspecialchars($p_category_name) . '" cadastrada com sucesso!';

        if ($is_ajax) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'status'  => 'success',
                'message' => $msg_sucesso,
                'id'      => $novo_id,
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

// Valores para repreenchimento em caso de erro
$p_category_name = htmlspecialchars($_POST['p_category_name'] ?? '');
$status          = $_POST['status'] ?? 'Active';
$is_active       = ($status === 'Active');
?>

<!-- Cabeçalho da Página -->
<div class="app-content-header pb-2 mb-3 border-bottom">
    <div class="container-fluid">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div>
                <h3 class="mb-1 fw-bold">Adicionar Categoria de Foto</h3>
                <ol class="breadcrumb mb-0 small">
                    <li class="breadcrumb-item"><a href="index.php"><i class="bi bi-house-door"></i> Início</a></li>
                    <li class="breadcrumb-item"><a href="photo.php">Multimídia</a></li>
                    <li class="breadcrumb-item"><a href="photo-category.php">Categorias de Fotos</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Nova Categoria</li>
                </ol>
            </div>
            <div class="d-flex flex-wrap align-items-center gap-2">
                <a href="<?= BASE_URL; ?>page.php?slug=fotos" target="_blank" rel="noopener" class="btn btn-outline-info btn-sm shadow-xs" title="Visualizar galeria de fotos no site público">
                    <i class="bi bi-box-arrow-up-right me-1"></i> Ver no Site
                </a>
                <a href="photo.php" class="btn btn-outline-secondary btn-sm shadow-xs" title="Ir para a galeria de fotos">
                    <i class="bi bi-images me-1"></i> Fotos do Acervo
                </a>
                <a href="photo-category.php" class="btn btn-outline-primary btn-sm shadow-xs" title="Voltar à listagem de categorias">
                    <i class="bi bi-tags me-1"></i> Ver Listagem
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
            <!-- KPI 1: Categorias Cadastradas -->
            <div class="col-12 col-sm-6 col-xl-4">
                <div class="card shadow-sm border-0 border-start border-primary border-4 h-100 bg-body">
                    <div class="card-body d-flex align-items-center justify-content-between py-3">
                        <div>
                            <div class="text-body-secondary small text-uppercase fw-bold">Categorias Cadastradas</div>
                            <div class="fs-4 fw-bold text-body mt-1"><?= $total_categorias; ?> categorias</div>
                            <small class="text-body-secondary"><?= $total_ativas; ?> ativas &bull; <?= $total_inativas; ?> ocultas</small>
                        </div>
                        <div class="p-3 bg-primary-subtle text-primary rounded-circle">
                            <i class="bi bi-tags-fill fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- KPI 2: Fotos Vinculadas no Total -->
            <div class="col-12 col-sm-6 col-xl-4">
                <div class="card shadow-sm border-0 border-start border-success border-4 h-100 bg-body">
                    <div class="card-body d-flex align-items-center justify-content-between py-3">
                        <div>
                            <div class="text-body-secondary small text-uppercase fw-bold">Fotos no Acervo</div>
                            <div class="fs-4 fw-bold text-success mt-1"><?= $total_fotos; ?> fotos</div>
                            <small class="text-body-secondary">Distribuídas nas categorias existentes</small>
                        </div>
                        <div class="p-3 bg-success-subtle text-success rounded-circle">
                            <i class="bi bi-images fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- KPI 3: Status Padrão -->
            <div class="col-12 col-sm-6 col-xl-4">
                <div class="card shadow-sm border-0 border-start border-info border-4 h-100 bg-body">
                    <div class="card-body d-flex align-items-center justify-content-between py-3">
                        <div>
                            <div class="text-body-secondary small text-uppercase fw-bold">Status Inicial</div>
                            <div class="mt-2">
                                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-1 fs-6" id="badgeStatusTopo">
                                    <i class="bi bi-check-circle-fill me-1"></i> Ativa por Padrão
                                </span>
                            </div>
                        </div>
                        <div class="p-3 bg-info-subtle text-info rounded-circle">
                            <i class="bi bi-eye-fill fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Formulário de Criação -->
        <div class="row">
            <div class="col-12 col-xl-8">
                <div class="card shadow-sm border-0 bg-body mb-4">
                    <div class="card-header bg-body-tertiary border-bottom border-secondary-subtle py-3 d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-primary rounded-pill p-2"><i class="bi bi-plus-circle-fill"></i></span>
                            <div>
                                <h5 class="card-title fw-bold text-body mb-0">Dados da Nova Categoria</h5>
                                <small class="text-body-secondary">Crie uma nova seção para organizar as fotos da clínica ou hospital</small>
                            </div>
                        </div>
                    </div>

                    <div class="card-body p-4">
                        <form action="" method="post" id="formPhotoCategoryAdd" novalidate>
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
                                        <i class="bi bi-folder-plus"></i>
                                    </span>
                                    <input type="text" 
                                           class="form-control form-control-lg bg-body text-body border-secondary-subtle" 
                                           id="p_category_name" 
                                           name="p_category_name" 
                                           value="<?= $p_category_name; ?>" 
                                           maxlength="100" 
                                           required 
                                           placeholder="Ex: Consultórios Médicos, Recepção, UTI Neonatal, Fachada...">
                                </div>
                                <div class="form-text text-body-secondary small mt-1">
                                    <i class="bi bi-info-circle me-1"></i> Este nome se tornará um botão de filtro interativo na página pública de fotos.
                                </div>
                            </div>

                            <!-- Campo: Status de Visibilidade Inicial -->
                            <div class="mb-4">
                                <label class="form-label fw-bold text-body d-block">
                                    <i class="bi bi-toggle-on text-primary me-1"></i> Visibilidade Inicial no Portal
                                </label>
                                
                                <div class="row g-3">
                                    <div class="col-12 col-sm-6">
                                        <label class="card h-100 p-3 border border-primary bg-primary-subtle bg-opacity-10 rounded-3 cursor-pointer radio-status-card" style="cursor: pointer;">
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
                                        <label class="card h-100 p-3 border border-secondary-subtle bg-body rounded-3 cursor-pointer radio-status-card" style="cursor: pointer;">
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
                                <a href="photo-category.php" class="btn btn-outline-secondary px-3 shadow-xs">
                                    <i class="bi bi-arrow-left me-1"></i> Cancelar e Voltar
                                </a>
                                <div class="d-flex align-items-center gap-2">
                                    <button type="submit" class="btn btn-primary px-4 shadow-sm" id="btnSalvar">
                                        <i class="bi bi-check-lg me-1"></i> Salvar Categoria
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Coluna Lateral de Ajuda e Boas Práticas -->
            <div class="col-12 col-xl-4">
                <!-- Card de Boas Práticas -->
                <div class="card shadow-sm border-0 bg-body mb-4">
                    <div class="card-header bg-body-tertiary border-bottom border-secondary-subtle py-3">
                        <h6 class="card-title fw-bold text-body mb-0">
                            <i class="bi bi-lightbulb text-warning me-1"></i> Dicas de Organização
                        </h6>
                    </div>
                    <div class="card-body p-3 small text-body-secondary">
                        <ul class="mb-0 ps-3 d-flex flex-column gap-2">
                            <li><strong>Nomes Claros:</strong> Use títulos intuitivos que os pacientes reconheçam facilmente (ex: <em>"Instalações"</em>, <em>"Equipamentos"</em>, <em>"Recepção"</em>).</li>
                            <li><strong>Evite Excesso de Categorias:</strong> Um número entre 3 a 6 categorias proporciona uma navegação fluida nos filtros da galeria pública.</li>
                            <li><strong>Próximo Passo:</strong> Logo após cadastrar a categoria, você poderá vincular fotos diretamente através da tela de cadastro de fotos.</li>
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
                        <a href="photo-category.php" class="btn btn-outline-secondary btn-sm text-start">
                            <i class="bi bi-tags-fill text-primary me-2"></i> Ver Todas as Categorias
                        </a>
                        <a href="photo-add.php" class="btn btn-outline-secondary btn-sm text-start">
                            <i class="bi bi-cloud-arrow-up text-success me-2"></i> Adicionar Foto ao Acervo
                        </a>
                        <a href="<?= BASE_URL; ?>page.php?slug=fotos" target="_blank" rel="noopener" class="btn btn-outline-secondary btn-sm text-start">
                            <i class="bi bi-box-arrow-up-right text-info me-2"></i> Abrir Galeria Pública no Site
                        </a>
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
    const form        = document.getElementById('formPhotoCategoryAdd');
    const btnSalvar   = document.getElementById('btnSalvar');

    // 1. Contador de Caracteres em Tempo Real
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

    // 2. Efeito Visual nos Cards de Status
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

            // Atualiza badge de status do topo em tempo real
            const badgeTopo = document.getElementById('badgeStatusTopo');
            if (badgeTopo) {
                if (this.value === 'Active') {
                    badgeTopo.className = 'badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-1 fs-6';
                    badgeTopo.innerHTML = '<i class="bi bi-check-circle-fill me-1"></i> Ativa por Padrão';
                } else {
                    badgeTopo.className = 'badge bg-warning-subtle text-warning border border-warning-subtle rounded-pill px-3 py-1 fs-6';
                    badgeTopo.innerHTML = '<i class="bi bi-pause-circle-fill me-1"></i> Oculta por Padrão';
                }
            }
        });
    });

    // 3. Envio Assíncrono com AJAX e SweetAlert2
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

            // Desabilita botão durante processamento
            const originalBtnHtml = btnSalvar.innerHTML;
            btnSalvar.disabled = true;
            btnSalvar.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Salvando...';

            const formData = new FormData(form);
            formData.append('action', 'add_category_ajax');

            fetch('photo-category-add.php', {
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
                            title: 'Categoria Cadastrada!',
                            text: data.message || 'Categoria de fotos cadastrada com sucesso!',
                            showCancelButton: true,
                            confirmButtonText: '<i class="bi bi-list-ul me-1"></i> Ver Categorias',
                            cancelButtonText: '<i class="bi bi-plus-lg me-1"></i> Adicionar Mais Uma',
                            customClass: {
                                confirmButton: 'btn btn-primary px-3 me-2',
                                cancelButton: 'btn btn-outline-secondary px-3'
                            },
                            buttonsStyling: false
                        }).then((result) => {
                            if (result.isConfirmed) {
                                window.location.href = 'photo-category.php';
                            } else {
                                // Limpa campo para adicionar outra
                                form.reset();
                                inputNome.value = '';
                                inputNome.focus();
                                if (charCounter) charCounter.textContent = '0/100 caracteres';
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
                            html: data.message || 'Não foi possível cadastrar a categoria.',
                            confirmButtonText: 'Corrigir',
                            customClass: { confirmButton: 'btn btn-danger px-4' },
                            buttonsStyling: false
                        });
                    } else {
                        alert(data.message || 'Erro ao cadastrar.');
                    }
                }
            })
            .catch(err => {
                console.error(err);
                btnSalvar.disabled = false;
                btnSalvar.innerHTML = originalBtnHtml;
                form.submit(); // fallback tradicional
            });
        });
    }
});
</script>

<?php require_once('footer.php'); ?>
