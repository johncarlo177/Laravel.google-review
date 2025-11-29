@extends('blue.layouts.page')

@section('page-content')
<style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    .feedback-page-wrapper {
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
        background: #f5f7fa;
        min-height: calc(100vh - 50px);
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 40px 20px 20px 20px;
    }

    .feedback-container {
        background: #ffffff;
        border-radius: 12px;
        padding: 48px 40px;
        max-width: 560px;
        width: 100%;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08), 0 4px 16px rgba(0, 0, 0, 0.04);
        text-align: center;
        border: 1px solid #e8ecf0;
    }

    h1 {
        color: #1a1d29;
        font-size: 28px;
        margin-bottom: 32px;
        font-weight: 600;
        line-height: 1.4;
        letter-spacing: -0.02em;
    }

    .stars-container {
        display: flex;
        justify-content: center;
        gap: 12px;
        margin-bottom: 32px;
        flex-wrap: wrap;
    }

    .star {
        font-size: 52px;
        color: #d1d5db;
        cursor: pointer;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        user-select: none;
        line-height: 1;
    }

    .star:hover {
        transform: scale(1.15);
        color: #fbbf24;
    }

    .star.active,
    .star.selected {
        color: #fbbf24;
    }

    /* High rating section (4-5 stars) */
    .high-rating-section {
        display: none;
        margin-top: 32px;
        animation: fadeIn 0.3s ease-in;
    }

    .high-rating-section.show {
        display: block;
    }

    .high-rating-message {
        color: #374151;
        font-size: 17px;
        margin-bottom: 24px;
        line-height: 1.6;
        font-weight: 400;
    }

    .google-review-button {
        display: inline-block;
        background: #4285f4;
        color: #ffffff;
        padding: 14px 32px;
        border-radius: 8px;
        text-decoration: none;
        font-size: 15px;
        font-weight: 600;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        margin-top: 12px;
        box-shadow: 0 2px 4px rgba(66, 133, 244, 0.2);
    }

    .google-review-button:hover {
        background: #357ae8;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(66, 133, 244, 0.3);
    }

    /* Low rating section (1-3 stars) */
    .low-rating-section {
        display: none;
        margin-top: 32px;
        animation: fadeIn 0.3s ease-in;
    }

    .low-rating-section.show {
        display: block;
    }

    .low-rating-message {
        color: #374151;
        font-size: 17px;
        margin-bottom: 24px;
        line-height: 1.6;
        font-weight: 400;
    }

    .feedback-form {
        text-align: left;
    }

    .form-group {
        margin-bottom: 24px;
    }

    .form-group label {
        display: block;
        color: #1a1d29;
        font-size: 14px;
        font-weight: 500;
        margin-bottom: 10px;
        letter-spacing: -0.01em;
    }

    .form-group input,
    .form-group textarea {
        width: 100%;
        padding: 14px 16px;
        border: 1.5px solid #e5e7eb;
        border-radius: 8px;
        font-size: 15px;
        font-family: inherit;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        background: #ffffff;
        color: #1a1d29;
    }

    .form-group input:focus,
    .form-group textarea:focus {
        outline: none;
        border-color: #4285f4;
        box-shadow: 0 0 0 3px rgba(66, 133, 244, 0.1);
    }

    .form-group textarea {
        resize: vertical;
        min-height: 120px;
    }

    .form-group small {
        display: block;
        color: #6b7280;
        font-size: 12px;
        margin-top: 6px;
        line-height: 1.4;
    }

    .button-group {
        display: flex;
        gap: 12px;
        flex-direction: column;
        margin-top: 24px;
    }

    .btn {
        padding: 14px 28px;
        border: none;
        border-radius: 8px;
        font-size: 15px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        text-decoration: none;
        display: inline-block;
        text-align: center;
        letter-spacing: -0.01em;
    }

    .btn-primary {
        background: #4285f4;
        color: #ffffff;
        box-shadow: 0 2px 4px rgba(66, 133, 244, 0.2);
    }

    .btn-primary:hover {
        background: #357ae8;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(66, 133, 244, 0.3);
    }

    .btn-secondary {
        background: #f3f4f6;
        color: #374151;
        border: 1px solid #e5e7eb;
    }

    .btn-secondary:hover {
        background: #e5e7eb;
        border-color: #d1d5db;
    }

    .footer-note {
        margin-top: 32px;
        padding-top: 24px;
        border-top: 1px solid #e5e7eb;
        font-size: 13px;
        color: #6b7280;
        line-height: 1.5;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(8px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* Remove layout-gap spacing */
    .layout-gap {
        display: none !important;
        margin: 0 !important;
    }

    @media (max-width: 640px) {
        /* .feedback-page-wrapper {
            padding: 24px 16px;
        } */

        .feedback-container {
            padding: 32px 24px;
            border-radius: 12px;
        }

        h1 {
            font-size: 24px;
            margin-bottom: 28px;
        }

        .star {
            font-size: 44px;
        }

        .google-review-button,
        .btn {
            width: 100%;
            padding: 14px 24px;
        }
    }

    @media (max-width: 480px) {
        .feedback-container {
            padding: 28px 20px;
        }

        h1 {
            font-size: 22px;
        }

        .star {
            font-size: 40px;
            gap: 8px;
        }

        .high-rating-message,
        .low-rating-message {
            font-size: 16px;
        }
    }
</style>

<div class="feedback-page-wrapper">
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
            <div id="highRatingLoading" style="display: none; text-align: center; padding: 16px; margin-top: 16px;">
                <span style="display: inline-block; width: 20px; height: 20px; border: 3px solid #4285f4; border-top-color: transparent; border-radius: 50%; animation: spin 0.6s linear infinite; margin-right: 8px; vertical-align: middle;"></span>
                <span style="color: #4285f4; font-weight: 500;">Submitting your feedback...</span>
            </div>
            @if($googleReviewUrl)
                <a href="{{ $googleReviewUrl }}" target="_blank" class="google-review-button">
                    Leave a Google Review
                </a>
            @endif
        </div>

        <!-- Low Rating Section (1-3 stars) -->
        <div class="low-rating-section" id="lowRatingSection">
            <p class="low-rating-message">
                We're sorry it wasn't perfect. Tell us what happened so we can make it right.
            </p>

            <form class="feedback-form" id="feedbackForm" method="POST" action="{{ route('feedback.submit', $token) }}">
                @csrf
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
                    <button type="submit" class="btn btn-primary" id="sendFeedbackBtn">
                        Send Feedback to Business
                    </button>
                    @if($googleReviewUrl)
                        <a href="{{ $googleReviewUrl }}" target="_blank" class="btn btn-secondary">
                            Leave a Google Review
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Footer Note -->
        <div class="footer-note">
            All customers may leave public reviews; private feedback helps us resolve issues faster.
        </div>
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
                        // High rating (4-5 stars) - show Google review option
                        highRatingSection.classList.add('show');
                        lowRatingSection.classList.remove('show');
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

        // Add loading state to feedback form submission
        document.getElementById('feedbackForm').addEventListener('submit', function(e) {
            const btn = document.getElementById('sendFeedbackBtn');
            if (btn) {
                btn.disabled = true;
                btn.innerHTML = '<span style="display: inline-block; width: 16px; height: 16px; border: 2px solid #ffffff; border-top-color: transparent; border-radius: 50%; animation: spin 0.6s linear infinite; margin-right: 8px; vertical-align: middle;"></span>Sending...';
                btn.style.opacity = '0.7';
                btn.style.cursor = 'not-allowed';
            }
        });
    </script>
    <style>
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
    </style>
@endsection

