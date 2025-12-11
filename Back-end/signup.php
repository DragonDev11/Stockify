<?php
session_unset();
session_start();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $last_name = $_POST['lastname'];
    $first_name = $_POST['firstname'];
    $username = $_POST['username'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirm-password'];

    if ($password !== $confirmPassword) {
        header("Location: ../Front-end/signup/index.php?error=password_not_match");
        exit();
    }
    // Set up PDO connection
    require("../includes/db_connection.php");

    $requete = $bd->prepare('SELECT USERNAME,USER_ROLE_ID FROM USERS;');
    $requete->execute();
    $usernames = $requete->fetchAll(PDO::FETCH_COLUMN, 0);
    $roles = $requete->fetchAll(PDO::FETCH_COLUMN, 1);
    var_dump($usernames);

    $password = password_hash($password, PASSWORD_BCRYPT);

    for ($i=0; $i<sizeof($usernames); $i++){
        if ($username == $usernames[$i]){
            header("Location: ../Front-end/signup/index.php?error=username_already_used");
            exit();
        }

        if ($roles[$i] == 0){
            header("Location: ../Front-end/signup/index.php?error=super_admin_already_exists");
            exit();
        }
    }
    
    // Generate unique ID
    $uniqueId = "U".bin2hex(random_bytes(15));

    $role_id = '0';

    // Prepare the SQL query to insert data into the USERS table
    $requete = $bd->prepare('
        INSERT INTO USERS (USER_ID, last_name, first_name, username, user_role_id, telephone, email, user_password) 
        VALUES (:user_id, :last_name, :first_name, :username, :role_id, :phone, :email, :password);

        UPDATE ROLES r
        JOIN (
            SELECT USER_ROLE_ID, COUNT(*) AS user_count
            FROM USERS
            GROUP BY USER_ROLE_ID
        ) counts ON r.ROLE_ID = counts.USER_ROLE_ID
        SET r.NUMBER_OF_USERS = counts.user_count;
    ');

    // Bind the values to the prepared statement
    $requete->bindValue(':user_id', $uniqueId);
    $requete->bindValue(':last_name', $last_name);
    $requete->bindValue(':first_name', $first_name);
    $requete->bindValue(':username', $username);
    $requete->bindValue(':role_id', $role_id);
    $requete->bindValue(':phone', $phone);
    $requete->bindValue(':email', $email);
    $requete->bindValue(':password', $password);

    // Execute the query
    try{
        $requete->execute();
    }catch (PDOException $e){
        echo $e->getMessage();
    }

    $_SESSION["user"] = ["username" => $username];

    // Redirect to the dashboard
    header("Location: ../Front-end/dashboard/index.php");
    exit();
}
?>
