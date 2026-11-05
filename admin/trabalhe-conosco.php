<?php
require_once('header.php');

// 1. Endpoint AJAX: Alternar Status da Vaga
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'toggle_status') {
    header('Content-Type: application/json; charset=utf-8');
    $id = (int)($_POST['id'] ?? 0);
    $status = (int)($_POST['status'] ?? 0);

    if ($id > 0) {
        $statement = $pdo->prepare("UPDATE tbl_trabalhe SET status=? WHERE id=?");
        $statement->execute([$status, $id]);
        echo json_encode([
            'status'     => 'success',
            'message'    => $status === 1 ? 'Vaga ativada e visível no site!' : 'Vaga pausada/encerrada com sucesso.',
            'new_status' => $status
        ]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'ID de vaga inválido.']);
    }
    exit;
}

// 2. Endpoint AJAX: Excluir Vaga com Remoção Segura de Fotos
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_vaga') {
    header('Content-Type: application/json; charset=utf-8');
    $id = (int)($_POST['id'] ?? 0);

    if ($id > 0) {
        $stmt_find = $pdo->prepare("SELECT foto, banner FROM tbl_trabalhe WHERE id=?");
        $stmt_find->execute([$id]);
        $vaga = $stmt_find->fetch(PDO::FETCH_ASSOC);

        if ($vaga) {
            if (!empty($vaga['foto']) && file_exists('../assets/uploads/' . $vaga['foto'])) {
                @unlink('../assets/uploads/' . $vaga['foto']);
            }
            if (!empty($vaga['banner']) && file_exists('../assets/uploads/' . $vaga['banner'])) {
                @unlink('../assets/uploads/' . $vaga['banner']);
            }
            $stmt_del = $pdo->prepare("DELETE FROM tbl_trabalhe WHERE id=?");
            $stmt_del->execute([$id]);
            echo json_encode(['status' => 'success', 'message' => 'Vaga removida com sucesso!']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Vaga não encontrada no banco de dados.']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Identificador inválido.']);
    }
    exit;
}

// 3. Consulta de Vagas e Indicadores Estatísticos (KPIs)
$statement = $pdo->prepare("SELECT * FROM tbl_trabalhe ORDER BY id DESC");
$statement->execute();
$vagas = $statement->fetchAll(PDO::FETCH_ASSOC);

$total_vagas    = count($vagas);
$total_ativas   = 0;
$total_inativas = 0;

foreach ($vagas as $v) {
    if ((int)$v['status'] === 1) {
        $total_ativas++;
    } else {
        $total_inativas++;
    }
}
?>

<!-- Cabeçalho da Página -->
<div class="app-content-header pb-2 mb-3 border-bottom">
    <div class="container-fluid">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div>
                <h3 class="mb-1 fw-bold">Oportunidades & Vagas (Trabalhe Conosco)</h3>
                <ol class="breadcrumb mb-0 small">
                    <li class="breadcrumb-item"><a href="index.php"><i class="bi bi-house-door"></i> Início</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Trabalhe Conosco</li>
                </ol>
            </div>
            <div class="d-flex flex-wrap align-items-center gap-2">
                <a href="<?= BASE_URL; ?>trabalhe-conosco.php" target="_blank" rel="noopener" class="btn btn-outline-info btn-sm shadow-xs" title="Ver vagas públicas no site">
                    <i class="bi bi-box-arrow-up-right me-1"></i> Ver no Site
                </a>
                <a href="vagas-add.php" class="btn btn-primary btn-sm shadow-xs px-3">
                    <i class="bi bi-plus-lg me-1"></i> Adicionar Nova Vaga
                </a>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">

        <!-- Mensagens Tradicionais de Sessão (Fallback) -->
        <?php if(!empty($_SESSION['msg'])): ?>
            <div class="alert alert-success alert-dismissible fade show shadow-xs mb-4" role="alert">
                <div class="d-flex align-items-center">
                    <i class="bi bi-check-circle-fill fs-5 me-2"></i>
                    <div><?= $_SESSION['msg']; ?></div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
            </div>
            <?php unset($_SESSION['msg']); ?>
        <?php endif; ?>

        <!-- Cards de Indicadores (KPIs) -->
        <div class="row g-3 mb-4">
            <div class="col-12 col-sm-6 col-xl-4">
                <div class="card shadow-sm border-0 border-start border-primary border-4 h-100 bg-body">
                    <div class="card-body d-flex align-items-center justify-content-between py-3">
                        <div>
                            <div class="text-body-secondary small text-uppercase fw-bold">Total de Vagas</div>
                            <div class="fs-3 fw-bold text-dark mt-1" id="kpiTotal"><?= $total_vagas; ?></div>
                        </div>
                        <div class="p-3 bg-primary-subtle text-primary rounded-circle">
                            <i class="bi bi-briefcase-fill fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-xl-4">
                <div class="card shadow-sm border-0 border-start border-success border-4 h-100 bg-body">
                    <div class="card-body d-flex align-items-center justify-content-between py-3">
                        <div>
                            <div class="text-body-secondary small text-uppercase fw-bold">Vagas Abertas (Ativas)</div>
                            <div class="fs-3 fw-bold text-success mt-1" id="kpiAtivas"><?= $total_ativas; ?></div>
                        </div>
                        <div class="p-3 bg-success-subtle text-success rounded-circle">
                            <i class="bi bi-check-circle-fill fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-xl-4">
                <div class="card shadow-sm border-0 border-start border-warning border-4 h-100 bg-body">
                    <div class="card-body d-flex align-items-center justify-content-between py-3">
                        <div>
                            <div class="text-body-secondary small text-uppercase fw-bold">Encerradas / Pausadas</div>
                            <div class="fs-3 fw-bold text-warning mt-1" id="kpiInativas"><?= $total_inativas; ?></div>
                        </div>
                        <div class="p-3 bg-warning-subtle text-warning rounded-circle">
                            <i class="bi bi-pause-circle-fill fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabela Principal de Vagas -->
        <div class="row">
            <div class="col-12">
                <div class="card card-outline card-primary shadow-sm mb-4">
                    
                    <div class="card-header bg-transparent py-3 d-flex flex-wrap align-items-center justify-content-between gap-2 border-bottom">
                        <div class="d-flex align-items-center gap-2">
                            <h5 class="card-title fw-bold mb-0 text-body">
                                <i class="bi bi-person-workspace text-primary me-2"></i>Gerenciar Oportunidades Cadastradas
                            </h5>
                        </div>

                        <!-- Filtros Rápidos por Status -->
                        <div class="btn-group btn-group-sm" role="group" aria-label="Filtrar por Status">
                            <button type="button" class="btn btn-outline-secondary active filter-btn" data-filter="all">
                                Todas <span class="badge bg-secondary-subtle text-body ms-1"><?= $total_vagas; ?></span>
                            </button>
                            <button type="button" class="btn btn-outline-success filter-btn" data-filter="active">
                                Abertas <span class="badge bg-success-subtle text-success ms-1"><?= $total_ativas; ?></span>
                            </button>
                            <button type="button" class="btn btn-outline-warning filter-btn" data-filter="inactive">
                                Encerradas <span class="badge bg-warning-subtle text-warning ms-1"><?= $total_inativas; ?></span>
                            </button>
                        </div>
                    </div>

                    <div class="card-body table-responsive">
                        <table id="tabelaVagas" class="table table-hover align-middle w-100">
                            <thead class="table-light">
                                <tr>
                                    <th width="50" class="text-center">#</th>
                                    <th width="75">Foto</th>
                                    <th>Título & Detalhes da Vaga</th>
                                    <th>Local & Carga Horária</th>
                                    <th>Remuneração & Benefícios</th>
                                    <th width="120" class="text-center">Status no Site</th>
                                    <th width="140" class="text-center">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $seq = 0;
                                foreach ($vagas as $row):
                                    $seq++;
                                    $vaga_id = (int)$row['id'];
                                    $is_active = ((int)$row['status'] === 1);
                                    
                                    $foto_src = (!empty($row['foto']) && file_exists('../assets/uploads/' . $row['foto']))
                                        ? BASE_URL . 'assets/uploads/' . $row['foto']
                                        : BASE_URL . 'assets/uploads/vagas/sem-foto.gif';

                                    $salario_fmt = !empty(trim($row['salario'] ?? '')) ? htmlspecialchars($row['salario']) : 'A Combinar';
                                    $local_fmt   = !empty(trim($row['local'] ?? '')) ? htmlspecialchars($row['local']) : 'Geral / Presencial';
                                    $carga_fmt   = !empty(trim($row['carga'] ?? '')) ? htmlspecialchars($row['carga']) : 'A combinar';
                                ?>
                                <tr id="tr-vaga-<?= $vaga_id; ?>" data-status="<?= $is_active ? 'active' : 'inactive'; ?>">
                                    <td class="text-center text-muted fw-semibold"><?= $seq; ?></td>
                                    
                                    <!-- Foto da Vaga -->
                                    <td>
                                        <div class="position-relative d-inline-block rounded overflow-hidden border bg-body" style="width: 60px; height: 60px;">
                                            <img src="<?= $foto_src; ?>" alt="<?= htmlspecialchars($row['titulo']); ?>" class="w-100 h-100 object-fit-cover">
                                        </div>
                                    </td>

                                    <!-- Título & Detalhes -->
                                    <td>
                                        <div class="fw-bold text-body fs-6 mb-1"><?= htmlspecialchars($row['titulo']); ?></div>
                                        <div class="small text-body-secondary text-truncate" style="max-width: 320px;" title="<?= htmlspecialchars(strip_tags($row['requisitos'])); ?>">
                                            <?= htmlspecialchars(mb_strimwidth(strip_tags($row['requisitos']), 0, 90, '...')); ?>
                                        </div>
                                    </td>

                                    <!-- Local & Carga -->
                                    <td>
                                        <div class="small mb-1 text-body">
                                            <i class="bi bi-geo-alt-fill text-danger me-1"></i><?= $local_fmt; ?>
                                        </div>
                                        <div class="small text-body-secondary">
                                            <i class="bi bi-clock-fill text-primary me-1"></i><?= $carga_fmt; ?>
                                        </div>
                                    </td>

                                    <!-- Salário & Benefícios -->
                                    <td>
                                        <div class="mb-1">
                                            <span class="badge bg-success-subtle text-success border border-success-subtle small fw-semibold">
                                                <i class="bi bi-cash me-1"></i><?= $salario_fmt; ?>
                                            </span>
                                        </div>
                                        <?php if (!empty(trim($row['beneficios'] ?? ''))): ?>
                                            <div class="small text-body-secondary text-truncate" style="max-width: 220px;" title="<?= htmlspecialchars(strip_tags($row['beneficios'])); ?>">
                                                <i class="bi bi-gift-fill text-info me-1"></i><?= htmlspecialchars(mb_strimwidth(strip_tags($row['beneficios']), 0, 35, '...')); ?>
                                            </div>
                                        <?php else: ?>
                                            <div class="small text-muted fst-italic">Sem benefícios informados</div>
                                        <?php endif; ?>
                                    </td>

                                    <!-- Status com Switch AJAX -->
                                    <td class="text-center">
                                        <div class="form-check form-switch d-inline-block fs-5 mb-0" title="Clique para ativar ou encerrar esta vaga">
                                            <input class="form-check-input vaga-status-switch" type="checkbox" role="switch"
                                                   data-id="<?= $vaga_id; ?>"
                                                   id="switch-vaga-<?= $vaga_id; ?>"
                                                   <?= $is_active ? 'checked' : ''; ?>>
                                        </div>
                                        <div>
                                            <span class="badge <?= $is_active ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-warning-subtle text-warning border border-warning-subtle'; ?> small fw-semibold" id="badge-status-<?= $vaga_id; ?>">
                                                <?= $is_active ? 'Aberta' : 'Encerrada'; ?>
                                            </span>
                                        </div>
                                    </td>

                                    <!-- Ações -->
                                    <td class="text-center text-nowrap">
                                        <div class="d-flex align-items-center justify-content-center gap-1">
                                            
                                            <!-- Botão Prévia / Detalhes -->
                                            <button type="button" class="btn btn-sm btn-outline-info shadow-xs" 
                                                    title="Visualizar detalhes completos"
                                                    onclick="abrirModalVaga(<?= htmlspecialchars(json_encode([
                                                        'id'         => $vaga_id,
                                                        'titulo'     => $row['titulo'],
                                                        'local'      => $local_fmt,
                                                        'carga'      => $carga_fmt,
                                                        'salario'    => $salario_fmt,
                                                        'beneficios' => $row['beneficios'] ?? '',
                                                        'requisitos' => $row['requisitos'] ?? '',
                                                        'foto'       => $foto_src,
                                                        'status'     => $is_active ? 'Aberta' : 'Encerrada'
                                                    ], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE)); ?>)">
                                                <i class="bi bi-eye"></i>
                                            </button>

                                            <!-- Botão Editar -->
                                            <a href="vagas-edit.php?id=<?= $vaga_id; ?>" class="btn btn-sm btn-primary shadow-xs" title="Editar Vaga">
                                                <i class="bi bi-pencil-square"></i>
                                            </a>

                                            <!-- Botão Excluir -->
                                            <button type="button" class="btn btn-sm btn-danger shadow-xs btn-delete-vaga"
                                                    data-id="<?= $vaga_id; ?>"
                                                    data-titulo="<?= htmlspecialchars($row['titulo']); ?>"
                                                    title="Excluir Vaga">
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

<!-- Modal de Detalhes da Vaga -->
<div class="modal fade" id="modalVagaDetalhes" tabindex="-1" aria-labelledby="modalVagaDetalhesLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content shadow-lg border-0 rounded-4">
            <div class="modal-header bg-primary text-white border-0 py-3">
                <h5 class="modal-title fw-bold d-flex align-items-center gap-2" id="modalVagaDetalhesLabel">
                    <i class="bi bi-briefcase-fill"></i> <span id="mvTitulo">Detalhes da Oportunidade</span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body p-4">
                <div class="row g-4 align-items-start">
                    <div class="col-md-4 text-center">
                        <div class="ratio ratio-1x1 rounded-3 overflow-hidden border shadow-xs mb-3 bg-light">
                            <img id="mvFoto" src="" alt="Foto da vaga" class="w-100 h-100 object-fit-cover">
                        </div>
                        <div id="mvBadgeStatus" class="mb-2"></div>
                    </div>
                    <div class="col-md-8">
                        <div class="row g-2 mb-3">
                            <div class="col-sm-6">
                                <div class="p-2 border rounded bg-light small">
                                    <span class="text-muted d-block"><i class="bi bi-geo-alt-fill text-danger me-1"></i>Local de Trabalho:</span>
                                    <strong id="mvLocal" class="text-dark"></strong>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="p-2 border rounded bg-light small">
                                    <span class="text-muted d-block"><i class="bi bi-clock-fill text-primary me-1"></i>Carga Horária:</span>
                                    <strong id="mvCarga" class="text-dark"></strong>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="p-2 border rounded bg-light small">
                                    <span class="text-muted d-block"><i class="bi bi-cash text-success me-1"></i>Remuneração:</span>
                                    <strong id="mvSalario" class="text-dark"></strong>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <h6 class="fw-bold text-dark border-bottom pb-1"><i class="bi bi-list-check text-primary me-1"></i> Requisitos & Atribuições</h6>
                            <div id="mvRequisitos" class="small text-secondary" style="max-height: 180px; overflow-y: auto;"></div>
                        </div>

                        <div class="mb-0">
                            <h6 class="fw-bold text-dark border-bottom pb-1"><i class="bi bi-gift-fill text-info me-1"></i> Benefícios Oferecidos</h6>
                            <div id="mvBeneficios" class="small text-secondary" style="max-height: 120px; overflow-y: auto;"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light border-0 py-2">
                <a href="#" id="mvLinkEditar" class="btn btn-primary btn-sm"><i class="bi bi-pencil-square me-1"></i> Editar Esta Vaga</a>
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // 1. Inicialização do DataTables com Tradução Completa
    let table = null;
    if (typeof window.jQuery !== 'undefined' && typeof window.jQuery.fn.DataTable !== 'undefined') {
        table = $('#tabelaVagas').DataTable({
            responsive: true,
            language: {
                search: "Buscar Vaga:",
                lengthMenu: "Mostrar _MENU_ vagas",
                info: "Exibindo _START_ a _END_ de _TOTAL_ vagas",
                infoEmpty: "Nenhuma vaga cadastrada",
                infoFiltered: "(filtrado de _MAX_ no total)",
                zeroRecords: "Nenhuma vaga encontrada com esse termo",
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

    // 2. Filtro Rápido por Pílulas (Todas, Abertas, Encerradas)
    const filterButtons = document.querySelectorAll('.filter-btn');
    filterButtons.forEach(btn => {
        btn.addEventListener('click', function () {
            filterButtons.forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            const filter = this.dataset.filter;

            if (table) {
                if (filter === 'all') {
                    table.column(5).search('').draw();
                } else if (filter === 'active') {
                    table.column(5).search('Aberta').draw();
                } else if (filter === 'inactive') {
                    table.column(5).search('Encerrada').draw();
                }
            }
        });
    });

    // 3. Alternância Rápida de Status via AJAX (Sem recarregar página!)
    document.querySelectorAll('.vaga-status-switch').forEach(toggle => {
        toggle.addEventListener('change', function () {
            const vagaId = this.dataset.id;
            const newStatus = this.checked ? 1 : 0;
            const badge = document.getElementById('badge-status-'.concat(vagaId));
            const tr = document.getElementById('tr-vaga-'.concat(vagaId));

            const formData = new FormData();
            formData.append('action', 'toggle_status');
            formData.append('id', vagaId);
            formData.append('status', newStatus);

            fetch(window.location.href, {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    if (newStatus === 1) {
                        badge.className = 'badge bg-success-subtle text-success border border-success-subtle small fw-semibold';
                        badge.textContent = 'Aberta';
                        if (tr) tr.dataset.status = 'active';
                        if (window.toastr) toastr.success('Vaga ativada com sucesso!');
                    } else {
                        badge.className = 'badge bg-warning-subtle text-warning border border-warning-subtle small fw-semibold';
                        badge.textContent = 'Encerrada';
                        if (tr) tr.dataset.status = 'inactive';
                        if (window.toastr) toastr.info('Vaga encerrada/pausada.');
                    }
                    atualizarKPIs();
                } else {
                    toggle.checked = !toggle.checked;
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'error',
                            title: 'Erro ao alterar status',
                            text: data.message || 'Ocorreu um erro no servidor.'
                        });
                    }
                }
            })
            .catch(err => {
                console.error(err);
                toggle.checked = !toggle.checked;
                if (window.toastr) toastr.error('Falha de conexão ao atualizar status.');
            });
        });
    });

    // 4. Exclusão Segura com Confirmação Global SweetAlert2
    document.querySelectorAll('.btn-delete-vaga').forEach(btn => {
        btn.addEventListener('click', function () {
            const vagaId = this.dataset.id;
            const titulo = this.dataset.titulo || 'esta vaga';

            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Deseja excluir esta vaga?',
                    html: `Tem certeza que deseja remover <strong>"${titulo}"</strong>?<br><span class="text-muted small">Esta ação apagará os dados e imagens da vaga permanentemente.</span>`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: '<i class="bi bi-trash3 me-1"></i> Sim, excluir!',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        const formData = new FormData();
                        formData.append('action', 'delete_vaga');
                        formData.append('id', vagaId);

                        fetch(window.location.href, {
                            method: 'POST',
                            body: formData
                        })
                        .then(res => res.json())
                        .then(data => {
                            if (data.status === 'success') {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Excluída!',
                                    text: 'A vaga foi removida com sucesso.',
                                    timer: 1600,
                                    showConfirmButton: false
                                });

                                // Remove a linha da tabela dinamicamente
                                if (table) {
                                    table.row($('#tr-vaga-'.concat(vagaId))).remove().draw();
                                } else {
                                    const tr = document.getElementById('tr-vaga-'.concat(vagaId));
                                    if (tr) tr.remove();
                                }
                                atualizarKPIs();
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Erro ao excluir',
                                    text: data.message || 'Não foi possível excluir a vaga.'
                                });
                            }
                        })
                        .catch(err => {
                            console.error(err);
                            Swal.fire({
                                icon: 'error',
                                title: 'Falha de comunicação',
                                text: 'Não foi possível concluir a exclusão.'
                            });
                        });
                    }
                });
            } else {
                // Fallback de rota tradicional se Swal não estiver carregado
                window.location.href = 'vagas-delete.php?id=' + vagaId;
            }
        });
    });

    // 5. Atualização Dinâmica dos Contadores de KPIs
    function atualizarKPIs() {
        const rows = document.querySelectorAll('#tabelaVagas tbody tr[data-status]');
        let total = rows.length;
        let ativas = 0;
        let inativas = 0;

        rows.forEach(r => {
            if (r.dataset.status === 'active') ativas++;
            else inativas++;
        });

        const kpiTotal = document.getElementById('kpiTotal');
        const kpiAtivas = document.getElementById('kpiAtivas');
        const kpiInativas = document.getElementById('kpiInativas');

        if (kpiTotal) kpiTotal.textContent = total;
        if (kpiAtivas) kpiAtivas.textContent = ativas;
        if (kpiInativas) kpiInativas.textContent = inativas;
    }

    // 6. Função Global para o Modal de Prévia de Detalhes
    window.abrirModalVaga = function(data) {
        document.getElementById('mvTitulo').textContent = data.titulo || 'Detalhes da Oportunidade';
        document.getElementById('mvFoto').src = data.foto || '';
        document.getElementById('mvLocal').textContent = data.local || 'Presencial / Geral';
        document.getElementById('mvCarga').textContent = data.carga || 'A combinar';
        document.getElementById('mvSalario').textContent = data.salario || 'A combinar';
        
        document.getElementById('mvRequisitos').innerHTML = data.requisitos || '<em class="text-muted">Nenhum requisito detalhado</em>';
        document.getElementById('mvBeneficios').innerHTML = data.beneficios || '<em class="text-muted">Nenhum benefício informado</em>';
        
        const badgeBox = document.getElementById('mvBadgeStatus');
        if (data.status === 'Aberta') {
            badgeBox.innerHTML = '<span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-1">Vaga Aberta</span>';
        } else {
            badgeBox.innerHTML = '<span class="badge bg-warning-subtle text-warning border border-warning-subtle px-3 py-1">Vaga Encerrada</span>';
        }

        const editLink = document.getElementById('mvLinkEditar');
        if (editLink) editLink.href = 'vagas-edit.php?id=' + data.id;

        const modalEl = document.getElementById('modalVagaDetalhes');
        if (modalEl && typeof bootstrap !== 'undefined') {
            const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
            modal.show();
        }
    };
});
</script>

<?php require_once('footer.php'); ?>
