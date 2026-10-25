<?php
// =========================================================================
// Pronto Saúde - Centro de Otimização & SEO Completo
// Endpoint AJAX de alta precisão e renderizador visual
// =========================================================================

// Funções auxiliares globais de limpeza e formatação de metadados de SEO
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

if (!function_exists('seo_clean_keywords')) {
	function seo_clean_keywords($text) {
		if (empty($text) && !is_numeric($text)) return '';
		$text = preg_replace('/<(script|style)[^>]*?>.*?<\/\\1>/si', '', (string)$text);
		$text = strip_tags($text);
		$text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
		$text = str_replace(["\xc2\xa0", '&nbsp;'], ' ', $text);
		$lines = preg_split('/[\r\n]+/', (string)$text);
		$clean = [];
		foreach ($lines as $line) {
			$line = trim(preg_replace('/\s+/', ' ', $line));
			if (!empty($line)) {
				$clean[] = $line;
			}
		}
		return implode("\n", array_unique($clean));
	}
}

// 1. Processamento AJAX Isolado (Executado antes de qualquer saída HTML)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
	while (ob_get_level()) {
		ob_end_clean();
	}
	ob_start();
	if (session_status() === PHP_SESSION_NONE) {
		session_start();
	}
	require_once('config.php');
	require_once('functions.php');

	// Validação estrita de autenticação de sessão
	if (!isset($_SESSION['user'])) {
		while (ob_get_level()) {
			ob_end_clean();
		}
		header('Content-Type: application/json; charset=utf-8');
		echo json_encode([
			'success' => false,
			'message' => 'Sessão expirada. Por favor, recarregue a página e faça login.'
		]);
		exit;
	}

	// Auto-migração da coluna og_image em tbl_settings para a Home se não existir
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

	// Endpoint AJAX: Alternar visibilidade/status da página (Active / Inactive)
	if ($_POST['action'] === 'toggle_page_status') {
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
				$clean_slug = str_replace('.php', '', $page_slug);
				$stmt = $pdo->prepare("UPDATE tbl_page SET status = ? WHERE page_slug = ? OR page_slug = ?");
				$stmt->execute([$new_status, $clean_slug, $clean_slug . '.php']);
			}
			echo json_encode([
				'success' => true,
				'new_status' => $new_status,
				'message' => 'Status da página alterado com sucesso no site!'
			]);
		} catch (Exception $e) {
			echo json_encode(['success' => false, 'message' => 'Erro ao alterar status: ' . $e->getMessage()]);
		}
		exit;
	}

	// Endpoint AJAX: Salvar Otimização de SEO e Imagem Open Graph
	if ($_POST['action'] === 'save_seo') {
		while (ob_get_level()) {
			ob_end_clean();
		}
		header('Content-Type: application/json; charset=utf-8');

		$page_type = $_POST['page_type'] ?? '';
		$page_id = (int)($_POST['page_id'] ?? 0);
		$page_slug = trim($_POST['page_slug'] ?? '');
		$page_name = trim($_POST['page_name'] ?? '');
		$meta_title = seo_clean_text($_POST['meta_title'] ?? '');
		$meta_description = seo_clean_text($_POST['meta_description'] ?? '');
		$meta_keyword = seo_clean_keywords($_POST['meta_keyword'] ?? '');

		if (empty($meta_title)) {
			echo json_encode(['success' => false, 'message' => 'O título da página (Meta Title) é obrigatório para indexação no Google.']);
			exit;
		}

		$new_banner = null;

		try {
			// Upload e validação da imagem Open Graph / Social (1200x630 px ideal)
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

				if ($_FILES['og_image']['size'] > 5 * 1024 * 1024) {
					echo json_encode([
						'success' => false,
						'message' => 'Arquivo de imagem muito grande. O limite máximo permitido é de 5MB.'
					]);
					exit;
				}

				$prefix = ($page_type === 'home') ? 'og_home_' : 'og_page_' . ($page_id > 0 ? $page_id . '_' : '');
				$final_filename = $prefix . time() . '_' . rand(100, 999) . '.' . $ext;
				$upload_dir = __DIR__ . '/../assets/uploads/';
				if (!is_dir($upload_dir)) {
					mkdir($upload_dir, 0755, true);
				}
				$upload_dest = $upload_dir . $final_filename;

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
			} else {
				// Página fixa ou institucional sincronizada
				$clean_slug = str_replace('.php', '', $page_slug);
				$stmtCheck = $pdo->prepare("SELECT page_id FROM tbl_page WHERE page_slug = ? OR page_slug = ? LIMIT 1");
				$stmtCheck->execute([$clean_slug, $clean_slug . '.php']);
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
					$stmt->execute([$page_name, $clean_slug, $banner_to_save, $meta_title, $meta_keyword, $meta_description]);
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
				'message' => 'Erro ao salvar no banco de dados: ' . $e->getMessage()
			]);
		}
		exit;
	}
}

// =========================================================================
// 2. Renderização da Interface Visual (GET)
// =========================================================================
require_once('header.php');

// Garante que a coluna og_image exista em tbl_settings para a Home
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

// Carrega configurações globais (Home)
$stmtSettings = $pdo->query("SELECT meta_title_home, meta_description_home, meta_keyword_home, logo, favicon, og_image FROM tbl_settings WHERE id = 1");
$settingsSEO = $stmtSettings->fetch(PDO::FETCH_ASSOC) ?: [];

// Carrega páginas cadastradas em tbl_page
$allPages = [];
try {
	$stmtPages = $pdo->query("SELECT page_id, page_name, page_slug, status, meta_title, meta_description, meta_keyword, banner FROM tbl_page ORDER BY page_id ASC");
	$allPages = $stmtPages->fetchAll(PDO::FETCH_ASSOC) ?: [];
} catch (Exception $e) {
	$allPages = [];
}

// Monta lista completa e unificada de páginas auditáveis
$pagesList = [];

// 1. Página Inicial (Home)
$defaultHomeBanner = 'assets/uploads/og_home.jpg';
if (!empty($settingsSEO['og_image']) && file_exists('../assets/uploads/' . $settingsSEO['og_image'])) {
	$defaultHomeBanner = 'assets/uploads/' . $settingsSEO['og_image'];
}

$pagesList[] = [
	'type' => 'home',
	'id' => 1,
	'name' => 'Página Inicial (Home)',
	'slug' => '',
	'url' => BASE_URL,
	'status' => 'Active',
	'title' => seo_clean_text($settingsSEO['meta_title_home'] ?? 'Pronto Saúde | Centro Médico, Odontologia e Exames no RJ'),
	'description' => seo_clean_text($settingsSEO['meta_description_home'] ?? 'Atendimento médico humanizado com mais de 30 especialidades, exames rápidos, odontologia completa e cartão fidelidade com descontos. Agende agora!'),
	'keywords' => seo_clean_keywords($settingsSEO['meta_keyword_home'] ?? "pronto saude\nclinica medica rj\ncentro medico rio de janeiro\nconsulta medica rio de janeiro\nexames laboratoriais\nodontologia rj\ncartao fidelidade saude\natendimento humanizado"),
	'banner' => $defaultHomeBanner
];

// 2. Páginas registradas em tbl_page
foreach ($allPages as $p) {
	$slug = $p['page_slug'];
	$bannerPath = 'assets/uploads/og_home.jpg';
	if (!empty($p['banner']) && file_exists('../assets/uploads/' . $p['banner'])) {
		$bannerPath = 'assets/uploads/' . $p['banner'];
	} elseif (file_exists('../assets/uploads/og_' . str_replace('-', '_', $slug) . '.jpg')) {
		$bannerPath = 'assets/uploads/og_' . str_replace('-', '_', $slug) . '.jpg';
	}

	$targetUrl = BASE_URL . $slug;
	if (strpos($slug, '.php') === false) {
		// Verifica se existe arquivo PHP físico na raiz com esse nome
		if (file_exists('../' . $slug . '.php')) {
			$targetUrl = BASE_URL . $slug . '.php';
		} else {
			$targetUrl = BASE_URL . 'page/' . $slug;
		}
	}

	$pagesList[] = [
		'type' => 'page',
		'id' => (int)$p['page_id'],
		'name' => $p['page_name'],
		'slug' => $slug,
		'status' => $p['status'] ?? 'Active',
		'url' => $targetUrl,
		'title' => seo_clean_text($p['meta_title'] ?: $p['page_name'] . ' | Pronto Saúde'),
		'description' => seo_clean_text($p['meta_description'] ?: 'Informações completas sobre ' . $p['page_name'] . ' na Pronto Saúde.'),
		'keywords' => seo_clean_keywords($p['meta_keyword'] ?: "pronto saude\n" . $p['page_name'] . "\nclinica medica rj\nsaude rio de janeiro"),
		'banner' => $bannerPath
	];
}

// 3. Páginas Institucionais Fixas do Sistema (caso não estejam em tbl_page)
$systemFixed = function_exists('pronto_get_system_pages') ? pronto_get_system_pages() : [
	['slug' => 'fidelidade', 'name' => 'Planos & Cartão Fidelidade', 'url' => 'fidelidade.php', 'title' => 'Cartão Fidelidade | Pronto Saúde', 'desc' => 'Conheça nossos Planos Fidelidade e aproveite nossos preços para aderir seu plano.', 'kw' => 'cartao fidelidade, planos de saude, convenio'],
	['slug' => 'servicos', 'name' => 'Nossos Serviços Médicos', 'url' => 'servicos.php', 'title' => 'Nossos Serviços Médicos | Pronto Saúde', 'desc' => 'Conheça nossos serviços médicos, exames laboratoriais, consultas e procedimentos especializados.', 'kw' => 'servicos medicos, exames laboratoriais, consultas'],
	['slug' => 'odontologia', 'name' => 'Odontologia Especializada', 'url' => 'odontologia.php', 'title' => 'Odontologia Especializada | Pronto Saúde', 'desc' => 'Oferecemos tratamentos ortodônticos avançados, próteses, clareamento e estética dental.', 'kw' => 'dentista, ortodontia, clareamento, aparelhos, odonto'],
	['slug' => 'doctor', 'name' => 'Corpo Clínico & Médicos', 'url' => 'doctor.php', 'title' => 'Corpo Clínico & Médicos | Pronto Saúde', 'desc' => 'Conheça nossa equipe de médicos e especialistas dedicados ao seu cuidado integral.', 'kw' => 'medicos, especialistas, cardiologia, pediatria, clinicos'],
	['slug' => 'news', 'name' => 'Notícias & Artigos de Saúde', 'url' => 'news.php', 'title' => 'Notícias & Blog de Saúde | Pronto Saúde', 'desc' => 'Artigos informativos, dicas de bem-estar, prevenção e novidades da medicina.', 'kw' => 'blog saude, dicas de saude, medicina, noticias medicas'],
	['slug' => 'trabalhe-conosco', 'name' => 'Trabalhe Conosco', 'url' => 'trabalhe-conosco.php', 'title' => 'Trabalhe Conosco | Pronto Saúde', 'desc' => 'Faça parte da nossa equipe médica e administrativa. Confira oportunidades abertas.', 'kw' => 'vagas saude, empregos clinica, trabalhe conosco']
];

foreach ($systemFixed as $idx => $sfp) {
	$found = false;
	$cleanSlug = str_replace('.php', '', $sfp['slug']);
	foreach ($pagesList as $pl) {
		$plCleanSlug = str_replace('.php', '', $pl['slug']);
		if ($plCleanSlug === $cleanSlug || mb_strtolower($pl['name']) === mb_strtolower($sfp['name'])) {
			$found = true;
			break;
		}
	}
	if (!$found) {
		$pagesList[] = [
			'type' => 'fixed',
			'id' => 900 + $idx,
			'name' => $sfp['name'],
			'slug' => $cleanSlug,
			'url' => BASE_URL . ($sfp['url'] ?? ($cleanSlug . '.php')),
			'status' => 'Active',
			'title' => $sfp['title'],
			'description' => $sfp['desc'],
			'keywords' => $sfp['kw'],
			'banner' => 'assets/images/about.jpg'
		];
	}
}
?>

<!-- Cabeçalho da Página -->
<div class="app-content-header py-3 border-bottom mb-4 bg-body">
	<div class="container-fluid">
		<div class="row align-items-center">
			<div class="col-sm-7">
				<h1 class="h3 mb-1 fw-bold text-body-emphasis d-flex align-items-center gap-2">
					<i class="bi bi-graph-up-arrow text-primary"></i>
					Centro de Otimização & SEO Completo
				</h1>
				<p class="text-body-secondary small mb-0">
					Gestão individual de metadados, imagens de compartilhamento social nas proporções oficiais (1200x630 px) e auditoria técnica em tempo real.
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
							<h3 class="fw-bold mb-0 text-primary" id="kpiAvgScore">95<span class="fs-6 text-muted">/100</span></h3>
							<small class="text-success fw-semibold"><i class="bi bi-arrow-up-short"></i> Otimização Excelente</small>
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

		<!-- 2. Workspace de Otimização e Edição com Suporte Preciso a #tab-form -->
		<div class="row g-4" id="seoWorkspace">
			
			<!-- Coluna Esquerda: Lista de Páginas do Sistema com Busca e Filtros -->
			<div class="col-lg-4">
				<div class="card border-0 shadow-sm rounded-3 h-100">
					<div class="card-header bg-body py-3 border-bottom">
						<div class="d-flex justify-content-between align-items-center mb-2">
							<h5 class="card-title fw-bold mb-0 d-flex align-items-center gap-2">
								<i class="bi bi-list-check text-primary"></i>
								Páginas do Sistema
							</h5>
							<span class="badge bg-body-secondary text-body-secondary" id="pagesCountBadge">
								<?= count($pagesList); ?> páginas
							</span>
						</div>
						<!-- Campo de Busca em Tempo Real -->
						<div class="input-group input-group-sm">
							<span class="input-group-text bg-body-tertiary"><i class="bi bi-search text-body-secondary"></i></span>
							<input type="text" class="form-control" id="searchSeoPage" placeholder="Filtrar por nome ou URL..." autocomplete="off">
							<button class="btn btn-outline-secondary d-none" type="button" id="btnClearSearch" title="Limpar busca">
								<i class="bi bi-x"></i>
							</button>
						</div>
					</div>
					<div class="card-body p-2" style="max-height: 820px; overflow-y: auto;">
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
									data-status="<?= htmlspecialchars($pg['status']) ?>"
									data-title="<?= htmlspecialchars($pg['title']) ?>"
									data-desc="<?= htmlspecialchars($pg['description']) ?>"
									data-keywords="<?= htmlspecialchars($pg['keywords']) ?>"
									data-banner="<?= htmlspecialchars($pg['banner']) ?>">
									<div class="d-flex align-items-center gap-2 mb-1">
										<div class="rounded overflow-hidden bg-body-tertiary flex-shrink-0 border" style="width: 48px; height: 32px;">
											<img src="<?= BASE_URL . htmlspecialchars($pg['banner']) ?>" class="w-100 h-100" style="object-fit: cover;" alt="Banner da página">
										</div>
										<div class="flex-grow-1 text-truncate">
											<div class="d-flex align-items-center gap-1">
												<strong class="d-block text-truncate"><?= htmlspecialchars($pg['name']) ?></strong>
												<?php if ($pg['status'] === 'Inactive'): ?>
													<span class="badge text-bg-danger rounded-pill flex-shrink-0 p-1" style="font-size: 8px;">Off</span>
												<?php endif; ?>
											</div>
											<div class="small opacity-75 text-truncate" style="font-size: 11px;"><?= htmlspecialchars($pg['url']) ?></div>
										</div>
										<span class="badge rounded-pill bg-success-subtle text-success border border-success-subtle seo-item-badge flex-shrink-0" id="badge-page-<?= $index ?>">
											95 pts
										</span>
									</div>
								</button>
							<?php endforeach; ?>
						</div>
						<div class="p-4 text-center text-muted d-none" id="seoNoResultsMessage">
							<i class="bi bi-search fs-2 d-block mb-2 text-secondary"></i>
							<p class="small mb-0">Nenhuma página encontrada para sua busca.</p>
						</div>
					</div>
				</div>
			</div>

			<!-- Coluna Direita: Editor de Metadados (#tab-form) e Previews Ao Vivo -->
			<div class="col-lg-8">
				<div class="card border-0 shadow-sm rounded-3 mb-4">
					<!-- Cabeçalho do Workspace de Edição -->
					<div class="card-header bg-body py-3 border-bottom d-flex flex-wrap justify-content-between align-items-center gap-3">
						<div class="d-flex align-items-center gap-2">
							<span class="badge text-bg-primary px-3 py-2 fs-6" id="currentEditingPageTag">Página Inicial (Home)</span>
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
						<!-- Navegação por Abas: #tab-form, #tab-google, #tab-social -->
						<ul class="nav nav-tabs card-header-tabs mb-4" id="seoTabs" role="tablist">
							<li class="nav-item" role="presentation">
								<button class="nav-link active fw-semibold" id="tab-form-btn" data-bs-toggle="tab" data-bs-target="#tab-form" type="button" role="tab" aria-controls="tab-form" aria-selected="true">
									<i class="bi bi-pencil-square me-1 text-primary"></i> Metadados & Imagem Social
								</button>
							</li>
							<li class="nav-item" role="presentation">
								<button class="nav-link fw-semibold" id="tab-google-btn" data-bs-toggle="tab" data-bs-target="#tab-google" type="button" role="tab" aria-controls="tab-google" aria-selected="false">
									<i class="bi bi-google me-1 text-danger"></i> Preview no Google (SERP)
								</button>
							</li>
							<li class="nav-item" role="presentation">
								<button class="nav-link fw-semibold" id="tab-social-btn" data-bs-toggle="tab" data-bs-target="#tab-social" type="button" role="tab" aria-controls="tab-social" aria-selected="false">
									<i class="bi bi-share-fill me-1 text-success"></i> Preview Redes Sociais (OG)
								</button>
							</li>
						</ul>

						<div class="tab-content" id="seoTabContent">
							<!-- TAB 1: FORMULÁRIO DE EDIÇÃO E AUDITORIA (#tab-form) -->
							<div class="tab-pane fade show active" id="tab-form" role="tabpanel" aria-labelledby="tab-form-btn">
								<form id="formSEO" enctype="multipart/form-data" novalidate>
									<input type="hidden" name="action" value="save_seo">
									<input type="hidden" name="page_type" id="inputPageType" value="home">
									<input type="hidden" name="page_id" id="inputPageId" value="1">
									<input type="hidden" name="page_slug" id="inputPageSlug" value="">
									<input type="hidden" name="page_name" id="inputPageName" value="Página Inicial (Home)">

									<!-- DROPZONE UNIVERSAL DRAG & DROP PARA IMAGEM SOCIAL (OPEN GRAPH 1200x630) -->
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
											<!-- Dropzone Interativa Drag & Drop / Clique / Ctrl+V -->
											<div class="ps-seo-dropzone border border-2 border-dashed rounded-3 p-4 text-center bg-body position-relative mb-3" 
												 id="seoDropzone" 
												 style="cursor: pointer; transition: all 0.25s ease;">
												
												<div id="dropzoneEmptyContent" class="py-2">
													<div class="text-primary fs-1 mb-2">
														<i class="bi bi-cloud-arrow-up-fill"></i>
													</div>
													<h6 class="fw-bold mb-1 text-body">
														Arraste e solte a imagem desta página aqui
													</h6>
													<p class="text-body-secondary small mb-3">
														ou <span class="text-primary text-decoration-underline fw-semibold">clique para selecionar</span> ou cole com <kbd class="px-1 py-0 bg-secondary text-white">Ctrl + V</kbd>
													</p>

													<!-- Badges de Proporção Oficial -->
													<div class="d-flex flex-wrap justify-content-center gap-2">
														<span class="badge bg-primary text-white px-2 py-1">
															<i class="bi bi-aspect-ratio me-1"></i> Proporção Ideal: 1.91:1
														</span>
														<span class="badge bg-dark text-white px-2 py-1">
															<i class="bi bi-arrows-fullscreen me-1"></i> Resolução Recomendada: 1200 x 630 px
														</span>
														<span class="badge bg-secondary-subtle text-body border px-2 py-1">
															<i class="bi bi-file-earmark-check me-1"></i> JPG, PNG, WEBP (máx. 5MB)
														</span>
													</div>
												</div>

												<!-- Input File Oculto -->
												<input type="file" name="og_image" id="inputOgImage" class="d-none" accept="image/jpeg,image/png,image/webp,image/gif">
											</div>

											<!-- Card de Preview Ativo com Proporção 1.91:1 e Inspetor de Dimensões -->
											<div class="card border rounded-3 overflow-hidden shadow-xs bg-body" id="seoPreviewCard">
												<div class="card-header bg-body-tertiary py-2 px-3 d-flex align-items-center justify-content-between">
													<span class="small fw-bold text-body-secondary d-flex align-items-center gap-1" id="seoPreviewStatusText">
														<i class="bi bi-image text-primary"></i> Imagem Ativa desta Página
													</span>
													<div class="d-flex align-items-center gap-2">
														<span class="badge bg-success-subtle text-success border border-success-subtle small" id="seoPreviewBadge">
															1200 x 630 px (1.91:1)
														</span>
														<button type="button" class="btn btn-sm btn-outline-danger p-0 px-2 d-none" id="btnRemoveCustomOg" title="Restaurar imagem original salva desta página">
															<i class="bi bi-x-lg me-1"></i> Cancelar / Restaurar Original
														</button>
													</div>
												</div>
												<div class="card-body p-2 text-center bg-dark-subtle position-relative">
													<div class="ratio ratio-16x9 rounded overflow-hidden mx-auto shadow-xs" style="max-height: 220px; max-width: 420px;">
														<img src="" id="currentOgImagePreview" alt="Preview da Imagem Social" style="object-fit: cover; width: 100%; height: 100%;">
													</div>
													<div class="small text-body-secondary mt-2 fw-medium" id="seoFileNameInfo">
														Proporção Widescreen 1.91:1 • Otimizada para WhatsApp, Facebook, Twitter e LinkedIn
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
										<input type="text" class="form-control form-control-lg fs-6" name="meta_title" id="inputMetaTitle" placeholder="Ex: Pronto Saúde | Clínica Médica e Odontológica no Rio de Janeiro" required autocomplete="off">
										<div class="form-text small" id="titleFeedback">
											Tamanho recomendado: entre <strong>50 e 60 caracteres</strong> para visualização completa sem reticências nos resultados do Google.
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
										<textarea class="form-control no-summernote" data-no-summernote="true" name="meta_description" id="inputMetaDesc" rows="3" placeholder="Ex: Atendimento humanizado com mais de 30 especialidades médicas, exames laboratoriais, cartão fidelidade e planos corporativos. Agende sua consulta!" required></textarea>
										<div class="form-text small" id="descFeedback">
											Tamanho recomendado: entre <strong>120 e 160 caracteres</strong>. Resuma com clareza os diferenciais e a proposta da página.
										</div>
									</div>

									<!-- Meta Keywords com Separação Individual por ENTER e Badges de Tags -->
									<div class="mb-4">
										<div class="d-flex justify-content-between align-items-center mb-1">
											<label class="form-label fw-bold mb-0 d-flex align-items-center gap-2">
												<i class="bi bi-tags-fill text-primary"></i>
												Palavras-chave Individuais (Meta Keywords & Hashtags)
											</label>
											<span class="badge text-bg-light border" id="kwCounter">0 palavras-chave cadastradas</span>
										</div>

										<!-- Campo de Digitação Rápida com Enter -->
										<div class="input-group mb-2 shadow-xs">
											<span class="input-group-text bg-body-tertiary text-primary"><i class="bi bi-plus-circle-fill"></i></span>
											<input type="text" class="form-control" id="quickAddKeyword" placeholder="Digite uma palavra-chave e pressione ENTER para adicionar..." autocomplete="off">
											<button class="btn btn-primary d-inline-flex align-items-center gap-1 px-3" type="button" id="btnAddKeyword">
												<i class="bi bi-arrow-return-left"></i> Adicionar (Enter)
											</button>
										</div>

										<!-- Badges Visuais Interativos com Botão Excluir -->
										<div class="d-flex flex-wrap gap-2 p-2 bg-body-tertiary rounded-3 border mb-2" id="keywordsTagsContainer" style="min-height: 48px; max-height: 140px; overflow-y: auto;">
											<span class="text-muted small align-self-center fst-italic p-1" id="noKeywordsMsg">Nenhuma palavra-chave cadastrada ainda. Digite acima e tecle Enter.</span>
										</div>

										<!-- Textarea com linhas individuais gravadas no banco -->
										<div class="d-flex justify-content-between align-items-center mb-1">
											<span class="small text-body-secondary fw-semibold">Armazenamento no Banco (Uma palavra-chave por linha):</span>
											<button type="button" class="btn btn-link btn-sm text-decoration-none p-0 small" id="btnConvertCommaToEnter" title="Se você colou uma lista com vírgulas, converta tudo em quebras de linha">
												<i class="bi bi-arrow-down-up me-1"></i> Separar vírgulas em linhas por Enter
											</button>
										</div>
										<textarea class="form-control font-monospace no-summernote" data-no-summernote="true" name="meta_keyword" id="inputMetaKeywords" rows="4" placeholder="Digite ou cole uma palavra-chave por linha...&#10;Ex:&#10;pronto saude&#10;clinica medica rj&#10;exames laboratoriais&#10;dentista pronto saude" style="line-height: 1.6; font-size: 13.5px;"></textarea>
										<div class="form-text small">
											<i class="bi bi-info-circle me-1"></i> Cada linha é gravada de forma individual e limpa no banco de dados, sendo indexada como termo único e convertida em hashtag para compartilhamento social.
										</div>
									</div>

									<!-- Checklist de Auditoria Técnica em Tempo Real -->
									<div class="p-3 bg-body-tertiary rounded-3 border mb-4">
										<h6 class="fw-bold mb-3 d-flex align-items-center gap-2">
											<i class="bi bi-shield-check text-primary"></i>
											Critérios de Auditoria & Boas Práticas SEO
										</h6>
										<ul class="list-unstyled mb-0" id="seoChecklist">
											<li class="mb-2 d-flex align-items-center gap-2" id="chkTitle">
												<i class="bi bi-check-circle-fill text-success"></i>
												<span>Título com comprimento ideal (recomendado: 40 a 65 caracteres)</span>
											</li>
											<li class="mb-2 d-flex align-items-center gap-2" id="chkDesc">
												<i class="bi bi-check-circle-fill text-success"></i>
												<span>Meta descrição persuasiva e completa (recomendado: 100 a 165 caracteres)</span>
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
												<span>URL amigável e protocolo seguro HTTPS ativo</span>
											</li>
										</ul>
									</div>

									<!-- Botões de Ação do Formulário -->
									<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 pt-2 border-top">
										<div class="d-flex align-items-center gap-2">
											<button type="submit" class="btn btn-primary d-inline-flex align-items-center gap-2 px-4 shadow-sm" id="btnSaveSEO">
												<i class="bi bi-save2-fill"></i>
												<span>Salvar Otimização de SEO</span>
											</button>
											<button type="button" class="btn btn-outline-secondary d-none" id="btnDiscardChanges" title="Descartar alterações não salvas">
												<i class="bi bi-arrow-counterclockwise me-1"></i> Descartar
											</button>
										</div>
										<a href="<?= BASE_URL ?>" target="_blank" class="btn btn-outline-primary" id="btnViewLivePage">
											<i class="bi bi-box-arrow-up-right me-1"></i> Ver Página no Site
										</a>
									</div>
								</form>
							</div>

							<!-- TAB 2: PREVIEW GOOGLE SERP (#tab-google) -->
							<div class="tab-pane fade" id="tab-google" role="tabpanel" aria-labelledby="tab-google-btn">
								<div class="mb-3 d-flex justify-content-between align-items-center">
									<span class="text-body-secondary small fw-semibold">Pré-visualização nos Resultados de Pesquisa do Google</span>
									<div class="btn-group btn-group-sm" role="group">
										<button type="button" class="btn btn-outline-secondary active" id="btnSerpDesktop"><i class="bi bi-display me-1"></i> Desktop</button>
										<button type="button" class="btn btn-outline-secondary" id="btnSerpMobile"><i class="bi bi-phone me-1"></i> Mobile</button>
									</div>
								</div>

								<!-- Card Google SERP Mockup -->
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
									<span>O snippet do Google é recalculado e atualizado instantaneamente conforme você altera os campos na aba <strong>Metadados & Imagem Social</strong>.</span>
								</div>
							</div>

							<!-- TAB 3: PREVIEW REDES SOCIAIS (OPEN GRAPH 1200x630) (#tab-social) -->
							<div class="tab-pane fade" id="tab-social" role="tabpanel" aria-labelledby="tab-social-btn">
								<span class="text-body-secondary small fw-semibold d-block mb-3">Pré-visualização do Card ao Compartilhar no WhatsApp, Facebook, LinkedIn e Twitter/X</span>

								<div class="border rounded-3 overflow-hidden shadow-sm bg-white mb-3" style="max-width: 540px;">
									<div class="ratio ratio-16x9 bg-dark-subtle overflow-hidden">
										<img src="<?= BASE_URL . htmlspecialchars($defaultHomeBanner) ?>" alt="Banner Preview" id="socialImageDisplay" style="width: 100%; height: 100%; object-fit: cover;">
									</div>
									<div class="p-3 bg-light border-top">
										<span class="text-uppercase text-muted fw-bold d-block mb-1" style="font-size: 11px;">PRONTOSAUDE.KDKHOST.COM.BR</span>
										<h6 class="fw-bold text-dark mb-1 text-truncate" id="socialTitleDisplay">Pronto Saúde | Centro Médico e Odontológico</h6>
										<p class="text-muted small mb-0 text-truncate" id="socialDescDisplay">Atendimento humanizado com mais de 30 especialidades médicas e odontologia.</p>
									</div>
								</div>

								<!-- Card de Hashtags Sugeridas para Redes Sociais -->
								<div class="card border rounded-3 p-3 bg-body-tertiary mb-3 shadow-xs" style="max-width: 540px;">
									<div class="d-flex justify-content-between align-items-center mb-2">
										<span class="small fw-bold text-body d-flex align-items-center gap-1">
											<i class="bi bi-hash text-primary"></i> Hashtags Geradas para o Compartilhamento:
										</span>
										<button type="button" class="btn btn-sm btn-outline-primary py-0 px-2" id="btnCopyHashtags" style="font-size: 11px;">
											<i class="bi bi-clipboard me-1"></i> Copiar Hashtags
										</button>
									</div>
									<div class="small font-monospace text-primary fw-semibold lh-base" id="socialHashtagsDisplay">
										#ProntoSaude #ClinicaMedicaRJ #SaudeRioDeJaneiro
									</div>
								</div>

								<div class="alert alert-success py-2 px-3 small d-flex align-items-center gap-2 mb-0">
									<i class="bi bi-check-circle-fill fs-5"></i>
									<span>As meta tags <code>og:title</code>, <code>og:description</code>, <code>og:image</code> (1200x630 px) e <code>twitter:card</code> são geradas automaticamente no <code>&lt;head&gt;</code> do portal público.</span>
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
 * Suporte de alta precisão para deep-linking (#tab-form), drag & drop 1.91:1,
 * sincronização em tempo real de SERP/OG, salvamento seguro AJAX e notificações globais.
 */
(function() {
	'use strict';

	window.initSEOModule = function(container) {
		container = container || document;
		const seoPagesList = container.querySelector('#seoPagesList');
		if (!seoPagesList) return;

		// Evita reinicialização duplicada no mesmo container
		if (seoPagesList.dataset.seoInit === 'true') return;
		seoPagesList.dataset.seoInit = 'true';

		const pages = <?= json_encode($pagesList, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); ?>;
		let currentIndex = 0;
		let isDirty = false;

		// Elementos do DOM
		const selectors = Array.from(container.querySelectorAll('.seo-page-selector'));
		const searchInput = container.querySelector('#searchSeoPage');
		const btnClearSearch = container.querySelector('#btnClearSearch');
		const noResultsMsg = container.querySelector('#seoNoResultsMessage');
		const pagesCountBadge = container.querySelector('#pagesCountBadge');

		const inputType = container.querySelector('#inputPageType');
		const inputId = container.querySelector('#inputPageId');
		const inputSlug = container.querySelector('#inputPageSlug');
		const inputName = container.querySelector('#inputPageName');
		const inputTitle = container.querySelector('#inputMetaTitle');
		const inputDesc = container.querySelector('#inputMetaDesc');
		const inputKeywords = container.querySelector('#inputMetaKeywords');
		const quickAddKeyword = container.querySelector('#quickAddKeyword');
		const btnAddKeyword = container.querySelector('#btnAddKeyword');
		const keywordsTagsContainer = container.querySelector('#keywordsTagsContainer');
		const btnConvertCommaToEnter = container.querySelector('#btnConvertCommaToEnter');
		const socialHashtagsDisplay = container.querySelector('#socialHashtagsDisplay');
		const btnCopyHashtags = container.querySelector('#btnCopyHashtags');
		const inputOgFile = container.querySelector('#inputOgImage');

		const currentOgPreview = container.querySelector('#currentOgImagePreview');
		const btnRemoveCustomOg = container.querySelector('#btnRemoveCustomOg');
		const seoPreviewStatusText = container.querySelector('#seoPreviewStatusText');
		const seoFileNameInfo = container.querySelector('#seoFileNameInfo');
		const seoPreviewBadge = container.querySelector('#seoPreviewBadge');
		const seoDropzone = container.querySelector('#seoDropzone');

		const btnView = container.querySelector('#btnViewLivePage');
		const pageTag = container.querySelector('#currentEditingPageTag');
		const scoreDisplay = container.querySelector('#pageScoreDisplay');
		const seoPageStatusSwitch = container.querySelector('#seoPageStatusSwitch');
		const seoPageStatusBadge = container.querySelector('#seoPageStatusBadge');

		const titleCounter = container.querySelector('#titleCounter');
		const descCounter = container.querySelector('#descCounter');
		const kwCounter = container.querySelector('#kwCounter');

		const serpTitle = container.querySelector('#serpTitleDisplay');
		const serpDesc = container.querySelector('#serpDescDisplay');
		const serpUrl = container.querySelector('#serpUrlDisplay');
		const socialTitle = container.querySelector('#socialTitleDisplay');
		const socialDesc = container.querySelector('#socialDescDisplay');
		const socialImg = container.querySelector('#socialImageDisplay');

		const btnDiscard = container.querySelector('#btnDiscardChanges');
		const form = container.querySelector('#formSEO');
		const spinner = container.querySelector('#saveSpinner');
		const btnSave = container.querySelector('#btnSaveSEO');

		// Notificação padronizada usando Toastr / window.notificar
		function notify(type, message, title) {
			if (window.notificar) {
				window.notificar(type, message, title);
			} else if (typeof toastr !== 'undefined') {
				if (type === 'success') toastr.success(message, title || 'Sucesso!');
				else if (type === 'error') toastr.error(message, title || 'Erro!');
				else if (type === 'warning') toastr.warning(message, title || 'Aviso');
				else toastr.info(message, title || 'Informação');
			} else {
				console.log(`[SEO ${type}]`, message);
			}
		}

		// Escape seguro de strings HTML
		function escapeHtml(text) {
			if (!text) return '';
			return String(text)
				.replace(/&/g, '&amp;')
				.replace(/</g, '&lt;')
				.replace(/>/g, '&gt;')
				.replace(/"/g, '&quot;')
				.replace(/'/g, '&#039;');
		}

		// Parser de palavras-chave individuais separadas por linha (Enter)
		function parseKeywords(raw) {
			if (!raw) return [];
			const lines = String(raw).split(/\r?\n/);
			const result = [];
			const seen = new Set();
			for (let line of lines) {
				const trimmed = line.trim();
				if (!trimmed) continue;
				const lower = trimmed.toLowerCase();
				if (!seen.has(lower)) {
					seen.add(lower);
					result.push(trimmed);
				}
			}
			return result;
		}

		// Converte palavra-chave em formato Hashtag (#ProntoSaude)
		function keywordToHashtag(str) {
			if (!str) return '';
			let clean = str.replace(/[^\p{L}\p{N}]+/gu, ' ').trim();
			if (!clean) return '';
			return '#' + clean.split(/\s+/).map(w => w.charAt(0).toUpperCase() + w.slice(1).toLowerCase()).join('');
		}

		// Renderiza badges visuais de palavras-chave e atualiza bloco de hashtags
		function renderKeywordTags() {
			if (!inputKeywords || !keywordsTagsContainer) return;
			const kwList = parseKeywords(inputKeywords.value);

			keywordsTagsContainer.innerHTML = '';
			if (kwList.length === 0) {
				keywordsTagsContainer.innerHTML = '<span class="text-muted small fst-italic">Nenhuma palavra-chave adicionada ainda. Digite no campo acima e pressione Enter.</span>';
			} else {
				kwList.forEach(kw => {
					const badge = document.createElement('span');
					badge.className = 'badge bg-primary-subtle text-primary border border-primary-subtle d-inline-flex align-items-center gap-1 px-2 py-1 fs-6 shadow-sm';
					badge.innerHTML = `<span>${escapeHtml(kw)}</span><button type="button" class="btn-close btn-close-sm ms-1" style="font-size: 0.60rem;" title="Remover termo"></button>`;
					
					const closeBtn = badge.querySelector('button');
					closeBtn.addEventListener('click', function(e) {
						e.preventDefault();
						removeKeyword(kw);
					});

					keywordsTagsContainer.appendChild(badge);
				});
			}

			// Atualiza contagem de palavras-chave
			if (kwCounter) {
				kwCounter.textContent = `${kwList.length} palavra${kwList.length === 1 ? '' : 's'}-chave`;
				kwCounter.className = kwList.length >= 3 ? 'badge text-bg-success' : 'badge text-bg-secondary';
			}

			// Atualiza bloco de hashtags no preview social
			if (socialHashtagsDisplay) {
				const hashtags = kwList.map(keywordToHashtag).filter(Boolean);
				if (!hashtags.some(h => h.toLowerCase() === '#prontosaude')) {
					hashtags.unshift('#ProntoSaude');
				}
				socialHashtagsDisplay.textContent = hashtags.join(' ');
			}
		}

		// Adiciona nova palavra-chave individual via Enter
		function addKeyword(term) {
			if (!term || !inputKeywords) return;
			const trimmed = term.trim();
			if (!trimmed) return;

			// Trata quebras de linha ou vírgulas caso venha colado
			const incoming = trimmed.split(/[\r\n,]+/).map(t => t.trim()).filter(Boolean);
			const current = parseKeywords(inputKeywords.value);

			let added = 0;
			incoming.forEach(t => {
				const lower = t.toLowerCase();
				if (!current.some(c => c.toLowerCase() === lower)) {
					current.push(t);
					added++;
				}
			});

			if (added > 0) {
				inputKeywords.value = current.join('\n');
				isDirty = true;
				renderKeywordTags();
				updateLiveView();
				notify('info', `${added} palavra-chave adicionada.`, 'Palavra-chave');
			} else {
				notify('warning', 'Essa palavra-chave já está cadastrada nesta página.', 'Termo Repetido');
			}
		}

		// Remove palavra-chave individual
		function removeKeyword(termToRemove) {
			if (!inputKeywords) return;
			const current = parseKeywords(inputKeywords.value);
			const lower = termToRemove.toLowerCase();
			const updated = current.filter(c => c.toLowerCase() !== lower);
			inputKeywords.value = updated.join('\n');
			isDirty = true;
			renderKeywordTags();
			updateLiveView();
		}

		// Cálculo da Pontuação Técnica de SEO (0 a 100)
		function calculateScore(title, desc, kw) {
			let score = 30; // Base por URL segura e canonical
			if (title.length >= 40 && title.length <= 65) score += 30;
			else if (title.length >= 25) score += 15;
			else if (title.length > 0) score += 5;

			if (desc.length >= 100 && desc.length <= 165) score += 30;
			else if (desc.length >= 60) score += 15;
			else if (desc.length > 0) score += 5;

			const kwList = parseKeywords(kw);
			if (kwList.length >= 5) score += 10;
			else if (kwList.length >= 2) score += 5;

			return Math.min(score, 100);
		}

		// Atualização da interface ao vivo (contadores, SERP e Social)
		function updateLiveView() {
			if (!inputTitle || !inputDesc || !inputKeywords) return;
			const title = inputTitle.value.trim();
			const desc = inputDesc.value.trim();
			const kw = inputKeywords.value.trim();

			// Contador Título
			if (titleCounter) {
				const len = title.length;
				titleCounter.textContent = `${len} / 60 caracteres`;
				if (len >= 45 && len <= 65) {
					titleCounter.className = 'badge text-bg-success';
				} else if (len >= 30 && len < 45) {
					titleCounter.className = 'badge text-bg-info';
				} else if (len > 65) {
					titleCounter.className = 'badge text-bg-warning';
				} else {
					titleCounter.className = 'badge text-bg-secondary';
				}
			}

			// Contador Descrição
			if (descCounter) {
				const len = desc.length;
				descCounter.textContent = `${len} / 160 caracteres`;
				if (len >= 120 && len <= 165) {
					descCounter.className = 'badge text-bg-success';
				} else if (len >= 70 && len < 120) {
					descCounter.className = 'badge text-bg-info';
				} else if (len > 165) {
					descCounter.className = 'badge text-bg-warning';
				} else {
					descCounter.className = 'badge text-bg-secondary';
				}
			}

			// Previews Google SERP
			if (serpTitle) serpTitle.textContent = title || 'Título da Página Não Definido';
			if (serpDesc) serpDesc.textContent = desc || 'Adicione uma meta descrição para visualizar o snippet completo do Google.';

			// Previews Social Card
			if (socialTitle) socialTitle.textContent = title || 'Pronto Saúde';
			if (socialDesc) socialDesc.textContent = desc || 'Descrição da página para compartilhamento nas redes sociais.';

			// Checklist Visual
			const chkTitle = container.querySelector('#chkTitle');
			if (chkTitle) {
				const ok = title.length >= 40 && title.length <= 65;
				chkTitle.innerHTML = `<i class="bi bi-${ok ? 'check-circle-fill text-success' : 'dash-circle text-muted'}"></i> <span>Título com comprimento ideal (recomendado: 40 a 65 caracteres - atual: ${title.length})</span>`;
			}

			const chkDesc = container.querySelector('#chkDesc');
			if (chkDesc) {
				const ok = desc.length >= 100 && desc.length <= 165;
				chkDesc.innerHTML = `<i class="bi bi-${ok ? 'check-circle-fill text-success' : 'dash-circle text-muted'}"></i> <span>Meta descrição persuasiva e completa (recomendado: 100 a 165 caracteres - atual: ${desc.length})</span>`;
			}

			const chkKeywords = container.querySelector('#chkKeywords');
			if (chkKeywords) {
				const kwList = parseKeywords(kw);
				const ok = kwList.length >= 3;
				chkKeywords.innerHTML = `<i class="bi bi-${ok ? 'check-circle-fill text-success' : 'dash-circle text-muted'}"></i> <span>Palavras-chave individuais cadastradas (${kwList.length} identificada${kwList.length === 1 ? '' : 's'} por linha)</span>`;
			}

			// Score
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
				badge.className = score >= 80 
					? 'badge rounded-pill bg-success-subtle text-success border border-success-subtle seo-item-badge flex-shrink-0' 
					: 'badge rounded-pill bg-warning-subtle text-warning border border-warning-subtle seo-item-badge flex-shrink-0';
			}

			// Controle do botão de descartar
			if (btnDiscard) {
				if (isDirty) btnDiscard.classList.remove('d-none');
				else btnDiscard.classList.add('d-none');
			}
		}

		// Restaura Dropzone para o Banner original da página
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
				seoFileNameInfo.textContent = 'Proporção Widescreen 1.91:1 • Otimizada para WhatsApp, Facebook, Twitter e LinkedIn';
			}
			if (seoPreviewBadge) {
				seoPreviewBadge.textContent = '1200 x 630 px (1.91:1)';
				seoPreviewBadge.className = 'badge bg-success-subtle text-success border border-success-subtle small';
			}
			if (btnRemoveCustomOg) btnRemoveCustomOg.classList.add('d-none');
		}

		// Carrega os dados de uma página selecionada no formulário
		function loadPageData(index) {
			currentIndex = index;
			isDirty = false;
			const pg = pages[index];
			if (!pg) return;

			if (inputType) inputType.value = pg.type || 'page';
			if (inputId) inputId.value = pg.id || 0;
			if (inputSlug) inputSlug.value = pg.slug || '';
			if (inputName) inputName.value = pg.name || '';
			if (inputTitle) inputTitle.value = pg.title || '';
			if (inputDesc) inputDesc.value = pg.description || '';
			if (inputKeywords) {
				inputKeywords.value = pg.keywords || '';
				renderKeywordTags();
			}
			if (btnView) btnView.href = pg.url || '#';
			if (pageTag) pageTag.textContent = pg.name || 'Página';
			if (serpUrl) serpUrl.textContent = pg.url || '<?= BASE_URL ?>';

			// Sincroniza switch de status da página
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

			// Atualiza lista na barra lateral
			selectors.forEach((btn, i) => {
				if (i === index) {
					btn.classList.add('active');
					btn.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
				} else {
					btn.classList.remove('active');
				}
			});

			resetDropzoneToOriginal();
			updateLiveView();
		}

		// Processamento de nova imagem selecionada/arrastada
		function handleNewOgFile(file) {
			if (!file) return;
			if (!file.type.startsWith('image/')) {
				notify('error', 'Selecione apenas arquivos de imagem válidos (JPG, PNG, WEBP ou GIF).', 'Formato Inválido');
				return;
			}

			if (file.size > 5 * 1024 * 1024) {
				notify('error', 'A imagem selecionada ultrapassa o limite de 5MB.', 'Arquivo Muito Pesado');
				return;
			}

			const reader = new FileReader();
			reader.onload = function(e) {
				const dataUrl = e.target.result;
				if (currentOgPreview) currentOgPreview.src = dataUrl;
				if (socialImg) socialImg.src = dataUrl;

				// Atualiza miniatura ativa
				const activeSelector = selectors[currentIndex];
				if (activeSelector) {
					const thumb = activeSelector.querySelector('img');
					if (thumb) thumb.src = dataUrl;
				}

				// Mede dimensões reais da imagem
				const testImg = new Image();
				testImg.onload = function() {
					const w = this.naturalWidth;
					const h = this.naturalHeight;
					const ratio = (w / h).toFixed(2);
					const isIdealRatio = ratio >= 1.75 && ratio <= 2.05;

					if (seoPreviewBadge) {
						if (isIdealRatio) {
							seoPreviewBadge.textContent = `${w} x ${h} px (Proporção Ideal ${ratio}:1)`;
							seoPreviewBadge.className = 'badge bg-success text-white small';
						} else {
							seoPreviewBadge.textContent = `${w} x ${h} px (Proporção ${ratio}:1)`;
							seoPreviewBadge.className = 'badge bg-warning text-dark small';
						}
					}

					if (seoPreviewStatusText) {
						seoPreviewStatusText.innerHTML = '<i class="bi bi-check-circle-fill text-success"></i> Nova Imagem Carregada';
					}

					if (seoFileNameInfo) {
						const sizeKb = (file.size / 1024).toFixed(1);
						seoFileNameInfo.innerHTML = `<strong>${file.name}</strong> (${sizeKb} KB) • Pronta para salvar nesta página`;
					}

					if (btnRemoveCustomOg) btnRemoveCustomOg.classList.remove('d-none');
					isDirty = true;
					updateLiveView();
					notify('info', `Imagem pronta: ${w}x${h}px. Clique em "Salvar" para confirmar.`, 'Imagem Carregada');
				};
				testImg.src = dataUrl;
			};
			reader.readAsDataURL(file);
		}

		// Eventos da Dropzone
		if (seoDropzone) {
			seoDropzone.addEventListener('click', function() {
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

		// Suporte a Paste (Ctrl+V) de Imagem
		window.addEventListener('paste', function(e) {
			const items = (e.clipboardData || e.originalEvent.clipboardData).items;
			for (let i = 0; i < items.length; i++) {
				if (items[i].type.indexOf('image') !== -1) {
					const blob = items[i].getAsFile();
					if (blob) {
						handleNewOgFile(blob);
						break;
					}
				}
			}
		});

		// Monitor de inputs para Dirty state e Preview
		[inputTitle, inputDesc].forEach(el => {
			if (!el) return;
			el.addEventListener('input', function() {
				isDirty = true;
				updateLiveView();
			});
		});

		if (inputKeywords) {
			inputKeywords.addEventListener('input', function() {
				isDirty = true;
				renderKeywordTags();
				updateLiveView();
			});
		}

		// Adição rápida de palavra-chave com a tecla Enter
		if (quickAddKeyword) {
			quickAddKeyword.addEventListener('keydown', function(e) {
				if (e.key === 'Enter') {
					e.preventDefault();
					const val = this.value;
					if (val && val.trim()) {
						addKeyword(val);
						this.value = '';
					}
				}
			});
		}

		if (btnAddKeyword && quickAddKeyword) {
			btnAddKeyword.addEventListener('click', function(e) {
				e.preventDefault();
				const val = quickAddKeyword.value;
				if (val && val.trim()) {
					addKeyword(val);
					quickAddKeyword.value = '';
					quickAddKeyword.focus();
				}
			});
		}

		// Utilitário para converter vírgulas/ponto-e-vírgula em quebras de linha (Enter)
		if (btnConvertCommaToEnter && inputKeywords) {
			btnConvertCommaToEnter.addEventListener('click', function(e) {
				e.preventDefault();
				const raw = inputKeywords.value;
				if (!raw.trim()) {
					notify('warning', 'O campo de palavras-chave está vazio.', 'Aviso');
					return;
				}
				const converted = raw
					.split(/[,;]+/)
					.map(s => s.trim())
					.filter(Boolean)
					.join('\n');
				inputKeywords.value = converted;
				isDirty = true;
				renderKeywordTags();
				updateLiveView();
				const total = parseKeywords(converted).length;
				notify('success', `Convertido! ${total} palavras-chave agora organizadas individualmente por linha (Enter).`, 'Lista Atualizada');
			});
		}

		// Cópia de hashtags prontas para redes sociais
		if (btnCopyHashtags && socialHashtagsDisplay) {
			btnCopyHashtags.addEventListener('click', function(e) {
				e.preventDefault();
				const text = socialHashtagsDisplay.textContent.trim();
				if (!text) {
					notify('warning', 'Nenhuma hashtag disponível para copiar.', 'Aviso');
					return;
				}
				if (navigator.clipboard && navigator.clipboard.writeText) {
					navigator.clipboard.writeText(text).then(() => {
						notify('success', 'Hashtags copiadas para a área de transferência!', 'Copiado');
					}).catch(() => {
						notify('info', text, 'Hashtags Geradas');
					});
				} else {
					notify('info', text, 'Hashtags Geradas');
				}
			});
		}

		// Descartar alterações não salvas
		if (btnDiscard) {
			btnDiscard.addEventListener('click', function() {
				if (typeof Swal !== 'undefined') {
					Swal.fire({
						title: 'Descartar alterações?',
						text: 'Todas as modificações não salvas nesta página serão revertidas.',
						icon: 'warning',
						showCancelButton: true,
						confirmButtonColor: '#dc3545',
						cancelButtonColor: '#6c757d',
						confirmButtonText: 'Sim, descartar',
						cancelButtonText: 'Cancelar'
					}).then(res => {
						if (res.isConfirmed) {
							loadPageData(currentIndex);
							notify('info', 'Alterações descartadas.', 'Aviso');
						}
					});
				} else {
					loadPageData(currentIndex);
				}
			});
		}

		// Alternar status da página via switch AJAX
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
				formData.append('action', 'toggle_page_status');
				formData.append('page_id', pg.id);
				formData.append('page_slug', pg.slug);
				formData.append('status', newStatus);

				fetch('seo.php', {
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
						// Atualiza indicador na lista
						const activeSelector = selectors[currentIndex];
						if (activeSelector) {
							const nameEl = activeSelector.querySelector('strong');
							let offBadge = activeSelector.querySelector('.text-bg-danger');
							if (newStatus === 'Inactive') {
								if (!offBadge && nameEl) {
									nameEl.insertAdjacentHTML('afterend', '<span class="badge text-bg-danger rounded-pill flex-shrink-0 p-1" style="font-size: 8px;">Off</span>');
								}
							} else {
								if (offBadge) offBadge.remove();
							}
						}
						notify('success', `A página "${pg.name}" foi ${newStatus === 'Active' ? 'ativada' : 'desativada'} no site!`, 'Status Atualizado');
					} else {
						this.checked = !this.checked;
						notify('error', data.message || 'Falha ao alterar status.', 'Erro');
					}
				})
				.catch(err => {
					this.disabled = false;
					this.checked = !this.checked;
					notify('error', 'Falha na comunicação com o servidor.', 'Erro');
				});
			});
		}

		// Clique na lista de páginas com proteção de alterações não salvas
		selectors.forEach((btn, idx) => {
			btn.addEventListener('click', function(e) {
				e.preventDefault();
				if (currentIndex === idx) return;

				if (isDirty && typeof Swal !== 'undefined') {
					Swal.fire({
						title: 'Alterações não salvas!',
						text: `Você fez alterações na página "${pages[currentIndex].name}" que ainda não foram salvas. Deseja descartá-las e trocar de página?`,
						icon: 'warning',
						showCancelButton: true,
						confirmButtonColor: '#0a6b74',
						cancelButtonColor: '#6c757d',
						confirmButtonText: 'Sim, descartar e trocar',
						cancelButtonText: 'Continuar editando'
					}).then(res => {
						if (res.isConfirmed) {
							loadPageData(idx);
							syncUrlHash();
						}
					});
				} else {
					loadPageData(idx);
					syncUrlHash();
				}
			});
		});

		// Filtro de Busca em Tempo Real
		if (searchInput) {
			searchInput.addEventListener('input', function() {
				const q = this.value.toLowerCase().trim();
				if (btnClearSearch) {
					if (q) btnClearSearch.classList.remove('d-none');
					else btnClearSearch.classList.add('d-none');
				}

				let visibleCount = 0;
				selectors.forEach(btn => {
					const name = (btn.getAttribute('data-name') || '').toLowerCase();
					const slug = (btn.getAttribute('data-slug') || '').toLowerCase();
					if (name.includes(q) || slug.includes(q)) {
						btn.style.display = '';
						visibleCount++;
					} else {
						btn.style.display = 'none';
					}
				});

				if (noResultsMsg) {
					if (visibleCount === 0) noResultsMsg.classList.remove('d-none');
					else noResultsMsg.classList.add('d-none');
				}

				if (pagesCountBadge) {
					pagesCountBadge.textContent = `${visibleCount} de ${pages.length}`;
				}
			});
		}

		if (btnClearSearch) {
			btnClearSearch.addEventListener('click', function() {
				if (searchInput) {
					searchInput.value = '';
					searchInput.dispatchEvent(new Event('input'));
					searchInput.focus();
				}
			});
		}

		// Submissão do Formulário de SEO via AJAX de Alta Precisão
		if (form) {
			form.addEventListener('submit', function(e) {
				e.preventDefault();

				const titleVal = (inputTitle ? inputTitle.value : '').trim();
				if (!titleVal) {
					notify('warning', 'O título da página (Meta Title) é obrigatório.', 'Campo Obrigatório');
					if (inputTitle) inputTitle.focus();
					return;
				}

				if (spinner) spinner.classList.remove('d-none');
				if (btnSave) btnSave.disabled = true;

				const formData = new FormData(this);

				fetch('seo.php', {
					method: 'POST',
					body: formData
				})
				.then(res => {
					if (!res.ok) throw new Error('HTTP ' + res.status);
					return res.json();
				})
				.then(data => {
					if (spinner) spinner.classList.add('d-none');
					if (btnSave) btnSave.disabled = false;

					if (data.success) {
						isDirty = false;
						if (btnDiscard) btnDiscard.classList.add('d-none');

						// Atualiza o cache local dos dados da página
						if (pages[currentIndex]) {
							pages[currentIndex].title = inputTitle.value.trim();
							pages[currentIndex].description = inputDesc.value.trim();
							pages[currentIndex].keywords = inputKeywords.value.trim();
							if (data.new_banner) {
								pages[currentIndex].banner = data.new_banner;
								if (currentOgPreview) currentOgPreview.src = data.new_banner;
								if (socialImg) socialImg.src = data.new_banner;
								const activeSelector = selectors[currentIndex];
								if (activeSelector) {
									const thumb = activeSelector.querySelector('img');
									if (thumb) thumb.src = data.new_banner;
								}
							}
						}

						if (btnRemoveCustomOg) btnRemoveCustomOg.classList.add('d-none');
						if (inputOgFile) inputOgFile.value = '';
						if (seoPreviewStatusText) {
							seoPreviewStatusText.innerHTML = '<i class="bi bi-image text-primary"></i> Imagem Ativa desta Página';
						}

						notify('success', data.message || 'Otimização de SEO salva com sucesso!', 'SEO Atualizado');
					} else {
						notify('error', data.message || 'Falha ao salvar metadados de SEO.', 'Atenção');
					}
				})
				.catch(err => {
					if (spinner) spinner.classList.add('d-none');
					if (btnSave) btnSave.disabled = false;
					notify('error', 'Falha na comunicação com o servidor ao salvar o formulário.', 'Erro');
				});
			});
		}

		// Alternador Desktop/Mobile no SERP Preview
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

		// Atalho de Teclado Global Ctrl+S para Salvar
		window.addEventListener('keydown', function(e) {
			if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 's') {
				const activePane = container.querySelector('#tab-form.active');
				if (activePane) {
					e.preventDefault();
					if (btnSave && !btnSave.disabled) {
						btnSave.click();
					}
				}
			}
		});

		// =========================================================================
		// DEEP-LINKING PRECISO COM SUPORTE A #tab-form, #tab-google, #tab-social
		// =========================================================================
		function syncUrlHash() {
			const activeTabBtn = container.querySelector('#seoTabs button.active');
			const targetId = activeTabBtn ? activeTabBtn.getAttribute('data-bs-target') : '#tab-form';
			if (window.history && window.history.replaceState) {
				const pg = pages[currentIndex];
				const pageParam = pg && pg.slug ? `?page=${encodeURIComponent(pg.slug)}` : '';
				window.history.replaceState(null, '', window.location.pathname + pageParam + targetId);
			}
		}

		function handleHashAndUrl() {
			const hash = window.location.hash || '#tab-form';
			const params = new URLSearchParams(window.location.search);
			const requestedPage = params.get('page') || params.get('slug');
			const requestedId = params.get('id');

			// Seleciona página solicitada via parâmetro se houver
			if (requestedPage || requestedId) {
				const targetIndex = pages.findIndex(p => {
					if (requestedId && String(p.id) === String(requestedId)) return true;
					if (requestedPage) {
						const cleanReq = requestedPage.replace('.php', '').toLowerCase();
						const cleanP = (p.slug || '').replace('.php', '').toLowerCase();
						return cleanReq === cleanP || (p.type === 'home' && cleanReq === 'home');
					}
					return false;
				});
				if (targetIndex !== -1) {
					loadPageData(targetIndex);
				} else {
					loadPageData(0);
				}
			} else {
				loadPageData(0);
			}

			// Ativa a aba solicitada (padrão: #tab-form)
			const validTabs = ['#tab-form', '#tab-google', '#tab-social'];
			const targetTab = validTabs.includes(hash) ? hash : '#tab-form';
			const tabBtn = container.querySelector(`button[data-bs-target="${targetTab}"]`);

			if (tabBtn) {
				if (typeof bootstrap !== 'undefined' && bootstrap.Tab) {
					const tabInstance = bootstrap.Tab.getOrCreateInstance 
						? bootstrap.Tab.getOrCreateInstance(tabBtn) 
						: new bootstrap.Tab(tabBtn);
					tabInstance.show();
				} else if (window.jQuery) {
					jQuery(tabBtn).tab('show');
				}

				// Se acessado diretamente via hash, realiza scroll suave para o workspace
				if (window.location.hash) {
					const workspace = container.querySelector('#seoWorkspace');
					if (workspace) {
						setTimeout(() => {
							workspace.scrollIntoView({ behavior: 'smooth', block: 'start' });
						}, 150);
					}
				}
			}
		}

		// Atualiza URL ao trocar de abas
		const tabButtons = container.querySelectorAll('#seoTabs button[data-bs-toggle="tab"]');
		tabButtons.forEach(btn => {
			btn.addEventListener('shown.bs.tab', function(e) {
				syncUrlHash();
			});
		});

		// Monitora evento hashchange do navegador
		window.addEventListener('hashchange', function() {
			const hash = window.location.hash;
			const tabBtn = container.querySelector(`button[data-bs-target="${hash}"]`);
			if (tabBtn && typeof bootstrap !== 'undefined' && bootstrap.Tab) {
				const tabInstance = bootstrap.Tab.getOrCreateInstance 
					? bootstrap.Tab.getOrCreateInstance(tabBtn) 
					: new bootstrap.Tab(tabBtn);
				tabInstance.show();
			}
		});

		// Executa ativação inicial
		handleHashAndUrl();
	};

	// Inicialização segura no carregamento do DOM
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
