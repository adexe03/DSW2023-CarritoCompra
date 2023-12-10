<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
require_once 'connection.php';

function mostrarError($mensaje)
{
    echo "<div style='margin: 10px 0; padding: 10px; border: 1px solid #ff0000; background-color: #ffe0e0; color: #ff0000;'>Error: $mensaje</div>";
}

$orderBy = isset($_SESSION['order']) ? $_SESSION['order'] : 'name';
$orderBy = isset($_GET['order']) ? $_GET['order'] : 'name';
$allowedOrders = ['name', 'price', 'amount'];

if (!in_array($orderBy, $allowedOrders)) {
    $orderBy = 'name'; // Establece un valor predeterminado si el orden recibido no es válido
}

// Guarda el filtro en la sesión
$_SESSION['order'] = $orderBy;

$conn = Connection::connect();

// Prepara y ejecuta la consulta con el orden recibido
$sql = "SELECT id, name, price, amount FROM products ORDER BY $orderBy";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("Error al preparar la consulta: " . $conn->errorInfo());
}

$stmt->execute();
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($products === false) {
    die("Error al obtener resultados");
}

$conn = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    $productId = $_POST['product_id'];
    $cantidadSeleccionada = 1;

    $conn = Connection::connect();
    $productInfo = getProductInfo($conn, $productId);
    $conn = null;

    if (!$productInfo || $cantidadSeleccionada > $productInfo['amount']) {
        mostrarError("No hay suficiente stock disponible para este producto.");
    } else {
        if (isset($_SESSION['cart'][$productId])) {
            $totalCantidad = $_SESSION['cart'][$productId]['quantity'] + $cantidadSeleccionada;

            if ($totalCantidad <= $productInfo['amount']) {
                $_SESSION['cart'][$productId]['quantity'] += $cantidadSeleccionada;
            } else {
                mostrarError("No puedes añadir más unidades de este producto. Stock insuficiente.");
            }
        } else {
            $_SESSION['cart'][$productId] = [
                'name' => $productInfo['name'],
                'price' => $productInfo['price'],
                'quantity' => $cantidadSeleccionada,
            ];
        }

        header('Location: index.php');
        exit;
    }
}

function getProductInfo($conn, $productId)
{
    $sql = "SELECT name, price, amount FROM products WHERE id = ?";
    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        die("Error al preparar la consulta: " . $conn->error);
    }

    $stmt->execute([$productId]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$result) {
        die("Error al ejecutar la consulta: " . $stmt->error);
    }

    return $result;
}

include 'header.php';
?>

<h1>Productos</h1>

<table>
    <tr>
        <th><a href="?order=name">Nombre</a></th>
        <th><a href="?order=price">Precio</a></th>
        <th><a href="?order=amount">Cantidad</a></th>
        <th>Acción</th>
    </tr>
    <?php foreach ($products as $product) : ?>
        <tr>
            <td><?= $product['name'] ?></td>
            <td>$<?= $product['price'] ?></td>
            <td><?= $product['amount'] ?></td>
            <td>
                <form method="post">
                    <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                    <button type="submit" name="add_to_cart">Añadir al Carrito</button>
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
</table>

<?php include 'footer.php'; ?>