<?php require_once('header.php'); ?>

<?php
if(isset($_POST['form1'])) {

	if($_SESSION['user']['role'] == 'Super Admin') {

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
				$statement->execute(array($_SESSION['user']['id']));
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
			
			$_SESSION['user']['full_name'] = $_POST['full_name'];
	    	$_SESSION['user']['email'] = $_POST['email'];

			// updating the database
			$statement = $pdo->prepare("UPDATE tbl_user SET full_name=?, email=?, phone=? WHERE id=?");
			$statement->execute(array($_POST['full_name'],$_POST['email'],$_POST['phone'],$_SESSION['user']['id']));

	    	$success_message = 'User Information is updated successfully.';
	    }
	}
	else {
		$_SESSION['user']['phone'] = $_POST['phone'];

		// updating the database
		$statement = $pdo->prepare("UPDATE tbl_user SET phone=? WHERE id=?");
		$statement->execute(array($_POST['phone'],$_SESSION['user']['id']));

		$success_message = 'User Information is updated successfully.';	
	}
}

if(isset($_POST['form2'])) {

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
    	if($_SESSION['user']['photo']!='') {
    		unlink('../assets/uploads/'.$_SESSION['user']['photo']);	
    	}

    	// updating the data
    	$final_name = 'user-'.$_SESSION['user']['id'].'.'.$ext;
        move_uploaded_file( $path_tmp, '../assets/uploads/'.$final_name );
        $_SESSION['user']['photo'] = $final_name;

        // updating the database
		$statement = $pdo->prepare("UPDATE tbl_user SET photo=? WHERE id=?");
		$statement->execute(array($final_name,$_SESSION['user']['id']));

        $success_message = 'User Photo is updated successfully.';
    	
    }
}

if(isset($_POST['form3'])) {
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

    	$_SESSION['user']['password'] = md5($_POST['password']);

    	// updating the database
		$statement = $pdo->prepare("UPDATE tbl_user SET password=? WHERE id=?");
		$statement->execute(array(md5($_POST['password']),$_SESSION['user']['id']));

    	$success_message = 'User Password is updated successfully.';
    }
}
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

<?php
$statement = $pdo->prepare("SELECT * FROM tbl_user WHERE id=?");
$statement->execute(array($_SESSION['user']['id']));
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

	<div class="row">
		<div class="col-md-12">
				
				<div class="nav-tabs-custom mb-4">
					<ul class="nav nav-tabs">
						<li class="nav-item"><a class="nav-link active" href="#tab_1" data-bs-toggle="tab">Atualizar Informações</a></li>
						<li class="nav-item"><a class="nav-link" href="#tab_2" data-bs-toggle="tab">Atualizar Foto</a></li>
						<li class="nav-item"><a class="nav-link" href="#tab_3" data-bs-toggle="tab">Atualizar Senha</a></li>
					</ul>
					<div class="tab-content">
          				<div class="tab-pane active" id="tab_1">
							
							<form action="" method="post">
							<div class="card card-outline card-primary shadow-sm mb-4">
								<div class="card-body">
									<div class="row mb-3">
										<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Nome <span class="text-danger">*</span></label>
										<?php if($_SESSION['user']['role'] == 'Super Admin' || $_SESSION['user']['role'] == 'Admin') { ?>
												<div class="col-sm-4">
													<input type="text" class="form-control" name="full_name" value="<?= $full_name; ?>">
												</div>
											<?php } else { ?>
												<div class="col-sm-4" style="padding-top:7px;">
													<?= $full_name; ?>
												</div>
											<?php } ?>
										
									</div>
									<div class="row mb-3">
							            <label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Foto Atual</label>
							            <div class="col-12 col-sm-9 col-lg-6">
							                <img src="../assets/uploads/<?= $photo; ?>" class="existing-photo" width="140">
							            </div>
							        </div>
									
									<div class="row mb-3">
										<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Email <span class="text-danger">*</span></label>
										<?php
										if($_SESSION['user']['role'] == 'Super Admin') {
											?>
												<div class="col-sm-4">
													<input type="email" class="form-control" name="email" value="<?= $email; ?>">
												</div>
											<?php
										} else {
											?>
											<div class="col-sm-4" style="padding-top:7px;">
												<?= $email; ?>
											</div>
											<?php
										}
										?>
										
									</div>
									<div class="row mb-3">
										<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Telefone</label>
										<div class="col-12 col-sm-9 col-lg-6">
											<input type="text" class="form-control" name="phone" value="<?= $phone; ?>">
										</div>
									</div>
									<div class="row mb-3">
										<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Permissão <span class="text-danger">*</span></label>
										<div class="col-12 col-sm-9 col-lg-6">
											<?php if($role == "Admin"){echo "Administrador";}elseif($role == "Super Admin"){echo "Super-Admin";}else{echo "Editor";} ?>
										</div>
									</div>
									<div class="row mb-3">
										<div class="col-sm-3 col-lg-2 d-none d-sm-block"></div>
										<div class="col-12 col-sm-9 col-lg-6">
											<button type="submit" class="btn btn-primary px-4" name="form1"><i class="bi bi-check-lg me-1"></i>Atualizar Informações</button>
										</div>
									</div>
								</div>
							</div>
							</form>
          				</div>
          				<div class="tab-pane" id="tab_2">
							<form action="" method="post" enctype="multipart/form-data">
							<div class="card card-outline card-primary shadow-sm mb-4">
								<div class="card-body">
									<div class="row mb-3">
							            <label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Nova Foto</label>
							            <div class="col-12 col-sm-9 col-lg-6">
							                <input type="file" name="photo">
							            </div>
							        </div>
							        <div class="row mb-3">
										<div class="col-sm-3 col-lg-2 d-none d-sm-block"></div>
										<div class="col-12 col-sm-9 col-lg-6">
											<button type="submit" class="btn btn-primary px-4" name="form2"><i class="bi bi-check-lg me-1"></i>Atualizar Foto</button>
										</div>
									</div>
								</div>
							</div>
							</form>
          				</div>
          				<div class="tab-pane" id="tab_3">
							<form action="" method="post">
							<div class="card card-outline card-primary shadow-sm mb-4">
								<div class="card-body">
									<div class="row mb-3">
										<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Senha</label>
										<div class="col-12 col-sm-9 col-lg-6">
											<input type="password" class="form-control" name="password">
										</div>
									</div>
									<div class="row mb-3">
										<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Repita a Senha</label>
										<div class="col-12 col-sm-9 col-lg-6">
											<input type="password" class="form-control" name="re_password">
										</div>
									</div>
							        <div class="row mb-3">
										<div class="col-sm-3 col-lg-2 d-none d-sm-block"></div>
										<div class="col-12 col-sm-9 col-lg-6">
											<button type="submit" class="btn btn-primary px-4" name="form3"><i class="bi bi-check-lg me-1"></i>Atualizar Senha</button>
										</div>
									</div>
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

<?php require_once('footer.php'); ?>