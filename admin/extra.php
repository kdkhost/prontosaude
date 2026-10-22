<?php require_once('header.php'); ?>

<div class="app-content-header">
	<div class="container-fluid">
		<div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
			<div>
				<h3 class="mb-1 fw-bold">Visualizar Todas</h3>
				<ol class="breadcrumb mb-0 small">
					<li class="breadcrumb-item"><a href="index.php"><i class="bi bi-house-door"></i> Início</a></li>
					<li class="breadcrumb-item active" aria-current="page">Visualizar Todas</li>
				</ol>
			</div>
			<div class="d-flex flex-wrap align-items-center gap-2">
				<a href="extra-add.php" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg"></i> Adicionar Especialidade</a>
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
          <table id="minhaTabela" class="table table-hover table-striped align-middle w-100">
			<thead>
			    <tr>
			        <th width="50">ID</th>
			        <th width="">Médico</th>
			        <th>Especialidade</th>
			        <th width="">Dia</th>
			        <th width="">Hora</th>
			        <th width="80">Ação</th>
			    </tr>
			</thead>
            <tbody>
	            <?php
	            	$i=0;
	            	$statement = $pdo->prepare("SELECT * FROM tbl_doctor AS m 
	            	                                INNER JOIN tbl_designation AS f 
	            	                                INNER JOIN tbl_especialidade AS e 
	            	                                INNER JOIN tbl_department_openning_hour AS d 
	            	                          WHERE m.id = e.medico AND d.oh_id = e.semana AND f.designation_id = e.funcao AND m.status = 'Active'");
	            	$statement->execute();
	            	$result = $statement->fetchAll(PDO::FETCH_ASSOC);							
	            	foreach ($result as $row) {
	            		$i++;
		            	?>
			            <tr>
			                <td><?= $i; ?></td>
			                <td><?= $row['name']; ?></td>
			                <td><?= $row['designation_name']; ?></td>
			                <td><?= $row['oh_day']; ?></td>
			                <td><?= date("H:i",strtotime($row['inicio'])); ?> às <?= date("H:i",strtotime($row['fim'])); ?></td>
			                <td class="text-nowrap"><div class="d-flex flex-wrap gap-1"><a href="extra-edit.php?ide=<?php echo $row['ide']; ?>" class="btn btn-primary btn-sm"><i class="bi bi-pencil-square"></i> Editar</a>
			                    <a href="#" class="btn btn-danger btn-sm" data-target="#confirm-delete-<?php echo $row['ide']; ?>"><i class="bi bi-trash3"></i> Excluir</a></div></td>
			            </tr>
			            <div class="modal fade" id="confirm-delete-<?php echo $row['ide']; ?>" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <button type="button" class="close" data-bs-dismiss="modal" aria-hidden="true">&times;</button>
                                    <h4 class="modal-title" id="myModalLabel">Confirmação de Exclusão</h4>
                                </div>
                                <div class="modal-body">
                                    Tem certeza de que deseja excluir este item?
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                    <a href="extra-delete.php?ide=<?php echo $row['ide']; ?>" class="btn btn-danger btn-ok">Deletar</a>
                                </div>
                            </div>
                        </div>
                    </div>
			            <?php }?>
            </tbody>
          </table>
        </div>
      </div>

</div>
</div>
</div>
</div>

<?php require_once('footer.php'); ?>