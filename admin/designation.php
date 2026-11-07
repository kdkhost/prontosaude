<?php
require_once('header.php');

// ==============================================================================
// ENDPOINTS AJAX: Adicionar, Editar, Excluir e Obter Médicos da Especialidade
// ==============================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json; charset=utf-8');
    $action = $_POST['action'];

    // 1. Adicionar Nova Especialidade via AJAX
    if ($action === 'add_designation') {
        $name = trim($_POST['designation_name'] ?? '');

        if (empty($name)) {
            echo json_encode(['status' => 'error', 'message' => 'Informe o nome da especialidade médica.']);
            exit;
        }

        // Verifica duplicidade
        $stmt_check = $pdo->prepare("SELECT designation_id FROM tbl_designation WHERE LOWER(designation_name) = LOWER(?)");
        $stmt_check->execute([$name]);
        if ($stmt_check->rowCount() > 0) {
            echo json_encode(['status' => 'error', 'message' => 'Já existe uma especialidade cadastrada com este nome.']);
            exit;
        }

        try {
            $stmt_ins = $pdo->prepare("INSERT INTO tbl_designation (designation_name) VALUES (?)");
            $stmt_ins->execute([$name]);
            $new_id = (int)$pdo->lastInsertId();

            echo json_encode([
                'status'  => 'success',
                'message' => 'Especialidade adicionada com sucesso!',
                'id'      => $new_id,
                'name'    => $name
            ]);
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => 'Erro ao salvar no banco: ' . $e->getMessage()]);
        }
        exit;
    }

    // 2. Editar Especialidade via AJAX
    if ($action === 'edit_designation') {
        $id   = (int)($_POST['designation_id'] ?? 0);
        $name = trim($_POST['designation_name'] ?? '');

        if ($id <= 0 || empty($name)) {
            echo json_encode(['status' => 'error', 'message' => 'Dados incompletos para atualização.']);
            exit;
        }

        // Verifica duplicidade com outras especialidades
        $stmt_check = $pdo->prepare("SELECT designation_id FROM tbl_designation WHERE LOWER(designation_name) = LOWER(?) AND designation_id != ?");
        $stmt_check->execute([$name, $id]);
        if ($stmt_check->rowCount() > 0) {
            echo json_encode(['status' => 'error', 'message' => 'Já existe outra especialidade com este nome.']);
            exit;
        }

        try {
            $stmt_upd = $pdo->prepare("UPDATE tbl_designation SET designation_name = ? WHERE designation_id = ?");
            $stmt_upd->execute([$name, $id]);

            echo json_encode([
                'status'  => 'success',
                'message' => 'Especialidade atualizada com sucesso!',
                'id'      => $id,
                'name'    => $name
            ]);
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => 'Erro ao atualizar: ' . $e->getMessage()]);
        }
        exit;
    }

    // 3. Excluir Especialidade Segura via AJAX
    if ($action === 'delete_designation') {
        $id = (int)($_POST['id'] ?? 0);

        if ($id <= 0) {
            echo json_encode(['status' => 'error', 'message' => 'Identificador de especialidade inválido.']);
            exit;
        }

        // Verifica se existem médicos vinculados a esta especialidade
        $stmt_doc = $pdo->prepare("SELECT COUNT(*) FROM tbl_doctor WHERE designation_id = ?");
        $stmt_doc->execute([$id]);
        $total_medicos = (int)$stmt_doc->fetchColumn();

        if ($total_medicos > 0) {
            echo json_encode([
                'status'  => 'has_dependencies',
                'count'   => $total_medicos,
                'message' => "Não é possível excluir esta especialidade porque existem {$total_medicos} médico(s) vinculado(s) a ela. Realoque ou altere a especialidade desses médicos antes de excluir."
            ]);
            exit;
        }

        try {
            $stmt_del = $pdo->prepare("DELETE FROM tbl_designation WHERE designation_id = ?");
            $stmt_del->execute([$id]);

            echo json_encode([
                'status'  => 'success',
                'message' => 'Especialidade excluída com sucesso!'
            ]);
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => 'Erro ao excluir especialidade: ' . $e->getMessage()]);
        }
        exit;
    }

    // 4. Obter Médicos Vinculados à Especialidade (Para Modal de Detalhes)
    if ($action === 'get_doctors') {
        $id = (int)($_POST['id'] ?? 0);

        $stmt_docs = $pdo->prepare("SELECT id, name, registro, photo, status, phone FROM tbl_doctor WHERE designation_id = ? ORDER BY name ASC");
        $stmt_docs->execute([$id]);
        $medicos = $stmt_docs->fetchAll(PDO::FETCH_ASSOC);

        $lista_formatada = [];
        foreach ($medicos as $m) {
            $photo_name = trim($m['photo'] ?? '');
            $photo_path = (!empty($photo_name) && file_exists(__DIR__ . '/../assets/uploads/' . $photo_name))
                ? '../assets/uploads/' . $photo_name
                : '../assets/images/sem-foto.gif';

            $lista_formatada[] = [
                'id'       => (int)$m['id'],
                'name'     => htmlspecialchars($m['name'], ENT_QUOTES, 'UTF-8'),
                'registro' => htmlspecialchars($m['registro'] ?? 'Sem CRM', ENT_QUOTES, 'UTF-8'),
                'status'   => $m['status'] ?? 'Active',
                'phone'    => htmlspecialchars($m['phone'] ?? '', ENT_QUOTES, 'UTF-8'),
                'photo'    => $photo_path
            ];
        }

        echo json_encode([
            'status'  => 'success',
            'doctors' => $lista_formatada
        ]);
        exit;
    }
}

// ==============================================================================
// CONSULTA PRINCIPAL: Especialidades com Contagem de Médicos
// ==============================================================================
$statement = $pdo->prepare("SELECT 
                                d.designation_id,
                                d.designation_name,
                                COUNT(doc.id) AS total_doctors,
                                SUM(CASE WHEN doc.status = 'Active' THEN 1 ELSE 0 END) AS active_doctors
                            FROM tbl_designation d
                            LEFT JOIN tbl_doctor doc ON d.designation_id = doc.designation_id
                            GROUP BY d.designation_id, d.designation_name
                            ORDER BY d.designation_name ASC");
$statement->execute();
$designations = $statement->fetchAll(PDO::FETCH_ASSOC);

// Métricas de Indicadores (KPIs)
$total_especialidades = count($designations);
$total_com_medicos    = 0;
$total_sem_medicos    = 0;
$total_medicos_geral  = 0;

foreach ($designations as $desig) {
    $qtd = (int)($desig['total_doctors'] ?? 0);
    $total_medicos_geral += $qtd;
    if ($qtd > 0) {
        $total_com_medicos++;
    } else {
        $total_sem_medicos++;
    }
}
?>

<style>
/* Estilos Customizados e Harmonizados para a Gestão de Especialidades */
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
.badge-count-pill {
    font-size: 0.78rem;
    padding: 0.35rem 0.7rem;
    letter-spacing: 0.2px;
}
.table-designation tbody tr {
    transition: background-color 0.15s ease-in-out;
}
.table-designation tbody tr:hover {
    background-color: var(--bs-tertiary-bg, rgba(0, 0, 0, 0.02));
}
.doctor-avatar-sm {
    width: 38px;
    height: 38px;
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
                    <i class="bi bi-award-fill text-primary me-2"></i>Especialidades Médicas &amp; Designações
                </h3>
                <ol class="breadcrumb mb-0 small">
                    <li class="breadcrumb-item"><a href="index.php" class="text-decoration-none"><i class="bi bi-house-door"></i> Início</a></li>
                    <li class="breadcrumb-item"><a href="doctor.php" class="text-decoration-none">Corpo Clínico</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Especialidades</li>
                </ol>
            </div>
            <div class="d-flex flex-wrap align-items-center gap-2">
                <a href="../page.php?slug=medicos" target="_blank" rel="noopener" class="btn btn-outline-info btn-sm rounded-pill px-3 shadow-sm" data-bs-toggle="tooltip" title="Visualizar página de médicos no portal público">
                    <i class="bi bi-box-arrow-up-right me-1"></i> Ver no Site
                </a>
                <a href="doctor.php" class="btn btn-outline-primary btn-sm rounded-pill px-3 shadow-sm" data-bs-toggle="tooltip" title="Ver todos os médicos cadastrados">
                    <i class="bi bi-people me-1"></i> Médicos
                </a>
                <button type="button" class="btn btn-primary btn-sm rounded-pill px-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#modalAddDesignation">
                    <i class="bi bi-plus-lg me-1"></i> Nova Especialidade
                </button>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">

        <!-- Mini Cards de Indicadores (KPIs) -->
        <div class="row g-3 mb-4">
            <!-- Total de Especialidades -->
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card border-0 shadow-sm rounded-3 h-100 kpi-card">
                    <div class="card-body d-flex align-items-center justify-content-between p-3">
                        <div>
                            <span class="text-uppercase fw-semibold text-body-secondary small d-block mb-1">Especialidades</span>
                            <h3 class="mb-0 fw-bold text-body"><?= $total_especialidades; ?></h3>
                            <span class="text-body-secondary small">Áreas cadastradas</span>
                        </div>
                        <div class="kpi-icon-circle bg-primary-subtle text-primary">
                            <i class="bi bi-award fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Especialidades com Médicos -->
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card border-0 shadow-sm rounded-3 h-100 kpi-card">
                    <div class="card-body d-flex align-items-center justify-content-between p-3">
                        <div>
                            <span class="text-uppercase fw-semibold text-body-secondary small d-block mb-1">Com Especialistas</span>
                            <h3 class="mb-0 fw-bold text-success"><?= $total_com_medicos; ?></h3>
                            <span class="text-body-secondary small">Com médicos no corpo clínico</span>
                        </div>
                        <div class="kpi-icon-circle bg-success-subtle text-success">
                            <i class="bi bi-person-check fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Especialidades Livres / Sem Médicos -->
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card border-0 shadow-sm rounded-3 h-100 kpi-card">
                    <div class="card-body d-flex align-items-center justify-content-between p-3">
                        <div>
                            <span class="text-uppercase fw-semibold text-body-secondary small d-block mb-1">Sem Especialista</span>
                            <h3 class="mb-0 fw-bold text-warning"><?= $total_sem_medicos; ?></h3>
                            <span class="text-body-secondary small">Disponíveis para vínculo</span>
                        </div>
                        <div class="kpi-icon-circle bg-warning-subtle text-warning">
                            <i class="bi bi-person-dash fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Total Geral de Médicos -->
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card border-0 shadow-sm rounded-3 h-100 kpi-card">
                    <div class="card-body d-flex align-items-center justify-content-between p-3">
                        <div>
                            <span class="text-uppercase fw-semibold text-body-secondary small d-block mb-1">Corpo Clínico</span>
                            <h3 class="mb-0 fw-bold text-info"><?= $total_medicos_geral; ?></h3>
                            <span class="text-body-secondary small">Total de especialistas</span>
                        </div>
                        <div class="kpi-icon-circle bg-info-subtle text-info">
                            <i class="bi bi-people fs-4"></i>
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
                            <input type="text" id="filtroBuscaEspecialidade" class="form-control bg-body text-body border" placeholder="Buscar por nome da especialidade...">
                            <button class="btn btn-outline-secondary border d-none" type="button" id="btnLimparBusca" title="Limpar pesquisa">
                                <i class="bi bi-x"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Botões de Filtro Rápido -->
                    <div class="col-12 col-md-7 col-lg-8 text-md-end">
                        <div class="btn-group btn-group-sm shadow-2xs" role="group" aria-label="Filtros Rápidos">
                            <button type="button" class="btn btn-outline-primary active btn-filter" data-filter="todos">
                                Todas <span class="badge bg-primary text-white ms-1 rounded-pill"><?= $total_especialidades; ?></span>
                            </button>
                            <button type="button" class="btn btn-outline-primary btn-filter" data-filter="com-medicos">
                                Com Médicos <span class="badge bg-success-subtle text-success ms-1 rounded-pill"><?= $total_com_medicos; ?></span>
                            </button>
                            <button type="button" class="btn btn-outline-primary btn-filter" data-filter="sem-medicos">
                                Sem Médicos <span class="badge bg-warning-subtle text-warning ms-1 rounded-pill"><?= $total_sem_medicos; ?></span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabela de Especialidades -->
            <div class="card-body table-responsive p-0">
                <table id="tabelaEspecialidades" class="table table-hover table-designation align-middle mb-0 w-100">
                    <thead class="bg-body-tertiary text-body-secondary small text-uppercase">
                        <tr>
                            <th width="60" class="text-center py-3">#</th>
                            <th class="py-3">Nome da Especialidade / Designação</th>
                            <th width="200" class="text-center py-3">Médicos Vinculados</th>
                            <th width="180" class="text-center py-3">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($designations)): ?>
                            <tr id="tr-empty-state">
                                <td colspan="4" class="text-center py-5 text-body-secondary">
                                    <i class="bi bi-award fs-1 d-block mb-2 text-body-tertiary"></i>
                                    <h6 class="fw-bold mb-1">Nenhuma especialidade cadastrada</h6>
                                    <p class="small mb-3">Cadastre as áreas de atuação médica dos especialistas da clínica.</p>
                                    <button type="button" class="btn btn-primary btn-sm rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#modalAddDesignation">
                                        <i class="bi bi-plus-lg me-1"></i> Adicionar Primeira Especialidade
                                    </button>
                                </td>
                            </tr>
                        <?php else: 
                            $seq = 0;
                            foreach ($designations as $row):
                                $seq++;
                                $d_id     = (int)$row['designation_id'];
                                $d_name   = htmlspecialchars($row['designation_name'], ENT_QUOTES, 'UTF-8');
                                $tot_docs = (int)($row['total_doctors'] ?? 0);
                                $act_docs = (int)($row['active_doctors'] ?? 0);
                                $has_docs = ($tot_docs > 0);
                        ?>
                            <tr id="tr-desig-<?= $d_id; ?>" 
                                data-id="<?= $d_id; ?>" 
                                data-name="<?= strtolower($d_name); ?>"
                                data-has-doctors="<?= $has_docs ? '1' : '0'; ?>">
                                
                                <!-- Número Sequencial -->
                                <td class="text-center text-body-secondary fw-semibold small">
                                    <?= $seq; ?>
                                </td>

                                <!-- Nome da Especialidade -->
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="rounded-circle bg-primary-subtle text-primary d-flex align-items-center justify-content-center" style="width: 34px; height: 34px; flex-shrink: 0;">
                                            <i class="bi bi-award"></i>
                                        </div>
                                        <div>
                                            <span class="fw-bold text-body fs-6 desig-name-text"><?= $d_name; ?></span>
                                            <span class="d-block text-body-secondary small" style="font-size: 11px;">ID #<?= $d_id; ?></span>
                                        </div>
                                    </div>
                                </td>

                                <!-- Badge de Médicos Vinculados -->
                                <td class="text-center">
                                    <?php if ($tot_docs > 0): ?>
                                        <button type="button" 
                                                class="btn btn-sm p-0 border-0 bg-transparent btn-view-doctors" 
                                                data-id="<?= $d_id; ?>" 
                                                data-name="<?= $d_name; ?>" 
                                                title="Clique para ver os médicos vinculados">
                                            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill badge-count-pill d-inline-flex align-items-center gap-1 shadow-2xs">
                                                <i class="bi bi-person-check-fill"></i>
                                                <span><?= $tot_docs; ?> <?= $tot_docs === 1 ? 'médico' : 'médicos'; ?></span>
                                                <i class="bi bi-chevron-right ms-1" style="font-size: 9px;"></i>
                                            </span>
                                        </button>
                                    <?php else: ?>
                                        <span class="badge bg-body-secondary text-body-secondary border rounded-pill badge-count-pill d-inline-flex align-items-center gap-1">
                                            <i class="bi bi-dash-circle"></i> Nenhum médico
                                        </span>
                                    <?php endif; ?>
                                </td>

                                <!-- Botões de Ação -->
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm" role="group">
                                        <!-- Ver Médicos (Se houver) -->
                                        <?php if ($tot_docs > 0): ?>
                                            <button type="button" 
                                                    class="btn btn-outline-info btn-view-doctors" 
                                                    data-id="<?= $d_id; ?>" 
                                                    data-name="<?= $d_name; ?>" 
                                                    data-bs-toggle="tooltip" 
                                                    title="Ver médicos desta especialidade">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                        <?php endif; ?>

                                        <!-- Editar Especialidade -->
                                        <button type="button" 
                                                class="btn btn-outline-primary btn-edit-desig" 
                                                data-id="<?= $d_id; ?>" 
                                                data-name="<?= $d_name; ?>" 
                                                data-bs-toggle="tooltip" 
                                                title="Editar nome da especialidade">
                                            <i class="bi bi-pencil-square"></i>
                                        </button>

                                        <!-- Excluir Especialidade -->
                                        <button type="button" 
                                                class="btn btn-outline-danger btn-delete-desig" 
                                                data-id="<?= $d_id; ?>" 
                                                data-name="<?= $d_name; ?>" 
                                                data-total-doctors="<?= $tot_docs; ?>"
                                                data-bs-toggle="tooltip" 
                                                title="Excluir especialidade">
                                            <i class="bi bi-trash3"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Rodapé da Tabela com Resumo -->
            <div class="card-footer bg-body py-2 px-3 border-top d-flex flex-wrap align-items-center justify-content-between small text-body-secondary">
                <div>
                    Mostrando <span id="contadorVisiveis" class="fw-bold text-body"><?= count($designations); ?></span> de <span class="fw-bold text-body"><?= count($designations); ?></span> especialidades
                </div>
                <div>
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill">
                        <i class="bi bi-shield-check me-1"></i>Proteção de integridade referencial ativa
                    </span>
                </div>
            </div>
        </div>

    </div>
</div>

<!-- ==============================================================================
     MODAL 1: ADICIONAR NOVA ESPECIALIDADE (AJAX)
     ============================================================================== -->
<div class="modal fade" id="modalAddDesignation" tabindex="-1" aria-labelledby="modalAddDesignationLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-3">
            <div class="modal-header border-bottom py-3">
                <h5 class="modal-title fw-bold text-body d-flex align-items-center gap-2" id="modalAddDesignationLabel">
                    <i class="bi bi-award-fill text-primary"></i> Nova Especialidade Médica
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <form id="formAddDesignation" novalidate>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label for="add_designation_name" class="form-label fw-bold text-body">
                            Nome da Especialidade <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-body-tertiary text-body-secondary border">
                                <i class="bi bi-award"></i>
                            </span>
                            <input type="text" 
                                   class="form-control bg-body text-body border" 
                                   id="add_designation_name" 
                                   name="designation_name" 
                                   required 
                                   autocomplete="off"
                                   placeholder="Ex: Cardiologia, Pediatria, Dermatologia...">
                        </div>
                        <div class="form-text small text-body-secondary mt-1">
                            Esta especialidade ficará disponível no cadastro e na edição de médicos do corpo clínico.
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-body-tertiary border-top py-2 px-4">
                    <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3" data-bs-dismiss="modal">
                        Cancelar
                    </button>
                    <button type="submit" class="btn btn-primary btn-sm rounded-pill px-4 shadow-sm" id="btnSalvarNovaDesig">
                        <i class="bi bi-check-lg me-1"></i> Salvar Especialidade
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ==============================================================================
     MODAL 2: EDITAR ESPECIALIDADE (AJAX)
     ============================================================================== -->
<div class="modal fade" id="modalEditDesignation" tabindex="-1" aria-labelledby="modalEditDesignationLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-3">
            <div class="modal-header border-bottom py-3">
                <h5 class="modal-title fw-bold text-body d-flex align-items-center gap-2" id="modalEditDesignationLabel">
                    <i class="bi bi-pencil-square text-primary"></i> Editar Especialidade Médica
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <form id="formEditDesignation" novalidate>
                <input type="hidden" id="edit_designation_id" name="designation_id" value="">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label for="edit_designation_name" class="form-label fw-bold text-body">
                            Nome da Especialidade <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-body-tertiary text-body-secondary border">
                                <i class="bi bi-award"></i>
                            </span>
                            <input type="text" 
                                   class="form-control bg-body text-body border" 
                                   id="edit_designation_name" 
                                   name="designation_name" 
                                   required 
                                   autocomplete="off">
                        </div>
                        <div class="form-text small text-body-secondary mt-1">
                            A alteração atualizará automaticamente a exibição em todos os médicos associados.
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-body-tertiary border-top py-2 px-4">
                    <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3" data-bs-dismiss="modal">
                        Cancelar
                    </button>
                    <button type="submit" class="btn btn-primary btn-sm rounded-pill px-4 shadow-sm" id="btnSalvarEditDesig">
                        <i class="bi bi-check-lg me-1"></i> Salvar Alterações
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ==============================================================================
     MODAL 3: VISUALIZAR MÉDICOS VINCULADOS À ESPECIALIDADE
     ============================================================================== -->
<div class="modal fade" id="modalViewDoctors" tabindex="-1" aria-labelledby="modalViewDoctorsLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg rounded-3">
            <div class="modal-header border-bottom py-3">
                <div class="d-flex align-items-center gap-2">
                    <div class="rounded-circle bg-primary-subtle text-primary d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                        <i class="bi bi-award-fill"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold text-body mb-0" id="modalViewDoctorsLabel">Médicos da Especialidade</h5>
                        <small class="text-body-secondary" id="modalViewDoctorsSub">Especialidade Selecionada</small>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body p-3">
                <div id="modalDoctorsLoading" class="text-center py-4">
                    <div class="spinner-border text-primary spinner-border-sm me-2" role="status"></div>
                    <span class="text-body-secondary small">Carregando médicos da especialidade...</span>
                </div>
                <div id="modalDoctorsList" class="d-none">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-body-tertiary text-body-secondary small text-uppercase">
                                <tr>
                                    <th width="60">Foto</th>
                                    <th>Nome do Especialista</th>
                                    <th width="140">Registro (CRM)</th>
                                    <th width="110" class="text-center">Status</th>
                                    <th width="100" class="text-center">Ação</th>
                                </tr>
                            </thead>
                            <tbody id="modalDoctorsTableBody">
                                <!-- Preenchido dinamicamente via JS -->
                            </tbody>
                        </table>
                    </div>
                </div>
                <div id="modalDoctorsEmpty" class="text-center py-4 d-none text-body-secondary">
                    <i class="bi bi-people fs-1 d-block mb-1 text-body-tertiary"></i>
                    <p class="small mb-0">Nenhum médico vinculado a esta especialidade no momento.</p>
                </div>
            </div>
            <div class="modal-footer bg-body-tertiary border-top py-2 px-4 d-flex justify-content-between">
                <a href="doctor-add.php" class="btn btn-outline-primary btn-sm rounded-pill px-3" id="btnModalAddDoctorDesig">
                    <i class="bi bi-plus-lg me-1"></i> Vincular Novo Médico
                </a>
                <button type="button" class="btn btn-secondary btn-sm rounded-pill px-3" data-bs-dismiss="modal">
                    Fechar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ==============================================================================
     SCRIPTS JAVASCRIPT: Filtros em Tempo Real, AJAX & SweetAlert2
     ============================================================================== -->
<script>
document.addEventListener('DOMContentLoaded', function () {

    // 1. Filtro de Busca Instantânea por Texto
    const inputBusca = document.getElementById('filtroBuscaEspecialidade');
    const btnLimpar  = document.getElementById('btnLimparBusca');
    const tabelaRows = document.querySelectorAll('#tabelaEspecialidades tbody tr[data-name]');
    const countSpan  = document.getElementById('contadorVisiveis');

    let currentFilterType = 'todos';

    function aplicarFiltros() {
        const termo = inputBusca ? inputBusca.value.trim().toLowerCase() : '';
        let visiveis = 0;

        if (btnLimpar) {
            if (termo.length > 0) {
                btnLimpar.classList.remove('d-none');
            } else {
                btnLimpar.classList.add('d-none');
            }
        }

        tabelaRows.forEach(row => {
            const nomeRow = row.getAttribute('data-name') || '';
            const hasDocs = row.getAttribute('data-has-doctors') === '1';

            let matchTexto = (termo === '' || nomeRow.includes(termo));
            let matchTipo  = true;

            if (currentFilterType === 'com-medicos') {
                matchTipo = hasDocs;
            } else if (currentFilterType === 'sem-medicos') {
                matchTipo = !hasDocs;
            }

            if (matchTexto && matchTipo) {
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

    if (btnLimpar) {
        btnLimpar.addEventListener('click', function () {
            inputBusca.value = '';
            aplicarFiltros();
            inputBusca.focus();
        });
    }

    // 2. Filtro Rápido por Botões (Todos, Com Médicos, Sem Médicos)
    const filterButtons = document.querySelectorAll('.btn-filter');
    filterButtons.forEach(btn => {
        btn.addEventListener('click', function () {
            filterButtons.forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            currentFilterType = this.getAttribute('data-filter') || 'todos';
            aplicarFiltros();
        });
    });

    // 3. Adicionar Nova Especialidade (AJAX)
    const formAdd = document.getElementById('formAddDesignation');
    const modalAddEl = document.getElementById('modalAddDesignation');
    const btnSalvarNovo = document.getElementById('btnSalvarNovaDesig');

    if (formAdd) {
        formAdd.addEventListener('submit', function (e) {
            e.preventDefault();

            const inputName = document.getElementById('add_designation_name');
            const nome = inputName ? inputName.value.trim() : '';

            if (!nome) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Atenção',
                        text: 'Informe o nome da especialidade médica.',
                        confirmButtonColor: '#0a6b74'
                    });
                }
                if (inputName) inputName.focus();
                return;
            }

            const btnOriginal = btnSalvarNovo.innerHTML;
            btnSalvarNovo.disabled = true;
            btnSalvarNovo.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Salvando...';

            const formData = new FormData();
            formData.append('action', 'add_designation');
            formData.append('designation_name', nome);

            fetch('designation.php', {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                btnSalvarNovo.disabled = false;
                btnSalvarNovo.innerHTML = btnOriginal;

                if (data.status === 'success') {
                    const bsModal = bootstrap.Modal.getInstance(modalAddEl);
                    if (bsModal) bsModal.hide();
                    formAdd.reset();

                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Especialidade Adicionada!',
                            text: data.message,
                            timer: 1500,
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
                            title: 'Não foi possível salvar',
                            text: data.message || 'Erro ao adicionar especialidade.',
                            confirmButtonColor: '#0a6b74'
                        });
                    }
                }
            })
            .catch(err => {
                console.error(err);
                btnSalvarNovo.disabled = false;
                btnSalvarNovo.innerHTML = btnOriginal;
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'error',
                        title: 'Falha de Comunicação',
                        text: 'Erro ao se comunicar com o servidor. Tente novamente.',
                        confirmButtonColor: '#0a6b74'
                    });
                }
            });
        });
    }

    // 4. Editar Especialidade (Modal & AJAX)
    const modalEditEl = document.getElementById('modalEditDesignation');
    const formEdit    = document.getElementById('formEditDesignation');
    const inputEditId = document.getElementById('edit_designation_id');
    const inputEditNm = document.getElementById('edit_designation_name');
    const btnSalvarEdit = document.getElementById('btnSalvarEditDesig');

    document.querySelectorAll('.btn-edit-desig').forEach(btn => {
        btn.addEventListener('click', function () {
            const id = this.getAttribute('data-id');
            const name = this.getAttribute('data-name');

            if (inputEditId && inputEditNm) {
                inputEditId.value = id;
                inputEditNm.value = name;

                const bsModal = new bootstrap.Modal(modalEditEl);
                bsModal.show();
                setTimeout(() => inputEditNm.focus(), 350);
            }
        });
    });

    if (formEdit) {
        formEdit.addEventListener('submit', function (e) {
            e.preventDefault();

            const id   = inputEditId.value;
            const nome = inputEditNm.value.trim();

            if (!nome) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Atenção',
                        text: 'O nome da especialidade não pode ficar vazio.',
                        confirmButtonColor: '#0a6b74'
                    });
                }
                inputEditNm.focus();
                return;
            }

            const btnOriginal = btnSalvarEdit.innerHTML;
            btnSalvarEdit.disabled = true;
            btnSalvarEdit.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Atualizando...';

            const formData = new FormData();
            formData.append('action', 'edit_designation');
            formData.append('designation_id', id);
            formData.append('designation_name', nome);

            fetch('designation.php', {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                btnSalvarEdit.disabled = false;
                btnSalvarEdit.innerHTML = btnOriginal;

                if (data.status === 'success') {
                    const bsModal = bootstrap.Modal.getInstance(modalEditEl);
                    if (bsModal) bsModal.hide();

                    // Atualiza o texto na linha da tabela dinamicamente
                    const tr = document.getElementById('tr-desig-' + id);
                    if (tr) {
                        const nameSpan = tr.querySelector('.desig-name-text');
                        if (nameSpan) nameSpan.textContent = nome;
                        tr.setAttribute('data-name', nome.toLowerCase());

                        // Atualiza atributos dos botões dessa linha
                        tr.querySelectorAll('[data-name]').forEach(el => el.setAttribute('data-name', nome));
                    }

                    if (typeof toastr !== 'undefined') {
                        toastr.success(data.message, 'Atualizado com Sucesso');
                    } else if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Atualizado!',
                            text: data.message,
                            timer: 1200,
                            showConfirmButton: false
                        });
                    }
                } else {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'error',
                            title: 'Erro ao Atualizar',
                            text: data.message || 'Não foi possível alterar a especialidade.',
                            confirmButtonColor: '#0a6b74'
                        });
                    }
                }
            })
            .catch(err => {
                console.error(err);
                btnSalvarEdit.disabled = false;
                btnSalvarEdit.innerHTML = btnOriginal;
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'error',
                        title: 'Falha de Comunicação',
                        text: 'Ocorreu um erro ao enviar os dados. Tente novamente.',
                        confirmButtonColor: '#0a6b74'
                    });
                }
            });
        });
    }

    // 5. Excluir Especialidade Segura com SweetAlert2
    document.querySelectorAll('.btn-delete-desig').forEach(btn => {
        btn.addEventListener('click', function () {
            const id = this.getAttribute('data-id');
            const name = this.getAttribute('data-name');
            const totalDocs = parseInt(this.getAttribute('data-total-doctors') || '0', 10);

            // Bloqueio Preventivo no Frontend caso haja médicos vinculados
            if (totalDocs > 0) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Exclusão Bloqueada',
                        html: `A especialidade <strong>"${name}"</strong> possui <strong>${totalDocs} médico(s) vinculado(s)</strong>.<br><br>Para manter a integridade dos dados, altere a especialidade desses médicos antes de excluir.`,
                        confirmButtonText: '<i class="bi bi-people me-1"></i> Ver Médicos Vinculados',
                        showCancelButton: true,
                        cancelButtonText: 'Entendi, fechar',
                        confirmButtonColor: '#0a6b74',
                        cancelButtonColor: '#6c757d'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            // Abre modal de médicos desta especialidade
                            const btnView = document.querySelector(`.btn-view-doctors[data-id="${id}"]`);
                            if (btnView) btnView.click();
                        }
                    });
                }
                return;
            }

            // Confirmação de Exclusão Segura
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'question',
                    title: 'Excluir Especialidade?',
                    html: `Tem certeza de que deseja remover a especialidade <strong>"${name}"</strong>?<br><small class="text-body-secondary">Esta ação não poderá ser revertida.</small>`,
                    showCancelButton: true,
                    confirmButtonText: '<i class="bi bi-trash3 me-1"></i> Sim, Excluir',
                    cancelButtonText: 'Cancelar',
                    confirmButtonColor: '#dc3545',
                    cancelButtonColor: '#6c757d',
                    reverseButtons: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        const formData = new FormData();
                        formData.append('action', 'delete_designation');
                        formData.append('id', id);

                        fetch('designation.php', {
                            method: 'POST',
                            headers: { 'X-Requested-With': 'XMLHttpRequest' },
                            body: formData
                        })
                        .then(res => res.json())
                        .then(data => {
                            if (data.status === 'success') {
                                const tr = document.getElementById('tr-desig-' + id);
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
                                    toastr.success(data.message, 'Excluído com Sucesso');
                                } else {
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Excluída!',
                                        text: data.message,
                                        timer: 1400,
                                        showConfirmButton: false
                                    });
                                }
                            } else if (data.status === 'has_dependencies') {
                                Swal.fire({
                                    icon: 'warning',
                                    title: 'Atenção',
                                    html: data.message,
                                    confirmButtonColor: '#0a6b74'
                                });
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Erro ao Excluir',
                                    text: data.message || 'Não foi possível excluir a especialidade.',
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

    // 6. Visualizar Médicos da Especialidade (Modal Dinâmico)
    const modalViewDocsEl = document.getElementById('modalViewDoctors');
    const modalTitleSub   = document.getElementById('modalViewDoctorsSub');
    const loadingDocsEl   = document.getElementById('modalDoctorsLoading');
    const listDocsEl      = document.getElementById('modalDoctorsList');
    const emptyDocsEl     = document.getElementById('modalDoctorsEmpty');
    const tbodyDocs       = document.getElementById('modalDoctorsTableBody');

    document.querySelectorAll('.btn-view-doctors').forEach(btn => {
        btn.addEventListener('click', function () {
            const id = this.getAttribute('data-id');
            const name = this.getAttribute('data-name');

            if (modalTitleSub) modalTitleSub.textContent = `Especialidade: ${name}`;

            if (loadingDocsEl) loadingDocsEl.classList.remove('d-none');
            if (listDocsEl) listDocsEl.classList.add('d-none');
            if (emptyDocsEl) emptyDocsEl.classList.add('d-none');
            if (tbodyDocs) tbodyDocs.innerHTML = '';

            const bsModal = new bootstrap.Modal(modalViewDocsEl);
            bsModal.show();

            const formData = new FormData();
            formData.append('action', 'get_doctors');
            formData.append('id', id);

            fetch('designation.php', {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (loadingDocsEl) loadingDocsEl.classList.add('d-none');

                if (data.status === 'success' && data.doctors && data.doctors.length > 0) {
                    let htmlRows = '';
                    data.doctors.forEach(doc => {
                        const isAtivo = (doc.status === 'Active');
                        const statusBadge = isAtivo 
                            ? '<span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill small">Ativo</span>'
                            : '<span class="badge bg-secondary-subtle text-body border rounded-pill small">Inativo</span>';

                        htmlRows += `
                            <tr>
                                <td>
                                    <img src="${doc.photo}" alt="${doc.name}" class="doctor-avatar-sm border shadow-2xs">
                                </td>
                                <td>
                                    <div class="fw-bold text-body">${doc.name}</div>
                                    <small class="text-body-secondary">${doc.phone || 'Sem telefone'}</small>
                                </td>
                                <td>
                                    <span class="badge bg-body-tertiary text-body border font-monospace">${doc.registro}</span>
                                </td>
                                <td class="text-center">
                                    ${statusBadge}
                                </td>
                                <td class="text-center">
                                    <a href="doctor-edit.php?id=${doc.id}" class="btn btn-outline-primary btn-sm rounded-pill px-2 py-1" title="Editar médico">
                                        <i class="bi bi-pencil-square me-1"></i>Editar
                                    </a>
                                </td>
                            </tr>
                        `;
                    });

                    if (tbodyDocs) tbodyDocs.innerHTML = htmlRows;
                    if (listDocsEl) listDocsEl.classList.remove('d-none');
                } else {
                    if (emptyDocsEl) emptyDocsEl.classList.remove('d-none');
                }
            })
            .catch(err => {
                console.error(err);
                if (loadingDocsEl) loadingDocsEl.classList.add('d-none');
                if (emptyDocsEl) {
                    emptyDocsEl.innerHTML = '<p class="text-danger small">Erro ao carregar os médicos da especialidade.</p>';
                    emptyDocsEl.classList.remove('d-none');
                }
            });
        });
    });

    // 7. Tooltips com Auto-Hide
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
