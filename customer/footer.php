    </div> <!-- End Container -->

    <!-- Shared Footer Styling -->
    <style>
        .hover-gold { transition: 0.2s ease-in-out; }
        .hover-gold:hover { color: var(--primary-gold) !important; padding-left: 4px; }
        footer a { transition: 0.2s ease-in-out; }
        footer a:hover { color: var(--primary-gold) !important; }
    </style>

    <!-- Shared Footer -->
    <footer class="mt-5 border-top" style="border-color: rgba(212, 175, 55, 0.15) !important; background: #0a0a0a; color: #fff;">
        <div class="container py-5">
            <div class="row g-4">
                <!-- About Column -->
                <div class="col-md-4">
                    <h5 class="brand-font mb-3" style="color: var(--primary-gold); font-size: 1.3rem;">SWIZY CATERING</h5>
                    <p class="text-muted small" style="line-height: 1.6;">
                        Crafting premium culinary experiences, delicious traditional and international menus, and flawless event planning. From intimate family gatherings to grand wedding celebrations, we bring taste and elegance to your table.
                    </p>
                    <div class="d-flex gap-3 mt-3">
                        <a href="#" class="text-muted" style="font-size: 1.2rem;"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="text-muted" style="font-size: 1.2rem;"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="text-muted" style="font-size: 1.2rem;"><i class="fab fa-whatsapp"></i></a>
                        <a href="#" class="text-muted" style="font-size: 1.2rem;"><i class="fab fa-youtube"></i></a>
                    </div>
                </div>
                
                <!-- Quick Links Column -->
                <div class="col-md-4">
                    <h5 class="brand-font mb-3" style="color: var(--primary-gold); font-size: 1.1rem; letter-spacing: 1px;">QUICK LINKS</h5>
                    <ul class="list-unstyled small d-flex flex-column gap-2">
                        <li><a href="dashboard.php" class="text-muted text-decoration-none hover-gold"><i class="fa fa-chevron-right me-2" style="font-size: 0.65rem; color: var(--primary-gold);"></i> Dashboard</a></li>
                        <li><a href="book.php" class="text-muted text-decoration-none hover-gold"><i class="fa fa-chevron-right me-2" style="font-size: 0.65rem; color: var(--primary-gold);"></i> Book Event</a></li>
                        <li><a href="bookings.php" class="text-muted text-decoration-none hover-gold"><i class="fa fa-chevron-right me-2" style="font-size: 0.65rem; color: var(--primary-gold);"></i> My Bookings</a></li>
                        <li><a href="reports.php" class="text-muted text-decoration-none hover-gold"><i class="fa fa-chevron-right me-2" style="font-size: 0.65rem; color: var(--primary-gold);"></i> Reports</a></li>
                        <li>
                            <button type="button" class="btn btn-link text-muted btn-sm p-0 border-0 hover-gold text-start w-100" style="text-decoration: none; font-size: 0.875rem;" onclick="showTermsModal()">
                                <i class="fa fa-chevron-right me-2" style="font-size: 0.65rem; color: var(--primary-gold);"></i> Terms & Conditions
                            </button>
                        </li>
                    </ul>
                </div>
                
                <!-- Contact Column -->
                <div class="col-md-4">
                    <h5 class="brand-font mb-3" style="color: var(--primary-gold); font-size: 1.1rem; letter-spacing: 1px;">GET IN TOUCH</h5>
                    <ul class="list-unstyled small d-flex flex-column gap-3 text-muted">
                        <li class="d-flex align-items-start">
                            <i class="fa fa-map-marker-alt me-3 mt-1" style="color: var(--primary-gold); font-size: 1rem;"></i>
                            <span>Swizy Plaza, NH-544 Bypass Junction,<br>Angamaly, Ernakulam, Kerala - 683572</span>
                        </li>
                        <li class="d-flex align-items-center">
                            <i class="fa fa-phone me-3" style="color: var(--primary-gold); font-size: 1rem;"></i>
                            <span>+91 98765 43210, +91 484 253 1122</span>
                        </li>
                        <li class="d-flex align-items-center">
                            <i class="fa fa-envelope me-3" style="color: var(--primary-gold); font-size: 1rem;"></i>
                            <span>info@swizy.com, bookings@swizy.com</span>
                        </li>
                    </ul>
                </div>
            </div>
            
            <hr class="mt-4 mb-3" style="border-color: rgba(212, 175, 55, 0.1) !important;">
            
            <div class="row align-items-center">
                <div class="col-md-6 text-center text-md-start">
                    <p class="text-muted small mb-0">&copy; <?= date('Y') ?> Swizy Catering Angamaly. All Rights Reserved.</p>
                </div>
                <div class="col-md-6 text-center text-md-end mt-2 mt-md-0">
                    <span class="text-muted small me-3">Payment Secured via</span>
                    <i class="fab fa-cc-visa text-muted fs-5 me-2" title="Visa"></i>
                    <i class="fab fa-cc-mastercard text-muted fs-5 me-2" title="Mastercard"></i>
                    <i class="fab fa-google-pay text-muted fs-4 me-2" title="Google Pay"></i>
                    <i class="fab fa-stripe text-muted fs-4" title="Stripe"></i>
                </div>
            </div>
        </div>
    </footer>

    <!-- Terms & Conditions Modal -->
    <div class="modal fade" id="termsModal" tabindex="-1" aria-labelledby="termsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content text-white" style="background: #1a1a1a; border: 1px solid var(--primary-gold); border-radius: 12px;">
                <div class="modal-header border-bottom-0 pb-0" style="border-bottom: 1px solid rgba(212, 175, 55, 0.2) !important; padding: 15px 20px;">
                    <h5 class="modal-title brand-font text-gold fs-4" id="termsModalLabel" style="color: var(--primary-gold);">
                        <i class="fa fa-file-contract me-2"></i> Terms & Conditions
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body py-4 px-4" style="max-height: 400px; overflow-y: auto;">
                    <h6 class="text-gold mb-2" style="color: var(--primary-gold);">1. Booking & Reservations</h6>
                    <p class="small text-muted mb-4">All event bookings must be made at least 3 days in advance and no more than 1 year prior to the scheduled event date. Bookings are subject to staff and schedule availability.</p>

                    <h6 class="text-gold mb-2" style="color: var(--primary-gold);">2. Payment Terms</h6>
                    <p class="small text-muted mb-4">To confirm a booking, an advance payment of 50% of the total estimated amount must be paid. The remaining balance must be paid fully prior to or on the day of the event.</p>

                    <h6 class="text-gold mb-2" style="color: var(--primary-gold);">3. Cancellation Policy</h6>
                    <p class="small text-muted mb-4">Cancellations must be made at least 3 days prior to the event date. In the event of a cancellation for a paid booking, a 5% administrative fee will be deducted from the advance amount, and the remaining 95% will be refunded to the customer.</p>

                    <h6 class="text-gold mb-2" style="color: var(--primary-gold);">4. Staff Allocation & Rules</h6>
                    <p class="small text-muted mb-4">Staff members (Chefs, Servers, Site Managers) are automatically allocated based on guest count capacity rules. Any special instructions or custom dietary requirements must be filed during booking.</p>

                    <h6 class="text-gold mb-2" style="color: var(--primary-gold);">5. Service Limitations</h6>
                    <p class="small text-muted mb-0">Swizy Catering reserves the right to decline services on specific blocked dates due to holidays, severe weather, or total staffing capacity constraints.</p>
                </div>
                <div class="modal-footer border-top-0 pt-0" style="border-top: 1px solid rgba(212, 175, 55, 0.2) !important; padding: 15px 20px;">
                    <button type="button" class="btn btn-sm btn-gold" data-bs-dismiss="modal">I Understand</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function showTermsModal() {
            var myModal = new bootstrap.Modal(document.getElementById('termsModal'));
            myModal.show();
        }

        // Active link script
        const currentLocation = location.href;
        const menuItem = document.querySelectorAll('.nav-link');
        const menuLength = menuItem.length;
        for (let i = 0; i < menuLength; i++) {
            if (menuItem[i].href === currentLocation) {
                menuItem[i].classList.add("active");
            }
        }
    </script>
</body>
</html>
