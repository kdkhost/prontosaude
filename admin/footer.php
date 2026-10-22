		</main>
		<!-- Fim do Conteúdo Principal -->

		<!-- Rodapé do Painel (Footer) -->
		<footer class="app-footer text-body-secondary py-3 px-4 border-top bg-body">
			<div class="float-end d-none d-sm-inline">
				<span class="badge text-bg-success-subtle text-success border border-success-subtle me-1">
					<i class="bi bi-check-circle-fill me-1"></i>PHP 8.4 Ativo
				</span>
				<span class="badge text-bg-info-subtle text-info border border-info-subtle">
					<i class="bi bi-layers-fill me-1"></i>AdminLTE 4
				</span>
			</div>
			<strong>&copy; <?= date('Y'); ?> <a href="<?= BASE_URL ?>" target="_blank" class="text-decoration-none text-primary fw-semibold"><?= htmlspecialchars($empresa); ?></a>.</strong> Sistema Hospitalar e Clínico Integrado.
		</footer>

		<!-- Botão Flutuante Voltar ao Topo no Painel Admin -->
		<button type="button" id="adminBackToTop" class="btn btn-primary shadow-lg rounded-circle" title="Voltar ao Topo" aria-label="Voltar ao Topo" style="position: fixed; bottom: 25px; right: 25px; width: 44px; height: 44px; display: none; align-items: center; justify-content: center; z-index: 1045; transition: opacity 0.3s ease, transform 0.2s ease; border: 2px solid rgba(255,255,255,0.25);">
			<i class="bi bi-arrow-up fs-5"></i>
		</button>

	</div><!-- ./app-wrapper -->

	<!-- Bibliotecas Core do AdminLTE 4 -->
	<script src="js/jquery-2.2.4.min.js"></script>
	<script src="js/overlayscrollbars.browser.es6.min.js"></script>
	<script src="js/bootstrap5.bundle.min.js"></script>
	<script src="js/adminlte4.min.js"></script>
	
	<!-- Bibliotecas de KPIs, Gráficos e Notificações -->
	<script src="js/apexcharts.min.js"></script>
	<script src="js/sweetalert2.all.min.js"></script>
	<script src="js/toastr.min.js"></script>

	<!-- Plugins de Formulários e Tabelas -->
	<script src="js/jquery.dataTables.min.js"></script>
	<script src="js/dataTables.bootstrap.min.js"></script>
	<script src="js/dataTables.responsive.min.js"></script>
	<script src="js/responsive.bootstrap4.min.js"></script>
	<script src="js/select2.full.min.js"></script>
	<script src="js/moment.min.js"></script>
	<script src="js/bootstrap-datepicker.js"></script>
	<script src="js/jscolor.js"></script>
	<script src="js/on-off-switch.js"></script>
	<script src="ckeditor/ckeditor.js"></script>
	<script>
	if (window.jQuery && !window.jQuery.fn.tooltip) {
		window.jQuery.fn.tooltip = function () { return this; };
	}
	</script>
	<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-bs5.min.js"></script>
	<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/lang/summernote-pt-BR.min.js"></script>

	<!-- Utilitário Global Pronto Saúde (SweetAlert2, Toastr, DarkMode, Confirmações) -->
	<script src="js/pronto-global.js?v=<?= time(); ?>"></script>
	<script src="js/pronto-uploader.js?v=<?= time(); ?>"></script>
	<script src="js/pronto-slug.js?v=<?= time(); ?>"></script>
	<script src="js/pronto-masks.js?v=<?= time(); ?>"></script>
	<script src="js/pronto-security.js?v=<?= time(); ?>"></script>
	<script src="js/pronto-spa.js?v=<?= time(); ?>"></script>

	<script>
	$(function () {
		// Inicialização de Tabelas DataTables em Português
		if ($.fn.dataTable) {
			$('#example1, #example2, table.table-bordered, table.table-striped, table.table-datatable').each(function() {
				if (!$.fn.DataTable.isDataTable(this) && !$(this).hasClass('no-datatable')) {
					$(this).addClass('dt-responsive nowrap');
					$(this).closest('.table-responsive').css({
						'overflow-x': 'clip',
						'overflow-y': 'visible'
					});
					$(this).DataTable({
						"language": {
							"sEmptyTable": "Nenhum registro encontrado",
							"sInfo": "Mostrando de _START_ até _END_ de _TOTAL_ registros",
							"sInfoEmpty": "Mostrando 0 até 0 de 0 registros",
							"sInfoFiltered": "(Filtrados de _MAX_ registros)",
							"sInfoThousands": ".",
							"sLengthMenu": "_MENU_ registros por página",
							"sLoadingRecords": "Carregando...",
							"sProcessing": "Processando...",
							"sZeroRecords": "Nenhum registro encontrado",
							"sSearch": "Pesquisar:",
							"oPaginate": {
								"sNext": "Próximo",
								"sPrevious": "Anterior",
								"sFirst": "Primeiro",
								"sLast": "Último"
							},
							"oAria": {
								"sSortAscending": ": Ordenar colunas de forma ascendente",
								"sSortDescending": ": Ordenar colunas de forma descendente"
							}
						},
						"paging": true,
						"lengthChange": true,
						"searching": true,
						"ordering": true,
						"info": true,
						"autoWidth": false,
						"responsive": true,
						"columnDefs": [
							{ responsivePriority: 1, targets: 0 },
							{ responsivePriority: 2, targets: -1 }
						]
					});
				}
			});

			$(window).on('resize.dtResp orientationchange.dtResp', function() {
				if ($.fn.dataTable) {
					try {
						$($.fn.dataTable.tables(true)).DataTable().columns.adjust().responsive.recalc();
					} catch(e) {}
				}
			});
		}

		// Inicialização segura do Select2
		if ($.fn.select2) {
			$('.select2').select2({
				width: '100%',
				language: 'pt-BR'
			});
		}

		// Inicialização segura do Datepicker
		if ($.fn.datepicker) {
			$('#datepicker, #datepicker1').datepicker({
				autoclose: true,
				format: 'dd-mm-yyyy',
				todayBtn: 'linked'
			});
		}

		// Inicialização Universal do Summernote como padrão para todos os textarea
		if ($.fn.summernote) {
			function initSummernote(scope) {
				const $context = scope ? $(scope) : $(document);
				$context.find('textarea').each(function () {
					const $ta = $(this);
					// Pula se já inicializado ou se marcado explicitamente como sem editor
					if ($ta.next().hasClass('note-editor') || 
						$ta.hasClass('no-summernote') || 
						$ta.hasClass('no-editor') || 
						$ta.attr('data-no-summernote') !== undefined || 
						$ta.attr('data-no-editor') !== undefined) {
						return;
					}

					// Pula campos técnicos que não devem ter HTML rico (código iframe, listas brutas de IP, chaves/segurança)
					const name = ($ta.attr('name') || '').toLowerCase();
					const id = ($ta.attr('id') || '').toLowerCase();
					if (name.includes('iframe') || name.includes('ip_') || id.includes('iframe') || $ta.hasClass('font-monospace')) {
						return;
					}

					// Configuração dinâmica de altura baseada em rows ou padrão
					const rows = parseInt($ta.attr('rows') || '5', 10);
					const calculatedHeight = Math.min(450, Math.max(180, rows * 36));

					$ta.summernote({
						tabsize: 2,
						height: calculatedHeight,
						lang: 'pt-BR',
						placeholder: $ta.attr('placeholder') || 'Escreva o conteúdo aqui...',
						dialogsInBody: true,
						toolbar: [
							['style', ['style']],
							['font', ['bold', 'italic', 'underline', 'clear']],
							['fontname', ['fontname']],
							['color', ['color']],
							['para', ['ul', 'ol', 'paragraph']],
							['table', ['table']],
							['insert', ['link', 'picture', 'video', 'hr']],
							['view', ['fullscreen', 'codeview', 'help']]
						]
					});
				});
			}

			initSummernote();

			// Garante que o valor do Summernote seja sempre sincronizado com o textarea original antes do envio
			$(document).on('submit', 'form', function () {
				$(this).find('textarea').each(function () {
					const $ta = $(this);
					if ($ta.next().hasClass('note-editor') && $ta.summernote) {
						$ta.val($ta.summernote('code'));
					}
				});
			});
		}
		// Controle do Botão Voltar ao Topo no Painel Admin
		const btnAdminTop = document.getElementById('adminBackToTop');
		if (btnAdminTop) {
			window.addEventListener('scroll', function () {
				if (window.scrollY > 200) {
					btnAdminTop.style.display = 'flex';
					btnAdminTop.style.opacity = '1';
				} else {
					btnAdminTop.style.opacity = '0';
					setTimeout(function () {
						if (window.scrollY <= 200) btnAdminTop.style.display = 'none';
					}, 250);
				}
			});
			btnAdminTop.addEventListener('click', function (e) {
				e.preventDefault();
				window.scrollTo({ top: 0, behavior: 'smooth' });
			});
		}
	});
	</script>
</body>
</html>