<?php

// Scryfall API
$url = "https://api.scryfall.com/sets";

// Instellingen voor de API
$options = [
    "http" => [
        "method" => "GET",
        "header" => "User-Agent: SkySkills/1.0\r\n" .
                    "Accept: application/json\r\n"
    ]
];

$context = stream_context_create($options);

// API ophalen
$response = file_get_contents($url, false, $context);

if ($response === false) {
    die("Er is iets misgegaan met het ophalen van de sets.");
}

// JSON omzetten naar PHP
$data = json_decode($response, true);

if ($data === null) {
    die("De gegevens van Scryfall konden niet worden gelezen.");
}

$sets = [];

// Alleen echte/officiële sets gebruiken
$officieleSets = [
    "core",
    "expansion",
    "masters",
    "commander",
    "draft_innovation",
    "duel_deck",
    "from_the_vault",
    "spellbook",
    "premium_deck",
    "starter",
    "box",
    "planechase",
    "archenemy",
    "vanguard",
    "arsenal",
    "treasure_chest",
    "alchemy"
];

// Alle sets bekijken
foreach ($data["data"] as $set) {

    // Niet-officiële/extra soorten overslaan
    if (!in_array($set["set_type"], $officieleSets)) {
        continue;
    }

    // Alleen sets met een releasedatum
    if (!isset($set["released_at"])) {
        continue;
    }

    $sets[] = [
        "Code" => $set["code"],
        "Name" => $set["name"],
        "API_url" => $set["scryfall_uri"],
        "Released" => $set["released_at"],
        "Icon_url" => $set["icon_svg_uri"],
        "Icon_svg_url" => $set["icon_svg_uri"]
    ];
}

// Sorteren op releasedatum
usort($sets, function ($a, $b) {
    return strcmp($a["Released"], $b["Released"]);
});

// CSV bestand maken
$file = fopen("sets.csv", "w");

// Kolomnamen toevoegen
fputcsv($file, [
    "Code",
    "Name",
    "API_url",
    "Released",
    "Icon_url",
    "Icon_svg_url"
], ",", '"', "\\");

// Sets in CSV zetten
foreach ($sets as $set) {
    fputcsv($file, [
        $set["Code"],
        $set["Name"],
        $set["API_url"],
        $set["Released"],
        $set["Icon_url"],
        $set["Icon_svg_url"]
    ], ",", '"', "\\");
}

// Bestand sluiten
fclose($file);

echo "Het CSV-bestand is gemaakt!";
?>