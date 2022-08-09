<?php
# This is a Windows-friendly symlink
if (!empty($_ENV['CACHE_HOST'])) {
  require_once WP_CONTENT_DIR . '/plugins/wp-redis/object-cache.php';
}
