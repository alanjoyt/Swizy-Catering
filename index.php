<?php
require_once('config/autoload.php');
$dao = new DataAccess();

// Fetch 5-star feedbacks
$feedbacks = $dao->getDataJoin(
    array("reviews.*", "users.name as customer_name"),
    "reviews",
    "reviews.rating >= 4 ORDER BY reviews.id DESC LIMIT 6",
    array("users" => array("reviews.user_id=users.id", "innerjoin"))
);

// Fetch gallery images
$gallery = $dao->getData("*", "media", "section='gallery' ORDER BY id DESC LIMIT 8");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Swizy - Sweet and Spicy</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <style>
        
        .hero { height: 100vh; display: flex; align-items: center; position: relative; overflow: hidden; background: linear-gradient(rgba(0, 0, 0, 0.8), rgba(0, 0, 0, 0.8)), url('assets/images/hero-bg.jpg') center/cover; }
        .hero-title { font-size: 4.5rem; font-weight: 700; color: var(--primary-gold); margin-bottom: 20px; text-shadow: 2px 2px 4px rgba(0,0,0,0.5); }
        .hero-subtitle { font-size: 1.5rem; margin-bottom: 40px; letter-spacing: 2px; }
        
        /* Cooking Animations */
        .steam { position: absolute; background: white; border-radius: 50%; filter: blur(8px); opacity: 0; animation: rise 4s infinite ease-in; }
        @keyframes rise { 
            0% { transform: translateY(0) scale(1) translateX(0); opacity: 0; } 
            20% { opacity: 0.5; }
            50% { transform: translateY(-100px) scale(2) translateX(20px); opacity: 0.3; }
            100% { transform: translateY(-200px) scale(3) translateX(-20px); opacity: 0; } 
        }
        .spice { position: absolute; background: var(--primary-gold); border-radius: 50%; opacity: 0.8; animation: floatSpice 8s infinite ease-in-out; }
        @keyframes floatSpice { 
            0% { transform: translateY(0) rotate(0deg); opacity: 0; } 
            10% { opacity: 0.8; }
            50% { transform: translateY(-300px) rotate(180deg) translateX(50px); opacity: 0.8; } 
            90% { opacity: 0.8; }
            100% { transform: translateY(-600px) rotate(360deg) translateX(-50px); opacity: 0; } 
        }

        .section-title { color: var(--primary-gold); font-size: 2.5rem; margin-bottom: 1rem; text-align: center; position: relative; display: inline-block; }
        .section-title::after { content: ''; position: absolute; width: 50px; height: 3px; background: var(--primary-gold); bottom: -10px; left: 50%; transform: translateX(-50%); }
        .section-subtitle { text-align: center; color: var(--text-muted); margin-bottom: 3rem; margin-top: 1.5rem; font-size: 1.1rem; }
        
        /* Gallery Styles */
        .gallery-item { position: relative; overflow: hidden; border-radius: 10px; cursor: pointer; transition: var(--transition); border: 2px solid transparent; }
        .gallery-item:hover { border-color: var(--primary-gold); transform: scale(1.02); }
        .gallery-img { width: 100%; height: 250px; object-fit: cover; transition: var(--transition); }
        .gallery-item:hover .gallery-img { transform: scale(1.1); filter: brightness(0.6); }
        .gallery-overlay { position: absolute; inset: 0; display: flex; flex-direction: column; justify-content: center; align-items: center; opacity: 0; transition: var(--transition); }
        .gallery-item:hover .gallery-overlay { opacity: 1; }
        
        /* Feedback Styles */
        .testimonial-card { background: rgba(20,20,20,0.8); border-left: 4px solid var(--primary-gold); padding: 2rem; border-radius: 5px; height: 100%; }
        
        footer { background: #050505; border-top: 1px solid rgba(212, 175, 55, 0.2); padding: 50px 0 20px; }
        .footer-logo { color: var(--primary-gold); font-size: 2rem; font-weight: 700; font-family: 'Playfair Display', serif; }
        .stat-item h3 { font-size: 3rem; color: var(--primary-gold); }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg fixed-top py-3">
        <div class="container">
            <a class="navbar-brand brand-font" href="index.php"><i class="fa fa-utensils me-2 fs-4"></i>SWIZY</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="fa fa-bars text-gold" style="color: #d4af37;"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav mx-auto">
                    <li class="nav-item"><a class="nav-link active" href="#home">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="#services">Services</a></li>
                    <li class="nav-item"><a class="nav-link" href="#menu">Smart Menu</a></li>
                    <li class="nav-item"><a class="nav-link" href="#gallery">Gallery</a></li>
                    <li class="nav-item"><a class="nav-link" href="#reviews">Reviews</a></li>
                </ul>
                <div class="d-flex gap-3">
                    <a href="login.php" class="btn btn-outline-gold">Login</a>
                    <a href="register.php" class="btn btn-gold">Book Now</a>
                </div>
            </div>
        </div>
    </nav>
    
    <section id="home" class="hero">
        <div id="cooking-animations"></div>
        <div class="container text-center position-relative" style="z-index: 1;">
            <div data-aos="fade-down" data-aos-duration="1500" class="mb-3">
                <i class="fa fa-fire fa-3x" style="color:var(--primary-gold); animation: pulse 2s infinite;"></i>
            </div>
            <h1 class="hero-title" data-aos="fade-down" data-aos-duration="1500" data-aos-delay="200">SWIZY Catering</h1>
            <p class="hero-subtitle brand-font" data-aos="fade-up" data-aos-duration="1500" data-aos-delay="400">Sweet and Spicy - A Symphony of Flavors</p>
            <div data-aos="zoom-in" data-aos-duration="1500" data-aos-delay="600">
                <a href="#menu" class="btn btn-gold btn-lg mx-2 my-2">Explore Menu</a>
                <a href="register.php" class="btn btn-outline-gold btn-lg mx-2 my-2">Plan Your Event</a>
            </div>
        </div>
    </section>

    <section id="services" class="py-5 mt-5">
        <div class="container">
            <div class="text-center"><h2 class="section-title" data-aos="fade-up">Our Premium Services</h2></div>
            <p class="section-subtitle" data-aos="fade-up" data-aos-delay="100">Elevating your events with unparalleled culinary experiences</p>
            <div class="row g-4 mt-2">
                <div class="col-md-4" data-aos="fade-up" data-aos-delay="200">
                    <div class="glass-card p-4 text-center h-100">
                        <i class="fa-solid fa-ring fa-3x mb-3" style="color:var(--primary-gold)"></i>
                        <h4 class="brand-font text-white">Wedding Catering</h4>
                        <p class="text-muted mt-3">Luxurious spreads tailored for your perfect day, ensuring every guest leaves with a smile.</p>
                    </div>
                </div>
                <div class="col-md-4" data-aos="fade-up" data-aos-delay="400">
                    <div class="glass-card p-4 text-center h-100">
                        <i class="fa-solid fa-briefcase fa-3x mb-3" style="color:var(--primary-gold)"></i>
                        <h4 class="brand-font text-white">Corporate Events</h4>
                        <p class="text-muted mt-3">Professional and sophisticated catering solutions to impress your clients and team.</p>
                    </div>
                </div>
                <div class="col-md-4" data-aos="fade-up" data-aos-delay="600">
                    <div class="glass-card p-4 text-center h-100">
                        <i class="fa-solid fa-glass-cheers fa-3x mb-3" style="color:var(--primary-gold)"></i>
                        <h4 class="brand-font text-white">Private Parties</h4>
                        <p class="text-muted mt-3">Intimate gatherings made special with our bespoke menu offerings and impeccable service.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="menu" class="py-5" style="background: rgba(212, 175, 55, 0.03);">
        <div class="container py-5">
            <div class="row align-items-center">
                <div class="col-lg-6" data-aos="fade-right">
                    <h2 class="section-title text-start">Smart AI Menu Recommendation</h2>
                    <p class="text-light mt-4" style="line-height: 1.8;">
                        Unsure of what to serve? Our intelligent recommendation engine analyzes your event type, guest count, and preferences to suggest the perfect culinary pairings. From traditional feasts to modern fusion, we design a menu that hits all the right notes.
                    </p>
                    <ul class="list-unstyled mt-4 text-muted">
                        <li class="mb-2"><i class="fa fa-check me-2" style="color:var(--primary-gold)"></i> Tailored to your exact budget</li>
                        <li class="mb-2"><i class="fa fa-check me-2" style="color:var(--primary-gold)"></i> Veg and Non-veg specific optimizations</li>
                        <li class="mb-2"><i class="fa fa-check me-2" style="color:var(--primary-gold)"></i> Seasonal ingredient suggestions</li>
                    </ul>
                    <a href="smart_menu.php" class="btn btn-gold mt-4">Try Smart Recommendation</a>
                </div>
                <div class="col-lg-6" data-aos="fade-left">
                    <div class="glass-card p-4 position-relative mt-4 mt-lg-0">
                        <div class="text-center p-3 border border-warning rounded" style="border-color: rgba(212,175,55,0.3) !important;">
                            <h4 class="brand-font mb-3" style="color:var(--primary-gold)"><i class="fa fa-magic me-2"></i>Quick Suggestion</h4>
                            <div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2" style="border-color: rgba(255,255,255,0.1) !important;">
                                <span>Event Type:</span> <span class="text-white">Wedding</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2" style="border-color: rgba(255,255,255,0.1) !important;">
                                <span>Guests:</span> <span class="text-white">500+</span>
                            </div>
                            <div class="mt-4">
                                <h5 class="text-white">Recommended: <span style="color:var(--primary-gold)">The Grand Royal Feast</span></h5>
                                <p class="small text-muted">A luxurious 15-course spread featuring exotic spices, live counters, and premium desserts.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- NEW: Gallery Section -->
    <section id="gallery" class="py-5">
        <div class="container">
            <div class="text-center"><h2 class="section-title" data-aos="fade-up">Culinary Gallery</h2></div>
            <p class="section-subtitle" data-aos="fade-up" data-aos-delay="100">A glimpse into our beautifully crafted culinary creations.</p>
            
            <div class="row g-4 mt-2">
                <?php if(!empty($gallery)): foreach($gallery as $img): ?>
                <div class="col-md-3 col-sm-6" data-aos="zoom-in" data-aos-delay="200">
                    <div class="gallery-item shadow">
                        <img src="uploads/<?= $img['media_path'] ?>" class="gallery-img" alt="<?= htmlspecialchars($img['title']) ?>" onerror="this.src='https://images.unsplash.com/photo-1555244162-803834f70033?w=500&auto=format&fit=crop&q=60'">
                        <div class="gallery-overlay">
                            <h5 class="brand-font text-white text-center px-2"><?= htmlspecialchars($img['title']) ?></h5>
                            <i class="fa fa-search-plus fa-2x text-gold mt-2" style="color:var(--primary-gold)"></i>
                        </div>
                    </div>
                </div>
                <?php endforeach; else: ?>
                <div class="col-12 text-center text-muted">
                    <p>Our gallery is currently being updated with fresh dishes!</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- NEW: Feedback & Reviews Section -->
    <section id="reviews" class="py-5" style="background: rgba(212, 175, 55, 0.03);">
        <div class="container py-4">
            <div class="text-center"><h2 class="section-title" data-aos="fade-up">Client Testimonials</h2></div>
            <p class="section-subtitle" data-aos="fade-up" data-aos-delay="100">Don't just take our word for it. Hear from our satisfied clients.</p>
            
            <div class="row g-4 mt-2">
                <?php if(!empty($feedbacks)): foreach($feedbacks as $fb): ?>
                <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="200">
                    <div class="testimonial-card">
                        <div class="d-flex justify-content-between mb-3">
                            <div>
                                <h6 class="text-white mb-0"><?= htmlspecialchars($fb['customer_name']) ?></h6>
                            </div>
                            <div style="color:var(--primary-gold)">
                                <?php for($i=1; $i<=5; $i++): ?>
                                    <i class="fa fa-star <?= $i <= $fb['rating'] ? '' : 'text-secondary' ?>"></i>
                                <?php endfor; ?>
                            </div>
                        </div>
                        <p class="text-muted fst-italic mb-0">"<?= htmlspecialchars($fb['review']) ?>"</p>
                    </div>
                </div>
                <?php endforeach; else: ?>
                <div class="col-12 text-center text-muted">
                    <p>Be the first to leave a review after your event!</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <section class="py-5 border-top border-bottom" style="border-color: rgba(212, 175, 55, 0.2) !important; background:var(--dark-bg);">
        <div class="container">
            <div class="row text-center g-4">
                <div class="col-md-3 stat-item" data-aos="zoom-in" data-aos-delay="100">
                    <h3 class="counter brand-font" data-target="500">0</h3>
                    <p class="text-uppercase text-muted" style="letter-spacing: 1px;">Events Hosted</p>
                </div>
                <div class="col-md-3 stat-item" data-aos="zoom-in" data-aos-delay="200">
                    <h3 class="counter brand-font" data-target="50">0</h3>
                    <p class="text-uppercase text-muted" style="letter-spacing: 1px;">Master Chefs</p>
                </div>
                <div class="col-md-3 stat-item" data-aos="zoom-in" data-aos-delay="300">
                    <h3 class="counter brand-font" data-target="150">0</h3>
                    <p class="text-uppercase text-muted" style="letter-spacing: 1px;">Menu Items</p>
                </div>
                <div class="col-md-3 stat-item" data-aos="zoom-in" data-aos-delay="400">
                    <h3 class="brand-font">100%</h3>
                    <p class="text-uppercase text-muted" style="letter-spacing: 1px;">Satisfaction</p>
                </div>
            </div>
        </div>
    </section>

    <footer class="mt-0">
        <div class="container">
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="footer-logo mb-3">SWIZY</div>
                    <p class="text-muted">Sweet and Spicy - Delivering premium culinary experiences for your unforgettable moments.</p>
                    <div class="d-flex gap-3 mt-4">
                        <a href="#" class="text-muted"><i class="fab fa-instagram fa-lg"></i></a>
                        <a href="#" class="text-muted"><i class="fab fa-facebook fa-lg"></i></a>
                        <a href="#" class="text-muted"><i class="fab fa-youtube fa-lg"></i></a>
                    </div>
                </div>
                <div class="col-md-4">
                    <h5 class="text-white mb-4">Quick Links</h5>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="#home" class="text-muted text-decoration-none">Home</a></li>
                        <li class="mb-2"><a href="#services" class="text-muted text-decoration-none">Services</a></li>
                        <li class="mb-2"><a href="#menu" class="text-muted text-decoration-none">Menu</a></li>
                        <li class="mb-2"><a href="login.php" class="text-muted text-decoration-none">Login</a></li>
                    </ul>
                </div>
                <div class="col-md-4">
                    <h5 class="text-white mb-4">Contact Us</h5>
                    <p class="text-muted mb-2"><i class="fa fa-map-marker-alt me-2" style="color:var(--primary-gold)"></i> Swizy Plaza, NH-544 Bypass Junction, Angamaly, Ernakulam, Kerala - 683572</p>
                    <p class="text-muted mb-2"><i class="fa fa-phone me-2" style="color:var(--primary-gold)"></i> +91 98765 43210</p>
                    <p class="text-muted mb-2"><i class="fa fa-envelope me-2" style="color:var(--primary-gold)"></i> info@swizy.com</p>
                </div>
            </div>
            <div class="text-center text-muted mt-5 pt-3 border-top" style="border-color: rgba(255,255,255,0.1) !important;">
                <p>&copy; 2026 Swizy Catering. All Rights Reserved.</p>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        AOS.init({ once: true, offset: 100 });
        
        // Navbar Scrolled Effect
        window.addEventListener('scroll', function() {
            if (window.scrollY > 50) {
                document.querySelector('.navbar').style.background = 'rgba(5, 5, 5, 0.98)';
                document.querySelector('.navbar').style.padding = '10px 0';
            } else {
                document.querySelector('.navbar').style.background = 'rgba(10, 10, 10, 0.95)';
                document.querySelector('.navbar').style.padding = '15px 0';
            }
        });
        
        // Counters
        const counters = document.querySelectorAll('.counter');
        const speed = 200;
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const counter = entry.target;
                    const target = +counter.getAttribute('data-target');
                    const updateCount = () => {
                        const count = +counter.innerText;
                        const inc = target / speed;
                        if (count < target) {
                            counter.innerText = Math.ceil(count + inc);
                            setTimeout(updateCount, 20);
                        } else {
                            counter.innerText = target + "+";
                        }
                    };
                    updateCount();
                    observer.unobserve(counter);
                }
            });
        });
        counters.forEach(counter => { observer.observe(counter); });

        // CSS Cooking Animations - Spices & Steam
        const container = document.getElementById('cooking-animations');
        
        // Create Steam
        for (let i = 0; i < 15; i++) {
            let steam = document.createElement('div');
            steam.classList.add('steam');
            steam.style.width = Math.random() * 40 + 20 + 'px';
            steam.style.height = steam.style.width;
            steam.style.left = Math.random() * 100 + 'vw';
            steam.style.bottom = '-50px';
            steam.style.animationDuration = Math.random() * 3 + 3 + 's';
            steam.style.animationDelay = Math.random() * 3 + 's';
            container.appendChild(steam);
        }

        // Create Spices
        for (let i = 0; i < 30; i++) {
            let spice = document.createElement('div');
            spice.classList.add('spice');
            let size = Math.random() * 4 + 2;
            spice.style.width = size + 'px';
            spice.style.height = size + 'px';
            spice.style.left = Math.random() * 100 + 'vw';
            spice.style.bottom = '-10px';
            spice.style.animationDuration = Math.random() * 6 + 4 + 's';
            spice.style.animationDelay = Math.random() * 5 + 's';
            // Randomly color some spices orange or red
            if(Math.random() > 0.7) spice.style.background = '#ff4500'; // Chili powder
            else if(Math.random() > 0.4) spice.style.background = '#ff8c00'; // Turmeric/Saffron
            container.appendChild(spice);
        }
    </script>
</body>
</html>
