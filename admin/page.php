<?php
require_once('header.php');

// Sincroniza páginas institucionais nativas do sistema com tbl_page
if (function_exists('pronto_sync_system_pages')) {
    pronto_sync_system_pages($pdo);
}

// Endpoint AJAX para alternar status da página (Active / Inactive) em tempo real
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'toggle_page_status') {
    while (ob_get_level()) {
        ob_end_clean();
    }
    header('Content-Type: application/json; charset=utf-8');
    $page_id = (int)($_POST['page_id'] ?? 0);
    $new_status = ($_POST['status'] ?? '') === 'Active' ? 'Active' : 'Inactive';

    if ($page_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Identificador de página inválido.']);
        exit;
    }

    try {
        $stmt_chk = $pdo->prepare("SELECT page_name, page_slug FROM tbl_page WHERE page_id = ?");
        $stmt_chk->execute([$page_id]);
        $page_info = $stmt_chk->fetch(PDO::FETCH_ASSOC);

        if (!$page_info) {
            echo json_encode(['success' => false, 'message' => 'Página não encontrada no sistema.']);
            exit;
        }

        $stmt = $pdo->prepare("UPDATE tbl_page SET status = ? WHERE page_id = ?");
        $stmt->execute([$new_status, $page_id]);

        $status_label = ($new_status === 'Active') ? 'ativada e visível' : 'desativada e oculta';
        echo json_encode([
            'success' => true,
            'page_id' => $page_id,
            'new_status' => $new_status,
            'page_name' => $page_info['page_name'],
            'message' => "A página \"{$page_info['page_name']}\" foi {$status_label} com sucesso no site!"
        ]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Erro ao atualizar status: ' . $e->getMessage()]);
    }
    exit;
}

// Slugs conhecidos de páginas do sistema
$system_slugs = ['doctor', 'servicos', 'fidelidade', 'odontologia', 'news', 'trabalhe-conosco', 'convenio', 'privacidade', 'rsocial'];

// Carrega todas as páginas
$statement = $pdo->prepare("SELECT * FROM tbl_page ORDER BY page_id ASC");
$statement->execute();
$pages = $statement->fetchAll(PDO::FETCH_ASSOC);

$total_pages = count($pages);
$total_active = 0;
$total_inactive = 0;
$total_system = 0;

foreach ($pages as $p) {
    if ($p['status'] === 'Active') {
        $total_active++;
    } else {
        $total_inactive++;
    }
    $clean_slug = strtolower(trim(str_replace('.php', '', $p['page_slug'])));
    if (in_array($clean_slug, $system_slugs)) {
        $total_system++;
    }
}
?>

<div class="app-content-header">
	<div class="container-fluid">
		<div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
			<div>
				<h3 class="mb-1 fw-bold d-flex align-items-center gap-2">
					<i class="bi bi-file-earmark-text-fill text-primary"></i> Gestor de Páginas & Controle de Visibilidade
				</h3>
				<ol class="breadcrumb mb-0 small">
					<li class="breadcrumb-item"><a href="index.php"><i class="bi bi-house-door"></i> Início</a></li>
					<li class="breadcrumb-item active" aria-current="page">Gestor de Páginas</li>
				</ol>
			</div>
			<div class="d-flex flex-wrap align-items-center gap-2">
				<a href="page-add.php" class="btn btn-primary btn-sm d-inline-flex align-items-center gap-1 shadow-sm">
					<i class="bi bi-plus-lg"></i> Adicionar Nova Página
				</a>
				<a href="seo.php" class="btn btn-outline-info btn-sm d-inline-flex align-items-center gap-1 shadow-sm">
					<i class="bi bi-graph-up-arrow"></i> Auditoria de SEO
				</a>
			</div>
		</div>
	</div>
</div>

<div class="app-content">
	<div class="container-fluid">

		<!-- Cards de Indicadores Rápidos -->
		<div class="row g-3 mb-4">
			<div class="col-12 col-sm-6 col-xl-3">
				<div class="card border-0 shadow-sm rounded-4 h-100 border-start border-4 border-primary">
					<div class="card-body p-3 d-flex align-items-center justify-content-between">
						<div>
							<div class="text-body-secondary small fw-semibold">Total de Páginas</div>
							<div class="fs-4 fw-bold text-body" id="counterTotal"><?= $total_pages ?></div>
						</div>
						<div class="rounded-circle p-3 bg-primary-subtle text-primary fs-4">
							<i class="bi bi-collection-fill"></i>
						</div>
					</div>
				</div>
			</div>
			<div class="col-12 col-sm-6 col-xl-3">
				<div class="card border-0 shadow-sm rounded-4 h-100 border-start border-4 border-success">
					<div class="card-body p-3 d-flex align-items-center justify-content-between">
						<div>
							<div class="text-body-secondary small fw-semibold">Páginas Ativas no Site</div>
							<div class="fs-4 fw-bold text-success" id="counterActive"><?= $total_active ?></div>
						</div>
						<div class="rounded-circle p-3 bg-success-subtle text-success fs-4">
							<i class="bi bi-eye-fill"></i>
						</div>
					</div>
				</div>
			</div>
			<div class="col-12 col-sm-6 col-xl-3">
				<div class="card border-0 shadow-sm rounded-4 h-100 border-start border-4 border-danger">
					<div class="card-body p-3 d-flex align-items-center justify-content-between">
						<div>
							<div class="text-body-secondary small fw-semibold">Páginas Desativadas (Ocultas)</div>
							<div class="fs-4 fw-bold text-danger" id="counterInactive"><?= $total_inactive ?></div>
						</div>
						<div class="rounded-circle p-3 bg-danger-subtle text-danger fs-4">
							<i class="bi bi-eye-slash-fill"></i>
						</div>
					</div>
				</div>
			</div>
			<div class="col-12 col-sm-6 col-xl-3">
				<div class="card border-0 shadow-sm rounded-4 h-100 border-start border-4 border-info">
					<div class="card-body p-3 d-flex align-items-center justify-content-between">
						<div>
							<div class="text-body-secondary small fw-semibold">Páginas do Sistema</div>
							<div class="fs-4 fw-bold text-info"><?= $total_system ?></div>
						</div>
						<div class="rounded-circle p-3 bg-info-subtle text-info fs-4">
							<i class="bi bi-shield-check"></i>
						</div>
					</div>
				</div>
			</div>
		</div>

		<!-- Alerta de Orientação -->
		<div class="alert alert-info bg-info-subtle border-info-subtle text-info-emphasis d-flex align-items-center gap-3 p-3 rounded-4 shadow-sm mb-4">
			<i class="bi bi-info-circle-fill fs-3 text-info flex-shrink-0"></i>
			<div class="small">
				<strong>Controle de Visibilidade em Tempo Real:</strong> Ao desligar o interruptor (switch) de qualquer página (inclusive <strong>Médicos</strong>, <strong>Serviços</strong>, <strong>Fidelidade</strong> ou institucionais), ela é <strong>ocultada imediatamente</strong> dos menus de navegação (desktop e mobile offcanvas), do rodapé e da Home, e qualquer tentativa de acesso direto à URL é redirecionada com segurança para a página inicial.
			</div>
		</div>

		<!-- Tabela Principal com Filtros e Switches AJAX -->
		<div class="card card-outline card-primary shadow-sm rounded-4 mb-4">
			<div class="card-header bg-body-tertiary p-3">
				<div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
					<!-- Filtros de Busca e Status -->
					<div class="d-flex flex-wrap align-items-center gap-2 flex-grow-1" style="max-width: 600px;">
						<div class="input-group input-group-sm">
							<span class="input-group-text bg-body border-end-0"><i class="bi bi-search text-body-secondary"></i></span>
							<input type="text" class="form-control border-start-0" id="searchPagesInput" placeholder="Pesquisar por nome da página, slug ou layout...">
						</div>
					</div>
					<!-- Botões de Filtragem Rápida -->
					<div class="btn-group btn-group-sm" role="group" id="filterStatusButtons">
						<button type="button" class="btn btn-outline-secondary active" data-filter="all">Todas (<?= $total_pages ?>)</button>
						<button type="button" class="btn btn-outline-success" data-filter="active">Ativas (<?= $total_active ?>)</button>
						<button type="button" class="btn btn-outline-danger" data-filter="inactive">Desativadas (<?= $total_inactive ?>)</button>
						<button type="button" class="btn btn-outline-primary" data-filter="system">Sistema (<?= $total_system ?>)</button>
					</div>
				</div>
			</div>

			<div class="card-body table-responsive p-0">
				<table id="tablePages" class="table table-hover table-striped align-middle mb-0 w-100">
					<thead class="table-light">
						<tr>
							<th style="width: 60px;" class="text-center"># ID</th>
							<th>Nome da Página</th>
							<th>Slug / URL</th>
							<th>Layout</th>
							<th style="width: 220px;" class="text-center">Status no Site</th>
							<th style="width: 170px;" class="text-center">Ações</th>
						</tr>
					</thead>
					<tbody id="pagesTableBody">
						<?php
						$i = 0;
						foreach ($pages as $row):
							$i++;
							$clean_slug = strtolower(trim(str_replace('.php', '', $row['page_slug'])));
							$is_system = in_array($clean_slug, $system_slugs);
							$is_active = ($row['status'] === 'Active');

							// Determina a URL no portal público
							if ($is_system) {
								$live_url = (strpos($row['page_slug'], '.php') !== false) ? BASE_URL . $row['page_slug'] : BASE_URL . $row['page_slug'] . '.php';
							} else {
								$live_url = (strpos($row['page_slug'], '.php') !== false) ? BASE_URL . $row['page_slug'] : BASE_URL . 'page/' . $row['page_slug'];
							}
						?>
						<tr class="page-row" data-id="<?= $row['page_id']; ?>" data-status="<?= $is_active ? 'active' : 'inactive'; ?>" data-system="<?= $is_system ? 'system' : 'custom'; ?>">
							<td class="text-center text-muted fw-semibold"><?= $row['page_id']; ?></td>
							<td>
								<div class="fw-bold text-body page-name-cell"><?= htmlspecialchars($row['page_name']); ?></div>
								<div class="d-flex align-items-center gap-1 mt-1">
									<?php if ($is_system): ?>
										<span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill small" title="Página nativa do sistema">
											<i class="bi bi-shield-fill-check me-1"></i> Sistema
										</span>
									<?php else: ?>
										<span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill small" title="Página dinâmica criada pelo painel">
											<i class="bi bi-file-earmark me-1"></i> Personalizada
										</span>
									<?php endif; ?>
									<?php if (!empty($row['banner'])): ?>
										<span class="badge bg-light text-muted border rounded-pill small">
											<i class="bi bi-image me-1"></i> Banner
										</span>
									<?php endif; ?>
								</div>
							</td>
							<td>
								<code class="text-primary small fw-semibold"><?= htmlspecialchars($row['page_slug']); ?></code>
								<div class="mt-1">
									<a href="<?= $live_url; ?>" target="_blank" class="small text-body-secondary text-decoration-none d-inline-flex align-items-center gap-1" title="Abrir no portal">
										<i class="bi bi-link-45deg"></i> Visualizar link
									</a>
								</div>
							</td>
							<td>
								<span class="badge bg-light text-dark border small text-wrap text-start">
									<?= htmlspecialchars($row['page_layout'] ?: 'Full Width Page Layout'); ?>
								</span>
							</td>
							<td class="text-center">
								<div class="d-flex flex-column align-items-center justify-content-center gap-1">
									<!-- Switch Interativo AJAX -->
									<div class="form-check form-switch fs-5 mb-0">
										<input class="form-check-input switch-page-status" 
										       type="checkbox" 
										       role="switch" 
										       id="switch_page_<?= $row['page_id']; ?>" 
										       data-page-id="<?= $row['page_id']; ?>" 
										       data-page-name="<?= htmlspecialchars($row['page_name']); ?>"
										       <?= $is_active ? 'checked' : ''; ?>>
									</div>
									<span class="badge <?= $is_active ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-danger-subtle text-danger border border-danger-subtle'; ?> status-badge rounded-pill small" id="badge_status_<?= $row['page_id']; ?>">
										<?= $is_active ? 'Ativa no Site' : 'Desativada'; ?>
									</span>
								</div>
							</td>
							<td class="text-center text-nowrap">
								<div class="d-inline-flex align-items-center gap-1">
									<a href="page-edit.php?id=<?= $row['page_id']; ?>" class="btn btn-outline-primary btn-sm" title="Editar Página e Conteúdo">
										<i class="bi bi-pencil-square"></i> Editar
									</a>
									<?php if (!$is_system): ?>
										<button type="button" class="btn btn-outline-danger btn-sm btn-delete-page" data-page-id="<?= $row['page_id']; ?>" data-page-name="<?= htmlspecialchars($row['page_name']); ?>" title="Excluir Página">
											<i class="bi bi-trash3"></i>
										</button>
									<?php else: ?>
										<button type="button" class="btn btn-outline-secondary btn-sm" disabled title="Páginas do sistema não podem ser excluídas, apenas desativadas">
											<i class="bi bi-lock-fill"></i>
										</button>
									<?php endif; ?>
								</div>
							</td>
						</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>

			<div class="card-footer bg-body-tertiary p-3 d-flex flex-wrap align-items-center justify-content-between gap-2">
				<span class="text-body-secondary small">
					<i class="bi bi-shield-lock text-primary me-1"></i> Páginas com ícone de cadeado são essenciais para o funcionamento do portal e podem ser ativadas ou desativadas a qualquer instante.
				</span>
				<span class="badge bg-secondary-subtle text-body border" id="visibleRowCount">
					Exibindo <?= $total_pages ?> páginas
				</span>
			</div>
		</div>

	</div>
</div>

<script>
/**
 * Gestão de Páginas & Controle de Visibilidade em Tempo Real
 * Suporte a SPA, switch AJAX, Toastr e SweetAlert2
 */
(function() {
	window.initPageManager = function(container) {
		container = container || document;
		const tableBody = container.querySelector('#pagesTableBody');
		if (!tableBody) return;

		// Evita binds duplicados
		if (tableBody.dataset.pageInit === 'true') return;
		tableBody.dataset.pageInit = 'true';

		const searchInput = container.querySelector('#searchPagesInput');
		const filterButtons = container.querySelectorAll('#filterStatusButtons button');
		const visibleCounter = container.querySelector('#visibleRowCount');
		const counterActive = container.querySelector('#counterActive');
		const counterInactive = container.querySelector('#counterInactive');

		let currentFilter = 'all';

		function filterTable() {
			const query = (searchInput ? searchInput.value : '').toLowerCase().trim();
			const rows = tableBody.querySelectorAll('.page-row');
			let visibleCount = 0;

			rows.forEach(row => {
				const status = row.getAttribute('data-status');
				const system = row.getAttribute('data-system');
				const text = row.textContent.toLowerCase();

				const matchesSearch = text.includes(query);
				let matchesFilter = true;

				if (currentFilter === 'active') matchesFilter = (status === 'active');
				else if (currentFilter === 'inactive') matchesFilter = (status === 'inactive');
				else if (currentFilter === 'system') matchesFilter = (system === 'system');

				if (matchesSearch && matchesFilter) {
					row.style.display = '';
					visibleCount++;
				} else {
					row.style.display = 'none';
				}
			});

			if (visibleCounter) {
				visibleCounter.textContent = `Exibindo ${visibleCount} páginas`;
			}
		}

		if (searchInput) {
			searchInput.addEventListener('input', filterTable);
		}

		filterButtons.forEach(btn => {
			btn.addEventListener('click', function() {
				filterButtons.forEach(b => b.classList.remove('active'));
				this.classList.add('active');
				currentFilter = this.getAttribute('data-filter') || 'all';
				filterTable();
			});
		});

		// Switch Toggle AJAX para ativar/desativar página
		container.querySelectorAll('.switch-page-status').forEach(switchEl => {
			switchEl.addEventListener('change', function() {
				const pageId = this.getAttribute('data-page-id');
				const pageName = this.getAttribute('data-page-name') || 'Página';
				const newStatus = this.checked ? 'Active' : 'Inactive';
				const badge = container.querySelector('#badge_status_' + pageId);
				const row = this.closest('.page-row');

				// Feedback visual temporário
				if (badge) {
					badge.textContent = 'Salvando...';
					badge.className = 'badge bg-warning-subtle text-warning border border-warning-subtle rounded-pill small';
				}
				this.disabled = true;

				const formData = new FormData();
				formData.append('entity', 'page');
				formData.append('id', pageId);
				formData.append('status', newStatus);

				fetch('toggle-ajax.php', {
					method: 'POST',
					body: formData
				})
				.then(res => {
					if (!res.ok) throw new Error('HTTP ' + res.status);
					return res.json();
				})
				.then(data => {
					this.disabled = false;
					if (data.success) {
						if (badge) {
							if (newStatus === 'Active') {
								badge.textContent = 'Ativa no Site';
								badge.className = 'badge bg-success-subtle text-success border border-success-subtle status-badge rounded-pill small';
								if (row) row.setAttribute('data-status', 'active');
							} else {
								badge.textContent = 'Desativada';
								badge.className = 'badge bg-danger-subtle text-danger border border-danger-subtle status-badge rounded-pill small';
								if (row) row.setAttribute('data-status', 'inactive');
							}
						}

						// Atualiza contadores do topo
						updateSummaryCounters();

						if (window.notificar) {
							window.notificar(newStatus === 'Active' ? 'success' : 'warning', data.message, newStatus === 'Active' ? 'Página Ativada' : 'Página Ocultada');
						} else if (typeof toastr !== 'undefined') {
							toastr[newStatus === 'Active' ? 'success' : 'warning'](data.message);
						}
					} else {
						// Reverte switch
						this.checked = !this.checked;
						if (badge) {
							badge.textContent = this.checked ? 'Ativa no Site' : 'Desativada';
							badge.className = this.checked ? 'badge bg-success-subtle text-success border border-success-subtle status-badge rounded-pill small' : 'badge bg-danger-subtle text-danger border border-danger-subtle status-badge rounded-pill small';
						}
						if (window.notificar) {
							window.notificar('error', data.message || 'Erro ao alterar status da página.', 'Atenção');
						} else if (typeof toastr !== 'undefined') {
							toastr.error(data.message);
						}
					}
				})
				.catch(err => {
					this.disabled = false;
					this.checked = !this.checked;
					if (badge) {
						badge.textContent = this.checked ? 'Ativa no Site' : 'Desativada';
						badge.className = this.checked ? 'badge bg-success-subtle text-success border border-success-subtle status-badge rounded-pill small' : 'badge bg-danger-subtle text-danger border border-danger-subtle status-badge rounded-pill small';
					}
					if (window.notificar) {
						window.notificar('error', 'Falha na comunicação com o servidor.', 'Erro');
					} else if (typeof toastr !== 'undefined') {
						toastr.error('Falha de conexão.');
					}
				});
			});
		});

		function updateSummaryCounters() {
			const activeCount = tableBody.querySelectorAll('.page-row[data-status="active"]').length;
			const inactiveCount = tableBody.querySelectorAll('.page-row[data-status="inactive"]').length;
			if (counterActive) counterActive.textContent = activeCount;
			if (counterInactive) counterInactive.textContent = inactiveCount;
		}

		// Exclusão com confirmação SweetAlert2 para páginas personalizadas
		container.querySelectorAll('.btn-delete-page').forEach(btn => {
			btn.addEventListener('click', function(e) {
				e.preventDefault();
				const pageId = this.getAttribute('data-page-id');
				const pageName = this.getAttribute('data-page-name') || 'esta página';

				if (typeof Swal !== 'undefined') {
					Swal.fire({
						title: 'Excluir Página?',
						text: `Tem certeza que deseja remover permanentemente a página "${pageName}"? Esta ação não pode ser desfeita.`,
						icon: 'warning',
						showCancelButton: true,
						confirmButtonColor: '#d33',
						cancelButtonColor: '#6c757d',
						confirmButtonText: '<i class="bi bi-trash3 me-1"></i> Sim, excluir',
						cancelButtonText: 'Cancelar'
					}).then((result) => {
						if (result.isConfirmed) {
							window.location.href = `page-delete.php?id=${pageId}`;
						}
					});
				} else {
					if (confirm(`Deseja realmente excluir a página "${pageName}"?`)) {
						window.location.href = `page-delete.php?id=${pageId}`;
					}
				}
			});
		});
	};

	if (document.readyState !== 'loading') {
		window.initPageManager(document);
	} else {
		document.addEventListener('DOMContentLoaded', function() {
			window.initPageManager(document);
		});
	}
})();
</script>

<?php require_once('footer.php'); ?>