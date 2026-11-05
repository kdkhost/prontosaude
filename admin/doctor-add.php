<?php
require_once('header.php');

// 1. Carrega Especialidades (Designações) e Clínicas (Departamentos)
$stmt_desig = $pdo->prepare("SELECT designation_id, designation_name FROM tbl_designation ORDER BY designation_name ASC");
$stmt_desig->execute();
$especialidades = $stmt_desig->fetchAll(PDO::FETCH_ASSOC);

$stmt_deps = $pdo->prepare("SELECT dep_id, dep_name FROM tbl_department ORDER BY dep_name ASC");
$stmt_deps->execute();
$clinicas = $stmt_deps->fetchAll(PDO::FETCH_ASSOC);

// Métricas de apoio para os KPIs do topo
$total_medicos      = (int)$pdo->query("SELECT COUNT(*) FROM tbl_doctor")->fetchColumn();
$total_ativos       = (int)$pdo->query("SELECT COUNT(*) FROM tbl_doctor WHERE status = 'Active'")->fetchColumn();
$total_especialidades = count($especialidades);
$total_clinicas     = count($clinicas);

// 2. Processamento do Envio (Suporta AJAX e POST tradicional)
$is_ajax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
           || (isset($_POST['action']) && $_POST['action'] === 'add_doctor_ajax');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && (isset($_POST['form1']) || $is_ajax)) {
    $name              = trim($_POST['name'] ?? '');
    $registro          = trim($_POST['registro'] ?? '');
    $slug_input        = trim($_POST['slug'] ?? '');
    $designation_id    = (int)($_POST['designation_id'] ?? 0);
    $clinica           = (int)($_POST['clinica'] ?? 0);
    $degree            = trim($_POST['degree'] ?? '');
    $detail            = trim($_POST['detail'] ?? '');
    $address           = trim($_POST['address'] ?? '');
    $practice_location = trim($_POST['practice_location'] ?? '');
    $phone             = trim($_POST['phone'] ?? '');
    $email             = trim($_POST['email'] ?? '');
    $website           = trim($_POST['website'] ?? '');
    $status            = trim($_POST['status'] ?? 'Active');

    // Redes Sociais
    $instagram         = trim($_POST['instagram'] ?? '');
    $linkedin          = trim($_POST['linkedin'] ?? '');
    $facebook          = trim($_POST['facebook'] ?? '');
    $youtube           = trim($_POST['youtube'] ?? '');
    $twitter           = trim($_POST['twitter'] ?? '');
    $google_plus       = trim($_POST['google_plus'] ?? '');
    $flickr            = trim($_POST['flickr'] ?? '');

    // SEO
    $meta_title        = trim(strip_tags($_POST['meta_title'] ?? ''));
    $meta_keyword      = trim(strip_tags($_POST['meta_keyword'] ?? ''));
    $meta_description  = trim(strip_tags($_POST['meta_description'] ?? ''));
    $meta_description  = preg_replace('/[\r\n\t]+/', ' ', $meta_description);
    $meta_description  = preg_replace('/\s+/', ' ', $meta_description);

    $valid = 1;
    $erros = [];

    // Validação de Campos Obrigatórios
    if (empty($name)) {
        $valid = 0;
        $erros[] = 'Informe o nome completo do médico ou especialista.';
    }

    if (empty($registro)) {
        $valid = 0;
        $erros[] = 'Informe o registro profissional do especialista (Ex: CRM, CRO, CRP, CRN, CRFA...).';
    }

    if ($designation_id <= 0) {
        $valid = 0;
        $erros[] = 'Selecione a especialidade médica correspondente.';
    }

    if ($clinica <= 0) {
        $valid = 0;
        $erros[] = 'Selecione a clínica ou departamento hospitalar de atendimento.';
    }

    if (!in_array($status, ['Active', 'Inactive'])) {
        $status = 'Active';
    }

    // Validação da Foto de Perfil
    $foto_enviada = isset($_FILES['photo']) && !empty($_FILES['photo']['name']);
    if (!$foto_enviada) {
        $valid = 0;
        $erros[] = 'Selecione uma foto de perfil para o médico.';
    } else {
        $ext_foto = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
        $ext_permitidas = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
        if (!in_array($ext_foto, $ext_permitidas)) {
            $valid = 0;
            $erros[] = 'Formato de foto de perfil inválido. Envie arquivos JPG, JPEG, PNG ou WEBP.';
        }
    }

    // Validação do Banner de Capa
    $banner_enviado = isset($_FILES['banner']) && !empty($_FILES['banner']['name']);
    if (!$banner_enviado) {
        $valid = 0;
        $erros[] = 'Selecione uma imagem de banner para a página do médico.';
    } else {
        $ext_banner = strtolower(pathinfo($_FILES['banner']['name'], PATHINFO_EXTENSION));
        $ext_permitidas = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
        if (!in_array($ext_banner, $ext_permitidas)) {
            $valid = 0;
            $erros[] = 'Formato de banner inválido. Envie arquivos JPG, JPEG, PNG ou WEBP.';
        }
    }

    if ($valid === 1) {
        // Geração do Slug Amigável
        $base_slug = !empty($slug_input) ? $slug_input : $name;
        $temp_slug = strtolower(trim($base_slug));
        $temp_slug = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $temp_slug);
        $final_slug = preg_replace('/[^a-z0-9\-]+/', '-', $temp_slug);
        $final_slug = trim(preg_replace('/-+/', '-', $final_slug), '-');

        if (empty($final_slug)) {
            $final_slug = 'medico-' . time();
        }

        // Verifica duplicidade de slug
        $stmt_slug = $pdo->prepare("SELECT id FROM tbl_doctor WHERE slug = ?");
        $stmt_slug->execute([$final_slug]);
        if ($stmt_slug->rowCount() > 0) {
            $final_slug .= '-' . time();
        }

        // Upload da Foto de Perfil
        $dir_uploads = __DIR__ . '/../assets/uploads/';
        if (!is_dir($dir_uploads)) {
            @mkdir($dir_uploads, 0777, true);
        }

        $final_photo_name = 'doctor-' . time() . '-' . rand(100, 999) . '.' . $ext_foto;
        move_uploaded_file($_FILES['photo']['tmp_name'], $dir_uploads . $final_photo_name);

        // Upload do Banner
        $final_banner_name = 'doctor-banner-' . time() . '-' . rand(100, 999) . '.' . $ext_banner;
        move_uploaded_file($_FILES['banner']['tmp_name'], $dir_uploads . $final_banner_name);

        // Inserção no Banco de Dados
        $stmt_ins = $pdo->prepare("INSERT INTO tbl_doctor (
            name,
            slug,
            designation_id,
            registro,
            clinica,
            photo,
            banner,
            degree,
            detail,
            facebook,
            twitter,
            linkedin,
            youtube,
            google_plus,
            instagram,
            flickr,
            address,
            practice_location,
            phone,
            email,
            website,
            status,
            meta_title,
            meta_keyword,
            meta_description
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

        $stmt_ins->execute([
            $name,
            $final_slug,
            $designation_id,
            $registro,
            $clinica,
            $final_photo_name,
            $final_banner_name,
            $degree,
            $detail,
            $facebook,
            $twitter,
            $linkedin,
            $youtube,
            $google_plus,
            $instagram,
            $flickr,
            $address,
            $practice_location,
            $phone,
            $email,
            $website,
            $status,
            $meta_title,
            $meta_keyword,
            $meta_description
        ]);

        $novo_id = $pdo->lastInsertId();
        $msg_sucesso = 'Médico cadastrado com sucesso no corpo clínico!';

        if ($is_ajax) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'status'    => 'success',
                'message'   => $msg_sucesso,
                'doctor_id' => $novo_id
            ]);
            exit;
        }

        $_SESSION['sucesso'] = $msg_sucesso;
        header('Location: doctor.php');
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
?>

<!-- Cabeçalho da Página -->
<div class="app-content-header pb-2 mb-3 border-bottom">
    <div class="container-fluid">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div>
                <h3 class="mb-1 fw-bold text-body">
                    <i class="bi bi-person-badge-fill text-primary me-2"></i>Adicionar Médico / Especialista
                </h3>
                <ol class="breadcrumb mb-0 small">
                    <li class="breadcrumb-item"><a href="index.php" class="text-decoration-none"><i class="bi bi-house-door"></i> Início</a></li>
                    <li class="breadcrumb-item"><a href="doctor.php" class="text-decoration-none">Corpo Clínico</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Novo Médico</li>
                </ol>
            </div>
            <div class="d-flex flex-wrap align-items-center gap-2">
                <a href="../page.php?slug=medicos" target="_blank" rel="noopener" class="btn btn-outline-info btn-sm rounded-pill px-3 shadow-sm" data-bs-toggle="tooltip" title="Visualizar página de médicos no portal público">
                    <i class="bi bi-box-arrow-up-right me-1"></i> Ver no Site
                </a>
                <a href="designation.php" class="btn btn-outline-secondary btn-sm rounded-pill px-3 shadow-sm" data-bs-toggle="tooltip" title="Gerenciar especialidades médicas">
                    <i class="bi bi-award me-1"></i> Especialidades
                </a>
                <a href="doctor.php" class="btn btn-outline-primary btn-sm rounded-pill px-3 shadow-sm" data-bs-toggle="tooltip" title="Ver listagem geral do corpo clínico">
                    <i class="bi bi-list-ul me-1"></i> Ver Médicos
                </a>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">

        <!-- Mensagens Tradicionais de Sessão (Fallback) -->
        <?php if (!empty($error_message)): ?>
            <div class="alert alert-danger alert-dismissible fade show shadow-sm mb-4" role="alert">
                <div class="d-flex align-items-center">
                    <i class="bi bi-exclamation-triangle-fill fs-5 me-2"></i>
                    <div><?= $error_message; ?></div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
            </div>
        <?php endif; ?>

        <!-- Mini Cards de Indicadores (KPIs) com .kpi-icon-circle perfeitamente redondo -->
        <div class="row g-3 mb-4">
            <!-- Total de Médicos -->
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card border-0 shadow-sm rounded-3 h-100 kpi-card">
                    <div class="card-body d-flex align-items-center justify-content-between p-3">
                        <div>
                            <span class="text-uppercase fw-semibold text-body-secondary small d-block mb-1">Corpo Clínico</span>
                            <h3 class="mb-0 fw-bold text-body"><?= $total_medicos; ?></h3>
                            <span class="text-body-secondary small">Médicos no acervo</span>
                        </div>
                        <div class="kpi-icon-circle bg-primary-subtle text-primary">
                            <i class="bi bi-people fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Médicos Ativos -->
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card border-0 shadow-sm rounded-3 h-100 kpi-card">
                    <div class="card-body d-flex align-items-center justify-content-between p-3">
                        <div>
                            <span class="text-uppercase fw-semibold text-body-secondary small d-block mb-1">Em Atendimento</span>
                            <h3 class="mb-0 fw-bold text-success"><?= $total_ativos; ?></h3>
                            <span class="text-body-secondary small">Visíveis aos pacientes</span>
                        </div>
                        <div class="kpi-icon-circle bg-success-subtle text-success">
                            <i class="bi bi-check2-circle fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Especialidades -->
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card border-0 shadow-sm rounded-3 h-100 kpi-card">
                    <div class="card-body d-flex align-items-center justify-content-between p-3">
                        <div>
                            <span class="text-uppercase fw-semibold text-body-secondary small d-block mb-1">Especialidades</span>
                            <h3 class="mb-0 fw-bold text-info"><?= $total_especialidades; ?></h3>
                            <span class="text-body-secondary small">Áreas cadastradas</span>
                        </div>
                        <div class="kpi-icon-circle bg-info-subtle text-info">
                            <i class="bi bi-award fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Clínicas / Unidades -->
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card border-0 shadow-sm rounded-3 h-100 kpi-card">
                    <div class="card-body d-flex align-items-center justify-content-between p-3">
                        <div>
                            <span class="text-uppercase fw-semibold text-body-secondary small d-block mb-1">Unidades / Clínicas</span>
                            <h3 class="mb-0 fw-bold text-warning"><?= $total_clinicas; ?></h3>
                            <span class="text-body-secondary small">Departamentos ativos</span>
                        </div>
                        <div class="kpi-icon-circle bg-warning-subtle text-warning">
                            <i class="bi bi-hospital fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Formulário de Criação -->
        <form action="" method="post" enctype="multipart/form-data" id="formDoctorAdd" novalidate>
            <input type="hidden" name="form1" value="1">

            <div class="row g-4">
                <!-- Coluna Principal com Abas (8 colunas) -->
                <div class="col-12 col-xl-8">
                    <div class="card border-0 shadow-sm rounded-3 mb-4">
                        <!-- Navegação de Abas -->
                        <div class="card-header bg-body border-bottom pt-3 px-4">
                            <ul class="nav nav-pills gap-2" id="doctorAddTabs" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active py-2 px-3 rounded-pill fw-semibold small" id="tab-geral-btn" data-bs-toggle="pill" data-bs-target="#tab-geral" type="button" role="tab" aria-controls="tab-geral" aria-selected="true">
                                        <i class="bi bi-person-lines-fill me-1"></i> Dados & Especialidade
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link py-2 px-3 rounded-pill fw-semibold small" id="tab-contato-btn" data-bs-toggle="pill" data-bs-target="#tab-contato" type="button" role="tab" aria-controls="tab-contato" aria-selected="false">
                                        <i class="bi bi-geo-alt me-1"></i> Endereço & Contatos
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link py-2 px-3 rounded-pill fw-semibold small" id="tab-redes-btn" data-bs-toggle="pill" data-bs-target="#tab-redes" type="button" role="tab" aria-controls="tab-redes" aria-selected="false">
                                        <i class="bi bi-share me-1"></i> Redes Sociais
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link py-2 px-3 rounded-pill fw-semibold small" id="tab-seo-btn" data-bs-toggle="pill" data-bs-target="#tab-seo" type="button" role="tab" aria-controls="tab-seo" aria-selected="false">
                                        <i class="bi bi-search me-1"></i> SEO & Google
                                    </button>
                                </li>
                            </ul>
                        </div>

                        <!-- Corpo das Abas -->
                        <div class="card-body p-4">
                            <div class="tab-content" id="doctorAddTabsContent">
                                
                                <!-- ABA 1: DADOS & ESPECIALIDADE -->
                                <div class="tab-pane fade show active" id="tab-geral" role="tabpanel" aria-labelledby="tab-geral-btn">
                                    <!-- Nome Completo -->
                                    <div class="mb-4">
                                        <label for="name" class="form-label fw-bold text-body">
                                            <i class="bi bi-person text-primary me-1"></i> Nome do Médico / Especialista <span class="text-danger">*</span>
                                        </label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-body-tertiary text-body-secondary border">
                                                <i class="bi bi-person-fill"></i>
                                            </span>
                                            <input type="text" 
                                                   class="form-control form-control-lg bg-body text-body border" 
                                                   id="name" 
                                                   name="name" 
                                                   required 
                                                   placeholder="Ex: Dra. Camila Vasconcelos, Dr. Marcelo Ribeiro...">
                                        </div>
                                    </div>

                                    <!-- Registro e Titulação -->
                                    <div class="row g-3 mb-4">
                                        <div class="col-12 col-md-6">
                                            <label for="registro" class="form-label fw-bold text-body">
                                                <i class="bi bi-card-text text-primary me-1"></i> Registro Profissional <span class="text-danger">*</span>
                                            </label>
                                            <input type="text" 
                                                   class="form-control bg-body text-body border" 
                                                   id="registro" 
                                                   name="registro" 
                                                   required 
                                                   placeholder="Ex: CRM-RJ 123456, CRO-RJ 4567...">
                                            <div class="form-text small text-body-secondary">CRM, CRO, CRP, CRN4, CRFA, CREFITO...</div>
                                        </div>

                                        <div class="col-12 col-md-6">
                                            <label for="degree" class="form-label fw-bold text-body">
                                                <i class="bi bi-mortarboard text-primary me-1"></i> Titulação / Formação
                                            </label>
                                            <input type="text" 
                                                   class="form-control bg-body text-body border" 
                                                   id="degree" 
                                                   name="degree" 
                                                   placeholder="Ex: Doutora em Cardiologia pela UFRJ, Especialista pela SBOT...">
                                            <div class="form-text small text-body-secondary">Posição, especializações acadêmicas</div>
                                        </div>
                                    </div>

                                    <!-- Especialidade e Clínica -->
                                    <div class="row g-3 mb-4">
                                        <div class="col-12 col-md-6">
                                            <label for="designation_id" class="form-label fw-bold text-body">
                                                <i class="bi bi-award text-primary me-1"></i> Especialidade Médica <span class="text-danger">*</span>
                                            </label>
                                            <select class="form-select bg-body text-body border select2" id="designation_id" name="designation_id" required>
                                                <option value="" disabled selected>Selecione a Especialidade...</option>
                                                <?php foreach ($especialidades as $esp): ?>
                                                    <option value="<?= $esp['designation_id']; ?>">
                                                        <?= htmlspecialchars($esp['designation_name'], ENT_QUOTES, 'UTF-8'); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>

                                        <div class="col-12 col-md-6">
                                            <label for="clinica" class="form-label fw-bold text-body">
                                                <i class="bi bi-hospital text-primary me-1"></i> Clínica / Unidade de Atendimento <span class="text-danger">*</span>
                                            </label>
                                            <select class="form-select bg-body text-body border select2" id="clinica" name="clinica" required>
                                                <option value="" disabled selected>Selecione a Unidade...</option>
                                                <?php foreach ($clinicas as $clin): ?>
                                                    <option value="<?= $clin['dep_id']; ?>">
                                                        <?= htmlspecialchars($clin['dep_name'], ENT_QUOTES, 'UTF-8'); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>

                                    <!-- Biografia e Informações Complementares -->
                                    <div class="mb-2">
                                        <label for="detail" class="form-label fw-bold text-body">
                                            <i class="bi bi-file-earmark-person text-primary me-1"></i> Biografia & Histórico Profissional
                                        </label>
                                        <textarea class="form-control" 
                                                  id="detail" 
                                                  name="detail" 
                                                  rows="8" 
                                                  placeholder="Escreva a biografia, experiência clínica, tratamentos realizados e diferenciais do especialista..."></textarea>
                                        <div class="form-text small text-body-secondary mt-1">
                                            Exibido em destaque na página pública do perfil do médico no portal.
                                        </div>
                                    </div>
                                </div>

                                <!-- ABA 2: ENDEREÇO & CONTATOS -->
                                <div class="tab-pane fade" id="tab-contato" role="tabpanel" aria-labelledby="tab-contato-btn">
                                    <!-- Busca por CEP com ViaCEP -->
                                    <div class="p-3 rounded-3 bg-body-tertiary border mb-4">
                                        <div class="row g-3 align-items-center">
                                            <div class="col-12 col-md-6">
                                                <label for="busca_cep" class="form-label fw-bold text-body small mb-1">
                                                    <i class="bi bi-geo-alt-fill text-danger me-1"></i> Buscar por CEP (Autocompletar)
                                                </label>
                                                <div class="input-group">
                                                    <span class="input-group-text bg-body text-body-secondary border">
                                                        <i class="bi bi-search"></i>
                                                    </span>
                                                    <input type="text" 
                                                           class="form-control bg-body text-body border mask-cep" 
                                                           id="busca_cep" 
                                                           placeholder="00000-000" 
                                                           maxlength="9">
                                                </div>
                                            </div>
                                            <div class="col-12 col-md-6">
                                                <label for="numero_endereco" class="form-label fw-bold text-body small mb-1">
                                                    Número e Complemento
                                                </label>
                                                <input type="text" 
                                                       class="form-control bg-body text-body border" 
                                                       id="numero_endereco" 
                                                       placeholder="Ex: Nº 120, Sala 304">
                                            </div>
                                        </div>
                                        <div class="form-text small text-body-secondary mt-2">
                                            <i class="bi bi-magic text-primary me-1"></i>Ao digitar o CEP, rua, bairro e cidade serão inseridos automaticamente no campo de endereço.
                                        </div>
                                    </div>

                                    <!-- Endereço Completo -->
                                    <div class="mb-4">
                                        <label for="address" class="form-label fw-bold text-body">
                                            <i class="bi bi-geo-alt text-primary me-1"></i> Endereço Completo
                                        </label>
                                        <textarea class="form-control bg-body text-body border" 
                                                  id="address" 
                                                  name="address" 
                                                  rows="3" 
                                                  placeholder="Ex: Av. das Lagoas, 2009 - Gardênia Azul, Rio de Janeiro - RJ, CEP 22765-455"></textarea>
                                    </div>

                                    <!-- Local de Trabalho -->
                                    <div class="mb-4">
                                        <label for="practice_location" class="form-label fw-bold text-body">
                                            <i class="bi bi-building text-primary me-1"></i> Local de Atendimento / Consultório
                                        </label>
                                        <input type="text" 
                                               class="form-control bg-body text-body border" 
                                               id="practice_location" 
                                               name="practice_location" 
                                               placeholder="Ex: Bloco A - Consultório 02, Centro Cirúrgico...">
                                    </div>

                                    <!-- Telefones, E-mail e Site -->
                                    <div class="row g-3">
                                        <div class="col-12 col-md-4">
                                            <label for="phone" class="form-label fw-bold text-body">
                                                <i class="bi bi-whatsapp text-success me-1"></i> Telefone / WhatsApp
                                            </label>
                                            <input type="text" 
                                                   class="form-control bg-body text-body border" 
                                                   id="phone" 
                                                   name="phone" 
                                                   placeholder="(21) 99999-9999">
                                        </div>

                                        <div class="col-12 col-md-4">
                                            <label for="email" class="form-label fw-bold text-body">
                                                <i class="bi bi-envelope text-primary me-1"></i> E-mail Profissional
                                            </label>
                                            <input type="email" 
                                                   class="form-control bg-body text-body border" 
                                                   id="email" 
                                                   name="email" 
                                                   placeholder="doutor@prontosaude.com.br">
                                        </div>

                                        <div class="col-12 col-md-4">
                                            <label for="website" class="form-label fw-bold text-body">
                                                <i class="bi bi-globe text-info me-1"></i> Site / Link Externo
                                            </label>
                                            <input type="text" 
                                                   class="form-control bg-body text-body border" 
                                                   id="website" 
                                                   name="website" 
                                                   placeholder="https://...">
                                        </div>
                                    </div>
                                </div>

                                <!-- ABA 3: REDES SOCIAIS -->
                                <div class="tab-pane fade" id="tab-redes" role="tabpanel" aria-labelledby="tab-redes-btn">
                                    <div class="row g-3">
                                        <div class="col-12 col-md-6">
                                            <label for="instagram" class="form-label fw-semibold text-body">
                                                <i class="bi bi-instagram text-danger me-1"></i> Instagram
                                            </label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-body-tertiary text-body-secondary border">@</span>
                                                <input type="text" class="form-control bg-body text-body border" id="instagram" name="instagram" placeholder="perfil ou https://instagram.com/...">
                                            </div>
                                        </div>

                                        <div class="col-12 col-md-6">
                                            <label for="linkedin" class="form-label fw-semibold text-body">
                                                <i class="bi bi-linkedin text-primary me-1"></i> LinkedIn
                                            </label>
                                            <input type="text" class="form-control bg-body text-body border" id="linkedin" name="linkedin" placeholder="https://linkedin.com/in/...">
                                        </div>

                                        <div class="col-12 col-md-6">
                                            <label for="facebook" class="form-label fw-semibold text-body">
                                                <i class="bi bi-facebook text-primary me-1"></i> Facebook
                                            </label>
                                            <input type="text" class="form-control bg-body text-body border" id="facebook" name="facebook" placeholder="https://facebook.com/...">
                                        </div>

                                        <div class="col-12 col-md-6">
                                            <label for="youtube" class="form-label fw-semibold text-body">
                                                <i class="bi bi-youtube text-danger me-1"></i> YouTube
                                            </label>
                                            <input type="text" class="form-control bg-body text-body border" id="youtube" name="youtube" placeholder="https://youtube.com/@...">
                                        </div>

                                        <div class="col-12 col-md-6">
                                            <label for="twitter" class="form-label fw-semibold text-body">
                                                <i class="bi bi-twitter-x me-1"></i> Twitter / X
                                            </label>
                                            <input type="text" class="form-control bg-body text-body border" id="twitter" name="twitter" placeholder="https://x.com/...">
                                        </div>

                                        <div class="col-12 col-md-6">
                                            <label for="google_plus" class="form-label fw-semibold text-body">
                                                <i class="bi bi-google text-warning me-1"></i> Google Perfil
                                            </label>
                                            <input type="text" class="form-control bg-body text-body border" id="google_plus" name="google_plus" placeholder="Link do perfil Google Meu Negócio">
                                        </div>
                                    </div>
                                </div>

                                <!-- ABA 4: SEO & GOOGLE -->
                                <div class="tab-pane fade" id="tab-seo" role="tabpanel" aria-labelledby="tab-seo-btn">
                                    <!-- Google Snippet Preview -->
                                    <div class="p-3 rounded-3 bg-body-tertiary border mb-4">
                                        <div class="small text-body-secondary mb-1 d-flex align-items-center gap-1 font-monospace" style="font-size: 12px;">
                                            <span><?= BASE_URL; ?>doctor/</span><span id="seoPreviewSlug" class="text-primary fw-semibold">nome-do-medico</span>
                                        </div>
                                        <h6 class="text-primary mb-1 fw-bold text-truncate cursor-pointer" id="seoPreviewTitle" style="font-size: 17px;">
                                            Nome do Médico - Especialidade | Pronto Saúde
                                        </h6>
                                        <p class="text-body-secondary small mb-0 text-break" id="seoPreviewDesc" style="font-size: 13px; line-height: 1.4;">
                                            Conheça a formação, tratamentos e agende consulta com o especialista na clínica Pronto Saúde...
                                        </p>
                                    </div>

                                    <!-- Meta Título -->
                                    <div class="mb-3">
                                        <label for="meta_title" class="form-label fw-bold text-body d-flex align-items-center justify-content-between">
                                            <span>Meta Título (Page Title)</span>
                                            <small class="text-body-secondary" id="metaTitleCounter">0/70 caracteres</small>
                                        </label>
                                        <input type="text" 
                                               class="form-control bg-body text-body border" 
                                               id="meta_title" 
                                               name="meta_title" 
                                               maxlength="70" 
                                               placeholder="Título específico para os motores de busca">
                                    </div>

                                    <!-- Palavras-chave -->
                                    <div class="mb-3">
                                        <label for="meta_keyword" class="form-label fw-bold text-body">
                                            Palavras-chave (Meta Keywords)
                                        </label>
                                        <input type="text" 
                                               class="form-control bg-body text-body border" 
                                               id="meta_keyword" 
                                               name="meta_keyword" 
                                               placeholder="cardiologista rj, medico barra da tijuca, consulta cardiologia (separadas por vírgula)">
                                    </div>

                                    <!-- Meta Descrição -->
                                    <div>
                                        <label for="meta_description" class="form-label fw-bold text-body d-flex align-items-center justify-content-between">
                                            <span>Meta Descrição (Meta Description)</span>
                                            <small class="text-body-secondary" id="metaDescCounter">0/160 caracteres</small>
                                        </label>
                                        <textarea class="form-control no-summernote bg-body text-body border" 
                                                  data-no-summernote="true" 
                                                  id="meta_description" 
                                                  name="meta_description" 
                                                  rows="3" 
                                                  maxlength="160" 
                                                  placeholder="Resumo que aparecerá no Google abaixo do título"></textarea>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>

                <!-- Coluna Lateral (4 colunas) -->
                <div class="col-12 col-xl-4">
                    <!-- Card de Publicação & Visibilidade -->
                    <div class="card border-0 shadow-sm rounded-3 mb-4">
                        <div class="card-header bg-body py-3 border-bottom">
                            <h6 class="card-title mb-0 fw-bold text-body d-flex align-items-center gap-2">
                                <i class="bi bi-shield-check text-primary"></i> Publicação & Status
                            </h6>
                        </div>

                        <div class="card-body p-3">
                            <!-- Switch de Visibilidade -->
                            <div class="p-3 rounded-3 bg-body-tertiary border mb-3">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div>
                                        <label for="statusSwitch" class="fw-bold text-body mb-0 d-block cursor-pointer">Status do Médico</label>
                                        <small class="text-body-secondary" id="statusLabelDesc">
                                            Ativo no Corpo Clínico
                                        </small>
                                    </div>
                                    <div class="form-check form-switch fs-4 mb-0">
                                        <input class="form-check-control form-check-input" 
                                               type="checkbox" 
                                               role="switch" 
                                               id="statusSwitch" 
                                               checked>
                                        <input type="hidden" name="status" id="statusInput" value="Active">
                                    </div>
                                </div>
                            </div>

                            <!-- Slug da Notícia / URL Amigável -->
                            <div class="mb-3">
                                <label for="slug" class="form-label fw-bold text-body small d-flex justify-content-between">
                                    <span>Slug da URL</span>
                                    <span class="badge bg-body-secondary text-body-secondary border font-monospace" style="font-size: 10px;">
                                        <i class="bi bi-lock-fill me-1 text-primary"></i>Automático
                                    </span>
                                </label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text bg-body-tertiary text-body-secondary border font-monospace" style="font-size: 11px;">
                                        /doctor/
                                    </span>
                                    <input type="text" 
                                           class="form-control bg-body text-body border font-monospace ps-slug-locked" 
                                           id="slug" 
                                           name="slug" 
                                           readonly 
                                           placeholder="gerado-ao-digitar-nome"
                                           style="background-color: var(--bs-tertiary-bg, #f1f5f9); cursor: not-allowed; border-style: dashed;">
                                </div>
                            </div>

                            <!-- Botões de Ação -->
                            <div class="d-grid gap-2 mt-4">
                                <button type="submit" class="btn btn-primary rounded-pill py-2 shadow-sm fw-semibold" id="btnSalvarMedico">
                                    <i class="bi bi-check-lg me-1"></i> Cadastrar Médico
                                </button>
                                <a href="doctor.php" class="btn btn-outline-secondary rounded-pill py-2">
                                    Cancelar
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Card da Foto de Perfil do Médico -->
                    <div class="card border-0 shadow-sm rounded-3 mb-4">
                        <div class="card-header bg-body py-3 border-bottom d-flex align-items-center justify-content-between">
                            <h6 class="card-title mb-0 fw-bold text-body d-flex align-items-center gap-2">
                                <i class="bi bi-person-bounding-box text-primary"></i> Foto de Perfil <span class="text-danger">*</span>
                            </h6>
                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill small">
                                Proporção 1:1
                            </span>
                        </div>

                        <div class="card-body p-3">
                            <div class="position-relative mb-3 rounded-3 overflow-hidden border bg-body-tertiary d-flex align-items-center justify-content-center mx-auto" 
                                 id="photoPreviewWrapper" 
                                 style="width: 160px; height: 160px; aspect-ratio: 1/1;">
                                <div id="photoPlaceholder" class="text-center p-3 text-body-secondary">
                                    <i class="bi bi-person-circle fs-1 d-block mb-1 text-body-tertiary"></i>
                                    <span class="small d-block fw-semibold" style="font-size: 11px;">Sem foto</span>
                                </div>
                                <img src="" alt="Foto do médico" id="photoPreviewImg" class="w-100 h-100 object-fit-cover d-none">
                            </div>

                            <input type="file" 
                                   class="form-control form-control-sm bg-body text-body border mb-2" 
                                   id="photo" 
                                   name="photo" 
                                   accept="image/*" 
                                   required>

                            <div class="small text-body-secondary text-center" style="font-size: 11px;">
                                <i class="bi bi-info-circle me-1"></i>Formatos: WEBP, JPG, PNG. Recomendado: 500x500 px.
                            </div>
                        </div>
                    </div>

                    <!-- Card do Banner de Capa do Médico -->
                    <div class="card border-0 shadow-sm rounded-3 mb-4">
                        <div class="card-header bg-body py-3 border-bottom d-flex align-items-center justify-content-between">
                            <h6 class="card-title mb-0 fw-bold text-body d-flex align-items-center gap-2">
                                <i class="bi bi-image text-primary"></i> Banner de Capa <span class="text-danger">*</span>
                            </h6>
                            <span class="badge bg-secondary-subtle text-body border rounded-pill small">
                                16:9 / Panorâmico
                            </span>
                        </div>

                        <div class="card-body p-3">
                            <div class="position-relative mb-3 rounded-3 overflow-hidden border bg-body-tertiary d-flex align-items-center justify-content-center" 
                                 id="bannerPreviewWrapper" 
                                 style="aspect-ratio: 16/9; min-height: 120px;">
                                <div id="bannerPlaceholder" class="text-center p-3 text-body-secondary">
                                    <i class="bi bi-card-image fs-2 d-block mb-1 text-body-tertiary"></i>
                                    <span class="small d-block fw-semibold" style="font-size: 11px;">Sem banner selecionado</span>
                                </div>
                                <img src="" alt="Banner do médico" id="bannerPreviewImg" class="w-100 h-100 object-fit-cover d-none">
                            </div>

                            <input type="file" 
                                   class="form-control form-control-sm bg-body text-body border mb-2" 
                                   id="banner" 
                                   name="banner" 
                                   accept="image/*" 
                                   required>

                            <div class="small text-body-secondary text-center" style="font-size: 11px;">
                                <i class="bi bi-info-circle me-1"></i>Formatos: WEBP, JPG, PNG. Recomendado: 1920x400 px.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>

    </div>
</div>

<!-- ======================================================= -->
<!-- Scripts JavaScript Integrados                           -->
<!-- ======================================================= -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    // 1. Geração Automática de Slug e Google Snippet Preview
    const inputName    = document.getElementById('name');
    const inputSlug    = document.getElementById('slug');
    const selectDesig  = document.getElementById('designation_id');
    const seoTitle     = document.getElementById('meta_title');
    const seoDesc      = document.getElementById('meta_description');
    const seoPrevTitle = document.getElementById('seoPreviewTitle');
    const seoPrevSlug  = document.getElementById('seoPreviewSlug');
    const seoPrevDesc  = document.getElementById('seoPreviewDesc');
    const counterMetaT = document.getElementById('metaTitleCounter');
    const counterMetaD = document.getElementById('metaDescCounter');

    function gerarSlug(texto) {
        return texto
            .toLowerCase()
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .replace(/[^a-z0-9]+/g, '-')
            .replace(/^-+|-+$/g, '');
    }

    function atualizarSeoPreview() {
        const nome = (inputName ? inputName.value.trim() : '');
        const espTexto = selectDesig && selectDesig.selectedIndex > 0 ? selectDesig.options[selectDesig.selectedIndex].text : '';
        const slug = gerarSlug(nome);

        if (inputSlug) inputSlug.value = slug;
        if (seoPrevSlug) seoPrevSlug.textContent = slug || 'nome-do-medico';

        if (seoPrevTitle) {
            if (seoTitle && seoTitle.value.trim()) {
                seoPrevTitle.textContent = seoTitle.value;
            } else {
                let tituloAuto = nome || 'Nome do Médico';
                if (espTexto) tituloAuto += ` - ${espTexto}`;
                tituloAuto += ' | Pronto Saúde';
                seoPrevTitle.textContent = tituloAuto;
            }
        }
    }

    if (inputName) {
        inputName.addEventListener('input', atualizarSeoPreview);
    }
    if (selectDesig) {
        selectDesig.addEventListener('change', atualizarSeoPreview);
    }

    if (seoTitle) {
        seoTitle.addEventListener('input', function () {
            const len = this.value.length;
            if (counterMetaT) counterMetaT.textContent = `${len}/70 caracteres`;
            atualizarSeoPreview();
        });
    }

    if (seoDesc) {
        seoDesc.addEventListener('input', function () {
            const len = this.value.length;
            if (counterMetaD) counterMetaD.textContent = `${len}/160 caracteres`;
            if (seoPrevDesc) {
                seoPrevDesc.textContent = this.value.trim() ? this.value : 'Conheça a formação, tratamentos e agende consulta com o especialista na clínica Pronto Saúde...';
            }
        });
    }

    // 2. Switch de Visibilidade
    const statusSwitch = document.getElementById('statusSwitch');
    const statusInput  = document.getElementById('statusInput');
    const statusDesc   = document.getElementById('statusLabelDesc');

    if (statusSwitch && statusInput) {
        statusSwitch.addEventListener('change', function () {
            if (this.checked) {
                statusInput.value = 'Active';
                if (statusDesc) statusDesc.textContent = 'Ativo no Corpo Clínico';
            } else {
                statusInput.value = 'Inactive';
                if (statusDesc) statusDesc.textContent = 'Inativo / Pausado no portal';
            }
        });
    }

    // 3. Autocompletar CEP com ViaCEP
    const inputCep = document.getElementById('busca_cep');
    const inputNum = document.getElementById('numero_endereco');
    const inputEnd = document.getElementById('address');

    if (inputCep && inputEnd) {
        inputCep.addEventListener('blur', function () {
            const cepLimpo = this.value.replace(/\D/g, '');
            if (cepLimpo.length === 8) {
                fetch(`https://viacep.com.br/ws/${cepLimpo}/json/`)
                    .then(response => response.json())
                    .then(data => {
                        if (!data.erro) {
                            let endMontado = `${data.logradouro}`;
                            if (inputNum && inputNum.value.trim()) {
                                endMontado += `, ${inputNum.value.trim()}`;
                            }
                            if (data.bairro) endMontado += ` - ${data.bairro}`;
                            if (data.localidade && data.uf) endMontado += `, ${data.localidade} - ${data.uf}`;
                            endMontado += `, CEP: ${data.cep}`;

                            inputEnd.value = endMontado;
                            if (inputNum) inputNum.focus();
                        }
                    })
                    .catch(err => console.log('Erro ViaCEP:', err));
            }
        });

        if (inputNum) {
            inputNum.addEventListener('input', function () {
                if (inputCep.value.replace(/\D/g, '').length === 8) {
                    inputCep.dispatchEvent(new Event('blur'));
                }
            });
        }
    }

    // 4. Previews Instantâneos de Foto e Banner
    function configurarPreview(inputId, imgId, placeholderId) {
        const input = document.getElementById(inputId);
        const img = document.getElementById(imgId);
        const ph = document.getElementById(placeholderId);

        if (input && img && ph) {
            input.addEventListener('change', function () {
                if (this.files && this.files[0]) {
                    const reader = new FileReader();
                    reader.onload = function (e) {
                        img.src = e.target.result;
                        img.classList.remove('d-none');
                        ph.classList.add('d-none');
                    };
                    reader.readAsDataURL(this.files[0]);
                }
            });
        }
    }

    configurarPreview('photo', 'photoPreviewImg', 'photoPlaceholder');
    configurarPreview('banner', 'bannerPreviewImg', 'bannerPlaceholder');

    // 5. Envio Seguro via AJAX com SweetAlert2
    const formDoctor = document.getElementById('formDoctorAdd');
    const btnSalvar = document.getElementById('btnSalvarMedico');

    if (formDoctor) {
        formDoctor.addEventListener('submit', function (e) {
            e.preventDefault();

            // Sincroniza Summernote se ativo
            if (window.jQuery && jQuery.fn.summernote) {
                jQuery('#detail').val(jQuery('#detail').summernote('code'));
            }

            // Validações básicas no frontend
            const nome = (inputName ? inputName.value.trim() : '');
            const reg = document.getElementById('registro').value.trim();
            const esp = document.getElementById('designation_id').value;
            const clin = document.getElementById('clinica').value;
            const photoEl = document.getElementById('photo');
            const bannerEl = document.getElementById('banner');

            if (!nome) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Nome Obrigatório',
                        text: 'Informe o nome completo do médico ou especialista.'
                    });
                }
                const tabGeral = document.getElementById('tab-geral-btn');
                if (tabGeral) new bootstrap.Tab(tabGeral).show();
                if (inputName) inputName.focus();
                return;
            }

            if (!reg) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Registro Obrigatório',
                        text: 'Informe o registro profissional do especialista (Ex: CRM, CRO...).'
                    });
                }
                const tabGeral = document.getElementById('tab-geral-btn');
                if (tabGeral) new bootstrap.Tab(tabGeral).show();
                document.getElementById('registro').focus();
                return;
            }

            if (!esp || parseInt(esp, 10) <= 0) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Especialidade Obrigatória',
                        text: 'Selecione a especialidade médica correspondente.'
                    });
                }
                const tabGeral = document.getElementById('tab-geral-btn');
                if (tabGeral) new bootstrap.Tab(tabGeral).show();
                return;
            }

            if (!clin || parseInt(clin, 10) <= 0) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Clínica Obrigatória',
                        text: 'Selecione a clínica ou unidade de atendimento do médico.'
                    });
                }
                const tabGeral = document.getElementById('tab-geral-btn');
                if (tabGeral) new bootstrap.Tab(tabGeral).show();
                return;
            }

            if (!photoEl || !photoEl.files || photoEl.files.length === 0) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Foto de Perfil Obrigatória',
                        text: 'Selecione uma foto de perfil para o médico.'
                    });
                }
                return;
            }

            if (!bannerEl || !bannerEl.files || bannerEl.files.length === 0) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Banner Obrigatório',
                        text: 'Selecione uma imagem de banner para a página do médico.'
                    });
                }
                return;
            }

            // Prepara o FormData
            const formData = new FormData(formDoctor);
            formData.append('action', 'add_doctor_ajax');

            const btnOriginalHtml = btnSalvar.innerHTML;
            btnSalvar.disabled = true;
            btnSalvar.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Cadastrando médico...';

            fetch('doctor-add.php', {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                btnSalvar.disabled = false;
                btnSalvar.innerHTML = btnOriginalHtml;

                if (data.status === 'success') {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Médico Cadastrado!',
                            text: data.message || 'O especialista foi adicionado com sucesso ao corpo clínico.',
                            timer: 1800,
                            showConfirmButton: false
                        }).then(() => {
                            window.location.href = 'doctor.php';
                        });
                    } else {
                        window.location.href = 'doctor.php';
                    }
                } else {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'error',
                            title: 'Erro ao Cadastrar',
                            html: data.message || 'Não foi possível cadastrar o médico.'
                        });
                    }
                }
            })
            .catch(err => {
                console.error(err);
                btnSalvar.disabled = false;
                btnSalvar.innerHTML = btnOriginalHtml;
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'error',
                        title: 'Falha de Comunicação',
                        text: 'Ocorreu um erro ao enviar os dados para o servidor. Tente novamente.'
                    });
                }
            });
        });
    }

    // 6. Tooltips com Auto-Hide
    if (typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
        document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => {
            new bootstrap.Tooltip(el, { trigger: 'hover', boundary: 'window' });
            el.addEventListener('mouseleave', () => {
                const tip = bootstrap.Tooltip.getInstance(el);
                if (tip) tip.hide();
            });
            el.addEventListener('click', () => {
                const tip = bootstrap.Tooltip.getInstance(el);
                if (tip) tip.hide();
            });
        });
    }
});
</script>

<?php require_once('footer.php'); ?>
