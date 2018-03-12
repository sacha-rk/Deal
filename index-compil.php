<?php
if(isset($_GET['q1'])){
require_once __DIR__ . '/include/init.php';
    
    if($_GET['q1'] !== ""){
        
        $req = 'SELECT titre FROM categorie WHERE mc LIKE :q OR titre LIKE :q';
        $stmt = $pdo->prepare($req);
        $stmt->bindValue(':q', '%'.$_GET['q1'].'%', PDO::PARAM_STR);
        $stmt->execute();
        $reponses = $stmt->fetchAll();
        
        $tableau = array();

        foreach($reponses as $reponse){
               $tableau[] = $reponse['titre'];      
        }
        
        echo json_encode($tableau);
        
        
    }
}


if(!empty($_POST)){
    
    
require_once __DIR__ . '/include/init.php';
    
    if(isset($_POST['v1']) && $_POST['v1'] !== "none"){
        $ajout = " AND c.titre = '".$_POST['v1']."'";
    
    }
    
    if(isset($_POST['v2']) && $_POST['v2'] !== "none"){
        $ajout .= " AND r.nom = '".$_POST['v2']."'";
    }
    
    if(isset($_POST['v3']) && $_POST['v3'] !== "none"){
        $ajout .= " AND m.pseudo = '".$_POST['v3']."'";
    }
    
    if(isset($_POST['v4']) && $_POST['v4'] !== "none"){
        
        if($_POST['v4'] == 1){
            $tri = 'a.prix';  
        }
        
        if($_POST['v4'] == 2){
            $tri = 'a.prix DESC';     
        }
        
        if($_POST['v4'] == 3){
            $tri = 'a.time_stamp';    
        }
        
    }else{
        $tri = 'a.time_stamp DESC';
    }

$req = 'SELECT m.pseudo, a.photo, a.titre, a.prix, a.cp, a.ville, a.d_courte, DATE_FORMAT(a.time_stamp, "%d/%m/%Y") AS date, m.id AS membre_id, a.id AS annonce_id, r.nom FROM annonce a 
JOIN membre m ON a.membre_id = m.id
JOIN categorie c ON a.categorie_id = c.id
JOIN departement d ON a.departement_id = d.id
JOIN region r ON d.region_id = r.id
WHERE a.statut = 1 '.$ajout.' 
GROUP BY a.titre
ORDER BY '.$tri;
$stmt = $pdo->query($req);
$annonces = $stmt->fetchAll();


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
}
            ?>