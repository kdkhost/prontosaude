<?php
require_once('header.php');

// 1. Endpoint AJAX: Excluir Slide com Remoção de Imagem do Servidor
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_slider') {
    header('Content-Type: application/json; charset=utf-8');
    $id = (int)($_POST['id'] ?? 0);

    if ($id > 0) {
        $stmt_find = $pdo->prepare("SELECT photo FROM tbl_slider WHERE id=?");
        $stmt_find->execute([$id]);
        $slider = $stmt_find->fetch(PDO::FETCH_ASSOC);

        if ($slider) {
            if (!empty($slider['photo']) && file_exists('../assets/uploads/' . $slider['photo'])) {
                @unlink('../assets/uploads/' . $slider['photo']);
            }
            $stmt_del = $pdo->prepare("DELETE FROM tbl_slider WHERE id=?");
            $stmt_del->execute([$id]);
            echo json_encode(['status' => 'success', 'message' => 'Slide removido com sucesso!']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Slide não encontrado no banco de dados.']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Identificador inválido.']);
    }
    exit;
}

// 2. Consulta de Sliders e Indicadores Estatísticos (KPIs)
$statement = $pdo->prepare("SELECT id, photo, heading, subheading, content, button_text, button_url, position, status FROM tbl_slider ORDER BY id DESC");
$statement->execute();
$sliders = $statement->fetchAll(PDO::FETCH_ASSOC);

$total_sliders = count($sliders);
$total_ativos  = 0;
$total_inativos = 0;

foreach ($sliders as $s) {
    if (($s['status'] ?? 'Active') === 'Active') {
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
                <h3 class="mb-1 fw-bold">Carrossel Principal (Hero Sliders)</h3>
                <ol class="breadcrumb mb-0 small">
                    <li class="breadcrumb-item"><a href="index.php"><i class="bi bi-house-door"></i> Início</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Hero Sliders</li>
                </ol>
            </div>
            <div class="d-flex flex-wrap align-items-center gap-2">
                <a href="<?= BASE_URL; ?>" target="_blank" rel="noopener" class="btn btn-outline-info btn-sm shadow-xs" title="Ver carrossel ao vivo na página inicial">
                    <i class="bi bi-box-arrow-up-right me-1"></i> Ver no Site
                </a>
                <a href="slider-add.php" class="btn btn-primary btn-sm shadow-xs px-3">
                    <i class="bi bi-plus-lg me-1"></i> Adicionar Novo Slide
                </a>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">

        <!-- Mensagens de Sessão Tradicionais (Fallback) -->
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
                            <div class="text-body-secondary small text-uppercase fw-bold">Total de Banners</div>
                            <div class="fs-3 fw-bold text-dark mt-1" id="kpiTotal"><?= $total_sliders; ?></div>
                        </div>
                        <div class="p-3 bg-primary-subtle text-primary rounded-circle">
                            <i class="bi bi-images fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-xl-4">
                <div class="card shadow-sm border-0 border-start border-success border-4 h-100 bg-body">
                    <div class="card-body d-flex align-items-center justify-content-between py-3">
                        <div>
                            <div class="text-body-secondary small text-uppercase fw-bold">Ativos no Carrossel</div>
                            <div class="fs-3 fw-bold text-success mt-1" id="kpiAtivos"><?= $total_ativos; ?></div>
                        </div>
                        <div class="p-3 bg-success-subtle text-success rounded-circle">
                            <i class="bi bi-play-circle-fill fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-xl-4">
                <div class="card shadow-sm border-0 border-start border-warning border-4 h-100 bg-body">
                    <div class="card-body d-flex align-items-center justify-content-between py-3">
                        <div>
                            <div class="text-body-secondary small text-uppercase fw-bold">Pausados / Ocultos</div>
                            <div class="fs-3 fw-bold text-warning mt-1" id="kpiInativos"><?= $total_inativos; ?></div>
                        </div>
                        <div class="p-3 bg-warning-subtle text-warning rounded-circle">
                            <i class="bi bi-pause-circle-fill fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabela Principal de Sliders -->
        <div class="row">
            <div class="col-12">
                <div class="card card-outline card-primary shadow-sm mb-4">
                    
                    <div class="card-header bg-transparent py-3 d-flex flex-wrap align-items-center justify-content-between gap-2 border-bottom">
                        <div class="d-flex align-items-center gap-2">
                            <h5 class="card-title fw-bold mb-0 text-body">
                                <i class="bi bi-collection-play text-primary me-2"></i>Slides de Destaque da Página Inicial
                            </h5>
                            <span class="badge bg-secondary-subtle text-secondary small d-none d-md-inline-block">Dimensão: 1920 x 800 px</span>
                        </div>

                        <!-- Filtros Rápidos por Status -->
                        <div class="btn-group btn-group-sm" role="group" aria-label="Filtrar por Status">
                            <button type="button" class="btn btn-outline-secondary active filter-btn" data-filter="all">
                                Todos <span class="badge bg-secondary-subtle text-body ms-1"><?= $total_sliders; ?></span>
                            </button>
                            <button type="button" class="btn btn-outline-success filter-btn" data-filter="active">
                                Ativos <span class="badge bg-success-subtle text-success ms-1"><?= $total_ativos; ?></span>
                            </button>
                            <button type="button" class="btn btn-outline-warning filter-btn" data-filter="inactive">
                                Pausados <span class="badge bg-warning-subtle text-warning ms-1"><?= $total_inativas = $total_inativos; ?></span>
                            </button>
                        </div>
                    </div>

                    <div class="card-body table-responsive">
                        <table id="tabelaSliders" class="table table-hover align-middle w-100">
                            <thead class="table-light">
                                <tr>
                                    <th width="45" class="text-center">#</th>
                                    <th width="150">Banner (Foto)</th>
                                    <th>Título & Conteúdo do Slide</th>
                                    <th width="160">Botão de Ação (CTA)</th>
                                    <th width="100" class="text-center">Alinhamento</th>
                                    <th width="120" class="text-center">Status no Site</th>
                                    <th width="130" class="text-center">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $seq = 0;
                                foreach ($sliders as $row):
                                    $seq++;
                                    $slider_id = (int)$row['id'];
                                    $is_active = (($row['status'] ?? 'Active') === 'Active');
                                    
                                    $foto_src = (!empty($row['photo']) && file_exists('../assets/uploads/' . $row['photo']))
                                        ? BASE_URL . 'assets/uploads/' . $row['photo']
                                        : BASE_URL . 'assets/uploads/department-1.png';

                                    $heading_fmt    = htmlspecialchars($row['heading'] ?? 'Slide #' . $slider_id);
                                    $subheading_fmt = htmlspecialchars($row['subheading'] ?? '');
                                    $content_fmt    = htmlspecialchars($row['content'] ?? '');
                                    $btn_text_fmt   = !empty(trim($row['button_text'] ?? '')) ? htmlspecialchars($row['button_text']) : 'Ver Mais';
                                    $btn_url_fmt    = !empty(trim($row['button_url'] ?? '')) ? htmlspecialchars($row['button_url']) : '#';
                                    
                                    $pos_label = ($row['position'] == 'Center') ? 'Centro' : (($row['position'] == 'Right') ? 'Direita' : 'Esquerda');
                                    $pos_badge_class = ($row['position'] == 'Center') ? 'bg-primary-subtle text-primary border border-primary-subtle' : (($row['position'] == 'Right') ? 'bg-info-subtle text-info border border-info-subtle' : 'bg-secondary-subtle text-secondary border');
                                ?>
                                <tr id="tr-slider-<?= $slider_id; ?>" data-status="<?= $is_active ? 'active' : 'inactive'; ?>">
                                    <td class="text-center text-muted fw-semibold"><?= $seq; ?></td>
                                    
                                    <!-- Banner em Miniatura Panorâmica -->
                                    <td>
                                        <div class="position-relative rounded-3 overflow-hidden border shadow-xs bg-dark" style="width: 140px; height: 68px;">
                                            <img src="<?= $foto_src; ?>" alt="<?= $heading_fmt; ?>" class="w-100 h-100 object-fit-cover">
                                            <span class="position-absolute bottom-0 end-0 badge bg-dark bg-opacity-75 text-white" style="font-size: 0.65rem; border-top-left-radius: 4px;">
                                                <i class="bi bi-aspect-ratio me-1"></i>Hero
                                            </span>
                                        </div>
                                    </td>

                                    <!-- Título & Textos do Slide -->
                                    <td>
                                        <div class="fw-bold text-body fs-6 mb-1"><?= $heading_fmt; ?></div>
                                        <?php if (!empty($subheading_fmt)): ?>
                                            <div class="small text-primary fw-semibold mb-1"><?= $subheading_fmt; ?></div>
                                        <?php endif; ?>
                                        <?php if (!empty($content_fmt)): ?>
                                            <div class="small text-body-secondary text-truncate" style="max-width: 320px;" title="<?= $content_fmt; ?>">
                                                <?= htmlspecialchars(mb_strimwidth(strip_tags($row['content']), 0, 85, '...')); ?>
                                            </div>
                                        <?php endif; ?>
                                    </td>

                                    <!-- Botão de Ação (CTA) -->
                                    <td>
                                        <?php if (!empty($row['button_text'])): ?>
                                            <div class="mb-1">
                                                <span class="badge bg-primary text-white shadow-xs">
                                                    <i class="bi bi-cursor-fill me-1"></i><?= $btn_text_fmt; ?>
                                                </span>
                                            </div>
                                            <?php if ($btn_url_fmt !== '#'): ?>
                                                <a href="<?= $btn_url_fmt; ?>" target="_blank" rel="noopener" class="small text-decoration-none text-truncate d-inline-block" style="max-width: 150px;" title="<?= $btn_url_fmt; ?>">
                                                    <i class="bi bi-box-arrow-up-right me-1"></i><?= $btn_url_fmt; ?>
                                                </a>
                                            <?php else: ?>
                                                <span class="small text-muted">Link interno / #</span>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <span class="small text-muted fst-italic">Sem botão CTA</span>
                                        <?php endif; ?>
                                    </td>

                                    <!-- Posição / Alinhamento -->
                                    <td class="text-center">
                                        <span class="badge <?= $pos_badge_class; ?> small fw-semibold">
                                            <i class="bi <?= ($row['position'] == 'Center') ? 'bi-text-center' : (($row['position'] == 'Right') ? 'bi-text-right' : 'bi-text-left'); ?> me-1"></i>
                                            <?= $pos_label; ?>
                                        </span>
                                    </td>

                                    <!-- Status com Switch AJAX Global -->
                                    <td class="text-center">
                                        <div class="d-inline-flex align-items-center gap-2">
                                            <div class="form-check form-switch mb-0" title="Ativar ou pausar este slide na Home">
                                                <input class="form-check-input pronto-status-switch" type="checkbox" role="switch"
                                                       data-entity="slider"
                                                       data-id="<?= $slider_id; ?>"
                                                       data-name="<?= $heading_fmt; ?>"
                                                       id="switch-slider-<?= $slider_id; ?>"
                                                       <?= $is_active ? 'checked' : ''; ?>>
                                            </div>
                                            <span class="badge status-badge <?= $is_active ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-warning-subtle text-warning border border-warning-subtle'; ?> rounded-pill small" id="badge-status-<?= $slider_id; ?>">
                                                <?= $is_active ? 'Ativo' : 'Pausado'; ?>
                                            </span>
                                        </div>
                                    </td>

                                    <!-- Ações -->
                                    <td class="text-center text-nowrap">
                                        <div class="d-flex align-items-center justify-content-center gap-1">
                                            
                                            <!-- Botão Prévia Hero (Simulador do Slide) -->
                                            <button type="button" class="btn btn-sm btn-outline-info shadow-xs" 
                                                    title="Simular visualização deste slide"
                                                    onclick="abrirModalSlider(<?= htmlspecialchars(json_encode([
                                                        'id'          => $slider_id,
                                                        'heading'     => $heading_fmt,
                                                        'subheading'  => $subheading_fmt,
                                                        'content'     => $row['content'] ?? '',
                                                        'button_text' => $btn_text_fmt,
                                                        'button_url'  => $btn_url_fmt,
                                                        'position'    => $row['position'] ?? 'Left',
                                                        'photo'       => $foto_src,
                                                        'status'      => $is_active ? 'Ativo' : 'Pausado'
                                                    ], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE)); ?>)">
                                                <i class="bi bi-eye"></i>
                                            </button>

                                            <!-- Botão Editar -->
                                            <a href="slider-edit.php?id=<?= $slider_id; ?>" class="btn btn-sm btn-primary shadow-xs" title="Editar Slide">
                                                <i class="bi bi-pencil-square"></i>
                                            </a>

                                            <!-- Botão Excluir -->
                                            <button type="button" class="btn btn-sm btn-danger shadow-xs btn-delete-slider"
                                                    data-id="<?= $slider_id; ?>"
                                                    data-name="<?= $heading_fmt; ?>"
                                                    title="Excluir Slide">
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

<!-- Modal de Simulação do Hero Slide (Prévia em Tempo Real) -->
<div class="modal fade" id="modalSliderPreview" tabindex="-1" aria-labelledby="modalSliderPreviewLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content shadow-lg border-0 rounded-4 overflow-hidden">
            <div class="modal-header bg-dark text-white border-0 py-3">
                <h5 class="modal-title fw-bold d-flex align-items-center gap-2" id="modalSliderPreviewLabel">
                    <i class="bi bi-display text-primary"></i> Simulação de Visualização do Hero Banner
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            
            <div class="modal-body p-0 bg-dark position-relative">
                
                <!-- Mockup do Slide Hero -->
                <div id="heroPreviewBox" class="position-relative text-white d-flex align-items-center" style="min-height: 400px; background-size: cover; background-position: center;">
                    <!-- Overlay Gradiente -->
                    <div class="position-absolute top-0 start-0 w-100 h-100" style="background: linear-gradient(135deg, rgba(15,23,42,0.88) 0%, rgba(15,23,42,0.65) 100%);"></div>

                    <div class="container position-relative py-5">
                        <div class="row" id="heroRowAlignment">
                            <div class="col-12 col-md-9 col-lg-8" id="heroColAlignment">
                                
                                <span class="badge bg-primary text-white px-3 py-2 rounded-pill fw-semibold mb-2 shadow-sm" id="heroSubheadingPreview">
                                    Subtítulo do Slide
                                </span>

                                <h2 class="display-6 fw-bold mb-3 text-white" id="heroHeadingPreview">
                                    Título Principal de Destaque
                                </h2>

                                <p class="lead text-white-50 fs-6 mb-4" id="heroContentPreview">
                                    Descrição detalhada e orientações de atendimento para os pacientes na página inicial da Pronto Saúde.
                                </p>

                                <div id="heroButtonWrap">
                                    <a href="#" class="btn btn-primary btn-lg shadow px-4 py-2 fw-semibold" id="heroBtnPreview">
                                        <span id="heroBtnText">Agendar Consulta</span> <i class="bi bi-arrow-right ms-1"></i>
                                    </a>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <div class="modal-footer bg-body border-0 py-2 d-flex justify-content-between">
                <div class="small text-muted">
                    <i class="bi bi-info-circle me-1"></i> Imagem recomendada para produção: <strong>1920 x 800 px (Paisagem panorâmica)</strong>.
                </div>
                <div>
                    <a href="#" id="modalLinkEditar" class="btn btn-primary btn-sm px-3"><i class="bi bi-pencil-square me-1"></i> Editar Este Slide</a>
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
        table = $('#tabelaSliders').DataTable({
            responsive: true,
            language: {
                search: "Buscar Slide:",
                lengthMenu: "Mostrar _MENU_ slides",
                info: "Exibindo _START_ a _END_ de _TOTAL_ slides",
                infoEmpty: "Nenhum slide cadastrado",
                infoFiltered: "(filtrado de _MAX_ no total)",
                zeroRecords: "Nenhum slide encontrado com esse termo",
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

    // 2. Filtro Rápido por Pílulas (Todos, Ativos, Pausados)
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
                    table.column(5).search('Ativo').draw();
                } else if (filter === 'inactive') {
                    table.column(5).search('Pausado').draw();
                }
            }
        });
    });

    // 3. Atualização Dinâmica de KPIs após Toggle de Status
    document.addEventListener('change', function (e) {
        if (e.target && e.target.classList.contains('pronto-status-switch')) {
            const tr = e.target.closest('tr');
            if (tr) {
                tr.setAttribute('data-status', e.target.checked ? 'active' : 'inactive');
                setTimeout(atualizarKPIs, 300);
            }
        }
    });

    // 4. Exclusão Segura com Confirmação SweetAlert2 via AJAX
    document.querySelectorAll('.btn-delete-slider').forEach(btn => {
        btn.addEventListener('click', function () {
            const sliderId = this.dataset.id;
            const sliderName = this.dataset.name || 'este slide';

            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Deseja excluir este slider?',
                    html: `Tem certeza que deseja remover <strong>"${sliderName}"</strong>?<br><span class="text-danger small">A imagem do banner também será apagada do servidor.</span>`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: '<i class="bi bi-trash3 me-1"></i> Sim, excluir!',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        const formData = new FormData();
                        formData.append('action', 'delete_slider');
                        formData.append('id', sliderId);

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
                                    text: 'O slide foi removido com sucesso.',
                                    timer: 1600,
                                    showConfirmButton: false
                                });

                                // Remove a linha da tabela na hora
                                if (table) {
                                    table.row($('#tr-slider-'.concat(sliderId))).remove().draw();
                                } else {
                                    const tr = document.getElementById('tr-slider-'.concat(sliderId));
                                    if (tr) tr.remove();
                                }
                                atualizarKPIs();
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Erro ao excluir',
                                    text: data.message || 'Não foi possível excluir o slide.'
                                });
                            }
                        })
                        .catch(err => {
                            console.error(err);
                            Swal.fire({
                                icon: 'error',
                                title: 'Falha de comunicação',
                                text: 'Não foi possível concluir a exclusão do slide.'
                            });
                        });
                    }
                });
            } else {
                // Fallback tradicional
                window.location.href = 'slider-delete.php?id=' + sliderId;
            }
        });
    });

    // 5. Atualização Dinâmica dos Contadores de KPIs
    function atualizarKPIs() {
        const rows = document.querySelectorAll('#tabelaSliders tbody tr[data-status]');
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

    // 6. Função Global para o Modal de Simulação Hero Slide
    window.abrirModalSlider = function(data) {
        const heroBox = document.getElementById('heroPreviewBox');
        const headingEl = document.getElementById('heroHeadingPreview');
        const subheadingEl = document.getElementById('heroSubheadingPreview');
        const contentEl = document.getElementById('heroContentPreview');
        const btnTextEl = document.getElementById('heroBtnText');
        const btnLinkEl = document.getElementById('heroBtnPreview');
        const btnWrapEl = document.getElementById('heroButtonWrap');
        const rowAlign = document.getElementById('heroRowAlignment');
        const colAlign = document.getElementById('heroColAlignment');

        if (heroBox) heroBox.style.backgroundImage = 'url("' + (data.photo || '') + '")';
        if (headingEl) headingEl.textContent = data.heading || '';
        
        if (subheadingEl) {
            if (data.subheading && data.subheading.trim() !== '') {
                subheadingEl.textContent = data.subheading;
                subheadingEl.style.display = 'inline-block';
            } else {
                subheadingEl.style.display = 'none';
            }
        }

        if (contentEl) {
            if (data.content && data.content.trim() !== '') {
                contentEl.innerHTML = data.content;
                contentEl.style.display = 'block';
            } else {
                contentEl.style.display = 'none';
            }
        }

        if (btnTextEl && btnLinkEl && btnWrapEl) {
            if (data.button_text && data.button_text.trim() !== '') {
                btnTextEl.textContent = data.button_text;
                btnLinkEl.href = data.button_url || '#';
                btnWrapEl.style.display = 'block';
            } else {
                btnWrapEl.style.display = 'none';
            }
        }

        // Alinhamento Visual
        if (rowAlign && colAlign) {
            if (data.position === 'Center') {
                rowAlign.className = 'row justify-content-center text-center';
                colAlign.className = 'col-12 col-md-10 col-lg-8';
            } else if (data.position === 'Right') {
                rowAlign.className = 'row justify-content-end text-end';
                colAlign.className = 'col-12 col-md-9 col-lg-8';
            } else {
                rowAlign.className = 'row justify-content-start text-start';
                colAlign.className = 'col-12 col-md-9 col-lg-8';
            }
        }

        const editLink = document.getElementById('modalLinkEditar');
        if (editLink) editLink.href = 'slider-edit.php?id=' + data.id;

        const modalEl = document.getElementById('modalSliderPreview');
        if (modalEl && typeof bootstrap !== 'undefined') {
            const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
            modal.show();
        }
    };
});
</script>

<?php require_once('footer.php'); ?>
