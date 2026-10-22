<?php require_once('header.php'); ?>

<?php
// Carrega as configurações atuais de seções
$statement = $pdo->prepare("SELECT * FROM tbl_settings WHERE id=1");
$statement->execute();
$config = $statement->fetch(PDO::FETCH_ASSOC) ?: [];

$status_service     = (int)($config['home_status_service'] ?? 1);
$status_department  = (int)($config['home_status_department'] ?? 1);
$status_doctor      = (int)($config['home_status_doctor'] ?? 1);
$status_pricing     = (int)($config['home_status_pricing'] ?? 1);
$status_testimonial = (int)($config['home_status_testimonial'] ?? 1);
$status_news        = (int)($config['home_status_news'] ?? 1);
$status_partner     = (int)($config['home_status_partner'] ?? 1);

// Modo de Manutenção (com checagem segura se a coluna existir ou arquivo flag)
$status_maintenance = (int)($config['maintenance_mode'] ?? 0);
if (file_exists(__DIR__ . '/../.maintenance')) {
    $status_maintenance = 1;
}
?>

<div class="app-content-header">
	<div class="container-fluid">
		<div class="row align-items-center">
			<div class="col-sm-6">
				<h3 class="mb-0 fw-bold d-flex align-items-center gap-2">
					<i class="bi bi-toggles text-primary"></i> Controle Individual de Seções
				</h3>
				<p class="text-body-secondary mb-0 small">
					Ative ou desative cada seção do portal público instantaneamente via AJAX
				</p>
			</div>
			<div class="col-sm-6 text-sm-end mt-2 mt-sm-0">
				<a href="<?= BASE_URL ?>" target="_blank" class="btn btn-sm btn-outline-primary d-inline-flex align-items-center gap-1 shadow-sm">
					<i class="bi bi-box-arrow-up-right"></i> Visualizar Portal
				</a>
			</div>
		</div>
	</div>
</div>

<div class="app-content">
	<div class="container-fluid">

		<!-- Alerta Informativo -->
		<div class="alert alert-info bg-info-subtle border-info-subtle text-info-emphasis d-flex align-items-center gap-3 p-3 rounded-4 shadow-sm mb-4">
			<i class="bi bi-info-circle-fill fs-2 text-info"></i>
			<div>
				<h6 class="fw-bold mb-1">Alterações Instantâneas em Tempo Real</h6>
				<p class="mb-0 small">
					Basta ligar ou desligar qualquer chave abaixo. O sistema salva a preferência no banco de dados automaticamente via AJAX, sem que você precise recarregar a página.
				</p>
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
							Quando ativo, os visitantes públicos serão direcionados para a página de manutenção personalizada. Administradores continuam com acesso normal.
						</p>
					</div>
				</div>
				<div class="form-check form-switch fs-3 mb-0">
					<input class="form-check-input switch-ajax" type="checkbox" role="switch" id="switch_maintenance_mode" data-secao="maintenance_mode" <?= $status_maintenance == 1 ? 'checked' : ''; ?>>
				</div>
			</div>
		</div>

		<!-- Grid de Seções do Portal -->
		<div class="row g-3">

			<!-- 1. Serviços Médicos -->
			<div class="col-12 col-md-6 col-xl-4">
				<div class="card border-0 shadow-sm rounded-4 h-100">
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
								<input class="form-check-input switch-ajax" type="checkbox" role="switch" data-secao="home_status_service" <?= $status_service == 1 ? 'checked' : ''; ?>>
							</div>
						</div>
						<p class="text-body-secondary small mb-0">
							Exibe os principais procedimentos, exames e serviços de saúde com botão para agendamento.
						</p>
					</div>
				</div>
			</div>

			<!-- 2. Clínicas e Departamentos -->
			<div class="col-12 col-md-6 col-xl-4">
				<div class="card border-0 shadow-sm rounded-4 h-100">
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
								<input class="form-check-input switch-ajax" type="checkbox" role="switch" data-secao="home_status_department" <?= $status_department == 1 ? 'checked' : ''; ?>>
							</div>
						</div>
						<p class="text-body-secondary small mb-0">
							Apresenta a estrutura física, fotos e especialidades de cada departamento da rede.
						</p>
					</div>
				</div>
			</div>

			<!-- 3. Corpo Clínico (Médicos) -->
			<div class="col-12 col-md-6 col-xl-4">
				<div class="card border-0 shadow-sm rounded-4 h-100">
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
								<input class="form-check-input switch-ajax" type="checkbox" role="switch" data-secao="home_status_doctor" <?= $status_doctor == 1 ? 'checked' : ''; ?>>
							</div>
						</div>
						<p class="text-body-secondary small mb-0">
							Mostra a equipe médica ativa, foto profissional, CRM e botão de perfil detalhado.
						</p>
					</div>
				</div>
			</div>

			<!-- 4. Planos e Preços -->
			<div class="col-12 col-md-6 col-xl-4">
				<div class="card border-0 shadow-sm rounded-4 h-100">
					<div class="card-body p-4 d-flex flex-column justify-content-between">
						<div class="d-flex align-items-start justify-content-between mb-3">
							<div class="d-flex align-items-center gap-2">
								<div class="p-2 rounded-3 bg-warning-subtle text-warning fs-4">
									<i class="bi bi-cash-stack"></i>
								</div>
								<div>
									<h6 class="fw-bold mb-0">Planos & Fidelidade</h6>
									<small class="text-body-secondary">Home &bull; Valores e Benefícios</small>
								</div>
							</div>
							<div class="form-check form-switch fs-4 mb-0">
								<input class="form-check-input switch-ajax" type="checkbox" role="switch" data-secao="home_status_pricing" <?= $status_pricing == 1 ? 'checked' : ''; ?>>
							</div>
						</div>
						<p class="text-body-secondary small mb-0">
							Tabela comparativa de planos individuais, familiares e planos corporativos com preços.
						</p>
					</div>
				</div>
			</div>

			<!-- 5. Depoimentos de Pacientes -->
			<div class="col-12 col-md-6 col-xl-4">
				<div class="card border-0 shadow-sm rounded-4 h-100">
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
								<input class="form-check-input switch-ajax" type="checkbox" role="switch" data-secao="home_status_testimonial" <?= $status_testimonial == 1 ? 'checked' : ''; ?>>
							</div>
						</div>
						<p class="text-body-secondary small mb-0">
							Carrossel com relatos reais, elogios e avaliações de pacientes atendidos na clínica.
						</p>
					</div>
				</div>
			</div>

			<!-- 6. Notícias e Blog -->
			<div class="col-12 col-md-6 col-xl-4">
				<div class="card border-0 shadow-sm rounded-4 h-100">
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
								<input class="form-check-input switch-ajax" type="checkbox" role="switch" data-secao="home_status_news" <?= $status_news == 1 ? 'checked' : ''; ?>>
							</div>
						</div>
						<p class="text-body-secondary small mb-0">
							Últimas postagens do blog médico, orientações preventivas e novidades da instituição.
						</p>
					</div>
				</div>
			</div>

			<!-- 7. Parceiros Institucionais -->
			<div class="col-12 col-md-6 col-xl-4">
				<div class="card border-0 shadow-sm rounded-4 h-100">
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
								<input class="form-check-input switch-ajax" type="checkbox" role="switch" data-secao="home_status_partner" <?= $status_partner == 1 ? 'checked' : ''; ?>>
							</div>
						</div>
						<p class="text-body-secondary small mb-0">
							Carrossel com logomarcas de laboratórios credenciados, operadoras e empresas parceiras.
						</p>
					</div>
				</div>
			</div>

		</div>

	</div>
</div>

<!-- Script de Controle AJAX dos Switches -->
<script>
document.addEventListener('DOMContentLoaded', function () {
	const switches = document.querySelectorAll('.switch-ajax');
	switches.forEach(sw => {
		if (sw.dataset.switchBound === 'true') return;
		sw.dataset.switchBound = 'true';
		sw.addEventListener('change', function () {
			const secao = this.getAttribute('data-secao');
			const status = this.checked ? 1 : 0;
			const inputElement = this;

			inputElement.disabled = true;

			const formData = new FormData();
			formData.append('secao', secao);
			formData.append('status', status);

			fetch('secoes-ajax.php', {
				method: 'POST',
				body: formData,
				headers: {
					'X-Requested-With': 'XMLHttpRequest'
				}
			})
			.then(res => res.json())
			.then(data => {
				inputElement.disabled = false;
				if (data.success) {
					if (window.notificar) {
						window.notificar('success', data.message, 'Atualizado');
					}
				} else {
					inputElement.checked = !inputElement.checked;
					if (window.notificar) {
						window.notificar('error', data.message || 'Erro ao atualizar.', 'Falha');
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
});
</script>

<?php require_once('footer.php'); ?>
