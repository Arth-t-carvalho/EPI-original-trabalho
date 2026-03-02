<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';

header('Content-Type: application/json');

// IMPORTANTE: Insira sua chave da API do Gemini aqui ou configure como variável de ambiente
$apiKey = getenv('GEMINI_API_KEY') ?: 'SUA_CHAVE_AQUI'; 

if (strpos($apiKey, 'SUA_CHAVE') !== false) {
    echo json_encode(['error' => 'Chave de API do Gemini não configurada. Por favor, configure GEMINI_API_KEY no servidor.']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$userMessage = $data['message'] ?? '';
$includeSummary = $data['include_summary'] ?? false;

if (empty($userMessage)) {
    echo json_encode(['error' => 'Mensagem vazia']);
    exit;
}

// CONTEXTO DO SISTEMA E RESUMO DAS TAREFAS
$resumoTarefas = "
Recapitulando o que foi feito recentemente no sistema:
1. Super Admin: Agora tem visão global e pode filtrar por curso em todas as telas.
2. Controle de Sala: Refatorado para arquitetura escalável. O status (Risco, Reincidente, Regular) agora é calculado no Backend.
3. Filtros: Remoção de filtros redundantes e inclusão de filtros por Aluno e Data Específica em Infrações.
4. Gestão de Alunos: Implementado upload de foto oficial.
5. Ocorrências: Transformada em lista administrativa de confirmação para o Super Admin.
";

$systemPrompt = "Você é o Assistente Virtual do sistema EPI Guard.
Sempre que o usuário informar a chave ou for a primeira interação, você deve agradecer e, se solicitado, apresentar este resumo: $resumoTarefas.
Informações atuais do sistema:
- Alunos: $totalAlunos | Ocorrências: $totalOcorrencias.
Responda sempre em Português do Brasil.";

if ($includeSummary) {
    $userMessage = "O usuário acabou de configurar a chave ou iniciou o chat. Apresente-se e dê o seguinte resumo das atualizações: " . $resumoTarefas . "\n\nMensagem dele: " . $userMessage;
}

// Chamada para a API do Gemini
$url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=" . $apiKey;

$payload = [
    "contents" => [
        [
            "parts" => [
                ["text" => $systemPrompt . "\n\nUsuário: " . $userMessage]
            ]
        ]
    ]
];

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

if ($httpCode !== 200) {
    echo json_encode(['error' => 'Erro na API de IA (HTTP ' . $httpCode . ')', 'details' => json_decode($response)]);
    exit;
}

$responseData = json_decode($response, true);
$aiText = $responseData['candidates'][0]['content']['parts'][0]['text'] ?? 'Desculpe, não consegui processar sua pergunta.';

echo json_encode(['reply' => $aiText]);
