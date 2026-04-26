<?php
// includes/footer.php
?>

<!-- Enhanced Interactive Footer -->
<footer class="custom-footer">
  <!-- Decorative Top Border -->
  <div class="footer-border">
    <div class="border-wave"></div>
  </div>

  <div class="container-fluid">
    <div class="footer-content">
      
      <!-- Main Footer Section -->
      <div class="row align-items-center">
        
        <!-- Left Section - Logo & Info -->
        <div class="col-lg-4 col-md-6 mb-3 mb-lg-0">
          <div class="footer-brand">
            <div class="footer-logo">
              <div class="logo-container">
                <i class="fas fa-building"></i>
                <div class="logo-glow"></div>
              </div>
              <div class="brand-info">
                <h6 class="brand-title mb-1">Sistem Informasi</h6>
                <small class="brand-subtitle">Kepegawaian Kota Banjarmasin</small>
              </div>
            </div>
          </div>
        </div>

        <!-- Center Section - Quick Links -->
        <div class="col-lg-4 col-md-6 mb-3 mb-lg-0">
          <div class="footer-links">
            <h6 class="links-title">Quick Links</h6>
            <div class="links-container">
              <a href="dashboard.php" class="footer-link">
                <i class="fas fa-home"></i>
                <span>Dashboard</span>
                <div class="link-underline"></div>
              </a>
              <a href="dataduk.php" class="footer-link">
                <i class="fas fa-users"></i>
                <span>Data DUK</span>
                <div class="link-underline"></div>
              </a>
              <a href="users.php" class="footer-link">
                <i class="fas fa-user-cog"></i>
                <span>Users</span>
                <div class="link-underline"></div>
              </a>
              <a href="help.php" class="footer-link">
                <i class="fas fa-question-circle"></i>
                <span>Bantuan</span>
                <div class="link-underline"></div>
              </a>
            </div>
          </div>
        </div>

        <!-- Right Section - Contact & Social -->
        <div class="col-lg-4 col-md-12">
          <div class="footer-contact">
            <h6 class="contact-title">Kontak & Info</h6>
            <div class="contact-info">
              <div class="contact-item">
                <i class="fas fa-envelope"></i>
                <span>info@banjarmasinkota.go.id</span>
              </div>
              <div class="contact-item">
                <i class="fas fa-phone"></i>
                <span>+62 511 3252040</span>
              </div>
              <div class="contact-item">
                <i class="fas fa-map-marker-alt"></i>
                <span>Banjarmasin, Kalimantan Selatan</span>
              </div>
            </div>
            
            <!-- Social Media Links -->
            <div class="social-links">
              <a href="#" class="social-link" data-bs-toggle="tooltip" title="Facebook">
                <i class="fab fa-facebook-f"></i>
                <div class="social-ripple"></div>
              </a>
              <a href="https://www.instagram.com/dppkbpm?igsh=MTZseGE2NDFpbXpwdA==" class="social-link" data-bs-toggle="tooltip" title="Instagram">
                <i class="fab fa-instagram"></i>
                <div class="social-ripple"></div>
              </a>
              <a href="#" class="social-link" data-bs-toggle="tooltip" title="Twitter">
                <i class="fab fa-twitter"></i>
                <div class="social-ripple"></div>
              </a>
              <a href="#" class="social-link" data-bs-toggle="tooltip" title="YouTube">
                <i class="fab fa-youtube"></i>
                <div class="social-ripple"></div>
              </a>
            </div>
          </div>
        </div>
      </div>

      <!-- Divider with Animation -->
      <div class="footer-divider">
        <div class="divider-line">
          <div class="divider-glow"></div>
        </div>
      </div>

      <!-- Bottom Section - Copyright & Status -->
      <div class="footer-bottom">
        <div class="row align-items-center">
          <div class="col-md-6">
            <div class="copyright">
              <i class="fas fa-copyright"></i>
              <span>2025 Sistem Informasi Kepegawaian</span>
              <div class="version-badge">V.1</div>
            </div>
          </div>
          <div class="col-md-6">
            <div class="footer-status">

            <div class="status-item">
                <i class="fas fa-clock"></i>
                <span id="current-time"></span>
              </div>
              
            <a href="https://www.instagram.com/dzkymrz10_?igsh=bWh2ejQxdHp0OG1n" class="footer-link">
            <i class="fab fa-instagram"></i>
                <span>DZAKY AKMAL RIZQULLAH</span>
                <div class="link-underline"></div>
              </a>
            <a href="https://www.instagram.com/dheovv?igsh=eWxnYnNzbWpwMWxm" class="footer-link">
            <i class="fab fa-instagram"></i>
                <span>DHEO VALENTINO ERAY</span>
                <div class="link-underline"></div>
              </a>
              
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Floating Action Buttons -->
  <div class="footer-fab">
    <button class="fab-btn scroll-top" data-bs-toggle="tooltip" title="Kembali ke Atas">
      <i class="fas fa-chevron-up"></i>
      <div class="fab-ripple"></div>
    </button>
  </div>

  <!-- Background Animation -->
  <div class="footer-bg-animation">
    <div class="floating-shape shape-1"></div>
    <div class="floating-shape shape-2"></div>
    <div class="floating-shape shape-3"></div>
  </div>
</footer>

<style>
/* ================================================
   FOOTER RESPONSIVE WITH SIDEBAR INTEGRATION
   ================================================ */

/* Enhanced Footer Styles */
.custom-footer {
  background: linear-gradient(135deg, #2c3e50 0%, #34495e 50%, #2c3e50 100%);
  color: #ecf0f1;
  position: relative;
  margin-top: 50px;
  overflow: hidden;
  /* PENTING: Footer mengikuti main content */
  margin-left: 260px;
  transition: margin-left 0.3s ease;
  width: calc(100% - 260px);
}

/* Footer adjustment saat sidebar collapsed */
.sidebar.collapsed ~ * .custom-footer,
body:has(.sidebar.collapsed) .custom-footer {
  margin-left: 60px;
  width: calc(100% - 60px);
}

/* Mobile: Footer full width */
@media (max-width: 768px) {
  .custom-footer {
    margin-left: 0 !important;
    width: 100% !important;
  }
}

/* Decorative Top Border */
.footer-border {
  height: 4px;
  background: linear-gradient(90deg, #3498db, #e74c3c, #3498db);
  position: relative;
  overflow: hidden;
}

.border-wave {
  position: absolute;
  top: 0;
  left: -100%;
  width: 100%;
  height: 100%;
  background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
  animation: wave-animation 3s infinite;
}

/* Main Footer Content */
.footer-content {
  padding: 40px 0 20px;
  position: relative;
  z-index: 2;
}

/* Footer Brand */
.footer-logo {
  display: flex;
  align-items: center;
  margin-bottom: 15px;
}

.logo-container {
  position: relative;
  width: 50px;
  height: 50px;
  background: linear-gradient(135deg, #3498db, #2980b9);
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-right: 15px;
  box-shadow: 0 4px 20px rgba(52, 152, 219, 0.3);
}

.logo-container i {
  font-size: 22px;
  color: white;
  z-index: 2;
}

.logo-glow {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: radial-gradient(circle, rgba(52, 152, 219, 0.4) 0%, transparent 70%);
  border-radius: 12px;
  animation: pulse-glow 2s infinite;
}

.brand-title {
  font-weight: 700;
  background: linear-gradient(45deg, #3498db, #e74c3c);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
  margin-bottom: 2px;
}

.brand-subtitle {
  color: #bdc3c7;
  font-size: 13px;
}

/* Footer Links */
.links-title {
  color: #3498db;
  font-weight: 600;
  margin-bottom: 15px;
  position: relative;
}

.links-title::after {
  content: '';
  position: absolute;
  bottom: -5px;
  left: 0;
  width: 30px;
  height: 2px;
  background: linear-gradient(90deg, #3498db, #e74c3c);
  border-radius: 1px;
}

.links-container {
  display: flex;
  flex-wrap: wrap;
  gap: 15px;
}

.footer-link {
  display: flex;
  align-items: center;
  color: #bdc3c7 !important;
  text-decoration: none;
  padding: 8px 12px;
  border-radius: 8px;
  transition: all 0.3s ease;
  position: relative;
  overflow: hidden;
  background: rgba(255, 255, 255, 0.05);
  border: 1px solid rgba(255, 255, 255, 0.1);
}

.footer-link i {
  font-size: 14px;
  margin-right: 8px;
  transition: all 0.3s ease;
}

.footer-link span {
  font-size: 13px;
  font-weight: 500;
}

.link-underline {
  position: absolute;
  bottom: 0;
  left: 0;
  width: 0;
  height: 2px;
  background: linear-gradient(90deg, #3498db, #e74c3c);
  transition: width 0.3s ease;
}

.footer-link:hover {
  color: #ffffff !important;
  background: rgba(52, 152, 219, 0.2);
  border-color: rgba(52, 152, 219, 0.3);
  transform: translateY(-2px);
}

.footer-link:hover .link-underline {
  width: 100%;
}

.footer-link:hover i {
  color: #3498db;
  transform: scale(1.1);
}

/* Contact Section */
.contact-title {
  color: #3498db;
  font-weight: 600;
  margin-bottom: 15px;
  position: relative;
}

.contact-title::after {
  content: '';
  position: absolute;
  bottom: -5px;
  left: 0;
  width: 30px;
  height: 2px;
  background: linear-gradient(90deg, #3498db, #e74c3c);
  border-radius: 1px;
}

.contact-info {
  margin-bottom: 20px;
}

.contact-item {
  display: flex;
  align-items: center;
  margin-bottom: 10px;
  color: #bdc3c7;
  font-size: 13px;
}

.contact-item i {
  width: 20px;
  color: #3498db;
  margin-right: 10px;
  font-size: 14px;
}

/* Social Links */
.social-links {
  display: flex;
  gap: 12px;
  margin-top: 15px;
}

.social-link {
  position: relative;
  width: 40px;
  height: 40px;
  background: rgba(255, 255, 255, 0.1);
  border: 1px solid rgba(255, 255, 255, 0.2);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #bdc3c7 !important;
  text-decoration: none;
  transition: all 0.3s ease;
  overflow: hidden;
}

.social-link:hover {
  background: rgba(52, 152, 219, 0.3);
  border-color: rgba(52, 152, 219, 0.5);
  color: #ffffff !important;
  transform: scale(1.1) rotate(5deg);
}

.social-link i {
  font-size: 16px;
  z-index: 2;
}

/* Footer Divider */
.footer-divider {
  margin: 30px 0 20px;
  position: relative;
}

.divider-line {
  height: 1px;
  background: rgba(255, 255, 255, 0.1);
  position: relative;
  overflow: hidden;
}

.divider-glow {
  position: absolute;
  top: 0;
  left: -100%;
  width: 50%;
  height: 100%;
  background: linear-gradient(90deg, transparent, #3498db, transparent);
  animation: glow-slide 3s infinite;
}

/* Footer Bottom */
.footer-bottom {
  padding-bottom: 20px;
}

.copyright {
  display: flex;
  align-items: center;
  color: #bdc3c7;
  font-size: 13px;
  flex-wrap: wrap;
}

.copyright i {
  margin-right: 8px;
  color: #3498db;
}

.version-badge {
  background: linear-gradient(135deg, #27ae60, #2ecc71);
  color: white;
  padding: 2px 8px;
  border-radius: 12px;
  font-size: 10px;
  font-weight: 600;
  margin-left: 10px;
  animation: version-pulse 2s infinite;
}

.footer-status {
  display: flex;
  justify-content: flex-end;
  gap: 20px;
  flex-wrap: wrap;
}

.status-item {
  display: flex;
  align-items: center;
  color: #bdc3c7;
  font-size: 13px;
}

.status-dot {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  margin-right: 6px;
}

.status-dot.online {
  background: #27ae60;
  animation: status-blink 2s infinite;
}

.status-item i {
  margin-right: 6px;
  color: #3498db;
}

/* Floating Action Button */
.footer-fab {
  position: fixed;
  bottom: 30px;
  right: 30px;
  z-index: 1000;
  opacity: 0;
  visibility: hidden;
  transition: all 0.3s ease;
}

.footer-fab.show {
  opacity: 1;
  visibility: visible;
}

.fab-btn {
  width: 50px;
  height: 50px;
  background: linear-gradient(135deg, #3498db, #2980b9);
  border: none;
  border-radius: 50%;
  color: white;
  font-size: 18px;
  box-shadow: 0 4px 20px rgba(52, 152, 219, 0.4);
  cursor: pointer;
  transition: all 0.3s ease;
  position: relative;
  overflow: hidden;
}

.fab-btn:hover {
  transform: scale(1.1) translateY(-5px);
  box-shadow: 0 6px 25px rgba(52, 152, 219, 0.6);
}

.fab-btn:active {
  transform: scale(0.95);
}

/* Background Animation */
.footer-bg-animation {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  overflow: hidden;
  z-index: 1;
  pointer-events: none;
}

.floating-shape {
  position: absolute;
  background: rgba(52, 152, 219, 0.1);
  border-radius: 50%;
}

.shape-1 {
  width: 100px;
  height: 100px;
  top: 20%;
  left: 10%;
  animation: float-1 6s ease-in-out infinite;
}

.shape-2 {
  width: 150px;
  height: 150px;
  top: 60%;
  right: 15%;
  animation: float-2 8s ease-in-out infinite;
}

.shape-3 {
  width: 80px;
  height: 80px;
  bottom: 30%;
  left: 70%;
  animation: float-3 7s ease-in-out infinite;
}

/* Animations */
@keyframes wave-animation {
  0% { left: -100%; }
  100% { left: 100%; }
}

@keyframes pulse-glow {
  0%, 100% { opacity: 1; transform: scale(1); }
  50% { opacity: 0.7; transform: scale(1.05); }
}

@keyframes glow-slide {
  0% { left: -100%; }
  100% { left: 100%; }
}

@keyframes version-pulse {
  0%, 100% { transform: scale(1); }
  50% { transform: scale(1.05); }
}

@keyframes status-blink {
  0%, 100% { opacity: 1; }
  50% { opacity: 0.5; }
}

@keyframes float-1 {
  0%, 100% { transform: translateY(0px) rotate(0deg); }
  50% { transform: translateY(-20px) rotate(180deg); }
}

@keyframes float-2 {
  0%, 100% { transform: translateY(0px) rotate(0deg); }
  50% { transform: translateY(-30px) rotate(-180deg); }
}

@keyframes float-3 {
  0%, 100% { transform: translateY(0px) rotate(0deg); }
  50% { transform: translateY(-15px) rotate(90deg); }
}

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

/* Ripple Effect */
.social-ripple,
.fab-ripple {
  position: absolute;
  border-radius: 50%;
  background: rgba(255, 255, 255, 0.3);
  transform: scale(0);
  pointer-events: none;
  width: 100%;
  height: 100%;
  top: 0;
  left: 0;
}

/* Responsive Design */
@media (max-width: 992px) {
  .footer-content {
    padding: 30px 0 15px;
  }
  
  .footer-status {
    justify-content: flex-start;
  }
}

@media (max-width: 768px) {
  .footer-content {
    text-align: center;
  }
  
  .footer-logo {
    justify-content: center;
  }
  
  .links-container {
    justify-content: center;
  }
  
  .social-links {
    justify-content: center;
  }
  
  .footer-status {
    justify-content: center;
    margin-top: 15px;
  }
  
  .copyright {
    justify-content: center;
  }
  
  .fab-btn {
    width: 45px;
    height: 45px;
    font-size: 16px;
  }
  
  .footer-fab {
    bottom: 20px;
    right: 20px;
  }
  
  .floating-shape {
    display: none;
  }
}

@media (max-width: 576px) {
  .links-container {
    flex-direction: column;
    align-items: center;
  }
  
  .footer-link {
    width: 200px;
    justify-content: center;
  }
  
  .footer-status {
    flex-direction: column;
    gap: 10px;
  }
  
  .copyright {
    font-size: 12px;
    flex-direction: column;
    gap: 8px;
  }
  
  .version-badge {
    margin-left: 0;
  }
  
  .contact-item {
    font-size: 12px;
  }
  
  .footer-content {
    padding: 25px 0 15px;
  }
}

/* Print Styles */
@media print {
  .custom-footer {
    margin-left: 0 !important;
    width: 100% !important;
  }
  
  .footer-fab,
  .footer-bg-animation,
  .social-links {
    display: none !important;
  }
}
</style>

<!-- JS: jQuery, Bootstrap, DataTables -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

<!-- Custom JS -->
<script src="assets/js/script.js"></script>

<script>
// Footer enhancements with sidebar integration
document.addEventListener("DOMContentLoaded", function () {
  
  const sidebar = document.getElementById('sidebar');
  const footer = document.querySelector('.custom-footer');
  const fabBtn = document.querySelector('.footer-fab');
  
  // Update footer position when sidebar changes
  function updateFooterPosition() {
    if (!footer || !sidebar) return;
    
    if (window.innerWidth > 768) {
      if (sidebar.classList.contains('collapsed')) {
        footer.style.marginLeft = '60px';
        footer.style.width = 'calc(100% - 60px)';
      } else {
        footer.style.marginLeft = '260px';
        footer.style.width = 'calc(100% - 260px)';
      }
    } else {
      footer.style.marginLeft = '0';
      footer.style.width = '100%';
    }
  }
  
  // Observer for sidebar class changes
  if (sidebar) {
    const observer = new MutationObserver(function(mutations) {
      mutations.forEach(function(mutation) {
        if (mutation.attributeName === 'class') {
          updateFooterPosition();
        }
      });
    });
    
    observer.observe(sidebar, { attributes: true });
  }
  
  // Handle window resize
  let resizeTimeout;
  window.addEventListener('resize', function() {
    clearTimeout(resizeTimeout);
    resizeTimeout = setTimeout(updateFooterPosition, 250);
  });
  
  // Initialize footer position
  updateFooterPosition();
  
  // Initialize tooltips
  var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
  var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
    return new bootstrap.Tooltip(tooltipTriggerEl);
  });
  
  // Current time display
  function updateTime() {
    const now = new Date();
    const timeString = now.toLocaleTimeString('id-ID', { 
      hour: '2-digit', 
      minute: '2-digit',
      timeZone: 'Asia/Makassar'
    });
    const timeElement = document.getElementById('current-time');
    if (timeElement) {
      timeElement.textContent = timeString;
    }
  }
  
  // Update time every second
  updateTime();
  setInterval(updateTime, 1000);
  
  // Scroll to top functionality
  const scrollTopBtn = document.querySelector('.scroll-top');
  if (scrollTopBtn && fabBtn) {
    scrollTopBtn.addEventListener('click', function() {
      window.scrollTo({
        top: 0,
        behavior: 'smooth'
      });
    });
    
    // Show/hide scroll button based on scroll position
    window.addEventListener('scroll', function() {
      if (window.scrollY > 500) {
        fabBtn.classList.add('show');
      } else {
        fabBtn.classList.remove('show');
      }
    });
    
    // Initial check
    if (window.scrollY > 500) {
      fabBtn.classList.add('show');
    }
  }
  
  // Ripple effect for social links and FAB
  function addRippleEffect(element) {
    element.addEventListener('click', function(e) {
      const ripple = element.querySelector('.social-ripple, .fab-ripple');
      if (ripple) {
        const rect = element.getBoundingClientRect();
        const size = Math.max(rect.width, rect.height);
        const x = e.clientX - rect.left - size / 2;
        const y = e.clientY - rect.top - size / 2;
        
        ripple.style.width = ripple.style.height = size + 'px';
        ripple.style.left = x + 'px';
        ripple.style.top = y + 'px';
        
        ripple.animate([
          { transform: 'scale(0)', opacity: 1 },
          { transform: 'scale(4)', opacity: 0 }
        ], {
          duration: 600,
          easing: 'ease-out'
        });
      }
    });
  }
  
  // Apply ripple effect
  document.querySelectorAll('.social-link, .fab-btn').forEach(addRippleEffect);
  
  // Animate footer elements on scroll into view
  const observerOptions = {
    threshold: 0.1,
    rootMargin: '0px 0px -50px 0px'
  };
  
  const animationObserver = new IntersectionObserver(function(entries) {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        entry.target.style.animation = 'fadeInUp 0.8s ease-out forwards';
        entry.target.style.opacity = '1';
        entry.target.style.transform = 'translateY(0)';
      }
    });
  }, observerOptions);
  
  // Observe footer elements
  document.querySelectorAll('.footer-brand, .footer-links, .footer-contact').forEach(el => {
    el.style.opacity = '0';
    el.style.transform = 'translateY(30px)';
    animationObserver.observe(el);
  });
  
  // Server status simulation
  const statusDot = document.querySelector('.status-dot');
  if (statusDot) {
    setInterval(() => {
      const random = Math.random();
      if (random > 0.95) {
        statusDot.classList.remove('online');
        statusDot.style.background = '#e74c3c';
        setTimeout(() => {
          statusDot.classList.add('online');
          statusDot.style.background = '#27ae60';
        }, 2000);
      }
    }, 10000);
  }
  
  console.log('✅ Footer loaded with sidebar integration');
});
</script>

</body>
</html>