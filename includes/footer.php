</main>
<?php 
  // Determine again if we are in the admin area for the footer.
  $is_admin_area = (strpos($_SERVER['SCRIPT_NAME'], '/admin/') !== false);
?>
<footer class="border-t border-zinc-800 mt-12">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 py-6 text-sm text-zinc-400 flex flex-col sm:flex-row items-center justify-between gap-4">
    <?php if ($is_admin_area): ?>
      <p class="text-center sm:text-left">Admin Panel © <?= date('Y') ?> Koralp Soy</p>
    <?php else: ?>
      <p class="text-center sm:text-left">© <?= date('Y') ?> Koralp Soy · Portfolio & Blog</p>
      <p>
        <?php global $latest_posts; if (!empty($latest_posts)): ?>
          <a class="hover:text-white" href="<?= $base_url ?>/blog.php">Blog</a> · 
        <?php endif; ?>
        <a class="hover:text-white" href="<?= $base_url ?>/#kontakt">Kontakt</a>
      </p>
    <?php endif; ?>
  </div>
</footer>
<script src="<?= $base_url ?>/assets/js/main.js"></script>
<?php if (!$is_admin_area): ?>
  <!-- KORRIGIERT: GLightbox-Skript statt baguetteBox -->
  <script src="https://cdn.jsdelivr.net/gh/mcstudios/glightbox/dist/js/glightbox.min.js"></script>
<?php endif; ?>
</body>
</html>

