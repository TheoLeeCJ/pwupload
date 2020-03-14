<?php
  if (!isset($_POST["action"])) header("Location: index.html");
  else {
    session_start();
    
    $host = "ftp.dlptest.com";

    // attempt login, assign to session
    if ($_POST["action"] == "login") {
      // session already there, a mistake must have been made
      if (isset($_SESSION["user"])) echo("Just what are you trying to do?");
      // attempt login
      else {
        if (empty($_POST["user"]) || empty($_POST["pass"])) echo("Invalid request! Just what are you trying to do? Don't molest me!");
        else {
          $ftp = ftp_connect($host, 21, 5);
          // PW server is down
          if (!$ftp) echo(json_encode(array(
            "status" => "error",
            "error" => "the projectsday server is down. please try again later."
          )));
          // continue with username & password
          else {
            // proceed
            if ($host == "projectsday.hci.edu.sg") { $_POST["pass"] = strtoupper($_POST["pass"]); }
            else { ftp_pasv($ftp, true); }
            if (@ftp_login($ftp, strtolower($_POST["user"]), $_POST["pass"])) {
              ftp_close($ftp); // be a polite user
              $_SESSION["user"] = strtolower($_POST["user"]);
              $_SESSION["pass"] = $_POST["pass"];
              update_stats("logins");
              echo(json_encode(array(
                "status" => "ok"
              )));
            }
            // either is incorrect
            else echo(json_encode(array(
              "status" => "error",
              "error" => "your username or password is / are incorrect. my grammar is very good."
            )));
          }
        }
      }
    }
    // check session
    else if ($_POST["action"] == "check session") {
      // yes there's a session
      if (isset($_SESSION["user"])) echo(json_encode(array(
        "status" => "ok",
        "user" => $_SESSION["user"]
      )));
      // no session
      else {
        echo(json_encode(array(
          "status" => "ok",
          "user" => "none"
        )));
      }
    }
    // list directory
    else if ($_POST["action"] == "ls") {
      // yes there's a session, try to list directory
      $time_start = microtime(true);
      if (isset($_SESSION["user"])) {
        $ftp = ftp_connect($host, 21, 5); $connect_time = microtime(true) - $time_start;
        if ($host !== "projectsday.hci.edu.sg") { ftp_pasv($ftp, true); }
        // PW server is down
        if (!$ftp) echo(json_encode(array(
          "status" => "error",
          "error" => "the projectsday server is down. please try again later."
        )));
        // continue with username & password
        else {
          // proceed
          if (@ftp_login($ftp, $_SESSION["user"], $_SESSION["pass"])) { $login_time = microtime(true) - $time_start - $connect_time;
            $listing = ftp_nlist($ftp, "."); $dir_time = microtime(true) - $time_start - $login_time;
            $current_dir = ftp_pwd($ftp); $pwd_time = microtime(true) - $time_start - $dir_time;
            ftp_close($ftp); $close_time = microtime(true) - $time_start - $pwd_time; // be a polite user
            update_stats("directory_listings"); $update_time = microtime(true) - $time_start - $close_time;
            echo(json_encode(array(
              "status" => "ok",
              "user" => $_SESSION["user"],
              "directory" => $current_dir,
              "files" => $listing,
              "connect_time" => $connect_time,
              "login_time" => $login_time,
              "dir_time" => $dir_time,
              "pwd_time" => $pwd_time,
              "close_time" => $close_time,
              "update_time" => $update_time,
              "AAA" => "AAA"
            )));
          }
          // either is incorrect
          else {
            ftp_close($ftp); // be a polite user
            echo(json_encode(array(
              "status" => "error",
              "error" => "your username or password is / are incorrect. my grammar is very good."
            )));
          } 
        }
      }
      // no session
      else {
        echo(json_encode(array(
          "status" => "ok",
          "user" => "none"
        )));
      }
    }
    // delete file
    else if ($_POST["action"] == "delete report") {
      // yes there's a session, try to list directory
      if (isset($_SESSION["user"])) {
        $ftp = ftp_connect($host, 21, 5);
        if ($host !== "projectsday.hci.edu.sg") { ftp_pasv($ftp, true); }
        // PW server is down
        if (!$ftp) echo(json_encode(array(
          "status" => "error",
          "error" => "the projectsday server is down. please try again later."
        )));
        // continue with username & password
        else {
          // proceed
          if (@ftp_login($ftp, $_SESSION["user"], $_SESSION["pass"])) {
            // handle file
            if (@ftp_delete($ftp, "index.pdf")) {
              ftp_close($ftp); // be a polite user
              update_stats("report_deletions");
              echo "Written report (index.pdf) was deleted.";
            }
            else {
              ftp_close($ftp); // be a polite user
              update_stats("report_deletions_failed");
              echo "There was a problem while deleting the written report (index.pdf) - it wasn't even on the server!";
            }
          }
          // either is incorrect
          else {
            ftp_close($ftp); // be a polite user
            echo(json_encode(array(
              "status" => "error",
              "error" => "your username or password is / are incorrect. my grammar is very good."
            )));
          }
        }
      }
      // no session
      else {
        echo(json_encode(array(
          "status" => "ok",
          "user" => "none"
        )));
      }
    }
    // upload PDF
    else if ($_POST["action"] == "upload pdf") {
      // yes there's a session, try to list directory
      if (isset($_SESSION["user"])) {
        $ftp = ftp_connect($host, 21, 5);
        if ($host !== "projectsday.hci.edu.sg") { ftp_pasv($ftp, true); }
        // PW server is down
        if (!$ftp) echo(json_encode(array(
          "status" => "error",
          "error" => "the projectsday server is down. please try again later."
        )));
        // continue with username & password
        else {
          // proceed
          if (@ftp_login($ftp, $_SESSION["user"], $_SESSION["pass"])) {
            error_reporting(E_ALL);
            ini_set('display_errors', TRUE);
            var_dump($_FILES);
            var_dump(move_uploaded_file($_FILES["pdf"]["tmp_name"], "./index.pdf"));
            // handle file
            if (ftp_put($ftp, "index.pdf", "./index.pdf", FTP_BINARY)) {
              ftp_close($ftp); // be a polite user
              update_stats("report_uploads");
              update_stats("group", $_SESSION["user"]);
              header("Location: upload.html");
            }
            else {
              ftp_close($ftp); // be a polite user
              update_stats("report_uploads_failed");
              echo "There was a problem while uploading your written report.";
            }
          }
          // either is incorrect
          else {
            ftp_close($ftp); // be a polite user
            echo(json_encode(array(
              "status" => "error",
              "error" => "your username or password is / are incorrect. my grammar is very good."
            )));
          }
        }
      }
      // no session
      else {
        echo(json_encode(array(
          "status" => "ok",
          "user" => "none"
        )));
      }
    }
    // unknown command
    else {
      echo(json_encode(array(
        "status" => "error",
        "error" => "unknown command."
      )));
    }
  }

  // analytics
  function update_stats($field, $data = 0) {
    if (!file_exists("stats.txt")) {
      file_put_contents("stats.txt", json_encode(array(
        "COMMENT" => "These are some rudimentary statistics collected about the use of PWupload. As you can see, no personal information is collected.",
        "report_uploads" => 0,
        "report_uploads_failed" => 0,
        "report_deletions" => 0,
        "report_deletions_failed" => 0,
        "logins" => 0,
        "directory_listings" => 0,
        "uploaded" => []
      )));
    }
    $stats_file = json_decode(file_get_contents("stats.txt"), true);

    // log new group name
    if ($field == "group") array_push($stats_file["uploaded"], $data);
    // increment smth
    else $stats_file[$field] = (int)$stats_file[$field] + 1;

    file_put_contents("stats.txt", json_encode($stats_file));
  }
?>