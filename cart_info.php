<!-- Muestro el número productos del carrito -->
<?= count($_SESSION['cart'] ?? []) ?>