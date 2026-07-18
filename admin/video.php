<?php require_once('header.php'); ?>

<section class="content-header">
	<div class="content-header-left">
		<h1>Visualizar Vídeos</h1>
	</div>
	<div class="content-header-right">
		<a href="video-add.php" class="btn btn-primary btn-sm">Adicionar Novo</a>
	</div>
</section>


<section class="content">

  <div class="row">
    <div class="col-md-12">


      <div class="box box-info">
        
        <div class="box-body table-responsive">
          <table id="example1" class="table table-bordered table-striped">
			<thead>
			    <tr>
			        <th>ID</th>
			        <th>Título</th>
			        <th style="width:300px;">Codigo (iframe)</th>
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
			                <td>
			                    <a href="video-edit.php?id=<?php echo $row['video_id']; ?>" class="btn btn-primary btn-xs">Editar</a>
			                    <a href="#" class="btn btn-danger btn-xs" data-href="video-delete.php?id=<?php echo $row['video_id']; ?>" data-toggle="modal" data-target="#confirm-delete-<?php echo $row['video_id']; ?>">Deletar</a>  
			                </td>
			            </tr>
			            <div class="modal fade" id="confirm-delete-<?php echo $row['video_id']; ?>" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                                        <h4 class="modal-title" id="myModalLabel">Confirmação de Exclusão</h4>
                                    </div>
                                    <div class="modal-body">
                                        Tem certeza de que deseja deletar este item?
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
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
  

</section>

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