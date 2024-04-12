<?php
// Cache pour éviter les appels cURL multiples sur la même URL
$cache = [];

// Fonction pour ajouter "http://" à une URL si nécessaire
function add_http($url) {
    if (strpos($url, '://') === false) {
        $url = 'http://' . $url;
    }
    return $url;
}

// Fonction pour récupérer le contenu d'une URL avec cURL et stocker les résultats dans le cache
function file_get_contents_curl($url) {
    global $cache; // Utilise le cache global
    if (!isset($cache[$url])) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_AUTOREFERER, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
        $data = curl_exec($ch);
        $info = curl_getinfo($ch);
        curl_close($ch);
        $cache[$url] = ['content' => $data, 'info' => $info];
    }
    return $cache[$url]['content'];
}

// Fonction pour récupérer le temps de réponse et le statut HTTP en une seule opération
function get_url_info($url) {
    global $cache;
    if (!isset($cache[$url])) {
        file_get_contents_curl($url); // Cela remplit le cache si ce n'est pas déjà fait
    }
    $info = $cache[$url]['info'];
    return [
        'http_status' => $info['http_code'],
        'load_time' => $info['total_time'] * 1000, // Temps de chargement en millisecondes
    ];
}

// Fonction pour récupérer les liens <a> et <img> d'une URL
function getLinks($url) {
    $urlContent = file_get_contents_curl($url);
    $dom = new DOMDocument();
    @$dom->loadHTML($urlContent);
    $xpath = new DOMXPath($dom);

    $links = [];
    $images = [];

    $hrefs = $xpath->query("//a");
    foreach ($hrefs as $href) {
        $links[] = $href->getAttribute('href');
    }

    $srcs = $xpath->query("//img");
    foreach ($srcs as $src) {
        $images[] = $src->getAttribute('src');
    }

    return ['links' => array_unique($links), 'images' => array_unique($images)];
}

// Fonction pour analyser une URL sans profondeur
function depth_zero($url) {
    $info = get_url_info($url);
    $linksImages = getLinks($url);

    return [
        'url' => $url,
        'http_status' => $info['http_status'],
        'load_time' => $info['load_time'],
        'links' => $linksImages['links'],
        'images' => $linksImages['images'],
    ];
}

// Fonction principale pour analyser une URL et afficher le résultat en JSON
function analyse_simple($url) {
    // Ensure URL has http:// prefix
    $url = add_http($url);

    // Call the depth_zero function (assuming it exists and returns the desired result)
    $resultat = depth_zero($url);

    // Encode the result as JSON
    $resultat = json_encode($resultat, JSON_PRETTY_PRINT);

    // Initialize curl
    $curl = curl_init();

    // Set curl options
    curl_setopt($curl, CURLOPT_URL, 'http://host.docker.internal:8000/resultat');
    curl_setopt($curl, CURLOPT_POST, 1);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $resultat);
    curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true); // Return the response instead of outputting it

    // Execute curl and get the response
    $response = curl_exec($curl);

    // Check for curl errors
    if ($response === false) {
        echo 'Curl error: ' . curl_error($curl);
    } else {
        // Display the response
        echo 'oui';
    }

    // Close curl
    curl_close($curl);
}


// Utilisation
if (isset($argv[1])) {
    $url = $argv[1];
    analyse_simple($url);
} else {
    echo "Usage: php script.php <url>\n";
}

