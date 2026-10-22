<?php require_once('header.php'); ?>

<div class="app-content-header">
	<div class="container-fluid">
		<div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
			<div>
				<h3 class="mb-1 fw-bold">Visualizar Departamento</h3>
				<ol class="breadcrumb mb-0 small">
					<li class="breadcrumb-item"><a href="index.php"><i class="bi bi-house-door"></i> Início</a></li>
					<li class="breadcrumb-item active" aria-current="page">Visualizar Departamento</li>
				</ol>
			</div>
			<div class="d-flex flex-wrap align-items-center gap-2">
				<a href="department-add.php" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg"></i> Adicionar Novo</a>
			</div>
		</div>
	</div>
</div>


<div class="app-content">
<div class="container-fluid">

  <div class="row">
    <div class="col-md-12">


      <div class="card card-outline card-primary shadow-sm mb-4">
        
        <div class="card-body table-responsive">
          <table id="example1" class="table table-hover table-striped align-middle w-100">
			<thead>
			    <tr>
			        <th>Id</th>
			        <th>Nome do departamento</th>
			        <th>Detalhes</th>
			        <th>Ação</th>
			    </tr>
			</thead>
            <tbody>
            	<?php
            	$i=0;
            	$statement = $pdo->prepare("SELECT * FROM tbl_department ORDER BY dep_name ASC");
            	$statement->execute();
            	$result = $statement->fetchAll(PDO::FETCH_ASSOC);							
            	foreach ($result as $row) {
            		$i++;
            		?>
					<tr>
	                    <td><?php echo $i; ?></td>
	                    <td><?php echo $row['dep_name']; ?></td>
	                    <td class="text-nowrap"><div class="d-flex flex-wrap gap-1"><a href="" data-target="#myModalDetail<?php echo $i; ?>" class="btn btn-warning btn-sm">Veja detalhes</a></div></td>
	                    <td class="text-nowrap"><div class="d-flex flex-wrap gap-1"><a href="department-edit.php?id=<?php echo $row['dep_id']; ?>" class="btn btn-primary btn-sm"><i class="bi bi-pencil-square"></i> Editar</a>
	                        <a href="#" class="btn btn-danger btn-sm" data-href="department-delete.php?id=<?php echo $row['dep_id']; ?>" data-target="#confirm-delete"><i class="bi bi-trash3"></i> Excluir</a></div></td>
	                </tr>

	                <!-- Modal Start -->   
					<div class="modal fade" id="myModalDetail<?php echo $i; ?>" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
						<div class="modal-dialog">
							<div class="modal-content">
								<div class="modal-header">
									<button type="button" class="close" data-bs-dismiss="modal" aria-hidden="true">&times;</button>
									<h4 class="modal-title" id="myModalLabel">
										Departamento - <?php echo $row['dep_name']; ?>
									</h4>
								</div>
								<div class="modal-body">
									<div class="rTable">
										<div class="rTableRow">
											<div class="rTableCell">
												Foto
											</div>
											<div class="rTableCell">
												<img src="../assets/uploads/<?php echo $row['dep_photo']; ?>" style="width:160px;">
											</div>
										</div>
										<div class="rTableRow">
											<div class="rTableCell">
												Banner
											</div>
											<div class="rTableCell">
												<img src="../assets/uploads/<?php echo $row['dep_banner']; ?>" style="width:400px;">
											</div>
										</div>
										<div class="rTableRow">
											<div class="rTableCell">
												Nome
											</div>
											<div class="rTableCell">
												<?php echo $row['dep_name']; ?>
											</div>
										</div>
										<div class="rTableRow">
											<div class="rTableCell">
												Slug
											</div>
											<div class="rTableCell">
												<?php echo $row['dep_slug']; ?>
											</div>
										</div>
										<div class="rTableRow">
											<div class="rTableCell">
												Detalhes
											</div>
											<div class="rTableCell">
												<?php echo $row['dep_detail']; ?>
											</div>
										</div>
										<div class="rTableRow">
											<div class="rTableCell">
												Endereço
											</div>
											<div class="rTableCell">
												<?php echo $row['dep_address']; ?>
											</div>
										</div>
										<div class="rTableRow">
											<div class="rTableCell">
												Telefone
											</div>
											<div class="rTableCell">
												<?php echo $row['dep_phone']; ?>
											</div>
										</div>
										<div class="rTableRow">
											<div class="rTableCell">
												WhatsApp
											</div>
											<div class="rTableCell">
												<?php echo $row['dep_fax']; ?>
											</div>
										</div>
										<div class="rTableRow">
											<div class="rTableCell">
												Email
											</div>
											<div class="rTableCell">
												<?php echo $row['dep_email']; ?>
											</div>
										</div>
										<div class="rTableRow">
											<div class="rTableCell">
												Horário de funcionamento
											</div>
											<div class="rTableCell">
												<?php
												$statement1 = $pdo->prepare("SELECT * FROM tbl_department_openning_hour WHERE dep_id=?");
												$statement1->execute(array($row['dep_id']));
												$result1 = $statement1->fetchAll(PDO::FETCH_ASSOC);		
												foreach ($result1 as $row1) {
													echo $row1['oh_day'].' ('.$row1['oh_time'].')<br>';
												}
												?>
											</div>
										</div>
										<div class="rTableRow">
											<div class="rTableCell">
												F.A.Q
											</div>
											<div class="rTableCell">
												<?php
												$statement1 = $pdo->prepare("SELECT * FROM tbl_department_faq WHERE dep_id=?");
												$statement1->execute(array($row['dep_id']));
												$result1 = $statement1->fetchAll(PDO::FETCH_ASSOC);		
												foreach ($result1 as $row1) {
													echo '<b>Q: </b>'.$row1['fq_title'].'<br>';
													echo '<b>A: </b>'.$row1['fq_content'].'<br><br>';
												}
												?>
											</div>
										</div>
										<div class="rTableRow">
											<div class="rTableCell">
												Meta Title
											</div>
											<div class="rTableCell">
												<?php echo $row['meta_title']; ?>
											</div>
										</div>
										<div class="rTableRow">
											<div class="rTableCell">
												Meta Keyword
											</div>
											<div class="rTableCell">
												<?php echo $row['meta_keyword']; ?>
											</div>
										</div>
										<div class="rTableRow">
											<div class="rTableCell">
												Meta Description
											</div>
											<div class="rTableCell">
												<?php echo $row['meta_description']; ?>
											</div>
										</div>
									</div>
								</div>
							</div>                                    
						</div>                                
					</div>
					<!-- Modal End -->
            		<?php
            	}
            	?>
            </tbody>
          </table>
        </div>
      </div>
  


</div>
</div>
</div>
</div>
<?php require_once('footer.php'); ?>