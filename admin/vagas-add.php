<?php
require_once('header.php');

// 1. Estatísticas de Vagas para os Indicadores (KPIs do topo)
$stmt_stats = $pdo->query("SELECT 
                                COUNT(*) AS total_vagas,
                                SUM(CASE WHEN status = 1 THEN 1 ELSE 0 END) AS total_ativas,
                                SUM(CASE WHEN status = 0 OR status IS NULL THEN 1 ELSE 0 END) AS total_inativas,
                                COUNT(DISTINCT local) AS total_locais
                           FROM tbl_trabalhe");
$stats = $stmt_stats->fetch(PDO::FETCH_ASSOC);

$total_vagas    = (int)($stats['total_vagas'] ?? 0);
$total_ativas   = (int)($stats['total_ativas'] ?? 0);
$total_inativas = (int)($stats['total_inativas'] ?? 0);
$total_locais   = (int)($stats['total_locais'] ?? 0);

// Carrega unidades cadastradas para sugestão
$stmt_deps = $pdo->query("SELECT dep_name FROM tbl_department ORDER BY dep_name ASC");
$unidades_sugestoes = $stmt_deps->fetchAll(PDO::FETCH_COLUMN);

// 2. Processamento do Envio (Suporta AJAX e POST tradicional)
$is_ajax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
           || (isset($_POST['action']) && $_POST['action'] === 'add_vaga_ajax');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && (isset($_POST['form1']) || $is_ajax)) {
    $titulo      = trim($_POST['titulo'] ?? '');
    $local       = trim($_POST['local'] ?? '');
    $carga       = trim($_POST['carga'] ?? '');
    $horario     = trim($_POST['horario'] ?? '');
    $salario     = trim($_POST['salario'] ?? '');
    $beneficios  = trim($_POST['beneficios'] ?? '');
    $requisitos  = trim($_POST['requisitos'] ?? '');
    $detalhes    = trim($_POST['detalhes'] ?? '');
    $status      = isset($_POST['status']) && (int)$_POST['status'] === 1 ? 1 : 0;

    $valid = 1;
    $erros = [];

    // Validação de Campos Obrigatórios
    if (empty($titulo)) {
        $valid = 0;
        $erros[] = 'Informe o cargo ou título da oportunidade de emprego.';
    }

    if (empty($local)) {
        $valid = 0;
        $erros[] = 'Informe a unidade ou local de trabalho da vaga.';
    }

    if (empty($carga)) {
        $carga = 'A consultar';
    }

    if (empty($salario)) {
        $salario = 'A combinar';
    }

    if (empty($requisitos)) {
        $valid = 0;
        $erros[] = 'Informe os requisitos e qualificações necessárias para a vaga.';
    }

    // Processamento da Foto em Destaque
    $final_foto_name = '';
    if (isset($_FILES['photo']) && !empty($_FILES['photo']['name'])) {
        $file_name = $_FILES['photo']['name'];
        $file_tmp  = $_FILES['photo']['tmp_name'];
        $file_size = $_FILES['photo']['size'];
        $file_err  = $_FILES['photo']['error'];

        if ($file_err !== UPLOAD_ERR_OK) {
            $valid = 0;
            $erros[] = 'Erro durante o upload da imagem da vaga (Código: ' . $file_err . ').';
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
                $erros[] = 'A imagem selecionada excede o limite máximo permitido de 15MB.';
            }
        }
    }

    if ($valid === 1) {
        // Se enviou foto, move para assets/uploads/
        if (isset($_FILES['photo']) && !empty($_FILES['photo']['name']) && !empty($file_tmp)) {
            $ext = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
            $final_foto_name = 'vagas-' . time() . '-' . rand(100, 999) . '.' . $ext;
            $dir_uploads = __DIR__ . '/../assets/uploads/';

            if (!is_dir($dir_uploads)) {
                @mkdir($dir_uploads, 0777, true);
            }

            if (!move_uploaded_file($file_tmp, $dir_uploads . $final_foto_name)) {
                $final_foto_name = '';
            }
        }

        // Inserção no Banco de Dados com ordem cirurgicamente correta
        $stmt_ins = $pdo->prepare("INSERT INTO tbl_trabalhe (
            titulo,
            detalhes,
            foto,
            requisitos,
            carga,
            local,
            beneficios,
            salario,
            horario,
            status
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

        $stmt_ins->execute([
            $titulo,
            $detalhes,
            $final_foto_name,
            $requisitos,
            $carga,
            $local,
            $beneficios,
            $salario,
            $horario,
            $status
        ]);

        $novo_id = $pdo->lastInsertId();
        $msg_sucesso = 'Oportunidade de emprego cadastrada com sucesso!';

        if ($is_ajax) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'status'  => 'success',
                'message' => $msg_sucesso,
                'vaga_id' => $novo_id
            ]);
            exit;
        }

        $_SESSION['sucesso'] = $msg_sucesso;
        header('Location: trabalhe-conosco.php');
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
                    <i class="bi bi-briefcase-fill text-primary me-2"></i>Cadastrar Oportunidade / Vaga
                </h3>
                <ol class="breadcrumb mb-0 small">
                    <li class="breadcrumb-item"><a href="index.php" class="text-decoration-none"><i class="bi bi-house-door"></i> Início</a></li>
                    <li class="breadcrumb-item"><a href="trabalhe-conosco.php" class="text-decoration-none">Trabalhe Conosco</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Nova Vaga</li>
                </ol>
            </div>
            <div class="d-flex flex-wrap align-items-center gap-2">
                <a href="../page.php?slug=trabalhe-conosco" target="_blank" rel="noopener" class="btn btn-outline-info btn-sm rounded-pill px-3 shadow-sm" data-bs-toggle="tooltip" title="Visualizar página Trabalhe Conosco no site público">
                    <i class="bi bi-box-arrow-up-right me-1"></i> Ver no Site
                </a>
                <a href="trabalhe-conosco.php" class="btn btn-outline-primary btn-sm rounded-pill px-3 shadow-sm" data-bs-toggle="tooltip" title="Ver listagem geral de vagas cadastradas">
                    <i class="bi bi-list-ul me-1"></i> Ver Listagem
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
            <!-- Total de Vagas -->
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card border-0 shadow-sm rounded-3 h-100 kpi-card">
                    <div class="card-body d-flex align-items-center justify-content-between p-3">
                        <div>
                            <span class="text-uppercase fw-semibold text-body-secondary small d-block mb-1">Total de Vagas</span>
                            <h3 class="mb-0 fw-bold text-body"><?= $total_vagas; ?></h3>
                            <span class="text-body-secondary small">Oportunidades no histórico</span>
                        </div>
                        <div class="kpi-icon-circle bg-primary-subtle text-primary">
                            <i class="bi bi-briefcase fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Vagas Abertas -->
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card border-0 shadow-sm rounded-3 h-100 kpi-card">
                    <div class="card-body d-flex align-items-center justify-content-between p-3">
                        <div>
                            <span class="text-uppercase fw-semibold text-body-secondary small d-block mb-1">Vagas Abertas</span>
                            <h3 class="mb-0 fw-bold text-success"><?= $total_ativas; ?></h3>
                            <span class="text-body-secondary small">Recebendo candidaturas</span>
                        </div>
                        <div class="kpi-icon-circle bg-success-subtle text-success">
                            <i class="bi bi-check-circle fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Vagas Pausadas / Encerradas -->
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card border-0 shadow-sm rounded-3 h-100 kpi-card">
                    <div class="card-body d-flex align-items-center justify-content-between p-3">
                        <div>
                            <span class="text-uppercase fw-semibold text-body-secondary small d-block mb-1">Vagas Pausadas</span>
                            <h3 class="mb-0 fw-bold text-warning"><?= $total_inativas; ?></h3>
                            <span class="text-body-secondary small">Processos finalizados</span>
                        </div>
                        <div class="kpi-icon-circle bg-warning-subtle text-warning">
                            <i class="bi bi-pause-circle fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Unidades com Vagas -->
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card border-0 shadow-sm rounded-3 h-100 kpi-card">
                    <div class="card-body d-flex align-items-center justify-content-between p-3">
                        <div>
                            <span class="text-uppercase fw-semibold text-body-secondary small d-block mb-1">Locais de Trabalho</span>
                            <h3 class="mb-0 fw-bold text-info"><?= max(1, $total_locais); ?></h3>
                            <span class="text-body-secondary small">Postos e unidades</span>
                        </div>
                        <div class="kpi-icon-circle bg-info-subtle text-info">
                            <i class="bi bi-geo-alt fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Formulário de Criação -->
        <form action="" method="post" enctype="multipart/form-data" id="formVagasAdd" novalidate>
            <input type="hidden" name="form1" value="1">

            <div class="row g-4">
                <!-- Coluna Principal (8 colunas) -->
                <div class="col-12 col-xl-8">
                    <div class="card border-0 shadow-sm rounded-3 mb-4">
                        <div class="card-header bg-body py-3 border-bottom d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center gap-2">
                                <i class="bi bi-card-checklist text-primary fs-5"></i>
                                <h5 class="card-title mb-0 fw-bold text-body">Especificações da Oportunidade</h5>
                            </div>
                            <span class="text-body-secondary small">
                                <i class="bi bi-info-circle me-1"></i>Campos com (<span class="text-danger">*</span>) são obrigatórios
                            </span>
                        </div>

                        <div class="card-body p-4">
                            <!-- Cargo / Título da Vaga -->
                            <div class="mb-4">
                                <label for="titulo" class="form-label fw-bold text-body d-flex align-items-center justify-content-between">
                                    <span>
                                        <i class="bi bi-person-workspace text-primary me-1"></i> Cargo / Título da Vaga <span class="text-danger">*</span>
                                    </span>
                                    <small class="text-body-secondary fw-normal" id="charCounterTitle">0/150 caracteres</small>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text bg-body-tertiary text-body-secondary border">
                                        <i class="bi bi-briefcase"></i>
                                    </span>
                                    <input type="text" 
                                           class="form-control form-control-lg bg-body text-body border" 
                                           id="titulo" 
                                           name="titulo" 
                                           maxlength="150" 
                                           required 
                                           placeholder="Ex: Recepcionista Hospitalar, Auxiliar de Enfermagem, Técnico de Radiologia...">
                                </div>
                                <div class="form-text text-body-secondary small mt-1">
                                    <i class="bi bi-info-circle me-1"></i> O título da função é exibido em destaque no card de Trabalhe Conosco do site.
                                </div>
                            </div>

                            <!-- Unidade e Horário -->
                            <div class="row g-3 mb-4">
                                <!-- Local de Trabalho -->
                                <div class="col-12 col-md-6">
                                    <label for="local" class="form-label fw-bold text-body">
                                        <i class="bi bi-geo-alt text-primary me-1"></i> Unidade / Local de Trabalho <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-body-tertiary text-body-secondary border">
                                            <i class="bi bi-hospital"></i>
                                        </span>
                                        <input type="text" 
                                               class="form-control bg-body text-body border" 
                                               id="local" 
                                               name="local" 
                                               required 
                                               list="locaisSugeridos" 
                                               placeholder="Ex: Pronto Saúde - Gardênia Azul">
                                    </div>
                                    <datalist id="locaisSugeridos">
                                        <?php foreach ($unidades_sugestoes as $unidade): ?>
                                            <option value="<?= htmlspecialchars($unidade, ENT_QUOTES, 'UTF-8'); ?>">
                                        <?php endforeach; ?>
                                        <option value="Pronto Saúde - Gardênia Azul">
                                        <option value="Pronto Saúde - Anil">
                                        <option value="Pronto Atendimento Central">
                                    </datalist>
                                    <div class="form-text text-body-secondary small">Unidade ou filial onde o profissional atuará</div>
                                </div>

                                <!-- Carga Horária -->
                                <div class="col-12 col-md-6">
                                    <label for="carga" class="form-label fw-bold text-body">
                                        <i class="bi bi-clock-history text-primary me-1"></i> Carga Horária <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-body-tertiary text-body-secondary border">
                                            <i class="bi bi-clock"></i>
                                        </span>
                                        <input type="text" 
                                               class="form-control bg-body text-body border" 
                                               id="carga" 
                                               name="carga" 
                                               required 
                                               placeholder="Ex: 44 horas semanais, 180 horas / mês, Escala 12x36...">
                                    </div>
                                    <div class="form-text text-body-secondary small">Jornada mensal ou escala de trabalho</div>
                                </div>
                            </div>

                            <!-- Salário e Turno/Horário de Trabalho -->
                            <div class="row g-3 mb-4">
                                <!-- Remuneração / Salário -->
                                <div class="col-12 col-md-6">
                                    <label for="salario" class="form-label fw-bold text-body">
                                        <i class="bi bi-cash-stack text-success me-1"></i> Remuneração / Salário <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-body-tertiary text-body-secondary border">
                                            <i class="bi bi-currency-dollar"></i>
                                        </span>
                                        <input type="text" 
                                               class="form-control bg-body text-body border" 
                                               id="salario" 
                                               name="salario" 
                                               required 
                                               placeholder="Ex: R$ 2.200,00, Piso da Categoria, A Combinar...">
                                    </div>
                                    <div class="form-text text-body-secondary small">Valor mensal ou faixa salarial</div>
                                </div>

                                <!-- Horário / Turno -->
                                <div class="col-12 col-md-6">
                                    <label for="horario" class="form-label fw-bold text-body">
                                        <i class="bi bi-sun text-warning me-1"></i> Horário / Turno
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-body-tertiary text-body-secondary border">
                                            <i class="bi bi-calendar-range"></i>
                                        </span>
                                        <input type="text" 
                                               class="form-control bg-body text-body border" 
                                               id="horario" 
                                               name="horario" 
                                               placeholder="Ex: 08:00 às 17:00 (Segunda a Sexta), Plantão Diurno...">
                                    </div>
                                    <div class="form-text text-body-secondary small">Escala diária ou turnos de atendimento</div>
                                </div>
                            </div>

                            <!-- Requisitos e Qualificações (Summernote) -->
                            <div class="mb-4">
                                <label for="requisitos" class="form-label fw-bold text-body">
                                    <i class="bi bi-check2-square text-primary me-1"></i> Requisitos e Qualificações <span class="text-danger">*</span>
                                </label>
                                <textarea class="form-control" 
                                          id="requisitos" 
                                          name="requisitos" 
                                          rows="6" 
                                          placeholder="Ex: Ensino médio completo ou superior em andamento; Experiência prévia em recepção hospitalar ou atendimento clínico; Conhecimento de sistemas de prontuário e guias TISS..."></textarea>
                                <div class="form-text text-body-secondary small mt-1">
                                    <i class="bi bi-list-ul me-1"></i> Utilize marcadores para separar as competências exigidas e desejáveis.
                                </div>
                            </div>

                            <!-- Benefícios Oferecidos -->
                            <div class="mb-4">
                                <label for="beneficios" class="form-label fw-bold text-body">
                                    <i class="bi bi-gift text-primary me-1"></i> Benefícios Oferecidos
                                </label>
                                <textarea class="form-control no-summernote bg-body text-body border" 
                                          data-no-summernote="true" 
                                          id="beneficios" 
                                          name="beneficios" 
                                          rows="3" 
                                          placeholder="Ex: Vale Transporte + Vale Refeição (R$ 30/dia) + Plano Odontológico + Descontos em Consultas e Exames na Clínica..."></textarea>
                                <div class="form-text text-body-secondary small mt-1">
                                    Descreva os benefícios, adicionais e diferenciais da vaga.
                                </div>
                            </div>

                            <!-- Atividades e Detalhes da Vaga -->
                            <div class="mb-2">
                                <label for="detalhes" class="form-label fw-bold text-body">
                                    <i class="bi bi-file-text text-primary me-1"></i> Descrição das Atividades / Detalhes
                                </label>
                                <textarea class="form-control" 
                                          id="detalhes" 
                                          name="detalhes" 
                                          rows="6" 
                                          placeholder="Descreva as principais atribuições e rotinas do dia a dia da função..."></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Coluna Lateral (4 colunas) -->
                <div class="col-12 col-xl-4">
                    <!-- Card de Publicação & Status -->
                    <div class="card border-0 shadow-sm rounded-3 mb-4">
                        <div class="card-header bg-body py-3 border-bottom">
                            <h6 class="card-title mb-0 fw-bold text-body d-flex align-items-center gap-2">
                                <i class="bi bi-send-check text-primary"></i> Publicação & Status
                            </h6>
                        </div>

                        <div class="card-body p-3">
                            <!-- Switch Estilizado de Status -->
                            <div class="p-3 rounded-3 bg-body-tertiary border mb-3">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div>
                                        <label for="statusSwitch" class="fw-bold text-body mb-0 d-block cursor-pointer">Status da Vaga</label>
                                        <small class="text-body-secondary" id="statusLabelDesc">
                                            Aberta (recebendo currículos)
                                        </small>
                                    </div>
                                    <div class="form-check form-switch fs-4 mb-0">
                                        <input class="form-check-control form-check-input" 
                                               type="checkbox" 
                                               role="switch" 
                                               id="statusSwitch" 
                                               checked>
                                        <input type="hidden" name="status" id="statusInput" value="1">
                                    </div>
                                </div>
                            </div>

                            <!-- Resumo das Especificações -->
                            <div class="d-flex flex-column gap-2 small text-body-secondary mb-3 p-2">
                                <div class="d-flex justify-content-between border-bottom pb-1">
                                    <span>Canal:</span>
                                    <span class="fw-bold text-body">Trabalhe Conosco</span>
                                </div>
                                <div class="d-flex justify-content-between border-bottom pb-1">
                                    <span>Divulgação:</span>
                                    <span class="fw-semibold text-success">Página Pública</span>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span>Candidaturas:</span>
                                    <span class="fw-semibold text-body">Envio Online de CV</span>
                                </div>
                            </div>

                            <!-- Botões de Ação -->
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary rounded-pill py-2 shadow-sm fw-semibold" id="btnSalvarVaga">
                                    <i class="bi bi-check-lg me-1"></i> Publicar Oportunidade
                                </button>
                                <a href="trabalhe-conosco.php" class="btn btn-outline-secondary rounded-pill py-2">
                                    Cancelar
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Card da Foto em Destaque da Vaga -->
                    <div class="card border-0 shadow-sm rounded-3 mb-4">
                        <div class="card-header bg-body py-3 border-bottom d-flex align-items-center justify-content-between">
                            <h6 class="card-title mb-0 fw-bold text-body d-flex align-items-center gap-2">
                                <i class="bi bi-image text-primary"></i> Imagem de Destaque
                            </h6>
                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill small">
                                16:9 / Capa
                            </span>
                        </div>

                        <div class="card-body p-3">
                            <!-- Container de Preview de Imagem -->
                            <div class="position-relative mb-3 rounded-3 overflow-hidden border bg-body-tertiary d-flex align-items-center justify-content-center" 
                                 id="photoPreviewWrapper" 
                                 style="aspect-ratio: 16/9; min-height: 160px;">
                                <div id="photoPlaceholder" class="text-center p-3 text-body-secondary">
                                    <i class="bi bi-card-image fs-1 d-block mb-2 text-body-tertiary"></i>
                                    <span class="small d-block fw-semibold">Sem foto selecionada</span>
                                    <span class="small text-body-secondary" style="font-size: 11px;">Opcional: Exibida no card da vaga</span>
                                </div>
                                <img src="" alt="Prévia da imagem" id="photoPreviewImg" class="w-100 h-100 object-fit-cover d-none">
                            </div>

                            <!-- Input de Upload -->
                            <div class="mb-2">
                                <input type="file" 
                                       class="form-control bg-body text-body border" 
                                       id="photo" 
                                       name="photo" 
                                       accept="image/*">
                            </div>

                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <button type="button" class="btn btn-outline-danger btn-sm rounded-pill px-3 d-none" id="btnRemoverFoto">
                                    <i class="bi bi-trash3 me-1"></i> Remover
                                </button>
                                <span class="text-body-secondary small ms-auto" style="font-size: 11px;">
                                    Formatos: WEBP, JPG, PNG
                                </span>
                            </div>

                            <div class="alert alert-info border-0 p-2 px-3 rounded-3 mb-0 small text-body-secondary" style="font-size: 12px;">
                                <i class="bi bi-lightbulb-fill text-info me-1"></i>
                                Imagens atraentes com profissionais ou instalações aumentam o engajamento e número de currículos qualificados.
                            </div>
                        </div>
                    </div>

                    <!-- Card de Orientações para Recrutamento -->
                    <div class="card border-0 shadow-sm rounded-3 mb-4">
                        <div class="card-header bg-body py-3 border-bottom">
                            <h6 class="card-title mb-0 fw-bold text-body d-flex align-items-center gap-2">
                                <i class="bi bi-shield-check text-success"></i> Boas Práticas
                            </h6>
                        </div>
                        <div class="card-body p-3 small text-body-secondary">
                            <ul class="list-unstyled mb-0 d-flex flex-column gap-2">
                                <li class="d-flex align-items-start gap-2">
                                    <i class="bi bi-check2-circle text-primary mt-1"></i>
                                    <div><strong>Clareza no Título:</strong> Especifique a função exata para facilitar a busca do candidato.</div>
                                </li>
                                <li class="d-flex align-items-start gap-2">
                                    <i class="bi bi-check2-circle text-primary mt-1"></i>
                                    <div><strong>Benefícios Transparentes:</strong> Destaque os diferenciais de trabalhar na Pronto Saúde.</div>
                                </li>
                                <li class="d-flex align-items-start gap-2">
                                    <i class="bi bi-check2-circle text-primary mt-1"></i>
                                    <div><strong>Gestão em Tempo Real:</strong> Quando a vaga for preenchida, altere o status para pausada.</div>
                                </li>
                            </ul>
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
    // 1. Contador de Caracteres do Título da Vaga
    const inputTitulo   = document.getElementById('titulo');
    const counterTitulo = document.getElementById('charCounterTitle');

    if (inputTitulo && counterTitulo) {
        inputTitulo.addEventListener('input', function () {
            const len = this.value.length;
            counterTitulo.textContent = `${len}/150 caracteres`;
            if (len > 120) {
                counterTitulo.className = 'text-warning small fw-bold';
            } else {
                counterTitulo.className = 'text-body-secondary small fw-normal';
            }
        });
    }

    // 2. Switch de Status da Vaga (Aberta / Pausada)
    const statusSwitch = document.getElementById('statusSwitch');
    const statusInput  = document.getElementById('statusInput');
    const statusDesc   = document.getElementById('statusLabelDesc');

    if (statusSwitch && statusInput) {
        statusSwitch.addEventListener('change', function () {
            if (this.checked) {
                statusInput.value = '1';
                if (statusDesc) statusDesc.textContent = 'Aberta (recebendo currículos)';
            } else {
                statusInput.value = '0';
                if (statusDesc) statusDesc.textContent = 'Pausada / Encerrada (oculta)';
            }
        });
    }

    // 3. Pré-visualização Instantânea da Foto da Vaga
    const inputPhoto       = document.getElementById('photo');
    const photoPreviewImg  = document.getElementById('photoPreviewImg');
    const photoPlaceholder = document.getElementById('photoPlaceholder');
    const btnRemoverFoto   = document.getElementById('btnRemoverFoto');

    if (inputPhoto) {
        inputPhoto.addEventListener('change', function () {
            if (this.files && this.files[0]) {
                const file = this.files[0];
                const reader = new FileReader();

                reader.onload = function (e) {
                    photoPreviewImg.src = e.target.result;
                    photoPreviewImg.classList.remove('d-none');
                    photoPlaceholder.classList.add('d-none');
                    if (btnRemoverFoto) btnRemoverFoto.classList.remove('d-none');
                };

                reader.readAsDataURL(file);
            }
        });
    }

    if (btnRemoverFoto) {
        btnRemoverFoto.addEventListener('click', function () {
            if (inputPhoto) inputPhoto.value = '';
            photoPreviewImg.src = '';
            photoPreviewImg.classList.add('d-none');
            photoPlaceholder.classList.remove('d-none');
            this.classList.add('d-none');
        });
    }

    // 4. Envio Seguro via AJAX com SweetAlert2
    const formVaga = document.getElementById('formVagasAdd');
    const btnSalvar = document.getElementById('btnSalvarVaga');

    if (formVaga) {
        formVaga.addEventListener('submit', function (e) {
            e.preventDefault();

            // Sincroniza Summernote se ativo
            if (window.jQuery && jQuery.fn.summernote) {
                jQuery('#requisitos').val(jQuery('#requisitos').summernote('code'));
                jQuery('#detalhes').val(jQuery('#detalhes').summernote('code'));
            }

            // Validações básicas no frontend
            const tituloVal = (inputTitulo ? inputTitulo.value.trim() : '');
            const localVal  = document.getElementById('local').value.trim();
            const reqVal    = document.getElementById('requisitos').value.trim();

            if (!tituloVal) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Cargo Obrigatório',
                        text: 'Informe o cargo ou título da oportunidade de emprego.'
                    });
                }
                if (inputTitulo) inputTitulo.focus();
                return;
            }

            if (!localVal) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Local Obrigatório',
                        text: 'Informe a unidade ou local de trabalho da vaga.'
                    });
                }
                document.getElementById('local').focus();
                return;
            }

            if (!reqVal || reqVal === '<p><br></p>') {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Requisitos Obrigatórios',
                        text: 'Informe as qualificações e requisitos necessários para o cargo.'
                    });
                }
                return;
            }

            // Prepara o FormData
            const formData = new FormData(formVaga);
            formData.append('action', 'add_vaga_ajax');

            const btnOriginalHtml = btnSalvar.innerHTML;
            btnSalvar.disabled = true;
            btnSalvar.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Publicando vaga...';

            fetch('vagas-add.php', {
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
                            title: 'Oportunidade Publicada!',
                            text: data.message || 'A vaga foi cadastrada com sucesso no portal.',
                            timer: 1800,
                            showConfirmButton: false
                        }).then(() => {
                            window.location.href = 'trabalhe-conosco.php';
                        });
                    } else {
                        window.location.href = 'trabalhe-conosco.php';
                    }
                } else {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'error',
                            title: 'Erro ao Publicar',
                            html: data.message || 'Não foi possível cadastrar a vaga.'
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
                        title: 'Falha de Conexão',
                        text: 'Ocorreu um erro ao enviar os dados para o servidor. Tente novamente.'
                    });
                }
            });
        });
    }

    // 5. Tooltips com Auto-Hide
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
