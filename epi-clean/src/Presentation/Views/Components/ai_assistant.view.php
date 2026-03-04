<?php
$aiRepo = new \App\Infrastructure\Persistence\AiRepository();
$aiData = $aiRepo->getAiData();
?>

<!-- Inclusão de Assets Necessários -->
<link rel="stylesheet" href="css/ai_assistant.css">

<div class="ai-assistant-popover" id="aiAssistantPanel">
    <div class="ai-header">
        <div class="ai-header-icon">
            <i data-lucide="sparkles" style="width: 16px; height: 16px;"></i> Assistente EPI Guard
        </div>
        <button class="ai-close-btn" onclick="toggleAssistenteIA()">
            <i data-lucide="x" style="width: 16px; height: 16px;"></i>
        </button>
    </div>

    <div class="ai-chat-body" id="aiChatBody">
        <div class="ai-message bot">
            Olá Administrador! Sou a Inteligência Artificial do EPI Guard. Em que posso ajudar com a análise de segurança hoje?
        </div>
        <div class="ai-loading" id="aiLoading">Processando dados...</div>
    </div>

    <div class="ai-input-area">
        <input type="text" class="ai-input" id="aiInput" placeholder="Ex: Qual o curso com mais infrações?" onkeypress="handleAIPress(event)">
        <button class="ai-send-btn" onclick="sendAIMessage()">
            <i data-lucide="send" style="width: 16px; height: 16px; margin-left: -2px;"></i>
        </button>
    </div>
</div>

<script>
    // Injeta dados reais para a IA
    window.aiData = <?php echo json_encode($aiData); ?>;
</script>
<script src="js/ai_assistant.js"></script>