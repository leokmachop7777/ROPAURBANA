// En tu script.js
$(document).ready(function() {
    // ... tu otro código JS ...

    // Ejemplo para formularios de agregar al carrito si usas AJAX
    $('.add-to-cart-form-ajax').on('submit', function(e) {
        e.preventDefault();
        var form = $(this);
        $.ajax({
            type: 'POST',
            url: form.attr('action'), // Debería ser un endpoint API o un script específico
            data: form.serialize(),
            dataType: 'json', // Esperar respuesta JSON
            success: function(response) {
                if (response.status === 'success') {
                    $('#cart-count').text(response.cart_item_count);
                    // Mostrar mensaje de éxito (ej. con un toast de Bootstrap)
                    showToast(response.message || 'Producto agregado al carrito');
                } else {
                    // Mostrar mensaje de error
                    showToast(response.message || 'Error al agregar producto', 'error');
                }
            },
            error: function() {
                showToast('Error de conexión al agregar producto.', 'error');
            }
        });
    });

    function showToast(message, type = 'success') {
        // Implementa tu lógica para mostrar un toast (notificación)
        // Puedes usar los toasts de Bootstrap o una librería simple.
        // Este es un placeholder:
        alert(message);
        console.log(`Toast (${type}): ${message}`);
    }
});