
<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $mail = new PHPMailer(true);

    try {
        // Form verilerini al
        $name = strip_tags(trim($_POST["name"]));
        $email = filter_var(trim($_POST["email"]), FILTER_SANITIZE_EMAIL);
        $number = strip_tags(trim($_POST["number"]));
        $website = strip_tags(trim($_POST["website"]));
        $message = strip_tags(trim($_POST["message"]));

        // Sunucu ayarları
         $mail->isSMTP();                                            
    $mail->Host       = 'mail.turingai.org.tr'; // SMTP sunucusunu değiştirin
    $mail->SMTPAuth   = true;                                   
    $mail->Username   = 'info@turingai.org.tr'; // SMTP kullanıcı adınız
    $mail->Password   = '34T.uring533'; // SMTP şifreniz
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;            
    $mail->Port       = 465; // SMTP Port numarası

        // Alıcıları ayarlayın
        $mail->setFrom('from@yourdomain.com', $name); // Gönderen olarak formdan gelen ismi kullan
        $mail->addAddress('recipient@example.com', 'Joe User'); // Alıcı ekleyin

        // İçerik
        $mail->isHTML(true);                                  
        $mail->Subject = 'Yeni Mesaj: ' . $name;
        $mail->Body    = "İsim: $name<br>Email: $email<br>Telefon Numarası: $number<br>Kurum: $website<br>Mesaj:<br>$message";
        $mail->AltBody = "İsim: $name\nEmail: $email\nTelefon Numarası: $number\nKurum: $website\nMesaj:\n$message";

        $mail->send();
        echo 'Message has been sent';
    } catch (Exception $e) {
        echo "Message could not be sent. PHPMailer Error: {$mail->ErrorInfo}";
    }
} else {
    // POST isteği dışındaki istekler için hata mesajı gönder.
    echo "Invalid request";
}
?>
