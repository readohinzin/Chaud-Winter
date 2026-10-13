# Chaud Winter : boutique en ligne

## Le projet
Boutique en ligne pour **Chaud Winter**, vendeur de **poêles à granulés, poêles à bois et chaudières**. La maquette validée par le client est dans `maquette/chaud-winter-maquette.html` (version « ivoire », choisie par Céline) : c'est la référence pour le design et la structure. L'ancienne version gris-bleu (`#EDF1F4`) n'est plus la référence. Ouvre-la et suis-la.

## Qui travaille avec toi
Céline n'est pas développeuse. Elle travaille sur iPhone, en français. Explique simplement, en français, sans jargon. Pour toute décision importante (choix d'un plugin, structure, coût), propose d'abord et attends son accord. Si tu penses qu'elle se trompe, dis-le clairement.

## Contraintes du client
- **Entreprise française**, compte bancaire français.
- **Hébergeur : Hostinger**, offre Unlimited.
- **Vend en France, Belgique et Allemagne**, en **euros**. Prix affichés **TTC** aux particuliers.
- **Paiement par virement bancaire uniquement.** Pas de carte, pas de PayPal, pas de paiement en plusieurs fois.
- Site en **français** pour l'instant. L'allemand pourra venir plus tard : prépare le site pour la traduction (textes dans les fichiers de traduction de WordPress), sans la faire maintenant.

## Technique : WordPress + WooCommerce
Ne recode pas un panier ni un système de commande à la main. Utilise **WordPress avec WooCommerce**, qui gère déjà commandes, stock, e-mails et factures.
- Crée le design comme un **thème sur mesure** à partir de la maquette. Pour la compatibilité avec WooCommerce, préfère un **thème enfant d'un thème léger et gratuit** plutôt qu'un thème écrit entièrement à la main. Explique ton choix avant de commencer.
- Pas de constructeur de pages lourd. Pas de plugin inutile : chaque plugin doit être justifié.
- Le travail se livre sous forme de **dossier ou fichier zip à installer** dans WordPress. Ne modifie jamais le site en production sans sauvegarde.
- Les produits s'importent par **fichier CSV** (voir le tableau plus bas), pas un par un.

## Design (tiré de la maquette)
**Couleurs**
| Usage | Code |
|---|---|
| Fond principal (graphite) | `#101216` |
| Surface des cartes | `#181B21` |
| Lignes et bordures | `#23272F` |
| Texte principal | `#F2EEE9` |
| Texte secondaire | `#B4AEA6` et `#A9B2BD` |
| Accent orange braise (boutons) | `#F26B2A` |
| Bleu glacier | `#9CCBE0` |
| Sections claires (ivoire) | `#F6F2EC` |
| Cartes sur fond clair | `#FFFFFF`, bordure `#E4DDD3` |
| Texte secondaire sur fond clair | `#5F574E` et `#7A7066` |
| Texte sur fond clair | `#14171C` |
| Orange foncé sur fond clair | `#B8420F` |
| Bleu glacier foncé sur fond clair | `#2C7A9B` |

Texte sur un bouton orange : toujours `#101216` (jamais blanc, le contraste serait insuffisant).

**Polices (Google Fonts)**
- **Playfair Display** pour les titres. En **romain** par défaut. En **italique** seulement pour : la fin du titre principal de l'accueil, le mot « Winter » du logo, et le titre du bandeau de conseil.
- **Poppins** (400, 500, 600, 700) pour tout le texte courant.

**Logo** : une flamme noire dans un carré arrondi orange, avec un cœur bleu glacier. Le mot « Chaud » en blanc romain, « Winter » en bleu glacier italique. Symbole en SVG :
```html
<svg viewBox="0 0 24 24"><rect width="24" height="24" rx="6" fill="#F26B2A"/><path d="M12 3c1 3.4 4.8 5.4 4.8 9.6a4.8 4.8 0 1 1-9.6 0c0-1.8.8-3.2 1.9-4.3.3 1.4 1 2.1 1.9 2.4C10.6 8.1 10.4 5.3 12 3z" fill="#101216"/><ellipse cx="12" cy="16.4" rx="2.1" ry="2.7" fill="#9CCBE0"/></svg>
```

**Style** : premium et moderne, alternance de sections sombres et claires, **mobile d'abord** (la majorité des visiteurs seront sur téléphone), boutons d'au moins 44 px de haut, contrastes lisibles. Les étapes « Comment commander » sont des cartes sombres (`#181B21`, chiffre orange `#F26B2A`).

## Pages à construire
1. **Accueil** : barre téléphone, en-tête collant (flou, menu des 3 catégories, panier, bouton « Être rappelé »), hero avec illustration animée, bande de réassurance, 3 univers, assistant de choix de puissance, boutique avec onglets, étapes « Comment commander », marques, bandeau de conseil, avis, guides, pied de page. Voir la section « Comportements de l'accueil ».
2. **Pages catégories** : poêles à granulés, poêles à bois, chaudières. Grille de produits, filtre par puissance (kW) et par prix.
3. **Fiche produit** : photos, marque, puissance, description, prix TTC, bouton « Ajouter au panier ».
4. **Panier et commande** : paiement par virement.
5. **Contact** : formulaire, téléphone, adresse.
6. **Pages légales** : conditions générales de vente, mentions légales, politique de confidentialité, cookies, livraison et retours.

## Comportements de l'accueil (à reproduire depuis la maquette)
- **En-tête collant** à fond semi-transparent flou. Sur téléphone : logo, panier et bouton menu sur une seule ligne.
- **Hero** : grande illustration d'un poêle allumé, flamme qui vacille légèrement, quelques flocons. Ces animations doivent s'arrêter si le visiteur a activé « réduire les animations » (`prefers-reduced-motion`).
- **Assistant de choix de puissance** : curseur de surface (30 à 300 m²) et 3 niveaux d'isolation. Calcul indicatif : surface × 0,09 (bien isolée), × 0,115 (isolation moyenne), × 0,14 (peu isolée), avec une fourchette de plus ou moins 20 %. Le bouton filtre la boutique sur cette fourchette. Ces coefficients sont des ordres de grandeur : **à faire valider par un professionnel du chauffage avant la mise en ligne**, et garder la mention « estimation indicative ».
- **Boutique** : un onglet par catégorie avec le nombre de produits. Seuls les 8 premiers modèles s'affichent, un bouton montre les autres. Cartes : photo, puissance en pastille, marque, nom, description courte, prix TTC, bouton « Ajouter au panier ».
- **Étapes « Comment commander »** : 1) choisir, 2) commander et recevoir les coordonnées bancaires, 3) régler par virement puis expédition.
- **Apparition au défilement** : les sections apparaissent en douceur, sans bloquer le contenu si le JavaScript est désactivé.
- Les illustrations de poêle, de chaudière et de flamme sont des SVG faits pour la maquette : on peut les garder pour le hero et les 3 univers. Pour les produits, utiliser les vraies photos du client.

## Paiement par virement (WooCommerce, passerelle intégrée « virement bancaire »)
- Après la commande, afficher l'IBAN sur la page de confirmation et dans l'e-mail au client.
- La commande reste **« en attente de paiement »** jusqu'à ce que le client du site (le vendeur) confirme avoir reçu le virement dans son tableau de bord.
- Écrire un texte clair pour l'acheteur : montant exact, référence de la commande à mettre dans le virement, délai de traitement.

## Livraison
Trois zones : France, Belgique, Allemagne. Ce sont des appareils lourds : prévoir des **classes d'expédition par poids** et un tarif par pays. Les tarifs réels viennent du transporteur du client (pas encore connus : laisser des valeurs modifiables).

## TVA
Taux normaux : France 20 %, Belgique 21 %, Allemagne 19 %. Par défaut, appliquer la **TVA française (20 %)** partout. Ne configure la TVA par pays de livraison qu'après confirmation écrite du comptable du client. Les prix sont TTC pour les particuliers.

## Légal (à faire relire par le client ou son comptable)
- Droit de rétractation de **14 jours** pour les particuliers, à indiquer dans les conditions de vente.
- **Médiateur de la consommation** à indiquer sur le site.
- Bandeau cookies conforme.
- Pour l'Allemagne : prévoir une page d'identification de l'entreprise (« Impressum ») et un bouton de commande au libellé conforme, à faire valider.
- Tu n'es pas juriste : écris des textes de base clairs et signale ce qui doit être validé.

## Informations encore manquantes (à ne pas inventer)
Remplis ces champs avec des repères entre crochets visibles, jamais avec de fausses valeurs : `[TÉLÉPHONE]`, `[EMAIL]`, `[ADRESSE]`, `[HORAIRES]`, `[SIRET]`, `[N° TVA]`, `[IBAN]`, `[MÉDIATEUR]`, `[DURÉE DE GARANTIE]`, `[TARIFS DE LIVRAISON]`. **Aucun repère entre crochets ne doit rester visible au lancement.** Fais-en une liste dans un fichier `A_COMPLETER.md`.

## Produits (catalogue de départ)
Les noms, puissances et photos viennent de **poelediscount.com** et **topchaleur.com**. Les **prix sont ceux de ces sites, en euros TTC, et servent seulement d'exemple.** Le client fixera les siens.

**Règles importantes**
- - **Photos** : autorisation d'utiliser les photos produits de topchaleur.com et poelediscount.com, donnée par [QUI] le [DATE]. Copie dans le dossier autorisations/. **Les textes de ces deux sites restent interdits.**
- Les produits s'importent par CSV WooCommerce : crée `produits.csv` avec les colonnes nom, catégorie, marque, puissance (kW), description courte, prix.

| Catégorie | Marque | Modèle | Description | kW | Prix exemple (€) |
|---|---|---|---|---|---|
| Poêles à granulés | Firematic | C12 | Ventilé étanche | 11,6 | 1023,20 |
| Poêles à granulés | Firematic | O9 | Oval étanche | 7,9 | 908,18 |
| Poêles à granulés | ETNA | Vulcano Silenzio UP | Étanche, sortie dessus concentrique | 9,2 | 1299 |
| Poêles à granulés | ETNA Luxe | Cayenne 14 | Poêle à granulés | 14 | 1899 |
| Poêles à granulés | ETNA Luxe | Cayenne 16 | Poêle à granulés | 16 | 1975 |
| Poêles à granulés | ETNA | Mini 5 | Extra plat | 5 | 749 |
| Poêles à granulés | ETNA | Mini 6,8 | Extra plat | 6,8 | 799 |
| Poêles à granulés | ETNA | Little | Poêle à granulés | 6 | 799 |
| Poêles à granulés | ETNA | Orcino | Extra plat | 7 | 1101 |
| Poêles à granulés | ETNA | Fango | Canalisable extra plat | 8 | 1199 |
| Poêles à granulés | ETNA | Vulcano Silenzio | Étanche | 9,2 | 949 |
| Poêles à granulés | ETNA Luxe | Ottimo Silenzio | Étanche | 9,2 | 999 |
| Poêles à granulés | ETNA | Ottimo Silenzio canalisable | Étanche canalisable | 9,2 | 1199 |
| Poêles à granulés | ETNA | Vertigo | Étanche | 9,2 | 1499 |
| Poêles à granulés | ETNA | Vertigo Up | Étanche, sortie concentrique dessus | 9,2 | 1699 |
| Poêles à granulés | ETNA Luxe | Cristallo Silenzio Up | Étanche, sortie dessus concentrique | 9,2 | 1599 |
| Poêles à granulés | ETNA | Ricanto | Canalisable extra plat | 10 | 1499 |
| Poêles à granulés | ETNA Luxe | Tendenza | Étanche et canalisable | 10 | 2099 |
| Poêles à granulés | ETNA Luxe | Anna | Étanche | 10 | 1799 |
| Poêles à granulés | ETNA | Vulcano EVO 12 | Étanche | 12 | 1199 |
| Poêles à granulés | ETNA | Vulcano EVO | Étanche canalisable | 12 | 1499 |
| Poêles à granulés | ETNA | Pinarello | Canalisable extra plat | 12 | 1999 |
| Poêles à granulés | ETNA Luxe | Anna canalisable | Étanche canalisable | 12 | 1999 |
| Poêles à granulés | ETNA Luxe | Cayenne canalisable | Canalisable | 16 | 2199 |
| Poêles à bois | Firematic | Calgary S | Raccordable 3 vitres avec bûcher | 12,5 | 1599 |
| Poêles à bois | Firematic | Calgary L | Raccordable 3 vitres avec bûcher | 13,5 | 2099 |
| Poêles à bois | Firematic | Ottawa | 2 vitres | 12,6 | 1499 |
| Poêles à bois | Firematic | Toronto S | Raccordable avec bûcher | 12,6 | 1499 |
| Poêles à bois | Firematic | Toronto S 65 | Raccordable avec bûcher | 12,6 | 1518,90 |
| Poêles à bois | Firematic | Chicago | Poêle à bois | 11,2 | 778,30 |
| Poêles à bois | Bronpi | Lira | Étanche en fonte | 5 | 779 |
| Poêles à bois | Bronpi | Loreto | 3 vitres raccordable | 10 | 992,16 |
| Poêles à bois | Bronpi | Sena Plus | Poêle à bois | 13 | 1035,22 |
| Poêles à bois | Bronpi | Etna | En fonte | 11,3 | 1533,17 |
| Poêles à bois | Bronpi | Orly | En fonte raccordable | 6 | 1104,48 |
| Poêles à bois | La Nordica | Jennifer.16 | Raccordable en fonte | 7,7 | 1650,60 |
| Poêles à bois | La Nordica | Isotta con cerchi | Avec chauffe-plat | 9,3 | 2184 |
| Poêles à bois | Thermorossi | Agorà | En fonte raccordable | 12 | 2761,20 |
| Poêles à bois | ETNA | Domino | Avec range-bûches, module 3 étages | 8,5 | 1498 |
| Poêles à bois | ETNA | Pramoda | Avec four et plaque de cuisson | 14 | 1399 |
| Poêles à bois | Smart Stove | Modula Square | Poêle à bois | 8,5 | 899 |
| Chaudières | ETNA | Tonnara | Chaudière à granulés | 16 | 2599 |
| Chaudières | ETNA | Folaca | Chaudière à granulés | 19 | 2699 |
| Chaudières | ETNA | Baliutra | Chaudière à granulés | 24 | 2699 |
| Chaudières | ETNA | Vinti-Legna | Chaudière à granulés | 27 | 3199 |
| Chaudières | ETNA | Fantea | Chaudière à granulés | 32 | 3499 |
| Chaudières | Extraflame | PK20 | Chaudière à granulés | 20 | 6379 |
| Chaudières | Extraflame | PK30 | Chaudière à granulés | 30 | 7396 |
| Chaudières | Ecoforest | Cantina Compact | Chaudière à granulés | 12 | 5099 |
| Chaudières | Ecoforest | Cantina Nova 24 | Chaudière à granulés | 24 | 7194 |
| Chaudières | Ecoforest | Cantina Nova 30 | Chaudière à granulés | 30 | 6666 |
| Chaudières | ETNA | Magma 20 | Chaudière à bois (bûches) | 20 | 1759 |
| Chaudières | ETNA | Magma 30 | Chaudière à bois (bûches) | 30 | 1899 |
| Chaudières | ETNA | Magma 40 | Chaudière à bois (bûches) | 40 | 2259 |

Seule la première page de la catégorie « Poêles à granulés » de topchaleur.com (sur 6) a été relevée. Le reste du catalogue sera fourni par les fournisseurs du client.

## Ordre de travail
1. Lire la maquette, proposer le choix du thème (voir « Technique ») et attendre l'accord.
2. Squelette du thème : couleurs, polices, en-tête, pied de page.
3. Page d'accueil.
4. Pages catégories et fiche produit.
5. Panier, commande et paiement par virement.
6. Contact et pages légales.
7. Import des produits par CSV.
8. **Test complet d'une commande par virement**, de l'ajout au panier jusqu'à l'e-mail reçu.
9. Contrôle sur téléphone, vitesse de chargement, accessibilité.

## Ce qu'on ne fait pas
- Pas de paiement par carte ni en plusieurs fois.
- Pas de version allemande tout de suite.
- Pas de valeurs inventées pour les informations manquantes.
