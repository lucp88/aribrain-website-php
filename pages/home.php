<section class="hero">
    <div class="container">
        <div class="hero-content">
            <div class="hero-badge">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"/>
                    <path d="M12 6v6l4 2"/>
                </svg>
                Open Source Neuroimaging Tool
            </div>
            <h1>Precise fMRI Analysis with <span>ARIbrain</span></h1>
            <p class="hero-subtitle">
                A desktop application for All-Resolutions Inference in functional MRI. 
                Quantify the true proportion of active voxels within any brain region 
                using rigorous statistical methods developed at Leiden University.
            </p>
            <div class="hero-actions">
                <a href="index.php?page=install" class="btn btn-primary btn-lg">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                        <polyline points="7 10 12 15 17 10"/>
                        <line x1="12" y1="15" x2="12" y2="3"/>
                    </svg>
                    Download App
                </a>
                <a href="index.php?page=docs" class="btn btn-outline btn-lg">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                        <polyline points="14 2 14 8 20 8"/>
                        <line x1="16" y1="13" x2="8" y2="13"/>
                        <line x1="16" y1="17" x2="8" y2="17"/>
                    </svg>
                    Documentation
                </a>
            </div>
        </div>
        <div class="hero-visual">
            <img src="assets/images/aribrain-ui.png" alt="ARIbrain Application Interface" class="app-screenshot">
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="section-header">
            <h2>Why ARIbrain?</h2>
            <p>Traditional cluster-wise inference methods suffer from the "spatial specificity paradox" - 
               larger clusters may be statistically significant but contain a low proportion of truly active voxels. 
               ARIbrain solves this with rigorous True Discovery Proportion estimation.</p>
        </div>
        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-icon">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M12 20V10"/>
                        <path d="M18 20V4"/>
                        <path d="M6 20v-4"/>
                    </svg>
                </div>
                <h3>True Discovery Proportion</h3>
                <p>Calculate rigorous lower bounds for the proportion of truly active voxels within any selected cluster, providing statistically valid inference.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="3"/>
                        <path d="M12 1v6m0 6v10"/>
                        <path d="M1 12h6m6 0h10"/>
                    </svg>
                </div>
                <h3>Multi-Resolution Analysis</h3>
                <p>Perform inference at any spatial scale - from large anatomical regions down to individual voxels - with full statistical control.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="2" y="3" width="20" height="14" rx="2" ry="2"/>
                        <line x1="8" y1="21" x2="16" y2="21"/>
                        <line x1="12" y1="17" x2="12" y2="21"/>
                    </svg>
                </div>
                <h3>Interactive Visualization</h3>
                <p>Explore activation maps with an intuitive PyQt interface. View TDP estimates alongside traditional statistical maps in real-time.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/>
                        <polyline points="3.27 6.96 12 12.01 20.73 6.96"/>
                        <line x1="12" y1="22.08" x2="12" y2="12"/>
                    </svg>
                </div>
                <h3>Template Alignment</h3>
                <p>Align statistical maps to standard brain templates (MNI, etc.), ensuring consistency across studies and subjects.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                        <circle cx="9" cy="7" r="4"/>
                        <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                        <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                    </svg>
                </div>
                <h3>Works with Your Tools</h3>
                <p>Import statistical maps from SPM, FSL, and AFNI. No need to change your existing preprocessing pipeline.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="16 18 22 12 16 6"/>
                        <polyline points="8 6 2 12 8 18"/>
                    </svg>
                </div>
                <h3>Open Source</h3>
                <p>Fully open source under MIT license. Built with Python and PyQt for transparency and extensibility.</p>
            </div>
        </div>
    </div>
</section>

<section class="section section-alt">
    <div class="container">
        <div class="workflow-section">
            <div class="workflow-content">
                <h2>How It Works</h2>
                <p>ARIbrain provides an intuitive graphical interface for All-Resolutions Inference, making advanced statistical analysis accessible to all neuroimaging researchers.</p>
                <ul class="workflow-steps">
                    <li>
                        <span class="step-number">1</span>
                        <div class="step-content">
                            <h4>Load Your Data</h4>
                            <p>Open statistical maps (t-maps, z-maps) from your fMRI analysis in NIfTI format.</p>
                        </div>
                    </li>
                    <li>
                        <span class="step-number">2</span>
                        <div class="step-content">
                            <h4>Explore Interactively</h4>
                            <p>Navigate through brain slices, adjust thresholds, and select regions of interest with point-and-click simplicity.</p>
                        </div>
                    </li>
                    <li>
                        <span class="step-number">3</span>
                        <div class="step-content">
                            <h4>Compute TDP Bounds</h4>
                            <p>Get instant lower bounds on the True Discovery Proportion for any selected cluster or region.</p>
                        </div>
                    </li>
                    <li>
                        <span class="step-number">4</span>
                        <div class="step-content">
                            <h4>Export Results</h4>
                            <p>Save your analyses, generate publication-ready figures, and export data for further processing.</p>
                        </div>
                    </li>
                </ul>
            </div>
            <div class="workflow-visual">
                <img src="assets/images/aribrain-ui.png" alt="ARIbrain Workflow" class="app-screenshot">
            </div>
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="section-header">
            <h2>Compatible With Your Analysis Pipeline</h2>
            <p>ARIbrain works seamlessly with statistical maps from the tools you already use.</p>
        </div>
        <div class="integration-logos">
            <div class="integration-logo">
                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <rect x="3" y="3" width="18" height="18" rx="2"/>
                    <path d="M7 7h10M7 12h10M7 17h6"/>
                </svg>
                SPM
            </div>
            <div class="integration-logo">
                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <circle cx="12" cy="12" r="9"/>
                    <path d="M12 3v18M3 12h18"/>
                </svg>
                FSL
            </div>
            <div class="integration-logo">
                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <path d="M12 2L2 7l10 5 10-5-10-5z"/>
                    <path d="M2 17l10 5 10-5"/>
                    <path d="M2 12l10 5 10-5"/>
                </svg>
                AFNI
            </div>
            <div class="integration-logo">
                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <path d="M12 2a10 10 0 1 0 10 10"/>
                    <path d="M12 12l8-8"/>
                    <circle cx="20" cy="4" r="2"/>
                </svg>
                NIfTI
            </div>
        </div>
    </div>
</section>

<section class="section section-alt">
    <div class="container">
        <div class="stats-grid">
            <div class="stat-item">
                <span class="stat-value">100%</span>
                <span class="stat-label">Open Source</span>
            </div>
            <div class="stat-item">
                <span class="stat-value">3</span>
                <span class="stat-label">Platforms Supported</span>
            </div>
            <div class="stat-item">
                <span class="stat-value">PyQt</span>
                <span class="stat-label">Native Desktop App</span>
            </div>
            <div class="stat-item">
                <span class="stat-value">MIT</span>
                <span class="stat-label">License</span>
            </div>
        </div>
    </div>
</section>

<section class="cta-section">
    <div class="container">
        <h2>Ready to Enhance Your fMRI Analysis?</h2>
        <p>Download ARIbrain and start getting more interpretable results from your neuroimaging studies.</p>
        <div class="hero-actions">
            <a href="index.php?page=install" class="btn btn-primary btn-lg">
                Download Now
            </a>
            <a href="<?php echo GITHUB_URL; ?>" class="btn btn-outline btn-lg" target="_blank" rel="noopener" style="border-color: rgba(255,255,255,0.3); color: white;">
                View on GitHub
            </a>
        </div>
    </div>
</section>
