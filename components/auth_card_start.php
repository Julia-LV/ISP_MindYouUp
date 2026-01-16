<?php

// Set default values for the variables
$form_title = $form_title ?? 'Welcome';
$form_subtitle = $form_subtitle ?? 'Please enter your details';
?>

<div class="bg-white w-full max-w-md p-6 sm:p-8 rounded-xl shadow-lg">

    
    <div class="text-center mb-6">
        <img src="../../assets/img/MYU logos/MYU_Horizontal Logo.png" alt="Mind You Up Logo" class="w-32 h-auto mx-auto mb-4"
             onerror="this.style.display='none'"> 
        <h1 class="text-3xl font-bold text-[#F26647]"><?php echo htmlspecialchars($form_title); ?></h1>
        <p class="text-gray-500 mt-2"><?php echo htmlspecialchars($form_subtitle); ?></p>
    </div>

    <!-- Start the Form -->
    
    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST" novalidate>
        

        <div class="space-y-4">
