<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="author" content="Antonio Siega">
    <title>Pokécompact</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link href="../styles/index.css" rel="stylesheet">
    <link rel="icon" type="image/x-icon" href="../images/favicon.ico">
</head>
<body class="bg-image">
    <div class="container text-center py-3">
        <div class="col justify-content-end">

            <img src="../images/logo.png" class="img img-big-shadow float-none img-fluid mx-auto" style="min-width: 40%; max-width: 40%">
            <form class='row align-items-center d-block' action="index.php" method="GET">
                <input type="text" class='my-2 w-50 shadow-sm' id="name" name="name" autofocus="true" placeholder="Charizard" required>
                <button type="submit" class="btn btn-primary rounded-0 w-50 shadow-sm">Search</button>
            </form><br>

    
            <?php
                session_start();
                use PokePHP\PokeApi;

                require_once('../vendor/autoload.php');
                require_once('../modules/cache_operations.php');
                require_once('../modules/auxiliary_functions.php');

                $_SESSION['moveListOffset'] = 0;
                $_SESSION['moveListLimit'] = 5;
                $_SESSION['api'] = new PokeApi();

                if (isset($_GET['name'])){
                    $pokemonName = strtolower(str_replace(" ", "-", $_GET['name']));
                    $cachedPokemonData = isCached("pokemon", $pokemonName);
                    if ($cachedPokemonData){
                        $extractedData = $cachedPokemonData;
                        $cacheOperationsSuccessful = true;
                    } else {
                        $untreatedResponse = $_SESSION['api']->pokemon($_GET['name']);

                        if ($untreatedResponse !== json_encode('An error has occured.')){
                            $treatedResponse = json_decode(($untreatedResponse), true);

                            $extractedData = array(
                                'name' => $treatedResponse['name'],
                                'moves' => array_map(function($data){
                                                //getting only the data I'm actually interested in
                                                return $data['move'];
                                            }, $treatedResponse['moves']),
                                'stats' => $treatedResponse['stats'],
                                'types' => $treatedResponse['types'],
                                'sprites' => array(
                                    'front' => isset($treatedResponse['sprites']['front_default']) ?
                                            $treatedResponse['sprites']['front_default'] : "",
                                    'back' => isset($treatedResponse['sprites']['back_default']) ?
                                            $treatedResponse['sprites']['back_default'] : ""
                                )
                            );
                            $cacheOperationsSuccessful = cacheRequest("pokemon", json_encode($extractedData));
                        } else {
                            $cacheOperationsSuccessful = false;
                        }
                    }
                    
                    if ($cacheOperationsSuccessful){
                        $link_sprite = $extractedData['sprites']['front'];
                        $_SESSION['all_moves'] = $extractedData['moves'];
                        $stats = $extractedData['stats'];

                        echo "<div class='col d-flex flex-wrap bg-white shadow rounded p-3'>"; //column of pokémon data
                            echo "<div class='col-md-4 col-12'>";
                                echo "<div class='row text-center'>";
                                    echo "<p class='text-uppercase fw-bold'>" . $extractedData['name'] . "</p>"; 
                                echo "</div>";
                                echo "<div class='row'>";
                                echo    "<img id='sprite' class='mx-auto img-big-shadow' style='max-width: 90%' src=$link_sprite><br>";
                                echo "</div>";
                                echo "<div class='row justify-content-center'>";
                                    $typeData = getTypeSprites($_SESSION['api'], $extractedData['types']);
                                    foreach ($typeData as $entry) {
                                        echo "<img src='" . $entry . "' class='w-25 img-sm-shadow'>";
                                    }
                                echo "</div>";
                            echo "</div>";

                            echo "<div class='col-md-4 col-12'>"; 
                                echo "<div class='row text-center'>";
                                    echo "<p class='fw-bold'>STATS</p>";
                                echo "</div>";
                                echo "<div class='row'>";
                                    echo "<table class='table table-striped mx-auto shadow border border-primary' style='width:75%'>";
                                        foreach($stats as $index => $data){
                                            $value = $data['base_stat'];
                                            $stat_name = ucwords(str_replace("-", " ", $data['stat']['name']));

                                            echo "<tr>
                                                    <td class='fw-bold' style='width: 50%; text-align:right'>" . $stat_name . "</td> 
                                                    <td style='width: 50%; text-align:left'>" . $value . "</td>
                                                 </tr>";
                                        }
                                    echo "</table>";
                                echo "</div>";
                            echo "</div>";

                            echo "<div class='col-md-4 col-12'>";
                                echo "<p class='fw-bold'>MOVES</p>";
                                echo "<table id='moves' class='table table-striped mx-auto border border-primary shadow' style='width:90%'>";
                                getMoveDetails($_SESSION['api'], $_SESSION['all_moves'], $_SESSION['moveListOffset'], $_SESSION['moveListLimit']);
                                echo "</table>";

                                echo "<button type='button' class='btn btn-primary' onclick='loadMoves(&quot moves_ajax.php &quot)'>More</button>";
                            echo "</div>";
                        echo "</div>"; //closing the initial column
                    } else {
                        echo "An error has occurred. Please check your spelling and connection status.";
                    }
                }
            ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
    <script src="https://ajax.aspnetcdn.com/ajax/jQuery/jquery-3.7.1.js"></script>
    <script>
        //function to dynamically load move info (doesn't require reloading the page)
        function loadMoves(url) {
            const xhttp = new XMLHttpRequest();
            xhttp.open("GET", url);
            xhttp.send();
            xhttp.onreadystatechange = (e) => {
                if (xhttp.readyState == 4){ //only inserts if readyState == done
                    document.getElementById("moves").insertAdjacentHTML('beforeend', xhttp.responseText);
                }
            }
        }
    </script>
</body>
</html>
