<?php $pageTitle = 'Installation'; ?>

<div class="page-header">
    <div class="container">
        <h1>Installation</h1>
        <p>Get ARIbrain up and running on your system in minutes</p>
    </div>
</div>

<section class="section">
    <div class="container">
        <div class="install-grid">
            <div class="install-nav">
                <h3>Quick Navigation</h3>
                <a href="#requirements" class="install-nav-item">Requirements</a>
                <a href="#macos-linux" class="install-nav-item">macOS & Linux</a>
                <a href="#windows" class="install-nav-item">Windows</a>
                <a href="#launch" class="install-nav-item">Launch ARIbrain</a>
                <a href="#troubleshooting" class="install-nav-item">Troubleshooting</a>
            </div>
            
            <div class="install-content">
                <div id="requirements" class="install-section">
                    <h2>Requirements</h2>
                    <p>Before installing ARIbrain, ensure your system meets the following requirements:</p>
                    
                    <div class="requirements-grid">
                        <div class="requirement-card">
                            <div class="requirement-icon">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M12 19l7-7 3 3-7 7-3-3z"></path>
                                    <path d="M18 13l-1.5-7.5L2 2l3.5 14.5L13 18l5-5z"></path>
                                    <path d="M2 2l7.586 7.586"></path>
                                    <circle cx="11" cy="11" r="2"></circle>
                                </svg>
                            </div>
                            <h4>Python 3.10.x</h4>
                            <p>Required version for compatibility</p>
                        </div>
                        <div class="requirement-card">
                            <div class="requirement-icon">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <rect x="4" y="4" width="16" height="16" rx="2" ry="2"></rect>
                                    <rect x="9" y="9" width="6" height="6"></rect>
                                    <line x1="9" y1="1" x2="9" y2="4"></line>
                                    <line x1="15" y1="1" x2="15" y2="4"></line>
                                    <line x1="9" y1="20" x2="9" y2="23"></line>
                                    <line x1="15" y1="20" x2="15" y2="23"></line>
                                    <line x1="20" y1="9" x2="23" y2="9"></line>
                                    <line x1="20" y1="14" x2="23" y2="14"></line>
                                    <line x1="1" y1="9" x2="4" y2="9"></line>
                                    <line x1="1" y1="14" x2="4" y2="14"></line>
                                </svg>
                            </div>
                            <h4>C Compiler</h4>
                            <p>For building extensions (macOS/Linux)</p>
                        </div>
                        <div class="requirement-card">
                            <div class="requirement-icon">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
                                </svg>
                            </div>
                            <h4>pipx</h4>
                            <p>For isolated package installation</p>
                        </div>
                        <div class="requirement-card">
                            <div class="requirement-icon">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path>
                                    <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path>
                                </svg>
                            </div>
                            <h4>Git</h4>
                            <p>Required for Windows installation</p>
                        </div>
                    </div>
                </div>
                
                <div id="macos-linux" class="install-section">
                    <h2>
                        <span class="install-os-icons">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><path d="M18.71 19.5c-.83 1.24-1.71 2.45-3.05 2.47-1.34.03-1.77-.79-3.29-.79-1.53 0-2 .77-3.27.82-1.31.05-2.3-1.32-3.14-2.53C4.25 17 2.94 12.45 4.7 9.39c.87-1.52 2.43-2.48 4.12-2.51 1.28-.02 2.5.87 3.29.87.78 0 2.26-1.07 3.81-.91.65.03 2.47.26 3.64 1.98-.09.06-2.17 1.28-2.15 3.81.03 3.02 2.65 4.03 2.68 4.04-.03.07-.42 1.44-1.38 2.83M13 3.5c.73-.83 1.94-1.46 2.94-1.5.13 1.17-.34 2.35-1.04 3.19-.69.85-1.83 1.51-2.95 1.42-.15-1.15.41-2.35 1.05-3.11z"/></svg>
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><path d="M12.504 0c-.155 0-.315.008-.48.021-4.226.333-3.105 4.807-3.17 6.298-.076 1.092-.3 1.953-1.05 3.02-.885 1.051-2.127 2.75-2.716 4.521-.278.832-.41 1.684-.287 2.489a.424.424 0 00-.11.135c-.26.268-.45.6-.663.839-.199.199-.485.267-.797.4-.313.136-.658.269-.864.68-.09.189-.136.394-.132.602 0 .199.027.4.055.536.058.399.116.728.04.97-.249.68-.28 1.145-.106 1.484.174.334.535.47.94.601.81.2 1.91.135 2.774.6.926.466 1.866.67 2.616.47.526-.116.97-.464 1.208-.946.587-.003 1.23-.269 2.26-.334.699-.058 1.574.267 2.577.2.025.134.063.198.114.333l.003.003c.391.778 1.113 1.132 1.884 1.071.771-.06 1.592-.536 2.257-1.306.631-.765 1.683-1.084 2.378-1.503.348-.199.629-.469.649-.853.023-.4-.2-.811-.714-1.376v-.097l-.003-.003c-.17-.2-.25-.535-.338-.926-.085-.401-.182-.786-.492-1.046h-.003c-.059-.054-.123-.067-.188-.135a.357.357 0 00-.19-.064c.431-1.278.264-2.55-.173-3.694-.533-1.41-1.465-2.638-2.175-3.483-.796-1.005-1.576-1.957-1.56-3.368.026-2.152.236-6.133-3.544-6.139zm.529 3.405h.013c.213 0 .396.062.584.198.19.135.33.332.438.533.105.259.158.459.166.724 0-.02.006-.04.006-.06v.105a.086.086 0 01-.004-.021l-.004-.024a1.807 1.807 0 01-.15.706.953.953 0 01-.213.335.71.71 0 00-.088-.042c-.104-.045-.198-.064-.284-.133a1.312 1.312 0 00-.22-.066c.05-.06.146-.133.183-.198.053-.128.082-.264.088-.402v-.02a1.21 1.21 0 00-.061-.4c-.045-.134-.101-.2-.183-.333-.084-.066-.167-.132-.267-.132h-.016c-.093 0-.176.03-.262.132a.8.8 0 00-.205.334 1.18 1.18 0 00-.09.4v.019c.002.089.008.179.02.267-.193-.067-.438-.135-.607-.202a1.635 1.635 0 01-.018-.2v-.02a1.772 1.772 0 01.15-.768c.082-.22.232-.406.43-.533a.985.985 0 01.594-.2zm-2.962.059h.036c.142 0 .27.048.399.135.146.129.264.288.344.465.09.199.14.4.153.667v.004c.007.134.006.2-.002.266v.08c-.03.007-.056.018-.083.024-.152.055-.274.135-.393.2.012-.09.013-.18.003-.267v-.015c-.012-.133-.04-.2-.082-.333a.613.613 0 00-.166-.267.248.248 0 00-.183-.064h-.021c-.071.006-.13.04-.186.132a.552.552 0 00-.12.27.944.944 0 00-.023.33v.015c.012.135.037.2.08.334.046.134.098.2.166.268.01.009.02.018.034.024-.07.057-.117.07-.176.136a.304.304 0 01-.131.068 2.62 2.62 0 01-.275-.402 1.772 1.772 0 01-.155-.667 1.759 1.759 0 01.08-.668 1.43 1.43 0 01.283-.535c.128-.133.26-.2.418-.2zm1.37 1.706c.332 0 .733.065 1.216.399.293.2.523.269 1.052.468h.003c.255.136.405.266.478.399v-.131a.571.571 0 01.016.47c-.123.31-.516.643-1.063.842v.002c-.268.135-.501.333-.775.465-.276.135-.588.292-1.012.267a1.139 1.139 0 01-.448-.067 3.566 3.566 0 01-.322-.198c-.195-.135-.363-.332-.612-.465v-.005h-.005c-.4-.246-.616-.512-.686-.71-.07-.268-.005-.47.193-.6.224-.135.38-.271.483-.336.104-.074.143-.102.176-.131h.002v-.003c.169-.202.436-.47.839-.601.139-.036.294-.065.466-.065zm2.8 2.142c.358 1.417 1.196 3.475 1.735 4.473.286.534.855 1.659 1.102 3.024.156-.005.33.018.513.064.646-1.671-.546-3.467-1.089-3.966-.22-.2-.232-.335-.123-.335.59.534 1.365 1.572 1.646 2.757.13.535.16 1.104.021 1.67.067.028.135.06.205.067 1.032.534 1.413.938 1.23 1.537v-.002c-.06-.135-.12-.2-.2-.334-.08-.135-.36-.468-.88-.468-.26 0-.514.063-.727.2-.078.035-.142.07-.193.136-.12.135-.06.2-.06.2s.013-.033.03-.065c.035-.067.08-.135.143-.135.063-.005.12 0 .207.064.094.066.2.135.293.135.226.136.49.266.85.4-.09.2-.18.4-.28.6-.14.269-.28.535-.45.735a1.42 1.42 0 01-.38.333c.512-.07.9-.333 1.09-.867.18-.535.12-1.27-.18-2.003v-.004a3.298 3.298 0 00-1.26-1.736c-.18-.133-.35-.2-.52-.266-.18-.067-.35-.135-.5-.267-.15-.133-.29-.333-.35-.6v-.003c-.06-.27-.03-.533.09-.8.12-.27.33-.47.59-.668.26-.197.55-.399.84-.665.29-.266.57-.535.82-.935.233-.399.434-.868.534-1.503a5.23 5.23 0 00.04-1.07v-.003c-.09.003-.18.003-.28 0h-.03c.03.135.06.2.09.4v.003c.04.2.05.4.04.67-.01.266-.04.533-.12.8-.08.27-.2.535-.37.8-.17.27-.39.535-.68.8a5.02 5.02 0 01-.87.533c-.31.2-.67.402-1.04.535-.37.135-.78.266-1.18.266-.39 0-.77-.066-1.09-.2-.32-.132-.58-.332-.77-.598a.987.987 0 01-.12-.4v-.003c-.01-.067-.02-.135-.02-.2-.01-.069-.01-.135-.01-.2 0-.066.01-.135.01-.2.01-.07.02-.135.03-.202.02-.133.06-.266.11-.4.1-.27.26-.535.49-.8.23-.27.52-.535.88-.801s.77-.535 1.23-.801c.46-.266.98-.532 1.55-.798.57-.266 1.18-.535 1.83-.868.59-.266.963-.668 1.17-1.07.103-.2.163-.4.18-.6.018-.2-.003-.4-.06-.6a1.592 1.592 0 00-.34-.6 1.24 1.24 0 00-.553-.333c-.195-.067-.39-.1-.6-.1a2.46 2.46 0 00-.593.066c-.2.067-.4.135-.59.267-.39.267-.7.67-.93 1.135-.23.47-.37.936-.42 1.47-.03.27-.03.533 0 .8v.003c-.03 0-.07-.003-.1-.003zm-4.84-.135c-.272 0-.478.203-.478.403s.18.4.443.4c.2 0 .378-.135.443-.333.072-.2-.06-.47-.408-.47zm.478 1.536c-.07.065-.142.135-.202.2-.06.067-.12.135-.17.202-.11.133-.2.266-.24.4a.768.768 0 00-.03.34c.02.2.09.332.2.466.09.133.2.2.32.265l.02.003c.07.067.14.135.23.2.1.067.2.135.32.2.12.066.26.135.4.2.27.135.56.266.87.4l-.003-.003a.28.28 0 01.05-.067c.03-.038.07-.1.12-.2.16-.266.25-.668.25-1.135 0-.066 0-.135-.003-.2v-.003a.66.66 0 00-.05-.2.47.47 0 00-.1-.2c-.06-.066-.14-.135-.24-.2-.18-.132-.45-.265-.8-.332a3.003 3.003 0 00-.93-.068zm6.79.333c-.7.003-1.4.135-2.02.4-.62.266-1.14.668-1.49 1.2-.35.535-.52 1.203-.44 1.87.08.668.38 1.337.88 1.87.51.534 1.2.935 2 1.135.8.2 1.67.2 2.5 0 .82-.202 1.58-.602 2.15-1.202.58-.601.97-1.403 1.07-2.27.09-.87-.08-1.8-.53-2.536-.44-.735-1.13-1.267-1.93-1.535-.6-.2-1.25-.27-1.89-.267a6.37 6.37 0 00-.29.003v-.002c-.02.002-.03.003-.03.003zm.11.669h.07c.5.005.97.068 1.39.2.52.165.97.47 1.28.87.31.4.49.935.46 1.468a2.28 2.28 0 01-.5 1.336 2.947 2.947 0 01-1.2.87c-.47.2-1 .298-1.52.265a3.032 3.032 0 01-1.42-.398 2.23 2.23 0 01-.88-.935c-.21-.4-.3-.87-.25-1.336.05-.468.24-.935.55-1.336.31-.4.73-.735 1.21-.935.35-.135.73-.2 1.12-.2h.02c.06-.003.12-.003.18-.003z"/></svg>
                        </span>
                        macOS & Linux
                    </h2>
                    
                    <div class="install-steps">
                        <div class="install-step">
                            <div class="step-number">1</div>
                            <div class="step-content">
                                <h4>Install pipx</h4>
                                <p>First, install pipx which allows you to install Python applications in isolated environments:</p>
                                <div class="code-block">
                                    <pre><code><span class="prompt">$</span> python3 -m pip install --user pipx
<span class="prompt">$</span> python3 -m pipx ensurepath</code></pre>
                                </div>
                                <p class="step-note">Restart your terminal after running these commands.</p>
                            </div>
                        </div>
                        
                        <div class="install-step">
                            <div class="step-number">2</div>
                            <div class="step-content">
                                <h4>Install ARIbrain</h4>
                                <p>Run the installation script with a single command:</p>
                                <div class="code-block">
                                    <pre><code><span class="prompt">$</span> curl -sSL https://raw.githubusercontent.com/AriBrain/ari-core/main/install.sh | bash</code></pre>
                                </div>
                            </div>
                        </div>
                        
                        <div class="install-step">
                            <div class="step-number">3</div>
                            <div class="step-content">
                                <h4>Verify Installation</h4>
                                <p>Check that ARIbrain was installed correctly:</p>
                                <div class="code-block">
                                    <pre><code><span class="prompt">$</span> aribrain --version</code></pre>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div id="windows" class="install-section">
                    <h2>
                        <span class="install-os-icons">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><path d="M0 3.449L9.75 2.1v9.451H0m10.949-9.602L24 0v11.4H10.949M0 12.6h9.75v9.451L0 20.699M10.949 12.6H24V24l-12.9-1.801"/></svg>
                        </span>
                        Windows
                    </h2>
                    
                    <div class="install-steps">
                        <div class="install-step">
                            <div class="step-number">1</div>
                            <div class="step-content">
                                <h4>Install Python 3.10</h4>
                                <p>Download and install Python from the official website. Make sure to check "Add Python to PATH" during installation.</p>
                                <a href="https://www.python.org/downloads/" class="btn btn-outline btn-sm" target="_blank" rel="noopener">Download Python</a>
                            </div>
                        </div>
                        
                        <div class="install-step">
                            <div class="step-number">2</div>
                            <div class="step-content">
                                <h4>Install Git</h4>
                                <p>Download and install Git for Windows:</p>
                                <a href="https://git-scm.com/download/win" class="btn btn-outline btn-sm" target="_blank" rel="noopener">Download Git</a>
                            </div>
                        </div>
                        
                        <div class="install-step">
                            <div class="step-number">3</div>
                            <div class="step-content">
                                <h4>Install pipx</h4>
                                <p>Open Command Prompt or PowerShell and run:</p>
                                <div class="code-block">
                                    <pre><code><span class="prompt">></span> python -m pip install --user pipx
<span class="prompt">></span> python -m pipx ensurepath</code></pre>
                                </div>
                                <p class="step-note">Restart your terminal after running these commands.</p>
                            </div>
                        </div>
                        
                        <div class="install-step">
                            <div class="step-number">4</div>
                            <div class="step-content">
                                <h4>Install ARIbrain</h4>
                                <p>Run the PowerShell installation script:</p>
                                <div class="code-block">
                                    <pre><code><span class="prompt">></span> irm https://raw.githubusercontent.com/AriBrain/ari-core/main/install.ps1 | iex</code></pre>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div id="launch" class="install-section">
                    <h2>Launch ARIbrain</h2>
                    <p>Once installed, simply open a terminal and run:</p>
                    
                    <div class="code-block code-block-highlight">
                        <pre><code><span class="prompt">$</span> aribrain</code></pre>
                    </div>
                    
                    <p>This will launch the ARIbrain graphical user interface where you can:</p>
                    
                    <div class="features-mini-grid">
                        <div class="feature-mini">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"></polyline>
                            </svg>
                            <span>Load and analyze fMRI data</span>
                        </div>
                        <div class="feature-mini">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="10"></circle>
                                <line x1="12" y1="8" x2="12" y2="12"></line>
                                <line x1="12" y1="16" x2="12.01" y2="16"></line>
                            </svg>
                            <span>Compute True Discovery Proportions</span>
                        </div>
                        <div class="feature-mini">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polygon points="12 2 2 7 12 12 22 7 12 2"></polygon>
                                <polyline points="2 17 12 22 22 17"></polyline>
                                <polyline points="2 12 12 17 22 12"></polyline>
                            </svg>
                            <span>Perform multi-resolution analysis</span>
                        </div>
                        <div class="feature-mini">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                                <circle cx="8.5" cy="8.5" r="1.5"></circle>
                                <polyline points="21 15 16 10 5 21"></polyline>
                            </svg>
                            <span>Visualize results interactively</span>
                        </div>
                    </div>
                </div>
                
                <div id="troubleshooting" class="install-section">
                    <h2>Troubleshooting</h2>
                    
                    <div class="troubleshoot-item">
                        <h4>Command not found: aribrain</h4>
                        <p>Make sure you've restarted your terminal after installing pipx. You may also need to add pipx to your PATH manually.</p>
                    </div>
                    
                    <div class="troubleshoot-item">
                        <h4>Python version mismatch</h4>
                        <p>ARIbrain requires Python 3.10.x. Check your version with <code>python3 --version</code> and install the correct version if needed.</p>
                    </div>
                    
                    <div class="troubleshoot-item">
                        <h4>C compiler errors (macOS)</h4>
                        <p>Install Xcode Command Line Tools by running <code>xcode-select --install</code> in Terminal.</p>
                    </div>
                    
                    <div class="troubleshoot-item">
                        <h4>Still having issues?</h4>
                        <p>Check the GitHub repository for more detailed troubleshooting or open an issue.</p>
                        <a href="<?php echo GITHUB_URL; ?>/issues" class="btn btn-outline btn-sm" target="_blank" rel="noopener">Report an Issue</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
