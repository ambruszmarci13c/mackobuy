-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Gép: 127.0.0.1
-- Létrehozás ideje: 2025. Már 31. 13:07
-- Kiszolgáló verziója: 10.4.32-MariaDB
-- PHP verzió: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Adatbázis: `mackobuy`
--

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `akciok`
--

CREATE TABLE `akciok` (
  `ID` int(11) NOT NULL,
  `merteke` int(11) DEFAULT NULL,
  `kezdesi_Ido` date DEFAULT NULL,
  `befejezesi_Ido` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;

--
-- A tábla adatainak kiíratása `akciok`
--

INSERT INTO `akciok` (`ID`, `merteke`, `kezdesi_Ido`, `befejezesi_Ido`) VALUES
(1, 10, '2024-01-01', '2024-12-31'),
(2, 15, '2024-03-01', '2024-06-01'),
(3, 20, '2024-07-01', '2024-09-30');

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `ertekelesek`
--

CREATE TABLE `ertekelesek` (
  `ID` int(11) NOT NULL,
  `FID` int(11) DEFAULT NULL,
  `TID` int(11) DEFAULT NULL,
  `ertekeles` int(11) DEFAULT NULL CHECK (`ertekeles` between 1 and 5),
  `megjegyzes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;

--
-- A tábla adatainak kiíratása `ertekelesek`
--

INSERT INTO `ertekelesek` (`ID`, `FID`, `TID`, `ertekeles`, `megjegyzes`) VALUES
(1, 1, 1, 5, 'Nagyon elégedett vagyok!'),
(2, 2, 2, 4, 'Jó termék, de lehetne olcsóbb.'),
(3, 3, 3, 3, 'Átlagos minőség.'),
(4, 4, 4, 5, 'Nagyon hasznos eszköz!'),
(5, 5, 5, 2, 'Nem volt olyan jó, mint vártam.');

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `felhasznalok`
--

CREATE TABLE `felhasznalok` (
  `ID` int(11) NOT NULL,
  `fnev` varchar(50) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `jelszo` varchar(50) DEFAULT NULL,
  `jogkor` enum('admin','user') DEFAULT NULL,
  `penznem` varchar(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;

--
-- A tábla adatainak kiíratása `felhasznalok`
--

INSERT INTO `felhasznalok` (`ID`, `fnev`, `email`, `jelszo`, `jogkor`, `penznem`) VALUES
(2, 'admin01', 'admin01@csigusz.com', 'adminpw', 'admin', 'HUF'),
(3, 'felhasznalo02', 'felhasznalo02@gmail.com', 'jelszo02', 'user', 'HUF'),
(4, 'felhasznalo03', 'felhasznalo03@gmail.com', 'jelszo03', 'user', 'HUF'),
(5, 'admin02', 'admin02@gmail.com', 'adminpw', 'admin', 'HUF');

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `kategoria`
--

CREATE TABLE `kategoria` (
  `id` int(10) NOT NULL,
  `nev` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;

--
-- A tábla adatainak kiíratása `kategoria`
--

INSERT INTO `kategoria` (`id`, `nev`) VALUES
(1, 'Elektronikai eszközök'),
(2, 'Háztartási kellékek'),
(3, 'Könyvek'),
(4, 'Játékok'),
(5, 'Kerti eszközök'),
(6, 'Egészség');

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `kedvencek`
--

CREATE TABLE `kedvencek` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `termek_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- A tábla adatainak kiíratása `kedvencek`
--

INSERT INTO `kedvencek` (`id`, `user_id`, `termek_id`) VALUES
(51, 2, 2),
(48, 2, 13),
(49, 2, 14),
(50, 2, 15),
(52, 2, 61),
(31, 3, 2),
(36, 3, 3),
(34, 3, 6);

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `rendelesek`
--

CREATE TABLE `rendelesek` (
  `ID` int(11) NOT NULL,
  `FID` int(11) DEFAULT NULL,
  `vegosszeg` int(11) NOT NULL,
  `rendeles_datum` date DEFAULT NULL,
  `szallitasi_dij` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;

--
-- A tábla adatainak kiíratása `rendelesek`
--

INSERT INTO `rendelesek` (`ID`, `FID`, `vegosszeg`, `rendeles_datum`, `szallitasi_dij`) VALUES
(1, 2, 150000, '2025-03-31', 1790),
(2, 3, 9100, '2024-01-20', 1000),
(3, 3, 8000, '2024-02-01', 1200),
(4, 2, 8000, '2024-02-10', 1300),
(5, 2, 8000, '2024-02-15', 1400),
(6, 2, 1200000, '2025-03-31', 1790),
(7, 2, 4400000, '2025-03-31', 1790),
(8, 2, 600000, '2025-03-31', 1190),
(9, 2, 150419, '2025-03-31', 1190);

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `termekek`
--

CREATE TABLE `termekek` (
  `ID` int(11) NOT NULL,
  `tnev` varchar(100) DEFAULT NULL,
  `leiras` text DEFAULT NULL,
  `ar` int(11) DEFAULT NULL,
  `kategoria` varchar(50) DEFAULT NULL,
  `kep` varchar(100) DEFAULT NULL,
  `garancia` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;

--
-- A tábla adatainak kiíratása `termekek`
--

INSERT INTO `termekek` (`ID`, `tnev`, `leiras`, `ar`, `kategoria`, `kep`, `garancia`) VALUES
(1, 'Apple iPhone 11', 'Apple iPhone 11 Mobiltelefon, Dual SIM, Kártyafüggetlen, 64 GB\nAz Apple iPhone 11 egy prémium kategóriás okostelefon, amely kiváló teljesítményt és eleganciát kínál. A készülék Dual SIM támogatással rendelkezik, így lehetőséget biztosít két SIM-kártya egyidejű használatára, ideális választás azok számára, akik szeretnék különválasztani személyes és üzleti kapcsolataikat, vagy a külföldi utazások során is egyszerűbben kezelni a mobilhálózatokat.', 150000, '1', 'elekt1.jpg', 36),
(2, 'ASUS ROG Laptop', 'Laptop Gaming ASUS ROG TUF A15, FA506NF-HN044, 15,6\", FHD (1920 x 1080) 16:9, r5-7535HS, AMD Radeon™ Graphics', 300000, '1', 'elekt2.jpg', 24),
(3, 'JBL Vibe 200TWS Bluetooth fülhallgató', 'JBL Vibe 200TWS - Valódi vezeték nélküli fülhallgató, 20 óra kombinált lejátszás, JBL Deep Bass hangzás', 30000, '1', 'elekt3.jpg', 24),
(4, 'Xiaomi Redmi Pad SE Tablet', 'Xiaomi Redmi Pad SE táblagép, 4 GB RAM, 128 GB', 80000, '1', 'elekt4.jpg', 24),
(5, 'AOVO QW33 Okosóra', 'AOVO QW33 Okosóra Magyar menüvel, Vezeték nélküli Hívás, Sportoláshoz ideális', 40000, '1', 'elekt5.jpg', 24),
(6, 'GeFors Nitro Ultra3 Gaming Asztali számítógép', 'GeFors Nitro Ultra3 Gaming Asztali Számítógép Intel® SIX-Core™ i5-9400 4,1 Ghz asztali PC-rendszer, 16 GB DDR4, 1000 GB HDD, VIDEO 4 GB GDDR5', 200000, '1', 'elekt6.jpg', 12),
(7, 'AirPods Pro 2', 'AirPods Pro (2. generáció) MagSafe tokkal (USB-C)', 120000, '1', 'elekt7.jpg', 36),
(8, 'E-book olvasó', 'Amazon Kindle 2022 e-book olvasó, 6\" kijelző, 300 ppi, USB-C, fekete', 35000, '1', 'elekt8.jpg', 24),
(9, 'Okos TV', 'ILIKE 43 inch FHD Smart LED Android Televízió, 109 cm Full HD Okos TV', 100000, '1', 'elekt9.jpg', 36),
(10, 'JBL GO3BLU hangszóró', 'JBL GO3BLU Hordozható hangszóró, Bluetooth, Kiváló hangminőség, vízálló', 20000, '1', 'elekt10.jpg', 24),
(11, 'Dreame MOVA J30 porszívó', 'Dreame MOVA J30 vezeték nélküli álló porszívó', 49990, '2', 'hazt1.jpg', 24),
(12, 'Bosch WGG242Z3BY Mosógép', 'Gyűrődésmentes ruhát minimális erőfeszítéssel? Akár 50%-kal kevesebb gyűrődés.', 186990, '2', 'hazt2.jpg', 36),
(13, 'Star-Light ACT-12WIFI Klíma', 'Star-Light ACT-12WIFI Klímaberendezés, 3.5kW, Inverter, WiFi vezérlés, A++ energiaosztály.', 138990, '2', 'hazt3.jpg', 24),
(14, 'Electrolux EOF3H00BX SurroundCook 600 Sütő', '600-as sorozatú SurroundCook sütő, egyenletes hőeloszlás légkeringetéssel.', 109990, '2', 'hazt4.jpg', 36),
(15, 'SAMSUNG MG23K3515AK/EO Mikrohullámú sütő', 'SAMSUNG MG23F301TAK/EO Mikrohullámú sütő, ECO üzemmóddal, 23 L', 40899, '2', 'hazt5.jpg', 12),
(16, 'Tefal Virtuo 30 FV2C42E0 vasaló', 'Tapadásmentes vasalólemez, 1800 W teljesítmény, gyors eredmények.', 10989, '2', 'hazt6.jpg', 24),
(17, 'SAMSUNG RB34C600ESA/EF Hűtőszekrény', '230 l kapacitás, LED világítás, nyitott hűtőajtó jelzés.', 156900, '2', 'hazt7.jpg', 24),
(18, 'SAMSUNG DW60A6092BB/EO Mosogatógép', 'Energiahatékony mosogatógép, rugalmas kosarak.', 199899, '2', 'hazt8.jpg', 36),
(19, 'Gusto Classico kávégép', 'Aromás kávékészítés retro dizájnnal, 20 bar nyomású eszpresszó gép.', 51590, '2', 'hazt9.jpg', 24),
(20, 'Ninja BN750EU Mixer', '1200 wattos asztali mixer, sokoldalú konyhai megoldás.', 55442, '2', 'hazt10.jpg', 12),
(21, 'A füredi lány', 'Almássy Anna története az 1763-as évbe vezet vissza.', 5000, '3', 'konyv1.jpg', NULL),
(22, 'Zserbó', 'Gerbeaud Emil fiumei csokoládégyárának története.', 5990, '3', 'konyv2.jpg', NULL),
(23, 'A villa', 'Egy izgalmas valóságshow titkai egy luxusvillában.', 4490, '3', 'konyv3.jpg', NULL),
(24, 'Crow - Varjú - Boston alvilág I.', 'Egy ír maffiózó története.', 4990, '3', 'konyv4.jpg', NULL),
(25, 'Hidegre vagy hűvösre - Maffiózók bálnamerciben 1.', 'Czibere Attila kalandos életútja.', 5490, '3', 'konyv5.jpg', NULL),
(26, 'Micimackó - Bölcsességek a Százholdas Pagonyból', 'A Százholdas Pagony lakóinak gondolatai.', 3000, '3', 'konyv6.jpg', NULL),
(27, 'Amikor találkoztunk', 'Egy romantikus történet New York utcáin.', 4990, '3', 'konyv7.jpg', NULL),
(28, 'Háború és béke', 'Lev Tolsztoj klasszikusának új magyar fordítása.', 5990, '3', 'konyv8.jpg', NULL),
(29, 'Stephen King - A világ királya', 'Stephen King eddigi élete és munkássága.', 4490, '3', 'konyv9.jpg', NULL),
(30, 'A jövő kódjai', 'A mesterséges intelligencia és az emberiség útja.', 6990, '3', 'konyv10.jpg', NULL),
(31, 'Ravensburger - Eltzi vár - 1000 db-os puzzle', 'A Ravensburger kiváló minőségű termékei mögött több évtizedes puzzle-gyártási tapasztalat áll, mely garantálja a felejthetetlen puzzle-élményt. A kiváló termékminőség mellett a 2D-s és 3D-s kirakók rendkívül széles választékát nyújtja. A minőség iránti szenvedély, a részletekre való odafigyelés és a motívumok hatalmas választéka teszi a Ravensburger kirakókat olyan egyedivé. Kínálatában kezdőktől a profikig mindenki megtalálja a számára tökéletes puzzle-t!', 3990, '4', 'jatek1.jpg', NULL),
(32, 'Fröccs társasjáték', 'A Hajnóczy Soma, kétszeres bűvészvilágbajnok által tervezett Fröccs társasjátékban minél több és tartalmasabb kocsmai keveréket kell eladjunk: egy fél literes vice házmester jóval magasabb áron kel el, mint egy háromdecis hosszú lépés, pláne ha finomabb borból készül! A jó fröccs készítésében segíthet karakterünk képessége - vagy abban is segíthet, hogy a konkurencia nehezebben tegyen nekünk keresztbe! Mert bizony akciókártyákkal ki tudják löttyinteni a poharunkból a szódát, a bort vagy össze tudják törni az üres poharat. De ha jó ajánlatot teszünk neki, megvesztegetjük, akkor lehet meg tudjuk menteni magunkat. Egy igazi könnyed, vicces partyjáték.', 7990, '4', 'jatek2.jpg', NULL),
(33, 'Eichhorn: hagyományos építőkocka - 50 db, színes', 'Hagyományos, fából készült kocka készlet. Már a legkisebbek is szerteni fogják, az élénk színek, különböző formák miatt. A csomag 50 db kockát tartalmaz egy praktikus tárolódobozban. A hordozást a tárolóhoz erősített fülek segítik.', 7990, '4', 'jatek3.jpg', NULL),
(34, 'Marcika plüss maci 25 cm', 'A Marcika maci tökéletes választás azok számára, akik imádnivaló és elegáns társat keresnek. A 25 cm magas Marcika kiváló minőségű anyagokból készült, puha és kellemes bundát kínálva. A Marcika mackó tökéletes partner az ölelésekhez és a gyengéd pillanatokhoz! Függetlenül attól, hogy elviszed játszani, vagy a polcra teszed dekorációként, Marcika mackó mindig mosolyt csal az arcodra!', 9990, '4', 'jatek4.jpg', NULL),
(35, 'Lean Toys távirányítós autó, Műanyag/Fém, 1:8, 8 év+', 'A pick-up modell a legmagasabb minőségből készült, minden részlete kiemeli ennek az autónak az eredetiségét. A nagy, széles, 14 cm átmérőjű kerekek növelik az autó tapadását a felületen. A lengéscsillapítók lehetővé teszik a vezetést egyenetlen terepen, és csökkentik az autó felborulásának kockázatát. A jármű nagy sebességre képes. Gyermeke saját akadálypályát készíthet játékaiból, könyveiből, és kipróbálhatja ezt az autót. A sok ötlet, a képzelet gyönyörű darabokat hoz létre.', 10000, '4', 'jatek5.jpg', NULL),
(36, 'Shrek mini figura, 9 cm', 'Bizonyára mindannyian ismeritek és kedvelitek a Shrek című animációs filmet! Most pedig meg is szerezhetitek őket, hogy minél több időt együtt tudjatok tölteni! A Shrek figura mérete 9 cm. Anyaga: PVC-mentes termoplasztik. Gyorsan a fiúk és lányok kedvenc játékává fog válni, minden korosztály számára ajánlani tudjuk. Ezekkel a figurákkal repülni fog az idő!', 8000, '4', 'jatek6.jpg', NULL),
(37, 'Otthoni csocsó-futballasztal', 'A Football Table for Home tökéletes megoldás a barátaival és családjával való szórakozáshoz. Kompakt, 57 x 28 x 11,5 cm-es méretének és strapabíró ABS anyagának köszönhetően ez az asztal ideális otthona bármely egyenes felületére. A csomag mindent tartalmaz, ami a móka elindításához kell: egy fociasztal és két kis labda. Ezzel a futballasztallal izgalmas versenyeket rendezhetsz, és minden eseményhez egy kis izgalmat adhatsz, legyen szó baráti összejövetelről vagy születésnapról.', 4500, '4', 'jatek7.jpg', NULL),
(38, 'Disney marie plüssjáték 25cm', 'Disney plus Dumbo 25 cm. Ez a szuper puha és szuper aranyos plüss gyermeked legjobb barátja lesz. Dumbo egy csodálatos állat. Hozd haza a Disney varázsát! Hozd létre saját gyűjteményedet ezekkel az időtlen Disney-figurákkal.', 6000, '4', 'jatek8.jpg', NULL),
(39, 'Pöttyös lakkfényű labda - 18 cm', 'A gyerekek életéből már pici kortól kimaradhatatlan játék a labda. Szórakozásra, hasznos időtöltésre, színpadi és játszótéri bemutatókra, illetve sportolás céljából is használható a labda. Egyedül és csoportos ügyességi játékokat is tudunk játszani. A labdajátékok fejlesztik a gyerekek reflex képességét és mozgáskoordinációját. Piros lakkfényű labda, fehér pöttyökkel tarkítva. Mérete: 18 cm', 2000, '4', 'jatek9.jpg', NULL),
(40, 'HOMCOM Gyerek kosárlabda karika', 'Kihívja gyermekeit a kosárlabdára! Ez a HOMCOM gyermekkosár tökéletes az egész családdal való játékhoz: a magasság állítható, vízzel vagy homokkal feltölthető állólámpával és robusztus fémvázzal rendelkezik. És amikor vége a játéknak, kényelmesen mozgathatja a kosarat az alap két kerekének köszönhetően.', 35000, '4', 'jatek10.jpg', NULL),
(41, 'Bosch ARM 3200 fűnyíró', 'A gyepfésű tökéletesen levágja a gyepet a szélekig. Kis súlyának köszönhetően könnyen kezelhető és szállítható. Az erőteljes Powerdrive motor gondoskodik a fáradság nélküli munkavégzésről még magas fűben is.', 40340, '5', 'kert1.jpg', 6),
(42, 'Hyundai HYD-7030-20VBL metszőolló', 'A Hyundai HYD-7030-20VBL egy hatékony akkumulátoros metszőolló, amely szénkefe nélküli motorral van felszerelve, ezáltal nagyobb hatékonyságot, hosszabb élettartamot és kevesebb karbantartást igényel. A készülék kiválóan alkalmas kertészeti feladatokra, például fák, bokrok és szőlők metszésére. A csomagban 2 db 20V-os lítium-ion akkumulátor és gyorstöltő, valamint egy pótkés található, biztosítva a hosszabb munkavégzést és a gondtalan használatot. A metszőolló maximális vágási átmérője 25 mm, így könnyedén elbánik vastagabb ágakkal is.', 23990, '5', 'kert2.jpg', 6),
(43, 'MTP AGRI 3/4\" 15M Tömlő', 'Kerti locsolótömlő belső fonott megerősítéssel háztartási használatra. Szerkezete 3 rétegű: 1.:belső fekete réteg algásodás ellen, 2.:háló textil erősítés réteg, 3.: külső színes réteg. Ezen tömlővel biztosan örömmel locsol majd kertjében.', 6199, '5', 'kert3.jpg', 6),
(44, 'Truper ültető lapát', 'Kiváló minőségű, ültető lapát, melyet kifejezetten ültetéshez, gyomláláshoz ajánlunk.', 1150, '5', 'kert4.jpg', 6),
(45, 'Kerti kesztyű Flower', 'Kényelmes női munkakesztyűt, biztos fogást biztosít, latexhab bevonattal. Kényelmes viselést és optimális biztonságot nyújt, valamint megvédi a kezeket a szennyeződéstől, illetve a mechanikai kockázatoktól. Tökéletes munkakesztyű raktári munkákhoz vagy gyártáshoz.', 1549, '5', 'kert5.jpg', 6),
(46, 'Komposztáló Láda Modul 800L', 'Ajánlott kertekhez, közös kertekhez. Nagyobb családoknak ideális választás méretben. Újrahasznosított műanyagból készül, így használatával duplán véded a környezetet. Az oldalán található lyukak biztosítják az oxigén és a nitrogén bejutását. A modulokat összetartó oldalak nyitottak.', 31990, '5', 'kert6.jpg', 6),
(47, 'Black+Decker axiális akkus lombfúvó Basis BCBL200B 18V', 'A Black+Decker BCBL200B axiális akkus lombfúvó ideális a kocsibeállók, járdák, teraszok, kertek, füves területek, ágyások és más szilárd talajok falevelektől, kerti hulladéktól és fűnyesedéktől való megtisztítására. 18V teljesítményének és akár 145 km/ h fúvási sebességének köszönhetően a lombseprést villámgyorsan elintézi. Az integrált lombgereblyével pedig a makacsabb szennyeződések is kényelmesen eltávolíthatók. A 577 m3/h óránkénti fúvási térfogatnak és a 145 km/h fúvás sebességnek köszönhetően a munka egy nagyobb kertben is gyerekjáték. Két fúvási sebesség közül lehet választani: vagy a teljesítmény maximális, vagy a működési idő.', 24990, '5', 'kert7.jpg', 6),
(48, '40 sejtes vetőtálca szivatótálcával', 'A 40 sejtes vetőtálca szivatótálcával palántaneveléshez, dugványneveléshez ajánljuk. Az alulról végzett öntözést a szivatótálca biztosítja. Palántadőlés kialakulása ellen kamillateás öntözést javaslunk.', 2000, '5', 'kert8.jpg', 6),
(49, 'LEKNES Rakásolható kerti szék', 'Rakásolható kerti szék, kényelmes könnyen szállítható', 8000, '5', 'kert9.jpg', 6),
(50, 'SOL LED Napelemes lámpa', 'LED napelemes lámpa érzékelővel. Beépített akkumulátor. Beépített alkonyat-érzékelő - szürkület után automatikusan bekapcsolja a lámpát. A világítás időtartama: 6-8 óra.', 2290, '5', 'kert10.jpg', 6),
(51, 'Algoflex Rapid 400 mg kapszula 10x Fájdalomcsillapító', 'Az Algoflex Rapid hatóanyaga az ibuprofén a nem-szteroid gyulladáscsökkentők csoportjába tartozik, amelyek a szervezetnek a fájdalomra, gyulladásra és lázra adott reakciójának megváltoztatásával biztosítanak enyhülést. Az Algoflex Rapid fejfájás, migrén, fogfájás, hátfájás, menstruációs fájdalmak, izomfájdalmak, láz valamint megfázás és influenza tünetei esetén alkalmazható.', 2079, '6', 'egi1.jpg', NULL),
(52, 'Walmark Marslakócskák Gummivitamin Echinaceával', 'A Walmark Marslakócskák Gummivitamin Echinaceával segíti gyermeked immunrendszerének erősítését természetes echinacea kivonattal. Finom gyümölcsös ízei miatt a kicsik szívesen fogyasztják, így könnyedén biztosíthatod számukra a szükséges vitaminokat a mindennapokban.', 2790, '6', 'egi2.jpg', NULL),
(53, 'Gárdonyi Teaház Gyümölcstea Válogatás', 'Relaxáló hatás finom :D', 3199, '6', 'egi3.jpg', NULL),
(54, 'Mivolis Vízálló sebtapasz', 'A Mivolis vízálló sebtapasz légáteresztő anyagból készült és hatékonyan óvja a kisebb sérüléseket a nedvességtől és a szennyeződéstől. Ezenkívül latexmentes, így ideális elsősegélynyújtáshoz és sebkezeléshez. Horzsolások és vágások esetén segít megakadályozni, hogy baktériumok kerüljenek a sebbe. Praktikus, 5 különböző méretű tapaszt tartalmazó csomagban.', 379, '6', 'egi4.jpg', NULL),
(55, 'Vivamax Mercury-free GYVDL1 lázmérő', 'Digitális hőmérő', 3000, '6', 'egi5.jpg', NULL),
(56, 'OMRON M2+ digitális vérnyomásmérő', 'Vérnyomásmérő - karra, mandzsetta: 22-42 cm, 30 memóriahely, mérési tartomány: vérnyomás 0-299 mmHg, pulzus 40-180 / perc, mérési pontosság: vérnyomás +/-3 Hgmm, pulzus +/-5%', 18290, '6', 'egi6.jpg', NULL),
(57, 'Bradolife kézfertőtlenítő gél', 'Víz nélkül tisztító és fertőtlenítő gél. Nem csak a jól ismert baktériumokkal de a gombákkal és a vírusokkal szemben is védelmet nyújtó készítmény.', 1003, '6', 'egi7.jpg', NULL),
(58, 'Nurofen eperízű lázcsillapító', 'A feltüntetett ár maximált fogyasztói ár, a webshopban résztvevő egyes gyógyszertárak ennél alacsonyabb áron is értékesíthetik. Az ár internetes megrendelés esetén érvényes a készlet erejéig.', 4649, '6', 'egi8.jpg', NULL),
(59, 'Denkmit Fertőtlenítő spray, pumpás', 'A Denkmit fertőtlenítő spray alkalmas a kéz és különböző alkoholálló felületek gyors és megbízható fertőtlenítésére. Baktériumok, élesztőgombák és bizonyos vírusok ellen is hatékonyan veszi fel a küzdelmet.', 699, '6', 'egi9.jpg', NULL),
(60, 'Massage Gun Black masszázspisztoly – Climaqx', 'A Massage Gun masszázspisztoly az egyik leginnovatívabb regenerációs fitnesz eszköz. A pisztoly magas teljesítményű motorja segít enyhíteni az izomfeszültséget, miközben az izmok vérkeringését is fokozza. A pisztolyhoz 4 masszázsfej tartozik, amiknek a segítségével hatékonyan átmasszírozhatod az egyes izmokat. A 20 sebességi fokozatnak köszönhetően igényeidnek megfelelően állíthatod be a masszázs erősségét, így szabályozhatod az izmok regenerációjának folyamatát.', 55490, '6', 'egi10.jpg', NULL),
(61, 'Lirili Larila', 'Ez a játékfigura egy fantasztikus, szürreális teremtmény, amelyet Lirili Larila-nak neveznek. Teste egy hosszúkás, zöld kaktusz, amelynek felszíne tüskés és bordázott, akárcsak egy saguaro kaktuszé. Az elefánt feje azonban valósághű, puha tapintású műanyagból készült, nagy fülekkel és hosszú ormánnyal.\r\n\r\nA figura lábai szandálba bújtatott kaktusztörzsek, amelyek kissé ügyetlenül, de stabilan állnak a sivatagi homokon. A szandálok részletesen kidolgozottak, barnára festett talpakkal és zöld talpbetéttel, mintha a növény a cipőből nőne ki.\r\n\r\nA figura kiegészítője egy kis, lebegő ébresztőóra, amely a közelében forog. Az óra antik stílusú, bronzos színű, és a sivatagi tájban szabadon lebeg, mintha varázserő tartaná a levegőben.\r\n\r\n', 100000, '4', 'jatek11.jpg', NULL);

--
-- Indexek a kiírt táblákhoz
--

--
-- A tábla indexei `akciok`
--
ALTER TABLE `akciok`
  ADD PRIMARY KEY (`ID`);

--
-- A tábla indexei `ertekelesek`
--
ALTER TABLE `ertekelesek`
  ADD PRIMARY KEY (`ID`);

--
-- A tábla indexei `felhasznalok`
--
ALTER TABLE `felhasznalok`
  ADD PRIMARY KEY (`ID`);

--
-- A tábla indexei `kategoria`
--
ALTER TABLE `kategoria`
  ADD PRIMARY KEY (`id`);

--
-- A tábla indexei `kedvencek`
--
ALTER TABLE `kedvencek`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `felhasznalo_id` (`user_id`,`termek_id`),
  ADD KEY `termek_id` (`termek_id`);

--
-- A tábla indexei `rendelesek`
--
ALTER TABLE `rendelesek`
  ADD PRIMARY KEY (`ID`);

--
-- A tábla indexei `termekek`
--
ALTER TABLE `termekek`
  ADD PRIMARY KEY (`ID`);

--
-- A kiírt táblák AUTO_INCREMENT értéke
--

--
-- AUTO_INCREMENT a táblához `kategoria`
--
ALTER TABLE `kategoria`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT a táblához `kedvencek`
--
ALTER TABLE `kedvencek`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=53;

--
-- AUTO_INCREMENT a táblához `rendelesek`
--
ALTER TABLE `rendelesek`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- Megkötések a kiírt táblákhoz
--

--
-- Megkötések a táblához `kedvencek`
--
ALTER TABLE `kedvencek`
  ADD CONSTRAINT `kedvencek_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `felhasznalok` (`ID`) ON DELETE CASCADE,
  ADD CONSTRAINT `kedvencek_ibfk_2` FOREIGN KEY (`termek_id`) REFERENCES `termekek` (`ID`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
