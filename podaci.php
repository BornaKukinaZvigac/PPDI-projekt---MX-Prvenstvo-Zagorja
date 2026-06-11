<?php
session_start();

if (!isset($_SESSION["user"])) {
    header("Location: index.php");
    exit;
}

$user = $_SESSION["user"];

$rezultati = json_decode(file_get_contents("rezultati.json"), true)["rezultati"];
$sljedeca = json_decode(file_get_contents("sljedeca.json"), true);
$vozaci = json_decode(file_get_contents("vozaci.json"), true)["vozaci"];

$imePrezime = $user["ime"] . " " . $user["prezime"];

if (isset($_POST["logout"])) {
    session_destroy();
    header("Location: index.php");
    exit;
}

if (isset($_POST["prijava_utrka"]) && !in_array($imePrezime, $sljedeca["prijavljeni"])) {
    $sljedeca["prijavljeni"][] = $imePrezime;

    file_put_contents(
        "sljedeca.json",
        json_encode($sljedeca, JSON_PRETTY_PRINT)
    );
}

if (isset($_POST["odjava_utrka"])) {

    $sljedeca["prijavljeni"] = array_values(
        array_filter(
            $sljedeca["prijavljeni"],
            function ($p) use ($imePrezime) {
                return $p != $imePrezime;
            }
        )
    );

    file_put_contents(
        "sljedeca.json",
        json_encode($sljedeca, JSON_PRETTY_PRINT)
    );

    $prijavljen = false;
}

$prijavljen = in_array($imePrezime, $sljedeca["prijavljeni"]);

$ukupno = 0;
$mojeUtrke = [];

foreach ($rezultati as $r) {
    if ($r["ime"] == $user["ime"] && $r["prezime"] == $user["prezime"]) {
        $mojeUtrke[] = $r;
        $ukupno += $r["bodovi"];
    }
}

$points = [];

foreach ($rezultati as $r) {
    $vozac = $r["ime"] . " " . $r["prezime"];

    foreach ($vozaci as $v) {

        if ($v["ime"] . " " . $v["prezime"] == $vozac && $v["klasa"] == $user["klasa"]
        ) {
            $points[$vozac] = ($points[$vozac] ?? 0) + $r["bodovi"];
        }
    }
}

arsort($points);

$pozicija = 1;

foreach ($points as $ime => $bodovi) {
    if ($ime == $imePrezime) {
        break;
    }

    $pozicija++;
}
?>

<!DOCTYPE html>
<html lang="hr">
    <head>
        <meta charset="utf-8">
        <title>MX Prvenstvo zagorja</title>
        <link rel="stylesheet" href="stil.css">
        <link rel="icon" type="image/png" href="logo.png">
    </head>

    <body>
        <div class="container">
            <h1>Dobrodošao, <?= $user["ime"] ?> <?= $user["prezime"] ?></h1>

            <div class="card">
                Motor: <?= $user["markaMotora"] ?> <?= $user["modelMotora"] ?><br>
                Broj: <?= $user["broj"] ?><br>
                Klasa: <?= $user["klasa"] ?>
            </div>

            <h2>Raspored utrka</h2>

            <div class="raspored">
                <img src="raspored.jpg" style="width:100%; height:100%; object-fit:cover;">
            </div>

            <p><b>Sljedeća utrka: <?= $sljedeca["utrka"] ?></b></p>

            <p>Broj prijavljenih: <b><?= count($sljedeca["prijavljeni"]) ?></b></p>

            <div id="timer" class="card"></div>

            <script>
                const targetDate = new Date("2026-09-06T00:00:00").getTime();

                function updateTimer() {

                    let diff = targetDate - Date.now();

                    if (diff <= 0) {
                        document.getElementById("timer").innerHTML = "Utrka je počela!";
                        return;
                    }

                    let d = Math.floor(diff / 86400000);
                    let h = Math.floor((diff / 3600000) % 24);
                    let m = Math.floor((diff / 60000) % 60);
                    let s = Math.floor((diff / 1000) % 60);

                    document.getElementById("timer").innerHTML =
                        `Do utrke: ${d}d ${h}h ${m}m ${s}s`;
                }

                setInterval(updateTimer, 1000);
                updateTimer();
            </script>

            <form method="post">

                <?php if (!$prijavljen) { ?>

                    <button name="prijava_utrka">
                        Prijavi se
                    </button>

                <?php } else { ?>

                    <div class="card" style="color: green; text-align: center;">
                        <b>✔ Prijavljeni ste!</b>
                    </div>

                    <button type="submit" name="odjava_utrka" style="background:#666;" onmouseover="this.style.background='#555'" onmouseout="this.style.background='#666'">
                        Odustani
                    </button>

                <?php } ?>

            </form>

            <h2>Rezultati</h2>

            <?php if (empty($mojeUtrke)) { ?>

                <p>Nema odvoženih utrka.</p>

            <?php } else { ?>

                <?php foreach ($mojeUtrke as $r) { ?>

                    <div class="card">
                        <b><?= $r["utrka"] ?></b><br>
                        Pozicija: <?= $r["pozicija"] ?><br>
                        Bodovi: <?= $r["bodovi"] ?>
                    </div>

                <?php } ?>

            <?php } ?>

            <hr>

            <h2>Cijelo prvenstvo</h2>

            <?php if (!empty($mojeUtrke)) { ?>

                <div class="card">
                    <b><?= $pozicija ?>. mjesto</b><br>
                    Klasa: <?= $user["klasa"] ?><br>
                    Ukupno bodova: <b><?= $ukupno ?></b>
                </div>

            <?php } else { ?>

                <p>Nema odvoženih utrka.</p>

            <?php } ?>

            <hr>

            <form method="post">
                <button name="logout">Odjava</button>
            </form>
        </div>
    </body>
</html>
