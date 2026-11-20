<?php
// Closes auth-layout-start.php. Optional: $authFooterLinks = [['label', 'url'], ...]
$authFooterLinks = $authFooterLinks ?? [['Back to website', 'index.php']];
?>
            <div class="admin-login-footer">
                <?php foreach ($authFooterLinks as $i => $link): ?>
                    <?php if ($i > 0): ?> &middot; <?php endif; ?>
                    <a href="<?php echo htmlspecialchars($link[1]); ?>"><?php echo htmlspecialchars($link[0]); ?></a>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</body>
</html>
