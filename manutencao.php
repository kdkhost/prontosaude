<?php
// Configurações e cabeçalhos HTTP
http_response_code(503);
header('Retry-After: 3600'); // 1 hora de previsão para motores de busca

require_once(__DIR__ . '/admin/config.php');

// Obter dados da clínica
$statement = $pdo->prepare("SELECT * FROM tbl_settings WHERE id=1");
$statement->execute();
$row = $statement->fetch(PDO::FETCH_ASSOC) ?: [];
$empresa = $row['empresa'] ?? 'Pronto Saúde';
$logo = $row['logo'] ?? '';
$favicon = $row['favicon'] ?? 'favicon.png';
$telefone = $row['contact_phone'] ?? '(21) 98132-5441';
$email = $row['contact_email'] ?? 'contato@prontosaude.kdkhost.com.br';
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Em Manutenção Programada | <?= htmlspecialchars($empresa); ?></title>
	
	<!-- Favicon -->
	<link href="<?= BASE_URL; ?>assets/uploads/<?= htmlspecialchars($favicon); ?>" rel="shortcut icon" type="image/png">
	
	<!-- Google Fonts -->
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

	<style>
		:root {
			--primary: #0a6b74;
			--primary-dark: #075259;
			--primary-light: #e6f3f4;
			--accent: #10b981;
			--text-dark: #0f172a;
			--text-muted: #64748b;
			--bg-grad: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
		}
		* {
			box-sizing: border-box;
			margin: 0;
			padding: 0;
		}
		body {
			font-family: 'Plus Jakarta Sans', sans-serif;
			background: var(--bg-grad);
			color: var(--text-dark);
			min-height: 100vh;
			display: flex;
			flex-direction: column;
			align-items: center;
			justify-content: center;
			padding: 1.5rem;
			position: relative;
			overflow-x: hidden;
		}
		/* Elementos decorativos de fundo */
		body::before, body::after {
			content: '';
			position: absolute;
			border-radius: 50%;
			filter: blur(80px);
			z-index: 0;
			opacity: 0.45;
			pointer-events: none;
		}
		body::before {
			width: 380px;
			height: 380px;
			background: #14b8a6;
			top: -100px;
			left: -100px;
		}
		body::after {
			width: 320px;
			height: 320px;
			background: #0ea5e9;
			bottom: -80px;
			right: -80px;
		}
		.card-maintenance {
			position: relative;
			z-index: 1;
			background: rgba(255, 255, 255, 0.95);
			backdrop-filter: blur(16px);
			border: 1px solid rgba(255, 255, 255, 0.6);
			border-radius: 1.5rem;
			box-shadow: 0 25px 50px -12px rgba(10, 107, 116, 0.15), 0 0 0 1px rgba(10, 107, 116, 0.05);
			max-width: 680px;
			width: 100%;
			padding: 3rem 2.5rem;
			text-align: center;
		}
		.logo-brand {
			max-height: 52px;
			margin-bottom: 2rem;
		}
		.badge-status {
			display: inline-flex;
			align-items: center;
			gap: 0.5rem;
			padding: 0.5rem 1.25rem;
			border-radius: 9999px;
			background: #fef3c7;
			color: #92400e;
			font-weight: 700;
			font-size: 0.85rem;
			text-transform: uppercase;
			letter-spacing: 0.05em;
			margin-bottom: 1.5rem;
		}
		.badge-status .pulse-dot {
			width: 10px;
			height: 10px;
			border-radius: 50%;
			background: #f59e0b;
			box-shadow: 0 0 0 0 rgba(245, 158, 11, 0.7);
			animation: pulse 1.8s infinite;
		}
		@keyframes pulse {
			0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(245, 158, 11, 0.7); }
			70% { transform: scale(1); box-shadow: 0 0 0 10px rgba(245, 158, 11, 0); }
			100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(245, 158, 11, 0); }
		}
		h1 {
			font-size: 2.15rem;
			font-weight: 800;
			line-height: 1.25;
			color: var(--primary-dark);
			margin-bottom: 1rem;
			letter-spacing: -0.02em;
		}
		p.lead {
			color: var(--text-muted);
			font-size: 1.05rem;
			line-height: 1.6;
			margin-bottom: 2rem;
		}
		.emergency-box {
			background: var(--primary-light);
			border: 1px solid rgba(10, 107, 116, 0.2);
			border-radius: 1rem;
			padding: 1.5rem;
			margin-bottom: 2rem;
			text-align: left;
		}
		.emergency-box h3 {
			font-size: 1rem;
			font-weight: 700;
			color: var(--primary);
			margin-bottom: 0.75rem;
			display: flex;
			align-items: center;
			gap: 0.5rem;
		}
		.channels-grid {
			display: grid;
			grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
			gap: 1rem;
			margin-top: 1rem;
		}
		.channel-btn {
			display: flex;
			align-items: center;
			justify-content: center;
			gap: 0.6rem;
			padding: 0.75rem 1rem;
			border-radius: 0.75rem;
			font-weight: 600;
			font-size: 0.9rem;
			text-decoration: none;
			transition: all 0.2s ease;
		}
		.channel-whatsapp {
			background: #16a34a;
			color: #ffffff;
		}
		.channel-whatsapp:hover {
			background: #15803d;
			transform: translateY(-2px);
		}
		.channel-phone {
			background: #0284c7;
			color: #ffffff;
		}
		.channel-phone:hover {
			background: #0369a1;
			transform: translateY(-2px);
		}
		.channel-email {
			background: #475569;
			color: #ffffff;
		}
		.channel-email:hover {
			background: #334155;
			transform: translateY(-2px);
		}
		.footer-note {
			color: var(--text-muted);
			font-size: 0.85rem;
			margin-top: 1.5rem;
		}
	</style>
</head>

<body>
	<div class="card-maintenance">
		<!-- Logotipo -->
		<?php if(!empty($logo)): ?>
			<img src="<?= BASE_URL; ?>assets/uploads/<?= htmlspecialchars($logo); ?>" alt="<?= htmlspecialchars($empresa); ?>" class="logo-brand">
		<?php else: ?>
			<h2 style="color: var(--primary); font-weight:800; margin-bottom: 1.5rem;">
				<i class="bi bi-hospital me-2"></i><?= htmlspecialchars($empresa); ?>
			</h2>
		<?php endif; ?>

		<!-- Badge de Status -->
		<div class="badge-status">
			<span class="pulse-dot"></span>
			<span>Manutenção Técnica Programada</span>
		</div>

		<!-- Título e Mensagem -->
		<h1>Estamos aprimorando nossos serviços para você!</h1>
		<p class="lead">
			Nosso portal está passando por uma atualização técnica de rotina para otimização de velocidade, segurança e integração de novos recursos de saúde. Retornaremos em instantes!
		</p>

		<!-- Canais de Atendimento de Emergência -->
		<div class="emergency-box">
			<h3><i class="bi bi-headset"></i> Precisa de atendimento ou suporte urgente?</h3>
			<p style="font-size: 0.875rem; color: #334155; margin-bottom: 0;">
				Nossa equipe de plantão continua ativa através dos canais diretos abaixo:
			</p>

			<div class="channels-grid">
				<a href="https://wa.me/5521981325441?text=Ol%C3%A1%2C%20estou%20entrando%20em%20contato%20atrav%C3%A9s%20do%20portal%20Pronto%20Sa%C3%BAde" target="_blank" class="channel-btn channel-whatsapp">
					<i class="bi bi-whatsapp"></i> WhatsApp
				</a>
				<a href="tel:<?= preg_replace('/\D/', '', $telefone); ?>" class="channel-btn channel-phone">
					<i class="bi bi-telephone-fill"></i> Ligar Agora
				</a>
				<a href="mailto:<?= htmlspecialchars($email); ?>" class="channel-btn channel-email">
					<i class="bi bi-envelope-fill"></i> E-mail
				</a>
			</div>
		</div>

		<!-- Horário de São Paulo -->
		<div class="footer-note">
			<i class="bi bi-clock-history me-1"></i> Fuso Horário de São Paulo: <?= date('d/m/Y H:i:s'); ?> &bull; &copy; <?= date('Y'); ?> <?= htmlspecialchars($empresa); ?>
		</div>
	</div>
</body>
</html>
