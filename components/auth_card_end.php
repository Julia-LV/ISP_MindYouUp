<?php


// Set default values for the variables
$link_text = $link_text ?? 'Need help?';
$link_url = $link_url ?? '#';
$link_label = $link_label ?? 'Contact Support';
?>
        </div> 
    </form> 

    <!-- Bottom Link -->
    <p class="mt-6 text-center text-sm text-gray-600">
        <?php echo htmlspecialchars($link_text); ?>
        <a href="<?php echo htmlspecialchars($link_url); ?>" class="font-medium text-[#005949] hover:text-[#004539]">
            <?php echo htmlspecialchars($link_label); ?>
        </a>
    </p>

</div> 

</body>
</html>