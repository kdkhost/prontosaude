<?php require_once('header.php'); ?>

<?php
$statement = $pdo->prepare("SELECT * FROM tbl_settings WHERE id=1");
$statement->execute();
$result = $statement->fetchAll(PDO::FETCH_ASSOC);							
foreach ($result as $row) {
	$total_recent_news_home_page = $row['total_recent_news_home_page'];
	$home_title_service          = $row['home_title_service'];
	$home_subtitle_service       = $row['home_subtitle_service'];
	$home_status_service         = $row['home_status_service'];
	$home_title_department       = $row['home_title_department'];
	$home_subtitle_department    = $row['home_subtitle_department'];
	$home_status_department      = $row['home_status_department'];
	$home_title_doctor           = $row['home_title_doctor'];
	$home_subtitle_doctor        = $row['home_subtitle_doctor'];
	$home_status_doctor          = $row['home_status_doctor'];
	$home_title_pricing          = $row['home_title_pricing'];
	$home_subtitle_pricing       = $row['home_subtitle_pricing'];
	$home_status_pricing         = $row['home_status_pricing'];
	$home_title_testimonial      = $row['home_title_testimonial'];
	$home_subtitle_testimonial   = $row['home_subtitle_testimonial'];
	$home_status_testimonial     = $row['home_status_testimonial'];
	$home_title_news             = $row['home_title_news'];
	$home_subtitle_news          = $row['home_subtitle_news'];
	$home_status_news            = $row['home_status_news'];
	$home_title_partner          = $row['home_title_partner'];
	$home_subtitle_partner       = $row['home_subtitle_partner'];
	$home_status_partner         = $row['home_status_partner'];
}
?>

<?php if($home_status_pricing == 1): ?>
<!-- Pricing Start -->
<section class="pricing-v1" id="fidelidade">
	<div class="container">
		<div class="row">
			<div class="col-md-12">
				<div class="heading">
					<h2><?php echo $home_title_pricing; ?></h2>
					<p><?php echo $home_subtitle_pricing; ?></p>
				</div>
			</div>
		</div>
		<div class="row">

			<?php
			$statement = $pdo->prepare("SELECT * FROM tbl_pricing_plan ORDER BY pricing_plan_id ASC");
			$statement->execute();
			$result = $statement->fetchAll(PDO::FETCH_ASSOC);
			foreach ($result as $row) {
			    //var_dump($row['pricing_plan_name']);
				?>
				<div class="col-md-4">
				    <style>
				        .pricing-item:hover{
				            background:#0a6b74;
				            color:white;
				        }
				    </style>
					<div class="pricing-item">
						<div class="title" style="font-size:18px;">
						    <?php echo $row['pricing_plan_name']; ?>
						</div>
						<div class="price">
						      <div class="" width="200px">
								<?php if($row['pricing_plan_name'] == "Top saúde"){ ?>
								    <img class="img img-responsive" src="../assets/images/planos/top-saude.png" alt="<?=$row['pricing_plan_name'];?>" />
								<?php }elseif($row['pricing_plan_name'] == "Top saúde Med Odonto"){ ?>
								    <img class="img img-responsive" src="../assets/images/planos/odonto.png" alt="<?=$row['pricing_plan_name'];?>" />
								<?php }elseif($row['pricing_plan_name'] == "Plano Empresa"){?>
								    <img class="img img-responsive" src="../assets/images/planos/empresarial.png" alt="<?=$row['pricing_plan_name'];?>" />
								<?php }?>
							</div>
						</div>
						        <?php
    						        if($row['pricing_plan_price'] != "consultar"){
    						            echo "<span class='text-uppercase h4'>À Partir de R$ " . $row['pricing_plan_price'] . "</span>";
    						        }else{
    						            echo "<span class='text-uppercase h4'>À " . $row['pricing_plan_price'] . "</span><br>";
    						        }
    						    ?>
    						    <p style='padding-bottom:25px;'></p>
						<div class="offer">
							<ul>
								<?php
								$statement1 = $pdo->prepare("SELECT * FROM tbl_pricing_item WHERE pricing_plan_id=?");
								$statement1->execute(array($row['pricing_plan_id']));
								$result1 = $statement1->fetchAll(PDO::FETCH_ASSOC);							
								foreach ($result1 as $row1) {
									?>
									<li>
									    <?php echo $row1['pricing_item_name']; ?>
									</li>
									<?php
								}
								?>
							</ul>
						</div>
						<?php if($row['pricing_plan_button_text']!=''): ?>
						<div class="button">
							<a href="<?php echo $row['pricing_plan_button_url']; ?>">Contratar Este</a>
						</div>
						<?php endif; ?>
					</div>
				</div>
				<?php
			}
			?>

			
		</div>
	</div>
</section>
<!-- Pricing End -->
<?php endif; ?>

<?php require_once('footer.php'); ?>