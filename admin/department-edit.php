<?php require_once('header.php'); ?>

<?php
if(isset($_POST['form1'])) {
	$valid = 1;

	if(empty($_POST['dep_name'])) {
		$valid = 0;
		$error_message .= 'Department Name can not be empty<br>';
	} else {
		// Duplicate Category checking
    	// current department name that is in the database
    	$statement = $pdo->prepare("SELECT * FROM tbl_department WHERE dep_id=?");
		$statement->execute(array($_REQUEST['id']));
		$result = $statement->fetchAll(PDO::FETCH_ASSOC);
		foreach($result as $row) {
			$current_dep_name = $row['dep_name'];
		}

		$statement = $pdo->prepare("SELECT * FROM tbl_department WHERE dep_name=? and dep_name!=?");
    	$statement->execute(array($_POST['dep_name'],$current_dep_name));
    	$total = $statement->rowCount();							
    	if($total) {
    		$valid = 0;
        	$error_message .= 'Department Name already exists<br>';
    	}
	}
	
    $path = $_FILES['dep_photo']['name'];
    $path_tmp = $_FILES['dep_photo']['tmp_name'];

    if($path!='') {
        $ext = pathinfo( $path, PATHINFO_EXTENSION );
        $file_name = basename( $path, '.' . $ext );
        if( $ext!='jpg' && $ext!='png' && $ext!='jpeg' && $ext!='gif' ) {
            $valid = 0;
            $error_message .= 'You must have to upload jpg, jpeg, gif or png file for Featured Photo<br>';
        }
    }

    $path1 = $_FILES['dep_banner']['name'];
    $path_tmp1 = $_FILES['dep_banner']['tmp_name'];

    if($path1!='') {
        $ext1 = pathinfo( $path1, PATHINFO_EXTENSION );
        $file_name1 = basename( $path1, '.' . $ext1 );
        if( $ext1!='jpg' && $ext1!='png' && $ext1!='jpeg' && $ext1!='gif' ) {
            $valid = 0;
            $error_message .= 'You must have to upload jpg, jpeg, gif or png file for Banner<br>';
        }
    }

    

    // Updating Old Fields
    

    // Updating New Fields
    $fq_empty = 0;
    if( !empty($_POST['fq_title']) && !empty($_POST['fq_content']) ) {
    	$fq_empty = 1;
		foreach ($_POST['fq_title'] as $value) {
			$arr1[] = $value;
		}
		foreach ($_POST['fq_content'] as $value) {
			$arr2[] = $value;
		}
		$j=0;
		for($i=0;$i<count($arr1);$i++) {
			if($arr1[$i]=='' && $arr2[$i]=='') {continue;}
			else {
				$new_arr1[$j] = $arr1[$i];
				$new_arr2[$j] = $arr2[$i];
				$j++;
			}
		}
	}

	// Updating New Fields
	$oh_empty = 0;
    if( !empty($_POST['oh_day']) && !empty($_POST['oh_time']) ) {
    	$oh_empty = 1;
		foreach ($_POST['oh_day'] as $value) {
			$arr3[] = $value;
		}
		foreach ($_POST['oh_time'] as $value) {
			$arr4[] = $value;
		}
		$j=0;
		for($i=0;$i<count($arr3);$i++) {
			if($arr3[$i]=='' && $arr4[$i]=='') {continue;}
			else {
				$new_arr3[$j] = $arr3[$i];
				$new_arr4[$j] = $arr4[$i];
				$j++;
			}
		}
	}

	if($valid == 1) {

		if($_POST['dep_slug'] == '') {
    		// generate slug
    		$temp_string = strtolower($_POST['dep_name']);
    		$dep_slug = preg_replace('/[^A-Za-z0-9-]+/', '-', $temp_string);;
    	} else {
    		$temp_string = strtolower($_POST['dep_slug']);
    		$dep_slug = preg_replace('/[^A-Za-z0-9-]+/', '-', $temp_string);
    	}

    	// if slug already exists, then rename it
		$statement = $pdo->prepare("SELECT * FROM tbl_department WHERE dep_slug=? AND dep_name!=?");
		$statement->execute(array($dep_slug,$current_dep_name));
		$total = $statement->rowCount();
		if($total) {
			$dep_slug = $dep_slug.'-1';
		}



		if($path == '' && $path1 == '') {
			$statement = $pdo->prepare("UPDATE tbl_department SET dep_name=?, dep_slug=?, dep_detail=?, dep_address=?, dep_phone=?, dep_fax=?, dep_email=?, meta_title=?, meta_keyword=?, meta_description=? WHERE dep_id=?");
    		$statement->execute(array($_POST['dep_name'],$dep_slug,$_POST['dep_detail'],$_POST['dep_address'],$_POST['dep_phone'],$_POST['dep_fax'],$_POST['dep_email'],$_POST['meta_title'],$_POST['meta_keyword'],$_POST['meta_description'],$_REQUEST['id']));
		}
		if($path != '' && $path1 == '') {
			unlink('../assets/uploads/'.$_POST['current_photo']);

			$final_name = 'department-'.$_REQUEST['id'].'.'.$ext;
        	move_uploaded_file( $path_tmp, '../assets/uploads/'.$final_name );

        	$statement = $pdo->prepare("UPDATE tbl_department SET dep_name=?, dep_slug=?, dep_detail=?, dep_address=?, dep_phone=?, dep_fax=?, dep_email=?, dep_photo=?, meta_title=?, meta_keyword=?, meta_description=? WHERE dep_id=?");
    		$statement->execute(array($_POST['dep_name'],$dep_slug,$_POST['dep_detail'],$_POST['dep_address'],$_POST['dep_phone'],$_POST['dep_fax'],$_POST['dep_email'],$final_name,$_POST['meta_title'],$_POST['meta_keyword'],$_POST['meta_description'],$_REQUEST['id']));
		}
		if($path == '' && $path1 != '') {
			unlink('../assets/uploads/'.$_POST['current_banner']);

			$final_name1 = 'department-banner-'.$_REQUEST['id'].'.'.$ext1;
        	move_uploaded_file( $path_tmp1, '../assets/uploads/'.$final_name1 );

        	$statement = $pdo->prepare("UPDATE tbl_department SET dep_name=?, dep_slug=?, dep_detail=?, dep_address=?, dep_phone=?, dep_fax=?, dep_email=?, dep_banner=?, meta_title=?, meta_keyword=?, meta_description=? WHERE dep_id=?");
    		$statement->execute(array($_POST['dep_name'],$dep_slug,$_POST['dep_detail'],$_POST['dep_address'],$_POST['dep_phone'],$_POST['dep_fax'],$_POST['dep_email'],$final_name1,$_POST['meta_title'],$_POST['meta_keyword'],$_POST['meta_description'],$_REQUEST['id']));
		}
		if($path != '' && $path1 != '') {
			unlink('../assets/uploads/'.$_POST['current_photo']);
			unlink('../assets/uploads/'.$_POST['current_banner']);

			$final_name = 'department-'.$_REQUEST['id'].'.'.$ext;
        	move_uploaded_file( $path_tmp, '../assets/uploads/'.$final_name );

        	$final_name1 = 'department-banner-'.$_REQUEST['id'].'.'.$ext1;
        	move_uploaded_file( $path_tmp1, '../assets/uploads/'.$final_name1 );

        	$statement = $pdo->prepare("UPDATE tbl_department SET dep_name=?, dep_slug=?, dep_detail=?, dep_address=?, dep_phone=?, dep_fax=?, dep_email=?, dep_photo=?, dep_banner=?, meta_title=?, meta_keyword=?, meta_description=? WHERE dep_id=?");
    		$statement->execute(array($_POST['dep_name'],$dep_slug,$_POST['dep_detail'],$_POST['dep_address'],$_POST['dep_phone'],$_POST['dep_fax'],$_POST['dep_email'],$final_name,$final_name1,$_POST['meta_title'],$_POST['meta_keyword'],$_POST['meta_description'],$_REQUEST['id']));
    		
		}


		// Insert into tbl_department_faq
		if($fq_empty == 1) {
			for($i=0;$i<count($new_arr1);$i++) {
				$statement = $pdo->prepare("INSERT INTO tbl_department_faq (fq_title,fq_content,dep_id) VALUES (?,?,?)");
				$statement->execute(array($new_arr1[$i],$new_arr2[$i],$_REQUEST['id']));
			}
		}

		// Insert into tbl_department_openning_hour
		if($oh_empty == 1) {
			for($i=0;$i<count($new_arr3);$i++) {
				$statement = $pdo->prepare("INSERT INTO tbl_department_openning_hour (oh_day,oh_time,dep_id) VALUES (?,?,?)");
				$statement->execute(array($new_arr3[$i],$new_arr4[$i],$_REQUEST['id']));
			}
		}  

	    $success_message = 'Department is updated successfully!';
	}
}
?>

<?php
if(!isset($_REQUEST['id'])) {
	header('location: logout.php');
	exit;
} else {
	// Check the id is valid or not
	$statement = $pdo->prepare("SELECT * FROM tbl_department WHERE dep_id=?");
	$statement->execute(array($_REQUEST['id']));
	$total = $statement->rowCount();
	$result = $statement->fetchAll(PDO::FETCH_ASSOC);
	if( $total == 0 ) {
		header('location: logout.php');
		exit;
	}
}
?>

<div class="app-content-header">
	<div class="container-fluid">
		<div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
			<div>
				<h3 class="mb-1 fw-bold">Editar Departamento</h3>
				<ol class="breadcrumb mb-0 small">
					<li class="breadcrumb-item"><a href="index.php"><i class="bi bi-house-door"></i> Início</a></li>
					<li class="breadcrumb-item active" aria-current="page">Editar Departamento</li>
				</ol>
			</div>
			<div class="d-flex flex-wrap align-items-center gap-2">
				<a href="department.php" class="btn btn-outline-secondary btn-sm"><i class="bi bi-list-ul"></i> Ver Listagem</a>
			</div>
		</div>
	</div>
</div>

<?php
$statement = $pdo->prepare("SELECT * FROM tbl_department WHERE dep_id=?");
$statement->execute(array($_REQUEST['id']));
$result = $statement->fetchAll(PDO::FETCH_ASSOC);
foreach ($result as $row) {
	$dep_name         = $row['dep_name'];
	$dep_slug         = $row['dep_slug'];
	$dep_detail       = $row['dep_detail'];
	$dep_address      = $row['dep_address'];
	$dep_phone        = $row['dep_phone'];
	$dep_fax          = $row['dep_fax'];
	$dep_email        = $row['dep_email'];
	$dep_photo        = $row['dep_photo'];
	$dep_banner       = $row['dep_banner'];
	$meta_title       = $row['meta_title'];
	$meta_keyword     = $row['meta_keyword'];
	$meta_description = $row['meta_description'];
}
$statement = $pdo->prepare("SELECT * FROM tbl_department_faq WHERE dep_id=?");
$statement->execute(array($_REQUEST['id']));
$result = $statement->fetchAll(PDO::FETCH_ASSOC);
foreach ($result as $row) {
	$arr_fq_id[] = $row['fq_id'];
	$arr_fq_title[] = $row['fq_title'];
	$arr_fq_content[] = $row['fq_content'];
}

$statement = $pdo->prepare("SELECT * FROM tbl_department_openning_hour WHERE dep_id=?");
$statement->execute(array($_REQUEST['id']));
$result = $statement->fetchAll(PDO::FETCH_ASSOC);
foreach ($result as $row) {
	$arr_oh_id[] = $row['oh_id'];
	$arr_oh_day[] = $row['oh_day'];
	$arr_oh_time[] = $row['oh_time'];
}

?>

<div class="app-content">
<div class="container-fluid">
	<div class="row">
		<div class="col-md-12">

			<?php if($error_message): ?>
			<div class="callout callout-danger">
				<p>
				<?= $error_message; ?>
				</p>
			</div>
			<?php endif; ?>

			<?php if($success_message): ?>
			<div class="callout callout-success">
				<p><?= $success_message; ?></p>
			</div>
			<?php endif; ?>

			<form action="" method="post" enctype="multipart/form-data">
				<input type="hidden" name="current_photo" value="<?= $dep_photo; ?>">
				<input type="hidden" name="current_banner" value="<?= $dep_banner; ?>">
				<div class="card card-outline card-primary shadow-sm mb-4">
					<div class="card-body">
						<div class="row mb-3">
							<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Nome <span class="text-danger">*</span></label>
							<div class="col-12 col-sm-9 col-lg-6">
								<input type="text" autocomplete="off" class="form-control" name="dep_name" value="<?= $dep_name; ?>">
							</div>
						</div>
						<div class="row mb-3">
							<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Slug</label>
							<div class="col-12 col-sm-9 col-lg-6">
								<input type="text" autocomplete="off" class="form-control ps-slug-locked" name="dep_slug" value="<?= $dep_slug; ?>" readonly style="background-color: var(--bs-tertiary-bg, #f1f5f9); cursor: not-allowed; border-style: dashed;">
								<div class="form-text small text-body-secondary mt-1 ps-slug-helper d-flex align-items-center gap-1">
									<i class="bi bi-lock-fill text-primary"></i> <span class="fw-semibold">Slug automático:</span> gerado sozinho pelo sistema (edição manual desativada para evitar bugs de URL)
								</div>
							</div>
						</div>
						<div class="row mb-3">
							<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Detalhes</label>
							<div class="col-12 col-sm-9 col-lg-9">
								<textarea class="form-control" name="dep_detail" id="1"><?= $dep_detail; ?></textarea>
							</div>
						</div>
						<div class="row mb-3">
							<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Buscar por CEP</label>
							<div class="col-12 col-sm-9 col-lg-6">
								<div class="row g-2">
									<div class="col-7">
										<div class="input-group">
											<span class="input-group-text bg-body-tertiary"><i class="bi bi-geo-alt-fill text-primary"></i></span>
											<input type="text" class="form-control mask-cep" id="busca_cep" placeholder="00000-000" maxlength="9">
										</div>
									</div>
									<div class="col-5">
										<input type="text" class="form-control" id="numero_endereco" placeholder="Nº (auto focus)">
									</div>
								</div>
								<div class="form-text small text-body-secondary mt-1">
									<i class="bi bi-info-circle text-primary me-1"></i>Digite o CEP para autocompletar o endereço. O cursor focará no número assim que localizado.
								</div>
							</div>
						</div>
						<div class="row mb-3">
							<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Endereço</label>
							<div class="col-12 col-sm-9 col-lg-6">
								<textarea class="form-control" name="dep_address" style="height:140px;"><?= $dep_address; ?></textarea>
							</div>
						</div>
						<div class="row mb-3">
							<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Telefone</label>
							<div class="col-12 col-sm-9 col-lg-6">
								<input type="text" autocomplete="off" class="form-control" name="dep_phone" value="<?= $dep_phone; ?>">
							</div>
						</div>
						<div class="row mb-3">
							<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">WhatsApp</label>
							<div class="col-12 col-sm-9 col-lg-6">
								<input type="text" autocomplete="off" class="form-control" name="dep_fax" value="<?= $dep_fax; ?>">
							</div>
						</div>
						<div class="row mb-3">
							<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Email</label>
							<div class="col-12 col-sm-9 col-lg-6">
								<input type="text" autocomplete="off" class="form-control" name="dep_email" value="<?= $dep_email; ?>">
							</div>
						</div>
						<div class="row mb-3">
							<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Foto Atual</label>
							<div class="col-12 col-sm-9 col-lg-9">
								<img src="<?= BASE_URL; ?>assets/uploads/<?= $dep_photo; ?>" alt="Department Photo" style="width:400px;">
							</div>
						</div>
						<div class="row mb-3">
							<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Foto em destaque <span class="text-danger">*</span></label>
							<div class="col-12 col-sm-9 col-lg-9">
								<input type="file" name="dep_photo"><div class="form-text">Somente jpg, jpeg, gif & png Serão aceitos</div>
							</div>
						</div>
						<div class="row mb-3">
							<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Banner Atual</label>
							<div class="col-12 col-sm-9 col-lg-9">
								<img src="<?= BASE_URL; ?>assets/uploads/<?= $dep_banner; ?>" alt="Department Photo" style="width:400px;">
							</div>
						</div>
						<div class="row mb-3">
							<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Banner <span class="text-danger">*</span></label>
							<div class="col-12 col-sm-9 col-lg-9">
								<input type="file" name="dep_banner"><div class="form-text">Somente jpg, jpeg, gif & png Serão aceitos</div>
							</div>
						</div>
						<div class="row mb-3">
							<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Seção de Horário de Funcionamento</label>
							<div class="col-12 col-sm-9 col-lg-9">
								<table id="ohSection" class="table table-hover table-striped align-middle w-100">
									<thead>
										<tr>
											<th style="width:50%;">Dia</th>
											<th>Horário</th>
											<th></th>
										</tr>
									</thead>
									<tbody>
										<?php
										if(isset($arr_oh_day)):
										for($i=0;$i<count($arr_oh_day);$i++) {
											?>
											<tr>
												<td>
													<input autocomplete="off" type="text" class="form-control" style="width:100%" value="<?= $arr_oh_day[$i]; ?>">
												</td>
												<td>
													<input autocomplete="off" type="text" class="form-control" style="width:100%" value="<?= $arr_oh_time[$i]; ?>">
												</td>
												<td class="text-nowrap"><div class="d-flex flex-wrap gap-1"><a href="department-oh-delete.php?id=<?= $arr_oh_id[$i]; ?>&id1=<?= $_REQUEST['id']; ?>" class="btn btn-danger btn-sm">X</a></div></td>
											</tr>
											<?php
										}
										endif;
										?>
									</tbody>
								</table>
								<input type="button" class="btn btn-info btn-sm" id="btnAddOpenningHour" value="+ Adicionar linha" style="margin-bottom:10px;">
							</div>
						</div>
						
						<script>
						    $(document).ready(function () {

                                $("#btnAddOpenningHour").click(function () {
                    
                                    var trNew1 = "";
                    
                                    var ohTitle = '<input autocomplete="off" type="text" class="form-control" name="oh_day[]" style="width:100%">';
                    				
                    				var ohContent = '<input autocomplete="off" type="text" class="form-control" name="oh_time[]" style="width:100%">';
                    
                                    var ohDelete = '<a href="javascript:void()" class="Delete1 btn btn-danger btn-sm">X</a>';
                    
                                    trNew1 = trNew1 + '<tr> ';
                    
                                    trNew1 += '<td>' + ohTitle + '</td>';
                                    trNew1 += '<td>' + ohContent + '</td>';
                                    trNew1 += '<td>' + ohDelete + '</td>';
                    
                                    trNew1 = trNew1 + ' </tr>';
                    
                                    $("#ohSection tbody").append(trNew1);
                    
                                });
                    
                                $('#ohSection').delegate('a.Delete1', 'click', function () {
                                    $(this).parent().parent().fadeOut('slow').remove();
                                    return false;
                                });
                    
                            });
						</script>
						
						<div class="row mb-3">
							<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Sessão F.A.Q</label>
							<div class="col-12 col-sm-9 col-lg-9">
								<table id="fqSection" class="table table-hover table-striped align-middle w-100">
									<thead>
										<tr>
											<th style="width:50%;">Título</th>
											<th>Conteúdo</th>
											<th></th>
										</tr>
									</thead>
									<tbody>
										<?php
										if(isset($arr_fq_title)):
										for($i=0;$i<count($arr_fq_title);$i++) {
											?>
											<tr>
												<td>
													<input autocomplete="off" type="text" class="form-control" style="width:100%" value="<?= $arr_fq_title[$i]; ?>">
												</td>
												<td>
													<textarea id="1" class="form-control" cols="30" rows="10" style="width:100%;height:50px;"><?= $arr_fq_content[$i]; ?></textarea>
												</td>
												<td class="text-nowrap"><div class="d-flex flex-wrap gap-1"><a href="department-fq-delete.php?id=<?= $arr_fq_id[$i]; ?>&id1=<?= $_REQUEST['id']; ?>" class="btn btn-danger btn-sm">X</a></div></td>
											</tr>
											<?php
										}
										endif;
										?>
									</tbody>
								</table>
								<input type="button" class="btn btn-info btn-sm" id="btnAddFaq" value="+ Adicionar linha" style="margin-bottom:10px;">
							</div>
						</div>
						
						<script>
						    $(document).ready(function () {

                                $("#btnAddFaq").click(function () {
                    
                                    var trNew1 = "";
                    
                                    var fqTitle = '<input autocomplete="off" type="text" class="form-control" name="fq_title[]" style="width:100%">';
                    				
                    				var fqContent = '<textarea class="form-control" cols="30" rows="10" name="fq_content[]" style="width:100%;height:50px;"></textarea>';
                    
                                    var fqDelete = '<a href="javascript:void()" class="Delete1 btn btn-danger btn-sm">X</a>';
                    
                                    trNew1 = trNew1 + '<tr> ';
                    
                                    trNew1 += '<td>' + fqTitle + '</td>';
                                    trNew1 += '<td>' + fqContent + '</td>';
                                    trNew1 += '<td>' + fqDelete + '</td>';
                    
                                    trNew1 = trNew1 + ' </tr>';
                    
                                    $("#fqSection tbody").append(trNew1);
                    
                                });
                    
                                $('#fqSection').delegate('a.Delete1', 'click', function () {
                                    $(this).parent().parent().fadeOut('slow').remove();
                                    return false;
                                });
                    
                            });
						</script>
						
						<div class="row mb-3 mt-4"><div class="col-12"><h5 class="fw-bold text-primary border-bottom pb-2 mb-0"><i class="bi bi-search me-2"></i>Informações do SEO</h5></div></div>
						<div class="row mb-3">
							<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Meta Título</label>
							<div class="col-12 col-sm-9 col-lg-9">
								<input type="text" class="form-control" name="meta_title" value="<?= $meta_title; ?>">
							</div>
						</div>
						<div class="row mb-3">
							<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Palavras-chave</label>
							<div class="col-12 col-sm-9 col-lg-9">
								<input type="text" class="form-control" name="meta_keyword" value="<?= $meta_keyword; ?>">
							</div>
						</div>
						<div class="row mb-3">
							<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Meta Descrição</label>
							<div class="col-12 col-sm-9 col-lg-9">
								<textarea class="form-control" name="meta_description" style="height:200px;"><?= $meta_description; ?></textarea>
							</div>
						</div>			
						<div class="row mb-3">
							<div class="col-sm-3 col-lg-2 d-none d-sm-block"></div>
							<div class="col-12 col-sm-9 col-lg-6">
								<button type="submit" class="btn btn-primary px-4" name="form1"><i class="bi bi-check-lg me-1"></i>Salvar</button>
<a href="department.php" class="btn btn-outline-secondary ms-2">Cancelar</a>
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