<pre>
<?php
if ($_SERVER['REQUEST_METHOD'] === "POST") {
    $ID = $_POST['id'];
    $last_name = $_POST['last_name'];
    $first_name = $_POST['first_name'];
    $username = $_POST['username'];
    $phone = $_POST['telephone'];
    $email = $_POST['email'];
    $new_role = $_POST['role'];
    $password = null;
    $new_password = null;
    if (isset($_POST["new_password"])){
        $new_password = $_POST["new_password"];
    }

    require("../../includes/db_connection.php");

    try {
        // Begin transaction
        $bd->beginTransaction();
        echo "now<br>";
        // 1. Get the user's current role ID
        $request = $bd->prepare("SELECT USER_ROLE_ID FROM USERS WHERE ID = :id");
        $request->bindValue(":id", $ID);
        $request->execute();
        $old_role_id = $request->fetchColumn(0);
        echo "now2<br>";
        // 2. Get the new role ID
        $request = $bd->prepare("SELECT ROLE_ID FROM ROLES WHERE NAME = :name");
        $request->bindValue(":name", $new_role);
        $request->execute();
        $new_role_id = $request->fetchColumn(0);
        echo "now3<br>";
        if ($new_password){
            $password = password_hash($new_password, PASSWORD_BCRYPT);
        }else{
            $request = $bd->prepare("SELECT USER_PASSWORD FROM USERS WHERE ID = :id");
            $request->bindValue(":id", $ID);
            $request->execute();
            $password = $request->fetchColumn(0);
            echo "now4<br>";
        }

        // 3. Update the user's information
        $request = $bd->prepare("UPDATE USERS SET LAST_NAME=:last_name, FIRST_NAME=:first_name, USERNAME=:username, USER_PASSWORD=:pass,TELEPHONE=:phone, EMAIL=:email, USER_ROLE_ID=:role_id WHERE ID=:ID");
        $request->bindValue(":last_name", $last_name);
        $request->bindValue(":first_name", $first_name);
        $request->bindValue(":username", $username);
        $request->bindValue(":pass", $password);
        $request->bindValue(":phone", $phone);
        $request->bindValue(":email", $email);
        $request->bindValue(":role_id", $new_role_id);
        $request->bindValue(":ID", $ID);
        echo "Updating user:<br>";
        var_dump($_POST);
        echo "old role: $old_role_id<br>";
        echo "new role: $new_role_id<br>";
        $request->execute();

        // 4. Update role counts if the role has changed
        if ($old_role_id != $new_role_id) {
            echo "role changes.<br>";
            // Decrement count for old role
            $request = $bd->prepare("UPDATE ROLES SET NUMBER_OF_USERS = NUMBER_OF_USERS - 1 WHERE ROLE_ID = :role_id");
            $request->bindValue(":role_id", $old_role_id);
            $request->execute();

            // Increment count for new role
            $request = $bd->prepare("UPDATE ROLES SET NUMBER_OF_USERS = NUMBER_OF_USERS + 1 WHERE ROLE_ID = :role_id");
            $request->bindValue(":role_id", $new_role_id);
            $request->execute();
        }

        // Commit transaction
        $bd->commit();
        
        header("Location: show_users.php?update=success");
        exit;
    } catch (PDOException $e) {
        // Rollback transaction on error
        $bd->rollBack();
        echo $e->getMessage();
        //header("Location: modify_user.php?id=$ID&error=" . urlencode($e->getMessage()));
        exit;
    }
}
?>