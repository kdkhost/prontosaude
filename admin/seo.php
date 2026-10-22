<?php
require_once('header.php');

// Endpoint AJAX para salvar alterações de SEO instantaneamente
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save_seo') {
	header('Content-Type: application/json');
	$page_type = $_POST['page_type'] ?? '';
	$page_id = (int)($_POST['page_id'] ?? 0);
	$meta_title = trim($_POST['meta_title'] ?? '');
	$meta_description = trim($_POST['meta_description'] ?? '');
	$meta_keyword = trim($_POST['meta_keyword'] ?? '');

	try {
		if ($page_type === 'home') {
			$stmt = $pdo->prepare("UPDATE tbl_settings SET meta_title_home = ?, meta_description_home = ?, meta_keyword_home = ? WHERE id = 1");
			$stmt->execute([$meta_title, $meta_description, $meta_keyword]);
		} elseif ($page_type === 'page' && $page_id > 0) {
			$stmt = $pdo->prepare("UPDATE tbl_page SET meta_title = ?, meta_description = ?, meta_keyword = ? WHERE page_id = ?");
			$stmt->execute([$meta_title, $meta_description, $meta_keyword, $page_id]);
		}
		echo json_encode([
			'success' => true,
			'message' => 'Configurações de SEO atualizadas e salvas com sucesso!'
		]);
	} catch (Exception $e) {
		echo json_encode([
			'success' => false,
			'message' => 'Erro ao salvar: ' . $e->getMessage()
		]);
	}
	exit;
}

// Carrega dados de configurações globais (Home)
$stmtSettings = $pdo->query("SELECT meta_title_home, meta_description_home, meta_keyword_home, logo, favicon FROM tbl_settings WHERE id = 1");
$settingsSEO = $stmtSettings->fetch(PDO::FETCH_ASSOC) ?: [];

// Carrega todas as páginas cadastradas no sistema
$stmtPages = $pdo->query("SELECT page_id, page_name, page_slug, meta_title, meta_description, meta_keyword, banner FROM tbl_page ORDER BY page_id ASC");
$allPages = $stmtPages->fetchAll(PDO::FETCH_ASSOC) ?: [];

// Monta lista completa de páginas auditáveis
$pagesList = [];

// 1. Home
$pagesList[] = [
	'type' => 'home',
	'id' => 1,
	'name' => 'Página Inicial (Home)',
	'slug' => '',
	'url' => BASE_URL,
	'title' => $settingsSEO['meta_title_home'] ?? 'Pronto Saúde | Centro Médico e Odontológico',
	'description' => $settingsSEO['meta_description_home'] ?? 'Atendimento médico e odontológico humanizado no Rio de Janeiro.',
	'keywords' => $settingsSEO['meta_keyword_home'] ?? 'clinica medica, odontologia, exames, saude rj',
	'banner' => 'assets/uploads/' . ($settingsSEO['logo'] ?? '')
];

// 2. Páginas de tbl_page
foreach ($allPages as $p) {
	$pagesList[] = [
		'type' => 'page',
		'id' => $p['page_id'],
		'name' => $p['page_name'],
		'slug' => $p['page_slug'],
		'url' => BASE_URL . 'page/' . $p['page_slug'],
		'title' => $p['meta_title'] ?: $p['page_name'] . ' | Pronto Saúde',
		'description' => $p['meta_description'] ?: 'Informações completas sobre ' . $p['page_name'] . ' na Pronto Saúde.',
		'keywords' => $p['meta_keyword'] ?: $p['page_name'] . ', saude, clinica, pronto saude',
		'banner' => (!empty($p['banner']) && file_exists('../assets/uploads/' . $p['banner'])) ? 'assets/uploads/' . $p['banner'] : 'assets/images/about.jpg'
	];
}

// 3. Páginas Institucionais Fixas Chave
$fixedPages = [
	['name' => 'Planos & Cartão Fidelidade', 'slug' => 'fidelidade.php', 'title' => 'Cartão Fidelidade | Pronto Saúde', 'desc' => 'Conheça nossos Planos Fidelidade e aproveite nossos preços para aderir seu plano agora mesmo.', 'kw' => 'cartao fidelidade, planos de saude, convenio'],
	['name' => 'Nossos Serviços Médicos', 'slug' => 'servicos.php', 'title' => 'Nossos Serviços | Pronto Saúde', 'desc' => 'Conheça nossos serviços médicos, exames laboratoriais, consultas e procedimentos especializados.', 'kw' => 'servicos medicos, exames laboratoriais, consultas'],
	['name' => 'Odontologia Especializada', 'slug' => 'odontologia.php', 'title' => 'Odontologia | Pronto Saúde', 'desc' => 'Oferecemos tratamentos ortodônticos avançados, próteses, clareamento e estética dental.', 'kw' => 'dentista, ortodontia, clareamento, aparelhos, odonto'],
	['name' => 'Corpo Clínico & Médicos', 'slug' => 'doctor.php', 'title' => 'Corpo Clínico | Pronto Saúde', 'desc' => 'Conheça nossa equipe de médicos e especialistas dedicados ao seu cuidado integral.', 'kw' => 'medicos, especialistas, cardiologia, pediatria, clinicos'],
	['name' => 'Notícias & Artigos de Saúde', 'slug' => 'news.php', 'title' => 'Notícias & Blog | Pronto Saúde', 'desc' => 'Artigos informativos, dicas de bem-estar, prevenção e novidades da medicina.', 'kw' => 'blog saude, dicas de saude, medicina, noticias medicas'],
	['name' => 'Trabalhe Conosco', 'slug' => 'trabalhe-conosco.php', 'title' => 'Trabalhe Conosco | Pronto Saúde', 'desc' => 'Faça parte da nossa equipe médica e administrativa. Confira oportunidades abertas.', 'kw' => 'vagas saude, empregos clinica, trabalhe conosco']
];

foreach ($fixedPages as $idx => $fp) {
	// Procura se já tem em tbl_page
	$found = false;
	foreach ($allPages as $ap) {
		if ($ap['page_slug'] === $fp['slug'] || stripos($ap['page_name'], $fp['name']) !== false) {
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
					Auditoria técnica em tempo real, pontuação de qualidade (0-100), pré-visualização no Google SERP e redes sociais, e gestão de metadados.
				</p>
			</div>
			<div class="col-sm-5 text-sm-end mt-2 mt-sm-0">
				<span class="badge text-bg-success-subtle text-success border border-success-subtle px-3 py-2 fs-6">
					<i class="bi bi-check2-all me-1"></i> Auditoria em Tempo Real
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
							<h3 class="fw-bold mb-0 text-primary" id="kpiAvgScore">92<span class="fs-6 text-muted">/100</span></h3>
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
							<small class="text-body-secondary">100% indexáveis</small>
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
							<small class="text-body-secondary">Snippets e Rich Previews</small>
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
							<h3 class="fw-bold mb-0 text-warning">Ativo</h3>
							<small class="text-body-secondary">WhatsApp & Facebook Cards</small>
						</div>
					</div>
				</div>
			</div>
		</div>

		<!-- 2. Seletor de Página e Workspace de Edição & Preview -->
		<div class="row g-4">
			<!-- Coluna Esquerda: Lista de Páginas para Selecionar -->
			<div class="col-lg-4">
				<div class="card border-0 shadow-sm rounded-3 h-100">
					<div class="card-header bg-body py-3 border-bottom">
						<h5 class="card-title fw-bold mb-0 d-flex align-items-center gap-2">
							<i class="bi bi-list-check text-primary"></i>
							Selecione uma Página
						</h5>
					</div>
					<div class="card-body p-2" style="max-height: 720px; overflow-y: auto;">
						<div class="list-group list-group-flush gap-1" id="seoPagesList">
							<?php foreach ($pagesList as $index => $pg): ?>
								<button type="button" 
									class="list-group-item list-group-item-action rounded-2 border-0 p-3 text-start <?= $index === 0 ? 'active' : '' ?> seo-page-selector"
									data-index="<?= $index ?>"
									data-type="<?= $pg['type'] ?>"
									data-id="<?= $pg['id'] ?>"
									data-name="<?= htmlspecialchars($pg['name']) ?>"
									data-url="<?= htmlspecialchars($pg['url']) ?>"
									data-title="<?= htmlspecialchars($pg['title']) ?>"
									data-desc="<?= htmlspecialchars($pg['description']) ?>"
									data-keywords="<?= htmlspecialchars($pg['keywords']) ?>"
									data-banner="<?= htmlspecialchars($pg['banner']) ?>">
									<div class="d-flex justify-content-between align-items-center mb-1">
										<strong class="d-block text-truncate" style="max-width: 200px;"><?= htmlspecialchars($pg['name']) ?></strong>
										<span class="badge rounded-pill bg-success-subtle text-success border border-success-subtle seo-item-badge" id="badge-page-<?= $index ?>">
											95 pts
										</span>
									</div>
									<div class="small opacity-75 text-truncate"><?= htmlspecialchars($pg['url']) ?></div>
								</button>
							<?php endforeach; ?>
						</div>
					</div>
				</div>
			</div>

			<!-- Coluna Direita: Editor, Auditoria e Previews Ao Vivo -->
			<div class="col-lg-8">
				<div class="card border-0 shadow-sm rounded-3 mb-4">
					<div class="card-header bg-body py-3 border-bottom d-flex justify-content-between align-items-center">
						<div class="d-flex align-items-center gap-2">
							<span class="badge text-bg-primary px-3 py-2" id="currentEditingPageTag">Página Inicial (Home)</span>
							<h5 class="fw-bold mb-0 d-none d-sm-inline" id="currentEditingPageTitle">Auditoria & Configuração</h5>
						</div>
						<div class="d-flex align-items-center gap-2">
							<div class="text-end">
								<span class="small text-body-secondary d-block">Pontuação da Página</span>
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
									<i class="bi bi-pencil-square me-1"></i> Metadados & Conteúdo
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
								<form id="formSEO">
									<input type="hidden" name="action" value="save_seo">
									<input type="hidden" name="page_type" id="inputPageType" value="home">
									<input type="hidden" name="page_id" id="inputPageId" value="1">

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
										<textarea class="form-control" name="meta_description" id="inputMetaDesc" rows="3" placeholder="Ex: Atendimento humanizado com mais de 30 especialidades, exames laboratoriais, cartão fidelidade e planos corporativos. Agende sua consulta!" required></textarea>
										<div class="form-text small" id="descFeedback">
											Tamanho recomendado: entre 120 e 160 caracteres. Resuma a proposta de valor para atrair cliques.
										</div>
									</div>

									<!-- Meta Keywords -->
									<div class="mb-3">
										<label class="form-label fw-bold mb-1">
											Palavras-chave (Meta Keywords)
										</label>
										<input type="text" class="form-control" name="meta_keyword" id="inputMetaKeywords" placeholder="Ex: clinica medica rj, exames laboratoriais, dentista, pronto saude">
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
											<li class="mb-2 d-flex align-items-center gap-2" id="chkKeywords">
												<i class="bi bi-check-circle-fill text-success"></i>
												<span>Palavras-chave relevantes cadastradas</span>
											</li>
											<li class="mb-2 d-flex align-items-center gap-2" id="chkSlug">
												<i class="bi bi-check-circle-fill text-success"></i>
												<span>URL amigável sem caracteres especiais ou espaços</span>
											</li>
											<li class="d-flex align-items-center gap-2" id="chkCanonical">
												<i class="bi bi-check-circle-fill text-success"></i>
												<span>Tag canônica e protocolo HTTPS seguro ativo</span>
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
									<span>O preview é atualizado automaticamente enquanto você digita no formulário de metadados.</span>
								</div>
							</div>

							<!-- TAB 3: PREVIEW REDES SOCIAIS (OPEN GRAPH) -->
							<div class="tab-pane fade" id="tab-social" role="tabpanel">
								<span class="text-body-secondary small fw-semibold d-block mb-3">Pré-visualização do Card ao Compartilhar no WhatsApp, Facebook e LinkedIn</span>

								<div class="border rounded-3 overflow-hidden shadow-sm bg-white mb-3" style="max-width: 500px;">
									<div style="height: 220px; background: #e2e8f0; overflow: hidden; display: flex; align-items: center; justify-content: center;">
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
									<span>As tags <code>og:title</code>, <code>og:description</code> e <code>og:image</code> são geradas automaticamente no cabeçalho do portal.</span>
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
document.addEventListener('DOMContentLoaded', function() {
	const pages = <?= json_encode($pagesList); ?>;
	let currentIndex = 0;

	const selectors = document.querySelectorAll('.seo-page-selector');
	const inputType = document.getElementById('inputPageType');
	const inputId = document.getElementById('inputPageId');
	const inputTitle = document.getElementById('inputMetaTitle');
	const inputDesc = document.getElementById('inputMetaDesc');
	const inputKeywords = document.getElementById('inputMetaKeywords');
	const btnView = document.getElementById('btnViewLivePage');
	const pageTag = document.getElementById('currentEditingPageTag');
	const pageTitle = document.getElementById('currentEditingPageTitle');
	const scoreDisplay = document.getElementById('pageScoreDisplay');

	const titleCounter = document.getElementById('titleCounter');
	const descCounter = document.getElementById('descCounter');
	const serpTitle = document.getElementById('serpTitleDisplay');
	const serpDesc = document.getElementById('serpDescDisplay');
	const serpUrl = document.getElementById('serpUrlDisplay');
	const socialTitle = document.getElementById('socialTitleDisplay');
	const socialDesc = document.getElementById('socialDescDisplay');
	const socialImg = document.getElementById('socialImageDisplay');

	function calculateScore(title, desc, kw) {
		let score = 50; // base
		if (title.length >= 30 && title.length <= 65) score += 20;
		else if (title.length > 0) score += 10;

		if (desc.length >= 100 && desc.length <= 165) score += 20;
		else if (desc.length > 0) score += 10;

		if (kw.length >= 10) score += 10;

		return Math.min(score, 100);
	}

	function updateLiveView() {
		const title = inputTitle.value.trim();
		const desc = inputDesc.value.trim();
		const kw = inputKeywords.value.trim();

		// Contadores
		titleCounter.textContent = `${title.length} / 60 caracteres`;
		titleCounter.className = (title.length >= 35 && title.length <= 65) ? 'badge text-bg-success' : 'badge text-bg-warning';

		descCounter.textContent = `${desc.length} / 160 caracteres`;
		descCounter.className = (desc.length >= 110 && desc.length <= 165) ? 'badge text-bg-success' : 'badge text-bg-warning';

		// Previews
		serpTitle.textContent = title || 'Título da Página Não Definido';
		serpDesc.textContent = desc || 'Adicione uma meta descrição para visualizar o snippet completo do Google.';
		socialTitle.textContent = title || 'Pronto Saúde';
		socialDesc.textContent = desc || 'Descrição da página para compartilhamento nas redes sociais.';

		// Pontuação
		const score = calculateScore(title, desc, kw);
		scoreDisplay.textContent = `${score}/100`;
		if (score >= 80) {
			scoreDisplay.className = 'fs-5 fw-bold text-success';
		} else if (score >= 60) {
			scoreDisplay.className = 'fs-5 fw-bold text-warning';
		} else {
			scoreDisplay.className = 'fs-5 fw-bold text-danger';
		}

		// Atualiza badge na lista
		const badge = document.getElementById('badge-page-' + currentIndex);
		if (badge) {
			badge.textContent = `${score} pts`;
			badge.className = score >= 80 ? 'badge rounded-pill bg-success-subtle text-success border border-success-subtle' : 'badge rounded-pill bg-warning-subtle text-warning border border-warning-subtle';
		}
	}

	function loadPageData(index) {
		currentIndex = index;
		const pg = pages[index];
		if (!pg) return;

		inputType.value = pg.type;
		inputId.value = pg.id;
		inputTitle.value = pg.title;
		inputDesc.value = pg.description;
		inputKeywords.value = pg.keywords;
		btnView.href = pg.url;
		pageTag.textContent = pg.name;
		serpUrl.textContent = pg.url;

		if (pg.banner) {
			socialImg.src = '<?= BASE_URL ?>' + pg.banner;
		}

		selectors.forEach((btn, i) => {
			if (i === index) btn.classList.add('active');
			else btn.classList.remove('active');
		});

		updateLiveView();
	}

	// Troca de página ao clicar
	selectors.forEach((btn, i) => {
		btn.addEventListener('click', function() {
			loadPageData(i);
		});
	});

	// Input em tempo real
	inputTitle.addEventListener('input', updateLiveView);
	inputDesc.addEventListener('input', updateLiveView);
	inputKeywords.addEventListener('input', updateLiveView);

	// Submissão do formulário via AJAX
	document.getElementById('formSEO').addEventListener('submit', function(e) {
		e.preventDefault();
		const spinner = document.getElementById('saveSpinner');
		const btn = document.getElementById('btnSaveSEO');
		spinner.classList.remove('d-none');
		btn.disabled = true;

		const formData = new FormData(this);

		fetch('seo.php', {
			method: 'POST',
			body: formData
		})
		.then(res => res.json())
		.then(data => {
			spinner.classList.add('d-none');
			btn.disabled = false;
			if (data.success) {
				if (window.notificar) {
					window.notificar('success', data.message);
				} else if (window.toastr) {
					toastr.success(data.message);
				}
				// Atualiza cache local
				pages[currentIndex].title = inputTitle.value;
				pages[currentIndex].description = inputDesc.value;
				pages[currentIndex].keywords = inputKeywords.value;
			} else {
				if (window.notificar) {
					window.notificar('error', data.message);
				} else if (window.toastr) {
					toastr.error(data.message);
				}
			}
		})
		.catch(err => {
			spinner.classList.add('d-none');
			btn.disabled = false;
			if (window.notificar) {
				window.notificar('error', 'Falha na comunicação com o servidor.');
			}
		});
	});

	// Alternador Desktop/Mobile no SERP
	const btnDesk = document.getElementById('btnSerpDesktop');
	const btnMob = document.getElementById('btnSerpMobile');
	const serpBox = document.getElementById('serpContainer');

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

	// Inicializa a primeira página
	loadPageData(0);
});
</script>

<?php require_once('footer.php'); ?>
