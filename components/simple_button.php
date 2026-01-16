<?php
/*
 * simple_button.php
 *
 * A reusable, flexible button component.
 * It accepts variables for text, type, extra classes, and an onclick event.
 */

// Set defaults for the variables
$button_text = $button_text ?? 'Submit';
$button_type = $button_type ?? 'submit';
$extra_classes = $extra_classes ?? 'w-full'; // Default to full-width

// 💡 NOVIDADE: Definir default para o atributo onclick (se não for fornecido, é vazio)
$button_onclick = $button_onclick ?? ''; 

// 💡 NOVIDADE: Preparar o atributo onclick para ser injetado no HTML
$onclick_attribute = !empty($button_onclick) ? 'onclick="' . htmlspecialchars($button_onclick) . '"' : '';

?>
<div>
    <button 
        type="<?php echo htmlspecialchars($button_type); ?>" 
        
        <?php echo $onclick_attribute; ?>
        
        class="<?php echo htmlspecialchars($extra_classes); ?> flex justify-center py-3 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-[#005949] hover:bg-[#004539] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
        
        <?php echo htmlspecialchars($button_text); ?>
    </button>
</div>