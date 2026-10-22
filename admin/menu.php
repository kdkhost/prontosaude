<?php require_once('header.php'); ?>

<?php
if(isset($_POST['form1'])) {
	foreach($_POST['arr_order'] as $key=>$value) {
		$statement = $pdo->prepare("UPDATE tbl_menu SET menu_order=? WHERE menu_id=?");
		$statement->execute(array($value,$key));
	}	
	$success_message = 'Menu Order has been changed successfully.';
}
?>

<div class="app-content-header">
	<div class="container-fluid">
		<div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
			<div>
				<h3 class="mb-1 fw-bold">Visualizar Menus</h3>
				<ol class="breadcrumb mb-0 small">
					<li class="breadcrumb-item"><a href="index.php"><i class="bi bi-house-door"></i> Início</a></li>
					<li class="breadcrumb-item active" aria-current="page">Visualizar Menus</li>
				</ol>
			</div>
			<div class="d-flex flex-wrap align-items-center gap-2">
				<a href="menu-add.php" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg"></i> Adicionar Novo</a>
			</div>
		</div>
	</div>
</div>


<div class="app-content">
<div class="container-fluid">

  	<div class="row">
    	<div class="col-md-12">
	
			<p>
			    Como você não mudará um menu com frequência, não criamos a opção de editar todos os itens de um menu. Você só pode mudar 
			    <b>ordens.</b> 
			    Você pode criar menus ilimitados (com menu suspenso) e excluir menus quando necessário. Você pode selecionar uma categoria ou página como um item de menu ou configurar outro menu com link personalizado.
			</p>
		

			<div class="card card-outline card-primary shadow-sm mb-4">        
				<div class="card-body table-responsive">

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
					
					<form action="" method="post">
					<table id="example1" class="table table-hover table-striped align-middle w-100">
						<thead>
							<tr>
								<th>ID Menu</th>
								<th>Tipo de Menu</th>
								<th>Nome do Menu</th>
								<th>Categoria/Slug de Página</th>
								<th>Ordem do Menu</th>
								<th>ID do menu pai</th>
								<th>URL</th>
								<th>Ação</th>
							</tr>
						</thead>
						<tbody>
							<?php
							$i=0;
							$statement = $pdo->prepare("SELECT * FROM tbl_menu");
							$statement->execute();
							$result = $statement->fetchAll(PDO::FETCH_ASSOC);							
							foreach ($result as $row) {
								$i++;
								?>
								<tr>
									<td><?php echo $row['menu_id']; ?></td>
									<td><?php echo $row['menu_type']; ?></td>
									<td><?php echo $row['menu_name']; ?></td>
									<td><?php echo $row['category_or_page_slug']; ?></td>
									<td>
									<input type="text" name="arr_order[<?php echo $row['menu_id']; ?>]" class="form-control" value="<?php echo $row['menu_order']; ?>">
									</td>
									<td><?php echo $row['menu_parent']; ?></td>
									<td><?php echo $row['menu_url']; ?></td>
									<td class="text-nowrap"><div class="d-flex flex-wrap gap-1"><a href="#" class="btn btn-danger btn-sm" data-href="menu-delete.php?id=<?php echo $row['menu_id']; ?>" data-target="#confirm-delete-<?php echo $row['menu_id']; ?>"><i class="bi bi-trash3"></i> Excluir</a></div></td>
								</tr>
								<div class="modal fade" id="confirm-delete-<?php echo $row['menu_id']; ?>" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <button type="button" class="close" data-bs-dismiss="modal" aria-hidden="true">&times;</button>
                                                <h4 class="modal-title" id="myModalLabel">Confirmação de Exclusão</h4>
                                            </div>
                                            <div class="modal-body">
                                                Tem certeza de que deseja remover este item ("<?php echo $row['menu_name']; ?>")?
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                                <a href="menu-delete.php?id=<?php echo $row['menu_id']; ?>" class="btn btn-danger btn-ok">Deletar</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
								<?php } ?>
						</tbody>
					</table>
					<div class="text-center">
						<input type="submit" class="btn btn-success" value="Atualizar Ordem" name="form1">
					</div>
					</form>
				</div>
			</div>
		</div>
	</div>

</div>
</div>

<?php require_once('footer.php'); ?>