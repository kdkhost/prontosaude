<?php require_once('header.php'); ?>

<?php
// Preventing the direct access of this page.
$search_query = trim($_REQUEST['search_string'] ?? '');
if(empty($search_query))
{
	header('location: ' . BASE_URL . 'page/blog');
	exit;
}
?>

<!-- Banner Start -->
<div class="page-banner" style="background-image: url(<?= BASE_URL; ?>assets/uploads/department-1.png);">
	<div class="overlay"></div>
	<div class="container">
		<div class="row">
			<div class="col-md-12 text-center">
				<div class="banner-text">
					<span class="ps-section-badge mb-2"><i class="fas fa-search me-1"></i> Busca no Blog</span>
					<h1 class="mt-2 mb-2">Busca: "<?php echo htmlspecialchars($search_query); ?>"</h1>
					<p class="lead text-white-50 mx-auto mb-0" style="max-width: 680px; font-size: 1.05rem;">
						Confira os artigos e publicações encontrados para sua pesquisa.
					</p>
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
					<?php
					$search_param = "%" . $search_query . "%";

					$statement = $pdo->prepare("SELECT COUNT(*) FROM tbl_news WHERE news_title LIKE ? OR news_content LIKE ?");
					$statement->execute(array($search_param, $search_param));
					$total = (int)$statement->fetchColumn();
					?>

					<?php if(!$total): ?>
					<div class="card p-5 text-center border-0 shadow-sm rounded-4 bg-white">
						<i class="fas fa-search-minus text-muted mb-3" style="font-size: 3rem; opacity: 0.4;"></i>
						<h4 class="fw-bold text-dark mb-2">Nenhum resultado encontrado</h4>
						<p class="text-muted small mb-4">Não encontramos nenhum artigo correspondente à sua busca por "<strong><?= htmlspecialchars($search_query); ?></strong>".</p>
						<div>
							<a href="<?= BASE_URL; ?>page/blog" class="btn btn-foco-primary py-2 px-4 fw-bold">
								<i class="fas fa-arrow-left me-2"></i> Ver Todos os Artigos
							</a>
						</div>
					</div>
					<?php else: ?>

					<?php
					$adjacents = 2;	
					$total_pages = $total;
					
					$targetpage = BASE_URL . 'search.php';
					$limit = 5;                                 
					$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
					if($page < 1) $page = 1;
					$start = ($page - 1) * $limit;          

					$statement = $pdo->prepare("SELECT
					                           t1.news_title,
					                           t1.news_slug,
					                           t1.news_content,
					                           t1.news_date,
					                           t1.photo,
					                           t1.category_id,
					                           t2.category_name,
					                           t2.category_slug
					                           FROM tbl_news t1
					                           JOIN tbl_category t2
					                           ON t1.category_id = t2.category_id
					                           WHERE t1.news_title LIKE ? OR t1.news_content LIKE ?
					                           ORDER BY t1.news_id DESC
					                           LIMIT $start, $limit");
					$statement->execute(array($search_param, $search_param));
					$result = $statement->fetchAll(PDO::FETCH_ASSOC);
					
					$s1 = urlencode($search_query);
					$prev = $page - 1;                          
					$next = $page + 1;                          
					$lastpage = ceil($total_pages / $limit);      
					$lpm1 = $lastpage - 1;   
					$pagination = "";
					if($lastpage > 1)
					{   
						$pagination .= "<div class=\"ps-blog-pagination\">";
						if ($page > 1) 
							$pagination .= "<a href=\"$targetpage?search_string=$s1&page=$prev\"><i class=\"fas fa-chevron-left me-1\"></i> Anterior</a>";
						else
							$pagination .= "<span class=\"disabled\"><i class=\"fas fa-chevron-left me-1\"></i> Anterior</span>";    
						
						if ($lastpage < 7 + ($adjacents * 2))
						{   
							for ($counter = 1; $counter <= $lastpage; $counter++)
							{
								if ($counter == $page)
									$pagination .= "<span class=\"current\">$counter</span>";
								else
									$pagination .= "<a href=\"$targetpage?search_string=$s1&page=$counter\">$counter</a>";                 
							}
						}
						elseif($lastpage > 5 + ($adjacents * 2))
						{
							if($page < 1 + ($adjacents * 2))        
							{
								for ($counter = 1; $counter < 4 + ($adjacents * 2); $counter++)
								{
									if ($counter == $page)
										$pagination .= "<span class=\"current\">$counter</span>";
									else
										$pagination .= "<a href=\"$targetpage?search_string=$s1&page=$counter\">$counter</a>";                 
								}
								$pagination .= "<span>...</span>";
								$pagination .= "<a href=\"$targetpage?search_string=$s1&page=$lpm1\">$lpm1</a>";
								$pagination .= "<a href=\"$targetpage?search_string=$s1&page=$lastpage\">$lastpage</a>";       
							}
							elseif($lastpage - ($adjacents * 2) > $page && $page > ($adjacents * 2))
							{
								$pagination .= "<a href=\"$targetpage?search_string=$s1&page=1\">1</a>";
								$pagination .= "<a href=\"$targetpage?search_string=$s1&page=2\">2</a>";
								$pagination .= "<span>...</span>";
								for ($counter = $page - $adjacents; $counter <= $page + $adjacents; $counter++)
								{
									if ($counter == $page)
										$pagination .= "<span class=\"current\">$counter</span>";
									else
										$pagination .= "<a href=\"$targetpage?search_string=$s1&page=$counter\">$counter</a>";                 
								}
								$pagination .= "<span>...</span>";
								$pagination .= "<a href=\"$targetpage?search_string=$s1&page=$lpm1\">$lpm1</a>";
								$pagination .= "<a href=\"$targetpage?search_string=$s1&page=$lastpage\">$lastpage</a>";       
							}
							else
							{
								$pagination .= "<a href=\"$targetpage?search_string=$s1&page=1\">1</a>";
								$pagination .= "<a href=\"$targetpage?search_string=$s1&page=2\">2</a>";
								$pagination .= "<span>...</span>";
								for ($counter = $lastpage - (2 + ($adjacents * 2)); $counter <= $lastpage; $counter++)
								{
									if ($counter == $page)
										$pagination .= "<span class=\"current\">$counter</span>";
									else
										$pagination .= "<a href=\"$targetpage?search_string=$s1&page=$counter\">$counter</a>";                 
								}
							}
						}
						if ($page < $lastpage) 
							$pagination .= "<a href=\"$targetpage?search_string=$s1&page=$next\">Próximo <i class=\"fas fa-chevron-right ms-1\"></i></a>";
						else
							$pagination .= "<span class=\"disabled\">Próximo <i class=\"fas fa-chevron-right ms-1\"></i></span>";
						$pagination .= "</div>\n";       
					}
					?>

					<!-- Lista de Resultados da Busca -->
					<div class="ps-blog-list">
						<?php
						foreach ($result as $row) {
							$n_photo = (!empty($row['photo']) && file_exists(__DIR__ . '/assets/uploads/' . $row['photo']))
								? BASE_URL . 'assets/uploads/' . $row['photo']
								: BASE_URL . 'assets/uploads/department-1.png';
							
							$content_plain = strip_tags($row['news_content']);
							$read_minutes = max(1, ceil(str_word_count($content_plain) / 160));
							$n_link = BASE_URL . 'news/' . $row['news_slug'];
							$cat_link = BASE_URL . 'category/' . $row['category_slug'];
							?>
							<article class="ps-blog-card">
								<div class="row g-0 align-items-center">
									<div class="col-md-5 mb-3 mb-md-0">
										<a href="<?= $n_link; ?>" class="ps-blog-thumb-wrap d-block">
											<img src="<?= $n_photo; ?>" alt="<?= htmlspecialchars($row['news_title']); ?>" class="ps-blog-thumb" loading="lazy">
										</a>
									</div>
									<div class="col-md-7 ps-md-3">
										<div class="ps-blog-card-body">
											<div class="ps-blog-meta-top">
												<a href="<?= $cat_link; ?>" class="ps-blog-cat-badge">
													<i class="fas fa-tag"></i> <?= htmlspecialchars($row['category_name']); ?>
												</a>
												<span class="ps-blog-read-time">
													<i class="far fa-clock"></i> <?= $read_minutes; ?> min de leitura
												</span>
											</div>
											
											<h3 class="ps-blog-title">
												<a href="<?= $n_link; ?>">
													<?= htmlspecialchars($row['news_title']); ?>
												</a>
											</h3>
											
											<p class="ps-blog-excerpt">
												<?= mb_strimwidth($content_plain, 0, 150, '...'); ?>
											</p>
											
											<div class="ps-blog-card-footer">
												<span class="ps-blog-date">
													<i class="far fa-calendar-alt text-primary"></i> <?= htmlspecialchars($row['news_date']); ?>
												</span>
												<a href="<?= $n_link; ?>" class="btn-blog-read">
													Ler Artigo <i class="fas fa-arrow-right"></i>
												</a>
											</div>
										</div>
									</div>
								</div>
							</article>
							<?php
						}
						?>
					</div>

					<!-- Paginação -->
					<?php if($total): ?>
					<div class="mt-4">
						<?= $pagination; ?>
					</div>
					<?php endif; ?>

					<?php endif; ?>
				</div>
			</div>
			
			<!-- Barra Lateral -->
			<div class="col-lg-4">
				<?php require_once('sidebar.php'); ?>
			</div>
		</div>
	</div>
</section>
<!-- Blog End -->

<?php require_once('footer.php'); ?>