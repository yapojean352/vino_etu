<div class="cellier">
<?php
foreach ($data as $cle => $bouteille) {
    
    ?>
        <div class="bouteille" data-quantite="">
        <div class="img">
            
            <img src="https:<?php echo $bouteille['image'] ?> " width="100" height="100">
        </div>
        <div class="description">
            <p class="nom">Nom : <?php echo $bouteille['nom'] ?></p>
            <p class="quantite">Quantité : <?php echo $bouteille['quantite'] ?></p>
            <p class="pays">Pays : <?php echo $bouteille['pays'] ?></p>
            <p class="type">Type : <?php echo $bouteille['type'] ?></p>
            <p class="millesime">Millesime : <?php echo $bouteille['millesime'] ?></p>
            <p class="prix">Prix : <?php echo $bouteille['prix'] ?></p>
            <p class="notes">Notes : <?php echo $bouteille['notes'] ?></p>
            <p><a href="<?php echo $bouteille['url_saq'] ?>">Voir SAQ</a></p>
        </div>
        <div class="options" data-id="<?php echo $bouteille['vino__bouteille_id'] ?>">
            <button ><a href="?requete=modifierBouteilleCellier&id=<?php echo $bouteille['vino__bouteille_id']?>">Modifier</a></button>
            <button class='btnAjouter'>Ajouter</button>
            <button class='btnBoire'>Boire</button>
            
        </div>
    </div>
<?php


}

?>	
</div>


