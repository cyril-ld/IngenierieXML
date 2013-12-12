<?php

	/**
     * Calcule la distance entre deux points en se basant sur les coordonnées deux ceux-ci
     * @param lat1
     * @param lon1
     * @param lat2
     * @param lon2
     * @return int float distance
     */
    function getDistance($lat1, $lon1, $lat2, $lon2) {
    	$rLat1 = deg2rad($lat1);
        $rLon1 = deg2rad($lon1);
        $rLat2 = deg2rad($lat2);
        $rLon2 = deg2rad($lon2);
        $x = ($rLon2 - $rLon1) * cos(($rLat1 + $rLat2)/2);
        $y = $rLat2 - $rLat1;
        $dist =sqrt($x*$x + $y*$y) * 6371009;
        return (int) $dist; 
    }

    // Début du script
	$heureDepart = microtime(true);

	$docMobilite = new DOMDocument();
	$docMobilite->load("../LOC_EQUIPUB_MOBILITE_NM_STBL.xml");

	$docEquisport = new DOMDocument();
	$docEquisport->load("../LOC_EQUIPUB_SPORT_NM_STBL.xml");

	$listeEquiSport = $docEquisport->getElementsByTagName('element');
	$listeEquiMobi = $docMobilite->getElementsByTagName('element');

	// Document Object Model résultat
	$docProduit = new DOMDocument('1.0', 'UTF-8');

	// Noeud racine du DOM produit
	$noeudRacine = $docProduit->createElement("liste-EquiSport");

	// Ajout des noeuds "<equisport>" en fonction de ce qui existe dans les fichiers chargés
	foreach ($listeEquiSport as $equiSport) {

		// Création du noeud
		$nouveauEquiSport = $docProduit->createElement("equisport");

		// Ajout de l'attribut nom
		$nouveauEquiSport->setAttribute("nom", utf8_encode($equiSport->getElementsByTagName("name")->item(0)->nodeValue));

		// Ajout de l'attribut adresse
		$nouveauEquiSport->setAttribute("adresse", utf8_encode($equiSport->getElementsByTagName("ADRESSE")->item(0)->nodeValue));

		$lat_lon = $equiSport->getElementsByTagName("_l")->item(0)->nodeValue;
		$crochets = array("[", "]");
		$lat_lon = str_replace($crochets, "", $lat_lon);
		$latlonTab = explode(",", $lat_lon);
		$latEquiSport = trim($latlonTab[0]);
		$lonEquiSport = trim($latlonTab[0]);

		// Parcours du fichier des équipements sportifs pour récupérer les équipements proches
		foreach ($listeEquiMobi as $equiMobi) {

			$lat_lon = $equiMobi->getElementsByTagName("_l")->item(0)->nodeValue;
			$crochets = array("[", "]");
			$lat_lon = str_replace($crochets, "", $lat_lon);
			$latlonTabMobi = explode(",", $lat_lon);
			$latEquiMobi = trim($latlonTabMobi[0]);
			$lonEquiMobi = trim($latlonTabMobi[0]);
			$distance = getDistance($latEquiSport, $lonEquiSport, $latEquiMobi, $lonEquiMobi);
			if($distance <= 500) {

				// Création du noeud <mobi-proxy> contenant les informations d'un équipement sportif à moins de 500 mètres
				$equiMobi_node = $docProduit->createElement("mobi-proxy");

				// Création des noeuds fils
				$nom_node = $docProduit->createElement("nom");
				$nom_node_text = $docProduit->createTextNode(utf8_encode($equiMobi->getElementsByTagName("name")->item(0)->nodeValue));
				$nom_node->appendChild($nom_node_text);

				$categorie_node = $docProduit->createElement("categorie");
				$categorie_node_text = $docProduit->createTextNode(utf8_encode($equiMobi->getElementsByTagName("LIBCATEGORIE")->item(0)->nodeValue));
				$categorie_node->appendChild($categorie_node_text);

				$adresse_node = $docProduit->createElement("adresse");
				$adresse_node_text = $docProduit->createTextNode(utf8_encode($equiMobi->getElementsByTagName("ADRESSE")->item(0)->nodeValue." ".$equiMobi->getElementsByTagName("CODE_POSTAL")->item(0)->nodeValue." ".$equiMobi->getElementsByTagName("COMMUNE")->item(0)->nodeValue));
				$adresse_node->appendChild($adresse_node_text);

				$distance_node = $docProduit->createElement("distance");
				$distance_node_text = $docProduit->createTextNode($distance);
				$distance_node->appendChild($distance_node_text);

				// Ajout des noeuds "<nom>", "<categorie>", "<adresse>", "<distance>" au noeud <mobi-proxy>
				$equiMobi_node->appendChild($nom_node);
				$equiMobi_node->appendChild($categorie_node);
				$equiMobi_node->appendChild($adresse_node);
				$equiMobi_node->appendChild($distance_node);

				// Ajout du noeud mobi-proxy au noeud equisport
				$nouveauEquiSport->appendChild($equiMobi_node);
			}
		}

		// Ajout du noeud equisport à <liste-equisport>
		$noeudRacine->appendChild($nouveauEquiSport);
	}

	header("Content-type: application/xml");
	$docProduit->appendChild($noeudRacine);
	echo utf8_decode($docProduit->saveXML());
	// Fin du script
	$heureFin = microtime(true);

	// Durée d'exé
	$time = $heureFin - $heureDepart;

	//Afficher le temps d'éxecution
	$page_load_time = number_format($time, 3);
	echo '<!-- Debut du script: '.date("H:i:s", $heureDepart).' Fin du script: '.date("H:i:s", $heureFin).' Script execute en '. $page_load_time .' sec -->';
?>
