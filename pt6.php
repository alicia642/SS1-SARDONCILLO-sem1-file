<!DOCTYPE html>
<html>
<head>
    <title>- Item Order </title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style type="text/css">
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, rgb(27, 238, 234), rgb(1, 186, 248), rgb(4, 93, 144), rgb(11, 186, 221));
            background-size: 400% 400%;
            animation: gradientBG 5s ease infinite;
            overflow: hidden;
            text-align: center;
        }
        @keyframes gradientBG {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
        table {
            width: 50%;
            border-collapse: collapse;
            margin: 20px auto;
            font-size: 18px;
            text-align: center;
            background-color: rgb(25, 182, 190);
        }
        th, td {
            border: 1px solid black;
            padding: 10px;
        }
        th {
            background-color: rgb(228, 228, 228);
        }
        form {
            display: inline-block;
            text-align: left;
        }
        input, select {
            padding: 15px;
            margin-top: 10px;
            display: block;
            font-size: 22px;
            width: 100%;
            border-radius: 5px;
            border: 2px solid #ccc;
        }
        button, input[type="submit"] {
            padding: 10px 20px;
            background-color: rgb(238, 247, 246);
            color: navy; 
            border: none;
            cursor: pointer;
            font-size: 20px;
        }
        button:hover, input[type="submit"]:hover {
            background-color: rgb(13, 232, 248);
        }
        .order-summary-container {
            width: 50%;
            margin: 20px auto;
            padding: 20px;
            border: 3px solid navy;
            border-radius: 10px;
            background-color: rgba(255, 255, 255, 0.9);
        }
        .order-summary {
            font-size: 24px;
            color: rgba(0, 0, 0, 0.88);
            font-weight: bold;
            text-align: center;
            background-color: lightgray;
            padding: 20px;
            border-radius: 10px;
        }
        label {
            font-size: 26px;
            color: rgb(5, 9, 12);
            font-weight: bold;
        }
        input[type="checkbox"] {
            transform: scale(3.5);
            margin-right: 50px;
        }
    </style>
</head>
<body>
    <h1>Item Order</h1>
    <table>
        <tr>
            <th>Menu</th>
            <th>Order</th>
            <th>Unit Price</th>
        </tr>
        <td><img src="https://www.siftandsimmer.com/wp-content/uploads/2021/01/matcha-peppermint-white-hot-chocolate3-683x1024.jpg" width="100" /></td>
        <td>Matcha Hot Chocolate</td>
        <td> ₱100.00</td>
    </tr>
    <tr>
        <td><img src="https://www.siftandsimmer.com/wp-content/uploads/2024/06/IMG_3139-featured.jpg" width="100"/></td>
        <td>Strawberry Matcha</td>
        <td> ₱150.00</td>
    </tr>
    <tr>
        <td><img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSWIY3yGj3ZGFvnJ6tjE9rq3N6OGSr2Nat8OQ&s" width="100" /></td>
        <td>Matcha Lattes</td>
        <td> ₱200.00</td>
    </tr>
    <tr>
        <td><img src="https://xofacafebistro.com/en/wp-content/uploads/2023/06/black-forest-milk-drink-e1685636158900.jpeg" width="100" /></td>
        <td>Black Forest Latte</td>
        <td> ₱157.00</td>
    </tr>
    <tr>
        <td><img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTUFOdcGRr8iaxen0SpLWHkMdgxZNlXX2ET4zr7mM_GCm4d_LO0wwBiqbNo-quk-sXiNIw&usqp=CAU" width="100" /></td>
        <td>Ice Caramel Macchiato</td>
        <td> ₱110.00</td>
    </table>

    <form method="post">
        <h1>Select your order:</h1>
        <select name="order" id="order" style="border: 2px solid blue; padding: 15px; font-size: 22px;">
            <option value="1">Matcha hot chocolate - ₱100.00</option>
            <option value="2">Strawberry matcha - ₱150.00</option>
            <option value="3">Matcha lattes -  ₱200.00</option>
            <option value="4">Black Forest -  ₱157.00</option>
            <option value="5">Ice Caramel Macchiato -  ₱110.00</option>
        </select>
        <br><br>
        
        <label for="quantity">Quantity:</label>
        <input type="number" name="quantity" id="quantity" min="1" required style="font-size: 22px;">
        <br><br>

        <label for="takeout">Takeout:</label>
        <input type="checkbox" name="takeout" id="takeout">
        <label for="dine_in">Dine in:</label>
        <input type="checkbox" name="dine_in" id="dine_in">
        <br>
        <input type="submit" value="Calculate Total" style="font-size: 20px; padding: 10px 20px;">
    </form>

    <?php 
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $menu_items = ["Matcha hot chocolate", "Strawberry matcha", "Matcha lattes", "Black Forest", "Ice Caramel Macchiato"];
        $prices = [100.00, 150.00, 200.00, 157.00, 110.00];

        $choice = intval($_POST["order"]);
        $quantity = intval($_POST["quantity"]);
        $is_takeout = isset($_POST["takeout"]);
        
        if ($choice < 1 || $choice > 5 || $quantity <= 0) {
            echo "<p>Invalid input!</p>";
            exit;
        }

        $order = $menu_items[$choice - 1];
        $price = $prices[$choice - 1];
        $subtotal = $price * $quantity;
        $tax = $is_takeout ? $subtotal * 0.12 : 0;
        $total_amount = $subtotal + $tax;
        $order_type = $is_takeout ? "Take-out" : "Dine-in";

        echo "<div class='order-summary-container'>";
        echo "<div class='order-summary'>";
        echo "<p><strong>Order Summary:</strong></p>";
        echo "<p>Item: $order</p>";
        echo "<p>Quantity: $quantity</p>";
        echo "<p>Order Type: $order_type</p>";
        echo "<p>Total Amount:  ₱" . number_format($total_amount, 2) . "</p>";
        echo "</div>";
        echo "</div>";
    }
    ?>
</body>
</html>