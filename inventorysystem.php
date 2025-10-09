<?php
// --- Configuration and Constants ---
const INVENTORY_FILE = 'inventory.json';

// --- Data Persistence Functions ---

/**
 * Loads the book inventory from the JSON file.
 * Creates an empty file if it doesn't exist.
 * @return array The list of books.
 */
function load_inventory(): array {
    if (!file_exists(INVENTORY_FILE)) {
        // Initialize an empty file if it doesn't exist
        save_inventory([]);
        return [];
    }
    
    $data = file_get_contents(INVENTORY_FILE);
    // Decode JSON data, return empty array if decoding fails
    return json_decode($data, true) ?? [];
}

/**
 * Saves the current inventory array back to the JSON file.
 * @param array $inventory The list of books to save.
 */
function save_inventory(array $inventory) {
    // Encode the PHP array into a JSON string
    $json_data = json_encode($inventory, JSON_PRETTY_PRINT);
    file_put_contents(INVENTORY_FILE, $json_data);
}

/**
 * Finds a book in the inventory by ISBN.
 * @param array $inventory The list of books.
 * @param string $isbn The ISBN to search for.
 * @return array|null The book data or null if not found.
 */
function find_book_by_isbn(array $inventory, string $isbn): ?array {
    foreach ($inventory as $book) {
        if ($book['isbn'] === $isbn) {
            return $book;
        }
    }
    return null;
}

// --- Main Logic: Handle Form Submissions ---

$inventory = load_inventory();
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    // Sanitize common inputs
    $isbn = trim($_POST['isbn'] ?? '');

    // --- Add Book Logic ---
    if ($action === 'add') {
        $title = trim($_POST['title'] ?? '');
        $author = trim($_POST['author'] ?? '');
        $price = filter_input(INPUT_POST, 'price', FILTER_VALIDATE_FLOAT);
        $stock = filter_input(INPUT_POST, 'stock', FILTER_VALIDATE_INT);

        // Simple validation
        if (empty($isbn) || empty($title) || empty($author) || $price === false || $stock === false || $price < 0 || $stock < 0) {
            $message = "<div class='bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded'>Error: Please fill all fields correctly (Price and Stock must be positive numbers).</div>";
        } elseif (find_book_by_isbn($inventory, $isbn)) {
            $message = "<div class='bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded'>Error: Book with ISBN '{$isbn}' already exists.</div>";
        } else {
            $new_book = [
                'isbn' => $isbn,
                'title' => $title,
                'author' => $author,
                'price' => $price,
                'stock' => $stock
            ];
            $inventory[] = $new_book;
            save_inventory($inventory);
            $message = "<div class='bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded'>Successfully added '{$title}'.</div>";
        }
    }

    // --- Update Book Stock/Price Logic ---
    elseif ($action === 'update') {
        $new_price = filter_input(INPUT_POST, 'new_price', FILTER_VALIDATE_FLOAT);
        $new_stock = filter_input(INPUT_POST, 'new_stock', FILTER_VALIDATE_INT);
        $found = false;

        if (empty($isbn)) {
             $message = "<div class='bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded'>Error: ISBN is required for update.</div>";
        } else {
            foreach ($inventory as $key => $book) {
                if ($book['isbn'] === $isbn) {
                    // Update only valid fields
                    if ($new_price !== false && $new_price >= 0) {
                        $inventory[$key]['price'] = $new_price;
                    }
                    if ($new_stock !== false && $new_stock >= 0) {
                        $inventory[$key]['stock'] = $new_stock;
                    }
                    $found = true;
                    $message = "<div class='bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded'>Book with ISBN '{$isbn}' updated successfully.</div>";
                    break;
                }
            }

            if (!$found) {
                 $message = "<div class='bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded'>Error: Book with ISBN '{$isbn}' not found for update.</div>";
            } else {
                save_inventory($inventory);
            }
        }
    }

    // --- Delete Book Logic ---
    elseif ($action === 'delete') {
        $initial_count = count($inventory);
        // Filter the inventory to exclude the book with the given ISBN
        $inventory = array_filter($inventory, fn($book) => $book['isbn'] !== $isbn);
        
        if (count($inventory) < $initial_count) {
            // Re-index the array after filtering
            $inventory = array_values($inventory); 
            save_inventory($inventory);
            $message = "<div class='bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded'>Book with ISBN '{$isbn}' successfully deleted.</div>";
        } else {
            $message = "<div class='bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded'>Error: Book with ISBN '{$isbn}' not found for deletion.</div>";
        }
    }
}

// --- Search/Filter Logic ---
$search_query = trim($_GET['search'] ?? '');
$filtered_inventory = $inventory;

if (!empty($search_query)) {
    $search_query_lower = strtolower($search_query);
    $filtered_inventory = array_filter($inventory, function($book) use ($search_query_lower) {
        return str_contains(strtolower($book['title']), $search_query_lower) ||
               str_contains(strtolower($book['author']), $search_query_lower) ||
               str_contains(strtolower($book['isbn']), $search_query_lower);
    });
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PHP Bookshop Inventory System</title>
    <!-- Load Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap');
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f7f7f7;
        }
        /* Custom scrollbar for better look */
        ::-webkit-scrollbar {
            width: 8px;
        }
        ::-webkit-scrollbar-thumb {
            background-color: #3b82f6;
            border-radius: 10px;
        }
    </style>
</head>
<body class="p-4 md:p-8">
    <div class="max-w-7xl mx-auto bg-white shadow-2xl rounded-xl p-6 lg:p-10">

        <!-- Header -->
        <header class="text-center mb-10">
            <h1 class="text-4xl font-extrabold text-blue-700">Bookshop Inventory Manager</h1>
            <p class="text-lg text-gray-500 mt-2">Manage your collection in real-time. Data is saved in `inventory.json`.</p>
        </header>
        
        <!-- Message Box -->
        <div class="mb-6"><?php echo $message; ?></div>

        <!-- --- Inventory Display & Search Section --- -->
        <section class="mb-12">
            <h2 class="text-2xl font-semibold text-gray-800 mb-4 border-b pb-2">Current Inventory</h2>

            <!-- Search Form -->
            <form method="GET" class="flex flex-col sm:flex-row gap-4 mb-6">
                <input 
                    type="text" 
                    name="search" 
                    placeholder="Search by Title, Author, or ISBN..." 
                    value="<?php echo htmlspecialchars($search_query); ?>"
                    class="flex-grow p-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 shadow-sm"
                >
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-lg shadow-md transition duration-300">
                    Search
                </button>
                <?php if (!empty($search_query)): ?>
                    <a href="bookshop_inventory.php" class="bg-gray-400 hover:bg-gray-500 text-white font-bold py-3 px-6 rounded-lg shadow-md transition duration-300 flex items-center justify-center">
                        Clear
                    </a>
                <?php endif; ?>
            </form>

            <!-- Book Table -->
            <div class="overflow-x-auto rounded-lg shadow-lg border border-gray-200">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-blue-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-blue-700 uppercase tracking-wider">ISBN</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-blue-700 uppercase tracking-wider">Title</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-blue-700 uppercase tracking-wider">Author</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-blue-700 uppercase tracking-wider">Price</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-blue-700 uppercase tracking-wider">Stock</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-100">
                        <?php if (empty($filtered_inventory)): ?>
                            <tr>
                                <td colspan="5" class="px-6 py-4 whitespace-nowrap text-center text-gray-500">
                                    <?php echo empty($inventory) ? 'No books in the inventory yet.' : 'No results found for your search query.'; ?>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($filtered_inventory as $book): ?>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?php echo htmlspecialchars($book['isbn']); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700"><?php echo htmlspecialchars($book['title']); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700"><?php echo htmlspecialchars($book['author']); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-900 font-semibold">$<?php echo number_format($book['price'], 2); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-right <?php echo $book['stock'] < 10 ? 'text-red-600 font-bold' : 'text-green-600'; ?>"><?php echo number_format($book['stock']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <p class="mt-4 text-sm text-gray-600">Total books in inventory: <span class="font-bold text-blue-600"><?php echo count($inventory); ?></span></p>
        </section>

        <hr class="my-10">

        <!-- --- Add Book Form Section --- -->
        <section class="mb-12">
            <h2 class="text-2xl font-semibold text-gray-800 mb-4 border-b pb-2">Add New Book</h2>
            <form method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-6 bg-gray-50 p-6 rounded-lg shadow-inner">
                <input type="hidden" name="action" value="add">
                
                <div>
                    <label for="add_isbn" class="block text-sm font-medium text-gray-700">ISBN (Unique)</label>
                    <input type="text" id="add_isbn" name="isbn" required class="mt-1 block w-full p-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500" placeholder="e.g., 978-0131103627">
                </div>
                <div>
                    <label for="add_title" class="block text-sm font-medium text-gray-700">Title</label>
                    <input type="text" id="add_title" name="title" required class="mt-1 block w-full p-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500" placeholder="The Great Gatsby">
                </div>
                <div>
                    <label for="add_author" class="block text-sm font-medium text-gray-700">Author</label>
                    <input type="text" id="add_author" name="author" required class="mt-1 block w-full p-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500" placeholder="F. Scott Fitzgerald">
                </div>
                <div>
                    <label for="add_price" class="block text-sm font-medium text-gray-700">Price ($)</label>
                    <input type="number" step="0.01" min="0" id="add_price" name="price" required class="mt-1 block w-full p-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500" placeholder="15.99">
                </div>
                <div class="col-span-1 md:col-span-2">
                    <label for="add_stock" class="block text-sm font-medium text-gray-700">Stock Quantity</label>
                    <input type="number" min="0" id="add_stock" name="stock" required class="mt-1 block w-full p-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500" placeholder="100">
                </div>
                <div class="col-span-1 md:col-span-2 pt-4">
                    <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-4 rounded-lg shadow-lg transition duration-300 transform hover:scale-[1.01]">
                        Add Book to Inventory
                    </button>
                </div>
            </form>
        </section>

        <hr class="my-10">

        <!-- --- Update/Delete Section --- -->
        <section class="grid grid-cols-1 lg:grid-cols-2 gap-10">
            
            <!-- Update Book Form -->
            <div class="bg-yellow-50 p-6 rounded-lg shadow-inner">
                <h2 class="text-2xl font-semibold text-gray-800 mb-4 border-b pb-2 text-yellow-700">Update Stock/Price</h2>
                <form method="POST" class="space-y-4">
                    <input type="hidden" name="action" value="update">
                    
                    <div>
                        <label for="update_isbn" class="block text-sm font-medium text-gray-700">ISBN of Book to Update</label>
                        <input type="text" id="update_isbn" name="isbn" required class="mt-1 block w-full p-3 border border-gray-300 rounded-lg focus:ring-yellow-500 focus:border-yellow-500" placeholder="Enter ISBN">
                        <p class="text-xs text-gray-500 mt-1">Both fields below are optional, but at least one must be entered.</p>
                    </div>

                    <div>
                        <label for="update_stock" class="block text-sm font-medium text-gray-700">New Stock Quantity (Optional)</label>
                        <input type="number" min="0" id="update_stock" name="new_stock" class="mt-1 block w-full p-3 border border-gray-300 rounded-lg focus:ring-yellow-500 focus:border-yellow-500" placeholder="e.g., 25">
                    </div>
                    
                    <div>
                        <label for="update_price" class="block text-sm font-medium text-gray-700">New Price ($) (Optional)</label>
                        <input type="number" step="0.01" min="0" id="update_price" name="new_price" class="mt-1 block w-full p-3 border border-gray-300 rounded-lg focus:ring-yellow-500 focus:border-yellow-500" placeholder="e.g., 22.50">
                    </div>

                    <button type="submit" class="w-full bg-yellow-600 hover:bg-yellow-700 text-white font-bold py-3 px-4 rounded-lg shadow-lg transition duration-300 transform hover:scale-[1.01]">
                        Apply Update
                    </button>
                </form>
            </div>

            <!-- Delete Book Form -->
            <div class="bg-red-50 p-6 rounded-lg shadow-inner">
                <h2 class="text-2xl font-semibold text-gray-800 mb-4 border-b pb-2 text-red-700">Delete Book</h2>
                <form method="POST" class="space-y-4">
                    <input type="hidden" name="action" value="delete">
                    
                    <div>
                        <label for="delete_isbn" class="block text-sm font-medium text-gray-700">ISBN of Book to Delete</label>
                        <input type="text" id="delete_isbn" name="isbn" required class="mt-1 block w-full p-3 border border-gray-300 rounded-lg focus:ring-red-500 focus:border-red-500" placeholder="Enter ISBN to confirm deletion">
                    </div>

                    <p class="text-sm text-red-500">Warning: This action is permanent and will remove the book entirely from the inventory.</p>

                    <button type="submit" class="w-full bg-red-600 hover:bg-red-700 text-white font-bold py-3 px-4 rounded-lg shadow-lg transition duration-300 transform hover:scale-[1.01]">
                        Delete Book
                    </button>
                </form>
            </div>

        </section>

    </div>
</body>
</html>