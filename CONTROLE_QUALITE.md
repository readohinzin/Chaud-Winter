# Contrôle qualité (étape 9)

Testé le 13 octobre 2026 sur un **vrai WordPress 7.1 avec WooCommerce 11.2**, thème Chaud Winter 0.6.1, avec les 54 produits importés.

## Résultats

| Contrôle | Résultat |
|---|---|
| Accessibilité (outil axe-core, 8 pages, téléphone et ordinateur) | **0 problème** |
| Accessibilité (Lighthouse) | **100/100** sur toutes les pages |
| Bonnes pratiques (Lighthouse) | **100/100** |
| Référencement (Lighthouse) | **100/100** (le panier est à 58 volontairement : il ne doit pas apparaître dans Google) |
| Débordement horizontal | **aucun**, de 320 à 768 px de large |
| Zones à toucher | **44 px minimum** partout |
| Commande complète par virement | **OK** (panier, commande, confirmation, 3 e-mails) |
| Erreurs JavaScript | **aucune** |
| Erreurs PHP | **aucune** |

## Vitesse

Mesurée sur un téléphone simulé avec une connexion lente, **sur une machine de test sans compression ni cache**.

| Page | Note | Premier affichage | Poids |
|---|---|---|---|
| Accueil | 81 | 2,7 s | 545 Ko |
| Catégorie | 83 | 2,9 s | 539 Ko |
| Fiche produit | 78 | 2,9 s | 557 Ko |
| Contact | 95 | 1,7 s | 320 Ko |

Sur Hostinger, avec la compression et le cache de **LiteSpeed Cache** (plugin déjà prévu), ces chiffres seront meilleurs. Réglages recommandés : activer le cache, la minification et la combinaison du CSS et du JavaScript, la conversion des images en WebP. **Ne pas** mettre en cache le panier, la commande et le compte client (le plugin le fait par défaut avec WooCommerce).

## Corrigé pendant ce contrôle

- **Cookies de traçage** : WooCommerce déposait des cookies `sbjs_*` (suivi de l'origine des visiteurs) sans demander l'accord, ce qui n'est pas conforme en France. Ils sont désactivés dans le thème. Il ne reste que les 3 cookies du panier (nécessaires). La page « Cookies » est ainsi exacte.
- **Accessibilité** : nom des boutons « Ajouter au panier », repère de page pour la barre du haut, ordre des titres sur la commande, lien du code promo, contrastes.
- **Téléphone** : débordement de 4 px à 320 px de large, zones à toucher trop petites (logo, pied de page, titres des cartes).
- **Vitesse** : 22 requêtes au lieu de 32 sur l'accueil, 58 Ko de styles au lieu de 177, jQuery en chargement différé, plus d'emojis ni de commentaires, polices dans la feuille principale.
- **Référencement** : description de chaque page pour Google (accueil, catégories, produits, pages).
- **Avis publics** sur les fiches produits désactivés (ils demandent une modération et des mentions légales). Les vrais avis se saisissent dans Apparence > Personnaliser.

## À faire sur le vrai site (non testable ici)

1. **Faire une sauvegarde**, puis installer le thème et les plugins (voir `INSTALLATION.md`).
2. **Tester l'envoi réel des e-mails** : passer une commande test avec sa propre adresse et vérifier que l'e-mail arrive dans la boîte de réception, pas dans les indésirables (WP Mail SMTP).
3. **Tester sur un vrai iPhone** : l'aperçu est fait avec un iPhone simulé, la sensation au toucher et le clavier ne se simulent pas.
4. **Mesurer à nouveau la vitesse** une fois LiteSpeed Cache réglé (PageSpeed Insights : pagespeed.web.dev).
5. **Titre du site** : Réglages > Général, mettre « Chaud Winter ».
6. **Ouvrir la boutique** : WooCommerce > Réglages > Visibilité du site > « En ligne » (voir `REGLAGES_BOUTIQUE.md`), seulement quand tous les repères entre crochets ont disparu (voir `A_COMPLETER.md`).
7. **Bandeau cookies** : le site n'utilise que des cookies nécessaires, donc aucun bandeau n'est obligatoire aujourd'hui. Installer Complianz et mettre à jour la page « Cookies » le jour où un outil de statistiques, une vidéo ou une publicité est ajouté.
