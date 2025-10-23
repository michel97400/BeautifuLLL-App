<main>

    
    <?php
        require_once "../model/etudiant.php";
        use Models\Etudiants;
        $etudiantsModel = new Etudiants();
        $etudiantsList = $etudiantsModel->read();

        
        echo("<table>");
        echo("<tbody>");

        foreach ($etudiantsList as $etudiant) {
            echo("<tr>");
            echo("<td>". $etudiant["nom"]."</td");
            echo("<td>". $etudiant["prenom"]."</td");
            echo("<td>". $etudiant["email"]."</td");
            // echo("<td>". $etudiant["avatar"]."</td");
            echo("<td>". $etudiant["date_inscription"]."</td");
            echo("<td>". $etudiant["id_niveau"]."</td");
            echo("<td>". $etudiant["id_role"]."</td");
            echo("<td>". $etudiant["nom"]."</td");
            echo("<tr>");
        }

        echo( "</tbody>");


    ?>
</main>