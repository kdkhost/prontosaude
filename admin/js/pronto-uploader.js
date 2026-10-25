/**
 * Pronto Saúde - Upload de Imagens com Drag & Drop e Preview em Tempo Real
 * Suporte a todos os formulários do sistema via AJAX e preview nativo
 */

(function () {
  'use strict';

  function initImageDropzones(container) {
    container = container || document;
    const fileInputs = container.querySelectorAll('input[type="file"]');

    fileInputs.forEach(input => {
      // Evita duplicar se já foi inicializado
      if (input.dataset.dropzoneInitialized === 'true') return;
      input.dataset.dropzoneInitialized = 'true';

      // Verifica se é campo de imagem
      const name = (input.getAttribute('name') || '').toLowerCase();
      const accept = (input.getAttribute('accept') || '').toLowerCase();
      const isImage = accept.includes('image') || 
                      name.includes('photo') || 
                      name.includes('foto') || 
                      name.includes('logo') || 
                      name.includes('favicon') || 
                      name.includes('banner') || 
                      name.includes('imagem') || 
                      name.includes('img') || 
                      name.includes('pic');

      if (!isImage && !accept.includes('image')) return;

      // Cria a estrutura visual da DropZone
      const wrapper = document.createElement('div');
      wrapper.className = 'ps-dropzone-wrapper my-2';

      const dropzone = document.createElement('div');
      dropzone.className = 'ps-dropzone border border-2 border-dashed rounded-4 p-4 text-center bg-body-tertiary position-relative';
      dropzone.style.cursor = 'pointer';
      dropzone.style.transition = 'all 0.25s ease';

      // Preview existente (se houver imagem cadastrada anteriormente ao lado do input)
      let existingImgSrc = '';
      const parentFormGroup = input.closest('.form-group, .mb-3');
      if (parentFormGroup) {
        const existingImg = parentFormGroup.querySelector('img:not(.brand-image):not(.user-image)');
        if (existingImg && existingImg.getAttribute('src')) {
          existingImgSrc = existingImg.getAttribute('src');
          existingImg.style.display = 'none'; // oculta o elemento estático antigo
        }
      }

      dropzone.innerHTML = `
        <div class="ps-dropzone-content">
          <div class="ps-dropzone-icon mb-2 text-primary fs-1">
            <i class="bi bi-cloud-arrow-up-fill"></i>
          </div>
          <div class="fw-bold fs-6 mb-1 text-body">Arraste e solte sua imagem aqui</div>
          <p class="text-body-secondary small mb-2">ou <span class="text-primary text-decoration-underline fw-semibold">clique para selecionar do dispositivo</span></p>
          <div class="badge bg-secondary-subtle text-secondary small">Formatos aceitos: JPG, PNG, GIF, WEBP</div>
        </div>
        <div class="ps-dropzone-preview d-none mt-2 position-relative">
          <div class="d-inline-block position-relative shadow-sm rounded-3 overflow-hidden border bg-body">
            <img class="ps-preview-img img-fluid" style="max-height: 180px; max-width: 100%; object-fit: contain; display: block;" alt="Preview" />
            <button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0 m-1 rounded-circle p-1 d-flex align-items-center justify-content-center ps-btn-remove" style="width: 26px; height: 26px;" title="Remover seleção">
              <i class="bi bi-x fs-6"></i>
            </button>
          </div>
          <div class="ps-file-info small text-body-secondary mt-1 fw-semibold"></div>
        </div>
      `;

      // Insere na DOM
      input.parentNode.insertBefore(wrapper, input);
      wrapper.appendChild(dropzone);
      wrapper.appendChild(input);

      // Oculta o input real, mantendo-o funcional para o formulário
      input.style.position = 'absolute';
      input.style.width = '1px';
      input.style.height = '1px';
      input.style.padding = '0';
      input.style.margin = '-1px';
      input.style.overflow = 'hidden';
      input.style.clip = 'rect(0,0,0,0)';
      input.style.border = '0';

      const content = dropzone.querySelector('.ps-dropzone-content');
      const preview = dropzone.querySelector('.ps-dropzone-preview');
      const previewImg = dropzone.querySelector('.ps-preview-img');
      const fileInfo = dropzone.querySelector('.ps-file-info');
      const removeBtn = dropzone.querySelector('.ps-btn-remove');

      // Se já tinha imagem existente antes de qualquer alteração, exibe no preview
      if (existingImgSrc) {
        previewImg.src = existingImgSrc;
        fileInfo.textContent = 'Imagem atualmente ativa no sistema';
        content.classList.add('d-none');
        preview.classList.remove('d-none');
      }

      // Clique para selecionar
      dropzone.addEventListener('click', (e) => {
        if (e.target.closest('.ps-btn-remove')) return;
        input.click();
      });

      // Drag and Drop Events
      ['dragenter', 'dragover'].forEach(eventName => {
        dropzone.addEventListener(eventName, (e) => {
          e.preventDefault();
          e.stopPropagation();
          dropzone.classList.add('border-primary', 'bg-primary-subtle');
          dropzone.style.transform = 'scale(1.01)';
        });
      });

      ['dragleave', 'drop'].forEach(eventName => {
        dropzone.addEventListener(eventName, (e) => {
          e.preventDefault();
          e.stopPropagation();
          dropzone.classList.remove('border-primary', 'bg-primary-subtle');
          dropzone.style.transform = 'none';
        });
      });

      dropzone.addEventListener('drop', (e) => {
        const dt = e.dataTransfer;
        const files = dt.files;
        if (files && files.length > 0) {
          input.files = files;
          handleFile(files[0]);
        }
      });

      // Change no input
      input.addEventListener('change', () => {
        if (input.files && input.files.length > 0) {
          handleFile(input.files[0]);
        }
      });

      function handleFile(file) {
        if (!file.type.startsWith('image/')) {
          if (window.notificar) {
            window.notificar('error', 'Por favor, selecione apenas arquivos de imagem válidos (JPG, PNG, GIF, WEBP).', 'Formato Inválido');
          } else if (typeof toastr !== 'undefined') {
            toastr.error('Por favor, selecione apenas arquivos de imagem válidos (JPG, PNG, GIF, WEBP).', 'Formato Inválido');
          }
          return;
        }

        const reader = new FileReader();
        reader.onload = (e) => {
          previewImg.src = e.target.result;
          const sizeKb = (file.size / 1024).toFixed(1);
          fileInfo.textContent = `${file.name} (${sizeKb} KB)`;
          content.classList.add('d-none');
          preview.classList.remove('d-none');
          
          if (window.notificar) {
            window.notificar('info', `Imagem pronta: ${file.name}`, 'Preview Carregado');
          }
        };
        reader.readAsDataURL(file);
      }

      // Botão de remover imagem selecionada
      removeBtn.addEventListener('click', (e) => {
        e.stopPropagation();
        input.value = '';
        previewImg.src = '';
        fileInfo.textContent = '';
        preview.classList.add('d-none');
        content.classList.remove('d-none');
      });
    });
  }

  window.initImageDropzones = initImageDropzones;

  document.addEventListener('DOMContentLoaded', () => {
    initImageDropzones();
  });
})();
