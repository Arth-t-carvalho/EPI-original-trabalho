document.addEventListener('DOMContentLoaded', () => {
    loadCursos();

    const form = document.getElementById('formCurso');
    if (form) {
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            saveCurso(new FormData(this));
        });
    }

    const searchInput = document.getElementById('searchCurso');
    if (searchInput) {
        searchInput.addEventListener('input', (e) => {
            loadCursos(e.target.value);
        });
    }
});

function loadCursos(search = '') {
    const tbody = document.getElementById('tableCursos');
    fetch(`../apis/api_gestao.php?action=list_cursos&search=${encodeURIComponent(search)}`)
        .then(res => res.json())
        .then(data => {
            tbody.innerHTML = '';
            if (data.length === 0) {
                tbody.innerHTML = '<tr><td colspan="4" style="text-align:center; padding: 20px;">Nenhum curso encontrado.</td></tr>';
                return;
            }

            data.forEach(curso => {
                tbody.innerHTML += `
                    <tr>
                        <td>#${curso.id}</td>
                        <td style="font-weight: 600;">${curso.nome}</td>
                        <td>${curso.vagas || '---'}</td>
                        <td style="text-align: right;">
                            <div class="action-btns" style="justify-content: flex-end;">
                                <button class="btn-icon btn-edit" onclick='editCurso(${JSON.stringify(curso)})' title="Editar">
                                    <i data-lucide="edit-3"></i>
                                </button>
                                <button class="btn-icon btn-delete" onclick="deleteCurso(${curso.id})" title="Excluir">
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

function saveCurso(formData) {
    fetch('../apis/api_gestao.php?action=save_curso', {
        method: 'POST',
        body: formData
    })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                closeModal('modalCurso');
                loadCursos();
                showNotification('Sucesso', 'Curso atualizado com sucesso!');
            } else {
                alert('Erro ao salvar: ' + data.message);
            }
        });
}

function deleteCurso(id) {
    openConfirmModal(
        'Excluir Curso?',
        'Deseja realmente excluir este curso? Alunos vinculados impedirão a exclusão.',
        () => {
            const formData = new FormData();
            formData.append('id', id);

            fetch('../apis/api_gestao.php?action=delete_curso', {
                method: 'POST',
                body: formData
            })
                .then(res => res.json())
                .then(data => {
                    if (data.status === 'success') {
                        loadCursos();
                        showNotification('Excluído', 'Curso removido.');
                    } else {
                        alert('Erro: ' + data.message);
                    }
                });
        }
    );
}

function editCurso(curso) {
    document.getElementById('modalLabel').innerText = 'Editar Curso';
    document.getElementById('cursoId').value = curso.id;
    document.getElementById('cursoNome').value = curso.nome;
    document.getElementById('cursoVagas').value = curso.vagas || '';
    openModal('modalCurso');
}

function openModal(id) {
    document.getElementById(id).classList.add('active');
    if (id === 'modalCurso' && !document.getElementById('cursoId').value) {
        document.getElementById('modalLabel').innerText = 'Novo Curso';
        document.getElementById('formCurso').reset();
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
