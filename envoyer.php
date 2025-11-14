<?php
session_start();
header('Content-Type: application/json');

// Vérification du jeton CSRF
if (!isset($_POST['_csrf']) || $_POST['_csrf'] !== ($_SESSION['csrf_token'] ?? '')) {
    echo json_encode(['success' => false, 'message' => 'Token CSRF invalide.']);
    exit;
}

// Vérification du reCAPTCHA
$recaptchaSecret = 'TA_CLE_SECRETE_RECAPTCHA';
$recaptchaResponse = $_POST['g-recaptcha-response'];
$recaptchaUrl = "https://www.google.com/recaptcha/api/siteverify?secret=$recaptchaSecret&response=$recaptchaResponse";
$recaptchaData = json_decode(file_get_contents($recaptchaUrl));

if (!$recaptchaData->success) {
    echo json_encode(['success' => false, 'message' => 'reCAPTCHA invalide.']);
    exit;
}

// Récupération des données du formulaire
$nomEntreprise = htmlspecialchars($_POST['nomEntreprise']);
$nomContact = htmlspecialchars($_POST['nomContact']);
$fonction = htmlspecialchars($_POST['fonction']);
$telephone = htmlspecialchars($_POST['telephone'] ?? '');
$mail = htmlspecialchars($_POST['mail']);
$motif = htmlspecialchars($_POST['motif']);
$description = htmlspecialchars($_POST['description']);

// Envoi de l'e-mail
$to = "elisa.bouville.osteo@gmail.com";
$subject = "Nouvelle demande de renseignement depuis le site";
$message = "
Nom de l'entreprise : $nomEntreprise
Nom du contact : $nomContact
Fonction : $fonction
Téléphone : $telephone
Mail : $mail
Motif : $motif
Description : $description
";

$headers = "From: no-reply@ton-domaine.com\r\n";
$headers .= "Reply-To: $mail\r\n";
$headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

if (mail($to, $subject, $message, $headers)) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Échec de l\'envoi de l\'e-mail.']);
}
?>
