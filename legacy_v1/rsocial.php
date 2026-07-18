<?php 
    include_once 'header.php'; 
    
    $filename = BASE_URL . 'assets/uploads/termos/' . $banner;
    
?>

<!-- Banner Start -->
<div class="page-banner" style="background-image: url(
        <?php 
            if(isset($banner)){
                echo BASE_URL . 'assets/uploads/termos/' . $banner;
            }else{
                echo BASE_URL . 'assets/uploads/images/no-image-found.png';
            }
        ?>
    )">
	<div class="overlay"></div>
	<div class="container">
		<div class="row">
			<div class="col-md-12">
				<div class="banner-text">
					<h1>
					    <?= $titulo; ?>
					</h1>
				</div>
			</div>
		</div>
	</div>
</div>
<!-- Banner End -->

<!-- Service Start -->
<section class="about-v2">
	<div class="container">
		<div class="row">
			<div class="col-md-12">
				<?= $detalhes ;?>
				<p>
				    Estes Termos foram Atualizados em nossos websites em 
				    <?= ucfirst(strftime('%B de %Y', strtotime($atualizado))) ;?>.
				</p>
			</div>
		</div>
	</div>
</section>
<!-- Service End -->

<?php include_once 'footer.php'; ?>