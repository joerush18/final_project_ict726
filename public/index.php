<?php
/**
 * Home Page
 * Landing page with hero section and overview
 */

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';

$pageTitle = 'Car Service & Garage Booking Portal - Book Car Services Online';
$pageDescription = 'Book car services, oil changes, tire replacements, and more at trusted garages near you. Easy online booking system.';
include __DIR__ . '/../includes/header.php';

// Get featured garages and stats
try {
    $pdo = getDBConnection();
    $stmt = $pdo->query("SELECT g.*, u.name as owner_name 
                         FROM garages g 
                         JOIN users u ON g.owner_user_id = u.id 
                         WHERE g.status = 'active' 
                         LIMIT 3");
    $featuredGarages = $stmt->fetchAll();

    $stats = [
        'garages' => (int)$pdo->query("SELECT COUNT(*) FROM garages WHERE status = 'active'")->fetchColumn(),
        'services' => (int)$pdo->query("SELECT COUNT(*) FROM services WHERE status = 'active'")->fetchColumn(),
        'bookings' => (int)$pdo->query("SELECT COUNT(*) FROM bookings")->fetchColumn(),
        'customers' => (int)$pdo->query("SELECT COUNT(*) FROM users WHERE role = 'customer' AND status = 'active'")->fetchColumn(),
    ];

    $stmt = $pdo->query("SELECT s.*, g.name AS garage_name
                         FROM services s
                         JOIN garages g ON s.garage_id = g.id
                         WHERE s.status = 'active' AND g.status = 'active'
                         ORDER BY s.price ASC
                         LIMIT 6");
    $popularServices = $stmt->fetchAll();
} catch (PDOException $e) {
    $featuredGarages = [];
    $stats = ['garages' => 0, 'services' => 0, 'bookings' => 0, 'customers' => 0];
    $popularServices = [];
}
?>

<main class="main-content">
    <!-- Hero Section -->
    <section class="hero hero-gradient" role="banner">
        <div class="container">
            <div class="hero-content">
                <span class="hero-kicker">Trusted by drivers & garages nationwide</span>
                <h1 class="hero-title">Book Car Services Online</h1>
                <p class="hero-subtitle">Find trusted garages, compare services, and book appointments easily. Get your car serviced with convenience and confidence.</p>
                <div class="hero-actions">
                    <a href="/garages.php" class="btn btn-primary btn-large">Browse Garages</a>
                    <?php if (!isLoggedIn()): ?>
                        <a href="/register.php" class="btn btn-secondary btn-large">Get Started</a>
                    <?php endif; ?>
                </div>
                <div class="hero-highlights">
                    <div class="highlight-chip"><i class="fas fa-shield-alt"></i> Verified garages</div>
                    <div class="highlight-chip"><i class="fas fa-star"></i> Transparent pricing</div>
                    <div class="highlight-chip"><i class="fas fa-calendar-check"></i> Easy scheduling</div>
                </div>
            </div>
        </div>
    </section>

    <!-- Live Stats -->
    <section class="stats-section">
        <div class="container py-12">
            <div class="stats-grid py-12">
                <div class="stat-tile">
                    <div class="stat-number" data-count="<?php echo $stats['garages']; ?>">0</div>
                    <div class="stat-label">Active Garages</div>
                </div>
                <div class="stat-tile">
                    <div class="stat-number" data-count="<?php echo $stats['services']; ?>">0</div>
                    <div class="stat-label">Services Available</div>
                </div>
                <div class="stat-tile">
                    <div class="stat-number" data-count="<?php echo $stats['bookings']; ?>">0</div>
                    <div class="stat-label">Bookings Completed</div>
                </div>
                <div class="stat-tile">
                    <div class="stat-number" data-count="<?php echo $stats['customers']; ?>">0</div>
                    <div class="stat-label">Happy Customers</div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features-section">
        <div class="container">
            <div class="section-header">
                <span class="section-kicker">Why us</span>
                <h2 class="section-title">Why Choose Our Platform?</h2>
                <p class="section-subtitle">Modern, reliable and built for speed.</p>
            </div>
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon"><i class="fas fa-calendar-check"></i></div>
                    <h3>Easy Booking</h3>
                    <p>Book your car service appointment in just a few clicks. Choose your preferred date and time.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon"><i class="fas fa-shield-alt"></i></div>
                    <h3>Trusted Garages</h3>
                    <p>All garages are verified and reviewed. Your vehicle is in safe hands.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon"><i class="fas fa-dollar-sign"></i></div>
                    <h3>Transparent Pricing</h3>
                    <p>See service prices upfront. No hidden fees or surprises.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon"><i class="fas fa-clock"></i></div>
                    <h3>24/7 Access</h3>
                    <p>Book services anytime, anywhere. Manage your bookings from your dashboard.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Popular Services -->
    <?php if (!empty($popularServices)): ?>
    <section class="services-section">
        <div class="container">
            <div class="section-header">
                <span class="section-kicker">Popular services</span>
                <h2 class="section-title">Most Booked Services</h2>
                <p class="section-subtitle">Affordable maintenance from top garages.</p>
            </div>
            <div class="services-grid">
                <?php foreach ($popularServices as $service): ?>
                    <div class="service-card">
                        <div class="service-top">
                            <h3><?php echo htmlspecialchars($service['name']); ?></h3>
                            <span class="service-price">$<?php echo number_format($service['price'], 2); ?></span>
                        </div>
                        <?php if ($service['description']): ?>
                            <p><?php echo htmlspecialchars(substr($service['description'], 0, 90)); ?>...</p>
                        <?php endif; ?>
                        <div class="service-meta">
                            <span><i class="fas fa-clock"></i> <?php echo (int)$service['duration_minutes']; ?> min</span>
                            <span><i class="fas fa-warehouse"></i> <?php echo htmlspecialchars($service['garage_name']); ?></span>
                        </div>
                        <div class="service-footer">
                            <a href="/garage_detail.php?id=<?php echo $service['garage_id']; ?>" class="btn btn-primary btn-sm">View Garage</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- Featured Garages Section -->
    <?php if (!empty($featuredGarages)): ?>
    <section class="garages-section">
        <div class="container">
            <div class="section-header">
                <span class="section-kicker">Featured</span>
                <h2 class="section-title">Featured Garages</h2>
                <p class="section-subtitle">Hand-picked garages with consistent quality.</p>
            </div>
            <div class="garages-grid">
                <?php foreach ($featuredGarages as $garage): ?>
                    <div class="garage-card">
                        <div class="garage-header">
                            <h3><?php echo htmlspecialchars($garage['name']); ?></h3>
                            <span class="garage-status status-active">Active</span>
                        </div>
                        <div class="garage-body">
                            <p class="garage-address">
                                <i class="fas fa-map-marker-alt"></i>
                                <?php echo htmlspecialchars($garage['address']); ?>
                            </p>
                            <p class="garage-phone">
                                <i class="fas fa-phone"></i>
                                <?php echo htmlspecialchars($garage['phone']); ?>
                            </p>
                            <?php if ($garage['description']): ?>
                                <p class="garage-description"><?php echo htmlspecialchars(substr($garage['description'], 0, 100)); ?>...</p>
                            <?php endif; ?>
                        </div>
                        <div class="garage-footer">
                            <a href="/garage_detail.php?id=<?php echo $garage['id']; ?>" class="btn btn-primary">View Details</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="section-footer">
                <a href="/garages.php" class="btn btn-secondary">View All Garages</a>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- How It Works Section -->
    <section class="how-it-works">
        <div class="container">
            <div class="section-header">
                <span class="section-kicker">Simple steps</span>
                <h2 class="section-title">How It Works</h2>
                <p class="section-subtitle">From search to service in minutes.</p>
            </div>
            <div class="steps-grid">
                <div class="step-card">
                    <div class="step-number">1</div>
                    <h3>Create Account</h3>
                    <p>Sign up as a customer and add your vehicles to your profile.</p>
                </div>
                <div class="step-card">
                    <div class="step-number">2</div>
                    <h3>Browse Garages</h3>
                    <p>Search and compare garages, services, and prices in your area.</p>
                </div>
                <div class="step-card">
                    <div class="step-number">3</div>
                    <h3>Book Service</h3>
                    <p>Select a service, choose date and time, and confirm your booking.</p>
                </div>
                <div class="step-card">
                    <div class="step-number">4</div>
                    <h3>Get Service</h3>
                    <p>Visit the garage at your scheduled time and get your car serviced.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonials -->
    <section class="testimonials-section">
        <div class="container">
            <div class="section-header">
                <span class="section-kicker">Reviews</span>
                <h2 class="section-title">Drivers Love It</h2>
                <p class="section-subtitle">Real feedback from real customers.</p>
            </div>
            <div class="testimonials-grid">
                <div class="testimonial-card">
                    <p>“Booked an oil change in under a minute. The garage was professional and on time.”</p>
                    <div class="testimonial-author">
                        <span class="author-name">Maya S.</span>
                        <span class="author-meta">Toyota Camry</span>
                    </div>
                </div>
                <div class="testimonial-card">
                    <p>“Great pricing and clear service details. I love seeing everything up front.”</p>
                    <div class="testimonial-author">
                        <span class="author-name">David K.</span>
                        <span class="author-meta">Honda Civic</span>
                    </div>
                </div>
                <div class="testimonial-card">
                    <p>“The booking dashboard makes it easy to manage my vehicles and appointments.”</p>
                    <div class="testimonial-author">
                        <span class="author-name">Anita R.</span>
                        <span class="author-meta">Ford Ranger</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA -->
    <section class="cta-section">
        <div class="container">
            <div class="cta-card">
                <div>
                    <h2>Ready to book your next service?</h2>
                    <p>Find a garage and schedule in minutes.</p>
                </div>
                <div class="cta-actions">
                    <a href="/garages.php" class="btn btn-primary btn-large">Find a Garage</a>
                    <?php if (!isLoggedIn()): ?>
                        <a href="/register.php" class="btn btn-secondary btn-large">Create Account</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>
</main>

<?php include __DIR__ . '/../includes/footer.php'; ?>
