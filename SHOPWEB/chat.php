<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Hỗ trợ AI - Tư vấn ô tô</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      background: #f4f7fc;
      font-family: 'Segoe UI', sans-serif;
    }
    .chat-card {
      background: #ffffff;
      border-radius: 15px;
      box-shadow: 0 4px 10px rgba(0, 0, 0, 0.07);
      padding: 25px;
    }
    .ai-response {
      background: #eaf4ff;
      padding: 20px;
      border-radius: 10px;
      border-left: 4px solid #0d6efd;
      white-space: pre-line;
    }
    .btn-primary:disabled {
      opacity: 0.6;
    }
  </style>
</head>
<body>
  <div class="container mt-5 mb-5">
    <h1 class="text-center text-primary mb-4">🚗 Tư vấn mua xe thông minh cùng AI</h1>

    <div class="chat-card mx-auto" style="max-width: 800px;">
      <h4 class="mb-3 text-success">Nhập câu hỏi của bạn:</h4>

      <div class="mb-3">
        <label for="message" class="form-label">Bạn đang quan tâm dòng xe nào, giá bao nhiêu, nhu cầu sử dụng ra sao?</label>
        <textarea id="message" class="form-control" rows="4" placeholder="Ví dụ: Tôi cần một chiếc xe gia đình 7 chỗ, giá dưới 1 tỷ..." required></textarea>
      </div>

      <button id="sendBtn" class="btn btn-primary w-100" onclick="sendMessage()">
        <span id="send-text">📨 Gửi câu hỏi</span>
        <span id="loading-icon" class="spinner-border spinner-border-sm visually-hidden ms-2" role="status" aria-hidden="true"></span>
      </button>

      <div class="mt-4">
        <h5 class="text-muted mb-2">📥 Phản hồi từ AI:</h5>
        <div id="response" class="ai-response">
          Chúng tôi sẽ trả lời bạn trong thời gian muộn nhất.
        </div>
      </div>
    </div>
  </div>

<?php
    $database_js = "";
    $conn = new mysqli("localhost", "root", "", "shopweb");
    if ($conn->connect_error) {
        echo "<script>console.error('Kết nối database thất bại');</script>";
    } else {
        $sql_query = "SELECT * FROM products";
        $stmt = $conn->prepare($sql_query);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $line = "database_info += 'ID: {$row['id']}, Title: {$row['title']}, Price: {$row['price']}, Description: {$row['description']}, Status: {$row['status']}\\n';\n";
                $line .= "console.log('Product ID: {$row['id']}, Title: {$row['title']}, Price: {$row['price']}, Description: {$row['description']}, Status: {$row['status']}');\n";
                $database_js .= $line;
            }
        } else {
            $database_js .= "console.log('No products found');";
        }
    }
?>

<script>
  const prompt = `Bạn là một chuyên viên tư vấn ô tô chuyên nghiệp. 
Bạn có nhiệm vụ cung cấp thông tin rõ ràng, ngắn gọn, hấp dẫn và chuyên sâu về các dòng xe, giá cả, khuyến mãi và dịch vụ hậu mãi. 
Mục tiêu là giúp khách hàng chọn được chiếc xe phù hợp nhất, thể hiện sự am hiểu và tận tâm.`;

  let database_info = ``;
  <?php echo $database_js; ?>

  function sendMessage() {
    const message = document.getElementById("message").value.trim();
    const responseDiv = document.getElementById("response");
    const btn = document.getElementById("sendBtn");
    const spinner = document.getElementById("loading-icon");
    const sendText = document.getElementById("send-text");

    if (!message) {
      responseDiv.innerHTML = "❗ Vui lòng nhập nội dung câu hỏi.";
      return;
    }

    // Show loading
    spinner.classList.remove("visually-hidden");
    sendText.textContent = "Đang gửi...";
    btn.disabled = true;

    const apiKey = 'AIzaSyB4jx2M8IkzDkxBCVF1HO1j5JxjVQgHakc';
    const url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent';
    const data = {
      "contents": [{
        "parts": [{
          "text": prompt + "\n\n" + database_info + "\n\nCâu hỏi khách hàng: " + message
        }]
      }]
    };

    fetch(url, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-goog-api-key': apiKey
      },
      body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(data => {
      if (data && data.candidates && data.candidates.length > 0) {
        const reply = data.candidates[0].content.parts[0].text;
        responseDiv.innerHTML = `<strong>AI:</strong><br>${reply}`;
      } else {
        responseDiv.innerHTML = "❌ Không có phản hồi từ AI.";
      }
    })
    .catch(error => {
      console.error("Lỗi:", error);
      responseDiv.innerHTML = "⚠️ Đã xảy ra lỗi khi gửi yêu cầu.";
    })
    .finally(() => {
      spinner.classList.add("visually-hidden");
      sendText.textContent = "📨 Gửi câu hỏi";
      btn.disabled = false;
    });
  }
</script>
</body>
</html>