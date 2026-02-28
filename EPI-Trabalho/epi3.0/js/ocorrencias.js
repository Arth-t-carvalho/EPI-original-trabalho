        // Funções do Header que faltavam
        function toggleInstructorCard() {
            const card = document.getElementById('instructorCard');
            card.classList.toggle('active');
        }

        function exportData() {
            alert("Exportando dados...");
        }

        document.addEventListener("DOMContentLoaded", () => {
            // --- 1. POPULAR DADOS FAKE (Simulando o que vem do Dashboard) ---
            const urlParams = new URLSearchParams(window.location.search);

            const studentName = urlParams.get('name') || "Arthur (Mecânica)";
            const epiMissing = urlParams.get('epi') || "Óculos de Proteção"; // Padrão se não vier nada

            // Preencher Aluno
            document.getElementById('studentNameInput').value = studentName;

            // Preencher Motivo (Já travado)
            document.getElementById('reasonInput').value = `Ausência de EPI: ${epiMissing}`;

            // Preencher Data/Hora Formatada
            const now = new Date();
            const formatted = now.toLocaleDateString('pt-BR') + ' às ' + now.toLocaleTimeString('pt-BR').substring(0, 5);
            document.getElementById('dateTimeInput').value = formatted;
        });

        // --- 2. LÓGICA DE FOTOS ADICIONAIS ---
        const fileInput = document.getElementById('fileInput');
        const gallery = document.getElementById('photoGallery');

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

        // Submit Mock
        document.getElementById('incidentForm').addEventListener('submit', function (e) {
            e.preventDefault();
            const btn = this.querySelector('.btn-submit');
            btn.innerHTML = 'Salvando...';
            setTimeout(() => {
                alert('Ocorrência registrada com sucesso!');
                window.location.href = 'dashboard.html';
            }, 800);
        });


        function sair() {
            window.location.href = "index.html";
        }
