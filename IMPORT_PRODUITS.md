# Importer les produits

Le fichier **`produits.csv`** contient les 54 produits du catalogue de départ : 24 poêles à granulés, 17 poêles à bois, 13 chaudières.

## Ce qu'il contient

| Colonne du tableau de départ | Où elle va dans WooCommerce |
|---|---|
| Nom (modèle) | Nom du produit |
| Catégorie | Catégorie (créée automatiquement si elle n'existe pas) |
| Marque | Marque (WooCommerce > Marques) **et** caractéristique « Marque » |
| Puissance (kW) | Caractéristique « Puissance », par exemple « 11,6 kW » : c'est elle qui sert au filtre et à l'assistant |
| Description courte | Description courte |
| Prix | Prix TTC |

En plus : une **référence** unique par produit (par exemple `ETNA-LUXE-CAYENNE-14`, pour pouvoir mettre les produits à jour plus tard avec un nouveau fichier) et l'**ordre d'affichage** du tableau.

Laissé **vide exprès** (rien n'est inventé) : photos, description longue, poids, classe de livraison.

## Importer (une seule fois)

1. Avant tout : **une sauvegarde du site**.
2. Avoir cliqué sur le bouton de **WooCommerce > Configuration Chaud Winter** (pour que les prix soient bien compris comme TTC).
3. **Produits > Tous les produits > Importer**.
4. Choisir `produits.csv`. Ne **pas** cocher « Mettre à jour les produits existants ». Cliquer sur **Continuer**.
5. L'écran suivant associe les colonnes : tout est reconnu automatiquement. Cliquer sur **Exécuter l'importateur**.
6. Attendre le message « 54 produits importés ».

> Si la colonne « Brands » apparaît en « Ne pas importer » (ancienne version de WooCommerce), ce n'est pas grave : la marque est aussi dans la caractéristique « Marque », que le thème sait lire. Il faudra juste mettre WooCommerce à jour pour voir la bande des marques sur l'accueil.

## Vérifier après l'import

- L'accueil affiche les 3 onglets avec **24, 17 et 13** produits.
- Sur la page « Poêles à granulés », le filtre **9 à 12 kW** trouve **15** poêles.
- La fiche « Mini 6,8 » affiche bien **6,8 kW** (et pas « 6 » ou « 8 »).

## À faire avant la mise en ligne

1. **Les prix sont des exemples** (relevés sur d'autres sites) : les remplacer par les vrais prix de Chaud Winter. Le plus simple : modifier la colonne `Regular price` du fichier et le réimporter en cochant « Mettre à jour les produits existants ».
2. **Les photos** des fournisseurs : sur chaque produit, « Image du produit ». En attendant, une illustration « Photo à venir » s'affiche.
3. **Le poids et la classe de livraison** de chaque appareil (onglet « Expédition » du produit), d'après les fiches techniques des fournisseurs. Sans classe, c'est le tarif « sans classe » de la zone qui s'applique.
4. **Les descriptions longues**, écrites par le client ou fournies par les fournisseurs (pas de texte copié sur les sites concurrents).

## Ajouter d'autres produits plus tard

Même méthode : ajouter des lignes au fichier, en gardant les mêmes colonnes. Pour la puissance, écrire la virgule avec une barre oblique inverse devant : `11\,6 kW`. Sinon WooCommerce coupe la valeur en deux.
