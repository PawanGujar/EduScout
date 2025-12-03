// ============================================================
// HAMBURGER MENU - SIMPLE & BULLETPROOF
// ============================================================
(function() {
  var hamburger;
  var nav;
  
  function findElements() {
    hamburger = document.querySelector('.hamburger');
    nav = document.querySelector('.site-header nav');
    return hamburger && nav;
  }
  
  function setupHamburgerMenu() {
    if (!findElements()) {
      // Retry in 100ms if elements not found
      setTimeout(setupHamburgerMenu, 100);
      return;
    }
    
    // Click handler for hamburger
    hamburger.onclick = function(e) {
      e.preventDefault();
      e.stopPropagation();
      hamburger.classList.toggle('active');
      nav.classList.toggle('active');
      return false;
    };
    
    // Click handler for nav links
    var links = nav.querySelectorAll('a');
    for (var i = 0; i < links.length; i++) {
      (function(link) {
        link.onclick = function() {
          hamburger.classList.remove('active');
          nav.classList.remove('active');
        };
      })(links[i]);
    }
    
    // Click outside handler
    document.onclick = function(e) {
      if (hamburger && nav) {
        var isHamburgerClick = false;
        var isNavClick = false;
        
        // Check if click is on hamburger or its children
        var current = e.target;
        while (current) {
          if (current === hamburger) {
            isHamburgerClick = true;
            break;
          }
          if (current === nav) {
            isNavClick = true;
            break;
          }
          current = current.parentNode;
        }
        
        // Close menu if clicking outside both
        if (!isHamburgerClick && !isNavClick) {
          hamburger.classList.remove('active');
          nav.classList.remove('active');
        }
      }
    };
  }
  
  // Try to setup immediately
  if (document.readyState === 'interactive' || document.readyState === 'complete') {
    setupHamburgerMenu();
  } else {
    document.addEventListener('DOMContentLoaded', setupHamburgerMenu);
    // Also try on load
    window.addEventListener('load', setupHamburgerMenu);
  }
})();

// ============================================================
// BOOKMARK FUNCTIONALITY
// ============================================================
(function() {
  function setupBookmarks() {
    var buttons = document.querySelectorAll('.bookmark-btn');
    
    for (var i = 0; i < buttons.length; i++) {
      (function(btn) {
        btn.onclick = function(e) {
          e.preventDefault();
          
          var courseId = btn.getAttribute('data-course-id');
          var isBookmarked = btn.classList.contains('bookmarked');
          var action = isBookmarked ? 'remove' : 'add';
          
          fetch('bookmark.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'course_id=' + encodeURIComponent(courseId) + '&action=' + encodeURIComponent(action)
          })
            .then(function(response) { 
              return response.json(); 
            })
            .then(function(data) {
              if (data.success) {
                if (data.status === 'bookmarked') {
                  btn.classList.add('bookmarked');
                  btn.textContent = '⭐ Bookmarked';
                } else {
                  btn.classList.remove('bookmarked');
                  btn.textContent = '☆ Bookmark';
                }
              }
            })
            .catch(function(err) {
              console.error('Bookmark error:', err);
            });
          
          return false;
        };
      })(buttons[i]);
    }
  }
  
  if (document.readyState === 'interactive' || document.readyState === 'complete') {
    setupBookmarks();
  } else {
    document.addEventListener('DOMContentLoaded', setupBookmarks);
    window.addEventListener('load', setupBookmarks);
  }
})();
