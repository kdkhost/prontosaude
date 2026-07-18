<?php require_once('header.php'); ?>

<?php
// Preventing the direct access of this page.
if(!isset($_REQUEST['slug']))
{
	header('location: '.BASE_URL);
	exit;
}
else
{
	// Check the page slug is valid or not.
	$statement = $pdo->prepare("SELECT * FROM tbl_doctor WHERE slug=?");
	$statement->execute(array($_REQUEST['slug']));
	$total = $statement->rowCount();
	if( $total == 0 )
	{
		header('location: '.BASE_URL);
		exit;
	}
}

// Getting the detailed data of a service from slug
$statement = $pdo->prepare("SELECT * FROM tbl_doctor WHERE slug=?");
$statement->execute(array($_REQUEST['slug']));
$result = $statement->fetchAll(PDO::FETCH_ASSOC);				
foreach ($result as $row) {
   extract($row);
            echo '<meta name="description" content="'.$row['meta_description'].'">';
			echo '<meta name="keywords" content="'.$row['meta_keyword'].'">';
			echo '<title>'.$row['meta_title'].'</title>';
}

$statement = $pdo->prepare("SELECT * FROM tbl_designation WHERE designation_id=?");
$statement->execute(array($designation_id));
$result = $statement->fetchAll(PDO::FETCH_ASSOC);				
foreach ($result as $row)
{
	$designation_name = $row['designation_name'];
}

$stmt = $pdo->prepare("SELECT * FROM tbl_designation WHERE designation_id");
$stmt->execute(); 
$data = $stmt->fetchAll();

?>

<!-- Banner Start -->
<div class="page-banner" style="background-image:url(<?php echo BASE_URL; ?>assets/uploads/<?php echo $banner; ?>);">
	<div class="overlay"></div>
	<div class="container">
		<div class="row">
			<div class="col-md-12">
				<div class="banner-text">
					<h1>Doutor (a): <?php echo $name; ?></h1>
				</div>
			</div>
		</div>
	</div>
</div>
<!-- Banner End -->


<!-- Doctor Start -->
<section class="doctor-detail">
	<div class="container">
		<div class="row">
			<div class="col-md-4">
				<div class="doctor-single">
					<div class="thumb">
						<img src="<?php echo BASE_URL; ?>assets/uploads/<?php echo $photo; ?>" alt="<?php echo $name; ?>">
					</div>
					<div class="text">
						<h2><?php echo $name; ?></h2>
						<h3><?php echo $designation_name; ?></h3>
						<p>
							<?php echo $degree; ?>
						</p>
					</div>
					<div class="social">
						<!--<div class="title">
							Atividades de Mídia Social
						</div>-->
						<ul>
							<?php if($facebook!=''): ?>
								<li><a href="<?php echo $facebook; ?>" target="_blank"><i class="fa fa-facebook"></i></a></li>
							<?php endif; ?>

							<?php if($twitter!=''): ?>
								<li><a href="<?php echo $twitter; ?>" target="_blank"><i class="fa fa-twitter"></i></a></li>
							<?php endif; ?>

							<?php if($linkedin!=''): ?>
								<li><a href="<?php echo $linkedin; ?>" target="_blank"><i class="fa fa-linkedin"></i></a></li>
							<?php endif; ?>

							<?php if($youtube!=''): ?>
								<li><a href="<?php echo $youtube; ?>" target="_blank"><i class="fa fa-youtube"></i></a></li>
							<?php endif; ?>

							<?php if($google_plus!=''): ?>
								<li><a href="<?php echo $google_plus; ?>" target="_blank"><i class="fa fa-google-plus"></i></a></li>
							<?php endif; ?>

							<?php if($instagram!=''): ?>
								<li><a href="<?php echo $instagram; ?>" target="_blank"><i class="fa fa-instagram"></i></a></li>
							<?php endif; ?>

							<?php if($flickr!=''): ?>
								<li><a href="<?php echo $flickr; ?>" target="_blank"><i class="fa fa-flickr"></i></a></li>
							<?php endif; ?>
						</ul>
					</div>
				</div>
			</div>
			<div class="col-md-8">
				
				<!-- Doctor Detail Tab Start -->
				<div class="doctor-detail-tab">
					<ul class="nav nav-tabs">
						<li class=""><a href="#tab1" data-toggle="tab" aria-expanded="false">Sobre</a></li>
						<?php if($address!='' || $phone!='' || $email!='' || $website!=''){?>
						<li class=""><a href="#tab2" data-toggle="tab" aria-expanded="false">Contato</a></li>
						<?php }?>
						<li class="active"><a href="#tab3" data-toggle="tab" aria-expanded="true">Especialidade</a></li>
					</ul>
					
					<!-- Tab Content Start -->
					<div class="tab-content">
						<div class="tab-pane fade" id="tab1">
							<div class="row">										
								<div class="col-md-12">
									<div class="content">
										<?php echo $detail; ?>										
									</div>
								</div>
							</div>
						</div>
						
						<div class="tab-pane fade" id="tab2">
							<div class="row">
								<div class="col-md-12">
									<div class="content">									
										<div class="row">
										<?php if($address!=''): ?>
											<div class="col-md-6">
												<div class="contact">
													<div class="icon"><i class="fa fa-map-o"></i></div>
													<div class="text">
														<h4>Endereço</h4>
														<p>
														    <a href="https://www.google.com/maps/place/<?=$address;?>" 
														    class="maps" style="text-decoration:none;" target="new">
														        <?= $address; ?>
														    </a>
														</p>
													</div>
												</div>
											</div>
										<?php endif; ?>
										<?php if($phone!=''): ?>
											<div class="col-md-6">
												<div class="contact">
													<div class="icon"><i class="fa fa-phone"></i></div>
													<div class="text">
														<h4>Telefone</h4>
														<p>
															<a href="tel:+55<?= $phone; ?>" class="" style="text-decoration:none;">
														        <?= $phone; ?>
														    </a>
														</p>
													</div>
												</div>
											</div>
										<?php endif; ?>
										<?php if($email!=''): ?>
											<div class="col-md-6">
												<div class="contact">
													<div class="icon"><i class="fa fa-envelope"></i></div>
													<div class="text">
														<h4>Email</h4>
														<p>
															<a href="mailto:<?= $email; ?>" class="" style="text-decoration:none;">
														        <?= $email; ?>
														    </a>
														</p>
													</div>
												</div>
											</div>
										<?php endif; ?>
										<?php if($website!=''): ?>
											<div class="col-md-6">
												<div class="contact">
													<div class="icon"><i class="fa fa-globe"></i></div>
													<div class="text">
														<h4>Website</h4>
														<p>
														    <a href="<?= $website; ?>" class="" style="text-decoration:none;">
														        <?= $website; ?>
														    </a>
														</p>
													</div>
												</div>
											</div>
										<?php endif; ?>
										</div>
									</div>
								</div>
							</div>									
						</div>
						
                        <div class="tab-pane fade active in" id="tab3">
                            <div class="row">										
                                <div class="col-md-12">
                                    <div class="content">
                                        <div class = "panel panel-default">
                                           <div class = "panel-heading" style="font-size:18px;">
                                              <b><?= $registro ;?></b>
                                           </div>
                                           
                                           <div class = "panel-body">
                                              <div class="panel-group" id="accordion">
                                            	<div class="panel panel-default">
                                                    <?php
                                                            
                                                            $funs = $pdo->prepare("SELECT * FROM tbl_department_openning_hour");
                                                            $funs->execute();
                                                            $viewss = $funs->fetchAll(PDO::FETCH_ASSOC);
                                                            
                                                                foreach($viewss as $sem){
                                                                    
                                                                }
                                                            
                                                            
                                                            $i=0;
                                                            $medico = $pdo->prepare("SELECT * FROM tbl_doctor 
                                                            AS t1 JOIN tbl_especialidade 
                                                            AS t2 JOIN tbl_department_openning_hour 
                                                            AS t3 JOIN tbl_designation 
                                                            AS t4 
                                                            WHERE t4.designation_id=t2.funcao AND t2.semana=t3.oh_id AND t1.id=t2.medico AND t1.slug=?");
                                                            $medico->execute(array($_REQUEST['slug']));
                                                            $view = $medico->fetchAll(PDO::FETCH_ASSOC);
                                                            foreach($view as $dias){
                                                                $func = $dias["funcao"];
                                                                $i++;
                                                                
                                                                
                                                                //var_dump($dias);
                                                                
                                                    ?>
                                                    
                                            		<div class="panel-heading acordo">
                                            			<h4 class="panel-title">
                                                            <a class="accordion-toggle text-captalize" data-toggle="collapse" 
                                                            data-parent="#accordion" href="#id<?=$i; ?>">
                                                              <i class="fa fa-hand-o-right"></i>
                                                                <?= $dias['designation_name'];?>
                                                            </a>
                                            			</h4>
                                            		</div>
                                            		<div id="id<?= $i; ?>" class="panel-collapse collapse">
                                            			<div class="panel-body">
                                                            <table class="table table-striped">
                                                                <thead>
                                                                    <tr class="text-uppercase">
                                                                        <th>Dias de Atendimento</th>
                                                                        <th>Horário de Atendimento</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    <tr>
                                                                        <td><?= $dias['oh_day']; ?></td>
                                                                        <td>
                                                                            <?php if($dias['inicio'] == "" && $dias['fim'] == ""){ ?>
                                                                            <b>Sob Demanda</b>
                                                                            <?php }else{ ?>
                                                                            <b>
                                                                                <?= date("H:i", strtotime($dias['inicio'])) . "</b> até às <b>" . date("H:i", strtotime($dias['fim'])); ?>
                                                                            </b>
                                                                            <?php } ?>
                                                                        </td>
                                                                    </tr>
                                                                </tbody>
                                                            </table>
                                            			</div>
                                            		</div>
                                                    <?php }  ?>
                                                </div>
                                            </div>
                                           </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
					</div>
					<!-- Tab Content End -->
				</div>
				<!-- Doctor Detail Tab End -->

			</div>
		</div>
	</div>
</section>
<!-- Doctor End -->


<!-- Doctors Start -->
<section class="doctor-v2" style="padding-top:0;">
	<div class="container">
		<div class="row">
			<div class="col-md-12">
				<div class="heading-normal">
					<h2>Nossos Médicos Qualificados</h2>
				</div>
			</div>
		</div>
		<div class="gap-small"></div>
		<div class="row">
			<div class="col-md-12">
				
				<!-- Doctor Carousel Start -->
				<div class="doctor-carousel">
					<?php
					$statement = $pdo->prepare("SELECT 
												
												t1.id,
												t1.name,
												t1.slug,
												t1.designation_id,
												t1.photo,
												t1.facebook,
												t1.twitter,
												t1.linkedin,
												t1.youtube,
												t1.google_plus,
												t1.instagram,
												t1.flickr,

												t2.designation_id,
												t2.designation_name

					                           FROM tbl_doctor t1
					                           JOIN tbl_designation t2
					                           ON t1.designation_id = t2.designation_id
					                           WHERE t1.status = ?
					                           ");
					$statement->execute(array('Active'));
					$result = $statement->fetchAll(PDO::FETCH_ASSOC);							
					foreach ($result as $row) {
						?>
						<div class="item wow fadeInUp">
							<div class="thumb">
								<div class="photo" style="background-image:url(<?php echo BASE_URL; ?>assets/uploads/<?php echo $row['photo']; ?>);"></div>
							</div>
							<div class="text">
								<h3><a href="<?php echo BASE_URL; ?>doctor/<?php echo $row['slug']; ?>"><?php echo $row['name']; ?></a></h3>
								<p><?php echo $row['designation_name']; ?></p>
								<div class="social-icons">
									<ul>
										<?php if($row['facebook']!=''): ?>
											<li><a href="<?php echo $row['facebook']; ?>" target="_blank"><i class="fa fa-facebook"></i></a></li>
										<?php endif; ?>

										<?php if($row['twitter']!=''): ?>
											<li><a href="<?php echo $row['twitter']; ?>" target="_blank"><i class="fa fa-twitter"></i></a></li>
										<?php endif; ?>

										<?php if($row['linkedin']!=''): ?>
											<li><a href="<?php echo $row['linkedin']; ?>" target="_blank"><i class="fa fa-linkedin"></i></a></li>
										<?php endif; ?>

										<?php if($row['youtube']!=''): ?>
											<li><a href="<?php echo $row['youtube']; ?>" target="_blank"><i class="fa fa-youtube"></i></a></li>
										<?php endif; ?>

										<?php if($row['google_plus']!=''): ?>
											<li><a href="<?php echo $row['google_plus']; ?>" target="_blank"><i class="fa fa-google-plus"></i></a></li>
										<?php endif; ?>

										<?php if($row['instagram']!=''): ?>
											<li><a href="<?php echo $row['instagram']; ?>" target="_blank"><i class="fa fa-instagram"></i></a></li>
										<?php endif; ?>

										<?php if($row['flickr']!=''): ?>
											<li>
											    <a href="<?php echo $row['flickr']; ?>" target="_blank">
											        <i class="fa fa-flickr"></i>
											    </a>
											</li>
										<?php endif; ?>
									</ul>
								</div>
							</div>
						</div>
						<?php
					}
					?>
				</div>
				<!-- Doctor Carousel End -->

			</div>
		</div>
	</div>
</section>
<!-- Doctors End -->

<?php require_once('footer.php'); ?>