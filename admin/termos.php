<?php
require_once('header.php');

// Consulta todos os termos cadastrados
$stmt = $pdo->prepare("SELECT * FROM tbl_termos ORDER BY id ASC");
$stmt->execute();
$termos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Identifica o documento principal (prioriza id=1 ou o primeiro da lista)
$doc_principal = null;
if (!empty($termos)) {
    foreach ($termos as $t) {
        if ((int)$t['id'] === 1) {
            $doc_principal = $t;
            break;
        }
    }
    if (!$doc_principal) {
        $doc_principal = $termos[0];
    }
}

// Dados do documento principal
$t_id        = (int)($doc_principal['id'] ?? 1);
$t_titulo    = $doc_principal['titulo'] ?? 'Termos de Uso & Condições Gerais de Atendimento';
$t_banner    = $doc_principal['banner'] ?? '';
$t_detalhes  = $doc_principal['detalhes'] ?? '';
$t_status    = (int)($doc_principal['status'] ?? 1);
$t_criado    = $doc_principal['criado'] ?? '';
$t_modificado= $doc_principal['modificado'] ?? '';

// Resolução segura do banner principal
$banner_principal_url = BASE_URL . 'assets/uploads/department-1.png';
$tem_banner_custom = false;
if (!empty($t_banner)) {
    if (file_exists(__DIR__ . '/../assets/uploads/termos/' . $t_banner)) {
        $banner_principal_url = BASE_URL . 'assets/uploads/termos/' . $t_banner;
        $tem_banner_custom = true;
    } elseif (file_exists(__DIR__ . '/../assets/uploads/' . $t_banner)) {
        $banner_principal_url = BASE_URL . 'assets/uploads/' . $t_banner;
        $tem_banner_custom = true;
    }
}

// Métricas do texto
$texto_puro = trim(strip_tags($t_detalhes));
$total_palavras = !empty($texto_puro) ? str_word_count($texto_puro) : 0;
$tempo_leitura = max(1, ceil($total_palavras / 160));

// Formatação de data da última revisão
$data_revisao_fmt = 'Recente';
if (!empty($t_modificado) && $t_modificado !== '0000-00-00 00:00:00') {
    $timestamp = strtotime($t_modificado);
    if ($timestamp && $timestamp > 0) {
        $data_revisao_fmt = date('d/m/Y \à\s H:i', $timestamp);
    }
} elseif (!empty($t_criado) && $t_criado !== '0000-00-00 00:00:00') {
    $timestamp = strtotime($t_criado);
    if ($timestamp && $timestamp > 0) {
        $data_revisao_fmt = date('d/m/Y \à\s H:i', $timestamp);
    }
}
?>

<div class="app-content-header">
    <div class="container-fluid">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div>
                <h3 class="mb-1 fw-bold text-dark d-flex align-items-center gap-2">
                    <i class="bi bi-file-earmark-medical text-primary"></i>
                    Termos de Uso &amp; Condições Gerais
                </h3>
                <ol class="breadcrumb mb-0 small">
                    <li class="breadcrumb-item"><a href="index.php" class="text-decoration-none"><i class="bi bi-house-door"></i> Início</a></li>
                    <li class="breadcrumb-item text-secondary">Institucional</li>
                    <li class="breadcrumb-item active" aria-current="page">Termos de Uso</li>
                </ol>
            </div>
            <div class="d-flex flex-wrap align-items-center gap-2">
                <a href="../termos.php" target="_blank" class="btn btn-outline-primary btn-sm px-3 shadow-sm" title="Visualizar página pública dos Termos de Uso">
                    <i class="bi bi-box-arrow-up-right me-1"></i> Ver no Site
                </a>
                <a href="termos-edit.php?id=<?= $t_id; ?>" class="btn btn-primary btn-sm px-3 shadow-sm" title="Editar texto e cláusulas dos termos">
                    <i class="bi bi-pencil-square me-1"></i> Editar Documento
                </a>
                <a href="termos-add.php" class="btn btn-outline-secondary btn-sm px-3 shadow-sm">
                    <i class="bi bi-plus-lg me-1"></i> Adicionar Versão
                </a>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">

        <!-- Mensagens de Notificação de Sessão -->
        <?php if (!empty($_SESSION['sucesso'])): ?>
            <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm d-flex align-items-center gap-2 mb-4" role="alert">
                <i class="bi bi-check-circle-fill fs-5 text-success"></i>
                <div class="flex-grow-1 fw-medium"><?= $_SESSION['sucesso']; ?></div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
            </div>
            <?php unset($_SESSION['sucesso']); ?>
        <?php endif; ?>

        <?php if (!empty($_SESSION['erro'])): ?>
            <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm d-flex align-items-center gap-2 mb-4" role="alert">
                <i class="bi bi-exclamation-triangle-fill fs-5 text-danger"></i>
                <div class="flex-grow-1 fw-medium"><?= $_SESSION['erro']; ?></div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
            </div>
            <?php unset($_SESSION['erro']); ?>
        <?php endif; ?>

        <!-- Cards de Indicadores (KPIs) -->
        <div class="row g-3 mb-4">
            <!-- KPI 1: Status Geral -->
            <div class="col-sm-6 col-xl-3">
                <div class="card border-0 shadow-sm rounded-3 h-100 bg-white">
                    <div class="card-body p-3 d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted small fw-semibold text-uppercase d-block mb-1">Status Jurídico</span>
                            <h4 class="mb-0 fw-bold text-dark d-flex align-items-center gap-2">
                                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill fs-6 px-3 py-1">
                                    <i class="bi bi-shield-fill-check me-1"></i> Vigente &amp; Válido
                                </span>
                            </h4>
                            <small class="text-muted mt-1 d-block">Publicado no portal</small>
                        </div>
                        <div class="rounded-3 p-3 bg-success-subtle text-success">
                            <i class="bi bi-journal-check fs-3"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- KPI 2: Banner Institucional -->
            <div class="col-sm-6 col-xl-3">
                <div class="card border-0 shadow-sm rounded-3 h-100 bg-white">
                    <div class="card-body p-3 d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted small fw-semibold text-uppercase d-block mb-1">Banner Superior</span>
                            <h5 class="mb-0 fw-bold text-dark">
                                <?= $tem_banner_custom ? 'Banner Ativo' : 'Banner Padrão'; ?>
                            </h5>
                            <small class="text-muted mt-1 d-block">1200 &times; 400 px recomendado</small>
                        </div>
                        <div class="rounded-3 p-3 bg-primary-subtle text-primary">
                            <i class="bi bi-image fs-3"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- KPI 3: Última Revisão -->
            <div class="col-sm-6 col-xl-3">
                <div class="card border-0 shadow-sm rounded-3 h-100 bg-white">
                    <div class="card-body p-3 d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted small fw-semibold text-uppercase d-block mb-1">Última Atualização</span>
                            <h6 class="mb-0 fw-bold text-dark text-truncate" style="max-width: 170px;" title="<?= $data_revisao_fmt; ?>">
                                <?= $data_revisao_fmt; ?>
                            </h6>
                            <small class="text-muted mt-1 d-block">Revisão de termos</small>
                        </div>
                        <div class="rounded-3 p-3 bg-info-subtle text-info">
                            <i class="bi bi-calendar-check fs-3"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- KPI 4: Extensão e Leitura -->
            <div class="col-sm-6 col-xl-3">
                <div class="card border-0 shadow-sm rounded-3 h-100 bg-white">
                    <div class="card-body p-3 d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted small fw-semibold text-uppercase d-block mb-1">Condições Gerais</span>
                            <h5 class="mb-0 fw-bold text-dark">
                                ~<?= $tempo_leitura; ?> min <span class="fs-6 text-muted fw-normal">(<?= number_format($total_palavras, 0, ',', '.'); ?> palavras)</span>
                            </h5>
                            <small class="text-success fw-medium mt-1 d-block">
                                <i class="bi bi-check-all"></i> Regulamentação Médica
                            </small>
                        </div>
                        <div class="rounded-3 p-3 bg-warning-subtle text-warning">
                            <i class="bi bi-file-text fs-3"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Painel de Destaque Widescreen: Documento Vigente -->
        <?php if ($doc_principal): ?>
        <div class="card card-outline card-primary shadow-sm rounded-3 mb-4 border-0">
            <div class="card-header bg-white py-3 border-bottom d-flex flex-wrap align-items-center justify-content-between gap-2">
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-primary rounded-pill p-2"><i class="bi bi-file-earmark-ruled"></i></span>
                    <div>
                        <h5 class="card-title fw-bold text-dark mb-0"><?= htmlspecialchars($t_titulo); ?></h5>
                        <small class="text-muted">Documento em exibição no endereço público <code class="text-primary">/termos.php</code></small>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <button type="button" class="btn btn-warning btn-sm fw-semibold shadow-sm" onclick="abrirModalTrocarBannerTermos(<?= $t_id; ?>, '<?= htmlspecialchars(addslashes($t_titulo)); ?>', '<?= $banner_principal_url; ?>')">
                        <i class="bi bi-camera me-1"></i> Trocar Banner
                    </button>
                    <a href="termos-edit.php?id=<?= $t_id; ?>" class="btn btn-primary btn-sm fw-semibold shadow-sm">
                        <i class="bi bi-pencil-square me-1"></i> Editar Conteúdo
                    </a>
                </div>
            </div>
            <div class="card-body p-4">
                <div class="row g-4 align-items-stretch">
                    <!-- Coluna Banner com Miniatura Widescreen -->
                    <div class="col-lg-5">
                        <div class="border rounded-3 p-2 bg-light h-100 d-flex flex-column justify-content-between">
                            <div>
                                <div class="d-flex align-items-center justify-content-between mb-2 px-1">
                                    <span class="small fw-semibold text-secondary"><i class="bi bi-card-image me-1"></i> Banner Superior Ativo</span>
                                    <span class="badge bg-secondary-subtle text-secondary small">1200 &times; 400 px</span>
                                </div>
                                <div class="position-relative overflow-hidden rounded-3 shadow-sm border bg-dark" style="aspect-ratio: 16/7;">
                                    <img src="<?= $banner_principal_url; ?>" alt="<?= htmlspecialchars($t_titulo); ?>" class="w-100 h-100 object-fit-cover" id="imgPreviewBannerPrincipalTermos">
                                    <div class="position-absolute bottom-0 start-0 w-100 p-2 bg-dark bg-opacity-75 text-white d-flex align-items-center justify-content-between">
                                        <small class="text-truncate me-2" style="font-size: 0.75rem;">
                                            <i class="bi bi-file-image me-1"></i> <?= htmlspecialchars($t_banner ?: 'department-1.png (padrão)'); ?>
                                        </small>
                                        <button type="button" class="btn btn-sm btn-light py-0 px-2 fw-semibold" style="font-size: 0.72rem;" onclick="abrirModalTrocarBannerTermos(<?= $t_id; ?>, '<?= htmlspecialchars(addslashes($t_titulo)); ?>', '<?= $banner_principal_url; ?>')">
                                            Alterar
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-3 p-3 bg-white rounded-3 border">
                                <div class="d-flex align-items-center justify-content-between small text-muted mb-1">
                                    <span>Identificador:</span>
                                    <span class="fw-bold text-dark">#<?= $t_id; ?></span>
                                </div>
                                <div class="d-flex align-items-center justify-content-between small text-muted mb-1">
                                    <span>Vigência:</span>
                                    <span class="badge bg-success-subtle text-success border border-success-subtle">Vigente</span>
                                </div>
                                <div class="d-flex align-items-center justify-content-between small text-muted">
                                    <span>Última revisão:</span>
                                    <span class="fw-medium text-dark"><?= $data_revisao_fmt; ?></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Coluna Leitor Executivo do Documento -->
                    <div class="col-lg-7">
                        <div class="d-flex flex-column h-100 border rounded-3 p-3 bg-white">
                            <div class="d-flex align-items-center justify-content-between pb-2 mb-3 border-bottom">
                                <div class="d-flex align-items-center gap-2">
                                    <i class="bi bi-journal-text text-primary fs-5"></i>
                                    <span class="fw-bold text-dark">Visualizador dos Termos Gerais</span>
                                </div>
                                <button type="button" class="btn btn-outline-secondary btn-sm px-2 py-1" onclick="abrirModalDocTermos(<?= $t_id; ?>)" title="Expandir para leitura completa">
                                    <i class="bi bi-arrows-fullscreen me-1"></i> Tela Cheia
                                </button>
                            </div>
                            <!-- Conteúdo renderizado em box com scroll limpo -->
                            <div class="flex-grow-1 p-3 bg-light rounded-3 border overflow-y-auto mb-3" style="max-height: 290px; font-size: 0.92rem; line-height: 1.65; color: #374151;">
                                <?= $t_detalhes; ?>
                            </div>
                            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 pt-2 border-top">
                                <span class="small text-muted">
                                    <i class="bi bi-info-circle text-primary me-1"></i> As alterações salvas surtem efeito imediato no site.
                                </span>
                                <div class="d-flex gap-2">
                                    <button type="button" class="btn btn-outline-primary btn-sm px-3" onclick="abrirModalDocTermos(<?= $t_id; ?>)">
                                        <i class="bi bi-eye me-1"></i> Ler Completo
                                    </button>
                                    <a href="termos-edit.php?id=<?= $t_id; ?>" class="btn btn-primary btn-sm px-3">
                                        <i class="bi bi-pencil-square me-1"></i> Editar Texto
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Tabela de Registros / Termos Cadastrados -->
        <div class="card card-outline card-secondary shadow-sm rounded-3 border-0 mb-4">
            <div class="card-header bg-white py-3 border-bottom d-flex flex-wrap align-items-center justify-content-between gap-2">
                <h5 class="card-title fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                    <i class="bi bi-table text-primary"></i>
                    Registros e Versões de Termos Cadastrados
                </h5>
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-3 py-1 small">
                    <?= count($termos); ?> registro(s) encontrado(s)
                </span>
            </div>
            <div class="card-body p-3 table-responsive">
                <table id="tabelaTermos" class="table table-hover table-striped align-middle w-100">
                    <thead class="table-light">
                        <tr>
                            <th width="60" class="text-center">#ID</th>
                            <th width="130">Banner</th>
                            <th width="200">Título do Documento</th>
                            <th>Prévia das Condições</th>
                            <th width="140" class="text-center">Atualizado</th>
                            <th width="140" class="text-center">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        foreach ($termos as $row):
                            $row_id = (int)$row['id'];
                            $row_titulo = htmlspecialchars($row['titulo'] ?? 'Termos de Uso');
                            $row_banner = $row['banner'] ?? '';
                            $row_detalhes_raw = $row['detalhes'] ?? '';
                            $row_detalhes_txt = strip_tags($row_detalhes_raw);
                            $row_mod = !empty($row['modificado']) && $row['modificado'] !== '0000-00-00 00:00:00' ? $row['modificado'] : ($row['criado'] ?? '');
                            $row_data_fmt = 'Recente';
                            if (!empty($row_mod)) {
                                $ts = strtotime($row_mod);
                                if ($ts && $ts > 0) $row_data_fmt = date('d/m/Y', $ts);
                            }

                            // Caminho do banner
                            $row_banner_url = BASE_URL . 'assets/uploads/department-1.png';
                            if (!empty($row_banner)) {
                                if (file_exists(__DIR__ . '/../assets/uploads/termos/' . $row_banner)) {
                                    $row_banner_url = BASE_URL . 'assets/uploads/termos/' . $row_banner;
                                } elseif (file_exists(__DIR__ . '/../assets/uploads/' . $row_banner)) {
                                    $row_banner_url = BASE_URL . 'assets/uploads/' . $row_banner;
                                }
                            }
                        ?>
                        <tr id="tr-termo-<?= $row_id; ?>">
                            <td class="text-center fw-bold text-muted"><?= $row_id; ?></td>

                            <!-- Miniatura do Banner com Botão Trocar -->
                            <td>
                                <div class="position-relative overflow-hidden rounded-2 border shadow-xs" style="width: 120px; aspect-ratio: 16/8; background-color: #f8fafc;">
                                    <img src="<?= $row_banner_url; ?>" alt="<?= $row_titulo; ?>" class="w-100 h-100 object-fit-cover" style="transition: transform 0.2s ease;">
                                </div>
                                <button type="button" class="btn btn-link btn-sm text-decoration-none p-0 mt-1 small text-primary" onclick="abrirModalTrocarBannerTermos(<?= $row_id; ?>, '<?= htmlspecialchars(addslashes($row['titulo'])); ?>', '<?= $row_banner_url; ?>')">
                                    <i class="bi bi-camera me-1"></i> Trocar
                                </button>
                            </td>

                            <!-- Título do Documento -->
                            <td>
                                <div class="fw-bold text-dark fs-6"><?= $row_titulo; ?></div>
                                <?php if ($row_id === 1): ?>
                                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill small mt-1">
                                        <i class="bi bi-star-fill me-1"></i> Principal do Portal
                                    </span>
                                <?php else: ?>
                                    <span class="badge bg-secondary-subtle text-secondary rounded-pill small mt-1">Registro Adicional</span>
                                <?php endif; ?>
                            </td>

                            <!-- Prévia do Conteúdo -->
                            <td>
                                <div class="small text-secondary text-truncate" style="max-width: 420px;" title="<?= htmlspecialchars($row_detalhes_txt); ?>">
                                    <?= htmlspecialchars(mb_strimwidth($row_detalhes_txt, 0, 120, '...')); ?>
                                </div>
                                <button type="button" class="btn btn-link btn-sm text-decoration-none p-0 mt-1 small" onclick="abrirModalDocTermos(<?= $row_id; ?>)">
                                    <i class="bi bi-text-paragraph me-1"></i> Ler texto completo
                                </button>
                            </td>

                            <!-- Data de Atualização -->
                            <td class="text-center text-nowrap">
                                <span class="badge bg-light text-dark border small">
                                    <i class="bi bi-clock-history me-1"></i> <?= $row_data_fmt; ?>
                                </span>
                            </td>

                            <!-- Ações -->
                            <td class="text-center text-nowrap">
                                <div class="d-flex align-items-center justify-content-center gap-1">
                                    <!-- Botão Prévia -->
                                    <button type="button" class="btn btn-outline-info btn-sm" onclick="abrirModalDocTermos(<?= $row_id; ?>)" title="Visualizar documento em detalhes">
                                        <i class="bi bi-eye"></i>
                                    </button>

                                    <!-- Botão Editar -->
                                    <a href="termos-edit.php?id=<?= $row_id; ?>" class="btn btn-outline-primary btn-sm" title="Editar este documento">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>

                                    <!-- Botão Excluir Seguro com SweetAlert2 -->
                                    <button type="button" class="btn btn-outline-danger btn-sm btn-delete-termo"
                                            data-id="<?= $row_id; ?>"
                                            data-title="<?= $row_titulo; ?>"
                                            data-is-main="<?= ($row_id === 1) ? '1' : '0'; ?>"
                                            title="Excluir documento">
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

<!-- ========================================== -->
<!-- MODAL DINÂMICO 1: TROCA RÁPIDA DE BANNER    -->
<!-- ========================================== -->
<div class="modal fade" id="modalTrocarBannerTermos" tabindex="-1" aria-labelledby="modalTrocarBannerTermosLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-3">
            <form action="termos-edit.php" method="POST" enctype="multipart/form-data" id="formTrocaBannerTermos">
                <input type="hidden" name="id" id="mbtId" value="<?= $t_id; ?>">
                
                <div class="modal-header bg-light py-3 border-bottom">
                    <h5 class="modal-title fw-bold text-dark d-flex align-items-center gap-2" id="modalTrocarBannerTermosLabel">
                        <i class="bi bi-image text-primary"></i>
                        <span>Alterar Imagem do Banner</span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>

                <div class="modal-body p-4">
                    <div class="alert alert-info border-0 shadow-none d-flex align-items-center gap-2 py-2 mb-3 small">
                        <i class="bi bi-info-circle-fill fs-5 text-info"></i>
                        <div>
                            Dimensão recomendada: <strong>1200 &times; 400 pixels</strong>.<br>
                            Formatos suportados: JPG, JPEG, PNG, WEBP ou GIF.
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-secondary mb-1">Banner Atual</label>
                        <div class="border rounded-3 p-1 bg-light text-center overflow-hidden" style="max-height: 150px;">
                            <img src="<?= $banner_principal_url; ?>" alt="Banner Atual" id="mbtPreviewAtual" class="img-fluid rounded" style="max-height: 140px; width: auto; object-fit: cover;">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="inputBannerFileTermos" class="form-label small fw-semibold text-dark mb-1">Selecione o Novo Arquivo</label>
                        <input type="file" class="form-control" name="banner" id="inputBannerFileTermos" accept="image/*" required>
                    </div>

                    <!-- Pré-visualização da Nova Imagem Selecionada -->
                    <div id="boxNovoPreviewTermos" class="d-none">
                        <label class="form-label small fw-semibold text-success mb-1">
                            <i class="bi bi-check2-circle me-1"></i> Nova Imagem Pronta para Envio:
                        </label>
                        <div class="border border-success rounded-3 p-1 bg-light text-center overflow-hidden" style="max-height: 150px;">
                            <img src="" alt="Nova Prévia" id="mbtPreviewNovo" class="img-fluid rounded" style="max-height: 140px; width: auto; object-fit: cover;">
                        </div>
                    </div>
                </div>

                <div class="modal-footer bg-light py-2 border-top d-flex justify-content-between">
                    <button type="button" class="btn btn-secondary btn-sm px-3" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-1"></i> Cancelar
                    </button>
                    <button type="submit" name="UpdateBanner" class="btn btn-success btn-sm px-4 fw-semibold shadow-sm">
                        <i class="bi bi-cloud-arrow-up me-1"></i> Salvar e Atualizar Banner
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ========================================== -->
<!-- MODAL DINÂMICO 2: LEITURA COMPLETA DO DOC   -->
<!-- ========================================== -->
<div class="modal fade" id="modalVisualizarDocTermos" tabindex="-1" aria-labelledby="modalVisualizarDocTermosLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg rounded-3">
            <div class="modal-header bg-light py-3 border-bottom d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-primary rounded-pill p-2"><i class="bi bi-file-earmark-medical"></i></span>
                    <div>
                        <h5 class="modal-title fw-bold text-dark mb-0" id="mdtTitulo">Termos de Uso</h5>
                        <small class="text-muted" id="mdtSubtitulo">Documento Oficial Pronto Saúde</small>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>

            <div class="modal-body p-4 bg-light">
                <div class="card border-0 shadow-sm rounded-3 p-4 bg-white">
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 pb-3 mb-4 border-bottom">
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2 rounded-pill small">
                                <i class="bi bi-check-circle-fill me-1"></i> Vigente &amp; Homologado
                            </span>
                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-2 rounded-pill small">
                                <i class="bi bi-journal-medical me-1"></i> Atendimento &amp; Serviços
                            </span>
                        </div>
                        <div class="small text-muted" id="mdtDataAtualizacao">
                            <i class="bi bi-clock-history me-1"></i> Revisado recentemente
                        </div>
                    </div>

                    <!-- Conteúdo HTML do Documento -->
                    <div id="mdtConteudo" class="p-3 bg-white rounded-3" style="font-size: 1rem; line-height: 1.75; color: #1f2937;">
                        <!-- Inserido dinamicamente via JS -->
                    </div>
                </div>
            </div>

            <div class="modal-footer bg-body border-top py-2 d-flex justify-content-between">
                <div class="small text-muted">
                    <i class="bi bi-shield-fill-check text-success me-1"></i> Centro Médico Pronto Saúde • Gardênia Azul, Jacarepaguá - RJ
                </div>
                <div class="d-flex gap-2">
                    <a href="#" id="mdtLinkEditar" class="btn btn-primary btn-sm px-3">
                        <i class="bi bi-pencil-square me-1"></i> Editar Documento
                    </a>
                    <button type="button" class="btn btn-secondary btn-sm px-3" data-bs-dismiss="modal">
                        Fechar
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Objeto JSON contendo os dados dos termos para abertura instantânea do modal -->
<script>
const TERMOS_DATA = <?= json_encode($termos, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP); ?>;
</script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // 1. DataTables com Tradução Completa
    let table = null;
    if (typeof window.jQuery !== 'undefined' && typeof window.jQuery.fn.DataTable !== 'undefined') {
        table = $('#tabelaTermos').DataTable({
            responsive: true,
            language: {
                search: "Buscar Documento:",
                lengthMenu: "Mostrar _MENU_ registros",
                info: "Exibindo _START_ a _END_ de _TOTAL_ documentos",
                infoEmpty: "Nenhum documento cadastrado",
                infoFiltered: "(filtrado de _MAX_ no total)",
                zeroRecords: "Nenhum documento encontrado",
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

    // 2. Preview instantâneo do Banner no Modal
    const inputFile = document.getElementById('inputBannerFileTermos');
    const boxNovoPreview = document.getElementById('boxNovoPreviewTermos');
    const mbtPreviewNovo = document.getElementById('mbtPreviewNovo');

    if (inputFile) {
        inputFile.addEventListener('change', function () {
            if (this.files && this.files[0]) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    if (mbtPreviewNovo) mbtPreviewNovo.src = e.target.result;
                    if (boxNovoPreview) boxNovoPreview.classList.remove('d-none');
                };
                reader.readAsDataURL(this.files[0]);
            } else {
                if (boxNovoPreview) boxNovoPreview.classList.add('d-none');
            }
        });
    }

    // 3. Exclusão Segura com SweetAlert2
    const deleteButtons = document.querySelectorAll('.btn-delete-termo');
    deleteButtons.forEach(btn => {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            const id = this.dataset.id;
            const title = this.dataset.title || 'este Termo de Uso';
            const isMain = this.dataset.isMain === '1';

            if (isMain) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Atenção: Documento Principal',
                        text: 'Este é o Termo de Uso principal exibido no portal. Deseja realmente excluí-lo?',
                        showCancelButton: true,
                        confirmButtonColor: '#dc3545',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: 'Sim, desejo excluir',
                        cancelButtonText: 'Cancelar'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            executarExclusaoTermos(id, title);
                        }
                    });
                } else {
                    executarExclusaoTermos(id, title);
                }
                return;
            }

            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Confirmar Exclusão',
                    html: `Tem certeza de que deseja excluir <strong>"${title}"</strong>?<br><small class="text-danger">Esta ação removerá o registro e os arquivos vinculados.</small>`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: '<i class="bi bi-trash3 me-1"></i> Sim, excluir',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        executarExclusaoTermos(id, title);
                    }
                });
            } else {
                executarExclusaoTermos(id, title);
            }
        });
    });

    function executarExclusaoTermos(id, title) {
        fetch('termos-delete.php?id=' + id + '&ajax=1', {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Excluído!',
                        text: data.message || 'O termo de uso foi removido com sucesso.',
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        window.location.reload();
                    });
                } else {
                    window.location.reload();
                }
            } else {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'error',
                        title: 'Não foi possível excluir',
                        text: data.message || 'Ocorreu um erro ao excluir o registro.'
                    });
                } else {
                    alert(data.message);
                }
            }
        })
        .catch(err => {
            console.error(err);
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'error',
                    title: 'Falha de Comunicação',
                    text: 'Ocorreu um erro ao comunicar com o servidor. Recarregue a página.'
                });
            } else {
                window.location.href = 'termos-delete.php?id=' + id;
            }
        });
    }

    // 4. Modal de Troca de Banner Global
    window.abrirModalTrocarBannerTermos = function(id, titulo, bannerUrl) {
        document.getElementById('mbtId').value = id;
        document.getElementById('mbtPreviewAtual').src = bannerUrl;
        
        const labelEl = document.getElementById('modalTrocarBannerTermosLabel');
        if (labelEl) {
            labelEl.innerHTML = `<i class="bi bi-image text-primary"></i> <span>Alterar Banner: ${titulo}</span>`;
        }

        const inputEl = document.getElementById('inputBannerFileTermos');
        if (inputEl) inputEl.value = '';

        const boxNovo = document.getElementById('boxNovoPreviewTermos');
        if (boxNovo) boxNovo.classList.add('d-none');

        const modalEl = document.getElementById('modalTrocarBannerTermos');
        if (modalEl && typeof bootstrap !== 'undefined') {
            bootstrap.Modal.getOrCreateInstance(modalEl).show();
        }
    };

    // 5. Modal de Leitura do Documento Completo
    window.abrirModalDocTermos = function(id) {
        const doc = TERMOS_DATA.find(item => parseInt(item.id) === parseInt(id));
        if (!doc) return;

        document.getElementById('mdtTitulo').textContent = doc.titulo || 'Termos de Uso';
        document.getElementById('mdtConteudo').innerHTML = doc.detalhes || '<em class="text-muted">Nenhum texto cadastrado para este documento.</em>';
        
        const dataMod = doc.modificado || doc.criado || '';
        document.getElementById('mdtDataAtualizacao').innerHTML = `<i class="bi bi-clock-history me-1"></i> Última modificação: ${dataMod || 'Vigente'}`;
        
        const editLink = document.getElementById('mdtLinkEditar');
        if (editLink) editLink.href = 'termos-edit.php?id=' + doc.id;

        const modalEl = document.getElementById('modalVisualizarDocTermos');
        if (modalEl && typeof bootstrap !== 'undefined') {
            bootstrap.Modal.getOrCreateInstance(modalEl).show();
        }
    };
});
</script>

<?php require_once('footer.php'); ?>
