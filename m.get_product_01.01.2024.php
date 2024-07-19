<!DOCTYPE html>
<html>
<head>

<link rel="stylesheet" href="m.get_product_01.01.2024.css">
<body> 

<?php
include 'connect.php';

function formatPrice($price) {
    return number_format($price, 2, '.', ' ') . ' рублей';
}

$limit = 3; // Количество товаров для первоначальной загрузки
$loadedProductsCount = isset($_GET['loadedProductsCount']) ? (int)$_GET['loadedProductsCount'] : 0;

$sqlProducts = "SELECT DISTINCT p.display_name, p.product_id, p.product_description, i.image_id, i.image_url, i.image_description, dpo.display_order
                FROM Products p 
                RIGHT JOIN Product_Images i ON p.product_id = i.product_id AND i.active = 1
                LEFT JOIN Display_Product_Order dpo ON p.product_id = dpo.product_id
                WHERE p.active = 1";

if (isset($_POST['videoID']) && $_POST['videoID'] !== '') {
    $videoID = $_POST['videoID'];
    $sqlProducts .= " AND p.product_id IN (
                        SELECT p.product_id
                        FROM Products p
                        RIGHT JOIN Product_Images i ON p.product_id = i.product_id AND i.active = 1
                        INNER JOIN Vocabulary_Video_Product vvp ON p.product_id = vvp.product_id
                        WHERE p.active = 1 
                        AND vvp.video_id = '$videoID')";}

$sqlProducts .= " ORDER BY COALESCE(dpo.display_order, 9999999) ASC, p.product_id ASC LIMIT 5 OFFSET $loadedProductsCount;";

$resultProducts = $conn->query($sqlProducts);

if ($resultProducts !== false) {
    if ($resultProducts->num_rows > 0) {
        echo '<div class="main" id="products-container">'; // Добавляем контейнер для товаров

        while ($rowProducts = $resultProducts->fetch_assoc()) {
            // Отображаем информацию о товаре
            echo '<div class="products">';

            // Код отображения изображения товара и описания товара
            echo '<div class="product-image">';
            echo '<img src="' . $rowProducts["image_url"] . '" alt="Изображение товара">';
            echo '</div>';

            echo '<div class="product-details" data-product-id="' . $rowProducts['product_id'] . '">';
            echo '<h3>' . $rowProducts["display_name"] . '</h3>';
            echo '<p class="product-description">' . $rowProducts["product_description"] . '</p>';

            // Кнопка для загрузки магазинов через AJAX
            echo '<div class="buttons">';
            echo '<button class="button-product" data-product-id="' . $rowProducts['product_id'] . '" style="float: left;">Посмотреть материалы</button>';
            echo '<button class="button-shops" style="float: right;">Предложения</button>';
            echo '</div>'; // Закрыть блок buttons

            // Контейнер для магазинов (пока пустой)
            echo '<div class="shop-offers" style="display: none; max-height: 300px; overflow-y: auto;"></div>';

            echo '</div>'; // Закрыть блок product-details
            echo '</div>'; // Закрыть блок products
        }

        echo '</div>'; // Закрыть контейнер для товаров
    } else {
        echo "Нет товаров.";
    }
} else {
    echo "Ошибка SQL-запроса: " . $conn->error;
}

$conn->close();
?>