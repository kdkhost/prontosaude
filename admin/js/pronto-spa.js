/**
 * Pronto Saúde - Motor SPA (Single Page Application) Desacoplado
 * Navegação e Submissão 100% via AJAX sem refresh e sem piscar tela
 * AdminLTE 4 + Bootstrap 5.3 + PHP 8.4
 */

(function () {
  'use strict';

  // 1. BARRA DE PROGRESSO NO TOPO
  const progressBar = document.createElement('div');
  progressBar.id = 'ps-spa-progressbar';
  progressBar.style.cssText = `
    position: fixed;
    top: 0;
    left: 0;
    height: 3px;
    width: 0%;
    background: linear-gradient(90deg, #0a6b74, #10b981, #06b6d4);
    z-index: 99999;
    transition: width 0.3s ease, opacity 0.3s ease;
    opacity: 0;
    box-shadow: 0 0 10px rgba(10, 107, 116, 0.7);
  `;
  document.body.appendChild(progressBar);

  let progressInterval = null;

  function startProgress() {
    clearInterval(progressInterval);
    progressBar.style.opacity = '1';
    progressBar.style.width = '15%';
    let w = 15;
    progressInterval = setInterval(() => {
      if (w < 85) {
        w += Math.random() * 12;
        progressBar.style.width = Math.min(w, 85) + '%';
      }
    }, 150);
  }

  function finishProgress() {
    clearInterval(progressInterval);
    progressBar.style.width = '100%';
    setTimeout(() => {
      progressBar.style.opacity = '0';
      setTimeout(() => {
        progressBar.style.width = '0%';
      }, 300);
    }, 200);
  }

  // 2. CICLO DE RE-INICIALIZAÇÃO DE COMPONENTES
  function reinitAllComponents(container) {
    container = container || document.querySelector('.app-main') || document;

    // A. Tooltips Bootstrap 5
    if (window.bootstrap && window.bootstrap.Tooltip) {
      const tooltipTriggerList = container.querySelectorAll('[data-bs-toggle="tooltip"], [title]:not([data-bs-toggle])');
      tooltipTriggerList.forEach(el => {
        try {
          if (!bootstrap.Tooltip.getInstance(el)) {
            new bootstrap.Tooltip(el, {
              boundary: document.body,
              delay: { show: 100, hide: 50 }
            });
          }
        } catch (e) {}
      });
    }

    // B. DataTables em Português com Extensão Responsiva (Estilo AdminLTE 3 / 4)
    if (window.jQuery && jQuery.fn.dataTable) {
      const tables = container.querySelectorAll('table.table:not(.dataTable):not(.no-datatable), #example1, #example2, table.table-bordered, table.table-striped, table.table-datatable');
      tables.forEach(tbl => {
        try {
          if (!jQuery.fn.DataTable.isDataTable(tbl)) {
            tbl.classList.add('dt-responsive', 'nowrap');
            tbl.style.width = '100%';

            // Permite ao DataTables Responsive acionar os breakpoints sem corte de scroll
            const parentResponsive = tbl.closest('.table-responsive');
            if (parentResponsive) {
              parentResponsive.style.overflowX = 'clip';
              parentResponsive.style.overflowY = 'visible';
            }

            const dtInstance = jQuery(tbl).DataTable({
              language: {
                sEmptyTable: "Nenhum registro encontrado",
                sInfo: "Mostrando de _START_ até _END_ de _TOTAL_ registros",
                sInfoEmpty: "Mostrando 0 até 0 de 0 registros",
                sInfoFiltered: "(Filtrados de _MAX_ registros)",
                sLengthMenu: "_MENU_ registros por página",
                sLoadingRecords: "Carregando...",
                sProcessing: "Processando...",
                sZeroRecords: "Nenhum registro encontrado",
                sSearch: "Pesquisar:",
                oPaginate: {
                  sNext: "Próximo",
                  sPrevious: "Anterior",
                  sFirst: "Primeiro",
                  sLast: "Último"
                },
                oAria: {
                  sSortAscending: ": Ordenar colunas de forma ascendente",
                  sSortDescending: ": Ordenar colunas de forma descendente"
                }
              },
              paging: true,
              lengthChange: true,
              searching: true,
              ordering: true,
              info: true,
              autoWidth: false,
              responsive: true,
              columnDefs: [
                { responsivePriority: 1, targets: 0 },
                { responsivePriority: 2, targets: -1 }
              ]
            });

            setTimeout(() => {
              try { dtInstance.columns.adjust().responsive.recalc(); } catch (e) {}
            }, 120);
          }
        } catch (e) {}
      });
    }

    // C. Summernote Global
    if (window.jQuery && jQuery.fn.summernote) {
      try {
        if (!window.jQuery.fn.tooltip) {
          window.jQuery.fn.tooltip = function () { return this; };
        }
        const editorSelector = '.summernote, [id="1"], [id="2"], [id="3"], textarea.editor, textarea#editor1, textarea[name="content"], textarea[name="news_content"], textarea[name="page_content"], textarea[name="description"], textarea[name="detail"], textarea[name="detalhes"], textarea[name="requisitos"], textarea[name="beneficios"], textarea[name="faq_content"], textarea[name="comment"]';
        const editors = container.querySelectorAll(editorSelector);
        editors.forEach(ed => {
          try {
            if (!jQuery(ed).next().hasClass('note-editor')) {
              jQuery(ed).summernote({
                tabsize: 2,
                height: 220,
                lang: 'pt-BR',
                toolbar: [
                  ['style', ['style']],
                  ['font', ['bold', 'italic', 'underline', 'clear']],
                  ['color', ['color']],
                  ['para', ['ul', 'ol', 'paragraph']],
                  ['table', ['table']],
                  ['insert', ['link', 'picture', 'video']],
                  ['view', ['fullscreen', 'codeview']]
                ]
              });
            }
          } catch (e) {}
        });
      } catch (e) {}
    }

    // D. Select2
    if (window.jQuery && jQuery.fn.select2) {
      try {
        jQuery(container).find('.select2').select2({
          width: '100%',
          language: 'pt-BR'
        });
      } catch (e) {}
    }

    // E. Datepicker
    if (window.jQuery && jQuery.fn.datepicker) {
      try {
        jQuery(container).find('#datepicker, #datepicker1').datepicker({
          autoclose: true,
          format: 'dd-mm-yyyy',
          todayBtn: 'linked'
        });
      } catch (e) {}
    }

    // F. Drag and Drop de Imagens com Preview
    if (window.initImageDropzones) {
      window.initImageDropzones(container);
    }

    // G. Gerador Automático de Slugs com Bloqueio Manual
    if (window.initSlugAutoGenerator) {
      window.initSlugAutoGenerator(container);
    }

    // H. Máscaras Universais e ViaCEP com Autofocus
    if (window.initUniversalMasks) {
      window.initUniversalMasks(container);
    }

    // I. Painel de Segurança e WAF
    if (window.initSecurityPanel) {
      window.initSecurityPanel(container);
    }

    // J. ApexCharts (se estiver na página inicial/dashboard)
    if (container.querySelector('#chart-indicadores') && window.renderDashboardCharts) {
      const isDark = document.documentElement.getAttribute('data-bs-theme') === 'dark';
      window.renderDashboardCharts(isDark);
    }

    // K. Notificações Toastr a partir de Callouts (Converte qualquer mensagem em Toastr e remove da tela)
    let hadCalloutNotification = false;
    const callouts = container.querySelectorAll('.callout, .alert:not(.alert-keep):not(.alert-info)');
    callouts.forEach(c => {
      const txt = c.innerText.trim();
      if (txt) {
        hadCalloutNotification = true;
        if (c.classList.contains('callout-success') || c.classList.contains('alert-success')) {
          window.notificar && window.notificar('success', txt, 'Sucesso!');
        } else if (c.classList.contains('callout-danger') || c.classList.contains('alert-danger')) {
          window.notificar && window.notificar('error', txt, 'Atenção!');
        } else if (c.classList.contains('callout-warning') || c.classList.contains('alert-warning')) {
          window.notificar && window.notificar('warning', txt, 'Alerta');
        }
      }
      // Remove o elemento visual da página para não duplicar na tela
      c.remove();
    });
    return hadCalloutNotification;
  }

  window.reinitAllComponents = reinitAllComponents;

  // 3. ATUALIZAÇÃO DO MENU LATERAL ATIVO COM MAPEAMENTO TREEVIEW
  function updateActiveSidebar(url) {
    const filename = url.split('/').pop().split('?')[0];

    // Mapeia páginas de adição/edição para o arquivo base da sidebar
    let targetFile = filename;
    if (targetFile.includes('-add.php') || targetFile.includes('-edit.php')) {
      targetFile = targetFile.replace('-add.php', '.php').replace('-edit.php', '.php');
    }
    if (targetFile === 'vagas.php') {
      targetFile = 'trabalhe-conosco.php';
    }

    const sidebarLinks = document.querySelectorAll('.sidebar-menu a.nav-link');

    // Remove active anterior
    sidebarLinks.forEach(link => link.classList.remove('active'));
    document.querySelectorAll('.sidebar-menu .nav-item.menu-open').forEach(item => {
      item.classList.remove('menu-open');
    });

    sidebarLinks.forEach(link => {
      const href = link.getAttribute('href') || '';
      const linkFilename = href.split('/').pop().split('?')[0];

      if (linkFilename && (linkFilename === filename || linkFilename === targetFile)) {
        link.classList.add('active');

        // Se estiver dentro de um treeview, abre o treeview e ativa o menu pai
        const parentTreeview = link.closest('.nav-treeview');
        if (parentTreeview) {
          const parentNavItem = parentTreeview.closest('.nav-item');
          if (parentNavItem) {
            parentNavItem.classList.add('menu-open');
            const parentLink = parentNavItem.querySelector('a.nav-link');
            if (parentLink) parentLink.classList.add('active');
          }
        }
      }
    });
  }

  // Suporte ao clique manual para alternar menus treeview
  document.addEventListener('click', function(e) {
    const treeviewToggle = e.target.closest('.sidebar-menu .nav-item > a.nav-link[href="#"]');
    if (!treeviewToggle) return;
    e.preventDefault();
    const parentNavItem = treeviewToggle.closest('.nav-item');
    if (parentNavItem) {
      parentNavItem.classList.toggle('menu-open');
    }
  });

  // 4. CARREGADOR DE PÁGINA SPA VIA AJAX
  function loadPageSPA(url, pushState = true) {
    startProgress();

    const mainContainer = document.querySelector('.app-main');
    if (mainContainer) {
      mainContainer.style.transition = 'opacity 0.2s ease';
      mainContainer.style.opacity = '0.35';
    }

    fetch(url, {
      method: 'GET',
      headers: {
        'X-Requested-With': 'XMLHttpRequest',
        'X-AdminLTE-SPA': '1'
      }
    })
    .then(response => {
      // Se redirecionar para fora ou login
      if (response.redirected && response.url.includes('login.php')) {
        window.location.href = response.url;
        return;
      }
      return response.text().then(html => ({ html, finalUrl: response.url || url }));
    })
    .then(data => {
      if (!data) return;
      const { html, finalUrl } = data;

      // Cria um DOM virtual para parsear
      const parser = new DOMParser();
      const doc = parser.parseFromString(html, 'text/html');

      // Extrai título
      const newTitle = doc.querySelector('title') ? doc.querySelector('title').innerText : document.title;
      document.title = newTitle;

      // Extrai o conteúdo principal (.app-main ou .content-wrapper ou fallback)
      let newContent = doc.querySelector('.app-main');
      if (!newContent) {
        newContent = doc.querySelector('.content-wrapper');
      }
      if (!newContent) {
        newContent = doc.querySelector('body');
      }

      if (mainContainer && newContent) {
        mainContainer.innerHTML = newContent.innerHTML;
        mainContainer.style.opacity = '1';
      }

      // Atualiza URL no histórico
      if (pushState) {
        window.history.pushState({ url: finalUrl }, newTitle, finalUrl);
      }

      // Atualiza sidebar ativa
      updateActiveSidebar(finalUrl);

      // Re-executa inicializações de plugins
      reinitAllComponents(mainContainer);

      // Rola para o topo suavemente
      window.scrollTo({ top: 0, behavior: 'smooth' });

      finishProgress();
    })
    .catch(error => {
      console.error('Erro no carregamento SPA:', error);
      finishProgress();
      if (mainContainer) mainContainer.style.opacity = '1';
      window.location.href = url; // Fallback para carregamento tradicional seguro
    });
  }

  window.loadPageSPA = loadPageSPA;

  // 5. INTERCEPTADOR GLOBAL DE CLIQUES EM LINKS INTERNOS
  document.addEventListener('click', function (e) {
    const link = e.target.closest('a');
    if (!link) return;

    const href = link.getAttribute('href');
    if (!href || href === '#' || href.startsWith('javascript:') || href.startsWith('mailto:') || href.startsWith('tel:') || href.startsWith('https://wa.me')) return;

    // Ignora links que abrem nova aba
    if (link.getAttribute('target') === '_blank' || link.getAttribute('target') === 'new') return;

    // Ignora botões de logout ou exclusão
    if (href.includes('logout.php') || href.includes('-delete.php') || link.getAttribute('data-href')) return;

    // Ignora links de download
    if (href.endsWith('.pdf') || href.endsWith('.zip') || href.endsWith('.csv') || href.endsWith('.xlsx')) return;

    // Apenas links internos dentro do admin
    const currentHost = window.location.host;
    const urlObj = new URL(href, window.location.href);

    if (urlObj.host === currentHost && urlObj.pathname.includes('/admin/')) {
      e.preventDefault();
      loadPageSPA(urlObj.href, true);
    }
  });

  // 6. INTERCEPTADOR GLOBAL DE SUBMISSÃO DE FORMULÁRIOS VIA AJAX
  document.addEventListener('submit', function (e) {
    const form = e.target;
    // Permite que formulários com data-no-spa sejam submetidos normalmente caso necessário
    if (form.getAttribute('data-no-spa') === 'true') return;

    // Apenas formulários dentro do painel
    const formAction = form.getAttribute('action') || window.location.href;
    const urlObj = new URL(formAction, window.location.href);

    if (urlObj.pathname.includes('/admin/')) {
      e.preventDefault();
      startProgress();

      const submitBtn = e.submitter || form.querySelector('button[type="submit"], input[type="submit"]');
      let originalBtnHtml = '';
      if (submitBtn) {
        originalBtnHtml = submitBtn.innerHTML || submitBtn.value;
        submitBtn.disabled = true;
        if (submitBtn.tagName === 'INPUT') {
          submitBtn.value = 'Salvando...';
        } else {
          submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Salvando...';
        }
      }

      // Sincroniza qualquer editor Summernote com o textarea antes de empacotar o FormData
      if (window.jQuery && jQuery.fn.summernote) {
        jQuery(form).find('.note-editor').each(function () {
          const $textarea = jQuery(this).prev('textarea');
          if ($textarea.length) {
            $textarea.val($textarea.summernote('code'));
          }
        });
      }

      const formData = new FormData(form);
      // Se houver submitter com nome/valor, anexa
      if (e.submitter && e.submitter.name) {
        formData.append(e.submitter.name, e.submitter.value || '');
      }

      fetch(urlObj.href, {
        method: form.method || 'POST',
        body: formData,
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
          'X-AdminLTE-SPA': '1'
        }
      })
      .then(response => {
        if (response.redirected && response.url.includes('login.php')) {
          window.location.href = response.url;
          return;
        }
        return response.text().then(html => ({ html, finalUrl: response.url || urlObj.href }));
      })
      .then(data => {
        if (!data) return;
        const { html, finalUrl } = data;

        const parser = new DOMParser();
        const doc = parser.parseFromString(html, 'text/html');

        const newTitle = doc.querySelector('title') ? doc.querySelector('title').innerText : document.title;
        document.title = newTitle;

        let newContent = doc.querySelector('.app-main') || doc.querySelector('.content-wrapper') || doc.querySelector('body');
        const mainContainer = document.querySelector('.app-main');

        if (mainContainer && newContent) {
          mainContainer.innerHTML = newContent.innerHTML;
        }

        window.history.pushState({ url: finalUrl }, newTitle, finalUrl);
        updateActiveSidebar(finalUrl);
        const hadCallout = reinitAllComponents(mainContainer);
        window.scrollTo({ top: 0, behavior: 'smooth' });

        finishProgress();

        if (submitBtn) {
          submitBtn.disabled = false;
          if (submitBtn.tagName === 'INPUT') {
            submitBtn.value = originalBtnHtml;
          } else {
            submitBtn.innerHTML = originalBtnHtml;
          }
        }

        // Se nenhum callout específico foi retornado pelo backend, emite notificação padrão
        if (!hadCallout && window.notificar) {
          window.notificar('success', 'Dados atualizados com sucesso!', 'Salvo!');
        }
      })
      .catch(err => {
        console.error('Erro no envio AJAX do formulário:', err);
        finishProgress();
        if (submitBtn) {
          submitBtn.disabled = false;
          if (submitBtn.tagName === 'INPUT') {
            submitBtn.value = originalBtnHtml;
          } else {
            submitBtn.innerHTML = originalBtnHtml;
          }
        }
        if (window.notificar) {
          window.notificar('error', 'Ocorreu um problema ao salvar os dados. Tente novamente.', 'Falha');
        }
      });
    }
  });

  // 7. SUPORTE A HISTÓRICO DO NAVEGADOR (BACK/FORWARD)
  window.addEventListener('popstate', function (e) {
    if (e.state && e.state.url) {
      loadPageSPA(e.state.url, false);
    } else {
      loadPageSPA(window.location.href, false);
    }
  });

  // 8. INICIALIZAÇÃO INICIAL APÓS O CARREGAMENTO DA PÁGINA
  document.addEventListener('DOMContentLoaded', function () {
    // Guarda o estado inicial
    window.history.replaceState({ url: window.location.href }, document.title, window.location.href);
    reinitAllComponents(document);
  });

})();
