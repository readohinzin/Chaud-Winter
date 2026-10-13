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
PHOTOS = RACINE / 'photos'
# Adresse publique des photos (dossier photos/ du dépôt GitHub) : l'importateur WooCommerce les télécharge.
URL_PHOTOS = 'https://raw.githubusercontent.com/readohinzin/Chaud-Winter/claude/clever-dirac-dswt2o/photos/'
CATEGORIES = ('Poêles à granulés', 'Poêles à bois', 'Chaudières')


def sku(marque, modele):
    """Référence unique, par exemple ETNA-LUXE-CAYENNE-14 : sert à mettre à jour les produits plus tard."""
    texte = unicodedata.normalize('NFKD', f'{marque} {modele}').encode('ascii', 'ignore').decode()
    return re.sub(r'[^A-Z0-9]+', '-', texte.upper()).strip('-')


# --------------------------------------------------------------------------
# Descriptions et caractéristiques, rédigées pour Chaud Winter à partir des
# informations sûres du tableau (type, puissance, particularités). Aucun chiffre
# inventé : poids, dimensions et rendement viendront des fiches des fournisseurs.
# --------------------------------------------------------------------------
TYPES = {
    'Poêles à granulés': ('Poêle à granulés', 'Granulés de bois'),
    'Poêles à bois': ('Poêle à bois', 'Bûches de bois'),
}

# Particularités : (mot repéré, caractéristique, valeur, explication pour l'acheteur)
PARTICULARITES = [
    ('étanche', 'Étanche', 'Oui',
     "<strong>Étanche</strong> : il prend l'air de combustion directement dehors, par un conduit dédié. "
     "Il n'utilise donc pas l'air de la pièce, ce qui convient bien aux maisons bien isolées et aux logements équipés d'une VMC."),
    ('canalisable', 'Canalisable', 'Oui',
     "<strong>Canalisable</strong> : une partie de l'air chaud peut être envoyée par des gaines vers une ou plusieurs pièces voisines."),
    ('ventilé', 'Diffusion de la chaleur', 'Ventilée',
     "<strong>Ventilé</strong> : un ventilateur diffuse rapidement la chaleur dans la pièce."),
    ('extra plat', 'Format', 'Extra plat',
     "<strong>Extra plat</strong> : sa faible profondeur lui permet de se loger dans un couloir ou une pièce où la place est comptée."),
    ('concentrique', 'Sortie des fumées', 'Par le dessus, concentrique',
     "<strong>Sortie concentrique par le dessus</strong> : un seul conduit évacue les fumées et amène l'air frais, ce qui simplifie l'installation."),
    ('3 vitres', 'Vitres', '3 (face et côtés)',
     "<strong>Trois vitres</strong> : la flamme se voit de face et sur les côtés."),
    ('2 vitres', 'Vitres', '2',
     "<strong>Deux vitres</strong> : une vue plus large sur la flamme."),
    ('fonte', 'Matériau', 'Fonte',
     "<strong>En fonte</strong> : la fonte accumule la chaleur et la restitue en douceur, même après l'extinction du feu."),
    ('bûcher', 'Rangement des bûches', 'Bûcher intégré',
     "<strong>Bûcher intégré</strong> : un espace sous le foyer pour garder les bûches à portée de main."),
    ('range-bûches', 'Rangement des bûches', 'Range-bûches, module 3 étages',
     "<strong>Range-bûches</strong> : un module de rangement à trois étages, pour les bûches ou les accessoires."),
    ('four', 'Cuisson', 'Four et plaque de cuisson',
     "<strong>Four et plaque de cuisson</strong> : il chauffe la maison et permet aussi de cuisiner."),
    ('chauffe-plat', 'Cuisson', 'Chauffe-plat sur le dessus',
     "<strong>Chauffe-plat</strong> : le dessus permet de garder un plat au chaud."),
]


def type_et_combustible(p):
    if p['categorie'] in TYPES:
        return TYPES[p['categorie']]
    if 'bûches' in p['description'] or 'bois' in p['description'].lower():
        return ('Chaudière à bois', 'Bûches de bois')
    return ('Chaudière à granulés', 'Granulés de bois')


def caracteristiques(p):
    """Liste ordonnée (nom, valeur) : seulement ce qui est certain."""
    type_, combustible = type_et_combustible(p)
    liste = [('Type d\'appareil', type_), ('Combustible', combustible)]
    desc = p['description'].lower()
    vus = set()
    for mot, nom, valeur, _ in PARTICULARITES:
        if mot in desc and nom not in vus:
            liste.append((nom, valeur))
            vus.add(nom)
    return liste


def de(marque):
    """« de Firematic », mais « d'ETNA », « d'Extraflame »."""
    return ("d'" if marque[0].upper() in 'AEIOUÉ' else 'de ') + marque


def description(p):
    """Description longue, en HTML simple."""
    type_, combustible = type_et_combustible(p)
    kw = p['kw'] + ' kW'
    paragraphes = []
    if type_ == 'Poêle à granulés':
        paragraphes.append(
            f"Le <strong>{p['modele']}</strong> {de(p['marque'])} est un poêle à granulés d'une puissance de {kw}. "
            "Les granulés se chargent dans un réservoir et le poêle dose lui-même le combustible : "
            "une chaleur régulière, avec peu de manipulations.")
    elif type_ == 'Poêle à bois':
        paragraphes.append(
            f"Le <strong>{p['modele']}</strong> {de(p['marque'])} est un poêle à bûches d'une puissance de {kw}. "
            "Le plaisir d'une vraie flamme, avec un combustible simple à trouver.")
    elif type_ == 'Chaudière à granulés':
        paragraphes.append(
            f"La <strong>{p['modele']}</strong> {de(p['marque'])} est une chaudière à granulés d'une puissance de {kw}. "
            "Reliée au circuit de chauffage central, elle alimente les radiateurs ou le plancher chauffant et, "
            "selon l'installation, peut aussi produire l'eau chaude. Elle s'installe en chaufferie, au garage ou dans un cellier.")
    else:
        paragraphes.append(
            f"La <strong>{p['modele']}</strong> {de(p['marque'])} est une chaudière à bûches d'une puissance de {kw}. "
            "Reliée au circuit de chauffage central, elle alimente les radiateurs ou le plancher chauffant "
            "avec un combustible économique et local.")
    desc = p['description'].lower()
    points = []
    vus = set()
    for mot, nom, _, texte in PARTICULARITES:
        if mot in desc and nom not in vus:
            points.append(texte)
            vus.add(nom)
    if points:
        paragraphes.append('<ul>' + ''.join(f'<li>{t}</li>' for t in points) + '</ul>')
    paragraphes.append(
        "<strong>La bonne puissance</strong> dépend de la surface, de l'isolation et de la région. "
        "Faites une estimation avec notre assistant de choix, ou demandez à être rappelé : un conseiller vous oriente.")
    paragraphes.append(
        "<strong>Installation</strong> : à confier à un professionnel qualifié, dans le respect des règles en vigueur. "
        "La garantie du fabricant peut en dépendre.")
    return ''.join(p_ if p_.startswith('<ul>') else f'<p>{p_}</p>' for p_ in paragraphes)


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
    NB_CARAC = max(len(caracteristiques(p)) for p in produits)
    for i in range(3, 3 + NB_CARAC):
        entetes += [f'Attribute {i} name', f'Attribute {i} value(s)', f'Attribute {i} visible', f'Attribute {i} global']
    position = {}
    with SORTIE.open('w', encoding='utf-8', newline='') as f:
        w = csv.writer(f, quoting=csv.QUOTE_MINIMAL)
        w.writerow(entetes)
        for p in produits:
            position[p['categorie']] = position.get(p['categorie'], 0) + 1
            ref = sku(p['marque'], p['modele'])
            image = URL_PHOTOS + ref + '.jpg' if (PHOTOS / (ref + '.jpg')).exists() else ''
            w.writerow([
                'simple', ref, p['modele'], 1, 0, 'visible',
                p['description'], description(p), 'taxable', '', 1,
                0, '', '', p['prix'],
                p['categorie'], p['marque'], image, position[p['categorie']],
                # WooCommerce sépare les valeurs d'attribut par des virgules : « 11\,6 kW » garde la virgule.
                'Puissance', p['kw'].replace(',', '\\,') + ' kW', 1, 1,
                'Marque', p['marque'], 1, 1,
            ] + sum(
                # Type et combustible : caractéristiques communes (utiles pour des filtres plus tard).
                ([nom, valeur.replace(',', '\\,'), 1, 1 if nom in ('Type d\'appareil', 'Combustible') else 0]
                 for nom, valeur in caracteristiques(p)), []
            ) + [''] * 4 * (NB_CARAC - len(caracteristiques(p))))
    print(f'{len(produits)} produits écrits dans {SORTIE.name} :',
          ', '.join(f'{c} {n}' for c, n in position.items()))


if __name__ == '__main__':
    main()
