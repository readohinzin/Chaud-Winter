#!/bin/sh
# Construit le zip du thème à installer dans WordPress (Apparence > Thèmes > Ajouter > Téléverser).
set -e
cd "$(dirname "$0")/.."
python3 outils/generer-pot.py theme/chaud-winter > theme/chaud-winter/languages/chaud-winter.pot
mkdir -p livraison
rm -f livraison/chaud-winter.zip
(cd theme && zip -qr -X ../livraison/chaud-winter.zip chaud-winter -x '*.DS_Store')
echo "Zip prêt : livraison/chaud-winter.zip"
