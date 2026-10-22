<?php
require_once('header.php');

// Preventing the direct access of this page.
$slug = trim($_REQUEST['slug'] ?? '');
if(empty($slug))
{
	header('location: index.php');
	exit;
}

// Getting the detailed data of a page from page slug
$statement = $pdo->prepare("SELECT * FROM tbl_page WHERE page_slug=? AND status=?");
$statement->execute(array($slug, 'Active'));
$row = $statement->fetch(PDO::FETCH_ASSOC);

if (!$row)
{
	header('location: index.php');
	exit;
}

$page_name    = $row['page_name'] ?? '';
$page_slug    = $row['page_slug'] ?? '';
$page_content = $row['page_content'] ?? '';
$page_layout  = $row['page_layout'] ?? '';
$banner       = $row['banner'] ?? '';
$status       = $row['status'] ?? '';
?>
		
		
<!-- Banner Start -->
<?php
$page_banner_src = (!empty($banner) && file_exists('assets/uploads/' . $banner)) ? BASE_URL . 'assets/uploads/' . $banner : BASE_URL . 'assets/uploads/department-1.png';
?>
<div class="page-banner" style="background-image: url(<?php echo $page_banner_src; ?>)">
	<div class="overlay"></div>
	<div class="container">
		<div class="row">
			<div class="col-md-12">
				<div class="banner-text">
					<h1><?php echo htmlspecialchars($page_name); ?></h1>
				</div>
			</div>
		</div>
	</div>
</div>
<!-- Banner End -->


<?php if($page_layout == 'Full Width Page Layout'): ?>
<section class="about-v2" style="padding: 60px 0; background: #f8fafc;">
	<div class="container">
		<div class="row">
			<div class="col-md-12">
				<div style="background: #ffffff; border-radius: 16px; padding: 36px 32px; box-shadow: 0 10px 30px rgba(0,0,0,0.06); border: 1px solid #e2e8f0; font-size: 15.5px; line-height: 1.85; color: #334155;">
					<?php echo $page_content; ?>
				</div>
			</div>
		</div>
	</div>
</section>
<?php endif; ?>


<?php if($page_layout == 'Contact Us Page Layout'): ?>
<?php
	$statement = $pdo->prepare("SELECT * FROM tbl_settings WHERE id=1");
	$statement->execute();
	$result = $statement->fetchAll(PDO::FETCH_ASSOC);							
	foreach ($result as $row) 
	{
		$contact_map_iframe = $row['contact_map_iframe'];
	}
?>
<section class="contact-v1">
	<div class="container">
		<div class="row">
			<div class="col-md-12">
				<div class="heading-normal">
					<h2>Formulário de Contato</h2>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-md-7">

<?php
// After form submit checking everything for email sending
if(isset($_POST['form_contact']))
{
	$error_message = '';
	$success_message = '';
	$statement = $pdo->prepare("SELECT * FROM tbl_settings WHERE id=1");
	$statement->execute();
	$result = $statement->fetchAll(PDO::FETCH_ASSOC);							
	foreach ($result as $row) 
	{
		$receive_email = $row['receive_email'];
		$receive_email_subject = $row['receive_email_subject'];
		$receive_email_thank_you_message = $row['receive_email_thank_you_message'];
		$recaptcha_status = $row['recaptcha_status'] ?? 0;
		$recaptcha_secret_key = $row['recaptcha_secret_key'] ?? '';
	}

    $valid = 1;

	// 1. Proteção Honeypot Anti-Spam (invisível para humanos, preenchido por robôs)
	if(!empty($_POST['website_hp'])) {
		$valid = 0;
		$error_message .= 'Envio bloqueado por suspeita de automação maliciosa.<br>';
	}

    if(empty($_POST['visitor_name']))
    {
        $valid = 0;
        $error_message .= 'Por favor, insira seu nome.<br>';
    }

    if(empty($_POST['visitor_phone']))
    {
        $valid = 0;
        $error_message .= 'Por favor, digite seu número de telefone.<br>';
    }


    if(empty($_POST['visitor_email']))
    {
        $valid = 0;
        $error_message .= 'Por favor, indique o seu endereço de e-mail.<br>';
    }
    else
    {
    	// Email validation check
        if(!filter_var($_POST['visitor_email'], FILTER_VALIDATE_EMAIL))
        {
            $valid = 0;
            $error_message .= 'Por favor insira um endereço de e-mail válido.<br>';
        }
    }

    if(empty($_POST['visitor_comment']))
    {
        $valid = 0;
        $error_message .= 'Por favor, insira seu comentário.<br>';
    }

	// 2. Validação do Google reCAPTCHA se ativado no painel
	if($valid == 1 && !empty($recaptcha_status) && !empty($recaptcha_secret_key)) {
		$recaptcha_response = $_POST['g-recaptcha-response'] ?? '';
		if(empty($recaptcha_response)) {
			$valid = 0;
			$error_message .= 'Por favor, confirme que você não é um robô resolvendo o reCAPTCHA.<br>';
		} else {
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, "https://www.google.com/recaptcha/api/siteverify");
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
				'secret'   => $recaptcha_secret_key,
				'response' => $recaptcha_response,
				'remoteip' => $_SERVER['REMOTE_ADDR'] ?? ''
			]));
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_TIMEOUT, 6);
			$verify_response = curl_exec($ch);
			curl_close($ch);

			$captcha_obj = json_decode($verify_response);
			if(!$captcha_obj || empty($captcha_obj->success)) {
				$valid = 0;
				$error_message .= 'Falha na verificação de segurança do reCAPTCHA. Por favor, tente novamente.<br>';
			}
		}
	}

    if($valid == 1)
    {

        // sending email
        $to_admin = $receive_email;
        $subject = $receive_email_subject;
		$message = '
<html><body>
<table>
<tr>
<td>Name</td>
<td>'.$_POST['visitor_name'].'</td>
</tr>
<tr>
<td>Email</td>
<td>'.$_POST['visitor_email'].'</td>
</tr>
<tr>
<td>Phone</td>
<td>'.$_POST['visitor_phone'].'</td>
</tr>
<tr>
<td>Comment</td>
<td>'.nl2br($_POST['visitor_comment']).'</td>
</tr>
</table>
</body></html>
';
$visitor_email = $_POST['visitor_email'];
		$headers = 'From: ' . $visitor_email . "\r\n" .
				   'Reply-To: ' . $visitor_email . "\r\n" .
				   'X-Mailer: PHP/' . phpversion() . "\r\n" . 
				   "MIME-Version: 1.0\r\n" . 
				   "Content-Type: text/html; charset=UTF-8\r\n";

		// Sending email to admin				   
        mail($to_admin, $subject, $message, $headers); 
		
        $success_message = $receive_email_thank_you_message;

    }

	// Resposta JSON para submissões via AJAX
	if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
		header('Content-Type: application/json; charset=utf-8');
		if(!empty($error_message)) {
			echo json_encode(['success' => false, 'message' => strip_tags(str_replace('<br>', "\n", $error_message))]);
		} else {
			echo json_encode(['success' => true, 'message' => $success_message]);
		}
		exit;
	}
}
?>
				
				<?php
				if(!empty($error_message))
				{
					echo '<div class="error">';
					echo $error_message;
					echo '</div>';
				} 
				if(!empty($success_message))
				{
					echo '<div class="success">';
					echo $success_message;
					echo '</div>';
				}
				?>	


				<div style="background: #fff; border-radius: 16px; padding: 32px; box-shadow: 0 10px 30px rgba(0,0,0,0.06); border: 1px solid #e2e8f0; margin-bottom: 30px;">
					<h3 style="font-size: 22px; font-weight: 800; color: #0a6b74; margin-top: 0; margin-bottom: 20px;">
						Envie-nos uma Mensagem
					</h3>
					<form action="<?php echo BASE_URL; ?>page/<?php echo $_REQUEST['slug']; ?>" class="form-horizontal cform-1" method="post">
						<!-- Campo Honeypot Invisível Anti-Spam -->
						<input type="text" name="website_hp" style="display:none !important; position:absolute; left:-9999px;" tabindex="-1" autocomplete="off">

						<div class="form-group" style="margin-bottom: 16px;">
							<div class="col-sm-12">
								<label style="font-weight: 600; font-size: 13.5px; color: #334155; margin-bottom: 6px; display: block;">Seu Nome Completo *</label>
								<input type="text" class="form-control" placeholder="Digite seu nome" name="visitor_name" required style="border-radius: 8px; padding: 12px 14px;">
							</div>
						</div>
						<div class="form-group" style="margin-bottom: 16px;">
							<div class="col-sm-12">
								<label style="font-weight: 600; font-size: 13.5px; color: #334155; margin-bottom: 6px; display: block;">Seu E-mail *</label>
								<input type="email" class="form-control" placeholder="exemplo@email.com" name="visitor_email" required style="border-radius: 8px; padding: 12px 14px;">
							</div>
						</div>
						<div class="form-group" style="margin-bottom: 16px;">
							<div class="col-sm-12">
								<label style="font-weight: 600; font-size: 13.5px; color: #334155; margin-bottom: 6px; display: block;">Telefone / WhatsApp *</label>
								<input type="text" class="form-control" placeholder="(00) 00000-0000" name="visitor_phone" required style="border-radius: 8px; padding: 12px 14px;">
							</div>
						</div>
						<div class="form-group" style="margin-bottom: 18px;">
							<div class="col-sm-12">
								<label style="font-weight: 600; font-size: 13.5px; color: #334155; margin-bottom: 6px; display: block;">Mensagem ou Dúvida *</label>
								<textarea name="visitor_comment" class="form-control" cols="30" rows="6" placeholder="Como podemos ajudar você hoje?" required style="border-radius: 8px; padding: 12px 14px;"></textarea>
							</div>
						</div>

						<?php if(!empty($recaptcha_status) && !empty($recaptcha_site_key)): ?>
						<div class="form-group" style="margin-bottom: 18px;">
							<div class="col-sm-12">
								<div class="g-recaptcha mb-2" data-sitekey="<?php echo htmlspecialchars($recaptcha_site_key); ?>"></div>
								<script src="https://www.google.com/recaptcha/api.js" async defer></script>
							</div>
						</div>
						<?php endif; ?>

						<div class="form-group" style="margin-bottom: 0;">
							<div class="col-sm-12">
								<button type="submit" class="btn btn-success" name="form_contact" style="background: #10b981; border: none; border-radius: 8px; font-weight: 700; padding: 12px 28px; box-shadow: 0 4px 14px rgba(16,185,129,0.35); font-size: 15px;">
									<i class="fa fa-paper-plane" style="margin-right: 6px;"></i> Enviar Mensagem
								</button>
							</div>
						</div>
					</form>
				</div>
			</div>
			<div class="col-md-5">
				<div style="background: #fff; border-radius: 16px; padding: 24px; box-shadow: 0 10px 30px rgba(0,0,0,0.06); border: 1px solid #e2e8f0; margin-bottom: 30px;">
					<h3 style="font-size: 20px; font-weight: 800; color: #0a6b74; margin-top: 0; margin-bottom: 16px;">
						Nossa Localização
					</h3>
					<div class="google-map" style="border-radius: 12px; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.08); margin-bottom: 20px;">
						<?php echo $contact_map_iframe; ?>
					</div>
					<?php if(!empty($contact_phone) || !empty($contact_email) || !empty($contact_address)): ?>
						<div style="background: #f8fafc; border-radius: 10px; padding: 16px; border: 1px solid #f1f5f9;">
							<?php if(!empty($contact_address)): ?>
								<div style="display: flex; align-items: flex-start; gap: 10px; margin-bottom: 10px;">
									<i class="fa fa-map-marker text-success" style="font-size: 16px; margin-top: 3px;"></i>
									<span style="font-size: 13.5px; color: #334155;"><?= htmlspecialchars($contact_address); ?></span>
								</div>
							<?php endif; ?>
							<?php if(!empty($contact_phone)): ?>
								<div style="display: flex; align-items: center; gap: 10px; margin-bottom: 10px;">
									<i class="fa fa-phone text-success" style="font-size: 16px;"></i>
									<a href="tel:+55<?= preg_replace('/[^0-9]/', '', $contact_phone); ?>" style="font-size: 13.5px; color: #0a6b74; font-weight: 600; text-decoration: none;">
										<?= htmlspecialchars($contact_phone); ?>
									</a>
								</div>
							<?php endif; ?>
							<?php if(!empty($contact_email)): ?>
								<div style="display: flex; align-items: center; gap: 10px;">
									<i class="fa fa-envelope text-success" style="font-size: 14px;"></i>
									<a href="mailto:<?= htmlspecialchars($contact_email); ?>" style="font-size: 13.5px; color: #0a6b74; text-decoration: none;">
										<?= htmlspecialchars($contact_email); ?>
									</a>
								</div>
							<?php endif; ?>
						</div>
					<?php endif; ?>
				</div>
			</div>
			
		</div>
	</div>
</section>
<?php endif; ?>

<?php if($page_layout == 'FAQ Page Layout'): ?>
<section class="faq" style="padding: 60px 0; background: #f8fafc;">
	<div class="container">
		<div class="row">
			<div class="col-md-12">			
				
				<?php
				$i=0;
				$j=0;
				$statement = $pdo->prepare("SELECT * FROM tbl_faq_category ORDER BY faq_category_id ASC");
				$statement->execute();
				$result = $statement->fetchAll(PDO::FETCH_ASSOC);							
				foreach ($result as $row) {
					$i++;
					$statement1 = $pdo->prepare("SELECT * FROM tbl_faq WHERE faq_category_id=?");
					$statement1->execute(array($row['faq_category_id']));
					$result1 = $statement1->fetchAll(PDO::FETCH_ASSOC);							
					if (empty($result1)) {
						continue;
					}
				?>
				<div style="margin-bottom: 36px;">
					<h3 style="font-size: 22px; font-weight: 800; color: #0a6b74; margin-bottom: 16px; display: flex; align-items: center; gap: 8px;">
						<i class="fa fa-question-circle"></i> <?php echo htmlspecialchars($row['faq_category_name'] ?? ''); ?>
					</h3>
					<div class="panel-group" id="accordion<?php echo $i; ?>" role="tablist" aria-multiselectable="true">
						<?php
						foreach ($result1 as $row1) {
							$j++;
							?>
							<div class="panel panel-default" style="border: 1px solid #e2e8f0; border-radius: 12px; margin-bottom: 12px; overflow: hidden; box-shadow: 0 4px 10px rgba(0,0,0,0.03);">
								<div class="panel-heading" role="tab" id="heading<?= $j; ?>" style="background: #fff; padding: 16px 20px;">
									<h4 class="panel-title" style="margin: 0; font-size: 16px;">
										<a class="collapsed" role="button" 
										data-toggle="collapse" data-parent="#accordion<?php echo $i; ?>" 
										href="#collapse<?php echo $j; ?>" aria-expanded="false" aria-controls="collapse<?php echo $j; ?>"
										style="color: #0f172a; font-weight: 700; text-decoration: none; display: flex; justify-content: space-between; align-items: center;">
											<span><?php echo $row1['faq_title']; ?></span>
											<i class="fa fa-chevron-down" style="font-size: 13px; opacity: 0.5;"></i>
										</a>
									</h4>
								</div>
								<div id="collapse<?php echo $j; ?>" class="panel-collapse collapse" role="tabpanel" aria-labelledby="heading<?= $j; ?>">
									<div class="panel-body" style="padding: 20px; color: #475569; font-size: 15px; line-height: 1.7; background: #f8fafc; border-top: 1px solid #e2e8f0;">
										<?php echo $row1['faq_content']; ?>
									</div>
								</div>
							</div>
							<?php
						}
						?>
					</div>
				</div>
				<?php
				}
				?>				
			</div>			
		</div>
	</div>
</section>
<?php endif; ?>



<?php if($page_layout == 'Photo Gallery Page Layout'): ?>
<section class="gallery">
	<div class="container">
		<div class="row">
			<div class="col-md-12">

				<ul class="gallery-menu">
					<li class="filter" data-filter="all" data-role="button">Todos</li>
					<?php
					$statement = $pdo->prepare("SELECT * FROM tbl_category_photo WHERE status=?");
					$statement->execute(array('Active'));
					$result = $statement->fetchAll(PDO::FETCH_ASSOC);							
					foreach ($result as $row) {
						$temp_string = strtolower($row['p_category_name']);
    					$temp_slug = preg_replace('/[^A-Za-z0-9-]+/', '-', $temp_string);
    					?>
    					<li class="filter" data-filter=".<?php echo $temp_slug; ?>" data-role="button"><?php echo $row['p_category_name']; ?></li>
						<?php
					}
					?>
				</ul>

				<div id="mix-container">
					<?php
					$i=0;
					$statement = $pdo->prepare("SELECT
					                           	t1.photo_id,
												t1.photo_caption,
												t1.photo_name,
												t1.p_category_id,
												t2.p_category_id,
												t2.p_category_name,
												t2.status
					                            FROM tbl_photo t1
					                            JOIN tbl_category_photo t2
					                            ON t1.p_category_id = t2.p_category_id 
					                            ");
					$statement->execute();
					$result = $statement->fetchAll(PDO::FETCH_ASSOC);							
					foreach ($result as $row) {
						$i++;
						$temp_string = strtolower($row['p_category_name']);
    					$temp_slug = preg_replace('/[^A-Za-z0-9-]+/', '-', $temp_string);
						?>
						<div class="col-md-4 mix <?php echo $temp_slug; ?> all" data-my-order="<?php echo $i; ?>">
							<div class="inner" style="margin-bottom: 24px; border-radius: 14px; overflow: hidden; background: #fff; box-shadow: 0 4px 14px rgba(0,0,0,0.06); border: 1px solid #e2e8f0;">
								<div class="photo" style="background-image:url(<?php echo BASE_URL; ?>assets/uploads/<?php echo $row['photo_name']; ?>); height: 230px; background-size: cover; background-position: center;"></div>
								<div class="overlay"></div>
								<div class="icons">
									<div class="icons-inner">
										<a class="gallery-photo" href="<?php echo BASE_URL; ?>assets/uploads/<?php echo $row['photo_name']; ?>" title="<?php echo htmlspecialchars($row['photo_caption'] ?? ''); ?>">
										    <i class="fa fa-search-plus"></i>
										</a>
									</div>
								</div>
								<?php if(!empty($row['photo_caption'])): ?>
									<div style="padding: 12px 16px; background: #ffffff; border-top: 1px solid #f1f5f9;">
										<p style="margin: 0; font-size: 13.5px; font-weight: 600; color: #334155;"><?php echo htmlspecialchars($row['photo_caption']); ?></p>
									</div>
								<?php endif; ?>
							</div>
						</div>
						<?php
					}
					?>

				</div>

			</div>
		</div>
	</div>
</section>
<?php endif; ?>





<?php if($page_layout == 'Video Gallery Page Layout'): ?>
<section class="gallery">
	<div class="container">
		<div class="row">
			<div class="col-md-12">

				<ul class="gallery-menu">
					<li class="filter" data-filter="all" data-role="button">Todos</li>
					<?php
					$statement = $pdo->prepare("SELECT * FROM tbl_category_video WHERE status=?");
					$statement->execute(array('Active'));
					$result = $statement->fetchAll(PDO::FETCH_ASSOC);							
					foreach ($result as $row) {
						$temp_string = strtolower($row['v_category_name']);
    					$temp_slug = preg_replace('/[^A-Za-z0-9-]+/', '-', $temp_string);
    					?>
    					<li class="filter" data-filter=".<?php echo $temp_slug; ?>" data-role="button">
    					    <?php echo $row['v_category_name']; ?>
    					</li>
						<?php } ?>
				</ul>

				<div id="mix-container">
					<?php
					$i=0;
					$statement = $pdo->prepare("SELECT
					                           	t1.video_id,
												t1.video_title,
												t1.video_iframe,
												t1.v_category_id,
												t2.v_category_id,
												t2.v_category_name,
												t2.status
					                            FROM tbl_video t1
					                            JOIN tbl_category_video t2
					                            ON t1.v_category_id = t2.v_category_id 
					                            ");
					$statement->execute();
					$result = $statement->fetchAll(PDO::FETCH_ASSOC);							
					foreach ($result as $row) {
						$i++;
						$temp_string = strtolower($row['v_category_name']);
    					$temp_slug = preg_replace('/[^A-Za-z0-9-]+/', '-', $temp_string);
						?>
						<div class="col-md-4 mix <?php echo $temp_slug; ?> all" data-my-order="<?php echo $i; ?>">
							<div class="inner viframe" style="margin-bottom: 24px; border-radius: 14px; overflow: hidden; background: #fff; box-shadow: 0 4px 14px rgba(0,0,0,0.06); border: 1px solid #e2e8f0; padding: 12px;">
								<video style="width: 100%; height: auto; border-radius: 10px;" class="parado" controls preload="auto">
                                  <source src="<?=$row['video_iframe']?>" type="video/mp4">
                                    Seu navegador não suporta tags de vídeo.
                                </video>
								<?php if(!empty($row['video_title'])): ?>
									<h4 style="font-size: 15px; font-weight: 700; color: #0f172a; margin: 12px 4px 4px 4px;"><?= htmlspecialchars($row['video_title']); ?></h4>
								<?php endif; ?>
							</div>
						</div>
						<?php
					}
					?>

				</div>

			</div>
		</div>
	</div>
</section>
<?php endif; ?>

    <script>  
        $(document).ready(function($) {
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


<?php if($page_layout == 'Blog Page Layout'): ?>
<section class="blog">
	<div class="container">
		<div class="row">
			<div class="col-md-9">
				
				<!-- Blog Classic Start -->
				<div class="blog-grid">
					<div class="row">
						<div class="col-md-12">
							

							<?php
							$statement = $pdo->prepare("SELECT * FROM tbl_news ORDER BY news_id DESC");
							$statement->execute();
							$total = $statement->rowCount();
							?>

							<?php if(!$total): ?>
							<p style="color:red;">Desculpe! Nenhuma notícia foi encontrada.</p>
							<?php else: ?>




<?php
/* ===================== Pagination Code Starts ================== */
		$adjacents = 10;	
		
		$statement = $pdo->prepare("SELECT * FROM tbl_news ORDER BY news_id DESC");
		$statement->execute();
		$total_pages = $statement->rowCount();
		
		$targetpage = $_SERVER['PHP_SELF'];
		$limit = 5;                                 
		$page = @$_GET['page'];
		if($page) 
			$start = ($page - 1) * $limit;          
		else
			$start = 0;	
		

		$statement = $pdo->prepare("SELECT
								   t1.news_title,
		                           t1.news_slug,
		                           t1.news_content,
		                           t1.news_date,
		                           t1.photo,
		                           t1.category_id,

		                           t2.category_id,
		                           t2.category_name,
		                           t2.category_slug
		                           FROM tbl_news t1
		                           JOIN tbl_category t2
		                           ON t1.category_id = t2.category_id 		                           
		                           ORDER BY t1.news_id 
		                           LIMIT $start, $limit");
		$statement->execute();
		$result = $statement->fetchAll(PDO::FETCH_ASSOC);
		
		
		$s1 = $_REQUEST['slug'];
				
		if ($page == 0) $page = 1;                  
		$prev = $page - 1;                          
		$next = $page + 1;                          
		$lastpage = ceil($total_pages/$limit);      
		$lpm1 = $lastpage - 1;   
		$pagination = "";
		if($lastpage > 1)
		{   
			$pagination .= "<div class=\"pagination\">";
			if ($page > 1) 
				$pagination.= "<a href=\"$targetpage?slug=$s1&page=$prev\">&#171; anterior</a>";
			else
				$pagination.= "<span class=\"disabled\">&#171; anterior</span>";    
			if ($lastpage < 7 + ($adjacents * 2))   //not enough pages to bother breaking it up
			{   
				for ($counter = 1; $counter <= $lastpage; $counter++)
				{
					if ($counter == $page)
						$pagination.= "<span class=\"current\">$counter</span>";
					else
						$pagination.= "<a href=\"$targetpage?slug=$s1&page=$counter\">$counter</a>";                 
				}
			}
			elseif($lastpage > 5 + ($adjacents * 2))    //enough pages to hide some
			{
				if($page < 1 + ($adjacents * 2))        
				{
					for ($counter = 1; $counter < 4 + ($adjacents * 2); $counter++)
					{
						if ($counter == $page)
							$pagination.= "<span class=\"current\">$counter</span>";
						else
							$pagination.= "<a href=\"$targetpage?slug=$s1&page=$counter\">$counter</a>";                 
					}
					$pagination.= "...";
					$pagination.= "<a href=\"$targetpage?slug=$s1&page=$lpm1\">$lpm1</a>";
					$pagination.= "<a href=\"$targetpage?slug=$s1&page=$lastpage\">$lastpage</a>";       
				}
				elseif($lastpage - ($adjacents * 2) > $page && $page > ($adjacents * 2))
				{
					$pagination.= "<a href=\"$targetpage?slug=$s1&page=1\">1</a>";
					$pagination.= "<a href=\"$targetpage?slug=$s1&page=2\">2</a>";
					$pagination.= "...";
					for ($counter = $page - $adjacents; $counter <= $page + $adjacents; $counter++)
					{
						if ($counter == $page)
							$pagination.= "<span class=\"current\">$counter</span>";
						else
							$pagination.= "<a href=\"$targetpage?slug=$s1&page=$counter\">$counter</a>";                 
					}
					$pagination.= "...";
					$pagination.= "<a href=\"$targetpage?slug=$s1&page=$lpm1\">$lpm1</a>";
					$pagination.= "<a href=\"$targetpage?slug=$s1&page=$lastpage\">$lastpage</a>";       
				}
				else
				{
					$pagination.= "<a href=\"$targetpage?slug=$s1&page=1\">1</a>";
					$pagination.= "<a href=\"$targetpage?slug=$s1&page=2\">2</a>";
					$pagination.= "...";
					for ($counter = $lastpage - (2 + ($adjacents * 2)); $counter <= $lastpage; $counter++)
					{
						if ($counter == $page)
							$pagination.= "<span class=\"current\">$counter</span>";
						else
							$pagination.= "<a href=\"$targetpage?slug=$s1&page=$counter\">$counter</a>";                 
					}
				}
			}
			if ($page < $counter - 1) 
				$pagination.= "<a href=\"$targetpage?slug=$s1&page=$next\">próximo &#187;</a>";
			else
				$pagination.= "<span class=\"disabled\">próximo &#187;</span>";
			$pagination.= "</div>\n";       
		}
		/* ===================== Pagination Code Ends ================== */
		?>

							<?php
							foreach ($result as $row) {
								?>
								<div class="post-item">
									<div class="image-holder">
										<img class="img-responsive" src="<?php echo BASE_URL; ?>assets/uploads/<?php echo $row['photo']; ?>" 
										alt="<?php echo $row['news_title']; ?>">
									</div>
									<div class="text">
										<h3><a href="<?php echo BASE_URL; ?>news/<?php echo $row['news_slug']; ?>"><?php echo $row['news_title']; ?></a></h3>
										<ul class="status">
											<li><i class="fa fa-tag"></i>
											Categoria: 
											<a href="<?php echo BASE_URL; ?>category/<?php echo $row['category_slug']; ?>">
											    <?php echo $row['category_name']; ?></a></li>
											<li><i class="fa fa-calendar"></i>Data: <?php echo $row['news_date']; ?></li>
										</ul>
										<p>
											<?php echo substr($row['news_content'],0,200).' ...'; ?>
										</p>
										<p class="button">
											<a href="<?php echo BASE_URL; ?>news/<?php echo $row['news_slug']; ?>">Saiba Mais</a>
										</p>
									</div>
								</div>
								<?php
							}
							?>							
							<?php endif; ?>

						</div>

						<div class="col-md-12">
							<?php if($total): ?>
							<?php echo $pagination; ?>
							<?php endif; ?>
						</div>

					</div>
				</div>
				<!-- Blog Classic End -->

			</div>
			<div class="col-md-3">
				
				<?php require_once('sidebar.php'); ?>
			
			</div>

			


		</div>
	</div>
</section>
<?php endif; ?>



<?php if($page_layout == 'Doctor Page Layout'): ?>
<section class="doctor-v3">
	<div class="container">
		<div class="row">
			<div class="col-md-12">
				
				<!-- Doctor Container Start -->
				<div class="doctor-inner">
					
					<?php
					$statement = $pdo->prepare("SELECT
												
												t1.id,
												t1.name,
												t1.slug,
												t1.designation_id,
												t1.photo,
												t1.degree,
												t1.detail,
												t1.facebook,
												t1.twitter,
												t1.linkedin,
												t1.youtube,
												t1.google_plus,
												t1.instagram,
												t1.flickr,
												t1.address,
												t1.practice_location,
												t1.phone, 
												t1.email,
												t1.website,
												t1.status,

												t2.designation_id,
												t2.designation_name
						
					                            FROM tbl_doctor t1
					                            JOIN tbl_designation t2
					                            ON t1.designation_id = t2.designation_id
					                            WHERE t1.status=?
					                            ");
					$statement->execute(array('Active'));
					$result = $statement->fetchAll(PDO::FETCH_ASSOC);							
					foreach ($result as $row) {
						?>
						<div class="col-md-3 item">
							<div class="inner">
								<div class="thumb">
									<div class="photo" style="background-image:url(<?php echo BASE_URL; ?>assets/uploads/<?php echo $row['photo']; ?>)"></div>
									<div class="overlay"></div>
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
												<li><a href="<?php echo $row['flickr']; ?>" target="_blank"><i class="fa fa-flickr"></i></a></li>
											<?php endif; ?>
										</ul>
									</div>
								</div>
								<div class="text">
									<h3><a href="<?php echo BASE_URL; ?>doctor/<?php echo $row['slug']; ?>"><?php echo $row['name']; ?></a></h3>
									<h4><?php echo $row['designation_name']; ?></h4>
									<p class="button">
										<a href="<?php echo BASE_URL; ?>doctor/<?php echo $row['slug']; ?>">Ver Perfil Completo</a>
									</p>
								</div>
							</div>
						</div>
						<?php
					}
					?>
					
				</div>
				<!-- Doctor Container End -->

			</div>
		</div>
	</div>
</section>
<?php endif; ?>

<?php require_once('footer.php'); ?>