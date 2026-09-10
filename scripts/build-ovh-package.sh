#!/usr/bin/env bash

set -euo pipefail

project_root="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
output_root="${project_root}/dist"
timestamp="$(date '+%Y%m%d-%H%M%S')"
package_name="lifers-ovh-${timestamp}"
package_path="${output_root}/${package_name}"
archive_path="${output_root}/${package_name}.zip"
temporary_root="$(mktemp -d "${TMPDIR:-/tmp}/lifers-ovh.XXXXXX")"
temporary_package="${temporary_root}/${package_name}"

cleanup() {
    rm -rf "${temporary_root}"
}

trap cleanup EXIT

for required_command in composer npm rsync zip; do
    if ! command -v "${required_command}" >/dev/null 2>&1; then
        echo "Commande manquante : ${required_command}" >&2
        exit 1
    fi
done

cd "${project_root}"

echo "Compilation des ressources de production…"
npm run build

mkdir -p "${temporary_package}" "${output_root}"

echo "Copie des seuls fichiers utiles au serveur…"
COPYFILE_DISABLE=1 rsync -a ./ "${temporary_package}/" \
    --exclude='.git/' \
    --exclude='.env' \
    --exclude='.env.backup' \
    --exclude='.env.example' \
    --exclude='.env.production' \
    --exclude='.editorconfig' \
    --exclude='.gitattributes' \
    --exclude='.gitignore' \
    --exclude='.nvmrc' \
    --exclude='.phpunit.cache' \
    --exclude='.phpunit.result.cache' \
    --exclude='.DS_Store' \
    --exclude='AGENTS.md' \
    --exclude='README.md' \
    --exclude='database/factories/' \
    --exclude='database/seeders/DemoSeeder.php' \
    --exclude='docs/' \
    --exclude='dist/' \
    --exclude='jsconfig.json' \
    --exclude='node_modules/' \
    --exclude='package-lock.json' \
    --exclude='package.json' \
    --exclude='phpunit.xml' \
    --exclude='postcss.config.js' \
    --exclude='public/hot' \
    --exclude='public/storage' \
    --exclude='resources/css/' \
    --exclude='resources/js/' \
    --exclude='scripts/' \
    --exclude='tailwind.config.js' \
    --exclude='tests/' \
    --exclude='tmp/' \
    --exclude='vite.config.js' \
    --exclude='vendor/'

echo "Installation des dépendances PHP de production…"
composer install \
    --working-dir="${temporary_package}" \
    --no-dev \
    --optimize-autoloader \
    --no-interaction \
    --prefer-dist

if find "${temporary_package}" -maxdepth 1 -name '.env' -print -quit | grep -q .; then
    echo "Arrêt : un fichier .env ne doit jamais entrer dans le paquet." >&2
    exit 1
fi

mv "${temporary_package}" "${package_path}"

echo "Création de l'archive de sauvegarde…"
(
    cd "${output_root}"
    COPYFILE_DISABLE=1 zip -qr "$(basename "${archive_path}")" "${package_name}"
)

echo
echo "Dossier SFTP prêt : ${package_path}"
echo "Archive locale       : ${archive_path}"
echo "Le fichier .env de production reste volontairement absent."
