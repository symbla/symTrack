<?php session_start(); $_SESSION['t_log_status'] = "1"; ?>
<?php

###### BITTE EINTRAGEN ##########################################
                                                     
  $MySQL_Server     = "localhost";		# MySQL-Server
  $MySQL_Benutzer   = "user";				# MySQL-Benutzer
  $MySQL_Passwort   = "UseR123!";		# MySQL-Passwort
  $MySQL_Datenbank  = "test";				# MySQL-Datenbank
  $MySQL_Tabelle    = "symtrack";		# MySQL-Tabelle
  
# Fehlermeldungen aufzeichnen? (j = Ja; n = Nein)
# Sollten Fehler auftreten, prüfen Sie "tracker.log" auf Hinweise.
  $Enable_LOG       = "j";                    

#################################################################

###### !AB HIER NICHTS NICHT ÄNDERN! ############################

  $t_date = date("d.m.Y", time());
  $t_time = date("H:i:s", time());

###### LOG_FUNKTION
  function t_log($t_log_message, $t_log_status) {
    global $Enable_LOG;
    if($Enable_LOG == "j") {
      $t_log_f = fopen("./tracker.log", "a+");
      global $t_date, $t_time;
      if($t_log_f) {
        fwrite($t_log_f, "<$t_date, $t_time Uhr> # $t_log_message\n");
        fclose($t_log_f);
      } else {
        echo "Fehler mit tracker.log\n";
      }
      return $_SESSION['t_log_status'] = $t_log_status;
    } else {}
  }

###### HAUPTFUNKTION
  function tracking($t_ip, $t_lang, $t_agent, $t_loc, $t_chron) {
                      
    global $MySQL_Server, $MySQL_Benutzer, $MySQL_Passwort, $MySQL_Datenbank, $MySQL_Tabelle;   
    global $t_date, $t_time;                                                                                                                                                                            
                                                                
###### ALLGEMEINE_VARIABLEN                                     
    $t_host             = $MySQL_Server;                               
    $t_user             = $MySQL_Benutzer;                             
    $t_pw               = $MySQL_Passwort;                           
    $t_db               = $MySQL_Datenbank;                          
    $t_table            = $MySQL_Tabelle;          
    
###### USER_BASICS
    $t_user_rand        = rand("0", "999999999");
    $t_user_temp_id     = "tr:{".$t_date."|".$t_time."}:".$t_user_rand;
    $t_user_ip          = $_SERVER['REMOTE_ADDR'];                            
                                                                
###### SYSTEM_VARIABLEN                                          
    $t_connect          = mysql_connect($t_host, $t_user, $t_pw);
    $t_select_db        = mysql_select_db($t_db, $t_connect);          
    
    if($t_ip=="0") {
      t_log("Die IP-Adresse muss gegeben sein. (Bitte 1 eintragen)", "0");
    } elseif(empty($t_host) || empty($t_user) || empty($t_pw)) { 
      t_log("Bitte geben Sie Mysql-Host-, Benutzer-, und Passwort an.", "0");
      unset($t_connect);
    } elseif(isset($t_connect) && !$t_connect) {
      die();
    	t_log("Ein Fehler bei der MySQL-Verbindung ist aufgetreten (SQL: ".mysql_error().")", "0");
    } elseif(empty($t_db)) {
      t_log("Keine Datenbank angegeben!", "0");
    } elseif(!empty($t_db)) {
      if(!$t_select_db) {
        die();
        t_log("Die Datenbank \"$t_db\" kann nicht verwendet werden (SQL: ".mysql_error().")", "0");
      } else {
        if(empty($t_table)) {
          $t_table = "tracker";
        }
        $t_ct_query = mysql_query("CREATE TABLE IF NOT EXISTS `".$t_db."`.`".$t_table."` (`ID` TEXT NOT NULL, `IP` TEXT NOT NULL, `SPRACHE` TEXT NOT NULL, `AGENT` TEXT NOT NULL, `POSITION` TEXT NOT NULL, `ERSTER_AUFRUF` TEXT NOT NULL, `AUFRUFE` INT NOT NULL, `LETZTER_AUFRUF` TEXT NOT NULL, `VERLAUF` TEXT NOT NULL)");
        if(!$t_ct_query) {
          t_log("Tabelle \"$t_table\" konnte nicht erstellt werden! (SQL: ".mysql_error().")", "0");
        }
      }
    } else {
      t_log("Datenbankstatus unbekannt. Installieren Sie das Skript erneut.", "0");
    }
    if($_SESSION['t_log_status']=="1") {
      // Tracking
        echo $t_user_firstconn;
        if($t_lang) {
          $t_user_lang = $_SERVER['HTTP_ACCEPT_LANGUAGE'];
          $t_user_lang = substr($t_user_lang, 0, 5);
        } else { $t_user_lang = "deaktiviert"; }
        if($t_agent) {
          $t_user_agent = $_SERVER['HTTP_USER_AGENT'];
        } else { $t_user_agent = "deaktiviert"; }
        if($t_loc) {
          $t_user_loc_init = unserialize(file_get_contents('http://www.geoplugin.net/php.gp?ip='.$t_user_ip));
          $t_user_loc_contcode = $t_user_loc_init['geoplugin_continentCode'];
          $t_user_loc_ccode = $t_user_loc_init['geoplugin_countryCode'];
          $t_user_loc_cname = $t_user_loc_init['geoplugin_countryName'];
          $t_user_loc_latitude = $t_user_loc_init['geoplugin_latitude'];
          $t_user_loc_longitude = $t_user_loc_init['geoplugin_longitude'];
          $t_user_loc = "$t_user_loc_cname ($t_user_loc_ccode), $t_user_loc_contcode \n [$t_user_loc_latitude, $t_user_loc_longitude]";
        } else { $t_user_loc = "deaktiviert"; }
        if($t_chron) {
          $t_user_temp_chron  = $_SERVER['REQUEST_URI'];
        } else {
          $t_user_temp_chron  = "deaktiviert";
        }
        if($_SESSION['t_tracker_id']!=$t_user_temp_id && $_SESSION['t_user_ip']==$_SERVER['REMOTE_ADDR']) {
          $_SESSION['t_sa_count']++;
          if(!$t_chron) {
            $_SESSION['t_user_chron'] = "deaktiviert";
          } else {
            $_SESSION['t_user_chron'] = $_SESSION['t_user_chron']."[$t_date, $t_time Uhr] -> ".$t_user_temp_chron."\n";
          }
        } else {
          $_SESSION['t_user_ip'] = $t_user_ip;
          $_SESSION['t_tracker_id'] = $t_user_temp_id;
          $_SESSION['t_sa_count'] = "1";
          $_SESSION['t_user_chron'] = "[".$_SERVER['HTTP_HOST']."]\n[$t_date, $t_time Uhr] -> ".$t_user_temp_chron."\n";
        }
        $t_user_lastconn = "$t_date, $t_time Uhr";
        $t_user_firstconn_date = substr($_SESSION['t_tracker_id'], 4, 10);
        $t_user_firstconn_time = substr($_SESSION['t_tracker_id'], 15, 8);
        $t_user_firstconn = "$t_user_firstconn_date, $t_user_firstconn_time Uhr";
        $t_sa_count = $_SESSION['t_sa_count'];
        $t_dr_query = mysql_query("DELETE FROM `".$t_table."` WHERE IP = '".$t_user_ip."'");
        if($t_dr_query) {
          $t_tr_query = mysql_query("INSERT INTO `".$t_table."` (ID, IP, SPRACHE, AGENT, POSITION, ERSTER_AUFRUF, AUFRUFE, LETZTER_AUFRUF, VERLAUF) VALUES ('".$_SESSION['t_tracker_id']."', '".$t_user_ip."', '".$t_user_lang."', '".$t_user_agent."', '".$t_user_loc."', '".$t_user_firstconn."', '".$t_sa_count."', '".$t_user_lastconn."', '".$_SESSION['t_user_chron']."')");
          if(!$t_tr_query) {
            t_log("Schreiben in die Datenbank \"$t_db\" fehlgeschlagen (SQL: ".mysql_error().")", "0");
          }
        }
      // ENDE Tracking
    } elseif($_SESSION['t_log_status']=="0") {
      echo "<i>Tracker:</i> Fehler! PrÃ¼fen Sie tracker.log fÃ¼r mehr Informationen\n";
    } else {
      echo "<i>Tracker:</i> Unbekannter Fehler.";
      t_log("Unbekannter Fehler aufgetreten. Installieren Sie das Skript erneut oder wenden Sie sich an den Support unter www.julianbaumueller.de", "0");
    }
  }
###### ENDE HAUPTFUNKTION
session_destroy(); ?>
