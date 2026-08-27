<footer id="contact">

    <div class="footer-content">

        <div class="footer-brand">
            <h2>MTU</h2>
            <h3>BADMINTON CLUB</h3>

            <p>
                Play. Improve. Connect with passion and energy.
            </p>
        </div>

        <div class="footer-column">

            <h3>QUICK LINKS</h3>

            <a href="index.php">Home</a>

            <a href="index.php#about">About</a>

            <a href="../public/announcement.php">
                Announcements
            </a>

            <a href="member.php">
                Members
            </a>

            <a href="../public/register.php">
                Register
            </a>

        </div>

        <div class="footer-column">

            <h3>CONTACT</h3>

            <p>
                <i class="fa-solid fa-location-dot" style="color: var(--accent-yellow);"></i>

                Mandalay Technological University
            </p>

            <p>
                <i class="fa-solid fa-envelope" style="color: var(--accent-yellow);"></i>

                mtubadminton@gmail.com
            </p>

            <div class="social">

                <a href="https://www.facebook.com/share/14p218QwE4D/" target="_blank" rel="noopener noreferrer"
                    aria-label="Facebook">
                    <i class="fa-brands fa-facebook-f"></i>
                </a>

                <a href="https://www.tiktok.com/@mtu.badminton?_r=1&_t=ZS-99CtJKK6KnN" target="_blank"
                    rel="noopener noreferrer" aria-label="TikTok">
                    <i class="fa-brands fa-tiktok"></i>
                </a>

            </div>

        </div>

    </div>

    <div class="copyright">
        © 2026 MTU Badminton Club. All Rights Reserved.
    </div>

</footer>

<script>
function toggleMenu() {

    const menu = document.getElementById("navMenu");

    menu.classList.toggle("active");

}

window.addEventListener("scroll", function() {

    const navbar = document.getElementById("navbar");

    if (window.scrollY > 50) {

        navbar.classList.add("scrolled");

    } else {

        navbar.classList.remove("scrolled");

    }

});

document.querySelectorAll("#navMenu a").forEach(function(link) {

    link.addEventListener("click", function() {

        document
            .getElementById("navMenu")
            .classList.remove("active");

    });

});
</script>