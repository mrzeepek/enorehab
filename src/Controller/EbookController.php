<?php

declare(strict_types=1);

namespace App\Controller;

use App\Form\EbookType;
use App\Service\EbookService;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class EbookController extends AbstractController
{
    public function __construct(
        private readonly EbookService $ebookService,
        private readonly LoggerInterface $logger
    ) {
    }

    /**
     * Traitement du formulaire de téléchargement d'ebook
     * Route compatible avec l'ancienne URL pour préserver les liens existants
     */
    #[Route('/process_ebook.php', name: 'app_ebook_process', methods: ['POST'])]
    public function processEbook(Request $request): Response
    {
        // Log l'accès à cette page
        $this->logger->info('Accès à process_ebook.php', [
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
                'ebook_success' => 'true'
            ]);
        }

        // 2. Traitement du formulaire
        $form = $this->createForm(EbookType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            
            // Convertir l'objet Entity en tableau pour le service
            $formData = [
                'name' => $data->getName(),
                'email' => $data->getEmail(),
                'consent' => $data->getConsent()
            ];

            // Traiter la demande via le service
            $result = $this->ebookService->processEbookRequest($formData);
            
            // Journaliser le résultat
            $this->logger->info('Résultat du traitement de téléchargement d\'ebook', [
                'email' => $formData['email'],
                'success' => $result['success'],
                'db_success' => $result['db_success'],
                'email_success' => $result['email_success']
            ]);

            if ($result['success']) {
                return $this->redirectToRoute('app_home', [
                    'ebook_success' => 'true'
                ]);
            }

            // En cas d'erreur, rediriger avec les messages d'erreur
            $errorParam = urlencode(implode('|', $result['errors']));
            return $this->redirectToRoute('app_home', [
                'error_ebook' => $errorParam
            ]);
        }

        // Si le formulaire n'est pas valide, rediriger avec une erreur générique
        return $this->redirectToRoute('app_home', [
            'error_ebook' => 'Une erreur inattendue est survenue'
        ]);
    }

    /**
     * Page spécifique pour télécharger l'ebook (route retrocompatible)
     */
    #[Route('/send_ebook.php', name: 'app_ebook_download_page')]
    public function ebookPage(Request $request): Response
    {
        // Log l'accès à cette page
        $this->logger->info('Accès à send_ebook.php', [
            'method' => $request->getMethod(),
            'ip' => $request->getClientIp()
        ]);

        if ($request->getMethod() !== 'POST') {
            $this->logger->warning('Tentative d\'accès direct à send_ebook.php', [
                'ip' => $request->getClientIp()
            ]);
        }

        // Formulaire de téléchargement d'ebook
        $form = $this->createForm(EbookType::class, null, [
            'action' => $this->generateUrl('app_ebook_process')
        ]);

        return $this->render('ebook/download.html.twig', [
            'form' => $form->createView(),
            'pageTitle' => 'Télécharger l\'ebook | Enorehab',
            'pageDescription' => 'Téléchargez gratuitement notre guide de mobilité pour les épaules destiné aux athlètes CrossFit, Hyrox et haltérophiles.'
        ]);
    }

    /**
     * Admin: Liste des téléchargements d'ebook
     * Note: Utilisation future avec gestion d'authentification admin
     */
    #[Route('/admin/ebook-subscribers', name: 'app_admin_ebook_subscribers')]
    public function listSubscribers(): Response
    {
        // Cette route nécessiterait une authentification en production
        
        // Obtenir les statistiques
        $stats = $this->ebookService->getEbookStats();
        
        return $this->render('ebook/admin_list.html.twig', [
            'stats' => $stats
        ]);
    }
}