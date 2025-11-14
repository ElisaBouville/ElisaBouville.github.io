<?php
session_start();
header('Content-Type: application/json');


if (!isset($_POST['_csrf']) || $_POST['_csrf'] !== ($_SESSION['csrf_token'] ?? '')) {
echo json_encode(['success' => false, 'message' => 'Token CSRF invalide.']);
exit;
}


$recaptchaSecret = '6LfumgwsAAAAAPj6QvI7gGtkChFEmUcllxZOBK_l';
$recaptchaResponse = $_POST['g-recaptcha-response'] ?? '';


$verify = file_get_contents("https://www.recaptcha.net/recaptcha/api/siteverify?secret=" . $recaptchaSecret . "&response=" . $recaptchaResponse);
$captchaSuccess = json_decode($verify);


if (!$captchaSuccess || !$captchaSuccess->success) {
echo json_encode(['success' => false, 'message' => 'reCAPTCHA invalide.']);
exit;
}


function safe($v) { return htmlspecialchars(trim($v), ENT_QUOTES, 'UTF-8'); }


$nomEntreprise = safe($_POST['nomEntreprise']);
$nomContact = safe($_POST['nomContact']);
$fonction = safe($_POST['fonction']);
$telephone = safe($_POST['telephone'] ?? '');
$mail = safe($_POST['mail']);
$motif = safe($_POST['motif']);
$description = safe($_POST['description']);


$to = "elisa.bouville.osteo@gmail.com";
$subject = "Nouvelle demande de renseignement depuis le site";
$message = "Nom de l'entreprise : $nomEntreprise\n" .
"Nom du contact : $nomContact\n" .
"Fonction : $fonction\n" .
"Téléphone : $telephone\n" .
"Mail : $mail\n" .
"Motif : $motif\n" .
"Description : $description\n";


$headers = "From: no-reply@ton-domaine.com\r\n".
"Reply-To: $mail\r\n".
"Content-Type: text/plain; charset=UTF-8\r\n";


if (mail($to, $subject, $message, $headers)) {
echo json_encode(['success' => true]);
} else {
echo json_encode(['success' => false, 'message' => "Échec de l'envoi de l'e-mail."]);
}


$confirmSubject = "Votre demande a bien été reçue";
$confirmMessage = "Bonjour,\n\nVotre demande a bien été reçue.\nNous vous répondrons dans les plus brefs délais.\n\nRécapitulatif :\n- Entreprise : $nomEntreprise\n- Contact : $nomContact\n- Motif : $motif\n\nMerci de votre confiance.";


$confirmHeaders = "From: no-reply@ton-domaine.com\r\n".
"Content-Type: text/plain; charset=UTF-8\r\n";


mail($mail, $confirmSubject, $confirmMessage, $confirmHeaders);
?>
