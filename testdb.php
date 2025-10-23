<?php
    require "php-crud/model/matieres.php";
    use Models\Matiere;
    $matiere = new Matiere();
    $matiere->read();
    echo($matiere);

?>