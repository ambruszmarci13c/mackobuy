<?php
function adatokLekeres($muvelet) {
    //Kapcsolat létrehozás:
    $db = new mysqli ('localhost', 'root', '', 'mackobuy');
    //Kapcsolat létrejöttének vizsgálata:
    if ($db->connect_errno == 0) {
        //Az SQL művelet végrehajtása:
        $eredmeny = $db->query($muvelet);
        //Történt-e hiba a végrehajtáskor:
        if ($db->errno == 0) {
            //Kaptunk-e vissza adatokat:
            if ($eredmeny->num_rows != 0) {
                //Az adatok lehívása az adatbázis kiszolgálóról:
                $adatok = $eredmeny->fetch_all(MYSQLI_ASSOC);
                return $adatok;
            }
            else {
                return 'Nincsenek találatok!';
            }
        }
        else {
            return $db->error;
        }
    }
    else {
        return $db->connect_error;
    }
}

function adatokValtoztatasa($muvelet){
    $db = new mysqli ('localhost', 'root', '', 'mackobuy');
    if($db->connect_errno==0){
        $db->query($muvelet);
        if($db-> errno==0){
            if($db->affected_rows>0){
                return 'Sikeres művelet!';
            }
            else if($db->affected_rows==0){
                return 'Sikertelen művelet!';
            }
            else{
                return $db->error;
            }
        }
        else{
            return $db->error;
        }
    }
    else{
        return $db->connect_error;
    }
}

?>