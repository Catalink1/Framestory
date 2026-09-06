<?php
if (!defined('ADMIN_ACCESS')) {
    http_response_code(403);
    exit('Acces direct interzis.');
}
?>
<header class="topbar">
  <a href="dashboard.php" class="topbar-logo">Frame<em>Story</em> <span>admin</span></a>
  <nav class="topbar-nav">
    <a href="../index.html" target="_blank" rel="noopener">Vezi site-ul →</a>
    <span class="topbar-user"><?= h($_SESSION['admin_username'] ?? '') ?></span>
    <a href="logout.php">Ieși din cont</a>
  </nav>
</header>
