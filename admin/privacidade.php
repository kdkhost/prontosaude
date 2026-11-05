<?php
require_once('header.php');

// Consulta todas as políticas cadastradas
$stmt = $pdo->prepare("SELECT * FROM tbl_privacidade ORDER BY id ASC");
$stmt->execute();
$politicas = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Identifica o documento principal
$doc_principal = null;
if (!empty($politicas)) {
    foreach ($politicas as $p) {
        if ((int)$p['id'] === 1) {
            $doc_principal = $p;
            break;
        }
    }
    if (!$doc_principal) {
        $doc_principal = $politicas[0];
    }
}

$p_id        = (int)($doc_principal['id'] ?? 1);
$p_titulo    = $doc_principal['titulo'] ?? 'Política de Privacidade & Proteção de Dados';
$p_banner    = $doc_principal['banner'] ?? '';
$p_detalhes  = $doc_principal['detalhes'] ?? '';
$p_status    = (int)($doc_principal['status'] ?? 1);
$p_criado    = $doc_principal['criado'] ?? '';
$p_modificado= $doc_principal['modificado'] ?? '';

// Resolução de imagem de banner
$banner_principal_url = BASE_URL . 'assets/uploads/department-1.png';
$tem_banner_custom = false;
if (!empty($p_banner)) {
    if (file_exists(__DIR__ . '/../assets/uploads/privacidade/' . $p_banner)) {
        $banner_principal_url = BASE_URL . 'assets/uploads/privacidade/' . $p_banner;
        $tem_banner_custom = true;
    } elseif (file_exists(__DIR__ . '/../assets/uploads/' . $p_banner)) {
        $banner_principal_url = BASE_URL . 'assets/uploads/' . $p_banner;
        $tem_banner_custom = true;
    }
}

// Métricas de leitura
$texto_puro = trim(strip_tags($p_detalhes));
$total_palavras = !empty($texto_puro) ? str_word_count($texto_puro) : 0;
$tempo_leitura = max(1, ceil($total_palavras / 160));

// Formatação de data
$data_revisao_fmt = 'Recente';
if (!empty($p_modificado) && $p_modificado !== '0000-00-00 00:00:00') {
    $timestamp = strtotime($p_modificado);
    if ($timestamp && $timestamp > 0) {
        $data_revisao_fmt = date('d/m/Y \à\s H:i', $timestamp);
    }
} elseif (!empty($p_criado) && $p_criado !== '0000-00-00 00:00:00') {
    $timestamp = strtotime($p_criado);
    if ($timestamp && $timestamp > 0) {
        $data_revisao_fmt = date('d/m/Y \à\s H:i', $timestamp);
    }
}
?>

<!-- Cabeçalho da Página -->
<div class="app-content-header pb-2 mb-3 border-bottom">
    <div class="container-fluid">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div>
                <h3 class="mb-1 fw-bold">Política de Privacidade</h3>
                <ol class="breadcrumb mb-0 small">
                    <li class="breadcrumb-item"><a href="index.php"><i class="bi bi-house-door"></i> Início</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Política de Privacidade</li>
                </ol>
            </div>
            <div class="d-flex flex-wrap align-items-center gap-2">
                <a href="<?= BASE_URL; ?>privacidade.php" target="_blank" rel="noopener" class="btn btn-outline-info btn-sm shadow-xs" title="Ver política no site público">
                    <i class="bi bi-box-arrow-up-right me-1"></i> Ver no Site
                </a>
                <a href="privacidade-edit.php?id=<?= $p_id; ?>" class="btn btn-primary btn-sm shadow-xs px-3" title="Editar texto e diretrizes">
                    <i class="bi bi-pencil-square me-1"></i> Editar Documento
                </a>
                <a href="privacidade-add.php" class="btn btn-outline-secondary btn-sm shadow-xs px-3">
                    <i class="bi bi-plus-lg me-1"></i> Adicionar Versão
                </a>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">

        <!-- Mensagens Tradicionais de Sessão (Fallback) -->
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

        <?php if (!empty($_SESSION['erro'])): ?>
            <div class="alert alert-danger alert-dismissible fade show shadow-xs mb-4" role="alert">
                <div class="d-flex align-items-center">
                    <i class="bi bi-exclamation-triangle-fill fs-5 me-2"></i>
                    <div><?= $_SESSION['erro']; ?></div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
            </div>
            <?php unset($_SESSION['erro']); ?>
        <?php endif; ?>

        <!-- Cards de Indicadores (KPIs) com Adaptação Completa Dark/Light -->
        <div class="row g-3 mb-4">
            <!-- KPI 1: Status Geral -->
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card shadow-sm border-0 border-start border-primary border-4 h-100 bg-body">
                    <div class="card-body d-flex align-items-center justify-content-between py-3">
                        <div>
                            <div class="text-body-secondary small text-uppercase fw-bold">Status Legal</div>
                            <div class="fs-4 fw-bold text-success mt-1 d-flex align-items-center gap-1">
                                <i class="bi bi-shield-fill-check"></i> Vigente &amp; Ativo
                            </div>
                        </div>
                        <div class="p-3 bg-primary-subtle text-primary rounded-circle">
                            <i class="bi bi-shield-check fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- KPI 2: Banner Superior -->
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card shadow-sm border-0 border-start border-success border-4 h-100 bg-body">
                    <div class="card-body d-flex align-items-center justify-content-between py-3">
                        <div>
                            <div class="text-body-secondary small text-uppercase fw-bold">Banner Superior</div>
                            <div class="fs-4 fw-bold text-body mt-1">1200 &times; 400 px</div>
                        </div>
                        <div class="p-3 bg-success-subtle text-success rounded-circle">
                            <i class="bi bi-image fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- KPI 3: Última Revisão -->
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card shadow-sm border-0 border-start border-info border-4 h-100 bg-body">
                    <div class="card-body d-flex align-items-center justify-content-between py-3">
                        <div>
                            <div class="text-body-secondary small text-uppercase fw-bold">Última Revisão</div>
                            <div class="fs-5 fw-bold text-body mt-1 text-truncate" style="max-width: 170px;" title="<?= $data_revisao_fmt; ?>"><?= $data_revisao_fmt; ?></div>
                        </div>
                        <div class="p-3 bg-info-subtle text-info rounded-circle">
                            <i class="bi bi-calendar-check fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- KPI 4: Conformidade LGPD -->
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card shadow-sm border-0 border-start border-warning border-4 h-100 bg-body">
                    <div class="card-body d-flex align-items-center justify-content-between py-3">
                        <div>
                            <div class="text-body-secondary small text-uppercase fw-bold">Conformidade LGPD</div>
                            <div class="fs-5 fw-bold text-warning mt-1">Lei 13.709/2018</div>
                        </div>
                        <div class="p-3 bg-warning-subtle text-warning rounded-circle">
                            <i class="bi bi-award fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabela Principal de Políticas Cadastradas -->
        <div class="row">
            <div class="col-12">
                <div class="card card-outline card-primary shadow-sm mb-4">
                    
                    <div class="card-header bg-transparent py-3 d-flex flex-wrap align-items-center justify-content-between gap-2 border-bottom">
                        <div class="d-flex align-items-center gap-2">
                            <h5 class="card-title fw-bold mb-0 text-body">
                                <i class="bi bi-shield-check text-primary me-2"></i>Gerenciar Política de Privacidade &amp; LGPD
                            </h5>
                            <span class="badge bg-secondary-subtle text-secondary small d-none d-md-inline-block">Dimensão: 1200 x 400 px</span>
                        </div>

                        <div class="d-flex align-items-center gap-2">
                            <button type="button" class="btn btn-warning btn-sm" onclick="abrirModalTrocarBanner(<?= $p_id; ?>, '<?= htmlspecialchars(addslashes($p_titulo)); ?>', '<?= $banner_principal_url; ?>')">
                                <i class="bi bi-camera me-1"></i> Trocar Banner
                            </button>
                            <a href="privacidade-edit.php?id=<?= $p_id; ?>" class="btn btn-primary btn-sm">
                                <i class="bi bi-pencil-square me-1"></i> Editar Documento
                            </a>
                        </div>
                    </div>

                    <div class="card-body table-responsive">
                        <table id="tabelaPrivacidade" class="table table-hover table-striped align-middle w-100">
                            <thead>
                                <tr>
                                    <th width="60" class="text-center">#ID</th>
                                    <th width="140">Banner</th>
                                    <th width="220">Título do Documento</th>
                                    <th>Resumo das Diretrizes</th>
                                    <th width="140" class="text-center">Atualizado</th>
                                    <th width="140" class="text-center">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                foreach ($politicas as $row):
                                    $row_id = (int)$row['id'];
                                    $row_titulo = htmlspecialchars($row['titulo'] ?? 'Política de Privacidade');
                                    $row_banner = $row['banner'] ?? '';
                                    $row_detalhes_raw = $row['detalhes'] ?? '';
                                    $row_detalhes_txt = strip_tags($row_detalhes_raw);
                                    $row_mod = !empty($row['modificado']) && $row['modificado'] !== '0000-00-00 00:00:00' ? $row['modificado'] : ($row['criado'] ?? '');
                                    $row_data_fmt = 'Recente';
                                    if (!empty($row_mod)) {
                                        $ts = strtotime($row_mod);
                                        if ($ts && $ts > 0) $row_data_fmt = date('d/m/Y', $ts);
                                    }

                                    $row_banner_url = BASE_URL . 'assets/uploads/department-1.png';
                                    if (!empty($row_banner)) {
                                        if (file_exists(__DIR__ . '/../assets/uploads/privacidade/' . $row_banner)) {
                                            $row_banner_url = BASE_URL . 'assets/uploads/privacidade/' . $row_banner;
                                        } elseif (file_exists(__DIR__ . '/../assets/uploads/' . $row_banner)) {
                                            $row_banner_url = BASE_URL . 'assets/uploads/' . $row_banner;
                                        }
                                    }
                                ?>
                                <tr id="tr-priv-<?= $row_id; ?>">
                                    <td class="text-center fw-bold text-body-secondary"><?= $row_id; ?></td>

                                    <!-- Miniatura do Banner com Botão Trocar -->
                                    <td>
                                        <div class="position-relative overflow-hidden rounded-2 border border-secondary-subtle shadow-xs bg-body-secondary" style="width: 120px; aspect-ratio: 16/8;">
                                            <img src="<?= $row_banner_url; ?>" alt="<?= $row_titulo; ?>" class="w-100 h-100 object-fit-cover">
                                        </div>
                                        <button type="button" class="btn btn-link btn-sm text-decoration-none p-0 mt-1 small text-primary" onclick="abrirModalTrocarBanner(<?= $row_id; ?>, '<?= htmlspecialchars(addslashes($row['titulo'])); ?>', '<?= $row_banner_url; ?>')">
                                            <i class="bi bi-camera me-1"></i> Trocar
                                        </button>
                                    </td>

                                    <!-- Título do Documento -->
                                    <td>
                                        <div class="fw-bold text-body fs-6"><?= $row_titulo; ?></div>
                                        <?php if ($row_id === 1): ?>
                                            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill small mt-1">
                                                <i class="bi bi-star-fill me-1"></i> Principal do Portal
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary-subtle text-body-secondary rounded-pill small mt-1">Registro Adicional</span>
                                        <?php endif; ?>
                                    </td>

                                    <!-- Prévia do Conteúdo -->
                                    <td>
                                        <div class="small text-body-secondary text-truncate" style="max-width: 420px;" title="<?= htmlspecialchars($row_detalhes_txt); ?>">
                                            <?= htmlspecialchars(mb_strimwidth($row_detalhes_txt, 0, 110, '...')); ?>
                                        </div>
                                        <button type="button" class="btn btn-link btn-sm text-decoration-none p-0 mt-1 small" onclick="abrirModalDoc(<?= $row_id; ?>)">
                                            <i class="bi bi-eye me-1"></i> Visualizar texto completo
                                        </button>
                                    </td>

                                    <!-- Data de Atualização -->
                                    <td class="text-center text-nowrap">
                                        <span class="badge bg-body-secondary text-body border border-secondary-subtle small">
                                            <i class="bi bi-clock-history me-1"></i> <?= $row_data_fmt; ?>
                                        </span>
                                    </td>

                                    <!-- Ações Padronizadas -->
                                    <td class="text-center text-nowrap">
                                        <div class="d-flex align-items-center justify-content-center gap-1">
                                            <!-- Botão Prévia -->
                                            <button type="button" class="btn btn-outline-info btn-sm" onclick="abrirModalDoc(<?= $row_id; ?>)" title="Visualizar documento em detalhes">
                                                <i class="bi bi-eye"></i>
                                            </button>

                                            <!-- Botão Trocar Banner -->
                                            <button type="button" class="btn btn-outline-warning btn-sm" onclick="abrirModalTrocarBanner(<?= $row_id; ?>, '<?= htmlspecialchars(addslashes($row['titulo'])); ?>', '<?= $row_banner_url; ?>')" title="Trocar imagem de banner">
                                                <i class="bi bi-camera"></i>
                                            </button>

                                            <!-- Botão Editar -->
                                            <a href="privacidade-edit.php?id=<?= $row_id; ?>" class="btn btn-outline-primary btn-sm" title="Editar este documento">
                                                <i class="bi bi-pencil-square"></i>
                                            </a>

                                            <!-- Botão Excluir Seguro com SweetAlert2 -->
                                            <button type="button" class="btn btn-outline-danger btn-sm btn-delete-priv"
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

    </div>
</div>

<!-- ========================================== -->
<!-- MODAL DINÂMICO 1: TROCA RÁPIDA DE BANNER    -->
<!-- ========================================== -->
<div class="modal fade" id="modalTrocarBanner" tabindex="-1" aria-labelledby="modalTrocarBannerLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content bg-body text-body border-0 shadow-lg rounded-3">
            <form action="privacidade-edit.php" method="POST" enctype="multipart/form-data" id="formTrocaBanner">
                <input type="hidden" name="id" id="mbId" value="<?= $p_id; ?>">
                
                <div class="modal-header bg-body-tertiary py-3 border-bottom border-secondary-subtle">
                    <h5 class="modal-title fw-bold text-body d-flex align-items-center gap-2" id="modalTrocarBannerLabel">
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
                        <label class="form-label small fw-semibold text-body-secondary mb-1">Banner Atual</label>
                        <div class="border border-secondary-subtle rounded-3 p-1 bg-body-secondary text-center overflow-hidden" style="max-height: 150px;">
                            <img src="<?= $banner_principal_url; ?>" alt="Banner Atual" id="mbPreviewAtual" class="img-fluid rounded" style="max-height: 140px; width: auto; object-fit: cover;">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="inputBannerFile" class="form-label small fw-semibold text-body mb-1">Selecione o Novo Arquivo</label>
                        <input type="file" class="form-control" name="banner" id="inputBannerFile" accept="image/*" required>
                    </div>

                    <!-- Pré-visualização da Nova Imagem Selecionada -->
                    <div id="boxNovoPreview" class="d-none">
                        <label class="form-label small fw-semibold text-success mb-1">
                            <i class="bi bi-check2-circle me-1"></i> Nova Imagem Pronta para Envio:
                        </label>
                        <div class="border border-success rounded-3 p-1 bg-body-secondary text-center overflow-hidden" style="max-height: 150px;">
                            <img src="" alt="Nova Prévia" id="mbPreviewNovo" class="img-fluid rounded" style="max-height: 140px; width: auto; object-fit: cover;">
                        </div>
                    </div>
                </div>

                <div class="modal-footer bg-body-tertiary py-2 border-top border-secondary-subtle d-flex justify-content-between">
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
<div class="modal fade" id="modalVisualizarDoc" tabindex="-1" aria-labelledby="modalVisualizarDocLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl modal-dialog-scrollable">
        <div class="modal-content bg-body text-body border-0 shadow-lg rounded-3">
            <div class="modal-header bg-body-tertiary py-3 border-bottom border-secondary-subtle d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-primary rounded-pill p-2"><i class="bi bi-shield-check"></i></span>
                    <div>
                        <h5 class="modal-title fw-bold text-body mb-0" id="mdTitulo">Política de Privacidade</h5>
                        <small class="text-body-secondary" id="mdSubtitulo">Documento Oficial Pronto Saúde</small>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>

            <div class="modal-body p-4 bg-body-tertiary">
                <div class="card border border-secondary-subtle shadow-sm rounded-3 p-4 bg-body">
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 pb-3 mb-4 border-bottom border-secondary-subtle">
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2 rounded-pill small">
                                <i class="bi bi-check-circle-fill me-1"></i> Vigente &amp; Homologado
                            </span>
                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-2 rounded-pill small">
                                <i class="bi bi-shield-lock me-1"></i> Proteção de Dados (LGPD)
                            </span>
                        </div>
                        <div class="small text-body-secondary" id="mdDataAtualizacao">
                            <i class="bi bi-clock-history me-1"></i> Revisado recentemente
                        </div>
                    </div>

                    <!-- Conteúdo HTML do Documento adaptado a Dark/Light -->
                    <div id="mdConteudo" class="p-3 bg-body rounded-3 text-body" style="font-size: 0.98rem; line-height: 1.75;">
                        <!-- Inserido dinamicamente via JS -->
                    </div>
                </div>
            </div>

            <div class="modal-footer bg-body border-top border-secondary-subtle py-2 d-flex justify-content-between">
                <div class="small text-body-secondary">
                    <i class="bi bi-shield-fill-check text-success me-1"></i> Centro Médico Pronto Saúde • Gardênia Azul, Jacarepaguá - RJ
                </div>
                <div class="d-flex gap-2">
                    <a href="#" id="mdLinkEditar" class="btn btn-primary btn-sm px-3">
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

<!-- Objeto JSON contendo os dados das políticas para abertura instantânea do modal -->
<script>
const POLITICAS_DATA = <?= json_encode($politicas, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP); ?>;
</script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // 1. DataTables com Tradução Completa
    let table = null;
    if (typeof window.jQuery !== 'undefined' && typeof window.jQuery.fn.DataTable !== 'undefined') {
        table = $('#tabelaPrivacidade').DataTable({
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
    const inputFile = document.getElementById('inputBannerFile');
    const boxNovoPreview = document.getElementById('boxNovoPreview');
    const mbPreviewNovo = document.getElementById('mbPreviewNovo');

    if (inputFile) {
        inputFile.addEventListener('change', function () {
            if (this.files && this.files[0]) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    if (mbPreviewNovo) mbPreviewNovo.src = e.target.result;
                    if (boxNovoPreview) boxNovoPreview.classList.remove('d-none');
                };
                reader.readAsDataURL(this.files[0]);
            } else {
                if (boxNovoPreview) boxNovoPreview.classList.add('d-none');
            }
        });
    }

    // 3. Exclusão Segura com SweetAlert2
    const deleteButtons = document.querySelectorAll('.btn-delete-priv');
    deleteButtons.forEach(btn => {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            const id = this.dataset.id;
            const title = this.dataset.title || 'esta Política de Privacidade';
            const isMain = this.dataset.isMain === '1';

            if (isMain) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Atenção: Documento Principal',
                        text: 'Esta é a Política de Privacidade principal exibida aos pacientes no site. Deseja realmente excluí-la?',
                        showCancelButton: true,
                        confirmButtonColor: '#dc3545',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: 'Sim, desejo excluir',
                        cancelButtonText: 'Cancelar'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            executarExclusao(id, title);
                        }
                    });
                } else {
                    executarExclusao(id, title);
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
                        executarExclusao(id, title);
                    }
                });
            } else {
                executarExclusao(id, title);
            }
        });
    });

    function executarExclusao(id, title) {
        fetch('privacidade-delete.php?id=' + id + '&ajax=1', {
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
                        text: data.message || 'A política foi removida com sucesso.',
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
                window.location.href = 'privacidade-delete.php?id=' + id;
            }
        });
    }

    // 4. Modal de Troca de Banner Global
    window.abrirModalTrocarBanner = function(id, titulo, bannerUrl) {
        document.getElementById('mbId').value = id;
        document.getElementById('mbPreviewAtual').src = bannerUrl;
        
        const labelEl = document.getElementById('modalTrocarBannerLabel');
        if (labelEl) {
            labelEl.innerHTML = `<i class="bi bi-image text-primary"></i> <span>Alterar Banner: ${titulo}</span>`;
        }

        const inputEl = document.getElementById('inputBannerFile');
        if (inputEl) inputEl.value = '';

        const boxNovo = document.getElementById('boxNovoPreview');
        if (boxNovo) boxNovo.classList.add('d-none');

        const modalEl = document.getElementById('modalTrocarBanner');
        if (modalEl && typeof bootstrap !== 'undefined') {
            bootstrap.Modal.getOrCreateInstance(modalEl).show();
        }
    };

    // 5. Modal de Leitura do Documento Completo
    window.abrirModalDoc = function(id) {
        const doc = POLITICAS_DATA.find(item => parseInt(item.id) === parseInt(id));
        if (!doc) return;

        document.getElementById('mdTitulo').textContent = doc.titulo || 'Política de Privacidade';
        document.getElementById('mdConteudo').innerHTML = doc.detalhes || '<em class="text-body-secondary">Nenhum texto cadastrado para este documento.</em>';
        
        const dataMod = doc.modificado || doc.criado || '';
        document.getElementById('mdDataAtualizacao').innerHTML = `<i class="bi bi-clock-history me-1"></i> Última modificação: ${dataMod || 'Vigente'}`;
        
        const editLink = document.getElementById('mdLinkEditar');
        if (editLink) editLink.href = 'privacidade-edit.php?id=' + doc.id;

        const modalEl = document.getElementById('modalVisualizarDoc');
        if (modalEl && typeof bootstrap !== 'undefined') {
            bootstrap.Modal.getOrCreateInstance(modalEl).show();
        }
    };
});
</script>

<?php require_once('footer.php'); ?>
