/**
 * Real-time Password Validator with Breach Checking
 * Provides real-time password strength and breach validation
 */
class PasswordValidator {
    constructor(options = {}) {
        this.apiUrl = options.apiUrl || '/api/v1';
        this.debounceDelay = options.debounceDelay || 500;
        this.minLength = options.minLength || 8;
        this.debounceTimer = null;
        this.currentValidation = null;
        
        this.strengthColors = {
            'Very Weak': '#ff4444',
            'Weak': '#ff8800',
            'Fair': '#ffaa00',
            'Good': '#00aa00',
            'Strong': '#008800'
        };
        
        this.severityColors = {
            'Safe': '#00aa00',
            'Low': '#ffaa00',
            'Medium': '#ff8800',
            'High': '#ff4400',
            'Critical': '#ff0000'
        };
    }

    /**
     * Initialize password validation on a form
     */
    init(formSelector, passwordFieldSelector, feedbackSelector) {
        this.form = document.querySelector(formSelector);
        this.passwordField = document.querySelector(passwordFieldSelector);
        this.feedbackElement = document.querySelector(feedbackSelector);
        
        if (!this.form || !this.passwordField || !this.feedbackElement) {
            console.error('Password validator: Required elements not found');
            return;
        }

        this.setupEventListeners();
        this.createFeedbackUI();
    }

    /**
     * Setup event listeners
     */
    setupEventListeners() {
        // Real-time validation on input
        this.passwordField.addEventListener('input', (e) => {
            this.debounceValidation(e.target.value);
        });

        // Form submission validation
        this.form.addEventListener('submit', (e) => {
            if (!this.validateOnSubmit()) {
                e.preventDefault();
            }
        });

        // Focus events
        this.passwordField.addEventListener('focus', () => {
            this.showFeedback();
        });

        this.passwordField.addEventListener('blur', () => {
            if (!this.passwordField.value) {
                this.hideFeedback();
            }
        });
    }

    /**
     * Create feedback UI
     */
    createFeedbackUI() {
        this.feedbackElement.innerHTML = `
            <div class="password-feedback" style="display: none;">
                <div class="strength-meter">
                    <div class="strength-bar">
                        <div class="strength-fill" style="width: 0%; background: #ccc;"></div>
                    </div>
                    <div class="strength-text">Enter a password</div>
                </div>
                <div class="breach-status" style="display: none;">
                    <div class="breach-icon">🔒</div>
                    <div class="breach-text">Checking breach status...</div>
                </div>
                <div class="recommendations" style="display: none;">
                    <ul class="recommendation-list"></ul>
                </div>
            </div>
        `;

        this.strengthBar = this.feedbackElement.querySelector('.strength-fill');
        this.strengthText = this.feedbackElement.querySelector('.strength-text');
        this.breachStatus = this.feedbackElement.querySelector('.breach-status');
        this.breachText = this.feedbackElement.querySelector('.breach-text');
        this.recommendations = this.feedbackElement.querySelector('.recommendations');
        this.recommendationList = this.feedbackElement.querySelector('.recommendation-list');
    }

    /**
     * Debounce validation to avoid too many API calls
     */
    debounceValidation(password) {
        clearTimeout(this.debounceTimer);
        
        if (password.length < this.minLength) {
            this.showBasicValidation(password);
            return;
        }

        this.debounceTimer = setTimeout(() => {
            this.performValidation(password);
        }, this.debounceDelay);
    }

    /**
     * Show basic validation without API call
     */
    showBasicValidation(password) {
        const strength = this.calculateBasicStrength(password);
        this.updateStrengthMeter(strength);
        this.hideBreachStatus();
        this.hideRecommendations();
        this.showFeedback();
    }

    /**
     * Calculate basic password strength
     */
    calculateBasicStrength(password) {
        let score = 0;
        const feedback = [];

        if (password.length >= 12) score += 2;
        else if (password.length >= 8) score += 1;
        else feedback.push('At least 8 characters');

        if (/[A-Z]/.test(password)) score += 1;
        else feedback.push('Include uppercase letters');

        if (/[a-z]/.test(password)) score += 1;
        else feedback.push('Include lowercase letters');

        if (/[0-9]/.test(password)) score += 1;
        else feedback.push('Include numbers');

        if (/[^A-Za-z0-9]/.test(password)) score += 1;
        else feedback.push('Include special characters');

        const strength = score <= 1 ? 'Very Weak' : 
                        score === 2 ? 'Weak' :
                        score === 3 ? 'Fair' :
                        score === 4 ? 'Good' : 'Strong';

        return { score, strength, feedback };
    }

    /**
     * Perform full validation with API
     */
    async performValidation(password) {
        try {
            this.showLoadingState();
            
            const response = await fetch(`${this.apiUrl}/validate-password`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                },
                body: JSON.stringify({ password })
            });

            const data = await response.json();

            if (data.success) {
                this.currentValidation = data.data;
                this.updateValidationUI(data.data);
            } else {
                this.showError('Validation failed: ' + data.message);
            }
        } catch (error) {
            console.error('Password validation error:', error);
            this.showError('Validation service unavailable');
        }
    }

    /**
     * Update validation UI
     */
    updateValidationUI(validation) {
        // Update strength meter
        this.updateStrengthMeter(validation.strength);

        // Update breach status
        this.updateBreachStatus(validation.breach_status);

        // Update recommendations
        this.updateRecommendations(validation.recommendations);

        this.showFeedback();
    }

    /**
     * Update strength meter
     */
    updateStrengthMeter(strength) {
        const percentage = (strength.score / strength.max_score) * 100;
        const color = this.strengthColors[strength.strength] || '#ccc';

        this.strengthBar.style.width = `${percentage}%`;
        this.strengthBar.style.background = color;
        this.strengthText.textContent = strength.strength;
        this.strengthText.style.color = color;
    }

    /**
     * Update breach status
     */
    updateBreachStatus(breachStatus) {
        if (breachStatus.compromised) {
            const color = this.severityColors[breachStatus.severity] || '#ff0000';
            const icon = breachStatus.severity === 'Critical' ? '🚨' : '⚠️';
            
            this.breachText.innerHTML = `
                <span style="color: ${color};">${icon} ${breachStatus.recommendation}</span>
            `;
            this.showBreachStatus();
        } else {
            this.breachText.innerHTML = `
                <span style="color: #00aa00;">🔒 ${breachStatus.recommendation}</span>
            `;
            this.showBreachStatus();
        }
    }

    /**
     * Update recommendations
     */
    updateRecommendations(recommendations) {
        if (recommendations && recommendations.length > 0) {
            this.recommendationList.innerHTML = recommendations
                .map(rec => `<li>${rec}</li>`)
                .join('');
            this.showRecommendations();
        } else {
            this.hideRecommendations();
        }
    }

    /**
     * Show loading state
     */
    showLoadingState() {
        this.strengthText.textContent = 'Checking password...';
        this.strengthBar.style.background = '#007bff';
        this.breachText.textContent = '🔍 Checking breach status...';
        this.showFeedback();
    }

    /**
     * Show error state
     */
    showError(message) {
        this.strengthText.textContent = 'Validation error';
        this.strengthBar.style.background = '#ff0000';
        this.breachText.textContent = `❌ ${message}`;
        this.showFeedback();
    }

    /**
     * Show feedback
     */
    showFeedback() {
        this.feedbackElement.querySelector('.password-feedback').style.display = 'block';
    }

    /**
     * Hide feedback
     */
    hideFeedback() {
        this.feedbackElement.querySelector('.password-feedback').style.display = 'none';
    }

    /**
     * Show breach status
     */
    showBreachStatus() {
        this.breachStatus.style.display = 'flex';
    }

    /**
     * Hide breach status
     */
    hideBreachStatus() {
        this.breachStatus.style.display = 'none';
    }

    /**
     * Show recommendations
     */
    showRecommendations() {
        this.recommendations.style.display = 'block';
    }

    /**
     * Hide recommendations
     */
    hideRecommendations() {
        this.recommendations.style.display = 'none';
    }

    /**
     * Validate on form submission
     */
    validateOnSubmit() {
        if (!this.currentValidation) {
            return true; // Allow submission if no validation performed
        }

        if (!this.currentValidation.is_safe) {
            this.showError('Please fix password issues before submitting');
            return false;
        }

        return true;
    }

    /**
     * Get current validation result
     */
    getValidationResult() {
        return this.currentValidation;
    }

    /**
     * Clear validation
     */
    clearValidation() {
        this.currentValidation = null;
        this.hideFeedback();
    }
}

// Export for use in other modules
if (typeof module !== 'undefined' && module.exports) {
    module.exports = PasswordValidator;
} else {
    window.PasswordValidator = PasswordValidator;
}
