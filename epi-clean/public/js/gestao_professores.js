document.addEventListener('DOMContentLoaded', () => {
    loadProfs();

    const form = document.getElementById('formProf');
    if (form) {
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            const formData = new FormData(this);
            const user = formData.get('usuario');

            if (!user) {
                showNotification('Erro', 'Por favor, insira um Gmail ou CPF.');
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
                const isPending = !p.senha || p.nome === 'Aguardando Cadastro';
                const statusHtml = isPending 
                    ? `<span class="badge-status pending" style="background:#fff7ed; color:#c2410c; padding:4px 8px; border-radius:6px; font-size:11px; font-weight:600; border:1px solid #ffedd5;">Pendente</span>`
                    : `<span class="badge-status active" style="background:#f0fdf4; color:#15803d; padding:4px 8px; border-radius:6px; font-size:11px; font-weight:600; border:1px solid #dcfce7;">Ativo</span>`;

                tbody.innerHTML += `
                    <tr>
                        <td style="font-weight: 600;">
                            ${p.nome}
                            <div style="margin-top:4px;">${statusHtml}</div>
                        </td>
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
                showNotification('Sucesso', 'Acesso autorizado com sucesso!');
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
    document.getElementById('modalLabel').innerText = 'Editar Autorização';
    document.getElementById('profId').value = p.id;
    document.getElementById('profUser').value = p.usuario;
    document.getElementById('profCargo').value = p.cargo;
    
    // Set course
    selectCourse(p.id_curso, p.curso_nome || 'Selecionar Curso...');
    
    openModal('modalProf');
}

function openModal(id) {
    document.getElementById(id).classList.add('active');
    if (id === 'modalProf' && !document.getElementById('profId').value) {
        document.getElementById('modalLabel').innerText = 'Autorizar Professor';
        document.getElementById('formProf').reset();
        document.getElementById('profCurso').value = '';
        document.getElementById('selectedCourseName').innerText = 'Selecionar Curso...';
    }
}

function closeModal(id) {
    document.getElementById(id).classList.remove('active');
}

// Funções de Seleção de Curso
function selectCourse(id, nome) {
    document.getElementById('profCurso').value = id;
    document.getElementById('selectedCourseName').innerText = nome;
    closeModal('modalSelectCurso');
}

function filterCoursesModal() {
    const term = document.getElementById('searchCourseModal').value.toLowerCase();
    const items = document.querySelectorAll('.course-item-option');
    items.forEach(item => {
        const nome = item.getAttribute('data-nome');
        item.style.display = nome.includes(term) ? 'block' : 'none';
    });
}

function showNotification(title, message) {
    if (typeof showToast === 'function') {
        showToast(title, message);
    }
}
