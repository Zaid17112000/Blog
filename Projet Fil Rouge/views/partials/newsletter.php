<style>
    #subscriptionMessage {
        padding: 10px;
        border-radius: 4px;
        margin-top: 10px;
        transition: all 0.3s ease;
    }

    #subscriptionMessage.success {
        background-color: #d4edda;
        color: #00e800;
        border: 1px solid #c3e6cb;
    }

    #subscriptionMessage.error {
        background-color: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
    }
</style>

<section class="newsletter">
    <div class="container">
        <h2>Subscribe to Our Newsletter</h2>
        <p>Stay updated with the latest articles, writing tips, and community news delivered directly to your inbox.</p>
        <form class="newsletter-form" id="newsletterForm">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
            <input type="email" name="email" id="newsletterEmail" placeholder="Enter your email address" required>
            <button type="submit">Subscribe</button><br><br>
        </form>
        <div id="subscriptionMessage" style="display: none; margin-top: 15px;"></div>
    </div>
</section>