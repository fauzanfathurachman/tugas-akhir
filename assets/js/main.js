// Responsive Navigation Toggle
const navToggle = document.getElementById('navToggle');
const navMenu = document.querySelector('.nav ul');

navToggle.addEventListener('click', () => {
    navMenu.classList.toggle('open');
    navToggle.classList.toggle('active');
});

// Close menu on link click (mobile)
document.querySelectorAll('.nav ul li a').forEach(link => {
    link.addEventListener('click', () => {
        if (window.innerWidth <= 700) {
            navMenu.classList.remove('open');
            navToggle.classList.remove('active');
        }
    });
});

// Smooth Scroll for anchor links
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        const targetId = this.getAttribute('href').substring(1);
        const target = document.getElementById(targetId);
        if (target) {
            e.preventDefault();
            window.scrollTo({
                top: target.offsetTop - 70,
                behavior: 'smooth'
            });
        }
    });
});

// Highlight active menu on scroll
window.addEventListener('scroll', () => {
    const sections = document.querySelectorAll('section');
    const scrollPos = window.scrollY + 80;
    sections.forEach(section => {
        if (scrollPos >= section.offsetTop && scrollPos < section.offsetTop + section.offsetHeight) {
            document.querySelectorAll('.nav ul li a').forEach(link => {
                link.classList.remove('active');
                if (link.getAttribute('href').substring(1) === section.id) {
                    link.classList.add('active');
                }
            });
        }
    });
}); 