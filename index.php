<?php
$telegramToken = "8965795554:AAFYuvrHzUg9QHLe3OyeneCvtQp2V9YyTiE";
$channelId = "@ITIN333";

// قراءة مفتاح Groq بأمان من متغير البيئة الممرر عبر GitHub Actions
$groqApiKey = getenv('GROQ_API_KEY');

if (empty($groqApiKey)) {
    die("Error: GROQ_API_KEY is missing. Please set it in GitHub Secrets.");
}

// قائمة شاملة ومختلفة للمواضيع لضمان التنويع الكامل بين كل رسالة وأخرى
$topicsPool = [
    "مكونات الحاسوب الأساسية ومميزاتها باختصار",
    "أنظمة التشغيل ودورها الأساسي في تشغيل الأجهزة",
    "مفاهيم الأمن السيبراني وأهميتها لحماية المستخدمين",
    "أهمية حماية البيانات الشخصية والشركات",
    "شبكات الحاسوب وأنواعها باختصار",
    "الذكاء الاصطناعي وتقنيات المستقبل القريب",
    "التخزين السحابي ومزاياه السريعة"
];

// اختيار موضوع عشوائي جديد تماماً في كل مرة يتم فيها إرسال رسالة
$randomTopic = $topicsPool[array_rand($topicsPool)];

$prompt = "اكتب خبراً أو معلومة تقنية وتوعوية قصيرة جداً لقناة تليجرام حول هذا الموضوع: ($randomTopic).
الشروط الصارمة:
- الطول لا يتجاوز 3 إلى 4 أسطر فقط كحد أقصى.
- لغة عربية سهلة ومباشرة.
- يتضمن عنواناً قصيراً وإيموجي معبراً.
- وضع هاشتاغين (#) في النهاية فقط.
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
