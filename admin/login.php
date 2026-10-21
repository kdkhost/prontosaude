<?php
ob_start();
session_start();
include("config.php");
$error_message = '';

if(isset($_POST['form1'])) {
    if(empty($_POST['email']) || empty($_POST['password'])) {
        $error_message = 'E-mail e/ou senha não podem estar vazios.';
    } else {
    	$statement = $pdo->prepare("SELECT * FROM tbl_user WHERE email=? AND status=?");
    	$statement->execute(array($_POST['email'],'Active'));
    	$total = $statement->rowCount();    
        $row = $statement->fetch(PDO::FETCH_ASSOC);    
        if($total == 0 || !$row) {
            $error_message = 'Endereço de e-mail não encontrado ou usuário inativo.';
        } else {       
            if($row['password'] !== md5($_POST['password'])) {
                $error_message = 'Senha incorreta. Verifique suas credenciais.';
            } else {       
				$_SESSION['user'] = $row;
                header("location: index.php");
                exit;
            }
        }
    }
}

// Configurações Básicas
$statement = $pdo->prepare("SELECT * FROM tbl_settings WHERE id=1");
$statement->execute();
$config = $statement->fetch(PDO::FETCH_ASSOC) ?: [];
$favicon = $config['favicon'] ?? 'favicon.png';
$empresa = $config['empresa'] ?? 'Pronto Saúde';
$logo = $config['logo'] ?? '';
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Acesso Restrito - <?= htmlspecialchars($empresa) ?></title>

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
			<div class="mb-4 text-center">
				<h4 class="fw-bold mb-1">Entrar no Sistema</h4>
				<small class="text-body-secondary">Informe seu e-mail e senha cadastrados</small>
			</div>

			<form method="post" action="">
				<div class="form-floating mb-3">
					<input type="email" class="form-control rounded-3" id="email" name="email" placeholder="nome@exemplo.com" required autofocus>
					<label for="email"><i class="bi bi-envelope me-1"></i> Endereço de E-mail</label>
				</div>

				<div class="form-floating mb-4">
					<input type="password" class="form-control rounded-3" id="password" name="password" placeholder="Senha" required>
					<label for="password"><i class="bi bi-lock me-1"></i> Senha de Acesso</label>
				</div>

				<div class="d-grid mb-3">
					<button type="submit" name="form1" class="btn btn-primary btn-login-submit text-white">
						<i class="bi bi-box-arrow-in-right me-1"></i> ACESSAR PAINEL
					</button>
				</div>

				<div class="text-center mt-3">
					<a href="recover.php" class="text-decoration-none small text-body-secondary">
						<i class="bi bi-key me-1"></i> Esqueceu sua senha?
					</a>
				</div>
			</form>
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
					title: 'Falha no Acesso',
					text: '<?= addslashes($error_message) ?>',
					confirmButtonText: 'Tentar Novamente',
					confirmButtonColor: '#0A6B74',
					customClass: {
						confirmButton: 'btn btn-primary px-4'
					},
					buttonsStyling: false
				});
			}
		});
	</script>
	<?php endif; ?>
</body>
</html>