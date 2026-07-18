<?php require_once('header.php'); ?>

<section class="content-header">
	<div class="content-header-left">
		<h1>Visualizar Médicos</h1>
	</div>
	<div class="content-header-right">
	    <a href="extra.php" class="btn btn-primary btn-sm">visualizar Funções</a>
		<a href="doctor-add.php" class="btn btn-primary btn-sm">Cadastrar Médico</a>
	</div>
</section>

<section class="content">
	<div class="row">
		<div class="col-md-12">
			<div class="box box-info">
			    <?php if($_SESSION['msg']){ ?>
		            <div id="alert" class="callout callout-success">
		                <?php echo $_SESSION['msg']; unset($_SESSION['msg']);?>
		            </div>
		        <?php }elseif($_SESSION['erro']){?>
		            <div id="alert" class="callout callout-danger">
		                <?php echo $_SESSION['erro']; unset($_SESSION['erro']);?>
		            </div>
		        <?php } ?>
				<div class="box-body table-responsive">
					<table id="example1" class="table table-bordered table-striped">
						<thead>
							<tr>
								<!--<th>SL</th>-->
								<th>Foto</th>
								<th>Nome</th>
								<th>Designação</th>
								<th class="text-center" width="60">Status</th>
								<th width="12%">Ação</th>
							</tr>
						</thead>
						<tbody>
							<?php
							$i=0;
							$statement = $pdo->prepare("SELECT
														t1.id,
														t1.name,
														t1.designation_id,
														t1.photo,
														t1.status,

														t2.designation_id,
														t2.designation_name

							                           	FROM tbl_doctor t1
							                           	JOIN tbl_designation t2
							                           	ON t1.designation_id = t2.designation_id
							                           	");
							$statement->execute();
							$result = $statement->fetchAll(PDO::FETCH_ASSOC);							
							foreach ($result as $row) {
								$i++;
								?>
								<tr>
									<!--<td><?php echo $i; ?></td>-->
									<td style="width:60px;">
									    <img src="<?php echo BASE_URL; ?>assets/uploads/<?php echo $row['photo']; ?>" 
									    alt="<?php echo $row['name']; ?>" style="width:60px; height:60px;">
									</td>
									<td><?php echo $row['name']; ?></td>
									<td><?php echo $row['designation_name']; ?></td>
									<td class="text-center">
									    <?php 
									        if($row['status'] == "Active"){
									            echo '<span class="text-success text-uppercase"><b>Ativo</b></span>';
									        }else{
									            echo '<span class="text-danger text-uppercase"><b>Inativo</b></span>';
									        } 
									    ?>
									</td>
									<td>										
										<a href="doctor-edit.php?id=<?php echo $row['id']; ?>" class="btn btn-primary btn-xs">Editar</a>
										<a href="#" class="btn btn-danger btn-xs" data-href="doctor-delete.php?id=<?php echo $row['id']; ?>" 
										data-toggle="modal" data-target="#confirm-delete">
										    Deletar
										</a>  
									</td>
								</tr>
								<?php }?>							
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>


</section>


<div class="modal fade" id="confirm-delete" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title" id="myModalLabel">Confirmação de Exclusão</h4>
            </div>
            <div class="modal-body">
                <p>Tem certeza de que deseja excluir este item?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                <form action="doctor-delete.php" method="POST">
                    <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                    <button type="submit" name="del" class="btn btn-danger">Deletar</button>
                </form>
                <!--<a href="doctor-delete.php?id=<?php echo $row['id']; ?>" class="btn btn-danger btn-ok">Deletar</a>-->
            </div>
        </div>
    </div>
</div>


<?php require_once('footer.php'); ?>