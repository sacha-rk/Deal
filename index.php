<?php
require_once __DIR__ . '/include/init.php';

$req = 'SELECT titre FROM categorie';
$stmt = $pdo->query($req);
$titresCat = $stmt->fetchAll();

$req = 'SELECT pseudo FROM membre WHERE statut = 1 ORDER BY pseudo';
$stmt = $pdo->query($req);
$membres = $stmt->fetchAll();

$req = 'SELECT nom FROM region ORDER BY nom';
$stmt = $pdo->query($req);
$regions = $stmt->fetchAll();

$req = 'SELECT min(prix) AS prix_min, max(prix) AS prix_max FROM annonce WHERE statut = 1';
$stmt = $pdo->query($req);
$fourchettePrix = $stmt->fetchAll();

$req = 'SELECT m.pseudo, a.photo, a.titre, a.prix, a.cp, a.ville, a.d_courte, DATE_FORMAT(a.time_stamp, "%d/%m/%Y") AS date, m.id AS membre_id, a.id AS annonce_id, r.nom FROM annonce a 
JOIN membre m ON a.membre_id = m.id
JOIN categorie c ON a.categorie_id = c.id
JOIN departement d ON a.departement_id = d.id
JOIN region r ON d.region_id = r.id
WHERE a.statut = 1';
if(!empty($_GET['cat'])){
    $stop = sizeof($titresCat);
    for($i = 0; $i < $stop; $i++){
        if($_GET['cat'] == $titresCat[$i]['titre']){
            $req.= ' AND c.titre = "'.$_GET['cat'].'"';
        }
    }
}
$req.= ' GROUP BY a.titre
ORDER BY a.time_stamp DESC';
$stmt = $pdo->query($req);
$annonces = $stmt->fetchAll();

include __DIR__ . '/layout/top.php';
?>
              <script src="/layout/jquery.js"></script>

                <h1>Accueil</h1>
                <hr>
                <h5>Filtrer :</h5>
                
                <form method="post" id="selector">
                    <label for=""><small>Par catégorie :</small></label>
                    <select id="filtre-cat" name="filtre-cat" class="form-control">
                    <option value=""></option>
                    <?php
                      foreach ($titresCat as $value){
                    ?>
                        <option value="<?=$value['titre'];?>"><strong><?=$value['titre'];?></strong></option>
                    <?php      
                      }  
                    ?>
                    </select>

                    <label for=""><small>Par region :</small></label>
                    <select id="filtre-region" name="filtre-region" class="form-control">
                    <option value=""></option>
                    <?php
                      foreach ($regions as $value){
                    ?>
                        <option value="<?=$value['nom'];?>"><?=$value['nom'];?></option>
                    <?php      
                      }  
                    ?>
                    </select>

                    <label for=""><small>Par membre :</small></label>
                    <select id="filtre-membre" name="filtre-membre" class="form-control">
                        <option value=""></option>
                        <?php
                          foreach ($membres as $value){
                        ?>
                            <option value="<?=$value['pseudo'];?>"><?=$value['pseudo'];?></option>
                        <?php      
                          }  
                        ?>
                    </select>
                    <hr>
                    <h4>Dernières annonces postées :</h4>
                    <h5>Trier :</h5>
                    <select id="tri" name="tri" class="form-control">
                        <option value=""></option>
                        <option value="1">Par prix croissant</option>
                        <option value="2">Par prix décroissant</option>
                        <option value="3">De la plus ancienne à la plus récente</option>
                    </select>
                </form> 
                <hr>   
    <div id="block-annonce">
        <?php
        if(empty($annonces)){
        ?>
            <div class="alert-warning">
                <strong>Je n'ai pas trouvé d'annonces...</strong>
            </div>
        <?php
        }else{
            
            foreach ($annonces as $annonce) {
            ?>
        <div class="row fake-table">
                <div class="col-md-3">
                      <img src="<?=PHOTO_WEB . $annonce['photo'];?>" alt="Photo actuelle" height="150px">
                </div>    
                <div class="col-md-7">
                    <div><?= $annonce['titre']; ?></div>
                    <div><?= $annonce['d_courte']; ?></div> 
                    <div><small>Date de publication : <?= $annonce['date']; ?></small></div>
                    <div>
                    <?php
                    $req = 'SELECT count(*) AS verif, ceil(avg(note)) AS moyenne FROM note WHERE membre_recep_id = :q AND statut = 1';
                    $stmt = $pdo->prepare($req);
                    $stmt->bindValue(':q', (int)$annonce['membre_id']);
                    $stmt->execute();
                    $result = $stmt->fetch();
                
                    if($result['verif'] == 0){
                        ?>
                        <small><?= 'Postée par : '.$annonce['pseudo']?></small>
                        <?php
                    }else{
                        
                        ?>
                        <small><?= 'Postée par : '.$annonce['pseudo']?> <?php
                        for($i = 0; $i < $result['moyenne']; $i++){
                            echo '<span class="glyphicon glyphicon-star"></span>';
                        }
                        for($j = 5; $j > $result['moyenne'] ; $j--){
                            echo '<span class="glyphicon glyphicon-star-empty"></span>';
                        }   
                    }
                    ?>
                    </small>
                
                    </div>
                    <div><small>Lieu : <?=$annonce['ville'] . ' ' . $annonce['cp']; ?></small></div>   
                    <div><a href="annonce-view.php?id=<?= $annonce['annonce_id'];?>&membre_id=<?= $annonce['membre_id']; ?>&note=<?=$result['moyenne'];?>"><button class="btn btn-primary btn-lg"><span class="glyphicon glyphicon-search"></span></button></a>
                    </div>     
                </div>
                <div class="col-md-2">
                        <?= number_format($annonce['prix'], 2, ',', ' '); ?> €</div>

        </div>
        <hr>

            <?php
            }
        }
            ?>
                
    </div>             
                
                
              
<script>
        jQuery(document).ready(function() {
            
            // FILTERS

            $('#selector').on('change', function() {

                
                if($('#filtre-cat').val() == ''){
                    var catValue = "none";   
                }else{
                    var catValue = $('#filtre-cat').val()
                }
                if($('#filtre-region').val() == ''){
                    var regValue = "none";   
                }else{
                    var regValue = $('#filtre-region').val()
                }
                if($('#filtre-membre').val() == ''){
                    var membValue = "none";   
                }else{
                    var membValue = $('#filtre-membre').val()
                }
                if($('#tri').val() == ''){
                    var tri = "none";   
                }else{
                    var tri = $('#tri').val()
                }

                    jQuery.ajax({

                        url: 'index-compil.php',
                        method: 'POST',
                        data: {
                            v1: catValue,
                            v2: regValue,
                            v3: membValue,
                            v4: tri
                        },
                        dataType: 'html',

                        success: function(donnees, statut, objet) {
                            
                            $('#block-annonce').html("");

                            $('#block-annonce').append('<div>'+donnees+'</div>');

                        },

                        error: function(error) {
                            console.log(error);

                        }

                    });

                });

                jQuery('form').on('submit', function(e) {
                    e.preventDefault();
                });

            
            // SEARCH ENGINE
            
            $('#target').keyup(function() {
            
            var qValue = $(this).val().trim();

                if (qValue !== "") {

                    jQuery.ajax({
                        url: 'index-compil.php',
                        method: 'GET',
                        data: {
                            q1: qValue
                        },
                        dataType: 'json',

                        success: function(donnees, statut, objXhr) {
                            console.log(donnees);

                            if (donnees.length){
                                
                                $('#affichage').html("");
                                $('#affichage').prepend('<li>Suggestions de catégorie :</li><br>');

                                for (var i = 0; i < donnees.length; i++) {
                                    console.log(donnees[i]);
                                    
                                    $('#affichage').append('<li class="list-group-item"><a href="index.php?cat=' + donnees[i] + '">' + donnees[i] + '</a></li><br>');

                                }
                                
                            }

                        },
                        error: function(error) {
                            console.log(error);
                        }
                    });

                } else {
                    
                    $('#affichage').html("");
                    
                }


                });

                jQuery('form').on('submit', function(event) {
                    event.preventDefault();
            });

        });
    </script>            
        
<?php
include __DIR__ . '/layout/bottom.php';
?>