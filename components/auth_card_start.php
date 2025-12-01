<?php
/*
 * auth_card_start.php
 *
 * This component starts the main white card for login/signup.
 * - It's responsive (w-full, max-w-md)
 * - It shows the logo, a flexible title, and a subtitle
 * - It opens the <form> tag and the <div> for form fields
 */

// Set default values for the variables
$form_title = $form_title ?? 'Welcome';
$form_subtitle = $form_subtitle ?? 'Please enter your details';
?>
<!-- Responsive Card Wrapper -->
<div class="bg-white w-full max-w-md p-6 sm:p-8 rounded-xl shadow-lg">

    <!-- Header: Logo, Title, Subtitle -->
    <div class="text-center mb-6">
        <!-- Make sure this path is correct based on your folder structure -->
        <img src="../../assets/img/MYU logos/MYU_Horizontal Logo.png" alt="Mind You Up Logo" class="w-32 h-auto mx-auto mb-4"
             onerror="this.style.display='none'"> <!-- Hides the image if the path is broken -->
        
        <h1 class="text-3xl font-bold text-[#F26647]"><?php echo htmlspecialchars($form_title); ?></h1>
        <p class="text-gray-500 mt-2"><?php echo htmlspecialchars($form_subtitle); ?></p>
    </div>

    <!-- Start the Form -->
    
    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST" novalidate>
        
        <!-- This div adds consistent spacing between all form fields -->
        <div class="space-y-4">
