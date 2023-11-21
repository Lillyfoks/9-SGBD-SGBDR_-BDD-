<?php
// Connexion à la base de données (à adapter selon votre configuration)
$dsn = 'mysql:dbname=pays;host=127.0.0.1';
$user = 'Lilly';
$password = "pays.lilly.2023";

try {
    $dbh = new PDO($dsn, $user, $password);
} catch (PDOException $e) {
    // Gérer les erreurs de connexion à la base de données ici
    die('Erreur de connexion à la base de données : ' . $e->getMessage());
}

// Récupérer le continent depuis la requête GET
$continent = isset($_GET['continent']) ? $_GET['continent'] : null;

// Préparer la requête pour récupérer les régions en fonction du continent
$sql = 'SELECT id_region, libelle_region FROM t_regions WHERE continent_id = :continent ORDER BY libelle_region';
$stmt = $dbh->prepare($sql);
$stmt->bindValue(':continent', $continent, PDO::PARAM_INT);
$stmt->execute();

// Générer le HTML des options du filtre par région
$options = '<option value="">Région</option>';
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $options .= '<option value="' . $row['id_region'] . '">' . $row['libelle_region'] . '</option>';
}

echo $options;
?>