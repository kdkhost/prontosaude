<?php
require_once('header.php');

// Permissão restrita a administradores
if ($_SESSION['user']['role'] !== 'Super Admin' && $_SESSION['user']['role'] !== 'Admin') {
    echo "<script>location.href='index.php';</script>";
    exit;
}

// 1. Carrega as configurações de segurança atuais
$stmt = $pdo->query("SELECT * FROM tbl_security_settings WHERE id = 1 LIMIT 1");
$sec = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

// 2. Estatísticas para os KPIs
$totalBloqueios = (int)$pdo->query("SELECT COUNT(*) FROM tbl_security_logs WHERE action_taken = 'BLOCKED'")->fetchColumn();
$totalInvasoes  = (int)$pdo->query("SELECT COUNT(*) FROM tbl_security_logs WHERE event_type IN ('SQL_INJECTION', 'XSS_ATTACK', 'RCE_ATTACK', 'PATH_TRAVERSAL')")->fetchColumn();
$totalForcaBruta = (int)$pdo->query("SELECT COUNT(*) FROM tbl_security_logs WHERE event_type IN ('BRUTE_FORCE_LOCKOUT', 'LOGIN_FAILED')")->fetchColumn();
$totalIpsBanidos = (int)$pdo->query("SELECT COUNT(*) FROM tbl_blocked_ips WHERE blocked_until IS NULL OR blocked_until > NOW()")->fetchColumn();

// 3. IPs Bloqueados no Momento
$blockedIpsList = $pdo->query("
    SELECT * FROM tbl_blocked_ips 
    WHERE blocked_until IS NULL OR blocked_until > NOW() 
    ORDER BY id DESC LIMIT 20
")->fetchAll(PDO::FETCH_ASSOC);

// 4. Últimos Logs de Auditoria
$logsList = $pdo->query("
    SELECT * FROM tbl_security_logs 
    ORDER BY id DESC LIMIT 50
")->fetchAll(PDO::FETCH_ASSOC);

$isWafActive = !empty($sec['waf_enabled']);
?>

<div class="app-content-header">
	<div class="container-fluid">
		<div class="row align-items-center">
			<div class="col-sm-7">
				<h3 class="mb-0 fw-bold d-flex align-items-center gap-2">
					<i class="bi bi-shield-fill-check text-danger"></i> Centro de Segurança & Firewall WAF
				</h3>
				<p class="text-body-secondary mb-0 small">
					Defesa ativa de padrão militar: Anti-SQLi, Anti-XSS, Anti-Força Bruta, Proteção Honeypot e Prevenção de Vazamento
				</p>
			</div>
			<div class="col-sm-5 text-sm-end mt-2 mt-sm-0">
				<?php if ($isWafActive): ?>
					<span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2 rounded-pill fs-6">
						<i class="bi bi-shield-lock-fill me-1"></i> ESCUDO ATIVO &bull; 100% PROTEGIDO
					</span>
				<?php else: ?>
					<span class="badge bg-danger-subtle text-danger border border-danger-subtle px-3 py-2 rounded-pill fs-6">
						<i class="bi bi-shield-slash-fill me-1"></i> FIREWALL PAUSADO
					</span>
				<?php endif; ?>
			</div>
		</div>
	</div>
</div>

<div class="app-content">
	<div class="container-fluid">

		<!-- CARDS DE MÉTRICAS E KPIS DE SEGURANÇA -->
		<div class="row g-3 mb-4">
			<!-- KPI 1: Status WAF -->
			<div class="col-6 col-lg-3">
				<div class="card border-0 shadow-sm rounded-4 h-100 bg-body">
					<div class="card-body p-3 d-flex align-items-center justify-content-between">
						<div>
							<small class="text-body-secondary fw-semibold text-uppercase">Firewall WAF</small>
							<h4 class="fw-bold mb-0 mt-1 <?= $isWafActive ? 'text-success' : 'text-danger' ?>">
								<?= $isWafActive ? 'Ativado' : 'Desativado' ?>
							</h4>
							<small class="text-muted" style="font-size: 0.75rem;">Inspeção profunda</small>
						</div>
						<div class="p-3 rounded-circle <?= $isWafActive ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' ?> fs-3">
							<i class="bi <?= $isWafActive ? 'bi-shield-check' : 'bi-shield-x' ?>"></i>
						</div>
					</div>
				</div>
			</div>

			<!-- KPI 2: Total de Bloqueios -->
			<div class="col-6 col-lg-3">
				<div class="card border-0 shadow-sm rounded-4 h-100 bg-body">
					<div class="card-body p-3 d-flex align-items-center justify-content-between">
						<div>
							<small class="text-body-secondary fw-semibold text-uppercase">Ameaças Bloqueadas</small>
							<h4 class="fw-bold mb-0 mt-1 text-primary"><?= number_format($totalBloqueios, 0, ',', '.'); ?></h4>
							<small class="text-muted" style="font-size: 0.75rem;">Requisições hostis</small>
						</div>
						<div class="p-3 rounded-circle bg-primary-subtle text-primary fs-3">
							<i class="bi bi-shield-slash"></i>
						</div>
					</div>
				</div>
			</div>

			<!-- KPI 3: Invasões Neutralizadas -->
			<div class="col-6 col-lg-3">
				<div class="card border-0 shadow-sm rounded-4 h-100 bg-body">
					<div class="card-body p-3 d-flex align-items-center justify-content-between">
						<div>
							<small class="text-body-secondary fw-semibold text-uppercase">Invasões Neutralizadas</small>
							<h4 class="fw-bold mb-0 mt-1 text-danger"><?= number_format($totalInvasoes, 0, ',', '.'); ?></h4>
							<small class="text-muted" style="font-size: 0.75rem;">SQLi, XSS, RCE barrados</small>
						</div>
						<div class="p-3 rounded-circle bg-danger-subtle text-danger fs-3">
							<i class="bi bi-bug-fill"></i>
						</div>
					</div>
				</div>
			</div>

			<!-- KPI 4: Força Bruta Barrada -->
			<div class="col-6 col-lg-3">
				<div class="card border-0 shadow-sm rounded-4 h-100 bg-body">
					<div class="card-body p-3 d-flex align-items-center justify-content-between">
						<div>
							<small class="text-body-secondary fw-semibold text-uppercase">Força Bruta Contida</small>
							<h4 class="fw-bold mb-0 mt-1 text-warning"><?= number_format($totalForcaBruta, 0, ',', '.'); ?></h4>
							<small class="text-muted" style="font-size: 0.75rem;"><?= $totalIpsBanidos ?> IP(s) em quarentena</small>
						</div>
						<div class="p-3 rounded-circle bg-warning-subtle text-warning fs-3">
							<i class="bi bi-lock-fill"></i>
						</div>
					</div>
				</div>
			</div>
		</div>

		<!-- NAVEGAÇÃO POR ABAS -->
		<div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
			<div class="card-header bg-body-tertiary border-0 p-3">
				<ul class="nav nav-pills card-header-pills gap-2" id="securityTabs" role="tablist">
					<li class="nav-item" role="presentation">
						<button class="nav-link active rounded-pill px-3 py-2 fw-semibold" id="tab-waf-btn" data-bs-toggle="pill" data-bs-target="#tab-waf" type="button" role="tab">
							<i class="bi bi-cpu me-1"></i> 1. Firewall WAF
						</button>
					</li>
					<li class="nav-item" role="presentation">
						<button class="nav-link rounded-pill px-3 py-2 fw-semibold" id="tab-bruteforce-btn" data-bs-toggle="pill" data-bs-target="#tab-bruteforce" type="button" role="tab">
							<i class="bi bi-shield-lock me-1"></i> 2. Força Bruta & Logins
						</button>
					</li>
					<li class="nav-item" role="presentation">
						<button class="nav-link rounded-pill px-3 py-2 fw-semibold" id="tab-headers-btn" data-bs-toggle="pill" data-bs-target="#tab-headers" type="button" role="tab">
							<i class="bi bi-file-earmark-lock me-1"></i> 3. Headers & Vazamento
						</button>
					</li>
					<li class="nav-item" role="presentation">
						<button class="nav-link rounded-pill px-3 py-2 fw-semibold" id="tab-ips-btn" data-bs-toggle="pill" data-bs-target="#tab-ips" type="button" role="tab">
							<i class="bi bi-diagram-3 me-1"></i> 4. Gestão de IPs
						</button>
					</li>
					<li class="nav-item" role="presentation">
						<button class="nav-link rounded-pill px-3 py-2 fw-semibold" id="tab-logs-btn" data-bs-toggle="pill" data-bs-target="#tab-logs" type="button" role="tab">
							<i class="bi bi-journal-text me-1"></i> 5. Auditoria & Logs
						</button>
					</li>
				</ul>
			</div>

			<div class="card-body p-4">
				<div class="tab-content" id="securityTabsContent">

					<!-- ABA 1: FIREWALL WAF -->
					<div class="tab-pane fade show active" id="tab-waf" role="tabpanel">
						<h5 class="fw-bold mb-3 d-flex align-items-center gap-2">
							<i class="bi bi-shield-shaded text-primary"></i> Módulos de Inspeção em Tempo Real (WAF)
						</h5>
						<p class="text-body-secondary small mb-4">
							Ative ou desative filtros individuais de segurança. Cada requisição é inspecionada nos parâmetros GET, POST, COOKIES e cabeçalhos HTTP.
						</p>

						<div class="row g-3">
							<!-- WAF Geral -->
							<div class="col-md-6 col-lg-4">
								<div class="card h-100 border border-secondary-subtle rounded-3 p-3">
									<div class="d-flex justify-content-between align-items-start mb-2">
										<div class="fw-bold text-body">Firewall WAF Geral</div>
										<div class="form-check form-switch fs-4 mb-0">
											<input class="form-check-input sec-toggle" type="checkbox" role="switch" data-chave="waf_enabled" <?= !empty($sec['waf_enabled']) ? 'checked' : '' ?>>
										</div>
									</div>
									<small class="text-body-secondary">Motor central de análise e interceptação de pacotes maliciosos.</small>
								</div>
							</div>

							<!-- Anti-SQL Injection -->
							<div class="col-md-6 col-lg-4">
								<div class="card h-100 border border-secondary-subtle rounded-3 p-3">
									<div class="d-flex justify-content-between align-items-start mb-2">
										<div class="fw-bold text-body">Anti-SQL Injection (SQLi)</div>
										<div class="form-check form-switch fs-4 mb-0">
											<input class="form-check-input sec-toggle" type="checkbox" role="switch" data-chave="block_sql_injection" <?= !empty($sec['block_sql_injection']) ? 'checked' : '' ?>>
										</div>
									</div>
									<small class="text-body-secondary">Bloqueia UNION SELECT, blind timing, dump de bancos e comandos DDL.</small>
								</div>
							</div>

							<!-- Anti-XSS -->
							<div class="col-md-6 col-lg-4">
								<div class="card h-100 border border-secondary-subtle rounded-3 p-3">
									<div class="d-flex justify-content-between align-items-start mb-2">
										<div class="fw-bold text-body">Anti-Cross Site Scripting (XSS)</div>
										<div class="form-check form-switch fs-4 mb-0">
											<input class="form-check-input sec-toggle" type="checkbox" role="switch" data-chave="block_xss" <?= !empty($sec['block_xss']) ? 'checked' : '' ?>>
										</div>
									</div>
									<small class="text-body-secondary">Impede injeção de scripts, eventos onerror/onload e roubo de cookies.</small>
								</div>
							</div>

							<!-- Anti-Path Traversal -->
							<div class="col-md-6 col-lg-4">
								<div class="card h-100 border border-secondary-subtle rounded-3 p-3">
									<div class="d-flex justify-content-between align-items-start mb-2">
										<div class="fw-bold text-body">Anti-Path Traversal (LFI/RFI)</div>
										<div class="form-check form-switch fs-4 mb-0">
											<input class="form-check-input sec-toggle" type="checkbox" role="switch" data-chave="block_path_traversal" <?= !empty($sec['block_path_traversal']) ? 'checked' : '' ?>>
										</div>
									</div>
									<small class="text-body-secondary">Bloqueia tentativas de leitura de arquivos do sistema (ex: ../../, /etc/passwd).</small>
								</div>
							</div>

							<!-- Anti-RCE -->
							<div class="col-md-6 col-lg-4">
								<div class="card h-100 border border-secondary-subtle rounded-3 p-3">
									<div class="d-flex justify-content-between align-items-start mb-2">
										<div class="fw-bold text-body">Bloqueio de Comandos Shell & RCE</div>
										<div class="form-check form-switch fs-4 mb-0">
											<input class="form-check-input sec-toggle" type="checkbox" role="switch" data-chave="block_rce" <?= !empty($sec['block_rce']) ? 'checked' : '' ?>>
										</div>
									</div>
									<small class="text-body-secondary">Neutraliza chamadas a system(), passthru(), shell_exec() e tags PHP maliciosas.</small>
								</div>
							</div>

							<!-- Scanners e Bad Bots -->
							<div class="col-md-6 col-lg-4">
								<div class="card h-100 border border-secondary-subtle rounded-3 p-3">
									<div class="d-flex justify-content-between align-items-start mb-2">
										<div class="fw-bold text-body">Bloqueio de Scanners & Bad Bots</div>
										<div class="form-check form-switch fs-4 mb-0">
											<input class="form-check-input sec-toggle" type="checkbox" role="switch" data-chave="block_bad_bots" <?= !empty($sec['block_bad_bots']) ? 'checked' : '' ?>>
										</div>
									</div>
									<small class="text-body-secondary">Identifica e barra ferramentas automáticas como SQLMap, Nikto, WPScan e Nmap.</small>
								</div>
							</div>

							<!-- Auto-Ban Reincidentes -->
							<div class="col-md-6 col-lg-4">
								<div class="card h-100 border border-secondary-subtle rounded-3 p-3">
									<div class="d-flex justify-content-between align-items-start mb-2">
										<div class="fw-bold text-body">Auto-Ban para Reincidentes</div>
										<div class="form-check form-switch fs-4 mb-0">
											<input class="form-check-input sec-toggle" type="checkbox" role="switch" data-chave="auto_block_repeat_attackers" <?= !empty($sec['auto_block_repeat_attackers']) ? 'checked' : '' ?>>
										</div>
									</div>
									<small class="text-body-secondary">Bloqueia automaticamente por 24h qualquer IP que dispare 3 ou mais ataques em 1 hora.</small>
								</div>
							</div>
						</div>
					</div>

					<!-- ABA 2: FORÇA BRUTA & LOGINS -->
					<div class="tab-pane fade" id="tab-bruteforce" role="tabpanel">
						<h5 class="fw-bold mb-3 d-flex align-items-center gap-2">
							<i class="bi bi-person-lock text-warning"></i> Defesa Ativa contra Força Bruta & Ataques de Dicionário
						</h5>
						<p class="text-body-secondary small mb-4">
							Configurações de suspensão temporária de IPs e contas após sucessivas falhas de autenticação.
						</p>

						<form id="form-security-settings" class="row g-4">
							<input type="hidden" name="action" value="save_settings">

							<div class="col-12 col-md-6">
								<div class="card h-100 border border-secondary-subtle rounded-3 p-3">
									<div class="d-flex justify-content-between align-items-start mb-3">
										<div>
											<h6 class="fw-bold mb-1">Proteção contra Força Bruta</h6>
											<small class="text-body-secondary">Monitora falhas consecutivas de login por IP e e-mail.</small>
										</div>
										<div class="form-check form-switch fs-4 mb-0">
											<input class="form-check-input sec-toggle" type="checkbox" role="switch" data-chave="brute_force_protection" <?= !empty($sec['brute_force_protection']) ? 'checked' : '' ?>>
										</div>
									</div>

									<div class="mb-3">
										<label class="form-label fw-semibold small">Máximo de Tentativas Permitidas</label>
										<div class="input-group">
											<span class="input-group-text bg-body-tertiary"><i class="bi bi-123"></i></span>
											<input type="number" class="form-control" name="max_login_attempts" min="2" max="20" value="<?= (int)($sec['max_login_attempts'] ?? 5) ?>" required>
										</div>
										<small class="text-muted">Padrão militar recomendado: 5 tentativas.</small>
									</div>

									<div class="mb-2">
										<label class="form-label fw-semibold small">Tempo de Bloqueio Temporário (Minutos)</label>
										<div class="input-group">
											<span class="input-group-text bg-body-tertiary"><i class="bi bi-clock-history"></i></span>
											<input type="number" class="form-control" name="lockout_time_minutes" min="1" max="1440" value="<?= (int)($sec['lockout_time_minutes'] ?? 15) ?>" required>
											<span class="input-group-text bg-body-tertiary">minutos</span>
										</div>
										<small class="text-muted">Tempo em que o agressor fica impedido de efetuar login.</small>
									</div>
								</div>
							</div>

							<div class="col-12 col-md-6">
								<div class="card h-100 border border-secondary-subtle rounded-3 p-3">
									<div class="d-flex justify-content-between align-items-start mb-3">
										<div>
											<h6 class="fw-bold mb-1">Armadilha Honeypot Anti-Robô</h6>
											<small class="text-body-secondary">Insere campos invisíveis que enganam bots e scripts automatizados.</small>
										</div>
										<div class="form-check form-switch fs-4 mb-0">
											<input class="form-check-input sec-toggle" type="checkbox" role="switch" data-chave="honeypot_protection" <?= !empty($sec['honeypot_protection']) ? 'checked' : '' ?>>
										</div>
									</div>

									<div class="alert alert-warning-subtle text-warning-emphasis border-warning-subtle small p-3 rounded-3 mb-0">
										<i class="bi bi-shield-exclamation me-1"></i> <strong>Como funciona:</strong> Usuários legítimos nunca vêem nem preenchem este campo oculto. Bots preenchem automaticamente todos os inputs de formulários, sendo desmascarados e banidos instantaneamente.
									</div>
								</div>
							</div>

							<div class="col-12 text-end">
								<button type="submit" class="btn btn-primary px-4 py-2 rounded-3 fw-bold shadow-sm">
									<i class="bi bi-check-lg me-1"></i> Salvar Parâmetros de Login
								</button>
							</div>
						</form>
					</div>

					<!-- ABA 3: HEADERS & VAZAMENTO -->
					<div class="tab-pane fade" id="tab-headers" role="tabpanel">
						<h5 class="fw-bold mb-3 d-flex align-items-center gap-2">
							<i class="bi bi-file-earmark-lock text-info"></i> Cabeçalhos HTTP de Segurança & Proteção contra Vazamento
						</h5>
						<p class="text-body-secondary small mb-4">
							Instrui os navegadores e firewalls externos a aplicar isolamento estrito e ocultar assinaturas do servidor.
						</p>

						<div class="row g-3">
							<!-- Security Headers -->
							<div class="col-12 col-md-6">
								<div class="card h-100 border border-secondary-subtle rounded-3 p-3">
									<div class="d-flex justify-content-between align-items-start mb-2">
										<div class="fw-bold text-body">Headers de Alta Segurança (HSTS, NoSniff, X-Frame)</div>
										<div class="form-check form-switch fs-4 mb-0">
											<input class="form-check-input sec-toggle" type="checkbox" role="switch" data-chave="enable_security_headers" <?= !empty($sec['enable_security_headers']) ? 'checked' : '' ?>>
										</div>
									</div>
									<small class="text-body-secondary d-block mb-3">
										Aplica <code>Strict-Transport-Security</code>, <code>X-Frame-Options: SAMEORIGIN</code> (previne clickjacking), <code>X-Content-Type-Options: nosniff</code> (previne MIME sniffing) e <code>Referrer-Policy</code>.
									</small>
									<span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1">
										Conformidade OWASP Top 10
									</span>
								</div>
							</div>

							<!-- Ocultar Versão do PHP -->
							<div class="col-12 col-md-6">
								<div class="card h-100 border border-secondary-subtle rounded-3 p-3">
									<div class="d-flex justify-content-between align-items-start mb-2">
										<div class="fw-bold text-body">Ocultação da Versão do PHP e Servidor</div>
										<div class="form-check form-switch fs-4 mb-0">
											<input class="form-check-input sec-toggle" type="checkbox" role="switch" data-chave="hide_php_version" <?= !empty($sec['hide_php_version']) ? 'checked' : '' ?>>
										</div>
									</div>
									<small class="text-body-secondary d-block mb-3">
										Remove o cabeçalho <code>X-Powered-By: PHP/8.4</code> e ofusca assinaturas do servidor para impedir que atacantes descubram versões exatas de software.
									</small>
									<span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">
										Prevenção de Fingerprinting
									</span>
								</div>
							</div>
						</div>
					</div>

					<!-- ABA 4: GESTÃO DE IPS -->
					<div class="tab-pane fade" id="tab-ips" role="tabpanel">
						<h5 class="fw-bold mb-3 d-flex align-items-center gap-2">
							<i class="bi bi-shield-shaded text-danger"></i> Controle de Acesso por Endereço IP
						</h5>

						<form id="form-ip-lists" class="row g-3 mb-4">
							<input type="hidden" name="action" value="save_settings">
							<input type="hidden" name="max_login_attempts" value="<?= (int)($sec['max_login_attempts'] ?? 5) ?>">
							<input type="hidden" name="lockout_time_minutes" value="<?= (int)($sec['lockout_time_minutes'] ?? 15) ?>">

							<div class="col-12 col-md-6">
								<div class="card border border-secondary-subtle rounded-3 p-3 h-100">
									<label class="form-label fw-bold text-success d-flex align-items-center gap-2">
										<i class="bi bi-check-circle-fill"></i> Lista Branca (Whitelist)
									</label>
									<textarea class="form-control font-monospace small" name="ip_whitelist" rows="4" placeholder="127.0.0.1&#10;192.168.1.100"><?= htmlspecialchars($sec['ip_whitelist'] ?? '') ?></textarea>
									<small class="text-body-secondary mt-1">IPs confiáveis com trânsito livre pelo firewall (separados por vírgula ou linha).</small>
								</div>
							</div>

							<div class="col-12 col-md-6">
								<div class="card border border-secondary-subtle rounded-3 p-3 h-100">
									<label class="form-label fw-bold text-danger d-flex align-items-center gap-2">
										<i class="bi bi-slash-circle-fill"></i> Lista Negra Permanente (Blacklist)
									</label>
									<textarea class="form-control font-monospace small" name="ip_blacklist" rows="4" placeholder="185.220.101.5&#10;45.146.164.110"><?= htmlspecialchars($sec['ip_blacklist'] ?? '') ?></textarea>
									<small class="text-body-secondary mt-1">IPs bloqueados imediatamente em qualquer página do sistema.</small>
								</div>
							</div>

							<div class="col-12 text-end">
								<button type="submit" class="btn btn-primary px-4 py-2 rounded-3 fw-bold shadow-sm">
									<i class="bi bi-check-lg me-1"></i> Atualizar Listas de IP
								</button>
							</div>
						</form>

						<!-- Tabela de IPs em Quarentena / Bloqueio Ativo -->
						<div class="border rounded-3 p-3 bg-body">
							<h6 class="fw-bold mb-3 d-flex align-items-center gap-2">
								<i class="bi bi-cone-striped text-warning"></i> IPs Bloqueados Temporariamente no Momento (Quarentena Ativa)
							</h6>

							<?php if (empty($blockedIpsList)): ?>
								<div class="alert alert-light border small text-body-secondary mb-0">
									<i class="bi bi-info-circle me-1"></i> Nenhum endereço IP está em quarentena no momento.
								</div>
							<?php else: ?>
								<div class="table-responsive">
									<table class="table table-sm table-hover align-middle mb-0">
										<thead>
											<tr class="table-light">
												<th>Endereço IP</th>
												<th>Motivo do Bloqueio</th>
												<th>Bloqueado Até</th>
												<th>Cadastrado em</th>
												<th class="text-end">Ações</th>
											</tr>
										</thead>
										<tbody>
											<?php foreach ($blockedIpsList as $bIp): ?>
											<tr>
												<td class="font-monospace fw-bold text-danger"><?= htmlspecialchars($bIp['ip_address']); ?></td>
												<td><small><?= htmlspecialchars($bIp['reason']); ?></small></td>
												<td><small class="badge bg-warning-subtle text-warning"><?= $bIp['blocked_until'] ? date('d/m/Y H:i', strtotime($bIp['blocked_until'])) : 'Permanente'; ?></small></td>
												<td><small class="text-muted"><?= date('d/m/Y H:i', strtotime($bIp['created_at'])); ?></small></td>
												<td class="text-end">
													<button type="button" class="btn btn-xs btn-outline-success btn-unblock-ip rounded-pill px-3" data-ip="<?= htmlspecialchars($bIp['ip_address']); ?>">
														<i class="bi bi-unlock-fill me-1"></i> Desbloquear
													</button>
												</td>
											</tr>
											<?php endforeach; ?>
										</tbody>
									</table>
								</div>
							<?php endif; ?>
						</div>
					</div>

					<!-- ABA 5: LOGS DE AUDITORIA EM TEMPO REAL -->
					<div class="tab-pane fade" id="tab-logs" role="tabpanel">
						<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
							<div>
								<h5 class="fw-bold mb-0 d-flex align-items-center gap-2">
									<i class="bi bi-journal-check text-success"></i> Registro de Auditoria & Incidentes em Tempo Real
								</h5>
								<small class="text-body-secondary">Exibindo os últimos 50 eventos registrados pelo firewall</small>
							</div>
							<div class="d-flex gap-2">
								<button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-3" id="btn-clear-logs">
									<i class="bi bi-trash3-fill me-1"></i> Limpar Registros
								</button>
							</div>
						</div>

						<?php if (empty($logsList)): ?>
							<div class="alert alert-success-subtle text-success-emphasis border border-success-subtle rounded-3 p-4 text-center">
								<i class="bi bi-shield-check fs-1 d-block mb-2 text-success"></i>
								<h6 class="fw-bold">Nenhum Incidente Registrado</h6>
								<p class="small mb-0">Seu sistema está operando perfeitamente e sem ocorrências de ataques recentes.</p>
							</div>
						<?php else: ?>
							<div class="table-responsive">
								<table class="table table-hover table-sm align-middle border rounded-3 overflow-hidden">
									<thead class="table-light">
										<tr>
											<th>Data / Horário</th>
											<th>IP de Origem</th>
											<th>Tipo de Evento</th>
											<th>Severidade</th>
											<th>Endpoint / URI</th>
											<th>Amostra do Payload</th>
											<th>Ação</th>
										</tr>
									</thead>
									<tbody>
										<?php foreach ($logsList as $log): 
											$badgeClass = 'bg-secondary';
											if ($log['threat_level'] === 'CRITICA') $badgeClass = 'bg-danger text-white';
											elseif ($log['threat_level'] === 'ALTA') $badgeClass = 'bg-warning text-dark';
											elseif ($log['threat_level'] === 'MEDIA') $badgeClass = 'bg-info text-dark';
											elseif ($log['threat_level'] === 'BAIXA') $badgeClass = 'bg-success text-white';
										?>
										<tr>
											<td class="small text-nowrap"><?= date('d/m/Y H:i:s', strtotime($log['created_at'])); ?></td>
											<td class="font-monospace small fw-bold"><?= htmlspecialchars($log['ip_address']); ?></td>
											<td>
												<span class="badge bg-dark-subtle text-body border font-monospace" style="font-size: 0.72rem;">
													<?= htmlspecialchars($log['event_type']); ?>
												</span>
											</td>
											<td>
												<span class="badge <?= $badgeClass; ?>" style="font-size: 0.72rem;">
													<?= htmlspecialchars($log['threat_level']); ?>
												</span>
											</td>
											<td class="small text-truncate" style="max-width: 180px;" title="<?= htmlspecialchars($log['request_uri'] ?? ''); ?>">
												<code><?= htmlspecialchars($log['request_method'] ?? 'GET'); ?></code> <?= htmlspecialchars($log['request_uri'] ?? ''); ?>
											</td>
											<td class="small text-truncate font-monospace" style="max-width: 220px;" title="<?= htmlspecialchars($log['payload_sample'] ?? ''); ?>">
												<?= htmlspecialchars($log['payload_sample'] ?? '-'); ?>
											</td>
											<td>
												<?php if ($log['action_taken'] === 'BLOCKED'): ?>
													<span class="badge bg-danger-subtle text-danger border border-danger-subtle">
														<i class="bi bi-x-circle me-1"></i> BLOQUEADO
													</span>
												<?php else: ?>
													<span class="badge bg-secondary-subtle text-secondary border">
														<?= htmlspecialchars($log['action_taken']); ?>
													</span>
												<?php endif; ?>
											</td>
										</tr>
										<?php endforeach; ?>
									</tbody>
								</table>
							</div>
						<?php endif; ?>
					</div>

				</div>
			</div>
		</div>

	</div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {

	// 1. Alternador Instantâneo via AJAX para os Switches de Segurança
	document.querySelectorAll('.sec-toggle').forEach(function(toggle) {
		toggle.addEventListener('change', function() {
			const chave = this.dataset.chave;
			const status = this.checked ? 1 : 0;
			const elem = this;

			fetch('seguranca-ajax.php', {
				method: 'POST',
				headers: {
					'Content-Type': 'application/x-www-form-urlencoded',
					'X-Requested-With': 'XMLHttpRequest'
				},
				body: new URLSearchParams({
					action: 'update_toggle',
					chave: chave,
					status: status
				})
			})
			.then(res => res.json())
			.then(data => {
				if (data.success) {
					if (window.notificar) {
						window.notificar('success', data.message, 'Segurança Atualizada');
					}
				} else {
					elem.checked = !elem.checked;
					if (window.notificar) {
						window.notificar('error', data.message || 'Erro ao alterar parâmetro.', 'Erro');
					}
				}
			})
			.catch(err => {
				elem.checked = !elem.checked;
				if (window.notificar) {
					window.notificar('error', 'Falha na comunicação assíncrona com o servidor.', 'Erro de Conexão');
				}
			});
		});
	});

	// 2. Submissão de Formulários de Parâmetros de Login e Listas de IP
	['form-security-settings', 'form-ip-lists'].forEach(function(formId) {
		const formElem = document.getElementById(formId);
		if (!formElem) return;

		formElem.addEventListener('submit', function(e) {
			e.preventDefault();
			const formData = new FormData(formElem);

			fetch('seguranca-ajax.php', {
				method: 'POST',
				headers: {
					'X-Requested-With': 'XMLHttpRequest'
				},
				body: formData
			})
			.then(res => res.json())
			.then(data => {
				if (data.success) {
					if (typeof Swal !== 'undefined') {
						Swal.fire({
							icon: 'success',
							title: 'Sucesso!',
							text: data.message,
							confirmButtonColor: '#0a6b74',
							confirmButtonText: 'OK'
						});
					} else if (window.notificar) {
						window.notificar('success', data.message, 'Sucesso');
					}
				} else {
					if (typeof Swal !== 'undefined') {
						Swal.fire({
							icon: 'error',
							title: 'Atenção',
							text: data.message || 'Erro ao salvar.',
							confirmButtonColor: '#d33'
						});
					}
				}
			})
			.catch(err => {
				if (window.notificar) {
					window.notificar('error', 'Falha na conexão com o servidor.', 'Erro');
				}
			});
		});
	});

	// 3. Desbloquear IP Dinamicamente com Confirmação SweetAlert2
	document.querySelectorAll('.btn-unblock-ip').forEach(function(btn) {
		btn.addEventListener('click', function() {
			const ip = this.dataset.ip;
			const row = this.closest('tr');

			if (typeof Swal !== 'undefined') {
				Swal.fire({
					title: 'Desbloquear IP?',
					text: `Deseja realmente remover o bloqueio para o IP ${ip}?`,
					icon: 'question',
					showCancelButton: true,
					confirmButtonColor: '#0a6b74',
					cancelButtonColor: '#6c757d',
					confirmButtonText: 'Sim, desbloquear',
					cancelButtonText: 'Cancelar'
				}).then((result) => {
					if (result.isConfirmed) {
						executarDesbloqueio(ip, row);
					}
				});
			} else {
				executarDesbloqueio(ip, row);
			}
		});
	});

	function executarDesbloqueio(ip, row) {
		fetch('seguranca-ajax.php', {
			method: 'POST',
			headers: {
				'Content-Type': 'application/x-www-form-urlencoded',
				'X-Requested-With': 'XMLHttpRequest'
			},
			body: new URLSearchParams({
				action: 'unblock_ip',
				ip: ip
			})
		})
		.then(res => res.json())
		.then(data => {
			if (data.success) {
				if (window.notificar) {
					window.notificar('success', data.message, 'IP Desbloqueado');
				}
				if (row) {
					row.style.transition = 'all 0.3s ease';
					row.style.opacity = '0';
					setTimeout(() => row.remove(), 300);
				}
			} else {
				if (window.notificar) {
					window.notificar('error', data.message, 'Erro');
				}
			}
		});
	}

	// 4. Limpar Logs de Auditoria com SweetAlert2
	const btnClearLogs = document.getElementById('btn-clear-logs');
	if (btnClearLogs) {
		btnClearLogs.addEventListener('click', function() {
			if (typeof Swal !== 'undefined') {
				Swal.fire({
					title: 'Limpar todos os registros de auditoria?',
					text: 'Esta ação removerá permanentemente o histórico de ataques e incidentes registrados.',
					icon: 'warning',
					showCancelButton: true,
					confirmButtonColor: '#d33',
					cancelButtonColor: '#6c757d',
					confirmButtonText: 'Sim, limpar tudo',
					cancelButtonText: 'Cancelar'
				}).then((result) => {
					if (result.isConfirmed) {
						fetch('seguranca-ajax.php', {
							method: 'POST',
							headers: {
								'Content-Type': 'application/x-www-form-urlencoded',
								'X-Requested-With': 'XMLHttpRequest'
							},
							body: new URLSearchParams({ action: 'clear_logs' })
						})
						.then(res => res.json())
						.then(data => {
							if (data.success) {
								Swal.fire('Limpo!', data.message, 'success').then(() => {
									location.reload();
								});
							}
						});
					}
				});
			}
		});
	}

});
</script>

<?php require_once('footer.php'); ?>
