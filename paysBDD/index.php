<?php
/* Connexion à une base MySQL avec l'invocation de pilote */
$dsn = 'mysql:dbname=pays;host=127.0.0.1';
$user = 'Lilly';
$password = "pays.lilly.2023";

$dbh = new PDO($dsn, $user, $password);

$sqlcontinents = 'SELECT DISTINCT t_continents.id_continent, t_continents.libelle_continent FROM t_continents
                  INNER JOIN t_pays ON (t_continents.id_continent=t_pays.continent_id)
                  INNER JOIN t_regions ON (t_pays.region_id=t_regions.id_region)
                  ORDER BY libelle_continent';

$sqlregions = 'SELECT DISTINCT t_regions.id_region, t_regions.libelle_region 
               FROM t_regions 
               INNER JOIN t_continents ON t_regions.continent_id=t_continents.id_continent
               INNER JOIN t_pays ON t_regions.id_region=t_pays.region_id
               ORDER BY libelle_region';

$sqlpays = 'SELECT * FROM t_pays
            INNER JOIN t_continents ON t_pays.continent_id = t_continents.id_continent
            INNER JOIN t_regions ON t_pays.region_id = t_regions.id_region';
if (isset($_POST["submit_continent"])) {
    $sqlpays .= ' WHERE t_pays.continent_id = :continent ORDER BY libelle_pays';
}

if (isset($_POST["submit_region"])) {
    $sqlpays .= ' WHERE t_pays.region_id = :region ORDER BY libelle_pays';
}

// Utilisation d'une requête préparée
$stmt = $dbh->prepare($sqlpays);

if (isset($_POST["submit_continent"]) && isset($_POST["continent"])) {
    $stmt->bindParam(':continent', $_POST["continent"], PDO::PARAM_INT);
}

if (isset($_POST["submit_region"]) && isset($_POST["region"])) {
    $stmt->bindParam(':region', $_POST["region"], PDO::PARAM_INT);
}

$stmt->execute();
$result = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Initialiser les variables pour les totaux
$totalSuperficie = 0;
$totalPopulation = 0;
$totalTauxNatalite = 0;
$totalTauxMortalite = 0;
$totalTauxMortaliteInfantile = 0;
$totalNbEftFemme  = 0;
$totalEspVie = 0;
$totalTauxCroiss = 0;
$TotalPop65 = 0;

// Itérer à travers le jeu de résultats
foreach ($result as $row) {
    // Mettre à jour les totaux
    $totalSuperficie += $row["Superficie_km2"];
    $totalPopulation += $row["population_pays"];
    $totalTauxNatalite += $row["taux_natalite_pays"];
    $totalTauxMortalite += $row["taux_mortalite_pays"];
    $totalTauxMortaliteInfantile += $row["taux_mortalite_infantile_pays"];
    $totalNbEftFemme += $row["nombre_enfants_par_femme_pays"];
    $totalEspVie += $row["esperance_vie_pays"];
    $totalTauxCroiss += $row["taux_croissance_pays"];
    $TotalPop65 += $row["population_plus_65_pays"];
}

$avgTauxNatalite = number_format(count($result) > 0 ? $totalTauxNatalite / count($result) : 0);
$avgTauxMortalite = number_format(count($result) > 0 ? $totalTauxMortalite / count($result) : 0);
$avgTauxMortaliteInfantile = number_format(count($result) > 0 ? $totalTauxMortaliteInfantile / count($result) : 0);
$avgTauxCroiss = number_format(count($result) > 0 ? $totalTauxCroiss / count($result) : 0);

?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="utf-8">
    <title>Liste des pays</title>
    <link rel="stylesheet" href="index.css" />

    <script>
        function updateRegions() {
            var continentSelect = document.getElementById('continent');
            var regionSelect = document.getElementById('region');

            // Récupérer la valeur sélectionnée dans le filtre par continent
            var selectedContinent = continentSelect.value;

            // Créer une nouvelle requête AJAX
            var xhr = new XMLHttpRequest();

            // Définir la fonction de rappel pour gérer la réponse AJAX
            xhr.onreadystatechange = function() {
                if (xhr.readyState === XMLHttpRequest.DONE) {
                    if (xhr.status === 200) {
                        // Mettre à jour les options du filtre par région avec la réponse AJAX
                        regionSelect.innerHTML = xhr.responseText;
                    } else {
                        // Gérer les erreurs ici
                        console.error('Erreur lors de la récupération des régions');
                    }
                }
            };

            // Préparer et envoyer la requête AJAX
            xhr.open('GET', 'get_regions.php?continent=' + selectedContinent, true);
            xhr.send();
        }
    </script>

</head>

<body>
    <header>
        <h1>Liste des Pays du Monde</h1>
    </header>
    <main>
        <form action="" method="POST">
            <select name="continent" id="continent" onchange="updateRegions()">
                <option value="">Continent</option>
                <?php foreach ($dbh->query($sqlcontinents) as $row) { ?>
                    <option value="<?php print $row["id_continent"]; ?>" <?php if (isset($_POST["continent"]) && $_POST["continent"] == $row["id_continent"]) echo "selected"; ?>>
                        <?php print $row["libelle_continent"]; ?>
                    </option>
                <?php } ?>
            </select>
            <button type="submit" name="submit_continent">Filtrer par continent</button>
        </form>

        <form action="" method="POST">
            <select name="region" id="region">
                <option value="">Région</option>
                <!-- Les options du filtre par région seront mises à jour ici -->
            </select>
            <button type="submit" name="submit_region">Filtrer par région</button>
        </form>

        <?php if (!empty($result)) { ?>
            <table id=table>
                <thead>
                    <th>Nom du pays</th>
                    <th>Capitale</th>
                    <th>Superficie</th>
                    <th>Population</th>
                    <th>Taux de natalité</th>
                    <th>Taux de mortalité</th>
                    <th>Taux de mortalité infantile</th>
                    <th>Nombre d'enfant par femme</th>
                    <th>Espérance de vie</th>
                    <th>Taux de croissance</th>
                    <th>Population de plus de 65 ans</th>
                </thead>
                <tbody>
                    <?php foreach ($result as $row) { ?>
                        <tr>
                            <td><?php print $row["libelle_pays"]; ?></td>
                            <td><?php print $row["Capital"]; ?></td>
                            <td><?php print $row["Superficie_km2"]; ?></td>
                            <td><?php print $row["population_pays"]; ?></td>
                            <td><?php print $row["taux_natalite_pays"]; ?></td>
                            <td><?php print $row["taux_mortalite_pays"]; ?></td>
                            <td><?php print $row["taux_mortalite_infantile_pays"]; ?></td>
                            <td><?php print $row["nombre_enfants_par_femme_pays"]; ?></td>
                            <td><?php print $row["esperance_vie_pays"]; ?></td>
                            <td><?php print $row["taux_croissance_pays"]; ?></td>
                            <td><?php print $row["population_plus_65_pays"]; ?></td>
                        </tr>
                    <?php } ?>

                </tbody>
                <tfoot>
                    <tr>
                        <td>QUEPOUIC</td>
                        <td></td>
                        <td><?php print $totalSuperficie; ?></td>
                        <td><?php print $totalPopulation; ?></td>
                        <td><?php print $avgTauxNatalite; ?></td>
                        <td><?php print $avgTauxMortalite; ?></td>
                        <td><?php print $avgTauxMortaliteInfantile; ?></td>
                        <td><?php print $totalNbEftFemme; ?></td>
                        <td><?php print $totalEspVie; ?></td>
                        <td><?php print $avgTauxCroiss; ?></td>
                        <td><?php print $TotalPop65; ?> </td>
                    </tr>
                </tfoot>
            </table>
        <?php } ?>

    </main>
    <footer>
        <p>Source : World Population Prospects. Nations Unies. 2022
            Note de lecture : La publication World Population Prospects fournit les estimations de population des Nations Unies pour tous les pays du monde pour chaque année entre 1950 et 2021
            et les projections selon différents scénarios (bas, moyen et haut) pour chaque année entre 2022 et 2100. Les chiffres présentés ici correspondent aux projections pour l’année en cours
            selon le scénario moyen.</p>
    </footer>
</body>

</html>