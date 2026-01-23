    </main>
    <footer class="site-footer">
        <div class="container">
            <div class="footer-grid">
                <div class="footer-brand">
                    <a href="index.php" class="logo">
                        <span class="logo-icon">
                            <svg width="28" height="28" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <circle cx="16" cy="16" r="14" stroke="currentColor" stroke-width="2"/>
                                <path d="M10 20C10 20 12 12 16 12C20 12 22 20 22 20" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                <circle cx="16" cy="10" r="2" fill="currentColor"/>
                                <circle cx="11" cy="18" r="1.5" fill="currentColor"/>
                                <circle cx="21" cy="18" r="1.5" fill="currentColor"/>
                            </svg>
                        </span>
                        <span class="logo-text">ARIbrain</span>
                    </a>
                    <p class="footer-description">
                        Open-source neuroimaging analysis tool developed at Leiden University. 
                        Advancing fMRI research through rigorous statistical inference.
                    </p>
                </div>
                <div class="footer-links">
                    <h4>Resources</h4>
                    <ul>
                        <li><a href="index.php?page=docs">Documentation</a></li>
                        <li><a href="index.php?page=install">Installation Guide</a></li>
                        <li><a href="index.php?page=use-cases">Use Cases</a></li>
                        <li><a href="<?php echo GITHUB_URL; ?>" target="_blank" rel="noopener">GitHub Repository</a></li>
                    </ul>
                </div>
                <div class="footer-links">
                    <h4>Community</h4>
                    <ul>
                        <li><a href="index.php?page=forum">Forum</a></li>
                        <li><a href="index.php?page=blog">Blog & Updates</a></li>
                        <li><a href="<?php echo GITHUB_URL; ?>/issues" target="_blank" rel="noopener">Report Issues</a></li>
                        <li><a href="<?php echo GITHUB_URL; ?>/blob/main/CONTRIBUTING.md" target="_blank" rel="noopener">Contributing</a></li>
                    </ul>
                </div>
                <div class="footer-links">
                    <h4>About</h4>
                    <ul>
                        <li><a href="index.php?page=about">Our Team</a></li>
                        <li><a href="index.php?page=publications">Publications & Talks</a></li>
                        <li><a href="https://www.universiteitleiden.nl" target="_blank" rel="noopener">Leiden University</a></li>
                        <li><a href="mailto:<?php echo CONTACT_EMAIL; ?>">Contact</a></li>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; <?php echo date('Y'); ?> ARIbrain. Licensed under MIT License.</p>
                <p>Developed at <a href="https://www.universiteitleiden.nl" target="_blank" rel="noopener">Leiden University</a></p>
            </div>
        </div>
    </footer>
    <script src="assets/js/main.js"></script>
</body>
</html>
