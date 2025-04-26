<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Bilan;
use App\Exception\BilanException;
use App\Repository\BilanRepository;
use Psr\Log\LoggerInterface;

class BilanService
{
    public function __construct(
        private readonly BilanRepository $bilanRepository,
        private readonly EmailService $emailService,
        private readonly LoggerInterface $logger
    ) {
    }

    /**
     * Traite une demande de bilan kiné
     *
     * @param array $data Données du formulaire
     * @return array Résultat du traitement avec statuts
     */
    public function processBilanRequest(array $data): array
    {
        $result = [
            'success' => false,
            'db_success' => false,
            'client_email_success' => false,
            'admin_email_success' => false,
            'errors' => [],
            'message' => ''
        ];

        try {
            // 1. Validation des données
            $this->validateBilanData($data);
            
            // 2. Enregistrement en BDD
            $dbResult = $this->saveBilanRequest($data);
            $result['db_success'] = $dbResult['success'];
            
            if (!$dbResult['success']) {
                $result['errors'][] = $dbResult['message'];
            }
            
            // 3. Email client
            $clientEmailSent = $this->emailService->sendBilanClientConfirmation(
                $data['email'],
                $data['name'],
                $data['phone'] ?? '',
                $data['instagram'] ?? ''
            );
            
            $result['client_email_success'] = $clientEmailSent;
            
            if (!$clientEmailSent) {
                $result['errors'][] = "L'envoi de l'email de confirmation a échoué.";
            }
            
            // 4. Email admin
            $adminEmailSent = $this->emailService->sendBilanAdminNotification(
                $data,
                $dbResult['success'],
                $dbResult['success'] ? '' : $dbResult['message']
            );
            
            $result['admin_email_success'] = $adminEmailSent;
            
            if (!$adminEmailSent) {
                $result['errors'][] = "L'envoi de la notification à l'administrateur a échoué.";
            }
            
            // 5. Résultat global
            $result['success'] = $clientEmailSent || $adminEmailSent || $dbResult['success']; // Au moins un succès
            $result['message'] = $result['success']
                ? 'Demande de bilan traitée avec succès'
                : 'Échec du traitement de la demande de bilan';
                
        } catch (BilanException $e) {
            $result['errors'][] = $e->getMessage();
            $this->logger->error('Erreur de traitement de bilan', [
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
     * @throws BilanException Si les données sont invalides
     * @return bool Toujours true (exception sinon)
     */
    private function validateBilanData(array $data): bool
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

        // Vérifier le téléphone (si fourni)
        if (!empty($data['phone'])) {
            $phonePattern = '/^[0-9+\s()-]{8,20}$/';
            if (!preg_match($phonePattern, $data['phone'])) {
                $errors[] = "Le format du numéro de téléphone n'est pas valide";
            }
        }

        if (!empty($errors)) {
            throw new BilanException(implode(', ', $errors));
        }

        return true;
    }

    /**
     * Enregistre une demande de bilan en base de données
     *
     * @param array $data Données de la demande
     * @return array{success: bool, message: string, id: int|null} Résultat de l'opération
     */
    private function saveBilanRequest(array $data): array
    {
        $result = [
            'success' => false,
            'message' => '',
            'id' => null
        ];

        try {
            $bilan = new Bilan();
            $bilan->setName($data['name']);
            $bilan->setEmail($data['email']);
            
            if (!empty($data['phone'])) {
                $bilan->setPhone($data['phone']);
            }
            
            if (!empty($data['instagram'])) {
                $bilan->setInstagram($data['instagram']);
            }
            
            $bilan->setIpAddress($_SERVER['REMOTE_ADDR'] ?? null);
            
            $this->bilanRepository->save($bilan);
            
            $result['id'] = $bilan->getId();
            $result['success'] = true;
            $result['message'] = "Demande enregistrée avec l'ID: " . $result['id'];
            
        } catch (\Exception $e) {
            $result['message'] = "Erreur de base de données: " . $e->getMessage();
            $this->logger->error('Erreur lors de l\'enregistrement du bilan', [
                'error' => $e->getMessage(),
                'data' => $data
            ]);
        }

        return $result;
    }

    /**
     * Récupère toutes les demandes de bilan
     *
     * @param string|null $status Filtrer par statut (optionnel)
     * @param int $limit Limite de résultats (optionnel)
     * @return array Liste des demandes
     */
    public function getAllBilanRequests(?string $status = null, int $limit = 100): array
    {
        return $this->bilanRepository->findByStatus($status, $limit);
    }

    /**
     * Met à jour le statut d'une demande de bilan
     *
     * @param int $id ID de la demande
     * @param string $status Nouveau statut
     * @param string|null $notes Notes (optionnel)
     * @return bool Succès ou échec
     */
    public function updateBilanStatus(int $id, string $status, ?string $notes = null): bool
    {
        return $this->bilanRepository->updateStatus($id, $status, $notes);
    }
}