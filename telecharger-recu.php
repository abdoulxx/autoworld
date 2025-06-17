<?php
session_start();
require_once 'admin/cnx.php';
require_once 'fpdf/fpdf.php';


// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: connexion.php');
    exit();
}

// Vérifier si l'ID de réservation est fourni
$reservation_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$reservation_id) {
    header('Location: mon-compte.php');
    exit();
}

try {
    // Récupérer les informations de la réservation
    $stmt = $conn->prepare("
        SELECT r.*, u.nom as user_nom, u.email, u.numero, u.adresse,
               v.marque, v.modele, v.annee,
               DATE_FORMAT(r.date_debut, '%d/%m/%Y') as date_debut_fr,
               DATE_FORMAT(r.date_fin, '%d/%m/%Y') as date_fin_fr,
               DATE_FORMAT(r.date_creation, '%d/%m/%Y') as date_creation_fr
        FROM reservations r
        JOIN users u ON r.user_id = u.id
        JOIN louer v ON r.vehicule_id = v.id
        WHERE r.id = ? AND r.user_id = ? AND r.statut = 'payé'
    ");
    $stmt->execute([$reservation_id, $_SESSION['user_id']]);
    $reservation = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$reservation) {
        header('Location: mon-compte.php');
        exit();
    }

    // Créer le PDF
    class FacturePDF extends FPDF {
        function Header() {
            // Titre centré en haut
            $this->SetFont('Arial', 'B', 20);
            $this->Cell(0, 15, 'RECU DE RESERVATION', 0, 1, 'C');
            
            // Logo
            $this->Image('images/logobg.png', 10, 20, 50);
        }

        function Footer() {
            $this->SetY(-15);
            $this->SetFont('Arial', 'I', 8);
            $this->Cell(0, 10, 'Page ' . $this->PageNo(), 0, 0, 'C');
        }
    }

    $pdf = new FacturePDF();
    $pdf->AddPage();
    $pdf->SetFont('Arial', '', 12);

    // Position pour les informations de la société (à droite)
    $pdf->SetXY(120, 20);
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(80, 10, 'AutoWorld', 0, 1);
    $pdf->SetX(120);
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(80, 6, 'Avenue de la Republique', 0, 1);
    $pdf->SetX(120);
    $pdf->Cell(80, 6, 'Abidjan, Cote dIvoire', 0, 1);
    $pdf->SetX(120);
    $pdf->Cell(80, 6, 'Tel: +225 01 51 51 60 84', 0, 1);
    $pdf->SetX(120);
    $pdf->Cell(80, 6, 'Email: contact@autoworld.com', 0, 1);
    
    // Retour à la position normale pour la suite
    $pdf->SetXY(10, 70);

    // En-tête client et réservation
    $pdf->SetFont('Arial', '', 10);
    
    // Colonne de gauche
    $pdf->SetXY(10, 90);
    $pdf->Cell(95, 6, 'CLIENT:', 0, 1, 'L');
    $pdf->Cell(95, 6, ($reservation['user_nom']), 0, 1, 'L');
    $pdf->Cell(95, 6, $reservation['email'], 0, 1, 'L');
    $pdf->Cell(95, 6, 'Tel: ' . $reservation['numero'], 0, 1, 'L');
    if ($reservation['adresse']) {
        $pdf->Cell(95, 6, ($reservation['adresse']), 0, 1, 'L');
    }
    
    // Colonne de droite
    $pdf->SetXY(105, 90);
    $pdf->Cell(95, 6, 'RESERVATION N ' . str_pad($reservation['id'], 6, '0', STR_PAD_LEFT), 0, 1, 'R');
    $pdf->SetX(105);
    $pdf->Cell(95, 6, 'Date: ' . $reservation['date_creation_fr'], 0, 1, 'R');
    
    $pdf->Ln(10);

    // Tableau récapitulatif
    $pdf->SetFillColor(240, 240, 240);
    $pdf->SetFont('Arial', 'B', 10);
    
    // En-têtes du tableau
    $pdf->Cell(70, 10, 'Details', 1, 0, 'C', true);
    $pdf->Cell(30, 10, 'Jours', 1, 0, 'C', true);
    $pdf->Cell(45, 10, 'Prix/Jour', 1, 0, 'C', true);
    $pdf->Cell(45, 10, 'Total', 1, 1, 'C', true);

    // Calcul du nombre de jours
    $date1 = new DateTime($reservation['date_debut']);
    $date2 = new DateTime($reservation['date_fin']);
    $interval = $date1->diff($date2);
    $nb_jours = $interval->days;
    $prix_jour = $reservation['prix_total'] / $nb_jours;

    // Ligne de détail
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(70, 8, $reservation['marque'] . ' ' . $reservation['modele'], 1, 0);
    $pdf->Cell(30, 8, $nb_jours . ' jours', 1, 0, 'C');
    $pdf->Cell(45, 8, number_format($prix_jour, 0, ',', ' ') . ' FCFA', 1, 0, 'R');
    $pdf->Cell(45, 8, number_format($reservation['prix_total'], 0, ',', ' ') . ' FCFA', 1, 1, 'R');

    // Période de location
    $pdf->Cell(70, 8, 'Periode: ' . $reservation['date_debut_fr'] . ' au ' . $reservation['date_fin_fr'], 1, 0);
    $pdf->Cell(75, 8, 'Mode de paiement: ' . ($reservation['mode_paiement'] === 'en_ligne' ? 'En ligne' : 'A la livraison'), 1, 0);
    $pdf->Cell(45, 8, '', 1, 1);

    // Total général
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(145, 10, 'TOTAL', 1, 0, 'R');
    $pdf->Cell(45, 10, number_format($reservation['prix_total'], 0, ',', ' ') . ' FCFA', 1, 1, 'R');

    // Mentions légales
    $pdf->Ln(10);
    $pdf->SetFont('Arial', 'I', 8);
    $pdf->MultiCell(0, 4, ("Ce recu fait office de facture. Tous les montants sont exprimes en FCFA. AutoWorld vous remercie de votre confiance."));    

    // Générer le PDF
    $pdf->Output('D', 'Recu_Reservation_' . str_pad($reservation['id'], 6, '0', STR_PAD_LEFT) . '.pdf');

} catch(PDOException $e) {
    $_SESSION['error'] = "Erreur lors de la génération du reçu : " . $e->getMessage();
    header('Location: mon-compte.php');
    exit();
}
?>
