<?php
require_once('header.php');

$error_message = '';
$success_message = '';
$active_tab = 'tab_1';

// Suporte a detecção de aba ativa via parâmetro GET
if (isset($_GET['tab']) && in_array($_GET['tab'], ['tab_1', 'tab_2', 'tab_3'])) {
    $active_tab = $_GET['tab'];
}

// Detecção de requisição AJAX
$is_ajax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
    || (isset($_POST['ajax']) && $_POST['ajax'] == '1')
    || (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false);

// ========================================================
// 1. ATUALIZAR INFORMAÇÕES PESSOAIS (FORM 1)
// ========================================================
if (isset($_POST['form1']) || (isset($_POST['action']) && $_POST['action'] === 'update_info')) {
    $active_tab = 'tab_1';
    $valid = 1;
    $user_id = $_SESSION['user']['id'];
    $phone_input = strip_tags(trim($_POST['phone'] ?? ''));

    if ($_SESSION['user']['role'] === 'Super Admin' || $_SESSION['user']['role'] === 'Admin') {
        $full_name_input = strip_tags(trim($_POST['full_name'] ?? ''));
        $email_input = strip_tags(trim($_POST['email'] ?? ''));

        if (empty($full_name_input)) {
            $valid = 0;
            $error_message .= "O nome completo não pode ficar vazio.<br>";
        }

        if ($_SESSION['user']['role'] === 'Super Admin') {
            if (empty($email_input)) {
                $valid = 0;
                $error_message .= "O endereço de e-mail não pode ficar vazio.<br>";
            } elseif (!filter_var($email_input, FILTER_VALIDATE_EMAIL)) {
                $valid = 0;
                $error_message .= "O endereço de e-mail informado é inválido.<br>";
            } else {
                $st_email = $pdo->prepare("SELECT id FROM tbl_user WHERE email = ? AND id != ?");
                $st_email->execute([$email_input, $user_id]);
                if ($st_email->rowCount() > 0) {
                    $valid = 0;
                    $error_message .= "Este endereço de e-mail já está em uso por outro usuário.<br>";
                }
            }
        }

        if ($valid == 1) {
            if ($_SESSION['user']['role'] === 'Super Admin') {
                $st_update = $pdo->prepare("UPDATE tbl_user SET full_name = ?, email = ?, phone = ? WHERE id = ?");
                $st_update->execute([$full_name_input, $email_input, $phone_input, $user_id]);
                $_SESSION['user']['email'] = $email_input;
            } else {
                $st_update = $pdo->prepare("UPDATE tbl_user SET full_name = ?, phone = ? WHERE id = ?");
                $st_update->execute([$full_name_input, $phone_input, $user_id]);
            }
            $_SESSION['user']['full_name'] = $full_name_input;
            $_SESSION['user']['phone'] = $phone_input;
            $success_message = "Informações do perfil atualizadas com sucesso.";
        }
    } else {
        $st_update = $pdo->prepare("UPDATE tbl_user SET phone = ? WHERE id = ?");
        $st_update->execute([$phone_input, $user_id]);
        $_SESSION['user']['phone'] = $phone_input;
        $success_message = "Informações de contato atualizadas com sucesso.";
    }

    if ($is_ajax) {
        header('Content-Type: application/json; charset=utf-8');
        if (!empty($error_message)) {
            echo json_encode(['success' => false, 'message' => $error_message]);
        } else {
            echo json_encode([
                'success' => true,
                'message' => $success_message,
                'full_name' => $_SESSION['user']['full_name'] ?? '',
                'phone' => $_SESSION['user']['phone'] ?? '',
                'email' => $_SESSION['user']['email'] ?? ''
            ]);
        }
        exit;
    }
}

// ========================================================
// 2. ATUALIZAR FOTO DE PERFIL (FORM 2)
// ========================================================
if (isset($_POST['form2']) || (isset($_POST['action']) && $_POST['action'] === 'update_photo')) {
    $active_tab = 'tab_2';
    $valid = 1;
    $user_id = $_SESSION['user']['id'];

    $path = $_FILES['photo']['name'] ?? '';
    $path_tmp = $_FILES['photo']['tmp_name'] ?? '';

    if (!empty($path)) {
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        if (!in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
            $valid = 0;
            $error_message .= "Formato inválido. A foto deve ser JPG, JPEG, PNG, GIF ou WEBP.<br>";
        }
    } else {
        $valid = 0;
        $error_message .= "Por favor, selecione uma imagem para atualizar seu perfil.<br>";
    }

    if ($valid == 1) {
        // Remove imagem física antiga
        if (!empty($_SESSION['user']['photo']) && file_exists('../assets/uploads/' . $_SESSION['user']['photo'])) {
            @unlink('../assets/uploads/' . $_SESSION['user']['photo']);
        }

        $final_name = 'user-' . $user_id . '-' . time() . '.' . $ext;
        move_uploaded_file($path_tmp, '../assets/uploads/' . $final_name);

        $_SESSION['user']['photo'] = $final_name;
        $st_photo = $pdo->prepare("UPDATE tbl_user SET photo = ? WHERE id = ?");
        $st_photo->execute([$final_name, $user_id]);

        $success_message = "Foto de perfil atualizada com sucesso.";
    }

    if ($is_ajax) {
        header('Content-Type: application/json; charset=utf-8');
        if (!empty($error_message)) {
            echo json_encode(['success' => false, 'message' => $error_message]);
        } else {
            echo json_encode([
                'success' => true,
                'message' => $success_message,
                'photo_url' => '../assets/uploads/' . $final_name
            ]);
        }
        exit;
    }
}

// ========================================================
// 3. ATUALIZAR SENHA (FORM 3)
// ========================================================
if (isset($_POST['form3']) || (isset($_POST['action']) && $_POST['action'] === 'update_password')) {
    $active_tab = 'tab_3';
    $valid = 1;
    $user_id = $_SESSION['user']['id'];

    $password = strip_tags(trim($_POST['password'] ?? ''));
    $re_password = strip_tags(trim($_POST['re_password'] ?? ''));

    if (empty($password) || empty($re_password)) {
        $valid = 0;
        $error_message .= "A nova senha e a confirmação são campos obrigatórios.<br>";
    } elseif (strlen($password) < 6) {
        $valid = 0;
        $error_message .= "A nova senha deve possuir no mínimo 6 caracteres.<br>";
    } elseif ($password !== $re_password) {
        $valid = 0;
        $error_message .= "As senhas digitadas não coincidem. Digite-as novamente.<br>";
    }

    if ($valid == 1) {
        $hash = md5($password);
        $_SESSION['user']['password'] = $hash;

        $st_pass = $pdo->prepare("UPDATE tbl_user SET password = ? WHERE id = ?");
        $st_pass->execute([$hash, $user_id]);

        $success_message = "Sua senha de acesso foi atualizada com sucesso.";
    }

    if ($is_ajax) {
        header('Content-Type: application/json; charset=utf-8');
        if (!empty($error_message)) {
            echo json_encode(['success' => false, 'message' => $error_message]);
        } else {
            echo json_encode(['success' => true, 'message' => $success_message]);
        }
        exit;
    }
}

// Carrega os dados mais recentes do usuário logado
$statement = $pdo->prepare("SELECT * FROM tbl_user WHERE id = ?");
$statement->execute([$_SESSION['user']['id']]);
$user_data = $statement->fetch(PDO::FETCH_ASSOC) ?: [];

$full_name = $user_data['full_name'] ?? '';
$email     = $user_data['email'] ?? '';
$phone     = $user_data['phone'] ?? '';
$photo     = $user_data['photo'] ?? '';
$status    = $user_data['status'] ?? 'Active';
$role      = $user_data['role'] ?? 'Editor';

$avatar_path = (!empty($photo) && file_exists('../assets/uploads/' . $photo)) 
    ? '../assets/uploads/' . $photo 
    : '../assets/uploads/user.png';
?>

<style>
/* Estilos Exclusivos do Módulo de Edição de Perfil Moderno */
.profile-card-header {
    background: linear-gradient(135deg, #0a6b74 0%, #07474d 100%);
    height: 100px;
    border-top-left-radius: 1rem;
    border-top-right-radius: 1rem;
    position: relative;
}
.profile-avatar-wrapper {
    position: relative;
    width: 110px;
    height: 110px;
    margin: -55px auto 10px auto;
}
.profile-avatar-img {
    width: 110px;
    height: 110px;
    object-fit: cover;
    border-radius: 50%;
    border: 4px solid var(--bs-card-bg, #ffffff);
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.18);
    background-color: #f1f5f9;
}
.profile-avatar-badge {
    position: absolute;
    bottom: 2px;
    right: 2px;
    width: 28px;
    height: 28px;
    border-radius: 50%;
    background-color: #10b981;
    border: 2px solid #ffffff;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #ffffff;
    font-size: 13px;
    box-shadow: 0 2px 6px rgba(0,0,0,0.2);
}
.btn-change-avatar-quick {
    position: absolute;
    bottom: 0;
    left: 0;
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: #0a6b74;
    color: #ffffff;
    border: 2px solid #ffffff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 13px;
    cursor: pointer;
    transition: all 0.2s ease;
    box-shadow: 0 2px 6px rgba(0,0,0,0.25);
}
.btn-change-avatar-quick:hover {
    background: #07474d;
    transform: scale(1.1);
    color: #ffffff;
}

/* Nav Pills Customizadas Modernas */
.nav-profile-pills .nav-link {
    color: var(--bs-secondary-color, #64748b);
    font-weight: 600;
    padding: 0.75rem 1.25rem;
    border-radius: 0.75rem;
    transition: all 0.2s ease;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    border: 1px solid transparent;
}
.nav-profile-pills .nav-link:hover {
    color: #0a6b74;
    background-color: rgba(10, 107, 116, 0.08);
}
.nav-profile-pills .nav-link.active {
    color: #ffffff !important;
    background: linear-gradient(135deg, #0a6b74 0%, #075259 100%) !important;
    box-shadow: 0 4px 12px rgba(10, 107, 116, 0.3);
}

/* Input Groups Verticais e Ícones */
.input-group-modern .input-group-text {
    background-color: var(--bs-tertiary-bg, #f8fafc);
    border-color: var(--bs-border-color, #e2e8f0);
    color: #0a6b74;
    font-size: 1.1rem;
    padding-left: 1rem;
    padding-right: 1rem;
    border-top-left-radius: 0.65rem;
    border-bottom-left-radius: 0.65rem;
}
.input-group-modern .form-control {
    border-top-right-radius: 0.65rem;
    border-bottom-right-radius: 0.65rem;
    border-color: var(--bs-border-color, #e2e8f0);
    padding: 0.65rem 0.95rem;
}
.input-group-modern .form-control:focus {
    border-color: #0a6b74;
    box-shadow: 0 0 0 0.25rem rgba(10, 107, 116, 0.15);
}
.input-group-modern .btn-toggle-eye {
    border-color: var(--bs-border-color, #e2e8f0);
    background-color: var(--bs-tertiary-bg, #f8fafc);
    color: #64748b;
    border-top-right-radius: 0.65rem !important;
    border-bottom-right-radius: 0.65rem !important;
}
.input-group-modern .btn-toggle-eye:hover {
    color: #0a6b74;
}

/* Dropzone de Upload da Foto */
.avatar-dropzone {
    border: 2px dashed rgba(10, 107, 116, 0.35);
    border-radius: 1rem;
    padding: 2rem 1.5rem;
    text-align: center;
    background-color: rgba(10, 107, 116, 0.02);
    cursor: pointer;
    transition: all 0.2s ease;
}
.avatar-dropzone:hover {
    border-color: #0a6b74;
    background-color: rgba(10, 107, 116, 0.05);
}

/* Medidor de Força de Senha */
.password-meter-bar {
    height: 6px;
    border-radius: 3px;
    background-color: #e2e8f0;
    overflow: hidden;
    margin-top: 6px;
}
.password-meter-fill {
    height: 100%;
    width: 0%;
    transition: width 0.3s ease, background-color 0.3s ease;
}

/* Informações Rápidas do Card Esquerdo */
.profile-info-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0.65rem 0;
    border-bottom: 1px dashed rgba(0, 0, 0, 0.08);
}
[data-bs-theme="dark"] .profile-info-row {
    border-bottom-color: rgba(255, 255, 255, 0.08);
}
.profile-info-row:last-child {
    border-bottom: none;
}
</style>

<div class="app-content-header mb-3">
	<div class="container-fluid">
		<div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
			<div>
				<h3 class="mb-1 fw-bold d-flex align-items-center gap-2">
					<i class="bi bi-person-circle text-primary"></i> Meu Perfil de Acesso
				</h3>
				<p class="text-body-secondary small mb-0">
					Gerencie suas credenciais de segurança, dados cadastrais e foto de identificação
				</p>
			</div>
			<ol class="breadcrumb mb-0 small">
				<li class="breadcrumb-item"><a href="index.php"><i class="bi bi-house-door"></i> Início</a></li>
				<li class="breadcrumb-item active" aria-current="page">Meu Perfil</li>
			</ol>
		</div>
	</div>
</div>

<div class="app-content">
	<div class="container-fluid">

		<?php if(!empty($error_message)): ?>
		<div class="alert alert-danger alert-dismissible fade show shadow-sm rounded-4 mb-4" role="alert">
			<div class="d-flex align-items-center gap-2">
				<i class="bi bi-exclamation-octagon-fill fs-4"></i>
				<div><?= $error_message; ?></div>
			</div>
			<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
		</div>
		<?php endif; ?>

		<?php if(!empty($success_message)): ?>
		<div class="alert alert-success alert-dismissible fade show shadow-sm rounded-4 mb-4" role="alert">
			<div class="d-flex align-items-center gap-2">
				<i class="bi bi-check-circle-fill fs-4"></i>
				<div><?= $success_message; ?></div>
			</div>
			<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
		</div>
		<?php endif; ?>

		<div class="row g-4">
			
			<!-- ========================================================
			     COLUNA ESQUERDA: CARD RESUMO DO PERFIL & SEGURANÇA
			     ======================================================== -->
			<div class="col-12 col-xl-4">
				
				<!-- Card do Usuário -->
				<div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4 bg-body">
					<div class="profile-card-header"></div>
					<div class="card-body p-4 pt-0 text-center">
						<div class="profile-avatar-wrapper">
							<img src="<?= $avatar_path; ?>" alt="<?= htmlspecialchars($full_name); ?>" class="profile-avatar-img user-live-avatar" id="profileLeftAvatar">
							<span class="profile-avatar-badge" title="Status: Ativo">
								<i class="bi bi-check"></i>
							</span>
							<button type="button" class="btn-change-avatar-quick" id="btnQuickAvatar" title="Alterar Foto de Perfil">
								<i class="bi bi-camera-fill"></i>
							</button>
						</div>

						<h4 class="fw-bold mb-1 user-live-fullname" id="profileLeftName"><?= htmlspecialchars($full_name); ?></h4>
						<div class="mb-3">
							<?php 
							if($role === "Super Admin") {
								echo '<span class="badge bg-danger-subtle text-danger border border-danger-subtle px-3 py-1 rounded-pill"><i class="bi bi-shield-lock-fill me-1"></i>Super Administrador</span>';
							} elseif($role === "Admin") {
								echo '<span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-1 rounded-pill"><i class="bi bi-shield-check me-1"></i>Administrador</span>';
							} else {
								echo '<span class="badge bg-info-subtle text-info border border-info-subtle px-3 py-1 rounded-pill"><i class="bi bi-person-fill-gear me-1"></i>Editor / Operador</span>';
							}
							?>
						</div>

						<div class="border-top pt-3 text-start small">
							<div class="profile-info-row">
								<span class="text-body-secondary"><i class="bi bi-envelope me-1 text-primary"></i> E-mail:</span>
								<span class="fw-semibold text-truncate user-live-email" style="max-width: 190px;" id="profileLeftEmail"><?= htmlspecialchars($email); ?></span>
							</div>
							<div class="profile-info-row">
								<span class="text-body-secondary"><i class="bi bi-telephone me-1 text-primary"></i> Telefone:</span>
								<span class="fw-semibold user-live-phone" id="profileLeftPhone"><?= !empty($phone) ? htmlspecialchars($phone) : '<span class="text-muted">Não informado</span>'; ?></span>
							</div>
							<div class="profile-info-row">
								<span class="text-body-secondary"><i class="bi bi-shield-check me-1 text-success"></i> Status:</span>
								<span class="badge bg-success-subtle text-success border border-success-subtle">Conta Ativa</span>
							</div>
							<div class="profile-info-row">
								<span class="text-body-secondary"><i class="bi bi-hash me-1 text-secondary"></i> ID da Conta:</span>
								<span class="font-monospace fw-bold">#USR-<?= str_pad((string)$_SESSION['user']['id'], 4, '0', STR_PAD_LEFT); ?></span>
							</div>
						</div>
					</div>
				</div>

				<!-- Card de Boas Práticas de Senha -->
				<div class="card border-0 shadow-sm rounded-4 bg-body">
					<div class="card-body p-4">
						<div class="d-flex align-items-center gap-2 mb-3">
							<i class="bi bi-shield-fill-check text-primary fs-4"></i>
							<h6 class="fw-bold mb-0">Diretrizes de Segurança</h6>
						</div>
						<ul class="list-unstyled small text-body-secondary mb-0 d-flex flex-column gap-2">
							<li class="d-flex align-items-start gap-2">
								<i class="bi bi-check2-circle text-success mt-1"></i>
								<span>Utilize no mínimo <strong>6 a 8 caracteres</strong> para sua senha de acesso.</span>
							</li>
							<li class="d-flex align-items-start gap-2">
								<i class="bi bi-check2-circle text-success mt-1"></i>
								<span>Combine letras maiúsculas, minúsculas, números e símbolos especiais.</span>
							</li>
							<li class="d-flex align-items-start gap-2">
								<i class="bi bi-check2-circle text-success mt-1"></i>
								<span>Evite sequências previsíveis, datas de aniversário ou dados públicos.</span>
							</li>
							<li class="d-flex align-items-start gap-2">
								<i class="bi bi-check2-circle text-success mt-1"></i>
								<span>Nunca compartilhe sua credencial de acesso com terceiros.</span>
							</li>
						</ul>
					</div>
				</div>

			</div>

			<!-- ========================================================
			     COLUNA DIREITA: FORMULÁRIOS MODERNOS EM ABAS VERTICAIS
			     ======================================================== -->
			<div class="col-12 col-xl-8">
				
				<div class="card border-0 shadow-sm rounded-4 bg-body overflow-hidden">
					
					<!-- Header de Abas Nav-Pills -->
					<div class="card-header bg-body-tertiary border-bottom p-3">
						<ul class="nav nav-pills nav-profile-pills gap-2 flex-wrap" id="profilePills" role="tablist">
							<li class="nav-item" role="presentation">
								<button class="nav-link <?= ($active_tab === 'tab_1') ? 'active' : ''; ?>" id="pill-info-tab" data-bs-toggle="pill" data-bs-target="#tab_1" type="button" role="tab" aria-controls="tab_1" aria-selected="<?= ($active_tab === 'tab_1') ? 'true' : 'false'; ?>">
									<i class="bi bi-person-vcard"></i>
									<span>Dados Pessoais</span>
								</button>
							</li>
							<li class="nav-item" role="presentation">
								<button class="nav-link <?= ($active_tab === 'tab_2') ? 'active' : ''; ?>" id="pill-photo-tab" data-bs-toggle="pill" data-bs-target="#tab_2" type="button" role="tab" aria-controls="tab_2" aria-selected="<?= ($active_tab === 'tab_2') ? 'true' : 'false'; ?>">
									<i class="bi bi-image"></i>
									<span>Foto de Perfil</span>
								</button>
							</li>
							<li class="nav-item" role="presentation">
								<button class="nav-link <?= ($active_tab === 'tab_3') ? 'active' : ''; ?>" id="pill-pass-tab" data-bs-toggle="pill" data-bs-target="#tab_3" type="button" role="tab" aria-controls="tab_3" aria-selected="<?= ($active_tab === 'tab_3') ? 'true' : 'false'; ?>">
									<i class="bi bi-key-fill"></i>
									<span>Segurança & Senha</span>
								</button>
							</li>
						</ul>
					</div>

					<div class="card-body p-4 p-md-5">
						<div class="tab-content" id="profilePillsContent">
							
							<!-- ========================================================
							     ABA 1: DADOS PESSOAIS (FORMULÁRIO VERTICAL LIMPO)
							     ======================================================== -->
							<div class="tab-pane fade <?= ($active_tab === 'tab_1') ? 'show active' : ''; ?>" id="tab_1" role="tabpanel" aria-labelledby="pill-info-tab">
								
								<div class="d-flex align-items-center justify-content-between mb-4 border-bottom pb-3">
									<div>
										<h5 class="fw-bold mb-1">Informações Básicas e Contato</h5>
										<p class="text-body-secondary small mb-0">Atualize seus dados de identificação e comunicação no sistema</p>
									</div>
									<span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-2">
										<i class="bi bi-person-check me-1"></i> Perfil Cadastrado
									</span>
								</div>

								<form action="" method="post" id="formProfileInfo">
									<input type="hidden" name="action" value="update_info">
									<input type="hidden" name="form1" value="1">

									<div class="row g-3 mb-4">
										
										<!-- Nome Completo -->
										<div class="col-12">
											<label for="full_name" class="form-label fw-semibold">
												<i class="bi bi-person me-1 text-primary"></i> Nome Completo <span class="text-danger">*</span>
											</label>
											<?php if($_SESSION['user']['role'] === 'Super Admin' || $_SESSION['user']['role'] === 'Admin'): ?>
												<div class="input-group input-group-modern">
													<span class="input-group-text"><i class="bi bi-person"></i></span>
													<input type="text" class="form-control" id="full_name" name="full_name" value="<?= htmlspecialchars($full_name); ?>" placeholder="Seu nome completo" required>
												</div>
											<?php else: ?>
												<div class="input-group input-group-modern">
													<span class="input-group-text"><i class="bi bi-person-lock"></i></span>
													<input type="text" class="form-control bg-body-secondary" value="<?= htmlspecialchars($full_name); ?>" readonly>
												</div>
												<div class="form-text text-muted small mt-1">A alteração de nome é restrita a administradores.</div>
											<?php endif; ?>
										</div>

										<!-- E-mail de Acesso -->
										<div class="col-12 col-md-6">
											<label for="email" class="form-label fw-semibold">
												<i class="bi bi-envelope me-1 text-primary"></i> E-mail de Acesso <span class="text-danger">*</span>
											</label>
											<?php if($_SESSION['user']['role'] === 'Super Admin'): ?>
												<div class="input-group input-group-modern">
													<span class="input-group-text"><i class="bi bi-envelope-at"></i></span>
													<input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($email); ?>" placeholder="nome@exemplo.com" required>
												</div>
											<?php else: ?>
												<div class="input-group input-group-modern">
													<span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
													<input type="email" class="form-control bg-body-secondary" value="<?= htmlspecialchars($email); ?>" readonly>
												</div>
												<div class="form-text text-muted small mt-1">E-mail corporativo gerenciado pela administração.</div>
											<?php endif; ?>
										</div>

										<!-- Telefone / WhatsApp -->
										<div class="col-12 col-md-6">
											<label for="phone" class="form-label fw-semibold">
												<i class="bi bi-whatsapp me-1 text-success"></i> Telefone / WhatsApp
											</label>
											<div class="input-group input-group-modern">
												<span class="input-group-text"><i class="bi bi-telephone"></i></span>
												<input type="text" class="form-control" id="phone" name="phone" value="<?= htmlspecialchars($phone); ?>" placeholder="(00) 00000-0000">
											</div>
											<div class="form-text text-muted small mt-1">Utilizado para notificações e suporte interno.</div>
										</div>

										<!-- Perfil de Acesso (Informativo) -->
										<div class="col-12">
											<label class="form-label fw-semibold">
												<i class="bi bi-shield-shaded me-1 text-primary"></i> Nível de Permissão no Sistema
											</label>
											<div class="p-3 bg-body-tertiary rounded-3 border d-flex align-items-center justify-content-between">
												<div class="d-flex align-items-center gap-2">
													<i class="bi bi-shield-check fs-4 text-primary"></i>
													<div>
														<div class="fw-bold mb-0">
															<?php 
															if($role === "Super Admin") echo "Super Administrador (Acesso Irrestrito)";
															elseif($role === "Admin") echo "Administrador Geral do Sistema";
															else echo "Editor / Operador de Conteúdo";
															?>
														</div>
														<small class="text-body-secondary">Permissão vinculada ao seu grupo de usuários</small>
													</div>
												</div>
												<span class="badge bg-primary text-white px-3 py-2 rounded-pill"><?= htmlspecialchars($role); ?></span>
											</div>
										</div>

									</div>

									<div class="d-flex justify-content-end">
										<button type="submit" id="btnSubmitInfo" class="btn btn-primary px-4 py-2 rounded-3 shadow-sm d-inline-flex align-items-center gap-2">
											<i class="bi bi-check2-circle fs-5"></i> Salvar Informações
										</button>
									</div>
								</form>

							</div>

							<!-- ========================================================
							     ABA 2: ATUALIZAR FOTO DE PERFIL (MODERNA COM PREVIEW)
							     ======================================================== -->
							<div class="tab-pane fade <?= ($active_tab === 'tab_2') ? 'show active' : ''; ?>" id="tab_2" role="tabpanel" aria-labelledby="pill-photo-tab">
								
								<div class="d-flex align-items-center justify-content-between mb-4 border-bottom pb-3">
									<div>
										<h5 class="fw-bold mb-1">Foto de Perfil & Avatar</h5>
										<p class="text-body-secondary small mb-0">Escolha uma foto quadrada recente para sua identificação visual no painel</p>
									</div>
									<span class="badge bg-info-subtle text-info border border-info-subtle px-3 py-2">
										<i class="bi bi-aspect-ratio me-1"></i> Formato 1:1 Recomendado
									</span>
								</div>

								<form action="" method="post" enctype="multipart/form-data" id="formProfilePhoto">
									<input type="hidden" name="action" value="update_photo">
									<input type="hidden" name="form2" value="1">

									<div class="row g-4 align-items-center mb-4">
										
										<!-- Comparativo Foto Atual x Novo Preview -->
										<div class="col-12 col-md-5 text-center">
											<div class="p-3 bg-body-tertiary rounded-4 border">
												<small class="text-body-secondary fw-semibold d-block mb-2">Visualização Atual / Nova</small>
												<div class="position-relative d-inline-block">
													<img src="<?= $avatar_path; ?>" alt="Foto" id="avatarPreviewImg" class="rounded-circle border shadow-sm object-fit-cover" style="width: 130px; height: 130px; background-color: #f8fafc;">
													<span class="position-absolute bottom-0 end-0 bg-primary text-white p-2 rounded-circle shadow-sm" style="font-size: 14px; width: 34px; height: 34px; display: flex; align-items: center; justify-content: center;">
														<i class="bi bi-camera"></i>
													</span>
												</div>
												<div class="mt-2 text-muted small" id="avatarFileNameDisplay">Foto atual gravada</div>
											</div>
										</div>

										<!-- Área de Seleção de Arquivo -->
										<div class="col-12 col-md-7">
											<div class="avatar-dropzone" id="avatarDropzone">
												<i class="bi bi-cloud-arrow-up text-primary fs-1 mb-2 d-block"></i>
												<h6 class="fw-bold mb-1">Clique para selecionar sua foto</h6>
												<p class="text-muted small mb-2">ou arraste e solte o arquivo de imagem aqui</p>
												<span class="badge bg-secondary-subtle text-secondary border px-3 py-1">Formatos: JPG, PNG, WEBP ou GIF (Máx. 5MB)</span>
												
												<input type="file" name="photo" id="photoInput" class="d-none" accept="image/jpeg,image/png,image/gif,image/webp" required>
											</div>
										</div>

									</div>

									<div class="d-flex justify-content-end gap-2">
										<button type="button" class="btn btn-outline-secondary px-3 py-2 rounded-3" id="btnCancelPhoto" style="display: none;">
											<i class="bi bi-x-circle me-1"></i> Desfazer Seleção
										</button>
										<button type="submit" id="btnSubmitPhoto" class="btn btn-primary px-4 py-2 rounded-3 shadow-sm d-inline-flex align-items-center gap-2">
											<i class="bi bi-upload fs-5"></i> Atualizar Minha Foto
										</button>
									</div>
								</form>

							</div>

							<!-- ========================================================
							     ABA 3: SEGURANÇA & ALTERAÇÃO DE SENHA (MODERNA VERTICAL)
							     ======================================================== -->
							<div class="tab-pane fade <?= ($active_tab === 'tab_3') ? 'show active' : ''; ?>" id="tab_3" role="tabpanel" aria-labelledby="pill-pass-tab">
								
								<div class="d-flex align-items-center justify-content-between mb-4 border-bottom pb-3">
									<div>
										<h5 class="fw-bold mb-1">Redefinir Senha de Acesso</h5>
										<p class="text-body-secondary small mb-0">Crie uma nova credencial segura para proteger sua conta administrativa</p>
									</div>
									<span class="badge bg-warning-subtle text-warning border border-warning-subtle px-3 py-2">
										<i class="bi bi-shield-lock me-1"></i> Criptografia MD5 Ativa
									</span>
								</div>

								<form action="" method="post" id="formProfilePassword">
									<input type="hidden" name="action" value="update_password">
									<input type="hidden" name="form3" value="1">

									<div class="row g-4 mb-4">
										
										<!-- Campo 1: Nova Senha -->
										<div class="col-12 col-md-6">
											<label for="password" class="form-label fw-semibold">
												<i class="bi bi-lock-fill me-1 text-primary"></i> Nova Senha <span class="text-danger">*</span>
											</label>
											<div class="input-group input-group-modern">
												<span class="input-group-text"><i class="bi bi-key"></i></span>
												<input type="password" class="form-control" id="password" name="password" placeholder="Digite sua nova senha" required autocomplete="new-password">
												<button type="button" class="btn btn-toggle-eye" data-target="password" title="Ver / Ocultar Senha">
													<i class="bi bi-eye"></i>
												</button>
											</div>

											<!-- Medidor Dinâmico de Força -->
											<div class="mt-2">
												<div class="d-flex justify-content-between align-items-center small">
													<span class="text-muted">Força da senha:</span>
													<span class="fw-bold" id="passStrengthLabel" style="color: #64748b;">Mínimo 6 dígitos</span>
												</div>
												<div class="password-meter-bar">
													<div class="password-meter-fill" id="passStrengthFill"></div>
												</div>
											</div>
										</div>

										<!-- Campo 2: Confirmar Senha -->
										<div class="col-12 col-md-6">
											<label for="re_password" class="form-label fw-semibold">
												<i class="bi bi-shield-check me-1 text-primary"></i> Confirmar Nova Senha <span class="text-danger">*</span>
											</label>
											<div class="input-group input-group-modern">
												<span class="input-group-text"><i class="bi bi-shield-lock"></i></span>
												<input type="password" class="form-control" id="re_password" name="re_password" placeholder="Repita a nova senha" required autocomplete="new-password">
												<button type="button" class="btn btn-toggle-eye" data-target="re_password" title="Ver / Ocultar Senha">
													<i class="bi bi-eye"></i>
												</button>
											</div>
											
											<!-- Indicador de Match em Tempo Real -->
											<div class="mt-2 small" id="passMatchMessage" style="min-height: 22px;"></div>
										</div>

									</div>

									<div class="p-3 bg-body-tertiary rounded-3 border mb-4">
										<div class="d-flex align-items-center gap-2 text-body-secondary small">
											<i class="bi bi-info-circle-fill text-primary fs-5"></i>
											<span>Após alterar sua senha, sua sessão atual será mantida com os novos parâmetros atualizados de segurança.</span>
										</div>
									</div>

									<div class="d-flex justify-content-end">
										<button type="submit" id="btnSubmitPassword" class="btn btn-primary px-4 py-2 rounded-3 shadow-sm d-inline-flex align-items-center gap-2">
											<i class="bi bi-shield-check fs-5"></i> Atualizar Minha Senha
										</button>
									</div>
								</form>

							</div>

						</div>
					</div>
				</div>

			</div>

		</div>

	</div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
	
	// ========================================================
	// 1. SUPORTE A DEEP LINKING POR HASH (#tab_1, #tab_2, #tab_3)
	// ========================================================
	function activateTabFromHash() {
		var hash = window.location.hash;
		if (hash && (hash === '#tab_1' || hash === '#tab_2' || hash === '#tab_3')) {
			var triggerEl = document.querySelector('button[data-bs-target="' + hash + '"]');
			if (triggerEl) {
				var tabInstance = bootstrap.Tab.getOrCreateInstance(triggerEl);
				tabInstance.show();
			}
		}
	}

	activateTabFromHash();

	// Atualiza o hash na URL sem recarregar ao trocar de aba
	var pillButtons = document.querySelectorAll('#profilePills button[data-bs-toggle="pill"]');
	pillButtons.forEach(function (btn) {
		btn.addEventListener('shown.bs.tab', function (e) {
			var target = e.target.getAttribute('data-bs-target');
			if (target && history.replaceState) {
				history.replaceState(null, null, target);
			}
		});
	});

	// Botão de atalho rápido na foto da esquerda foca na aba 2
	document.getElementById('btnQuickAvatar')?.addEventListener('click', function () {
		var triggerEl = document.querySelector('button[data-bs-target="#tab_2"]');
		if (triggerEl) {
			bootstrap.Tab.getOrCreateInstance(triggerEl).show();
			document.getElementById('photoInput')?.click();
		}
	});

	// ========================================================
	// 2. ALTERNAR VISIBILIDADE DE SENHA (OLHO)
	// ========================================================
	document.querySelectorAll('.btn-toggle-eye').forEach(function (btn) {
		btn.addEventListener('click', function () {
			var targetId = this.getAttribute('data-target');
			var input = document.getElementById(targetId);
			if (!input) return;
			var icon = this.querySelector('i');
			if (input.type === 'password') {
				input.type = 'text';
				icon.className = 'bi bi-eye-slash text-primary';
			} else {
				input.type = 'password';
				icon.className = 'bi bi-eye';
			}
		});
	});

	// ========================================================
	// 3. MEDIDOR DINÂMICO DE FORÇA E CORRESPONDÊNCIA DE SENHA
	// ========================================================
	var passInput = document.getElementById('password');
	var rePassInput = document.getElementById('re_password');
	var meterFill = document.getElementById('passStrengthFill');
	var meterLabel = document.getElementById('passStrengthLabel');
	var matchMessage = document.getElementById('passMatchMessage');

	function checkPasswordStrength(pass) {
		var score = 0;
		if (!pass) return { score: 0, text: 'Vazia', color: '#64748b', width: '0%' };
		if (pass.length >= 6) score += 25;
		if (pass.length >= 8) score += 15;
		if (/[A-Z]/.test(pass)) score += 20;
		if (/[0-9]/.test(pass)) score += 20;
		if (/[^A-Za-z0-9]/.test(pass)) score += 20;

		if (score < 40) return { score: score, text: 'Fraca', color: '#ef4444', width: '25%' };
		if (score < 70) return { score: score, text: 'Média', color: '#f59e0b', width: '60%' };
		return { score: score, text: 'Forte & Segura', color: '#10b981', width: '100%' };
	}

	function validateMatch() {
		var p1 = passInput ? passInput.value : '';
		var p2 = rePassInput ? rePassInput.value : '';

		if (!p2) {
			matchMessage.innerHTML = '';
			return;
		}
		if (p1 === p2) {
			matchMessage.innerHTML = '<span class="text-success fw-bold"><i class="bi bi-check-circle-fill me-1"></i> As senhas coincidem perfeitamente.</span>';
		} else {
			matchMessage.innerHTML = '<span class="text-danger fw-semibold"><i class="bi bi-x-circle-fill me-1"></i> As senhas digitadas não coincidem.</span>';
		}
	}

	passInput?.addEventListener('input', function () {
		var res = checkPasswordStrength(this.value);
		meterFill.style.width = res.width;
		meterFill.style.backgroundColor = res.color;
		meterLabel.textContent = res.text;
		meterLabel.style.color = res.color;
		validateMatch();
	});

	rePassInput?.addEventListener('input', validateMatch);

	// ========================================================
	// 4. PREVIEW E DROPZONE DA FOTO DE PERFIL
	// ========================================================
	var photoInput = document.getElementById('photoInput');
	var dropzone = document.getElementById('avatarDropzone');
	var previewImg = document.getElementById('avatarPreviewImg');
	var fileNameDisplay = document.getElementById('avatarFileNameDisplay');
	var btnCancelPhoto = document.getElementById('btnCancelPhoto');
	var originalSrc = previewImg ? previewImg.src : '';

	dropzone?.addEventListener('click', function () {
		photoInput?.click();
	});

	dropzone?.addEventListener('dragover', function (e) {
		e.preventDefault();
		dropzone.style.borderColor = '#0a6b74';
		dropzone.style.backgroundColor = 'rgba(10, 107, 116, 0.1)';
	});

	dropzone?.addEventListener('dragleave', function () {
		dropzone.style.borderColor = '';
		dropzone.style.backgroundColor = '';
	});

	dropzone?.addEventListener('drop', function (e) {
		e.preventDefault();
		dropzone.style.borderColor = '';
		dropzone.style.backgroundColor = '';
		if (e.dataTransfer.files && e.dataTransfer.files.length > 0) {
			photoInput.files = e.dataTransfer.files;
			handleFileSelect(e.dataTransfer.files[0]);
		}
	});

	photoInput?.addEventListener('change', function () {
		if (this.files && this.files[0]) {
			handleFileSelect(this.files[0]);
		}
	});

	function handleFileSelect(file) {
		if (!file) return;
		var reader = new FileReader();
		reader.onload = function (e) {
			if (previewImg) previewImg.src = e.target.result;
			if (fileNameDisplay) fileNameDisplay.innerHTML = '<strong class="text-primary">' + file.name + '</strong> (' + (file.size / 1024).toFixed(1) + ' KB)';
			if (btnCancelPhoto) btnCancelPhoto.style.display = 'inline-flex';
		};
		reader.readAsDataURL(file);
	}

	btnCancelPhoto?.addEventListener('click', function () {
		if (photoInput) photoInput.value = '';
		if (previewImg) previewImg.src = originalSrc;
		if (fileNameDisplay) fileNameDisplay.innerHTML = 'Foto atual gravada';
		this.style.display = 'none';
	});

	// ========================================================
	// 5. SUBMISSÃO ASSÍNCRONA AJAX COM SWEETALERT2 & TOASTR
	// ========================================================
	
	// A. FORM 1 - DADOS PESSOAIS
	document.getElementById('formProfileInfo')?.addEventListener('submit', function (e) {
		e.preventDefault();
		var form = this;
		var btn = document.getElementById('btnSubmitInfo');
		var origHtml = btn.innerHTML;
		btn.disabled = true;
		btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status"></span> Salvando...';

		var formData = new FormData(form);
		formData.append('ajax', '1');

		fetch('profile-edit.php', {
			method: 'POST',
			body: formData,
			headers: { 'X-Requested-With': 'XMLHttpRequest' }
		})
		.then(function (res) { return res.json(); })
		.then(function (data) {
			btn.disabled = false;
			btn.innerHTML = origHtml;

			if (data.success) {
				if (typeof toastr !== 'undefined') toastr.success(data.message, 'Pronto Saúde');
				if (typeof Swal !== 'undefined') {
					Swal.fire({
						icon: 'success',
						title: 'Informações Atualizadas!',
						text: data.message,
						confirmButtonColor: '#0a6b74'
					});
				}
				// Atualiza textos visíveis na tela em tempo real
				if (data.full_name) {
					document.querySelectorAll('.user-live-fullname').forEach(function(el) { el.textContent = data.full_name; });
				}
				if (data.phone) {
					document.querySelectorAll('.user-live-phone').forEach(function(el) { el.textContent = data.phone; });
				}
				if (data.email) {
					document.querySelectorAll('.user-live-email').forEach(function(el) { el.textContent = data.email; });
				}
			} else {
				if (typeof toastr !== 'undefined') toastr.error(data.message.replace(/<br>/g, ' '), 'Atenção');
				if (typeof Swal !== 'undefined') {
					Swal.fire({
						icon: 'error',
						title: 'Não foi possível salvar',
						html: data.message,
						confirmButtonColor: '#0a6b74'
					});
				}
			}
		})
		.catch(function () {
			btn.disabled = false;
			btn.innerHTML = origHtml;
			form.submit();
		});
	});

	// B. FORM 2 - FOTO DE PERFIL
	document.getElementById('formProfilePhoto')?.addEventListener('submit', function (e) {
		e.preventDefault();
		var form = this;
		var btn = document.getElementById('btnSubmitPhoto');
		var origHtml = btn.innerHTML;

		if (!photoInput || !photoInput.files || photoInput.files.length === 0) {
			if (typeof Swal !== 'undefined') {
				Swal.fire({
					icon: 'warning',
					title: 'Selecione uma Imagem',
					text: 'Por favor, escolha um arquivo de imagem para atualizar.',
					confirmButtonColor: '#0a6b74'
				});
			}
			return;
		}

		btn.disabled = true;
		btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status"></span> Enviando Foto...';

		var formData = new FormData(form);
		formData.append('ajax', '1');

		fetch('profile-edit.php', {
			method: 'POST',
			body: formData,
			headers: { 'X-Requested-With': 'XMLHttpRequest' }
		})
		.then(function (res) { return res.json(); })
		.then(function (data) {
			btn.disabled = false;
			btn.innerHTML = origHtml;

			if (data.success) {
				if (typeof toastr !== 'undefined') toastr.success(data.message, 'Pronto Saúde');
				if (typeof Swal !== 'undefined') {
					Swal.fire({
						icon: 'success',
						title: 'Foto Atualizada!',
						text: data.message,
						confirmButtonColor: '#0a6b74'
					});
				}
				// Atualiza avatares do painel sem reload
				if (data.photo_url) {
					document.querySelectorAll('.user-live-avatar').forEach(function(img) {
						img.src = data.photo_url + '?v=' + Date.now();
					});
					originalSrc = data.photo_url;
				}
				btnCancelPhoto.style.display = 'none';
			} else {
				if (typeof toastr !== 'undefined') toastr.error(data.message.replace(/<br>/g, ' '), 'Atenção');
				if (typeof Swal !== 'undefined') {
					Swal.fire({
						icon: 'error',
						title: 'Erro no Upload',
						html: data.message,
						confirmButtonColor: '#0a6b74'
					});
				}
			}
		})
		.catch(function () {
			btn.disabled = false;
			btn.innerHTML = origHtml;
			form.submit();
		});
	});

	// C. FORM 3 - SEGURANÇA E SENHA
	document.getElementById('formProfilePassword')?.addEventListener('submit', function (e) {
		e.preventDefault();
		var form = this;
		var btn = document.getElementById('btnSubmitPassword');
		var origHtml = btn.innerHTML;

		var p1 = passInput ? passInput.value.trim() : '';
		var p2 = rePassInput ? rePassInput.value.trim() : '';

		if (!p1 || !p2) {
			if (typeof Swal !== 'undefined') {
				Swal.fire({
					icon: 'warning',
					title: 'Preencha os Campos',
					text: 'A nova senha e a confirmação são obrigatórias.',
					confirmButtonColor: '#0a6b74'
				});
			}
			return;
		}

		if (p1.length < 6) {
			if (typeof Swal !== 'undefined') {
				Swal.fire({
					icon: 'warning',
					title: 'Senha Curta',
					text: 'A nova senha deve possuir pelo menos 6 caracteres.',
					confirmButtonColor: '#0a6b74'
				});
			}
			return;
		}

		if (p1 !== p2) {
			if (typeof Swal !== 'undefined') {
				Swal.fire({
					icon: 'error',
					title: 'Senhas Diferentes',
					text: 'As senhas digitadas não coincidem. Digite novamente.',
					confirmButtonColor: '#0a6b74'
				});
			}
			return;
		}

		btn.disabled = true;
		btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status"></span> Atualizando Senha...';

		var formData = new FormData(form);
		formData.append('ajax', '1');

		fetch('profile-edit.php', {
			method: 'POST',
			body: formData,
			headers: { 'X-Requested-With': 'XMLHttpRequest' }
		})
		.then(function (res) { return res.json(); })
		.then(function (data) {
			btn.disabled = false;
			btn.innerHTML = origHtml;

			if (data.success) {
				if (typeof toastr !== 'undefined') toastr.success(data.message, 'Pronto Saúde');
				if (typeof Swal !== 'undefined') {
					Swal.fire({
						icon: 'success',
						title: 'Senha Atualizada com Sucesso!',
						text: data.message,
						confirmButtonColor: '#0a6b74'
					});
				}
				// Limpa os campos de senha e medidor
				form.reset();
				if (meterFill) meterFill.style.width = '0%';
				if (meterLabel) {
					meterLabel.textContent = 'Mínimo 6 dígitos';
					meterLabel.style.color = '#64748b';
				}
				if (matchMessage) matchMessage.innerHTML = '';
			} else {
				if (typeof toastr !== 'undefined') toastr.error(data.message.replace(/<br>/g, ' '), 'Atenção');
				if (typeof Swal !== 'undefined') {
					Swal.fire({
						icon: 'error',
						title: 'Erro na Atualização',
						html: data.message,
						confirmButtonColor: '#0a6b74'
					});
				}
			}
		})
		.catch(function () {
			btn.disabled = false;
			btn.innerHTML = origHtml;
			form.submit();
		});
	});

});
</script>

<?php require_once('footer.php'); ?>