/* //// INÍCIO CONFIGURAÇÃO DA IA REFINADA //// */

const GEMINI_API_KEY = "AIzaSyDB4L43-22MQowxx__liT4Fb-n4096yOd8";

function toggleAssistenteIA() {
    const panel = document.getElementById('aiAssistantPanel');
    if (!panel) return;

    panel.classList.toggle('open');
    if (panel.classList.contains('open')) {
        setTimeout(() => {
            const input = document.getElementById('aiInput');
            if (input) input.focus();
        }, 100);
    }
}

function handleAIPress(event) {
    if (event.key === 'Enter') {
        sendAIMessage();
    }
}

function appendAIMessage(text, sender) {
    const chatBody = document.getElementById('aiChatBody');
    const loading = document.getElementById('aiLoading');
    if (!chatBody) return;

    const msgDiv = document.createElement('div');
    msgDiv.className = 'ai-message ' + sender;

    let chartConfig = null;
    let cleanText = text;

    // Detecção e Extração de Gráfico
    if (sender === 'bot' && text.includes("CHART_DATA:")) {
        try {
            const parts = text.split("CHART_DATA:");
            cleanText = parts[0].trim();
            const jsonPart = parts[1].trim();
            chartConfig = JSON.parse(jsonPart);
        } catch (e) {
            console.error("Erro ao processar JSON do gráfico:", e);
        }
    }

    let formattedText = cleanText.replace(/\n/g, '<br>').replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
    msgDiv.innerHTML = formattedText;

    if (sender === 'bot' && text.length > 50) {
        const pdftool = document.createElement('div');
        pdftool.style.marginTop = '10px';

        const pdfBtn = document.createElement('button');
        pdfBtn.className = 'ai-pdf-btn';
        pdfBtn.innerHTML = '<i data-lucide="file-text" style="width: 12px; height: 12px;"></i> Exportar PDF';

        let targetChartId = null;
        if (chartConfig) {
            targetChartId = 'chart-' + Date.now();
        }

        pdfBtn.onclick = () => exportToPDF(cleanText, targetChartId);
        pdftool.appendChild(pdfBtn);
        msgDiv.appendChild(pdftool);
        if (window.lucide) lucide.createIcons();

        // Renderização do Gráfico
        if (chartConfig) {
            const container = document.createElement('div');
            container.className = 'ai-chart-container';
            container.innerHTML = `<canvas id="${targetChartId}"></canvas>`;
            msgDiv.appendChild(container);

            setTimeout(() => {
                new Chart(document.getElementById(targetChartId), chartConfig);
            }, 100);
        }
    }

    chatBody.insertBefore(msgDiv, loading);
    chatBody.scrollTop = chatBody.scrollHeight;
}

function exportToPDF(text, chartId = null) {
    if (!window.jspdf) return;
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF();

    doc.setFontSize(22); doc.setTextColor(227, 6, 19); doc.text("EPI GUARD", 105, 20, { align: "center" });
    doc.setFontSize(16); doc.setTextColor(31, 41, 55); doc.text("Relatório Técnico de IA", 105, 30, { align: "center" });
    doc.setFontSize(10); doc.setTextColor(100, 116, 139); doc.text("Emissão: " + new Date().toLocaleString(), 105, 38, { align: "center" });
    doc.setDrawColor(229, 231, 235); doc.line(20, 45, 190, 45);

    doc.setFontSize(11); doc.setTextColor(17, 24, 39);
    const cleanText = text.replace(/\*\*/g, '');
    const splitText = doc.splitTextToSize(cleanText, 170);
    doc.text(splitText, 20, 55);

    let currentY = 55 + (splitText.length * 6);

    if (chartId) {
        const canvas = document.getElementById(chartId);
        if (canvas) {
            try {
                const imgData = canvas.toDataURL("image/png", 1.0);
                if (currentY > 200) {
                    doc.addPage();
                    currentY = 20;
                }
                doc.setFontSize(12); doc.setTextColor(227, 6, 19);
                doc.text("Análise Visual:", 20, currentY + 10);
                doc.addImage(imgData, 'PNG', 20, currentY + 15, 170, 90);
            } catch (e) {
                console.error("Erro ao incluir gráfico no PDF:", e);
            }
        }
    }

    doc.save("Relatorio_EPI_Guard_" + new Date().getTime() + ".pdf");
}

async function sendAIMessage() {
    const inputField = document.getElementById('aiInput');
    const userText = inputField.value.trim();
    if (!userText) return;

    appendAIMessage(userText, 'user');
    inputField.value = '';

    const loading = document.getElementById('aiLoading');
    loading.style.display = 'block';

    const systemPrompt = `Você é o "Estrategista de Dados EPI Guard", um analista sênior do SENAI focado em BI (Business Intelligence).

CONTEXTO DE DADOS CONSOLIDADOS:
1. RANKING EPIs: ${JSON.stringify(window.aiData.epis)}
2. RANKING ALUNOS: ${JSON.stringify(window.aiData.alunos)}
3. RESUMO: ${JSON.stringify(window.aiData.resumo)}
4. HISTÓRICO: ${JSON.stringify(window.aiData.tempo)}
5. SANÇÕES: ${JSON.stringify(window.aiData.sancoes)}
6. TURNOS: ${JSON.stringify(window.aiData.turnos)}
7. SUPERVISORES: ${JSON.stringify(window.aiData.supervisores)}
8. CURSOS: ${JSON.stringify(window.aiData.cursos)}

SUA CAPACIDADE ANALÍTICA:
- RELATÓRIOS: Ranking, Temporais, Comparativos.
- CÁLCULOS: Média diária, dias críticos, recordes.
- GRÁFICOS: QUANDO houver dados comparativos, adicione CHART_DATA no final em formato JSON.

DIRETRIZES:
- OBJETIVIDADE. Use negrito e listas. padrão SENAI (#E30613).

Usuário solicitou: "${userText}"`;

    try {
        const response = await fetch(`https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=${GEMINI_API_KEY}`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ contents: [{ parts: [{ text: systemPrompt }] }] })
        });

        const data = await response.json();
        loading.style.display = 'none';

        if (data.candidates && data.candidates.length > 0) {
            appendAIMessage(data.candidates[0].content.parts[0].text, 'bot');
        } else {
            appendAIMessage("Falha ao processar dados da IA.", 'bot');
        }
    } catch (error) {
        loading.style.display = 'none';
        appendAIMessage("Erro de conexão com o serviço de IA.", 'bot');
    }
}
