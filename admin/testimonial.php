<?php
require_once('header.php');

// 1. Endpoint AJAX: Excluir Depoimento com Remoção Segura de Foto
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_testimonial') {
    header('Content-Type: application/json; charset=utf-8');
    $id = (int)($_POST['id'] ?? 0);

    if ($id > 0) {
        $stmt_find = $pdo->prepare("SELECT photo FROM tbl_testimonial WHERE id=?");
        $stmt_find->execute([$id]);
        $depoimento = $stmt_find->fetch(PDO::FETCH_ASSOC);

        if ($depoimento) {
            if (!empty($depoimento['photo']) && file_exists('../assets/uploads/' . $depoimento['photo'])) {
                @unlink('../assets/uploads/' . $depoimento['photo']);
            }
            $stmt_del = $pdo->prepare("DELETE FROM tbl_testimonial WHERE id=?");
            $stmt_del->execute([$id]);
            echo json_encode(['status' => 'success', 'message' => 'Depoimento excluído com sucesso!']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Depoimento não encontrado no banco de dados.']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Identificador inválido.']);
    }
    exit;
}

// 2. Consulta de Depoimentos e Indicadores Estatísticos (KPIs)
$statement = $pdo->prepare("SELECT id, name, designation, company, photo, comment, status FROM tbl_testimonial ORDER BY id DESC");
$statement->execute();
$testimonials = $statement->fetchAll(PDO::FETCH_ASSOC);

$total_depoimentos = count($testimonials);
$total_ativos      = 0;
$total_inativos    = 0;

foreach ($testimonials as $t) {
    if (($t['status'] ?? 'Active') === 'Active') {
        $total_ativos++;
    } else {
        $total_inativos++;
    }
}
?>

<!-- Cabeçalho da Página -->
<div class="app-content-header pb-2 mb-3 border-bottom">
    <div class="container-fluid">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div>
                <h3 class="mb-1 fw-bold">Depoimentos & Avaliações de Pacientes</h3>
                <ol class="breadcrumb mb-0 small">
                    <li class="breadcrumb-item"><a href="index.php"><i class="bi bi-house-door"></i> Início</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Depoimentos</li>
                </ol>
            </div>
            <div class="d-flex flex-wrap align-items-center gap-2">
                <a href="<?= BASE_URL; ?>#depoimentos" target="_blank" rel="noopener" class="btn btn-outline-info btn-sm shadow-xs" title="Ver seção de depoimentos na página inicial">
                    <i class="bi bi-box-arrow-up-right me-1"></i> Ver no Site
                </a>
                <a href="testimonial-add.php" class="btn btn-primary btn-sm shadow-xs px-3">
                    <i class="bi bi-plus-lg me-1"></i> Adicionar Depoimento
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
                            <div class="text-body-secondary small text-uppercase fw-bold">Total de Depoimentos</div>
                            <div class="fs-3 fw-bold text-dark mt-1" id="kpiTotal"><?= $total_depoimentos; ?></div>
                        </div>
                        <div class="p-3 bg-primary-subtle text-primary rounded-circle">
                            <i class="bi bi-chat-heart-fill fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-xl-4">
                <div class="card shadow-sm border-0 border-start border-success border-4 h-100 bg-body">
                    <div class="card-body d-flex align-items-center justify-content-between py-3">
                        <div>
                            <div class="text-body-secondary small text-uppercase fw-bold">Ativos no Site</div>
                            <div class="fs-3 fw-bold text-success mt-1" id="kpiAtivos"><?= $total_ativos; ?></div>
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
                            <div class="text-body-secondary small text-uppercase fw-bold">Ocultos / Inativos</div>
                            <div class="fs-3 fw-bold text-warning mt-1" id="kpiInativos"><?= $total_inativos; ?></div>
                        </div>
                        <div class="p-3 bg-warning-subtle text-warning rounded-circle">
                            <i class="bi bi-pause-circle-fill fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabela Principal de Depoimentos -->
        <div class="row">
            <div class="col-12">
                <div class="card card-outline card-primary shadow-sm mb-4">
                    
                    <div class="card-header bg-transparent py-3 d-flex flex-wrap align-items-center justify-content-between gap-2 border-bottom">
                        <div class="d-flex align-items-center gap-2">
                            <h5 class="card-title fw-bold mb-0 text-body">
                                <i class="bi bi-star-fill text-warning me-2"></i>Gerenciar Depoimentos Cadastrados
                            </h5>
                        </div>

                        <!-- Filtros Rápidos por Status -->
                        <div class="btn-group btn-group-sm" role="group" aria-label="Filtrar por Status">
                            <button type="button" class="btn btn-outline-secondary active filter-btn" data-filter="all">
                                Todos <span class="badge bg-secondary-subtle text-body ms-1"><?= $total_depoimentos; ?></span>
                            </button>
                            <button type="button" class="btn btn-outline-success filter-btn" data-filter="active">
                                Ativos <span class="badge bg-success-subtle text-success ms-1"><?= $total_ativos; ?></span>
                            </button>
                            <button type="button" class="btn btn-outline-warning filter-btn" data-filter="inactive">
                                Inativos <span class="badge bg-warning-subtle text-warning ms-1"><?= $total_inativos; ?></span>
                            </button>
                        </div>
                    </div>

                    <div class="card-body table-responsive">
                        <table id="tabelaDepoimentos" class="table table-hover align-middle w-100">
                            <thead class="table-light">
                                <tr>
                                    <th width="45" class="text-center">#</th>
                                    <th width="75" class="text-center">Avatar</th>
                                    <th>Paciente</th>
                                    <th>Serviço Avaliado</th>
                                    <th>Origem / Cidade</th>
                                    <th>Depoimento</th>
                                    <th width="120" class="text-center">Status</th>
                                    <th width="130" class="text-center">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $seq = 0;
                                foreach ($testimonials as $row):
                                    $seq++;
                                    $dep_id = (int)$row['id'];
                                    $is_active = (($row['status'] ?? 'Active') === 'Active');
                                    
                                    $foto_src = (!empty($row['photo']) && file_exists('../assets/uploads/' . $row['photo']))
                                        ? BASE_URL . 'assets/uploads/' . $row['photo']
                                        : BASE_URL . 'assets/uploads/testimonial-default.png';

                                    $paciente_nome = htmlspecialchars($row['name']);
                                    $servico_fmt   = !empty(trim($row['designation'] ?? '')) ? htmlspecialchars($row['designation']) : 'Atendimento Geral';
                                    $origem_fmt    = !empty(trim($row['company'] ?? '')) ? htmlspecialchars($row['company']) : 'Rio de Janeiro / RJ';
                                    $comentario    = $row['comment'] ?? '';
                                ?>
                                <tr id="tr-dep-<?= $dep_id; ?>" data-status="<?= $is_active ? 'active' : 'inactive'; ?>">
                                    <td class="text-center text-muted fw-semibold"><?= $seq; ?></td>
                                    
                                    <!-- Avatar do Paciente -->
                                    <td class="text-center">
                                        <div class="position-relative d-inline-block rounded-circle overflow-hidden border shadow-xs bg-body" style="width: 52px; height: 52px;">
                                            <img src="<?= $foto_src; ?>" alt="<?= $paciente_nome; ?>" class="w-100 h-100 object-fit-cover" onerror="this.src='<?= BASE_URL; ?>assets/uploads/testimonial-default.png';">
                                        </div>
                                    </td>

                                    <!-- Nome do Paciente -->
                                    <td>
                                        <div class="fw-bold text-body fs-6 mb-0"><?= $paciente_nome; ?></div>
                                        <div class="text-warning small" style="font-size: 0.8rem;">
                                            <i class="bi bi-star-fill"></i>
                                            <i class="bi bi-star-fill"></i>
                                            <i class="bi bi-star-fill"></i>
                                            <i class="bi bi-star-fill"></i>
                                            <i class="bi bi-star-fill"></i>
                                            <span class="text-muted ms-1" style="font-size: 0.72rem;">(5.0)</span>
                                        </div>
                                    </td>

                                    <!-- Serviço Avaliado -->
                                    <td>
                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle fw-semibold small">
                                            <i class="bi bi-heart-pulse me-1"></i><?= $servico_fmt; ?>
                                        </span>
                                    </td>

                                    <!-- Origem / Cidade -->
                                    <td>
                                        <div class="small text-body-secondary">
                                            <i class="bi bi-geo-alt-fill text-danger me-1"></i><?= $origem_fmt; ?>
                                        </div>
                                    </td>

                                    <!-- Depoimento (Truncado com Estilo) -->
                                    <td>
                                        <div class="small text-body fst-italic text-truncate" style="max-width: 280px;" title="<?= htmlspecialchars(strip_tags($comentario)); ?>">
                                            &ldquo;<?= htmlspecialchars(mb_strimwidth(strip_tags($comentario), 0, 85, '...')); ?>&rdquo;
                                        </div>
                                    </td>

                                    <!-- Status com Switch AJAX Global -->
                                    <td class="text-center">
                                        <div class="d-inline-flex align-items-center gap-2">
                                            <div class="form-check form-switch mb-0" title="Ativar ou desativar este depoimento">
                                                <input class="form-check-input pronto-status-switch" type="checkbox" role="switch"
                                                       data-entity="testimonial"
                                                       data-id="<?= $dep_id; ?>"
                                                       data-name="<?= $paciente_nome; ?>"
                                                       id="switch-dep-<?= $dep_id; ?>"
                                                       <?= $is_active ? 'checked' : ''; ?>>
                                            </div>
                                            <span class="badge status-badge <?= $is_active ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-warning-subtle text-warning border border-warning-subtle'; ?> rounded-pill small" id="badge-status-<?= $dep_id; ?>">
                                                <?= $is_active ? 'Ativo' : 'Inativo'; ?>
                                            </span>
                                        </div>
                                    </td>

                                    <!-- Ações -->
                                    <td class="text-center text-nowrap">
                                        <div class="d-flex align-items-center justify-content-center gap-1">
                                            
                                            <!-- Botão Prévia / Detalhes -->
                                            <button type="button" class="btn btn-sm btn-outline-info shadow-xs" 
                                                    title="Visualizar avaliação completa"
                                                    onclick="abrirModalDepoimento(<?= htmlspecialchars(json_encode([
                                                        'id'          => $dep_id,
                                                        'name'        => $row['name'],
                                                        'designation' => $servico_fmt,
                                                        'company'     => $origem_fmt,
                                                        'comment'     => $row['comment'] ?? '',
                                                        'photo'       => $foto_src,
                                                        'status'      => $is_active ? 'Ativo' : 'Inativo'
                                                    ], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE)); ?>)">
                                                <i class="bi bi-eye"></i>
                                            </button>

                                            <!-- Botão Editar -->
                                            <a href="testimonial-edit.php?id=<?= $dep_id; ?>" class="btn btn-sm btn-primary shadow-xs" title="Editar Depoimento">
                                                <i class="bi bi-pencil-square"></i>
                                            </a>

                                            <!-- Botão Excluir -->
                                            <button type="button" class="btn btn-sm btn-danger shadow-xs btn-delete-testimonial"
                                                    data-id="<?= $dep_id; ?>"
                                                    data-name="<?= $paciente_nome; ?>"
                                                    title="Excluir Depoimento">
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

<!-- Modal Elegante de Prévia do Depoimento (Card de Avaliação) -->
<div class="modal fade" id="modalDepoimentoDetalhes" tabindex="-1" aria-labelledby="modalDepoimentoDetalhesLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow-lg border-0 rounded-4 overflow-hidden">
            <div class="modal-header bg-primary text-white border-0 py-3">
                <h5 class="modal-title fw-bold d-flex align-items-center gap-2" id="modalDepoimentoDetalhesLabel">
                    <i class="bi bi-chat-quote-fill"></i> Avaliação do Paciente
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body p-4 bg-light">
                
                <div class="card border-0 shadow-sm rounded-3 p-3 bg-body mb-3">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="position-relative rounded-circle overflow-hidden border shadow-xs" style="width: 64px; height: 64px; flex-shrink: 0;">
                            <img id="mdFoto" src="" alt="Avatar" class="w-100 h-100 object-fit-cover">
                        </div>
                        <div class="flex-grow-1 min-w-0">
                            <h5 class="fw-bold text-dark mb-1 text-truncate" id="mdNome"></h5>
                            <div class="d-flex flex-wrap align-items-center gap-2">
                                <span class="badge bg-primary-subtle text-primary border small" id="mdDesignation"></span>
                                <span class="text-body-secondary small" id="mdCompany"><i class="bi bi-geo-alt me-1"></i></span>
                            </div>
                        </div>
                    </div>

                    <div class="text-warning mb-2 fs-6">
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                        <span class="text-muted small ms-1 fw-semibold">Avaliação Excelente (5/5)</span>
                    </div>

                    <div class="p-3 bg-light rounded-3 border-start border-primary border-3 fst-italic text-secondary" id="mdComentario" style="font-size: 0.95rem; line-height: 1.6; max-height: 220px; overflow-y: auto;">
                    </div>

                    <div class="d-flex align-items-center justify-content-between mt-3 pt-2 border-top">
                        <span class="small text-muted"><i class="bi bi-shield-check text-success me-1"></i> Depoimento Verificado</span>
                        <div id="mdBadgeStatus"></div>
                    </div>
                </div>

            </div>
            <div class="modal-footer bg-body border-0 py-2">
                <a href="#" id="mdLinkEditar" class="btn btn-primary btn-sm px-3"><i class="bi bi-pencil-square me-1"></i> Editar Depoimento</a>
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // 1. Inicialização do DataTables com Tradução
    let table = null;
    if (typeof window.jQuery !== 'undefined' && typeof window.jQuery.fn.DataTable !== 'undefined') {
        table = $('#tabelaDepoimentos').DataTable({
            responsive: true,
            language: {
                search: "Buscar Depoimento:",
                lengthMenu: "Mostrar _MENU_ registros",
                info: "Exibindo _START_ a _END_ de _TOTAL_ depoimentos",
                infoEmpty: "Nenhum depoimento cadastrado",
                infoFiltered: "(filtrado de _MAX_ no total)",
                zeroRecords: "Nenhum depoimento encontrado",
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

    // 2. Filtro Rápido por Pílulas (Todos, Ativos, Inativos)
    const filterButtons = document.querySelectorAll('.filter-btn');
    filterButtons.forEach(btn => {
        btn.addEventListener('click', function () {
            filterButtons.forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            const filter = this.dataset.filter;

            if (table) {
                if (filter === 'all') {
                    table.column(6).search('').draw();
                } else if (filter === 'active') {
                    table.column(6).search('Ativo').draw();
                } else if (filter === 'inactive') {
                    table.column(6).search('Inativo').draw();
                }
            }
        });
    });

    // 3. Ouvinte para atualização dos KPIs após toggle do switch
    document.addEventListener('change', function (e) {
        if (e.target && e.target.classList.contains('pronto-status-switch')) {
            const tr = e.target.closest('tr');
            if (tr) {
                tr.setAttribute('data-status', e.target.checked ? 'active' : 'inactive');
                setTimeout(atualizarKPIs, 300);
            }
        }
    });

    // 4. Exclusão Segura com SweetAlert2 Global
    document.querySelectorAll('.btn-delete-testimonial').forEach(btn => {
        btn.addEventListener('click', function () {
            const depId = this.dataset.id;
            const nome = this.dataset.name || 'este depoimento';

            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Deseja excluir este depoimento?',
                    html: `Tem certeza que deseja remover a avaliação de <strong>"${nome}"</strong>?<br><span class="text-muted small">Esta ação apagará a foto e o texto permanentemente.</span>`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: '<i class="bi bi-trash3 me-1"></i> Sim, excluir!',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        const formData = new FormData();
                        formData.append('action', 'delete_testimonial');
                        formData.append('id', depId);

                        fetch(window.location.href, {
                            method: 'POST',
                            body: formData
                        })
                        .then(res => res.json())
                        .then(data => {
                            if (data.status === 'success') {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Excluído!',
                                    text: 'O depoimento foi removido com sucesso.',
                                    timer: 1600,
                                    showConfirmButton: false
                                });

                                // Remove a linha dinamicamente
                                if (table) {
                                    table.row($('#tr-dep-'.concat(depId))).remove().draw();
                                } else {
                                    const tr = document.getElementById('tr-dep-'.concat(depId));
                                    if (tr) tr.remove();
                                }
                                atualizarKPIs();
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Erro ao excluir',
                                    text: data.message || 'Não foi possível excluir o depoimento.'
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
                // Fallback tradicional
                window.location.href = 'testimonial-delete.php?id=' + depId;
            }
        });
    });

    // 5. Atualização Dinâmica dos Contadores de KPIs
    function atualizarKPIs() {
        const rows = document.querySelectorAll('#tabelaDepoimentos tbody tr[data-status]');
        let total = rows.length;
        let ativos = 0;
        let inativos = 0;

        rows.forEach(r => {
            if (r.getAttribute('data-status') === 'active') ativos++;
            else inativos++;
        });

        const kpiTotal = document.getElementById('kpiTotal');
        const kpiAtivos = document.getElementById('kpiAtivos');
        const kpiInativos = document.getElementById('kpiInativos');

        if (kpiTotal) kpiTotal.textContent = total;
        if (kpiAtivos) kpiAtivos.textContent = ativos;
        if (kpiInativos) kpiInativos.textContent = inativos;
    }

    // 6. Função Global para o Modal de Prévia de Depoimento
    window.abrirModalDepoimento = function(data) {
        document.getElementById('mdNome').textContent = data.name || 'Paciente';
        document.getElementById('mdFoto').src = data.photo || '';
        document.getElementById('mdDesignation').textContent = data.designation || 'Atendimento Geral';
        document.getElementById('mdCompany').innerHTML = '<i class="bi bi-geo-alt me-1"></i>' + (data.company || 'Rio de Janeiro / RJ');
        document.getElementById('mdComentario').innerHTML = '&ldquo;' + (data.comment || 'Nenhum comentário') + '&rdquo;';
        
        const badgeBox = document.getElementById('mdBadgeStatus');
        if (data.status === 'Ativo') {
            badgeBox.innerHTML = '<span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 small">Ativo no Site</span>';
        } else {
            badgeBox.innerHTML = '<span class="badge bg-warning-subtle text-warning border border-warning-subtle px-2 py-1 small">Oculto</span>';
        }

        const editLink = document.getElementById('mdLinkEditar');
        if (editLink) editLink.href = 'testimonial-edit.php?id=' + data.id;

        const modalEl = document.getElementById('modalDepoimentoDetalhes');
        if (modalEl && typeof bootstrap !== 'undefined') {
            const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
            modal.show();
        }
    };
});
</script>

<?php require_once('footer.php'); ?>
