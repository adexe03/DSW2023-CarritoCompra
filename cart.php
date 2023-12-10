<?php
session_start();
require_once 'connection.php';

// Muestro el carrito
$cart = $_SESSION['cart'] ?? [];

// Modifico el carrito
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $conn = Connection::connect();

    if (isset($_POST['update_cart'])) {
        actualizarCarrito($_POST['quantity']);
    }

    if (isset($_POST['empty_cart'])) {
        vaciarCarrito();
    }

    $conn = null;

    // Redirijo la página para evitar el reenvío del formulario al actualizar la página
    header('Location: cart.php');
    exit;
}


include 'header.php';
?>

<!-- Contenido del carrito -->
<h1>Carrito de Compra</h1>

<form method="post">
    <table>
        <tr>
            <th>Nombre</th>
            <th>Precio</th>
            <th>Cantidad</th>
            <th>Subtotal</th>
        </tr>
        <?php $total = 0; ?>
        <?php foreach ($cart as $productId => $item) : ?>
            <tr>
                <td><?= $item['name'] ?></td>
                <td><?= $item['price'] ?></td>
                <td>
                    <input type="number" name="quantity[<?= $productId ?>]" value="<?= $item['quantity'] ?>" min="0">
                </td>
                <td><?= $item['price'] * $item['quantity'] ?></td>
            </tr>
            <?php $total += $item['price'] * $item['quantity']; ?>
        <?php endforeach; ?>
    </table>
    <p id="cart-info">Total: <?= $total ?></p>
    <button type="submit" name="update_cart">Actualizar Carrito</button>
    <button type="submit" name="empty_cart">Vaciar Carrito</button>
</form>

<?php include 'footer.php'; ?>

<?php
function actualizarCarrito($quantityArray)
{
    foreach ($quantityArray as $productId => $quantity) {
        $quantity = (int)$quantity;

        if ($quantity == 0) {
            unset($_SESSION['cart'][$productId]);
        } else {
            $_SESSION['cart'][$productId]['quantity'] = $quantity;
        }
    }
}

function vaciarCarrito()
{
    unset($_SESSION['cart']);
}
?>