<!DOCTYPE html>

<html>
    <head>
        <meta charset="utf-8" />
        <title>Scraper</title>
        <meta name="description" content="We need to scrape">
    </head>
    <body>
        <header>
            <h1>Scraper de page </h1>
        </header>

        <section>

            <?php

                $projet = $url = $userAgent = "";
                $timeOut = 0;
                $png = $jpg = $gif = false;

                $arrayRefUserAgent = [
                    "googleChromeWindows",
                    "googleChromeAndroid",
                    "googleChromeLinux",
                    "FirefoxWindow",
                    "FirefoxAndroid",
                    "FirefoxLinux",
                    "ieWindow",
                    "ieMobile",
                    "samsungGalaxyS",
                    "random",
                ];
                $arrayUserAgent = [
                        "googleChromeWindows" => "Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/27.0.1453.110 Safari/537.36",
                        "googleChromeAndroid" => "Mozilla/5.0 (Linux; Android 4.2.2; Nexus 7 Build/JDQ39) AppleWebKit/537.31 (KHTML, like Gecko) Chrome/26.0.1410.49 Safari/537.31",
                        "googleChromeLinux" => "Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/39.0.2171.99 Safari/537.36",
                        "FirefoxWindow" => "Mozilla/5.0 (Windows; U; Windows NT 6.1; fr; rv:1.9.2) Gecko/20100115 Firefox/3.6",
                        "FirefoxAndroid" => "Mozilla/5.0 (Android; Tablet; rv:19.0) Gecko/19.0 Firefox/19.",
                        "FirefoxLinux" => "Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:34.0) Gecko/20100101 Firefox/34.0",
                        "ieWindow" => "Mozilla/5.0 (Windows NT 6.3; Trident/7.0; rv:11.0) like Gecko",
                        "ieMobile" => "Mozilla/5.0 (compatible; MSIE 10.0; Windows Phone 8.0; Trident/6.0; IEMobile/10.0; ARM; Touch; NOKIA; Lumia 820)",
                        "samsungGalaxyS" => "Mozilla/5.0 (Linux; U; Android 2.3.3; fr-fr; GT-I9100 Build/GINGERBREAD) AppleWebKit/533.1 (KHTML, like Gecko) Version/4.0 Mobile Safari/533.1",
                ];

                if(!empty($_POST)) {
                    $error = 0;

                    $projet = $_POST["projet"];
                    $url = $_POST["url"];
                    $userAgent = $_POST["userAgent"];

                    $timeOut = $_POST["timeOut"];

                    $png = $_POST["png"];
                    $jpg = $_POST["jpg"];
                    $gif = $_POST["gif"];

                    if(empty($projet))
                        $error |= 0x0001;
                    if(empty($url))
                        $error |= 0x0002;
                    if(empty($userAgent))
                        $error |= 0x0004;

                    if(strlen($projet) < 2)
                        $error |= 0x0008;

                    if (!filter_var($url, FILTER_VALIDATE_URL))
                        $error |= 0x0010;

                    if($timeOut < 1)
                        $error |= 0x0020;

                    if(empty($png || $jpg || $gif))
                        $error |= 0x0040;

                    //User agent : 1 parmis la liste
                    $valideRefUserAgent = false;
                    foreach ($arrayRefUserAgent as $ref) {
                        if($ref == $userAgent)
                            $valideRefUserAgent = true;
                    }
                    if(!$valideRefUserAgent)
                        $error |= 0x0080;

                    if($error != 0) {
                        if($error & 0x0001)
                            echo "Entrer le nom du projet<br>";
                        if($error & 0x0002)
                            echo "Entrer une URL<br>";
                        if($error & 0x0004)
                            echo "veuillez choisire un User Agent<br>";
                        if($error & 0x0008)
                            echo "Le nom du projet doit contenire au moin 2 character<br>";
                        if($error & 0x0010)
                            echo "l'url n'est pas valide<br>";
                        if($error & 0x0020)
                            echo "Le time out n'est pas valide<br>";
                        if($error & 0x0040)
                            echo "choisire une extension pour le type d'image<br>";
                        if($error & 0x0080)
                            echo "l'user agent n'est pas valide<br>";
                    }
                    else {
                        if($userAgent == "random") {
                            $userAgent = $arrayUserAgent[$arrayRefUserAgent[rand(0, 8)]];
                        }
                        else {
                            $userAgent = $arrayUserAgent[$userAgent];
                        }

                        $projet = date("YmdHis")."_".$projet;
                        mkdir($projet);

                        $ch = curl_init($url);

                        curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);
                        curl_setopt($ch, CURLOPT_TIMEOUT, $timeOut);
                        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeOut);

                        curl_setopt($ch, CURLOPT_USERAGENT, $userAgent);

                        if (preg_match('#^https://#i', $url))
                        {
                            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
                        }

                        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

                        $page_content = curl_exec($ch);

                        curl_close($ch);

                        $img_content = strip_tags($page_content, "<img>");

                        preg_match_all("/http[^\"')]*\.gif/i", $img_content, $matchesGIF);
                        preg_match_all("/http[^\"')]*\.png/i", $img_content, $matchesPNG);
                        preg_match_all("/http[^\"')]*\.jpg/i", $img_content, $matchesJPG);

                        $resultsGIF = array_unique($matchesGIF[0]);
                        $resultsPNG = array_unique($matchesPNG[0]);
                        $resultsJPG = array_unique($matchesJPG[0]);

                        if(empty($resultsGIF)) {
                            echo "Il na pas d'image au format gif à cette adresse<br>";
                        }
                        else {
                            foreach ($resultsGIF as $url)
                            {
                                $image = @file_get_contents($url);
                                if(!empty($image)) {
                                    file_put_contents($projet."/".uniqid().".gif", $image);
                                }
                            }
                        }

                        if(empty($resultsPNG)) {
                            echo "Il na pas d'image au format png à cette adresse<br>";
                        }
                        else {
                            foreach ($resultsPNG as $url)
                            {
                                $image = @file_get_contents($url);
                                if(!empty($image)) {
                                    file_put_contents($projet."/".uniqid().".png", $image);
                                }
                            }
                        }

                        if(empty($resultsJPG)) {
                            echo "Il na pas d'image au format jpg à cette adresse<br>";
                        }
                        else {
                            foreach ($resultsJPG as $url)
                            {
                                $image = @file_get_contents($url);
                                if(!empty($image)) {
                                    file_put_contents($projet."/".uniqid().".jpg", $image);
                                }
                            }
                        }
                        
                    }
                }
            ?>

            <form action="" method="POST">
                <fieldset>
                    <legend> Information du scraping </legend>
                    <input type="text" name="projet" placeholder="Projet"><br>
                    <input type="url" name="url" placeholder="URL"> <br>
                    <input type="number" name="timeOut" placeholder="Time Out"><br>
                    <br>
                    <input type="checkbox" name="png" checked="checked"> png <br>
                    <input type="checkbox" name="jpg" checked="checked"> jpg <br>
                    <input type="checkbox" name="gif" checked="checked"> gif <br>
                    <br>
                    <select name="userAgent">
                        <option value="googleChromeWindows" > Google Chrome Windows </option> <!-- Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/27.0.1453.110 Safari/537.36 -->
                        <option value="googleChromeAndroid" > Google Chrome Android </option> <!-- Mozilla/5.0 (Linux; Android 4.2.2; Nexus 7 Build/JDQ39) AppleWebKit/537.31 (KHTML, like Gecko) Chrome/26.0.1410.49 Safari/537.31 -->
                        <option value="googleChromeLinux" > Google Chrome Linux Mint17 </option> <!-- Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/39.0.2171.99 Safari/537.36 -->
                        <option value="FirefoxWindow" > Firefox Windows </option> <!-- Mozilla/5.0 (Windows; U; Windows NT 6.1; fr; rv:1.9.2) Gecko/20100115 Firefox/3.6 -->
                        <option value="FirefoxAndroid" > Firefox Android </option> <!-- 	Mozilla/5.0 (Android; Tablet; rv:19.0) Gecko/19.0 Firefox/19.0 -->
                        <option value="FirefoxLinux" > Firefox Linux Mint 17 </option> <!-- Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:34.0) Gecko/20100101 Firefox/34.0 -->
                        <option value="ieWindow" > Internet Explorer Window </option> <!-- Mozilla/5.0 (Windows NT 6.3; Trident/7.0; rv:11.0) like Gecko -->
                        <option value="ieMobile" > Internet Explorer Mobile  </option> <!--  Mozilla/5.0 (compatible; MSIE 10.0; Windows Phone 8.0; Trident/6.0; IEMobile/10.0; ARM; Touch; NOKIA; Lumia 820) -->
                        <option value="samsungGalaxyS" > Samsung Galaxy S </option> <!-- Mozilla/5.0 (Linux; U; Android 2.3.3; fr-fr; GT-I9100 Build/GINGERBREAD) AppleWebKit/533.1 (KHTML, like Gecko) Version/4.0 Mobile Safari/533.1 -->
                        <option value="random" > Random </option>
                    </select><br>
                    <br>
                    <input type="submit" placeholder="Valider"> <br>
                </fieldset>
            </form>
        </section>

        <footer>
            <center> LAVAUD Louis-Philemon</center>
        </footer>
    </body>
</html>
