<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>ChatGPT in Core PHP</title>
</head>
<body>
    <h2>Ask ChatGPT</h2>
    <form method="post" action="">
        <textarea name="prompt" rows="5" cols="40" placeholder="Type your question here..."></textarea><br>
        <input type="submit" value="Ask">
    </form>

    <?php
    if ($_SERVER["REQUEST_METHOD"] === "POST" && !empty($_POST["prompt"])) {
        $prompt = $_POST["prompt"];
        $apiKey = "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"; // Replace this

        $data = [
            "model" => "gpt-3.5-turbo",
            "messages" => [
                ["role" => "user", "content" => $prompt]
            ]
        ];

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, "https://api.openai.com/v1/chat/completions");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Content-Type: application/json",
            "Authorization: Bearer " . $apiKey
        ]);

        $response = curl_exec($ch);
        if (curl_errno($ch)) {
            echo "Error: " . curl_error($ch);
        } else {
            $result = json_decode($response, true);

            echo "<pre>";print_R($result);exit;
            echo "<h3>ChatGPT Says:</h3>";
            echo "<p>" . htmlspecialchars($result['choices'][0]['message']['content']) . "</p>";
        }

        curl_close($ch);
    }
    ?>
</body>
</html>
