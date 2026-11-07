<?php
require_once('header.php');

// 1. Carrega dados de apoio para os seletores
$stmt_medicos = $pdo->prepare("SELECT id, name, registro, designation_id, clinica, photo, status FROM tbl_doctor ORDER BY name ASC");
$stmt_medicos->execute();
$medicos = $stmt_medicos->fetchAll(PDO::FETCH_ASSOC);

$stmt_deps = $pdo->prepare("SELECT dep_id, dep_name FROM tbl_department ORDER BY dep_name ASC");
$stmt_deps->execute();
$clinicas = $stmt_deps->fetchAll(PDO::FETCH_ASSOC);

$stmt_dias = $pdo->prepare("SELECT oh_id, oh_day FROM tbl_department_openning_hour ORDER BY oh_id ASC");
$stmt_dias->execute();
$dias_semana = $stmt_dias->fetchAll(PDO::FETCH_ASSOC);

$stmt_desig = $pdo->prepare("SELECT designation_id, designation_name FROM tbl_designation ORDER BY designation_name ASC");
$stmt_desig->execute();
$especialidades = $stmt_desig->fetchAll(PDO::FETCH_ASSOC);

// Métricas de apoio para os KPIs do topo
$total_escalas        = (int)$pdo->query("SELECT COUNT(*) FROM tbl_especialidade")->fetchColumn();
$total_medicos        = count($medicos);
$total_especialidades = count($especialidades);
$total_clinicas       = count($clinicas);

// 2. Processamento do Envio (Suporta AJAX e POST tradicional)
$is_ajax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
           || (isset($_POST['action']) && $_POST['action'] === 'add_extra_ajax');

$error_message = '';
$success_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && (isset($_POST['form1']) || $is_ajax)) {
    $medico_id  = (int)($_POST['medico'] ?? 0);
    $clinica_id = (int)($_POST['clinica'] ?? 0);
    $semana_id  = (int)($_POST['semana'] ?? 0);
    $funcao_id  = (int)($_POST['especialidade'] ?? 0);
    $inicio     = trim($_POST['inicio'] ?? '');
    $fim        = trim($_POST['fim'] ?? '');

    $valid = 1;
    $erros = [];

    if ($medico_id <= 0) {
        $valid = 0;
        $erros[] = 'Selecione o médico ou especialista do atendimento.';
    }

    if ($clinica_id <= 0) {
        $valid = 0;
        $erros[] = 'Selecione a clínica ou unidade onde o atendimento ocorrerá.';
    }

    if ($semana_id <= 0) {
        $valid = 0;
        $erros[] = 'Selecione o dia da semana do atendimento.';
    }

    if ($funcao_id <= 0) {
        $valid = 0;
        $erros[] = 'Selecione a especialidade/função exercida nesse horário.';
    }

    if (empty($inicio)) {
        $valid = 0;
        $erros[] = 'Informe o horário de início do atendimento.';
    }

    if (empty($fim)) {
        $valid = 0;
        $erros[] = 'Informe o horário de término do atendimento.';
    }

    if (!empty($inicio) && !empty($fim) && strtotime($inicio) >= strtotime($fim)) {
        $valid = 0;
        $erros[] = 'O horário de término deve ser posterior ao horário de início.';
    }

    // Verifica se já existe exatamente essa mesma escala para o médico
    if ($valid === 1) {
        $stmt_check = $pdo->prepare("SELECT ide FROM tbl_especialidade WHERE medico = ? AND semana = ? AND clinica = ? AND inicio = ? AND fim = ?");
        $stmt_check->execute([$medico_id, $semana_id, $clinica_id, $inicio, $fim]);
        if ($stmt_check->rowCount() > 0) {
            $valid = 0;
            $erros[] = 'Já existe um plantão cadastrado exatamente neste mesmo dia e horário para este médico.';
        }
    }

    if ($valid === 1) {
        try {
            $stmt_ins = $pdo->prepare("INSERT INTO tbl_especialidade (medico, clinica, semana, funcao, inicio, fim) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt_ins->execute([$medico_id, $clinica_id, $semana_id, $funcao_id, $inicio, $fim]);
            $novo_id = (int)$pdo->lastInsertId();

            $msg_sucesso = 'Escala de atendimento atribuída com sucesso!';

            if ($is_ajax) {
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode([
                    'status'   => 'success',
                    'message'  => $msg_sucesso,
                    'extra_id' => $novo_id
                ]);
                exit;
            }

            $success_message = $msg_sucesso;
        } catch (Exception $e) {
            $msg_erro = 'Erro ao salvar no banco de dados: ' . $e->getMessage();
            if ($is_ajax) {
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(['status' => 'error', 'message' => $msg_erro]);
                exit;
            }
            $error_message = $msg_erro;
        }
    } else {
        $msg_erro = implode('<br>', $erros);
        if ($is_ajax) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['status' => 'error', 'message' => $msg_erro]);
            exit;
        }
        $error_message = $msg_erro;
    }
}
?>

<style>
/* Estilos Harmonizados para Adicionar Escala */
.kpi-card {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.kpi-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.08) !important;
}
.kpi-icon-circle {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}
.preview-doctor-avatar {
    width: 64px;
    height: 64px;
    border-radius: 50%;
    object-fit: cover;
}
</style>

<!-- Cabeçalho da Página -->
<div class="app-content-header pb-2 mb-3 border-bottom">
    <div class="container-fluid">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div>
                <h3 class="mb-1 fw-bold text-body">
                    <i class="bi bi-clock-history text-primary me-2"></i>Atribuir Horário &amp; Escala de Atendimento
                </h3>
                <ol class="breadcrumb mb-0 small">
                    <li class="breadcrumb-item"><a href="index.php" class="text-decoration-none"><i class="bi bi-house-door"></i> Início</a></li>
                    <li class="breadcrumb-item"><a href="extra.php" class="text-decoration-none">Horários &amp; Funções</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Nova Escala</li>
                </ol>
            </div>
            <div class="d-flex flex-wrap align-items-center gap-2">
                <a href="../page.php?slug=medicos" target="_blank" rel="noopener" class="btn btn-outline-info btn-sm rounded-pill px-3 shadow-sm" data-bs-toggle="tooltip" title="Visualizar página de médicos no portal público">
                    <i class="bi bi-box-arrow-up-right me-1"></i> Ver no Site
                </a>
                <a href="doctor.php" class="btn btn-outline-secondary btn-sm rounded-pill px-3 shadow-sm" data-bs-toggle="tooltip" title="Ver listagem dos médicos">
                    <i class="bi bi-person-badge me-1"></i> Médicos
                </a>
                <a href="extra.php" class="btn btn-outline-primary btn-sm rounded-pill px-3 shadow-sm" data-bs-toggle="tooltip" title="Ver tabela completa de escalas">
                    <i class="bi bi-list-ul me-1"></i> Ver Escalas
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

        <!-- Mini Cards de Indicadores (KPIs) -->
        <div class="row g-3 mb-4">
            <!-- Total de Escalas -->
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card border-0 shadow-sm rounded-3 h-100 kpi-card">
                    <div class="card-body d-flex align-items-center justify-content-between p-3">
                        <div>
                            <span class="text-uppercase fw-semibold text-body-secondary small d-block mb-1">Escalas Ativas</span>
                            <h3 class="mb-0 fw-bold text-body"><?= $total_escalas; ?></h3>
                            <span class="text-body-secondary small">Turnos cadastrados</span>
                        </div>
                        <div class="kpi-icon-circle bg-primary-subtle text-primary">
                            <i class="bi bi-calendar2-week fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Médicos no Sistema -->
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card border-0 shadow-sm rounded-3 h-100 kpi-card">
                    <div class="card-body d-flex align-items-center justify-content-between p-3">
                        <div>
                            <span class="text-uppercase fw-semibold text-body-secondary small d-block mb-1">Corpo Clínico</span>
                            <h3 class="mb-0 fw-bold text-success"><?= $total_medicos; ?></h3>
                            <span class="text-body-secondary small">Médicos no acervo</span>
                        </div>
                        <div class="kpi-icon-circle bg-success-subtle text-success">
                            <i class="bi bi-people fs-4"></i>
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
                            <span class="text-body-secondary small">Áreas disponíveis</span>
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
                            <span class="text-body-secondary small">Locais de atendimento</span>
                        </div>
                        <div class="kpi-icon-circle bg-warning-subtle text-warning">
                            <i class="bi bi-hospital fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Formulário de Criação de Escala -->
        <form action="" method="post" id="formExtraAdd" novalidate>
            <input type="hidden" name="form1" value="1">

            <div class="row g-4">
                <!-- Coluna Principal do Formulário (8 colunas) -->
                <div class="col-12 col-xl-8">
                    <div class="card border-0 shadow-sm rounded-3 mb-4">
                        <div class="card-header bg-body py-3 border-bottom">
                            <h6 class="card-title mb-0 fw-bold text-body d-flex align-items-center gap-2">
                                <i class="bi bi-calendar-plus text-primary"></i> Informações do Plantão / Escala
                            </h6>
                        </div>

                        <div class="card-body p-4">
                            <!-- Seleção do Médico -->
                            <div class="mb-4">
                                <label for="selectMedico" class="form-label fw-bold text-body">
                                    <i class="bi bi-person text-primary me-1"></i> Selecione o Médico / Especialista <span class="text-danger">*</span>
                                </label>
                                <select class="form-select bg-body text-body border select2" id="selectMedico" name="medico" required>
                                    <option value="" disabled selected>Selecione um médico do corpo clínico...</option>
                                    <?php foreach ($medicos as $m): 
                                        $foto = (!empty($m['photo']) && file_exists(__DIR__ . '/../assets/uploads/' . $m['photo']))
                                            ? '../assets/uploads/' . $m['photo']
                                            : '../assets/images/sem-foto.gif';
                                    ?>
                                        <option value="<?= $m['id']; ?>" 
                                                data-registro="<?= htmlspecialchars($m['registro'] ?? 'CRM', ENT_QUOTES, 'UTF-8'); ?>"
                                                data-desig="<?= (int)($m['designation_id'] ?? 0); ?>"
                                                data-clinica="<?= (int)($m['clinica'] ?? 0); ?>"
                                                data-photo="<?= $foto; ?>"
                                                data-name="<?= htmlspecialchars($m['name'], ENT_QUOTES, 'UTF-8'); ?>">
                                            <?= htmlspecialchars($m['name'], ENT_QUOTES, 'UTF-8'); ?> <?= !empty($m['registro']) ? '— ' . htmlspecialchars($m['registro'], ENT_QUOTES, 'UTF-8') : ''; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="form-text small text-body-secondary mt-1">
                                    Ao selecionar o médico, a especialidade e unidade padrão serão sugeridas automaticamente.
                                </div>
                            </div>

                            <!-- Clínica e Especialidade em 2 Colunas -->
                            <div class="row g-3 mb-4">
                                <div class="col-12 col-md-6">
                                    <label for="selectClinica" class="form-label fw-bold text-body">
                                        <i class="bi bi-hospital text-primary me-1"></i> Clínica / Unidade de Atendimento <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select bg-body text-body border select2" id="selectClinica" name="clinica" required>
                                        <option value="" disabled selected>Selecione a Clínica / Unidade...</option>
                                        <?php foreach ($clinicas as $c): ?>
                                            <option value="<?= $c['dep_id']; ?>">
                                                <?= htmlspecialchars($c['dep_name'], ENT_QUOTES, 'UTF-8'); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="col-12 col-md-6">
                                    <label for="selectEspecialidade" class="form-label fw-bold text-body">
                                        <i class="bi bi-award text-primary me-1"></i> Especialidade / Função no Turno <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select bg-body text-body border select2" id="selectEspecialidade" name="especialidade" required>
                                        <option value="" disabled selected>Selecione a Especialidade...</option>
                                        <?php foreach ($especialidades as $e): ?>
                                            <option value="<?= $e['designation_id']; ?>">
                                                <?= htmlspecialchars($e['designation_name'], ENT_QUOTES, 'UTF-8'); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <!-- Dia da Semana e Turno de Horário -->
                            <div class="row g-3 mb-4">
                                <div class="col-12 col-md-4">
                                    <label for="selectSemana" class="form-label fw-bold text-body">
                                        <i class="bi bi-calendar-event text-primary me-1"></i> Dia da Semana <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select bg-body text-body border select2" id="selectSemana" name="semana" required>
                                        <option value="" disabled selected>Selecione o Dia...</option>
                                        <?php foreach ($dias_semana as $ds): ?>
                                            <option value="<?= $ds['oh_id']; ?>">
                                                <?= htmlspecialchars($ds['oh_day'], ENT_QUOTES, 'UTF-8'); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="col-12 col-sm-6 col-md-4">
                                    <label for="inputInicio" class="form-label fw-bold text-body">
                                        <i class="bi bi-clock text-success me-1"></i> Início de Atendimento <span class="text-danger">*</span>
                                    </label>
                                    <input type="time" 
                                           class="form-control bg-body text-body border form-control-lg" 
                                           id="inputInicio" 
                                           name="inicio" 
                                           required 
                                           value="08:00">
                                </div>

                                <div class="col-12 col-sm-6 col-md-4">
                                    <label for="inputFim" class="form-label fw-bold text-body">
                                        <i class="bi bi-clock-history text-danger me-1"></i> Término de Atendimento <span class="text-danger">*</span>
                                    </label>
                                    <input type="time" 
                                           class="form-control bg-body text-body border form-control-lg" 
                                           id="inputFim" 
                                           name="fim" 
                                           required 
                                           value="12:00">
                                </div>
                            </div>

                            <!-- Caixa Informativa de Boas Práticas -->
                            <div class="p-3 rounded-3 bg-body-tertiary border d-flex align-items-start gap-2">
                                <i class="bi bi-info-circle-fill text-primary fs-5 mt-1 flex-shrink-0"></i>
                                <div class="small text-body-secondary">
                                    <strong>Horários no Site Público:</strong> Estes horários são sincronizados com a página do médico e com a página de clínicas/departamentos, orientando os pacientes e a recepção sobre os dias exatos de atendimento.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Coluna Lateral: Prévia da Escala em Tempo Real (4 colunas) -->
                <div class="col-12 col-xl-4">
                    <!-- Live Preview Card da Escala -->
                    <div class="card border-0 shadow-sm rounded-3 mb-4 position-sticky" style="top: 20px;">
                        <div class="card-header bg-body py-3 border-bottom d-flex align-items-center justify-content-between">
                            <h6 class="card-title mb-0 fw-bold text-body d-flex align-items-center gap-2">
                                <i class="bi bi-eye text-primary"></i> Prévia da Escala
                            </h6>
                            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill small">
                                <i class="bi bi-lightning-charge"></i> Tempo Real
                            </span>
                        </div>

                        <div class="card-body p-3">
                            <!-- Card Estilo Crachá de Plantão -->
                            <div class="p-3 rounded-3 border bg-body-tertiary text-center mb-3">
                                <img src="../assets/images/sem-foto.gif" alt="Médico" id="previewFoto" class="preview-doctor-avatar border shadow-xs mb-2">
                                <h6 class="fw-bold text-body mb-0" id="previewNome">Selecione o Médico</h6>
                                <span class="badge bg-body text-body-secondary border font-monospace mb-2" id="previewRegistro" style="font-size: 11px;">CRM / Registro</span>

                                <hr class="my-2 border-secondary-subtle">

                                <div class="d-flex flex-column gap-1 text-start small">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <span class="text-body-secondary">Especialidade:</span>
                                        <span class="fw-bold text-primary" id="previewEspecialidade">A Definir</span>
                                    </div>
                                    <div class="d-flex align-items-center justify-content-between">
                                        <span class="text-body-secondary">Unidade:</span>
                                        <span class="fw-semibold text-body" id="previewClinica">A Definir</span>
                                    </div>
                                    <div class="d-flex align-items-center justify-content-between">
                                        <span class="text-body-secondary">Dia da Semana:</span>
                                        <span class="badge bg-body-secondary text-body border rounded-pill" id="previewDia">A Definir</span>
                                    </div>
                                    <div class="d-flex align-items-center justify-content-between">
                                        <span class="text-body-secondary">Horário:</span>
                                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill fw-bold" id="previewHorario">08:00 às 12:00</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Botões de Ação -->
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary rounded-pill py-2 shadow-sm fw-semibold" id="btnSalvarEscala">
                                    <i class="bi bi-check-lg me-1"></i> Salvar Escala de Atendimento
                                </button>
                                <a href="extra.php" class="btn btn-outline-secondary rounded-pill py-2">
                                    Cancelar / Voltar
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>

    </div>
</div>

<!-- ==============================================================================
     SCRIPTS JAVASCRIPT: Live Preview & Envio AJAX com SweetAlert2
     ============================================================================== -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    const selectMed = document.getElementById('selectMedico');
    const selectCli = document.getElementById('selectClinica');
    const selectEsp = document.getElementById('selectEspecialidade');
    const selectSem = document.getElementById('selectSemana');
    const inputIni  = document.getElementById('inputInicio');
    const inputFim  = document.getElementById('inputFim');

    // Elementos da Prévia
    const prevFoto   = document.getElementById('previewFoto');
    const prevNome   = document.getElementById('previewNome');
    const prevReg    = document.getElementById('previewRegistro');
    const prevEsp    = document.getElementById('previewEspecialidade');
    const prevCli    = document.getElementById('previewClinica');
    const prevDia    = document.getElementById('previewDia');
    const prevHor    = document.getElementById('previewHorario');

    function atualizarPreview() {
        // 1. Médico
        if (selectMed && selectMed.selectedIndex > 0) {
            const opt = selectMed.options[selectMed.selectedIndex];
            if (prevNome) prevNome.textContent = opt.getAttribute('data-name') || opt.text;
            if (prevReg) prevReg.textContent = opt.getAttribute('data-registro') || 'CRM';
            if (prevFoto) prevFoto.src = opt.getAttribute('data-photo') || '../assets/images/sem-foto.gif';
        } else {
            if (prevNome) prevNome.textContent = 'Selecione o Médico';
            if (prevReg) prevReg.textContent = 'CRM / Registro';
            if (prevFoto) prevFoto.src = '../assets/images/sem-foto.gif';
        }

        // 2. Especialidade
        if (selectEsp && selectEsp.selectedIndex > 0) {
            if (prevEsp) prevEsp.textContent = selectEsp.options[selectEsp.selectedIndex].text;
        } else {
            if (prevEsp) prevEsp.textContent = 'A Definir';
        }

        // 3. Clínica
        if (selectCli && selectCli.selectedIndex > 0) {
            if (prevCli) prevCli.textContent = selectCli.options[selectCli.selectedIndex].text;
        } else {
            if (prevCli) prevCli.textContent = 'A Definir';
        }

        // 4. Dia da Semana
        if (selectSem && selectSem.selectedIndex > 0) {
            if (prevDia) prevDia.textContent = selectSem.options[selectSem.selectedIndex].text;
        } else {
            if (prevDia) prevDia.textContent = 'A Definir';
        }

        // 5. Horário
        const hIni = inputIni ? inputIni.value : '08:00';
        const hFim = inputFim ? inputFim.value : '12:00';
        if (prevHor) prevHor.textContent = `${hIni || '--:--'} às ${hFim || '--:--'}`;
    }

    // Auto-seleciona Especialidade e Clínica sugeridas do médico ao escolher o médico
    if (selectMed) {
        selectMed.addEventListener('change', function () {
            const opt = this.options[this.selectedIndex];
            const desigId = opt.getAttribute('data-desig');
            const clinicaId = opt.getAttribute('data-clinica');

            if (desigId && selectEsp) {
                selectEsp.value = desigId;
                if (window.jQuery && jQuery.fn.select2) {
                    jQuery(selectEsp).trigger('change.select2');
                }
            }

            if (clinicaId && selectCli) {
                selectCli.value = clinicaId;
                if (window.jQuery && jQuery.fn.select2) {
                    jQuery(selectCli).trigger('change.select2');
                }
            }

            atualizarPreview();
        });
    }

    if (selectCli) selectCli.addEventListener('change', atualizarPreview);
    if (selectEsp) selectEsp.addEventListener('change', atualizarPreview);
    if (selectSem) selectSem.addEventListener('change', atualizarPreview);
    if (inputIni)  inputIni.addEventListener('input', atualizarPreview);
    if (inputFim)  inputFim.addEventListener('input', atualizarPreview);

    // Envio Seguro via AJAX com SweetAlert2
    const formExtra = document.getElementById('formExtraAdd');
    const btnSalvar = document.getElementById('btnSalvarEscala');

    if (formExtra) {
        formExtra.addEventListener('submit', function (e) {
            e.preventDefault();

            const medico = selectMed.value;
            const clinica = selectCli.value;
            const especialidade = selectEsp.value;
            const semana = selectSem.value;
            const inicio = inputIni.value.trim();
            const fim = inputFim.value.trim();

            if (!medico) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Médico Obrigatório',
                    text: 'Selecione o médico ou especialista do atendimento.',
                    confirmButtonColor: '#0a6b74'
                });
                return;
            }

            if (!clinica) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Clínica Obrigatória',
                    text: 'Selecione a clínica ou unidade onde o atendimento ocorrerá.',
                    confirmButtonColor: '#0a6b74'
                });
                return;
            }

            if (!especialidade) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Especialidade Obrigatória',
                    text: 'Selecione a especialidade/função exercida nesse turno.',
                    confirmButtonColor: '#0a6b74'
                });
                return;
            }

            if (!semana) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Dia Obrigatório',
                    text: 'Selecione o dia da semana do atendimento.',
                    confirmButtonColor: '#0a6b74'
                });
                return;
            }

            if (!inicio || !fim) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Horário Obrigatório',
                    text: 'Preencha o horário de início e término do atendimento.',
                    confirmButtonColor: '#0a6b74'
                });
                return;
            }

            const btnOriginal = btnSalvar.innerHTML;
            btnSalvar.disabled = true;
            btnSalvar.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Salvando escala...';

            const formData = new FormData(formExtra);
            formData.append('action', 'add_extra_ajax');

            fetch('extra-add.php', {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                btnSalvar.disabled = false;
                btnSalvar.innerHTML = btnOriginal;

                if (data.status === 'success') {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Escala Salva!',
                            text: data.message || 'Horário de atendimento cadastrado com sucesso.',
                            showCancelButton: true,
                            confirmButtonText: '<i class="bi bi-list-ul me-1"></i> Ver Todas as Escalas',
                            cancelButtonText: '<i class="bi bi-plus-lg me-1"></i> Cadastrar Outro Turno',
                            confirmButtonColor: '#0a6b74',
                            cancelButtonColor: '#10b981'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                window.location.href = 'extra.php';
                            } else {
                                // Reseta horários e foca no dia para cadastrar outro turno rápido
                                if (inputIni) inputIni.value = '13:00';
                                if (inputFim) inputFim.value = '17:00';
                                atualizarPreview();
                            }
                        });
                    } else {
                        window.location.href = 'extra.php';
                    }
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Não foi possível salvar',
                        html: data.message || 'Erro ao cadastrar escala de atendimento.',
                        confirmButtonColor: '#0a6b74'
                    });
                }
            })
            .catch(err => {
                console.error(err);
                btnSalvar.disabled = false;
                btnSalvar.innerHTML = btnOriginal;
                Swal.fire({
                    icon: 'error',
                    title: 'Falha de Comunicação',
                    text: 'Ocorreu um erro ao enviar os dados para o servidor. Tente novamente.',
                    confirmButtonColor: '#0a6b74'
                });
            });
        });
    }

    // Inicializa preview
    atualizarPreview();

    // Tooltips com Auto-Hide
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
