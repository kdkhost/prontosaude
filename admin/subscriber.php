<?php require_once('header.php'); ?>

<div class="app-content-header">
	<div class="container-fluid">
		<div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
			<div>
				<h3 class="mb-1 fw-bold">Inscritos na Newsletter</h3>
				<ol class="breadcrumb mb-0 small">
					<li class="breadcrumb-item"><a href="index.php"><i class="bi bi-house-door"></i> Início</a></li>
					<li class="breadcrumb-item active" aria-current="page">Inscritos na Newsletter</li>
				</ol>
			</div>
			<div class="d-flex flex-wrap align-items-center gap-2">
				<a href="subscriber-remove.php" class="btn btn-primary btn-sm"><i class="bi bi-person-x"></i> Remover Pendentes</a>
				<a href="subscriber-csv.php" class="btn btn-primary btn-sm"><i class="bi bi-filetype-csv"></i> Exportar CSV</a>
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
			        <th>SL</th>
			        <th>Subscriber Email</th>
			    </tr>
			</thead>
            <tbody>
            	<?php
            	$i=0;
            	$statement = $pdo->prepare("SELECT * FROM tbl_subscriber WHERE subs_active=1");
            	$statement->execute();
            	$result = $statement->fetchAll(PDO::FETCH_ASSOC);							
            	foreach ($result as $row) {
            		$i++;
            		?>
					<tr>
	                    <td><?php echo $i; ?></td>
	                    <td><?php echo $row['subs_email']; ?></td>
	                </tr>
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