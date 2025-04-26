#!/usr/bin/env bash
set -e

# Liste des répertoires à créer
dirs=(
  "assets/controllers"
  "assets/css"
  "assets/images"
  "assets/js"
  "assets/styles"
  "bin"
  "config/packages"
  "config/routes"
  "migrations"
  "public/assets"
  "public/build"
  "src/Controller"
  "src/Entity"
  "src/Exception"
  "src/Form"
  "src/Repository"
  "src/Service"
  "templates"
  "templates/bilan"
  "templates/ebook"
  "templates/emails"
  "templates/home"
  "tests"
  "translations"
  "var"
  "vendor"
)

echo "Création des répertoires..."
for d in "${dirs[@]}"; do
  if [ ! -d "$d" ]; then
    mkdir -p "$d"
    echo "  └── créé : $d"
  else
    echo "  └── existe : $d"
  fi
done

# Liste des fichiers à créer
files=(
  "assets/css/app.css"
  "assets/js/app.js"
  "assets/styles/app.scss"
  "config/packages/doctrine.yaml"
  "config/packages/framework.yaml"
  "config/packages/mailer.yaml"
  "config/packages/security.yaml"
  "config/packages/twig.yaml"
  "config/packages/webpack_encore.yaml"
  "config/routes/annotations.yaml"
  "config/routes/bilan.yaml"
  "config/routes.yaml"
  "config/services.yaml"
  "bin/console"
  "public/index.php"
  "src/Controller/BilanController.php"
  "src/Controller/EbookController.php"
  "src/Controller/HomeController.php"
  "src/Entity/Bilan.php"
  "src/Entity/EbookSubscriber.php"
  "src/Exception/BilanException.php"
  "src/Exception/EbookException.php"
  "src/Form/BilanType.php"
  "src/Form/EbookType.php"
  "src/Repository/BilanRepository.php"
  "src/Repository/EbookSubscriberRepository.php"
  "src/Service/BilanService.php"
  "src/Service/EmailService.php"
  "src/Service/EbookService.php"
  "templates/base.html.twig"
  "templates/bilan/form.html.twig"
  "templates/ebook/form.html.twig"
  "templates/emails/admin_bilan_notification.html.twig"
  "templates/emails/admin_ebook_notification.html.twig"
  "templates/emails/client_bilan_confirmation.html.twig"
  "templates/emails/ebook_template.html.twig"
  "templates/home/index.html.twig"
)

echo ""
echo "Création des fichiers…"
for f in "${files[@]}"; do
  if [ ! -e "$f" ]; then
    # on s'assure que le dossier parent existe
    mkdir -p "$(dirname "$f")"
    touch "$f"
    echo "  └── créé : $f"
  else
    echo "  └── existe : $f (skip)"
  fi
done

# Fichiers à la racine du projet
root_files=(
  ".env"
  ".env.local"
  "composer.json"
  "composer.lock"
  "package.json"
  "symfony.lock"
  "webpack.config.js"
)

echo ""
echo "Création des fichiers à la racine…"
for rf in "${root_files[@]}"; do
  if [ ! -e "$rf" ]; then
    touch "$rf"
    echo "  └── créé : $rf"
  else
    echo "  └── existe : $rf (skip)"
  fi
done

echo ""
echo "✅ Structure et fichiers en place (sans écraser l’existant)."
