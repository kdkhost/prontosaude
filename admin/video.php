<?php require_once('header.php'); ?>

<div class="app-content-header">
	<div class="container-fluid">
		<div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
			<div>
				<h3 class="mb-1 fw-bold">Visualizar Vídeos</h3>
				<ol class="breadcrumb mb-0 small">
					<li class="breadcrumb-item"><a href="index.php"><i class="bi bi-house-door"></i> Início</a></li>
					<li class="breadcrumb-item active" aria-current="page">Visualizar Vídeos</li>
				</ol>
			</div>
			<div class="d-flex flex-wrap align-items-center gap-2">
				<a href="video-add.php" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg"></i> Adicionar Novo</a>
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
			        <th>ID</th>
			        <th>Título</th>
			        <th style="width:300px;">Código (iframe)</th>
			        <th>Categoria</th>
			        <th>Ação</th>
			    </tr>
			</thead>
            <tbody>
	            <?php
	            	$i=0;
	            	$statement = $pdo->prepare("SELECT 
	            	                           
												t1.video_id,
												t1.video_title,
												t1.video_iframe,
												t1.v_category_id,

												t2.v_category_id,
												t2.v_category_name

	            	                           	FROM tbl_video t1
	            	                           	JOIN tbl_category_video t2
	            	                           	ON t1.v_category_id = t2.v_category_id");
	            	$statement->execute();
	            	$result = $statement->fetchAll(PDO::FETCH_ASSOC);							
	            	foreach ($result as $row) {
	            		$i++;
		            	?>
			            <tr>
			                <td><?php echo $i; ?></td>
			                <td><?php echo $row['video_title']; ?></td>
			                <td>
			                	<div class="video-iframe" autoplay="false" onloadeddata="this.play();this.muted=false;" muted>
			                		<video width="240" height="auto" controls class="parado">
                                      <source src="<?=$row['video_iframe']?>" type="video/mp4">
                                        Seu navegador não suporta tags de vídeo.
                                    </video>
			                	</div>
			                </td>
			                <td><?php echo $row['v_category_name']; ?></td>
			                <td class="text-nowrap"><div class="d-flex flex-wrap gap-1"><a href="video-edit.php?id=<?php echo $row['video_id']; ?>" class="btn btn-primary btn-sm"><i class="bi bi-pencil-square"></i> Editar</a>
			                    <a href="#" class="btn btn-danger btn-sm" data-href="video-delete.php?id=<?php echo $row['video_id']; ?>" data-target="#confirm-delete-<?php echo $row['video_id']; ?>"><i class="bi bi-trash3"></i> Excluir</a></div></td>
			            </tr>
			            <div class="modal fade" id="confirm-delete-<?php echo $row['video_id']; ?>" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <button type="button" class="close" data-bs-dismiss="modal" aria-hidden="true">&times;</button>
                                        <h4 class="modal-title" id="myModalLabel">Confirmação de Exclusão</h4>
                                    </div>
                                    <div class="modal-body">
                                        Tem certeza de que deseja deletar este item?
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                        <a href="video-delete.php?id=<?php echo $row['video_id']; ?>" class="btn btn-danger btn-ok">Deletar</a>
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

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <script>  
        $( document ).ready(function($) {
            $('.parado').on('play', function() {
                $(".rodando").each(function(){
                        $(this).removeClass('rodando').addClass('parado');
                        $(this).get(0).pause();
                });
                $(this).removeClass('parado').addClass('rodando');
                $(this).get(0).play();
            });
        });
    </script>


<script>
    $(document).ready(function() {
         $('iframe').prop('muted',true).play()
     });
</script>

<?php require_once('footer.php'); ?>