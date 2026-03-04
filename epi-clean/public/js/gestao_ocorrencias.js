document.addEventListener('DOMContentLoaded', () => {
    // Verifica se há um ID na URL para focar
    const urlParams = new URLSearchParams(window.location.search);
    const focusId = urlParams.get('id');

    loadOcorrencias('', focusId);

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

function loadOcorrencias(search = '', focusId = null) {
    const tbody = document.getElementById('tableOcorrencias');
    const cursoId = document.getElementById('filterCurso')?.value || 'todos';

    let url = `../apis/api.php?action=list_all_ocorrencias&search=${encodeURIComponent(search)}`;
    if (cursoId !== 'todos') url += `&curso_id=${cursoId}`;
    if (focusId) url += `&show_id=${focusId}`;

    fetch(url)
        .then(res => res.json())
        .then(data => {
            tbody.innerHTML = '';
            if (data.length === 0) {
                tbody.innerHTML = '<tr><td colspan="6" style="text-align:center; padding: 20px;">Nesta visualização não há ocorrências disponíveis.</td></tr>';
                return;
            }

            data.forEach(o => {
                const date = new Date(o.data_hora);
                const dateF = date.toLocaleDateString('pt-BR') + ' ' + date.toLocaleTimeString('pt-BR').substring(0, 5);

                const isResolvido = (o.status_formatado === 'Resolvido');
                const statusClass = isResolvido ? 'status-resolvido' : 'status-pendente';
                const statusText = isResolvido ? 'Verificado' : 'Aguardando Verificação';

                tbody.innerHTML += `
                    <tr id="row-occ-${o.id}" style="${o.id == focusId ? 'background: #fff7ed; border-left: 4px solid var(--primary);' : ''}">
                        <td style="font-size: 0.85rem; color: #64748b;">${dateF}</td>
                        <td style="font-weight: 600;">${o.aluno_nome}</td>
                        <td>${o.curso_nome || 'Geral'}</td>
                        <td style="color: #dc2626; font-weight: 500;">${o.epi_nome}</td>
                        <td><span class="status-badge ${statusClass}">${statusText}</span></td>
                        <td style="text-align: right;">
                            <button class="btn-verify" onclick='openVerifyModal(${JSON.stringify(o).replace(/'/g, "&apos;")})'>
                                <i data-lucide="${isResolvido ? 'eye' : 'check-circle'}" style="width:16px;"></i> ${isResolvido ? 'Ver' : 'Verificar'}
                            </button>
                        </td>
                    </tr>
                `;
            });
            lucide.createIcons();

            // Se tiver foco, abre o modal
            if (focusId) {
                const target = data.find(x => x.id == focusId);
                if (target) openVerifyModal(target);
            }
        });
}

function openVerifyModal(o) {
    document.getElementById('verifyId').value = o.id;
    const details = document.getElementById('verifyDetails');
    const date = new Date(o.data_hora);
    const dateF = date.toLocaleDateString('pt-BR') + ' ' + date.toLocaleTimeString('pt-BR').substring(0, 5);

    const isResolvido = (o.status_formatado === 'Resolvido');

    details.innerHTML = `
        <div style="background: #f8fafc; padding: 15px; border-radius: 8px; border-left: 4px solid var(--primary);">
            <p><strong>Status:</strong> <span style="color: ${isResolvido ? '#059669' : '#d97706'}">${o.status_formatado || 'Pendente'}</span></p>
            <p><strong>Aluno:</strong> ${o.aluno_nome}</p>
            <p><strong>Curso:</strong> ${o.curso_nome || 'Geral'}</p>
            <p><strong>Infração:</strong> ${o.epi_nome}</p>
            <p><strong>Data/Hora:</strong> ${dateF}</p>
            <div style="margin-top: 10px;">
                <img src="mostrar_imagem.php?id=${o.id}" style="width: 100%; max-height: 200px; object-fit: cover; border-radius: 4px;" alt="Evidência" onerror="this.src='/img/placeholder-evidence.png'; this.onerror=null;">
            </div>
        </div>
    `;

    // Se já estiver resolvido, esconde o botão de confirmação ou mostra desabilitado
    const submitBtn = document.querySelector('#formVerify .btn-submit');
    if (submitBtn) {
        submitBtn.style.display = isResolvido ? 'none' : 'block';
    }

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
