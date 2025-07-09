<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>DesignHub - Premium Freelance Design Platform</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }
    
    body {
      font-family: 'Poppins', sans-serif;
      overflow-x: hidden;
    }
    
    /* Gradient Background */
    .gradient-bg {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      min-height: 100vh;
      position: relative;
    }
    
    .gradient-bg::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 1000"><polygon fill="rgba(255,255,255,0.1)" points="0,1000 1000,0 1000,1000"/></svg>');
      background-size: cover;
    }
    
    /* Navbar */
    .navbar {
      background: rgba(255, 255, 255, 0.1) !important;
      backdrop-filter: blur(10px);
      border-bottom: 1px solid rgba(255, 255, 255, 0.2);
      transition: all 0.3s ease;
    }
    
    .navbar-brand {
      font-weight: 700;
      font-size: 1.8rem;
      color: white !important;
    }
    
    .nav-link {
      color: white !important;
      font-weight: 500;
      margin: 0 10px;
      transition: all 0.3s ease;
      position: relative;
    }
    
    .nav-link:hover {
      color: #ffd700 !important;
      transform: translateY(-2px);
    }
    
    .nav-link::after {
      content: '';
      position: absolute;
      bottom: -5px;
      left: 50%;
      width: 0;
      height: 2px;
      background: #ffd700;
      transition: all 0.3s ease;
      transform: translateX(-50%);
    }
    
    .nav-link:hover::after {
      width: 100%;
    }
    
    /* Hero Section */
    .hero {
      padding: 120px 0 80px;
      text-align: center;
      position: relative;
      z-index: 2;
    }
    
    .hero h1 {
      font-size: 4rem;
      font-weight: 800;
      color: white;
      margin-bottom: 20px;
      animation: fadeInUp 1s ease;
    }
    
    .hero p {
      font-size: 1.3rem;
      color: rgba(255, 255, 255, 0.9);
      margin-bottom: 40px;
      animation: fadeInUp 1s ease 0.2s both;
    }
    
    .hero-buttons {
      animation: fadeInUp 1s ease 0.4s both;
    }
    
    .btn-hero {
      padding: 15px 40px;
      font-size: 1.1rem;
      font-weight: 600;
      border-radius: 50px;
      margin: 0 10px;
      transition: all 0.3s ease;
      text-decoration: none;
      display: inline-block;
    }
    
    .btn-primary-hero {
      background: linear-gradient(45deg, #ff6b6b, #ffd93d);
      border: none;
      color: white;
      box-shadow: 0 10px 30px rgba(255, 107, 107, 0.3);
    }
    
    .btn-primary-hero:hover {
      transform: translateY(-3px);
      box-shadow: 0 15px 40px rgba(255, 107, 107, 0.4);
      color: white;
    }
    
    .btn-outline-hero {
      background: transparent;
      border: 2px solid white;
      color: white;
    }
    
    .btn-outline-hero:hover {
      background: white;
      color: #667eea;
      transform: translateY(-3px);
    }
    
    /* Features Section */
    .features {
      padding: 100px 0;
      background: white;
    }
    
    .section-title {
      text-align: center;
      margin-bottom: 80px;
    }
    
    .section-title h2 {
      font-size: 3rem;
      font-weight: 700;
      color: #333;
      margin-bottom: 20px;
    }
    
    .section-title p {
      font-size: 1.2rem;
      color: #666;
      max-width: 600px;
      margin: 0 auto;
    }
    
    .feature-card {
      text-align: center;
      padding: 40px 30px;
      border-radius: 20px;
      background: white;
      box-shadow: 0 20px 60px rgba(0, 0, 0, 0.1);
      margin-bottom: 30px;
      transition: all 0.3s ease;
      position: relative;
      overflow: hidden;
    }
    
    .feature-card::before {
      content: '';
      position: absolute;
      top: 0;
      left: -100%;
      width: 100%;
      height: 100%;
      background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.4), transparent);
      transition: left 0.5s;
    }
    
    .feature-card:hover::before {
      left: 100%;
    }
    
    .feature-card:hover {
      transform: translateY(-10px);
      box-shadow: 0 30px 80px rgba(0, 0, 0, 0.15);
    }
    
    .feature-icon {
      width: 80px;
      height: 80px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 0 auto 30px;
      font-size: 2rem;
      color: white;
      transition: all 0.3s ease;
    }
    
    .feature-card:hover .feature-icon {
      transform: scale(1.1) rotate(5deg);
    }
    
    .icon-1 { background: linear-gradient(45deg, #ff6b6b, #ff8e8e); }
    .icon-2 { background: linear-gradient(45deg, #4ecdc4, #44a08d); }
    .icon-3 { background: linear-gradient(45deg, #45b7d1, #96c93d); }
    .icon-4 { background: linear-gradient(45deg, #f093fb, #f5576c); }
    .icon-5 { background: linear-gradient(45deg, #4facfe, #00f2fe); }
    .icon-6 { background: linear-gradient(45deg, #43e97b, #38f9d7); }
    
    .feature-card h3 {
      font-size: 1.5rem;
      font-weight: 600;
      color: #333;
      margin-bottom: 15px;
    }
    
    .feature-card p {
      color: #666;
      line-height: 1.6;
    }
    
    /* Testimonials */
    .testimonials {
      padding: 100px 0;
      background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
      color: white;
    }
    
    .testimonial-card {
      background: rgba(255, 255, 255, 0.1);
      backdrop-filter: blur(10px);
      border-radius: 20px;
      padding: 40px;
      margin-bottom: 30px;
      text-align: center;
      border: 1px solid rgba(255, 255, 255, 0.2);
      transition: all 0.3s ease;
    }
    
    .testimonial-card:hover {
      transform: translateY(-5px);
      background: rgba(255, 255, 255, 0.15);
    }
    
    .testimonial-avatar {
      width: 80px;
      height: 80px;
      border-radius: 50%;
      margin: 0 auto 20px;
      background: linear-gradient(45deg, #ffd700, #ffed4e);
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 2rem;
      color: white;
    }
    
    .testimonial-text {
      font-style: italic;
      margin-bottom: 20px;
      font-size: 1.1rem;
    }
    
    .testimonial-author {
      font-weight: 600;
      font-size: 1.1rem;
    }
    
    /* CTA Section */
    .cta {
      padding: 100px 0;
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      text-align: center;
      color: white;
    }
    
    .cta h2 {
      font-size: 3rem;
      font-weight: 700;
      margin-bottom: 20px;
    }
    
    .cta p {
      font-size: 1.2rem;
      margin-bottom: 40px;
      opacity: 0.9;
    }
    
    /* Footer */
    .footer {
      background: #1a1a1a;
      color: white;
      padding: 60px 0 30px;
    }
    
    .footer h5 {
      color: #ffd700;
      margin-bottom: 20px;
    }
    
    .footer a {
      color: #ccc;
      text-decoration: none;
      transition: color 0.3s ease;
    }
    
    .footer a:hover {
      color: #ffd700;
    }
    
    .social-links a {
      display: inline-block;
      width: 40px;
      height: 40px;
      background: #333;
      border-radius: 50%;
      text-align: center;
      line-height: 40px;
      margin-right: 10px;
      transition: all 0.3s ease;
    }
    
    .social-links a:hover {
      background: #ffd700;
      color: #333;
      transform: translateY(-3px);
    }
    
    /* Animations */
    @keyframes fadeInUp {
      from {
        opacity: 0;
        transform: translateY(30px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }
    
    @keyframes float {
      0%, 100% { transform: translateY(0px); }
      50% { transform: translateY(-20px); }
    }
    
    .floating {
      animation: float 6s ease-in-out infinite;
    }
    
    /* Responsive */
    @media (max-width: 768px) {
      .hero h1 {
        font-size: 2.5rem;
      }
      
      .hero p {
        font-size: 1.1rem;
      }
      
      .section-title h2 {
        font-size: 2rem;
      }
      
      .btn-hero {
        display: block;
        margin: 10px auto;
        max-width: 250px;
      }
    }
    
    /* Scroll animations */
    .animate-on-scroll {
      opacity: 0;
      transform: translateY(30px);
      transition: all 0.6s ease;
    }
    
    .animate-on-scroll.animated {
      opacity: 1;
      transform: translateY(0);
    }
  </style>
</head>
<body>
  <!-- Navigation -->
  <nav class="navbar navbar-expand-lg fixed-top">
    <div class="container">
      <a class="navbar-brand" href="#">
        <i class="fas fa-palette me-2"></i>DesignHub
      </a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav ms-auto">
          <li class="nav-item">
            <a class="nav-link" href="#features">Features</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="#testimonials">Testimonials</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="login.php">Login</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="register.php">Register</a>
          </li>
        </ul>
      </div>
    </div>
  </nav>

  <!-- Hero Section -->
  <section class="gradient-bg">
    <div class="container">
      <div class="hero">
        <h1>Transform Your Ideas Into Reality</h1>
        <p>Connect with talented designers and bring your creative vision to life. From logos to websites, we've got you covered.</p>
        <div class="hero-buttons">
          <a href="register.php" class="btn-hero btn-primary-hero">
            <i class="fas fa-rocket me-2"></i>Get Started
          </a>
          <a href="#features" class="btn-hero btn-outline-hero">
            <i class="fas fa-play me-2"></i>Learn More
          </a>
        </div>
      </div>
    </div>
  </section>

  <!-- Features Section -->
  <section id="features" class="features">
    <div class="container">
      <div class="section-title animate-on-scroll">
        <h2>Why Choose DesignHub?</h2>
        <p>Discover the perfect blend of creativity, quality, and convenience</p>
      </div>
      <div class="row">
        <div class="col-lg-4 col-md-6">
          <div class="feature-card animate-on-scroll">
            <div class="feature-icon icon-1">
              <i class="fas fa-users"></i>
            </div>
            <h3>Expert Designers</h3>
            <p>Connect with verified, talented designers from around the world who specialize in your project needs.</p>
          </div>
        </div>
        <div class="col-lg-4 col-md-6">
          <div class="feature-card animate-on-scroll">
            <div class="feature-icon icon-2">
              <i class="fas fa-shield-alt"></i>
            </div>
            <h3>Secure & Safe</h3>
            <p>Your projects and payments are protected with industry-leading security and escrow services.</p>
          </div>
        </div>
        <div class="col-lg-4 col-md-6">
          <div class="feature-card animate-on-scroll">
            <div class="feature-icon icon-3">
              <i class="fas fa-clock"></i>
            </div>
            <h3>Fast Delivery</h3>
            <p>Get your designs delivered on time with our efficient project management and communication tools.</p>
          </div>
        </div>
        <div class="col-lg-4 col-md-6">
          <div class="feature-card animate-on-scroll">
            <div class="feature-icon icon-4">
              <i class="fas fa-star"></i>
            </div>
            <h3>Quality Guaranteed</h3>
            <p>Every project comes with quality assurance and unlimited revisions until you're 100% satisfied.</p>
          </div>
        </div>
        <div class="col-lg-4 col-md-6">
          <div class="feature-card animate-on-scroll">
            <div class="feature-icon icon-5">
              <i class="fas fa-comments"></i>
            </div>
            <h3>Direct Communication</h3>
            <p>Chat directly with your designer, share feedback, and collaborate in real-time for the best results.</p>
          </div>
        </div>
        <div class="col-lg-4 col-md-6">
          <div class="feature-card animate-on-scroll">
            <div class="feature-icon icon-6">
              <i class="fas fa-trophy"></i>
            </div>
            <h3>Award-Winning</h3>
            <p>Join thousands of satisfied clients who have created amazing designs through our platform.</p>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Testimonials Section -->
  <section id="testimonials" class="testimonials">
    <div class="container">
      <div class="section-title animate-on-scroll">
        <h2>What Our Clients Say</h2>
        <p>Real stories from real people who transformed their ideas with DesignHub</p>
      </div>
      <div class="row">
        <div class="col-lg-4">
          <div class="testimonial-card animate-on-scroll">
            <div class="testimonial-avatar">
              <i class="fas fa-user"></i>
            </div>
            <div class="testimonial-text">
              "DesignHub connected me with an amazing designer who created the perfect logo for my startup. The process was smooth and the result exceeded my expectations!"
            </div>
            <div class="testimonial-author">Sarah Johnson, CEO</div>
          </div>
        </div>
        <div class="col-lg-4">
          <div class="testimonial-card animate-on-scroll">
            <div class="testimonial-avatar">
              <i class="fas fa-user"></i>
            </div>
            <div class="testimonial-text">
              "As a designer, DesignHub has given me the opportunity to work with amazing clients and grow my portfolio. The platform is intuitive and professional."
            </div>
            <div class="testimonial-author">Mike Chen, Designer</div>
          </div>
        </div>
        <div class="col-lg-4">
          <div class="testimonial-card animate-on-scroll">
            <div class="testimonial-avatar">
              <i class="fas fa-user"></i>
            </div>
            <div class="testimonial-text">
              "The quality of work and professionalism on DesignHub is outstanding. I've completed multiple projects and each one has been a great experience."
            </div>
            <div class="testimonial-author">Emily Rodriguez, Entrepreneur</div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- CTA Section -->
  <section class="cta">
    <div class="container">
      <div class="animate-on-scroll">
        <h2>Ready to Start Your Project?</h2>
        <p>Join thousands of satisfied clients and designers on DesignHub</p>
        <a href="register.php" class="btn-hero btn-primary-hero">
          <i class="fas fa-rocket me-2"></i>Start Your Journey
        </a>
      </div>
    </div>
  </section>

  <!-- Footer -->
  <footer class="footer">
    <div class="container">
      <div class="row">
        <div class="col-lg-4">
          <h5><i class="fas fa-palette me-2"></i>DesignHub</h5>
          <p>Connecting creative minds with amazing opportunities. Transform your ideas into reality with our platform.</p>
          <div class="social-links">
            <a href="#"><i class="fab fa-facebook-f"></i></a>
            <a href="#"><i class="fab fa-twitter"></i></a>
            <a href="#"><i class="fab fa-instagram"></i></a>
            <a href="#"><i class="fab fa-linkedin-in"></i></a>
          </div>
        </div>
        <div class="col-lg-2">
          <h5>Platform</h5>
          <ul class="list-unstyled">
            <li><a href="#">How it Works</a></li>
            <li><a href="#">For Clients</a></li>
            <li><a href="#">For Designers</a></li>
            <li><a href="#">Success Stories</a></li>
          </ul>
        </div>
        <div class="col-lg-2">
          <h5>Support</h5>
          <ul class="list-unstyled">
            <li><a href="#">Help Center</a></li>
            <li><a href="#">Contact Us</a></li>
            <li><a href="#">FAQ</a></li>
            <li><a href="#">Community</a></li>
          </ul>
        </div>
        <div class="col-lg-2">
          <h5>Company</h5>
          <ul class="list-unstyled">
            <li><a href="#">About Us</a></li>
            <li><a href="#">Careers</a></li>
            <li><a href="#">Blog</a></li>
            <li><a href="#">Press</a></li>
          </ul>
        </div>
        <div class="col-lg-2">
          <h5>Legal</h5>
          <ul class="list-unstyled">
            <li><a href="#">Privacy Policy</a></li>
            <li><a href="#">Terms of Service</a></li>
            <li><a href="#">Cookie Policy</a></li>
            <li><a href="#">GDPR</a></li>
          </ul>
        </div>
      </div>
      <hr class="my-4">
      <div class="text-center">
        <p>&copy; 2024 DesignHub. All rights reserved.</p>
      </div>
    </div>
  </footer>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    // Navbar scroll effect
    window.addEventListener('scroll', function() {
      const navbar = document.querySelector('.navbar');
      if (window.scrollY > 50) {
        navbar.style.background = 'rgba(255, 255, 255, 0.95) !important';
        navbar.style.boxShadow = '0 2px 20px rgba(0, 0, 0, 0.1)';
      } else {
        navbar.style.background = 'rgba(255, 255, 255, 0.1) !important';
        navbar.style.boxShadow = 'none';
      }
    });

    // Scroll animations
    const observerOptions = {
      threshold: 0.1,
      rootMargin: '0px 0px -50px 0px'
    };

    const observer = new IntersectionObserver(function(entries) {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          entry.target.classList.add('animated');
        }
      });
    }, observerOptions);

    document.querySelectorAll('.animate-on-scroll').forEach(el => {
      observer.observe(el);
    });

    // Smooth scrolling for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
      anchor.addEventListener('click', function (e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
          target.scrollIntoView({
            behavior: 'smooth',
            block: 'start'
          });
        }
      });
    });

    // Add floating animation to hero elements
    document.addEventListener('DOMContentLoaded', function() {
      const heroElements = document.querySelectorAll('.hero h1, .hero p, .hero-buttons');
      heroElements.forEach((el, index) => {
        el.style.animationDelay = `${index * 0.2}s`;
      });
    });
  </script>
</body>
</html> 