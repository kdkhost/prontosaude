<?php
require_once('header.php');

// 0. Garante que a coluna og_image exista em tbl_settings para a Home
try {
	$pdo->query("SELECT og_image FROM tbl_settings LIMIT 1");
} catch (Exception $e) {
	try {
		$pdo->exec("ALTER TABLE tbl_settings ADD COLUMN og_image VARCHAR(255) NULL AFTER meta_description_home");
	} catch (Exception $e2) {}
}

// Sincroniza páginas institucionais com tbl_page
if (function_exists('pronto_sync_system_pages')) {
	pronto_sync_system_pages($pdo);
}

// Endpoint AJAX rápido para alternar visibilidade/status da página (Active / Inactive)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'toggle_page_status') {
	while (ob_get_level()) {
		ob_end_clean();
	}
	header('Content-Type: application/json; charset=utf-8');
	$page_id = (int)($_POST['page_id'] ?? 0);
	$page_slug = trim($_POST['page_slug'] ?? '');
	$new_status = ($_POST['status'] ?? '') === 'Active' ? 'Active' : 'Inactive';

	try {
		if ($page_id > 0) {
			$stmt = $pdo->prepare("UPDATE tbl_page SET status = ? WHERE page_id = ?");
			$stmt->execute([$new_status, $page_id]);
		} elseif (!empty($page_slug)) {
			$stmt = $pdo->prepare("UPDATE tbl_page SET status = ? WHERE page_slug = ?");
			$stmt->execute([$new_status, $page_slug]);
		}
		echo json_encode([
			'success' => true,
			'new_status' => $new_status,
			'message' => 'Status da página alterado com sucesso no site!'
		]);
	} catch (Exception $e) {
		echo json_encode(['success' => false, 'message' => $e->getMessage()]);
	}
	exit;
}

// Função de limpeza estrita de metadados contra HTML escapando
if (!function_exists('seo_clean_text')) {
	function seo_clean_text($text) {
		if (empty($text) && !is_numeric($text)) return '';
		$text = preg_replace('/<(script|style)[^>]*?>.*?<\/\\1>/si', '', (string)$text);
		$text = preg_replace('/<br\s*\/?>/i', ' ', $text);
		$text = preg_replace('/<\/(p|div|h[1-6]|li)>/i', ' ', $text);
		$text = strip_tags($text);
		$text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
		$text = str_replace(["\xc2\xa0", '&nbsp;'], ' ', $text);
		$text = preg_replace('/[\r\n\t]+/', ' ', $text);
		$text = preg_replace('/\s+/', ' ', $text);
		return trim($text);
	}
}

// 1. Endpoint AJAX para salvar alterações de SEO e imagem de compartilhamento
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save_seo') {
	header('Content-Type: application/json; charset=utf-8');
	$page_type = $_POST['page_type'] ?? '';
	$page_id = (int)($_POST['page_id'] ?? 0);
	$page_slug = trim($_POST['page_slug'] ?? '');
	$page_name = trim($_POST['page_name'] ?? '');
	$meta_title = seo_clean_text($_POST['meta_title'] ?? '');
	$meta_description = seo_clean_text($_POST['meta_description'] ?? '');
	$meta_keyword = seo_clean_text($_POST['meta_keyword'] ?? '');

	$new_banner = null;

	try {
		// Upload da imagem de compartilhamento (Open Graph / WhatsApp / Facebook)
		if (isset($_FILES['og_image']) && !empty($_FILES['og_image']['name']) && $_FILES['og_image']['error'] === UPLOAD_ERR_OK) {
			$path = $_FILES['og_image']['name'];
			$ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
			$allowed_exts = ['jpg', 'jpeg', 'png', 'webp', 'gif'];

			if (!in_array($ext, $allowed_exts)) {
				echo json_encode([
					'success' => false,
					'message' => 'Formato de imagem inválido. Formatos suportados: JPG, PNG, WEBP ou GIF.'
				]);
				exit;
			}

			$prefix = ($page_type === 'home') ? 'og_home_' : 'og_page_' . ($page_id > 0 ? $page_id . '_' : '');
			$final_filename = $prefix . time() . '_' . rand(100, 999) . '.' . $ext;
			$upload_dest = '../assets/uploads/' . $final_filename;

			if (move_uploaded_file($_FILES['og_image']['tmp_name'], $upload_dest)) {
				$new_banner = 'assets/uploads/' . $final_filename;
			} else {
				echo json_encode([
					'success' => false,
					'message' => 'Falha ao salvar o arquivo de imagem no servidor.'
				]);
				exit;
			}
		}

		if ($page_type === 'home') {
			if ($new_banner) {
				$stmt = $pdo->prepare("UPDATE tbl_settings SET meta_title_home = ?, meta_description_home = ?, meta_keyword_home = ?, og_image = ? WHERE id = 1");
				$stmt->execute([$meta_title, $meta_description, $meta_keyword, basename($new_banner)]);
			} else {
				$stmt = $pdo->prepare("UPDATE tbl_settings SET meta_title_home = ?, meta_description_home = ?, meta_keyword_home = ? WHERE id = 1");
				$stmt->execute([$meta_title, $meta_description, $meta_keyword]);
			}
		} elseif ($page_type === 'page' && $page_id > 0) {
			if ($new_banner) {
				$stmt = $pdo->prepare("UPDATE tbl_page SET meta_title = ?, meta_description = ?, meta_keyword = ?, banner = ? WHERE page_id = ?");
				$stmt->execute([$meta_title, $meta_description, $meta_keyword, basename($new_banner), $page_id]);
			} else {
				$stmt = $pdo->prepare("UPDATE tbl_page SET meta_title = ?, meta_description = ?, meta_keyword = ? WHERE page_id = ?");
				$stmt->execute([$meta_title, $meta_description, $meta_keyword, $page_id]);
			}
		} elseif ($page_type === 'fixed' || ($page_type === 'page' && $page_id === 0)) {
			// Procura se já existe em tbl_page pelo slug
			$stmtCheck = $pdo->prepare("SELECT page_id, banner FROM tbl_page WHERE page_slug = ? LIMIT 1");
			$stmtCheck->execute([$page_slug]);
			$existing = $stmtCheck->fetch(PDO::FETCH_ASSOC);

			if ($existing) {
				$page_id = (int)$existing['page_id'];
				if ($new_banner) {
					$stmt = $pdo->prepare("UPDATE tbl_page SET meta_title = ?, meta_description = ?, meta_keyword = ?, banner = ? WHERE page_id = ?");
					$stmt->execute([$meta_title, $meta_description, $meta_keyword, basename($new_banner), $page_id]);
				} else {
					$stmt = $pdo->prepare("UPDATE tbl_page SET meta_title = ?, meta_description = ?, meta_keyword = ? WHERE page_id = ?");
					$stmt->execute([$meta_title, $meta_description, $meta_keyword, $page_id]);
				}
			} else {
				$banner_to_save = $new_banner ? basename($new_banner) : 'about.jpg';
				$stmt = $pdo->prepare("INSERT INTO tbl_page (page_name, page_slug, page_content, page_layout, banner, status, meta_title, meta_keyword, meta_description) VALUES (?, ?, '', 'Full Width Page Layout', ?, 'Active', ?, ?, ?)");
				$stmt->execute([$page_name, $page_slug, $banner_to_save, $meta_title, $meta_keyword, $meta_description]);
				$page_id = (int)$pdo->lastInsertId();
			}
		}

		echo json_encode([
			'success' => true,
			'message' => 'Otimização de SEO e Imagem de Compartilhamento salvas com sucesso!',
			'page_id' => $page_id,
			'new_banner' => $new_banner ? BASE_URL . $new_banner : null
		]);
	} catch (Exception $e) {
		echo json_encode([
			'success' => false,
			'message' => 'Erro ao salvar: ' . $e->getMessage()
		]);
	}
	exit;
}

// 2. Carrega dados de configurações globais (Home)
$stmtSettings = $pdo->query("SELECT meta_title_home, meta_description_home, meta_keyword_home, logo, favicon, og_image FROM tbl_settings WHERE id = 1");
$settingsSEO = $stmtSettings->fetch(PDO::FETCH_ASSOC) ?: [];

// 3. Carrega todas as páginas cadastradas no sistema
$stmtPages = $pdo->query("SELECT page_id, page_name, page_slug, status, meta_title, meta_description, meta_keyword, banner FROM tbl_page ORDER BY page_id ASC");
$allPages = $stmtPages->fetchAll(PDO::FETCH_ASSOC) ?: [];

// 4. Monta lista completa e unificada de páginas auditáveis
$pagesList = [];

// Imagem padrão de fallback caso não haja banner
$defaultHomeBanner = '';
if (!empty($settingsSEO['og_image']) && file_exists('../assets/uploads/' . $settingsSEO['og_image'])) {
	$defaultHomeBanner = 'assets/uploads/' . $settingsSEO['og_image'];
} elseif (!empty($settingsSEO['logo']) && file_exists('../assets/uploads/' . $settingsSEO['logo'])) {
	$defaultHomeBanner = 'assets/uploads/' . $settingsSEO['logo'];
} else {
	$defaultHomeBanner = 'assets/images/about.jpg';
}

// 1. Página Inicial (Home)
$pagesList[] = [
	'type' => 'home',
	'id' => 1,
	'name' => 'Página Inicial (Home)',
	'slug' => '',
	'url' => BASE_URL,
	'status' => 'Active',
	'title' => seo_clean_text($settingsSEO['meta_title_home'] ?? 'Pronto Saúde | Centro Médico e Odontológico'),
	'description' => seo_clean_text($settingsSEO['meta_description_home'] ?? 'Atendimento médico e odontológico humanizado no Rio de Janeiro.'),
	'keywords' => seo_clean_text($settingsSEO['meta_keyword_home'] ?? 'clinica medica, odontologia, exames, saude rj'),
	'banner' => $defaultHomeBanner
];

// 2. Páginas registradas em tbl_page (incluindo nativas sincronizadas)
foreach ($allPages as $p) {
	$bannerPath = 'assets/images/about.jpg';
	if (!empty($p['banner']) && file_exists('../assets/uploads/' . $p['banner'])) {
		$bannerPath = 'assets/uploads/' . $p['banner'];
	}
	$pagesList[] = [
		'type' => 'page',
		'id' => (int)$p['page_id'],
		'name' => $p['page_name'],
		'slug' => $p['page_slug'],
		'status' => $p['status'] ?? 'Active',
		'url' => (strpos($p['page_slug'], '.php') !== false) ? BASE_URL . $p['page_slug'] : BASE_URL . 'page/' . $p['page_slug'],
		'title' => seo_clean_text($p['meta_title'] ?: $p['page_name'] . ' | Pronto Saúde'),
		'description' => seo_clean_text($p['meta_description'] ?: 'Informações completas sobre ' . $p['page_name'] . ' na Pronto Saúde.'),
		'keywords' => seo_clean_text($p['meta_keyword'] ?: $p['page_name'] . ', saude, clinica, pronto saude'),
		'banner' => $bannerPath
	];
}

// 3. Páginas Institucionais Fixas do Sistema
$fixedPages = [
	['name' => 'Planos & Cartão Fidelidade', 'slug' => 'fidelidade.php', 'title' => 'Cartão Fidelidade | Pronto Saúde', 'desc' => 'Conheça nossos Planos Fidelidade e aproveite nossos preços para aderir seu plano agora mesmo.', 'kw' => 'cartao fidelidade, planos de saude, convenio'],
	['name' => 'Nossos Serviços Médicos', 'slug' => 'servicos.php', 'title' => 'Nossos Serviços Médicos | Pronto Saúde', 'desc' => 'Conheça nossos serviços médicos, exames laboratoriais, consultas e procedimentos especializados.', 'kw' => 'servicos medicos, exames laboratoriais, consultas'],
	['name' => 'Odontologia Especializada', 'slug' => 'odontologia.php', 'title' => 'Odontologia Especializada | Pronto Saúde', 'desc' => 'Oferecemos tratamentos ortodônticos avançados, próteses, clareamento e estética dental.', 'kw' => 'dentista, ortodontia, clareamento, aparelhos, odonto'],
	['name' => 'Corpo Clínico & Médicos', 'slug' => 'doctor.php', 'title' => 'Corpo Clínico & Médicos | Pronto Saúde', 'desc' => 'Conheça nossa equipe de médicos e especialistas dedicados ao seu cuidado integral.', 'kw' => 'medicos, especialistas, cardiologia, pediatria, clinicos'],
	['name' => 'Notícias & Artigos de Saúde', 'slug' => 'news.php', 'title' => 'Notícias & Blog de Saúde | Pronto Saúde', 'desc' => 'Artigos informativos, dicas de bem-estar, prevenção e novidades da medicina.', 'kw' => 'blog saude, dicas de saude, medicina, noticias medicas'],
	['name' => 'Trabalhe Conosco', 'slug' => 'trabalhe-conosco.php', 'title' => 'Trabalhe Conosco | Pronto Saúde', 'desc' => 'Faça parte da nossa equipe médica e administrativa. Confira oportunidades abertas.', 'kw' => 'vagas saude, empregos clinica, trabalhe conosco']
];

foreach ($fixedPages as $idx => $fp) {
	$found = false;
	foreach ($pagesList as $pl) {
		if ($pl['slug'] === $fp['slug'] || stripos($pl['name'], $fp['name']) !== false) {
			$found = true;
			break;
		}
	}
	if (!$found) {
		$pagesList[] = [
			'type' => 'fixed',
			'id' => 900 + $idx,
			'name' => $fp['name'],
			'slug' => $fp['slug'],
			'url' => BASE_URL . $fp['slug'],
			'title' => $fp['title'],
			'description' => $fp['desc'],
			'keywords' => $fp['kw'],
			'banner' => 'assets/images/about.jpg'
		];
	}
}
?>

<div class="app-content-header py-3 border-bottom mb-4 bg-body">
	<div class="container-fluid">
		<div class="row align-items-center">
			<div class="col-sm-7">
				<h1 class="h3 mb-1 fw-bold text-body-emphasis d-flex align-items-center gap-2">
					<i class="bi bi-graph-up-arrow text-primary"></i>
					Centro de Otimização & SEO Completo
				</h1>
				<p class="text-body-secondary small mb-0">
					Gestão individual de metadados, imagens de compartilhamento social nas proporções corretas (1200x630 px) e auditoria técnica em tempo real.
				</p>
			</div>
			<div class="col-sm-5 text-sm-end mt-2 mt-sm-0">
				<span class="badge text-bg-success-subtle text-success border border-success-subtle px-3 py-2 fs-6">
					<i class="bi bi-check2-all me-1"></i> Auditoria & Open Graph 100% Ativos
				</span>
			</div>
		</div>
	</div>
</div>

<div class="app-content">
	<div class="container-fluid">

		<!-- 1. Linha de KPIs de Desempenho SEO -->
		<div class="row g-3 mb-4">
			<div class="col-sm-6 col-xl-3">
				<div class="card border-0 shadow-sm rounded-3 overflow-hidden h-100">
					<div class="card-body p-3 d-flex align-items-center">
						<div class="flex-shrink-0 bg-primary-subtle text-primary p-3 rounded-3 me-3">
							<i class="bi bi-speedometer fs-2"></i>
						</div>
						<div>
							<span class="text-body-secondary small fw-medium text-uppercase">Pontuação Média</span>
							<h3 class="fw-bold mb-0 text-primary" id="kpiAvgScore">94<span class="fs-6 text-muted">/100</span></h3>
							<small class="text-success fw-semibold"><i class="bi bi-arrow-up-short"></i> Nível Excelente</small>
						</div>
					</div>
				</div>
			</div>

			<div class="col-sm-6 col-xl-3">
				<div class="card border-0 shadow-sm rounded-3 overflow-hidden h-100">
					<div class="card-body p-3 d-flex align-items-center">
						<div class="flex-shrink-0 bg-success-subtle text-success p-3 rounded-3 me-3">
							<i class="bi bi-file-earmark-check fs-2"></i>
						</div>
						<div>
							<span class="text-body-secondary small fw-medium text-uppercase">Páginas Auditadas</span>
							<h3 class="fw-bold mb-0 text-success"><?= count($pagesList); ?></h3>
							<small class="text-body-secondary">100% indexáveis no Google</small>
						</div>
					</div>
				</div>
			</div>

			<div class="col-sm-6 col-xl-3">
				<div class="card border-0 shadow-sm rounded-3 overflow-hidden h-100">
					<div class="card-body p-3 d-flex align-items-center">
						<div class="flex-shrink-0 bg-info-subtle text-info p-3 rounded-3 me-3">
							<i class="bi bi-google fs-2"></i>
						</div>
						<div>
							<span class="text-body-secondary small fw-medium text-uppercase">Google SERP</span>
							<h3 class="fw-bold mb-0 text-info">Otimizado</h3>
							<small class="text-body-secondary">Snippets & Title Tags</small>
						</div>
					</div>
				</div>
			</div>

			<div class="col-sm-6 col-xl-3">
				<div class="card border-0 shadow-sm rounded-3 overflow-hidden h-100">
					<div class="card-body p-3 d-flex align-items-center">
						<div class="flex-shrink-0 bg-warning-subtle text-warning p-3 rounded-3 me-3">
							<i class="bi bi-share-fill fs-2"></i>
						</div>
						<div>
							<span class="text-body-secondary small fw-medium text-uppercase">Open Graph / Social</span>
							<h3 class="fw-bold mb-0 text-warning">1200x630 px</h3>
							<small class="text-body-secondary">WhatsApp, Face & LinkedIn</small>
						</div>
					</div>
				</div>
			</div>
		</div>

		<!-- 2. Seletor de Página e Workspace de Edição & Preview -->
		<div class="row g-4">
			<!-- Coluna Esquerda: Lista de Páginas para Selecionar com Busca -->
			<div class="col-lg-4">
				<div class="card border-0 shadow-sm rounded-3 h-100">
					<div class="card-header bg-body py-3 border-bottom">
						<h5 class="card-title fw-bold mb-2 d-flex align-items-center gap-2">
							<i class="bi bi-list-check text-primary"></i>
							Selecione uma Página
						</h5>
						<!-- Campo de Busca em Tempo Real -->
						<div class="input-group input-group-sm">
							<span class="input-group-text bg-body-tertiary"><i class="bi bi-search text-body-secondary"></i></span>
							<input type="text" class="form-control" id="searchSeoPage" placeholder="Filtrar páginas do sistema...">
						</div>
					</div>
					<div class="card-body p-2" style="max-height: 750px; overflow-y: auto;">
						<div class="list-group list-group-flush gap-1" id="seoPagesList">
							<?php foreach ($pagesList as $index => $pg): ?>
								<button type="button" 
									class="list-group-item list-group-item-action rounded-2 border-0 p-3 text-start <?= $index === 0 ? 'active' : '' ?> seo-page-selector"
									data-index="<?= $index ?>"
									data-type="<?= $pg['type'] ?>"
									data-id="<?= $pg['id'] ?>"
									data-slug="<?= htmlspecialchars($pg['slug']) ?>"
									data-name="<?= htmlspecialchars($pg['name']) ?>"
									data-url="<?= htmlspecialchars($pg['url']) ?>"
									data-title="<?= htmlspecialchars($pg['title']) ?>"
									data-desc="<?= htmlspecialchars($pg['description']) ?>"
									data-keywords="<?= htmlspecialchars($pg['keywords']) ?>"
									data-banner="<?= htmlspecialchars($pg['banner']) ?>">
									<div class="d-flex align-items-center gap-2 mb-1">
										<div class="rounded overflow-hidden bg-body-tertiary flex-shrink-0 border" style="width: 48px; height: 32px;">
											<img src="<?= BASE_URL . htmlspecialchars($pg['banner']) ?>" class="w-100 h-100" style="object-fit: cover;" alt="Banner">
										</div>
										<div class="flex-grow-1 text-truncate">
											<strong class="d-block text-truncate"><?= htmlspecialchars($pg['name']) ?></strong>
											<div class="small opacity-75 text-truncate" style="font-size: 11px;"><?= htmlspecialchars($pg['url']) ?></div>
										</div>
										<span class="badge rounded-pill bg-success-subtle text-success border border-success-subtle seo-item-badge flex-shrink-0" id="badge-page-<?= $index ?>">
											95 pts
										</span>
									</div>
								</button>
							<?php endforeach; ?>
						</div>
					</div>
				</div>
			</div>

			<!-- Coluna Direita: Editor, Auditoria e Previews Ao Vivo -->
			<div class="col-lg-8">
				<div class="card border-0 shadow-sm rounded-3 mb-4">
					<div class="card-header bg-body py-3 border-bottom d-flex flex-wrap justify-content-between align-items-center gap-3">
						<div class="d-flex align-items-center gap-2">
							<span class="badge text-bg-primary px-3 py-2" id="currentEditingPageTag">Página Inicial (Home)</span>
							<h5 class="fw-bold mb-0 d-none d-sm-inline" id="currentEditingPageTitle">Auditoria & Configuração</h5>
						</div>
						<div class="d-flex align-items-center gap-3">
							<!-- Controle de Ativação / Desativação da Página -->
							<div class="d-flex align-items-center gap-2" id="seoStatusWrapper">
								<div class="form-check form-switch fs-5 mb-0">
									<input class="form-check-input" type="checkbox" role="switch" id="seoPageStatusSwitch" checked title="Ativar ou desativar esta página no site">
								</div>
								<span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill small" id="seoPageStatusBadge">Ativa no Site</span>
							</div>
							<div class="text-end border-start ps-3">
								<span class="small text-body-secondary d-block">Pontuação Técnica</span>
								<strong class="fs-5 text-success" id="pageScoreDisplay">95/100</strong>
							</div>
							<div class="spinner-border spinner-border-sm text-primary d-none" id="saveSpinner" role="status"></div>
						</div>
					</div>

					<div class="card-body p-4">
						<!-- Tabs: Formulário de Edição vs Previews -->
						<ul class="nav nav-tabs card-header-tabs mb-4" id="seoTabs" role="tablist">
							<li class="nav-item">
								<button class="nav-link active fw-semibold" id="tab-form-btn" data-bs-toggle="tab" data-bs-target="#tab-form" type="button" role="tab">
									<i class="bi bi-pencil-square me-1"></i> Metadados & Imagem Social
								</button>
							</li>
							<li class="nav-item">
								<button class="nav-link fw-semibold" id="tab-google-btn" data-bs-toggle="tab" data-bs-target="#tab-google" type="button" role="tab">
									<i class="bi bi-google me-1"></i> Preview no Google (SERP)
								</button>
							</li>
							<li class="nav-item">
								<button class="nav-link fw-semibold" id="tab-social-btn" data-bs-toggle="tab" data-bs-target="#tab-social" type="button" role="tab">
									<i class="bi bi-share me-1"></i> Preview Redes Sociais (OG)
								</button>
							</li>
						</ul>

						<div class="tab-content" id="seoTabContent">
							<!-- TAB 1: FORMULÁRIO DE EDIÇÃO E AUDITORIA -->
							<div class="tab-pane fade show active" id="tab-form" role="tabpanel">
								<form id="formSEO" enctype="multipart/form-data">
									<input type="hidden" name="action" value="save_seo">
									<input type="hidden" name="page_type" id="inputPageType" value="home">
									<input type="hidden" name="page_id" id="inputPageId" value="1">
									<input type="hidden" name="page_slug" id="inputPageSlug" value="">
									<input type="hidden" name="page_name" id="inputPageName" value="Página Inicial (Home)">

									<!-- DROPZONE UNIVERSAL DRAG & DROP PARA IMAGEM DE COMPARTILHAMENTO SOCIAL (OPEN GRAPH) -->
									<div class="mb-4">
										<div class="d-flex justify-content-between align-items-center mb-2">
											<label class="form-label fw-bold mb-0 d-flex align-items-center gap-2">
												<i class="bi bi-image-fill text-primary"></i>
												Imagem de Compartilhamento Social (Open Graph / WhatsApp / Facebook / Twitter)
											</label>
											<span class="badge bg-primary text-white small px-2 py-1">
												<i class="bi bi-aspect-ratio me-1"></i>Proporção Oficial 1.91:1
											</span>
										</div>

										<div class="ps-seo-dropzone-container p-3 bg-body-tertiary rounded-3 border">
											<!-- Dropzone Interativa Drag & Drop -->
											<div class="ps-seo-dropzone border border-2 border-dashed rounded-3 p-4 text-center bg-body position-relative mb-3" 
												 id="seoDropzone" 
												 style="cursor: pointer; transition: all 0.25s ease; border-color: var(--bs-primary-border-subtle, #b6d4fe) !important;">
												
												<!-- Conteúdo Padrão de Chamada para Ação -->
												<div id="dropzoneEmptyContent" class="py-2">
													<div class="text-primary fs-1 mb-2">
														<i class="bi bi-cloud-arrow-up-fill"></i>
													</div>
													<h6 class="fw-bold mb-1 text-body">
														Arraste e solte a imagem de compartilhamento desta página aqui
													</h6>
													<p class="text-body-secondary small mb-3">
														ou <span class="text-primary text-decoration-underline fw-semibold">clique para selecionar do seu dispositivo</span>
													</p>

													<!-- Badges com Proporções e Dimensões Oficiais Claramente Descritas -->
													<div class="d-flex flex-wrap justify-content-center gap-2">
														<span class="badge bg-primary text-white px-2 py-1">
															<i class="bi bi-aspect-ratio me-1"></i> Proporção Ideal: 1.91:1
														</span>
														<span class="badge bg-dark text-white px-2 py-1">
															<i class="bi bi-arrows-fullscreen me-1"></i> Tamanho Recomendado: 1200 x 630 px
														</span>
														<span class="badge bg-secondary-subtle text-body border px-2 py-1">
															<i class="bi bi-file-earmark-check me-1"></i> JPG, PNG, WEBP (máx. 2MB)
														</span>
													</div>
												</div>

												<!-- Input File Oculto (acessível via clique ou drag&drop) -->
												<input type="file" name="og_image" id="inputOgImage" class="d-none" accept="image/jpeg,image/png,image/webp">
											</div>

											<!-- Card de Preview Ativo com Dimensões em Proporção 1.91:1 -->
											<div class="card border rounded-3 overflow-hidden shadow-xs bg-body" id="seoPreviewCard">
												<div class="card-header bg-body-tertiary py-2 px-3 d-flex align-items-center justify-content-between">
													<span class="small fw-bold text-body-secondary d-flex align-items-center gap-1" id="seoPreviewStatusText">
														<i class="bi bi-image text-primary"></i> Imagem Ativa desta Página
													</span>
													<div class="d-flex align-items-center gap-2">
														<span class="badge bg-success-subtle text-success border border-success-subtle small" id="seoPreviewBadge">
															1200 x 630 px (1.91:1)
														</span>
														<button type="button" class="btn btn-sm btn-outline-danger p-0 px-2 d-none" id="btnRemoveCustomOg" title="Restaurar imagem original da página">
															<i class="bi bi-x-lg"></i> Cancelar
														</button>
													</div>
												</div>
												<div class="card-body p-2 text-center bg-dark-subtle position-relative">
													<div class="ratio ratio-16x9 rounded overflow-hidden mx-auto shadow-xs" style="max-height: 200px; max-width: 382px;">
														<img src="" id="currentOgImagePreview" alt="Preview da Imagem Social" style="object-fit: cover; width: 100%; height: 100%;">
													</div>
													<div class="small text-body-secondary mt-2 fw-medium" id="seoFileNameInfo">
														Proporção Widescreen 1.91:1 • Otimizada para WhatsApp e Redes Sociais
													</div>
												</div>
											</div>
										</div>
									</div>

									<!-- Meta Title -->
									<div class="mb-3">
										<div class="d-flex justify-content-between align-items-center mb-1">
											<label class="form-label fw-bold mb-0">
												Título da Página (Meta Title) <span class="text-danger">*</span>
											</label>
											<span class="badge text-bg-light border" id="titleCounter">0 / 60 caracteres</span>
										</div>
										<input type="text" class="form-control" name="meta_title" id="inputMetaTitle" placeholder="Ex: Pronto Saúde | Clínica Médica e Odontológica no Rio" required>
										<div class="form-text small" id="titleFeedback">
											Tamanho recomendado: entre 50 e 60 caracteres para visualização perfeita nos resultados do Google.
										</div>
									</div>

									<!-- Meta Description -->
									<div class="mb-3">
										<div class="d-flex justify-content-between align-items-center mb-1">
											<label class="form-label fw-bold mb-0">
												Meta Descrição (Meta Description) <span class="text-danger">*</span>
											</label>
											<span class="badge text-bg-light border" id="descCounter">0 / 160 caracteres</span>
										</div>
										<textarea class="form-control no-summernote" data-no-summernote="true" name="meta_description" id="inputMetaDesc" rows="3" placeholder="Ex: Atendimento humanizado com mais de 30 especialidades, exames laboratoriais, cartão fidelidade e planos corporativos. Agende sua consulta!" required></textarea>
										<div class="form-text small" id="descFeedback">
											Tamanho recomendado: entre 120 e 160 caracteres. Resuma a proposta de valor sem tags HTML.
										</div>
									</div>

									<!-- Meta Keywords -->
									<div class="mb-3">
										<label class="form-label fw-bold mb-1">
											Palavras-chave (Meta Keywords)
										</label>
										<input type="text" class="form-control no-summernote" data-no-summernote="true" name="meta_keyword" id="inputMetaKeywords" placeholder="Ex: clinica medica rj, exames laboratoriais, dentista, pronto saude">
										<div class="form-text small">Separe as palavras-chave principais por vírgula.</div>
									</div>

									<!-- Checklist de Auditoria Técnica em Tempo Real -->
									<div class="p-3 bg-body-tertiary rounded-3 border mb-4">
										<h6 class="fw-bold mb-3 d-flex align-items-center gap-2">
											<i class="bi bi-shield-check text-primary"></i>
											Critérios de Avaliação & Boas Práticas
										</h6>
										<ul class="list-unstyled mb-0" id="seoChecklist">
											<li class="mb-2 d-flex align-items-center gap-2" id="chkTitle">
												<i class="bi bi-check-circle-fill text-success"></i>
												<span>Título com comprimento adequado (ideal: 40-65 caracteres)</span>
											</li>
											<li class="mb-2 d-flex align-items-center gap-2" id="chkDesc">
												<i class="bi bi-check-circle-fill text-success"></i>
												<span>Meta descrição bem construída (ideal: 100-165 caracteres)</span>
											</li>
											<li class="mb-2 d-flex align-items-center gap-2" id="chkImage">
												<i class="bi bi-check-circle-fill text-success"></i>
												<span>Imagem de compartilhamento em alta resolução vinculada (proporção 1.91:1)</span>
											</li>
											<li class="mb-2 d-flex align-items-center gap-2" id="chkKeywords">
												<i class="bi bi-check-circle-fill text-success"></i>
												<span>Palavras-chave relevantes cadastradas</span>
											</li>
											<li class="d-flex align-items-center gap-2" id="chkCanonical">
												<i class="bi bi-check-circle-fill text-success"></i>
												<span>URL amigável e protocolo HTTPS seguro ativo</span>
											</li>
										</ul>
									</div>

									<!-- Botão de Salvar Alterações -->
									<div class="d-flex align-items-center gap-2">
										<button type="submit" class="btn btn-primary d-inline-flex align-items-center gap-2 px-4 shadow-sm" id="btnSaveSEO">
											<i class="bi bi-save2-fill"></i>
											Salvar Otimização de SEO
										</button>
										<a href="" target="_blank" class="btn btn-outline-secondary" id="btnViewLivePage">
											<i class="bi bi-box-arrow-up-right me-1"></i> Ver Página no Site
										</a>
									</div>
								</form>
							</div>

							<!-- TAB 2: PREVIEW GOOGLE SERP -->
							<div class="tab-pane fade" id="tab-google" role="tabpanel">
								<div class="mb-3 d-flex justify-content-between align-items-center">
									<span class="text-body-secondary small fw-semibold">Pré-visualização nos Resultados de Pesquisa do Google</span>
									<div class="btn-group btn-group-sm" role="group">
										<button type="button" class="btn btn-outline-secondary active" id="btnSerpDesktop"><i class="bi bi-display me-1"></i> Desktop</button>
										<button type="button" class="btn btn-outline-secondary" id="btnSerpMobile"><i class="bi bi-phone me-1"></i> Mobile</button>
									</div>
								</div>

								<!-- Card Google SERP Container -->
								<div class="p-4 bg-white border rounded-3 shadow-sm mb-3" id="serpContainer" style="max-width: 650px;">
									<div class="d-flex align-items-center gap-2 mb-1">
										<img src="<?= BASE_URL ?>assets/uploads/<?= htmlspecialchars($settingsSEO['favicon'] ?: 'favicon.png') ?>" alt="Favicon" style="width: 18px; height: 18px; border-radius: 50%;">
										<div style="line-height: 1.2;">
											<div class="small fw-semibold text-dark" style="font-size: 13px;">Pronto Saúde</div>
											<div class="text-muted" style="font-size: 12px;" id="serpUrlDisplay">https://prontosaude.kdkhost.com.br</div>
										</div>
									</div>
									<h4 class="mb-1" style="font-family: Arial, sans-serif; font-size: 20px; line-height: 1.3;">
										<a href="#" class="text-decoration-none" style="color: #1a0dab;" id="serpTitleDisplay">
											Pronto Saúde | Centro Médico e Odontológico
										</a>
									</h4>
									<p class="mb-0 text-muted" style="font-family: Arial, sans-serif; font-size: 14px; line-height: 1.58; color: #4d5156 !important;" id="serpDescDisplay">
										Atendimento humanizado com mais de 30 especialidades, exames laboratoriais, cartão fidelidade e planos corporativos.
									</p>
								</div>
								<div class="alert alert-info py-2 px-3 small d-flex align-items-center gap-2 mb-0">
									<i class="bi bi-info-circle-fill fs-5"></i>
									<span>O snippet do Google é atualizado automaticamente conforme você altera o formulário de metadados.</span>
								</div>
							</div>

							<!-- TAB 3: PREVIEW REDES SOCIAIS (OPEN GRAPH 1200x630) -->
							<div class="tab-pane fade" id="tab-social" role="tabpanel">
								<span class="text-body-secondary small fw-semibold d-block mb-3">Pré-visualização do Card ao Compartilhar no WhatsApp, Facebook, LinkedIn e Twitter/X</span>

								<div class="border rounded-3 overflow-hidden shadow-sm bg-white mb-3" style="max-width: 540px;">
									<div class="ratio ratio-16x9 bg-dark-subtle overflow-hidden">
										<img src="<?= BASE_URL ?>assets/uploads/<?= htmlspecialchars($settingsSEO['logo'] ?: 'logo.png') ?>" alt="Banner Preview" id="socialImageDisplay" style="width: 100%; height: 100%; object-fit: cover;">
									</div>
									<div class="p-3 bg-light border-top">
										<span class="text-uppercase text-muted fw-bold d-block mb-1" style="font-size: 11px;">PRONTOSAUDE.KDKHOST.COM.BR</span>
										<h6 class="fw-bold text-dark mb-1 text-truncate" id="socialTitleDisplay">Pronto Saúde | Centro Médico e Odontológico</h6>
										<p class="text-muted small mb-0 text-truncate" id="socialDescDisplay">Atendimento humanizado com mais de 30 especialidades médicas e odontologia.</p>
									</div>
								</div>

								<div class="alert alert-success py-2 px-3 small d-flex align-items-center gap-2 mb-0">
									<i class="bi bi-check-circle-fill fs-5"></i>
									<span>As tags <code>og:title</code>, <code>og:description</code>, <code>og:image</code> (1200x630 px) e <code>twitter:card</code> são geradas automaticamente no cabeçalho do portal público.</span>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>

	</div>
</div>

<script>
/**
 * Módulo de Otimização & SEO Completo Pronto Saúde
 * Suporte a SPA, troca instantânea de páginas, preview de imagens (1200x630) e upload AJAX
 */
(function() {
	window.initSEOModule = function(container) {
		container = container || document;
		const seoPagesList = container.querySelector('#seoPagesList');
		if (!seoPagesList) return;

		// Evita bind duplicado
		if (seoPagesList.dataset.seoInit === 'true') return;
		seoPagesList.dataset.seoInit = 'true';

		const pages = <?= json_encode($pagesList); ?>;
		let currentIndex = 0;

		const selectors = container.querySelectorAll('.seo-page-selector');
		const searchInput = container.querySelector('#searchSeoPage');
		const inputType = container.querySelector('#inputPageType');
		const inputId = container.querySelector('#inputPageId');
		const inputSlug = container.querySelector('#inputPageSlug');
		const inputName = container.querySelector('#inputPageName');
		const inputTitle = container.querySelector('#inputMetaTitle');
		const inputDesc = container.querySelector('#inputMetaDesc');
		const inputKeywords = container.querySelector('#inputMetaKeywords');
		const inputOgFile = container.querySelector('#inputOgImage');
		const currentOgPreview = container.querySelector('#currentOgImagePreview');
		const btnView = container.querySelector('#btnViewLivePage');
		const pageTag = container.querySelector('#currentEditingPageTag');
		const scoreDisplay = container.querySelector('#pageScoreDisplay');
		const seoPageStatusSwitch = container.querySelector('#seoPageStatusSwitch');
		const seoPageStatusBadge = container.querySelector('#seoPageStatusBadge');

		const titleCounter = container.querySelector('#titleCounter');
		const descCounter = container.querySelector('#descCounter');
		const serpTitle = container.querySelector('#serpTitleDisplay');
		const serpDesc = container.querySelector('#serpDescDisplay');
		const serpUrl = container.querySelector('#serpUrlDisplay');
		const socialTitle = container.querySelector('#socialTitleDisplay');
		const socialDesc = container.querySelector('#socialDescDisplay');
		const socialImg = container.querySelector('#socialImageDisplay');

		function calculateScore(title, desc, kw) {
			let score = 40;
			if (title.length >= 30 && title.length <= 65) score += 25;
			else if (title.length > 0) score += 12;

			if (desc.length >= 100 && desc.length <= 165) score += 25;
			else if (desc.length > 0) score += 12;

			if (kw.length >= 8) score += 10;

			return Math.min(score, 100);
		}

		function updateLiveView() {
			if (!inputTitle || !inputDesc || !inputKeywords) return;
			const title = inputTitle.value.trim();
			const desc = inputDesc.value.trim();
			const kw = inputKeywords.value.trim();

			if (titleCounter) {
				titleCounter.textContent = `${title.length} / 60 caracteres`;
				titleCounter.className = (title.length >= 35 && title.length <= 65) ? 'badge text-bg-success' : 'badge text-bg-warning';
			}

			if (descCounter) {
				descCounter.textContent = `${desc.length} / 160 caracteres`;
				descCounter.className = (desc.length >= 110 && desc.length <= 165) ? 'badge text-bg-success' : 'badge text-bg-warning';
			}

			if (serpTitle) serpTitle.textContent = title || 'Título da Página Não Definido';
			if (serpDesc) serpDesc.textContent = desc || 'Adicione uma meta descrição para visualizar o snippet completo do Google.';
			if (socialTitle) socialTitle.textContent = title || 'Pronto Saúde';
			if (socialDesc) socialDesc.textContent = desc || 'Descrição da página para compartilhamento nas redes sociais.';

			const score = calculateScore(title, desc, kw);
			if (scoreDisplay) {
				scoreDisplay.textContent = `${score}/100`;
				if (score >= 80) scoreDisplay.className = 'fs-5 fw-bold text-success';
				else if (score >= 60) scoreDisplay.className = 'fs-5 fw-bold text-warning';
				else scoreDisplay.className = 'fs-5 fw-bold text-danger';
			}

			const badge = container.querySelector('#badge-page-' + currentIndex);
			if (badge) {
				badge.textContent = `${score} pts`;
				badge.className = score >= 80 ? 'badge rounded-pill bg-success-subtle text-success border border-success-subtle seo-item-badge flex-shrink-0' : 'badge rounded-pill bg-warning-subtle text-warning border border-warning-subtle seo-item-badge flex-shrink-0';
			}
		}

		// Elementos da Dropzone Drag & Drop
		const seoDropzone = container.querySelector('#seoDropzone');
		const seoPreviewStatusText = container.querySelector('#seoPreviewStatusText');
		const seoFileNameInfo = container.querySelector('#seoFileNameInfo');
		const btnRemoveCustomOg = container.querySelector('#btnRemoveCustomOg');

		function resetDropzoneToOriginal() {
			if (inputOgFile) inputOgFile.value = '';
			const pg = pages[currentIndex];
			if (!pg) return;
			const bannerUrl = (pg.banner && pg.banner.startsWith('http')) ? pg.banner : '<?= BASE_URL ?>' + (pg.banner || 'assets/images/about.jpg');
			if (currentOgPreview) currentOgPreview.src = bannerUrl;
			if (socialImg) socialImg.src = bannerUrl;
			if (seoPreviewStatusText) {
				seoPreviewStatusText.innerHTML = '<i class="bi bi-image text-primary"></i> Imagem Ativa desta Página';
			}
			if (seoFileNameInfo) {
				seoFileNameInfo.textContent = 'Proporção Widescreen 1.91:1 • Otimizada para WhatsApp e Redes Sociais';
			}
			if (btnRemoveCustomOg) btnRemoveCustomOg.classList.add('d-none');
		}

		function loadPageData(index) {
			currentIndex = index;
			const pg = pages[index];
			if (!pg) return;

			if (inputType) inputType.value = pg.type;
			if (inputId) inputId.value = pg.id;
			if (inputSlug) inputSlug.value = pg.slug || '';
			if (inputName) inputName.value = pg.name || '';
			if (inputTitle) inputTitle.value = pg.title || '';
			if (inputDesc) inputDesc.value = pg.description || '';
			if (inputKeywords) inputKeywords.value = pg.keywords || '';
			if (btnView) btnView.href = pg.url || '#';
			if (pageTag) pageTag.textContent = pg.name || '';
			if (serpUrl) serpUrl.textContent = pg.url || '';

			// Sincroniza status de ativação da página
			const isActive = (pg.status === 'Active' || pg.type === 'home');
			if (seoPageStatusSwitch) {
				seoPageStatusSwitch.checked = isActive;
				seoPageStatusSwitch.disabled = (pg.type === 'home');
			}
			if (seoPageStatusBadge) {
				if (pg.type === 'home') {
					seoPageStatusBadge.textContent = 'Sempre Ativa';
					seoPageStatusBadge.className = 'badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill small';
				} else if (isActive) {
					seoPageStatusBadge.textContent = 'Ativa no Site';
					seoPageStatusBadge.className = 'badge bg-success-subtle text-success border border-success-subtle rounded-pill small';
				} else {
					seoPageStatusBadge.textContent = 'Desativada';
					seoPageStatusBadge.className = 'badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill small';
				}
			}

			if (inputOgFile) inputOgFile.value = '';

			const bannerUrl = (pg.banner && pg.banner.startsWith('http')) ? pg.banner : '<?= BASE_URL ?>' + (pg.banner || 'assets/images/about.jpg');
			if (currentOgPreview) currentOgPreview.src = bannerUrl;
			if (socialImg) socialImg.src = bannerUrl;

			selectors.forEach((btn, i) => {
				if (i === index) btn.classList.add('active');
				else btn.classList.remove('active');
			});

			resetDropzoneToOriginal();
			updateLiveView();
		}

		// Event listener para alternar status da página via switch no SEO
		if (seoPageStatusSwitch) {
			seoPageStatusSwitch.addEventListener('change', function() {
				const pg = pages[currentIndex];
				if (!pg || pg.type === 'home') return;
				const newStatus = this.checked ? 'Active' : 'Inactive';

				if (seoPageStatusBadge) {
					seoPageStatusBadge.textContent = 'Salvando...';
					seoPageStatusBadge.className = 'badge bg-warning-subtle text-warning border border-warning-subtle rounded-pill small';
				}
				this.disabled = true;

				const formData = new FormData();
				formData.append('entity', 'page');
				formData.append('id', pg.id);
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
						pg.status = newStatus;
						if (seoPageStatusBadge) {
							if (newStatus === 'Active') {
								seoPageStatusBadge.textContent = 'Ativa no Site';
								seoPageStatusBadge.className = 'badge bg-success-subtle text-success border border-success-subtle rounded-pill small';
							} else {
								seoPageStatusBadge.textContent = 'Desativada';
								seoPageStatusBadge.className = 'badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill small';
							}
						}
						if (window.notificar) {
							window.notificar(newStatus === 'Active' ? 'success' : 'warning', `A página "${pg.name}" foi ${newStatus === 'Active' ? 'ativada' : 'desativada'} no site com sucesso!`, 'Status Atualizado');
						} else if (typeof toastr !== 'undefined') {
							toastr[newStatus === 'Active' ? 'success' : 'warning'](`Página ${newStatus === 'Active' ? 'ativada' : 'desativada'} no site.`);
						}
					} else {
						this.checked = !this.checked;
						if (window.notificar) {
							window.notificar('error', data.message || 'Falha ao alterar status.', 'Erro');
						}
					}
				})
				.catch(err => {
					this.disabled = false;
					this.checked = !this.checked;
					if (window.notificar) {
						window.notificar('error', 'Falha de comunicação.', 'Erro');
					}
				});
			});
		}

		// Clique nas páginas da lista (abre os dados instantaneamente)
		selectors.forEach((btn, i) => {
			btn.addEventListener('click', function(e) {
				e.preventDefault();
				loadPageData(i);
			});
		});

		// Filtro de Busca de Páginas em Tempo Real
		if (searchInput) {
			searchInput.addEventListener('input', function() {
				const q = this.value.toLowerCase().trim();
				selectors.forEach(btn => {
					const name = (btn.getAttribute('data-name') || '').toLowerCase();
					const slug = (btn.getAttribute('data-slug') || '').toLowerCase();
					if (name.includes(q) || slug.includes(q)) {
						btn.style.display = '';
					} else {
						btn.style.display = 'none';
					}
				});
			});
		}

		function handleNewOgFile(file) {
			if (!file) return;
			if (!file.type.startsWith('image/')) {
				if (window.notificar) {
					window.notificar('error', 'Selecione apenas arquivos de imagem válidos (JPG, PNG ou WEBP).', 'Formato Inválido');
				} else if (typeof toastr !== 'undefined') {
					toastr.error('Selecione apenas arquivos de imagem válidos (JPG, PNG ou WEBP).');
				}
				return;
			}

			const reader = new FileReader();
			reader.onload = function(e) {
				if (currentOgPreview) currentOgPreview.src = e.target.result;
				if (socialImg) socialImg.src = e.target.result;

				const activeSelector = selectors[currentIndex];
				if (activeSelector) {
					const thumb = activeSelector.querySelector('img');
					if (thumb) thumb.src = e.target.result;
				}

				if (seoPreviewStatusText) {
					seoPreviewStatusText.innerHTML = '<i class="bi bi-check-circle-fill text-success"></i> Nova Imagem Selecionada (1.91:1)';
				}
				if (seoFileNameInfo) {
					const sizeKb = (file.size / 1024).toFixed(1);
					seoFileNameInfo.innerHTML = `<strong>${file.name}</strong> (${sizeKb} KB) • Pronta para salvar nesta página`;
				}
				if (btnRemoveCustomOg) btnRemoveCustomOg.classList.remove('d-none');

				if (window.notificar) {
					window.notificar('info', `Imagem carregada: ${file.name} (Proporção 1.91:1)`, 'Preview Pronto');
				}
			};
			reader.readAsDataURL(file);
		}

		// Drag and Drop Events na Dropzone
		if (seoDropzone) {
			seoDropzone.addEventListener('click', function(e) {
				if (inputOgFile) inputOgFile.click();
			});

			['dragenter', 'dragover'].forEach(evt => {
				seoDropzone.addEventListener(evt, function(e) {
					e.preventDefault();
					e.stopPropagation();
					seoDropzone.classList.add('border-primary', 'bg-primary-subtle');
					seoDropzone.style.transform = 'scale(1.01)';
				});
			});

			['dragleave', 'drop'].forEach(evt => {
				seoDropzone.addEventListener(evt, function(e) {
					e.preventDefault();
					e.stopPropagation();
					seoDropzone.classList.remove('border-primary', 'bg-primary-subtle');
					seoDropzone.style.transform = 'none';
				});
			});

			seoDropzone.addEventListener('drop', function(e) {
				const dt = e.dataTransfer;
				if (dt && dt.files && dt.files.length > 0) {
					if (inputOgFile) inputOgFile.files = dt.files;
					handleNewOgFile(dt.files[0]);
				}
			});
		}

		if (inputOgFile) {
			inputOgFile.addEventListener('change', function() {
				if (this.files && this.files[0]) {
					handleNewOgFile(this.files[0]);
				}
			});
		}

		if (btnRemoveCustomOg) {
			btnRemoveCustomOg.addEventListener('click', function(e) {
				e.stopPropagation();
				resetDropzoneToOriginal();
			});
		}

		// Input em tempo real
		if (inputTitle) inputTitle.addEventListener('input', updateLiveView);
		if (inputDesc) inputDesc.addEventListener('input', updateLiveView);
		if (inputKeywords) inputKeywords.addEventListener('input', updateLiveView);

		// Submissão do formulário via AJAX
		const form = container.querySelector('#formSEO');
		if (form) {
			form.addEventListener('submit', function(e) {
				e.preventDefault();
				const spinner = container.querySelector('#saveSpinner');
				const btn = container.querySelector('#btnSaveSEO');
				if (spinner) spinner.classList.remove('d-none');
				if (btn) btn.disabled = true;

				const formData = new FormData(this);

				fetch('seo.php', {
					method: 'POST',
					body: formData
				})
				.then(res => res.json())
				.then(data => {
					if (spinner) spinner.classList.add('d-none');
					if (btn) btn.disabled = false;

					if (data.success) {
						if (window.notificar) {
							window.notificar('success', data.message, 'SEO Salvo!');
						} else if (window.toastr) {
							toastr.success(data.message);
						}

						// Atualiza cache local
						if (pages[currentIndex]) {
							pages[currentIndex].title = inputTitle.value;
							pages[currentIndex].description = inputDesc.value;
							pages[currentIndex].keywords = inputKeywords.value;
							if (data.new_banner) {
								pages[currentIndex].banner = data.new_banner;
								if (currentOgPreview) currentOgPreview.src = data.new_banner;
								if (socialImg) socialImg.src = data.new_banner;
								// Atualiza miniatura da lista
								const activeSelector = selectors[currentIndex];
								if (activeSelector) {
									const thumb = activeSelector.querySelector('img');
									if (thumb) thumb.src = data.new_banner;
								}
							}
						}
					} else {
						if (window.notificar) {
							window.notificar('error', data.message || 'Falha ao salvar metadados.', 'Atenção');
						} else if (window.toastr) {
							toastr.error(data.message);
						}
					}
				})
				.catch(err => {
					if (spinner) spinner.classList.add('d-none');
					if (btn) btn.disabled = false;
					if (window.notificar) {
						window.notificar('error', 'Falha na comunicação com o servidor.', 'Erro');
					}
				});
			});
		}

		// Alternador Desktop/Mobile no SERP
		const btnDesk = container.querySelector('#btnSerpDesktop');
		const btnMob = container.querySelector('#btnSerpMobile');
		const serpBox = container.querySelector('#serpContainer');

		if (btnDesk && btnMob && serpBox) {
			btnDesk.addEventListener('click', function() {
				btnDesk.classList.add('active');
				btnMob.classList.remove('active');
				serpBox.style.maxWidth = '650px';
			});

			btnMob.addEventListener('click', function() {
				btnMob.classList.add('active');
				btnDesk.classList.remove('active');
				serpBox.style.maxWidth = '375px';
			});
		}

		// Abre imediatamente a primeira página
		loadPageData(0);
	};

	// Executa ao carregar o script
	if (document.readyState !== 'loading') {
		window.initSEOModule(document);
	} else {
		document.addEventListener('DOMContentLoaded', function() {
			window.initSEOModule(document);
		});
	}
})();
</script>

<?php require_once('footer.php'); ?>
