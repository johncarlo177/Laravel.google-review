<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title><?php echo e($businessName); ?> - Rate Your Experience</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .feedback-container {
            background: white;
            border-radius: 20px;
            padding: 40px;
            max-width: 500px;
            width: 100%;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            text-align: center;
        }

        h1 {
            color: #333;
            font-size: 24px;
            margin-bottom: 30px;
            font-weight: 600;
            line-height: 1.4;
        }

        .stars-container {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-bottom: 30px;
            flex-wrap: wrap;
        }

        .star {
            font-size: 48px;
            color: #ddd;
            cursor: pointer;
            transition: all 0.2s;
            user-select: none;
        }

        .star:hover {
            transform: scale(1.1);
        }

        .star.active,
        .star.selected {
            color: #ffd700;
        }

        /* High rating section (4-5 stars) */
        .high-rating-section {
            display: none;
            margin-top: 30px;
        }

        .high-rating-section.show {
            display: block;
        }

        .high-rating-message {
            color: #333;
            font-size: 18px;
            margin-bottom: 20px;
            line-height: 1.5;
        }

        .google-review-button {
            display: inline-block;
            background: #4285f4;
            color: white;
            padding: 15px 30px;
            border-radius: 8px;
            text-decoration: none;
            font-size: 16px;
            font-weight: 600;
            transition: all 0.3s;
            margin-top: 10px;
        }

        .google-review-button:hover {
            background: #357ae8;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(66, 133, 244, 0.4);
        }

        /* Low rating section (1-3 stars) */
        .low-rating-section {
            display: none;
            margin-top: 30px;
        }

        .low-rating-section.show {
            display: block;
        }

        .low-rating-message {
            color: #333;
            font-size: 18px;
            margin-bottom: 20px;
            line-height: 1.5;
        }

        .feedback-form {
            text-align: left;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            color: #333;
            font-size: 14px;
            font-weight: 500;
            margin-bottom: 8px;
        }

        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 16px;
            font-family: inherit;
            transition: border-color 0.3s;
        }

        .form-group input:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #667eea;
        }

        .form-group textarea {
            resize: vertical;
            min-height: 100px;
        }

        .form-group small {
            display: block;
            color: #666;
            font-size: 12px;
            margin-top: 4px;
        }

        .button-group {
            display: flex;
            gap: 10px;
            flex-direction: column;
            margin-top: 20px;
        }

        .btn {
            padding: 15px 30px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
            text-align: center;
        }

        .btn-primary {
            background: #667eea;
            color: white;
        }

        .btn-primary:hover {
            background: #5568d3;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
        }

        .btn-secondary {
            background: #f5f5f5;
            color: #333;
        }

        .btn-secondary:hover {
            background: #e0e0e0;
        }

        .footer-note {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e0e0e0;
            font-size: 12px;
            color: #666;
            line-height: 1.5;
        }

        @media (max-width: 480px) {
            .feedback-container {
                padding: 30px 20px;
            }

            h1 {
                font-size: 20px;
            }

            .star {
                font-size: 40px;
            }
        }
    </style>
</head>
<body>
    <div class="feedback-container">
        <h1>How did we do today?<br>Rate your experience — 1–5 stars.</h1>

        <div class="stars-container" id="starsContainer">
            <span class="star" data-rating="1">★</span>
            <span class="star" data-rating="2">★</span>
            <span class="star" data-rating="3">★</span>
            <span class="star" data-rating="4">★</span>
            <span class="star" data-rating="5">★</span>
        </div>

        <!-- High Rating Section (4-5 stars) -->
        <div class="high-rating-section" id="highRatingSection">
            <p class="high-rating-message">
                Thanks — we're glad you enjoyed it! Would you share your experience on Google?
            </p>
            <?php if($googleReviewUrl): ?>
                <a href="<?php echo e($googleReviewUrl); ?>" target="_blank" class="google-review-button">
                    Leave a Google Review
                </a>
            <?php endif; ?>
        </div>

        <!-- Low Rating Section (1-3 stars) -->
        <div class="low-rating-section" id="lowRatingSection">
            <p class="low-rating-message">
                We're sorry it wasn't perfect. Tell us what happened so we can make it right.
            </p>

            <form class="feedback-form" id="feedbackForm" method="POST" action="<?php echo e(route('feedback.submit', $token)); ?>">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="rating" id="ratingInput" value="">

                <div class="form-group">
                    <label for="comment">Please tell us briefly what went wrong. A team member will follow up.</label>
                    <textarea 
                        id="comment" 
                        name="comment" 
                        required 
                        placeholder="Tell us about your experience..."
                    ></textarea>
                </div>

                <div class="form-group">
                    <label for="contact">Email or Phone (optional - if you want us to follow up)</label>
                    <input 
                        type="text" 
                        id="contact" 
                        name="contact" 
                        placeholder="your@email.com or +1234567890"
                    >
                    <small>We'll only use this to follow up on your feedback</small>
                </div>

                <div class="button-group">
                    <button type="submit" class="btn btn-primary">
                        Send Feedback to Business
                    </button>
                    <?php if($googleReviewUrl): ?>
                        <a href="<?php echo e($googleReviewUrl); ?>" target="_blank" class="btn btn-secondary">
                            Leave a Google Review
                        </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <!-- Footer Note -->
        <div class="footer-note">
            All customers may leave public reviews; private feedback helps us resolve issues faster.
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const stars = document.querySelectorAll('.star');
            const highRatingSection = document.getElementById('highRatingSection');
            const lowRatingSection = document.getElementById('lowRatingSection');
            const ratingInput = document.getElementById('ratingInput');
            let selectedRating = 0;

            stars.forEach(star => {
                star.addEventListener('click', function() {
                    const rating = parseInt(this.getAttribute('data-rating'));
                    selectedRating = rating;
                    ratingInput.value = rating;

                    // Update star display
                    stars.forEach((s, index) => {
                        if (index < rating) {
                            s.classList.add('selected', 'active');
                        } else {
                            s.classList.remove('selected', 'active');
                        }
                    });

                    // Show appropriate section
                    if (rating >= 4) {
                        // High rating (4-5 stars) - submit directly
                        highRatingSection.classList.add('show');
                        lowRatingSection.classList.remove('show');
                        
                        // Auto-submit for 4-5 stars after a short delay
                        setTimeout(() => {
                            const form = document.createElement('form');
                            form.method = 'POST';
                            form.action = '<?php echo e(route('feedback.submit', $token)); ?>';
                            
                            const csrf = document.createElement('input');
                            csrf.type = 'hidden';
                            csrf.name = '_token';
                            csrf.value = '<?php echo e(csrf_token()); ?>';
                            form.appendChild(csrf);
                            
                            const ratingInput = document.createElement('input');
                            ratingInput.type = 'hidden';
                            ratingInput.name = 'rating';
                            ratingInput.value = rating;
                            form.appendChild(ratingInput);
                            
                            document.body.appendChild(form);
                            form.submit();
                        }, 2000); // Submit after 2 seconds
                    } else {
                        // Low rating (1-3 stars)
                        lowRatingSection.classList.add('show');
                        highRatingSection.classList.remove('show');
                    }
                });

                // Hover effect
                star.addEventListener('mouseenter', function() {
                    const rating = parseInt(this.getAttribute('data-rating'));
                    stars.forEach((s, index) => {
                        if (index < rating) {
                            s.classList.add('active');
                        } else {
                            s.classList.remove('active');
                        }
                    });
                });
            });

            // Remove hover effect when mouse leaves
            document.getElementById('starsContainer').addEventListener('mouseleave', function() {
                if (selectedRating === 0) {
                    stars.forEach(s => s.classList.remove('active'));
                } else {
                    stars.forEach((s, index) => {
                        if (index < selectedRating) {
                            s.classList.add('active');
                        } else {
                            s.classList.remove('active');
                        }
                    });
                }
            });
        });
    </script>
</body>
</html>

<?php /**PATH /home/johncarlo/Documents/Projects/FREE-TESK/review/review/resources/views/feedback/rating.blade.php ENDPATH**/ ?>