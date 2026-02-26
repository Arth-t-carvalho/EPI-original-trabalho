document.addEventListener("DOMContentLoaded", () => {
    // --- 1. POPULAR DADOS FAKE (Simulando o que vem do Dashboard) ---
    const urlParams = new URLSearchParams(window.location.search);

    const studentName = urlParams.get('name') || "Arthur (Mecânica)";
    const epiMissing = urlParams.get('epi') || "Óculos de Proteção"; // Padrão se não vier nada

    // Preencher Aluno
    const studentInput = document.getElementById('studentNameInput');
    if (studentInput) studentInput.value = studentName;

    // Preencher Motivo (Já travado)
    const reasonInput = document.getElementById('reasonInput');
    if (reasonInput) reasonInput.value = `Ausência de EPI: ${epiMissing}`;

    // Preencher Data/Hora Formatada
    const dateTimeInput = document.getElementById('dateTimeInput');
    if (dateTimeInput) {
        const now = new Date();
        const formatted = now.toLocaleDateString('pt-BR') + ' às ' + now.toLocaleTimeString('pt-BR').substring(0, 5);
        dateTimeInput.value = formatted;
    }
});

// --- 2. LÓGICA DE FOTOS ADICIONAIS ---
const fileInput = document.getElementById('fileInput');
const gallery = document.getElementById('photoGallery');

if (fileInput && gallery) {
    fileInput.addEventListener('change', function () {
        if (this.files) {
            Array.from(this.files).forEach(file => {
                const reader = new FileReader();
                reader.onload = function (e) {
                    const div = document.createElement('div');
                    div.className = 'photo-wrapper-new'; // Classe sem borda vermelha

                    const img = document.createElement('img');
                    img.src = e.target.result;

                    div.appendChild(img);

                    // Inserir antes do botão "+"
                    const addBtn = gallery.lastElementChild;
                    gallery.insertBefore(div, addBtn);
                }
                reader.readAsDataURL(file);
            });
        }
    });
}

// --- 3. SUBMIT FORMULÁRIO (MOCK) ---
const incidentForm = document.getElementById('incidentForm');
if (incidentForm) {
    incidentForm.addEventListener('submit', function (e) {
        e.preventDefault();
        const btn = this.querySelector('.btn-submit');
        
        if (btn) btn.innerHTML = 'Salvando...';
        
        setTimeout(() => {
            alert('Ocorrência registrada com sucesso!');
            window.location.href = 'dashboard.php';
        }, 800);
    });
}