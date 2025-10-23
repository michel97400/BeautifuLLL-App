<?php
    require "php-crud/model/matieres.php";
    use Models\Matiere;
    $matiere = new Matiere();
    $matiere->read();

    foreach ($matiere as $key => $value) {
        # code...
        echo($value);
    }

?>