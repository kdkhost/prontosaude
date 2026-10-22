<?php require_once('header.php'); ?>

<div class="app-content-header">
	<div class="container-fluid">
		<div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
			<div>
				<h3 class="mb-1 fw-bold">Visualizar FAQs</h3>
				<ol class="breadcrumb mb-0 small">
					<li class="breadcrumb-item"><a href="index.php"><i class="bi bi-house-door"></i> Início</a></li>
					<li class="breadcrumb-item active" aria-current="page">Visualizar FAQs</li>
				</ol>
			</div>
			<div class="d-flex flex-wrap align-items-center gap-2">
				<a href="faq-add.php" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg"></i> Adicionar Novo</a>
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
			        <th width="50">ID</th>
			        <th width="140">Título FAQ</th>
			        <th>Conteúdo FAQ</th>
			        <th width="120">Categoria FAQ</th>
			        <th width="110" class="text-center">Status</th>
			        <th width="80">Ação</th>
			    </tr>
			</thead>
            <tbody>
	            <?php
	            	$i=0;
	            	$statement = $pdo->prepare("SELECT 
	            	                           
												t1.faq_id,
												t1.faq_title,
												t1.faq_content,
												t1.faq_category_id,
												t1.status,

												t2.faq_category_id,
												t2.faq_category_name

	            	                           	FROM tbl_faq t1
	            	                           	JOIN tbl_faq_category t2
	            	                           	ON t1.faq_category_id = t2.faq_category_id");
	            	$statement->execute();
	            	$result = $statement->fetchAll(PDO::FETCH_ASSOC);							
	            	foreach ($result as $row) {
	            		$i++;
		            	?>
			            <tr>
			                <td><?= $i; ?></td>
			                <td><?= $row['faq_title']; ?></td>
			                <td><?= $row['faq_content']; ?></td>
			                <td><?= $row['faq_category_name']; ?></td>
			                <td class="text-center">
			                	<div class="d-inline-flex align-items-center gap-2">
			                		<div class="form-check form-switch mb-0">
			                			<input class="form-check-input pronto-status-switch" type="checkbox" role="switch" data-entity="faq" data-id="<?= $row['faq_id']; ?>" data-name="<?= htmlspecialchars($row['faq_title'] ?: 'FAQ #' . $i); ?>" data-active-text="Visível" data-inactive-text="Oculto" <?= (($row['status'] ?? 'Active') == 'Active') ? 'checked' : ''; ?> title="Ativar ou desativar esta dúvida frequente no site">
			                		</div>
			                		<span class="badge status-badge <?= (($row['status'] ?? 'Active') == 'Active') ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-danger-subtle text-danger border border-danger-subtle'; ?> rounded-pill small">
			                			<?= (($row['status'] ?? 'Active') == 'Active') ? 'Visível' : 'Oculto'; ?>
			                		</span>
			                	</div>
			                </td>
			                <td class="text-nowrap"><div class="d-flex flex-wrap gap-1"><a href="faq-edit.php?id=<?= $row['faq_id']; ?>" class="btn btn-primary btn-sm"><i class="bi bi-pencil-square"></i> Editar</a>
			                    <a href="#" class="btn btn-danger btn-sm" data-href="faq-delete.php?id=<?= $row['faq_id']; ?>" data-target="#confirm-delete-<?=$row['faq_id']?>"><i class="bi bi-trash3"></i> Excluir</a></div></td>
			            </tr>
			            <div class="modal fade" id="confirm-delete-<?=$row['faq_id']?>" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <button type="button" class="close" data-bs-dismiss="modal" aria-hidden="true">&times;</button>
                                        <h4 class="modal-title" id="myModalLabel">Confirmar Exclusão</h4>
                                    </div>
                                    <div class="modal-body">
                                        Você tem certeza de que vai deletar essa Pergunta?<br>
                                        <b>Pergunta:</b> <strong class="text-danger"><?= $row['faq_title']; ?></strong><br>
                                        <b>Resposta:</b>  <strong class="text-danger"><?= $row['faq_content']; ?></strong><br>
                                        <b>Reservado para Categoria:</b>  <strong class="text-danger"><?= $row['faq_category_name']; ?></strong>
                                        
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                        <a href="faq-delete.php?id=<?= $row['faq_id']; ?>" class="btn btn-danger btn-ok">Deletar</a>
                                    </div>
                                </div>
                            </div>
                        </div>
			            <?php } ?>
            </tbody>
          </table>
        </div>
      </div>

</div>
</div>
</div>
</div>
<?php require_once('footer.php'); ?>