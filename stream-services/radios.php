<?php
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

include_once('../connect.php'); 

$queryHosts = "SELECT idHost, name, country, getTrackInfoUrl AS gettrackUrl, trackTitle AS pathTT, trackArtist AS pathTA, trackCover AS pathTC FROM ss_radiohost";
$resultHosts = mysqli_query($conn, $queryHosts);

$hosts = [];
while ($row = mysqli_fetch_assoc($resultHosts)) {
    $hosts[] = $row;
}

// Fetching the stations
$queryStations = "SELECT idHostedBy, idStation AS id, name, streamUrl AS url FROM ss_stations";
$resultStations = mysqli_query($conn, $queryStations);

$stations = [];
while ($row = mysqli_fetch_assoc($resultStations)) {
    $stations[] = $row;
}

// Organizing data
$organizedRadios = [];
foreach ($hosts as $host) {
    // Check if country group already exists
    if (!isset($organizedRadios[$host['country']])) {
        $organizedRadios[$host['country']] = [];
    }
    
    // Prepare radio host entry
    $radioHost = [
        'id' => $host['idHost'],
        'name' => $host['name'],
        'country' => $host['country'],
        'gettrackUrl' => $host['gettrackUrl'],
        'pathTT' => $host['pathTT'],
        'pathTA' => $host['pathTA'],
        'pathTC' => $host['pathTC'],
        'stations' => [],
    ];

    // Find related stations
    foreach ($stations as $station) {
        if ($station['idHostedBy'] == $host['idHost']) {
            $radioHost['stations'][] = [
                'id' => $station['id'],
                'idHostedBy' => $station['idHostedBy'],
                'name' => $station['name'],
                'url' => $station['url'],
            ];
        }
    }

    // Add radio host to the appropriate country group
    $organizedRadios[$host['country']][] = $radioHost;
}

// Return JSON response
echo json_encode(
    ['status' => 'success',
    'radios' => $organizedRadios
    ]
);

// Close the database connection
mysqli_close($conn);
?>
