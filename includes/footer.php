    <footer class="footer bg-slate-900 text-slate-200" role="contentinfo">
        <div class="container max-w-6xl mx-auto px-4 py-12">
            <div class="footer-content grid gap-10 md:grid-cols-4">
                <div class="footer-section">
                    <h3 class="text-lg font-semibold text-white">Car Service Portal</h3>
                    <p class="text-sm text-slate-300">Your trusted platform for booking car services and garage appointments online.</p>
                </div>
                <div class="footer-section">
                    <h4 class="text-sm font-semibold uppercase tracking-wider text-slate-300">Quick Links</h4>
                    <ul class="mt-3 space-y-2 text-sm">
                        <li><a href="/index.php" class="hover:text-white">Home</a></li>
                        <li><a href="/garages.php" class="hover:text-white">Browse Garages</a></li>
                        <?php if (isLoggedIn()): ?>
                            <li><a href="/dashboard.php" class="hover:text-white">Dashboard</a></li>
                        <?php else: ?>
                            <li><a href="/login.php" class="hover:text-white">Login</a></li>
                            <li><a href="/register.php" class="hover:text-white">Register</a></li>
                        <?php endif; ?>
                    </ul>
                </div>
                <div class="footer-section">
                    <h4 class="text-sm font-semibold uppercase tracking-wider text-slate-300">Legal</h4>
                    <ul class="mt-3 space-y-2 text-sm">
                        <li><a href="/privacy.php" class="hover:text-white">Privacy Policy</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h4 class="text-sm font-semibold uppercase tracking-wider text-slate-300">Contact</h4>
                    <p class="mt-3 text-sm text-slate-300">Email: support@carserviceportal.com</p>
                    <p class="text-sm text-slate-300">Phone: +1 (555) 123-4567</p>
                </div>
            </div>
            <div class="footer-bottom mt-10 border-t border-slate-700 pt-6 text-center text-xs text-slate-400">
                <p>&copy; <?php echo date('Y'); ?> Car Service Portal. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <!-- Custom JavaScript -->
    <script src="/assets/js/main.js"></script>
</body>
</html>
