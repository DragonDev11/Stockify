<?php session_start() ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Parkinsans:wght@300..800&display=swap" rel="stylesheet">

    <script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>

    
    <script src="../main.js"></script>

<!-- Styles -->
<link rel="stylesheet" href="../css/style.css">

    <title>Modify Role</title>
    <style>
        /* =============== Base Styles ============== */
        * {
            font-family: "Parkinsans", sans-serif;
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --blue: #0ce48d;
            --white: #fff;
            --gray: #f5f5f5;
            --black1: #222;
            --black2: #999;
        }

        body {
            min-height: 100vh;
            overflow-x: hidden;
            background: #f5f5f5;
        }

        /* =============== Modify Role Container ============== */
        .modify-role-container {
            margin: 20px;
            padding: 30px;
            background: #fff;
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        }

        .role-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }

        .role-header h1 {
            color: #0ce48d;
            margin: 0;
            font-size: 1.8rem;
        }

        .role-actions {
            display: flex;
            gap: 10px;
        }

        /* =============== Form Styles ============== */
        .role-form {
            margin-top: 20px;
            max-width: 1000px;
        }

        .form-row {
            display: flex;
            gap: 20px;
            margin-bottom: 25px;
        }

        .form-group {
            flex: 1;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #444;
            font-size: 0.95rem;
        }

        .form-group input {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 1rem;
            transition: border 0.3s;
        }

        .form-group input:focus {
            border-color: #0ce48d;
            outline: none;
            box-shadow: 0 0 0 3px rgba(12, 228, 141, 0.1);
        }

        .form-group input[readonly] {
            background-color: #f5f5f5;
            cursor: not-allowed;
        }

        /* =============== Privileges Section ============== */
        .privileges-section h3 {
            margin: 30px 0 20px;
            color: #0ce48d;
            font-size: 1.3rem;
            display: flex;
            flex-direction: row;
            align-items: center;
            gap: 10px;
        }

        .privileges-section h3 ion-icon {
            font-size: 1.4rem;
        }

        .privileges-container {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .privilege-category {
            background: #f9f9f9;
            border-radius: 12px;
            overflow: hidden;
            border: 1px solid #eee;
        }

        /* =============== Category Header ============== */
        .category-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 15px;
            background: #f5f5f5;
            border-bottom: 1px solid #e0e0e0;
        }

        .category-title {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .category-title ion-icon {
            font-size: 1.2rem;
            color: #666;
        }

        .category-title h4 {
            margin: 0;
            font-size: 1rem;
            color: #333;
        }

        .category-actions {
            display: flex;
            gap: 8px;
        }

        .category-actions button {
            background: none;
            border: 1px solid #ddd;
            color: #555;
            font-size: 0.8rem;
            cursor: pointer;
            padding: 4px 8px;
            border-radius: 4px;
            display: flex;
            align-items: center;
            gap: 4px;
            transition: all 0.2s;
        }

        .category-actions button:hover {
            background: rgba(0,0,0,0.05);
            border-color: #0ce48d;
            color: #0ce48d;
        }

        .category-actions button ion-icon {
            font-size: 0.9rem;
        }

        /* =============== Privileges Grid ============== */
        .privileges-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
            gap: 12px;
            padding: 15px;
        }

        .privilege-card {
            display: flex;
            flex-direction: column;
            padding: 12px;
            background: white;
            border-radius: 8px;
            border: 1px solid #e0e0e0;
            transition: all 0.2s;
            cursor: pointer;
            position: relative;
            overflow: hidden;
        }

        .privilege-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            border-color: #0ce48d;
        }

        .privilege-card.VIEW {
            border-left: 4px solid #1976d2;
        }

        .privilege-card.ADD {
            border-left: 4px solid #388e3c;
        }

        .privilege-card.MODIFY {
            border-left: 4px solid #ffa000;
        }

        .privilege-card.privilege-card.CONFIRM {
            border-left: 4px solid #ffa000;
        }

        .privilege-card.DELETE {
            border-left: 4px solid #d32f2f;
        }

        .privilege-card.USE {
            border-left: 4px solid #8e24aa;
        }

        .privilege-card.STATE {
            border-left: 4px solid rgb(36, 143, 170);
        }

        .privilege-content {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .action-icon {
            font-size: 1.4rem;
            color: #666;
        }

        .privilege-card.VIEW .action-icon {
            color: #1976d2;
        }

        .privilege-card.ADD .action-icon {
            color: #388e3c;
        }

        .privilege-card.MODIFY .action-icon {
            color: #ffa000;
        }

        .privilege-card.CONFIRM .action-icon {
            color: #ffa000;
        }

        .privilege-card.DELETE .action-icon {
            color: #d32f2f;
        }

        .privilege-card.ADMINISTRATOR .action-icon {
            color: #8e24aa;
        }

        .privilege-name {
            font-size: 0.9rem;
            color: #444;
            line-height: 1.3;
        }

        .privilege-checkbox {
            position: absolute;
            opacity: 0;
            width: 0;
            height: 0;
        }

        .privilege-checkbox:checked + .privilege-content {
            background-color: #f5f5f5;
        }

        .privilege-checkbox:checked + .privilege-content::after {
            content: '';
            position: absolute;
            top: 5px;
            right: 5px;
            width: 16px;
            height: 16px;
            background-color: #0ce48d;
            border-radius: 50%;
            border: 2px solid white;
        }

        /* =============== Form Actions ============== */
        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 15px;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }

        .btn {
            padding: 12px 20px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 1rem;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s;
        }

        .btn-primary {
            background: #0ce48d;
            color: white;
        }

        .btn-primary:hover {
            background: #0abf7a;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(12, 228, 141, 0.2);
        }

        .btn-secondary {
            background: #f0f0f0;
            color: #555;
        }

        .btn-secondary:hover {
            background: #e0e0e0;
        }

        .btn-cancel {
            background: white;
            color: #666;
            border: 1px solid #ddd;
        }

        .btn-cancel:hover {
            background: #f5f5f5;
            border-color: #ccc;
        }

        .btn ion-icon {
            font-size: 1.1rem;
        }

        /* =============== Error Handling ============== */
        .error {
            color: #e74c3c;
            padding: 12px 15px;
            background: #fde8e8;
            border-radius: 8px;
            margin: 20px 0;
            text-align: center;
            border-left: 4px solid #e74c3c;
        }

        /* =============== Responsive Design ============== */
        @media (max-width: 1024px) {
            .privileges-grid {
                grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            }
        }

        @media (max-width: 768px) {
            .form-row {
                flex-direction: column;
                gap: 15px;
            }
            
            .privileges-grid {
                grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
            }
            
            .privilege-card {
                padding: 10px;
            }
            
            .action-icon {
                font-size: 1.2rem;
            }
            
            .privilege-name {
                font-size: 0.8rem;
            }
            
            .role-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }
            
            .form-actions {
                flex-direction: column;
            }
        }

        @media (max-width: 480px) {
            .modify-role-container {
                margin: 10px;
                padding: 15px;
            }
            
            .privileges-grid {
                grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
                gap: 8px;
                padding: 10px;
            }
            
            .privilege-card {
                padding: 8px;
            }
            
            .category-actions {
                flex-direction: column;
                gap: 5px;
            }
            
            .category-actions button {
                padding: 2px 4px;
                font-size: 0.7rem;
            }
            
            .btn {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <!-- =============== Navigation ================ -->
    <div class="container">
        <?php include("../../includes/menu.php"); ?>

        <!-- ========================= Main ==================== -->
        <div class="main">
            <?php include("../../includes/topbar.php"); ?>

            <!-- ======================= Content ================== -->
            <div class="modify-role-container">
                <div class="role-header">
                    <h1>Modify Role</h1>
                    <div class="role-actions">
                        <button class="btn btn-secondary" id="select-all">
                            <ion-icon name="checkbox-outline"></ion-icon> Select All
                        </button>
                        <button class="btn btn-secondary" id="deselect-all">
                            <ion-icon name="square-outline"></ion-icon> Deselect All
                        </button>
                    </div>
                </div>
                
                <?php
                    try {
                        require("../../includes/db_connection.php");
                        $requete = null;

                        if ($_SERVER['REQUEST_METHOD'] === "GET" && isset($_GET['id'])){
                            $ID = $_GET["id"];

                            $requete = null;
                            $query = null;

                            $query = "SELECT * FROM ROLES WHERE ROLE_ID = :ID";
                            $requete = $bd->prepare($query);
                            $requete->bindValue(":ID", $ID);
                            $requete->execute();
                            $result = $requete->fetch(PDO::FETCH_ASSOC);

                            print_form($result, $ID);
                        }            
                    } catch (PDOException $e){
                        echo "<p class='error'>Error: " . $e->getMessage() . "</p>";
                    }

                    function print_form($result, $ID) {
                        echo "
                            <form method='POST' action='apply_modifications.php' class='role-form'>
                                <input type='hidden' name='id' value='{$ID}'>
                                <div class='form-row'>
                                    <div class='form-group'>
                                        <label>Role ID</label>
                                        <input type='text' value='{$result['ROLE_ID']}' name='new_id' readonly>
                                    </div>
                                    <div class='form-group'>
                                        <label>Role Name</label>
                                        <input type='text' value='{$result['NAME']}' name='new_name' required>
                                    </div>
                                </div>
                                
                                <div class='privileges-section'>
                                    <h3>
                                        <ion-icon name='key-outline'></ion-icon>
                                        Privileges Management
                                    </h3>
                                    <div class='privileges-container'>
                        ";
                        
                        // Group privileges by category
                        $categories = [];
                        foreach ($result as $column => $value) {
                            if ($column !== 'ID' && $column !== 'ADMINISTRATOR' && $column !== 'ROLE_ID' && $column !== 'NAME' && $column !== 'NUMBER_OF_USERS') {
                                $category = '';
                                $action = '';

                                $parts = explode('_', $column, 2);
                                $action = $parts[0];
                                $category = isset($parts[1]) ? str_replace('_', ' ', $parts[1]) : '';
                                
                                if (str_starts_with($column, 'STATE_')) {
                                    $category = 'Order States';
                                }
                            
                                if (!isset($categories[$category])) {
                                    $categories[$category] = [];
                                }
                                
                                $categories[$category][] = [
                                    'column' => $column,
                                    'value' => $value,
                                    'action' => $action,
                                    'name' => strtolower(str_replace('_', ' ', $column))
                                ];
                            }
                        }

                        // Print each category in flexible rows
                        foreach ($categories as $category => $privileges) {
                            $categoryId = strtolower(str_replace(' ', '_', $category)) . "_category";
                            $categoryIcon = getCategoryIcon($category);
                            
                            echo "
                                <div class='privilege-category' id='{$categoryId}'>
                                    <div class='category-header'>
                                        <div class='category-title'>
                                            {$categoryIcon}
                                            <h4>{$category}</h4>
                                        </div>
                                        <div class='category-actions'>
                                            <button type='button' class='btn-select-all' data-category='{$categoryId}'>
                                                <ion-icon name='checkbox-outline'></ion-icon> All
                                            </button>
                                            <button type='button' class='btn-deselect-all' data-category='{$categoryId}'>
                                                <ion-icon name='square-outline'></ion-icon> None
                                            </button>
                                        </div>
                                    </div>
                                    <div class='privileges-grid'>
                            ";
                            
                            foreach ($privileges as $priv) {
                                $checked = $priv['value'] ? 'checked' : '';
                                echo "
                                    <label class='privilege-card {$priv['action']}'>
                                        <input type='checkbox' name='privileges[{$priv['column']}]' value='1' id='{$priv['column']}' class='privilege-checkbox' {$checked}>
                                        <div class='privilege-content'>
                                            <span class='action-icon'>" . getActionIcon($priv['action']) . "</span>
                                            <span class='privilege-name'>{$priv['name']}</span>
                                        </div>
                                    </label>
                                ";
                            }
                            
                            echo "
                                    </div>
                                </div>
                            ";
                        }

                        echo "
                                    </div>
                                </div>
                                
                                <div class='form-actions'>
                                    <button type='submit' class='btn btn-primary'>
                                        <ion-icon name='save-outline'></ion-icon>
                                        Save Changes
                                    </button>
                                    <a href='show_roles.php' class='btn btn-cancel'>
                                        <ion-icon name='arrow-back-outline'></ion-icon>
                                        Cancel
                                    </a>
                                </div>
                            </form>
                        ";
                    }
                    
                    function getCategoryIcon($category) {
                        switch ($category) {
                            case 'Order States': return '<ion-icon name="list-outline"></ion-icon>';
                            case 'USERS': return '<ion-icon name="people-outline"></ion-icon>';
                            case 'ROLES': return '<ion-icon name="key-outline"></ion-icon>';
                            case 'PRODUCTS': return '<ion-icon name="cube-outline"></ion-icon>';
                            case 'ORDERS': return '<ion-icon name="cart-outline"></ion-icon>';
                            case 'INVOICES': return '<ion-icon name="document-text-outline"></ion-icon>';
                            case 'QUOTATIONS': return '<ion-icon name="file-tray-outline"></ion-icon>';
                            case 'STORAGE': return '<ion-icon name="archive-outline"></ion-icon>';
                            case 'SUPPLIERS': return '<ion-icon name="business-outline"></ion-icon>';
                            case 'AI': return '<ion-icon name="hardware-chip-outline"></ion-icon>';
                            default: return '<ion-icon name="ellipse-outline"></ion-icon>';
                        }
                    }
                    
                    function getActionIcon($action) {
                        switch ($action) {
                            case 'VIEW': return '<ion-icon name="eye-outline"></ion-icon>';
                            case 'ADD': return '<ion-icon name="add-outline"></ion-icon>';
                            case 'MODIFY': return '<ion-icon name="create-outline"></ion-icon>';
                            case 'DELETE': return '<ion-icon name="trash-outline"></ion-icon>';
                            case 'CONFIRM': return '<ion-icon name="checkmark-outline"></ion-icon>';
                            case 'USE': return '<ion-icon name="sparkles-outline"></ion-icon>';
                            default: return '<ion-icon name="ellipse-outline"></ion-icon>';
                        }
                    }
                ?>
            </div>
        </div>
    </div>

    <script>
        // Category toggle functionality
        document.querySelectorAll('.category-toggle').forEach(categoryCheckbox => {
            categoryCheckbox.addEventListener('change', function() {
                const categoryId = this.id;
                const privileges = document.querySelectorAll('.' + categoryId + '-privilege');
                privileges.forEach(checkbox => {
                    checkbox.checked = this.checked;
                });
            });
        });

        // Select all privileges in a category
        document.querySelectorAll('.btn-select-all').forEach(btn => {
            btn.addEventListener('click', function() {
                const category = this.dataset.category;
                const checkboxes = document.querySelectorAll(`#${category} .privilege-checkbox`);
                checkboxes.forEach(checkbox => {
                    checkbox.checked = true;
                });
            });
        });

        // Deselect all privileges in a category
        document.querySelectorAll('.btn-deselect-all').forEach(btn => {
            btn.addEventListener('click', function() {
                const category = this.dataset.category;
                const checkboxes = document.querySelectorAll(`#${category} .privilege-checkbox`);
                checkboxes.forEach(checkbox => {
                    checkbox.checked = false;
                });
            });
        });

        // Global select all
        document.getElementById('select-all').addEventListener('click', function(e) {
            e.preventDefault();
            document.querySelectorAll('.privilege-checkbox').forEach(checkbox => {
                checkbox.checked = true;
            });
        });

        // Global deselect all
        document.getElementById('deselect-all').addEventListener('click', function(e) {
            e.preventDefault();
            document.querySelectorAll('.privilege-checkbox').forEach(checkbox => {
                checkbox.checked = false;
            });
        });

        // Update category select all buttons when individual checkboxes change
        document.querySelectorAll('.privilege-checkbox').forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                const category = this.closest('.privilege-category');
                if (category) {
                    const checkboxes = category.querySelectorAll('.privilege-checkbox');
                    const checked = category.querySelectorAll('.privilege-checkbox:checked');
                    
                    // Update the category header to show selection status
                    const header = category.querySelector('.category-header');
                    if (checked.length === checkboxes.length) {
                        header.classList.add('all-selected');
                        header.classList.remove('some-selected');
                    } else if (checked.length > 0) {
                        header.classList.add('some-selected');
                        header.classList.remove('all-selected');
                    } else {
                        header.classList.remove('some-selected', 'all-selected');
                    }
                }
            });
        });
    </script>
</body>
</html>