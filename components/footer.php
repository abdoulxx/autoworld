<style>
.footer {
    background-color: #1a237e;
    color: white;
    padding: 3rem 0;
    margin-top: 3rem;
}
.footer h5 {
    color: #90caf9;
    margin-bottom: 1.5rem;
    font-weight: 600;
}
.footer-links {
    list-style: none;
    padding: 0;
}
.footer-links li {
    margin-bottom: 0.8rem;
}
.footer-links a {
    color: white;
    text-decoration: none;
    transition: color 0.3s ease;
}
.footer-links a:hover {
    color: #90caf9;
}
.social-links a {
    color: white;
    font-size: 1.5rem;
    margin-right: 1rem;
    transition: color 0.3s ease;
}
.social-links a:hover {
    color: #90caf9;
}
.footer-bottom {
    background-color: #151b60;
    padding: 1rem 0;
    margin-top: 2rem;
}
</style>

<footer class="footer">
    <div class="container">
        <div class="row">
            <div class="col-md-4 mb-4">
                <!-- Scripts Bootstrap et autres dépendances -->
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
                <h5>À propos d'AutoWorld</h5>
                <p>Votre partenaire de confiance pour l'achat et la location de véhicules. Nous proposons une large gamme de véhicules pour répondre à tous vos besoins.</p>
            </div>
            <div class="col-md-4 mb-4">
                <h5>Liens Rapides</h5>
                <ul class="footer-links">
                    <li><a href="acheter.php">Acheter un véhicule</a></li>
                    <li><a href="louer.php">Louer un véhicule</a></li>
                    <li><a href="connexion.php">Espace client</a></li>
                    <li><a href="#">Nos services</a></li>
                    <li><a href="#">Contact</a></li>
                </ul>
            </div>
            <div class="col-md-4 mb-4">
                <h5>Contact</h5>
                <ul class="footer-links">
                    <li><i class="fas fa-phone"></i> +1 234 567 890</li>
                    <li><i class="fas fa-envelope"></i> contact@autoworld.com</li>
                    <li><i class="fas fa-map-marker-alt"></i> 123 Rue Principale, Ville</li>
                </ul>
                <div class="social-links mt-3">
                    <a href="#"><i class="fab fa-facebook"></i></a>
                    <a href="#"><i class="fab fa-twitter"></i></a>
                    <a href="#"><i class="fab fa-instagram"></i></a>
                    <a href="#"><i class="fab fa-linkedin"></i></a>
                </div>
            </div>
        </div>
    </div>
    <div class="footer-bottom text-center">
        <div class="container">
            <p class="mb-0">&copy; <?php echo date('Y'); ?> AutoWorld. Tous droits réservés.</p>
        </div>
    </div>
</footer>
