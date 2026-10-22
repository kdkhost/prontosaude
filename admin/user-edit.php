<?php require_once('header.php'); ?>

<?php
$error_message = '';
$success_message = '';
$active_tab = 'tab_1';

if(isset($_POST['form1'])) {
	$active_tab = 'tab_1';
	$valid = 1;

    if(empty($_POST['full_name'])) {
        $valid = 0;
        $error_message .= "Name can not be empty<br>";
    }

    if(empty($_POST['email'])) {
        $valid = 0;
        $error_message .= 'Email address can not be empty<br>';
    } else {
    	if (filter_var($_POST['email'], FILTER_VALIDATE_EMAIL) === false) {
	        $valid = 0;
	        $error_message .= 'Email address must be valid<br>';
	    } else {
	    	// current email address that is in the database
	    	$statement = $pdo->prepare("SELECT * FROM tbl_user WHERE id=?");
			$statement->execute(array($_REQUEST['id']));
			$result = $statement->fetchAll(PDO::FETCH_ASSOC);
			foreach($result as $row) {
				$current_email = $row['email'];
			}

	    	$statement = $pdo->prepare("SELECT * FROM tbl_user WHERE email=? and email!=?");
	    	$statement->execute(array($_POST['email'],$current_email));
	    	$total = $statement->rowCount();							
	    	if($total) {
	    		$valid = 0;
	        	$error_message .= 'Email address already exists<br>';
	    	}
	    }
    }

    if($valid == 1) {

		// updating the database
		$statement = $pdo->prepare("UPDATE tbl_user SET full_name=?, email=?, phone=?, role=?, status=? WHERE id=?");
		$statement->execute(array($_POST['full_name'],$_POST['email'],$_POST['phone'],$_POST['role'],$_POST['status'],$_REQUEST['id']));

    	$success_message = 'User Information is updated successfully.';
    }
}


if(isset($_POST['form2'])) {
	$active_tab = 'tab_2';
	$valid = 1;

	$path = $_FILES['photo']['name'];
    $path_tmp = $_FILES['photo']['tmp_name'];

    if($path!='') {
        $ext = pathinfo( $path, PATHINFO_EXTENSION );
        $file_name = basename( $path, '.' . $ext );
        if( $ext!='jpg' && $ext!='png' && $ext!='jpeg' && $ext!='gif' ) {
            $valid = 0;
            $error_message .= 'You must have to upload jpg, jpeg, gif or png file<br>';
        }
    }

    if($valid == 1) {
    	// removing the existing photo
    	$statement = $pdo->prepare("SELECT * FROM tbl_user WHERE id=?");
    	$statement->execute(array($_REQUEST['id']));
    	$result = $statement->fetchAll(PDO::FETCH_ASSOC);							
    	foreach ($result as $row) {
    		$photo = $row['photo'];
    	}
    	if($photo!='') {
    		unlink('../assets/uploads/'.$photo);	
    	}

    	// updating the data
    	$final_name = 'user-'.$_REQUEST['id'].'.'.$ext;
        move_uploaded_file( $path_tmp, '../assets/uploads/'.$final_name );

        // updating the database
		$statement = $pdo->prepare("UPDATE tbl_user SET photo=? WHERE id=?");
		$statement->execute(array($final_name,$_REQUEST['id']));

        $success_message = 'User Photo is updated successfully.';
    	
    }
}

if(isset($_POST['form3'])) {
	$active_tab = 'tab_3';
	$valid = 1;

	if( empty($_POST['password']) || empty($_POST['re_password']) ) {
        $valid = 0;
        $error_message .= "Password can not be empty<br>";
    }

    if( !empty($_POST['password']) && !empty($_POST['re_password']) ) {
    	if($_POST['password'] != $_POST['re_password']) {
	    	$valid = 0;
	        $error_message .= "Passwords do not match<br>";	
    	}        
    }

    if($valid == 1) {

    	// updating the database
		$statement = $pdo->prepare("UPDATE tbl_user SET password=? WHERE id=?");
		$statement->execute(array(md5($_POST['password']),$_REQUEST['id']));

    	$success_message = 'User Password is updated successfully.';
    }
}



?>

<?php
if(!isset($_REQUEST['id'])) {
	header('location: logout.php');
	exit;
} else {
	// Check the id is valid or not
	$statement = $pdo->prepare("SELECT * FROM tbl_user WHERE id=?");
	$statement->execute(array($_REQUEST['id']));
	$total = $statement->rowCount();
	$result = $statement->fetchAll(PDO::FETCH_ASSOC);
	if( $total == 0 || $_REQUEST['id'] == 1 ) {
		header('location: logout.php');
		exit;
	}
}
?>

<div class="app-content-header">
	<div class="container-fluid">
		<div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
			<div>
				<h3 class="mb-1 fw-bold">Editar Usuário</h3>
				<ol class="breadcrumb mb-0 small">
					<li class="breadcrumb-item"><a href="index.php"><i class="bi bi-house-door"></i> Início</a></li>
					<li class="breadcrumb-item active" aria-current="page">Editar Usuário</li>
				</ol>
			</div>
			<div class="d-flex flex-wrap align-items-center gap-2">
				<a href="user.php" class="btn btn-outline-secondary btn-sm"><i class="bi bi-list-ul"></i> Ver Listagem</a>
			</div>
		</div>
	</div>
</div>


<?php
$statement = $pdo->prepare("SELECT * FROM tbl_user WHERE id=?");
$statement->execute(array($_REQUEST['id']));
$statement->rowCount();
$result = $statement->fetchAll(PDO::FETCH_ASSOC);							
foreach ($result as $row) {
	$full_name = $row['full_name'];
	$email     = $row['email'];
	$phone     = $row['phone'];
	$photo     = $row['photo'];
	$status    = $row['status'];
	$role      = $row['role'];
}
?>


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
				<div class="card card-primary card-outline card-outline-tabs shadow-sm mb-4">
					<div class="card-header p-0 border-bottom-0">
						<ul class="nav nav-tabs" id="userEditTabs" role="tablist">
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
						<div class="tab-content" id="userEditTabsContent">
							
							<!-- ABA 1: ATUALIZAR INFORMAÇÕES -->
							<div class="tab-pane fade <?= ($active_tab == 'tab_1') ? 'show active' : ''; ?>" id="tab_1" role="tabpanel" aria-labelledby="tab-info-tab">
								<form action="" method="post">
									<div class="row mb-3">
										<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Nome <span class="text-danger">*</span></label>
										<div class="col-12 col-sm-9 col-lg-6">
											<input type="text" class="form-control" name="full_name" value="<?= htmlspecialchars($full_name); ?>" required>
										</div>
									</div>
									<div class="row mb-3">
										<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Foto Atual</label>
										<div class="col-12 col-sm-9 col-lg-6">
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
										<div class="col-12 col-sm-9 col-lg-6">
											<input type="email" class="form-control" name="email" value="<?= htmlspecialchars($email); ?>" required>
										</div>
									</div>
									<div class="row mb-3">
										<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Telefone</label>
										<div class="col-12 col-sm-9 col-lg-6">
											<input type="text" class="form-control" name="phone" value="<?= htmlspecialchars($phone); ?>">
										</div>
									</div>
									<div class="row mb-3">
										<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Permissão <span class="text-danger">*</span></label>
										<div class="col-12 col-sm-9 col-lg-6">
											<select name="role" class="form-select">
												<option value="Admin" <?= ($role == 'Admin') ? 'selected' : ''; ?>>Administrador</option>
												<option value="Publisher" <?= ($role == 'Publisher') ? 'selected' : ''; ?>>Editor / Publicador</option>
											</select>
										</div>
									</div>
									<div class="row mb-3">
										<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Status <span class="text-danger">*</span></label>
										<div class="col-12 col-sm-9 col-lg-6">
											<div class="form-check form-check-inline">
												<input class="form-check-input" type="radio" name="status" id="status_active" value="Active" <?= ($status == 'Active') ? 'checked' : ''; ?>>
												<label class="form-check-label" for="status_active">Ativo</label>
											</div>
											<div class="form-check form-check-inline">
												<input class="form-check-input" type="radio" name="status" id="status_inactive" value="Inactive" <?= ($status == 'Inactive') ? 'checked' : ''; ?>>
												<label class="form-check-label" for="status_inactive">Inativo</label>
											</div>
										</div>
									</div>
									<div class="row mb-3">
										<div class="col-sm-3 col-lg-2 d-none d-sm-block"></div>
										<div class="col-12 col-sm-9 col-lg-6 d-flex gap-2">
											<button type="submit" class="btn btn-primary px-4 shadow-sm" name="form1">
												<i class="bi bi-check-lg me-1"></i> Salvar Informações
											</button>
											<a href="user.php" class="btn btn-outline-secondary">Cancelar</a>
										</div>
									</div>
								</form>
							</div>

							<!-- ABA 2: ATUALIZAR FOTO -->
							<div class="tab-pane fade <?= ($active_tab == 'tab_2') ? 'show active' : ''; ?>" id="tab_2" role="tabpanel" aria-labelledby="tab-photo-tab">
								<form action="" method="post" enctype="multipart/form-data">
									<div class="row mb-3">
										<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Foto Atual</label>
										<div class="col-12 col-sm-9 col-lg-6">
											<?php if(!empty($photo) && file_exists('../assets/uploads/' . $photo)): ?>
												<img src="../assets/uploads/<?= htmlspecialchars($photo); ?>" class="rounded-circle border shadow-sm object-fit-cover mb-2" width="100" height="100" alt="Foto">
											<?php else: ?>
												<div class="d-inline-flex align-items-center justify-content-center bg-secondary-subtle text-secondary rounded-circle border shadow-sm mb-2" style="width:100px;height:100px;">
													<i class="bi bi-person-fill fs-1"></i>
												</div>
											<?php endif; ?>
										</div>
									</div>
									<div class="row mb-3">
										<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Nova Foto <span class="text-danger">*</span></label>
										<div class="col-12 col-sm-9 col-lg-6">
											<input type="file" name="photo" class="form-control" accept="image/*" required>
											<div class="form-text text-muted small mt-1">Formatos aceitos: JPG, PNG, GIF ou WEBP.</div>
										</div>
									</div>
									<div class="row mb-3">
										<div class="col-sm-3 col-lg-2 d-none d-sm-block"></div>
										<div class="col-12 col-sm-9 col-lg-6">
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
										<div class="col-12 col-sm-9 col-lg-6">
											<input type="password" class="form-control" name="password" placeholder="Digite a nova senha" required autocomplete="new-password">
										</div>
									</div>
									<div class="row mb-3">
										<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Confirmar Senha <span class="text-danger">*</span></label>
										<div class="col-12 col-sm-9 col-lg-6">
											<input type="password" class="form-control" name="re_password" placeholder="Digite a senha novamente" required autocomplete="new-password">
										</div>
									</div>
									<div class="row mb-3">
										<div class="col-sm-3 col-lg-2 d-none d-sm-block"></div>
										<div class="col-12 col-sm-9 col-lg-6">
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