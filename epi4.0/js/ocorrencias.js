document.addEventListener('DOMContentLoaded', () => {
    // 1. Inicializa data e hora no formulário
    const dateTimeInput = document.getElementById('dateTimeInput');
    if (dateTimeInput) {
        const now = new Date();
        dateTimeInput.value = now.toLocaleString('pt-BR');
    }

    // 2. Monitora seleção de aluno para atualizar infos (se necessário)
    const studentSelect = document.getElementById('studentNameInput');
    if (studentSelect) {
        studentSelect.addEventListener('change', function () {
            const selected = this.options[this.selectedIndex];
            // Você pode usar os data-attributes aqui se quiser mostrar algo na tela
            // console.log("Aluno:", selected.text, "Curso:", selected.dataset.curso);

            // Se houver lógica de carregar foto de referência, seria aqui
        });
    }

    // 3. Envio do Formulário
    const incidentForm = document.getElementById('incidentForm');
    if (incidentForm) {
        incidentForm.addEventListener('submit', function (e) {
            e.preventDefault();

            const formData = new FormData(this);
            // Adiciona o motivo (razão) se necessário - o formulário tem o campo #reasonInput
            // Como no seu HTML ele é readonly, vamos pegar o valor dele ou fixar um ID de EPI
            formData.append('epi_id', 1); // Supondo ID 1 como padrão se for manual por enquanto

            // Adiciona fotos do fileInput
            const fileInput = document.getElementById('fileInput');
            if (fileInput && fileInput.files.length > 0) {
                for (let i = 0; i < fileInput.files.length; i++) {
                    formData.append('fotos[]', fileInput.files[i]);
                }
            }

            fetch('../apis/api.php?action=save_occurrence', {
                method: 'POST',
                body: formData
            })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        if (typeof showToast === 'function') {
                            showToast('Sucesso', 'Ocorrência registrada com sucesso!');
                        } else {
                            alert('Ocorrência registrada com sucesso!');
                        }
                        setTimeout(() => window.location.href = 'dashboard.php', 1500);
                    } else {
                        alert('Erro: ' + (data.error || 'Falha ao salvar.'));
                    }
                })
                .catch(err => {
                    console.error(err);
                    alert('Erro na comunicação com o servidor.');
                });
        });
    }

    // 4. Lógica de Galeria (Preview de fotos)
    const fileInput = document.getElementById('fileInput');
    const photoGallery = document.getElementById('photoGallery');
    if (fileInput && photoGallery) {
        fileInput.addEventListener('change', function () {
            // Limpa previews anteriores que não sejam o botão de adicionar
            const previews = photoGallery.querySelectorAll('.photo-preview');
            previews.forEach(p => p.remove());

            Array.from(this.files).forEach(file => {
                const reader = new FileReader();
                reader.onload = function (e) {
                    const div = document.createElement('div');
                    div.className = 'photo-preview';
                    div.style.width = '80px';
                    div.style.height = '80px';
                    div.style.borderRadius = '8px';
                    div.style.overflow = 'hidden';
                    div.style.border = '1px solid #ddd';
                    div.innerHTML = `<img src="${e.target.result}" style="width:100%; height:100%; object-fit:cover;">`;
                    photoGallery.insertBefore(div, photoGallery.firstChild);
                }
                reader.readAsDataURL(file);
            });
        });
    }
});

function toggleInstructorCard() {
    const card = document.getElementById('instructorCard');
    if (card) card.classList.toggle('active');
}

function exportData() {
    alert('Função de exportação em desenvolvimento.');
}
