document.addEventListener('DOMContentLoaded', () => {
    loadAlunos();

    const form = document.getElementById('formAluno');
    if (form) {
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            saveAluno(new FormData(this));
        });
    }

    const searchInput = document.getElementById('searchAluno');
    if (searchInput) {
        searchInput.addEventListener('input', (e) => {
            loadAlunos(e.target.value);
        });
    }
});

function loadAlunos(search = '') {
    const tbody = document.getElementById('tableAlunos');
    fetch(`../apis/api_gestao.php?action=list_alunos&search=${encodeURIComponent(search)}`)
        .then(res => res.json())
        .then(data => {
            tbody.innerHTML = '';
            if (data.length === 0) {
                tbody.innerHTML = '<tr><td colspan="4" style="text-align:center; padding: 20px;">Nenhum aluno encontrado.</td></tr>';
                return;
            }

            data.forEach(aluno => {
                tbody.innerHTML += `
                    <tr>
                        <td>#${aluno.id}</td>
                        <td style="font-weight: 600;">${aluno.nome}</td>
                        <td>${aluno.curso_nome}</td>
                        <td style="text-align: center;">
                            <div style="display: flex; gap: 4px; justify-content: center;">
                                ${(() => {
                        const avg = parseFloat(aluno.daily_avg) || 0;
                        let icons = '';
                        if (avg > 1) icons += '<i data-lucide="alert-triangle" style="width:16px; color: #eab308;" title="Média > 1/dia"></i>';
                        if (avg > 5) icons += '<i data-lucide="alert-circle" style="width:16px; color: #f97316;" title="Média > 5/dia"></i>';
                        if (avg > 10) icons += '<i data-lucide="alert-octagon" style="width:16px; color: #ef4444;" title="Média > 10/dia"></i>';
                        return icons || '<span style="color: #10b981; font-size: 11px;">Baixo Risco</span>';
                    })()}
                            </div>
                        </td>
                        <td style="text-align: right;">
                            <div class="action-btns" style="justify-content: flex-end;">
                                <button class="btn-icon btn-edit" onclick='editAluno(${JSON.stringify(aluno)})' title="Editar">
                                    <i data-lucide="edit-3"></i>
                                </button>
                                <button class="btn-icon btn-delete" onclick="deleteAluno(${aluno.id})" title="Excluir">
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

function saveAluno(formData) {
    fetch('../apis/api_gestao.php?action=save_aluno', {
        method: 'POST',
        body: formData
    })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                closeModal('modalAluno');
                loadAlunos();
                showNotification('Sucesso', 'Dados do aluno salvos com sucesso!');
            } else {
                alert('Erro ao salvar: ' + data.message);
            }
        });
}

function deleteAluno(id) {
    openConfirmModal(
        'Excluir Aluno?',
        'Tem certeza que deseja remover este aluno? Esta ação não poderá ser desfeita.',
        () => {
            const formData = new FormData();
            formData.append('id', id);

            fetch('../apis/api_gestao.php?action=delete_aluno', {
                method: 'POST',
                body: formData
            })
                .then(res => res.json())
                .then(data => {
                    if (data.status === 'success') {
                        loadAlunos();
                        showNotification('Excluído', 'Aluno removido com sucesso.');
                    } else {
                        alert('Erro ao excluir: ' + data.message);
                    }
                });
        }
    );
}

function editAluno(aluno) {
    document.getElementById('modalLabel').innerText = 'Editar Aluno';
    document.getElementById('alunoId').value = aluno.id;
    document.getElementById('alunoNome').value = aluno.nome;
    document.getElementById('alunoCurso').value = aluno.curso_id;
    openModal('modalAluno');
}

function openModal(id) {
    const modal = document.getElementById(id);
    modal.classList.add('active');
    if (id === 'modalAluno' && !document.getElementById('alunoId').value) {
        document.getElementById('modalLabel').innerText = 'Novo Aluno';
        document.getElementById('formAluno').reset();
        document.getElementById('alunoId').value = '';
    }
}

function closeModal(id) {
    const modal = document.getElementById(id);
    modal.classList.remove('active');
}

function showNotification(title, message) {
    if (typeof showToast === 'function') {
        showToast(title, message);
    } else {
        console.log(`${title}: ${message}`);
    }
}
