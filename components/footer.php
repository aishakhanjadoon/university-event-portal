<?php
// Determine if we're on the index page
$is_index = strpos($_SERVER['PHP_SELF'], 'index.php') !== false;
$path_prefix = $is_index ? '' : '../';
?>
    </main>
    <footer class="footer mt-auto py-3">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5>University Events Portal</h5>
                    <p class="text-muted">Discover and participate in exciting events across departments and societies.</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <h5>Quick Links</h5>
                    <ul class="list-unstyled">
                        <li><a href="<?php echo $path_prefix; ?>index.php">Home</a></li>
                        <li><a href="<?php echo $path_prefix; ?>pages/events/department-events.php">Department Events</a></li>
                        <li><a href="<?php echo $path_prefix; ?>pages/events/society-events.php">Society Events</a></li>
                    </ul>
                </div>
            </div>
            <hr class="mt-4">
            <div class="text-center">
                <p class="mb-0">&copy; <?php echo date('Y'); ?> University Events Portal. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://kit.fontawesome.com/your-fontawesome-kit.js"></script>
    <script src="<?php echo $path_prefix; ?>assets/js/main.js"></script>
</body>
</html> 