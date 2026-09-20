    <script>
      // Admin lists reference media files that may be missing on this server (e.g. storage not synced): show a
      // neutral placeholder instead of the browser's broken-image icon + alt text. Runs once per image.
      document.addEventListener('error', function (e) {
        var img = e.target;
        if (!img || img.tagName !== 'IMG' || img.dataset.adminFallback) return;
        img.dataset.adminFallback = '1';
        img.classList.add('admin-img-missing');
        img.src = 'data:image/svg+xml;utf8,' + encodeURIComponent('<svg xmlns="http://www.w3.org/2000/svg" width="80" height="80" viewBox="0 0 80 80"><rect width="80" height="80" fill="#f1f3f5"/><path d="M22 54l14-16 10 11 6-7 8 12z" fill="#ced4da"/><circle cx="30" cy="28" r="5" fill="#ced4da"/></svg>');
      }, true);
    </script>
  </body>
  <!-- [Body] end -->
</html>