<?php
    require_once('../modules/auxiliary_functions.php');
    session_start();
    
    if (isset($_SESSION['all_moves'])){
        if ($_SESSION['moveListOffset'] === 0) {
            $_SESSION['moveListOffset'] += $_SESSION['moveListLimit'];
        }

        getMoveDetails($_SESSION['api'], $_SESSION['all_moves'], $_SESSION['moveListOffset'], $_SESSION['moveListLimit']);
        $_SESSION['moveListOffset'] += $_SESSION['moveListLimit'];
    }
?>