<?php
require_once('header.php');

// 1. Endpoint AJAX: Excluir Parceiro/Convênio com Remoção de Foto
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_partner') {
    header('Content-Type: application/json; charset=utf-8');
    $id = (int)($_POST['id'] ?? 0);

    if ($id > 0) {
        $stmt_find = $pdo->prepare("SELECT photo FROM tbl_partner WHERE id=?");
        $stmt_find->execute([$id]);
        $partner = $stmt_find->fetch(PDO::FETCH_ASSOC);

        if ($partner) {
            if (!empty($partner['photo']) && file_exists('../assets/uploads/' . $partner['photo'])) {
                @unlink('../assets/uploads/' . $partner['photo']);
            }
            $stmt_del = $pdo->prepare("DELETE FROM tbl_partner WHERE id=?");
            $stmt_del->execute([$id]);
            echo json_encode(['status' => 'success', 'message' => 'Convênio/Parceiro removido com sucesso!']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Convênio não encontrado no banco de dados.']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Identificador inválido.']);
    }
    exit;
}

// 2. Consulta de Parceiros e Indicadores Estatísticos (KPIs)
$statement = $pdo->prepare("SELECT id, name, url, photo FROM tbl_partner ORDER BY id DESC");
$statement->execute();
$partners = $statement->fetchAll(PDO::FETCH_ASSOC);

$total_parceiros = count($partners);
$total_com_link  = 0;
$total_com_logo  = 0;

foreach ($partners as $p) {
    if (!empty(trim($p['url'] ?? '')) && $p['url'] !== '#') {
        $total_com_link++;
    }
    if (!empty($p['photo']) && file_exists('../assets/uploads/' . $p['photo'])) {
        $total_com_logo++;
    }
}
?>

<!-- Cabeçalho da Página -->
<div class="app-content-header pb-2 mb-3 border-bottom">
    <div class="container-fluid">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div>
                <h3 class="mb-1 fw-bold">Convênios Médicos & Parceiros</h3>
                <ol class="breadcrumb mb-0 small">
                    <li class="breadcrumb-item"><a href="index.php"><i class="bi bi-house-door"></i> Início</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Convênios e Parceiros</li>
                </ol>
            </div>
            <div class="d-flex flex-wrap align-items-center gap-2">
                <a href="<?= BASE_URL; ?>#convenios" target="_blank" rel="noopener" class="btn btn-outline-info btn-sm shadow-xs" title="Ver convênios na página inicial">
                    <i class="bi bi-box-arrow-up-right me-1"></i> Ver no Site
                </a>
                <a href="partner-add.php" class="btn btn-primary btn-sm shadow-xs px-3">
                    <i class="bi bi-plus-lg me-1"></i> Adicionar Parceiro
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
                            <div class="text-body-secondary small text-uppercase fw-bold">Total de Convênios</div>
                            <div class="fs-3 fw-bold text-dark mt-1" id="kpiTotal"><?= $total_parceiros; ?></div>
                        </div>
                        <div class="p-3 bg-primary-subtle text-primary rounded-circle">
                            <i class="bi bi-shield-check fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-xl-4">
                <div class="card shadow-sm border-0 border-start border-success border-4 h-100 bg-body">
                    <div class="card-body d-flex align-items-center justify-content-between py-3">
                        <div>
                            <div class="text-body-secondary small text-uppercase fw-bold">Com Portal / Link Oficial</div>
                            <div class="fs-3 fw-bold text-success mt-1" id="kpiLinks"><?= $total_com_link; ?></div>
                        </div>
                        <div class="p-3 bg-success-subtle text-success rounded-circle">
                            <i class="bi bi-link-45deg fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-xl-4">
                <div class="card shadow-sm border-0 border-start border-info border-4 h-100 bg-body">
                    <div class="card-body d-flex align-items-center justify-content-between py-3">
                        <div>
                            <div class="text-body-secondary small text-uppercase fw-bold">Logos em Alta Resolução</div>
                            <div class="fs-3 fw-bold text-info mt-1" id="kpiLogos"><?= $total_com_logo; ?></div>
                        </div>
                        <div class="p-3 bg-info-subtle text-info rounded-circle">
                            <i class="bi bi-card-image fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabela Principal de Parceiros -->
        <div class="row">
            <div class="col-12">
                <div class="card card-outline card-primary shadow-sm mb-4">
                    
                    <div class="card-header bg-transparent py-3 d-flex flex-wrap align-items-center justify-content-between gap-2 border-bottom">
                        <div class="d-flex align-items-center gap-2">
                            <h5 class="card-title fw-bold mb-0 text-body">
                                <i class="bi bi-handshake text-primary me-2"></i>Operadoras & Convênios Atendidos
                            </h5>
                        </div>
                        <span class="badge bg-secondary-subtle text-secondary small">
                            Dimensão Recomendada: 300 x 120 px (Fundo Transparente)
                        </span>
                    </div>

                    <div class="card-body table-responsive">
                        <table id="tabelaParceiros" class="table table-hover align-middle w-100">
                            <thead class="table-light">
                                <tr>
                                    <th width="45" class="text-center">#</th>
                                    <th width="140" class="text-center">Logotipo</th>
                                    <th>Convênio / Parceiro</th>
                                    <th>Website / Portal do Beneficiário</th>
                                    <th width="130" class="text-center">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $seq = 0;
                                foreach ($partners as $row):
                                    $seq++;
                                    $part_id = (int)$row['id'];
                                    
                                    $has_logo = (!empty($row['photo']) && file_exists('../assets/uploads/' . $row['photo']));
                                    $foto_src = $has_logo
                                        ? BASE_URL . 'assets/uploads/' . $row['photo']
                                        : BASE_URL . 'assets/uploads/partner-default.png';

                                    $nome_fmt = htmlspecialchars($row['name'] ?? 'Convênio #' . $part_id);
                                    $url_fmt  = trim($row['url'] ?? '');
                                    $has_url  = (!empty($url_fmt) && $url_fmt !== '#');
                                ?>
                                <tr id="tr-partner-<?= $part_id; ?>">
                                    <td class="text-center text-muted fw-semibold"><?= $seq; ?></td>
                                    
                                    <!-- Logotipo em Fundo Branco / Transparente -->
                                    <td class="text-center">
                                        <div class="position-relative d-inline-block rounded-3 p-2 border bg-white shadow-xs" style="width: 120px; height: 55px;">
                                            <img src="<?= $foto_src; ?>" alt="<?= $nome_fmt; ?>" class="w-100 h-100 object-fit-contain" onerror="this.src='<?= BASE_URL; ?>assets/uploads/partner-default.png';">
                                        </div>
                                    </td>

                                    <!-- Nome do Convênio -->
                                    <td>
                                        <div class="fw-bold text-body fs-6 mb-1"><?= $nome_fmt; ?></div>
                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle small">
                                            <i class="bi bi-check-circle-fill me-1 text-success"></i>Convênio Aceito
                                        </span>
                                    </td>

                                    <!-- Link do Portal -->
                                    <td>
                                        <?php if ($has_url): ?>
                                            <a href="<?= htmlspecialchars($url_fmt); ?>" target="_blank" rel="noopener" class="btn btn-sm btn-outline-secondary rounded-pill px-3 shadow-xs" title="<?= htmlspecialchars($url_fmt); ?>">
                                                <i class="bi bi-box-arrow-up-right me-1 text-primary"></i>
                                                <span class="d-inline-block text-truncate align-middle" style="max-width: 250px;">
                                                    <?= htmlspecialchars($url_fmt); ?>
                                                </span>
                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted small fst-italic">
                                                <i class="bi bi-dash-circle me-1"></i>Portal não informado
                                            </span>
                                        <?php endif; ?>
                                    </td>

                                    <!-- Ações -->
                                    <td class="text-center text-nowrap">
                                        <div class="d-flex align-items-center justify-content-center gap-1">
                                            
                                            <!-- Botão Prévia / Modal -->
                                            <button type="button" class="btn btn-sm btn-outline-info shadow-xs" 
                                                    title="Visualizar detalhes do convênio"
                                                    onclick="abrirModalParceiro(<?= htmlspecialchars(json_encode([
                                                        'id'    => $part_id,
                                                        'name'  => $nome_fmt,
                                                        'url'   => $has_url ? $url_fmt : '',
                                                        'photo' => $foto_src
                                                    ], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE)); ?>)">
                                                <i class="bi bi-eye"></i>
                                            </button>

                                            <!-- Botão Editar -->
                                            <a href="partner-edit.php?id=<?= $part_id; ?>" class="btn btn-sm btn-primary shadow-xs" title="Editar Convênio">
                                                <i class="bi bi-pencil-square"></i>
                                            </a>

                                            <!-- Botão Excluir -->
                                            <button type="button" class="btn btn-sm btn-danger shadow-xs btn-delete-partner"
                                                    data-id="<?= $part_id; ?>"
                                                    data-name="<?= $nome_fmt; ?>"
                                                    title="Excluir Convênio">
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

<!-- Modal Elegante de Detalhes do Convênio / Parceiro -->
<div class="modal fade" id="modalParceiroDetalhes" tabindex="-1" aria-labelledby="modalParceiroDetalhesLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow-lg border-0 rounded-4 overflow-hidden">
            <div class="modal-header bg-primary text-white border-0 py-3">
                <h5 class="modal-title fw-bold d-flex align-items-center gap-2" id="modalParceiroDetalhesLabel">
                    <i class="bi bi-shield-check"></i> Detalhes do Convênio / Parceiro
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body p-4 bg-light text-center">
                
                <div class="card border-0 shadow-sm rounded-3 p-4 bg-white mb-3">
                    <div class="d-flex align-items-center justify-content-center p-3 border rounded-3 bg-light mb-3" style="min-height: 110px;">
                        <img id="mpFoto" src="" alt="Logotipo" class="img-fluid" style="max-height: 90px; object-fit: contain;">
                    </div>

                    <h4 class="fw-bold text-dark mb-2" id="mpNome"></h4>
                    
                    <div class="mb-3">
                        <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2 rounded-pill small">
                            <i class="bi bi-check-circle-fill me-1"></i>Convênio Ativo & Homologado
                        </span>
                    </div>

                    <div id="mpUrlContainer" class="p-2 border rounded bg-light mb-0">
                        <div class="small text-muted mb-1">Portal do Beneficiário:</div>
                        <a href="#" id="mpLinkUrl" target="_blank" rel="noopener" class="fw-semibold text-primary text-break small">
                            <i class="bi bi-box-arrow-up-right me-1"></i><span id="mpUrlTexto">Acessar Portal</span>
                        </a>
                    </div>
                </div>

            </div>
            <div class="modal-footer bg-body border-0 py-2 d-flex justify-content-between">
                <span class="small text-muted"><i class="bi bi-info-circle me-1"></i> Pronto Saúde</span>
                <div>
                    <a href="#" id="mpLinkEditar" class="btn btn-primary btn-sm px-3"><i class="bi bi-pencil-square me-1"></i> Editar Dados</a>
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Fechar</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // 1. Inicialização do DataTables com Tradução Completa
    let table = null;
    if (typeof window.jQuery !== 'undefined' && typeof window.jQuery.fn.DataTable !== 'undefined') {
        table = $('#tabelaParceiros').DataTable({
            responsive: true,
            language: {
                search: "Buscar Convênio:",
                lengthMenu: "Mostrar _MENU_ registros",
                info: "Exibindo _START_ a _END_ de _TOTAL_ convênios",
                infoEmpty: "Nenhum convênio cadastrado",
                infoFiltered: "(filtrado de _MAX_ no total)",
                zeroRecords: "Nenhum convênio encontrado",
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

    // 2. Exclusão Segura com Confirmação SweetAlert2 via AJAX
    document.querySelectorAll('.btn-delete-partner').forEach(btn => {
        btn.addEventListener('click', function () {
            const partId = this.dataset.id;
            const nome = this.dataset.name || 'este convênio';

            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Deseja excluir este parceiro?',
                    html: `Tem certeza que deseja remover <strong>"${nome}"</strong>?<br><span class="text-danger small">O logotipo também será apagado do servidor.</span>`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: '<i class="bi bi-trash3 me-1"></i> Sim, excluir!',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        const formData = new FormData();
                        formData.append('action', 'delete_partner');
                        formData.append('id', partId);

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
                                    text: 'O convênio foi removido com sucesso.',
                                    timer: 1600,
                                    showConfirmButton: false
                                });

                                // Remove a linha da tabela na hora
                                if (table) {
                                    table.row($('#tr-partner-'.concat(partId))).remove().draw();
                                } else {
                                    const tr = document.getElementById('tr-partner-'.concat(partId));
                                    if (tr) tr.remove();
                                }
                                atualizarKPIs();
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Erro ao excluir',
                                    text: data.message || 'Não foi possível excluir o convênio.'
                                });
                            }
                        })
                        .catch(err => {
                            console.error(err);
                            Swal.fire({
                                icon: 'error',
                                title: 'Falha de comunicação',
                                text: 'Não foi possível concluir a exclusão do parceiro.'
                            });
                        });
                    }
                });
            } else {
                // Fallback tradicional
                window.location.href = 'partner-delete.php?id=' + partId;
            }
        });
    });

    // 3. Atualização Dinâmica do Contador de KPIs
    function atualizarKPIs() {
        const rows = document.querySelectorAll('#tabelaParceiros tbody tr');
        let total = rows.length;
        const kpiTotal = document.getElementById('kpiTotal');
        if (kpiTotal) kpiTotal.textContent = total;
    }

    // 4. Função Global para o Modal de Prévia do Parceiro
    window.abrirModalParceiro = function(data) {
        document.getElementById('mpNome').textContent = data.name || 'Convênio';
        document.getElementById('mpFoto').src = data.photo || '';
        
        const urlBox = document.getElementById('mpUrlContainer');
        const urlLink = document.getElementById('mpLinkUrl');
        const urlTexto = document.getElementById('mpUrlTexto');

        if (data.url && data.url.trim() !== '') {
            urlLink.href = data.url;
            urlTexto.textContent = data.url;
            urlBox.style.display = 'block';
        } else {
            urlBox.style.display = 'none';
        }

        const editLink = document.getElementById('mpLinkEditar');
        if (editLink) editLink.href = 'partner-edit.php?id=' + data.id;

        const modalEl = document.getElementById('modalParceiroDetalhes');
        if (modalEl && typeof bootstrap !== 'undefined') {
            const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
            modal.show();
        }
    };
});
</script>

<?php require_once('footer.php'); ?>
