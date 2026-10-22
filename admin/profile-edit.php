<?php
require_once('header.php');

$error_message = '';
$success_message = '';
$active_tab = 'tab_1';

if(isset($_POST['form1'])) {
	$active_tab = 'tab_1';

	if($_SESSION['user']['role'] == 'Super Admin' || $_SESSION['user']['role'] == 'Admin') {
		$valid = 1;

	    if(empty($_POST['full_name'])) {
	        $valid = 0;
	        $error_message .= "O nome não pode estar vazio.<br>";
	    }

	    if(empty($_POST['email'])) {
	        $valid = 0;
	        $error_message .= 'O endereço de e-mail não pode estar vazio.<br>';
	    } else {
	    	if (filter_var($_POST['email'], FILTER_VALIDATE_EMAIL) === false) {
		        $valid = 0;
		        $error_message .= 'O endereço de e-mail informado não é válido.<br>';
		    } else {
		    	// Email atual gravado no banco de dados
		    	$statement = $pdo->prepare("SELECT email FROM tbl_user WHERE id=?");
				$statement->execute(array($_SESSION['user']['id']));
				$current_user = $statement->fetch(PDO::FETCH_ASSOC);
				$current_email = $current_user['email'] ?? '';

		    	$statement = $pdo->prepare("SELECT id FROM tbl_user WHERE email=? AND email!=?");
		    	$statement->execute(array($_POST['email'], $current_email));
		    	$total = $statement->rowCount();							
		    	if($total) {
		    		$valid = 0;
		        	$error_message .= 'Este endereço de e-mail já está em uso por outro usuário.<br>';
		    	}
		    }
	    }

	    if($valid == 1) {
			$_SESSION['user']['full_name'] = $_POST['full_name'];
	    	$_SESSION['user']['email'] = $_POST['email'];
	    	$_SESSION['user']['phone'] = $_POST['phone'] ?? '';

			// Atualiza no banco de dados
			$statement = $pdo->prepare("UPDATE tbl_user SET full_name=?, email=?, phone=? WHERE id=?");
			$statement->execute(array($_POST['full_name'], $_POST['email'], $_POST['phone'], $_SESSION['user']['id']));

	    	$success_message = 'Informações do perfil atualizadas com sucesso.';
	    }
	}
	else {
		$_SESSION['user']['phone'] = $_POST['phone'] ?? '';

		// Atualiza no banco de dados
		$statement = $pdo->prepare("UPDATE tbl_user SET phone=? WHERE id=?");
		$statement->execute(array($_POST['phone'], $_SESSION['user']['id']));

		$success_message = 'Informações de contato atualizadas com sucesso.';	
	}
}

if(isset($_POST['form2'])) {
	$active_tab = 'tab_2';
	$valid = 1;

	$path = $_FILES['photo']['name'] ?? '';
    $path_tmp = $_FILES['photo']['tmp_name'] ?? '';

    if(!empty($path)) {
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        if(!in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
            $valid = 0;
            $error_message .= 'A foto deve estar nos formatos JPG, JPEG, PNG, GIF ou WEBP.<br>';
        }
    } else {
        $valid = 0;
        $error_message .= 'Por favor, selecione um arquivo de foto para enviar.<br>';
    }

    if($valid == 1) {
    	// Remove foto antiga se existir e for arquivo físico
    	if(!empty($_SESSION['user']['photo']) && file_exists('../assets/uploads/' . $_SESSION['user']['photo'])) {
    		@unlink('../assets/uploads/' . $_SESSION['user']['photo']);	
    	}

    	// Grava o novo arquivo
    	$final_name = 'user-' . $_SESSION['user']['id'] . '-' . time() . '.' . $ext;
        move_uploaded_file($path_tmp, '../assets/uploads/' . $final_name);
        $_SESSION['user']['photo'] = $final_name;

        // Atualiza banco de dados
		$statement = $pdo->prepare("UPDATE tbl_user SET photo=? WHERE id=?");
		$statement->execute(array($final_name, $_SESSION['user']['id']));

        $success_message = 'Foto de perfil atualizada com sucesso.';
    }
}

if(isset($_POST['form3'])) {
	$active_tab = 'tab_3';
	$valid = 1;

	if(empty($_POST['password']) || empty($_POST['re_password'])) {
        $valid = 0;
        $error_message .= "A nova senha e a confirmação não podem estar vazias.<br>";
    }

    if(!empty($_POST['password']) && !empty($_POST['re_password'])) {
    	if($_POST['password'] !== $_POST['re_password']) {
	    	$valid = 0;
	        $error_message .= "As senhas não coincidem. Digite-as novamente.<br>";	
    	}        
    }

    if($valid == 1) {
    	$_SESSION['user']['password'] = md5($_POST['password']);

    	// Atualiza banco de dados
		$statement = $pdo->prepare("UPDATE tbl_user SET password=? WHERE id=?");
		$statement->execute(array(md5($_POST['password']), $_SESSION['user']['id']));

    	$success_message = 'Senha de acesso alterada com sucesso.';
    }
}

// Carrega os dados mais recentes do usuário logado
$statement = $pdo->prepare("SELECT * FROM tbl_user WHERE id=?");
$statement->execute(array($_SESSION['user']['id']));
$user_data = $statement->fetch(PDO::FETCH_ASSOC) ?: [];

$full_name = $user_data['full_name'] ?? '';
$email     = $user_data['email'] ?? '';
$phone     = $user_data['phone'] ?? '';
$photo     = $user_data['photo'] ?? '';
$status    = $user_data['status'] ?? '';
$role      = $user_data['role'] ?? '';
?>

<div class="app-content-header">
	<div class="container-fluid">
		<div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
			<div>
				<h3 class="mb-1 fw-bold">Editar Perfil</h3>
				<ol class="breadcrumb mb-0 small">
					<li class="breadcrumb-item"><a href="index.php"><i class="bi bi-house-door"></i> Início</a></li>
					<li class="breadcrumb-item active" aria-current="page">Editar Perfil</li>
				</ol>
			</div>
		</div>
	</div>
</div>

<div class="app-content">
	<div class="container-fluid">

		<?php if(!empty($error_message)): ?>
		<div class="callout callout-danger shadow-sm mb-4">
			<div class="d-flex align-items-center">
				<i class="bi bi-exclamation-triangle-fill text-danger fs-4 me-2"></i>
				<div><?= $error_message; ?></div>
			</div>
		</div>
		<?php endif; ?>

		<?php if(!empty($success_message)): ?>
		<div class="callout callout-success shadow-sm mb-4">
			<div class="d-flex align-items-center">
				<i class="bi bi-check-circle-fill text-success fs-4 me-2"></i>
				<div><?= $success_message; ?></div>
			</div>
		</div>
		<?php endif; ?>

		<div class="row">
			<div class="col-md-12">
				
				<!-- Card com Abas no Padrão Oficial AdminLTE 4 -->
				<div class="card card-primary card-outline card-outline-tabs shadow-sm mb-4">
					<div class="card-header p-0 border-bottom-0">
						<ul class="nav nav-tabs" id="profileTabs" role="tablist">
							<li class="nav-item" role="presentation">
								<button class="nav-link <?= ($active_tab == 'tab_1') ? 'active' : ''; ?>" id="tab-info-tab" data-bs-toggle="tab" data-bs-target="#tab_1" type="button" role="tab" aria-controls="tab_1" aria-selected="<?= ($active_tab == 'tab_1') ? 'true' : 'false'; ?>">
									<i class="bi bi-person-gear me-1"></i> Atualizar Informações
								</button>
							</li>
							<li class="nav-item" role="presentation">
								<button class="nav-link <?= ($active_tab == 'tab_2') ? 'active' : ''; ?>" id="tab-photo-tab" data-bs-toggle="tab" data-bs-target="#tab_2" type="button" role="tab" aria-controls="tab_2" aria-selected="<?= ($active_tab == 'tab_2') ? 'true' : 'false'; ?>">
									<i class="bi bi-image me-1"></i> Atualizar Foto
								</button>
							</li>
							<li class="nav-item" role="presentation">
								<button class="nav-link <?= ($active_tab == 'tab_3') ? 'active' : ''; ?>" id="tab-pass-tab" data-bs-toggle="tab" data-bs-target="#tab_3" type="button" role="tab" aria-controls="tab_3" aria-selected="<?= ($active_tab == 'tab_3') ? 'true' : 'false'; ?>">
									<i class="bi bi-key me-1"></i> Atualizar Senha
								</button>
							</li>
						</ul>
					</div>
					
					<div class="card-body">
						<div class="tab-content" id="profileTabsContent">
							
							<!-- ABA 1: ATUALIZAR INFORMAÇÕES -->
	          				<div class="tab-pane fade <?= ($active_tab == 'tab_1') ? 'show active' : ''; ?>" id="tab_1" role="tabpanel" aria-labelledby="tab-info-tab">
								<form action="" method="post">
									<div class="row mb-3">
										<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Nome <span class="text-danger">*</span></label>
										<?php if($_SESSION['user']['role'] == 'Super Admin' || $_SESSION['user']['role'] == 'Admin') { ?>
											<div class="col-sm-8 col-lg-6">
												<input type="text" class="form-control" name="full_name" value="<?= htmlspecialchars($full_name); ?>" required>
											</div>
										<?php } else { ?>
											<div class="col-sm-8 col-lg-6 pt-2 fw-bold text-secondary">
												<?= htmlspecialchars($full_name); ?>
											</div>
										<?php } ?>
									</div>

									<div class="row mb-3">
							            <label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Foto Atual</label>
							            <div class="col-sm-8 col-lg-6">
											<?php if(!empty($photo) && file_exists('../assets/uploads/' . $photo)): ?>
												<img src="../assets/uploads/<?= htmlspecialchars($photo); ?>" class="rounded-circle border shadow-sm object-fit-cover" width="90" height="90" alt="Foto">
											<?php else: ?>
												<div class="d-inline-flex align-items-center justify-content-center bg-secondary-subtle text-secondary rounded-circle border shadow-sm" style="width:90px;height:90px;">
													<i class="bi bi-person-fill fs-1"></i>
												</div>
											<?php endif; ?>
							            </div>
							        </div>
									
									<div class="row mb-3">
										<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">E-mail <span class="text-danger">*</span></label>
										<?php if($_SESSION['user']['role'] == 'Super Admin') { ?>
											<div class="col-sm-8 col-lg-6">
												<input type="email" class="form-control" name="email" value="<?= htmlspecialchars($email); ?>" required>
											</div>
										<?php } else { ?>
											<div class="col-sm-8 col-lg-6 pt-2 fw-semibold text-secondary">
												<?= htmlspecialchars($email); ?>
											</div>
										<?php } ?>
									</div>

									<div class="row mb-3">
										<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Telefone</label>
										<div class="col-sm-8 col-lg-6">
											<input type="text" class="form-control" name="phone" value="<?= htmlspecialchars($phone); ?>" placeholder="(00) 00000-0000">
										</div>
									</div>

									<div class="row mb-3">
										<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Perfil de Acesso</label>
										<div class="col-sm-8 col-lg-6 pt-2">
											<span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-2 fs-6">
												<i class="bi bi-shield-lock me-1"></i>
												<?php 
												if($role == "Admin") echo "Administrador";
												elseif($role == "Super Admin") echo "Super Administrador";
												else echo "Editor / Publicador"; 
												?>
											</span>
										</div>
									</div>

									<div class="row mb-3">
										<div class="col-sm-3 col-lg-2 d-none d-sm-block"></div>
										<div class="col-sm-8 col-lg-6">
											<button type="submit" class="btn btn-primary px-4 shadow-sm" name="form1">
												<i class="bi bi-check-lg me-1"></i> Salvar Informações
											</button>
										</div>
									</div>
								</form>
	          				</div>

							<!-- ABA 2: ATUALIZAR FOTO -->
	          				<div class="tab-pane fade <?= ($active_tab == 'tab_2') ? 'show active' : ''; ?>" id="tab_2" role="tabpanel" aria-labelledby="tab-photo-tab">
								<form action="" method="post" enctype="multipart/form-data">
									<div class="row mb-3">
							            <label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Foto Atual</label>
							            <div class="col-sm-8 col-lg-6">
											<?php if(!empty($photo) && file_exists('../assets/uploads/' . $photo)): ?>
												<img src="../assets/uploads/<?= htmlspecialchars($photo); ?>" class="rounded-circle border shadow-sm object-fit-cover mb-2" width="100" height="100" alt="Foto Atual">
											<?php else: ?>
												<div class="d-inline-flex align-items-center justify-content-center bg-secondary-subtle text-secondary rounded-circle border shadow-sm mb-2" style="width:100px;height:100px;">
													<i class="bi bi-person-fill fs-1"></i>
												</div>
											<?php endif; ?>
							            </div>
							        </div>

									<div class="row mb-3">
							            <label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Nova Foto <span class="text-danger">*</span></label>
							            <div class="col-sm-8 col-lg-6">
							                <input type="file" name="photo" class="form-control" accept="image/*" required>
											<div class="form-text text-muted small mt-1">Formatos aceitos: JPG, PNG, GIF ou WEBP.</div>
							            </div>
							        </div>

							        <div class="row mb-3">
										<div class="col-sm-3 col-lg-2 d-none d-sm-block"></div>
										<div class="col-sm-8 col-lg-6">
											<button type="submit" class="btn btn-primary px-4 shadow-sm" name="form2">
												<i class="bi bi-upload me-1"></i> Atualizar Foto
											</button>
										</div>
									</div>
								</form>
	          				</div>

							<!-- ABA 3: ATUALIZAR SENHA -->
	          				<div class="tab-pane fade <?= ($active_tab == 'tab_3') ? 'show active' : ''; ?>" id="tab_3" role="tabpanel" aria-labelledby="tab-pass-tab">
								<form action="" method="post">
									<div class="row mb-3">
										<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Nova Senha <span class="text-danger">*</span></label>
										<div class="col-sm-8 col-lg-6">
											<input type="password" class="form-control" name="password" placeholder="Digite a nova senha" required autocomplete="new-password">
										</div>
									</div>

									<div class="row mb-3">
										<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Confirmar Senha <span class="text-danger">*</span></label>
										<div class="col-sm-8 col-lg-6">
											<input type="password" class="form-control" name="re_password" placeholder="Digite a senha novamente" required autocomplete="new-password">
										</div>
									</div>

							        <div class="row mb-3">
										<div class="col-sm-3 col-lg-2 d-none d-sm-block"></div>
										<div class="col-sm-8 col-lg-6">
											<button type="submit" class="btn btn-primary px-4 shadow-sm" name="form3">
												<i class="bi bi-shield-check me-1"></i> Alterar Senha
											</button>
										</div>
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

<?php require_once('footer.php'); ?>