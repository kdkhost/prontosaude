<?php require_once('header.php'); ?>

<?php 
// Consultas Otimizadas com COUNT(*) para Alta Performance
$statement = $pdo->prepare("SELECT COUNT(*) FROM tbl_user");
$statement->execute();
$total_user = (int)$statement->fetchColumn();

$statement = $pdo->prepare("SELECT COUNT(*) FROM tbl_category");
$statement->execute();
$total_category = (int)$statement->fetchColumn();

$statement = $pdo->prepare("SELECT COUNT(*) FROM tbl_news");
$statement->execute();
$total_news = (int)$statement->fetchColumn();

$statement = $pdo->prepare("SELECT SUM(total_view) FROM tbl_news");
$statement->execute();
$total_news_views = (int)$statement->fetchColumn();

$statement = $pdo->prepare("SELECT COUNT(*) FROM tbl_photo");
$statement->execute();
$total_photo = (int)$statement->fetchColumn();

$statement = $pdo->prepare("SELECT COUNT(*) FROM tbl_video");
$statement->execute();
$total_video = (int)$statement->fetchColumn();

$statement = $pdo->prepare("SELECT COUNT(*) FROM tbl_subscriber WHERE subs_active=1");
$statement->execute();
$total_subscriber = (int)$statement->fetchColumn();

$statement = $pdo->prepare("SELECT COUNT(*) FROM tbl_doctor WHERE status='Active'");
$statement->execute();
$total_medico = (int)$statement->fetchColumn();

$statement = $pdo->prepare("SELECT COUNT(*) FROM tbl_doctor WHERE status='Inactive'");
$statement->execute();
$total_medicos_inativos = (int)$statement->fetchColumn();

$statement = $pdo->prepare("SELECT COUNT(*) FROM tbl_department");
$statement->execute();
$total_clinica = (int)$statement->fetchColumn();

$statement = $pdo->prepare("SELECT COUNT(*) FROM tbl_dental");
$statement->execute();
$total_dental = (int)$statement->fetchColumn();

$statement = $pdo->prepare("SELECT COUNT(*) FROM tbl_trabalhe");
$statement->execute();
$total_jobs = (int)$statement->fetchColumn();

$statement = $pdo->prepare("SELECT COUNT(*) FROM tbl_pricing_plan");
$statement->execute();
$total_plans = (int)$statement->fetchColumn();

$statement = $pdo->prepare("SELECT COUNT(*) FROM tbl_partner");
$statement->execute();
$total_partners = (int)$statement->fetchColumn();
?>

<!-- Cabeçalho do Conteúdo -->
<div class="app-content-header">
	<div class="container-fluid">
		<div class="row align-items-center">
			<div class="col-sm-6">
				<h3 class="mb-0 fw-bold d-flex align-items-center gap-2">
					<i class="bi bi-speedometer2 text-primary"></i> Painel de Controle
				</h3>
				<p class="text-body-secondary mb-0 small">
					Visão executiva e indicadores em tempo real • Pronto Saúde
				</p>
			</div>
			<div class="col-sm-6 text-sm-end mt-2 mt-sm-0">
				<span class="badge bg-body-secondary text-body border px-3 py-2 me-1">
					<i class="bi bi-calendar3 me-1 text-primary"></i> <?= date('d/m/Y'); ?> (São Paulo)
				</span>
				<span class="badge text-bg-success border border-success-subtle px-3 py-2">
					<i class="bi bi-lightning-charge-fill me-1"></i> PHP 8.4 Otimizado
				</span>
			</div>
		</div>
	</div>
</div>

<!-- Conteúdo Principal com KPIs Interativos -->
<div class="app-content">
	<div class="container-fluid">

		<!-- Alerta de Boas-vindas Humanizado -->
		<div class="alert alert-primary bg-primary-subtle border-primary-subtle text-primary-emphasis d-flex align-items-center justify-content-between p-3 rounded-4 shadow-sm mb-4">
			<div class="d-flex align-items-center gap-3">
				<div class="fs-1 text-primary">
					<i class="bi bi-person-circle"></i>
				</div>
				<div>
					<h5 class="alert-heading mb-1 fw-bold">Olá, <?= htmlspecialchars($_SESSION['user']['full_name']); ?>!</h5>
					<p class="mb-0 small text-body-secondary">
						Painel administrativo atualizado para <strong>AdminLTE 4</strong> com notificações ativas, confirmações seguras via <strong>SweetAlert2</strong> e <strong>Toastr</strong>.
					</p>
				</div>
			</div>
			<div class="d-none d-md-block">
				<button class="btn btn-sm btn-primary" onclick="window.notificar('success', 'Todas as funções operando com 100% de estabilidade no PHP 8.4!', 'Painel Atualizado')">
					<i class="bi bi-bell-fill me-1"></i> Testar Toastr
				</button>
			</div>
		</div>

		<!-- GRID DE KPIS MODERNOS (Cards Gradientes) -->
		<div class="row g-3 mb-4">

			<!-- KPI 1: Corpo Clínico Ativo -->
			<div class="col-12 col-sm-6 col-xl-3">
				<div class="kpi-card kpi-gradient-teal">
					<i class="bi bi-heart-pulse-fill kpi-icon"></i>
					<div class="kpi-title">Corpo Clínico Ativo</div>
					<div class="kpi-number"><?= $total_medico; ?></div>
					<div class="small opacity-75">
						<i class="bi bi-check-circle-fill me-1"></i> Especialistas em atendimento
					</div>
					<a href="doctor.php" class="kpi-footer">
						<span>Ver Médicos</span>
						<i class="bi bi-arrow-right"></i>
					</a>
				</div>
			</div>

			<!-- KPI 2: Notícias e Publicações -->
			<div class="col-12 col-sm-6 col-xl-3">
				<div class="kpi-card kpi-gradient-emerald">
					<i class="bi bi-newspaper kpi-icon"></i>
					<div class="kpi-title">Notícias Publicadas</div>
					<div class="kpi-number"><?= $total_news; ?></div>
					<div class="small opacity-75">
						<i class="bi bi-eye-fill me-1"></i> <?= number_format($total_news_views, 0, ',', '.'); ?> visualizações
					</div>
					<a href="news.php" class="kpi-footer">
						<span>Gerenciar Notícias</span>
						<i class="bi bi-arrow-right"></i>
					</a>
				</div>
			</div>

			<!-- KPI 3: Clínicas e Departamentos -->
			<div class="col-12 col-sm-6 col-xl-3">
				<div class="kpi-card kpi-gradient-blue">
					<i class="bi bi-hospital-fill kpi-icon"></i>
					<div class="kpi-title">Clínicas / Departamentos</div>
					<div class="kpi-number"><?= $total_clinica; ?></div>
					<div class="small opacity-75">
						<i class="bi bi-diagram-3-fill me-1"></i> Unidades e especialidades
					</div>
					<a href="department.php" class="kpi-footer">
						<span>Ver Unidades</span>
						<i class="bi bi-arrow-right"></i>
					</a>
				</div>
			</div>

			<!-- KPI 4: Vagas de Emprego -->
			<div class="col-12 col-sm-6 col-xl-3">
				<div class="kpi-card kpi-gradient-amber">
					<i class="bi bi-briefcase-fill kpi-icon"></i>
					<div class="kpi-title">Vagas no Trabalhe Conosco</div>
					<div class="kpi-number"><?= $total_jobs; ?></div>
					<div class="small opacity-75">
						<i class="bi bi-clock-fill me-1"></i> Oportunidades disponíveis
					</div>
					<a href="trabalhe-conosco.php" class="kpi-footer">
						<span>Visualizar Vagas</span>
						<i class="bi bi-arrow-right"></i>
					</a>
				</div>
			</div>

			<!-- KPI 5: Serviços Odontológicos -->
			<div class="col-12 col-sm-6 col-xl-3">
				<div class="kpi-card kpi-gradient-cyan">
					<i class="bi bi-bandaid-fill kpi-icon"></i>
					<div class="kpi-title">Serviços Dentários</div>
					<div class="kpi-number"><?= $total_dental; ?></div>
					<div class="small opacity-75">
						<i class="bi bi-shield-check me-1"></i> Tratamentos cadastrados
					</div>
					<a href="dental.php" class="kpi-footer">
						<span>Tabela Dentária</span>
						<i class="bi bi-arrow-right"></i>
					</a>
				</div>
			</div>

			<!-- KPI 6: Inscritos na Newsletter -->
			<div class="col-12 col-sm-6 col-xl-3">
				<div class="kpi-card kpi-gradient-indigo">
					<i class="bi bi-envelope-check-fill kpi-icon"></i>
					<div class="kpi-title">Inscritos Ativos</div>
					<div class="kpi-number"><?= $total_subscriber; ?></div>
					<div class="small opacity-75">
						<i class="bi bi-send-fill me-1"></i> Base confirmada
					</div>
					<?php if($_SESSION['user']['role'] == 'Super Admin'): ?>
					<a href="subscriber.php" class="kpi-footer">
						<span>Exportar / Ver Lista</span>
						<i class="bi bi-arrow-right"></i>
					</a>
					<?php else: ?>
					<div class="kpi-footer"><span>Base Newsletter</span></div>
					<?php endif; ?>
				</div>
			</div>

			<!-- KPI 7: Planos de Saúde -->
			<div class="col-12 col-sm-6 col-xl-3">
				<div class="kpi-card kpi-gradient-purple">
					<i class="bi bi-cash-stack kpi-icon"></i>
					<div class="kpi-title">Planos de Saúde</div>
					<div class="kpi-number"><?= $total_plans; ?></div>
					<div class="small opacity-75">
						<i class="bi bi-star-fill me-1"></i> Modalidades e fidelidade
					</div>
					<a href="pricing-plan.php" class="kpi-footer">
						<span>Gerenciar Planos</span>
						<i class="bi bi-arrow-right"></i>
					</a>
				</div>
			</div>

			<!-- KPI 8: Usuários do Painel -->
			<?php if($_SESSION['user']['role'] == 'Super Admin'): ?>
			<div class="col-12 col-sm-6 col-xl-3">
				<div class="kpi-card kpi-gradient-rose">
					<i class="bi bi-people-fill kpi-icon"></i>
					<div class="kpi-title">Gestores / Usuários</div>
					<div class="kpi-number"><?= $total_user; ?></div>
					<div class="small opacity-75">
						<i class="bi bi-shield-lock-fill me-1"></i> Acessos administrativos
					</div>
					<a href="user.php" class="kpi-footer">
						<span>Controle de Acessos</span>
						<i class="bi bi-arrow-right"></i>
					</a>
				</div>
			</div>
			<?php else: ?>
			<div class="col-12 col-sm-6 col-xl-3">
				<div class="kpi-card kpi-gradient-rose">
					<i class="bi bi-images kpi-icon"></i>
					<div class="kpi-title">Mídias & Fotos</div>
					<div class="kpi-number"><?= $total_photo + $total_video; ?></div>
					<div class="small opacity-75">
						<i class="bi bi-camera-fill me-1"></i> Fotos e vídeos cadastrados
					</div>
					<a href="photo.php" class="kpi-footer">
						<span>Galeria de Mídias</span>
						<i class="bi bi-arrow-right"></i>
					</a>
				</div>
			</div>
			<?php endif; ?>

		</div>

		<!-- SEÇÃO DE GRÁFICOS INTERATIVOS (ApexCharts) -->
		<div class="row g-3 mb-4">
			
			<!-- Gráfico 1: Visão Geral de Indicadores -->
			<div class="col-12 col-lg-8">
				<div class="card border-0 shadow-sm rounded-4 h-100">
					<div class="card-header bg-transparent border-bottom p-3 d-flex align-items-center justify-content-between">
						<div>
							<h5 class="card-title fw-bold mb-0">
								<i class="bi bi-bar-chart-line-fill text-primary me-2"></i>Distribuição e Volume do Sistema
							</h5>
							<small class="text-body-secondary">Métricas consolidadas de atendimento e conteúdos</small>
						</div>
						<span class="badge bg-primary-subtle text-primary border border-primary-subtle">
							Interativo
						</span>
					</div>
					<div class="card-body p-3">
						<div id="chart-indicadores" style="min-height: 320px;"></div>
					</div>
				</div>
			</div>

			<!-- Gráfico 2: Composição dos Serviços de Saúde (Donut) -->
			<div class="col-12 col-lg-4">
				<div class="card border-0 shadow-sm rounded-4 h-100">
					<div class="card-header bg-transparent border-bottom p-3 d-flex align-items-center justify-content-between">
						<div>
							<h5 class="card-title fw-bold mb-0">
								<i class="bi bi-pie-chart-fill text-info me-2"></i>Módulos de Saúde
							</h5>
							<small class="text-body-secondary">Proporção dos recursos assistenciais</small>
						</div>
					</div>
					<div class="card-body p-3 d-flex flex-column justify-content-center align-items-center">
						<div id="chart-donut-saude" style="width: 100%; min-height: 290px;"></div>
					</div>
				</div>
			</div>

		</div>

		<!-- AÇÕES RÁPIDAS (Quick Actions) -->
		<div class="card border-0 shadow-sm rounded-4 mb-4">
			<div class="card-header bg-transparent border-bottom p-3">
				<h5 class="card-title fw-bold mb-0">
					<i class="bi bi-lightning-fill text-warning me-2"></i>Ações Rápidas do Gestor
				</h5>
				<small class="text-body-secondary">Atalhos diretos para as rotinas mais frequentes</small>
			</div>
			<div class="card-body p-3">
				<div class="row g-3">
					<div class="col-6 col-md-4 col-xl-2">
						<a href="doctor-add.php" class="quick-action-btn">
							<i class="bi bi-person-plus-fill"></i>
							<div>
								<div class="small text-body-secondary">Cadastrar</div>
								<div>Novo Médico</div>
							</div>
						</a>
					</div>
					<div class="col-6 col-md-4 col-xl-2">
						<a href="news-add.php" class="quick-action-btn">
							<i class="bi bi-pencil-square"></i>
							<div>
								<div class="small text-body-secondary">Publicar</div>
								<div>Nova Notícia</div>
							</div>
						</a>
					</div>
					<div class="col-6 col-md-4 col-xl-2">
						<a href="vagas-add.php" class="quick-action-btn">
							<i class="bi bi-plus-circle-fill"></i>
							<div>
								<div class="small text-body-secondary">Abrir</div>
								<div>Nova Vaga</div>
							</div>
						</a>
					</div>
					<div class="col-6 col-md-4 col-xl-2">
						<a href="department-add.php" class="quick-action-btn">
							<i class="bi bi-hospital"></i>
							<div>
								<div class="small text-body-secondary">Adicionar</div>
								<div>Clínica / Depto</div>
							</div>
						</a>
					</div>
					<div class="col-6 col-md-4 col-xl-2">
						<a href="settings.php" class="quick-action-btn">
							<i class="bi bi-sliders"></i>
							<div>
								<div class="small text-body-secondary">Configurar</div>
								<div>Ajustes Gerais</div>
							</div>
						</a>
					</div>
					<div class="col-6 col-md-4 col-xl-2">
						<a href="<?= BASE_URL ?>" target="_blank" class="quick-action-btn">
							<i class="bi bi-box-arrow-up-right"></i>
							<div>
								<div class="small text-body-secondary">Navegar</div>
								<div>Portal Público</div>
							</div>
						</a>
					</div>
				</div>
			</div>
		</div>

		<!-- CANAIS DE SUPORTE E ATENDIMENTO TÉCNICO -->
		<div class="row g-3">
			<div class="col-12 col-md-4">
				<div class="support-card">
					<div class="support-icon remote">
						<i class="bi bi-display"></i>
					</div>
					<h5 class="fw-bold mb-2">Suporte Remoto</h5>
					<p class="text-body-secondary small mb-4 flex-grow-1">
						Os dados de acesso ao sistema devem ser enviados por e-mail para realização de suporte ou manutenções assistidas.
					</p>
					<a href="mailto:marcelobradrj@gmail.com" class="btn btn-warning text-dark fw-bold w-100" onclick="window.notificar && window.notificar('info', 'Abrindo cliente de e-mail para suporte remoto...', 'Suporte')">
						<i class="bi bi-envelope-fill me-1"></i> Enviar Solicitação
					</a>
				</div>
			</div>

			<div class="col-12 col-md-4">
				<div class="support-card">
					<div class="support-icon phone">
						<i class="bi bi-telephone-inbound-fill"></i>
					</div>
					<h5 class="fw-bold mb-2">Suporte Telefônico</h5>
					<p class="text-body-secondary small mb-4 flex-grow-1">
						Ao solicitar suporte por telefone, descreva o cenário identificado para que o técnico possa orientá-lo com agilidade.
					</p>
					<a href="tel:5521981325441" class="btn btn-info text-white fw-bold w-100" onclick="window.notificar && window.notificar('info', 'Iniciando discagem para o suporte...', 'Telefone')">
						<i class="bi bi-telephone-fill me-1"></i> Ligar: (21) 98132-5441
					</a>
				</div>
			</div>

			<div class="col-12 col-md-4">
				<div class="support-card">
					<div class="support-icon whatsapp">
						<i class="bi bi-whatsapp"></i>
					</div>
					<h5 class="fw-bold mb-2">Suporte via WhatsApp</h5>
					<p class="text-body-secondary small mb-4 flex-grow-1">
						Envie capturas de tela e detalhamento da solicitação diretamente para o atendimento especializado.
					</p>
					<a href="https://wa.me/5521981325441?text=Ol%C3%A1%2C%20preciso%20de%20suporte%20no%20sistema%20Pronto%20Sa%C3%BAde" target="_blank" class="btn btn-success fw-bold w-100" onclick="window.notificar && window.notificar('success', 'Redirecionando para o WhatsApp...', 'Suporte')">
						<i class="bi bi-whatsapp me-1"></i> Iniciar Conversa
					</a>
				</div>
			</div>
		</div>

	</div>
</div>

<!-- SCRIPTS DOS GRÁFICOS APEXCHARTS INTERATIVOS -->
<script>
window.renderDashboardCharts = function(isDark) {
	// Limpar instâncias anteriores se existirem
	if (window.chartIndicadoresInstance) {
		window.chartIndicadoresInstance.destroy();
	}
	if (window.chartDonutInstance) {
		window.chartDonutInstance.destroy();
	}

	const themeMode = isDark ? 'dark' : 'light';
	const textColor = isDark ? '#cbd5e1' : '#475569';

	// 1. Gráfico de Barras Moderno
	const optionsIndicadores = {
		series: [{
			name: 'Quantidade Cadastrada',
			data: [
				<?= $total_medico; ?>,
				<?= $total_news; ?>,
				<?= $total_clinica; ?>,
				<?= $total_jobs; ?>,
				<?= $total_dental; ?>,
				<?= $total_plans; ?>,
				<?= $total_subscriber; ?>
			]
		}],
		chart: {
			type: 'bar',
			height: 320,
			toolbar: { show: false },
			fontFamily: 'Source Sans 3, sans-serif'
		},
		theme: { mode: themeMode },
		plotOptions: {
			bar: {
				borderRadius: 8,
				columnWidth: '45%',
				distributed: true
			}
		},
		colors: ['#0a6b74', '#10b981', '#3b82f6', '#f59e0b', '#06b6d4', '#8b5cf6', '#6366f1'],
		dataLabels: {
			enabled: true,
			style: { fontSize: '12px', fontWeight: 700 }
		},
		legend: { show: false },
		xaxis: {
			categories: ['Médicos', 'Notícias', 'Clínicas', 'Vagas', 'Odonto', 'Planos', 'Inscritos'],
			labels: {
				style: { colors: textColor, fontSize: '12px', fontWeight: 600 }
			}
		},
		yaxis: {
			labels: {
				style: { colors: textColor, fontSize: '12px' }
			}
		},
		grid: {
			borderColor: isDark ? '#334155' : '#e2e8f0',
			strokeDashArray: 4
		},
		tooltip: {
			theme: themeMode,
			y: {
				formatter: function (val) {
					return val + " cadastros";
				}
			}
		}
	};

	window.chartIndicadoresInstance = new ApexCharts(document.querySelector("#chart-indicadores"), optionsIndicadores);
	window.chartIndicadoresInstance.render();

	// 2. Gráfico Donut de Saúde
	const optionsDonut = {
		series: [
			<?= max(1, $total_medico); ?>,
			<?= max(1, $total_clinica); ?>,
			<?= max(1, $total_dental); ?>,
			<?= max(1, $total_jobs); ?>
		],
		chart: {
			type: 'donut',
			height: 300,
			fontFamily: 'Source Sans 3, sans-serif'
		},
		theme: { mode: themeMode },
		labels: ['Médicos', 'Clínicas', 'Odontologia', 'Vagas'],
		colors: ['#0a6b74', '#3b82f6', '#06b6d4', '#f59e0b'],
		legend: {
			position: 'bottom',
			labels: { colors: textColor }
		},
		dataLabels: {
			enabled: true,
			formatter: function (val) {
				return Math.round(val) + "%";
			}
		},
		plotOptions: {
			pie: {
				donut: {
					size: '70%',
					labels: {
						show: true,
						name: { color: textColor },
						value: { color: textColor, fontSize: '20px', fontWeight: 700 },
						total: {
							show: true,
							label: 'Total Módulos',
							color: textColor,
							formatter: function () {
								return <?= $total_medico + $total_clinica + $total_dental + $total_jobs; ?>;
							}
						}
					}
				}
			}
		},
		tooltip: {
			theme: themeMode,
			y: {
				formatter: function(val) {
					return val + " itens";
				}
			}
		}
	};

	window.chartDonutInstance = new ApexCharts(document.querySelector("#chart-donut-saude"), optionsDonut);
	window.chartDonutInstance.render();
};

document.addEventListener('DOMContentLoaded', function () {
	const currentTheme = document.documentElement.getAttribute('data-bs-theme') || 'light';
	window.renderDashboardCharts(currentTheme === 'dark');
});
</script>

<?php require_once('footer.php'); ?>