        function toggleDarkMode() {
            const body = document.documentElement; // ou document.body

            if (body.getAttribute("data-theme") === "dark") {
                body.removeAttribute("data-theme");
            } else {
                body.setAttribute("data-theme", "dark");
            }
        }
          lucide.createIcons();

                 // 2. Dark Mode
        function toggleTheme() {
            const isDark = document.getElementById('toggle-darkmode').checked;
            document.body.setAttribute('data-theme', isDark ? 'dark' : 'light');
        }

            function showThemeNotification(theme) {
        const container = document.getElementById('notification-container');
        if (!container) return;
        
        const toast = document.createElement('div');
        toast.className = 'toast';
        toast.innerHTML = `
            <div class="toast-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"></circle>
                    <path d="M12 16v-4M12 8h.01"></path>
                </svg>
            </div>
            <div class="toast-content">
                <span class="toast-title">Tema ${theme === 'dark' ? 'escuro' : 'claro'} ativado</span>
                <span class="toast-message">Aparência alterada com sucesso</span>
                <span class="toast-time">agora</span>
            </div>
        `;
        
        container.appendChild(toast);
        
        setTimeout(() => {
            toast.classList.add('removing');
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    }

      window.toggleTheme = function() {
        const isDark = document.body.getAttribute('data-theme') === 'dark';
        const newTheme = isDark ? 'light' : 'dark';
        
        document.body.setAttribute('data-theme', newTheme);
        localStorage.setItem('theme', newTheme);
        
        // Mostrar notificação de mudança de tema
        showThemeNotification(newTheme);
    }

         document.addEventListener('DOMContentLoaded', function() {
        const savedTheme = localStorage.getItem('theme');
        if (savedTheme === 'dark') {
            document.body.setAttribute('data-theme', 'dark');
        }
        
        // Verificar estado dos links nos cards (se existirem)
        const linksEnabled = localStorage.getItem('linksEnabled') === 'true';
        if (linksEnabled) {
            document.querySelectorAll('.card, .violation-card, .student-card').forEach(c => {
                c.classList.add('clickable');
            });
        }
    });


    // Aplicar tema salvo ao carregar a página
        document.addEventListener('DOMContentLoaded', function() {
            const savedTheme = localStorage.getItem('theme');
            if (savedTheme === 'dark') {
                document.body.setAttribute('data-theme', 'dark');
            }
            
            // Verificar estado do link nos cards
            const linksEnabled = localStorage.getItem('linksEnabled') === 'true';
            if (linksEnabled) {
                document.querySelectorAll('.card').forEach(c => c.classList.add('clickable'));
            }
            
            // Carregar cor do gráfico
            const chartColor = localStorage.getItem('chartColor') || '#E30613';
            document.documentElement.style.setProperty('--chart-main-color', chartColor);
        });

        // Observer para mudanças no localStorage (sincronizar entre abas)
        window.addEventListener('storage', function(e) {
            if (e.key === 'theme' || e.key === 'theme_trigger') {
                const currentTheme = localStorage.getItem('theme');
                if (currentTheme === 'dark') {
                    document.body.setAttribute('data-theme', 'dark');
                } else {
                    document.body.removeAttribute('data-theme');
                }
            }
            
            if (e.key === 'linksEnabled') {
                const enabled = localStorage.getItem('linksEnabled') === 'true';
                document.querySelectorAll('.card').forEach(card => {
                    if (enabled) {
                        card.classList.add('clickable');
                    } else {
                        card.classList.remove('clickable');
                    }
                });
            }
            
            if (e.key === 'chartColor') {
                document.documentElement.style.setProperty('--chart-main-color', e.newValue);
            }
        });

        // Função para alternar tema (será chamada pela página de configurações)
        window.toggleTheme = function() {
            const isDark = document.body.getAttribute('data-theme') === 'dark';
            const newTheme = isDark ? 'light' : 'dark';
            
            if (newTheme === 'dark') {
                document.body.setAttribute('data-theme', 'dark');
            } else {
                document.body.removeAttribute('data-theme');
            }
            
            localStorage.setItem('theme', newTheme);
        }

          // Verificar preferência salva ao carregar a página
    document.addEventListener('DOMContentLoaded', function() {
        const savedTheme = localStorage.getItem('theme');
        if (savedTheme === 'dark') {
            document.body.setAttribute('data-theme', 'dark');
        }
        
        // Verificar estado dos links nos cards (se existirem)
        const linksEnabled = localStorage.getItem('linksEnabled') === 'true';
        if (linksEnabled) {
            document.querySelectorAll('.card, .violation-card, .student-card').forEach(c => {
                c.classList.add('clickable');
            });
        }
    });

        // Função para alternar tema (será chamada pela página de configurações)
    window.toggleTheme = function() {
        const isDark = document.body.getAttribute('data-theme') === 'dark';
        const newTheme = isDark ? 'light' : 'dark';
        
        document.body.setAttribute('data-theme', newTheme);
        localStorage.setItem('theme', newTheme);
        
        // Mostrar notificação de mudança de tema
        showThemeNotification(newTheme);
    }

            // Sistema de Tema
        document.addEventListener('DOMContentLoaded', function() {
            const savedTheme = localStorage.getItem('theme');
            if (savedTheme === 'dark') {
                document.body.setAttribute('data-theme', 'dark');
            }
            
            const linksEnabled = localStorage.getItem('linksEnabled') === 'true';
            if (linksEnabled) {
                document.querySelectorAll('.violation-card').forEach(c => {
                    c.classList.add('clickable');
                });
            }
        });

        window.toggleTheme = function() {
            const isDark = document.body.getAttribute('data-theme') === 'dark';
            const newTheme = isDark ? 'light' : 'dark';
            
            document.body.setAttribute('data-theme', newTheme);
            localStorage.setItem('theme', newTheme);
            showThemeNotification(newTheme);
        }

                // Aplica o tema imediatamente para evitar que a tela "pisque" em branco
        const savedTheme = localStorage.getItem('theme');
        if (savedTheme === 'dark') {
            document.documentElement.setAttribute('data-theme', 'dark');
            // Como fallback para o seu CSS específico:
            document.addEventListener('DOMContentLoaded', () => {
                document.body.setAttribute('data-theme', 'dark');
            });
        }

        // Fica "escutando" mudanças de tema feitas em outras abas
        window.addEventListener('storage', function(e) {
            if (e.key === 'theme' || e.key === 'theme_trigger') {
                const currentTheme = localStorage.getItem('theme');
                if (currentTheme === 'dark') {
                    document.body.setAttribute('data-theme', 'dark');
                    document.documentElement.setAttribute('data-theme', 'dark');
                } else {
                    document.body.removeAttribute('data-theme');
                    document.documentElement.removeAttribute('data-theme');
                }
            }
        });


                function showThemeNotification(theme) {
            const container = document.getElementById('notification-container');
            if (!container) return;
            
            const toast = document.createElement('div');
            toast.className = 'toast';
            toast.innerHTML = `
                <div class="toast-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"></circle>
                        <path d="M12 16v-4M12 8h.01"></path>
                    </svg>
                </div>
                <div class="toast-content">
                    <span class="toast-title">Tema ${theme === 'dark' ? 'escuro' : 'claro'} ativado</span>
                    <span class="toast-message">Aparência alterada com sucesso</span>
                    <span class="toast-time">agora</span>
                </div>
            `;
            
            container.appendChild(toast);
            
            setTimeout(() => {
                toast.classList.add('removing');
                setTimeout(() => toast.remove(), 300);
            }, 3000);
        }

        
        // Sistema de Tema
        document.addEventListener('DOMContentLoaded', function() {
            const savedTheme = localStorage.getItem('theme');
            if (savedTheme === 'dark') {
                document.body.setAttribute('data-theme', 'dark');
            }
        });

          window.toggleTheme = function() {
            const isDark = document.body.getAttribute('data-theme') === 'dark';
            const newTheme = isDark ? 'light' : 'dark';
            
            document.body.setAttribute('data-theme', newTheme);
            localStorage.setItem('theme', newTheme);
        }

           // Função para mostrar notificação de tema
    function showThemeNotification(theme) {
        const container = document.getElementById('notification-container');
        if (!container) return;
        
       
       
        const toast = document.createElement('div');
        toast.className = 'toast';
        toast.innerHTML = `
            <div class="toast-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"></circle>
                    <path d="M12 16v-4M12 8h.01"></path>
                </svg>
            </div>
            <div class="toast-content">
                <span class="toast-title">Tema ${theme === 'dark' ? 'escuro' : 'claro'} ativado</span>
                <span class="toast-message">Aparência alterada com sucesso</span>
                <span class="toast-time">agora</span>
            </div>
        `;
        
        container.appendChild(toast);
        
        setTimeout(() => {
            toast.classList.add('removing');
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    }

       // Verificar preferência salva ao carregar a página
    document.addEventListener('DOMContentLoaded', function() {
        const savedTheme = localStorage.getItem('theme');
        if (savedTheme === 'dark') {
            document.body.setAttribute('data-theme', 'dark');
        }
        
        // Verificar estado dos links nos cards (se existirem)
        const linksEnabled = localStorage.getItem('linksEnabled') === 'true';
        if (linksEnabled) {
            document.querySelectorAll('.card, .violation-card, .student-card').forEach(c => {
                c.classList.add('clickable');
            });
        }
    });

    // Função para alternar tema (será chamada pela página de configurações)
    window.toggleTheme = function() {
        const isDark = document.body.getAttribute('data-theme') === 'dark';
        const newTheme = isDark ? 'light' : 'dark';
        
        document.body.setAttribute('data-theme', newTheme);
        localStorage.setItem('theme', newTheme);
        
        // Mostrar notificação de mudança de tema
        showThemeNotification(newTheme);
    }

    