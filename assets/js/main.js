// Simple typewriter + page reveal logic
(function(){
  const intro = document.getElementById('intro');
  if (sessionStorage.getItem('introShown')) {
    if (intro) intro.remove();
  } else {
    if (intro) {
      const screen = document.getElementById('crtText');
      const lines = ['boot> INIT 6502 ... ok','mem>  64KB free','net>  LINK established','io >  TUBE_MONITOR online','','loading web page……'];
      let i = 0, j = 0;
      function tick(){
        if(i < lines.length){
          const line = lines[i];
          if(j <= line.length){
            screen.textContent = lines.slice(0,i).join('\n') + (i?'\n':'') + line.slice(0,j) + (j%2 ? '_' : '');
            j++;
            setTimeout(tick, 25 + Math.random()*40);
          } else { i++; j = 0; setTimeout(tick, 180); }
        } else {
          setTimeout(()=>{
            intro.classList.add('opacity-0','pointer-events-none');
            sessionStorage.setItem('introShown', 'true');
            setTimeout(()=> intro.remove(), 600);
          }, 500);
        }
      }
      tick();
    }
  }

  // Scroll reveal
  const obs = new IntersectionObserver((entries)=>{
    for(const e of entries){
      if(e.isIntersecting){
        e.target.classList.add('opacity-100','translate-y-0');
        obs.unobserve(e.target);
      }
    }
  }, {threshold:.1});
  document.querySelectorAll('[data-reveal]').forEach(el=>{
    el.classList.add('opacity-0','translate-y-4','transition','duration-700','ease-out');
    obs.observe(el);
  });

  // Mobile Menu Toggle
  const menuToggle = document.getElementById('menu-toggle') || document.getElementById('mobile-menu-button');
  const mobileMenu = document.getElementById('mobile-menu');
  if(menuToggle && mobileMenu) {
      menuToggle.addEventListener('click', function() {
          mobileMenu.classList.toggle('hidden');
      });
  }

})();

// Optimierte GLightbox Konfiguration mit besserer Responsive-Anpassung
window.addEventListener('load', function() {
  if (typeof GLightbox !== 'undefined') {
    
    // Funktion zur Berechnung optimaler Bildgröße basierend auf Viewport
    function getOptimalImageSize() {
      const vw = window.innerWidth;
      const vh = window.innerHeight;
      
      // Verschiedene Breakpoints für optimale Darstellung
      let config = {
        width: 'auto',
        height: 'auto'
      };
      
      // Mobile Geräte (Portrait)
      if (vw < 768) {
        config.width = Math.min(vw * 0.95, 600) + 'px';
        config.height = Math.min(vh * 0.70, 800) + 'px';
      }
      // Tablets und kleine Laptops
      else if (vw < 1366) {
        config.width = Math.min(vw * 0.85, 1024) + 'px';
        config.height = Math.min(vh * 0.80, 768) + 'px';
      }
      // Desktop HD bis Full HD
      else if (vw < 1920) {
        config.width = Math.min(vw * 0.80, 1400) + 'px';
        config.height = Math.min(vh * 0.85, 900) + 'px';
      }
      // 4K und größere Displays
      else {
        config.width = Math.min(vw * 0.70, 1920) + 'px';
        config.height = Math.min(vh * 0.85, 1080) + 'px';
      }
      
      return config;
    }
    
    // Initial Größe berechnen
    let imageSize = getOptimalImageSize();
    
    // Lightbox initialisieren mit optimierten Einstellungen
    const lightbox = GLightbox({
      selector: '.portfolio-gallery a.portfolio-item',
      touchNavigation: true,
      touchFollowAxis: true,
      loop: true,
      closeOnOutsideClick: true,
      
      // Responsive Bildgrößen
      width: imageSize.width,
      height: imageSize.height,
      
      // Zoom-Funktionalität
      zoomable: true,
      draggable: true,
      
      // Beschreibung mit Link
      descPosition: 'bottom',
      moreLength: 0,
      
      // Animation Settings
      openEffect: 'zoom',
      closeEffect: 'zoom',
      slideEffect: 'slide',
      
      // CSS Klassen für besseres Styling
      cssEfects: {
        fade: { in: 'fadeIn', out: 'fadeOut' },
        zoom: { in: 'zoomIn', out: 'zoomOut' },
        slide: { in: 'slideInRight', out: 'slideOutLeft' },
        slideBack: { in: 'slideInLeft', out: 'slideOutRight' },
        none: { in: 'none', out: 'none' }
      },
      
      // Mobile-spezifische Einstellungen
      mobileSettings: {
        touchNavigation: true,
        touchFollowAxis: true,
        closeOnOutsideClick: true
      },
      
      // Video-Settings falls benötigt
      videosWidth: '100%',
      
      // Callbacks für zusätzliche Anpassungen
      onOpen: function() {
        // Bei Öffnen Größe neu berechnen
        imageSize = getOptimalImageSize();
        
        // Prevent body scroll auf Mobile
        if (window.innerWidth < 768) {
          document.body.style.overflow = 'hidden';
        }
      },
      
      onClose: function() {
        // Body scroll wieder aktivieren
        document.body.style.overflow = '';
      }
    });
    
    // Bei Resize die Größe neu berechnen
    let resizeTimer;
    window.addEventListener('resize', function() {
      clearTimeout(resizeTimer);
      resizeTimer = setTimeout(function() {
        imageSize = getOptimalImageSize();
        // GLightbox neu initialisieren mit neuen Größen wenn geöffnet
        if (lightbox && lightbox.activeSlide) {
          lightbox.setSize(lightbox.activeSlide, imageSize.width, imageSize.height);
        }
      }, 250);
    });
    
    // Touch-Gesten für Mobile optimieren
    if ('ontouchstart' in window) {
      let touchStartX = 0;
      let touchStartY = 0;
      let touchEndX = 0;
      let touchEndY = 0;
      
      document.addEventListener('touchstart', function(e) {
        if (e.target.closest('.gslide-image')) {
          touchStartX = e.changedTouches[0].screenX;
          touchStartY = e.changedTouches[0].screenY;
        }
      }, false);
      
      document.addEventListener('touchend', function(e) {
        if (e.target.closest('.gslide-image')) {
          touchEndX = e.changedTouches[0].screenX;
          touchEndY = e.changedTouches[0].screenY;
          handleSwipe();
        }
      }, false);
      
      function handleSwipe() {
        const diffX = touchEndX - touchStartX;
        const diffY = touchEndY - touchStartY;
        const threshold = 50; // Minimum swipe distance
        
        // Horizontal swipe hat Priorität
        if (Math.abs(diffX) > Math.abs(diffY) && Math.abs(diffX) > threshold) {
          if (diffX > 0 && lightbox) {
            lightbox.prevSlide(); // Swipe right
          } else if (lightbox) {
            lightbox.nextSlide(); // Swipe left
          }
        }
      }
    }
  }
});