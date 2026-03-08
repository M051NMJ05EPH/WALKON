<?php
// Simulate request to wishlist.php with no session
$output = shell_exec('php wishlist.php');
if (empty($output)) {
    echo "SUCCESS: wishlist.php produced no output (likely redirected).\n";
} else {
    echo "WARNING: wishlist.php produced output:\n" . substr($output, 0, 100) . "...\n";
}
?>
