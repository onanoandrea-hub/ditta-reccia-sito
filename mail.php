<?php

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
  http_response_code(403);
  echo "There was a problem with your submission, please try again.";
  exit;
}

// TODO: sostituisci con la mail reale del destinatario
$mail_to = "onano.andrea@me.com";

$subject = isset($_POST["subject"]) ? trim($_POST["subject"]) : "";
if ($subject === "") {
  $subject = "Richiesta dal sito";
}

$name = isset($_POST["name"])
  ? str_replace(array("\r", "\n"), array(" ", " "), strip_tags(trim($_POST["name"])))
  : "";

$phone = isset($_POST["phone"]) ? trim($_POST["phone"]) : "";

$email = isset($_POST["email"]) ? filter_var(trim($_POST["email"]), FILTER_SANITIZE_EMAIL) : "";
$message = isset($_POST["message"]) ? trim($_POST["message"]) : "";

if (empty($name) || !filter_var($email, FILTER_VALIDATE_EMAIL) || empty($message)) {
  http_response_code(400);
  echo "Please complete the form and try again.";
  exit;
}

$content = "Name: $name\n";
if ($phone !== "") $content .= "Phone: $phone\n\n";
$content .= "Email: $email\n\n";
$content .= "Message:\n$message\n";

$headers = "From: $name <$email>\r\n";
$headers .= "Reply-To: $email\r\n";

$success = mail($mail_to, $subject, $content, $headers);
if ($success) {
  http_response_code(200);
  echo "Thank You! Your message has been sent.";
  exit;
}

http_response_code(500);
echo "Oops! Something went wrong, we couldn't send your message.";

?>

