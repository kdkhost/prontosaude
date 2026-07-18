<?php require_once('header.php'); ?>

<?php
if(isset($_POST['form1'])) {

	$statement = $pdo->prepare("UPDATE tbl_home_category SET category_order=?, category_layout=?");
	$statement->execute(array('',''));

	foreach ($_POST['category_id'] as $key => $value) {
		$arr1[] = $value;
	}
	foreach ($_POST['category_order'] as $key => $value) {
		$arr2[] = $value;
	}
	foreach ($_POST['category_layout'] as $key => $value) {
		$arr3[] = $value;
	}

	for($i=0;$i<count($arr1);$i++) {
		if($arr2[$i] != '') {
			$statement = $pdo->prepare("UPDATE tbl_home_category SET category_order=?, category_layout=? WHERE category_id=?");
			$statement->execute(array($arr2[$i],$arr3[$i],$arr1[$i]));
		}
	}

	$success_message = 'Home Category Settings is updated successfully.';

}
?>

<section class="content-header">
	<div class="content-header-left">
		<h1>Categorias da página inicial</h1>
	</div>
</section>


<section class="content">

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

			<form class="form-horizontal" action="" method="post">

				<div class="box box-info">
					<div class="box-body">
						
						<p style="padding-bottom: 20px;">Se você não quiser mostrar uma categoria em sua página inicial, basta deixar o campo do pedido em branco.</p>
						
						<div class="form-group">
							<div class="col-sm-12">
								<table class="table table-bordered table-striped table-responsive" style="width:auto;">
									<thead>
										<tr>
											<th>Nome da Categoria</th>
											<th>Pedido</th>
											<th>Esquema</th>
										</tr>
									</thead>
									<tbody>
										<?php
										$statement = $pdo->prepare("SELECT 
																	
																	t1.id,
																	t1.category_id,
																	t1.category_order,
																	t1.category_layout,

																	t2.category_id,
																	t2.category_name

										                           	FROM tbl_home_category t1
																	JOIN tbl_category t2
																	ON t1.category_id = t2.category_id

																	ORDER by t2.category_name ASC
										                           ");
										$statement->execute();
										$result = $statement->fetchAll(PDO::FETCH_ASSOC);							
										foreach ($result as $row) {
											?>
											<input type="hidden" name="category_id[]" value="<?php echo $row['category_id']; ?>">
											<tr>
												<td style="padding-top:14px;"><?php echo $row['category_name']; ?></td>
												<td><input type="text" class="form-control" name="category_order[]" value="<?php echo $row['category_order']; ?>" style="width:100px;"></td>
												<td>
													<select class="form-control" name="category_layout[]">
														<option value="Normal 1 Column" <?php if($row['category_layout'] == 'Normal 1 Column'){echo 'selected';} ?>>Normal 1 Coluna</option>
														<option value="Normal 2 Columns" <?php if($row['category_layout'] == 'Normal 2 Columns'){echo 'selected';} ?>>Normal 2 Colunas</option>
														<option value="Normal 3 Columns" <?php if($row['category_layout'] == 'Normal 3 Columns'){echo 'selected';} ?>>Normal 3 Colunas</option>
														<option value="2 Columns (6 posts)" <?php if($row['category_layout'] == '2 Columns (6 posts)'){echo 'selected';} ?>>2 Colunas (6 postagens)</option>
													</select>
												</td>
											</tr>
											<?php
										}
										?>
									</tbody>
								</table>
							</div>
						</div>					
						<div class="form-group">
							<div class="col-sm-12">
								<button type="submit" class="btn btn-success pull-left" name="form1">Atualizar Informação</button>
							</div>
						</div>
					</div>
				</div>

			</form>


		</div>
	</div>

</section>

<?php require_once('footer.php'); ?>