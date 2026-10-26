    </div> <!-- End Main Content -->

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Active link script
        const currentLocation = location.href;
        const menuItem = document.querySelectorAll('.nav-link');
        const menuLength = menuItem.length;
        for (let i = 0; i < menuLength; i++) {
            if (menuItem[i].href === currentLocation) {
                menuItem[i].classList.add("active");
            } else {
                menuItem[i].classList.remove("active");
            }
        }
    </script>
</body>
</html>
