<?php require_once('header.php'); ?>
		
<?php
// Preventing the direct access of this page.
$slug = trim($_REQUEST['slug'] ?? '');
if(empty($slug))
{
	header('location: '.BASE_URL);
	exit;
}

// Getting the news detailed data from the news slug
$statement = $pdo->prepare("SELECT
							t1.news_title,
							t1.news_slug,
							t1.news_content,
							t1.news_date,
							t1.publisher,
							t1.photo,
							t1.category_id,
							t2.category_name,
							t2.category_slug
                           	FROM tbl_news t1
                           	LEFT JOIN tbl_category t2
                           	ON t1.category_id = t2.category_id
                           	WHERE t1.news_slug=?");
$statement->execute(array($slug));
$row = $statement->fetch(PDO::FETCH_ASSOC);

if (!$row) {
	header('location: '.BASE_URL);
	exit;
}

$news_title    = $row['news_title'] ?? '';
$news_content  = $row['news_content'] ?? '';
$news_date     = $row['news_date'] ?? '';
$publisher     = $row['publisher'] ?? '';
$photo         = $row['photo'] ?? '';
$category_id   = $row['category_id'] ?? '';
$category_slug = $row['category_slug'] ?? '';
$category_name = $row['category_name'] ?? '';

// Atualização atômica e otimizada da contagem de visualizações
$update_stmt = $pdo->prepare("UPDATE tbl_news SET total_view = total_view + 1 WHERE news_slug=?");
$update_stmt->execute(array($slug));
?>


<?php
$news_banner_src = (!empty($photo) && file_exists('assets/uploads/' . $photo)) ? BASE_URL . 'assets/uploads/' . $photo : BASE_URL . 'assets/uploads/department-1.png';
?>
<!-- Banner Start -->
<div class="page-banner" style="background-image: url(<?= $news_banner_src; ?>);">
	<div class="overlay"></div>
	<div class="container">
		<div class="row">
			<div class="col-md-12">
				<div class="banner-text">
					<h1><?php echo htmlspecialchars($news_title); ?></h1>
				</div>
			</div>
		</div>
	</div>
</div>
<!-- Banner End -->


<!-- Blog Start -->
<section class="ps-blog-section">
	<div class="container">
		<div class="row g-4">
			<div class="col-lg-8">
				
				<div class="ps-blog-main-area">
					<!-- Post Item Start -->
					<article class="post-item" style="background: #ffffff; border-radius: 16px; padding: 28px 32px; box-shadow: 0 10px 30px rgba(0,0,0,0.06); border: 1px solid #e2e8f0; margin-bottom: 30px;">
						<?php if(!empty($photo)): ?>
							<div class="image-holder" style="border-radius: 12px; overflow: hidden; margin-bottom: 24px; box-shadow: 0 4px 14px rgba(0,0,0,0.08);">
								<img class="img-responsive" src="<?php echo BASE_URL; ?>assets/uploads/<?php echo $photo ?>" alt="<?php echo htmlspecialchars($news_title); ?>" style="width: 100%; max-height: 480px; object-fit: cover;">
							</div>
						<?php endif; ?>
						
						<div class="text">
							<h2 style="font-size: 26px; font-weight: 800; color: #0f172a; margin-top: 0; margin-bottom: 14px; line-height: 1.35;">
								<?php echo htmlspecialchars($news_title); ?>
							</h2>
							
							<ul class="status" style="margin-bottom: 24px; display: flex; flex-wrap: wrap; gap: 10px; padding: 0; list-style: none;">
								<?php if(!empty($category_name)): ?>
									<li>
										<a href="<?php echo BASE_URL; ?>category/<?php echo $category_slug; ?>" style="background: #e6f3f4; color: #0a6b74; font-weight: 700; font-size: 13px; padding: 5px 14px; border-radius: 20px; text-decoration: none; display: inline-flex; align-items: center; gap: 5px;">
											<i class="fas fa-tag"></i> <?php echo htmlspecialchars($category_name); ?>
										</a>
									</li>
								<?php endif; ?>
								<?php if(!empty($news_date)): ?>
									<li style="background: #f1f5f9; color: #64748b; font-weight: 600; font-size: 13px; padding: 5px 14px; border-radius: 20px; display: inline-flex; align-items: center; gap: 5px;">
										<i class="far fa-calendar-alt text-primary"></i> <?php echo htmlspecialchars($news_date); ?>
									</li>
								<?php endif; ?>
							</ul>

							<div class="article-content" style="color: #334155; font-size: 16px; line-height: 1.85; margin-bottom: 30px;">
								<?php echo $news_content; ?>
							</div>
							
							<div style="padding-top: 20px; border-top: 1px solid #f1f5f9; margin-bottom: 24px;">
								<h4 style="font-size: 16px; font-weight: 700; color: #0a6b74; margin-bottom: 12px;">
									<i class="fas fa-share-alt me-1"></i> Compartilhe este artigo:
								</h4>
								<div class="sharethis-inline-share-buttons"></div>
							</div>

							<div style="margin-top: 16px;">
								<a href="<?= BASE_URL; ?>page/blog" class="btn btn-outline-secondary fw-bold px-4 py-2 rounded-3 text-decoration-none d-inline-flex align-items-center">
									<i class="fas fa-arrow-left me-2"></i> Voltar ao Blog
								</a>
							</div>
						</div>
					</article>

					<!-- Comentários -->
					<div style="background: #ffffff; border-radius: 16px; padding: 24px; box-shadow: 0 10px 30px rgba(0,0,0,0.06); border: 1px solid #e2e8f0; margin-bottom: 30px;">
						<h3 style="font-size: 19px; font-weight: 800; color: #0a6b74; margin-top: 0; margin-bottom: 16px;">
							<i class="far fa-comments me-2"></i> Comentários e Opiniões
						</h3>
						<?php
						$final_url = BASE_URL . 'news/' . $_REQUEST['slug'];
						?>
						<div class="fb-comments" data-href="<?php echo $final_url; ?>" data-numposts="5" data-width="100%"></div>
					</div>
					<!-- Post Item End -->

				</div>

			</div>
			<div class="col-lg-4">
				<?php require_once('sidebar.php'); ?>
			</div>
		</div>
	</div>
</section>
<!-- Blog End -->


<?php require_once('footer.php'); ?>