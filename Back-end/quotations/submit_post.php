<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submit Post</title>
</head>
<body>
    <form action="check_user_privileges.php" method="POST" id="privileges-form">
        <input type="hidden" name="action" value="view_quotations">
    </form>
</body>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        document.getElementById("privileges-form").submit();
    });
</script>
</html>