<?php

// --- 1. الإعدادات والمفاتيح ---
$telegramToken = "8965795554:AAFYuvrHzUg9QHLe3OyeneCvtQp2V9YyTiE";
$channelId     = "@ITIN333";
$geminiApiKey   = "AQ.Ab8RN6LdFFcoNX2goXQ6oDI-818XHFXuqqF0WMPEmiAaC4B3WQ";

// --- 2. طلب النص من الذكاء الاصطناعي (Gemini API) ---
$prompt = "اكتب منشوراً تووعوياً ومفيداً لقناة تليجرام في أحد المجالات التالية بصورة متنوّعة: (أمن المعلومات، الأمن السيبراني، حماية الحسابات والأجهزة من الاختراق، نصائح تقنية للحاسب، تقنيات الإنترنت الحديثة). الشروط: لغة عربية سهلة ومشوقة، يحتوي على عنوان ونقاط، رموز تعبيرية (Emojis)، وهاشتاقات في النهاية. لا تذكر أي مقدمات، ابدأ بالمنشور مباشرة.";

// استخدام الموديل الحديث gemini-3.6-flash
$geminiUrl = "https://generativelanguage.googleapis.com/v1beta/models/gemini-3.6-flash:generateContent?key=" . trim($geminiApiKey);

$geminiData = [
    "contents" => [
        [
            "parts" => [
                ["text" => $prompt]
            ]
        ]
    ]
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $geminiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($geminiData));
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$geminiResponse = curl_exec($ch);
curl_close($ch);

$geminiResult = json_decode($geminiResponse, true);
$postText = $geminiResult['candidates'][0]['content']['parts'][0]['text'] ?? null;

// --- 3. إرسال المنشور إلى تليجرام ---
if ($postText) {
    $telegramUrl = "https://api.telegram.org/bot{$telegramToken}/sendMessage";
   
    $telegramData = [
        'chat_id'    => $channelId,
        'text'       => $postText,
        'parse_mode' => 'Markdown'
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $telegramUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($telegramData));
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
   
    $telegramResponse = curl_exec($ch);
    curl_close($ch);

    // رد قصير ليتوافق مع كرون جوب
    echo "OK"; 
} else {
    echo "Error";
}

?>
