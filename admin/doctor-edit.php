<?php
require_once('header.php');

// 1. Validação do ID do Médico
$doctor_id = (int)($_REQUEST['id'] ?? 0);
if ($doctor_id <= 0) {
    header('Location: doctor.php');
    exit;
}

// 2. Busca dados do médico atual
$stmt_doc = $pdo->prepare("SELECT * FROM tbl_doctor WHERE id = ?");
$stmt_doc->execute([$doctor_id]);
$current_doctor = $stmt_doc->fetch(PDO::FETCH_ASSOC);

if (!$current_doctor) {
    header('Location: doctor.php');
    exit;
}

// 3. Carrega Especialidades (Designações) e Clínicas (Departamentos)
$stmt_desig = $pdo->prepare("SELECT designation_id, designation_name FROM tbl_designation ORDER BY designation_name ASC");
$stmt_desig->execute();
$especialidades = $stmt_desig->fetchAll(PDO::FETCH_ASSOC);

$stmt_deps = $pdo->prepare("SELECT dep_id, dep_name FROM tbl_department ORDER BY dep_name ASC");
$stmt_deps->execute();
$clinicas = $stmt_deps->fetchAll(PDO::FETCH_ASSOC);

// Métricas de apoio para os KPIs do topo
$total_medicos        = (int)$pdo->query("SELECT COUNT(*) FROM tbl_doctor")->fetchColumn();
$total_ativos         = (int)$pdo->query("SELECT COUNT(*) FROM tbl_doctor WHERE status = 'Active'")->fetchColumn();
$total_especialidades = count($especialidades);
$total_clinicas       = count($clinicas);

// 4. Processamento do Envio (Suporta AJAX e POST tradicional)
$is_ajax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
           || (isset($_POST['action']) && $_POST['action'] === 'edit_doctor_ajax');

$error_message = '';
$success_message = '';

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

    $current_photo     = trim($_POST['current_photo'] ?? $current_doctor['photo']);
    $current_banner    = trim($_POST['current_banner'] ?? $current_doctor['banner']);

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
        $erros[] = 'Selecione a clínica ou unidade de atendimento.';
    }

    if (!in_array($status, ['Active', 'Inactive'])) {
        $status = 'Active';
    }

    // Diretório de Uploads
    $dir_uploads = __DIR__ . '/../assets/uploads/';
    if (!is_dir($dir_uploads)) {
        @mkdir($dir_uploads, 0777, true);
    }

    // Validação de Nova Foto de Perfil (Opcional na edição)
    $nova_foto_enviada = isset($_FILES['photo']) && !empty($_FILES['photo']['name']);
    $final_photo_name = $current_photo;

    if ($nova_foto_enviada) {
        $ext_foto = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
        $ext_permitidas = ['jpg', 'jpeg', 'png', 'webp', 'gif'];

        if (!in_array($ext_foto, $ext_permitidas)) {
            $valid = 0;
            $erros[] = 'Formato de foto de perfil inválido. Envie arquivos JPG, JPEG, PNG ou WEBP.';
        } elseif ($_FILES['photo']['size'] > 10 * 1024 * 1024) {
            $valid = 0;
            $erros[] = 'A foto de perfil selecionada excede o limite máximo de 10MB.';
        }
    }

    // Validação de Novo Banner de Capa (Opcional na edição)
    $novo_banner_enviado = isset($_FILES['banner']) && !empty($_FILES['banner']['name']);
    $final_banner_name = $current_banner;

    if ($novo_banner_enviado) {
        $ext_banner = strtolower(pathinfo($_FILES['banner']['name'], PATHINFO_EXTENSION));
        $ext_permitidas = ['jpg', 'jpeg', 'png', 'webp', 'gif'];

        if (!in_array($ext_banner, $ext_permitidas)) {
            $valid = 0;
            $erros[] = 'Formato de banner inválido. Envie arquivos JPG, JPEG, PNG ou WEBP.';
        } elseif ($_FILES['banner']['size'] > 15 * 1024 * 1024) {
            $valid = 0;
            $erros[] = 'A imagem do banner excede o limite máximo de 15MB.';
        }
    }

    if ($valid === 1) {
        // Geração / Validação do Slug Amigável
        $base_slug = !empty($slug_input) ? $slug_input : $name;
        $temp_slug = strtolower(trim($base_slug));
        $temp_slug = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $temp_slug);
        $final_slug = preg_replace('/[^a-z0-9\-]+/', '-', $temp_slug);
        $final_slug = trim(preg_replace('/-+/', '-', $final_slug), '-');

        if (empty($final_slug)) {
            $final_slug = 'medico-' . $doctor_id;
        }

        // Verifica duplicidade de slug com outros médicos
        $stmt_slug = $pdo->prepare("SELECT id FROM tbl_doctor WHERE slug = ? AND id != ?");
        $stmt_slug->execute([$final_slug, $doctor_id]);
        if ($stmt_slug->rowCount() > 0) {
            $final_slug .= '-' . $doctor_id;
        }

        // Processa Nova Foto
        if ($nova_foto_enviada) {
            // Remove foto antiga se existir e for arquivo do médico
            if (!empty($current_photo) && file_exists($dir_uploads . $current_photo)) {
                @unlink($dir_uploads . $current_photo);
            }
            $final_photo_name = 'doctor-' . $doctor_id . '-' . time() . '.' . $ext_foto;
            move_uploaded_file($_FILES['photo']['tmp_name'], $dir_uploads . $final_photo_name);
        }

        // Processa Novo Banner
        if ($novo_banner_enviado) {
            // Remove banner antigo se existir
            if (!empty($current_banner) && file_exists($dir_uploads . $current_banner)) {
                @unlink($dir_uploads . $current_banner);
            }
            $final_banner_name = 'doctor-banner-' . $doctor_id . '-' . time() . '.' . $ext_banner;
            move_uploaded_file($_FILES['banner']['tmp_name'], $dir_uploads . $final_banner_name);
        }

        // Atualização no Banco de Dados
        $stmt_upd = $pdo->prepare("UPDATE tbl_doctor SET 
            name = ?,
            slug = ?,
            designation_id = ?,
            registro = ?,
            clinica = ?,
            photo = ?,
            banner = ?,
            degree = ?,
            detail = ?,
            facebook = ?,
            twitter = ?,
            linkedin = ?,
            youtube = ?,
            google_plus = ?,
            instagram = ?,
            flickr = ?,
            address = ?,
            practice_location = ?,
            phone = ?,
            email = ?,
            website = ?,
            status = ?,
            meta_title = ?,
            meta_keyword = ?,
            meta_description = ?
            WHERE id = ?");

        $stmt_upd->execute([
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
            $meta_description,
            $doctor_id
        ]);

        $msg_sucesso = 'Dados do médico atualizados com sucesso!';

        if ($is_ajax) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'status'    => 'success',
                'message'   => $msg_sucesso,
                'doctor_id' => $doctor_id,
                'photo_url' => !empty($final_photo_name) && file_exists($dir_uploads . $final_photo_name) ? '../assets/uploads/' . $final_photo_name : '',
                'banner_url'=> !empty($final_banner_name) && file_exists($dir_uploads . $final_banner_name) ? '../assets/uploads/' . $final_banner_name : ''
            ]);
            exit;
        }

        $success_message = $msg_sucesso;
        // Recarrega os dados do médico atualizado
        $stmt_doc->execute([$doctor_id]);
        $current_doctor = $stmt_doc->fetch(PDO::FETCH_ASSOC);
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

// Extrai variáveis para preenchimento dos campos do formulário
$name              = htmlspecialchars($current_doctor['name'] ?? '', ENT_QUOTES, 'UTF-8');
$slug              = htmlspecialchars($current_doctor['slug'] ?? '', ENT_QUOTES, 'UTF-8');
$designation_id    = (int)($current_doctor['designation_id'] ?? 0);
$clinica           = (int)($current_doctor['clinica'] ?? 0);
$registro          = htmlspecialchars($current_doctor['registro'] ?? '', ENT_QUOTES, 'UTF-8');
$degree            = htmlspecialchars($current_doctor['degree'] ?? '', ENT_QUOTES, 'UTF-8');
$detail            = $current_doctor['detail'] ?? '';
$photo             = $current_doctor['photo'] ?? '';
$banner            = $current_doctor['banner'] ?? '';
$address           = htmlspecialchars($current_doctor['address'] ?? '', ENT_QUOTES, 'UTF-8');
$practice_location = htmlspecialchars($current_doctor['practice_location'] ?? '', ENT_QUOTES, 'UTF-8');
$phone             = htmlspecialchars($current_doctor['phone'] ?? '', ENT_QUOTES, 'UTF-8');
$email             = htmlspecialchars($current_doctor['email'] ?? '', ENT_QUOTES, 'UTF-8');
$website           = htmlspecialchars($current_doctor['website'] ?? '', ENT_QUOTES, 'UTF-8');
$status            = $current_doctor['status'] ?? 'Active';
$is_active         = ($status === 'Active');

// Redes Sociais
$instagram         = htmlspecialchars($current_doctor['instagram'] ?? '', ENT_QUOTES, 'UTF-8');
$linkedin          = htmlspecialchars($current_doctor['linkedin'] ?? '', ENT_QUOTES, 'UTF-8');
$facebook          = htmlspecialchars($current_doctor['facebook'] ?? '', ENT_QUOTES, 'UTF-8');
$youtube           = htmlspecialchars($current_doctor['youtube'] ?? '', ENT_QUOTES, 'UTF-8');
$twitter           = htmlspecialchars($current_doctor['twitter'] ?? '', ENT_QUOTES, 'UTF-8');
$google_plus       = htmlspecialchars($current_doctor['google_plus'] ?? '', ENT_QUOTES, 'UTF-8');
$flickr            = htmlspecialchars($current_doctor['flickr'] ?? '', ENT_QUOTES, 'UTF-8');

// SEO
$meta_title        = htmlspecialchars($current_doctor['meta_title'] ?? '', ENT_QUOTES, 'UTF-8');
$meta_keyword      = htmlspecialchars($current_doctor['meta_keyword'] ?? '', ENT_QUOTES, 'UTF-8');
$meta_description  = htmlspecialchars($current_doctor['meta_description'] ?? '', ENT_QUOTES, 'UTF-8');

// URLs de imagens existentes com fallback
$photo_exists = !empty($photo) && file_exists(__DIR__ . '/../assets/uploads/' . $photo);
$photo_url    = $photo_exists ? '../assets/uploads/' . $photo : '';

$banner_exists = !empty($banner) && file_exists(__DIR__ . '/../assets/uploads/' . $banner);
$banner_url    = $banner_exists ? '../assets/uploads/' . $banner : '';

// URL pública para visualização no site
$url_publica = BASE_URL . 'doctor/' . $slug;
?>

<!-- Cabeçalho da Página -->
<div class="app-content-header pb-2 mb-3 border-bottom">
    <div class="container-fluid">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div>
                <div class="d-flex align-items-center gap-2 mb-1">
                    <h3 class="mb-0 fw-bold text-body">
                        <i class="bi bi-person-gear text-primary me-2"></i>Editar Médico / Especialista
                    </h3>
                    <span class="badge <?= $is_active ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-secondary-subtle text-body border'; ?> rounded-pill px-3 py-1 small">
                        <?= $is_active ? 'Ativo' : 'Inativo'; ?>
                    </span>
                </div>
                <ol class="breadcrumb mb-0 small">
                    <li class="breadcrumb-item"><a href="index.php" class="text-decoration-none"><i class="bi bi-house-door"></i> Início</a></li>
                    <li class="breadcrumb-item"><a href="doctor.php" class="text-decoration-none">Corpo Clínico</a></li>
                    <li class="breadcrumb-item active" aria-current="page"><?= $name ?: 'Editar Médico'; ?></li>
                </ol>
            </div>
            <div class="d-flex flex-wrap align-items-center gap-2">
                <a href="<?= $url_publica; ?>" target="_blank" rel="noopener" class="btn btn-outline-info btn-sm rounded-pill px-3 shadow-sm" data-bs-toggle="tooltip" title="Visualizar perfil público deste médico no portal">
                    <i class="bi bi-box-arrow-up-right me-1"></i> Ver no Site
                </a>
                <a href="doctor-add.php" class="btn btn-outline-secondary btn-sm rounded-pill px-3 shadow-sm" data-bs-toggle="tooltip" title="Cadastrar um novo médico no sistema">
                    <i class="bi bi-plus-lg me-1"></i> Novo Médico
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

        <?php if (!empty($success_message)): ?>
            <div class="alert alert-success alert-dismissible fade show shadow-sm mb-4" role="alert">
                <div class="d-flex align-items-center">
                    <i class="bi bi-check-circle-fill fs-5 me-2"></i>
                    <div><?= $success_message; ?></div>
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

        <!-- Formulário de Edição -->
        <form action="" method="post" enctype="multipart/form-data" id="formDoctorEdit" novalidate>
            <input type="hidden" name="form1" value="1">
            <input type="hidden" name="id" value="<?= $doctor_id; ?>">
            <input type="hidden" name="current_photo" value="<?= htmlspecialchars($photo, ENT_QUOTES, 'UTF-8'); ?>">
            <input type="hidden" name="current_banner" value="<?= htmlspecialchars($banner, ENT_QUOTES, 'UTF-8'); ?>">

            <div class="row g-4">
                <!-- Coluna Principal com Abas (8 colunas) -->
                <div class="col-12 col-xl-8">
                    <div class="card border-0 shadow-sm rounded-3 mb-4">
                        <!-- Navegação de Abas -->
                        <div class="card-header bg-body border-bottom pt-3 px-4">
                            <ul class="nav nav-pills gap-2" id="doctorEditTabs" role="tablist">
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
                            <div class="tab-content" id="doctorEditTabsContent">
                                
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
                                                   value="<?= $name; ?>"
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
                                                   value="<?= $registro; ?>"
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
                                                   value="<?= $degree; ?>"
                                                   placeholder="Ex: Doutora em Cardiologia pela UFRJ, Especialista pela SBOT...">
                                            <div class="form-text small text-body-secondary">Posição e especializações acadêmicas</div>
                                        </div>
                                    </div>

                                    <!-- Especialidade e Clínica -->
                                    <div class="row g-3 mb-4">
                                        <div class="col-12 col-md-6">
                                            <label for="designation_id" class="form-label fw-bold text-body">
                                                <i class="bi bi-award text-primary me-1"></i> Especialidade Médica <span class="text-danger">*</span>
                                            </label>
                                            <select class="form-select bg-body text-body border select2" id="designation_id" name="designation_id" required>
                                                <option value="" disabled>Selecione a Especialidade...</option>
                                                <?php foreach ($especialidades as $esp): ?>
                                                    <option value="<?= $esp['designation_id']; ?>" <?= ($esp['designation_id'] == $designation_id) ? 'selected' : ''; ?>>
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
                                                <option value="" disabled>Selecione a Unidade...</option>
                                                <?php foreach ($clinicas as $clin): ?>
                                                    <option value="<?= $clin['dep_id']; ?>" <?= ($clin['dep_id'] == $clinica) ? 'selected' : ''; ?>>
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
                                                  placeholder="Escreva a biografia, experiência clínica, tratamentos realizados e diferenciais do especialista..."><?= htmlspecialchars($detail, ENT_QUOTES, 'UTF-8'); ?></textarea>
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
                                                  placeholder="Ex: Av. das Lagoas, 2009 - Gardênia Azul, Rio de Janeiro - RJ, CEP 22765-455"><?= $address; ?></textarea>
                                    </div>

                                    <!-- Local de Atendimento / Consultório -->
                                    <div class="mb-4">
                                        <label for="practice_location" class="form-label fw-bold text-body">
                                            <i class="bi bi-building text-primary me-1"></i> Local de Atendimento / Consultório
                                        </label>
                                        <input type="text" 
                                               class="form-control bg-body text-body border" 
                                               id="practice_location" 
                                               name="practice_location" 
                                               value="<?= $practice_location; ?>"
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
                                                   value="<?= $phone; ?>"
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
                                                   value="<?= $email; ?>"
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
                                                   value="<?= $website; ?>"
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
                                                <input type="text" class="form-control bg-body text-body border" id="instagram" name="instagram" value="<?= $instagram; ?>" placeholder="perfil ou https://instagram.com/...">
                                            </div>
                                        </div>

                                        <div class="col-12 col-md-6">
                                            <label for="linkedin" class="form-label fw-semibold text-body">
                                                <i class="bi bi-linkedin text-primary me-1"></i> LinkedIn
                                            </label>
                                            <input type="text" class="form-control bg-body text-body border" id="linkedin" name="linkedin" value="<?= $linkedin; ?>" placeholder="https://linkedin.com/in/...">
                                        </div>

                                        <div class="col-12 col-md-6">
                                            <label for="facebook" class="form-label fw-semibold text-body">
                                                <i class="bi bi-facebook text-primary me-1"></i> Facebook
                                            </label>
                                            <input type="text" class="form-control bg-body text-body border" id="facebook" name="facebook" value="<?= $facebook; ?>" placeholder="https://facebook.com/...">
                                        </div>

                                        <div class="col-12 col-md-6">
                                            <label for="youtube" class="form-label fw-semibold text-body">
                                                <i class="bi bi-youtube text-danger me-1"></i> YouTube
                                            </label>
                                            <input type="text" class="form-control bg-body text-body border" id="youtube" name="youtube" value="<?= $youtube; ?>" placeholder="https://youtube.com/@...">
                                        </div>

                                        <div class="col-12 col-md-6">
                                            <label for="twitter" class="form-label fw-semibold text-body">
                                                <i class="bi bi-twitter-x text-body me-1"></i> Twitter / X
                                            </label>
                                            <input type="text" class="form-control bg-body text-body border" id="twitter" name="twitter" value="<?= $twitter; ?>" placeholder="https://x.com/...">
                                        </div>

                                        <div class="col-12 col-md-6">
                                            <label for="flickr" class="form-label fw-semibold text-body">
                                                <i class="bi bi-camera text-body-secondary me-1"></i> Galeria / Flickr
                                            </label>
                                            <input type="text" class="form-control bg-body text-body border" id="flickr" name="flickr" value="<?= $flickr; ?>" placeholder="https://flickr.com/...">
                                        </div>
                                    </div>
                                </div>

                                <!-- ABA 4: SEO & GOOGLE -->
                                <div class="tab-pane fade" id="tab-seo" role="tabpanel" aria-labelledby="tab-seo-btn">
                                    <div class="p-3 rounded-3 bg-body-tertiary border mb-4">
                                        <h6 class="fw-bold text-body mb-2 d-flex align-items-center gap-2">
                                            <i class="bi bi-google text-primary"></i> Prévia de Exibição no Google (Snippet SERP)
                                        </h6>
                                        <div class="p-3 bg-body rounded-2 border shadow-xs" style="max-width: 620px;">
                                            <div class="d-flex align-items-center gap-2 mb-1">
                                                <div class="rounded-circle bg-primary-subtle text-primary d-flex align-items-center justify-content-center" style="width: 20px; height: 20px; font-size: 11px;">
                                                    <i class="bi bi-globe"></i>
                                                </div>
                                                <span class="text-body-secondary small font-monospace" style="font-size: 11px;">
                                                    <?= BASE_URL; ?>doctor/<span id="seoPreviewSlug"><?= $slug ?: 'nome-do-medico'; ?></span>
                                                </span>
                                            </div>
                                            <h6 class="text-primary mb-1 fw-bold text-truncate" id="seoPreviewTitle" style="font-size: 16px;">
                                                <?= $meta_title ?: ($name ? $name . ' | Pronto Saúde' : 'Nome do Médico | Pronto Saúde'); ?>
                                            </h6>
                                            <p class="text-body-secondary small mb-0 text-truncate-2" id="seoPreviewDesc" style="font-size: 13px; line-height: 1.4;">
                                                <?= $meta_description ?: 'Conheça a formação, tratamentos e agende consulta com o especialista na clínica Pronto Saúde...'; ?>
                                            </p>
                                        </div>
                                    </div>

                                    <!-- Meta Título -->
                                    <div class="mb-4">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <label for="meta_title" class="form-label fw-bold text-body mb-0">
                                                Meta Título (Título SEO)
                                            </label>
                                            <span class="small text-body-secondary" id="metaTitleCounter">0/70 caracteres</span>
                                        </div>
                                        <input type="text" 
                                               class="form-control bg-body text-body border" 
                                               id="meta_title" 
                                               name="meta_title" 
                                               maxlength="70" 
                                               value="<?= $meta_title; ?>"
                                               placeholder="Ex: Dra. Camila Vasconcelos - Cardiologista no Rio | Pronto Saúde">
                                        <div class="form-text small text-body-secondary">
                                            Recomendado entre 50 e 60 caracteres. Deixe em branco para usar o nome automático.
                                        </div>
                                    </div>

                                    <!-- Meta Palavras-Chave -->
                                    <div class="mb-4">
                                        <label for="meta_keyword" class="form-label fw-bold text-body">
                                            Palavras-chave (Tags SEO)
                                        </label>
                                        <input type="text" 
                                               class="form-control bg-body text-body border" 
                                               id="meta_keyword" 
                                               name="meta_keyword" 
                                               value="<?= $meta_keyword; ?>"
                                               placeholder="cardiologista, médico jacarepaguá, consulta coração rj, pronto saude">
                                        <div class="form-text small text-body-secondary">
                                            Termos separados por vírgula que ajudam nos mecanismos de busca.
                                        </div>
                                    </div>

                                    <!-- Meta Descrição -->
                                    <div class="mb-2">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <label for="meta_description" class="form-label fw-bold text-body mb-0">
                                                Meta Descrição (Resumo SERP)
                                            </label>
                                            <span class="small text-body-secondary" id="metaDescCounter">0/160 caracteres</span>
                                        </div>
                                        <textarea class="form-control bg-body text-body border" 
                                                  id="meta_description" 
                                                  name="meta_description" 
                                                  rows="3" 
                                                  maxlength="160" 
                                                  placeholder="Agende sua consulta com Dr(a)... especialista em... Atendimento ágil e humanizado na clínica Pronto Saúde."><?= $meta_description; ?></textarea>
                                        <div class="form-text small text-body-secondary">
                                            Resumo atrativo de até 160 caracteres exibido abaixo do título no Google.
                                        </div>
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
                                            <?= $is_active ? 'Ativo no Corpo Clínico' : 'Inativo / Pausado no portal'; ?>
                                        </small>
                                    </div>
                                    <div class="form-check form-switch fs-4 mb-0">
                                        <input class="form-check-control form-check-input" 
                                               type="checkbox" 
                                               role="switch" 
                                               id="statusSwitch" 
                                               <?= $is_active ? 'checked' : ''; ?>>
                                        <input type="hidden" name="status" id="statusInput" value="<?= $status; ?>">
                                    </div>
                                </div>
                            </div>

                            <!-- Slug da URL Amigável -->
                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <label for="slug" class="form-label fw-bold text-body small mb-0">
                                        Slug da URL
                                    </label>
                                    <button type="button" class="btn btn-link btn-sm p-0 text-decoration-none small" id="btnDesbloquearSlug">
                                        <i class="bi bi-pencil-square me-1"></i>Editar slug
                                    </button>
                                </div>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text bg-body-tertiary text-body-secondary border font-monospace" style="font-size: 11px;">
                                        /doctor/
                                    </span>
                                    <input type="text" 
                                           class="form-control bg-body text-body border font-monospace ps-slug-locked" 
                                           id="slug" 
                                           name="slug" 
                                           readonly 
                                           value="<?= $slug; ?>"
                                           placeholder="gerado-ao-digitar-nome"
                                           style="background-color: var(--bs-tertiary-bg, #f1f5f9); cursor: not-allowed; border-style: dashed;">
                                </div>
                                <div class="form-text small text-body-secondary mt-1 ps-slug-helper d-flex align-items-center gap-1">
                                    <i class="bi bi-lock-fill text-primary"></i> <span class="fw-semibold">Slug protegido:</span> clique em "Editar slug" se desejar alterá-lo.
                                </div>
                            </div>

                            <!-- Botões de Ação -->
                            <div class="d-grid gap-2 mt-4">
                                <button type="submit" class="btn btn-primary rounded-pill py-2 shadow-sm fw-semibold" id="btnSalvarMedico">
                                    <i class="bi bi-check-lg me-1"></i> Salvar Alterações
                                </button>
                                <div class="d-flex gap-2">
                                    <a href="<?= $url_publica; ?>" target="_blank" rel="noopener" class="btn btn-outline-info rounded-pill py-2 w-50">
                                        <i class="bi bi-eye me-1"></i> Ver no Site
                                    </a>
                                    <a href="doctor.php" class="btn btn-outline-secondary rounded-pill py-2 w-50">
                                        Voltar
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Card da Foto de Perfil do Médico -->
                    <div class="card border-0 shadow-sm rounded-3 mb-4">
                        <div class="card-header bg-body py-3 border-bottom d-flex align-items-center justify-content-between">
                            <h6 class="card-title mb-0 fw-bold text-body d-flex align-items-center gap-2">
                                <i class="bi bi-person-bounding-box text-primary"></i> Foto de Perfil
                            </h6>
                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill small">
                                Proporção 1:1
                            </span>
                        </div>

                        <div class="card-body p-3">
                            <div class="position-relative mb-3 rounded-3 overflow-hidden border bg-body-tertiary d-flex align-items-center justify-content-center mx-auto" 
                                 id="photoPreviewWrapper" 
                                 style="width: 160px; height: 160px; aspect-ratio: 1/1;">
                                <div id="photoPlaceholder" class="text-center p-3 text-body-secondary <?= $photo_exists ? 'd-none' : ''; ?>">
                                    <i class="bi bi-person-circle fs-1 d-block mb-1 text-body-tertiary"></i>
                                    <span class="small d-block fw-semibold" style="font-size: 11px;">Sem foto cadastrada</span>
                                </div>
                                <img src="<?= $photo_url; ?>" alt="Foto do médico" id="photoPreviewImg" class="w-100 h-100 object-fit-cover <?= $photo_exists ? '' : 'd-none'; ?>">
                                
                                <?php if ($photo_exists): ?>
                                    <span class="position-absolute bottom-0 start-50 translate-middle-x badge bg-dark bg-opacity-75 rounded-pill mb-2 px-2 py-1 small" style="font-size: 10px;" id="badgeFotoAtual">
                                        Foto Atual
                                    </span>
                                <?php endif; ?>
                            </div>

                            <div class="mb-2">
                                <label for="photo" class="form-label small fw-bold text-body mb-1 d-flex justify-content-between">
                                    <span><?= $photo_exists ? 'Substituir Foto de Perfil' : 'Enviar Foto de Perfil'; ?></span>
                                    <?php if ($photo_exists): ?>
                                        <span class="text-success small"><i class="bi bi-check-circle me-1"></i>Possui foto</span>
                                    <?php endif; ?>
                                </label>
                                <input type="file" 
                                       class="form-control form-control-sm bg-body text-body border" 
                                       id="photo" 
                                       name="photo" 
                                       accept="image/*">
                            </div>

                            <div class="small text-body-secondary text-center" style="font-size: 11px;">
                                <i class="bi bi-info-circle me-1"></i>Formatos: WEBP, JPG, PNG. Deixe em branco para manter a foto atual.
                            </div>
                        </div>
                    </div>

                    <!-- Card do Banner de Capa do Médico -->
                    <div class="card border-0 shadow-sm rounded-3 mb-4">
                        <div class="card-header bg-body py-3 border-bottom d-flex align-items-center justify-content-between">
                            <h6 class="card-title mb-0 fw-bold text-body d-flex align-items-center gap-2">
                                <i class="bi bi-image text-primary"></i> Banner de Capa
                            </h6>
                            <span class="badge bg-secondary-subtle text-body border rounded-pill small">
                                16:9 / Panorâmico
                            </span>
                        </div>

                        <div class="card-body p-3">
                            <div class="position-relative mb-3 rounded-3 overflow-hidden border bg-body-tertiary d-flex align-items-center justify-content-center" 
                                 id="bannerPreviewWrapper" 
                                 style="aspect-ratio: 16/9; min-height: 120px;">
                                <div id="bannerPlaceholder" class="text-center p-3 text-body-secondary <?= $banner_exists ? 'd-none' : ''; ?>">
                                    <i class="bi bi-card-image fs-2 d-block mb-1 text-body-tertiary"></i>
                                    <span class="small d-block fw-semibold" style="font-size: 11px;">Sem banner cadastrado</span>
                                </div>
                                <img src="<?= $banner_url; ?>" alt="Banner do médico" id="bannerPreviewImg" class="w-100 h-100 object-fit-cover <?= $banner_exists ? '' : 'd-none'; ?>">
                                
                                <?php if ($banner_exists): ?>
                                    <span class="position-absolute bottom-0 end-0 badge bg-dark bg-opacity-75 rounded-pill m-2 px-2 py-1 small" style="font-size: 10px;" id="badgeBannerAtual">
                                        Banner Atual
                                    </span>
                                <?php endif; ?>
                            </div>

                            <div class="mb-2">
                                <label for="banner" class="form-label small fw-bold text-body mb-1 d-flex justify-content-between">
                                    <span><?= $banner_exists ? 'Substituir Banner de Capa' : 'Enviar Banner de Capa'; ?></span>
                                    <?php if ($banner_exists): ?>
                                        <span class="text-success small"><i class="bi bi-check-circle me-1"></i>Possui banner</span>
                                    <?php endif; ?>
                                </label>
                                <input type="file" 
                                       class="form-control form-control-sm bg-body text-body border" 
                                       id="banner" 
                                       name="banner" 
                                       accept="image/*">
                            </div>

                            <div class="small text-body-secondary text-center" style="font-size: 11px;">
                                <i class="bi bi-info-circle me-1"></i>Formatos: WEBP, JPG, PNG. Deixe em branco para manter o banner atual.
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
    // 1. Controle de Slug e Google Snippet Preview
    const inputName       = document.getElementById('name');
    const inputSlug       = document.getElementById('slug');
    const btnUnlockSlug   = document.getElementById('btnDesbloquearSlug');
    const selectDesig     = document.getElementById('designation_id');
    const seoTitle        = document.getElementById('meta_title');
    const seoDesc         = document.getElementById('meta_description');
    const seoPrevTitle    = document.getElementById('seoPreviewTitle');
    const seoPrevSlug     = document.getElementById('seoPreviewSlug');
    const seoPrevDesc     = document.getElementById('seoPreviewDesc');
    const counterMetaT    = document.getElementById('metaTitleCounter');
    const counterMetaD    = document.getElementById('metaDescCounter');

    let slugUnlocked = false;

    function gerarSlug(texto) {
        return texto
            .toLowerCase()
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .replace(/[^a-z0-9]+/g, '-')
            .replace(/^-+|-+$/g, '');
    }

    if (btnUnlockSlug && inputSlug) {
        btnUnlockSlug.addEventListener('click', function () {
            slugUnlocked = !slugUnlocked;
            if (slugUnlocked) {
                inputSlug.removeAttribute('readonly');
                inputSlug.style.backgroundColor = '';
                inputSlug.style.cursor = 'text';
                inputSlug.style.borderStyle = 'solid';
                inputSlug.focus();
                this.innerHTML = '<i class="bi bi-lock me-1"></i>Bloquear slug';
            } else {
                inputSlug.setAttribute('readonly', 'readonly');
                inputSlug.style.backgroundColor = 'var(--bs-tertiary-bg, #f1f5f9)';
                inputSlug.style.cursor = 'not-allowed';
                inputSlug.style.borderStyle = 'dashed';
                this.innerHTML = '<i class="bi bi-pencil-square me-1"></i>Editar slug';
            }
        });

        inputSlug.addEventListener('input', function () {
            const limpo = gerarSlug(this.value);
            if (seoPrevSlug) seoPrevSlug.textContent = limpo || 'nome-do-medico';
        });
    }

    function atualizarSeoPreview() {
        const nome = (inputName ? inputName.value.trim() : '');
        const espTexto = selectDesig && selectDesig.selectedIndex > 0 ? selectDesig.options[selectDesig.selectedIndex].text : '';

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
        const updateTitleCount = function () {
            const len = seoTitle.value.length;
            if (counterMetaT) counterMetaT.textContent = `${len}/70 caracteres`;
            atualizarSeoPreview();
        };
        seoTitle.addEventListener('input', updateTitleCount);
        updateTitleCount();
    }

    if (seoDesc) {
        const updateDescCount = function () {
            const len = seoDesc.value.length;
            if (counterMetaD) counterMetaD.textContent = `${len}/160 caracteres`;
            if (seoPrevDesc) {
                seoPrevDesc.textContent = seoDesc.value.trim() ? seoDesc.value : 'Conheça a formação, tratamentos e agende consulta com o especialista na clínica Pronto Saúde...';
            }
        };
        seoDesc.addEventListener('input', updateDescCount);
        updateDescCount();
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
                            
                            if (typeof toastr !== 'undefined') {
                                toastr.success('Endereço localizado com sucesso!', 'ViaCEP');
                            }
                        } else {
                            if (typeof toastr !== 'undefined') {
                                toastr.warning('CEP não encontrado.', 'ViaCEP');
                            }
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
    function configurarPreview(inputId, imgId, placeholderId, badgeId) {
        const input = document.getElementById(inputId);
        const img   = document.getElementById(imgId);
        const ph    = document.getElementById(placeholderId);
        const badge = badgeId ? document.getElementById(badgeId) : null;

        if (input && img && ph) {
            input.addEventListener('change', function () {
                if (this.files && this.files[0]) {
                    const reader = new FileReader();
                    reader.onload = function (e) {
                        img.src = e.target.result;
                        img.classList.remove('d-none');
                        ph.classList.add('d-none');
                        if (badge) {
                            badge.textContent = 'Nova Foto Selecionada';
                            badge.className = 'position-absolute bottom-0 start-50 translate-middle-x badge bg-primary text-white rounded-pill mb-2 px-2 py-1 small';
                        }
                    };
                    reader.readAsDataURL(this.files[0]);
                }
            });
        }
    }

    configurarPreview('photo', 'photoPreviewImg', 'photoPlaceholder', 'badgeFotoAtual');
    configurarPreview('banner', 'bannerPreviewImg', 'bannerPlaceholder', 'badgeBannerAtual');

    // 5. Envio Seguro via AJAX com SweetAlert2
    const formDoctor = document.getElementById('formDoctorEdit');
    const btnSalvar  = document.getElementById('btnSalvarMedico');

    if (formDoctor) {
        formDoctor.addEventListener('submit', function (e) {
            e.preventDefault();

            // Sincroniza Summernote se ativo
            if (window.jQuery && jQuery.fn.summernote) {
                jQuery('#detail').val(jQuery('#detail').summernote('code'));
            }

            // Validações básicas no frontend
            const nome  = (inputName ? inputName.value.trim() : '');
            const reg   = document.getElementById('registro').value.trim();
            const esp   = document.getElementById('designation_id').value;
            const clin  = document.getElementById('clinica').value;

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

            // Prepara o FormData
            const formData = new FormData(formDoctor);
            formData.append('action', 'edit_doctor_ajax');

            const btnOriginalHtml = btnSalvar.innerHTML;
            btnSalvar.disabled = true;
            btnSalvar.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Salvando alterações...';

            fetch('doctor-edit.php?id=<?= $doctor_id; ?>', {
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
                            title: 'Alterações Salvas!',
                            text: data.message || 'Os dados do médico foram atualizados com sucesso.',
                            showCancelButton: true,
                            confirmButtonText: '<i class="bi bi-list-ul me-1"></i> Ir para Listagem',
                            cancelButtonText: '<i class="bi bi-check2 me-1"></i> Continuar Editando',
                            confirmButtonColor: '#0a6b74',
                            cancelButtonColor: '#6c757d'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                window.location.href = 'doctor.php';
                            }
                        });
                    } else if (typeof toastr !== 'undefined') {
                        toastr.success(data.message, 'Sucesso');
                    }
                } else {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'error',
                            title: 'Erro ao Salvar',
                            html: data.message || 'Não foi possível atualizar os dados do médico.'
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
