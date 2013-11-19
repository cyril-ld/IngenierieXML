<?php header('Content-type: text/xml');
include_once('Sax4PHP.php');

/**
 * Classe regroupant des méthodes utilitaires, statiques.
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

    function __construct() {}
    function __destruct(){}

    function EquiSport($nom, $prenom, $distanceMax) {
        $this->nom = $nom;
        $this->adresse = $adresse;
        $this->distanceMax = $distanceMax;
        $this->$equipements = array();
    }

    function setLongitude($longitude) {
        $this->longitude = $longitude;
    }

    function getLongitude() {
        return $this->longitude;
    }

    function setLatitude($latitude) {
        $this->latitude = $latitude;
    }

    function getLatitude() {
        return $this->latitude;
    }

    function setNom($nom) {
        $this->nom = $nom;
    }

    function getNom() {
        return $this->nom;
    }

    function setAdresse($adresse) {
        $this->adresse = $adresse;
    }

    function getAdresse() {
        return $this->adresse;
    }

    function addEquipement($equipement) {
        array_push($this->equipements, $equipement);
    }

    function setDistanceMax($distance) {
        $this->distanceMax = $distance;
    }

    function getDistanceMax() {
        return $this->distanceMax;
    }

    function getEquipements() {
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
                // Cette instruction doit être la dernière effectuée dans le cas où on quitte le noeud représentant un équipement
                $this->equiSportCourant = null;
                break;
            case 'name' :
                if (strlen($this->texte) > 0){
                    $this->equiSportCourant->setNom(utf8_decode($this->texte));
                }
                break;
            case 'ADRESSE' :
                if (strlen($this->texte) > 0){
                    $this->equiSportCourant->setAdresse(utf8_decode($this->texte).$this->equiSportCourant->getAdresse());
                }
                break;
            case 'CODE_POSTAL' :
                if (strlen($this->texte) > 0){
                    $this->equiSportCourant->setAdresse($this->equiSportCourant->getAdresse().utf8_decode($this->texte));
                }
                echo is_null($this->equiSportCourant);
                break;
            case 'COMMUNE' :
                if (strlen($this->texte) > 0){
                    $this->equiSportCourant->setAdresse($this->equiSportCourant->getAdresse().utf8_decode($this->texte));
                }
                echo is_null($this->equiSportCourant);
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
        echo "<document>\n";
    } 
    
    function endDocument() {
        echo "</document>\n";
    }

    function characters($txt) {
        $txt_reduit = trim($txt);
        if (!(empty($txt_reduit))) $this->texte .= $txt;
    }
}

/**
 * Handler pour le parcours du fichier contenant les équipements de mobilité
 */
class MobiliteHandler extends DefaultHandler {
    
    function startElement($name, $att) {
        $rien;
    }
    
    function endElement($name) {
        $rien;
    } 

    function startDocument() {
        $rien;
    } 
    
    function endDocument() {
        $rien;
    }
}


class MySaxHandler extends DefaultHandler {

  function startElement($name, $att) {echo "<start name='$name'/>\n";}
  function endElement($name) {echo "<end name='$name'/>\n";} 
  
  function startDocument() {echo "<list>\n";} 
  function endDocument() {echo "</list>\n";}
}

/*
 * ============================================================================
 * Code dégueulasse, placé là pour pouvoir être appelé au chargement de la page
 */
//$xmlMobilite = file_get_contents('LOC_EQUIPUB_MOBILITE_NM_STBL.xml');
$xmlSport = file_get_contents('./LOC_EQUIPUB_SPORT_NM_STBL.xml');

echo "<?xml version='1.0'?>";

if(is_null($xmlSport)) {
    echo "rien dans le xml de sport !";
}

//$saxMobilite = new SaxParser(new MobiliteHandler());
$saxSport = new SaxParser(new SportHandler());

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