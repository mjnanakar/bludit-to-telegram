<?php

class BluditToTelegram extends Plugin {

    public function init()
    {
        // تنظیمات پیش‌فرض پلاگین
        $this->dbFields = array(
            'telegram_token' => '',  // توکن ربات تلگرام
            'chat_id' => '',         // شناسه‌ی چت یا کانال تلگرام
            'enable_notifications' => true // فعال/غیرفعال کردن ارسال پیام
        );
    }

    public function adminSidebar()
    {
        return '<a class="nav-link" href="#">Bludit to Telegram</a>';
    }

    public function form()
    {
        // دریافت مقادیر ذخیره‌شده در تنظیمات
        $telegramToken = $this->getValue('telegram_token');
        $chatID = $this->getValue('chat_id');
        $enableNotifications = $this->getValue('enable_notifications');

        // نمایش فرم تنظیمات در پنل مدیریت
        $html = '
        <h2>Bludit to Telegram</h2>
        <p>Enter your Telegram bot token and chat ID to enable notifications.</p>
        <div>
            <label>توکن ربات تلگرام:</label>
            <input name="telegram_token" type="text" value="'.$telegramToken.'" class="form-control">
        </div>
        <div>
            <label>شناسه‌ی چت (chat ID):</label>
            <input name="chat_id" type="text" value="'.$chatID.'" class="form-control">
        </div>
        <div>
            <label>ارسال پیام فعال باشد؟</label>
            <select name="enable_notifications" class="form-control">
                <option value="1" '.($enableNotifications ? 'selected' : '').'>بله</option>
                <option value="0" '.(!$enableNotifications ? 'selected' : '').'>خیر</option>
            </select>
        </div>
        ';
        return $html;
    }

    // 📌 متد برای ارسال پیام به تلگرام
    private function sendToTelegram($message)
    {
        if (!$this->getValue('enable_notifications')) {
            return; // اگر ارسال پیام غیرفعال باشد، کاری انجام نمی‌شود.
        }

        $telegramToken = $this->getValue('telegram_token');
        $chatID = $this->getValue('chat_id');

        if (empty($telegramToken) || empty($chatID)) {
            return; // اگر تنظیمات وارد نشده باشد، پیام ارسال نشود.
        }

        $url = "https://api.telegram.org/bot{$telegramToken}/sendMessage";
        $data = [
            'chat_id' => $chatID,
            'text' => $message,
            'parse_mode' => 'HTML'
        ];

        $options = [
            'http' => [
                'header'  => "Content-Type: application/x-www-form-urlencoded\r\n",
                'method'  => 'POST',
                'content' => http_build_query($data),
            ]
        ];
        $context  = stream_context_create($options);
        file_get_contents($url, false, $context);
    }

   // 📌 ارسال پیام هنگام ایجاد مقاله جدید
public function afterPageCreate($key)
{
    $page = new Page($key);
    $message = "🆕 <b>مقاله جدید منتشر شد:</b>\n"
             . "📌 <b>عنوان:</b> " . $page->title() . "\n"
             . "🔗 <a href='" . $page->permalink() . "'>مشاهده مقاله</a>";

    $this->sendToTelegram($message);
}

// 📌 ارسال پیام هنگام به‌روزرسانی مقاله
public function afterPageModify($key)
{
    $page = new Page($key);
    $message = "✏️ <b>مقاله به‌روزرسانی شد:</b>\n"
             . "📌 <b>عنوان:</b> " . $page->title() . "\n"
             . "🔗 <a href='" . $page->permalink() . "'>مشاهده مقاله</a>";

    $this->sendToTelegram($message);
}
}

?>
