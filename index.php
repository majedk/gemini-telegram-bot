<?php
$telegramToken = "8965795554:AAFYuvrHzUg9QHLe3OyeneCvtQp2V9YyTiE";
$channelId = "@ITIN333";

// قراءة مفتاح Groq بأمان من متغير البيئة الممرر عبر GitHub Actions
$groqApiKey = getenv('GROQ_API_KEY');

if (empty($groqApiKey)) {
    die("Error: GROQ_API_KEY is missing. Please set it in GitHub Secrets.");
}

// قائمة شاملة للمواضيع
$topicsPool = [
    "مكونات الحاسوب الأساسية (العتاد والبرمجيات)",
    "أنظمة التشغيل المختلفة وإيجابياتها",
    "مفاهيم الأمن السيبراني وأهميته في العصر الحديث",
    "حماية البيانات الشخصية والشركات من الاختراق",
    "شبكات الحاسوب وأنواعها",
    "الذكاء الاصطناعي وتقنيات المستقبل",
    "التخزين السحابي وتقنيات حفظ البيانات"
];

// اختيار موضوع عشوائي
$randomTopic = $topicsPool[array_rand($topicsPool)];

// ملف مؤقت لحفظ آخر نمط تم إرساله لضمان التبادل (طويل / قصير)
$styleFile = 'last_style.txt';
$lastStyle = '';

if (file_exists($styleFile)) {
    $lastStyle = trim(file_get_contents($styleFile));
}

// تبديل النمط بناءً على آخر رسالة أُرسلت
if ($lastStyle === 'long') {
    $randomStyle = 'short';
} else {
    $randomStyle = 'long';
}

// حفظ النمط الجديد للمرة القادمة
file_put_contents($styleFile, $randomStyle);

// بناء الـ Prompt بناءً على النمط المتناوب
if ($randomStyle === "long") {
    $prompt = "اكتب منشوراً تقنياً وتوعوياً شاملاً ومفصلاً لقناة تليجرام حول هذا الموضوع: ($randomTopic).
الشروط:
- لغة عربية سهلة وجذابة مع عنوان رئيسي معبر.
- استخدام نقاط توضيحية (Bullet points) لتنظيم الشرح.
- استخدام الإيموجي بشكل مناسب وجميل.
- وضع هاشتاغات دقيقة في النهاية.
- لا تضع أي مقدمة أو كلام إضافي، وابدأ بالمنشور مباشرة.";
} else {
    $prompt = "اكتب خبراً أو معلومة تقنية قصيرة ومباشرة لقناة تليجرام حول هذا الموضوع: ($randomTopic).
الشروط:
- الطول لا يتجاوز 3 إلى 4 أسطر فقط.
- يتضمن عنواناً قصيراً وإيموجي معبر.
- وضع هاشتاغين (#) في النهاية فقط.
- لا تضع أي مقدمة أو كلام إضافي، وابدأ بالمنشور مباشرة.";
}

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
