<?php
$telegramToken = "8965795554:AAFYuvrHzUg9QHLe3OyeneCvtQp2V9YyTiE";
$channelId = "@ITIN333";
$groqApiKey = "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";

// قائمة شاملة ومختلفة للمواضيع لضمان التنويع الكامل بين كل رسالة وأخرى
$topicsPool = [
    "مكونات الحاسوب الأساسية (العتاد والبرمجيات) ومميزاتها",
    "أنظمة التشغيل المختلفة، إيجابياتها وسلبياتها وطريقة عملها",
    "مفاهيم الأمن السيبراني: أهميته، أهدافه، وضرورته في العصر الحديث",
    "أمن المعلومات وحماية البيانات الشخصية والشركات",
    "شبكات الحاسوب، أنواعها، وكيفية حمايتها من الاختراق",
    "الذكاء الاصطناعي وتقنيات المستقبل: إيجابياته وسلبياته المحتملة",
    "التخزين السحابي وتقنيات حفظ البيانات الحديثة"
];

// اختيار موضوع عشوائي جديد تماماً في كل مرة يتم فيها إرسال رسالة
$randomTopic = $topicsPool[array_rand($topicsPool)];

$prompt = "اكتب منشوراً تقنياً وتوعوياً جديداً ومختلفاً لقناة تليجرام حول هذا الموضوع المحدد: ($randomTopic).
الشروط الصارمة:
- تجنب تكرار المواضيع السابقة واجعل المعلومات جديدة ومفيدة.
- لغة عربية سهلة، سلسة، وجذابة للقارئ.
- يتضمن عنواناً رئيسياً معبراً وجذاباً.
- استخدام نقاط توضيحية (Bullet points) لتنظيم الشرح.
- استخدام الإيموجي بشكل مناسب وجميل.
- وضع هاشتاغات دقيقة ومناسبة في النهاية.
- لا تضع أي مقدمة أو كلام إضافي، وابدأ بالمنشور مباشرة.";

$groqUrl = "https://api.groq.com/openai/v1/chat/completions";
$groqData = [
    "model" => "openai/gpt-oss-120b",
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
