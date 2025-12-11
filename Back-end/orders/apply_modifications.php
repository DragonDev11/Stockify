<?php
    session_start();
    if ($_SERVER['REQUEST_METHOD'] === "POST" || $_SERVER['REQUEST_METHOD'] === "GET") {
        require("../../includes/db_connection.php");

        $bd->beginTransaction();

        if (!isset($_GET['new_state'])){
            $id = null;
            if (isset($_SESSION["order"])){
                $id = $_SESSION['order']['orderID'];
                $client_name = $_SESSION['order']['client_name'];
                $sell_type = $_SESSION['order']['sell_type'];
                $ice = isset($_SESSION['order']['company_ICE']) ? $_SESSION['order']['company_ICE'] : null;
                $address = $_SESSION['order']['address'];
                $date = $_SESSION['order']['creation_date'];
            }

            try {
                $request = $bd->prepare("
                    SELECT CLIENT_NAME FROM BON_COMMANDE_HEADER WHERE ID = :id;
                ");

                $request->execute([':id' => $id]);

                $old_client_name = $request->fetch(PDO::FETCH_ASSOC)["CLIENT_NAME"];

                if ($client_name !== $old_client_name){
                    if (($sell_type !== 'Personal')){
                        $request = $bd->prepare("UPDATE CLIENT SET CLIENTNAME = :new, ICE = :ice WHERE CLIENTNAME = :old;");
                        $request->execute([':new' => $client_name, ':ice' => $ice, ':old' => $old_client_name]);
                    }else{
                        $request = $bd->prepare("UPDATE CLIENT SET CLIENTNAME = :new WHERE CLIENTNAME = :old;");
                        $request->execute([':new' => $client_name, ':old' => $old_client_name]);
                    }
                }


                $request = $bd->prepare("
                    UPDATE BON_COMMANDE_HEADER 
                    SET CLIENT_NAME = :c_name, TYPE = :type, COMPANY_ICE = :ice, ADDRESSE = :address, DATE = :date 
                    WHERE ID = :id;
                ");
                $request->execute([
                    ":id" => $id,
                    ':c_name' => $client_name,
                    ':type' => $sell_type,
                    ':ice' => $ice,
                    ':address' => $address,
                    ':date' => $date
                ]);

                $bd->commit();
                unset($_SESSION['order']);
                header("Location: show_orders.php");
                exit;
            } catch (PDOException $e) {
                $bd->rollBack();
                error_log($e->getMessage());
                unset($_SESSION['order']);
                header("Location: show_orders.php?error=pdo_exception");
            }
        }else{
            $id = $_GET["orderID"];
            $new_state = $_GET["new_state"];

            $transitions = [
                'initiated' => ['in progress', 'canceled'],
                'in progress' => ['halted', 'delivering', 'canceled'],
                'delivering' => [],
                'halted' => ['in progress'],
                'canceled' => [],
                'delivered' => []
            ];

            $new_state = format_string($new_state);

            $query = "SELECT STATE FROM BON_COMMANDE_HEADER WHERE ID = :id";
            $request = $bd->prepare($query);
            $request->bindValue(":id", $id);

            try{
                $request->execute();
            }catch (PDOException $e){
                error_log($e->getMessage());
                $bd->rollBack();
                exit();
            }

            $current_state = format_string($request->fetchColumn(0));

            if (canChangeState($current_state, $new_state, $transitions, $id, $bd)){
                try{
                    $request = $bd->prepare("
                        UPDATE BON_COMMANDE_HEADER 
                        SET STATE = :new_state 
                        WHERE ID = :id;
                    ");
                    $request->execute([
                        ':new_state' => $new_state,
                        ':id' => $id
                    ]);

                    if ($new_state == "canceled" && $current_state == "in progress"){
                        $query = "
                            UPDATE PRODUCT p
                            JOIN BON_COMMANDE_DETAILS o
                            ON o.PRODUCT_ID = p.REFERENCE
                            SET p.QUANTITY = p.QUANTITY + o.QUANTITY
                            WHERE o.ID_COMMANDE = :id;
                        ";
                        $request = $bd->prepare($query);
                        $request->execute([
                            ':id' => $id
                        ]);
                    }else if ($new_state == "in progress" && $current_state != "halted"){
                        $query = "
                            UPDATE PRODUCT p
                            JOIN BON_COMMANDE_DETAILS o
                            ON o.PRODUCT_ID = p.REFERENCE
                            SET p.QUANTITY = p.QUANTITY - o.QUANTITY
                            WHERE o.ID_COMMANDE = :id;
                        ";
                        $request = $bd->prepare($query);
                        $request->execute([
                            ':id' => $id
                        ]);
                    }

                    $bd->commit();
                    header("Location: show_orders.php");
                    exit;
                } catch (PDOException $e) {
                    $bd->rollBack();
                    error_log($e->getMessage());
                    header("Location: show_orders.php?error=pdo_exception");
                }
            }else{
                if ($new_state != "delivering"){
                    header("Location: show_orders.php?error=Illegal_state_transition");
                }else{
                    header("Location: show_orders.php?error=Not_enough_products_in_storage");
                }
            }

        }
    }

    function canChangeState($current_state, $new_state, $transitions_table, $order_id, $database){
        if (isset($transitions_table[$current_state])){
            return in_array($new_state, $transitions_table[$current_state]);
        }
        return false;
    }

    function format_string($string){
        $formattedState = strtolower(str_replace("_", " ", $string));
        $finalformattedState = str_ireplace("state ", "", $formattedState);
        return $finalformattedState;
    }
?>