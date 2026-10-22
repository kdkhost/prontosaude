<?php
ob_start();
session_start();
include("config.php");
include("functions.php");
include("../assets/PHPmailer/email.php");

$error_message = '';
$success_message = '';
$GET  = filter_input_array(INPUT_GET, FILTER_DEFAULT);
$POST = filter_input_array(INPUT_POST, FILTER_DEFAULT);

// Obtendo dados de configuração
$statement = $pdo->prepare("SELECT * FROM tbl_settings WHERE id=1");
$statement->execute();
$config = $statement->fetch(PDO::FETCH_ASSOC) ?: [];
$logo = $config['logo'] ?? '';
$favicon = $config['favicon'] ?? 'favicon.png';
$empresa = $config['empresa'] ?? 'Pronto Saúde';
$footer_copyright = $config['footer_copyright'] ?? '';

$ValidaToken = false;
$email_decoded = '';
if(!empty($GET['tk']) && !empty($GET['e'])) {
	$email_decoded = base64_decode($GET['e']);
	$statement = $pdo->prepare("SELECT * FROM tbl_user WHERE email=? AND token=?");
	$statement->execute(array($email_decoded, $GET['tk']));
	if($statement->rowCount()) {
		$ValidaToken = true;
	}
}

if(isset($POST['send'])) {
	if(empty($POST['email'])) {
		$error_message = 'O e-mail não pode estar vazio.';
	} else {
		$email = strip_tags(trim($POST['email']));
		$statement = $pdo->prepare("SELECT * FROM tbl_user WHERE email=? AND status=?");
		$statement->execute(array($email, 'Active'));
		$total = $statement->rowCount();
		$result = $statement->fetchAll(PDO::FETCH_ASSOC);

		if($total == 0) {
			$error_message = 'Endereço de e-mail não corresponde a nenhum usuário ativo cadastrado.';
		} else {
			$user = $result[0];
			$token = hash('sha512', $email . time());
			$email_encoded = base64_encode($email);
			$statement = $pdo->prepare("UPDATE tbl_user SET token=? WHERE id=?");
			$statement->execute(array($token, $user['id']));

			$linkrecover = BASE_URL . "admin/recover.php?tk={$token}&e={$email_encoded}";
			$html = "<h3>Recuperação de Senha - " . htmlspecialchars($empresa) . "</h3>";
			$html .= "<p>Foi solicitada a redefinição de sua senha de acesso ao painel administrativo.</p>";
			$html .= "<p>Para criar uma nova senha, clique no link abaixo ou copie e cole no seu navegador:</p>";
			$html .= "<p><a href='{$linkrecover}' style='display:inline-block;padding:10px 20px;background-color:#0a6b74;color:#ffffff;text-decoration:none;border-radius:6px;font-weight:bold;'>Redefinir Minha Senha</a></p>";
			$html .= "<p><small>Link direto: <a href='{$linkrecover}'>{$linkrecover}</a></small></p>";
			$html .= "<p>Se você não realizou esta solicitação, desconsidere esta mensagem com segurança.</p>";

			$email_send = SendMail($user['email'], $user['full_name'], "Recuperação de Senha - {$empresa}", $html);
			if($email_send) {
				$success_message = "Instruções e link para redefinição foram enviados para seu e-mail!";
			} else {
				$error_message = "Falha ao enviar e-mail. Verifique a configuração de SMTP do servidor.";
			}
		}
	}
}

if(isset($POST['recover'])) {
	if(empty($POST['password']) || empty($POST['re_password'])) {
		$error_message = 'Por favor, preencha todos os campos obrigatórios.';
	} else {
		$password = strip_tags(trim($POST['password']));
		$repassword = strip_tags(trim($POST['re_password']));
		$email = strip_tags(trim($POST['email']));

		if($password !== $repassword) {
			$error_message = 'As senhas não coincidem. Por favor, tente novamente.';
		} else {
			$statement = $pdo->prepare("SELECT * FROM tbl_user WHERE email=?");
			$statement->execute(array($email));
			$total = $statement->rowCount();
			$result = $statement->fetchAll(PDO::FETCH_ASSOC);

			if($total == 0) {
				$error_message = 'Endereço de e-mail não encontrado ou inválido.';
			} else {
				$user = $result[0];
				$statement = $pdo->prepare("UPDATE tbl_user SET password=?, token='' WHERE id=?");
				$statement->execute(array(md5($password), $user['id']));

				$html = "<h3>Senha Redefinida com Sucesso</h3>";
				$html .= "<p>Olá " . htmlspecialchars($user['full_name']) . ",</p>";
				$html .= "<p>A sua senha de acesso ao painel " . htmlspecialchars($empresa) . " foi alterada com sucesso.</p>";
				SendMail($user['email'], $user['full_name'], "Senha Redefinida - {$empresa}", $html);

				header("location: login.php?recovered=1");
				exit;
			}
		}
	}
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Recuperar Senha - <?= htmlspecialchars($empresa) ?></title>

	<!-- Favicon -->
	<link href="<?= BASE_URL; ?>assets/uploads/<?= htmlspecialchars($favicon); ?>" rel="shortcut icon" type="image/png">

	<!-- Fontes e Ícones -->
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fontsource/source-sans-3@5.0.12/index.css">
	<link rel="stylesheet" href="css/bootstrap-icons.min.css">

	<!-- AdminLTE 4 e Bootstrap 5.3 -->
	<link rel="stylesheet" href="css/adminlte4.min.css">
	<link rel="stylesheet" href="css/toastr.min.css">

	<style>
		body {
			min-height: 100vh;
			display: flex;
			align-items: center;
			justify-content: center;
			background: linear-gradient(135deg, rgba(10, 107, 116, 0.85) 0%, rgba(7, 82, 89, 0.95) 100%), url('../assets/images/login/teletriagem.jpg') no-repeat center center fixed;
			background-size: cover;
			font-family: 'Source Sans 3', sans-serif;
			padding: 1.5rem;
		}
		.login-card-modern {
			background: rgba(255, 255, 255, 0.95);
			backdrop-filter: blur(12px);
			border: 1px solid rgba(255, 255, 255, 0.3);
			border-radius: 1.25rem;
			box-shadow: 0 20px 40px rgba(0, 0, 0, 0.25);
			max-width: 440px;
			width: 100%;
			overflow: hidden;
			transition: all 0.3s ease;
		}
		.login-header-logo {
			background: rgba(10, 107, 116, 0.05);
			padding: 2rem 1.5rem 1.25rem 1.5rem;
			text-align: center;
			border-bottom: 1px solid rgba(0, 0, 0, 0.05);
		}
		.login-header-logo img {
			max-height: 48px;
			max-width: 240px;
			width: auto;
		}
		.form-floating > .form-control:focus ~ label,
		.form-floating > .form-control:not(:placeholder-shown) ~ label {
			color: #0a6b74;
		}
		.form-control:focus {
			border-color: #0a6b74;
			box-shadow: 0 0 0 0.25rem rgba(10, 107, 116, 0.15);
		}
		.btn-login-submit {
			background-color: #0a6b74;
			border-color: #0a6b74;
			padding: 0.75rem 1.5rem;
			font-weight: 700;
			letter-spacing: 0.03em;
			border-radius: 0.75rem;
			box-shadow: 0 4px 12px rgba(10, 107, 116, 0.3);
			transition: all 0.2s ease;
		}
		.btn-login-submit:hover {
			background-color: #075259;
			border-color: #075259;
			transform: translateY(-2px);
			box-shadow: 0 6px 16px rgba(10, 107, 116, 0.4);
		}
	</style>
</head>

<body>
	<div class="login-card-modern">
		<div class="login-header-logo">
			<?php if(!empty($logo)): ?>
				<img src="../assets/uploads/<?= htmlspecialchars($logo) ?>" alt="<?= htmlspecialchars($empresa) ?>">
			<?php else: ?>
				<h3 class="fw-bold text-primary mb-0"><i class="bi bi-hospital me-2"></i><?= htmlspecialchars($empresa) ?></h3>
			<?php endif; ?>
			<p class="text-body-secondary small mt-2 mb-0">Portal de Gestão Integrada • AdminLTE 4</p>
		</div>

		<div class="p-4 p-sm-5">
			<?php if($ValidaToken): ?>
				<div class="mb-4 text-center">
					<h4 class="fw-bold mb-1">Nova Senha</h4>
					<small class="text-body-secondary">Crie uma nova senha segura para sua conta</small>
				</div>

				<form method="post" action="">
					<input name="email" type="hidden" value="<?= htmlspecialchars($email_decoded); ?>">

					<div class="form-floating mb-3">
						<input type="password" class="form-control rounded-3" id="password" name="password" placeholder="Nova Senha" required autofocus autocomplete="new-password">
						<label for="password"><i class="bi bi-lock me-1"></i> Nova Senha</label>
					</div>

					<div class="form-floating mb-4">
						<input type="password" class="form-control rounded-3" id="re_password" name="re_password" placeholder="Confirmar Senha" required autocomplete="new-password">
						<label for="re_password"><i class="bi bi-shield-check me-1"></i> Confirmar Nova Senha</label>
					</div>

					<div class="d-grid mb-3">
						<button type="submit" name="recover" class="btn btn-primary btn-login-submit text-white">
							<i class="bi bi-check-circle me-1"></i> REDEFINIR SENHA
						</button>
					</div>

					<div class="text-center mt-3">
						<a href="login.php" class="text-decoration-none small text-body-secondary">
							<i class="bi bi-arrow-left me-1"></i> Voltar ao Login
						</a>
					</div>
				</form>
			<?php else: ?>
				<div class="mb-4 text-center">
					<h4 class="fw-bold mb-1">Recuperar Senha</h4>
					<small class="text-body-secondary">Informe seu e-mail cadastrado para enviarmos as instruções</small>
				</div>

				<form method="post" action="">
					<div class="form-floating mb-4">
						<input type="email" class="form-control rounded-3" id="email" name="email" placeholder="nome@exemplo.com" required autofocus>
						<label for="email"><i class="bi bi-envelope me-1"></i> Endereço de E-mail</label>
					</div>

					<div class="d-grid mb-3">
						<button type="submit" name="send" class="btn btn-primary btn-login-submit text-white">
							<i class="bi bi-send me-1"></i> ENVIAR INSTRUÇÕES
						</button>
					</div>

					<div class="text-center mt-3">
						<a href="login.php" class="text-decoration-none small text-body-secondary">
							<i class="bi bi-arrow-left me-1"></i> Lembrou a senha? Fazer Login
						</a>
					</div>
				</form>
			<?php endif; ?>
		</div>

		<div class="py-2 text-center bg-light border-top">
			<small class="text-body-secondary">&copy; <?= date('Y'); ?> <?= htmlspecialchars($empresa); ?> • PHP 8.4</small>
		</div>
	</div>

	<!-- Scripts -->
	<script src="js/jquery-2.2.4.min.js"></script>
	<script src="js/bootstrap5.bundle.min.js"></script>
	<script src="js/sweetalert2.all.min.js"></script>
	<script src="js/toastr.min.js"></script>
	<script src="js/pronto-global.js"></script>

	<?php if (!empty($error_message)): ?>
	<script>
		document.addEventListener('DOMContentLoaded', function() {
			if (typeof Swal !== 'undefined') {
				Swal.fire({
					icon: 'error',
					title: 'Atenção',
					text: '<?= addslashes($error_message) ?>',
					confirmButtonText: 'Entendido',
					confirmButtonColor: '#0A6B74',
					customClass: { confirmButton: 'btn btn-primary px-4' },
					buttonsStyling: false
				});
			}
		});
	</script>
	<?php endif; ?>

	<?php if (!empty($success_message)): ?>
	<script>
		document.addEventListener('DOMContentLoaded', function() {
			if (typeof Swal !== 'undefined') {
				Swal.fire({
					icon: 'success',
					title: 'Sucesso',
					text: '<?= addslashes($success_message) ?>',
					confirmButtonText: 'OK',
					confirmButtonColor: '#0A6B74',
					customClass: { confirmButton: 'btn btn-primary px-4' },
					buttonsStyling: false
				});
			}
		});
	</script>
	<?php endif; ?>
</body>
</html>