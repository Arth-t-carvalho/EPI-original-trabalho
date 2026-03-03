document.addEventListener('DOMContentLoaded', () => {
    loadProfs();

    const form = document.getElementById('formProf');
    if (form) {
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            const formData = new FormData(this);
            const email = formData.get('usuario');

            if (!email.toLowerCase().endsWith('@gmail.com')) {
                showNotification('Erro de Validação', 'O usuário deve ser um e-mail @gmail.com válido.');
                return;
            }

            saveProf(formData);
        });
    }

    const searchInput = document.getElementById('searchProf');
    if (searchInput) {
        searchInput.addEventListener('input', (e) => {
            loadProfs(e.target.value);
        });
    }
});

function loadProfs(search = '') {
    const tbody = document.getElementById('tableProfs');
    fetch(`../apis/api_gestao.php?action=list_professores&search=${encodeURIComponent(search)}`)
        .then(res => res.json())
        .then(data => {
            tbody.innerHTML = '';
            if (data.length === 0) {
                tbody.innerHTML = '<tr><td colspan="5" style="text-align:center; padding: 20px;">Nenhum professor encontrado.</td></tr>';
                return;
            }
            data.forEach(p => {
                tbody.innerHTML += `
                    <tr>
                        <td style="font-weight: 600;">${p.nome}</td>
                        <td>${p.usuario}</td>
                        <td><span class="badge-cargo">${p.cargo}</span></td>
                        <td>${p.curso_nome || 'Nenhum'}</td>
                        <td style="text-align: right;">
                            <div class="action-btns" style="justify-content: flex-end;">
                                <button class="btn-icon btn-edit" onclick='editProf(${JSON.stringify(p)})'>
                                    <i data-lucide="edit-3"></i>
                                </button>
                                <button class="btn-icon btn-delete" onclick="deleteProf(${p.id})">
                                    <i data-lucide="trash-2"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                `;
            });
            lucide.createIcons();
        });
}

function saveProf(formData) {
    fetch('../apis/api_gestao.php?action=save_professor', {
        method: 'POST',
        body: formData
    })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                closeModal('modalProf');
                loadProfs();
                showNotification('Sucesso', 'Professor salvo com sucesso!');
            } else {
                alert('Erro: ' + data.message);
            }
        });
}

function deleteProf(id) {
    openConfirmModal(
        'Remover Credenciais?',
        'Deseja excluir este professor? Ele perderá o acesso ao sistema imediatamente.',
        () => {
            const formData = new FormData();
            formData.append('id', id);
            fetch('../apis/api_gestao.php?action=delete_professor', {
                method: 'POST',
                body: formData
            })
                .then(res => res.json())
                .then(data => {
                    if (data.status === 'success') {
                        loadProfs();
                        showNotification('Excluído', 'Professor removido.');
                    }
                });
        }
    );
}

function editProf(p) {
    document.getElementById('modalLabel').innerText = 'Editar Professor';
    document.getElementById('profId').value = p.id;
    document.getElementById('profNome').value = p.nome;
    document.getElementById('profUser').value = p.usuario;
    document.getElementById('profPass').value = '';
    document.getElementById('profCargo').value = p.cargo;
    document.getElementById('profCurso').value = p.id_curso;
    document.getElementById('passLabel').style.display = 'inline';
    openModal('modalProf');
}

function openModal(id) {
    document.getElementById(id).classList.add('active');
    if (id === 'modalProf' && !document.getElementById('profId').value) {
        document.getElementById('modalLabel').innerText = 'Novo Professor';
        document.getElementById('formProf').reset();
        document.getElementById('passLabel').style.display = 'none';
        document.getElementById('profPass').required = true;
    } else {
        document.getElementById('profPass').required = false;
    }
}

function closeModal(id) {
    document.getElementById(id).classList.remove('active');
}

function showNotification(title, message) {
    if (typeof showToast === 'function') {
        showToast(title, message);
    }
}
