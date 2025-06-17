<!-- Sidebar -->
<div class="fixed inset-y-0 left-0 w-64 bg-white shadow-lg">
    <div class="flex flex-col h-full">
        <div class="p-4 bg-purple-600">
            <h2 class="text-white text-xl font-bold">AutoWorld Admin</h2>
        </div>
        
        <nav class="flex-1 p-4 space-y-2">
            <a href="dashboard.php" class="flex items-center p-3 text-gray-600 hover:bg-gray-100 rounded-lg <?php echo basename($_SERVER['PHP_SELF']) === 'dashboard.php' ? 'bg-gray-100' : ''; ?>">
                <i class="fas fa-tachometer-alt w-6"></i>
                <span>Dashboard</span>
            </a>

            <!-- Menu Location avec dropdown -->
            <div class="relative dropdown-menu">
                <button class="w-full flex items-center p-3 text-gray-600 hover:bg-gray-100 rounded-lg dropdown-toggle">
                    <i class="fas fa-car w-6"></i>
                    <span class="flex-1">Location</span>
                    <i class="fas fa-chevron-down ml-2 transition-transform duration-200"></i>
                </button>
                <div class="hidden absolute left-0 w-full bg-white shadow-lg rounded-lg mt-1 py-2 z-50 dropdown-content">
                    <a href="vehicules.php" class="block px-4 py-2 text-gray-600 hover:bg-gray-100 <?php echo basename($_SERVER['PHP_SELF']) === 'vehicules.php' ? 'bg-gray-100' : ''; ?>">
                        <i class="fas fa-car-side w-6"></i> Véhicules
                    </a>
                    <a href="reservations.php" class="block px-4 py-2 text-gray-600 hover:bg-gray-100 <?php echo basename($_SERVER['PHP_SELF']) === 'reservations.php' ? 'bg-gray-100' : ''; ?>">
                        <i class="fas fa-calendar-check w-6"></i> Réservations
                    </a>
                </div>
            </div>

            <!-- Menu Vente avec dropdown -->
            <div class="relative dropdown-menu">
                <button class="w-full flex items-center p-3 text-gray-600 hover:bg-gray-100 rounded-lg dropdown-toggle">
                    <i class="fas fa-tags w-6"></i>
                    <span class="flex-1">Vente</span>
                    <i class="fas fa-chevron-down ml-2 transition-transform duration-200"></i>
                </button>
                <div class="hidden absolute left-0 w-full bg-white shadow-lg rounded-lg mt-1 py-2 z-50 dropdown-content">
                    <a href="vente.php" class="block px-4 py-2 text-gray-600 hover:bg-gray-100 <?php echo basename($_SERVER['PHP_SELF']) === 'vente.php' ? 'bg-gray-100' : ''; ?>">
                        <i class="fas fa-car w-6"></i> Véhicules
                    </a>
                    <a href="demandes-essais.php" class="block px-4 py-2 text-gray-600 hover:bg-gray-100 <?php echo basename($_SERVER['PHP_SELF']) === 'demandes-essais.php' ? 'bg-gray-100' : ''; ?>">
                        <i class="fas fa-clipboard-check w-6"></i> Demandes d'essai
                    </a>
                </div>
            </div>

            <a href="users.php" class="flex items-center p-3 text-gray-600 hover:bg-gray-100 rounded-lg <?php echo basename($_SERVER['PHP_SELF']) === 'users.php' ? 'bg-gray-100' : ''; ?>">
                <i class="fas fa-users w-6"></i>
                <span>Utilisateurs</span>
            </a>

            <a href="settings.php" class="flex items-center p-3 text-gray-600 hover:bg-gray-100 rounded-lg <?php echo basename($_SERVER['PHP_SELF']) === 'settings.php' ? 'bg-gray-100' : ''; ?>">
                <i class="fas fa-cog w-6"></i>
                <span>Paramètres</span>
            </a>
        </nav>
    </div>
</div>
