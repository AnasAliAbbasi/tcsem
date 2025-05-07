<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>DeepSeek Integration with Core PHP</title>
</head>
<body>
    <h2>Ask DeepSeek</h2>
    <form method="post" action="">
        <textarea name="prompt" rows="5" cols="40" placeholder="Type your question here..."></textarea><br>
        <input type="submit" value="Ask">
    </form>

    <?php
    if ($_SERVER["REQUEST_METHOD"] === "POST" && !empty($_POST["prompt"])) {
        // User's prompt
        $prompt = $_POST["prompt"];
        
        // DeepSeek API key (replace with your actual API key)
        $apiKey = "sk-49e2f123b46d4dd09913cbcd80fa81d2"; 

        // Prepare the data to send in the API request
        $data = [
            "model" => "deepseek-chat",  // Ensure this matches the model available in DeepSeek
            "messages" => [
                ["role" => "user", "content" => $prompt]
            ],
            "stream" => false  // Whether or not to stream the response
        ];

        // Initialize cURL for API request
        $ch = curl_init();
        
        curl_setopt($ch, CURLOPT_URL, "https://api.deepseek.com/chat/completions");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Content-Type: application/json",
            "Authorization: Bearer " . $apiKey
        ]);

        // Execute the cURL request and get the response
        $response = curl_exec($ch);
        
        // Check for errors in the cURL request
        if (curl_errno($ch)) {
            echo "Error: " . curl_error($ch);
        } else {
            // Decode the API response from JSON
            $result = json_decode($response, true);
            echo "<pre>";print_r($result);exit;
            // Display the result from DeepSeek
            if (isset($result['choices'][0]['message']['content'])) {
                echo "<h3>DeepSeek Says:</h3>";
                echo "<p>" . htmlspecialchars($result['choices'][0]['message']['content']) . "</p>";
            } else {
                echo "<p>No response from DeepSeek.</p>";
            }
        }

        // Close the cURL session
        curl_close($ch);
    }
    ?>
</body>
</html>
