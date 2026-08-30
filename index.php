<?php
$telegramToken = "8965795554:AAFYuvrHzUg9QHLe3OyeneCvtQp2V9YyTiE";
$channelId = "@ITIN333";
$groqApiKey = "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";
$prompt = "اكتب منشوراً تووعوياً ومفيداً لقناة تليجرام في أحد المجالات التالية بصورة متنوّعة: (أمن المعلومات، الأمن السيبراني، حماية الحسابات والأجهزة من الاختراق، نصائح تقنية للحاسب، تقنيات الإنترنت الحديثة). الشروط: لغة عربية سهلة ومشوقة، يحتوي على عنوان ونقاط، رموز تعبيرية (Emojis)، وهاشتاقات في النهاية. لا تذكر أي مقدمات، ابدأ بالمنشور مباشرة.";
$groqUrl = "https://api.groq.com/openai/v1/chat/completions";
$groqData = [
    "model" => "llama-3.3-70b-versatile",
    "messages" => [
        [
            "role" => "user",
            "content" => $prompt
        ]
    ]
];
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $groqUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: Bearer ' . trim($groqApiKey)
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($groqData));
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$groqResponse = curl_exec($ch);
curl_close($ch);
$groqResult = json_decode($groqResponse, true);
$postText = $groqResult['choices'][0]['message']['content'] ?? null;
if (!$postText) {
    echo "Error: Groq did not return text. Response: " . $groqResponse;
    exit;
}
$telegramUrl = "https://api.telegram.org/bot{$telegramToken}/sendMessage";
$telegramData = [
    'chat_id' => $channelId,
    'text' => $postText
];
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $telegramUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($telegramData));
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$telegramResponse = curl_exec($ch);
curl_close($ch);
$telegramResult = json_decode($telegramResponse, true);
if (isset($telegramResult['ok']) && $telegramResult['ok'] === true) {
    echo "SUCCESS_SENT";
} else {
    echo "Telegram Error: " . $telegramResponse;
}
?>
