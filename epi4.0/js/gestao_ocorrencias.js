document.addEventListener('DOMContentLoaded', () => {
    loadOcorrencias();

    const searchInput = document.getElementById('searchOcorrencia');
    if (searchInput) {
        searchInput.addEventListener('input', (e) => {
            loadOcorrencias(e.target.value);
        });
    }

    const filterCurso = document.getElementById('filterCurso');
    if (filterCurso) {
        filterCurso.addEventListener('change', () => {
            loadOcorrencias(searchInput ? searchInput.value : '');
        });
    }

    const formVerify = document.getElementById('formVerify');
    if (formVerify) {
        formVerify.addEventListener('submit', function (e) {
            e.preventDefault();
            confirmOccurrence(new FormData(this));
        });
    }
});

function loadOcorrencias(search = '') {
    const tbody = document.getElementById('tableOcorrencias');
    const cursoId = document.getElementById('filterCurso')?.value || 'todos';

    let url = `../apis/api.php?action=list_all_ocorrencias&search=${encodeURIComponent(search)}`;
    if (cursoId !== 'todos') url += `&curso_id=${cursoId}`;

    fetch(url)
        .then(res => res.json())
        .then(data => {
            tbody.innerHTML = '';
            if (data.length === 0) {
                tbody.innerHTML = '<tr><td colspan="6" style="text-align:center; padding: 20px;">Nenhuma ocorrência pendente encontrada.</td></tr>';
                return;
            }

            data.forEach(o => {
                const date = new Date(o.data_hora);
                const dateF = date.toLocaleDateString('pt-BR') + ' ' + date.toLocaleTimeString('pt-BR').substring(0, 5);

                const statusClass = 'status-pendente';

                tbody.innerHTML += `
                    <tr>
                        <td style="font-size: 0.85rem; color: #64748b;">${dateF}</td>
                        <td style="font-weight: 600;">${o.aluno_nome}</td>
                        <td>${o.curso_nome || 'Geral'}</td>
                        <td style="color: #dc2626; font-weight: 500;">${o.epi_nome}</td>
                        <td><span class="status-badge ${statusClass}">Aguardando Verificação</span></td>
                        <td style="text-align: right;">
                            <button class="btn-verify" onclick='openVerifyModal(${JSON.stringify(o).replace(/'/g, "&apos;")})'>
                                <i data-lucide="check-circle" style="width:16px;"></i> Verificar
                            </button>
                        </td>
                    </tr>
                `;
            });
            lucide.createIcons();
        });
}

function openVerifyModal(o) {
    document.getElementById('verifyId').value = o.id;
    const details = document.getElementById('verifyDetails');
    const date = new Date(o.data_hora);
    const dateF = date.toLocaleDateString('pt-BR') + ' ' + date.toLocaleTimeString('pt-BR').substring(0, 5);

    details.innerHTML = `
        <div style="background: #f8fafc; padding: 15px; border-radius: 8px; border-left: 4px solid var(--primary);">
            <p><strong>Aluno:</strong> ${o.aluno_nome}</p>
            <p><strong>Curso:</strong> ${o.curso_nome || 'Geral'}</p>
            <p><strong>Infração:</strong> ${o.epi_nome}</p>
            <p><strong>Data/Hora:</strong> ${dateF}</p>
            <div style="margin-top: 10px;">
                <img src="mostrar_imagem.php?id=${o.id}" style="width: 100%; max-height: 200px; object-fit: cover; border-radius: 4px;" alt="Evidência" onerror="this.src='/img/placeholder-evidence.png'; this.onerror=null;">
            </div>
        </div>
    `;

    const modal = document.getElementById('modalVerify');
    modal.classList.add('active');
}

function confirmOccurrence(formData) {
    fetch('../apis/api.php?action=resolve_occurrence', {
        method: 'POST',
        body: formData
    })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                closeModal('modalVerify');
                loadOcorrencias();
                if (typeof showToast === 'function') showToast('Sucesso', 'Ocorrência confirmada com sucesso!');
            } else {
                alert('Erro ao confirmar: ' + data.error);
            }
        });
}

function closeModal(id) {
    document.getElementById(id).classList.remove('active');
}

function toggleInstructorCard() {
    document.getElementById('instructorCard').classList.toggle('active');
}
