<?php
/*
 * auth_card_end.php
 *
 * This component closes the card.
 * - Closes the form field <div>
 * - Closes the </form>
 * - Shows the flexible link at the bottom (e.g., "Don't have an account? Sign up")
 * - Closes the main card <div>
 * - Closes the </body> and </html> tags
 */

// Set default values for the variables
$link_text = $link_text ?? 'Need help?';
$link_url = $link_url ?? '#';
$link_label = $link_label ?? 'Contact Support';
?>
        </div> <!-- Closes the .space-y-4 div -->
    </form> <!-- Closes the </form> -->

    <!-- Bottom Link -->
    <p class="mt-6 text-center text-sm text-gray-600">
        <?php echo htmlspecialchars($link_text); ?>
        <a href="<?php echo htmlspecialchars($link_url); ?>" class="font-medium text-[#005949] hover:text-[#004539]">
            <?php echo htmlspecialchars($link_label); ?>
        </a>
    </p>

</div> <!-- Closes the main card .bg-white -->

</body>
</html>