<?php

declare(strict_types=1);

namespace App\Service;

use Psr\Log\LoggerInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Address;
use Twig\Environment;

class EmailService
{
    /**
     * Paramètres communs pour les emails
     */
    private const DEFAULT_SENDER_EMAIL = 'enora.lenez@enorehab.fr';
    private const DEFAULT_SENDER_NAME = 'Enorehab';
    private const ADMIN_EMAIL = 'enora.lenez@enorehab.fr';
    private const EBOOK_PATH = '/assets/ebooks/epaule-mobilite.pdf';
    
    public function __construct(
        private readonly MailerInterface $mailer,
        private readonly Environment $twig,
        private readonly LoggerInterface $logger,
        private readonly string $projectDir
    ) {
    }

    /**
     * Envoie l'email de confirmation au client pour la demande de bilan
     *
     * @param string $to Email du client
     * @param string $name Nom du client
     * @param string $phone Téléphone (optionnel)
     * @param string $instagram Instagram (optionnel)
     * @return bool Succès ou échec de l'envoi
     */
    public function sendBilanClientConfirmation(string $to, string $name, string $phone = '', string $instagram = ''): bool
    {
        try {
            $context = [
                'NAME' => $name,
                'EMAIL' => $to,
                'PHONE' => $phone ?: 'Non renseigné',
                'INSTAGRAM' => $instagram ?: 'Non renseigné',
                'YEAR' => date('Y')
            ];

            $emailContent = $this->twig->render('emails/client_bilan_confirmation.html.twig', $context);

            $email = (new Email())
                ->from(new Address(self::DEFAULT_SENDER_EMAIL, self::DEFAULT_SENDER_NAME))
                ->to($to)
                ->replyTo(self::DEFAULT_SENDER_EMAIL)
                ->subject('Confirmation de votre bilan kiné personnalisé')
                ->html($emailContent)
                ->text($this->createTextVersion($emailContent));

            $this->mailer->send($email);
            return true;
        } catch (\Exception $e) {
            $this->logger->error('Erreur lors de l\'envoi de l\'email de confirmation au client', [
                'error' => $e->getMessage(),
                'email' => $to
            ]);
            return false;
        }
    }

    /**
     * Envoie l'email de notification à l'administrateur pour une demande de bilan
     *
     * @param array $data Données du client
     * @param bool $dbSuccess Succès de l'enregistrement en base
     * @param string $dbErrorMessage Message d'erreur DB (si échec)
     * @return bool Succès ou échec de l'envoi
     */
    public function sendBilanAdminNotification(array $data, bool $dbSuccess = true, string $dbErrorMessage = ''): bool
    {
        try {
            $context = [
                'NAME' => $data['name'],
                'EMAIL' => $data['email'],
                'PHONE' => $data['phone'] ?? 'Non renseigné',
                'INSTAGRAM' => $data['instagram'] ?? 'Non renseigné',
                'DATE' => date('d/m/Y H:i:s'),
                'IP' => $_SERVER['REMOTE_ADDR'] ?? 'Inconnue',
                'DB_STATUS' => $dbSuccess ? 'Enregistré avec succès en base de données' : 'Échec de l\'enregistrement en base',
                'DB_BG_COLOR' => $dbSuccess ? '#1a3a1a' : '#3a1a1a',
                'DB_ERROR_MESSAGE' => $dbErrorMessage ? '<br><span style="color: #ff6b6b;">' . $dbErrorMessage . '</span>' : '',
                'YEAR' => date('Y')
            ];

            $emailContent = $this->twig->render('emails/admin_bilan_notification.html.twig', $context);

            $email = (new Email())
                ->from(new Address(self::DEFAULT_SENDER_EMAIL, 'Système Enorehab'))
                ->to(self::ADMIN_EMAIL)
                ->replyTo($data['email'])
                ->subject('Nouvelle demande de bilan kiné - ' . $data['name'])
                ->html($emailContent)
                ->text($this->createTextVersion($emailContent));

            $this->mailer->send($email);
            return true;
        } catch (\Exception $e) {
            $this->logger->error('Erreur lors de l\'envoi de l\'email de notification admin', [
                'error' => $e->getMessage(),
                'client' => $data['email'] ?? 'inconnu'
            ]);
            return false;
        }
    }

    /**
     * Envoie l'ebook au client
     *
     * @param string $to Email du destinataire
     * @param string $name Nom du destinataire
     * @return bool Succès ou échec de l'envoi
     */
    public function sendEbookToClient(string $to, string $name): bool
    {
        try {
            $context = [
                'NAME' => $name,
                'EMAIL' => $to,
                'YEAR' => date('Y')
            ];

            $ebookPath = $this->projectDir . '/public' . self::EBOOK_PATH;
            
            if (!file_exists($ebookPath)) {
                $this->logger->error('Ebook non trouvé', ['path' => $ebookPath]);
                return false;
            }

            $emailContent = $this->twig->render('emails/ebook_template.html.twig', $context);

            $email = (new Email())
                ->from(new Address(self::DEFAULT_SENDER_EMAIL, self::DEFAULT_SENDER_NAME))
                ->to($to)
                ->replyTo(self::DEFAULT_SENDER_EMAIL)
                ->subject('Votre ebook gratuit : Épaule - Guide de mobilité')
                ->html($emailContent)
                ->text($this->createTextVersion($emailContent))
                ->attachFromPath($ebookPath, 'Epaule - Guide de mobilité.pdf');

            $this->mailer->send($email);
            return true;
        } catch (\Exception $e) {
            $this->logger->error('Erreur lors de l\'envoi de l\'ebook', [
                'error' => $e->getMessage(),
                'email' => $to
            ]);
            return false;
        }
    }

    /**
     * Envoie une notification admin pour un téléchargement d'ebook
     *
     * @param array $data Données utilisateur
     * @param bool $dbSuccess Succès de l'enregistrement en base
     * @param array $stats Statistiques
     * @return bool Succès ou échec de l'envoi
     */
    public function sendEbookAdminNotification(array $data, bool $dbSuccess = true, array $stats = []): bool
    {
        try {
            $context = [
                'NAME' => $data['name'],
                'EMAIL' => $data['email'],
                'DATE' => date('d/m/Y H:i:s'),
                'IP' => $_SERVER['REMOTE_ADDR'] ?? 'Inconnue',
                'CONSENT' => isset($data['consent']) && $data['consent'] ? 'Oui' : 'Non',
                'DB_SUCCESS' => $dbSuccess ? 'Oui' : 'Non',
                'DB_COLOR' => $dbSuccess ? '#0ed0ff' : '#ff6b6b',
                'TOTAL_DOWNLOADS' => $stats['total'] ?? '?',
                'TODAY_DOWNLOADS' => $stats['today'] ?? '?',
                'MAIL_LIST_COUNT' => $stats['mail_list'] ?? '?',
                'YEAR' => date('Y')
            ];

            $emailContent = $this->twig->render('emails/admin_ebook_notification.html.twig', $context);

            $email = (new Email())
                ->from(new Address(self::DEFAULT_SENDER_EMAIL, 'Système Enorehab'))
                ->to(self::ADMIN_EMAIL)
                ->replyTo($data['email'])
                ->subject('Nouveau téléchargement d\'ebook - ' . $data['name'])
                ->html($emailContent)
                ->text($this->createTextVersion($emailContent));

            $this->mailer->send($email);
            return true;
        } catch (\Exception $e) {
            $this->logger->error('Erreur lors de l\'envoi de la notification admin ebook', [
                'error' => $e->getMessage(),
                'client' => $data['email'] ?? 'inconnu'
            ]);
            return false;
        }
    }

    /**
     * Crée une version texte à partir du contenu HTML pour les clients mail
     * qui ne supportent pas le HTML
     *
     * @param string $html Contenu HTML
     * @return string Version texte
     */
    private function createTextVersion(string $html): string
    {
        // Suppression des balises HTML
        $text = strip_tags($html);
        
        // Remplacer les balises courantes par des retours à la ligne
        $text = str_replace(['<br>', '<br/>', '<br />', '</p>', '</h1>', '</h2>', '</h3>'], "\n", $text);
        
        // Supprimer les espaces multiples
        $text = preg_replace('/\s+/', ' ', $text);
        
        // Convertir les entités HTML
        $text = html_entity_decode($text);
        
        return trim($text);
    }
}