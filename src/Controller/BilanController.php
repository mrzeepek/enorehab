<?php

declare(strict_types=1);

namespace App\Controller;

use App\Form\BilanType;
use App\Service\BilanService;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class BilanController extends AbstractController
{
    public function __construct(
        private readonly BilanService $bilanService,
        private readonly LoggerInterface $logger
    ) {
    }

    /**
     * Traitement du formulaire de demande de bilan
     * Route compatible avec l'ancienne URL pour préserver les liens existants
     */
    #[Route('/process_form.php', name: 'app_bilan_process', methods: ['POST'])]
    public function processBilan(Request $request): Response
    {
        // Log l'accès à cette page
        $this->logger->info('Accès à process_form.php', [
            'method' => $request->getMethod(),
            'ip' => $request->getClientIp()
        ]);

        // 1. Vérification du honeypot (protection anti-bot)
        if ($request->request->get('website')) {
            // C'est probablement un bot, rejeter silencieusement
            $this->logger->warning('Honeypot rempli - probable bot', [
                'ip' => $request->getClientIp()
            ]);

            // Simuler un succès pour ne pas alerter le bot
            return $this->redirectToRoute('app_home', [
                'success' => 'true',
                '_fragment' => 'booking'
            ]);
        }

        // 2. Traitement du formulaire
        $form = $this->createForm(BilanType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            
            // Convertir l'objet Entity en tableau pour le service
            $formData = [
                'name' => $data->getName(),
                'email' => $data->getEmail(),
                'phone' => $data->getPhone(),
                'instagram' => $data->getInstagram()
            ];

            // Traiter la demande via le service
            $result = $this->bilanService->processBilanRequest($formData);
            
            // Journaliser le résultat
            $this->logger->info('Résultat du traitement de demande de bilan', [
                'email' => $formData['email'],
                'success' => $result['success'],
                'db_success' => $result['db_success'],
                'client_email_success' => $result['client_email_success'],
                'admin_email_success' => $result['admin_email_success']
            ]);

            if ($result['success']) {
                return $this->redirectToRoute('app_home', [
                    'success' => 'true',
                    '_fragment' => 'booking'
                ]);
            }

            // En cas d'erreur, rediriger avec les messages d'erreur
            $errorParam = urlencode(implode('|', $result['errors']));
            return $this->redirectToRoute('app_home', [
                'error' => $errorParam,
                '_fragment' => 'booking'
            ]);
        }

        // Si le formulaire n'est pas valide, rediriger avec une erreur générique
        return $this->redirectToRoute('app_home', [
            'error' => 'Une erreur inattendue est survenue',
            '_fragment' => 'booking'
        ]);
    }

    /**
     * Admin: Liste des demandes de bilan
     * Note: Utilisation future avec gestion d'authentification admin
     */
    #[Route('/admin/bilans', name: 'app_admin_bilans')]
    public function listBilans(Request $request): Response
    {
        // Cette route nécessiterait une authentification en production
        
        $status = $request->query->get('status');
        $limit = $request->query->getInt('limit', 100);
        
        $bilans = $this->bilanService->getAllBilanRequests($status, $limit);
        
        return $this->render('bilan/admin_list.html.twig', [
            'bilans' => $bilans,
            'currentStatus' => $status
        ]);
    }

    /**
     * Admin: Mise à jour du statut d'une demande
     * Note: Utilisation future avec gestion d'authentification admin
     */
    #[Route('/admin/bilans/{id}/status', name: 'app_admin_bilan_status', methods: ['POST'])]
    public function updateStatus(int $id, Request $request): Response
    {
        // Cette route nécessiterait une authentification en production
        
        $status = $request->request->get('status');
        $notes = $request->request->get('notes');
        
        if (!$status) {
            $this->addFlash('error', 'Le statut est requis');
            return $this->redirectToRoute('app_admin_bilans');
        }
        
        $success = $this->bilanService->updateBilanStatus($id, $status, $notes);
        
        if ($success) {
            $this->addFlash('success', 'Statut mis à jour avec succès');
        } else {
            $this->addFlash('error', 'Erreur lors de la mise à jour du statut');
        }
        
        return $this->redirectToRoute('app_admin_bilans');
    }
}