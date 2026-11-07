<?php
require_once('header.php');

// ==============================================================================
// ENDPOINT AJAX: Exclusão Segura de Escala / Horário
// ==============================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_extra') {
    header('Content-Type: application/json; charset=utf-8');
    $ide = (int)($_POST['ide'] ?? 0);

    if ($ide > 0) {
        try {
            $stmt_del = $pdo->prepare("DELETE FROM tbl_especialidade WHERE ide = ?");
            $stmt_del->execute([$ide]);

            echo json_encode([
                'status'  => 'success',
                'message' => 'Escala de atendimento removida com sucesso!'
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'status'  => 'error',
                'message' => 'Erro ao remover escala: ' . $e->getMessage()
            ]);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Identificador de escala inválido.']);
    }
    exit;
}

// ==============================================================================
// CONSULTA PRINCIPAL: Escala de Médicos com Clínicas, Dias e Horários
// ==============================================================================
$statement = $pdo->prepare("SELECT 
                                e.ide,
                                e.inicio,
                                e.fim,
                                m.id AS doctor_id,
                                m.name AS doctor_name,
                                m.registro AS doctor_registro,
                                m.photo AS doctor_photo,
                                m.status AS doctor_status,
                                f.designation_id,
                                f.designation_name,
                                d.oh_id,
                                d.oh_day,
                                dep.dep_id,
                                dep.dep_name
                            FROM tbl_especialidade e
                            INNER JOIN tbl_doctor m ON m.id = e.medico
                            LEFT JOIN tbl_designation f ON f.designation_id = e.funcao
                            LEFT JOIN tbl_department_openning_hour d ON d.oh_id = e.semana
                            LEFT JOIN tbl_department dep ON dep.dep_id = e.clinica
                            ORDER BY d.oh_id ASC, e.inicio ASC, m.name ASC");
$statement->execute();
$escalas = $statement->fetchAll(PDO::FETCH_ASSOC);

// Lista de dias da semana para o filtro rápido
$stmt_dias = $pdo->query("SELECT oh_id, oh_day FROM tbl_department_openning_hour ORDER BY oh_id ASC");
$dias_semana = $stmt_dias->fetchAll(PDO::FETCH_ASSOC);

// Métricas de Indicadores (KPIs)
$total_escalas      = count($escalas);
$medicos_unicos     = [];
$clinicas_unicas    = [];
$dias_unicos        = [];

foreach ($escalas as $esc) {
    if (!empty($esc['doctor_id'])) {
        $medicos_unicos[$esc['doctor_id']] = true;
    }
    if (!empty($esc['dep_id'])) {
        $clinicas_unicas[$esc['dep_id']] = true;
    }
    if (!empty($esc['oh_id'])) {
        $dias_unicos[$esc['oh_id']] = true;
    }
}

$total_medicos_escala  = count($medicos_unicos);
$total_clinicas_escala = count($clinicas_unicas);
$total_dias_escala     = count($dias_unicos);
?>

<style>
/* Estilos Customizados e Harmonizados para a Escala de Atendimento */
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
.table-escala tbody tr {
    transition: background-color 0.15s ease-in-out;
}
.table-escala tbody tr:hover {
    background-color: var(--bs-tertiary-bg, rgba(0, 0, 0, 0.02));
}
.doctor-avatar-thumb {
    width: 38px;
    height: 38px;
    border-radius: 50%;
    object-fit: cover;
    flex-shrink: 0;
}
.time-badge {
    font-size: 0.8rem;
    padding: 0.35rem 0.65rem;
    letter-spacing: 0.3px;
    font-variant-numeric: tabular-nums;
}
</style>

<!-- Cabeçalho da Página -->
<div class="app-content-header pb-2 mb-3 border-bottom">
    <div class="container-fluid">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div>
                <h3 class="mb-1 fw-bold text-body">
                    <i class="bi bi-clock-history text-primary me-2"></i>Escala de Atendimento &amp; Horários
                </h3>
                <ol class="breadcrumb mb-0 small">
                    <li class="breadcrumb-item"><a href="index.php" class="text-decoration-none"><i class="bi bi-house-door"></i> Início</a></li>
                    <li class="breadcrumb-item"><a href="doctor.php" class="text-decoration-none">Corpo Clínico</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Horários &amp; Funções</li>
                </ol>
            </div>
            <div class="d-flex flex-wrap align-items-center gap-2">
                <a href="../page.php?slug=medicos" target="_blank" rel="noopener" class="btn btn-outline-info btn-sm rounded-pill px-3 shadow-sm" data-bs-toggle="tooltip" title="Visualizar página de médicos no portal público">
                    <i class="bi bi-box-arrow-up-right me-1"></i> Ver no Site
                </a>
                <a href="doctor.php" class="btn btn-outline-secondary btn-sm rounded-pill px-3 shadow-sm" data-bs-toggle="tooltip" title="Ver listagem dos médicos">
                    <i class="bi bi-person-badge me-1"></i> Médicos
                </a>
                <a href="extra-add.php" class="btn btn-primary btn-sm rounded-pill px-3 shadow-sm" data-bs-toggle="tooltip" title="Atribuir novo dia e horário de atendimento para um médico">
                    <i class="bi bi-plus-lg me-1"></i> Nova Escala
                </a>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">

        <!-- Mini Cards de Indicadores (KPIs) -->
        <div class="row g-3 mb-4">
            <!-- Total de Plantões/Turnos -->
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card border-0 shadow-sm rounded-3 h-100 kpi-card">
                    <div class="card-body d-flex align-items-center justify-content-between p-3">
                        <div>
                            <span class="text-uppercase fw-semibold text-body-secondary small d-block mb-1">Escalas Ativas</span>
                            <h3 class="mb-0 fw-bold text-body"><?= $total_escalas; ?></h3>
                            <span class="text-body-secondary small">Turnos de plantão</span>
                        </div>
                        <div class="kpi-icon-circle bg-primary-subtle text-primary">
                            <i class="bi bi-calendar2-week fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Médicos Escalados -->
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card border-0 shadow-sm rounded-3 h-100 kpi-card">
                    <div class="card-body d-flex align-items-center justify-content-between p-3">
                        <div>
                            <span class="text-uppercase fw-semibold text-body-secondary small d-block mb-1">Médicos Escalados</span>
                            <h3 class="mb-0 fw-bold text-success"><?= $total_medicos_escala; ?></h3>
                            <span class="text-body-secondary small">Profissionais com horário</span>
                        </div>
                        <div class="kpi-icon-circle bg-success-subtle text-success">
                            <i class="bi bi-person-check fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Clínicas com Escala -->
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card border-0 shadow-sm rounded-3 h-100 kpi-card">
                    <div class="card-body d-flex align-items-center justify-content-between p-3">
                        <div>
                            <span class="text-uppercase fw-semibold text-body-secondary small d-block mb-1">Unidades / Clínicas</span>
                            <h3 class="mb-0 fw-bold text-info"><?= $total_clinicas_escala; ?></h3>
                            <span class="text-body-secondary small">Pontos de atendimento</span>
                        </div>
                        <div class="kpi-icon-circle bg-info-subtle text-info">
                            <i class="bi bi-hospital fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Dias com Atendimento -->
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card border-0 shadow-sm rounded-3 h-100 kpi-card">
                    <div class="card-body d-flex align-items-center justify-content-between p-3">
                        <div>
                            <span class="text-uppercase fw-semibold text-body-secondary small d-block mb-1">Dias de Cobertura</span>
                            <h3 class="mb-0 fw-bold text-warning"><?= $total_dias_escala; ?></h3>
                            <span class="text-body-secondary small">Dias na semana</span>
                        </div>
                        <div class="kpi-icon-circle bg-warning-subtle text-warning">
                            <i class="bi bi-clock-history fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card da Tabela com Barra de Filtros Rápidos -->
        <div class="card border-0 shadow-sm rounded-3 mb-4">
            <div class="card-header bg-body py-3 border-bottom">
                <div class="row g-2 align-items-center justify-content-between">
                    <!-- Campo de Busca em Tempo Real -->
                    <div class="col-12 col-md-5 col-lg-4">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-body-tertiary text-body-secondary border">
                                <i class="bi bi-search"></i>
                            </span>
                            <input type="text" id="filtroBuscaEscala" class="form-control bg-body text-body border" placeholder="Buscar por médico, especialidade ou unidade...">
                            <button class="btn btn-outline-secondary border d-none" type="button" id="btnLimparBusca" title="Limpar pesquisa">
                                <i class="bi bi-x"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Filtro por Dia da Semana -->
                    <div class="col-12 col-md-7 col-lg-8 text-md-end">
                        <div class="d-flex flex-wrap align-items-center justify-content-md-end gap-2">
                            <label for="selectFiltroDia" class="small fw-semibold text-body-secondary mb-0 d-none d-sm-inline">
                                <i class="bi bi-funnel me-1"></i>Filtrar Dia:
                            </label>
                            <select id="selectFiltroDia" class="form-select form-select-sm bg-body text-body border w-auto" style="min-width: 160px;">
                                <option value="todos">Todos os Dias (<?= $total_escalas; ?>)</option>
                                <?php foreach ($dias_semana as $ds): ?>
                                    <option value="<?= strtolower(trim($ds['oh_day'])); ?>">
                                        <?= htmlspecialchars($ds['oh_day'], ENT_QUOTES, 'UTF-8'); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabela de Escalas de Atendimento -->
            <div class="card-body table-responsive p-0">
                <table id="tabelaEscalas" class="table table-hover table-escala align-middle mb-0 w-100">
                    <thead class="bg-body-tertiary text-body-secondary small text-uppercase">
                        <tr>
                            <th width="50" class="text-center py-3">#</th>
                            <th class="py-3">Médico / Especialista</th>
                            <th width="180" class="py-3">Especialidade</th>
                            <th width="180" class="py-3">Clínica / Unidade</th>
                            <th width="150" class="py-3">Dia da Semana</th>
                            <th width="170" class="text-center py-3">Horário de Atendimento</th>
                            <th width="130" class="text-center py-3">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($escalas)): ?>
                            <tr id="tr-empty-state">
                                <td colspan="7" class="text-center py-5 text-body-secondary">
                                    <i class="bi bi-calendar-x fs-1 d-block mb-2 text-body-tertiary"></i>
                                    <h6 class="fw-bold mb-1">Nenhuma escala de atendimento cadastrada</h6>
                                    <p class="small mb-3">Defina os dias, turnos e especialidades dos médicos para consulta no site.</p>
                                    <a href="extra-add.php" class="btn btn-primary btn-sm rounded-pill px-3">
                                        <i class="bi bi-plus-lg me-1"></i> Criar Primeira Escala
                                    </a>
                                </td>
                            </tr>
                        <?php else: 
                            $seq = 0;
                            foreach ($escalas as $row):
                                $seq++;
                                $ide        = (int)$row['ide'];
                                $doc_name   = htmlspecialchars($row['doctor_name'] ?? 'Médico Não Identificado', ENT_QUOTES, 'UTF-8');
                                $doc_reg    = htmlspecialchars($row['doctor_registro'] ?? '', ENT_QUOTES, 'UTF-8');
                                $doc_photo  = trim($row['doctor_photo'] ?? '');
                                $doc_status = $row['doctor_status'] ?? 'Active';
                                $is_ativo   = ($doc_status === 'Active');

                                $foto_path = (!empty($doc_photo) && file_exists(__DIR__ . '/../assets/uploads/' . $doc_photo))
                                    ? '../assets/uploads/' . $doc_photo
                                    : '../assets/images/sem-foto.gif';

                                $esp_name = htmlspecialchars($row['designation_name'] ?? 'Geral', ENT_QUOTES, 'UTF-8');
                                $dep_name = htmlspecialchars($row['dep_name'] ?? 'Unidade Principal', ENT_QUOTES, 'UTF-8');
                                $dia_name = htmlspecialchars($row['oh_day'] ?? 'A Definir', ENT_QUOTES, 'UTF-8');

                                $hora_inicio = !empty($row['inicio']) ? date('H:i', strtotime($row['inicio'])) : '--:--';
                                $hora_fim    = !empty($row['fim']) ? date('H:i', strtotime($row['fim'])) : '--:--';

                                $texto_busca = strtolower("{$doc_name} {$doc_reg} {$esp_name} {$dep_name} {$dia_name}");
                        ?>
                            <tr id="tr-extra-<?= $ide; ?>" 
                                data-ide="<?= $ide; ?>"
                                data-search="<?= $texto_busca; ?>"
                                data-dia="<?= strtolower(trim($dia_name)); ?>">
                                
                                <!-- Número Sequencial -->
                                <td class="text-center text-body-secondary fw-semibold small">
                                    <?= $seq; ?>
                                </td>

                                <!-- Médico com Foto e CRM -->
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <img src="<?= $foto_path; ?>" alt="<?= $doc_name; ?>" class="doctor-avatar-thumb border shadow-2xs">
                                        <div>
                                            <a href="doctor-edit.php?id=<?= $row['doctor_id']; ?>" class="fw-bold text-body text-decoration-none hover-primary d-block">
                                                <?= $doc_name; ?>
                                            </a>
                                            <div class="d-flex align-items-center gap-2 small">
                                                <?php if (!empty($doc_reg)): ?>
                                                    <span class="badge bg-body-tertiary text-body border font-monospace" style="font-size: 10px;">
                                                        <?= $doc_reg; ?>
                                                    </span>
                                                <?php endif; ?>
                                                <?php if (!$is_ativo): ?>
                                                    <span class="badge bg-secondary-subtle text-body border rounded-pill" style="font-size: 10px;">
                                                        Médico Inativo
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                <!-- Especialidade -->
                                <td>
                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill small py-1 px-2 d-inline-flex align-items-center gap-1">
                                        <i class="bi bi-award"></i> <?= $esp_name; ?>
                                    </span>
                                </td>

                                <!-- Clínica / Unidade -->
                                <td>
                                    <span class="badge bg-info-subtle text-info border border-info-subtle rounded-pill small py-1 px-2 d-inline-flex align-items-center gap-1">
                                        <i class="bi bi-hospital"></i> <?= $dep_name; ?>
                                    </span>
                                </td>

                                <!-- Dia da Semana -->
                                <td>
                                    <span class="badge bg-body-secondary text-body border rounded-pill small py-1 px-2 d-inline-flex align-items-center gap-1">
                                        <i class="bi bi-calendar-event text-primary"></i> <?= $dia_name; ?>
                                    </span>
                                </td>

                                <!-- Horário -->
                                <td class="text-center">
                                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill time-badge d-inline-flex align-items-center gap-1 shadow-2xs">
                                        <i class="bi bi-clock"></i> <?= $hora_inicio; ?> às <?= $hora_fim; ?>
                                    </span>
                                </td>

                                <!-- Ações -->
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm" role="group">
                                        <!-- Editar Escala -->
                                        <a href="extra-edit.php?ide=<?= $ide; ?>" class="btn btn-outline-primary" data-bs-toggle="tooltip" title="Editar escala de atendimento">
                                            <i class="bi bi-pencil-square"></i>
                                        </a>

                                        <!-- Excluir Escala -->
                                        <button type="button" 
                                                class="btn btn-outline-danger btn-delete-extra" 
                                                data-ide="<?= $ide; ?>" 
                                                data-doctor="<?= $doc_name; ?>" 
                                                data-dia="<?= $dia_name; ?>"
                                                data-bs-toggle="tooltip" 
                                                title="Remover esta escala">
                                            <i class="bi bi-trash3"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Rodapé com Total de Itens Visíveis -->
            <div class="card-footer bg-body py-2 px-3 border-top d-flex flex-wrap align-items-center justify-content-between small text-body-secondary">
                <div>
                    Mostrando <span id="contadorVisiveis" class="fw-bold text-body"><?= count($escalas); ?></span> de <span class="fw-bold text-body"><?= count($escalas); ?></span> turnos cadastrados
                </div>
                <div>
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill">
                        <i class="bi bi-shield-check me-1"></i>Integrado à exibição pública de horários
                    </span>
                </div>
            </div>
        </div>

    </div>
</div>

<!-- ==============================================================================
     SCRIPTS JAVASCRIPT: Filtros em Tempo Real, AJAX & SweetAlert2
     ============================================================================== -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    const inputBusca = document.getElementById('filtroBuscaEscala');
    const selectDia  = document.getElementById('selectFiltroDia');
    const btnLimpar  = document.getElementById('btnLimparBusca');
    const tabelaRows = document.querySelectorAll('#tabelaEscalas tbody tr[data-search]');
    const countSpan  = document.getElementById('contadorVisiveis');

    function aplicarFiltros() {
        const termoBusca = inputBusca ? inputBusca.value.trim().toLowerCase() : '';
        const diaFiltro  = selectDia ? selectDia.value.trim().toLowerCase() : 'todos';

        if (btnLimpar) {
            if (termoBusca.length > 0) {
                btnLimpar.classList.remove('d-none');
            } else {
                btnLimpar.classList.add('d-none');
            }
        }

        let visiveis = 0;

        tabelaRows.forEach(row => {
            const searchData = row.getAttribute('data-search') || '';
            const diaData    = row.getAttribute('data-dia') || '';

            const matchTexto = (termoBusca === '' || searchData.includes(termoBusca));
            const matchDia   = (diaFiltro === 'todos' || diaData === diaFiltro);

            if (matchTexto && matchDia) {
                row.classList.remove('d-none');
                visiveis++;
            } else {
                row.classList.add('d-none');
            }
        });

        if (countSpan) countSpan.textContent = visiveis;
    }

    if (inputBusca) {
        inputBusca.addEventListener('input', aplicarFiltros);
    }

    if (selectDia) {
        selectDia.addEventListener('change', aplicarFiltros);
    }

    if (btnLimpar) {
        btnLimpar.addEventListener('click', function () {
            inputBusca.value = '';
            aplicarFiltros();
            inputBusca.focus();
        });
    }

    // Exclusão Segura via AJAX com SweetAlert2
    document.querySelectorAll('.btn-delete-extra').forEach(btn => {
        btn.addEventListener('click', function () {
            const ide = this.getAttribute('data-ide');
            const doctor = this.getAttribute('data-doctor');
            const dia = this.getAttribute('data-dia');

            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'question',
                    title: 'Remover Escala de Atendimento?',
                    html: `Deseja remover o plantão de <strong>${doctor}</strong> na <strong>${dia}</strong>?<br><small class="text-body-secondary">O médico não aparecerá mais neste horário no site.</small>`,
                    showCancelButton: true,
                    confirmButtonText: '<i class="bi bi-trash3 me-1"></i> Sim, Remover',
                    cancelButtonText: 'Cancelar',
                    confirmButtonColor: '#dc3545',
                    cancelButtonColor: '#6c757d',
                    reverseButtons: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        const formData = new FormData();
                        formData.append('action', 'delete_extra');
                        formData.append('ide', ide);

                        fetch('extra.php', {
                            method: 'POST',
                            headers: { 'X-Requested-With': 'XMLHttpRequest' },
                            body: formData
                        })
                        .then(res => res.json())
                        .then(data => {
                            if (data.status === 'success') {
                                const tr = document.getElementById('tr-extra-' + ide);
                                if (tr) {
                                    tr.style.transition = 'all 0.35s ease';
                                    tr.style.opacity = '0';
                                    tr.style.transform = 'translateX(20px)';
                                    setTimeout(() => {
                                        tr.remove();
                                        aplicarFiltros();
                                    }, 350);
                                }

                                if (typeof toastr !== 'undefined') {
                                    toastr.success(data.message, 'Escala Removida');
                                } else {
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Removida!',
                                        text: data.message,
                                        timer: 1400,
                                        showConfirmButton: false
                                    });
                                }
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Erro ao Remover',
                                    text: data.message || 'Não foi possível excluir a escala de atendimento.',
                                    confirmButtonColor: '#0a6b74'
                                });
                            }
                        })
                        .catch(err => {
                            console.error(err);
                            Swal.fire({
                                icon: 'error',
                                title: 'Falha de Comunicação',
                                text: 'Erro ao se comunicar com o servidor.',
                                confirmButtonColor: '#0a6b74'
                            });
                        });
                    }
                });
            }
        });
    });

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
