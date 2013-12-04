<?php header('Content-type: text/xml');
include_once('Sax4PHP.php');

/**
 * Classe regroupant des méthodes utilitaires.
 */
class Utils {


    /**
     * Calcule la distance entre deux points en se basant sur les coordonnées deux ceux-ci
     * @param lat1
     * @param lon1
     * @param lat2
     * @param lon2
     * @return int float distance
     */
    public static function getDistance($lat1, $lon1, $lat2, $lon2) {
        $rLat1 = deg2rad($lat1);
        $rLon1 = deg2rad($lon1);
        $rLat2 = deg2rad($lat2);
        $rLon2 = deg2rad($lon2);
        $x = ($rLon2 - $rLon1) * cos(($rLat1 + $rLat2)/2);
        $y = $rLat2 - $rLat1;
        $dist =sqrt($x*$x + $y*$y) * 6371009;
        return $dist; 
    }
}

/**
 * Classe représentant un équipement de mobilité.
 */
class EquiMobi {

    /**
     * Nom de l'équipement
     */
    private $nom;

    /**
     * Catégorie de l'équipement
     */
    private $categorie;

    /**
     * Adresse de l'équipement
     */
    private $adresse;

    /**
     * Latitude de l'équipement
     */
    private $latitude;

    /**
     * Distance par rapport à l'équipement sportif englobant
     */
    private $distance;

    /**
     * Longitude de l'équipement
     */
    private $longitude;

    public function setLongitude($longitude) {
        $this->longitude = $longitude;
    }

    public function setDistance($distance) {
        $this->distance = $distance;
    }

    public function getDistance() {
        return $this->distance;
    }

    public function getLongitude() {
        return $this->longitude;
    }

    public function setLatitude($latitude) {
        $this->latitude = $latitude;
    }

    public function getLatitude() {
        return $this->latitude;
    }

    public function setNom($nom) {
        $this->nom = $nom;
    }

    public function getNom() {
        return $this->nom;
    }

    public function setCategorie($categorie) {
        $this->categorie = $categorie;
    }

    public function getCategorie() {
        return $this->categorie;
    }

    public function setAdresse($adresse) {
        $this->adresse = $adresse;
    }

    public function getAdresse() {
        return $this->adresse;
    }
}

/**
 * Classe représentant un équipement sportif.
 * Utilisée pour stocker les éléments lors du parcours des différents fichiers.
 */
class EquiSport {

    /**
     * Nom de l'équipement sportif (Exemple : Piscine du Petit Port)
     */
    private $nom;

    /**
     * Adresse de l'équipement sportif (Exemple : Boulevard du Petit Port)
     */
    private $adresse;

    /**
     * Liste des équipements de mobilité compris dans un rayon suffisament proche
     */
    private $equipements;

    /**
     * Distance maximale à laquelle peut être situé un équipement ajouté dans la liste des équipements de mobilité
     */
    private $distanceMax;

    /**
     * Latitude de l'équipement de sport
     */
    private $latitude;

    /**
     * Longitude de l'équipement
     */
    private $longitude;

    public function EquiSport() {
        $this->equipements = array();
    }

    public function setLongitude($longitude) {
        $this->longitude = $longitude;
    }

    public function getLongitude() {
        return $this->longitude;
    }

    public function setLatitude($latitude) {
        $this->latitude = $latitude;
    }

    public function getLatitude() {
        return $this->latitude;
    }

    public function setNom($nom) {
        $this->nom = $nom;
    }

    public function getNom() {
        return $this->nom;
    }

    public function setAdresse($adresse) {
        $this->adresse = $adresse;
    }

    public function getAdresse() {
        return $this->adresse;
    }

    public function addEquipement($equipement) {
        array_push($this->equipements, $equipement);
    }

    public function setDistanceMax($distance) {
        $this->distanceMax = $distance;
    }

    public function getDistanceMax() {
        return $this->distanceMax;
    }

    public function getEquipements() {
        return $this->equipements;
    }
}

/**
 * Handler pour le parcours du fichier contenant les équipements sportifs
 */
class SportHandler extends DefaultHandler {

    /**
     * Liste des équipements sportifs
     */
    private $listeEquipements;

    private $texte;

    private $equiSportCourant;

    private $latitude;

    private $longitude;

    private $liste_equipements_sportifs;

    function __construct($new_liste_equipements_sportifs) {
        $this->liste_equipements_sportifs = $new_liste_equipements_sportifs;
        parent::__construct();
    }

    function startElement($name, $att) {
        
        // Reset du texte pour récupérer l'élément courant
        $this->texte = '';

        switch(utf8_decode($name)) {
            
            case 'element' : 
                // Création d'un nouvel équipement sportif
                $this->equiSportCourant = new EquiSport();
                break;
            default :
                // rien
                break;
        }
    }
    
    function endElement($name) {

        switch(utf8_decode($name)) {
            case 'element' :
                
                $this->setListeEquipementsMobilite();

                // Ajout de l'élément courant dans la liste des équipements sportifs
                array_push($this->liste_equipements_sportifs, $this->equiSportCourant);
                break;
            case 'name' :
                if (strlen($this->texte) > 0){
                    $this->equiSportCourant->setNom(utf8_decode($this->texte));
                }
                break;
            case 'ADRESSE' :
                if (strlen($this->texte) > 0){
                    $this->equiSportCourant->setAdresse(utf8_decode($this->texte)." ".$this->equiSportCourant->getAdresse());
                }
                break;
            case 'CODE_POSTAL' :
                if (strlen($this->texte) > 0){
                    $this->equiSportCourant->setAdresse($this->equiSportCourant->getAdresse()." ".utf8_decode($this->texte));
                }
                break;
            case 'COMMUNE' :
                if (strlen($this->texte) > 0){
                    $this->equiSportCourant->setAdresse($this->equiSportCourant->getAdresse()." ".utf8_decode($this->texte));
                }
                break;
            case '_l' :
                $caracteres = array("[", "]", " ");
                $latlon = str_replace($caracteres, "", utf8_decode($this->texte));
                $latLonTab = explode(",", $latlon);
                $this->equiSportCourant->setLatitude($latLonTab[0]);
                $this->equiSportCourant->setLongitude($latLonTab[1]);
                break;
            default:
                break;
        }
    } 

    function startDocument() {
        echo "<liste-equisport>\n";
    } 
    
    function endDocument() {
        foreach ($this->liste_equipements_sportifs as $value) {
            echo '<equisport nom="'.utf8_encode($value->getNom()).'" adresse="'.utf8_encode($value->getAdresse()).'">';

            $liste_equipements_mobilite = $value->getEquipements();

            if(count($liste_equipements_mobilite) == 0) {
                echo '<mobi-proxy/>';
            } else {
                echo '<liste-mobi-proxy>';
                foreach($liste_equipements_mobilite as $equiMobi) {
                    echo '<mobi-proxy>';
                    echo '<nom>'.utf8_encode($equiMobi->getNom()).'</nom>';
                    echo '<categorie>'.utf8_encode($equiMobi->getCategorie()).'</categorie>';
                    echo '<adresse>'.utf8_encode($equiMobi->getAdresse()).'</adresse>';
                    echo '<distance>'.$equiMobi->getDistance().'</distance>';
                    echo '</mobi-proxy>';
                }
                echo '</liste-mobi-proxy>';
            }

            echo '</equisport>';
        }
        echo "</liste-equisport>\n";
    }

    function characters($txt) {
        $txt_reduit = trim($txt);
        if (!(empty($txt_reduit))) $this->texte .= $txt;
    }

    /**
     * Fonction permettant de récupérer la liste des équipements de mobilité dans un rayon de 500 mètres.
     */
    function setListeEquipementsMobilite() {
        $xmlMobilite = null;
        $xmlMobilite = file_get_contents('../LOC_EQUIPUB_MOBILITE_NM_STBL.xml');

        $this->equiSportCourant->setDistanceMax(500);

        $saxMobilite = new SaxParser(new MobiliteHandler($this->equiSportCourant));

        try {
            $saxMobilite->parse($xmlMobilite);
        }catch(SAXException $e){  
            echo "\n",$e;
        }catch(Exception $e) {
            echo "Default exception >>", $e;
        }
    }
}

/**
 * Handler pour le parcours du fichier contenant les équipements de mobilité
 */
class MobiliteHandler extends DefaultHandler {
    
    /**
     * Equipement de sport qui est en cours de traitement par le SportHandler,
     * reçu par référence pour pouvoir modifier la liste des équipements
     */
    private $equipementSport;

    /**
     * Equipement de mobilité courant
     */
    private $equiMobiCourant;

    /**
     * Variable de stockage du contenu texte du noeud courant
     */
    private $texte;

    function MobiliteHandler($equipementSport) {
        $this->equipementSport = $equipementSport;
        parent::__construct();

    }

    function startElement($name, $att) {
        
        // Reset du texte pour récupérer l'élément courant
        $this->texte = '';
        switch ($name) {
            case 'element':
                $this->equiMobiCourant = new EquiMobi();
                break;
            default:
                # code...
                break;
        }
    }
    
    function endElement($name) {
        
        switch ($name) {
            case 'element':
                $distance = Utils::getDistance($this->equiMobiCourant->getLatitude(), $this->equiMobiCourant->getLongitude(), $this->equipementSport->getLatitude(), $this->equipementSport->getLongitude());

                if($distance < 500) {
                    $this->equiMobiCourant->setDistance($distance);
                    $this->equipementSport->addEquipement($this->equiMobiCourant);
                }
                $this->equiMobiCourant = null;
                break;
            case 'name' :
                $this->equiMobiCourant->setNom(utf8_decode($this->texte));
                break;
            case '_l' :
                $caracteres = array("[", "]", " ");
                $latlon = str_replace($caracteres, "", utf8_decode($this->texte));
                $latLonTab = explode(",", $latlon);
                $this->equiMobiCourant->setLatitude($latLonTab[0]);
                $this->equiMobiCourant->setLongitude($latLonTab[1]);
            case 'LIBCATEGORIE' :
                $this->equiMobiCourant->setCategorie(utf8_decode($this->texte));
                break;
            case 'ADRESSE' :
                $this->equiMobiCourant->setAdresse(utf8_decode($this->texte));
                break;
            default:
                # code...
                break;
        }
    } 

    function startDocument() {
        
    } 
    
    function endDocument() {
        
    }

    function characters($txt) {
        $txt_reduit = trim($txt);
        if (!(empty($txt_reduit))) $this->texte .= $txt;
    }
}

/*
 * Code appelé au chargement de la page, permettant de lancer le petit programme.
 */
$xmlSport = file_get_contents('../LOC_EQUIPUB_SPORT_NM_STBL.xml');

echo "<?xml version='1.0' encoding='UTF-8'?>";

if(is_null($xmlSport)) {
    echo "rien dans le xml de sport !";
}

$liste_equipements_sportifs = array();

$saxSport = new SaxParser(new SportHandler($liste_equipements_sportifs));

try {
    $saxSport->parse($xmlSport);
}catch(SAXException $e){  
    echo "\n",$e;
}catch(Exception $e) {
    echo "Default exception >>", $e;
}
/*
 * ============================================================================
 */

?>
