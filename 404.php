<?php
http_response_code(404);
require_once(__DIR__ . '/admin/config.php');

$statement = $pdo->prepare("SELECT * FROM tbl_settings WHERE id=1");
$statement->execute();
$row = $statement->fetch(PDO::FETCH_ASSOC) ?: [];
$empresa = $row['empresa'] ?? 'Pronto Saúde';
$logo = $row['logo'] ?? '';
$favicon = $row['favicon'] ?? 'favicon.png';
$telefone = $row['contact_phone'] ?? '(21) 98132-5441';
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>404 - Página Não Encontrada | <?= htmlspecialchars($empresa); ?></title>
	
	<!-- Favicon -->
	<link href="<?= BASE_URL; ?>assets/uploads/<?= htmlspecialchars($favicon); ?>" rel="shortcut icon" type="image/png">
	
	<!-- Fontes e Ícones -->
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

	<style>
		:root {
			--primary: #0a6b74;
			--primary-dark: #075259;
			--primary-light: #e6f3f4;
			--text-dark: #0f172a;
			--text-muted: #64748b;
			--bg-grad: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
		}
		* { box-sizing: border-box; margin: 0; padding: 0; }
		body {
			font-family: 'Plus Jakarta Sans', sans-serif;
			background: var(--bg-grad);
			color: var(--text-dark);
			min-height: 100vh;
			display: flex;
			align-items: center;
			justify-content: center;
			padding: 1.5rem;
		}
		.error-card {
			background: rgba(255, 255, 255, 0.95);
			backdrop-filter: blur(12px);
			border: 1px solid rgba(255, 255, 255, 0.6);
			border-radius: 1.5rem;
			box-shadow: 0 20px 40px rgba(10, 107, 116, 0.12);
			max-width: 600px;
			width: 100%;
			padding: 3rem 2rem;
			text-align: center;
		}
		.error-code {
			font-size: 6rem;
			font-weight: 900;
			line-height: 1;
			background: linear-gradient(135deg, #0a6b74, #06b6d4);
			-webkit-background-clip: text;
			-webkit-text-fill-color: transparent;
			margin-bottom: 0.5rem;
			letter-spacing: -0.05em;
		}
		.logo-brand {
			max-height: 46px;
			margin-bottom: 1.5rem;
		}
		h1 {
			font-size: 1.75rem;
			font-weight: 800;
			color: var(--text-dark);
			margin-bottom: 0.75rem;
		}
		p {
			color: var(--text-muted);
			font-size: 1rem;
			line-height: 1.6;
			margin-bottom: 2rem;
		}
		.btn-group {
			display: flex;
			gap: 1rem;
			justify-content: center;
			flex-wrap: wrap;
		}
		.btn {
			padding: 0.75rem 1.5rem;
			border-radius: 0.75rem;
			font-weight: 700;
			font-size: 0.95rem;
			text-decoration: none;
			display: inline-flex;
			align-items: center;
			gap: 0.5rem;
			transition: all 0.2s ease;
		}
		.btn-primary {
			background: var(--primary);
			color: #fff;
			box-shadow: 0 4px 12px rgba(10, 107, 116, 0.3);
		}
		.btn-primary:hover {
			background: var(--primary-dark);
			transform: translateY(-2px);
		}
		.btn-secondary {
			background: #e2e8f0;
			color: #334155;
		}
		.btn-secondary:hover {
			background: #cbd5e1;
			transform: translateY(-2px);
		}
	</style>
</head>

<body>
	<div class="error-card">
		<?php if(!empty($logo)): ?>
			<img src="<?= BASE_URL; ?>assets/uploads/<?= htmlspecialchars($logo); ?>" alt="<?= htmlspecialchars($empresa); ?>" class="logo-brand">
		<?php else: ?>
			<h2 style="color: var(--primary); font-weight:800; margin-bottom: 1rem;">
				<i class="bi bi-hospital me-2"></i><?= htmlspecialchars($empresa); ?>
			</h2>
		<?php endif; ?>

		<div class="error-code">404</div>
		<h1>Página Não Encontrada</h1>
		<p>
			O endereço que você tentou acessar não existe, foi alterado ou está temporariamente indisponível. Utilize os atalhos abaixo para continuar sua navegação.
		</p>

		<div class="btn-group">
			<a href="<?= BASE_URL; ?>" class="btn btn-primary">
				<i class="bi bi-house-door-fill"></i> Voltar para o Início
			</a>
			<a href="https://wa.me/5521981325441" target="_blank" class="btn btn-secondary">
				<i class="bi bi-whatsapp text-success"></i> Suporte WhatsApp
			</a>
		</div>
	</div>
</body>
</html>
