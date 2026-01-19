<?php
/**
 * Privacy Policy Page
 * Describes data collection and usage policies
 */

require_once __DIR__ . '/../includes/auth.php';

$pageTitle = 'Privacy Policy - Car Service Portal';
$pageDescription = 'Privacy policy and data protection information for Car Service Portal';
include __DIR__ . '/../includes/header.php';
?>

<main class="main-content py-12">
    <div class="container max-w-4xl mx-auto px-4">
        <div class="page-header text-center">
            <h1 class="page-title text-3xl font-semibold text-slate-900">Privacy Policy</h1>
            <p class="page-subtitle text-sm text-slate-500">Last updated: <?php echo date('F j, Y'); ?></p>
        </div>

        <div class="content-section rounded-2xl border border-slate-200 bg-white p-8 shadow-sm">
            <section class="privacy-section">
                <h2 class="text-xl font-semibold text-slate-900">1. Information We Collect</h2>
                <p class="mt-3 text-sm text-slate-600">Car Service Portal collects the following information to provide our booking services:</p>
                <ul class="mt-3 list-disc space-y-2 pl-5 text-sm text-slate-600">
                    <li><strong>Personal Information:</strong> Name, email address, phone number (for garage owners)</li>
                    <li><strong>Account Information:</strong> User credentials (password stored securely using hashing)</li>
                    <li><strong>Vehicle Information:</strong> Make, model, year, license plate, and color of your vehicles</li>
                    <li><strong>Booking Information:</strong> Service bookings, dates, times, and any notes you provide</li>
                    <li><strong>Usage Data:</strong> Information about how you interact with our platform</li>
                </ul>
            </section>

            <section class="privacy-section mt-8">
                <h2 class="text-xl font-semibold text-slate-900">2. How We Use Your Information</h2>
                <p class="mt-3 text-sm text-slate-600">We use the collected information for the following purposes:</p>
                <ul class="mt-3 list-disc space-y-2 pl-5 text-sm text-slate-600">
                    <li>To create and manage your user account</li>
                    <li>To process and manage service bookings</li>
                    <li>To facilitate communication between customers and garages</li>
                    <li>To improve our services and user experience</li>
                    <li>To send important notifications about your bookings</li>
                    <li>To comply with legal obligations</li>
                </ul>
            </section>

            <section class="privacy-section mt-8">
                <h2 class="text-xl font-semibold text-slate-900">3. Data Protection</h2>
                <p class="mt-3 text-sm text-slate-600">We implement appropriate security measures to protect your personal information:</p>
                <ul class="mt-3 list-disc space-y-2 pl-5 text-sm text-slate-600">
                    <li>Passwords are hashed using secure algorithms (password_hash with bcrypt)</li>
                    <li>Database queries use prepared statements to prevent SQL injection</li>
                    <li>Session management with secure session handling</li>
                    <li>Access controls based on user roles</li>
                </ul>
            </section>

            <section class="privacy-section mt-8">
                <h2 class="text-xl font-semibold text-slate-900">4. Data Sharing</h2>
                <p class="mt-3 text-sm text-slate-600">We do not sell your personal information. We may share information in the following circumstances:</p>
                <ul class="mt-3 list-disc space-y-2 pl-5 text-sm text-slate-600">
                    <li>With garage owners: Your booking information and vehicle details are shared with the garage you book with</li>
                    <li>Service providers: We may use third-party services for hosting and database management</li>
                    <li>Legal requirements: When required by law or to protect our rights</li>
                </ul>
            </section>

            <section class="privacy-section mt-8">
                <h2 class="text-xl font-semibold text-slate-900">5. Your Rights</h2>
                <p class="mt-3 text-sm text-slate-600">You have the right to:</p>
                <ul class="mt-3 list-disc space-y-2 pl-5 text-sm text-slate-600">
                    <li>Access your personal information</li>
                    <li>Update or correct your information through your dashboard</li>
                    <li>Delete your account (contact support)</li>
                    <li>Request a copy of your data</li>
                </ul>
            </section>

            <section class="privacy-section mt-8">
                <h2 class="text-xl font-semibold text-slate-900">6. Cookies and Sessions</h2>
                <p class="mt-3 text-sm text-slate-600">We use PHP sessions to maintain your login state. Session data is stored securely on the server and is used only for authentication and authorization purposes.</p>
            </section>

            <section class="privacy-section mt-8">
                <h2 class="text-xl font-semibold text-slate-900">7. Contact Us</h2>
                <p class="mt-3 text-sm text-slate-600">If you have questions about this privacy policy or wish to exercise your rights, please contact us:</p>
                <ul class="mt-3 list-disc space-y-2 pl-5 text-sm text-slate-600">
                    <li>Email: support@carserviceportal.com</li>
                    <li>Phone: +1 (555) 123-4567</li>
                </ul>
            </section>

            <section class="privacy-section mt-8">
                <h2 class="text-xl font-semibold text-slate-900">8. Changes to This Policy</h2>
                <p class="mt-3 text-sm text-slate-600">We may update this privacy policy from time to time. We will notify users of any significant changes by posting the new policy on this page and updating the "Last updated" date.</p>
            </section>
        </div>
    </div>
</main>

<?php include __DIR__ . '/../includes/footer.php'; ?>
