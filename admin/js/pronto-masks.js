/**
 * Pronto Saúde - Sistema Universal de Máscaras e Auto-preenchimento ViaCEP
 * Máscaras: Telefone, Celular, WhatsApp, CEP, CPF, CNPJ, Documento, Data, Hora, Moeda
 * Auto-preenchimento ViaCEP em tempo real com auto-focus no campo de número
 */

(function () {
  'use strict';

  // ========================================================
  // 1. FUNÇÕES PURAS DE FORMATAÇÃO E MÁSCARAS (PT-BR)
  // ========================================================

  function maskPhone(val) {
    if (!val) return '';
    let digits = val.replace(/\D/g, '').substring(0, 11);
    if (digits.length <= 10) {
      // (99) 9999-9999
      return digits.replace(/^(\d{2})(\d)/, '($1) $2')
                   .replace(/(\d{4})(\d)/, '$1-$2');
    } else {
      // (99) 99999-9999
      return digits.replace(/^(\d{2})(\d)/, '($1) $2')
                   .replace(/(\d{5})(\d)/, '$1-$2');
    }
  }

  function maskCep(val) {
    if (!val) return '';
    let digits = val.replace(/\D/g, '').substring(0, 8);
    return digits.replace(/^(\d{5})(\d)/, '$1-$2');
  }

  function maskCpf(val) {
    if (!val) return '';
    let digits = val.replace(/\D/g, '').substring(0, 11);
    return digits.replace(/^(\d{3})(\d)/, '$1.$2')
                 .replace(/^(\d{3})\.(\d{3})(\d)/, '$1.$2.$3')
                 .replace(/\.(\d{3})(\d)/, '.$1-$2');
  }

  function maskCnpj(val) {
    if (!val) return '';
    let digits = val.replace(/\D/g, '').substring(0, 14);
    return digits.replace(/^(\d{2})(\d)/, '$1.$2')
                 .replace(/^(\d{2})\.(\d{3})(\d)/, '$1.$2.$3')
                 .replace(/\.(\d{3})(\d)/, '.$1/$2')
                 .replace(/\/(\d{4})(\d)/, '/$1-$2');
  }

  function maskDoc(val) {
    if (!val) return '';
    let digits = val.replace(/\D/g, '').substring(0, 14);
    return digits.length > 11 ? maskCnpj(digits) : maskCpf(digits);
  }

  function maskDate(val) {
    if (!val) return '';
    let digits = val.replace(/\D/g, '').substring(0, 8);
    return digits.replace(/^(\d{2})(\d)/, '$1/$2')
                 .replace(/^(\d{2})\/(\d{2})(\d)/, '$1/$2/$3');
  }

  function maskTime(val) {
    if (!val) return '';
    let digits = val.replace(/\D/g, '').substring(0, 4);
    return digits.replace(/^(\d{2})(\d)/, '$1:$2');
  }

  function maskMoney(val) {
    if (!val) return '';
    let digits = val.replace(/\D/g, '');
    if (!digits) return '';
    let num = (parseInt(digits, 10) / 100).toFixed(2);
    let parts = num.split('.');
    parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    return 'R$ ' + parts.join(',');
  }

  // ========================================================
  // 2. APLICAÇÃO DINÂMICA DE MÁSCARAS NOS INPUTS
  // ========================================================

  function attachMask(input, maskFn) {
    if (input.dataset.psMaskBound === 'true') return;
    input.dataset.psMaskBound = 'true';

    // Aplica valor inicial formatado se houver
    if (input.value) {
      input.value = maskFn(input.value);
    }

    input.addEventListener('input', function (e) {
      const prevVal = input.value;
      const formatted = maskFn(prevVal);
      if (prevVal !== formatted) {
        input.value = formatted;
      }
    });
  }

  function initUniversalMasks(container) {
    container = container || document;

    // A. Telefones / WhatsApp
    const phoneInputs = container.querySelectorAll(
      'input.mask-phone, input.mask-telefone, input.mask-whatsapp, ' +
      'input[name*="phone"], input[name*="telefone"], input[name*="whatsapp"], ' +
      'input[name*="celular"], input[name*="dep_fax"], input[type="tel"]'
    );
    phoneInputs.forEach(input => attachMask(input, maskPhone));

    // B. CEP
    const cepInputs = container.querySelectorAll('input.mask-cep, input[name*="cep"], input[id*="cep"]');
    cepInputs.forEach(input => attachMask(input, maskCep));

    // C. CPF
    const cpfInputs = container.querySelectorAll('input.mask-cpf, input[name*="cpf"]');
    cpfInputs.forEach(input => attachMask(input, maskCpf));

    // D. CNPJ
    const cnpjInputs = container.querySelectorAll('input.mask-cnpj, input[name*="cnpj"]');
    cnpjInputs.forEach(input => attachMask(input, maskCnpj));

    // E. Documentos Dinâmicos (CPF ou CNPJ)
    const docInputs = container.querySelectorAll('input.mask-doc, input[name*="documento"]');
    docInputs.forEach(input => attachMask(input, maskDoc));

    // F. Data
    const dateInputs = container.querySelectorAll('input.mask-date, input.mask-data, input[name*="data"]:not([type="date"])');
    dateInputs.forEach(input => attachMask(input, maskDate));

    // G. Hora
    const timeInputs = container.querySelectorAll('input.mask-time, input.mask-hora, input[name*="oh_time"], input[name*="hora"]:not([type="time"])');
    timeInputs.forEach(input => attachMask(input, maskTime));

    // H. Moeda / Dinheiro
    const moneyInputs = container.querySelectorAll('input.mask-money, input.mask-moeda, input[name*="preco"], input[name*="valor"], input[name*="salario"], input[name*="pricing_plan_price"]');
    moneyInputs.forEach(input => attachMask(input, maskMoney));

    // I. Inicializa escuta de busca automática ViaCEP
    initViaCepLookup(container);
  }

  // ========================================================
  // 3. AUTO-PREENCHIMENTO VIACEP COM AUTO-FOCUS NO NÚMERO
  // ========================================================

  function initViaCepLookup(container) {
    container = container || document;
    const cepInputs = container.querySelectorAll('input.mask-cep, input[name*="cep"], input[id*="cep"]');

    cepInputs.forEach(cepInput => {
      if (cepInput.dataset.psViaCepBound === 'true') return;
      cepInput.dataset.psViaCepBound = 'true';

      let lastQueriedCep = '';

      cepInput.addEventListener('keyup', function () {
        const rawCep = cepInput.value.replace(/\D/g, '');

        if (rawCep.length === 8 && rawCep !== lastQueriedCep) {
          lastQueriedCep = rawCep;
          consultarCep(rawCep, cepInput);
        }
      });
    });
  }

  function consultarCep(cep, cepInput) {
    const form = cepInput.closest('form') || document;

    // Feedback visual sutil de busca
    cepInput.style.transition = 'all 0.2s';
    cepInput.style.boxShadow = '0 0 0 0.25rem rgba(13, 202, 240, 0.25)';

    fetch(`https://viacep.com.br/ws/${cep}/json/`)
      .then(response => response.json())
      .then(data => {
        cepInput.style.boxShadow = '';

        if (data.erro) {
          if (window.notificar) {
            window.notificar('warning', 'CEP não encontrado na base dos Correios. Por favor, preencha o endereço manualmente.', 'Atenção');
          }
          return;
        }

        // 1. Procura campo de número para o auto focus
        const numeroInput = form.querySelector('#numero_endereco, input[name*="numero"], input[name*="number"], input[name*="num"]');

        // 2. Procura campo de endereço principal (textarea ou input)
        const addressField = form.querySelector('textarea[name*="address"], input[name*="address"], textarea[name*="endereco"], input[name*="endereco"]');

        // 3. Procura campos separados (se existirem)
        const ruaInput = form.querySelector('input[name*="rua"], input[name*="logradouro"]');
        const bairroInput = form.querySelector('input[name*="bairro"]');
        const cidadeInput = form.querySelector('input[name*="cidade"]');
        const ufInput = form.querySelector('input[name*="uf"], select[name*="uf"]');

        if (ruaInput) ruaInput.value = data.logradouro || '';
        if (bairroInput) bairroInput.value = data.bairro || '';
        if (cidadeInput) cidadeInput.value = data.localidade || '';
        if (ufInput) ufInput.value = data.uf || '';

        // Formatação composta no campo consolidado de endereço (ex: dep_address ou contact_address)
        let enderecoBase = `${data.logradouro}, Nº `;
        let enderecoResto = `${data.bairro} - ${data.localidade}/${data.uf}, CEP ${maskCep(cep)}`;

        if (addressField) {
          const numAtual = numeroInput ? numeroInput.value.trim() : '';
          addressField.value = `${data.logradouro}, Nº ${numAtual || ''}, ${data.bairro} - ${data.localidade}/${data.uf}, CEP ${maskCep(cep)}`;
          addressField.dispatchEvent(new Event('change', { bubbles: true }));
        }

        // Sincronização ao digitar no campo de número
        if (numeroInput && addressField) {
          numeroInput.oninput = function () {
            const n = numeroInput.value.trim();
            addressField.value = `${data.logradouro}, Nº ${n}, ${data.bairro} - ${data.localidade}/${data.uf}, CEP ${maskCep(cep)}`;
          };
        }

        // 4. AUTO-FOCUS NO CAMPO NÚMERO ASSIM QUE LOCALIZAR O ENDEREÇO
        if (numeroInput) {
          setTimeout(() => {
            numeroInput.focus();
            numeroInput.select();
          }, 100);
        } else if (addressField) {
          setTimeout(() => {
            addressField.focus();
            // Posiciona o cursor no "Nº "
            const pos = enderecoBase.length;
            if (addressField.setSelectionRange) {
              addressField.setSelectionRange(pos, pos);
            }
          }, 100);
        }

        if (window.notificar) {
          window.notificar('success', `${data.logradouro}, ${data.bairro} - ${data.localidade}/${data.uf}`, 'Endereço Localizado');
        }
      })
      .catch(err => {
        cepInput.style.boxShadow = '';
        console.error('Erro na consulta ViaCEP:', err);
      });
  }

  // Exporta globalmente
  window.initUniversalMasks = initUniversalMasks;
  window.maskPhone = maskPhone;
  window.maskCep = maskCep;
  window.maskCpf = maskCpf;
  window.maskCnpj = maskCnpj;
  window.maskMoney = maskMoney;

  document.addEventListener('DOMContentLoaded', function () {
    initUniversalMasks(document);
  });
})();
