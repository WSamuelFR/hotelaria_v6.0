document.addEventListener('DOMContentLoaded', () => {
    const typeSwitch = document.getElementById('cadastroTypeSwitch');
    const hospedeFields = document.getElementById('hospedeFields');
    const empresaFields = document.getElementById('empresaFields');
    const form = document.getElementById('cadastroForm');
    const messageBox = document.getElementById('message-box');
    const submitButton = document.getElementById('submitButton');
    const typeBadge = document.getElementById('currentTypeBadge');

    const inputCep = document.getElementById('cep');
    const inputCpf = document.getElementById('cpf');
    const inputCnpj = document.getElementById('cnpj');
    const inputSenha = document.getElementById('senha');

    // --- FUNÇÃO DE VALIDAÇÃO DE CPF (Algoritmo Oficial Corrigido) ---
    const validarCPF = (cpf) => {
        if (!cpf) return false;

        // Remove qualquer coisa que não seja número
        cpf = cpf.replace(/\D/g, '');

        // Deve ter 11 dígitos
        if (cpf.length !== 11) return false;

        // Elimina CPFs com todos os números iguais
        if (/^(\d)\1{10}$/.test(cpf)) return false;

        const calcularDigito = (base) => {
            let soma = 0;
            for (let i = 0; i < base.length; i++) {
                soma += parseInt(base[i]) * (base.length + 1 - i);
            }

            const resto = soma % 11;
            return resto < 2 ? 0 : 11 - resto;
        };

        // Primeiro dígito
        const digito1 = calcularDigito(cpf.slice(0, 9));
        if (digito1 !== parseInt(cpf[9])) return false;

        // Segundo dígito
        const digito2 = calcularDigito(cpf.slice(0, 10));
        if (digito2 !== parseInt(cpf[10])) return false;

        return true;
    };

    // --- Função para atualizar o Badge de Tipo ---
    const updateBadge = (isHospede) => {
        if (typeBadge) {
            typeBadge.textContent = isHospede ? 'Hóspede (Pessoa Física)' : 'Empresa (Pessoa Jurídica)';
        }
    };

    // --- Função para alternar visibilidade e atributos ---
    const toggleFields = (isHospede) => {
        const h_inputs = hospedeFields.querySelectorAll('input, select');
        const e_inputs = empresaFields.querySelectorAll('input, select');

        if (isHospede) {
            hospedeFields.classList.remove('d-none');
            empresaFields.classList.add('d-none');
            h_inputs.forEach(input => input.setAttribute('required', 'required'));
            e_inputs.forEach(input => input.removeAttribute('required'));
            document.getElementById('cadastroType').value = 'hospede';
        } else {
            empresaFields.classList.remove('d-none');
            hospedeFields.classList.add('d-none');
            e_inputs.forEach(input => input.setAttribute('required', 'required'));
            h_inputs.forEach(input => input.removeAttribute('required'));
            document.getElementById('cadastroType').value = 'empresa';
        }
        updateBadge(isHospede);
    };

    // --- BUSCA CEP (ViaCEP) com Foco Inteligente ---
    inputCep.addEventListener('blur', async () => {
        const cep = inputCep.value.replace(/\D/g, '');
        if (cep.length !== 8) return;

        try {
            const response = await fetch(`https://viacep.com.br/ws/${cep}/json/`);
            const data = await response.json();

            if (!data.erro) {
                document.getElementById('state').value = data.uf;
                document.getElementById('city').value = data.localidade;
                document.getElementById('neighborhood').value = data.bairro;
                document.getElementById('street').value = data.logradouro;

                // Define País automaticamente se o campo existir
                const countryField = document.getElementById('country');
                if (countryField) countryField.value = "Brasil";

                // Foco Inteligente: Se a rua veio vazia (CEP geral), foca na rua. 
                // Se a rua veio preenchida, foca no número.
                if (data.logradouro === "") {
                    document.getElementById('street').focus();
                } else {
                    document.getElementById('address_number').focus();
                }
            }
        } catch (e) { console.error("Falha ao buscar CEP"); }
    });

    const verificarDuplicidade = async (valor) => {
        if (!valor) return false;
        try {
            const res = await fetch(`../controllers/cadastro_process.php?check_duplicity=1&value=${valor}`);
            const data = await res.json();
            if (data.exists) {
                alert("⚠️ AVISO: Este CPF/CNPJ já consta no banco de dados!");
                return true;
            }
            return false;
        } catch (e) { return false; }
    };

    inputCnpj.addEventListener('blur', async () => {
        const valor = inputCnpj.value.replace(/\D/g, '');
        if (valor.length !== 14) return;

        inputSenha.value = valor;
        const existe = await verificarDuplicidade(valor);
        if (existe) return;

        try {
            const res = await fetch(`https://brasilapi.com.br/api/cnpj/v1/${valor}`);
            if (res.ok) {
                const data = await res.json();
                document.getElementById('company_name').value = data.razao_social;
                document.getElementById('commercial_phone').value = data.telefone || '';
                document.getElementById('email').value = data.email || '';

                if (data.cep) {
                    inputCep.value = data.cep;
                    document.getElementById('state').value = data.uf;
                    document.getElementById('city').value = data.municipio;
                    document.getElementById('neighborhood').value = data.bairro;
                    document.getElementById('street').value = data.logradouro;
                    document.getElementById('address_number').value = data.numero;
                }
            }
        } catch (e) { console.error("Falha ao buscar CNPJ"); }
    });

    // --- GATILHO CPF ---
    inputCpf.addEventListener('blur', async () => {
        const valor = inputCpf.value.replace(/\D/g, '');
        if (valor.length === 0) return;

        if (!validarCPF(valor)) {
            alert("❌ CPF Inválido! Por favor, verifique os números digitados.");
            inputCpf.classList.add('is-invalid');
            inputCpf.classList.remove('is-valid');
            return;
        }

        inputCpf.classList.remove('is-invalid');
        inputCpf.classList.add('is-valid');
        inputSenha.value = valor;
        await verificarDuplicidade(valor);
    });

    toggleFields(typeSwitch.checked);
    typeSwitch.addEventListener('change', () => toggleFields(typeSwitch.checked));

    const showMessage = (message, isError = false) => {
        messageBox.textContent = message;
        messageBox.classList.remove('d-none', 'alert-danger', 'alert-success');
        messageBox.classList.add(isError ? 'alert-danger' : 'alert-success');
    };

    form.addEventListener('submit', async (e) => {
        e.preventDefault();

        if (document.getElementById('cadastroType').value === 'hospede') {
            const cpfVal = inputCpf.value.replace(/\D/g, '');
            if (!validarCPF(cpfVal)) {
                alert("O CPF informado é inválido. Corrija para prosseguir.");
                inputCpf.focus();
                return;
            }
        }

        submitButton.disabled = true;
        submitButton.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Gravando...';

        const formData = new FormData(form);
        const payload = Object.fromEntries(formData.entries());

        try {
            const response = await fetch('../../app/controllers/cadastro_process.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });
            const result = await response.json();
            if (result.success) {
                showMessage(result.message, false);
                form.reset();
                inputCpf.classList.remove('is-valid', 'is-invalid');
                toggleFields(true);
            } else {
                showMessage('Erro: ' + result.message, true);
            }
        } catch (error) {
            showMessage('Erro de comunicação.', true);
        } finally {
            submitButton.disabled = false;
            submitButton.textContent = 'Cadastrar Cliente';
        }
    });
});