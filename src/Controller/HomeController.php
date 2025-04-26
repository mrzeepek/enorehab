<?php

declare(strict_types=1);

namespace App\Controller;

use App\Form\BilanType;
use App\Form\EbookType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    /**
     * Page d'accueil
     */
    #[Route('/', name: 'app_home')]
    public function index(Request $request): Response
    {
        // Formulaire de demande de bilan
        $bilanForm = $this->createForm(BilanType::class, null, [
            'action' => $this->generateUrl('app_bilan_process')
        ]);

        // Formulaire de téléchargement d'ebook
        $ebookForm = $this->createForm(EbookType::class, null, [
            'action' => $this->generateUrl('app_ebook_process')
        ]);

        // Métadonnées pour la page
        $pageTitle = "Enorehab | Accompagnement en ligne pour athlètes CrossFit, Hyrox et haltérophiles";
        $pageDescription = "Diagnostic kiné expert 100% personnalisé pour continuer à performer malgré la douleur. Pour CrossFitters, athlètes Hyrox et haltérophiles.";
        $pageKeywords = "kiné, crossfit, hyrox, haltérophilie, blessure, douleur, bilan, online, visio";

        return $this->render('home/index.html.twig', [
            'bilanForm' => $bilanForm->createView(),
            'ebookForm' => $ebookForm->createView(),
            'pageTitle' => $pageTitle,
            'pageDescription' => $pageDescription,
            'pageKeywords' => $pageKeywords,
            'success' => $request->query->get('success'),
            'error' => $request->query->get('error'),
            'ebook_success' => $request->query->get('ebook_success'),
            'error_ebook' => $request->query->get('error_ebook')
        ]);
    }

    /**
     * Page Politique de confidentialité
     */
    #[Route('/privacy', name: 'app_privacy')]
    public function privacy(): Response
    {
        return $this->render('home/privacy.html.twig', [
            'pageTitle' => 'Politique de confidentialité | Enorehab',
            'pageDescription' => 'Politique de confidentialité et informations sur la protection des données chez Enorehab',
            'pageKeywords' => 'politique de confidentialité, RGPD, données personnelles, Enorehab'
        ]);
    }

    /**
     * Retrocompatibilité avec l'ancien URL de politique de confidentialité
     */
    #[Route('/privacy.php', name: 'app_privacy_legacy')]
    public function privacyLegacy(): Response
    {
        return $this->redirectToRoute('app_privacy', [], Response::HTTP_MOVED_PERMANENTLY);
    }
}