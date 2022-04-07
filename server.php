<?php

#para correr el codigo usar php -S localhost:8000 server.php(nombre del archivo)

// Inicio el servidor en la terminal 1
// php -S localhost:8000 server.php

// Terminal 2 ejecutar
// curl http://localhost:8000 -v
// curl http://localhost:8000/\?resource_type\=books
// curl http://localhost:8000/\?resource_type\=books | jq

#Autenticación poco eficaz HTTP

// $user = array_key_exists( 'PHP_AUTH_USER', $_SERVER ) ? $_SERVER['PHP_AUTH_USER'] : '';
// $pwd = array_key_exists( 'PHP_AUTH_PW', $_SERVER ) ? $_SERVER['PHP_AUTH_PW'] : '';

// if( $user !== 'Maic' || $pwd !== '1234' ){
//     die;
// }

# Autenticacion más eficaz con hmac
// if (
//     !array_key_exists('HTTP_X_HASH', $_SERVER) ||
//     !array_key_exists('HTTP_X_TIMESTAMP', $_SERVER) ||
//     !array_key_exists('HTTP_X_UID', $_SERVER)
// ) {
//     die;
// }

// list( $hash, $uid, $timestamp ) = [
//     $_SERVER['HTTP_X_HASH'],
//     $_SERVER['HTTP_X_UID'],
//     $_SERVER['HTTP_X_TIMESTAMP'],

// ];

// $secret = 'sh!! no se lo cuentes a nadie!';
// $newHash = sha1($uid.$timestamp.$secret);

// if ( $newHash !== $hash ) {
//     die;
// }

#Autenticacion con tokens

// if ( !array_key_exists( 'HTTP_X_TOKEN', $_SERVER ) ) {

//     die;
// }

// $url = 'https://'.$_SERVER['HTTP_HOST'].'/auth';

// $ch = curl_init( $url );
// curl_setopt( $ch, CURLOPT_HTTPHEADER, [
//     "X-Token: {$_SERVER['HTTP_X_TOKEN']}",
// ]);
// curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
// $ret = curl_exec( $ch );

// if ( curl_errno($ch) != 0 ) {
//     die ( curl_error($ch) );
// }

// if ( $ret !== 'true' ) {
//     http_response_code( 403 );

//     die;
// }

header('Content-Type: application/json');

//Definimos recursos disponibles
$allowedResourceTypes = [
    'books',
    'authors',
    'genres',
];

// Validamos que el recurso este disponible
$resourceType = $_GET['resource_type'];

if (!in_array($resourceType, $allowedResourceTypes)) {
    http_response_code(400);

    // echo json_encode(
    //     [
    //         'error' => "$resourceType is un unkown",
    //     ]
    // );
    die;
}

// $books = [
//     1 => [
//         'titulo' => 'Lo que el viento se llevo',
//         'id_autor' => 2,
//         'id_genero' => 2,
//     ],
//     2 => [
//         'titulo' => 'La Iliada',
//         'id_autor' => 1,
//         'id_genero' => 1,
//     ],
//     3 => [
//         'titulo' => 'La Odisea',
//         'id_autor' => 1,
//         'id_genero' => 1,
//     ],
// ];

$books = array();

// Levantamos el id del recurso buscado
$resourceId = array_key_exists('resource_id', $_GET) ? $_GET['resource_id'] : '';

switch (strtoupper($_SERVER['REQUEST_METHOD'])) {
    case 'GET':
        //Leemos el JSON
        $dateBooks = file_get_contents("books.json");
        $books = json_decode($dateBooks, true);
        end($books);
        $key = key($books);
        if (empty($resourceId)) {
            echo json_encode($books);
        } else {
            if (array_key_exists($resourceId, $books) || array_key_exists()) {
                
                echo json_encode($books[$resourceId]);
            } else {
                http_response_code(404);
            }
        }

        break;
    case 'POST':
        $payload = file_get_contents('php://input');
        $books[] = json_decode($payload, true);
        //echo array_keys($books)[count($books)-1];
        end($books); // move the internal pointer to the end of the array
        $key = key($books); // fetches the key of the element pointed to by the internal pointer

        // echo json_encode($books[$key]);
        // echo "\nGuardado con exito!";

        $result = [];
        //Validación de error
        if (empty(trim($payload)) || $payload == null) {
            http_response_code(400);
            $result['error'] = 'Hubo algún fallo :(';
            echo json_encode($result);
            return;
        }

        //Leemos el JSON
        $dateBooks = file_get_contents("books.json");
        $jsonBooks = json_decode($dateBooks, true);

        if (!file_exists("books.json")) {
            // aqui va el JSON
            $fichero = 'books.json';
            //$actual = file_get_contents($fichero);
            $actual = json_encode($books);

            file_put_contents($fichero, $actual);
        }

        if (!empty($jsonBooks)) {
            $jsonBooks[] = json_decode($payload, true);
            // aqui va el JSON
            $fichero = 'books.json';
            //$actual = file_get_contents($fichero);
            $actual = json_encode($jsonBooks);

            file_put_contents($fichero, $actual);
        }

        //Status del arreglo
        $result['create'] = json_decode($payload, true);
        $result['status'] = "Guardado con exito";
        echo json_encode($result);

        break;

    case 'PUT':
        // Validamos que el recurso buscado exista
        if (!empty($resourceId) && array_key_exists($resourceId, $books)) {
            // Tomamos la entrada curda
            $payload = file_get_contents('php://input');

            // Tansformamos el json recibido a un nuevo elemento
            $books[$resourceId] = json_decode($payload, true);

            echo json_encode($books[$resourceId]);
        }
        break;

    case 'DELETE':
        // Validamos que el recurso buscado exista
        if (!empty($resourceId) && array_key_exists($resourceId, $books)) {
            unset($books[$resourceId]);
            echo json_encode($books);
        }
        break;
}

//error_log(__METHOD__.__LINE__." Json ============>  ".  var_export($jsonBooks ,true));

//$method = $_SERVER['REQUEST_METHOD'];

// switch ( strtoupper( $method ) ) {
//     case 'GET':
//         if ( "books" !== $resourceType ) {
//             http_response_code( 404 );

//             echo json_encode(
//                 [
//                     'error' => "$resourceType not yet implemented :(",
//                 ]
//             );

//             die;
//         }

//         if ( !empty( $resourceId ) ) {
//             if ( array_key_exists( $resourceId, $books ) ) {
//                 echo json_encode(
//                     $books[ $resourceId ]
//                 );
//             } else {
//                 http_response_code( 404 );

//                 echo json_encode(
//                     [
//                         'error' => 'Book '.$resourceId.' not found :(',
//                     ]
//                 );
//             }
//         } else {
//             echo json_encode(
//                 $books
//             );
//         }

//         die;

//         break;
//     case 'POST':
//         $json = file_get_contents( 'php://input' );

//         $books[] = json_decode( $json );

//         echo array_keys($books)[count($books)-1];
//         break;

//     case 'PUT':
//         if ( !empty($resourceId) && array_key_exists( $resourceId, $books ) ) {
//             $json = file_get_contents( 'php://input' );

//             $books[ $resourceId ] = json_decode( $json, true );

//             echo $resourceId;
//         }
//         break;
//     case 'DELETE':
//         if ( !empty($resourceId) && array_key_exists( $resourceId, $books ) ) {
//             unset( $books[ $resourceId ] );
//         }
//         break;
//     default:
//         http_response_code( 404 );

//         echo json_encode(
//             [
//                 'error' => $method.' not yet implemented :(',
//             ]
//         );

//         break;
// }
