<?php
/**
 * Class Bouteille
 * Cette classe possède les fonctions de gestion des bouteilles dans le cellier et des bouteilles dans le catalogue complet.
 * 
 * @author Jonathan Martel
 * @version 1.0
 * @update 2019-01-21
 * @license Creative Commons BY-NC 3.0 (Licence Creative Commons Attribution - Pas d’utilisation commerciale 3.0 non transposé)
 * @license http://creativecommons.org/licenses/by-nc/3.0/deed.fr
 * 
 */
class Bouteille extends Modele {
	const TABLE = 'vino__bouteille';
    
	public function getListeBouteille()
	{
		
		$rows = Array();
		$res = $this->_db->query('Select * from '. self::TABLE);
		if($res->num_rows)
		{
			while($row = $res->fetch_assoc())
			{
				$rows[] = $row;
			}
		}
		
		return $rows;
	}

	/**
	 * Fonction: Permetant de montrer tous les bouteilles qui sont dans des celliers
	 * 
	 * TODO:: Ajouter un WHERE losrqu'on nous allons avoir les USER et ajouter param
	 * 
	 * @throws Exception Erreur de requête sur la base de données 
	 * 
	 * @return array de tous les bouteilles du cellier
	 */
	public function getListeBouteilleCellier($idUtilisateur, $idCellier= "")
	{
		
		$rows = Array();
		$requete ='SELECT
		B.nom,
		B.format,
		B.image,
		B.code_saq,
		B.pays,
		B.description,
		B.prix_saq,
		B.url_saq,
		B.url_img,
		C.quantite,
		C.prix,
		C.date_achat,
		C.notes,
		C.garde_jusqua,
		C.millesime,
		T.type,
		C.vino__bouteille_id,
		VC.id,
		VC.cellier__nom
		FROM cellier__bouteille AS C
		INNER JOIN vino__bouteille AS B ON C.vino__bouteille_id = B.id
		INNER JOIN vino__type AS T ON B.fk__vino__type_id =T.id
        INNER JOIN vino__cellier AS VC ON C.vino__cellier_id = VC.id
		WHERE VC.fk__users_id = ' . $idUtilisateur; 

		//Permet de vérifier si recherche un cellier précis
		if(!empty($idCellier)) {
			$requete .= " AND C.vino__cellier_id = " . $idCellier;
		}
		
		if(($res = $this->_db->query($requete)) ==	 true)
		{
			if($res->num_rows)
			{
				while($row = $res->fetch_assoc())
				{
					$row['nom'] = trim(utf8_encode($row['nom']));
					$rows[] = $row;
				}
			}
		}
		else 
		{
			throw new Exception("Erreur de requête sur la base de donnée", 1);
			 //$this->_db->error;
		}
		return $rows;
	}

	/**
	 * Fonction: Permetant de montrer tous les bouteilles qui sont dans des celliers
	 * 
	 * TODO:: Ajouter un WHERE losrqu'on nous allons avoir les USER et ajouter param
	 * 
	 * @throws Exception Erreur de requête sur la base de données 
	 * 
	 * @return array de tous les bouteilles du cellier
	 */
	public function getUneBouteilleCellier($id)
	{
		
		$rows = Array();
		$requete ='SELECT
		B.nom,
		B.format,
		B.image,
		B.code_saq,
		B.pays,
		B.description,
		B.prix_saq,
		B.url_saq,
		B.url_img,
		C.quantite,
		C.prix,
		C.date_achat,
		C.notes,
		C.garde_jusqua,
		C.millesime,
		T.type,
		C.vino__bouteille_id
		FROM cellier__bouteille AS C
		INNER JOIN vino__bouteille AS B ON C.vino__bouteille_id = B.id
		INNER JOIN vino__type AS T ON B.fk__vino__type_id =T.id
		WHERE C.vino__bouteille_id = '.$id.'
		'; 
		if(($res = $this->_db->query($requete)) ==	 true)
		{
			if($res->num_rows)
			{
				while($row = $res->fetch_assoc())
				{
					$row['nom'] = trim(utf8_encode($row['nom']));
					$rows[] = $row;
				}
			}
		}
		else 
		{
			throw new Exception("Erreur de requête sur la base de donnée", 1);
			 //$this->_db->error;
		}
		return $rows;
	}
	
	/**
	 * Cette méthode permet de retourner les résultats de recherche pour la fonction d'autocomplete de l'ajout des bouteilles dans le cellier
	 * 
	 * @param string $nom La chaine de caractère à rechercher
	 * @param integer $nb_resultat Le nombre de résultat maximal à retourner.
	 * 
	 * @throws Exception Erreur de requête sur la base de données 
	 * 
	 * @return array id et nom de la bouteille trouvée dans le catalogue
	 */
       
	public function autocomplete($nom, $nb_resultat=10)
	{
		
		$rows = Array();
		$nom = $this->_db->real_escape_string($nom);
		$nom = preg_replace("/\*/","%" , $nom);
		 
		//echo $nom;
		$requete ='SELECT id, nom FROM vino__bouteille where LOWER(nom) like LOWER("%'. $nom .'%") LIMIT 0,'. $nb_resultat; 
		//var_dump($requete);
		if(($res = $this->_db->query($requete)) ==	 true)
		{
			if($res->num_rows)
			{
				while($row = $res->fetch_assoc())
				{
					$row['nom'] = trim(utf8_encode($row['nom']));
					$rows[] = $row;
					
				}
			}
		}
		else 
		{
			throw new Exception("Erreur de requête sur la base de données", 1);
			 
		}
		
		
		//var_dump($rows);
		return $rows;
	}
	
	
	/**
	 * Cette méthode ajoute une ou des bouteilles au cellier
	 * 
	 * @param Array $data Tableau des données représentants la bouteille.
	 * 
	 * @return Boolean Succès ou échec de l'ajout.
	 */
	public function ajouterBouteilleCellier($data)
	{

		//Debut creation de la premiere partie de la requete
		$requete ="INSERT INTO cellier__bouteille(vino__bouteille_id,vino__cellier_id,quantite";

		//Initialise un tableau pour inserer des erreurs
		$erreur = array();

		//Verification de la quantite Min 1
		$regExp = "/^[1-9]\d*$/i";
		if(!preg_match($regExp, $data->quantite)){
			$erreur["quantite"] = true;
		}

		//Permet de coonstruire la deuxieme partie de la requete
		$requete2="VALUES ("."'".$data->id_bouteille."',"."'".$data->cellier ."',"."'". $data->quantite ."'";

		//Verification bon format de date YYYY-MM-DD seulement si une valeur est entr/
		if($data->date_achat !== ""){

			//Permet de construire la requete
			$requete .= ",date_achat";
			$requete2 .= ",'".$data->date_achat."'";

			$regExp = "/^([12]\d{3}-(0[1-9]|1[0-2])-(0[1-9]|[12]\d|3[01]))$/i";
			if(!preg_match($regExp, $data->date_achat)){
				//Ajoute une erreur dans le tableau
				$erreur["date_achat"] = true;
			}
		}

		//Verification garde_jusqua
		if($data->garde_jusqua !== ""){

			//Permet de construire la requete
			$requete .= ",garde_jusqua";
			$requete2 .= ",'".$data->garde_jusqua."'";

			$regExp = "/^\d*$/i";
			if(!preg_match($regExp, $data->garde_jusqua)){
				//Ajoute une erreur dans le tableau
				$erreur["garde_jusqua"] = true;
			}
		}

		//Verification du prix 
		if($data->prix !== ""){

			//Permet de construire la requete
			$requete .= ",prix";
			$requete2 .= ",'".$data->prix."'";

			$regExp = "/^[1-9]\d*\.?\d\d$/i";
			if(!preg_match($regExp, $data->prix)){
				$erreur["prix"] = true;
			}
		}


		//Verification  du millesime
		if($data->millesime !== ""){
			
			//Permet de construire la requete
			$requete .= ",millesime";
			$requete2 .= ",'".$data->millesime."'";

			$regExp = "/^\d*$/i";
			if(!preg_match($regExp, $data->millesime)){
				$erreur["millesime"] = true ;
			}
		}

		//Permet de construire la requete
		$requete .= ",notes)";
		$requete2 .= ",'".$data->notes."')";
		

		//Verifie si il y a des erreurs
		if(count($erreur) == 0){
			
			//Fusionne les deux requete ense3mble
			$requete .= $requete2;
			$res = $this->_db->query($requete);

		}else{
			//Si contient erreur envoie quelle sont les erreurs
			$res = $erreur;
			
		}
		return $res;
	}
	/**
	 * Cette méthode change les données d'une bouteille dans le cellier
	 * 
	 * @param array $data contenant tous les infos necessaire
	 * 
	 * @return Boolean Succès ou échec de l'ajout.
	 */
	public function modifierBouteilleCellier($data)
	{
		
		//Debut creation de la requete
		$requete ="UPDATE cellier__bouteille
		SET quantite = $data->quantite" ;

		//Initialise un tableau pour inserer des erreurs
		$erreur = array();

		//Verification de la quantite Min 1
		$regExp = "/^[1-9]\d*$/i";
		if(!preg_match($regExp, $data->quantite)){
			$erreur["quantite"] = true;
		}

		//Verification bon format de date YYYY-MM-DD seulement si une valeur est entr/
		if($data->date_achat !== ""){

			//Permet de construire la requete
			$requete .= ", date_achat = '" . $data->date_achat ."'";

			$regExp = "/^([12]\d{3}-(0[1-9]|1[0-2])-(0[1-9]|[12]\d|3[01]))$/i";
			if(!preg_match($regExp, $data->date_achat)){
				//Ajoute une erreur dans le tableau
				$erreur["date_achat"] = true;
			}
		}

		//Verification garde_jusqua
		if($data->garde_jusqua !== ""){

			//Permet de construire la requete
			$requete .= ", garde_jusqua = " . $data->garde_jusqua ;

			$regExp = "/^\d*$/i";
			if(!preg_match($regExp, $data->garde_jusqua)){
				//Ajoute une erreur dans le tableau
				$erreur["garde_jusqua"] = true;
			}
		}

		//Verification du prix 
		if($data->prix !== ""){

			//Permet de construire la requete
			$requete .= ", prix = " . $data->prix ;

			$regExp = "/^[1-9]\d*\.?\d\d$/i";
			if(!preg_match($regExp, $data->prix)){
				$erreur["prix"] = true;
			}
		}


		//Verification  du millesime
		if($data->millesime !== ""){
			
			//Permet de construire la requete
			$requete .= ", millesime = " . $data->millesime ;

			$regExp = "/^\d*$/i";
			if(!preg_match($regExp, $data->millesime)){
				$erreur["millesime"] = true ;
			}
		}


		//Verification  du millesime
		if($data->notes !== ""){
			//Permet de construire la requete
			$requete .= ", notes = '".$data->notes . "'";
		}

		//Permet de construire la requete
		//TODO: Aller chercher le $id du cellier avec $_SESSION
		$requete .= " WHERE vino__bouteille_id = " . $data->id . " AND vino__cellier_id=" . $data->cellier_id;

		//Verifie si il y a des erreurs
		if(count($erreur) == 0){
			$res = $this->_db->query($requete);
		}else{
			//Si contient erreur envoie quelle sont les erreurs
			$res = $erreur;

		}
        
		return $res;
	}
	
	
	/**
	 * Cette méthode change la quantité d'une bouteille en particulier dans le cellier
	 * 
	 * @param int $id id de la bouteille
	 * @param int $nombre Nombre de bouteille a ajouter ou retirer
	 * 
	 * @return Boolean Succès ou échec de l'ajout.
	 */
	public function modifierQuantiteBouteilleCellier($id, $nombre)
	{
		//TODO : Valider les données.
		$requete = "UPDATE cellier__bouteille SET quantite = GREATEST(quantite + ". $nombre. ", 0) WHERE vino__bouteille_id = ". $id;
		//echo $requete;
        $res = $this->_db->query($requete);
        
		return $res;
	}

	public function supprimerBouteilleCellier($data){
		
		$this->stmt = $this->_db->prepare("DELETE FROM cellier__bouteille WHERE vino__bouteille_id = ? AND vino__cellier_id = ?");
		//Bind les params
		$this->stmt->bind_param('ii', $data->id, $data->cellier_id);
		return $this->stmt->execute();	
	}
}




?>