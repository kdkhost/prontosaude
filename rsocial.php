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
<section class="about-v2" style="padding: 60px 0; background: #f8fafc;">
	<div class="container">
		<div class="row">
			<div class="col-md-12">
				<div style="background: #ffffff; border-radius: 16px; padding: 36px 32px; box-shadow: 0 10px 30px rgba(0,0,0,0.06); border: 1px solid #e2e8f0; font-size: 15.5px; line-height: 1.85; color: #334155;">
					<?= $detalhes ;?>
					<div style="margin-top: 24px; padding-top: 18px; border-top: 1px solid #f1f5f9; color: #64748b; font-size: 13.5px;">
						<i class="fa fa-clock-o" style="margin-right: 5px;"></i> Estes Termos foram atualizados em nossos websites em <?= formata_mes_ano($atualizado ?? ''); ?>.
					</div>
				</div>
			</div>
		</div>
	</div>
</section>
<!-- Service End -->

<?php include_once 'footer.php'; ?>