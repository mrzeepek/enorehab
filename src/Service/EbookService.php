<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\EbookSubscriber;
use App\Exception\EbookException;
use App\Repository\EbookSubscriberRepository;
use Psr\Log\LoggerInterface;

class EbookService
{
    public function __construct(
        private readonly EbookSubscriberRepository $ebookSubscriberRepository,
        private readonly EmailService $emailService,
        private readonly LoggerInterface $logger
    ) {
    }

    /**
     * Traite une demande de téléchargement d'ebook
     *
     * @param array $data Données du formulaire
     * @return array Résultat du traitement avec statuts
     */
    public function processEbookRequest(array $data): array
    {
        $result = [
            'success' => false,
            'db_success' => false,
            'email_success' => false,
            'admin_email_success' => false,
            'errors' => [],
            'message' => '',
            'stats' => []
        ];

        try {
            // 1. Validation des données
            $this->validateEbookData($data);
            
            // 2. Enregistrement en BDD
            $dbResult = $this->saveEbookSubscriber($data);
            $result['db_success'] = $dbResult['success'];
            
            if (!$dbResult['success']) {
                $result['errors'][] = $dbResult['message'];
            }
            
            // 3. Envoi de l'ebook par email
            $emailSent = $this->emailService->sendEbookToClient(
                $data['email'],
                $data['name']
            );
            
            $result['email_success'] = $emailSent;
            
            if (!$emailSent) {
                $result['errors'][] = "L'envoi de l'email a échoué.";
            }
            
            // 4. Statistiques et notification admin
            $stats = $this->ebookSubscriberRepository->getStats();
            $result['stats'] = $stats;
            
            $adminEmailSent = $this->emailService->sendEbookAdminNotification(
                $data,
                $dbResult['success'],
                $stats
            );
            
            $result['admin_email_success'] = $adminEmailSent;
            
            // 5. Résultat global
            $result['success'] = $emailSent; // Considéré réussi si l'email a été envoyé
            $result['message'] = $emailSent
                ? 'Ebook envoyé avec succès'
                : 'Échec de l\'envoi de l\'ebook';
                
        } catch (EbookException $e) {
            $result['errors'][] = $e->getMessage();
            $this->logger->error('Erreur de traitement d\'ebook', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
        
        return $result;
    }

    /**
     * Valide les données du formulaire
     *
     * @param array $data Données à valider
     * @throws EbookException Si les données sont invalides
     * @return bool Toujours true (exception sinon)
     */
    private function validateEbookData(array $data): bool
    {
        $errors = [];

        // Vérifier le nom
        if (empty($data['name'])) {
            $errors[] = "Le nom est requis";
        } elseif (strlen($data['name']) < 2) {
            $errors[] = "Le nom doit contenir au moins 2 caractères";
        }

        // Vérifier l'email
        if (empty($data['email'])) {
            $errors[] = "L'email est requis";
        } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = "L'email n'est pas valide";
        }

        // Vérifier le consentement
        if (!isset($data['consent']) || !$data['consent']) {
            $errors[] = "Vous devez accepter les conditions";
        }

        if (!empty($errors)) {
            throw new EbookException(implode(', ', $errors));
        }

        return true;
    }

    /**
     * Enregistre un abonné à l'ebook en base de données
     *
     * @param array $data Données de l'abonné
     * @return array{success: bool, message: string, id: int|null, is_update: bool} Résultat de l'opération
     */
    private function saveEbookSubscriber(array $data): array
    {
        $result = [
            'success' => false,
            'message' => '',
            'id' => null,
            'is_update' => false
        ];

        try {
            // Vérifier si l'email existe déjà
            $existingSubscriber = $this->ebookSubscriberRepository->findOneByEmail($data['email']);
            
            if ($existingSubscriber === null) {
                // Création d'un nouvel abonné
                $subscriber = new EbookSubscriber();
                $subscriber->setName($data['name']);
                $subscriber->setEmail($data['email']);
                $subscriber->setIpAddress($_SERVER['REMOTE_ADDR'] ?? null);
                $subscriber->setConsent(isset($data['consent']) && $data['consent'] ? true : false);
                $subscriber->setMailList(isset($data['consent']) && $data['consent'] ? true : false);
                
                $this->ebookSubscriberRepository->save($subscriber);
                
                $result['id'] = $subscriber->getId();
                $result['success'] = true;
                $result['message'] = "Abonné enregistré avec l'ID: " . $result['id'];
                
            } else {
                // Mise à jour d'un abonné existant
                $existingSubscriber->setName($data['name']);
                $existingSubscriber->setDownloadDate(new \DateTime());
                $existingSubscriber->setIpAddress($_SERVER['REMOTE_ADDR'] ?? null);
                
                $this->ebookSubscriberRepository->save($existingSubscriber);
                
                $result['id'] = $existingSubscriber->getId();
                $result['success'] = true;
                $result['message'] = "Abonné existant mis à jour";
                $result['is_update'] = true;
            }
            
        } catch (\Exception $e) {
            $result['message'] = "Erreur de base de données: " . $e->getMessage();
            $this->logger->error('Erreur lors de l\'enregistrement de l\'abonné ebook', [
                'error' => $e->getMessage(),
                'data' => $data
            ]);
        }

        return $result;
    }

    /**
     * Récupère les statistiques sur les ebooks
     *
     * @return array Statistiques
     */
    public function getEbookStats(): array
    {
        return $this->ebookSubscriberRepository->getStats();
    }
}