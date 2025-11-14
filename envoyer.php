<?php
session_start();
header('Content-Type: application/json');

// Inclure PHPMailer
require 'PHPMailer.php';
require 'SMTP.php';
require 'Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// CSRF
if(!isset($_POST['_csrf']) || $_POST['_csrf'] !== ($_SESSION['csrf_token'] ?? '')){
    echo json_encode(['success'=>false,'message'=>'Token CSRF invalide.']);
    exit;
}

// reCAPTCHA
$recaptchaSecret = '6LedsAwsAAAAALFJQ2sMSlt2dfvMtOOlunSTtUeU';
$recaptchaResponse = $_POST['g-recaptcha-response'] ?? '';
$verify = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret={$recaptchaSecret}&response={$recaptchaResponse}");
$captchaSuccess = json_decode($verify);

if(!$captchaSuccess || !$captchaSuccess->success){
    echo json_encode(['success'=>false,'message'=>'reCAPTCHA invalide.']);
    exit;
}

// Nettoyage
function safe($v){return htmlspecialchars(trim($v),ENT_QUOTES,'UTF-8');}

$nomEntreprise = safe($_POST['nomEntreprise']);
$nomContact = safe($_POST['nomContact']);
$fonction = safe($_POST['fonction']);
$telephone = safe($_POST['telephone'] ?? '');
$mail = safe($_POST['mail']);
$motif = safe($_POST['motif']);
$description = safe($_POST['description']);

try {
    $mailer = new PHPMailer(true);
    $mailer->isSMTP();
    $mailer->Host = 'smtp.gmail.com';
    $mailer->SMTPAuth = true;
    $mailer->Username = 'elisa.bouville.osteo@gmail.com';
    $mailer->Password = 'Motdepassecabinet80!';
    $mailer->SMTPSecure = 'tls';
    $mailer->Port = 587;

    $mailer->setFrom('no-reply@ton-domaine.com','Site Elisa Bouville');
    $mailer->addAddress('elisa.bouville.osteo@gmail.com');
    $mailer->addReplyTo($mail,$nomContact);
    $mailer->Subject = "Nouvelle demande depuis le site";
    $mailer->Body = "Nom de l'entreprise: $nomEntreprise\nNom du contact: $nomContact\nFonction: $fonction\nTéléphone: $telephone\nMail: $mail\nMotif: $motif\nDescription: $description";
    $mailer->send();

    // Mail de confirmation
    $confirm = new PHPMailer(true);
    $confirm->isSMTP();
    $confirm->Host = 'smtp.gmail.com';
    $confirm->SMTPAuth = true;
    $confirm->Username = 'elisa.bouville.osteo@gmail.com';
    $confirm->Password = 'XOXOXOXXOXOXXOX';
    $confirm->SMTPSecure = 'tls';
    $confirm->Port = 587;
    $confirm->setFrom('no-reply@ton-domaine.com','Site Elisa Bouville');
    $confirm->addAddress($mail);
    $confirm->Subject = "Votre demande a bien été reçue";
    $confirm->Body = "Bonjour,\n\nVotre demande a bien été reçue.\nNous vous répondrons rapidement.\n\nRécapitulatif:\n- Entreprise: $nomEntreprise\n- Contact: $nomContact\n- Motif: $motif\n\nMerci de votre confiance.";
    $confirm->send();

    echo json_encode(['success'=>true]);

} catch(Exception $e){
    echo json_encode(['success'=>false,'message'=>"Erreur PHPMailer: ".$e->getMessage()]);
}
?>
