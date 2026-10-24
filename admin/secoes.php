<?php require_once('header.php'); ?>

<?php
// Sincroniza páginas institucionais nativas se a função estiver disponível
if (function_exists('pronto_sync_system_pages')) {
    pronto_sync_system_pages($pdo);
}

// Carrega as configurações de seções da Home
$statement = $pdo->prepare("SELECT * FROM tbl_settings WHERE id=1");
$statement->execute();
$config = $statement->fetch(PDO::FETCH_ASSOC) ?: [];

$status_hero_box    = (int)($config['hero_box_status'] ?? 1);
$status_risco       = (int)($config['home_risco_status'] ?? 1);
$status_about       = (int)($config['home_about_status'] ?? 1);
$status_service     = (int)($config['home_status_service'] ?? 1);
$status_department  = (int)($config['home_status_department'] ?? 1);
$status_doctor      = (int)($config['home_status_doctor'] ?? 1);
$status_pricing     = (int)($config['home_status_pricing'] ?? 1);
$status_testimonial = (int)($config['home_status_testimonial'] ?? 1);
$status_news        = (int)($config['home_status_news'] ?? 1);
$status_partner     = (int)($config['home_status_partner'] ?? 1);

// Modo de Manutenção
$status_maintenance = (int)($config['maintenance_mode'] ?? 0);
if (file_exists(__DIR__ . '/../.maintenance')) {
    $status_maintenance = 1;
}

// Carrega todas as páginas cadastradas em tbl_page
$st_pages = $pdo->query("SELECT * FROM tbl_page ORDER BY page_id ASC");
$all_pages = $st_pages->fetchAll(PDO::FETCH_ASSOC) ?: [];

$total_pages_active = 0;
$total_pages_inactive = 0;
foreach ($all_pages as $p) {
    if ($p['status'] === 'Active') {
        $total_pages_active++;
    } else {
        $total_pages_inactive++;
    }
}

// Slugs nativos conhecidos do sistema
$system_slugs = [
    'doctor', 'servicos', 'fidelidade', 'odontologia', 'news',
    'trabalhe-conosco', 'convenio', 'privacidade', 'rsocial',
    'fotos', 'videos', 'faq', 'contato', 'sobre', 'blog'
];

/**
 * Retorna ícone, cor e descrição contextual para cada página
 */
function getPageIconAndColor($slug, $layout = '') {
    $clean = strtolower(trim(str_replace('.php', '', $slug)));
    switch ($clean) {
        case 'doctor':
        case 'medicos':
            return [
                'icon' => 'bi-heart-pulse-fill',
                'color' => 'danger',
                'bg' => 'bg-danger-subtle text-danger',
                'category' => 'Corpo Clínico',
                'desc' => 'Equipe de médicos especialistas, corpo clínico qualificado e agendamentos.'
            ];
        case 'servicos':
        case 'services':
            return [
                'icon' => 'bi-bandaid-fill',
                'color' => 'primary',
                'bg' => 'bg-primary-subtle text-primary',
                'category' => 'Serviços & Exames',
                'desc' => 'Consultas especializadas, exames laboratoriais e procedimentos completos.'
            ];
        case 'fidelidade':
        case 'pricing':
            return [
                'icon' => 'bi-credit-card-2-front-fill',
                'color' => 'warning',
                'bg' => 'bg-warning-subtle text-warning',
                'category' => 'Planos & Cartão',
                'desc' => 'Tabela dos planos Fidelidade com descontos de até 50% em exames e consultas.'
            ];
        case 'odontologia':
        case 'dentista':
            return [
                'icon' => 'bi-shield-plus',
                'color' => 'info',
                'bg' => 'bg-info-subtle text-info',
                'category' => 'Odontologia',
                'desc' => 'Tratamentos ortodônticos, implantes, estética dental e profilaxia clínica.'
            ];
        case 'news':
        case 'blog':
            return [
                'icon' => 'bi-newspaper',
                'color' => 'success',
                'bg' => 'bg-success-subtle text-success',
                'category' => 'Notícias & Blog',
                'desc' => 'Artigos preventivos, orientações de bem-estar e novidades da medicina.'
            ];
        case 'videos':
            return [
                'icon' => 'bi-play-btn-fill',
                'color' => 'danger',
                'bg' => 'bg-danger-subtle text-danger',
                'category' => 'Galeria Multimídia',
                'desc' => 'Galeria oficial de vídeos com player moderno, capa inteligente e depoimentos.'
            ];
        case 'fotos':
        case 'photos':
            return [
                'icon' => 'bi-images',
                'color' => 'primary',
                'bg' => 'bg-primary-subtle text-primary',
                'category' => 'Galeria de Fotos',
                'desc' => 'Instalações, recepção, consultórios e tecnologia da clínica.'
            ];
        case 'faq':
            return [
                'icon' => 'bi-patch-question-fill',
                'color' => 'secondary',
                'bg' => 'bg-secondary-subtle text-secondary',
                'category' => 'Dúvidas Frequentes',
                'desc' => 'Respostas rápidas às principais perguntas dos pacientes e convênios.'
            ];
        case 'contato':
        case 'contact':
            return [
                'icon' => 'bi-envelope-paper-fill',
                'color' => 'success',
                'bg' => 'bg-success-subtle text-success',
                'category' => 'Canais de Contato',
                'desc' => 'Formulário direto de contato, WhatsApp oficial e localização no mapa.'
            ];
        case 'sobre':
        case 'about':
            return [
                'icon' => 'bi-hospital-fill',
                'color' => 'info',
                'bg' => 'bg-info-subtle text-info',
                'category' => 'Institucional',
                'desc' => 'História, missão, valores humanizados e estrutura da Pronto Saúde.'
            ];
        case 'trabalhe-conosco':
            return [
                'icon' => 'bi-briefcase-fill',
                'color' => 'dark',
                'bg' => 'bg-dark-subtle text-dark',
                'category' => 'Carreiras & RH',
                'desc' => 'Portal de oportunidades profissionais médicas e administrativas.'
            ];
        case 'convenio':
            return [
                'icon' => 'bi-file-earmark-medical-fill',
                'color' => 'primary',
                'bg' => 'bg-primary-subtle text-primary',
                'category' => 'Convênios',
                'desc' => 'Atendimento pelo sistema de livre escolha com reembolso simplificado.'
            ];
        case 'privacidade':
            return [
                'icon' => 'bi-shield-lock-fill',
                'color' => 'secondary',
                'bg' => 'bg-secondary-subtle text-secondary',
                'category' => 'Legal & LGPD',
                'desc' => 'Política de privacidade, proteção de dados e termos de segurança.'
            ];
        case 'rsocial':
            return [
                'icon' => 'bi-people-fill',
                'color' => 'info',
                'bg' => 'bg-info-subtle text-info',
                'category' => 'Responsabilidade Social',
                'desc' => 'Projetos comunitários, saúde preventiva e impacto social positivo.'
            ];
        default:
            return [
                'icon' => 'bi-file-earmark-text-fill',
                'color' => 'primary',
                'bg' => 'bg-primary-subtle text-primary',
                'category' => 'Página do Sistema',
                'desc' => 'Página institucional do portal com layout ' . ($layout ?: 'personalizado') . '.'
            ];
    }
}
?>

<div class="app-content-header">
	<div class="container-fluid">
		<div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
			<div>
				<h3 class="mb-1 fw-bold d-flex align-items-center gap-2">
					<i class="bi bi-toggles text-primary"></i> Controle de Seções &amp; Páginas do Sistema
				</h3>
				<p class="text-body-secondary mb-0 small">
					Ative ou desative seções da Home e páginas públicas do portal instantaneamente via AJAX
				</p>
			</div>
			<div class="d-flex flex-wrap align-items-center gap-2">
				<a href="<?= BASE_URL ?>" target="_blank" class="btn btn-sm btn-outline-primary d-inline-flex align-items-center gap-1 shadow-sm">
					<i class="bi bi-box-arrow-up-right"></i> Visualizar Portal
				</a>
				<a href="page.php" class="btn btn-sm btn-outline-secondary d-inline-flex align-items-center gap-1 shadow-sm">
					<i class="bi bi-file-earmark-text"></i> Gestor Completo de Páginas
				</a>
				<a href="page-add.php" class="btn btn-sm btn-primary d-inline-flex align-items-center gap-1 shadow-sm">
					<i class="bi bi-plus-lg"></i> Nova Página
				</a>
			</div>
		</div>
	</div>
</div>

<div class="app-content">
	<div class="container-fluid">

		<!-- Alerta Informativo Global -->
		<div class="alert alert-info bg-info-subtle border-info-subtle text-info-emphasis d-flex align-items-center gap-3 p-3 rounded-4 shadow-sm mb-4">
			<i class="bi bi-info-circle-fill fs-2 text-info flex-shrink-0"></i>
			<div>
				<h6 class="fw-bold mb-1">Controle em Tempo Real (Zero Recarregamento)</h6>
				<p class="mb-0 small">
					Basta ligar ou desligar qualquer chave abaixo. O status é salvo instantaneamente via AJAX.
					Ao desativar uma <strong>página</strong>, ela é ocultada imediatamente de todos os menus (desktop, lateral mobile e rodapé) e seus acessos diretos são redirecionados de forma segura.
				</p>
			</div>
		</div>

		<!-- Cards de Resumo & Métricas Rápidas -->
		<div class="row g-3 mb-4">
			<div class="col-6 col-lg-3">
				<div class="card border-0 shadow-sm rounded-4 h-100 border-start border-4 border-primary">
					<div class="card-body p-3 d-flex align-items-center justify-content-between">
						<div>
							<div class="text-body-secondary small fw-semibold">Seções da Home</div>
							<div class="fs-4 fw-bold text-primary">10 Seções</div>
						</div>
						<div class="rounded-circle p-3 bg-primary-subtle text-primary fs-4">
							<i class="bi bi-layout-text-window-reverse"></i>
						</div>
					</div>
				</div>
			</div>
			<div class="col-6 col-lg-3">
				<div class="card border-0 shadow-sm rounded-4 h-100 border-start border-4 border-success">
					<div class="card-body p-3 d-flex align-items-center justify-content-between">
						<div>
							<div class="text-body-secondary small fw-semibold">Páginas Ativas</div>
							<div class="fs-4 fw-bold text-success" id="counterPagesActive"><?= $total_pages_active ?></div>
						</div>
						<div class="rounded-circle p-3 bg-success-subtle text-success fs-4">
							<i class="bi bi-check2-circle"></i>
						</div>
					</div>
				</div>
			</div>
			<div class="col-6 col-lg-3">
				<div class="card border-0 shadow-sm rounded-4 h-100 border-start border-4 border-danger">
					<div class="card-body p-3 d-flex align-items-center justify-content-between">
						<div>
							<div class="text-body-secondary small fw-semibold">Páginas Ocultas</div>
							<div class="fs-4 fw-bold text-danger" id="counterPagesInactive"><?= $total_pages_inactive ?></div>
						</div>
						<div class="rounded-circle p-3 bg-danger-subtle text-danger fs-4">
							<i class="bi bi-eye-slash-fill"></i>
						</div>
					</div>
				</div>
			</div>
			<div class="col-6 col-lg-3">
				<div class="card border-0 shadow-sm rounded-4 h-100 border-start border-4 <?= $status_maintenance == 1 ? 'border-warning' : 'border-secondary' ?>">
					<div class="card-body p-3 d-flex align-items-center justify-content-between">
						<div>
							<div class="text-body-secondary small fw-semibold">Portal Público</div>
							<div class="fs-5 fw-bold <?= $status_maintenance == 1 ? 'text-warning' : 'text-success' ?>" id="portalModeStatus">
								<?= $status_maintenance == 1 ? 'Modo Manutenção' : 'Online Normal' ?>
							</div>
						</div>
						<div class="rounded-circle p-3 <?= $status_maintenance == 1 ? 'bg-warning-subtle text-warning' : 'bg-success-subtle text-success' ?> fs-4">
							<i class="bi <?= $status_maintenance == 1 ? 'bi-tools' : 'bi-globe-americas' ?>"></i>
						</div>
					</div>
				</div>
			</div>
		</div>

		<!-- Card de Destaque: Modo de Manutenção -->
		<div class="card border-0 shadow-sm rounded-4 mb-4 border-start border-4 border-warning">
			<div class="card-body p-4 d-flex align-items-center justify-content-between flex-wrap gap-3">
				<div class="d-flex align-items-center gap-3">
					<div class="rounded-circle p-3 bg-warning-subtle text-warning fs-3">
						<i class="bi bi-tools"></i>
					</div>
					<div>
						<h5 class="fw-bold mb-1 text-body">Modo de Manutenção do Portal</h5>
						<p class="text-body-secondary small mb-0">
							Quando ativo, os visitantes públicos serão direcionados para a página de manutenção personalizada. Administradores continuam com acesso normal ao painel e ao site.
						</p>
					</div>
				</div>
				<div class="form-check form-switch fs-3 mb-0">
					<input class="form-check-input switch-ajax-section" type="checkbox" role="switch" id="switch_maintenance_mode" data-secao="maintenance_mode" <?= $status_maintenance == 1 ? 'checked' : ''; ?>>
				</div>
			</div>
		</div>

		<!-- Barra de Filtros e Busca Rápida -->
		<div class="card border-0 shadow-sm rounded-4 mb-4">
			<div class="card-body p-3">
				<div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
					<!-- Botões de Filtro -->
					<div class="btn-group btn-group-sm" role="group" id="filterNavButtons">
						<button type="button" class="btn btn-outline-primary active" data-filter="all">
							<i class="bi bi-grid-fill me-1"></i> Ver Todos (<?= 10 + count($all_pages) ?>)
						</button>
						<button type="button" class="btn btn-outline-primary" data-filter="home">
							<i class="bi bi-layout-text-window-reverse me-1"></i> Seções da Home (10)
						</button>
						<button type="button" class="btn btn-outline-primary" data-filter="pages">
							<i class="bi bi-file-earmark-text-fill me-1"></i> Páginas do Sistema (<?= count($all_pages) ?>)
						</button>
						<button type="button" class="btn btn-outline-success" data-filter="active">
							<i class="bi bi-check-circle-fill me-1"></i> Ativas
						</button>
						<button type="button" class="btn btn-outline-danger" data-filter="inactive">
							<i class="bi bi-slash-circle-fill me-1"></i> Ocultas
						</button>
					</div>

					<!-- Campo de Busca em Tempo Real -->
					<div class="input-group input-group-sm" style="max-width: 380px;">
						<span class="input-group-text bg-body border-end-0"><i class="bi bi-search text-body-secondary"></i></span>
						<input type="text" class="form-control border-start-0" id="searchFilterInput" placeholder="Pesquisar por nome, seção ou slug...">
						<button class="btn btn-outline-secondary" type="button" id="clearSearchBtn" title="Limpar busca"><i class="bi bi-x-lg"></i></button>
					</div>
				</div>
			</div>
		</div>

		<!-- ==============================================================================
		     BLOCO 1: SEÇÕES DA PÁGINA INICIAL (HOME)
		     ============================================================================== -->
		<div class="section-group-container mb-5" id="groupHomeSections" data-group="home">
			<div class="d-flex align-items-center justify-content-between mb-3 border-bottom pb-2">
				<div class="d-flex align-items-center gap-2">
					<div class="p-2 rounded-3 bg-primary text-white fs-5">
						<i class="bi bi-layout-text-window-reverse"></i>
					</div>
					<div>
						<h4 class="fw-bold mb-0 text-body">1. Seções da Página Inicial (Home)</h4>
						<small class="text-body-secondary">Controle de exibição dos blocos e seções dinâmicas na Home do portal</small>
					</div>
				</div>
				<span class="badge bg-primary-subtle text-primary fw-bold px-3 py-2 rounded-pill">10 Seções</span>
			</div>

			<div class="row g-3">

				<!-- 0. Atendimento Rápido na Hero -->
				<div class="col-12 col-md-6 col-xl-4 filterable-item" data-group="home" data-status="<?= $status_hero_box == 1 ? 'active' : 'inactive' ?>" data-search="hero card agendamento rapido atendimento 1 minuto hero_box_status">
					<div class="card border-0 shadow-sm rounded-4 h-100 card-hover-shadow">
						<div class="card-body p-4 d-flex flex-column justify-content-between">
							<div class="d-flex align-items-start justify-content-between mb-3">
								<div class="d-flex align-items-center gap-2">
									<div class="p-2 rounded-3 bg-primary-subtle text-primary fs-4">
										<i class="bi bi-layout-sidebar-inset"></i>
									</div>
									<div>
										<h6 class="fw-bold mb-0">Atendimento Rápido (Hero)</h6>
										<small class="text-body-secondary">Home &bull; Card 1 Minuto no Slider</small>
									</div>
								</div>
								<div class="form-check form-switch fs-4 mb-0">
									<input class="form-check-input switch-ajax-section" type="checkbox" role="switch" data-secao="hero_box_status" <?= $status_hero_box == 1 ? 'checked' : ''; ?>>
								</div>
							</div>
							<p class="text-body-secondary small mb-3">
								Card de acesso rápido com 4 botões de serviços diretos e botão de fidelidade sobreposto no slider.
							</p>
							<div class="d-flex align-items-center justify-content-between pt-2 border-top">
								<span class="badge status-badge-indicator <?= $status_hero_box == 1 ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' ?>">
									<?= $status_hero_box == 1 ? 'Visível na Home' : 'Oculto na Home' ?>
								</span>
								<a href="settings.php?tab=tab_7" class="btn btn-sm btn-link text-decoration-none text-primary p-0 fw-semibold">
									<i class="bi bi-sliders me-1"></i> Personalizar Card
								</a>
							</div>
						</div>
					</div>
				</div>

				<!-- 0.1 Diferenciais Clínicos & Saúde Preventiva -->
				<div class="col-12 col-md-6 col-xl-4 filterable-item" data-group="home" data-status="<?= $status_risco == 1 ? 'active' : 'inactive' ?>" data-search="diferenciais clinicos saude preventiva 3 cards diagnósticos home_risco_status">
					<div class="card border-0 shadow-sm rounded-4 h-100 card-hover-shadow">
						<div class="card-body p-4 d-flex flex-column justify-content-between">
							<div class="d-flex align-items-start justify-content-between mb-3">
								<div class="d-flex align-items-center gap-2">
									<div class="p-2 rounded-3 bg-success-subtle text-success fs-4">
										<i class="bi bi-shield-plus"></i>
									</div>
									<div>
										<h6 class="fw-bold mb-0">Diferenciais Clínicos</h6>
										<small class="text-body-secondary">Home &bull; 3 Cards de Saúde Preventiva</small>
									</div>
								</div>
								<div class="form-check form-switch fs-4 mb-0">
									<input class="form-check-input switch-ajax-section" type="checkbox" role="switch" data-secao="home_risco_status" <?= $status_risco == 1 ? 'checked' : ''; ?>>
								</div>
							</div>
							<p class="text-body-secondary small mb-3">
								Seção de diagnóstico ágil, corpo clínico humanizado e economia sem carências com destaques em verde.
							</p>
							<div class="d-flex align-items-center justify-content-between pt-2 border-top">
								<span class="badge status-badge-indicator <?= $status_risco == 1 ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' ?>">
									<?= $status_risco == 1 ? 'Visível na Home' : 'Oculto na Home' ?>
								</span>
								<a href="settings.php?tab=tab_7" class="btn btn-sm btn-link text-decoration-none text-success p-0 fw-semibold">
									<i class="bi bi-sliders me-1"></i> Personalizar 3 Cards
								</a>
							</div>
						</div>
					</div>
				</div>

				<!-- 0.2 Quem Somos & Centro Médico Moderno -->
				<div class="col-12 col-md-6 col-xl-4 filterable-item" data-group="home" data-status="<?= $status_about == 1 ? 'active' : 'inactive' ?>" data-search="quem somos sobre centro medico moderno autoridade estatisticas home_about_status">
					<div class="card border-0 shadow-sm rounded-4 h-100 card-hover-shadow">
						<div class="card-body p-4 d-flex flex-column justify-content-between">
							<div class="d-flex align-items-start justify-content-between mb-3">
								<div class="d-flex align-items-center gap-2">
									<div class="p-2 rounded-3 bg-info-subtle text-info fs-4">
										<i class="bi bi-hospital"></i>
									</div>
									<div>
										<h6 class="fw-bold mb-0">Quem Somos &amp; Estrutura</h6>
										<small class="text-body-secondary">Home &bull; Estatísticas &amp; Unidade</small>
									</div>
								</div>
								<div class="form-check form-switch fs-4 mb-0">
									<input class="form-check-input switch-ajax-section" type="checkbox" role="switch" data-secao="home_about_status" <?= $status_about == 1 ? 'checked' : ''; ?>>
								</div>
							</div>
							<p class="text-body-secondary small mb-3">
								Apresentação da clínica com 4 métricas em grid e o card de destaques da unidade com tópicos e botões de ação.
							</p>
							<div class="d-flex align-items-center justify-content-between pt-2 border-top">
								<span class="badge status-badge-indicator <?= $status_about == 1 ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' ?>">
									<?= $status_about == 1 ? 'Visível na Home' : 'Oculto na Home' ?>
								</span>
								<a href="settings.php?tab=tab_7" class="btn btn-sm btn-link text-decoration-none text-info p-0 fw-semibold">
									<i class="bi bi-sliders me-1"></i> Personalizar Textos &amp; Stats
								</a>
							</div>
						</div>
					</div>
				</div>

				<!-- 1. Serviços Médicos -->
				<div class="col-12 col-md-6 col-xl-4 filterable-item" data-group="home" data-status="<?= $status_service == 1 ? 'active' : 'inactive' ?>" data-search="servicos medicos procedimentos consultas exames home_status_service">
					<div class="card border-0 shadow-sm rounded-4 h-100 card-hover-shadow">
						<div class="card-body p-4 d-flex flex-column justify-content-between">
							<div class="d-flex align-items-start justify-content-between mb-3">
								<div class="d-flex align-items-center gap-2">
									<div class="p-2 rounded-3 bg-primary-subtle text-primary fs-4">
										<i class="bi bi-check2-circle"></i>
									</div>
									<div>
										<h6 class="fw-bold mb-0">Serviços Médicos</h6>
										<small class="text-body-secondary">Home &bull; Lista de Serviços</small>
									</div>
								</div>
								<div class="form-check form-switch fs-4 mb-0">
									<input class="form-check-input switch-ajax-section" type="checkbox" role="switch" data-secao="home_status_service" <?= $status_service == 1 ? 'checked' : ''; ?>>
								</div>
							</div>
							<p class="text-body-secondary small mb-3">
								Exibe os principais procedimentos, exames e serviços de saúde com botão para agendamento.
							</p>
							<div class="d-flex align-items-center justify-content-between pt-2 border-top">
								<span class="badge status-badge-indicator <?= $status_service == 1 ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' ?>">
									<?= $status_service == 1 ? 'Ativo na Home' : 'Oculto na Home' ?>
								</span>
								<small class="text-muted font-monospace" style="font-size: 0.72rem;">home_status_service</small>
							</div>
						</div>
					</div>
				</div>

				<!-- 2. Clínicas e Departamentos -->
				<div class="col-12 col-md-6 col-xl-4 filterable-item" data-group="home" data-status="<?= $status_department == 1 ? 'active' : 'inactive' ?>" data-search="clinicas departamentos especialidades infraestrutura home_status_department">
					<div class="card border-0 shadow-sm rounded-4 h-100 card-hover-shadow">
						<div class="card-body p-4 d-flex flex-column justify-content-between">
							<div class="d-flex align-items-start justify-content-between mb-3">
								<div class="d-flex align-items-center gap-2">
									<div class="p-2 rounded-3 bg-info-subtle text-info fs-4">
										<i class="bi bi-hospital"></i>
									</div>
									<div>
										<h6 class="fw-bold mb-0">Clínicas e Departamentos</h6>
										<small class="text-body-secondary">Home &bull; Especialidades</small>
									</div>
								</div>
								<div class="form-check form-switch fs-4 mb-0">
									<input class="form-check-input switch-ajax-section" type="checkbox" role="switch" data-secao="home_status_department" <?= $status_department == 1 ? 'checked' : ''; ?>>
								</div>
							</div>
							<p class="text-body-secondary small mb-3">
								Apresenta a estrutura física, fotos e especialidades de cada departamento da rede.
							</p>
							<div class="d-flex align-items-center justify-content-between pt-2 border-top">
								<span class="badge status-badge-indicator <?= $status_department == 1 ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' ?>">
									<?= $status_department == 1 ? 'Ativo na Home' : 'Oculto na Home' ?>
								</span>
								<small class="text-muted font-monospace" style="font-size: 0.72rem;">home_status_department</small>
							</div>
						</div>
					</div>
				</div>

				<!-- 3. Corpo Clínico (Médicos) -->
				<div class="col-12 col-md-6 col-xl-4 filterable-item" data-group="home" data-status="<?= $status_doctor == 1 ? 'active' : 'inactive' ?>" data-search="corpo clinico medicos doutores especialistas equipe crm home_status_doctor">
					<div class="card border-0 shadow-sm rounded-4 h-100 card-hover-shadow">
						<div class="card-body p-4 d-flex flex-column justify-content-between">
							<div class="d-flex align-items-start justify-content-between mb-3">
								<div class="d-flex align-items-center gap-2">
									<div class="p-2 rounded-3 bg-danger-subtle text-danger fs-4">
										<i class="bi bi-heart-pulse-fill"></i>
									</div>
									<div>
										<h6 class="fw-bold mb-0">Corpo Clínico (Médicos)</h6>
										<small class="text-body-secondary">Home &bull; Especialistas</small>
									</div>
								</div>
								<div class="form-check form-switch fs-4 mb-0">
									<input class="form-check-input switch-ajax-section" type="checkbox" role="switch" data-secao="home_status_doctor" <?= $status_doctor == 1 ? 'checked' : ''; ?>>
								</div>
							</div>
							<p class="text-body-secondary small mb-3">
								Mostra a equipe médica ativa, foto profissional, CRM e botão de perfil detalhado.
							</p>
							<div class="d-flex align-items-center justify-content-between pt-2 border-top">
								<span class="badge status-badge-indicator <?= $status_doctor == 1 ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' ?>">
									<?= $status_doctor == 1 ? 'Ativo na Home' : 'Oculto na Home' ?>
								</span>
								<small class="text-muted font-monospace" style="font-size: 0.72rem;">home_status_doctor</small>
							</div>
						</div>
					</div>
				</div>

				<!-- 4. Planos e Fidelidade -->
				<div class="col-12 col-md-6 col-xl-4 filterable-item" data-group="home" data-status="<?= $status_pricing == 1 ? 'active' : 'inactive' ?>" data-search="planos fidelidade precos valores descontos tabela home_status_pricing">
					<div class="card border-0 shadow-sm rounded-4 h-100 card-hover-shadow">
						<div class="card-body p-4 d-flex flex-column justify-content-between">
							<div class="d-flex align-items-start justify-content-between mb-3">
								<div class="d-flex align-items-center gap-2">
									<div class="p-2 rounded-3 bg-warning-subtle text-warning fs-4">
										<i class="bi bi-cash-stack"></i>
									</div>
									<div>
										<h6 class="fw-bold mb-0">Planos &amp; Fidelidade</h6>
										<small class="text-body-secondary">Home &bull; Valores e Benefícios</small>
									</div>
								</div>
								<div class="form-check form-switch fs-4 mb-0">
									<input class="form-check-input switch-ajax-section" type="checkbox" role="switch" data-secao="home_status_pricing" <?= $status_pricing == 1 ? 'checked' : ''; ?>>
								</div>
							</div>
							<p class="text-body-secondary small mb-3">
								Tabela comparativa de planos individuais, familiares e corporativos com valores.
							</p>
							<div class="d-flex align-items-center justify-content-between pt-2 border-top">
								<span class="badge status-badge-indicator <?= $status_pricing == 1 ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' ?>">
									<?= $status_pricing == 1 ? 'Ativo na Home' : 'Oculto na Home' ?>
								</span>
								<small class="text-muted font-monospace" style="font-size: 0.72rem;">home_status_pricing</small>
							</div>
						</div>
					</div>
				</div>

				<!-- 5. Depoimentos de Pacientes -->
				<div class="col-12 col-md-6 col-xl-4 filterable-item" data-group="home" data-status="<?= $status_testimonial == 1 ? 'active' : 'inactive' ?>" data-search="depoimentos pacientes avaliacoes relatos prova social home_status_testimonial">
					<div class="card border-0 shadow-sm rounded-4 h-100 card-hover-shadow">
						<div class="card-body p-4 d-flex flex-column justify-content-between">
							<div class="d-flex align-items-start justify-content-between mb-3">
								<div class="d-flex align-items-center gap-2">
									<div class="p-2 rounded-3 bg-secondary-subtle text-secondary fs-4">
										<i class="bi bi-quote"></i>
									</div>
									<div>
										<h6 class="fw-bold mb-0">Depoimentos de Pacientes</h6>
										<small class="text-body-secondary">Home &bull; Prova Social</small>
									</div>
								</div>
								<div class="form-check form-switch fs-4 mb-0">
									<input class="form-check-input switch-ajax-section" type="checkbox" role="switch" data-secao="home_status_testimonial" <?= $status_testimonial == 1 ? 'checked' : ''; ?>>
								</div>
							</div>
							<p class="text-body-secondary small mb-3">
								Carrossel com relatos reais, elogios e avaliações de pacientes atendidos na clínica.
							</p>
							<div class="d-flex align-items-center justify-content-between pt-2 border-top">
								<span class="badge status-badge-indicator <?= $status_testimonial == 1 ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' ?>">
									<?= $status_testimonial == 1 ? 'Ativo na Home' : 'Oculto na Home' ?>
								</span>
								<small class="text-muted font-monospace" style="font-size: 0.72rem;">home_status_testimonial</small>
							</div>
						</div>
					</div>
				</div>

				<!-- 6. Notícias e Blog -->
				<div class="col-12 col-md-6 col-xl-4 filterable-item" data-group="home" data-status="<?= $status_news == 1 ? 'active' : 'inactive' ?>" data-search="noticias blog artigos dicas saude prevencao home_status_news">
					<div class="card border-0 shadow-sm rounded-4 h-100 card-hover-shadow">
						<div class="card-body p-4 d-flex flex-column justify-content-between">
							<div class="d-flex align-items-start justify-content-between mb-3">
								<div class="d-flex align-items-center gap-2">
									<div class="p-2 rounded-3 bg-success-subtle text-success fs-4">
										<i class="bi bi-newspaper"></i>
									</div>
									<div>
										<h6 class="fw-bold mb-0">Artigos e Notícias</h6>
										<small class="text-body-secondary">Home &bull; Dicas de Saúde</small>
									</div>
								</div>
								<div class="form-check form-switch fs-4 mb-0">
									<input class="form-check-input switch-ajax-section" type="checkbox" role="switch" data-secao="home_status_news" <?= $status_news == 1 ? 'checked' : ''; ?>>
								</div>
							</div>
							<p class="text-body-secondary small mb-3">
								Últimas postagens do blog médico, orientações preventivas e novidades da instituição.
							</p>
							<div class="d-flex align-items-center justify-content-between pt-2 border-top">
								<span class="badge status-badge-indicator <?= $status_news == 1 ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' ?>">
									<?= $status_news == 1 ? 'Ativo na Home' : 'Oculto na Home' ?>
								</span>
								<small class="text-muted font-monospace" style="font-size: 0.72rem;">home_status_news</small>
							</div>
						</div>
					</div>
				</div>

				<!-- 7. Parceiros Institucionais -->
				<div class="col-12 col-md-6 col-xl-4 filterable-item" data-group="home" data-status="<?= $status_partner == 1 ? 'active' : 'inactive' ?>" data-search="parceiros institucionais convenios operadoras marcas logos home_status_partner">
					<div class="card border-0 shadow-sm rounded-4 h-100 card-hover-shadow">
						<div class="card-body p-4 d-flex flex-column justify-content-between">
							<div class="d-flex align-items-start justify-content-between mb-3">
								<div class="d-flex align-items-center gap-2">
									<div class="p-2 rounded-3 bg-primary-subtle text-primary fs-4">
										<i class="bi bi-handshake-fill"></i>
									</div>
									<div>
										<h6 class="fw-bold mb-0">Parceiros Institucionais</h6>
										<small class="text-body-secondary">Home &bull; Logotipos</small>
									</div>
								</div>
								<div class="form-check form-switch fs-4 mb-0">
									<input class="form-check-input switch-ajax-section" type="checkbox" role="switch" data-secao="home_status_partner" <?= $status_partner == 1 ? 'checked' : ''; ?>>
								</div>
							</div>
							<p class="text-body-secondary small mb-3">
								Carrossel com logomarcas de laboratórios credenciados, operadoras e empresas parceiras.
							</p>
							<div class="d-flex align-items-center justify-content-between pt-2 border-top">
								<span class="badge status-badge-indicator <?= $status_partner == 1 ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' ?>">
									<?= $status_partner == 1 ? 'Ativo na Home' : 'Oculto na Home' ?>
								</span>
								<small class="text-muted font-monospace" style="font-size: 0.72rem;">home_status_partner</small>
							</div>
						</div>
					</div>
				</div>

			</div>
		</div>

		<!-- ==============================================================================
		     BLOCO 2: PÁGINAS DO SISTEMA & INSTITUCIONAIS (MULTIPAGE)
		     ============================================================================== -->
		<div class="section-group-container mb-5" id="groupPagesSections" data-group="pages">
			<div class="d-flex align-items-center justify-content-between mb-3 border-bottom pb-2 flex-wrap gap-2">
				<div class="d-flex align-items-center gap-2">
					<div class="p-2 rounded-3 bg-success text-white fs-5">
						<i class="bi bi-file-earmark-check-fill"></i>
					</div>
					<div>
						<h4 class="fw-bold mb-0 text-body">2. Páginas do Sistema &amp; Institucionais</h4>
						<small class="text-body-secondary">
							Ative ou desative qualquer página do portal público (menus, rodapé e acesso direto via URL)
						</small>
					</div>
				</div>
				<div class="d-flex align-items-center gap-2">
					<span class="badge bg-success-subtle text-success fw-bold px-3 py-2 rounded-pill">
						<?= count($all_pages) ?> Páginas Cadastradas
					</span>
				</div>
			</div>

			<div class="row g-3" id="pagesGridContainer">
				<?php
				foreach ($all_pages as $page):
					$p_id     = (int)$page['page_id'];
					$p_name   = $page['page_name'];
					$p_slug   = $page['page_slug'];
					$p_layout = $page['page_layout'] ?? '';
					$is_active = ($page['status'] === 'Active');

					$meta = getPageIconAndColor($p_slug, $p_layout);

					$clean_slug = strtolower(trim(str_replace('.php', '', $p_slug)));
					$is_system = in_array($clean_slug, $system_slugs);

					if ($is_system) {
						$public_url = (strpos($p_slug, '.php') !== false) ? BASE_URL . $p_slug : BASE_URL . $p_slug . '.php';
					} else {
						$public_url = (strpos($p_slug, '.php') !== false) ? BASE_URL . $p_slug : BASE_URL . 'page/' . $p_slug;
					}
					// Se for layout tipo gallery/contact/faq que usa page.php?slug=
					if (in_array($clean_slug, ['fotos', 'videos', 'faq', 'contato', 'sobre', 'photos', 'contact', 'about'])) {
						$public_url = BASE_URL . 'page.php?slug=' . urlencode($p_slug);
					}
				?>
				<div class="col-12 col-md-6 col-xl-4 filterable-item" data-group="pages" data-status="<?= $is_active ? 'active' : 'inactive' ?>" data-search="<?= strtolower(htmlspecialchars($p_name . ' ' . $p_slug . ' ' . $p_layout . ' ' . $meta['category'])); ?>">
					<div class="card border-0 shadow-sm rounded-4 h-100 card-hover-shadow page-card" id="page_card_<?= $p_id ?>">
						<div class="card-body p-4 d-flex flex-column justify-content-between">
							
							<div>
								<div class="d-flex align-items-start justify-content-between mb-3">
									<div class="d-flex align-items-center gap-2">
										<div class="p-2 rounded-3 <?= $meta['bg']; ?> fs-4">
											<i class="bi <?= $meta['icon']; ?>"></i>
										</div>
										<div>
											<h6 class="fw-bold mb-0 text-body page-title-text"><?= htmlspecialchars($p_name); ?></h6>
											<small class="text-body-secondary"><?= htmlspecialchars($meta['category']); ?></small>
										</div>
									</div>
									<div class="form-check form-switch fs-4 mb-0">
										<input class="form-check-input switch-ajax-page" type="checkbox" role="switch" data-tipo="page" data-page-id="<?= $p_id; ?>" data-page-name="<?= htmlspecialchars($p_name); ?>" <?= $is_active ? 'checked' : ''; ?>>
									</div>
								</div>

								<p class="text-body-secondary small mb-3">
									<?= htmlspecialchars($meta['desc']); ?>
								</p>
							</div>

							<div>
								<div class="d-flex flex-wrap align-items-center gap-1 mb-3">
									<span class="badge bg-light text-dark border px-2 py-1 font-monospace" style="font-size: 0.72rem;">
										<i class="bi bi-link-45deg"></i> <?= htmlspecialchars($p_slug); ?>
									</span>
									<?php if (!empty($p_layout)): ?>
										<span class="badge bg-secondary-subtle text-secondary px-2 py-1" style="font-size: 0.7rem;">
											<?= htmlspecialchars($p_layout); ?>
										</span>
									<?php endif; ?>
								</div>

								<div class="d-flex align-items-center justify-content-between pt-2 border-top">
									<span class="badge status-badge-page status-badge-page-<?= $p_id ?> <?= $is_active ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' ?>">
										<?= $is_active ? 'Página Ativa (Visível)' : 'Página Oculta (Desativada)' ?>
									</span>
									<div class="d-flex align-items-center gap-2">
										<a href="<?= $public_url; ?>" target="_blank" class="btn btn-sm btn-link text-decoration-none p-0 text-primary" title="Ver no site público">
											<i class="bi bi-box-arrow-up-right"></i> Abrir
										</a>
										<a href="page-edit.php?id=<?= $p_id; ?>" class="btn btn-sm btn-link text-decoration-none p-0 text-secondary" title="Editar conteúdo da página">
											<i class="bi bi-pencil-square"></i> Editar
										</a>
									</div>
								</div>
							</div>

						</div>
					</div>
				</div>
				<?php endforeach; ?>
			</div>
		</div>

		<!-- Estado de Busca Vazia -->
		<div id="noResultsState" class="text-center py-5 d-none">
			<div class="p-4 rounded-circle bg-light d-inline-block text-muted mb-3 fs-1">
				<i class="bi bi-search"></i>
			</div>
			<h5 class="fw-bold text-body">Nenhum resultado encontrado</h5>
			<p class="text-body-secondary small mb-3">Nenhuma seção ou página corresponde ao termo pesquisado.</p>
			<button class="btn btn-sm btn-outline-primary" type="button" id="resetSearchBtn">
				<i class="bi bi-arrow-counterclockwise"></i> Redefinir Filtros
			</button>
		</div>

	</div>
</div>

<style>
.card-hover-shadow {
	transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.card-hover-shadow:hover {
	transform: translateY(-2px);
	box-shadow: 0 10px 24px rgba(0, 0, 0, 0.08) !important;
}
.switch-ajax-section,
.switch-ajax-page {
	cursor: pointer;
}
</style>

<!-- Script de Controle AJAX Unificado e Filtragem Instantânea -->
<script>
document.addEventListener('DOMContentLoaded', function () {

	// 1. Controle AJAX para Seções da Home e Modo de Manutenção
	const sectionSwitches = document.querySelectorAll('.switch-ajax-section');
	sectionSwitches.forEach(sw => {
		if (sw.dataset.bound === 'true') return;
		sw.dataset.bound = 'true';

		sw.addEventListener('change', function () {
			const secao = this.getAttribute('data-secao');
			const status = this.checked ? 1 : 0;
			const inputElement = this;
			const card = inputElement.closest('.card');
			const badge = card ? card.querySelector('.status-badge-indicator') : null;

			inputElement.disabled = true;

			const formData = new FormData();
			formData.append('secao', secao);
			formData.append('status', status);

			fetch('secoes-ajax.php', {
				method: 'POST',
				body: formData,
				headers: { 'X-Requested-With': 'XMLHttpRequest' }
			})
			.then(res => res.json())
			.then(data => {
				inputElement.disabled = false;
				if (data.success) {
					// Atualiza badge de status da seção
					if (badge) {
						if (status === 1) {
							badge.className = 'badge status-badge-indicator bg-success-subtle text-success';
							badge.textContent = 'Ativo na Home';
						} else {
							badge.className = 'badge status-badge-indicator bg-danger-subtle text-danger';
							badge.textContent = 'Oculto na Home';
						}
					}
					// Atualiza card de modo de manutenção se for ele
					if (secao === 'maintenance_mode') {
						const portalStatusEl = document.getElementById('portalModeStatus');
						if (portalStatusEl) {
							portalStatusEl.textContent = status === 1 ? 'Modo Manutenção' : 'Online Normal';
							portalStatusEl.className = 'fs-5 fw-bold ' + (status === 1 ? 'text-warning' : 'text-success');
						}
					}

					if (window.notificar) {
						window.notificar('success', data.message, 'Atualizado com Sucesso');
					}
				} else {
					inputElement.checked = !inputElement.checked;
					if (window.notificar) {
						window.notificar('error', data.message || 'Erro ao atualizar seção.', 'Falha');
					}
				}
			})
			.catch(err => {
				inputElement.disabled = false;
				inputElement.checked = !inputElement.checked;
				if (window.notificar) {
					window.notificar('error', 'Falha na conexão com o servidor.', 'Erro');
				}
			});
		});
	});

	// 2. Controle AJAX para Páginas do Sistema (tbl_page)
	const pageSwitches = document.querySelectorAll('.switch-ajax-page');
	pageSwitches.forEach(sw => {
		if (sw.dataset.bound === 'true') return;
		sw.dataset.bound = 'true';

		sw.addEventListener('change', function () {
			const pageId = this.getAttribute('data-page-id');
			const pageName = this.getAttribute('data-page-name') || 'Página';
			const status = this.checked ? 1 : 0;
			const inputElement = this;
			const itemContainer = inputElement.closest('.filterable-item');
			const badge = document.querySelector('.status-badge-page-' + pageId);

			inputElement.disabled = true;

			const formData = new FormData();
			formData.append('tipo', 'page');
			formData.append('page_id', pageId);
			formData.append('status', status);

			fetch('secoes-ajax.php', {
				method: 'POST',
				body: formData,
				headers: { 'X-Requested-With': 'XMLHttpRequest' }
			})
			.then(res => res.json())
			.then(data => {
				inputElement.disabled = false;
				if (data.success) {
					// Atualiza badge visual do card
					if (badge) {
						if (status === 1) {
							badge.className = 'badge status-badge-page status-badge-page-' + pageId + ' bg-success-subtle text-success';
							badge.textContent = 'Página Ativa (Visível)';
						} else {
							badge.className = 'badge status-badge-page status-badge-page-' + pageId + ' bg-danger-subtle text-danger';
							badge.textContent = 'Página Oculta (Desativada)';
						}
					}
					// Atualiza dataset para filtros
					if (itemContainer) {
						itemContainer.setAttribute('data-status', status === 1 ? 'active' : 'inactive');
					}

					// Atualiza contadores numéricos no topo
					recalcPageCounters();

					if (window.notificar) {
						window.notificar(
							status === 1 ? 'success' : 'warning',
							data.message,
							status === 1 ? 'Página Ativada' : 'Página Ocultada'
						);
					}
				} else {
					inputElement.checked = !inputElement.checked;
					if (window.notificar) {
						window.notificar('error', data.message || 'Erro ao alterar status da página.', 'Atenção');
					}
				}
			})
			.catch(err => {
				inputElement.disabled = false;
				inputElement.checked = !inputElement.checked;
				if (window.notificar) {
					window.notificar('error', 'Falha na comunicação com o servidor.', 'Erro');
				}
			});
		});
	});

	// Função de Recálculo de Contadores de Páginas
	function recalcPageCounters() {
		const activeCount = document.querySelectorAll('.switch-ajax-page:checked').length;
		const totalCount = document.querySelectorAll('.switch-ajax-page').length;
		const inactiveCount = totalCount - activeCount;

		const activeEl = document.getElementById('counterPagesActive');
		const inactiveEl = document.getElementById('counterPagesInactive');
		if (activeEl) activeEl.textContent = activeCount;
		if (inactiveEl) inactiveEl.textContent = inactiveCount;
	}

	// 3. Sistema de Busca e Filtragem Rápida
	const searchInput = document.getElementById('searchFilterInput');
	const clearSearchBtn = document.getElementById('clearSearchBtn');
	const resetSearchBtn = document.getElementById('resetSearchBtn');
	const filterButtons = document.querySelectorAll('#filterNavButtons button');
	const allItems = document.querySelectorAll('.filterable-item');
	const noResultsState = document.getElementById('noResultsState');

	let currentGroupFilter = 'all';

	function applyFilters() {
		const searchTerm = (searchInput ? searchInput.value : '').toLowerCase().trim();
		let visibleCount = 0;

		allItems.forEach(item => {
			const itemGroup = item.getAttribute('data-group');
			const itemStatus = item.getAttribute('data-status');
			const itemSearchText = item.getAttribute('data-search') || '';

			let matchesGroup = true;
			if (currentGroupFilter === 'home') {
				matchesGroup = (itemGroup === 'home');
			} else if (currentGroupFilter === 'pages') {
				matchesGroup = (itemGroup === 'pages');
			} else if (currentGroupFilter === 'active') {
				matchesGroup = (itemStatus === 'active');
			} else if (currentGroupFilter === 'inactive') {
				matchesGroup = (itemStatus === 'inactive');
			}

			let matchesSearch = true;
			if (searchTerm !== '') {
				matchesSearch = itemSearchText.includes(searchTerm);
			}

			if (matchesGroup && matchesSearch) {
				item.classList.remove('d-none');
				visibleCount++;
			} else {
				item.classList.add('d-none');
			}
		});

		// Mostra ou esconde cabeçalhos de bloco conforme itens visíveis
		['home', 'pages'].forEach(grp => {
			const groupContainer = document.querySelector(`.section-group-container[data-group="${grp}"]`);
			if (groupContainer) {
				const visibleInGroup = groupContainer.querySelectorAll('.filterable-item:not(.d-none)').length;
				if (visibleInGroup === 0 && (searchTerm !== '' || currentGroupFilter !== 'all')) {
					groupContainer.classList.add('d-none');
				} else {
					groupContainer.classList.remove('d-none');
				}
			}
		});

		// Mostra aviso caso zero itens
		if (noResultsState) {
			if (visibleCount === 0) {
				noResultsState.classList.remove('d-none');
			} else {
				noResultsState.classList.add('d-none');
			}
		}
	}

	filterButtons.forEach(btn => {
		btn.addEventListener('click', function () {
			filterButtons.forEach(b => b.classList.remove('active'));
			this.classList.add('active');
			currentGroupFilter = this.getAttribute('data-filter') || 'all';
			applyFilters();
		});
	});

	if (searchInput) {
		searchInput.addEventListener('input', applyFilters);
	}

	if (clearSearchBtn) {
		clearSearchBtn.addEventListener('click', function () {
			if (searchInput) searchInput.value = '';
			applyFilters();
		});
	}

	if (resetSearchBtn) {
		resetSearchBtn.addEventListener('click', function () {
			if (searchInput) searchInput.value = '';
			filterButtons.forEach(b => {
				b.classList.toggle('active', b.getAttribute('data-filter') === 'all');
			});
			currentGroupFilter = 'all';
			applyFilters();
		});
	}
});
</script>

<?php require_once('footer.php'); ?>
