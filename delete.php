<?php
if(!empty($_GET['compId'])) {

    include('config.php');

    $compId = $_GET['compId'];

    $consul = "SELECT * FROM comandaspendentes WHERE comp_Id = $compId";

    $result = mysqli_query($conn, $consul);

    if(mysqli_num_rows($result) > 0) {
        $consulDelete = "DELETE FROM comandaspendentes WHERE comp_Id = $compId";
            //echo $consulDelete;
        $resultDelete = $conn->query($consulDelete);
    }
    header('Location: Central_adm.php');
}
?>