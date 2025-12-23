<?php
class GeminiService {
    private $apiKey;
    private $model;
    private $timeout;

    public function __construct($config) {
        $this->apiKey = $config['api_key'];
        $this->model = $config['model'] ?? 'gemini-2.5-flash';
        $this->timeout = $config['timeout'] ?? 30;
    }

    /**
     * Gửi nội dung đến Gemini API
     * Code gốc tham khảo từ: ChatController.php -> callGeminiAPI
     */
    public function generateContent($text, $imageBase64 = null) {
        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$this->model}:generateContent?key=" . $this->apiKey;

        // 1. Chuẩn bị Payload (Dữ liệu gửi đi)
        $parts = [
            ["text" => $text]
        ];

        // Xử lý ảnh (Multimodal) - Giữ nguyên logic cũ của bạn
        if ($imageBase64) {
            // Lấy mime type (image/jpeg, image/png...)
            preg_match('/^data:(image\/\w+);base64,/', $imageBase64, $matches);
            $mimeType = $matches[1] ?? 'image/jpeg';
            
            // Chỉ lấy phần data base64 sạch
            $base64Clean = explode(',', $imageBase64)[1] ?? $imageBase64;

            $parts[] = [
                "inline_data" => [
                    "mime_type" => $mimeType,
                    "data" => $base64Clean
                ]
            ];
        }

        $body = [
            "contents" => [
                [ "parts" => $parts ]
            ]
        ];

        // 2. Cấu hình cURL
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout); // Thêm timeout để tránh treo
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        if (curl_errno($ch)) {
            $errorMsg = curl_error($ch);
            curl_close($ch);
            // Ném lỗi ra ngoài để Controller xử lý
            throw new Exception("Connection Error: " . $errorMsg);
        }
        
        curl_close($ch);

        // 3. Xử lý phản hồi (Response)
        $jsonObj = json_decode($response, true);

        // Kiểm tra lỗi từ phía Google trả về (ví dụ sai Key, sai Model)
        if (isset($jsonObj['error'])) {
            throw new Exception("Gemini API Error: " . $jsonObj['error']['message']);
        }
        
        // Kiểm tra HTTP Code không phải 200
        if ($httpCode !== 200) {
             throw new Exception("API responded with HTTP $httpCode");
        }

        // Lấy nội dung trả lời
        if (isset($jsonObj['candidates'][0]['content']['parts'][0]['text'])) {
            return $jsonObj['candidates'][0]['content']['parts'][0]['text'];
        }

        // Trường hợp không có nội dung (bị filter do safety settings hoặc lỗi lạ)
        return "I can't analyze this right now. (No content returned)"; 
    }
}
?>