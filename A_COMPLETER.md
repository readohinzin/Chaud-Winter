# Informations à compléter avant la mise en ligne

Tant qu'une information manque, le site affiche un repère entre crochets, par exemple `[TÉLÉPHONE]`.
**Aucun repère ne doit rester visible le jour du lancement.**

Coche chaque ligne quand l'information est fournie et saisie sur le site.

| | Repère | Ce qu'il faut | Où le saisir | Où il s'affiche |
|---|---|---|---|---|
| ☑ | ~~`[TÉLÉPHONE]`~~ | **+33 7 59 23 32 43** (reçu le 13/10/2026, intégré au thème, modifiable dans Apparence > Personnaliser > Chaud Winter : coordonnées) | | Barre du haut, bande de réassurance, pied de page, contact |
| ☑ | ~~`[HORAIRES]`~~ | **Commande possible tous les jours, livraison du lundi au samedi de 8h à 19h** (reçu le 13/10/2026). Ce ne sont pas les horaires de réponse au téléphone : à confirmer avec le client | Même endroit | Barre du haut, pied de page, contact, page Livraison |
| ☐ | `[EMAIL]` | Adresse e-mail de contact | Même endroit | Pied de page |
| ☑ | ~~`[ADRESSE]`~~ | **1650 route de Bouverat, 74130 Bonneville** (reçue le 13/10/2026, intégrée au thème et à l'adresse de la boutique WooCommerce) | Même endroit | Pied de page, contact, pages légales |
| ☑ | ~~`[SIRET]`~~ | **818 272 940 00029** (SIREN 818 272 940, reçu le 13/10/2026) | Apparence > Personnaliser > Chaud Winter : coordonnées | Pied de page, mentions légales, Impressum, CGV |
| ☑ | ~~`[N° TVA]`~~ | **FR62818272940** (reçu le 13/10/2026) | Même endroit | Pied de page, mentions légales, Impressum, CGV, factures |
| ☑ | ~~`[IBAN]`~~ | Titulaire, IBAN et BIC reçus le 13/10/2026 et enregistrés par le bouton de configuration. Le titulaire, JEAN PAUL VANCASSEL, est le chargé des ventes (confirmé par Céline). Reste à faire valider par le comptable que ce compte peut encaisser les ventes de MARTELIN Thierry Nom de la banque non communiqué (champ facultatif) | WooCommerce > Réglages > Paiements > Virement bancaire | Page de confirmation, e-mail de commande |
| ☐ | `[MÉDIATEUR]` | Nom et site du médiateur de la consommation | (pages légales, étape 6) | CGV, mentions légales |
| ☐ | `[DURÉE DE GARANTIE]` | Durée de garantie des appareils | Apparence > Personnaliser > Chaud Winter : coordonnées | Accueil (bande de réassurance), CGV, fiches produits |
| ☐ | `[TARIFS DE LIVRAISON]` | Tarifs du transporteur pour la **Belgique et l'Allemagne**, par classe de poids (**à saisir hors taxes**). La France métropolitaine est offerte : rien à saisir | WooCommerce > Réglages > Expédition (voir `REGLAGES_BOUTIQUE.md`) | Panier, commande, page Livraison |

### Repères ajoutés pour les pages légales (étape 6)

Ces informations n'étaient pas dans la liste de départ mais sont obligatoires dans les mentions légales, les CGV ou l'Impressum. Elles se remplacent directement dans le texte des pages (Pages > modifier la page).

| | Repère | Ce qu'il faut | Pages |
|---|---|---|---|
| ☑ | ~~`[RAISON SOCIALE]`~~ | **MARTELIN Thierry**, nom commercial **HotHiver** (reçu le 13/10/2026). Le site s'appelle désormais **HotHiver** (logo « Hot » + « Hiver » collés), comme le nom commercial déclaré. Le registre mentionne aussi « MARTELIN BOIS DE CHAUFFAGE » : à confirmer s'il doit apparaître | Mentions légales, CGV, Impressum, Confidentialité |
| ☑ | ~~`[FORME JURIDIQUE]`~~ | **Entrepreneur individuel** (reçu le 13/10/2026) | Mentions légales, CGV, Impressum |
| ☑ | ~~`[CAPITAL SOCIAL]`~~ | **Sans objet** : un entrepreneur individuel n'a pas de capital social, la mention a été retirée des textes | Mentions légales, CGV |
| ☑ | ~~`[VILLE DU RCS]`~~ | **Annecy**, n° 818 272 940 (inscrit le 08/02/2016) | Mentions légales, CGV, Impressum |
| ☑ | ~~`[REPRÉSENTANT LÉGAL]`~~ | **MARTELIN Thierry** (l'exploitant) | Mentions légales, Impressum |
| ☐ | `[DÉLAI DE PAIEMENT]` | Nombre de jours avant d'annuler une commande non payée | CGV, Paiement par virement |
| ☑ | ~~`[DÉLAI DE LIVRAISON]`~~ | **Expédition sous 24 h après réception du virement, 2 à 3 jours ouvrés au total** pour les appareils en stock (reçu le 13/10/2026). À confirmer avec le client, surtout pour les produits hors stock | CGV, Livraison et retours, Questions fréquentes |
| ☑ | ~~`[CONDITIONS DE REMISE]`~~ | **Livraison devant le domicile**, sur rendez-vous avec le transporteur (reçu le 13/10/2026). Le texte de la page Livraison est rédigé avec nos mots. À confirmer avec le client : emballage sanglé, e-mail de prise en charge avec consignes de réception | CGV, Livraison et retours |
| ☐ | `[COÛT ESTIMÉ DU RETOUR]` | Coût maximal d'un retour par le transporteur (obligatoire pour les objets lourds) | CGV, Livraison et retours |
| ☐ | `[ADRESSE DU DÉPÔT]` | Adresse du dépôt où l'on peut retirer sa commande (retrait gratuit sur rendez-vous). Est-ce la même que 1650 route de Bouverat ? | Questions fréquentes |
| ☐ | `[DATE DE MISE EN LIGNE]` | Date de la version des CGV | CGV |
| ☐ | `[À VÉRIFIER : …]` | Pays des serveurs de l'hébergeur et du service d'e-mails | Politique de confidentialité |

Pour ces informations d'identité, les textes des pages sont remplis automatiquement à la création des pages (bouton de configuration). Une page déjà publiée n'est jamais écrasée.

Un rappel orange dans le tableau de bord liste les pages qui contiennent encore des repères.

## Catalogue (après l'import de `produits.csv`)

| | À faire | Où |
|---|---|---|
| ☐ | **Remplacer les prix d'exemple** par les vrais prix (ceux du fichier viennent d'autres sites) | Fichier CSV puis réimport, ou fiche de chaque produit |
| ☐ | Ranger une **copie écrite des autorisations** d'utiliser les photos, et compléter `[QUI]` et `[DATE]` dans CLAUDE.md | Dossier `autorisations/` |
| ☐ | Photos des produits ajoutés plus tard (fournisseurs) | Fiche produit > Image du produit |
| ☐ | Poids et classe de livraison de chaque appareil | Fiche produit > Expédition |
| ☐ | Fiches techniques des fournisseurs : poids, dimensions, rendement, réservoir, sortie des fumées (les descriptions et caractéristiques de base sont déjà rédigées) | Fiche produit > Caractéristiques |
| ☐ | Reste du catalogue des fournisseurs | Nouvelles lignes dans le CSV |

Voir `IMPORT_PRODUITS.md`.

## Contenus facultatifs de l'accueil

Ces blocs restent **cachés** tant qu'ils ne sont pas remplis : rien de faux ne s'affiche.

| | Bloc | Comment le remplir |
|---|---|---|
| ☐ | Bouton « Écrire sur WhatsApp » | Numéro WhatsApp dans Apparence > Personnaliser > Chaud Winter : coordonnées |
| ☐ | Avis clients (jusqu'à 3) | **Vrais** avis uniquement, avec l'accord des clients : Apparence > Personnaliser > Chaud Winter : avis clients |
| ☐ | Guides | Écrire des articles dans la catégorie « Guides » (Articles > Ajouter). Les 3 derniers s'affichent |
| ☐ | Produit « À la une » (carte dans l'illustration du haut) | Produits > Tous les produits : cliquer sur l'étoile d'un produit |

Les marques s'affichent toutes seules dès que les produits ont une marque (étape 7).

## À faire valider par un professionnel

- **Coefficients de l'assistant de puissance** (surface × 0,09 / 0,115 / 0,14) : à faire valider par un chauffagiste.
- **Textes légaux** (CGV, mentions légales, confidentialité, cookies, Impressum, libellé du bouton de commande pour l'Allemagne) : à faire relire par le client ou son comptable.
- **Toutes les pages légales** (CGV, mentions légales, confidentialité, cookies, livraison et retours, Impressum) : textes de base écrits sans juriste, à faire relire. En particulier :
  - l'encadré sur la **garantie légale de conformité** dans les CGV : son texte est imposé par décret, à comparer mot pour mot avec le texte officiel ;
  - l'adresse de l'**hébergeur** Hostinger dans les mentions légales, à vérifier sur le contrat ;
  - l'**Impressum**, rédigé en allemand.
- **Médiateur de la consommation** : il faut adhérer à un médiateur agréé (c'est en général payant, quelques dizaines d'euros par an) pour pouvoir remplir `[MÉDIATEUR]`.
- **Libellé du bouton de commande** : « Commander avec obligation de paiement » (obligatoire en France et en Allemagne).
- **Classes de poids de livraison** (moins de 100 kg, 100 à 200 kg, plus de 200 kg) : à adapter à la grille du transporteur.
- **TVA par pays de livraison** : 20 % partout par défaut, à changer seulement après accord écrit du comptable.

Ce fichier sera complété à chaque étape.
