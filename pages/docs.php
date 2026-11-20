<?php $pageTitle = 'User Guide'; ?>

<div class="page-header">
    <div class="container">
        <h1>ARIbrain User Guide</h1>
        <p>A comprehensive guide for researchers using ARIbrain to perform cluster-based statistical inference on neuroimaging data.</p>
    </div>
</div>

<div class="container">
    <div class="docs-layout">
        <nav class="docs-nav">
            <h3>Quick Navigation</h3>
            <a href="#introduction" class="docs-nav-item">Introduction</a>
            <a href="#getting-started" class="docs-nav-item">Getting Started</a>
            <a href="#setting-up" class="docs-nav-item">Setting Up Your Analysis</a>
            <a href="#main-interface" class="docs-nav-item">The Main Interface</a>
            <a href="#brain-navigation" class="docs-nav-item">Brain Navigation</a>
            <a href="#statistical-analysis" class="docs-nav-item">Statistical Analysis</a>
            <a href="#cluster-table" class="docs-nav-item">Working with the Cluster Table</a>
            <a href="#cluster-workstation" class="docs-nav-item">Cluster Work Station</a>
            <a href="#multiple-maps" class="docs-nav-item">Managing Multiple Maps</a>
            <a href="#saving-loading" class="docs-nav-item">Saving and Loading Projects</a>
            <a href="#exporting" class="docs-nav-item">Exporting Results</a>
            <a href="#troubleshooting" class="docs-nav-item">Troubleshooting</a>
            <a href="#glossary" class="docs-nav-item">Glossary</a>
            <a href="#quick-reference" class="docs-nav-item">Quick Reference</a>
        </nav>

        <div class="docs-content">
            <section id="introduction" class="docs-section">
                <h2>Introduction</h2>
                <p>ARIbrain is a desktop application for performing cluster-based statistical inference on neuroimaging data using the All-Resolutions Inference (ARI) framework. It enables researchers to:</p>
                <ul class="docs-list">
                    <li>Visualize statistical brain maps in 2D orthogonal views and 3D renderings</li>
                    <li>Apply statistically principled thresholding using True Discovery Proportion (TDP)</li>
                    <li>Interactively explore and adjust cluster boundaries</li>
                    <li>Export publication-ready results and reports</li>
                </ul>
                <p>ARI provides a powerful alternative to traditional cluster-based inference by offering:</p>
                <div class="docs-features">
                    <div class="docs-feature">
                        <div class="docs-feature-icon">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2L2 7l10 5 10-5-10-5z"/><path d="M2 17l10 5 10-5"/><path d="M2 12l10 5 10-5"/></svg>
                        </div>
                        <div>
                            <strong>Simultaneous inference</strong>
                            <span>at all possible thresholds</span>
                        </div>
                    </div>
                    <div class="docs-feature">
                        <div class="docs-feature-icon">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                        </div>
                        <div>
                            <strong>TDP guarantees</strong>
                            <span>for each identified cluster</span>
                        </div>
                    </div>
                    <div class="docs-feature">
                        <div class="docs-feature-icon">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
                        </div>
                        <div>
                            <strong>Flexible exploration</strong>
                            <span>without inflating false positive rates</span>
                        </div>
                    </div>
                </div>
            </section>

            <section id="getting-started" class="docs-section">
                <h2>Getting Started</h2>
                
                <h3>Launching the Application</h3>
                <p><strong>From Terminal (recommended for troubleshooting):</strong></p>
                <div class="code-block">
                    <pre><span class="prompt">$</span> aribrain</pre>
                </div>
                <p><strong>From macOS Application:</strong></p>
                <p>Double-click <code>ARIbrain.app</code> in your Applications folder or wherever you installed it.</p>

                <h3>The Landing Screen</h3>
                <p>When ARIbrain launches, you'll see the landing screen with two options:</p>
                <div class="docs-table-wrapper">
                    <table class="docs-table">
                        <thead>
                            <tr>
                                <th>Button</th>
                                <th>Description</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><strong>New Project</strong></td>
                                <td>Start a fresh analysis with new data</td>
                            </tr>
                            <tr>
                                <td><strong>Load Project</strong></td>
                                <td>Resume a previously saved analysis session</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>

            <section id="setting-up" class="docs-section">
                <h2>Setting Up Your Analysis</h2>
                <p>After clicking <strong>New Project</strong>, you'll be guided through the setup process.</p>

                <h3>Loading a Statistical Map</h3>
                <ol class="docs-list docs-list-numbered">
                    <li>Click the <strong>Browse</strong> button next to the file input field</li>
                    <li>Navigate to your NIfTI file (<code>.nii</code> or <code>.nii.gz</code>)</li>
                    <li>Select your statistical map file</li>
                    <li>A preview of your data will appear in the orthogonal view panels on the right</li>
                </ol>
                <p><strong>Supported file formats:</strong></p>
                <ul class="docs-list">
                    <li>Uncompressed NIfTI (<code>.nii</code>)</li>
                    <li>Compressed NIfTI (<code>.nii.gz</code>)</li>
                </ul>

                <h3>Selecting Map Type</h3>
                <p>ARIbrain will attempt to automatically detect your map type from the NIfTI header. If detection fails, or you need to override it, select from:</p>
                <div class="docs-table-wrapper">
                    <table class="docs-table">
                        <thead>
                            <tr>
                                <th>Map Type</th>
                                <th>Description</th>
                                <th>Additional Input</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><strong>z-map</strong></td>
                                <td>Z-score statistical map</td>
                                <td>None</td>
                            </tr>
                            <tr>
                                <td><strong>t-map</strong></td>
                                <td>T-statistic map</td>
                                <td>Degrees of freedom (required)</td>
                            </tr>
                            <tr>
                                <td><strong>p-map</strong></td>
                                <td>P-value map</td>
                                <td>None</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="docs-note">
                    <strong>Note for t-maps:</strong> You will be prompted to enter the degrees of freedom. This is essential for accurate p-value conversion.
                </div>

                <h3>Choosing a Background Template</h3>
                <p>Select the anatomical reference for visualization:</p>
                <div class="docs-table-wrapper">
                    <table class="docs-table">
                        <thead>
                            <tr>
                                <th>Option</th>
                                <th>Description</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><strong>Raw Data</strong></td>
                                <td>Use your statistical map as background (no template)</td>
                            </tr>
                            <tr>
                                <td><strong>MNI Template T1</strong></td>
                                <td>Standard MNI152 T1-weighted template</td>
                            </tr>
                            <tr>
                                <td><strong>MNI Template GM</strong></td>
                                <td>MNI152 grey matter probability template</td>
                            </tr>
                            <tr>
                                <td><strong>Upload Template</strong></td>
                                <td>Use your own anatomical template</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <p>After configuring these options, click <strong>Next</strong> to proceed. ARIbrain will automatically run the ARI analysis, which may take a few moments depending on your data size.</p>
            </section>

            <section id="main-interface" class="docs-section">
                <h2>The Main Interface</h2>

                <h3>Interface Overview</h3>
                <p>The main interface is divided into several functional areas:</p>
                <div class="docs-screenshot">
                    <img src="assets/images/aribrain-ui.png" alt="ARIbrain User Interface" class="docs-screenshot-img">
                    <p class="docs-screenshot-caption">ARIbrain main interface showing orthogonal views, 3D cluster viewer, and analysis panels</p>
                </div>

                <h3>Left Sidebar</h3>
                <p>The left sidebar manages your analysis resources:</p>
                <div class="docs-card">
                    <h4>Statistical Images</h4>
                    <ul class="docs-list">
                        <li>Lists all loaded statistical maps</li>
                        <li>The "ARI" label turns green when analysis is complete</li>
                        <li>Click <strong>Add</strong> to load additional maps</li>
                        <li>Click <strong>Set</strong> to switch between maps</li>
                    </ul>
                </div>
                <div class="docs-card">
                    <h4>Template</h4>
                    <ul class="docs-list">
                        <li>Lists available background templates</li>
                        <li>Click <strong>Add</strong> to upload custom templates</li>
                        <li>Click <strong>Set</strong> to change the current template</li>
                    </ul>
                </div>
                <div class="docs-card">
                    <h4>Atlas</h4>
                    <ul class="docs-list">
                        <li>Shows available anatomical atlases (AAL2 by default)</li>
                        <li>Used for region labeling in the metrics panel</li>
                    </ul>
                </div>

                <h3>Orthogonal Brain Views</h3>
                <p>Three synchronized 2D slice views showing:</p>
                <div class="docs-table-wrapper">
                    <table class="docs-table">
                        <thead>
                            <tr>
                                <th>View</th>
                                <th>Orientation</th>
                                <th>Plane</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><strong>Sagittal</strong></td>
                                <td>Left-Right</td>
                                <td>YZ plane</td>
                            </tr>
                            <tr>
                                <td><strong>Coronal</strong></td>
                                <td>Front-Back</td>
                                <td>XZ plane</td>
                            </tr>
                            <tr>
                                <td><strong>Axial</strong></td>
                                <td>Top-Bottom</td>
                                <td>XY plane</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <p>Each view displays:</p>
                <ul class="docs-list">
                    <li>The anatomical template (grayscale)</li>
                    <li>Cluster overlay (colored)</li>
                    <li>Crosshairs indicating current position</li>
                    <li>Orientation labels (L/R, A/P, S/I)</li>
                </ul>

                <h3>3D Cluster Viewer</h3>
                <p>An interactive 3D rendering showing:</p>
                <ul class="docs-list">
                    <li>Semi-transparent brain surface for spatial context</li>
                    <li>The currently selected cluster in its assigned color</li>
                    <li>XYZ axis lines for orientation reference</li>
                </ul>
                <div class="docs-controls">
                    <h4>3D Viewer Controls:</h4>
                    <div class="docs-control-grid">
                        <div class="docs-control-item">
                            <span class="docs-control-key">Click + Drag</span>
                            <span>Rotate</span>
                        </div>
                        <div class="docs-control-item">
                            <span class="docs-control-key">Scroll Wheel</span>
                            <span>Zoom</span>
                        </div>
                        <div class="docs-control-item">
                            <span class="docs-control-key">Shift + Click + Drag</span>
                            <span>Pan</span>
                        </div>
                        <div class="docs-control-item">
                            <span class="docs-control-key">⏸ Button</span>
                            <span>Pause updates (improves performance)</span>
                        </div>
                        <div class="docs-control-item">
                            <span class="docs-control-key">⧉ Button</span>
                            <span>Undock to separate window</span>
                        </div>
                    </div>
                </div>

                <h3>Metrics Panel</h3>
                <p>Displays real-time information about the current crosshair position:</p>
                <div class="docs-table-wrapper">
                    <table class="docs-table">
                        <thead>
                            <tr>
                                <th>Metric</th>
                                <th>Description</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><strong>Dimensions</strong></td>
                                <td>Size of the current image (voxels)</td>
                            </tr>
                            <tr>
                                <td><strong>Voxel Size</strong></td>
                                <td>Physical dimensions of each voxel (mm)</td>
                            </tr>
                            <tr>
                                <td><strong>Cross Hair (xyz)</strong></td>
                                <td>Current position in voxel coordinates</td>
                            </tr>
                            <tr>
                                <td><strong>MNI (xyz)</strong></td>
                                <td>Current position in MNI coordinates (mm)</td>
                            </tr>
                            <tr>
                                <td><strong>Cluster ID</strong></td>
                                <td>The cluster at the current position (if any)</td>
                            </tr>
                            <tr>
                                <td><strong>Region (AAL2)</strong></td>
                                <td>Anatomical region from the AAL2 atlas</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>

            <section id="brain-navigation" class="docs-section">
                <h2>Brain Navigation</h2>

                <h3>Navigating with Crosshairs</h3>
                <div class="docs-card">
                    <h4>Click Navigation</h4>
                    <ul class="docs-list">
                        <li>Click anywhere in any orthogonal view to move the crosshairs to that location</li>
                        <li>All three views update simultaneously</li>
                    </ul>
                </div>
                <div class="docs-card">
                    <h4>Drag Navigation</h4>
                    <ul class="docs-list">
                        <li>Click and drag in any view to continuously update the position</li>
                        <li>Useful for exploring along a specific axis</li>
                    </ul>
                </div>

                <h3>Using Coordinate Controls</h3>
                <p>Located near the orthogonal views, coordinate controls allow precise navigation:</p>
                <div class="docs-card">
                    <h4>XYZ Spinboxes</h4>
                    <ul class="docs-list">
                        <li>Enter exact voxel coordinates</li>
                        <li>Use arrow buttons for fine adjustments</li>
                        <li>Press Enter to confirm and navigate</li>
                    </ul>
                </div>

                <h3>Understanding Orientation Labels</h3>
                <p>Each view displays anatomical orientation labels at its edges:</p>
                <div class="docs-table-wrapper">
                    <table class="docs-table">
                        <thead>
                            <tr>
                                <th>Label</th>
                                <th>Full Name</th>
                                <th>Meaning</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><strong>L</strong></td>
                                <td>Left</td>
                                <td>Patient's left side</td>
                            </tr>
                            <tr>
                                <td><strong>R</strong></td>
                                <td>Right</td>
                                <td>Patient's right side</td>
                            </tr>
                            <tr>
                                <td><strong>A</strong></td>
                                <td>Anterior</td>
                                <td>Front of the brain</td>
                            </tr>
                            <tr>
                                <td><strong>P</strong></td>
                                <td>Posterior</td>
                                <td>Back of the brain</td>
                            </tr>
                            <tr>
                                <td><strong>S</strong></td>
                                <td>Superior</td>
                                <td>Top of the brain</td>
                            </tr>
                            <tr>
                                <td><strong>I</strong></td>
                                <td>Inferior</td>
                                <td>Bottom of the brain</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <p>Labels are determined from the NIfTI sform matrix and accurately reflect your data's orientation.</p>
            </section>

            <section id="statistical-analysis" class="docs-section">
                <h2>Statistical Analysis</h2>

                <h3>Understanding the ARI Analysis</h3>
                <p>When you load data, ARIbrain automatically performs All-Resolutions Inference:</p>
                <ol class="docs-list docs-list-numbered">
                    <li><strong>Hommel correction</strong> computes the whole-brain TDP</li>
                    <li><strong>Adjacency mapping</strong> identifies voxel neighborhoods</li>
                    <li><strong>Cluster tree</strong> builds a hierarchical cluster structure</li>
                    <li><strong>TDP computation</strong> calculates TDP for all possible clusters</li>
                    <li><strong>Gradient map</strong> visualizes the maximum TDP at each voxel</li>
                </ol>
                <p>The gradient map you see initially shows, for each voxel, the highest TDP threshold at which that voxel would be included in a cluster.</p>

                <h3>Whole-Brain Thresholding</h3>
                <p>Access via the <strong>Whole Brain Thresholding</strong> tab.</p>
                <div class="docs-table-wrapper">
                    <table class="docs-table">
                        <thead>
                            <tr>
                                <th>Control</th>
                                <th>Function</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><strong>Slider</strong></td>
                                <td>Adjust threshold value</td>
                            </tr>
                            <tr>
                                <td><strong>- / +</strong></td>
                                <td>Fine adjustment (±0.01)</td>
                            </tr>
                            <tr>
                                <td><strong>Text box</strong></td>
                                <td>Enter exact threshold value</td>
                            </tr>
                            <tr>
                                <td><strong>Run</strong></td>
                                <td>Apply the entered threshold</td>
                            </tr>
                            <tr>
                                <td><strong>Dropdown</strong></td>
                                <td>Switch between TDP-based and Z-score based methods</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <h3>TDP-Based Thresholding</h3>
                <p><strong>What it does:</strong> Identifies clusters where at least X% of voxels are truly active.</p>
                <p><strong>How to use:</strong></p>
                <ol class="docs-list docs-list-numbered">
                    <li>Select "TDP-based" from the dropdown</li>
                    <li>Set your desired TDP threshold (e.g., 0.75 for 75%)</li>
                    <li>The slider adjusts from the minimum achievable TDP to 1.0</li>
                    <li>Higher values = more stringent = smaller/fewer clusters</li>
                </ol>
                <div class="docs-note docs-note-info">
                    <strong>Interpretation:</strong> A TDP of 0.75 means that at least 75% of voxels in each displayed cluster are truly active (with statistical guarantee at alpha = 0.05).
                </div>

                <h3>Z-Score Based Thresholding</h3>
                <p><strong>What it does:</strong> Traditional voxel-wise thresholding followed by cluster-level TDP computation.</p>
                <p><strong>How to use:</strong></p>
                <ol class="docs-list docs-list-numbered">
                    <li>Select "Z-score based" from the dropdown</li>
                    <li>Set your Z-score threshold (e.g., 3.0)</li>
                    <li>Clusters are formed from contiguous voxels exceeding this threshold</li>
                    <li>TDP is computed for each resulting cluster</li>
                </ol>
                <div class="docs-note docs-note-info">
                    <strong>Interpretation:</strong> Each cluster's TDP tells you the proportion of truly active voxels within that cluster.
                </div>
            </section>

            <section id="cluster-table" class="docs-section">
                <h2>Working with the Cluster Table</h2>

                <h3>Reading the Table</h3>
                <p>Access via the <strong>Cluster Analysis</strong> tab. The table shows all clusters meeting your current threshold.</p>

                <h3>Understanding Table Columns</h3>
                <div class="docs-table-wrapper">
                    <table class="docs-table">
                        <thead>
                            <tr>
                                <th>Column</th>
                                <th>Description</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><strong>Cluster</strong></td>
                                <td>Cluster number (corresponds to exported NIfTI labels)</td>
                            </tr>
                            <tr>
                                <td><strong>Unique ID</strong></td>
                                <td>Internal identifier for tracking across threshold changes</td>
                            </tr>
                            <tr>
                                <td><strong>Size</strong></td>
                                <td>Number of voxels in the cluster</td>
                            </tr>
                            <tr>
                                <td><strong>TDP</strong></td>
                                <td>True Discovery Proportion (0-1)</td>
                            </tr>
                            <tr>
                                <td><strong>max(Z)</strong></td>
                                <td>Maximum Z-score within the cluster</td>
                            </tr>
                            <tr>
                                <td><strong>Vox (x, y, z)</strong></td>
                                <td>Voxel coordinates of the local maximum</td>
                            </tr>
                            <tr>
                                <td><strong>MNI (x, y, z)</strong></td>
                                <td>MNI coordinates of the local maximum</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <h3>Selecting Clusters</h3>
                <p><strong>To select a cluster:</strong></p>
                <ol class="docs-list docs-list-numbered">
                    <li>Click on any row in the table</li>
                    <li>The selected cluster is highlighted in the orthogonal views</li>
                    <li>The 3D viewer shows only that cluster</li>
                    <li>The crosshairs move to the cluster's peak voxel</li>
                </ol>
                <p><strong>Visual feedback:</strong></p>
                <ul class="docs-list">
                    <li>Selected cluster: Full opacity in views</li>
                    <li>Non-selected clusters: Reduced opacity</li>
                    <li>Table row: Green highlight</li>
                </ul>
            </section>

            <section id="cluster-workstation" class="docs-section">
                <h2>Cluster Work Station</h2>
                <p>The <strong>Selected Cluster Work Station</strong> appears below the table when a cluster is selected.</p>

                <h3>Interactive TDP Adjustment</h3>
                <p>Adjust the TDP threshold for the selected cluster only:</p>
                <div class="docs-table-wrapper">
                    <table class="docs-table">
                        <thead>
                            <tr>
                                <th>Control</th>
                                <th>Function</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><strong>Slider</strong></td>
                                <td>Adjust cluster-specific TDP</td>
                            </tr>
                            <tr>
                                <td><strong>- / +</strong></td>
                                <td>Fine adjustment (±0.01)</td>
                            </tr>
                            <tr>
                                <td><strong>Text box</strong></td>
                                <td>Enter exact TDP value</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <p><strong>What happens when you adjust:</strong></p>
                <ul class="docs-list">
                    <li>Lowering TDP: Cluster grows (includes more voxels)</li>
                    <li>Raising TDP: Cluster shrinks (only highest-confidence voxels remain)</li>
                </ul>

                <h3>Changing Cluster Size</h3>
                <p>As you adjust the TDP slider:</p>
                <ol class="docs-list docs-list-numbered">
                    <li>The cluster boundary updates in real-time</li>
                    <li>The table values update to reflect the new size</li>
                    <li>Other clusters remain unchanged</li>
                </ol>
                <div class="docs-note">
                    <strong>Note:</strong> Clusters with TDP = 0 cannot be adjusted (no truly active voxels detected).
                </div>

                <h3>State History Navigation</h3>
                <p>Use the ↺ and ↻ buttons to navigate through cluster modification history:</p>
                <ul class="docs-list">
                    <li><strong>↺</strong> Previous state</li>
                    <li><strong>↻</strong> Next state</li>
                </ul>
            </section>

            <section id="multiple-maps" class="docs-section">
                <h2>Managing Multiple Statistical Maps</h2>
                <p>ARIbrain supports analyzing multiple statistical maps simultaneously.</p>

                <h3>To add another map:</h3>
                <ol class="docs-list docs-list-numbered">
                    <li>Click <strong>Add</strong> in the Statistical Images section</li>
                    <li>Browse and select your NIfTI file</li>
                    <li>Configure map type if needed</li>
                    <li>The new map appears in the list</li>
                </ol>

                <h3>To switch between maps:</h3>
                <ol class="docs-list docs-list-numbered">
                    <li>Click on the desired map in the list</li>
                    <li>Click <strong>Set</strong></li>
                    <li>If ARI hasn't been run, you'll be prompted</li>
                </ol>

                <div class="docs-card">
                    <h4>Indicator</h4>
                    <ul class="docs-list">
                        <li><span class="docs-indicator docs-indicator-grey">ARI</span> Grey "ARI" label = Analysis not yet performed</li>
                        <li><span class="docs-indicator docs-indicator-green">ARI</span> Green "ARI" label = Analysis complete</li>
                    </ul>
                </div>
            </section>

            <section id="saving-loading" class="docs-section">
                <h2>Saving and Loading Projects</h2>

                <h3>Saving a Project</h3>
                <p>Access via the <strong>Save & Export</strong> tab.</p>
                <ol class="docs-list docs-list-numbered">
                    <li>Click <strong>Save Project</strong></li>
                    <li>Choose a location and filename</li>
                    <li>The project saves as an <code>.ari</code> file</li>
                </ol>
                <div class="docs-card">
                    <h4>What's saved:</h4>
                    <ul class="docs-list">
                        <li>All loaded statistical maps and their ARI results</li>
                        <li>Current thresholds and settings</li>
                        <li>Cluster modifications</li>
                        <li>Template configurations</li>
                        <li>UI state (selected clusters, crosshair positions)</li>
                    </ul>
                </div>

                <h3>Loading a Project</h3>
                <div class="docs-card">
                    <h4>From the landing screen:</h4>
                    <ol class="docs-list docs-list-numbered">
                        <li>Click <strong>Load Project</strong></li>
                        <li>Navigate to your <code>.ari</code> file</li>
                        <li>The entire session restores automatically</li>
                    </ol>
                </div>
                <div class="docs-card">
                    <h4>From within the application:</h4>
                    <ol class="docs-list docs-list-numbered">
                        <li>Go to <strong>Save & Export</strong> tab</li>
                        <li>Click <strong>Load Project</strong></li>
                        <li>Select your <code>.ari</code> file</li>
                    </ol>
                </div>
            </section>

            <section id="exporting" class="docs-section">
                <h2>Exporting Results</h2>

                <h3>Export Options</h3>
                <p>Click <strong>Export Results</strong> in the <strong>Save & Export</strong> tab to export all results to a selected directory.</p>

                <h3>Understanding Exported Files</h3>
                <div class="docs-table-wrapper">
                    <table class="docs-table">
                        <thead>
                            <tr>
                                <th>File</th>
                                <th>Format</th>
                                <th>Description</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><code>cluster_table_N.csv</code></td>
                                <td>CSV</td>
                                <td>Cluster statistics table for each statistical map</td>
                            </tr>
                            <tr>
                                <td><code>cluster_map_filename_N.nii.gz</code></td>
                                <td>NIfTI</td>
                                <td>3D cluster label image (values = cluster numbers)</td>
                            </tr>
                            <tr>
                                <td><code>3d_cluster_view.png</code></td>
                                <td>PNG</td>
                                <td>Screenshot of the current 3D view</td>
                            </tr>
                            <tr>
                                <td><code>full_report.html</code></td>
                                <td>HTML</td>
                                <td>Interactive report with tables and images</td>
                            </tr>
                            <tr>
                                <td><code>ARI_report_table.pdf</code></td>
                                <td>PDF</td>
                                <td>Formatted cluster table for publication</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="docs-card">
                    <h4>Using the cluster map NIfTI:</h4>
                    <ul class="docs-list">
                        <li>Load in any NIfTI viewer (FSLeyes, MRIcron, etc.)</li>
                        <li>Voxel values correspond to cluster numbers in the CSV</li>
                        <li>Value 0 = background (no cluster)</li>
                        <li>Values 1-N = cluster labels</li>
                    </ul>
                </div>

                <div class="docs-card">
                    <h4>The HTML report includes:</h4>
                    <ul class="docs-list">
                        <li>Analysis settings and parameters</li>
                        <li>Interactive tabs for each statistical map</li>
                        <li>Cluster tables with all statistics</li>
                        <li>Embedded orthogonal view screenshots</li>
                        <li>Explanation of table columns</li>
                    </ul>
                </div>
            </section>

            <section id="troubleshooting" class="docs-section">
                <h2>Troubleshooting</h2>

                <h3>Common Issues</h3>
                <div class="troubleshoot-item">
                    <h4>"No significant brain activations can be detected"</h4>
                    <p>Your data may not contain statistically significant effects. Try using a different statistical threshold. Verify your input data is a valid statistical map.</p>
                </div>
                <div class="troubleshoot-item">
                    <h4>Clusters disappear when changing threshold</h4>
                    <p>This is expected behavior - higher thresholds are more stringent. Lower the TDP threshold to reveal more clusters.</p>
                </div>
                <div class="troubleshoot-item">
                    <h4>Application runs slowly</h4>
                    <p>Pause 3D updates using the ⏸ button. Close other applications to free memory. Consider using a subset of your data for initial exploration.</p>
                </div>
                <div class="troubleshoot-item">
                    <h4>Crosshairs don't align with expected coordinates</h4>
                    <p>Verify your NIfTI file has correct header information. Check that the sform/qform matrices are properly set.</p>
                </div>

                <h3>Getting Help</h3>
                <p>If you encounter issues:</p>
                <ol class="docs-list docs-list-numbered">
                    <li>Run from terminal to see error messages: <code>aribrain</code></li>
                    <li>Check the message log at the bottom of the application</li>
                    <li>Ask questions on our <a href="index.php?page=forum">community forum</a></li>
                    <li>Report issues at: <a href="https://github.com/AriBrain/ari-core/issues" target="_blank" rel="noopener">GitHub Issues</a></li>
                </ol>
            </section>

            <section id="glossary" class="docs-section">
                <h2>Glossary</h2>
                <div class="docs-table-wrapper">
                    <table class="docs-table docs-table-glossary">
                        <thead>
                            <tr>
                                <th>Term</th>
                                <th>Definition</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><strong>ARI</strong></td>
                                <td>All-Resolutions Inference - a statistical framework for simultaneous inference at all thresholds</td>
                            </tr>
                            <tr>
                                <td><strong>Cluster</strong></td>
                                <td>A contiguous group of voxels meeting the statistical threshold</td>
                            </tr>
                            <tr>
                                <td><strong>Crosshair</strong></td>
                                <td>The intersecting lines indicating the current position in 3D space</td>
                            </tr>
                            <tr>
                                <td><strong>Degrees of Freedom</strong></td>
                                <td>Statistical parameter required for t-distributions</td>
                            </tr>
                            <tr>
                                <td><strong>Gradient Map</strong></td>
                                <td>Visualization showing maximum achievable TDP at each voxel</td>
                            </tr>
                            <tr>
                                <td><strong>Hommel Correction</strong></td>
                                <td>A statistical method for controlling family-wise error rate</td>
                            </tr>
                            <tr>
                                <td><strong>Local Minimum</strong></td>
                                <td>The voxel with the most extreme statistic within a cluster</td>
                            </tr>
                            <tr>
                                <td><strong>MNI Coordinates</strong></td>
                                <td>Standardized brain coordinates in millimeters (Montreal Neurological Institute space)</td>
                            </tr>
                            <tr>
                                <td><strong>NIfTI</strong></td>
                                <td>Neuroimaging Informatics Technology Initiative - standard brain image format</td>
                            </tr>
                            <tr>
                                <td><strong>Orthogonal Views</strong></td>
                                <td>The three perpendicular 2D slice views (sagittal, coronal, axial)</td>
                            </tr>
                            <tr>
                                <td><strong>P-value</strong></td>
                                <td>Probability of observing the data under the null hypothesis</td>
                            </tr>
                            <tr>
                                <td><strong>Sform</strong></td>
                                <td>Affine transformation matrix in NIfTI headers defining spatial orientation</td>
                            </tr>
                            <tr>
                                <td><strong>TDP</strong></td>
                                <td>True Discovery Proportion - the proportion of truly active voxels in a cluster</td>
                            </tr>
                            <tr>
                                <td><strong>Template</strong></td>
                                <td>Anatomical reference image for visualization</td>
                            </tr>
                            <tr>
                                <td><strong>Thresholding</strong></td>
                                <td>Filtering data based on a statistical cutoff</td>
                            </tr>
                            <tr>
                                <td><strong>Voxel</strong></td>
                                <td>A 3D pixel - the smallest unit in a brain image</td>
                            </tr>
                            <tr>
                                <td><strong>Z-score</strong></td>
                                <td>Standardized statistic measuring deviation from the null hypothesis</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>

            <section id="quick-reference" class="docs-section">
                <h2>Quick Reference Card</h2>

                <h3>Keyboard & Mouse</h3>
                <div class="docs-table-wrapper">
                    <table class="docs-table">
                        <thead>
                            <tr>
                                <th>Action</th>
                                <th>Control</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Navigate slices</td>
                                <td>Click in any orthogonal view</td>
                            </tr>
                            <tr>
                                <td>Continuous navigation</td>
                                <td>Click and drag in views</td>
                            </tr>
                            <tr>
                                <td>Rotate 3D view</td>
                                <td>Click and drag in 3D viewer</td>
                            </tr>
                            <tr>
                                <td>Zoom 3D view</td>
                                <td>Scroll wheel</td>
                            </tr>
                            <tr>
                                <td>Select cluster</td>
                                <td>Click table row</td>
                            </tr>
                            <tr>
                                <td>Apply threshold</td>
                                <td>Press Enter in threshold textbox</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <h3>Key Thresholds</h3>
                <div class="docs-table-wrapper">
                    <table class="docs-table">
                        <thead>
                            <tr>
                                <th>TDP Value</th>
                                <th>Interpretation</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><strong>1.00</strong></td>
                                <td>All voxels in cluster are truly active</td>
                            </tr>
                            <tr>
                                <td><strong>0.75</strong></td>
                                <td>At least 75% of voxels are truly active</td>
                            </tr>
                            <tr>
                                <td><strong>0.50</strong></td>
                                <td>At least 50% of voxels are truly active</td>
                            </tr>
                            <tr>
                                <td><strong>0.00</strong></td>
                                <td>No evidence of truly active voxels</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <h3>File Extensions</h3>
                <div class="docs-table-wrapper">
                    <table class="docs-table">
                        <thead>
                            <tr>
                                <th>Extension</th>
                                <th>Type</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><code>.nii</code></td>
                                <td>Uncompressed NIfTI image</td>
                            </tr>
                            <tr>
                                <td><code>.nii.gz</code></td>
                                <td>Compressed NIfTI image</td>
                            </tr>
                            <tr>
                                <td><code>.ari</code></td>
                                <td>ARIbrain project file</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="docs-footer">
                    <p><em>Last updated: February 2025</em></p>
                    <p><em>ARIbrain Version 0.1.0</em></p>
                </div>
            </section>
        </div>
    </div>
</div>
