# Génère produits.csv (import WooCommerce) à partir du tableau « Produits » de CLAUDE.md.
# Usage : python3 outils/generer-csv.py
#
# Colonnes au format de l'importateur WooCommerce (Produits > Importer), en anglais :
# WooCommerce les reconnaît alors toutes seules.
import csv
import pathlib
import re
import unicodedata

RACINE = pathlib.Path(__file__).resolve().parent.parent
SOURCE = RACINE / 'CLAUDE.md'
SORTIE = RACINE / 'produits.csv'
CATEGORIES = ('Poêles à granulés', 'Poêles à bois', 'Chaudières')


def sku(marque, modele):
    """Référence unique, par exemple ETNA-LUXE-CAYENNE-14 : sert à mettre à jour les produits plus tard."""
    texte = unicodedata.normalize('NFKD', f'{marque} {modele}').encode('ascii', 'ignore').decode()
    return re.sub(r'[^A-Z0-9]+', '-', texte.upper()).strip('-')


def lire_tableau():
    produits = []
    for ligne in SOURCE.read_text(encoding='utf-8').splitlines():
        cases = [c.strip() for c in ligne.strip().strip('|').split('|')]
        if len(cases) == 6 and cases[0] in CATEGORIES:
            categorie, marque, modele, description, kw, prix = cases
            produits.append({
                'categorie': categorie,
                'marque': marque,
                'modele': modele,
                'description': description,
                'kw': kw,                      # « 11,6 »
                'prix': prix.replace(',', '.'),  # « 1023.20 »
            })
    return produits


def main():
    produits = lire_tableau()
    entetes = [
        'Type', 'SKU', 'Name', 'Published', 'Is featured?', 'Visibility in catalog',
        'Short description', 'Description', 'Tax status', 'Tax class', 'In stock?',
        'Backorders allowed?', 'Weight (kg)', 'Shipping class', 'Regular price',
        'Categories', 'Brands', 'Images', 'Position',
        'Attribute 1 name', 'Attribute 1 value(s)', 'Attribute 1 visible', 'Attribute 1 global',
        'Attribute 2 name', 'Attribute 2 value(s)', 'Attribute 2 visible', 'Attribute 2 global',
    ]
    position = {}
    with SORTIE.open('w', encoding='utf-8', newline='') as f:
        w = csv.writer(f, quoting=csv.QUOTE_MINIMAL)
        w.writerow(entetes)
        for p in produits:
            position[p['categorie']] = position.get(p['categorie'], 0) + 1
            w.writerow([
                'simple', sku(p['marque'], p['modele']), p['modele'], 1, 0, 'visible',
                p['description'], '', 'taxable', '', 1,
                0, '', '', p['prix'],
                p['categorie'], p['marque'], '', position[p['categorie']],
                # WooCommerce sépare les valeurs d'attribut par des virgules : « 11\,6 kW » garde la virgule.
                'Puissance', p['kw'].replace(',', '\\,') + ' kW', 1, 1,
                'Marque', p['marque'], 1, 1,
            ])
    print(f'{len(produits)} produits écrits dans {SORTIE.name} :',
          ', '.join(f'{c} {n}' for c, n in position.items()))


if __name__ == '__main__':
    main()
