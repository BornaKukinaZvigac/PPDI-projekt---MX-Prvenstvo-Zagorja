<?php
session_start();

$vozaci = json_decode(file_get_contents("vozaci.json"), true);

function pronadiVozaca($ime, $prezime, $vozaci) {
    foreach ($vozaci["vozaci"] as $vozac) {
        if (strtolower($vozac["ime"]) === strtolower($ime) && strtolower($vozac["prezime"]) === strtolower($prezime)) {
            return $vozac;
        }
    }
    return null;
}

function spremiVozace($vozaci) {
    file_put_contents("vozaci.json", json_encode($vozaci, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

$korak = "ime";
$greska = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
        if (isset($_POST["odustani"])) {
        unset($_SESSION["registracija"]);
        header("Location: index.php");
        exit;
    }

    if (isset($_POST["lozinka"]) && isset($_SESSION["temp_user"])) {

        if ($_SESSION["temp_user"]["lozinka"] === $_POST["lozinka"]) {

            $_SESSION["user"] = $_SESSION["temp_user"];
            unset($_SESSION["temp_user"]);

            header("Location: podaci.php");
            exit;
        }

        $greska = "Pogrešna lozinka!";
        $korak = "login";
    }

    elseif (isset($_POST["marka"]) || isset($_POST["klasa"])) {

        $noviVozac = [
            "ime" => trim($_POST["ime"]),
            "prezime" => trim($_POST["prezime"]),
            "lozinka" => $_POST["lozinka"],
            "markaMotora" => trim($_POST["marka"]),
            "modelMotora" => trim($_POST["model"]),
            "broj" => trim($_POST["broj"]),
            "klasa" => trim($_POST["klasa"])
        ];

        $vozaci["vozaci"][] = $noviVozac;
        spremiVozace($vozaci);

        $_SESSION["user"] = $noviVozac;

        header("Location: podaci.php");
        exit;
    }

    else {
        $vozac = pronadiVozaca(trim($_POST["ime"]), trim($_POST["prezime"]), $vozaci);

        if ($vozac) {
            $_SESSION["temp_user"] = $vozac;
            $korak = "login";
        } else {
            $_SESSION["registracija"] = [
                "ime" => trim($_POST["ime"]),
                "prezime" => trim($_POST["prezime"])
            ];

            $korak = "registracija";
        }
    }
}

$registracija = $_SESSION["registracija"] ?? null;
?>

<!DOCTYPE html>
<html lang="hr">
    <head>
        <meta charset="UTF-8">
        <title>MX Prvenstvo Zagorja</title>
        <link rel="stylesheet" href="stil.css">
        <link rel="icon" href="logo.png">
    </head>

    <body>
        <div class="container">
            <h1>Prvenstvo Zagorja 2026</h1>

            <?php if ($korak === "ime") : ?>

                <h2>Prijava vozača</h2>

                <form method="post">
                    <input type="text" name="ime" placeholder="Ime" required>

                    <input type="text" name="prezime" placeholder="Prezime" required>

                    <button type="submit">Nastavi</button>
                </form>

            <?php endif; ?>

            <?php if ($korak === "login") : ?>

                <h2>Unesite lozinku</h2>

                <p>
                    <?= $_SESSION["temp_user"]["ime"] ?>
                    <?= $_SESSION["temp_user"]["prezime"] ?>
                </p>

                <?php if ($greska) : ?>
                    <br><h3><?= $greska ?></h3>
                <?php endif; ?>

                <form method="post">
                    <input type="password" name="lozinka" placeholder="Lozinka" required>

                    <button type="submit">Prijava</button>
                </form>

            <?php endif; ?>

            <?php if ($korak === "registracija") : ?>

                <h2>Registracija novog vozača</h2>

                <form method="post">
                    <input type="text" name="ime" value="<?= $registracija["ime"] ?>" required>

                    <input type="text" name="prezime" value="<?= $registracija["prezime"] ?>" required>

                    <input type="password" name="lozinka" placeholder="Lozinka" required>

                    <input type="text" name="marka" placeholder="Marka motora">

                    <input type="text" name="model" placeholder="Model motora">

                    <input type="text" name="broj" placeholder="Broj vozača">

                    <input type="text" name="klasa" placeholder="Klasa">

                    <button type="submit">Registriraj se</button>
                    <button type="submit" name="odustani" formnovalidate style="background:#666;color:white;"onmouseover="this.style.background='#555'" onmouseout="this.style.background='#666'">
                        Odustani
                    </button>
                </form>
            <?php endif; ?>
        </div>
    </body>
</html>
