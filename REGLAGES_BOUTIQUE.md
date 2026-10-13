# Régler la boutique (panier, commande, virement, livraison)

À faire **une fois**, après avoir installé WooCommerce et le thème Chaud Winter.

> **Avant toute chose : une sauvegarde du site** (hPanel Hostinger > Sauvegardes, ou UpdraftPlus).

## 1. Le bouton qui fait presque tout

Tableau de bord WordPress > **WooCommerce > Configuration Chaud Winter**.

La page liste tout ce qui va changer. Tout est coché : clique sur **Appliquer les réglages cochés**. Un compte rendu s'affiche.

Ce que fait le bouton :

| Bloc | Réglages |
|---|---|
| Pays et monnaie | Boutique en France, vente en France, Belgique et Allemagne, euros, prix « 1 023,20 € », kg et cm |
| TVA | Prix saisis et affichés TTC, TVA française 20 % pour tout le monde |
| Paiement | Virement bancaire activé avec des textes clairs, **tous les autres moyens désactivés** |
| Livraison | 3 classes par poids, 3 zones (France, Belgique, Allemagne), tarifs à 0 € pour l'instant |
| Commande | Téléphone obligatoire, commande sans compte possible, pages Panier et Commande compatibles avec le thème |
| E-mails | Expéditeur « Chaud Winter », couleurs du site |

Rien n'est supprimé. Si une zone de livraison ou un taux de TVA existe déjà, il est gardé tel quel. On peut recliquer sans risque.

## 2. Ce qu'il faut compléter à la main

1. **IBAN, BIC et titulaire du compte** : WooCommerce > Réglages > Paiements > Virement bancaire. Remplacer `[IBAN]`, `[BIC]` et `[TITULAIRE DU COMPTE]`.
2. **Tarifs de livraison** : WooCommerce > Réglages > Expédition. Pour chaque pays, ouvrir « Livraison à domicile » :
   - enlever `[TARIFS DE LIVRAISON]` du titre ;
   - saisir le prix de chaque classe de poids.
   - ⚠️ **Les tarifs de livraison se saisissent HORS TAXES** : WooCommerce ajoute la TVA (100 € saisis = 120 € payés par le client).
   - Astuce : `[qty]` dans un prix le multiplie par le nombre d'appareils. Exemple : `90 * [qty]`.
3. **Adresse de la boutique** : WooCommerce > Réglages > Général.
4. **Classe de poids de chaque produit** : sur la fiche du produit, onglet Expédition. Pour le catalogue de départ, elle sera dans le fichier d'import (étape 7).

Tant que l'IBAN ou les tarifs manquent, un rappel orange s'affiche en haut du tableau de bord.

### Les classes de poids
- Moins de 100 kg
- 100 à 200 kg
- Plus de 200 kg

Ce découpage est une proposition : à adapter à la grille du transporteur (WooCommerce > Réglages > Expédition > Classes).

## 3. Au quotidien : confirmer un virement reçu

1. Le client commande. Il voit à l'écran, et reçoit par e-mail, **le montant exact, la référence (« Commande 1042 ») et l'IBAN**.
2. La commande apparaît dans WooCommerce > Commandes avec le statut **« En attente du virement »**. Le stock est déjà réservé.
3. Quand le virement arrive sur le compte : ouvrir la commande, passer le statut à **« En cours »**, puis **Mettre à jour**. Le client reçoit automatiquement un e-mail.
4. Une fois l'appareil expédié : passer le statut à **« Terminée »**.

Si le virement n'arrive jamais : passer le statut à « Annulée ». Le stock est remis en vente.

> **Pourquoi « En attente du virement » et pas « En attente de paiement » ?**
> WooCommerce a déjà un statut « En attente de paiement », mais il sert aux paniers abandonnés : il n'envoie pas d'e-mail au client et annule la commande au bout de quelques heures. Le bon statut pour un virement s'appelle « En attente » dans WooCommerce. Je l'ai renommé « En attente du virement » pour que ce soit clair.

## 4. À installer dans cette étape (déjà validé)

- **WP Mail SMTP** : pour que l'e-mail avec l'IBAN arrive bien (et pas dans les indésirables). Le régler avec l'adresse e-mail de la boutique créée chez Hostinger (hPanel > E-mails).
- **PDF Invoices & Packing Slips for WooCommerce** : factures PDF. Dans ses réglages, remplir le nom, l'adresse, le SIRET et le n° de TVA de l'entreprise, et joindre la facture à l'e-mail « Commande en cours » (donc une fois le virement reçu).

## 5. Ce qui reste à valider

- **Libellé du bouton de commande** : « Commander avec obligation de paiement ». C'est la formulation exigée en France (et en Allemagne, « zahlungspflichtig bestellen »). À faire valider.
- **TVA** : 20 % partout pour l'instant. La TVA par pays (Belgique 21 %, Allemagne 19 %) ne sera réglée qu'après l'accord écrit du comptable.
- **Corse** : elle fait partie de la zone France. Si le transporteur la facture plus cher, il faudra une zone à part.
