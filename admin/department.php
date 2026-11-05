<?php
require_once('header.php');

// 1. Endpoint AJAX: Exclusão Segura de Departamento
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_department') {
    header('Content-Type: application/json; charset=utf-8');
    $id = (int)($_POST['id'] ?? 0);

    if ($id > 0) {
        try {
            // Busca fotos vinculadas para remoção física
            $stmt_find = $pdo->prepare("SELECT dep_photo, dep_banner FROM tbl_department WHERE dep_id = ?");
            $stmt_find->execute([$id]);
            $dep_info = $stmt_find->fetch(PDO::FETCH_ASSOC);

            if ($dep_info) {
                $photo = trim($dep_info['dep_photo'] ?? '');
                $banner = trim($dep_info['dep_banner'] ?? '');

                if (!empty($photo)) {
                    $caminhos_foto = [
                        __DIR__ . '/../assets/uploads/' . $photo,
                        __DIR__ . '/../assets/uploads/departments/' . $photo
                    ];
                    foreach ($caminhos_foto as $path) {
                        if (file_exists($path) && is_file($path)) {
                            @unlink($path);
                        }
                    }
                }

                if (!empty($banner)) {
                    $caminhos_banner = [
                        __DIR__ . '/../assets/uploads/' . $banner,
                        __DIR__ . '/../assets/uploads/departments/' . $banner
                    ];
                    foreach ($caminhos_banner as $path) {
                        if (file_exists($path) && is_file($path)) {
                            @unlink($path);
                        }
                    }
                }
            }

            // Exclui registros filhos e o departamento
            $pdo->prepare("DELETE FROM tbl_department_openning_hour WHERE dep_id = ?")->execute([$id]);
            $pdo->prepare("DELETE FROM tbl_department_faq WHERE dep_id = ?")->execute([$id]);
            $pdo->prepare("DELETE FROM tbl_department_appointment WHERE dep_id = ?")->execute([$id]);
            $pdo->prepare("DELETE FROM tbl_department WHERE dep_id = ?")->execute([$id]);

            echo json_encode([
                'status'  => 'success',
                'message' => 'Departamento e todas as suas configurações foram excluídos com sucesso!'
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'status'  => 'error',
                'message' => 'Erro ao excluir departamento: ' . $e->getMessage()
            ]);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Identificador de departamento inválido.']);
    }
    exit;
}

// 2. Carregamento de Departamentos, Horários e FAQs
$statement = $pdo->prepare("SELECT * FROM tbl_department ORDER BY dep_name ASC");
$statement->execute();
$departments = $statement->fetchAll(PDO::FETCH_ASSOC);

// Carregar todos os horários agrupados por departamento
$stmt_hours = $pdo->prepare("SELECT * FROM tbl_department_openning_hour ORDER BY oh_id ASC");
$stmt_hours->execute();
$all_hours = $stmt_hours->fetchAll(PDO::FETCH_ASSOC);
$hours_by_dep = [];
foreach ($all_hours as $h) {
    $hours_by_dep[$h['dep_id']][] = $h;
}

// Carregar todas as FAQs agrupadas por departamento
$stmt_faqs = $pdo->prepare("SELECT * FROM tbl_department_faq ORDER BY fq_id ASC");
$stmt_faqs->execute();
$all_faqs = $stmt_faqs->fetchAll(PDO::FETCH_ASSOC);
$faqs_by_dep = [];
foreach ($all_faqs as $f) {
    $faqs_by_dep[$f['dep_id']][] = $f;
}

// Contagens para os KPIs
$total_departamentos = count($departments);
$total_com_contato   = 0;
$total_horarios_cfg  = count($all_hours);
$total_faqs_cfg      = count($all_faqs);

foreach ($departments as $d) {
    $has_phone = !empty(trim($d['dep_phone'] ?? ''));
    $has_fax   = !empty(trim($d['dep_fax'] ?? ''));
    $has_email = !empty(trim($d['dep_email'] ?? ''));
    if ($has_phone || $has_fax || $has_email) {
        $total_com_contato++;
    }
}
?>

<!-- Cabeçalho da Página -->
<div class="app-content-header pb-2 mb-3 border-bottom">
    <div class="container-fluid">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div>
                <h3 class="mb-1 fw-bold text-body"><i class="bi bi-hospital text-primary me-2"></i>Departamentos & Especialidades</h3>
                <ol class="breadcrumb mb-0 small">
                    <li class="breadcrumb-item"><a href="index.php" class="text-decoration-none"><i class="bi bi-house-door"></i> Início</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Departamentos</li>
                </ol>
            </div>
            <div class="d-flex flex-wrap align-items-center gap-2">
                <a href="../department.php" target="_blank" class="btn btn-outline-info btn-sm rounded-pill px-3 shadow-sm" data-bs-toggle="tooltip" title="Visualizar página de departamentos no site público">
                    <i class="bi bi-box-arrow-up-right me-1"></i> Ver no Site
                </a>
                <a href="doctor.php" class="btn btn-outline-secondary btn-sm rounded-pill px-3 shadow-sm" data-bs-toggle="tooltip" title="Gerenciar médicos e corpo clínico">
                    <i class="bi bi-people me-1"></i> Médicos
                </a>
                <a href="department-add.php" class="btn btn-primary btn-sm rounded-pill px-3 shadow-sm" data-bs-toggle="tooltip" title="Cadastrar um novo departamento hospitalar">
                    <i class="bi bi-plus-lg me-1"></i> Adicionar Departamento
                </a>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">

        <!-- 3. Mini Cards de KPIs com .kpi-icon-circle perfeitamente redondo -->
        <div class="row g-3 mb-4">
            <!-- Total de Departamentos -->
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card border-0 shadow-sm rounded-3 h-100 kpi-card">
                    <div class="card-body d-flex align-items-center justify-content-between p-3">
                        <div>
                            <span class="text-uppercase fw-semibold text-body-secondary small d-block mb-1">Total de Departamentos</span>
                            <h3 class="mb-0 fw-bold text-body" id="kpiTotal"><?= $total_departamentos; ?></h3>
                            <span class="text-body-secondary small">Setores hospitalares</span>
                        </div>
                        <div class="kpi-icon-circle bg-primary-subtle text-primary">
                            <i class="bi bi-hospital fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Departamentos com Contato -->
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card border-0 shadow-sm rounded-3 h-100 kpi-card">
                    <div class="card-body d-flex align-items-center justify-content-between p-3">
                        <div>
                            <span class="text-uppercase fw-semibold text-body-secondary small d-block mb-1">Canais de Atendimento</span>
                            <h3 class="mb-0 fw-bold text-success" id="kpiContatos"><?= $total_com_contato; ?></h3>
                            <span class="text-body-secondary small">Telefone, WhatsApp ou e-mail</span>
                        </div>
                        <div class="kpi-icon-circle bg-success-subtle text-success">
                            <i class="bi bi-headset fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Horários de Funcionamento -->
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card border-0 shadow-sm rounded-3 h-100 kpi-card">
                    <div class="card-body d-flex align-items-center justify-content-between p-3">
                        <div>
                            <span class="text-uppercase fw-semibold text-body-secondary small d-block mb-1">Escalas de Horário</span>
                            <h3 class="mb-0 fw-bold text-info" id="kpiHorarios"><?= $total_horarios_cfg; ?></h3>
                            <span class="text-body-secondary small">Períodos de atendimento</span>
                        </div>
                        <div class="kpi-icon-circle bg-info-subtle text-info">
                            <i class="bi bi-clock-history fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Perguntas Frequentes (FAQ) -->
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card border-0 shadow-sm rounded-3 h-100 kpi-card">
                    <div class="card-body d-flex align-items-center justify-content-between p-3">
                        <div>
                            <span class="text-uppercase fw-semibold text-body-secondary small d-block mb-1">Dúvidas Frequentes</span>
                            <h3 class="mb-0 fw-bold text-warning" id="kpiFaqs"><?= $total_faqs_cfg; ?></h3>
                            <span class="text-body-secondary small">Perguntas cadastradas</span>
                        </div>
                        <div class="kpi-icon-circle bg-warning-subtle text-warning">
                            <i class="bi bi-patch-question fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 4. Card da Tabela de Departamentos -->
        <div class="card border-0 shadow-sm rounded-3 mb-4">
            <div class="card-header bg-body py-3 border-bottom d-flex flex-wrap align-items-center justify-content-between gap-2">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-building-gear text-primary fs-5"></i>
                    <h5 class="card-title mb-0 fw-bold text-body">Gerenciamento de Departamentos</h5>
                </div>
                <span class="text-body-secondary small">
                    <i class="bi bi-info-circle me-1"></i>Clique em <strong>Ver Detalhes</strong> para visualizar horários, FAQs e SEO
                </span>
            </div>

            <div class="card-body p-3">
                <div class="table-responsive">
                    <table id="tabelaDepartamentos" class="table table-hover align-middle w-100 mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 50px;" class="text-center">#</th>
                                <th style="min-width: 240px;">Departamento</th>
                                <th style="min-width: 200px;">Contatos & Atendimento</th>
                                <th style="min-width: 170px;" class="text-center">Horários & FAQs</th>
                                <th style="width: 140px;" class="text-center">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $i = 0;
                            foreach ($departments as $row):
                                $i++;
                                $dep_id     = (int)$row['dep_id'];
                                $dep_name   = htmlspecialchars($row['dep_name'] ?? '', ENT_QUOTES, 'UTF-8');
                                $dep_slug   = htmlspecialchars($row['dep_slug'] ?? '', ENT_QUOTES, 'UTF-8');
                                $dep_phone  = htmlspecialchars($row['dep_phone'] ?? '', ENT_QUOTES, 'UTF-8');
                                $dep_fax    = htmlspecialchars($row['dep_fax'] ?? '', ENT_QUOTES, 'UTF-8');
                                $dep_email  = htmlspecialchars($row['dep_email'] ?? '', ENT_QUOTES, 'UTF-8');
                                $dep_addr   = htmlspecialchars($row['dep_address'] ?? '', ENT_QUOTES, 'UTF-8');
                                $dep_photo  = trim($row['dep_photo'] ?? '');
                                $dep_banner = trim($row['dep_banner'] ?? '');

                                $hours_list = $hours_by_dep[$dep_id] ?? [];
                                $faqs_list  = $faqs_by_dep[$dep_id] ?? [];
                                $total_h    = count($hours_list);
                                $total_f    = count($faqs_list);

                                // Determina miniatura da foto
                                $photo_url = '';
                                if (!empty($dep_photo)) {
                                    if (file_exists(__DIR__ . '/../assets/uploads/' . $dep_photo)) {
                                        $photo_url = '../assets/uploads/' . $dep_photo;
                                    } elseif (file_exists(__DIR__ . '/../assets/uploads/departments/' . $dep_photo)) {
                                        $photo_url = '../assets/uploads/departments/' . $dep_photo;
                                    }
                                }

                                // Dados completos estruturados para o modal dinâmico
                                $dep_json = json_encode([
                                    'id'          => $dep_id,
                                    'name'        => $row['dep_name'] ?? '',
                                    'slug'        => $row['dep_slug'] ?? '',
                                    'detail'      => $row['dep_detail'] ?? '',
                                    'address'     => $row['dep_address'] ?? '',
                                    'phone'       => $row['dep_phone'] ?? '',
                                    'fax'         => $row['dep_fax'] ?? '',
                                    'email'       => $row['dep_email'] ?? '',
                                    'photo'       => $dep_photo,
                                    'photo_url'   => $photo_url,
                                    'banner'      => $dep_banner,
                                    'meta_title'  => $row['meta_title'] ?? '',
                                    'meta_key'    => $row['meta_keyword'] ?? '',
                                    'meta_desc'   => $row['meta_description'] ?? '',
                                    'hours'       => $hours_list,
                                    'faqs'        => $faqs_list
                                ], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE);
                            ?>
                            <tr id="row-dep-<?= $dep_id; ?>">
                                <!-- # ID -->
                                <td class="text-center fw-semibold text-body-secondary"><?= $i; ?></td>

                                <!-- Foto e Identificação do Departamento -->
                                <td>
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="position-relative flex-shrink-0" style="width: 54px; height: 54px;">
                                            <?php if (!empty($photo_url)): ?>
                                                <img src="<?= htmlspecialchars($photo_url, ENT_QUOTES, 'UTF-8'); ?>" 
                                                     alt="<?= $dep_name; ?>" 
                                                     class="w-100 h-100 rounded-3 object-fit-cover border shadow-sm"
                                                     loading="lazy"
                                                     onerror="this.onerror=null; this.parentElement.innerHTML='<div class=\'w-100 h-100 rounded-3 bg-primary-subtle text-primary d-flex align-items-center justify-content-center fw-bold fs-5 shadow-sm\'><i class=\'bi bi-hospital\'></i></div>';">
                                            <?php else: ?>
                                                <div class="w-100 h-100 rounded-3 bg-primary-subtle text-primary d-flex align-items-center justify-content-center fw-bold fs-5 shadow-sm">
                                                    <i class="bi bi-hospital"></i>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="overflow-hidden">
                                            <h6 class="mb-1 fw-bold text-body text-truncate"><?= $dep_name; ?></h6>
                                            <div class="d-flex flex-wrap align-items-center gap-2 small">
                                                <span class="badge bg-body-secondary text-body-secondary border font-monospace py-1 px-2">
                                                    <i class="bi bi-link-45deg me-1"></i><?= $dep_slug; ?>
                                                </span>
                                                <span class="text-body-tertiary">#<?= $dep_id; ?></span>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- Dados JSON para o Modal Único -->
                                    <script type="application/json" id="dep-data-<?= $dep_id; ?>"><?= $dep_json; ?></script>
                                </td>

                                <!-- Contatos & Local -->
                                <td>
                                    <div class="d-flex flex-column gap-1 small">
                                        <?php if (!empty($dep_fax)): ?>
                                            <div class="d-flex align-items-center gap-2">
                                                <i class="bi bi-whatsapp text-success"></i>
                                                <span class="fw-semibold text-body"><?= $dep_fax; ?></span>
                                            </div>
                                        <?php endif; ?>

                                        <?php if (!empty($dep_phone)): ?>
                                            <div class="d-flex align-items-center gap-2">
                                                <i class="bi bi-telephone text-primary"></i>
                                                <span class="text-body-secondary"><?= $dep_phone; ?></span>
                                            </div>
                                        <?php endif; ?>

                                        <?php if (!empty($dep_email)): ?>
                                            <div class="d-flex align-items-center gap-2 text-truncate" style="max-width: 220px;">
                                                <i class="bi bi-envelope text-info"></i>
                                                <a href="mailto:<?= $dep_email; ?>" class="text-decoration-none text-body-secondary text-truncate"><?= $dep_email; ?></a>
                                            </div>
                                        <?php endif; ?>

                                        <?php if (empty($dep_fax) && empty($dep_phone) && empty($dep_email)): ?>
                                            <span class="text-body-tertiary fst-italic">Nenhum canal informado</span>
                                        <?php endif; ?>

                                        <?php if (!empty($dep_addr)): ?>
                                            <div class="text-body-tertiary text-truncate mt-1" style="max-width: 220px;" title="<?= $dep_addr; ?>">
                                                <i class="bi bi-geo-alt me-1 text-danger"></i><?= $dep_addr; ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </td>

                                <!-- Horários & FAQs Vinculados -->
                                <td class="text-center">
                                    <div class="d-flex flex-column align-items-center gap-1">
                                        <span class="badge <?= $total_h > 0 ? 'bg-info-subtle text-info border border-info-subtle' : 'bg-body-secondary text-body-secondary'; ?> rounded-pill px-2 py-1">
                                            <i class="bi bi-clock me-1"></i><?= $total_h; ?> dia(s) configurado(s)
                                        </span>
                                        <span class="badge <?= $total_f > 0 ? 'bg-warning-subtle text-warning border border-warning-subtle' : 'bg-body-secondary text-body-secondary'; ?> rounded-pill px-2 py-1">
                                            <i class="bi bi-patch-question me-1"></i><?= $total_f; ?> pergunta(s) FAQ
                                        </span>
                                    </div>
                                </td>

                                <!-- Ações Padronizadas -->
                                <td class="text-center text-nowrap">
                                    <div class="d-inline-flex align-items-center gap-1">
                                        <!-- Botão Ver Detalhes (Modal Único) -->
                                        <button type="button" 
                                                class="btn btn-outline-info btn-sm rounded-circle d-inline-flex align-items-center justify-content-center btn-view-dep" 
                                                style="width: 34px; height: 34px;" 
                                                data-id="<?= $dep_id; ?>" 
                                                data-bs-toggle="tooltip" 
                                                title="Visualizar detalhes do departamento">
                                            <i class="bi bi-eye"></i>
                                        </button>

                                        <!-- Botão Editar -->
                                        <a href="department-edit.php?id=<?= $dep_id; ?>" 
                                           class="btn btn-outline-primary btn-sm rounded-circle d-inline-flex align-items-center justify-content-center" 
                                           style="width: 34px; height: 34px;" 
                                           data-bs-toggle="tooltip" 
                                           title="Editar departamento">
                                            <i class="bi bi-pencil-square"></i>
                                        </a>

                                        <!-- Botão Excluir Seguro (SweetAlert2 + AJAX) -->
                                        <button type="button" 
                                                class="btn btn-outline-danger btn-sm rounded-circle d-inline-flex align-items-center justify-content-center btn-delete-dep" 
                                                style="width: 34px; height: 34px;" 
                                                data-id="<?= $dep_id; ?>" 
                                                data-name="<?= $dep_name; ?>" 
                                                data-bs-toggle="tooltip" 
                                                title="Excluir departamento">
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

<!-- ======================================================= -->
<!-- 5. Modal Único Dinâmico de Detalhes do Departamento    -->
<!-- ======================================================= -->
<div class="modal fade" id="modalDetalhesDepartamento" tabindex="-1" aria-labelledby="modalDetalhesDepartamentoLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow rounded-4 overflow-hidden">
            <!-- Cabeçalho do Modal -->
            <div class="modal-header bg-body border-bottom py-3 px-4">
                <div class="d-flex align-items-center gap-3">
                    <div id="mDepFotoTopo" class="rounded-3 bg-primary-subtle text-primary d-flex align-items-center justify-content-center overflow-hidden flex-shrink-0 shadow-sm" style="width: 48px; height: 48px;">
                        <i class="bi bi-hospital fs-4"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold text-body mb-0" id="mDepNome">Nome do Departamento</h5>
                        <div class="d-flex align-items-center gap-2 small text-body-secondary mt-1">
                            <span class="badge bg-body-secondary text-body-secondary border font-monospace py-0 px-2" id="mDepSlug">slug-do-departamento</span>
                            <span>ID: <strong id="mDepId">#0</strong></span>
                        </div>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>

            <!-- Navegação por Abas no Topo do Modal -->
            <div class="bg-body-tertiary px-4 pt-3 border-bottom">
                <ul class="nav nav-pills gap-2" id="depModalTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active py-2 px-3 rounded-pill fw-semibold small" id="tab-geral-btn" data-bs-toggle="pill" data-bs-target="#tab-geral" type="button" role="tab" aria-controls="tab-geral" aria-selected="true">
                            <i class="bi bi-info-circle me-1"></i> Visão Geral & Contato
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link py-2 px-3 rounded-pill fw-semibold small" id="tab-horarios-btn" data-bs-toggle="pill" data-bs-target="#tab-horarios" type="button" role="tab" aria-controls="tab-horarios" aria-selected="false">
                            <i class="bi bi-clock-history me-1"></i> Horários <span class="badge bg-info-subtle text-info ms-1" id="mBadgeHorarios">0</span>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link py-2 px-3 rounded-pill fw-semibold small" id="tab-faqs-btn" data-bs-toggle="pill" data-bs-target="#tab-faqs" type="button" role="tab" aria-controls="tab-faqs" aria-selected="false">
                            <i class="bi bi-patch-question me-1"></i> Dúvidas (FAQ) <span class="badge bg-warning-subtle text-warning ms-1" id="mBadgeFaqs">0</span>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link py-2 px-3 rounded-pill fw-semibold small" id="tab-seo-btn" data-bs-toggle="pill" data-bs-target="#tab-seo" type="button" role="tab" aria-controls="tab-seo" aria-selected="false">
                            <i class="bi bi-search me-1"></i> SEO & Metadados
                        </button>
                    </li>
                </ul>
            </div>

            <!-- Corpo das Abas -->
            <div class="modal-body p-4">
                <div class="tab-content" id="depModalTabsContent">
                    
                    <!-- ABA 1: Visão Geral & Contato -->
                    <div class="tab-pane fade show active" id="tab-geral" role="tabpanel" aria-labelledby="tab-geral-btn">
                        <!-- Banner do Departamento -->
                        <div id="mDepBannerWrapper" class="mb-4 rounded-3 overflow-hidden border shadow-sm d-none" style="max-height: 220px;">
                            <img id="mDepBannerImg" src="" alt="Banner" class="w-100 h-100 object-fit-cover">
                        </div>

                        <!-- Grid de Canais de Contato e Endereço -->
                        <div class="row g-3 mb-4">
                            <div class="col-12 col-md-4">
                                <div class="p-3 rounded-3 bg-body-tertiary border h-100">
                                    <div class="d-flex align-items-center gap-2 mb-1 text-success fw-semibold small">
                                        <i class="bi bi-whatsapp"></i> WhatsApp
                                    </div>
                                    <div class="fw-bold text-body text-break" id="mDepFax">-</div>
                                </div>
                            </div>
                            <div class="col-12 col-md-4">
                                <div class="p-3 rounded-3 bg-body-tertiary border h-100">
                                    <div class="d-flex align-items-center gap-2 mb-1 text-primary fw-semibold small">
                                        <i class="bi bi-telephone"></i> Telefone
                                    </div>
                                    <div class="fw-bold text-body text-break" id="mDepPhone">-</div>
                                </div>
                            </div>
                            <div class="col-12 col-md-4">
                                <div class="p-3 rounded-3 bg-body-tertiary border h-100">
                                    <div class="d-flex align-items-center gap-2 mb-1 text-info fw-semibold small">
                                        <i class="bi bi-envelope"></i> E-mail
                                    </div>
                                    <div class="fw-bold text-body text-break" id="mDepEmail">-</div>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="p-3 rounded-3 bg-body-tertiary border">
                                    <div class="d-flex align-items-center gap-2 mb-1 text-danger fw-semibold small">
                                        <i class="bi bi-geo-alt"></i> Endereço e Localização
                                    </div>
                                    <div class="text-body" id="mDepAddress">Endereço não informado.</div>
                                </div>
                            </div>
                        </div>

                        <!-- Descrição / Detalhes Completos -->
                        <div>
                            <h6 class="fw-bold text-body mb-2 d-flex align-items-center gap-2">
                                <i class="bi bi-file-text text-primary"></i> Descrição do Departamento
                            </h6>
                            <div class="p-3 rounded-3 bg-body-tertiary border text-body text-break" id="mDepDetail" style="min-height: 80px;">
                                Sem descrição informada.
                            </div>
                        </div>
                    </div>

                    <!-- ABA 2: Horários de Funcionamento -->
                    <div class="tab-pane fade" id="tab-horarios" role="tabpanel" aria-labelledby="tab-horarios-btn">
                        <div class="table-responsive border rounded-3 overflow-hidden">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 50%;">Dia da Semana</th>
                                        <th style="width: 50%;">Horário de Atendimento</th>
                                    </tr>
                                </thead>
                                <tbody id="mDepHorariosList">
                                    <!-- Preenchido dinamicamente via JS -->
                                </tbody>
                            </table>
                        </div>
                        <div id="mDepHorariosEmpty" class="text-center py-4 text-body-secondary d-none">
                            <i class="bi bi-clock-slash fs-2 d-block mb-2 text-body-tertiary"></i>
                            Nenhum horário de funcionamento configurado para este departamento.
                        </div>
                    </div>

                    <!-- ABA 3: Perguntas Frequentes (FAQ) -->
                    <div class="tab-pane fade" id="tab-faqs" role="tabpanel" aria-labelledby="tab-faqs-btn">
                        <div class="accordion accordion-flush border rounded-3 overflow-hidden" id="mDepFaqAccordion">
                            <!-- Preenchido dinamicamente via JS -->
                        </div>
                        <div id="mDepFaqsEmpty" class="text-center py-4 text-body-secondary d-none">
                            <i class="bi bi-question-circle-slash fs-2 d-block mb-2 text-body-tertiary"></i>
                            Nenhuma pergunta frequente cadastrada para este departamento.
                        </div>
                    </div>

                    <!-- ABA 4: SEO & Metadados -->
                    <div class="tab-pane fade" id="tab-seo" role="tabpanel" aria-labelledby="tab-seo-btn">
                        <div class="d-flex flex-column gap-3">
                            <div class="p-3 rounded-3 bg-body-tertiary border">
                                <span class="fw-semibold text-body-secondary small d-block mb-1">Meta Título (Page Title)</span>
                                <div class="fw-bold text-body" id="mDepMetaTitle">-</div>
                            </div>
                            <div class="p-3 rounded-3 bg-body-tertiary border">
                                <span class="fw-semibold text-body-secondary small d-block mb-1">Palavras-chave (Meta Keywords)</span>
                                <div class="text-body" id="mDepMetaKey">-</div>
                            </div>
                            <div class="p-3 rounded-3 bg-body-tertiary border">
                                <span class="fw-semibold text-body-secondary small d-block mb-1">Descrição para Mecanismos de Busca (Meta Description)</span>
                                <div class="text-body" id="mDepMetaDesc">-</div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <!-- Rodapé do Modal -->
            <div class="modal-footer bg-body border-top py-2 px-4 d-flex align-items-center justify-content-between">
                <span class="small text-body-secondary">
                    <i class="bi bi-shield-check me-1"></i>Painel Administrativo Pronto Saúde
                </span>
                <div class="d-flex align-items-center gap-2">
                    <a href="#" id="mDepBtnEdit" class="btn btn-primary btn-sm rounded-pill px-3 shadow-sm">
                        <i class="bi bi-pencil-square me-1"></i> Editar Departamento
                    </a>
                    <button type="button" class="btn btn-secondary btn-sm rounded-pill px-3" data-bs-dismiss="modal">
                        Fechar
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ======================================================= -->
<!-- 6. Scripts JavaScript Integrados                        -->
<!-- ======================================================= -->
<script>
// Gerenciamento e Limpeza Rigorosa de Tooltips
function ocultarTodosTooltips() {
    document.querySelectorAll('.tooltip').forEach(el => el.remove());
}

function inicializarTooltipsDepartamentos() {
    if (typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
        document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => {
            const instancia = bootstrap.Tooltip.getInstance(el);
            if (instancia) instancia.dispose();
            new bootstrap.Tooltip(el, {
                trigger: 'hover',
                boundary: 'window'
            });
            // Oculta ao retirar mouse e ao clicar
            el.addEventListener('mouseleave', () => {
                const tip = bootstrap.Tooltip.getInstance(el);
                if (tip) tip.hide();
            });
            el.addEventListener('click', () => {
                const tip = bootstrap.Tooltip.getInstance(el);
                if (tip) tip.hide();
                ocultarTodosTooltips();
            });
        });
    }
}

// Abertura do Modal de Detalhes
window.abrirModalDepartamento = function(id) {
    ocultarTodosTooltips();
    const scriptEl = document.getElementById('dep-data-' + id);
    if (!scriptEl) return;

    let data;
    try {
        data = JSON.parse(scriptEl.textContent);
    } catch (e) {
        console.error('Erro ao ler dados do departamento:', e);
        return;
    }

    // Informações Básicas
    document.getElementById('mDepId').textContent = '#' + (data.id || '');
    document.getElementById('mDepNome').textContent = data.name || 'Departamento';
    document.getElementById('mDepSlug').textContent = data.slug || '-';
    document.getElementById('mDepBtnEdit').href = 'department-edit.php?id=' + (data.id || '');

    // Foto do Topo
    const fotoTopo = document.getElementById('mDepFotoTopo');
    if (data.photo_url) {
        fotoTopo.innerHTML = `<img src="${data.photo_url}" alt="${data.name}" class="w-100 h-100 object-fit-cover">`;
    } else {
        fotoTopo.innerHTML = `<i class="bi bi-hospital fs-4"></i>`;
    }

    // Banner
    const bannerWrapper = document.getElementById('mDepBannerWrapper');
    const bannerImg = document.getElementById('mDepBannerImg');
    if (data.banner && data.banner.trim() !== '') {
        bannerImg.src = '../assets/uploads/' + data.banner;
        bannerWrapper.classList.remove('d-none');
    } else {
        bannerWrapper.classList.add('d-none');
        bannerImg.src = '';
    }

    // Contatos e Endereço
    document.getElementById('mDepFax').textContent = data.fax || 'Não informado';
    document.getElementById('mDepPhone').textContent = data.phone || 'Não informado';
    document.getElementById('mDepEmail').innerHTML = data.email ? `<a href="mailto:${data.email}" class="text-decoration-none">${data.email}</a>` : 'Não informado';
    document.getElementById('mDepAddress').textContent = data.address || 'Endereço não informado.';
    
    // Detalhes HTML
    const detailBox = document.getElementById('mDepDetail');
    detailBox.innerHTML = (data.detail && data.detail.trim() !== '') ? data.detail : '<span class="text-body-secondary fst-italic">Sem descrição informada.</span>';

    // Horários de Atendimento
    const horariosList = document.getElementById('mDepHorariosList');
    const horariosEmpty = document.getElementById('mDepHorariosEmpty');
    const badgeHorarios = document.getElementById('mBadgeHorarios');
    horariosList.innerHTML = '';
    
    const hours = data.hours || [];
    badgeHorarios.textContent = hours.length;

    if (hours.length > 0) {
        horariosEmpty.classList.add('d-none');
        hours.forEach(h => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td class="fw-semibold text-body"><i class="bi bi-calendar3 me-2 text-primary"></i>${h.oh_day || ''}</td>
                <td><span class="badge bg-body-secondary text-body border px-2 py-1 font-monospace">${h.oh_time || ''}</span></td>
            `;
            horariosList.appendChild(tr);
        });
    } else {
        horariosEmpty.classList.remove('d-none');
    }

    // Dúvidas Frequentes (FAQ)
    const faqAccordion = document.getElementById('mDepFaqAccordion');
    const faqsEmpty = document.getElementById('mDepFaqsEmpty');
    const badgeFaqs = document.getElementById('mBadgeFaqs');
    faqAccordion.innerHTML = '';

    const faqs = data.faqs || [];
    badgeFaqs.textContent = faqs.length;

    if (faqs.length > 0) {
        faqsEmpty.classList.add('d-none');
        faqs.forEach((f, idx) => {
            const itemId = `faqItem_${data.id}_${idx}`;
            const item = document.createElement('div');
            item.className = 'accordion-item border-0 border-bottom';
            item.innerHTML = `
                <h2 class="accordion-header" id="heading_${itemId}">
                    <button class="accordion-button collapsed py-3 fw-semibold text-body" type="button" data-bs-toggle="collapse" data-bs-target="#collapse_${itemId}" aria-expanded="false" aria-controls="collapse_${itemId}">
                        <i class="bi bi-question-circle text-primary me-2"></i> ${f.fq_title || 'Pergunta'}
                    </button>
                </h2>
                <div id="collapse_${itemId}" class="accordion-collapse collapse" aria-labelledby="heading_${itemId}" data-bs-parent="#mDepFaqAccordion">
                    <div class="accordion-body bg-body-tertiary small text-body">
                        ${f.fq_content || 'Sem resposta.'}
                    </div>
                </div>
            `;
            faqAccordion.appendChild(item);
        });
    } else {
        faqsEmpty.classList.remove('d-none');
    }

    // SEO & Metadados
    document.getElementById('mDepMetaTitle').textContent = data.meta_title || 'Não definido';
    document.getElementById('mDepMetaKey').textContent   = data.meta_key || 'Não definidas';
    document.getElementById('mDepMetaDesc').textContent  = data.meta_desc || 'Não definida';

    // Ativa a primeira aba (Visão Geral)
    const tabBtn = document.getElementById('tab-geral-btn');
    if (tabBtn && typeof bootstrap !== 'undefined') {
        const tabTrigger = new bootstrap.Tab(tabBtn);
        tabTrigger.show();
    }

    // Abre o Modal
    const modalEl = document.getElementById('modalDetalhesDepartamento');
    if (modalEl && typeof bootstrap !== 'undefined') {
        const modalInst = bootstrap.Modal.getOrCreateInstance(modalEl);
        modalInst.show();
    }
};

// Confirmação e Exclusão com SweetAlert2
window.confirmarExclusaoDep = function(id, name) {
    ocultarTodosTooltips();

    const alertHtml = `
        <div class="text-start">
            Tem certeza de que deseja excluir o departamento <strong>"${name}"</strong>?
            <div class="alert alert-warning border-0 mt-3 mb-0 small">
                <i class="bi bi-exclamation-triangle-fill text-warning me-1"></i>
                <strong>Atenção:</strong> Ao confirmar, todos os horários de atendimento, dúvidas frequentes (FAQ) e arquivos de imagem associados serão excluídos permanentemente.
            </div>
        </div>
    `;

    if (typeof Swal !== 'undefined') {
        Swal.fire({
            title: 'Excluir Departamento?',
            html: alertHtml,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="bi bi-trash3 me-1"></i> Sim, excluir',
            cancelButtonText: 'Cancelar',
            customClass: {
                confirmButton: 'btn btn-danger px-3 me-2',
                cancelButton: 'btn btn-secondary px-3'
            },
            buttonsStyling: false
        }).then((result) => {
            if (result.isConfirmed) {
                executarExclusaoDep(id);
            }
        });
    } else {
        executarExclusaoDep(id);
    }
};

window.executarExclusaoDep = function(id) {
    fetch('department.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: new URLSearchParams({
            action: 'delete_department',
            id: id
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'success',
                    title: 'Excluído com sucesso!',
                    text: data.message,
                    timer: 1600,
                    showConfirmButton: false
                }).then(() => {
                    const row = document.getElementById('row-dep-' + id);
                    if (row) {
                        row.remove();
                    } else {
                        window.location.reload();
                    }
                });
            } else {
                window.location.reload();
            }
        } else {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'error',
                    title: 'Erro ao excluir',
                    text: data.message || 'Não foi possível excluir o departamento.'
                });
            }
        }
    })
    .catch(err => {
        console.error(err);
        window.location.href = 'department-delete.php?id=' + id;
    });
};

// Delegação Global de Cliques
document.addEventListener('click', function(e) {
    // 1. Botão Ver Detalhes
    const btnView = e.target.closest('.btn-view-dep');
    if (btnView) {
        e.preventDefault();
        const id = btnView.getAttribute('data-id');
        window.abrirModalDepartamento(id);
        return;
    }

    // 2. Botão Excluir
    const btnDel = e.target.closest('.btn-delete-dep');
    if (btnDel) {
        e.preventDefault();
        const id = btnDel.getAttribute('data-id');
        const name = btnDel.getAttribute('data-name') || 'este departamento';
        window.confirmarExclusaoDep(id, name);
        return;
    }
});

// Inicialização do DataTables em PT-BR
function initTabelaDepartamentos() {
    if (typeof window.jQuery !== 'undefined' && typeof window.jQuery.fn.DataTable !== 'undefined') {
        const $table = $('#tabelaDepartamentos');
        if ($table.length && !$.fn.DataTable.isDataTable('#tabelaDepartamentos')) {
            $table.DataTable({
                responsive: true,
                language: {
                    search: "Buscar Departamento:",
                    lengthMenu: "Mostrar _MENU_ registros",
                    info: "Exibindo _START_ a _END_ de _TOTAL_ departamentos",
                    infoEmpty: "Nenhum departamento cadastrado",
                    infoFiltered: "(filtrado de _MAX_ no total)",
                    zeroRecords: "Nenhum departamento encontrado",
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
    }
    inicializarTooltipsDepartamentos();
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initTabelaDepartamentos);
} else {
    initTabelaDepartamentos();
}
</script>

<?php require_once('footer.php'); ?>
