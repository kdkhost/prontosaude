<?php
ob_start();
session_start();
include("config.php");
include("functions.php");
include("../assets/PHPmailer/email.php");

// Garante auto-migração das colunas token e token_time em tbl_user
if (function_exists('pronto_ensure_user_recovery_columns')) {
    pronto_ensure_user_recovery_columns($pdo);
} else {
    try {
        $pdo->query("SELECT token FROM tbl_user LIMIT 1");
    } catch (Exception $e) {
        try {
            $pdo->query("ALTER TABLE tbl_user ADD COLUMN token VARCHAR(255) NULL DEFAULT ''");
        } catch (Exception $e2) {}
    }
    try {
        $pdo->query("SELECT token_time FROM tbl_user LIMIT 1");
    } catch (Exception $e) {
        try {
            $pdo->query("ALTER TABLE tbl_user ADD COLUMN token_time INT NULL DEFAULT 0");
        } catch (Exception $e2) {}
    }
}

$error_message = '';
$success_message = '';
$GET  = $_GET ?? [];
$POST = $_POST ?? [];

// Detecção de requisição AJAX
$is_ajax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
    || (isset($POST['ajax']) && $POST['ajax'] == '1')
    || (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false);

// Obtendo dados de configuração da empresa
$statement = $pdo->prepare("SELECT * FROM tbl_settings WHERE id=1");
$statement->execute();
$config = $statement->fetch(PDO::FETCH_ASSOC) ?: [];
$logo = $config['logo'] ?? '';
$favicon = $config['favicon'] ?? 'favicon.png';
$empresa = $config['empresa'] ?? 'Pronto Saúde';
$footer_copyright = $config['footer_copyright'] ?? '';

// Validação do Token recebido por link de e-mail (GET)
$ValidaToken = false;
$email_decoded = '';
$token_param = trim($GET['tk'] ?? '');
$email_encoded_param = trim($GET['e'] ?? '');

if(!empty($token_param) && !empty($email_encoded_param)) {
    $email_decoded = base64_decode($email_encoded_param);
    if(filter_var($email_decoded, FILTER_VALIDATE_EMAIL)) {
        try {
            $statement = $pdo->prepare("SELECT id, full_name, email, token, token_time, status FROM tbl_user WHERE email=? AND token=? AND token != '' AND status=?");
            $statement->execute([$email_decoded, $token_param, 'Active']);
            $user_row = $statement->fetch(PDO::FETCH_ASSOC);

            if($user_row) {
                $token_time = (int)($user_row['token_time'] ?? 0);
                // Validade de 2 horas (7200 segundos)
                if ($token_time > 0 && (time() - $token_time > 7200)) {
                    $error_message = 'Este link de recuperação expirou (validade máxima de 2 horas). Por favor, solicite um novo link.';
                    $ValidaToken = false;
                } else {
                    $ValidaToken = true;
                }
            } else {
                $error_message = 'Link de recuperação inválido ou já utilizado. Por favor, solicite um novo se necessário.';
            }
        } catch (Exception $e) {
            error_log("Erro ao validar token de recuperação: " . $e->getMessage());
            $error_message = 'Não foi possível validar o link no momento. Tente novamente.';
        }
    } else {
        $error_message = 'Parâmetros de recuperação inválidos ou corrompidos.';
    }
}

// ========================================================
// AÇÃO 1: Solicitar link de recuperação por e-mail
// ========================================================
if(isset($POST['send']) || (isset($POST['action']) && $POST['action'] === 'send')) {
    if(empty($POST['email'])) {
        $error_message = 'Por favor, informe seu endereço de e-mail.';
    } else {
        $email = strip_tags(trim($POST['email']));
        if(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error_message = 'Por favor, informe um formato de e-mail válido.';
        } else {
            try {
                $statement = $pdo->prepare("SELECT id, full_name, email, status FROM tbl_user WHERE email=? AND status=?");
                $statement->execute([$email, 'Active']);
                $total = $statement->rowCount();
                $result = $statement->fetchAll(PDO::FETCH_ASSOC);

                if($total == 0) {
                    $error_message = 'Endereço de e-mail não corresponde a nenhum usuário ativo cadastrado no sistema.';
                } else {
                    $user = $result[0];
                    $token = bin2hex(random_bytes(32));
                    $now = time();
                    $email_encoded = base64_encode($email);

                    // Atualiza token e token_time no banco com tratamento resiliente
                    try {
                        $statement = $pdo->prepare("UPDATE tbl_user SET token=?, token_time=? WHERE id=?");
                        $statement->execute([$token, $now, $user['id']]);
                    } catch (PDOException $pe) {
                        try {
                            $pdo->query("ALTER TABLE tbl_user ADD COLUMN token VARCHAR(255) NULL DEFAULT ''");
                            $pdo->query("ALTER TABLE tbl_user ADD COLUMN token_time INT NULL DEFAULT 0");
                            $statement = $pdo->prepare("UPDATE tbl_user SET token=?, token_time=? WHERE id=?");
                            $statement->execute([$token, $now, $user['id']]);
                        } catch (Exception $pe2) {
                            error_log("Falha crítica ao atualizar token em tbl_user: " . $pe2->getMessage());
                        }
                    }

                    $base = rtrim(BASE_URL, '/') . '/';
                    $linkrecover = $base . "admin/recover.php?tk={$token}&e=" . urlencode($email_encoded);

                    $html = "<div style='font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;'>";
                    $html .= "<h2 style='color: #0a6b74; margin-top: 0;'>Recuperação de Acesso - " . htmlspecialchars($empresa) . "</h2>";
                    $html .= "<p>Olá, <strong>" . htmlspecialchars($user['full_name']) . "</strong>,</p>";
                    $html .= "<p>Recebemos uma solicitação para redefinir sua senha de acesso ao Painel de Gestão.</p>";
                    $html .= "<p>Para cadastrar uma nova senha, clique no botão abaixo (este link é válido por <strong>2 horas</strong>):</p>";
                    $html .= "<p style='margin: 30px 0; text-align: center;'>";
                    $html .= "<a href='{$linkrecover}' style='display:inline-block;padding:12px 28px;background-color:#0a6b74;color:#ffffff;text-decoration:none;border-radius:6px;font-weight:bold;font-size:15px;box-shadow:0 4px 10px rgba(10,107,116,0.3);'>Redefinir Minha Senha</a>";
                    $html .= "</p>";
                    $html .= "<p style='color: #666; font-size: 13px;'>Se o botão não responder, copie e cole o link a seguir no seu navegador:<br>";
                    $html .= "<a href='{$linkrecover}' style='color: #0a6b74; word-break: break-all;'>{$linkrecover}</a></p>";
                    $html .= "<hr style='border: none; border-top: 1px solid #eee; margin: 25px 0;'>";
                    $html .= "<p style='color: #999; font-size: 12px;'>Caso não tenha realizado este pedido, fique tranquilo: desconsidere esta mensagem e seus dados continuarão em total segurança.</p>";
                    $html .= "</div>";

                    $email_send = SendMail($user['email'], $user['full_name'], "Recuperação de Senha - {$empresa}", $html);
                    if($email_send) {
                        $success_message = "Instruções e link para redefinição foram enviados com sucesso para seu e-mail!";
                    } else {
                        error_log("Falha de envio SendMail para: " . $user['email']);
                        $error_message = "Não foi possível enviar o e-mail no momento. Por favor, tente novamente ou contate o suporte.";
                    }
                }
            } catch (Exception $e) {
                error_log("Erro no fluxo de recuperação de senha: " . $e->getMessage());
                $error_message = "Ocorreu um erro no processamento da solicitação. Tente novamente.";
            }
        }
    }

    if($is_ajax) {
        header('Content-Type: application/json; charset=utf-8');
        if(!empty($error_message)) {
            echo json_encode(['success' => false, 'message' => $error_message]);
        } else {
            echo json_encode(['success' => true, 'message' => $success_message]);
        }
        exit;
    }
}

// ========================================================
// AÇÃO 2: Redefinição da Senha com Validação de Token
// ========================================================
if(isset($POST['recover']) || (isset($POST['action']) && $POST['action'] === 'recover')) {
    $password = strip_tags(trim($POST['password'] ?? ''));
    $repassword = strip_tags(trim($POST['re_password'] ?? ''));
    $email = strip_tags(trim($POST['email'] ?? ''));
    $token = strip_tags(trim($POST['token'] ?? ''));

    if(empty($password) || empty($repassword)) {
        $error_message = 'Por favor, preencha todos os campos obrigatórios.';
    } elseif(strlen($password) < 6) {
        $error_message = 'A nova senha deve possuir pelo menos 6 caracteres.';
    } elseif($password !== $repassword) {
        $error_message = 'As senhas digitadas não coincidem. Por favor, tente novamente.';
    } elseif(empty($email) || empty($token)) {
        $error_message = 'Parâmetros de validação ausentes ou inválidos. Reinicie a solicitação.';
    } else {
        try {
            // Valida estritamente se o token confere com o email no banco
            $statement = $pdo->prepare("SELECT id, full_name, email, token_time FROM tbl_user WHERE email=? AND token=? AND token != '' AND status=?");
            $statement->execute([$email, $token, 'Active']);
            $user = $statement->fetch(PDO::FETCH_ASSOC);

            if(!$user) {
                $error_message = 'Token de recuperação inválido ou expirado. Por favor, solicite um novo link.';
            } else {
                $token_time = (int)($user['token_time'] ?? 0);
                if ($token_time > 0 && (time() - $token_time > 7200)) {
                    $error_message = 'Este link de recuperação expirou (validade de 2 horas). Solicite um novo link.';
                } else {
                    // Atualiza a nova senha e consome o token
                    $statement = $pdo->prepare("UPDATE tbl_user SET password=?, token='', token_time=0 WHERE id=?");
                    $statement->execute([md5($password), $user['id']]);

                    // E-mail de notificação de segurança
                    $html = "<div style='font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;'>";
                    $html .= "<h2 style='color: #0a6b74; margin-top: 0;'>Senha Redefinida com Sucesso</h2>";
                    $html .= "<p>Olá, <strong>" . htmlspecialchars($user['full_name']) . "</strong>,</p>";
                    $html .= "<p>A senha de acesso da sua conta no painel <strong>" . htmlspecialchars($empresa) . "</strong> foi alterada com sucesso.</p>";
                    $html .= "<p>Você já pode realizar o login normalmente utilizando suas novas credenciais.</p>";
                    $html .= "<p style='color: #c00;'><small>Se você não realizou esta alteração, entre em contato imediatamente com a administração.</small></p>";
                    $html .= "</div>";
                    SendMail($user['email'], $user['full_name'], "Senha Redefinida - {$empresa}", $html);

                    if($is_ajax) {
                        header('Content-Type: application/json; charset=utf-8');
                        echo json_encode([
                            'success' => true,
                            'message' => 'Senha alterada com sucesso! Redirecionando para o login...',
                            'redirect' => 'login.php?recovered=1'
                        ]);
                        exit;
                    }

                    header("location: login.php?recovered=1");
                    exit;
                }
            }
        } catch (Exception $e) {
            error_log("Erro ao redefinir senha em recover.php: " . $e->getMessage());
            $error_message = 'Ocorreu um erro ao atualizar a senha no sistema. Tente novamente.';
        }
    }

    if($is_ajax) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => false, 'message' => $error_message]);
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Recuperar Senha - <?= htmlspecialchars($empresa) ?></title>

	<!-- Script de Inicialização Instantânea do Tema Dark/Light (Zero Flash / FOUC) -->
	<script>
		(function () {
			try {
				var stored = localStorage.getItem('pronto_admin_theme') || localStorage.getItem('theme');
				var preferred = stored || (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
				var resolved = preferred === 'auto' ? (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light') : preferred;
				document.documentElement.setAttribute('data-bs-theme', resolved);
			} catch (e) {}
		})();
	</script>

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
			position: relative;
		}
		.login-card-modern {
			background: rgba(255, 255, 255, 0.96);
			backdrop-filter: blur(12px);
			border: 1px solid rgba(255, 255, 255, 0.3);
			border-radius: 1.25rem;
			box-shadow: 0 20px 40px rgba(0, 0, 0, 0.25);
			max-width: 440px;
			width: 100%;
			overflow: hidden;
			transition: all 0.3s ease;
		}
		[data-bs-theme="dark"] .login-card-modern {
			background: rgba(19, 31, 51, 0.96);
			border: 1px solid rgba(255, 255, 255, 0.12);
			box-shadow: 0 25px 50px rgba(0, 0, 0, 0.55);
			color: #f8fafc;
		}
		.login-header-logo {
			background: rgba(10, 107, 116, 0.05);
			padding: 2rem 1.5rem 1.25rem 1.5rem;
			text-align: center;
			border-bottom: 1px solid rgba(0, 0, 0, 0.05);
			position: relative;
		}
		[data-bs-theme="dark"] .login-header-logo {
			background: rgba(10, 107, 116, 0.15);
			border-bottom: 1px solid rgba(255, 255, 255, 0.08);
		}
		.login-header-logo img {
			max-height: 48px;
			max-width: 240px;
			width: auto;
		}
		[data-bs-theme="dark"] .ps-login-logo-light { display: inline-block; }
		[data-bs-theme="dark"] .ps-login-logo-dark { display: none; }
		:root:not([data-bs-theme="dark"]) .ps-login-logo-light,
		[data-bs-theme="light"] .ps-login-logo-light { display: none; }
		:root:not([data-bs-theme="dark"]) .ps-login-logo-dark,
		[data-bs-theme="light"] .ps-login-logo-dark { display: inline-block; }

		[data-bs-theme="dark"] .form-control {
			background-color: #152238;
			border-color: #2e4363;
			color: #f8fafc;
		}
		[data-bs-theme="dark"] .form-floating > label {
			color: #94a3b8;
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
		.btn-login-theme-toggle {
			position: absolute;
			top: 1rem;
			right: 1rem;
			background: rgba(255, 255, 255, 0.2);
			border: 1px solid rgba(255, 255, 255, 0.3);
			color: #ffffff;
			border-radius: 50%;
			width: 40px;
			height: 40px;
			display: flex;
			align-items: center;
			justify-content: center;
			cursor: pointer;
			backdrop-filter: blur(8px);
			transition: all 0.2s;
			z-index: 10;
		}
		.btn-login-theme-toggle:hover {
			background: rgba(255, 255, 255, 0.35);
			transform: scale(1.08);
			color: #ffffff;
		}
		[data-bs-theme="dark"] .icon-theme-dark { display: none; }
		[data-bs-theme="dark"] .icon-theme-light { display: inline-block; color: #ffc107; }
		:root:not([data-bs-theme="dark"]) .icon-theme-light,
		[data-bs-theme="light"] .icon-theme-light { display: none; }
		:root:not([data-bs-theme="dark"]) .icon-theme-dark,
		[data-bs-theme="light"] .icon-theme-dark { display: inline-block; color: #ffffff; }

		.password-field-wrapper {
			position: relative;
		}
		.password-toggle-btn {
			position: absolute;
			right: 12px;
			top: 50%;
			transform: translateY(-50%);
			background: transparent;
			border: none;
			color: #64748b;
			padding: 4px 8px;
			cursor: pointer;
			z-index: 5;
		}
		.password-toggle-btn:hover {
			color: #0a6b74;
		}
	</style>
</head>

<body>
	<!-- Botão Flutuante de Alternância de Tema -->
	<button type="button" class="btn-login-theme-toggle" id="btnThemeToggle" aria-label="Alternar Tema Claro / Escuro" title="Alternar Modo Escuro / Claro">
		<i class="bi bi-moon-stars-fill icon-theme-dark"></i>
		<i class="bi bi-sun-fill icon-theme-light"></i>
	</button>

	<div class="login-card-modern">
		<div class="login-header-logo">
			<?php if(!empty($logo)): ?>
				<img src="../assets/uploads/<?= htmlspecialchars($logo) ?>" alt="<?= htmlspecialchars($empresa) ?>" class="ps-login-logo-light">
				<img src="../assets/uploads/logo-dark.png" alt="<?= htmlspecialchars($empresa) ?>" class="ps-login-logo-dark">
			<?php else: ?>
				<h3 class="fw-bold text-primary mb-0"><i class="bi bi-hospital me-2"></i><?= htmlspecialchars($empresa) ?></h3>
			<?php endif; ?>
			<p class="text-body-secondary small mt-2 mb-0">Portal de Gestão Integrada • AdminLTE 4</p>
		</div>

		<div class="p-4 p-sm-5">
			<?php if($ValidaToken): ?>
				<!-- FORMULÁRIO 1: Redefinição com Token Válido -->
				<div class="mb-4 text-center">
					<h4 class="fw-bold mb-1">Criar Nova Senha</h4>
					<small class="text-body-secondary">Digite sua nova senha segura para continuar</small>
				</div>

				<form method="post" action="" id="formRecoverPassword">
					<input name="email" type="hidden" value="<?= htmlspecialchars($email_decoded); ?>">
					<input name="token" type="hidden" value="<?= htmlspecialchars($token_param); ?>">
					<input name="action" type="hidden" value="recover">

					<div class="password-field-wrapper mb-3">
						<div class="form-floating">
							<input type="password" class="form-control rounded-3" id="password" name="password" placeholder="Nova Senha" required autofocus autocomplete="new-password">
							<label for="password"><i class="bi bi-lock me-1"></i> Nova Senha</label>
						</div>
						<button type="button" class="password-toggle-btn" data-target="password" title="Ver / Ocultar Senha">
							<i class="bi bi-eye"></i>
						</button>
					</div>

					<div class="password-field-wrapper mb-4">
						<div class="form-floating">
							<input type="password" class="form-control rounded-3" id="re_password" name="re_password" placeholder="Confirmar Nova Senha" required autocomplete="new-password">
							<label for="re_password"><i class="bi bi-shield-check me-1"></i> Confirmar Nova Senha</label>
						</div>
						<button type="button" class="password-toggle-btn" data-target="re_password" title="Ver / Ocultar Senha">
							<i class="bi bi-eye"></i>
						</button>
					</div>

					<div class="d-grid mb-3">
						<button type="submit" id="btnSubmitRecover" name="recover" class="btn btn-primary btn-login-submit text-white">
							<i class="bi bi-check-circle me-1"></i> REDEFINIR SENHA
						</button>
					</div>

					<div class="text-center mt-3">
						<a href="login.php" class="text-decoration-none small text-body-secondary">
							<i class="bi bi-arrow-left me-1"></i> Cancelar e Voltar ao Login
						</a>
					</div>
				</form>
			<?php else: ?>
				<!-- FORMULÁRIO 2: Solicitação Inicial de Envio por E-mail -->
				<div class="mb-4 text-center">
					<h4 class="fw-bold mb-1">Recuperar Senha</h4>
					<small class="text-body-secondary">Informe seu e-mail cadastrado para enviarmos as instruções</small>
				</div>

				<form method="post" action="" id="formSendInstructions">
					<input name="action" type="hidden" value="send">

					<div class="form-floating mb-4">
						<input type="email" class="form-control rounded-3" id="email" name="email" placeholder="nome@exemplo.com" required autofocus>
						<label for="email"><i class="bi bi-envelope me-1"></i> Endereço de E-mail</label>
					</div>

					<div class="d-grid mb-3">
						<button type="submit" id="btnSubmitSend" name="send" class="btn btn-primary btn-login-submit text-white">
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
			<small class="text-body-secondary">&copy; <?= date('Y'); ?> <?= htmlspecialchars($empresa); ?> • Desenvolvido por <a href="https://kdkhost.com.br" target="_blank" rel="noopener" class="text-decoration-none fw-semibold" style="color: #0a6b74;">KDK Host</a></small>
		</div>
	</div>

	<!-- Scripts -->
	<script src="js/jquery-2.2.4.min.js"></script>
	<script src="js/bootstrap5.bundle.min.js"></script>
	<script src="js/sweetalert2.all.min.js"></script>
	<script src="js/toastr.min.js"></script>
	<script src="js/pronto-global.js"></script>

	<script>
		// Alternância de Tema Claro / Escuro
		document.getElementById('btnThemeToggle')?.addEventListener('click', function() {
			var current = document.documentElement.getAttribute('data-bs-theme') || 'light';
			var next = current === 'dark' ? 'light' : 'dark';
			document.documentElement.setAttribute('data-bs-theme', next);
			localStorage.setItem('pronto_admin_theme', next);
			if (window.toastr) {
				toastr.info('Tema: ' + (next === 'dark' ? 'Modo Escuro' : 'Modo Claro'), '', { timeOut: 1500, positionClass: 'toast-bottom-center' });
			}
		});

		// Visualizar / Ocultar Senhas
		document.querySelectorAll('.password-toggle-btn').forEach(function(btn) {
			btn.addEventListener('click', function() {
				var targetId = this.getAttribute('data-target');
				var input = document.getElementById(targetId);
				if (!input) return;
				var icon = this.querySelector('i');
				if (input.type === 'password') {
					input.type = 'text';
					icon.className = 'bi bi-eye-slash';
				} else {
					input.type = 'password';
					icon.className = 'bi bi-eye';
				}
			});
		});

		// Submissão Assíncrona via AJAX do Envio de Instruções
		document.getElementById('formSendInstructions')?.addEventListener('submit', function(e) {
			e.preventDefault();
			var form = this;
			var btn = document.getElementById('btnSubmitSend');
			var originalBtnHtml = btn.innerHTML;
			var emailInput = document.getElementById('email');
			var emailVal = (emailInput?.value || '').trim();

			if (!emailVal) {
				if (typeof toastr !== 'undefined') {
					toastr.warning('Por favor, informe seu endereço de e-mail.', 'Atenção');
				}
				return;
			}

			btn.disabled = true;
			btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Enviando...';

			var formData = new FormData(form);
			formData.append('ajax', '1');

			fetch('recover.php', {
				method: 'POST',
				body: formData,
				headers: {
					'X-Requested-With': 'XMLHttpRequest'
				}
			})
			.then(function(res) {
				return res.json();
			})
			.then(function(data) {
				btn.disabled = false;
				btn.innerHTML = originalBtnHtml;

				if (data.success) {
					if (typeof toastr !== 'undefined') {
						toastr.success(data.message, 'Pronto Saúde');
					}
					emailInput.value = '';
				} else {
					if (typeof toastr !== 'undefined') {
						toastr.error(data.message || 'Erro ao processar solicitação.', 'Atenção');
					}
				}
			})
			.catch(function(err) {
				btn.disabled = false;
				btn.innerHTML = originalBtnHtml;
				if (typeof toastr !== 'undefined') {
					toastr.error('Erro na conexão com o servidor. Tente novamente.', 'Falha de Comunicação');
				}
			});
		});

		// Submissão Assíncrona via AJAX da Redefinição de Senha
		document.getElementById('formRecoverPassword')?.addEventListener('submit', function(e) {
			e.preventDefault();
			var form = this;
			var btn = document.getElementById('btnSubmitRecover');
			var originalBtnHtml = btn.innerHTML;
			var pass = (document.getElementById('password')?.value || '').trim();
			var repass = (document.getElementById('re_password')?.value || '').trim();

			if (!pass || !repass) {
				if (typeof toastr !== 'undefined') {
					toastr.warning('Por favor, preencha todos os campos.', 'Campos Obrigatórios');
				}
				return;
			}

			if (pass.length < 6) {
				if (typeof toastr !== 'undefined') {
					toastr.warning('A nova senha deve possuir pelo menos 6 caracteres.', 'Senha Curta');
				}
				return;
			}

			if (pass !== repass) {
				if (typeof toastr !== 'undefined') {
					toastr.error('As senhas digitadas não coincidem. Digite novamente.', 'Senhas Diferentes');
				}
				return;
			}

			btn.disabled = true;
			btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Salvando...';

			var formData = new FormData(form);
			formData.append('ajax', '1');

			fetch('recover.php', {
				method: 'POST',
				body: formData,
				headers: {
					'X-Requested-With': 'XMLHttpRequest'
				}
			})
			.then(function(res) {
				return res.json();
			})
			.then(function(data) {
				btn.disabled = false;
				btn.innerHTML = originalBtnHtml;

				if (data.success) {
					if (typeof toastr !== 'undefined') {
						toastr.success('Senha redefinida com sucesso! Redirecionando...', 'Pronto Saúde');
					}
					setTimeout(function() {
						window.location.href = data.redirect || 'login.php?recovered=1';
					}, 1500);
				} else {
					if (typeof toastr !== 'undefined') {
						toastr.error(data.message || 'Erro ao redefinir senha.', 'Atenção');
					}
				}
			})
			.catch(function(err) {
				btn.disabled = false;
				btn.innerHTML = originalBtnHtml;
				if (typeof toastr !== 'undefined') {
					toastr.error('Erro na conexão com o servidor. Tente novamente.', 'Falha de Comunicação');
				}
			});
		});
	</script>

	<?php if (!empty($error_message)): ?>
	<script>
		document.addEventListener('DOMContentLoaded', function() {
			if (typeof toastr !== 'undefined') {
				toastr.error('<?= addslashes($error_message) ?>', 'Pronto Saúde');
			}
		});
	</script>
	<?php endif; ?>

	<?php if (!empty($success_message)): ?>
	<script>
		document.addEventListener('DOMContentLoaded', function() {
			if (typeof toastr !== 'undefined') {
				toastr.success('<?= addslashes($success_message) ?>', 'Pronto Saúde');
			}
		});
	</script>
	<?php endif; ?>
</body>
</html>