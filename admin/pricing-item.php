<?php require_once('header.php'); ?>

<div class="app-content-header">
	<div class="container-fluid">
		<div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
			<div>
				<h3 class="mb-1 fw-bold">Visualizar Preços dos itens</h3>
				<ol class="breadcrumb mb-0 small">
					<li class="breadcrumb-item"><a href="index.php"><i class="bi bi-house-door"></i> Início</a></li>
					<li class="breadcrumb-item active" aria-current="page">Visualizar Preços dos itens</li>
				</ol>
			</div>
			<div class="d-flex flex-wrap align-items-center gap-2">
				<a href="pricing-item-add.php" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg"></i> Adicionar Novo</a>
			</div>
		</div>
	</div>
</div>


<div class="app-content">
<div class="container-fluid">

  <div class="row">
    <div class="col-md-12">


      <div class="card card-outline card-primary shadow-sm mb-4">
        <?php if(!empty($_SESSION['msg'])){ ?>
		            <div id="alert" class="callout callout-success">
		                <?php echo $_SESSION['msg']; unset($_SESSION['msg']);?>
		            </div>
		        <?php }elseif(!empty($_SESSION['erro'])){?>
		            <div id="alert" class="callout callout-danger">
		                <?php echo $_SESSION['erro']; unset($_SESSION['erro']);?>
		            </div>
		        <?php } ?>
        <div class="card-body table-responsive">
          <table id="example1" class="table table-hover table-striped align-middle w-100">
			<thead>
			    <tr>
			        <th>ID</th>
			        <th>Nome do item</th>
			        <th>Nome do Plano</th>
			        <th>Ação</th>
			    </tr>
			</thead>
            <tbody>
	            <?php
	            	$i=0;
	            	$statement = $pdo->prepare("SELECT
                                                t1.pricing_item_id,
                                                t1.pricing_item_name,
                                                t1.pricing_plan_id,
                                                t2.pricing_plan_id,
                                                t2.pricing_plan_name
                                            FROM
                                                tbl_pricing_item t1
                                            JOIN tbl_pricing_plan t2 ON
                                                t1.pricing_plan_id = t2.pricing_plan_id
                                            ORDER BY
                                                t2.pricing_plan_id");
	            	$statement->execute();
	            	$result = $statement->fetchAll(PDO::FETCH_ASSOC);							
	            	foreach ($result as $row) {
	            	    extract($row);
	            		$i++;
		            	?>
			            <tr>
			                <td><?php echo $i; ?></td>
			                <td><?php echo $row['pricing_item_name']; ?></td>
			                <td><?php echo $row['pricing_plan_name']; ?></td>
			                <td class="text-nowrap"><div class="d-flex flex-wrap gap-1"><a href="pricing-item-edit.php?id=<?php echo $row['pricing_item_id']; ?>" class="btn btn-primary btn-sm"><i class="bi bi-pencil-square"></i> Editar</a>
			                    <a href="#" class="btn btn-danger btn-sm" data-target="#confirm-delete-<?= $row['pricing_item_id']; ?>"><i class="bi bi-trash3"></i> Excluir</a></div></td>
			            </tr>
			            <div class="modal fade" id="confirm-delete-<?= $row['pricing_item_id']; ?>" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <button type="button" class="close" data-bs-dismiss="modal" aria-hidden="true">&times;</button>
                                        <h4 class="modal-title" id="myModalLabel">Confirmação de Remoção</h4>
                                    </div>
                                    <div class="modal-body">
                                        Tem certeza de que deseja excluir este item, (<?php echo $row['pricing_item_name']; ?>)?
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                        <form action="pricing-item-delete.php" method="POST">
                                            <input type="hidden" name="id" value="<?php echo $row['pricing_item_id']; ?>">
                                            <button type="submit" name="del" class="btn btn-danger">Deletar</button>
                                        </form>
                                        <!--<a href="pricing-item-delete.php?id=<?php echo $row['pricing_item_id']; ?>" class="btn btn-danger btn-ok">Deletar</a>-->
                                    </div>
                                </div>
                            </div>
                        </div>
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