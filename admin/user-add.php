<?php require_once('header.php'); ?>

<?php
if (isset($_POST['form1'])) {

    $valid = 1;

    if(empty($_POST['full_name'])) {
        $valid = 0;
        $error_message .= "O nome não pode ficar vazio<br>";
    }

    if(empty($_POST['email'])) {
        $valid = 0;
        $error_message .= 'O endereço de e-mail não pode estar vazio<br>';
    } else {
    	if (filter_var($_POST['email'], FILTER_VALIDATE_EMAIL) === false) {
	        $valid = 0;
	        $error_message .= 'O endereço de e-mail deve ser válido<br>';
	    } else {
	    	$statement = $pdo->prepare("SELECT * FROM tbl_user WHERE email=?");
	    	$statement->execute(array($_POST['email']));
	    	$total = $statement->rowCount();							
	    	if($total) {
	    		$valid = 0;
	        	$error_message .= 'O endereço de e-mail já existe<br>';
	    	}
	    }
    }

    if( empty($_POST['password']) || empty($_POST['re_password']) ) {
        $valid = 0;
        $error_message .= "A senha não pode estar vazia<br>";
    }

    if( !empty($_POST['password']) && !empty($_POST['re_password']) ) {
    	if($_POST['password'] != $_POST['re_password']) {
	    	$valid = 0;
	        $error_message .= "As senhas não coincidem<br>";	
    	}        
    }

    $path = $_FILES['photo']['name'];
    $path_tmp = $_FILES['photo']['tmp_name'];

    if($path!='') {
        $ext = pathinfo( $path, PATHINFO_EXTENSION );
        $file_name = basename( $path, '.' . $ext );
        if( $ext!='jpg' && $ext!='png' && $ext!='jpeg' && $ext!='gif' ) {
            $valid = 0;
            $error_message .= 'Você deve fazer o upload de um arquivo jpg, jpeg, gif ou png<br>';
        }
    }

    if($valid == 1) {

    	if($path=='') {
    		// saving into the database
    		$statement = $pdo->prepare("INSERT INTO tbl_user (full_name,email,phone,password,photo,role,status) VALUES (?,?,?,?,?,?,?)");
    		$statement->execute(array($_POST['full_name'],$_POST['email'],$_POST['phone'],md5($_POST['password']),'',$_POST['role'],$_POST['status']));
    	} else {
    		// getting auto increment id for photo renaming
    		$statement = $pdo->prepare("SHOW TABLE STATUS LIKE 'tbl_user'");
    		$statement->execute();
    		$result = $statement->fetchAll();
    		foreach($result as $row) {
    			$ai_id=$row[10];
    		}

    		// uploading the photo into the main location and giving it a final name
    		$final_name = 'user-'.$ai_id.'.'.$ext;
            move_uploaded_file( $path_tmp, '../assets/uploads/'.$final_name );

            // saving into the database
    		$statement = $pdo->prepare("INSERT INTO tbl_user (full_name,email,phone,password,photo,role,status) VALUES (?,?,?,?,?,?,?)");
    		$statement->execute(array($_POST['full_name'],$_POST['email'],$_POST['phone'],md5($_POST['password']),$final_name,$_POST['role'],$_POST['status']));
    	}

    	unset($_POST['full_name']);
    	unset($_POST['email']);
    	unset($_POST['phone']);

    	$success_message = 'O usuário foi adicionado com sucesso.';
    }
}
?>

<div class="app-content-header">
	<div class="container-fluid">
		<div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
			<div>
				<h3 class="mb-1 fw-bold">Adicionar Usuário</h3>
				<ol class="breadcrumb mb-0 small">
					<li class="breadcrumb-item"><a href="index.php"><i class="bi bi-house-door"></i> Início</a></li>
					<li class="breadcrumb-item active" aria-current="page">Adicionar Usuário</li>
				</ol>
			</div>
			<div class="d-flex flex-wrap align-items-center gap-2">
				<a href="user.php" class="btn btn-outline-secondary btn-sm"><i class="bi bi-list-ul"></i> Ver Listagem</a>
			</div>
		</div>
	</div>
</div>


<div class="app-content">
<div class="container-fluid">

	<div class="row">
		<div class="col-md-12">

			<?php if($error_message): ?>
			<div class="callout callout-danger">
			
			<p>
			<?php echo $error_message; ?>
			</p>
			</div>
			<?php endif; ?>

			<?php if($success_message): ?>
			<div class="callout callout-success">
			
			<p><?php echo $success_message; ?></p>
			</div>
			<?php endif; ?>

			<form action="" method="post" autocomplete="off" enctype="multipart/form-data">
				<div class="card card-outline card-primary shadow-sm mb-4">
					<div class="card-body">
						<div class="row mb-3">
							<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Nome <span class="text-danger">*</span></label>
							<div class="col-12 col-sm-9 col-lg-6">
								<input type="text" class="form-control" name="full_name" value="<?php if(isset($_POST['full_name'])) {echo $_POST['full_name'];} ?>">
							</div>
						</div>
						
						<div class="row mb-3">
							<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Email <span class="text-danger">*</span></label>
							<div class="col-12 col-sm-9 col-lg-6">
								<input autocomplete="off" type="email" class="form-control" name="email" value="<?php if(isset($_POST['email'])) {echo $_POST['email'];} ?>">
							</div>
						</div>
						<div class="row mb-3">
							<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Telefone</label>
							<div class="col-12 col-sm-9 col-lg-6">
								<input type="text" class="form-control" name="phone" value="<?php if(isset($_POST['phone'])) {echo $_POST['phone'];} ?>">
							</div>
						</div>
						<div class="row mb-3">
							<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Senha <span class="text-danger">*</span></label>
							<div class="col-12 col-sm-9 col-lg-6">
								<input type="password" class="form-control" name="password">
							</div>
						</div>
						<div class="row mb-3">
							<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Repita a Senha <span class="text-danger">*</span></label>
							<div class="col-12 col-sm-9 col-lg-6">
								<input type="password" class="form-control" name="re_password">
							</div>
						</div>
						<div class="row mb-3">
				            <label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Foto</label>
				            <div class="col-12 col-sm-9 col-lg-6">
				                <input type="file" name="photo">
				            </div>
				        </div>
						<div class="row mb-3">
							<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Selecionar função <span class="text-danger">*</span></label>
							<div class="col-12 col-sm-9 col-lg-6">
								<select name="role" class="form-control">
									<option value="Admin">Admin</option>
									<option value="Publisher">Editor</option>
								</select>
							</div>
						</div>
				        <div class="row mb-3">
				            <label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Ativo?</label>
				            <div class="col-12 col-sm-9 col-lg-6">
				                <div class="form-check form-check-inline"><input class="form-check-input" type="radio" name="status" value="Active" checked> <label class="form-check-label">Sim</label></div>
				                <div class="form-check form-check-inline"><input class="form-check-input" type="radio" name="status" value="Inactive"> <label class="form-check-label">Não</label></div>
				            </div>
				        </div>
						<div class="row mb-3">
							<div class="col-sm-3 col-lg-2 d-none d-sm-block"></div>
							<div class="col-12 col-sm-9 col-lg-6">
								<button type="submit" class="btn btn-primary px-4" name="form1"><i class="bi bi-check-lg me-1"></i>Salvar</button>
<a href="user.php" class="btn btn-outline-secondary ms-2">Cancelar</a>
							</div>
						</div>
					</div>
				</div>
			</form>
		</div>
	</div>


</div>
</div>

<?php require_once('footer.php'); ?>