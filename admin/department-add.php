<?php
require_once('header.php');

// 1. Contagens para os Mini Cards de KPIs do topo
$total_departamentos = (int)$pdo->query("SELECT COUNT(*) FROM tbl_department")->fetchColumn();
$total_com_contato   = (int)$pdo->query("SELECT COUNT(*) FROM tbl_department WHERE (dep_phone != '' AND dep_phone IS NOT NULL) OR (dep_fax != '' AND dep_fax IS NOT NULL) OR (dep_email != '' AND dep_email IS NOT NULL)")->fetchColumn();
$total_horarios_cfg  = (int)$pdo->query("SELECT COUNT(*) FROM tbl_department_openning_hour")->fetchColumn();
$total_faqs_cfg      = (int)$pdo->query("SELECT COUNT(*) FROM tbl_department_faq")->fetchColumn();

// 2. Processamento do Envio (Suporta AJAX e POST tradicional)
$is_ajax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
           || (isset($_POST['action']) && $_POST['action'] === 'add_department_ajax');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && (isset($_POST['form1']) || $is_ajax)) {
    $dep_name        = trim($_POST['dep_name'] ?? '');
    $dep_slug_input  = trim($_POST['dep_slug'] ?? '');
    $dep_detail      = trim($_POST['dep_detail'] ?? '');
    $dep_address     = trim($_POST['dep_address'] ?? '');
    $dep_phone       = trim($_POST['dep_phone'] ?? '');
    $dep_fax         = trim($_POST['dep_fax'] ?? ''); // WhatsApp/Contato
    $dep_email       = trim($_POST['dep_email'] ?? '');

    // SEO & Metadados
    $meta_title       = trim(strip_tags($_POST['meta_title'] ?? ''));
    $meta_keyword     = trim(strip_tags($_POST['meta_keyword'] ?? ''));
    $meta_description = trim(strip_tags($_POST['meta_description'] ?? ''));
    $meta_description = preg_replace('/[\r\n\t]+/', ' ', $meta_description);
    $meta_description = preg_replace('/\s+/', ' ', $meta_description);

    $valid = 1;
    $erros = [];

    // Validação do Nome
    if (empty($dep_name)) {
        $valid = 0;
        $erros[] = 'Informe o nome do departamento ou clínica hospitalar.';
    }

    // Validação da Foto Principal
    $foto_enviada = isset($_FILES['dep_photo']) && !empty($_FILES['dep_photo']['name']);
    if (!$foto_enviada) {
        $valid = 0;
        $erros[] = 'Selecione uma foto de destaque para o departamento.';
    } else {
        $ext_foto = strtolower(pathinfo($_FILES['dep_photo']['name'], PATHINFO_EXTENSION));
        $ext_permitidas = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
        if (!in_array($ext_foto, $ext_permitidas)) {
            $valid = 0;
            $erros[] = 'Formato de foto de destaque inválido. Envie JPG, JPEG, PNG ou WEBP.';
        }
    }

    // Validação do Banner
    $banner_enviado = isset($_FILES['dep_banner']) && !empty($_FILES['dep_banner']['name']);
    if (!$banner_enviado) {
        $valid = 0;
        $erros[] = 'Selecione um banner panorâmico de cabeçalho para o departamento.';
    } else {
        $ext_banner = strtolower(pathinfo($_FILES['dep_banner']['name'], PATHINFO_EXTENSION));
        $ext_permitidas = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
        if (!in_array($ext_banner, $ext_permitidas)) {
            $valid = 0;
            $erros[] = 'Formato de banner inválido. Envie JPG, JPEG, PNG ou WEBP.';
        }
    }

    if ($valid === 1) {
        // Geração do Slug Amigável
        $base_slug = !empty($dep_slug_input) ? $dep_slug_input : $dep_name;
        $temp_slug = strtolower(trim($base_slug));
        $temp_slug = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $temp_slug);
        $final_slug = preg_replace('/[^a-z0-9\-]+/', '-', $temp_slug);
        $final_slug = trim(preg_replace('/-+/', '-', $final_slug), '-');

        if (empty($final_slug)) {
            $final_slug = 'departamento-' . time();
        }

        // Verifica unicidade de slug
        $stmt_slug = $pdo->prepare("SELECT COUNT(*) FROM tbl_department WHERE dep_slug = ?");
        $stmt_slug->execute([$final_slug]);
        if ((int)$stmt_slug->fetchColumn() > 0) {
            $final_slug = $final_slug . '-' . time();
        }

        // Upload da Foto Principal
        $final_foto = 'department-' . time() . '-' . rand(100, 999) . '.' . $ext_foto;
        $destino_foto = __DIR__ . '/../assets/uploads/' . $final_foto;
        if (!move_uploaded_file($_FILES['dep_photo']['tmp_name'], $destino_foto)) {
            $valid = 0;
            $erros[] = 'Não foi possível salvar o arquivo de foto no servidor.';
        }

        // Upload do Banner
        $final_banner = 'department-banner-' . time() . '-' . rand(100, 999) . '.' . $ext_banner;
        $destino_banner = __DIR__ . '/../assets/uploads/' . $final_banner;
        if ($valid === 1 && !move_uploaded_file($_FILES['dep_banner']['tmp_name'], $destino_banner)) {
            $valid = 0;
            $erros[] = 'Não foi possível salvar o arquivo de banner no servidor.';
            @unlink($destino_foto);
        }

        if ($valid === 1) {
            try {
                // Inserção em tbl_department
                $stmt_insert = $pdo->prepare("INSERT INTO tbl_department (
                    dep_name, dep_slug, dep_detail, dep_address, dep_phone, dep_fax, dep_email,
                    dep_photo, dep_banner, meta_title, meta_keyword, meta_description
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

                $stmt_insert->execute([
                    $dep_name,
                    $final_slug,
                    $dep_detail,
                    $dep_address,
                    $dep_phone,
                    $dep_fax,
                    $dep_email,
                    $final_foto,
                    $final_banner,
                    $meta_title,
                    $meta_keyword,
                    $meta_description
                ]);

                $new_dep_id = (int)$pdo->lastInsertId();

                // Inserção dos Horários de Atendimento Dinâmicos
                if (!empty($_POST['oh_day']) && is_array($_POST['oh_day']) && !empty($_POST['oh_time']) && is_array($_POST['oh_time'])) {
                    $days = $_POST['oh_day'];
                    $times = $_POST['oh_time'];
                    $stmt_hour = $pdo->prepare("INSERT INTO tbl_department_openning_hour (oh_day, oh_time, dep_id) VALUES (?, ?, ?)");

                    for ($i = 0; $i < count($days); $i++) {
                        $dia = trim($days[$i] ?? '');
                        $horario = trim($times[$i] ?? '');
                        if (!empty($dia) || !empty($horario)) {
                            $stmt_hour->execute([$dia, $horario, $new_dep_id]);
                        }
                    }
                }

                // Inserção das Dúvidas Frequentes FAQ Dinâmicas
                if (!empty($_POST['fq_title']) && is_array($_POST['fq_title']) && !empty($_POST['fq_content']) && is_array($_POST['fq_content'])) {
                    $titles = $_POST['fq_title'];
                    $contents = $_POST['fq_content'];
                    $stmt_faq = $pdo->prepare("INSERT INTO tbl_department_faq (fq_title, fq_content, dep_id) VALUES (?, ?, ?)");

                    for ($i = 0; $i < count($titles); $i++) {
                        $pergunta = trim($titles[$i] ?? '');
                        $resposta = trim($contents[$i] ?? '');
                        if (!empty($pergunta) || !empty($resposta)) {
                            $stmt_faq->execute([$pergunta, $resposta, $new_dep_id]);
                        }
                    }
                }

                if ($is_ajax) {
                    header('Content-Type: application/json; charset=utf-8');
                    echo json_encode([
                        'status'   => 'success',
                        'message'  => 'Departamento cadastrado com sucesso!',
                        'redirect' => 'department.php'
                    ]);
                    exit;
                } else {
                    $_SESSION['sucesso'] = 'Departamento cadastrado com sucesso!';
                    header('Location: department.php');
                    exit;
                }
            } catch (Exception $e) {
                @unlink($destino_foto);
                @unlink($destino_banner);
                $erros[] = 'Erro ao salvar no banco de dados: ' . $e->getMessage();
            }
        }
    }

    if ($is_ajax) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'status'  => 'error',
            'message' => implode('<br>', $erros)
        ]);
        exit;
    } else {
        $error_message = implode('<br>', $erros);
    }
}
?>

<style>
/* Abas Modernas com Transição Fluida */
.nav-pills-custom .nav-link {
    color: var(--bs-secondary-color);
    font-weight: 600;
    font-size: 0.95rem;
    padding: 0.75rem 1.25rem;
    border-radius: 0.5rem;
    border: 1px solid transparent;
    transition: all 0.2s ease-in-out;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
}
.nav-pills-custom .nav-link:hover {
    color: var(--bs-primary);
    background-color: var(--bs-tertiary-bg);
}
.nav-pills-custom .nav-link.active {
    color: #fff !important;
    background-color: var(--bs-primary) !important;
    box-shadow: 0 4px 12px rgba(10, 107, 116, 0.25);
}

/* Upload & Dropzone Moderno */
.upload-dropzone {
    border: 2px dashed var(--bs-border-color);
    border-radius: 0.75rem;
    padding: 1.5rem 1rem;
    text-align: center;
    background-color: var(--bs-tertiary-bg);
    cursor: pointer;
    transition: border-color 0.2s ease, background-color 0.2s ease;
    position: relative;
}
.upload-dropzone:hover, .upload-dropzone.dragover {
    border-color: var(--bs-primary);
    background-color: rgba(10, 107, 116, 0.05);
}

/* Google Snippet Preview */
.google-preview-box {
    background-color: #ffffff;
    border: 1px solid #dfe1e5;
    border-radius: 8px;
    padding: 16px;
    max-width: 650px;
    font-family: Arial, sans-serif;
}
[data-bs-theme="dark"] .google-preview-box {
    background-color: #202124;
    border-color: #3c4043;
}
.google-preview-url {
    font-size: 13px;
    color: #202124;
    margin-bottom: 4px;
    display: flex;
    align-items: center;
    gap: 6px;
}
[data-bs-theme="dark"] .google-preview-url {
    color: #bdc1c6;
}
.google-preview-title {
    font-size: 19px;
    line-height: 1.3;
    color: #1a0dab;
    text-decoration: none;
    cursor: pointer;
    margin-bottom: 4px;
    font-weight: 400;
}
[data-bs-theme="dark"] .google-preview-title {
    color: #8ab4f8;
}
.google-preview-desc {
    font-size: 14px;
    line-height: 1.5;
    color: #4d5156;
    word-break: break-word;
}
[data-bs-theme="dark"] .google-preview-desc {
    color: #bdc1c6;
}

/* Linha Dinâmica de Horários e FAQs */
.dynamic-row-item {
    transition: background-color 0.2s ease;
    border-radius: 0.5rem;
}
.dynamic-row-item:hover {
    background-color: var(--bs-tertiary-bg);
}
</style>

<!-- Cabeçalho da Página -->
<div class="app-content-header pb-2 mb-3 border-bottom">
    <div class="container-fluid">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div>
                <h3 class="mb-1 fw-bold text-body">
                    <i class="bi bi-hospital text-primary me-2"></i>Adicionar Departamento
                </h3>
                <ol class="breadcrumb mb-0 small">
                    <li class="breadcrumb-item"><a href="index.php" class="text-decoration-none"><i class="bi bi-house-door"></i> Início</a></li>
                    <li class="breadcrumb-item"><a href="department.php" class="text-decoration-none">Departamentos</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Novo Departamento</li>
                </ol>
            </div>
            <div class="d-flex flex-wrap align-items-center gap-2">
                <a href="../department.php" target="_blank" class="btn btn-outline-info btn-sm rounded-pill px-3 shadow-sm" data-bs-toggle="tooltip" title="Visualizar página de departamentos no site público">
                    <i class="bi bi-box-arrow-up-right me-1"></i> Ver no Site
                </a>
                <a href="department.php" class="btn btn-outline-secondary btn-sm rounded-pill px-3 shadow-sm" data-bs-toggle="tooltip" title="Voltar para a listagem geral de departamentos">
                    <i class="bi bi-list-ul me-1"></i> Departamentos
                </a>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">

        <!-- Mini Cards de Indicadores (KPIs) com .kpi-icon-circle perfeitamente redondo -->
        <div class="row g-3 mb-4">
            <!-- Total de Departamentos -->
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card border-0 shadow-sm rounded-3 h-100 kpi-card">
                    <div class="card-body d-flex align-items-center justify-content-between p-3">
                        <div>
                            <span class="text-uppercase fw-semibold text-body-secondary small d-block mb-1">Total no Sistema</span>
                            <h3 class="mb-0 fw-bold text-body" id="kpiTotal"><?= $total_departamentos; ?></h3>
                            <span class="text-body-secondary small">Departamentos ativos</span>
                        </div>
                        <div class="kpi-icon-circle bg-primary-subtle text-primary">
                            <i class="bi bi-hospital fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Canais de Contato -->
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card border-0 shadow-sm rounded-3 h-100 kpi-card">
                    <div class="card-body d-flex align-items-center justify-content-between p-3">
                        <div>
                            <span class="text-uppercase fw-semibold text-body-secondary small d-block mb-1">Canais de Atendimento</span>
                            <h3 class="mb-0 fw-bold text-success" id="kpiContatos"><?= $total_com_contato; ?></h3>
                            <span class="text-body-secondary small">Com telefone / e-mail</span>
                        </div>
                        <div class="kpi-icon-circle bg-success-subtle text-success">
                            <i class="bi bi-telephone-inbound fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Escalas de Horários -->
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card border-0 shadow-sm rounded-3 h-100 kpi-card">
                    <div class="card-body d-flex align-items-center justify-content-between p-3">
                        <div>
                            <span class="text-uppercase fw-semibold text-body-secondary small d-block mb-1">Escalas de Horários</span>
                            <h3 class="mb-0 fw-bold text-warning" id="kpiHorarios"><?= $total_horarios_cfg; ?></h3>
                            <span class="text-body-secondary small">Turnos cadastrados</span>
                        </div>
                        <div class="kpi-icon-circle bg-warning-subtle text-warning">
                            <i class="bi bi-clock-history fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Perguntas Frequentes (FAQs) -->
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card border-0 shadow-sm rounded-3 h-100 kpi-card">
                    <div class="card-body d-flex align-items-center justify-content-between p-3">
                        <div>
                            <span class="text-uppercase fw-semibold text-body-secondary small d-block mb-1">Dúvidas Frequentes</span>
                            <h3 class="mb-0 fw-bold text-info" id="kpiFaqs"><?= $total_faqs_cfg; ?></h3>
                            <span class="text-body-secondary small">Itens de FAQ</span>
                        </div>
                        <div class="kpi-icon-circle bg-info-subtle text-info">
                            <i class="bi bi-question-diamond fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Formulário Principal -->
        <form action="" method="post" enctype="multipart/form-data" id="formAddDepartment">
            <input type="hidden" name="action" value="add_department_ajax">

            <div class="row g-4">
                
                <!-- Coluna Principal com Abas (8 colunas) -->
                <div class="col-12 col-lg-8">
                    <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
                        
                        <!-- Navegação por Abas -->
                        <div class="card-header bg-transparent border-bottom p-3">
                            <ul class="nav nav-pills nav-pills-custom gap-2" id="departmentTabs" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active" id="tab-geral-btn" data-bs-toggle="pill" data-bs-target="#tab-geral" type="button" role="tab" aria-controls="tab-geral" aria-selected="true">
                                        <i class="bi bi-info-circle"></i> Informações &amp; Contato
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="tab-horarios-btn" data-bs-toggle="pill" data-bs-target="#tab-horarios" type="button" role="tab" aria-controls="tab-horarios" aria-selected="false">
                                        <i class="bi bi-clock-history"></i> Horários
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="tab-faqs-btn" data-bs-toggle="pill" data-bs-target="#tab-faqs" type="button" role="tab" aria-controls="tab-faqs" aria-selected="false">
                                        <i class="bi bi-question-diamond"></i> Perguntas Frequentes
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="tab-seo-btn" data-bs-toggle="pill" data-bs-target="#tab-seo" type="button" role="tab" aria-controls="tab-seo" aria-selected="false">
                                        <i class="bi bi-google"></i> Google &amp; SEO
                                    </button>
                                </li>
                            </ul>
                        </div>

                        <!-- Conteúdo das Abas -->
                        <div class="card-body p-4">
                            <div class="tab-content" id="departmentTabsContent">

                                <!-- ABA 1: INFORMAÇÕES GERAIS & CONTATO -->
                                <div class="tab-pane fade show active" id="tab-geral" role="tabpanel" aria-labelledby="tab-geral-btn">
                                    <div class="row g-3">
                                        
                                        <!-- Nome do Departamento -->
                                        <div class="col-12">
                                            <label class="form-label fw-bold text-body" for="dep_name">
                                                Nome do Departamento ou Clínica <span class="text-danger">*</span>
                                            </label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-body-tertiary"><i class="bi bi-hospital text-primary"></i></span>
                                                <input type="text" class="form-control form-control-lg fw-semibold" id="dep_name" name="dep_name" placeholder="Ex: Cardiologia Integrada, UTI Neonatal, Centro Cirúrgico..." required autocomplete="off">
                                            </div>
                                            <div class="form-text small text-body-secondary mt-1">
                                                Nome oficial exibido no portal público, menu de especialidades e agendamentos.
                                            </div>
                                        </div>

                                        <!-- Detalhes com Summernote -->
                                        <div class="col-12 mt-3">
                                            <label class="form-label fw-bold text-body" for="dep_detail">
                                                Apresentação &amp; Detalhes da Clínica
                                            </label>
                                            <textarea class="form-control" name="dep_detail" id="dep_detail" rows="6" placeholder="Apresente a história, estrutura física, corpo clínico, diferenciais e tecnologia da especialidade..."></textarea>
                                        </div>

                                        <!-- Busca Integrada de CEP -->
                                        <div class="col-12 mt-4 pt-3 border-top">
                                            <div class="d-flex align-items-center justify-content-between mb-2">
                                                <label class="form-label fw-bold text-body mb-0">
                                                    <i class="bi bi-geo-alt-fill text-danger me-1"></i>Localização &amp; Endereço do Setor
                                                </label>
                                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle small">
                                                    <i class="bi bi-lightning-charge me-1"></i>Preenchimento Rápido via CEP
                                                </span>
                                            </div>
                                            <div class="row g-2">
                                                <div class="col-12 col-md-5">
                                                    <div class="input-group">
                                                        <span class="input-group-text bg-body-tertiary"><i class="bi bi-search"></i></span>
                                                        <input type="text" class="form-control mask-cep" id="busca_cep" placeholder="CEP: 00000-000" maxlength="9">
                                                        <button class="btn btn-outline-primary" type="button" id="btnBuscarCep" title="Consultar CEP">
                                                            <i class="bi bi-arrow-right"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                                <div class="col-12 col-md-7">
                                                    <input type="text" class="form-control" id="numero_endereco" placeholder="Número / Andar / Bloco (Ex: Sala 302, 3º Andar)">
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Endereço Completo -->
                                        <div class="col-12">
                                            <label class="form-label fw-semibold text-body" for="dep_address">
                                                Endereço Completo Formatado
                                            </label>
                                            <textarea class="form-control" name="dep_address" id="dep_address" rows="3" placeholder="Av. Principal, 1000 - Bloco B, 2º Andar - Centro Médico, Cidade - UF"></textarea>
                                        </div>

                                        <!-- Contatos: Telefone, WhatsApp e E-mail -->
                                        <div class="col-12 mt-3 pt-3 border-top">
                                            <h6 class="fw-bold text-body mb-3">
                                                <i class="bi bi-chat-left-dots text-primary me-2"></i>Canais Diretos de Comunicação
                                            </h6>
                                            <div class="row g-3">
                                                <div class="col-12 col-md-4">
                                                    <label class="form-label fw-semibold text-body small" for="dep_phone">
                                                        Telefone de Atendimento
                                                    </label>
                                                    <div class="input-group">
                                                        <span class="input-group-text bg-body-tertiary"><i class="bi bi-telephone text-success"></i></span>
                                                        <input type="text" class="form-control mask-phone" id="dep_phone" name="dep_phone" placeholder="(00) 0000-0000" autocomplete="off">
                                                    </div>
                                                </div>

                                                <div class="col-12 col-md-4">
                                                    <label class="form-label fw-semibold text-body small" for="dep_fax">
                                                        WhatsApp / Contato Direto
                                                    </label>
                                                    <div class="input-group">
                                                        <span class="input-group-text bg-body-tertiary"><i class="bi bi-whatsapp text-success"></i></span>
                                                        <input type="text" class="form-control mask-cell" id="dep_fax" name="dep_fax" placeholder="(00) 90000-0000" autocomplete="off">
                                                    </div>
                                                </div>

                                                <div class="col-12 col-md-4">
                                                    <label class="form-label fw-semibold text-body small" for="dep_email">
                                                        E-mail do Departamento
                                                    </label>
                                                    <div class="input-group">
                                                        <span class="input-group-text bg-body-tertiary"><i class="bi bi-envelope text-info"></i></span>
                                                        <input type="email" class="form-control" id="dep_email" name="dep_email" placeholder="departamento@prontosaude.com" autocomplete="off">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                    </div>
                                </div>

                                <!-- ABA 2: HORÁRIOS DE ATENDIMENTO DINÂMICOS -->
                                <div class="tab-pane fade" id="tab-horarios" role="tabpanel" aria-labelledby="tab-horarios-btn">
                                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 pb-3 mb-3 border-bottom">
                                        <div>
                                            <h6 class="fw-bold text-body mb-1">
                                                <i class="bi bi-clock-history text-primary me-2"></i>Escalas de Atendimento e Funcionamento
                                            </h6>
                                            <p class="text-body-secondary small mb-0">
                                                Configure os turnos, dias da semana e horários em que este departamento opera.
                                            </p>
                                        </div>
                                        <button type="button" class="btn btn-outline-primary btn-sm rounded-pill shadow-xs" id="btnAddHorario">
                                            <i class="bi bi-plus-lg me-1"></i> Adicionar Horário
                                        </button>
                                    </div>

                                    <!-- Sugestões Rápidas de Horários -->
                                    <div class="mb-3 d-flex flex-wrap gap-2 align-items-center">
                                        <span class="small text-body-secondary me-1"><i class="bi bi-magic me-1"></i>Sugestões Rápidas:</span>
                                        <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill py-1 px-2 small btn-sugestao-horario" data-dia="Segunda a Sexta" data-hora="07:00 às 19:00">
                                            Seg a Sex (07h às 19h)
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill py-1 px-2 small btn-sugestao-horario" data-dia="Sábados" data-hora="08:00 às 13:00">
                                            Sábados (08h às 13h)
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill py-1 px-2 small btn-sugestao-horario" data-dia="Urgência & Emergência" data-hora="Plantão 24 Horas">
                                            Plantão 24h
                                        </button>
                                    </div>

                                    <!-- Container Dinâmico de Linhas de Horário -->
                                    <div id="containerHorarios" class="d-flex flex-column gap-2">
                                        <!-- Primeira Linha Padrão -->
                                        <div class="p-3 border rounded-3 dynamic-row-item d-flex flex-wrap align-items-center gap-3">
                                            <div class="flex-grow-1" style="min-width: 200px;">
                                                <label class="form-label small fw-semibold text-body-secondary mb-1">Dia / Período</label>
                                                <input type="text" class="form-control" name="oh_day[]" placeholder="Ex: Segunda a Sexta-feira" value="Segunda a Sexta">
                                            </div>
                                            <div class="flex-grow-1" style="min-width: 200px;">
                                                <label class="form-label small fw-semibold text-body-secondary mb-1">Horário de Funcionamento</label>
                                                <input type="text" class="form-control" name="oh_time[]" placeholder="Ex: 07:00 às 19:00" value="07:00 às 19:00">
                                            </div>
                                            <div class="pt-4">
                                                <button type="button" class="btn btn-outline-danger btn-sm btn-remover-linha" title="Excluir este horário">
                                                    <i class="bi bi-trash3"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- ABA 3: PERGUNTAS FREQUENTES (FAQ) DINÂMICAS -->
                                <div class="tab-pane fade" id="tab-faqs" role="tabpanel" aria-labelledby="tab-faqs-btn">
                                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 pb-3 mb-3 border-bottom">
                                        <div>
                                            <h6 class="fw-bold text-body mb-1">
                                                <i class="bi bi-question-diamond text-primary me-2"></i>Perguntas Frequentes do Setor (FAQ)
                                            </h6>
                                            <p class="text-body-secondary small mb-0">
                                                Tire as dúvidas mais comuns dos pacientes sobre preparos de exames, convênios ou agendamentos.
                                            </p>
                                        </div>
                                        <button type="button" class="btn btn-outline-primary btn-sm rounded-pill shadow-xs" id="btnAddFaq">
                                            <i class="bi bi-plus-lg me-1"></i> Adicionar Pergunta (FAQ)
                                        </button>
                                    </div>

                                    <!-- Container Dinâmico de FAQs -->
                                    <div id="containerFaqs" class="d-flex flex-column gap-3">
                                        <!-- Primeira Pergunta Padrão -->
                                        <div class="p-3 border rounded-3 dynamic-row-item">
                                            <div class="d-flex align-items-center justify-content-between mb-2">
                                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle small fw-semibold">
                                                    Pergunta #1
                                                </span>
                                                <button type="button" class="btn btn-outline-danger btn-sm py-0 px-2 btn-remover-faq" title="Remover esta pergunta">
                                                    <i class="bi bi-trash3 me-1"></i>Remover
                                                </button>
                                            </div>
                                            <div class="mb-2">
                                                <label class="form-label small fw-semibold text-body-secondary mb-1">Título da Pergunta / Dúvida</label>
                                                <input type="text" class="form-control fw-semibold" name="fq_title[]" placeholder="Ex: É necessário agendamento prévio ou jejum para o atendimento?" value="É necessário agendamento prévio para consultas?">
                                            </div>
                                            <div>
                                                <label class="form-label small fw-semibold text-body-secondary mb-1">Resposta / Orientação Completa</label>
                                                <textarea class="form-control" name="fq_content[]" rows="2" placeholder="Descreva a resposta e orientações aos pacientes...">Recomendamos o agendamento prévio pela nossa central ou WhatsApp para evitar filas, porém casos de urgência são atendidos conforme triagem.</textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- ABA 4: GOOGLE & SEO -->
                                <div class="tab-pane fade" id="tab-seo" role="tabpanel" aria-labelledby="tab-seo-btn">
                                    <div class="pb-3 mb-3 border-bottom">
                                        <h6 class="fw-bold text-body mb-1">
                                            <i class="bi bi-google text-primary me-2"></i>Otimização para Mecanismos de Busca (Google SEO)
                                        </h6>
                                        <p class="text-body-secondary small mb-0">
                                            Personalize as meta tags e visualize como o departamento será apresentado nos resultados do Google.
                                        </p>
                                    </div>

                                    <!-- Google Search Snippet Preview -->
                                    <div class="mb-4">
                                        <label class="form-label small fw-bold text-uppercase text-body-secondary mb-2">
                                            <i class="bi bi-eye text-primary me-1"></i>Pré-visualização do Resultado no Google
                                        </label>
                                        <div class="google-preview-box shadow-xs">
                                            <div class="google-preview-url">
                                                <span>https://prontosaude.com.br</span>
                                                <span>&rsaquo;</span>
                                                <span>departamento</span>
                                                <span>&rsaquo;</span>
                                                <span id="googlePreviewSlug">departamento-novo</span>
                                            </div>
                                            <div class="google-preview-title" id="googlePreviewTitle">
                                                Nome do Departamento | Pronto Saúde
                                            </div>
                                            <div class="google-preview-desc" id="googlePreviewDesc">
                                                Conheça o departamento hospitalar do Pronto Saúde. Equipe médica altamente qualificada, tecnologia moderna e atendimento humanizado.
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Campos de Metadados -->
                                    <div class="row g-3">
                                        <div class="col-12">
                                            <div class="d-flex justify-content-between align-items-center mb-1">
                                                <label class="form-label fw-semibold text-body mb-0" for="meta_title">
                                                    Meta Título (Título da Página)
                                                </label>
                                                <small class="text-body-secondary" id="metaTitleCount">0 / 60 caracteres</small>
                                            </div>
                                            <input type="text" class="form-control" id="meta_title" name="meta_title" placeholder="Ex: Cardiologia Integrada | Pronto Saúde" maxlength="70">
                                        </div>

                                        <div class="col-12">
                                            <label class="form-label fw-semibold text-body mb-1" for="meta_keyword">
                                                Palavras-chave (Separadas por vírgula)
                                            </label>
                                            <input type="text" class="form-control" id="meta_keyword" name="meta_keyword" placeholder="cardiologia, hospital, exames do coracao, pronto saude, consultas">
                                        </div>

                                        <div class="col-12">
                                            <div class="d-flex justify-content-between align-items-center mb-1">
                                                <label class="form-label fw-semibold text-body mb-0" for="meta_description">
                                                    Meta Descrição (Resumo para o Google)
                                                </label>
                                                <small class="text-body-secondary" id="metaDescCount">0 / 160 caracteres</small>
                                            </div>
                                            <textarea class="form-control" id="meta_description" name="meta_description" rows="3" placeholder="Resuma em até 160 caracteres a especialidade, tratamentos e diferenciais..." maxlength="180"></textarea>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>

                    </div>
                </div>

                <!-- Coluna Lateral (4 colunas): Publicação, Foto e Banner -->
                <div class="col-12 col-lg-4">
                    <div class="d-flex flex-column gap-4">

                        <!-- Card de Publicação & Slug -->
                        <div class="card border-0 shadow-sm rounded-3">
                            <div class="card-header bg-transparent border-bottom p-3">
                                <h6 class="fw-bold text-body mb-0">
                                    <i class="bi bi-send-check text-primary me-2"></i>Publicação &amp; URL
                                </h6>
                            </div>
                            <div class="card-body p-3">
                                
                                <!-- Slug Automático com Prefixo -->
                                <div class="mb-3">
                                    <label class="form-label small fw-semibold text-body-secondary mb-1" for="dep_slug">
                                        URL Amigável (Slug)
                                    </label>
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text bg-body-tertiary">/department/</span>
                                        <input type="text" class="form-control font-monospace" id="dep_slug" name="dep_slug" placeholder="slug-automatico">
                                    </div>
                                    <div class="form-text small text-body-secondary mt-1">
                                        Gerado automaticamente a partir do nome do departamento.
                                    </div>
                                </div>

                                <hr class="my-3 opacity-25">

                                <!-- Botões de Ação -->
                                <div class="d-grid gap-2">
                                    <button type="submit" class="btn btn-primary btn-lg shadow-sm" id="btnSalvarDepartment" name="form1">
                                        <i class="bi bi-check-lg me-1"></i> Cadastrar Departamento
                                    </button>
                                    <a href="department.php" class="btn btn-outline-secondary">
                                        <i class="bi bi-arrow-left me-1"></i> Cancelar e Voltar
                                    </a>
                                </div>

                            </div>
                        </div>

                        <!-- Card de Foto Principal do Departamento -->
                        <div class="card border-0 shadow-sm rounded-3">
                            <div class="card-header bg-transparent border-bottom p-3">
                                <h6 class="fw-bold text-body mb-0">
                                    <i class="bi bi-image text-primary me-2"></i>Foto de Destaque <span class="text-danger">*</span>
                                </h6>
                            </div>
                            <div class="card-body p-3 text-center">
                                
                                <!-- Área de Dropzone / Input -->
                                <div class="upload-dropzone mb-3" id="dropzoneFoto">
                                    <input type="file" id="dep_photo" name="dep_photo" class="d-none" accept="image/jpeg,image/png,image/webp,image/gif" required>
                                    
                                    <!-- Preview Imagem -->
                                    <div id="previewFotoContainer" class="d-none mb-2">
                                        <img src="" id="imgPreviewFoto" class="img-fluid rounded-3 shadow-xs border" style="max-height: 180px; width: 100%; object-fit: cover;">
                                        <div class="mt-2">
                                            <button type="button" class="btn btn-sm btn-outline-danger py-0 px-2" id="btnRemoverFoto">
                                                <i class="bi bi-trash3 me-1"></i>Trocar Imagem
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Placeholder / Ícone -->
                                    <div id="placeholderFoto">
                                        <i class="bi bi-cloud-arrow-up display-5 text-primary mb-2 d-block"></i>
                                        <span class="fw-bold text-body d-block">Clique para selecionar a foto</span>
                                        <small class="text-body-secondary d-block mt-1">Recomendado: 600 &times; 400 px (JPG, PNG ou WEBP)</small>
                                    </div>
                                </div>

                                <div class="small text-body-secondary text-start">
                                    <i class="bi bi-info-circle me-1 text-primary"></i>Esta foto será exibida no card de especialidades da página inicial e na lista de departamentos.
                                </div>

                            </div>
                        </div>

                        <!-- Card de Banner Panorâmico de Cabeçalho -->
                        <div class="card border-0 shadow-sm rounded-3">
                            <div class="card-header bg-transparent border-bottom p-3">
                                <h6 class="fw-bold text-body mb-0">
                                    <i class="bi bi-aspect-ratio text-primary me-2"></i>Banner Panorâmico <span class="text-danger">*</span>
                                </h6>
                            </div>
                            <div class="card-body p-3 text-center">
                                
                                <!-- Área de Dropzone / Input -->
                                <div class="upload-dropzone mb-3" id="dropzoneBanner">
                                    <input type="file" id="dep_banner" name="dep_banner" class="d-none" accept="image/jpeg,image/png,image/webp,image/gif" required>
                                    
                                    <!-- Preview Imagem -->
                                    <div id="previewBannerContainer" class="d-none mb-2">
                                        <img src="" id="imgPreviewBanner" class="img-fluid rounded-3 shadow-xs border" style="max-height: 140px; width: 100%; object-fit: cover;">
                                        <div class="mt-2">
                                            <button type="button" class="btn btn-sm btn-outline-danger py-0 px-2" id="btnRemoverBanner">
                                                <i class="bi bi-trash3 me-1"></i>Trocar Imagem
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Placeholder / Ícone -->
                                    <div id="placeholderBanner">
                                        <i class="bi bi-image display-5 text-info mb-2 d-block"></i>
                                        <span class="fw-bold text-body d-block">Clique para selecionar o banner</span>
                                        <small class="text-body-secondary d-block mt-1">Recomendado: 1920 &times; 600 px (Formato 16:9)</small>
                                    </div>
                                </div>

                                <div class="small text-body-secondary text-start">
                                    <i class="bi bi-info-circle me-1 text-primary"></i>Imagem de fundo exibida no topo panorâmico da página individual do departamento.
                                </div>

                            </div>
                        </div>

                    </div>
                </div>

            </div>
        </form>

    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // 1. Inicialização do Summernote com Adaptação Dark/Light
    if (typeof $ !== 'undefined' && $.fn.summernote) {
        $('#dep_detail').summernote({
            placeholder: 'Descreva a infraestrutura, especialidades atendidas, diferenciais tecnológicos e equipe médica...',
            tabsize: 2,
            height: 220,
            toolbar: [
                ['style', ['style']],
                ['font', ['bold', 'italic', 'underline', 'clear']],
                ['color', ['color']],
                ['para', ['ul', 'ol', 'paragraph']],
                ['table', ['table']],
                ['insert', ['link', 'picture', 'video']],
                ['view', ['fullscreen', 'codeview', 'help']]
            ],
            lang: 'pt-BR'
        });
    }

    // 2. Máscaras de Entrada (Telefone, WhatsApp e CEP)
    const phoneInput = document.getElementById('dep_phone');
    if (phoneInput) {
        phoneInput.addEventListener('input', function (e) {
            let v = e.target.value.replace(/\D/g, '');
            if (v.length > 10) {
                v = v.replace(/^(\d{2})(\d{5})(\d{4}).*/, '($1) $2-$3');
            } else if (v.length > 5) {
                v = v.replace(/^(\d{2})(\d{4})(\d{0,4}).*/, '($1) $2-$3');
            } else if (v.length > 2) {
                v = v.replace(/^(\d{2})(\d{0,5})/, '($1) $2');
            }
            e.target.value = v;
        });
    }

    const cellInput = document.getElementById('dep_fax');
    if (cellInput) {
        cellInput.addEventListener('input', function (e) {
            let v = e.target.value.replace(/\D/g, '');
            if (v.length > 10) {
                v = v.replace(/^(\d{2})(\d{5})(\d{4}).*/, '($1) $2-$3');
            } else if (v.length > 5) {
                v = v.replace(/^(\d{2})(\d{4})(\d{0,4}).*/, '($1) $2-$3');
            } else if (v.length > 2) {
                v = v.replace(/^(\d{2})(\d{0,5})/, '($1) $2');
            }
            e.target.value = v;
        });
    }

    const cepInput = document.getElementById('busca_cep');
    if (cepInput) {
        cepInput.addEventListener('input', function (e) {
            let v = e.target.value.replace(/\D/g, '');
            if (v.length > 5) {
                v = v.replace(/^(\d{5})(\d{1,3}).*/, '$1-$2');
            }
            e.target.value = v;
            if (v.replace(/\D/g, '').length === 8) {
                consultarViaCep(v);
            }
        });
    }

    // 3. Consulta de CEP (ViaCEP)
    function consultarViaCep(cepLimpo) {
        const cep = cepLimpo.replace(/\D/g, '');
        if (cep.length !== 8) return;

        const btnCep = document.getElementById('btnBuscarCep');
        if (btnCep) btnCep.innerHTML = '<span class="spinner-border spinner-border-sm" role="status"></span>';

        fetch(`https://viacep.com.br/ws/${cep}/json/`)
            .then(res => res.json())
            .then(data => {
                if (btnCep) btnCep.innerHTML = '<i class="bi bi-arrow-right"></i>';
                if (!data.erro) {
                    const enderecoArea = document.getElementById('dep_address');
                    const numInput = document.getElementById('numero_endereco');
                    const num = numInput ? numInput.value.trim() : '';

                    let enderecoFormatado = `${data.logradouro || ''}`;
                    if (num) enderecoFormatado += `, ${num}`;
                    if (data.bairro) enderecoFormatado += ` - ${data.bairro}`;
                    if (data.localidade) enderecoFormatado += `, ${data.localidade} - ${data.uf}`;
                    if (data.cep) enderecoFormatado += ` (CEP: ${data.cep})`;

                    if (enderecoArea) enderecoArea.value = enderecoFormatado;
                    if (numInput) numInput.focus();

                    if (typeof toastr !== 'undefined') {
                        toastr.success('Endereço localizado com sucesso!', 'ViaCEP');
                    }
                } else {
                    if (typeof toastr !== 'undefined') {
                        toastr.warning('CEP não localizado nos Correios.', 'Atenção');
                    }
                }
            })
            .catch(() => {
                if (btnCep) btnCep.innerHTML = '<i class="bi bi-arrow-right"></i>';
            });
    }

    const btnBuscarCep = document.getElementById('btnBuscarCep');
    if (btnBuscarCep) {
        btnBuscarCep.addEventListener('click', function () {
            const val = cepInput ? cepInput.value : '';
            consultarViaCep(val);
        });
    }

    const numEnderecoInput = document.getElementById('numero_endereco');
    if (numEnderecoInput) {
        numEnderecoInput.addEventListener('blur', function () {
            const enderecoArea = document.getElementById('dep_address');
            if (enderecoArea && enderecoArea.value && this.value.trim()) {
                if (!enderecoArea.value.includes(this.value.trim())) {
                    let partes = enderecoArea.value.split(' - ');
                    if (partes.length > 0) {
                        partes[0] += ', ' + this.value.trim();
                        enderecoArea.value = partes.join(' - ');
                    }
                }
            }
        });
    }

    // 4. Slug Dinâmico & Atualização de Google Preview
    const nameInput = document.getElementById('dep_name');
    const slugInput = document.getElementById('dep_slug');
    const googlePreviewSlug = document.getElementById('googlePreviewSlug');
    const googlePreviewTitle = document.getElementById('googlePreviewTitle');
    const googlePreviewDesc = document.getElementById('googlePreviewDesc');
    const metaTitleInput = document.getElementById('meta_title');
    const metaDescInput = document.getElementById('meta_description');
    const metaTitleCount = document.getElementById('metaTitleCount');
    const metaDescCount = document.getElementById('metaDescCount');

    function gerarSlug(texto) {
        return texto
            .toString()
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .toLowerCase()
            .trim()
            .replace(/[^a-z0-9\s-]/g, '')
            .replace(/\s+/g, '-')
            .replace(/-+/g, '-');
    }

    if (nameInput) {
        nameInput.addEventListener('input', function () {
            const slug = gerarSlug(this.value);
            if (slugInput) slugInput.value = slug;
            if (googlePreviewSlug) googlePreviewSlug.textContent = slug || 'departamento-novo';
            if (!metaTitleInput.value && googlePreviewTitle) {
                googlePreviewTitle.textContent = (this.value ? this.value + ' | Pronto Saúde' : 'Nome do Departamento | Pronto Saúde');
            }
        });
    }

    if (slugInput) {
        slugInput.addEventListener('input', function () {
            const s = gerarSlug(this.value);
            this.value = s;
            if (googlePreviewSlug) googlePreviewSlug.textContent = s || 'departamento-novo';
        });
    }

    if (metaTitleInput) {
        metaTitleInput.addEventListener('input', function () {
            const len = this.value.length;
            if (metaTitleCount) metaTitleCount.textContent = `${len} / 60 caracteres`;
            if (googlePreviewTitle) {
                googlePreviewTitle.textContent = this.value || (nameInput.value ? nameInput.value + ' | Pronto Saúde' : 'Nome do Departamento | Pronto Saúde');
            }
        });
    }

    if (metaDescInput) {
        metaDescInput.addEventListener('input', function () {
            const len = this.value.length;
            if (metaDescCount) metaDescCount.textContent = `${len} / 160 caracteres`;
            if (googlePreviewDesc) {
                googlePreviewDesc.textContent = this.value || 'Conheça o departamento hospitalar do Pronto Saúde. Equipe médica altamente qualificada, tecnologia moderna e atendimento humanizado.';
            }
        });
    }

    // 5. Upload com Previews de Foto e Banner
    function configurarUploadPreview(dropzoneId, inputId, previewContainerId, imgPreviewId, placeholderId, btnRemoverId) {
        const dropzone = document.getElementById(dropzoneId);
        const input = document.getElementById(inputId);
        const container = document.getElementById(previewContainerId);
        const img = document.getElementById(imgPreviewId);
        const placeholder = document.getElementById(placeholderId);
        const btnRemover = document.getElementById(btnRemoverId);

        if (!dropzone || !input) return;

        dropzone.addEventListener('click', function (e) {
            if (e.target !== btnRemover && !btnRemover.contains(e.target)) {
                input.click();
            }
        });

        dropzone.addEventListener('dragover', function (e) {
            e.preventDefault();
            dropzone.classList.add('dragover');
        });

        dropzone.addEventListener('dragleave', function () {
            dropzone.classList.remove('dragover');
        });

        dropzone.addEventListener('drop', function (e) {
            e.preventDefault();
            dropzone.classList.remove('dragover');
            if (e.dataTransfer.files && e.dataTransfer.files[0]) {
                input.files = e.dataTransfer.files;
                carregarPreview(e.dataTransfer.files[0]);
            }
        });

        input.addEventListener('change', function () {
            if (this.files && this.files[0]) {
                carregarPreview(this.files[0]);
            }
        });

        function carregarPreview(file) {
            const reader = new FileReader();
            reader.onload = function (e) {
                img.src = e.target.result;
                container.classList.remove('d-none');
                placeholder.classList.add('d-none');
            };
            reader.readAsDataURL(file);
        }

        if (btnRemover) {
            btnRemover.addEventListener('click', function (e) {
                e.stopPropagation();
                input.value = '';
                img.src = '';
                container.classList.add('d-none');
                placeholder.classList.remove('d-none');
            });
        }
    }

    configurarUploadPreview('dropzoneFoto', 'dep_photo', 'previewFotoContainer', 'imgPreviewFoto', 'placeholderFoto', 'btnRemoverFoto');
    configurarUploadPreview('dropzoneBanner', 'dep_banner', 'previewBannerContainer', 'imgPreviewBanner', 'placeholderBanner', 'btnRemoverBanner');

    // 6. Adição Dinâmica de Horários
    const btnAddHorario = document.getElementById('btnAddHorario');
    const containerHorarios = document.getElementById('containerHorarios');

    function criarLinhaHorario(dia = '', hora = '') {
        const div = document.createElement('div');
        div.className = 'p-3 border rounded-3 dynamic-row-item d-flex flex-wrap align-items-center gap-3';
        div.innerHTML = `
            <div class="flex-grow-1" style="min-width: 200px;">
                <label class="form-label small fw-semibold text-body-secondary mb-1">Dia / Período</label>
                <input type="text" class="form-control" name="oh_day[]" placeholder="Ex: Segunda a Sexta-feira" value="${dia}">
            </div>
            <div class="flex-grow-1" style="min-width: 200px;">
                <label class="form-label small fw-semibold text-body-secondary mb-1">Horário de Funcionamento</label>
                <input type="text" class="form-control" name="oh_time[]" placeholder="Ex: 07:00 às 19:00" value="${hora}">
            </div>
            <div class="pt-4">
                <button type="button" class="btn btn-outline-danger btn-sm btn-remover-linha" title="Excluir este horário">
                    <i class="bi bi-trash3"></i>
                </button>
            </div>
        `;
        containerHorarios.appendChild(div);
        const inputs = div.querySelectorAll('input');
        if (inputs.length && !dia) inputs[0].focus();
    }

    if (btnAddHorario && containerHorarios) {
        btnAddHorario.addEventListener('click', function () {
            criarLinhaHorario('', '');
        });

        containerHorarios.addEventListener('click', function (e) {
            const btnDel = e.target.closest('.btn-remover-linha');
            if (btnDel) {
                const row = btnDel.closest('.dynamic-row-item');
                if (row) {
                    row.style.opacity = '0';
                    row.style.transform = 'translateY(-10px)';
                    row.style.transition = 'all 0.2s ease';
                    setTimeout(() => row.remove(), 200);
                }
            }
        });

        // Sugestões Rápidas de Horário
        document.querySelectorAll('.btn-sugestao-horario').forEach(btn => {
            btn.addEventListener('click', function () {
                const dia = this.getAttribute('data-dia') || '';
                const hora = this.getAttribute('data-hora') || '';
                criarLinhaHorario(dia, hora);
                if (typeof toastr !== 'undefined') {
                    toastr.info(`Horário adicionado: ${dia} (${hora})`, 'Escala');
                }
            });
        });
    }

    // 7. Adição Dinâmica de Perguntas Frequentes (FAQs)
    const btnAddFaq = document.getElementById('btnAddFaq');
    const containerFaqs = document.getElementById('containerFaqs');

    if (btnAddFaq && containerFaqs) {
        btnAddFaq.addEventListener('click', function () {
            const totalAtual = containerFaqs.querySelectorAll('.dynamic-row-item').length + 1;
            const div = document.createElement('div');
            div.className = 'p-3 border rounded-3 dynamic-row-item';
            div.innerHTML = `
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle small fw-semibold">
                        Pergunta #${totalAtual}
                    </span>
                    <button type="button" class="btn btn-outline-danger btn-sm py-0 px-2 btn-remover-faq" title="Remover esta pergunta">
                        <i class="bi bi-trash3 me-1"></i>Remover
                    </button>
                </div>
                <div class="mb-2">
                    <label class="form-label small fw-semibold text-body-secondary mb-1">Título da Pergunta / Dúvida</label>
                    <input type="text" class="form-control fw-semibold" name="fq_title[]" placeholder="Ex: Como agendar consultas neste setor?">
                </div>
                <div>
                    <label class="form-label small fw-semibold text-body-secondary mb-1">Resposta / Orientação Completa</label>
                    <textarea class="form-control" name="fq_content[]" rows="2" placeholder="Descreva a resposta e orientações aos pacientes..."></textarea>
                </div>
            `;
            containerFaqs.appendChild(div);
            div.querySelector('input').focus();
        });

        containerFaqs.addEventListener('click', function (e) {
            const btnDel = e.target.closest('.btn-remover-faq');
            if (btnDel) {
                const row = btnDel.closest('.dynamic-row-item');
                if (row) {
                    row.style.opacity = '0';
                    row.style.transform = 'translateY(-10px)';
                    row.style.transition = 'all 0.2s ease';
                    setTimeout(() => {
                        row.remove();
                        // Reindexar Badges
                        containerFaqs.querySelectorAll('.dynamic-row-item').forEach((item, index) => {
                            const badge = item.querySelector('.badge');
                            if (badge) badge.textContent = `Pergunta #${index + 1}`;
                        });
                    }, 200);
                }
            }
        });
    }

    // 8. Submissão do Formulário com AJAX & SweetAlert2
    const form = document.getElementById('formAddDepartment');
    const btnSalvar = document.getElementById('btnSalvarDepartment');

    if (form) {
        form.addEventListener('submit', function (e) {
            e.preventDefault();

            // Sincroniza Summernote se ativo
            if (typeof $ !== 'undefined' && $.fn.summernote) {
                $('#dep_detail').val($('#dep_detail').summernote('code'));
            }

            // Validações Básicas Frontend
            const nomeVal = nameInput ? nameInput.value.trim() : '';
            if (!nomeVal) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Atenção',
                        text: 'Por favor, informe o nome do departamento ou clínica.',
                        confirmButtonText: 'OK'
                    }).then(() => {
                        const tabBtn = document.getElementById('tab-geral-btn');
                        if (tabBtn && typeof bootstrap !== 'undefined') {
                            bootstrap.Tab.getOrCreateInstance(tabBtn).show();
                        }
                        if (nameInput) nameInput.focus();
                    });
                }
                return;
            }

            const fotoInput = document.getElementById('dep_photo');
            if (!fotoInput || !fotoInput.files || !fotoInput.files.length) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Foto Obrigatória',
                        text: 'Por favor, selecione uma foto de destaque para o departamento.',
                        confirmButtonText: 'OK'
                    });
                }
                return;
            }

            const bannerInput = document.getElementById('dep_banner');
            if (!bannerInput || !bannerInput.files || !bannerInput.files.length) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Banner Obrigatório',
                        text: 'Por favor, selecione uma imagem de banner panorâmico para o departamento.',
                        confirmButtonText: 'OK'
                    });
                }
                return;
            }

            const originalBtnHtml = btnSalvar.innerHTML;
            btnSalvar.disabled = true;
            btnSalvar.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status"></span>Cadastrando...';

            const formData = new FormData(form);

            fetch('department-add.php', {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Sucesso!',
                            text: data.message || 'Departamento cadastrado com sucesso!',
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => {
                            window.location.href = data.redirect || 'department.php';
                        });
                    } else {
                        window.location.href = data.redirect || 'department.php';
                    }
                } else {
                    btnSalvar.disabled = false;
                    btnSalvar.innerHTML = originalBtnHtml;
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'error',
                            title: 'Erro ao cadastrar',
                            html: data.message || 'Ocorreu um erro ao processar o cadastro.',
                            confirmButtonText: 'Entendido'
                        });
                    }
                }
            })
            .catch(err => {
                console.error(err);
                btnSalvar.disabled = false;
                btnSalvar.innerHTML = originalBtnHtml;
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'error',
                        title: 'Erro de Comunicação',
                        text: 'Houve uma falha ao enviar os dados para o servidor. Tente novamente.',
                        confirmButtonText: 'Fechar'
                    });
                }
            });
        });
    }
});
</script>

<?php require_once('footer.php'); ?>
